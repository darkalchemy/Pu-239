<?php

use Imdb\Config;

/**
 * @param      $imdb_id
 * @param bool $title
 *
 * @return array|bool
 *
 * @throws Exception
 */
function get_imdb_info($imdb_id, $title = true)
{
    global $cache, $BLOCKS, $fluent;

    if (!$BLOCKS['imdb_api_on']) {
        return false;
    }

    $imdbid = $imdb_id;
    $imdb_id = str_replace('tt', '', $imdb_id);
    $imdb_data = $cache->get('imdb_' . $imdb_id);
    if ($imdb_data === false || is_null($imdb_data)) {
        $config = new Config();
        $config->language = 'en-US';
        $config->cachedir = IMDB_CACHE_DIR;
        $config->throwHttpExceptions = 0;
        $config->default_agent = get_random_useragent();

        $movie = new \Imdb\Title($imdb_id, $config);
        if (empty($movie->title())) {
            $cache->set('imdb_' . $imdb_id, 0, 86400);

            return false;
        }
        $imdb_data = [
            'title' => $movie->title(),
            'director' => array_slice($movie->director(), 0, 30),
            'writing' => array_slice($movie->writing(), 0, 30),
            'producer' => array_slice($movie->producer(), 0, 30),
            'composer' => array_slice($movie->composer(), 0, 30),
            'cast' => array_slice($movie->cast(), 0, 30),
            'genres' => $movie->genres(),
            'plotoutline' => $movie->plotoutline(true),
            'trailers' => $movie->trailers(true, true),
            'language' => $movie->language(),
            'rating' => $movie->rating(),
            'year' => $movie->year(),
            'runtime' => $movie->runtime(),
            'votes' => $movie->votes(),
            'critics' => $movie->metacriticRating(),
            'poster' => $movie->photo(false),
            'country' => $movie->country(),
        ];

        $cache->set('imdb_' . $imdb_id, $imdb_data, 604800);
    }
    if (empty($imdb_data)) {
        $cache->set('imdb_' . $imdb_id, 0, 86400);

        return false;
    }
    $poster = !empty($imdb_data['poster']) ? $imdb_data['poster'] : '';

    if (!empty($poster)) {
        $insert = $cache->get('insert_imdb_imdbid_' . $imdbid);
        if ($insert === false || is_null($insert)) {
            $values = [
                'imdb_id' => $imdbid,
                'url' => $imdb_data['poster'],
                'type' => 'poster',
            ];
            $fluent->insertInto('images')
                ->values($values)
                ->ignore()
                ->execute();

            $cache->set('insert_imdb_imdbid_' . $imdbid, 0, 604800);
        }
    }

    $imdb = [
        'title' => 'Title',
        'country' => 'Country',
        'director' => 'Directors',
        'writing' => 'Writers',
        'producer' => 'Producer',
        'plot' => 'Description',
        'composer' => 'Music',
        'plotoutline' => 'Plot outline',
        'trailers' => 'Trailers',
        'genres' => 'All genres',
        'language' => 'Language',
        'rating' => 'Rating',
        'year' => 'Year',
        'runtime' => 'Runtime',
        'votes' => 'Votes',
        'critics' => 'Critic Rating',
        'cast' => 'Cast',
    ];

    foreach ($imdb_data['cast'] as $pp) {
        if (!empty($pp['name']) && !empty($pp['photo']) && !empty($pp['thumb'])) {
            $cast[] = "
                            <span class='padding5'>
                                <a href='" . url_proxy("https://www.imdb.com/name/nm{$pp['imdb']}") . "' target='_blank'>
                                    <span class='dt-tooltipper-small' data-tooltip-content='#cast_{$pp['imdb']}_tooltip'>
                                        <span class='cast'>
                                            <img src='" . url_proxy(strip_tags($pp['thumb']), true) . "' alt='' class='round5'>
                                        </span>
                                        <span class='tooltip_templates'>
                                            <span id='cast_{$pp['imdb']}_tooltip'>
                                                <span class='is-flex'>
                                                    <span class='has-text-centered'>
                                                        <img src='" . url_proxy(strip_tags($pp['photo']), true, 150, null) . "' class='tooltip-poster' />
                                                        <p class='top10'>{$pp['name']}</p>
                                                        <p>{$pp['role']}</p>
                                                    </span>
                                                </span>
                                            </span>
                                        </span>
                                    </span>
                                </a>
                            </span>";
        }
    }

    $imdb_info = '';
    foreach ($imdb as $foo => $boo) {
        if (isset($imdb_data[$foo]) && !empty($imdb_data[$foo])) {
            if (!is_array($imdb_data[$foo])) {
                $imdb_info .= "
                    <div class='columns'>
                        <div class='has-text-red column is-2 size_5 padding5'>$boo: </div>
                        <div class='column padding5'>{$imdb_data[$foo]}</div>
                    </div>";
            } elseif (is_array($imdb_data[$foo]) && in_array($foo, [
                    'director',
                    'writing',
                    'producer',
                    'composer',
                    'cast',
                    'trailers',
                ])) {
                foreach ($imdb_data[$foo] as $pp) {
                    if ($foo === 'cast' && !empty($cast)) {
                        $imdb_tmp[] = implode(' ', $cast);
                        unset($cast);
                    }
                    if ($foo === 'trailers') {
                        $imdb_tmp[] = "<a href='" . url_proxy($pp['url']) . "' target='_blank'>{$pp['title']}</a>";
                    } elseif ($foo != 'cast') {
                        $imdb_tmp[] = "<a href='" . url_proxy("https://www.imdb.com/name/nm{$pp['imdb']}") . "' target='_blank' class='tooltipper' title='" . (!empty($pp['role']) ? $pp['role'] : 'unknown') . "'>" . $pp['name'] . '</a>';
                    }
                }
            }

            if (!empty($imdb_tmp)) {
                $imdb_info .= "
                    <div class='columns'>
                        <div class='has-text-red column is-2 size_5 padding5'>$boo: </div>
                        <div class='column padding5'>" . implode(', ', $imdb_tmp) . '</div>
                    </div>';
                unset($imdb_tmp);
            }
        }
    }

    $imdb_info = preg_replace('/&(?![A-Za-z0-9#]{1,7};)/', '&amp;', $imdb_info);
    if ($title) {
        $imdb_info = "<div class='padding10'><div class='has-text-centered size_6 bottom20'>IMDb</div>$imdb_info</div>";
    } else {
        $imdb_info = "<div class='padding10'>$imdb_info</div>";
    }

    return [
        $imdb_info,
        $poster,
    ];
}

