<?php

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'user_functions.php';
require_once INCL_DIR . 'bbcode_functions.php';
require_once INCL_DIR . 'html_functions.php';
require_once INCL_DIR . 'function_imdb.php';
require_once INCL_DIR . 'function_tmdb.php';
require_once INCL_DIR . 'function_tvmaze.php';
require_once INCL_DIR . 'function_omdb.php';
require_once INCL_DIR . 'function_bluray.php';
require_once INCL_DIR . 'function_fanart.php';
check_user_status();
global $CURUSER, $site_config, $fluent, $cache, $session;

$lang = load_language('global');

$lists = [
    'upcoming',
    'top100',
    'theaters',
    'tv',
    'tvmaze',
    'bluray',
];

extract($_GET);
if (empty($list) || !in_array($list, $lists)) {
    $list = 'upcoming';
}

switch ($list) {
    case 'bluray':
        $title = 'Bluray Releases';
        $pubs = get_bluray_info();

        if (!empty($pubs)) {
            $div = "
        <div class='level-center'>";
            foreach ($pubs as $movie) {
                $div .= "
            <div class='padding10 round10 bg-00 margin10'>
                <div class='dt-tooltipper-large has-text-centered' data-tooltip-content='#movie_{$movie['imdbid']}_tooltip'>
                    <img src='{$movie['placeholder']}' data-src='{$movie['poster']}' alt='Poster' class='lazy tooltip-poster'>
                    <div class='has-text-centered top10'>{$movie['title']} ({$movie['year']})</div>
                    <div class='has-text-centered'>{$movie['release_date']}</div>
                    <div class='tooltip_templates'>
                        <div id='movie_{$movie['imdbid']}_tooltip' class='round10 tooltip-background'>
                            <div class='is-flex tooltip-torrent bg-09'>
                                <span class='padding10 w-40'>
                                    <img data-src='{$movie['poster']}' alt='Poster' class='lazy tooltip-poster'>
                                </span>
                                <span class='padding10'>
                                    <p><span class='size_4 right10 has-text-primary has-text-bold'>Title: </span><span>" . htmlsafechars($movie['title']) . "</span></p>
                                    <p><span class='size_4 right10 has-text-primary'>Release Date: </span><span>" . htmlsafechars($movie['release_date']) . "</span></p>
                                    <p><span class='size_4 right10 has-text-primary'>Genre: </span><span>" . htmlsafechars($movie['genre']) . "</span></p>
                                    <p><span class='size_4 right10 has-text-primary'>Rating: </span><span>" . htmlsafechars($movie['mpaa']) . "</span></p>
                                    <p><span class='size_4 right10 has-text-primary'>Runtime: </span>" . htmlsafechars($movie['runtime']) . "</span></p>
                                    <p><span class='size_4 right10 has-text-primary'>Overview: </span><span>" . htmlsafechars($movie['description']) . '</span></p>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>';
            }

            $div .= '
        </div>';
        } else {
            $div = "<h1 class='has-text-centered'>Blu-ray.com may be down, check back later</h1>";
        }

        $HTMLOUT = "
        <h1 class='has-text-centered'>Blu-ray Releases</h1>" . main_div($div);

        break;

    case 'tvmaze':
        $title = 'TV Schedule';
        $tvmaze_data = get_schedule();

        if ($tvmaze_data) {
            $today = date('Y-m-d');
            $shows = [];
            foreach ($tvmaze_data as $listing) {
                if (!empty($listing['airstamp']) && !empty($listing['airdate']) && $listing['airdate'] === $today && $listing['_embedded']['show']['language'] === 'English') {
                    $shows[] = $listing;
                }
            }

            if (!empty($shows)) {
                usort($shows, 'timeSort');
                $titles = $body = [];
                foreach ($shows as $tv) {
                    if (!empty($tv['name']) && !in_array(strtolower($tv['name']), $titles)) {
                        $poster = !empty($tv['image']['original']) ? $tv['image']['original'] : !empty($tv['_embedded']['show']['image']['original']) ? $tv['_embedded']['show']['image']['original'] : $site_config['pic_baseurl'] . 'noposter.png';
                        $airtime = strtotime($tv['airstamp']);
                        $use_12_hour = !empty($CURUSER['12_hour']) ? $CURUSER['12_hour'] === 'yes' ? 1 : 0 : $site_config['12_hour'];
                        $body[] = [
                            'poster'      => url_proxy($poster, true, 150),
                            'placeholder' => url_proxy($poster, true, 150, null, 10),
                            'title'       => $tv['_embedded']['show']['name'],
                            'ep_title'    => $tv['name'],
                            'season'      => $tv['season'],
                            'episode'     => $tv['number'],
                            'runtime'     => !empty($tv['runtime']) ? "{$tv['runtime']} minutes" : '',
                            'type'        => $tv['_embedded']['show']['type'],
                            'airtime'     => !empty($tv['airtime']) ? $use_12_hour ? time24to12($airtime) : get_date($airtime, 'WITHOUT_SEC', 1, 1) : '',
                            'id'          => $tv['_embedded']['show']['id'],
                            'overview'    => str_replace([
                                '<p>',
                                '</p>',
                                '<b>',
                                '</b>',
                                '<i>',
                                '</i>',
                            ], '', $tv['_embedded']['show']['summary']),
                        ];
                        $titles[] = strtolower($tv['_embedded']['show']['name']);
                    }
                }

                $div = "
        <h1 class='has-text-centered'>TV Today</h1>
        <div class='level-center'>";
                foreach ($body as $tv) {
                    $div .= "
            <div class='padding10 round10 bg-00 margin10'>
                <div class='dt-tooltipper-large has-text-centered' data-tooltip-content='#movie_{$tv['id']}_tooltip'>
                    <img src='{$tv['placeholder']}' data-src='{$tv['poster']}' alt='Poster' class='lazy tooltip-poster'>
                    <div class='has-text-centered top10'>{$tv['title']}</div>
                    <div class='has-text-centered top10'>{$tv['airtime']}</div>
                    <div class='tooltip_templates'>
                        <div id='movie_{$tv['id']}_tooltip'>
                            <div class='is-flex tooltip-torrent bg-09'>
                                <span class='padding10 w-40'>
                                    <img data-src='{$tv['poster']}' alt='Poster' class='lazy tooltip-poster'>
                                </span>
                                <span class='padding10'>
                                    <p><span class='size_4 right10 has-text-primary has-text-bold'>Title: </span><span>" . htmlsafechars($tv['title']) . "</span></p>
                                    <p><span class='size_4 right10 has-text-primary has-text-bold'>Episode Title: </span><span>" . htmlsafechars($tv['ep_title']) . "</span></p>
                                    <p><span class='size_4 right10 has-text-primary has-text-bold'>Season: </span><span>" . htmlsafechars($tv['season']) . "</span></p>
                                    <p><span class='size_4 right10 has-text-primary has-text-bold'>Episode: </span><span>" . htmlsafechars($tv['episode']) . "</span></p>
                                    <p><span class='size_4 right10 has-text-primary has-text-bold'>Runtime: </span><span>" . htmlsafechars($tv['runtime']) . "</span></p>
                                    <p><span class='size_4 right10 has-text-primary has-text-bold'>Type: </span><span>" . htmlsafechars($tv['type']) . "</span></p>
                                    <p><span class='size_4 right10 has-text-primary'>Overview: </span><span>" . htmlsafechars($tv['overview']) . '</span></p>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>';
                }

                $div .= '
        </div>';

                $HTMLOUT = main_div($div);
            }
        } else {
            $HTMLOUT = main_div("<h1 class='has-text-centered'>TVMaze may be down, check back later</h1>");
        }

        break;

    case 'tv':
        $title = 'TV Schedule';
        $base = $today = date('Y-m-d');
        if (!empty($date)) {
            $today = $date;
        }
        $date = new DateTime($today);
        $yesterday = $date->modify('-1 day')
            ->format('Y-m-d');
        $date = new DateTime($today);
        $tomorrow = $date->modify('+1 day')
            ->format('Y-m-d');
        $date = new DateTime($today);
        $display = $date->format('l Y-m-d');

        $HTMLOUT = "
    <h1 class='has-text-centered'>TV Airing By Date</h1>
    <div class='level-center top20'>
        <a href='{$_SERVER['PHP_SELF']}?date={$yesterday}' class='tooltipper' title='{$yesterday}'>{$yesterday}</a>
        <a href='{$_SERVER['PHP_SELF']}?date={$base}' class='tooltipper' title='GoTo {$base}'><h2>{$display}</h2></a>
        <a href='{$_SERVER['PHP_SELF']}?date={$tomorrow}' class='tooltipper' title='{$tomorrow}'>{$tomorrow}</a>
    </div>";

        $tvs = get_tv_by_day($today);

        if ($tvs) {
            $titles = $body = [];
            foreach ($tvs as $tv) {
                if (!empty($tv['name']) && !in_array(strtolower($tv['name']), $titles)) {
                    $poster = !empty($tv['poster_path']) ? "https://image.tmdb.org/t/p/w185{$tv['poster_path']}" : $site_config['pic_baseurl'] . 'noposter.png';
                    $backdrop = !empty($tv['backdrop_path']) ? "https://image.tmdb.org/t/p/w1280{$tv['backdrop_path']}" : '';

                    $body[] = [
                        'poster'       => url_proxy($poster, true, 150),
                        'placeholder'  => url_proxy($poster, true, 150, null, 10),
                        'backdrop'     => url_proxy($backdrop, true),
                        'title'        => $tv['name'],
                        'vote_count'   => $tv['vote_count'],
                        'id'           => $tv['id'],
                        'vote_average' => $tv['vote_average'],
                        'popularity'   => $tv['popularity'],
                        'overview'     => $tv['overview'],
                    ];
                    $titles[] = strtolower($tv['name']);
                }
            }

            $div = "
        <div class='level-center'>";
            foreach ($body as $tv) {
                $div .= "
            <div class='padding10 round10 bg-00 margin10'>
                <div class='dt-tooltipper-large has-text-centered' data-tooltip-content='#movie_{$tv['id']}_tooltip'>
                    <img src='{$tv['placeholder']}' data-src='{$tv['poster']}' alt='Poster' class='lazy tooltip-poster'>
                    <div class='has-text-centered top10'>{$tv['title']}</div>
                    <div class='tooltip_templates'>
                        <div id='movie_{$tv['id']}_tooltip' class='round10 tooltip-background' style='background-image: url({$tv['backdrop']});'>
                            <div class='is-flex tooltip-torrent bg-09'>
                                <span class='padding10 w-40'>
                                    <img data-src='{$tv['poster']}' alt='Poster' class='lazy tooltip-poster'>
                                </span>
                                <span class='padding10'>
                                    <p><span class='size_4 right10 has-text-primary has-text-bold'>Title: </span><span>" . htmlsafechars($tv['title']) . "</span></p>
                                    <p><span class='size_4 right10 has-text-primary'>Popularity: </span><span>" . htmlsafechars($tv['popularity']) . "</span></p>
                                    <p><span class='size_4 right10 has-text-primary'>Votes: </span><span>" . htmlsafechars($tv['vote_average']) . "</span></p>
                                    <p><span class='size_4 right10 has-text-primary'>Overview: </span><span>" . htmlsafechars($tv['overview']) . '</span></p>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>';
            }

            $div .= '
        </div>';

            $HTMLOUT .= main_div($div);
        } else {
            $HTMLOUT = main_div("<h1 class='has-text-centered'>TMDb may be down, check back later</h1>");
        }

        break;

    case 'theaters':
        $title = 'In Theaters';
        $HTMLOUT = "
    <h1 class='has-text-centered'>In Theaters</h1>";

        $movies = get_movies_in_theaters();

        if ($movies) {
            $titles = $body = [];
            foreach ($movies as $movie) {
                if (!empty($movie['title']) && !in_array(strtolower($movie['title']), $titles)) {
                    $poster = !empty($movie['poster_path']) ? "https://image.tmdb.org/t/p/w185{$movie['poster_path']}" : $site_config['pic_baseurl'] . 'noposter.png';
                    $backdrop = !empty($movie['backdrop_path']) ? "https://image.tmdb.org/t/p/w1280{$movie['backdrop_path']}" : '';
                    $body[] = [
                        'poster'       => url_proxy($poster, true, 150),
                        'placeholder'  => url_proxy($poster, true, 150, null, 10),
                        'backdrop'     => url_proxy($backdrop, true),
                        'title'        => $movie['title'],
                        'vote_count'   => $movie['vote_count'],
                        'id'           => $movie['id'],
                        'vote_average' => $movie['vote_average'],
                        'popularity'   => $movie['popularity'],
                        'overview'     => $movie['overview'],
                        'release_date' => $movie['release_date'],
                    ];
                    $titles[] = strtolower($movie['title']);
                }
            }

            $div = "
        <div class='level-center'>";
            foreach ($body as $movie) {
                $div .= "
            <div class='padding10 round10 bg-00 margin10'>
                <div class='dt-tooltipper-large has-text-centered' data-tooltip-content='#movie_{$movie['id']}_tooltip'>
                    <img src='{$movie['placeholder']}' data-src='{$movie['poster']}' alt='Poster' class='lazy tooltip-poster'>
                    <div class='has-text-centered top10'>{$movie['title']}</div>
                    <div class='has-text-centered'>{$movie['release_date']}</div>
                    <div class='tooltip_templates'>
                        <div id='movie_{$movie['id']}_tooltip' clss='round10 tooltip-background' style='background-image: url({$movie['backdrop']});'>
                            <div class='is-flex tooltip-torrent bg-09'>
                                <span class='padding10 w-40'>
                                    <img data-src='{$movie['poster']}' alt='Poster' class='lazy tooltip-poster'>
                                </span>
                                <span class='padding10'>
                                    <p><span class='size_4 right10 has-text-primary has-text-bold'>Title: </span><span>" . htmlsafechars($movie['title']) . "</span></p>
                                    <p><span class='size_4 right10 has-text-primary'>Release Date: </span><span>" . htmlsafechars($movie['release_date']) . "</span></p>
                                    <p><span class='size_4 right10 has-text-primary'>Popularity: </span><span>" . htmlsafechars($movie['popularity']) . "</span></p>
                                    <p><span class='size_4 right10 has-text-primary'>Votes: </span><span>" . htmlsafechars($movie['vote_average']) . "</span></p>
                                    <p><span class='size_4 right10 has-text-primary'>Overview: </span><span>" . htmlsafechars($movie['overview']) . '</span></p>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>';
            }

            $div .= '
        </div>';

            $HTMLOUT .= main_div($div);
        } else {
            $HTMLOUT = main_div("<h1 class='has-text-centered'>TMDb may be down, check back later</h1>");
        }

        break;

    case 'top100':
        $title = 'Top 100';
        $HTMLOUT = "
    <h1 class='has-text-centered'>Top 100 Movies</h1>";

        $movies = get_movies_by_vote_average(100);

        if ($movies) {
            $titles = $body = [];
            foreach ($movies as $movie) {
                if (!empty($movie['title']) && !in_array(strtolower($movie['title']), $titles)) {
                    $poster = !empty($movie['poster_path']) ? "https://image.tmdb.org/t/p/w185{$movie['poster_path']}" : $site_config['pic_baseurl'] . 'noposter.png';
                    $backdrop = !empty($movie['backdrop_path']) ? "https://image.tmdb.org/t/p/w1280{$movie['backdrop_path']}" : '';
                    $body[] = [
                        'poster'       => url_proxy($poster, true, 150),
                        'placeholder'  => url_proxy($poster, true, 150, null, 10),
                        'backdrop'     => url_proxy($backdrop, true),
                        'title'        => $movie['title'],
                        'vote_count'   => $movie['vote_count'],
                        'id'           => $movie['id'],
                        'vote_average' => $movie['vote_average'],
                        'popularity'   => $movie['popularity'],
                        'overview'     => $movie['overview'],
                        'release_date' => $movie['release_date'],
                    ];
                    $titles[] = strtolower($movie['title']);
                }
            }

            $div = "
        <div class='level-center'>";
            foreach ($body as $movie) {
                $div .= "
            <div class='padding10 round10 bg-00 margin10'>
                <div class='dt-tooltipper-large has-text-centered' data-tooltip-content='#movie_{$movie['id']}_tooltip'>
                    <img src='{$movie['placeholder']}' data-src='{$movie['poster']}' alt='Poster' class='lazy tooltip-poster'>
                    <div class='has-text-centered top10'>{$movie['title']}</div>
                    <div class='has-text-centered'>{$movie['release_date']}</div>
                    <div class='tooltip_templates'>
                        <div id='movie_{$movie['id']}_tooltip' clss='round10 tooltip-background' style='background-image: url({$movie['backdrop']});'>
                            <div class='is-flex tooltip-torrent bg-09'>
                                <span class='padding10 w-40'>
                                    <img data-src='{$movie['poster']}' alt='Poster' class='lazy tooltip-poster'>
                                </span>
                                <span class='padding10'>
                                    <p><span class='size_4 right10 has-text-primary has-text-bold'>Title: </span><span>" . htmlsafechars($movie['title']) . "</span></p>
                                    <p><span class='size_4 right10 has-text-primary'>Release Date: </span><span>" . htmlsafechars($movie['release_date']) . "</span></p>
                                    <p><span class='size_4 right10 has-text-primary'>Popularity: </span><span>" . htmlsafechars($movie['popularity']) . "</span></p>
                                    <p><span class='size_4 right10 has-text-primary'>Votes: </span><span>" . htmlsafechars($movie['vote_average']) . "</span></p>
                                    <p><span class='size_4 right10 has-text-primary'>Overview: </span><span>" . htmlsafechars($movie['overview']) . '</span></p>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>';
            }

            $div .= '
        </div>';

            $HTMLOUT .= main_div($div);
        } else {
            $HTMLOUT = main_div("<h1 class='has-text-centered'>TMDb may be down, check back later</h1>");
        }
        break;

    case 'upcoming':
        $title = 'Upcoming';
        $HTMLOUT = '';

        $imdbs = get_upcoming();
        if ($imdbs) {
            foreach ($imdbs as $key => $imdb) {
                $body = '';
                $HTMLOUT .= "
        <h1 class='has-text-centered'>Upcoming Movies $key</h1>";

                $body .= "
        <div class='level-center'>";
                foreach ($imdb as $item) {
                    $movie = get_imdb_info_short($item);
                    if (!empty($movie)) {
                        $body .= $movie;
                    }
                }

                $body .= '
        </div>';

                $HTMLOUT .= main_div($body);
            }
        }
}
echo stdhead($title) . wrapper($HTMLOUT) . stdfoot();
