<?php

global $site_config, $lang, $fluent, $CURUSER, $cache;

$torrents = $cache->get('scroller_torrents_');
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
        ->select('t.newgenre AS genre')
        ->select('u.username')
        ->select('u.class')
        ->select('p.name AS parent_name')
        ->select('c.name AS cat')
        ->select('c.image')
        ->leftJoin('users AS u ON t.owner = u.id')
        ->leftJoin('categories AS c ON t.category = c.id')
        ->leftJoin('categories AS p ON c.parent_id=p.id')
        ->where('visible = "yes"')
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

    $cache->set('scroller_torrents_', $torrents, $site_config['expires']['scroll_torrents']);
}

foreach ($torrents as $torrent) {
    if (empty($torrent['poster']) && !empty($torrent['imdb_id'])) {
        $images = $cache->get('posters_' . $torrent['imdb_id']);
        if ($images === false || is_null($images)) {
            $images = $fluent->from('images')
                ->select(null)
                ->select('url')
                ->where('type = "poster"')
                ->where('imdb_id=?', $torrent['imdb_id'])
                ->fetchAll();

            $cache->set('posters_' . $torrent['imdb_id'], $images, 86400);
        }

        if (!empty($images)) {
            shuffle($images);
            $torrent['poster'] = $images[0]['url'];
        } else {
            $torrent['poster'] = $site_config['paths']['images_baseurl'] . 'noposter.png';
        }
    }
    $scroller_torrents[] = $torrent;
    if (count($scroller_torrents) >= $site_config['latest']['scroller_limit']) {
        break;
    }
}
if (!empty($scroller_torrents)) {
    shuffle($scroller_torrents);
    $torrents_scroller .= "
    <a id='scroller-hash'></a>
    <div id='scroller' class='box'>
        <div class='bordered'>
            <div id='carousel-container' class='alt_bordered bg-00 carousel-container'>
                <div id='icarousel' class='icarousel'>";

    foreach ($scroller_torrents as $scroll_torrent) {
        $subtitles = $year = $rating = $owner = $anonymous = $name = $poster = $seeders = $leechers = $size = $added = $class = $username = $id = $cat = $image = $times_completed = $genre = '';
        extract($scroll_torrent);
        $i = $site_config['latest']['scroller_limit'];

        if ($anonymous === 'yes' && ($CURUSER['class'] < UC_STAFF || $owner === $CURUSER['id'])) {
            $uploader = '<span>' . get_anonymous_name() . '</span>';
        } else {
            $uploader = "<span class='" . get_user_class_name($class, true) . "'>" . htmlsafechars($username) . '</span>';
        }
        $scroll_poster = $poster;
        $poster = "<img src='" . url_proxy($poster, true, 250) . "' class='tooltip-poster'>";

        $torrents_scroller .= "
                    <div class='slide'>";
        $torrname = "<img src='" . url_proxy($scroll_poster, true, null, 300) . "' alt='{$name}' style='width: auto; height: 300px; max-height: 300px;'>";
        $block_id = "scroll_id_{$id}";
        $torrents_scroller .= torrent_tooltip($torrname, $id, $block_id, $name, $poster, $uploader, $added, $size, $seeders, $leechers, $imdb_id, $rating, $year, $subtitles, $genre);
        $torrents_scroller .= '
                    </div>';
    }

    $torrents_scroller .= '
                </div>
            </div>
        </div>
    </div>';
}
