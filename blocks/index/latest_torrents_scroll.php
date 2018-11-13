<?php

global $site_config, $lang, $fluent, $CURUSER, $cache;

$scroll_torrents = $cache->get('scroll_tor_');
if ($scroll_torrents === false || is_null($scroll_torrents)) {
    $scroll_torrents = $fluent->from('torrents')
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
        ->leftJoin('users ON torrents.owner = users.id')
        ->orderBy('torrents.added DESC')
        ->limit(100)
        ->fetchAll();

    $cache->set('scroll_tor_', $scroll_torrents, $site_config['expires']['scroll_torrents']);
}

foreach ($scroll_torrents as $torrent) {
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
    $scroller_torrents[] = $torrent;
    if (count($scroller_torrents) >= $site_config['latest_torrents_limit_scroll']) {
        break;
    }
}

if ($scroller_torrents) {
    $torrents_scroller .= "
    <a id='scroller-hash'></a>
    <fieldset id='scroller' class='header'>
        <legend class='flipper has-text-primary'>
            <i class='icon-down-open size_2' aria-hidden='true'></i>
            {$lang['index_latest']} Scroller
        </legend>
        <div class='bordered'>
            <div id='carousel-container' class='alt_bordered bg-00 carousel-container'>
                <div id='icarousel' class='icarousel'>";

    foreach ($scroller_torrents as $scroll_torrent) {
        $owner = $anonymous = $name = $poster = $seeders = $leechers = $size = $added = $class = $username = $id = $cat = $image = $times_completed = '';
        extract($scroll_torrent);
        $i = $site_config['latest_torrents_limit_scroll'];

        if ($anonymous === 'yes' && ($CURUSER['class'] < UC_STAFF || $owner === $CURUSER['id'])) {
            $uploader = '<span>' . get_anonymous_name() . '</span>';
        } else {
            $uploader = "<span class='" . get_user_class_name($class, true) . "'>" . htmlsafechars($username) . '</span>';
        }
        $scroll_poster = $poster;
        $poster = "<img src='" . url_proxy($poster, true, 150, null) . "' class='tooltip-poster'>";

        $torrents_scroller .= "
                    <div class='slide'>";
        $torrname = "<img src='" . url_proxy($scroll_poster, true, null, 300) . "' alt='{$name}' style='width: auto; height: 300px; max-height: 300px;'>";
        $block_id = "scroll_id_{$id}";
        $torrents_scroller .= torrent_tooltip($torrname);
        $torrents_scroller .= '
                    </div>';
    }

    $torrents_scroller .= '
                </div>
            </div>
        </div>
    </fieldset>';
} else {
    $torrents_scroller .= "
    <a id='scroller-hash'></a>
    <fieldset id='scroller' class='header'>
        <legend class='flipper has-text-primary'>
            <i class='icon-down-open size_2' aria-hidden='true'></i>
            {$lang['index_latest']} Scroller
        </legend>
        <div class='bordered'>
            <div class='alt_bordered bg-00'>
                {$lang['last5torrents_no_torrents']}
            </div>
        </div>
    </fieldset>";
}
