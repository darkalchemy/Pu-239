<?php

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'user_functions.php';
require_once INCL_DIR . 'html_functions.php';
require_once INCL_DIR . 'function_tmdb.php';
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
                'poster' => url_proxy($poster, true, 150),
                'placeholder' => url_proxy($poster, true, 150, null, 10),
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

echo stdhead('TV Shows Today') . wrapper($HTMLOUT) . stdfoot();
