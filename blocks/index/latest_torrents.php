<?php

global $site_config, $lang, $fluent, $CURUSER, $cache;

$last5torrents = $cache->get('latest_torrents_');
if ($last5torrents === false || is_null($last5torrents)) {
    $last5torrents = [];
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
        ->orderBy('t.added DESC')
        ->limit($site_config['latest']['torrents_limit']);

    foreach ($torrents as $torrent) {
        if (!empty($torrent['parent_name'])) {
            $torrent['cat'] = $torrent['parent_name'] . '::' . $torrent['cat'];
        }
        $last5torrents[] = $torrent;
    }

    $cache->set('latest_torrents_', $last5torrents, $site_config['expires']['last_torrents']);
}

$latest_torrents .= "
    <a id='latesttorrents-hash'></a>
    <div id='latesttorrents' class='box'>
        <div class='has-text-centered'>
            <div class='module table-wrapper'>
                <div class='badge badge-new'></div>
                <table class='table table-bordered table-striped'>
                    <thead>
                        <tr>
                            <th class='has-text-centered w-10'>{$lang['index_mow_type']}</th>
                            <th class='w-50 minw-150'>{$lang['last5torrents_title']}</th>
                            <th class='has-text-centered'>{$lang['index_mow_snatched']}</th>
                            <th class='has-text-centered'>{$lang['last5torrents_seeders']}</th>
                            <th class='has-text-centered'>{$lang['last5torrents_leechers']}</th>
                        </tr>
                    </thead>
                    <tbody>";
foreach ($last5torrents as $last5torrent) {
    $subtitles = $year = $rating = $owner = $anonymous = $name = $poster = $seeders = $leechers = $size = $added = $class = $username = $id = $cat = $image = $times_completed = $genre = '';
    extract($last5torrent);
    if (empty($poster) && !empty($imdb_id)) {
        $poster = find_images($imdb_id);
    }
    $poster = empty($poster) ? "<img src='{$site_config['paths']['images_baseurl']}noposter.png' class='tooltip-poster'>" : "<img src='" . url_proxy($poster, true, 250) . "' class='tooltip-poster'>";

    if ($anonymous === 'yes' && ($CURUSER['class'] < UC_STAFF || $owner === $CURUSER['id'])) {
        $uploader = '<span>' . get_anonymous_name() . '</span>';
    } else {
        $uploader = "<span class='" . get_user_class_name($class, true) . "'>" . htmlsafechars($username) . '</span>';
    }

    $block_id = "last_id_{$id}";
    $latest_torrents .= torrent_tooltip_wrapper(htmlsafechars($name) . " ($year)", $id, $block_id, $name, $poster, $uploader, $added, $size, $seeders, $leechers, $imdb_id, $rating, $year, $subtitles, $genre);
}
if (count($last5torrents) === 0) {
    $latest_torrents .= "
                        <tr>
                            <td colspan='5'>{$lang['last5torrents_no_torrents']}</td>
                        </tr>";
}
$latest_torrents .= '
                    </tbody>
                </table>
            </div>
        </div>
    </div>';
