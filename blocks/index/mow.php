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

$HTMLOUT .= "
    <a id='mow-hash'></a>
    <fieldset id='mow' class='header'>
        <legend class='flipper has-text-primary'><i class='fa icon-up-open size_3' aria-hidden='true'></i>{$lang['index_mow_title']}</legend>
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
    $torrname = htmlsafechars($name);
    if (strlen($torrname) > 75) {
        $torrname = substr($torrname, 0, 50) . '...';
    }
    $poster = empty($poster) ? "<img src='{$site_config['pic_baseurl']}noposter.png' class='tooltip-poster' />" : "<img src='" . url_proxy($poster, true, 150, null) . "' class='tooltip-poster' />";

    if ($anonymous === 'yes' && ($CURUSER['class'] < UC_STAFF || $owner === $CURUSER['id'])) {
        $uploader = '<span>' . get_anonymous_name() . '</span>';
    } else {
        $uploader = "<span class='" . get_user_class_name($class, true) . "'>" . htmlsafechars($username) . '</span>';
    }

    $HTMLOUT .= "
                        <tr>
                            <td class='has-text-centered'><img src='{$site_config['pic_baseurl']}caticons/" . get_category_icons() . "/{$image}' class='tooltipper' alt='{$cat}' title='{$cat}' /></td>
                            <td>
                                <a href='{$site_config['baseurl']}/details.php?id={$id}&amp;hit=1'>
                                    <span class='dt-tooltipper-large' data-tooltip-content='#mow_id_{$id}_tooltip'>
                                        $torrname
                                        <div class='tooltip_templates'>
                                            <div id='mow_id_{$id}_tooltip'>
                                                <div class='is-flex tooltip-torrent'>
                                                    <span class='margin10'>
                                                        $poster
                                                    </span>
                                                    <span class='margin10'>
                                                        <b class='size_4 right10 has-text-primary'>{$lang['index_ltst_name']}</b>" . htmlsafechars($name) . "<br>
                                                        <b class='size_4 right10 has-text-primary'>{$lang['index_ltst_uploader']}</b>$uploader<br>
                                                        <b class='size_4 right10 has-text-primary'>{$lang['index_ltst_added']}</b>" . get_date($added, 'DATE', 0, 1) . "<br>
                                                        <b class='size_4 right10 has-text-primary'>{$lang['index_ltst_size']}</b>" . mksize(htmlsafechars($size)) . "<br>
                                                        <b class='size_4 right10 has-text-primary'>{$lang['index_ltst_seeder']}</b>{$seeders}<br>
                                                        <b class='size_4 right10 has-text-primary'>{$lang['index_ltst_leecher']}</b>{$leechers}<br>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </span>
                                </a>
                            </td>
                            <td class='has-text-centered'>{$times_completed}</td>
                            <td class='has-text-centered'>{$seeders}</td>
                            <td class='has-text-centered'>{$leechers}</td>
                        </tr>";
}

if (count($motw) === 0) {
    $HTMLOUT .= "
                        <tr>
                            <td colspan='5'>{$lang['index_mow_no']}!</td>
                        </tr>";
}
$HTMLOUT .= '
                    </tbody>
                </table>
            </div>
        </div>
    </fieldset>';
