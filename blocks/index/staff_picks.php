<?php

global $site_config, $lang, $fluent, $CURUSER, $cache;

$staff_picks = $cache->get('staff_picks_');
if ($staff_picks === false || is_null($staff_picks)) {
    $staff_picks = $fluent->from('torrents')
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
        ->where('torrents.staff_picks != 0')
        ->orderBy('torrents.staff_picks DESC')
        ->limit($site_config['staff_picks_limit'])
        ->fetchAll();

    $cache->set('staff_picks_', $staff_picks, $site_config['expires']['staff_picks']);
}

$staffpicks = "
    <a id='staffpicks-hash'></a>
    <div id='staffpicks' class='box'>
        <div class='has-text-centered'>";

$staffpicks .= "
        <div class='table-wrapper'>
            <table class='table table-bordered table-striped'>";
$staffpicks .= "
                <thead>
                    <tr>
                        <th class='has-text-centered w-10'>{$lang['index_mow_type']}</th>
                        <th class='w-50 minw-150'>{$lang['staff_picks']}</th>
                        <th class='has-text-centered'>{$lang['index_mow_snatched']}</th>
                        <th class='has-text-centered'>{$lang['top5torrents_seeders']}</th>
                        <th class='has-text-centered'>{$lang['top5torrents_leechers']}</th>
                    </tr>
                </thead>
                <tbody>";
foreach ($staff_picks as $staff_pick) {
    $owner = $anonymous = $name = $poster = $seeders = $leechers = $size = $added = $class = $username = $id = $cat = $image = $times_completed = '';
    extract($staff_pick);
    if (empty($poster) && !empty($imdb_id)) {
        $poster = find_images($imdb_id);
    }
    $poster = empty($poster) ? "<img src='{$site_config['pic_baseurl']}noposter.png' class='tooltip-poster'>" : "<img src='" . url_proxy($poster, true, 250) . "' class='tooltip-poster'>";

    if ($anonymous === 'yes' && ($CURUSER['class'] < UC_STAFF || $owner === $CURUSER['id'])) {
        $uploader = '<span>' . get_anonymous_name() . '</span>';
    } else {
        $uploader = "<span class='" . get_user_class_name($class, true) . "'>" . htmlsafechars($username) . '</span>';
    }

    $block_id = "staff_pick_id_{$id}";
    $staffpicks .= torrent_tooltip_wrapper(htmlsafechars($name));
}
if (count($staff_picks) === 0) {
    $staffpicks .= "
                        <tr>
                            <td colspan='5'>{$lang['staff_picks_no_torrents']}</td>
                        </tr>";
}
$staffpicks .= '
                    </tbody>
                </table>
            </div>
        </div>
    </div>';