/**
 * @param $imdb_id
 *
 * @return bool|null|string|string[]
 *
 * @throws Exception
 */
function get_imdb_info_short($imdb_id)
{
    global $cache, $BLOCKS, $fluent;

    if (!$BLOCKS['imdb_api_on']) {
        return false;
    }

    $imdbid = $imdb_id;
    $imdb_id = str_replace('tt', '', $imdb_id);
    $imdb_data = $cache->get('imdb_short_' . $imdb_id);
    if ($imdb_data === false || is_null($imdb_data)) {
        $config = new Config();
        $config->language = 'en-US';
        $config->cachedir = IMDB_CACHE_DIR;
        $config->throwHttpExceptions = 0;
        $config->default_agent = get_random_useragent();

        $movie = new \Imdb\Title($imdb_id, $config);
        if (empty($movie->title())) {
            return false;
        }
        $poster = $placeholder = '';

        if (!empty($movie->photo(false))) {
            $image = url_proxy($movie->photo(true), true, 150);
            if ($image) {
                $poster = $image;
                $placeholder = url_proxy($movie->photo(true), true, 150, null, 10);
            }
        }

        $imdb_data = [
            'orig_poster' => $movie->photo(false),
            'poster' => $poster,
            'placeholder' => $placeholder,
            'title' => $movie->title(),
            'vote_count' => $movie->votes(),
            'critic' => $movie->metacriticRating(),
            'rating' => $movie->rating(),
            'overview' => $movie->plotoutline(true),
            'mpaa' => $movie->mpaa(),
            'mpaa_reason' => $movie->mpaa_reason(),
            'id' => $imdbid,
        ];
        $cache->set('imdb_short_' . $imdb_id, $imdb_data, 604800);
    }

    if (empty($imdb_data)) {
        $cache->set('imdb_short_' . $imdb_id, 0, 86400);

        return false;
    }

    if (!empty($imdb_data['critic'])) {
        $imdb_data['critic'] .= '%';
    } else {
        $imdb_data['critic'] = '?';
    }
    if (empty($imdb_data['vote_count'])) {
        $imdb_data['vote_count'] = '?';
    }
    if (empty($imdb_data['rating'])) {
        $imdb_data['rating'] = '?';
    }
    if (empty($imdb_data['mpaa_reason']) && !empty($imdb_data['mpaa']['United States'])) {
        $imdb_data['mpaa_reason'] = $imdb_data['mpaa']['United States'];
    }

    if (!empty($imdb_data['orig_poster'])) {
        $insert = $cache->get('insert_imdb_imdbid_' . $imdbid);
        if ($insert === false || is_null($insert)) {
            $values = [
                'imdb_id' => $imdbid,
                'url' => $imdb_data['orig_poster'],
                'type' => 'poster',
            ];
            $fluent->insertInto('images')
                ->values($values)
                ->ignore()
                ->execute();

            $cache->set('insert_imdb_imdbid_' . $imdbid, 0, 604800);
        }
    }

    $imdb_info = "
            <div class='padding10 round10 bg-00 margin10'>
                <div class='dt-tooltipper-large has-text-centered' data-tooltip-content='#movie_{$imdb_data['id']}_tooltip'>
                    <img src='{$imdb_data['placeholder']}' data-src='{$imdb_data['poster']}' alt='Poster' class='lazy tooltip-poster'>
                    <div class='has-text-centered top10'>{$imdb_data['title']}</div>
                    <div class='tooltip_templates'>
                        <div id='movie_{$imdb_data['id']}_tooltip' class='round10 tooltip-background'>
                            <div class='is-flex tooltip-torrent bg-09'>
                                <span class='padding10 w-40'>
                                    <img data-src='{$imdb_data['poster']}' alt='Poster' class='lazy tooltip-poster'>
                                </span>
                                <span class='padding10'>
                                    <p>
                                        <span class='size_4 right10 has-text-primary has-text-bold'>Title: </span>
                                        <span>" . htmlsafechars($imdb_data['title']) . "</span>
                                    </p>
                                    <p>
                                        <span class='size_4 right10 has-text-primary'>MPAA: </span>
                                        <span>" . htmlsafechars($imdb_data['mpaa_reason']) . "</span>
                                    </p>
                                    <p>
                                        <span class='size_4 right10 has-text-primary'>Critics: </span>
                                        <span>" . htmlsafechars($imdb_data['critic']) . "</span>
                                    </p>
                                    <p>
                                        <span class='size_4 right10 has-text-primary'>Rating: </span>
                                        <span>" . htmlsafechars($imdb_data['rating']) . "</span>
                                    </p>
                                    <p>
                                        <span class='size_4 right10 has-text-primary'>Votes: </span>
                                        <span>" . htmlsafechars($imdb_data['vote_count']) . "</span>
                                    </p>
                                    <p>
                                        <span class='size_4 right10 has-text-primary'>Overview: </span>
                                        <span>" . htmlsafechars(strip_tags($imdb_data['overview'])) . '</span>
                                    </p>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>';

    $imdb_info = preg_replace('/&(?![A-Za-z0-9#]{1,7};)/', '&amp;', $imdb_info);

    return $imdb_info;
}

