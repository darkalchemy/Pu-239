<?php

declare(strict_types = 1);

require_once __DIR__ . '/../include/bittorrent.php';
require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_bbcode.php';
require_once INCL_DIR . 'function_html.php';
require_once INCL_DIR . 'function_imdb.php';
require_once INCL_DIR . 'function_tmdb.php';
require_once INCL_DIR . 'function_tvmaze.php';
require_once INCL_DIR . 'function_bluray.php';
require_once INCL_DIR . 'function_fanart.php';
$user = check_user_status();
$lang = load_language('global');
$image = placeholder_image();
global $site_config;

$lists = [
    'upcoming',
    'top100',
    'theaters',
    'tv',
    'tvmaze',
    'bluray',
];
$list = $_GET['list'];
if (empty($list) || !in_array($list, $lists)) {
    $list = 'upcoming';
}

switch ($list) {
    case 'bluray':
        $title = 'Bluray Releases';
        $pubs = get_bluray_info();
        if (is_array($pubs)) {
            $div = "
        <div class='masonry padding20'>";
            foreach ($pubs as $data) {
                $div .= generate_html($data);
            }
            $div .= '
        </div>';
            $div = main_div($div);
        } else {
            $div = main_div("<p class='has-text-centered'>Blu-ray.com may be down, check back later</p>", '', 'padding20');
        }
        $HTMLOUT = "
        <h1 class='has-text-centered'>Blu-ray Releases</h1>" . $div;

        break;

    case 'tvmaze':
        $title = 'TV Schedule';
        $tvmaze_data = get_schedule();
        if (is_array($tvmaze_data)) {
            $today = date('Y-m-d');
            $shows = [];
            foreach ($tvmaze_data as $listing) {
                if (!empty($listing['airstamp']) && !empty($listing['airdate']) && $listing['airdate'] === $today && $listing['_embedded']['show']['language'] === 'English') {
                    $shows[] = $listing;
                }
            }

            if (is_array($shows)) {
                usort($shows, 'timeSort');
                $titles = $body = [];
                foreach ($shows as $tv) {
                    if (!empty($tv['name']) && !in_array(strtolower($tv['name']), $titles)) {
                        $poster = !empty($tv['image']['original']) ? $tv['image']['original'] : (!empty($tv['_embedded']['show']['image']['original']) ? $tv['_embedded']['show']['image']['original'] : $site_config['paths']['images_baseurl'] . 'noposter.png');
                        $airtime = strtotime($tv['airstamp']);
                        $use_12_hour = !empty($user['use_12_hour']) ? $user['use_12_hour'] : $site_config['site']['use_12_hour'];
                        $body[] = [
                            'poster' => url_proxy($poster, true, 250),
                            'placeholder' => url_proxy($poster, true, 250, null, 20),
                            'title' => $tv['_embedded']['show']['name'],
                            'ep_title' => $tv['name'],
                            'season' => $tv['season'],
                            'episode' => $tv['number'],
                            'runtime' => !empty($tv['runtime']) ? "{$tv['runtime']} minutes" : '',
                            'type' => $tv['_embedded']['show']['type'],
                            'airtime' => !empty($tv['airtime']) ? get_date((int) $airtime, 'WITHOUT_SEC', 0, 1) : '',
                            'id' => $tv['_embedded']['show']['id'],
                            'overview' => str_replace([
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
        <h1 class='has-text-centered'>TVMaze TV Today</h1>
        <div class='masonry padding20'>";
                foreach ($body as $data) {
                    $div .= generate_html($data);
                }
                $div .= '
        </div>';

                $HTMLOUT = main_div($div);
            }
        } else {
            $HTMLOUT = "
        <h1 class='has-text-centered'>TVMaze TV Today</h1>" . main_div("<p class='has-text-centered'>TVMaze may be down, check back later</p>", '', 'padding20');
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

        if (is_array($tvs)) {
            $titles = $body = [];
            foreach ($tvs as $tv) {
                if (!empty($tv['name']) && !in_array(strtolower($tv['name']), $titles)) {
                    $poster = !empty($tv['poster_path']) ? "https://image.tmdb.org/t/p/w185{$tv['poster_path']}" : $site_config['paths']['images_baseurl'] . 'noposter.png';
                    $backdrop = !empty($tv['backdrop_path']) ? "https://image.tmdb.org/t/p/w1280{$tv['backdrop_path']}" : '';

                    $body[] = [
                        'poster' => url_proxy($poster, true, 250),
                        'placeholder' => url_proxy($poster, true, 250, null, 20),
                        'backdrop' => url_proxy($backdrop, true),
                        'title' => $tv['name'],
                        'vote_count' => $tv['vote_count'],
                        'id' => $tv['id'],
                        'vote_average' => $tv['vote_average'],
                        'popularity' => $tv['popularity'],
                        'overview' => $tv['overview'],
                    ];
                    $titles[] = strtolower($tv['name']);
                }
            }

            $div = "
        <div class='masonry padding20'>";
            foreach ($body as $data) {
                $div .= generate_html($data);
            }
            $div .= '
        </div>';

            $HTMLOUT .= main_div($div);
        } else {
            $HTMLOUT = "
        <h1 class='has-text-centered'>TMBb TV Airing By Date</h1>" . main_div("<p class='has-text-centered'>TMDb may be down, check back later</p>", '', 'padding20');
        }

        break;

    case 'theaters':
        $title = 'In Theaters';
        $HTMLOUT = "
    <h1 class='has-text-centered'>TMDb In Theaters</h1>";

        $movies = get_movies_in_theaters();

        if (is_array($movies)) {
            $titles = $body = [];
            foreach ($movies as $movie) {
                if (!empty($movie['title']) && !in_array(strtolower($movie['title']), $titles)) {
                    $poster = !empty($movie['poster_path']) ? "https://image.tmdb.org/t/p/w185{$movie['poster_path']}" : $site_config['paths']['images_baseurl'] . 'noposter.png';
                    $backdrop = !empty($movie['backdrop_path']) ? "https://image.tmdb.org/t/p/w1280{$movie['backdrop_path']}" : '';
                    $body[] = [
                        'poster' => url_proxy($poster, true, 250),
                        'placeholder' => url_proxy($poster, true, 250, null, 20),
                        'backdrop' => url_proxy($backdrop, true),
                        'title' => $movie['title'],
                        'vote_count' => $movie['vote_count'],
                        'id' => $movie['id'],
                        'vote_average' => $movie['vote_average'],
                        'popularity' => $movie['popularity'],
                        'overview' => $movie['overview'],
                        'release_date' => $movie['release_date'],
                    ];
                    $titles[] = strtolower($movie['title']);
                }
            }

            $div = "
        <div class='masonry padding20'>";
            foreach ($body as $data) {
                $div .= generate_html($data);
            }
            $div .= '
        </div>';

            $HTMLOUT .= main_div($div);
        } else {
            $HTMLOUT = "
        <h1 class='has-text-centered'>TMBb In Theaters</h1>" . main_div("<p class='has-text-centered'>TMDb may be down, check back later</p>", '', 'padding20');
        }

        break;

    case 'top100':
        $title = 'Top 100';
        $HTMLOUT = "
    <h1 class='has-text-centered'>Top 100 Movies</h1>";

        $movies = get_movies_by_vote_average(100);

        if (is_array($movies)) {
            $titles = $body = [];
            foreach ($movies as $movie) {
                if (!empty($movie['title']) && !in_array(strtolower($movie['title']), $titles)) {
                    $poster = !empty($movie['poster_path']) ? "https://image.tmdb.org/t/p/w185{$movie['poster_path']}" : $site_config['paths']['images_baseurl'] . 'noposter.png';
                    $backdrop = !empty($movie['backdrop_path']) ? "https://image.tmdb.org/t/p/w1280{$movie['backdrop_path']}" : '';
                    $body[] = [
                        'poster' => url_proxy($poster, true, 250),
                        'placeholder' => url_proxy($poster, true, 250, null, 20),
                        'backdrop' => url_proxy($backdrop, true),
                        'title' => $movie['title'],
                        'vote_count' => $movie['vote_count'],
                        'id' => $movie['id'],
                        'vote_average' => $movie['vote_average'],
                        'popularity' => $movie['popularity'],
                        'overview' => $movie['overview'],
                        'release_date' => $movie['release_date'],
                    ];
                    $titles[] = strtolower($movie['title']);
                }
            }

            $div = "
        <div class='masonry padding20'>";
            foreach ($body as $data) {
                $div .= generate_html($data);
            }
            $div .= '
        </div>';

            $HTMLOUT .= main_div($div);
        } else {
            $HTMLOUT = "
        <h1 class='has-text-centered'>TMBb Top 100 Movies</h1>" . main_div("<p class='has-text-centered'>TMDb may be down, check back later</p>", '', 'padding20');
        }
        break;

    case 'upcoming':
        $title = 'Upcoming';
        $HTMLOUT = '';

        $imdbs = get_upcoming();
        if (is_array($imdbs)) {
            foreach ($imdbs as $key => $imdb) {
                $body = '';
                $HTMLOUT .= "
        <h1 class='has-text-centered'>IMDb Upcoming Movies $key</h1>";

                $body .= "
        <div class='masonry padding20'>";
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
        } else {
            $HTMLOUT = "
        <h1 class='has-text-centered'>IMBb Upcoming Movies</h1>" . main_div("<p class='has-text-centered'>IMDb.com may be down, check back later</p>", '', 'padding20');
        }
}
echo stdhead($title) . wrapper($HTMLOUT) . stdfoot();

/**
 * @param array $data
 *
 * @return string
 */
function generate_html(array $data)
{
    $html = "
     <div class='masonry-item-clean padding10 bg-04 round10'>
        <div class='dt-tooltipper-large has-text-centered' data-tooltip-content='#movie_{$data['id']}_tooltip'>
            <img src='{$data['placeholder']}' data-src='{$data['poster']}' alt='Poster' class='lazy tooltip-poster'>
            <div class='has-text-centered top10'>{$data['title']}</div>";

    if (!empty($data['airtime'])) {
        $html .= "
                    <div class='has-text-centered top10'>{$data['airtime']}</div>";
    }
    if (!empty($data['release_date'])) {
        $html .= "
            <div class='has-text-centered'>{$data['release_date']}</div>";
    }
    $html .= "
            <div class='tooltip_templates'>
                <div id='movie_{$data['id']}_tooltip' class='round10 tooltip-background'" . (!empty($data['backdrop']) ? " style='background-image: url({$data['backdrop']});'" : '') . ">
                    <div class='columns is-marginless is-paddingless'>
                        <div class='column padding10 is-4'>
                            <span>
                                <img src='{$data['placeholder']}' data-src='{$data['poster']}' alt='Poster' class='lazy tooltip-poster'>
                            </span>
                        </div>
                        <div class='column padding10 is-8'>
                            <div class='padding20 is-8 bg-09 round10'>
                                <div class='columns is-multiline'>";

    if (!empty($data['title'])) {
        $html .= "
                                    <div class='column padding5 is-4'>
                                        <span class='size_4 right10 has-text-primary has-text-wight-bold'>Title: </span>
                                    </div>
                                    <div class='column padding5 is-8'>
                                        <span class='size_4'>" . htmlsafechars($data['title']) . '</span>
                                    </div>';
    }
    if (!empty($data['ep_title'])) {
        $html .= "
                                    <div class='column padding5 is-4'>
                                        <span class='size_4 right10 has-text-primary has-text-wight-bold'>Episode Title: </span>
                                    </div>
                                    <div class='column padding5 is-8'>
                                        <span class='size_4'>" . htmlsafechars($data['ep_title']) . '</span>
                                    </div>';
    }
    if (!empty($data['season'])) {
        $html .= "
                                    <div class='column padding5 is-4'>
                                        <span class='size_4 right10 has-text-primary has-text-wight-bold'>Season: </span>
                                    </div>
                                    <div class='column padding5 is-8'>
                                        <span class='size_4'>" . (int) $data['season'] . '</span>
                                    </div>';
    }
    if (!empty($data['episode'])) {
        $html .= "
                                    <div class='column padding5 is-4'>
                                        <span class='size_4 right10 has-text-primary has-text-wight-bold'>Episode: </span>
                                    </div>
                                    <div class='column padding5 is-8'>
                                        <span class='size_4'>" . (int) $data['episode'] . '</span>
                                    </div>';
    }
    if (!empty($data['runtime'])) {
        $html .= "
                                    <div class='column padding5 is-4'>
                                        <span class='size_4 right10 has-text-primary has-text-wight-bold'>Runtime: </span>
                                    </div>
                                    <div class='column padding5 is-8'>
                                        <span class='size_4'>" . htmlsafechars($data['runtime']) . '</span>
                                    </div>';
    }
    if (!empty($data['type'])) {
        $html .= "
                                    <div class='column padding5 is-4'>
                                        <span class='size_4 right10 has-text-primary has-text-wight-bold'>Type: </span>
                                    </div>
                                    <div class='column padding5 is-8'>
                                        <span class='size_4'>" . htmlsafechars($data['type']) . '</span>
                                    </div>';
    }
    if (!empty($data['release_date'])) {
        $html .= "
                                    <div class='column padding5 is-4'>
                                        <span class='size_4 right10 has-text-primary has-text-wight-bold'>Release Date: </span>
                                    </div>
                                    <div class='column padding5 is-8'>
                                        <span class='size_4'>" . htmlsafechars($data['release_date']) . '</span>
                                    </div>';
    }
    if (!empty($data['popularity'])) {
        $html .= "
                                    <div class='column padding5 is-4'>
                                        <span class='size_4 right10 has-text-primary has-text-wight-bold'>Popularity: </span>
                                    </div>
                                    <div class='column padding5 is-8'>
                                        <span class='size_4'>" . (int) $data['popularity'] . '</span>
                                    </div>';
    }
    if (!empty($data['vote_average'])) {
        $html .= "
                                    <div class='column padding5 is-4'>
                                        <span class='size_4 right10 has-text-primary has-text-wight-bold'>Votes: </span>
                                    </div>
                                    <div class='column padding5 is-8'>
                                        <span class='size_4'>" . (int) $data['vote_average'] . '</span>
                                    </div>';
    }
    if (!empty($data['overview'])) {
        $html .= "
                                    <div class='column padding5 is-4'>
                                        <span class='size_4 right10 has-text-primary has-text-wight-bold'>Overview: </span>
                                    </div>
                                    <div class='column padding5 is-8'>
                                        <span class='size_4'>" . htmlsafechars($data['overview']) . '</span>
                                    </div>';
    }
    $html .= '
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div> ';

    return $html;
}
