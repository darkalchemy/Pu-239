<?php

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'user_functions.php';
require_once INCL_DIR . 'html_functions.php';
require_once INCL_DIR . 'bbcode_functions.php';
require_once INCL_DIR . 'function_tvmaze.php';
check_user_status();
global $CURUSER, $site_config, $fluent, $cache, $session;

$lang = array_merge(load_language('global'), load_language('index'), load_language('details'));

$base = $today = date('Y-m-d');
if (!empty($_GET['date'])) {
    $today = $_GET['date'];
}
$date = new DateTime($today);
$yesterday = $date->modify('-1 day')->format('Y-m-d');
$date = new DateTime($today);
$tomorrow = $date->modify('+1 day')->format('Y-m-d');
$date = new DateTime($today);
$display = $date->format('l Y-m-d');
$timestamp = strtotime($display);

$HTMLOUT = "
    <h1 class='has-text-centered'>TV Airing By Date</h1>
    <div class='level-center top20'>";

if ($yesterday >= $base) {
    $HTMLOUT .= "
        <a href='{$_SERVER['PHP_SELF']}?date={$yesterday}' class='tooltipper' title='{$yesterday}'>{$yesterday}</a>";
}
$HTMLOUT .= "
        <a href='{$_SERVER['PHP_SELF']}?date={$base}' class='tooltipper' title='GoTo {$base}'><h3>{$display}</h3></a>
        <a href='{$_SERVER['PHP_SELF']}?date={$tomorrow}' class='tooltipper' title='{$tomorrow}'>{$tomorrow}</a>
    </div>";

$tvmaze_data = get_schedule();

if ($tvmaze_data) {
    $base = $today = date('Y-m-d');
    if (!empty($_GET['date'])) {
        $today = $_GET['date'];
    }
    $date = new DateTime($today);
    $yesterday = $date->modify('-1 day')->format('Y-m-d');
    $date = new DateTime($today);
    $tomorrow = $date->modify('+1 day')->format('Y-m-d');
    $date = new DateTime($today);
    $display = $date->format('l Y-m-d');
    $shows = [];
    foreach ($tvmaze_data as $listing) {
        if ($listing['airdate'] === $today && $listing['_embedded']['show']['language'] === 'English') {
            $shows[] = $listing;
        }
    }

    if (!empty($shows)) {
        usort($shows, 'timeSort');
        $titles = $body = [];
        foreach ($shows as $tv) {
            if (!empty($tv['name']) && !in_array(strtolower($tv['name']), $titles)) {
                $poster = !empty($tv['image']['original']) ? $tv['image']['original'] : !empty($tv['_embedded']['show']['image']['original']) ? $tv['_embedded']['show']['image']['original'] : $site_config['pic_baseurl'] . 'noposter.png';
                $airtime = !empty($tv['airtime']) ? explode(':', $tv['airtime']) : '';
                if (!empty($airtime)) {
                    $airtime = $timestamp + $airtime[0] * 3600 + $airtime[1] * 60;
                }
                $body[] = [
                    'poster' => url_proxy($poster, true, 150),
                    'placeholder' => url_proxy($poster, true, null, null, 20),
                    'title' => $tv['_embedded']['show']['name'],
                    'ep_title' => $tv['name'],
                    'season' => $tv['season'],
                    'episode' => $tv['number'],
                    'runtime' => !empty($tv['runtime']) ? "{$tv['runtime']} minutes" : '',
                    'type' => $tv['_embedded']['show']['type'],
                    'airtime' => !empty($tv['airtime']) ? $site_config['12_hour'] ? time24to12($airtime) : get_date($airtime, 'WITHOUT_SEC') : '',
                    'id' => $tv['_embedded']['show']['id'],
                    'overview' => str_replace(['<p>', '</p>', '<b>', '</b>', '<i>', '</i>'], '', $tv['_embedded']['show']['summary']),
                ];
                $titles[] = strtolower($tv['_embedded']['show']['name']);
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

        $HTMLOUT .= main_div($div);
    }
} else {
    $HTMLOUT = main_div("<h1 class='has-text-centered'>TVMaze may be down, check back later</h1>");
}

echo stdhead('TV Shows Today') . wrapper($HTMLOUT) . stdfoot();
