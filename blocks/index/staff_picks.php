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

$HTMLOUT .= "
    <a id='staffpicks-hash'></a>
    <fieldset id='staffpicks' class='header'>
        <legend class='flipper has-text-primary'><i class='icon-down-open size_3' aria-hidden='true'></i>{$lang['staff_picks']}</legend>
        <div class='has-text-centered'>";

$HTMLOUT .= "
        <div class='module table-wrapper bottom20'>
            <table class='table table-bordered table-striped'>";
$HTMLOUT .= "
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
foreach ($staff_picks as $staff_pick) {
    $owner = $anonymous = $name = $poster = $seeders = $leechers = $size = $added = $class = $username = $id = $cat = $image = $times_completed = '';
    extract($staff_pick);

    $torrname = htmlsafechars($name);
    $poster = empty($poster) ? "<img src='{$site_config['pic_baseurl']}noposter.png' class='tooltip-poster' />" : "<img src='" . url_proxy($poster, true, 150, null) . "' class='tooltip-poster' />";

    if ($anonymous === 'yes' && ($CURUSER['class'] < UC_STAFF || $owner === $CURUSER['id'])) {
        $uploader = '<span>' . get_anonymous_name() . '</span>';
    } else {
        $uploader = "<span class='" . get_user_class_name($class, true) . "'>" . htmlsafechars($username) . '</span>';
    }

    $HTMLOUT .= "
                    <tr>
                        <td class='has-text-centered'>
                            <img src='{$site_config['pic_baseurl']}caticons/" . get_category_icons() . '/' . htmlsafechars($image) . "' class='tooltipper' alt='" . htmlsafechars($cat) . "' title='" . htmlsafechars($cat) . "' />
                        </td>
                        <td>
                            <a href='{$site_config['baseurl']}/details.php?id={$id}&amp;hit=1'>
                                <span class='dt-tooltipper-large' data-tooltip-content='#top_id_{$id}_tooltip'>
                                    {$torrname}
                                    <div class='tooltip_templates'>
                                        <div id='top_id_{$id}_tooltip'>
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
if (count($staff_picks) === 0) {
    $HTMLOUT .= "
                        <tr>
                            <td colspan='5'>{$lang['staff_picks_no_torrents']}</td>
                        </tr>";
}
$HTMLOUT .= '
                    </tbody>
                </table>
            </div>
        </div>
    </fieldset>';
