<?php

declare(strict_types = 1);

use Pu239\Torrent;

global $container, $lang, $site_config, $CURUSER;

$torrent = $container->get(Torrent::class);
$torrents = $torrent->get_latest_scroller();

if (!empty($torrents)) {
    shuffle($torrents);
    $torrents_scroller .= "
    <a id='scroller-hash'></a>
    <div id='scroller' class='box'>
        <div class='bordered'>
            <div id='carousel-container' class='alt_bordered bg-00 carousel-container'>
                <div id='icarousel' class='icarousel'>";

    foreach ($torrents as $last) {
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

        if ($anonymous === 'yes' && ($CURUSER['class'] < UC_STAFF || $owner === $CURUSER['id'])) {
            $uploader = '<span>' . get_anonymous_name() . '</span>';
        } else {
            $username = !empty($username) ? htmlsafechars($username) : 'unknown';
            $uploader = "<span class='" . get_user_class_name((int) $class, true) . "'>" . $username . '</span>';
        }
        $scroll_poster = $poster;
        $poster = "<img src='" . url_proxy($poster, true, 250) . "' class='tooltip-poster'>";
        $torrents_scroller .= "
                    <div class='slide'>";
        $torrname = "<img src='" . url_proxy($scroll_poster, true, null, 300) . "' alt='{$name}' style='width: auto; height: 300px; max-height: 300px;'>";
        $block_id = "scroll_id_{$id}";
        $torrents_scroller .= torrent_tooltip($torrname, $id, $block_id, $name, $poster, $uploader, $added, $size, $seeders, $leechers, $imdb_id, $rating, $year, $subtitles, $audios, $genre);
        $torrents_scroller .= '
                    </div>';
    }

    $torrents_scroller .= '
                </div>
            </div>
        </div>
    </div>';
}
