<?php
global $site_config, $cache, $lang, $db;

$HTMLOUT .= "
    <a id='latesttorrents-hash'></a>
    <fieldset id='latesttorrents' class='header'>
        <legend class='flipper has-text-primary'><i class='fa fa-angle-up right10' aria-hidden='true'></i>{$lang['index_latest']}</legend>
        <div class='has-text-centered'>";
$top5torrents = $cache->get('top5_tor_');
if ($top5torrents === false || is_null($top5torrents)) {
    $query = $db->from('torrents')
        ->leftJoin('users ON torrents.owner = users.id')
        ->select('users.username')
        ->select('users.class')
        ->leftJoin('categories ON torrents.category = categories.id')
        ->select('categories.name AS cat')
        ->select('categories.image')
        ->orderBy('seeders + leechers DESC')
        ->limit(5);
    foreach ($query as $row) {
        $top5torrents[] = $row;
    }

    $cache->set('top5_tor_', $top5torrents, $site_config['expires']['top5_torrents']);
}
if (count($top5torrents) > 0) {
    $HTMLOUT .= "
            <div class='module table-wrapper bottom20'>
                <div class='badge badge-top'></div>
                <table class='table table-bordered table-striped'>";
    $HTMLOUT .= "
                    <thead>
                        <tr>
                            <th class='has-text-centered w-10'>{$lang['index_mow_type']}</th>
                            <th class='w-50'>{$lang['top5torrents_title']}</th>
                            <th class='has-text-centered'>{$lang['index_mow_snatched']}</th>
                            <th class='has-text-centered'>{$lang['top5torrents_seeders']}</th>
                            <th class='has-text-centered'>{$lang['top5torrents_leechers']}</th>
                        </tr>
                    </thead>
                    <tbody>";
    if ($top5torrents) {
        foreach ($top5torrents as $top5torrentarr) {
            $torrname = htmlsafechars($top5torrentarr['name']);
            if (strlen($torrname) > 50) {
                $torrname = substr($torrname, 0, 50) . '...';
            }
            $poster = empty($top5torrentarr['poster']) ? "<img src='{$site_config['pic_base_url']}noposter.png' class='tooltip-poster' />" : "<img src='" . htmlsafechars($top5torrentarr['poster']) . "' class='tooltip-poster' />";

            $HTMLOUT .= "
                        <tr>
                            <td class='has-text-centered'>
                                <img src='{$site_config['pic_base_url']}caticons/" . get_categorie_icons() . "/" . htmlsafechars($top5torrentarr['image']) . "' class='tooltipper' alt='" . htmlsafechars($top5torrentarr['cat']) . "' title='" . htmlsafechars($top5torrentarr['cat']) . "' />
                            </td>
                            <td>
                                <a href='{$site_config['baseurl']}/details.php?id=" . (int)$top5torrentarr['id'] . "&amp;hit=1'>
                                    <span class='dt-tooltipper-large' data-tooltip-content='#top_id_{$top5torrentarr['id']}_tooltip'>
                                        {$torrname}
                                        <div class='tooltip_templates'>
                                            <div id='top_id_{$top5torrentarr['id']}_tooltip'>
                                                <div class='is-flex tooltip-torrent'>
                                                    <span class='margin10'>
                                                        $poster
                                                    </span>
                                                    <span class='margin10'>
                                                        <b class='size_4 right10 has-text-primary'>{$lang['index_ltst_name']}</b>" . htmlsafechars($top5torrentarr['name']) . "<br>
                                                        <b class='size_4 right10 has-text-primary'>{$lang['index_ltst_uploader']}</b><span class='" . get_user_class_name($top5torrentarr['class'], true) . "'>" . htmlsafechars($top5torrentarr['username']) . "</span><br>
                                                        <b class='size_4 right10 has-text-primary'>{$lang['index_ltst_added']}</b>" . get_date($top5torrentarr['added'], 'DATE', 0, 1) . "<br>
                                                        <b class='size_4 right10 has-text-primary'>{$lang['index_ltst_size']}</b>" . mksize(htmlsafechars($top5torrentarr['size'])) . "<br>
                                                        <b class='size_4 right10 has-text-primary'>{$lang['index_ltst_seeder']}</b>" . (int)$top5torrentarr['seeders'] . "<br>
                                                        <b class='size_4 right10 has-text-primary'>{$lang['index_ltst_leecher']}</b>" . (int)$top5torrentarr['leechers'] . "<br>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </span>
                                </a>
                            </td>
                            <td class='has-text-centered'>" . (int)$top5torrentarr['times_completed'] . "</td>
                            <td class='has-text-centered'>" . (int)$top5torrentarr['seeders'] . "</td>
                            <td class='has-text-centered'>" . (int)$top5torrentarr['leechers'] . "</td>
                        </tr>";
        }
        $HTMLOUT .= '
                    </tbody>
                </table>
            </div>';
    } else {
        if (empty($top5torrents)) {
            $HTMLOUT .= "
                        <tr>
                            <td colspan='5'>{$lang['top5torrents_no_torrents']}</td>
                        </tr>
                    </tbody>
                </table>
            </div>";
        }
    }
}
$last5torrents = $cache->get('last5_tor_');
if ($last5torrents === false || is_null($last5torrents)) {
    $query = $db->from('torrents')
        ->leftJoin('users ON torrents.owner = users.id')
        ->select('users.username')
        ->select('users.class')
        ->leftJoin('categories ON torrents.category = categories.id')
        ->select('categories.name AS cat')
        ->select('categories.image')
        ->orderBy('added DESC')
        ->limit(5);
    foreach ($query as $row) {
        $last5torrents[] = $row;
    }
    $cache->set('last5_tor_', $last5torrents, $site_config['expires']['last5_torrents']);
}
if (count($last5torrents) > 0) {
    $HTMLOUT .= "
            <div class='module table-wrapper'>
                <div class='badge badge-new'></div>
                <table class='table table-bordered table-striped'>
                    <thead>
                        <tr>
                            <th class='has-text-centered w-10'>{$lang['index_mow_type']}</th>
                            <th class='w-50'>{$lang['last5torrents_title']}</th>
                            <th class='has-text-centered'>{$lang['index_mow_snatched']}</th>
                            <th class='has-text-centered'>{$lang['last5torrents_seeders']}</th>
                            <th class='has-text-centered'>{$lang['last5torrents_leechers']}</th>
                        </tr>
                    </thead>
                    <tbody>";
    if ($last5torrents) {
        foreach ($last5torrents as $last5torrentarr) {
            $torrname = htmlsafechars($last5torrentarr['name']);
            if (strlen($torrname) > 50) {
                $torrname = substr($torrname, 0, 50) . '...';
            }
            $poster = empty($last5torrentarr['poster']) ? "<img src='{$site_config['pic_base_url']}noposter.png' class='tooltip-poster' />" : "<img src='" . htmlsafechars($last5torrentarr['poster']) . "' class='tooltip-poster' />";

            $HTMLOUT .= "
                        <tr id='id_{$last5torrentarr['id']}_tooltip'>
                            <td class='has-text-centered'>
                                <img src='{$site_config['pic_base_url']}caticons/" . get_categorie_icons() . "/" . htmlsafechars($last5torrentarr['image']) . "' class='tooltipper' alt='" . htmlsafechars($last5torrentarr['cat']) . "' title='" . htmlsafechars($last5torrentarr['cat']) . "' />
                            </td>
                            <td>
                                <a href='{$site_config['baseurl']}/details.php?id=" . (int)$last5torrentarr['id'] . "&amp;hit=1'>
                                    <span class='dt-tooltipper-large' data-tooltip-content='#last_id_{$last5torrentarr['id']}_tooltip'>
                                        {$torrname}
                                        <div class='tooltip_templates'>
                                            <div id='last_id_{$last5torrentarr['id']}_tooltip'>
                                                <div class='is-flex tooltip-torrent'>
                                                    <span class='margin10'>
                                                        $poster
                                                    </span>
                                                    <span class='margin10'>
                                                        <b class='size_4 right10 has-text-primary'>{$lang['index_ltst_name']}</b>" . htmlsafechars($last5torrentarr['name']) . "<br>
                                                        <b class='size_4 right10 has-text-primary'>{$lang['index_ltst_uploader']}</b><span class='" . get_user_class_name($last5torrentarr['class'], true) . "'>" . htmlsafechars($last5torrentarr['username']) . "</span><br>
                                                        <b class='size_4 right10 has-text-primary'>{$lang['index_ltst_added']}</b>" . get_date($last5torrentarr['added'], 'DATE', 0, 1) . "<br>
                                                        <b class='size_4 right10 has-text-primary'>{$lang['index_ltst_size']}</b>" . mksize(htmlsafechars($last5torrentarr['size'])) . "<br>
                                                        <b class='size_4 right10 has-text-primary'>{$lang['index_ltst_seeder']}</b>" . (int)$last5torrentarr['seeders'] . "<br>
                                                        <b class='size_4 right10 has-text-primary'>{$lang['index_ltst_leecher']}</b>" . (int)$last5torrentarr['leechers'] . "<br>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </span>
                                </a>
                            </td>
                            </td>
                            <td class='has-text-centered'>" . (int)$last5torrentarr['times_completed'] . "</td>
                            <td class='has-text-centered'>" . (int)$last5torrentarr['seeders'] . "</td>
                            <td class='has-text-centered'>" . (int)$last5torrentarr['leechers'] . "</td>
                        </tr>";
        }
        $HTMLOUT .= '
                    </tbody>
                </table>
            </div>';
    } else {
        if (empty($last5torrents)) {
            $HTMLOUT .= "
                        <tr>
                            <td colspan='5'>{$lang['last5torrents_no_torrents']}</td>
                        </tr>
                    </tbody>
                </table>
            </div>";
        }
    }
}
$HTMLOUT .= '</div>';
