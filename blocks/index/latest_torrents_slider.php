<?php

global $site_config, $lang, $fluent, $CURUSER, $cache;

$torrents = $cache->get('slider_torrents_');
if ($torrents === false || is_null($torrents)) {
    $torrents = $fluent->from('torrents AS t')
        ->select(null)
        ->select('t.id')
        ->select('t.added')
        ->select('t.seeders')
        ->select('t.leechers')
        ->select('t.name')
        ->select('t.size')
        ->select('t.poster')
        ->select('t.anonymous')
        ->select('t.owner')
        ->select('t.imdb_id')
        ->select('t.times_completed')
        ->select('t.rating')
        ->select('t.year')
        ->select('t.subs AS subtitles')
        ->select('u.username')
        ->select('u.class')
        ->select('p.name AS parent_name')
        ->select('c.name AS cat')
        ->select('c.image')
        ->leftJoin('users AS u ON t.owner = u.id')
        ->leftJoin('categories AS c ON t.category = c.id')
        ->leftJoin('categories AS p ON c.parent_id = p.id')
        ->where('t.imdb_id IS NOT NULL')
        ->orderBy('t.added DESC')
        ->limit(100)
        ->fetchAll();

    foreach ($torrents as $torrent) {
        if (!empty($torrent['parent_name'])) {
            $torrent['cat'] = $torrent['parent_name'] . '::' . $torrent['cat'];
        }
        $top5torrents[] = $torrent;
    }
    $cache->set('slider_torrents_', $torrents, $site_config['expires']['slider_torrents']);
}

foreach ($torrents as $torrent) {
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
        $subtitles = $year = $rating = $owner = $anonymous = $name = $poster = $seeders = $leechers = $size = $added = $class = $username = $id = $cat = $image = $times_completed = '';
        extract($slider_torrent);
        $i = $site_config['latest_torrents_limit_slider'];

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
        $slider .= torrent_tooltip($torrname, $id, $block_id, $name, $poster,  $uploader, $added, $size, $seeders, $leechers, $imdb_id, $rating, $year, $subtitles);
        $slider .= '
                    </li>';
    }

    $slider .= '
                </ul>
            </div>
        </div>
    </div>';
}
