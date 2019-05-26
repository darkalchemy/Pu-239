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
        $banner = $imdb_id = $subtitles = $year = $rating = $owner = $anonymous = $name = $poster = $seeders = $leechers = $size = $added = $class = $username = $id = $cat = $image = $times_completed = $genre = '';
        extract($slider_torrent);
        $i = $site_config['latest']['slider_limit'];

        if ($anonymous === 'yes' && ($CURUSER['class'] < UC_STAFF || $owner === $CURUSER['id'])) {
            $uploader = '<span>' . get_anonymous_name() . '</span>';
        } else {
            $uploader = "<span class='" . get_user_class_name($class, true) . "'>" . htmlsafechars($username) . '</span>';
        }

        $src = $i++ <= 1 ? "src='" . url_proxy($banner, true, 1000, 185) . "' class='noshow round10'" : "data-src='" . url_proxy($banner, true, 1000, 185) . "' class='noshow lazy round10'";
        $poster = "<img src='" . url_proxy($poster, true, 250) . "' class='tooltip-poster'>";

        $slider .= '
                    <li>';
        $torrname = "<img $src>";
        $block_id = "slider_id_{$id}";
        $slider .= torrent_tooltip($torrname, $id, $block_id, $name, $poster, $uploader, $added, $size, $seeders, $leechers, $imdb_id, $rating, $year, $subtitles, $genre);
        $slider .= '
                    </li>';
    }

    $slider .= '
                </ul>
            </div>
        </div>
    </div>';
}
