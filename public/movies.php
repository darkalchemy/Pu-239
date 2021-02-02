<?php

declare(strict_types = 1);

use Pu239\Cache;

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
$image = placeholder_image();
global $site_config, $container;
$cache = $container->get(Cache::class);

$lists = [
    'upcoming',
    'top100',
    'theaters',
    'tv',
    'tvmaze',
    'bluray',
    'imdb_top100',
    'imdb_theaters',
];
$list = 'upcoming';
$title = _('Poster Views');
if (!empty($_GET['list']) && in_array($_GET['list'], $lists)) {
    $list = $_GET['list'];
}

switch ($list) {
    case 'bluray':
        $title = _('Bluray Releases');
        $pubs = $cache->get('bluray_pubs_');
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
            $div = main_div("<p class='has-text-centered'>" . _('Blu-ray.com may be down or caching queue is incomplete, check back later') . '</p>', '', 'padding20');
        }
        $HTMLOUT = "
        <h1 class='has-text-centered'>$title</h1>" . $div;

        break;

    case 'tvmaze':
        $title = _('TV Schedule');
        if (($data = $cache->get('tvmaze_schedule_')) !== false) {
            $json = bzdecompress($data);
            $tvmaze_data = json_decode($json, true);
        }
        if (is_array($tvmaze_data)) {
            $today = date('Y-m-d');
            $shows = [];
            foreach ($tvmaze_data as $listing) {
                if (
                    !empty($listing['airstamp']) &&
                    !empty($listing['airdate']) &&
                    $listing['airdate'] === $today &&
                    $listing['_embedded']['show']['language'] === 'English'
                ) {
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
                            'airtime' => !empty($tv['airtime']) ? get_date((int) $airtime, 'WITHOUT_SEC', 1, 0) : '',
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
        <h1 class='has-text-centered'>" . _('TVMaze TV Today') . "</h1>
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
        <h1 class='has-text-centered'>" . _('TVMaze TV Today') . '</h1>' . main_div("<p class='has-text-centered'>" . _('TVMaze may be down or caching queue is incomplete, check back later') . '</p>', '', 'padding20');
        }

        break;

    case 'tv':
        $title = _('TV Schedule');
        $base = $today = date('Y-m-d');
        if (!empty($_GET['date'])) {
            $today = $_GET['date'];
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
    <h1 class='has-text-centered'>" . _('TV Airing By Date') . "</h1>
    <div class='level-center top20'>
        <a href='{$_SERVER['PHP_SELF']}?list=tv&amp;date={$yesterday}' class='tooltipper' title='{$yesterday}'>{$yesterday}</a>
        <a href='{$_SERVER['PHP_SELF']}?list=tv&amp;date={$base}' class='tooltipper' title='GoTo {$base}'><h2>{$display}</h2></a>
        <a href='{$_SERVER['PHP_SELF']}?list=tv&amp;date={$tomorrow}' class='tooltipper' title='{$tomorrow}'>{$tomorrow}</a>
    </div>";
        $tvs = $cache->get('tmdb_tv_' . $today);
        if (is_array($tvs)) {
            $titles = $body = [];
            foreach ($tvs as $tv) {
                if (!empty($tv['name']) && !in_array(strtolower($tv['name']), $titles)) {
                    $imdb_id = get_imdbid($tv['id']);
                    $poster = !empty($tv['poster_path']) ? "https://image.tmdb.org/t/p/original{$tv['poster_path']}" : $site_config['paths']['images_baseurl'] . 'noposter.png';
                    $backdrop = !empty($tv['backdrop_path']) ? "https://image.tmdb.org/t/p/original{$tv['backdrop_path']}" : '';

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
        <h1 class='has-text-centered'>" . _('TMBb TV Airing By Date') . '</h1>' . main_div("<p class='has-text-centered'>" . _('TMDb may be down or caching queue is incomplete, check back later') . '</p>', '', 'padding20');
        }

        break;

    case 'theaters':
        $title = _('TMDb In Theaters');
        $HTMLOUT = "
    <h1 class='has-text-centered'>$title</h1>";
        $movies = $cache->get('tmdb_movies_in_theaters_');
        if (is_array($movies)) {
            $body = "
        <div class='masonry padding20'>";
            foreach ($movies as $movie) {
                $imdb_id = get_imdbid($movie['id']);
                $movie = get_imdb_info_short($imdb_id);
                if (!empty($movie)) {
                    $body .= $movie;
                }
            }
            $body .= '
        </div>';

            $HTMLOUT .= main_div($body);
        } else {
            $HTMLOUT = "
        <h1 class='has-text-centered'>$title</h1>" . main_div("<p class='has-text-centered'>" . _('TMDb may be down or caching queue is incomplete, check back later') . '</p>', '', 'padding20');
        }

        break;

    case 'imdb_theaters':
        $title = _('IMDb In Theaters');
        $HTMLOUT = "
    <h1 class='has-text-centered'>{$title}</h1>";
        $movies = $cache->get('imdb_in_theaters_display_');
        if (is_array($movies)) {
            $body = "
        <div class='masonry padding20'>";
            foreach ($movies as $imdb_id) {
                $movie = get_imdb_info_short($imdb_id);
                if (!empty($movie)) {
                    $body .= $movie;
                }
            }
            $body .= '
        </div>';

            $HTMLOUT .= main_div($body);
        } else {
            $HTMLOUT = "
        <h1 class='has-text-centered'>$title</h1>" . main_div("<p class='has-text-centered'>" . _('IMDb may be down or caching queue is incomplete, check back later') . '</p>', '', 'padding20');
        }

        break;

    case 'imdb_top100':
        $title = _('IMDb Top 100 Movies');
        $HTMLOUT = "
    <h1 class='has-text-centered'>{$title}</h1>";
        $movies = $cache->get('imdb_top_movies_100');
        if (is_array($movies)) {
            $body = "
        <div class='masonry padding20'>";
            foreach ($movies as $imdb_id) {
                $movie = get_imdb_info_short($imdb_id);
                if (!empty($movie)) {
                    $body .= $movie;
                }
            }
            $body .= '
        </div>';

            $HTMLOUT .= main_div($body);
        } else {
            $HTMLOUT = "
        <h1 class='has-text-centered'>$title</h1>" . main_div("<p class='has-text-centered'>" . _('IMDb may be down or caching queue is incomplete, check back later') . '</p>', '', 'padding20');
        }

        break;
    case 'top100':
        $title = _('TMDb Top 100 Movies');
        $HTMLOUT = "
    <h1 class='has-text-centered'>{$title}</h1>";
        $movies = $cache->get('tmdb_movies_vote_average_100');
        if (is_array($movies)) {
            $body = "
        <div class='masonry padding20'>";
            foreach ($movies as $movie) {
                $imdb_id = get_imdbid($movie['id']);
                if (!empty($imdb_id)) {
                    $movie = get_imdb_info_short($imdb_id);
                    if (!empty($movie)) {
                        $body .= $movie;
                    }
                }
            }
            $body .= '
        </div>';

            $HTMLOUT .= main_div($body);
        } else {
            $HTMLOUT = "
        <h1 class='has-text-centered'>$title</h1>" . main_div("<p class='has-text-centered'>" . _('TMDb may be down or caching queue is incomplete, check back later') . '</p>', '', 'padding20');
        }

        break;

    case 'upcoming':
        $title = _('IMDb Upcoming Movies');
        $HTMLOUT = '';
        $imdbs = $cache->get('imdb_upcoming_movies_');
        if (is_array($imdbs)) {
            foreach ($imdbs as $key => $imdb) {
                $body = '';
                $HTMLOUT .= "
        <h1 class='has-text-centered'>$title $key</h1>";

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
        <h1 class='has-text-centered'>$title</h1>" . main_div("<p class='has-text-centered'>" . _('IMDb may be down or caching queue is incomplete, check back later') . '</p>', '', 'padding20');
        }
}

$breadcrumbs = [
    "<a href='{$site_config['paths']['baseurl']}/browse.php'>" . _('Browse Torrents') . '</a>',
    "<a href='{$_SERVER['PHP_SELF']}'>$title</a>",
];
echo stdhead($title, [], 'page-wrapper', $breadcrumbs) . wrapper($HTMLOUT) . stdfoot();

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
                <div id='movie_{$data['id']}_tooltip' class='round10 tooltip-background' " . (!empty($data['backdrop']) ? "style='background-image: url({$data['backdrop']});'" : '') . ">
                    <div class='columns is-marginless is-paddingless'>
                        <div class='column padding10 is-4'>
                            <span>
                                <img src='{$data['placeholder']}' data-src='{$data['poster']}' alt='Poster' class='lazy tooltip-poster'>
                            </span>
                        </div>
                        <div class='column padding10 is-8'>
                            <div class='padding20 is-8 bg-09 round10 h-100'>
                                <div class='columns is-multiline'>";

    if (!empty($data['title'])) {
        $html .= "
                                    <div class='column padding5 is-4'>
                                        <span class='size_4 right10 has-text-primary has-text-wight-bold'>" . _('Title') . ": </span>
                                    </div>
                                    <div class='column padding5 is-8'>
                                        <span class='size_4'>" . htmlsafechars($data['title']) . '</span>
                                    </div>';
    }
    if (!empty($data['ep_title'])) {
        $html .= "
                                    <div class='column padding5 is-4'>
                                        <span class='size_4 right10 has-text-primary has-text-wight-bold'>" . _('Episode Title') . ": </span>
                                    </div>
                                    <div class='column padding5 is-8'>
                                        <span class='size_4'>" . htmlsafechars($data['ep_title']) . '</span>
                                    </div>';
    }
    if (!empty($data['season'])) {
        $html .= "
                                    <div class='column padding5 is-4'>
                                        <span class='size_4 right10 has-text-primary has-text-wight-bold'>" . _('Season') . ": </span>
                                    </div>
                                    <div class='column padding5 is-8'>
                                        <span class='size_4'>" . (int) $data['season'] . '</span>
                                    </div>';
    }
    if (!empty($data['episode'])) {
        $html .= "
                                    <div class='column padding5 is-4'>
                                        <span class='size_4 right10 has-text-primary has-text-wight-bold'>" . _('Episode') . ": </span>
                                    </div>
                                    <div class='column padding5 is-8'>
                                        <span class='size_4'>" . (int) $data['episode'] . '</span>
                                    </div>';
    }
    if (!empty($data['runtime'])) {
        $html .= "
                                    <div class='column padding5 is-4'>
                                        <span class='size_4 right10 has-text-primary has-text-wight-bold'>" . _('Runtime') . ": </span>
                                    </div>
                                    <div class='column padding5 is-8'>
                                        <span class='size_4'>" . htmlsafechars($data['runtime']) . '</span>
                                    </div>';
    }
    if (!empty($data['type'])) {
        $html .= "
                                    <div class='column padding5 is-4'>
                                        <span class='size_4 right10 has-text-primary has-text-wight-bold'>" . _('Type') . ": </span>
                                    </div>
                                    <div class='column padding5 is-8'>
                                        <span class='size_4'>" . htmlsafechars($data['type']) . '</span>
                                    </div>';
    }
    if (!empty($data['release_date'])) {
        $html .= "
                                    <div class='column padding5 is-4'>
                                        <span class='size_4 right10 has-text-primary has-text-wight-bold'>" . _('Release Date') . ": </span>
                                    </div>
                                    <div class='column padding5 is-8'>
                                        <span class='size_4'>" . htmlsafechars($data['release_date']) . '</span>
                                    </div>';
    }
    if (!empty($data['popularity'])) {
        $html .= "
                                    <div class='column padding5 is-4'>
                                        <span class='size_4 right10 has-text-primary has-text-wight-bold'>" . _('Popularity') . ": </span>
                                    </div>
                                    <div class='column padding5 is-8'>
                                        <span class='size_4'>" . (int) $data['popularity'] . '</span>
                                    </div>';
    }
    if (!empty($data['vote_average'])) {
        $html .= "
                                    <div class='column padding5 is-4'>
                                        <span class='size_4 right10 has-text-primary has-text-wight-bold'>" . _('Votes') . ": </span>
                                    </div>
                                    <div class='column padding5 is-8'>
                                        <span class='size_4'>" . (int) $data['vote_average'] . '</span>
                                    </div>';
    }
    if (!empty($data['overview'])) {
        $html .= "
                                    <div class='column padding5 is-4'>
                                        <span class='size_4 right10 has-text-primary has-text-wight-bold'>" . _('Overview') . ": </span>
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
