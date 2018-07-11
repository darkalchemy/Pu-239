<?php

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'user_functions.php';
require_once INCL_DIR . 'html_functions.php';
require_once INCL_DIR . 'function_tmdb.php';
check_user_status();
global $CURUSER, $site_config, $fluent, $cache, $session;

$lang = array_merge(load_language('global'), load_language('details'));

$HTMLOUT = "
    <h1 class='has-text-centered'>Top 100 Movies</h1>";

$movies = get_movies_by_vote_average(100);

if ($movies) {
    $titles = $body = [];
    foreach ($movies as $movie) {
        if (!empty($movie['title']) && !in_array(strtolower($movie['title']), $titles)) {
            $poster = !empty($movie['poster_path']) ? "https://image.tmdb.org/t/p/w185{$movie['poster_path']}" : $site_config['pic_baseurl'] . 'noposter.png';
            $backdrop = !empty($movie['backdrop_path']) ? "https://image.tmdb.org/t/p/w500{$movie['backdrop_path']}" : '';
            $body[] = [
                'poster' => url_proxy($poster, true, 150),
                'placeholder' => url_proxy($poster, true, null, null, 20),
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

echo stdhead('Top 100 Movies') . wrapper($HTMLOUT) . stdfoot();
