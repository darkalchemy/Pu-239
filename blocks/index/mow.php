<?php

global $lang, $site_config, $fluent, $CURUSER, $cache;

$motw = $cache->get('motw_');
if ($motw === false || is_null($motw)) {
    $motw = [];
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
        ->leftJoin('categories AS p ON c.parent_id = p.id')
        ->leftJoin('avps AS a ON t.id = a.value_u')
        ->orderBy('t.seeders + t.leechers DESC')
        ->where('a.arg', 'bestfilmofweek');

    foreach ($torrents as $torrent) {
        if (!empty($torrent['parent_name'])) {
            $torrent['cat'] = $torrent['parent_name'] . '::' . $torrent['cat'];
        }
        $motw[] = $torrent;
    }

    $cache->set('motw_', $motw, $site_config['expires']['motw']);
}

$torrents_mow .= "
    <a id='mow-hash'></a>
    <div id='mow' class='box'>
        <div class='has-text-centered'>
            <div class='table-wrapper module'><div class='badge badge-hot'></div>
                <table class='table table-bordered table-striped'>
                    <thead>
                        <tr>
                            <th class='has-text-centered w-10'>{$lang['index_mow_type']}</th>
                            <th class='w-50 minw-150'>{$lang['index_mow_title']}</th>
                            <th class='has-text-centered'>{$lang['index_mow_snatched']}</th>
                            <th class='has-text-centered'>{$lang['index_mow_seeder']}</th>
                            <th class='has-text-centered'>{$lang['index_mow_leecher']}</th>
                        </tr>
                    </thead>
                    <tbody>";

foreach ($motw as $m_w) {
    $subtitles = $year = $rating = $owner = $anonymous = $name = $poster = $seeders = $leechers = $size = $added = $class = $username = $id = $cat = $image = $times_completed = $genre = '';
    extract($m_w);
    if (empty($poster) && !empty($imdb_id)) {
        $poster = find_images($imdb_id);
    }
    $poster = empty($poster) ? "<img src='{$site_config['pic_baseurl']}noposter.png' class='tooltip-poster'>" : "<img src='" . url_proxy($poster, true, 250) . "' class='tooltip-poster'>";

    if ($anonymous === 'yes' && ($CURUSER['class'] < UC_STAFF || $owner === $CURUSER['id'])) {
        $uploader = '<span>' . get_anonymous_name() . '</span>';
    } else {
        $uploader = "<span class='" . get_user_class_name($class, true) . "'>" . htmlsafechars($username) . '</span>';
    }

    $block_id = "mow_id_{$id}";
    $torrents_mow .= torrent_tooltip_wrapper(htmlsafechars($name) . " ($year)", $id, $block_id, $name, $poster, $uploader, $added, $size, $seeders, $leechers, $imdb_id, $rating, $year, $subtitles, $genre);
}

if (count($motw) === 0) {
    $torrents_mow .= "
                        <tr>
                            <td colspan='5'>{$lang['index_mow_no']}!</td>
                        </tr>";
}
$torrents_mow .= '
                    </tbody>
                </table>
            </div>
        </div>
    </div>';
