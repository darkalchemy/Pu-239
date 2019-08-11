<?php

declare(strict_types = 1);

use Pu239\Torrent;

global $container, $lang, $site_config, $CURUSER;

$torrent = $container->get(Torrent::class);
$sliding_torrents = $torrent->get_latest_slider();
if (!empty($sliding_torrents)) {
    shuffle($sliding_torrents);
    $slider .= "
    <a id='slider-hash'></a>
    <div id='slider' class='box'>
        <div class='bordered'>
            <div class='alt_bordered bg-00 automatic-slider flexslider is-paddingless'>
                <ul class='slides'>";
    $i = 0;
    foreach ($sliding_torrents as $slider_torrent) {
        $imdb_id = $last['imdb_id'];
        $subtitles = $last['subtitles'];
        $audios = $last['audios'];
        $year = $last['year'];
        $rating = $last['rating'];
        $owner = $last['owner'];
        $anonymous = $last['anonymous'];
        $name = $last['name'];
        $poster = $last['poster'];
        $seeders = $last['seeders'];
        $leechers = $last['leechers'];
        $size = $last['size'];
        $added = $last['added'];
        $class = $last['class'];
        $username = $last['username'];
        $id = $last['id'];
        $cat = $last['cat'];
        $image = $last['image'];
        $times_completed = $last['times_completed'];
        $genre = $last['genre'];
        $i = $site_config['latest']['slider_limit'];
        if ($anonymous === '1' && ($CURUSER['class'] < UC_STAFF || $owner === $CURUSER['id'])) {
            $uploader = '<span>' . get_anonymous_name() . '</span>';
        } else {
            $username = !empty($username) ? htmlsafechars($username) : 'unknown';
            $uploader = "<span class='" . get_user_class_name((int) $class, true) . "'>" . $username . '</span>';
        }

        $src = $i++ <= 1 ? "src='" . url_proxy($banner, true, 1000, 185) . "' alt='Poster for {$last['name']}' class='noshow round10'" : "data-src='" . url_proxy($banner, true, 1000, 185) . "' alt='Poster for {$last['name']}' class='noshow lazy round10'";
        $poster = "<img src='" . url_proxy($poster, true, 250) . "' alt='Poster for {$last['name']}' class='tooltip-poster'>";

        $slider .= '
                    <li>';
        $torrname = "<img $src>";
        $block_id = "slider_id_{$id}";
        $slider .= torrent_tooltip($torrname, $id, $block_id, $name, $poster, $uploader, $added, $size, $seeders, $leechers, $imdb_id, $rating, $year, $subtitles, $audios, $genre);
        $slider .= '
                    </li>';
    }

    $slider .= '
                </ul>
            </div>
        </div>
    </div>';
}
