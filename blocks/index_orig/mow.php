<?php

global $lang, $site_config, $fluent, $CURUSER, $cache;

$motw = $cache->get('motw_');
if ($motw === false || is_null($motw)) {
    $motw = $fluent->from('torrents')
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
        ->leftJoin('avps ON torrents.id = avps.value_u')
        ->where('avps.arg', 'bestfilmofweek')
        ->fetchAll();

    $cache->set('motw_', $motw, $site_config['expires']['motw']);
}

$torrents_mow .= "
    <a id='mow-hash'></a>
    <fieldset id='mow' class='header'>
        <legend class='flipper has-text-primary'><i class='icon-down-open size_2' aria-hidden='true'></i>{$lang['index_mow_title']}</legend>
        <div class='has-text-centered'>
            <div class='table-wrapper module'><div class='badge badge-hot'></div>
                <table class='table table-bordered table-striped'>
                    <thead>
                        <tr>
                            <th class='has-text-centered w-10'>{$lang['index_mow_type']}</th>
                            <th class='w-50 minw-150'>{$lang['index_mow_name']}</th>
                            <th class='has-text-centered'>{$lang['index_mow_snatched']}</th>
                            <th class='has-text-centered'>{$lang['index_mow_seeder']}</th>
                            <th class='has-text-centered'>{$lang['index_mow_leecher']}</th>
                        </tr>
                    </thead>
                    <tbody>";
foreach ($motw as $m_w) {
    $owner = $anonymous = $name = $poster = $seeders = $leechers = $size = $added = $class = $username = $id = $cat = $image = $times_completed = '';
    extract($m_w);
    if (strlen($torrname) > 75) {
        $torrname = substr($torrname, 0, 50) . '...';
    }
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
    $torrents_mow .= torrent_tooltip_wrapper(htmlsafechars($name));
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
    </fieldset>';
