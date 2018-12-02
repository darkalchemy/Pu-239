<?php

global $site_config, $lang, $fluent, $CURUSER, $cache;

$last5torrents = $cache->get('last5_tor_');
if ($last5torrents === false || is_null($last5torrents)) {
    $last5torrents = $fluent->from('torrents')
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
        ->orderBy('torrents.added DESC')
        ->limit($site_config['latest_torrents_limit'])
        ->fetchAll();

    $cache->set('last5_tor_', $last5torrents, $site_config['expires']['last5_torrents']);
}

$latest_torrents .= "
    <a id='latesttorrents-hash'></a>
    <fieldset id='latesttorrents' class='header'>
        <legend class='flipper has-text-primary'><i class='icon-down-open size_2' aria-hidden='true'></i>{$lang['index_latest']}</legend>
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
    $owner = $anonymous = $name = $poster = $seeders = $leechers = $size = $added = $class = $username = $id = $cat = $image = $times_completed = '';
    extract($last5torrent);
    if (empty($poster) && !empty($imdb_id)) {
        $poster = find_images($imdb_id);
    }
    $poster = empty($poster) ? "<img src='{$site_config['pic_baseurl']}noposter.png' class='tooltip-poster'>" : "<img src='" . url_proxy($poster, true, 250) . "' class='tooltip-poster'>";

    if ($anonymous === 'yes' && ($CURUSER['class'] < UC_STAFF || $owner === $CURUSER['id'])) {
        $uploader = '<span>' . get_anonymous_name() . '</span>';
    } else {
        $uploader = "<span class='" . get_user_class_name($class, true) . "'>" . htmlsafechars($username) . '</span>';
    }

    $block_id = "last_id_{$id}";
    $latest_torrents .= torrent_tooltip_wrapper(htmlsafechars($name));
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
    </fieldset>';
