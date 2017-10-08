<?php
$HTMLOUT .= "
    <a id='latesttorrents-hash'></a>
    <fieldset id='latesttorrents' class='header'>
        <legend class='flipper'><i class='fa fa-angle-up right10' aria-hidden='true'></i>{$lang['index_latest']}</legend>
        <div class='text-center'>";
if (($top5torrents = $mc1->get_value('top5_tor_')) === false) {
    $res = sql_query("SELECT t.id, t.seeders, t.poster, t.leechers, t.name, t.times_completed, t.category, c.image AS cat_pic, c.name AS cat_name, t.times_completed, t.added, t.size
                        FROM torrents AS t
                        INNER JOIN categories AS c ON t.category = c.id
                        ORDER BY seeders + leechers DESC
                        LIMIT {$site_config['latest_torrents_limit']}") or sqlerr(__FILE__, __LINE__);
    while ($top5torrent = mysqli_fetch_assoc($res)) {
        $top5torrents[] = $top5torrent;
    }
    $mc1->cache_value('top5_tor_', $top5torrents, $site_config['expires']['top5_torrents']);
}
if (count($top5torrents) > 0) {
    $HTMLOUT .= "
            <div class='module'>
                <div class='badge badge-top'>
            </div>
            <table class='table table-bordered table-striped bottom10'>";
    $HTMLOUT .= "
                <thead>
                    <tr>
                        <th class='span1 text-center'>{$lang['index_mow_type']}</th>
                        <th class='span8'>{$lang['top5torrents_title']}</th>
                        <th class='span1 text-center'>{$lang['index_mow_snatched']}</th>
                        <th class='span1 text-center'>{$lang['top5torrents_seeders']}</th>
                        <th class='span1 text-center'>{$lang['top5torrents_leechers']}</th>
                    </tr>
                </thead>
                <tbody>";
    if ($top5torrents) {
        foreach ($top5torrents as $top5torrentarr) {
            $torrname = htmlsafechars($top5torrentarr['name']);
            if (strlen($torrname) > 50) {
                $torrname = substr($torrname, 0, 50) . '...';
            }
            $poster = empty($top5torrentarr['poster']) ? "<img src='{$site_config['pic_base_url']}noposter.png' width='150' height='220' />" : "<img src='" . htmlsafechars($top5torrentarr['poster']) . "' width='150' height='220' />";
            $title = "
                <div class='flex'>
                    <span class='margin10'>
                        $poster
                    </span>
                    <span class='margin10'>
                        <b>{$lang['index_ltst_name']} " . htmlsafechars($top5torrentarr['name']) . "</b><br>
                        <b>Added: " . get_date($top5torrentarr['added'], 'DATE', 0, 1) . "</b><br>
                        <b>Size: " . mksize(htmlsafechars($top5torrentarr['size'])) . "</b><br>
                        <b>{$lang['index_ltst_seeder']} " . (int)$top5torrentarr['seeders'] . "</b><br>
                        <b>{$lang['index_ltst_leecher']} " . (int)$top5torrentarr['leechers'] . "</b><br>
                    </span>
                </div>";

            $HTMLOUT .= "
                    <tr>
                        <td class='span1 text-center'>
                            <img src='./images/caticons/" . get_categorie_icons() . "/" . htmlsafechars($top5torrentarr['cat_pic']) . "' class='tooltipper' alt='" . htmlsafechars($top5torrentarr['cat_name']) . "' title='" . htmlsafechars($top5torrentarr['cat_name']) . "' />
                        </td>
                        <td class='span8'>
                            <a href='{$site_config['baseurl']}/details.php?id=" . (int)$top5torrentarr['id'] . "&amp;hit=1' class='tooltipper' title=\"$title\">
                                {$torrname}
                            </a>
                        </td>
                        <td class='span1 text-center'>" . (int)$top5torrentarr['times_completed'] . "</td>
                        <td class='span1 text-center'>" . (int)$top5torrentarr['seeders'] . "</td>
                        <td class='span1 text-center'>" . (int)$top5torrentarr['leechers'] . "</td>
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
if (($last5torrents = $mc1->get_value('last5_tor_')) === false) {
    $sql = "SELECT t.id, t.seeders, t.poster, t.leechers, t.name, t.times_completed, t.category, c.image AS cat_pic, c.name AS cat_name, t.times_completed, t.added, t.size
                FROM torrents AS t
                INNER JOIN categories AS c ON t.category = c.id
                WHERE visible='yes'
                ORDER BY added DESC
                LIMIT {$site_config['latest_torrents_limit']}";
    $result = sql_query($sql) or sqlerr(__FILE__, __LINE__);
    while ($last5torrent = mysqli_fetch_assoc($result)) {
        $last5torrents[] = $last5torrent;
    }
    $mc1->cache_value('last5_tor_', $last5torrents, $site_config['expires']['last5_torrents']);
}
if (count($last5torrents) > 0) {
    $HTMLOUT .= "
                <div class='module'>
                    <div class='badge badge-new'></div>
                        <table class='table table-bordered table-striped'>
                            <thead>
                                <tr>
                                    <th class='span1 text-center'>{$lang['index_mow_type']}</th>
                                    <th class='span8'>{$lang['last5torrents_title']}</th>
                                    <th class='span1 text-center'>{$lang['index_mow_snatched']}</th>
                                    <th class='span1 text-center'>{$lang['last5torrents_seeders']}</th>
                                    <th class='span1 text-center'>{$lang['last5torrents_leechers']}</th>
                                </tr>
                            </thead>
                            <tbody>";
    if ($last5torrents) {
        foreach ($last5torrents as $last5torrentarr) {
            $torrname = htmlsafechars($last5torrentarr['name']);
            if (strlen($torrname) > 50) {
                $torrname = substr($torrname, 0, 50) . '...';
            }
            $poster = empty($last5torrentarr['poster']) ? "<img src='{$site_config['pic_base_url']}noposter.png' width='150' height='220' />" : "<img src='" . htmlsafechars($last5torrentarr['poster']) . "' width='150' height='220' />";
            $title = "
                <div class='flex'>
                    <span class='margin10'>
                        $poster
                    </span>
                    <span class='margin10 var(--grey-color)'>
                        <b>{$lang['index_ltst_name']} " . htmlsafechars($last5torrentarr['name']) . "</b><br>
                        <b>Added: " . get_date($last5torrentarr['added'], 'DATE', 0, 1) . "</b><br>
                        <b>Size: " . mksize(htmlsafechars($last5torrentarr['size'])) . "</b><br>
                        <b>{$lang['index_ltst_seeder']} " . (int)$last5torrentarr['seeders'] . "</b><br>
                        <b>{$lang['index_ltst_leecher']} " . (int)$last5torrentarr['leechers'] . "</b><br>
                    </span>
                </div>";
            $HTMLOUT .= "
                                <tr>
                                    <td class='span1 text-center'>
                                        <img src='./images/caticons/" . get_categorie_icons() . "/" . htmlsafechars($last5torrentarr['cat_pic']) . "' class='tooltipper' alt='" . htmlsafechars($last5torrentarr['cat_name']) . "' title='" . htmlsafechars($last5torrentarr['cat_name']) . "' />
                                    </td>
                                    <td class='span8'>
                                        <a href='{$site_config['baseurl']}/details.php?id=" . (int)$last5torrentarr['id'] . "&amp;hit=1' class='tooltipper' title=\"$title\">
                                            {$torrname}
                                        </a>
                                    </td>
                                    <td class='span1 text-center'>" . (int)$last5torrentarr['times_completed'] . "</td>
                                    <td class='span1 text-center'>" . (int)$last5torrentarr['seeders'] . "</td>
                                    <td class='span1 text-center'>" . (int)$last5torrentarr['leechers'] . "</td>
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
