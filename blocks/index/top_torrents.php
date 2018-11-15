<?php

global $site_config, $lang, $fluent, $CURUSER, $cache;

$top5torrents = $cache->get('top5_tor_');
if ($top5torrents === false || is_null($top5torrents)) {
    $top5torrents = $fluent->from('torrents')
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
        ->leftJoin('users ON torrents.owner = users.id')
        ->select('users.username')
        ->select('users.class')
        ->leftJoin('categories ON torrents.category = categories.id')
        ->select('categories.name AS cat')
        ->select('categories.image')
        ->orderBy('torrents.seeders + torrents.leechers DESC')
        ->limit($site_config['latest_torrents_limit'])
        ->fetchAll();

    $cache->set('top5_tor_', $top5torrents, $site_config['expires']['top5_torrents']);
}

$torrents_top .= "
    <a id='toptorrents-hash'></a>
    <div id='toptorrents' class='box'>
        <div class='has-text-centered'>
        <div class='module table-wrapper bottom20'>
            <div class='badge badge-top'></div>
            <table class='table table-bordered table-striped'>
                <thead>
                    <tr>
                        <th class='has-text-centered w-10'>{$lang['index_mow_type']}</th>
                        <th class='w-50 minw-150'>{$lang['top5torrents_title']}</th>
                        <th class='has-text-centered'>{$lang['index_mow_snatched']}</th>
                        <th class='has-text-centered'>{$lang['top5torrents_seeders']}</th>
                        <th class='has-text-centered'>{$lang['top5torrents_leechers']}</th>
                    </tr>
                </thead>
                <tbody>";

foreach ($top5torrents as $top5torrentarr) {
    $owner = $anonymous = $name = $poster = $seeders = $leechers = $size = $added = $class = $username = $id = $cat = $image = $times_completed = '';
    extract($top5torrentarr);
    if (empty($poster) && !empty($imdb_id)) {
        $poster = find_images($imdb_id);
    }
    $poster = empty($poster) ? "<img src='{$site_config['pic_baseurl']}noposter.png' class='tooltip-poster'>" : "<img src='" . url_proxy($poster, true, 150, null) . "' class='tooltip-poster'>";

    if ($anonymous === 'yes' && ($CURUSER['class'] < UC_STAFF || $owner === $CURUSER['id'])) {
        $uploader = '<span>' . get_anonymous_name() . '</span>';
    } else {
        $uploader = "<span class='" . get_user_class_name($class, true) . "'>" . htmlsafechars($username) . '</span>';
    }

    $block_id = "top_id_{$id}";
    $torrents_top .= torrent_tooltip_wrapper(htmlsafechars($name));
}
if (count($top5torrents) === 0) {
    $torrents_top .= "
                    <tr>
                        <td colspan='5'>{$lang['top5torrents_no_torrents']}</td>
                    </tr>";
}
$torrents_top .= '
                    </tbody>
                </table>
            </div>
        </div>
    </div>';
