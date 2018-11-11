<?php

require_once INCL_DIR . 'function_fanart.php';
require_once INCL_DIR . 'function_tmdb.php';
require_once INCL_DIR . 'function_imdb.php';
require_once INCL_DIR . 'function_details.php';
require_once INCL_DIR . 'html_functions.php';

use Imdb\Config;

/**
 * @param      $imdb_id
 * @param bool $title
 *
 * @return array|bool
 *
 * @throws Exception
 */
function get_imdb_info($imdb_id, $title = true, $data_only = false, $tid = false, $poster = false)
{
    global $cache, $BLOCKS, $torrent_stuffs, $image_stuffs;

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
            $cache->set('imdb_' . $imdb_id, 'failed', 86400);

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
            'rating' => is_numeric($movie->rating()) ? $movie->rating() : 0,
            'year' => $movie->year(),
            'runtime' => $movie->runtime(),
            'votes' => $movie->votes(),
            'critics' => $movie->metacriticRating(),
            'poster' => $movie->photo(false),
            'country' => $movie->country(),
            'vote_count' => $movie->votes(),
            'overview' => $movie->plotoutline(true),
            'mpaa' => $movie->mpaa(),
            'mpaa_reason' => $movie->mpaa_reason(),
            'id' => $imdbid,
            'updated' => get_date(TIME_NOW, 'LONG'),
        ];

        if (count($imdb_data['genres']) > 0) {
            $temp = implode(', ', array_map('strtolower', $imdb_data['genres']));
            $temp = explode(', ', $temp);
            $imdb_data['newgenre'] = implode(', ', array_map('ucwords', $temp));
        }

        $set = [];
        if (!empty($imdb_data['newgenre'])) {
            $set = [
                'newgenre' => $imdb_data['newgenre'],
            ];
        }
        $set = array_merge($set, [
            'year' => $imdb_data['year'],
            'rating' => $imdb_data['rating'],
        ]);

        if ($tid) {
            $torrent_stuffs->set($set, $tid);
        }
        $cache->set('imdb_' . $imdb_id, $imdb_data, 604800);
    }
    if (empty($imdb_data)) {
        $cache->set('imdb_' . $imdb_id, 'failed', 86400);

        return false;
    }
    if ($data_only) {
        return $imdb_data;
    }
    if (!empty($poster)) {
        $poster = $poster;
    }
    if (!empty($imdb_data['poster'])) {
        if (empty($poster)) {
            $poster = $imdb_data['poster'];
        }
        $values = [
            'imdb_id' => $imdbid,
            'url' => $imdb_data['poster'],
            'type' => 'poster',
        ];
        $image_stuffs->insert($values);
    }
    if (empty($poster)) {
        $poster = get_poster($imdbid);
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
        'updated' => 'Last Updated',
        'cast' => 'Cast',
    ];

    foreach ($imdb_data['cast'] as $pp) {
        if (!empty($pp['name']) && !empty($pp['photo'])) {
            $cast[] = "
                            <span class='padding5'>
                                <a href='" . url_proxy("https://www.imdb.com/name/nm{$pp['imdb']}") . "' target='_blank'>
                                    <span class='dt-tooltipper-small' data-tooltip-content='#cast_{$pp['imdb']}_tooltip'>
                                        <span class='cast'>
                                            <img src='" . url_proxy(strip_tags($pp['photo']), true, null, 60) . "' class='round5'>
                                        </span>
                                        <span class='tooltip_templates'>
                                            <span id='cast_{$pp['imdb']}_tooltip'>
                                                <span class='is-flex'>
                                                    <span class='has-text-centered'>
                                                        <img src='" . url_proxy(strip_tags($pp['photo']), true, 150, null) . "' class='tooltip-poster'>
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
                $imdb_data[$foo] = $boo === 'Title' ? "<a href='" . url_proxy("https://www.imdb.com/title/{$imdbid}") . "' target='_blank' class='tooltipper' title='IMDb: {$imdb_data[$foo]}'>{$imdb_data[$foo]}</a>" : $imdb_data[$foo];
                if ($boo === 'Rating') {
                    $percent = $imdb_data['rating'] * 10;
                    $imdb_data[$foo] = "
                        <div class='star-ratings-css tooltipper' title='{$percent}% out of {$imdb_data['votes']} votes!'>
                            <div class='star-ratings-css-top' style='width: {$percent}%'><span>★</span><span>★</span><span>★</span><span>★</span><span>★</span></div>
                            <div class='star-ratings-css-bottom'><span>★</span><span>★</span><span>★</span><span>★</span><span>★</span></div>
                        </div>";
                }
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
                        $imdb_tmp[] = "<a href='" . url_proxy($pp['url']) . "' target='_blank' class='tooltipper' title='IMDb: {$pp['title']}'>{$pp['title']}</a>";
                    } elseif ($foo != 'cast') {
                        $role = !empty($pp['role']) ? ucwords($pp['role']) : 'unknown';
                        $imdb_tmp[] = "<a href='" . url_proxy("https://www.imdb.com/name/nm{$pp['imdb']}") . "' target='_blank' class='tooltipper' title='$role'>" . $pp['name'] . '</a>';
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
        $imdb_info = "
        <div class='padding10'>
            <div class='columns bottom20'>
                <div class='column is-3'>
                    <img src='" . placeholder_image('225') . "' data-src='" . url_proxy($poster, true, 225) . "' class='lazy round10 img-polaroid'>
                </div>
                <div class='column'>
                    $imdb_info
                </div>
            </div>
        </div>";
    } else {
        $imdb_info = "<div class='padding10'>$imdb_info</div>";
    }
    $cache->set('imdb_fullset_' . $imdbid, $imdb_info, 604800);

    return [
        $imdb_info,
        $poster,
    ];
}

