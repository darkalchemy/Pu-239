<?php

global $site_config, $lang, $fluent, $CURUSER, $cache;

$staff_picks = $cache->get('staff_picks_');
if ($staff_picks === false || is_null($staff_picks)) {
    $staff_picks = [];
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
                       ->where('t.staff_picks != 0')
                       ->where('visible = "yes"')
                       ->orderBy('t.staff_picks DESC')
                       ->limit($site_config['latest']['staff_picks']);

    foreach ($torrents as $torrent) {
        if (!empty($torrent['parent_name'])) {
            $torrent['cat'] = $torrent['parent_name'] . '::' . $torrent['cat'];
        }
        $staff_picks[] = $torrent;
    }

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
    $subtitles = $year = $rating = $owner = $anonymous = $name = $poster = $seeders = $leechers = $size = $added = $class = $username = $id = $cat = $image = $times_completed = $genre = '';
    extract($staff_pick);
    if (empty($poster) && !empty($imdb_id)) {
        $poster = find_images($imdb_id);
    }
    $poster = empty($poster) ? "<img src='{$site_config['paths']['images_baseurl']}noposter.png' class='tooltip-poster'>" : "<img src='" . url_proxy($poster, true, 250) . "' class='tooltip-poster'>";

    if ($anonymous === 'yes' && ($CURUSER['class'] < UC_STAFF || $owner === $CURUSER['id'])) {
        $uploader = '<span>' . get_anonymous_name() . '</span>';
    } else {
        $uploader = "<span class='" . get_user_class_name($class, true) . "'>" . htmlsafechars($username) . '</span>';
    }

    $block_id = "staff_pick_id_{$id}";
    $staffpicks .= torrent_tooltip_wrapper(htmlsafechars($name) . " ($year)", $id, $block_id, $name, $poster, $uploader, $added, $size, $seeders, $leechers, $imdb_id, $rating, $year, $subtitles, $genre);
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
