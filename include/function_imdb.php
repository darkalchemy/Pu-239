<?php

use Imdb\Config;

/**
 * @param $imdb_id
 *
 * @return array
 */
function get_imdb_info($imdb_id)
{
    global $cache, $site_config;
    $imdb_info = $cache->get('imdb_' . $imdb_id);
    $poster = '';
    if ($imdb_info === false || is_null($imdb_info)) {
        $config = new Config();
        $config->language = 'en-US';
        $config->cachedir = IMDB_CACHE_DIR;
        $movie = new \Imdb\Title($imdb_id, $config);

        $imdb_data['title'] = $movie->title();
        $imdb_data['director'] = $movie->director();
        $imdb_data['writing'] = $movie->writing();
        $imdb_data['producer'] = $movie->producer();
        $imdb_data['composer'] = $movie->composer();
        $imdb_data['cast'] = $movie->cast();
        $imdb_data['genres'] = $movie->genres();
        $imdb_data['plotoutline'] = $movie->plotoutline(true);
        $imdb_data['trailers'] = $movie->trailers(true, true);
        $imdb_data['language'] = $movie->language();
        $imdb_data['rating'] = $movie->rating();
        $imdb_data['year'] = $movie->year();
        $imdb_data['runtime'] = $movie->runtime();
        $imdb_data['votes'] = $movie->votes();
        $imdb_data['critics'] = $movie->metacriticRating();
        $imdb_data['poster'] = $movie->photo(false);
        $imdb_data['country'] = $movie->country();
        $poster = !empty($imdb_data['poster']) ? $imdb_data['poster'] : '';

        $imdb = [
            'title'       => 'Title',
            'country'     => 'Country',
            'director'    => 'Directors',
            'writing'     => 'Writers',
            'producer'    => 'Producer',
            'cast'        => 'Cast',
            'plot'        => 'Description',
            'composer'    => 'Music',
            'plotoutline' => 'Plot outline',
            'trailers'    => 'Trailers',
            'genres'      => 'All genres',
            'language'    => 'Language',
            'rating'      => 'Rating',
            'year'        => 'Year',
            'runtime'     => 'Runtime',
            'votes'       => 'Votes',
            'critics'     => 'Critic Rating',
        ];

        foreach ($imdb_data['cast'] as $pp) {
            if (!empty($pp['name']) && !empty($pp['photo']) && !empty($pp['thumb'])) {
                $cast[] = "
                            <span class='padding5'>
                                <a href='{$site_config['anonymizer_url']}http://www.imdb.com/name/nm{$pp['imdb']}' target='_blank'>
                                    <span class='dt-tooltipper-small' data-tooltip-content='#cast_{$pp['imdb']}_tooltip'>
                                        <span>
                                            <img src='" . htmlspecialchars(image_proxy($pp['thumb'])) . "' alt='' class='round5'>
                                        </span>
                                        <span class='tooltip_templates'>
                                            <span id='cast_{$pp['imdb']}_tooltip'>
                                                <span class='is-flex'>
                                                    <span class='has-text-centered'>
                                                        <img src='" . htmlspecialchars(image_proxy($pp['photo'])) . "' class='tooltip-poster' />
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
                        if ($foo == 'cast' && !empty($cast)) {
                            $imdb_tmp[] = join(' ', $cast);
                            unset($cast);
                        }
                        if ($foo != 'cast' && $foo == 'trailers') {
                            $imdb_tmp[] = "<a href='{$site_config['anonymizer_url']}{$pp['url']}' target='_blank'>{$pp['title']}</a>";
                        } elseif ($foo != 'cast') {
                            $imdb_tmp[] = "<a href='{$site_config['anonymizer_url']}http://www.imdb.com/name/nm" . $pp['imdb'] . "' target='_blank' class='tooltipper' title='" . (!empty($pp['role']) ? $pp['role'] : 'unknown') . "'>" . $pp['name'] . "</a>";
                        }
                    }
                }
                if (!empty($imdb_tmp)) {
                    $imdb_info .= "
                    <div class='columns'>
                        <div class='has-text-red column is-2 size_5 padding5'>$boo: </div>
                        <span class='column padding5'>" . join(', ', $imdb_tmp) . "</span>
                    </div>";
                    unset($imdb_tmp);
                }
            }
        }

        $imdb_info = preg_replace('/&(?![A-Za-z0-9#]{1,7};)/', '&amp;', $imdb_info);
        $imdb_info = "<div class='padding10'>$imdb_info</div>";
        $cache->add('imdb_' . $imdb_id, $imdb_info, 0);
    }
    return [
        $imdb_info,
        $poster
    ];

}