/**
 * @return array|bool
 */
function get_upcoming()
{
    global $cache, $BLOCKS;

    if (!$BLOCKS['imdb_api_on']) {
        return false;
    }

    $imdb_data = $cache->get('imdb_upcoming_');
    if ($imdb_data === false || is_null($imdb_data)) {
        $url = 'https://www.imdb.com/movies-coming-soon/';
        $imdb_data = fetch($url);
        if ($imdb_data) {
            $cache->set('imdb_upcoming_', $imdb_data, 86400);
        } else {
            $cache->set('imdb_upcoming_', 0, 3600);
        }
    }

    if (empty($imdb_data)) {
        return false;
    }

    preg_match_all('/<h4.*<a name=.*>(.*)&nbsp;/i', $imdb_data, $timestamp);
    $dates = $timestamp[1];
    $regex = '';
    foreach ($dates as $date) {
        $regex .= '<a name(.*)';
    }
    $regex .= 'see-more';
    preg_match("/$regex/isU", $imdb_data, $datemovies);
    $temp = [];
    foreach ($datemovies as $key => $value) {
        preg_match_all('/<table(.*)<\/table/isU', $value, $out);
        if ($key != 0) {
            $temp[$dates[$key - 1]] = $out[1];
        }
    }
    $imdbs = [];
    foreach ($dates as $date) {
        foreach ($temp[$date] as $code) {
            preg_match('/title\/(tt[\d]{7})/i', $code, $imdb);
            if (!empty($imdb[1])) {
                $imdbs[$date][] = $imdb[1];
            }
        }
    }

    if (!empty($imdbs)) {
        return $imdbs;
    }

    return false;
}

/**
 * @return bool|mixed
 */
function get_random_useragent()
{
    global $fluent, $cache, $site_config, $BLOCKS;

    if (!$BLOCKS['imdb_api_on']) {
        return false;
    }

    $browser = $cache->get('browser_user_agents_');
    if ($browser === false || is_null($browser)) {
        $results = $fluent->from('users')
            ->select(null)
            ->select('DISTINCT browser')
            ->limit(100);
        $browser = [];
        foreach ($results as $result) {
            preg_match('/Agent : (.*)/', $result['browser'], $match);
            if (!empty($match[1])) {
                $browser[] = $match[1];
            }
        }
        $cache->set('browser_user_agents_', $browser, $site_config['expires']['browser_user_agent']);
    }

    if (!empty($browser)) {
        shuffle($browser);
    } else {
        $browser[] = 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/67.0.3396.99 Safari/537.36';
    }

    return $browser[0];
}