function get_imdb_title($imdb_id)
{
    global $cache, $BLOCKS, $site_config, $image_stuffs;

    if (!$BLOCKS['imdb_api_on']) {
        return false;
    }

    $imdbid = $imdb_id;
    $imdb_id = str_replace('tt', '', $imdb_id);
    $imdb_data = get_imdb_info($imdb_id, true, true);
    if (empty($imdb_data['title'])) {
        return false;
    }

    return $imdb_data['title'];
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
    global $cache, $BLOCKS, $site_config, $image_stuffs;

    if (!$BLOCKS['imdb_api_on']) {
        return false;
    }

    $imdbid = $imdb_id;
    $imdb_id = str_replace('tt', '', $imdb_id);
    $imdb_data = get_imdb_info($imdb_id, true, true);
    if (empty($imdb_data)) {
        return false;
    }
    $poster = $placeholder = '';

    if (empty($imdb_data['poster'])) {
        $poster = getMovieImagesByID($imdbid, 'movieposter');
        $imdb_data['poster'] = $poster;
    }
    if (empty($imdb_data['poster'])) {
        $tmdbid = get_movie_id($imdbid, 'tmdb_id');
        if (!empty($tmdbid)) {
            $poster = getMovieImagesByID($tmdbid, 'movieposter');
            $imdb_data['poster'] = $poster;
        }
    }
    if (empty($imdb_data['poster'])) {
        $omdb = get_omdb_info($imdbid, true, true);
        if (!empty($omdb['Poster']) && $omdb['Poster'] != 'N/A') {
            $imdb_data['poster'] = $omdb['Poster'];
        }
    }
    if (!empty($imdb_data['poster'])) {
        $image = url_proxy($imdb_data['poster'], true, 150);
        if ($image) {
            $imdb_data['poster'] = $image;
            $imdb_data['placeholder'] = url_proxy($imdb_data['poster'], true, 150, null, 10);
        }
        $values = [];
        if (!empty($tmdbid)) {
            $values = [
                'tmdb_id' => $tmdbid,
            ];
        }
        $values = array_merge($values, [
            'imdb_id' => $imdbid,
            'url' => $poster,
            'type' => 'poster',
        ]);
        $image_stuffs->insert($values);
    }
    if (empty($imdb_data['poster'])) {
        $poster = $site_config['pic_baseurl'] . 'noposter.png';
        $imdb_data['poster'] = $poster;
        $imdb_data['placeholder'] = $poster;
    }
    if (!empty($imdb_data['critics'])) {
        $imdb_data['critics'] .= '%';
    } else {
        $imdb_data['critics'] = '?';
    }
    if (empty($imdb_data['vote_count'])) {
        $imdb_data['vote_count'] = '?';
    }
    if (empty($imdb_data['rating'])) {
        $rating = '?';
    }
    if (empty($imdb_data['mpaa_reason']) && !empty($imdb_data['mpaa']['United States'])) {
        $imdb_data['mpaa_reason'] = $imdb_data['mpaa']['United States'];
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
                                    <img src='{$imdb_data['placeholder']}' data-src='{$imdb_data['poster']}' alt='Poster' class='lazy tooltip-poster'>
                                </span>
                                <span class='padding10'>
                                    <div>
                                        <span class='size_4 right10 has-text-primary has-text-bold'>Title: </span>
                                        <span>" . htmlsafechars($imdb_data['title']) . "</span>
                                    </div>
                                    <div>
                                        <span class='size_4 right10 has-text-primary'>MPAA: </span>
                                        <span>" . htmlsafechars($imdb_data['mpaa_reason']) . "</span>
                                    </div>
                                    <div>
                                        <span class='size_4 right10 has-text-primary'>Critics: </span>
                                        <span>" . htmlsafechars($imdb_data['critics']) . "</span>
                                    </div>
                                    <div>
                                        <span class='size_4 right10 has-text-primary'>Rating: </span>
                                        <span>" . htmlsafechars($rating) . "</span>
                                    </div>
                                    <div>
                                        <span class='size_4 right10 has-text-primary'>Votes: </span>
                                        <span>" . htmlsafechars($imdb_data['vote_count']) . "</span>
                                    </div>
                                    <div>
                                        <span class='size_4 right10 has-text-primary'>Overview: </span>
                                        <span>" . htmlsafechars(strip_tags($imdb_data['overview'])) . '</span>
                                    </div>
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
            $cache->set('imdb_upcoming_', 'failed', 3600);
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
        foreach ($imdbs as $day) {
            foreach ($day as $imdb) {
                get_imdb_info($imdb);
            }
        }

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
