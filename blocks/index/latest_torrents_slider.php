<?php

global $site_config, $lang, $fluent, $CURUSER, $cache;

$slider_torrents = $cache->get('slider_torrents_');
if ($slider_torrents === false || is_null($slider_torrents)) {
    $slider_torrents = $fluent->from('torrents')
        ->select(null)
        ->select('torrents.id')
        ->select('torrents.added')
        ->select('torrents.seeders')
        ->select('torrents.leechers')
        ->select('torrents.name')
        ->select('torrents.size')
        ->select('torrents.poster')
        ->select('torrents.anonymous')
        ->select('torrents.owner')
        ->select('torrents.imdb_id')
        ->select('torrents.times_completed')
        ->select('users.username')
        ->select('users.class')
        ->where('imdb_id IS NOT NULL')
        ->leftJoin('users ON torrents.owner = users.id')
        ->limit(100)
        ->orderBy('torrents.added DESC')
        ->fetchAll();

    $cache->set('slider_torrents_', $slider_torrents, $site_config['expires']['slider_torrents']);
}

foreach ($slider_torrents as $torrent) {
    if (empty($torrent['poster']) && !empty($torrent['imdb_id'])) {
        $images = $cache->get('posters_' . $torrent['imdb_id']);
        if ($images === false || is_null($images)) {
            $images = $fluent->from('images')
                ->select(null)
                ->select('url')
                ->where('type = "poster"')
                ->where('imdb_id = ?', $torrent['imdb_id'])
                ->fetchAll();

            $cache->set('posters_' . $torrent['imdb_id'], $images, 86400);
        }

        if (!empty($images)) {
            shuffle($images);
            $torrent['poster'] = $images[0]['url'];
        } else {
            $torrent['poster'] = $site_config['pic_baseurl'] . 'noposter.png';
        }
    }
    if (!empty($torrent['imdb_id'])) {
        $images = $cache->get('banners_' . $torrent['imdb_id']);
        if ($images === false || is_null($images)) {
            $images = $fluent->from('images')
                ->select(null)
                ->select('url')
                ->where('type = "banner"')
                ->where('imdb_id = ?', $torrent['imdb_id'])
                ->fetchAll();

            $cache->set('banners_' . $torrent['imdb_id'], $images, 86400);
        }
        if (!empty($images)) {
            shuffle($images);
            $torrent['banner'] = $images[0]['url'];
        }

        if (!empty($torrent['banner'])) {
            $sliding_torrents[] = $torrent;
        }
    }

    if (!empty($sliding_torrents) && count($sliding_torrents) >= $site_config['latest_torrents_limit_slider']) {
        break;
    }
}

if (!empty($sliding_torrents)) {
    $slider .= "
    <a id='slider-hash'></a>
    <div id='slider' class='box'>
        <div class='bordered'>
            <div class='alt_bordered bg-00 automatic-slider flexslider is-paddingless'>
                <ul class='slides'>";
    $i = 0;
    foreach ($sliding_torrents as $slider_torrent) {
        $owner = $anonymous = $name = $poster = $seeders = $leechers = $size = $added = $class = $username = $id = $cat = $image = $times_completed = '';
        extract($slider_torrent);
        $i = $site_config['latest_torrents_limit_slider'];

        if ($anonymous === 'yes' && ($CURUSER['class'] < UC_STAFF || $owner === $CURUSER['id'])) {
            $uploader = '<span>' . get_anonymous_name() . '</span>';
        } else {
            $uploader = "<span class='" . get_user_class_name($class, true) . "'>" . htmlsafechars($username) . '</span>';
        }

        $src = $i++ <= 1 ? "src='" . url_proxy($banner, true, 1000, 185) . "' class='noshow round10'" : "data-src='" . url_proxy($banner, true, 1000, 185) . "' class='noshow lazy round10'";
        $poster = "<img src='" . url_proxy($poster, true, 150, null) . "' class='tooltip-poster'>";

        $slider .= '
                    <li>';
        $torrname = "<img $src>";
        $block_id = "slider_id_{$id}";
        $slider .= torrent_tooltip($torrname);
        $slider .= '
                    </li>';
    }

    $slider .= '
                </ul>
            </div>
        </div>
    </div>';
}
