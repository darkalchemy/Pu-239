<?php
global $CURUSER, $site_config, $lang;

$count_snatched = $count2 = $dlc = '';
if ($CURUSER['class'] >= UC_STAFF) {
    if (isset($_GET['snatched_table'])) {
        $HTMLOUT .= "<tr><td class='one'><b>{$lang['userdetails_snatched']}</b><br>[ <a href=\"userdetails.php?id=$id\" class=\"sublink\">{$lang['userdetails_snatched_hide']}</a> ]</td><td class='one'>";
        if (XBT_TRACKER === false) {
            $res = sql_query('SELECT sn.start_date AS s, sn.complete_date AS c, sn.last_action AS l_a, sn.seedtime AS s_t, sn.seedtime, sn.leechtime AS l_t, sn.leechtime, sn.downspeed, sn.upspeed, sn.uploaded, sn.downloaded, sn.torrentid, sn.start_date, sn.complete_date, sn.seeder, sn.last_action, sn.connectable, sn.agent, sn.seedtime, sn.port, cat.name, cat.image, t.size, t.seeders, t.leechers, t.owner, t.name AS torrent_name ' . 'FROM snatched AS sn ' . 'LEFT JOIN torrents AS t ON t.id = sn.torrentid ' . 'LEFT JOIN categories AS cat ON cat.id = t.category ' . 'WHERE sn.userid=' . sqlesc($id) . ' ORDER BY sn.start_date DESC') or sqlerr(__FILE__, __LINE__);
        } else {
            $res = sql_query('SELECT x.started AS s, x.completedtime AS c, x.mtime AS l_a, x.seedtime AS s_t, x.seedtime, x.leechtime AS l_t, x.leechtime, x.downspeed, x.upspeed, x.uploaded, x.downloaded, x.fid, x.started, x.completedtime, x.active, x.mtime, x.connectable, x.peer_id, cat.name, cat.image, t.size, t.seeders, t.leechers, t.owner, t.name AS torrent_name ' . 'FROM xbt_files_users AS x ' . 'LEFT JOIN torrents AS t ON t.id = x.fid ' . 'LEFT JOIN categories AS cat ON cat.id = t.category ' . 'WHERE x.uid=' . sqlesc($id) . ' ORDER BY x.started DESC') or sqlerr(__FILE__, __LINE__);
        }
        $HTMLOUT .= "<table ><tr><td class='colhead'>{$lang['userdetails_s_cat']}</td><td class='colhead'>{$lang['userdetails_s_torr']}</td>" . "<td class='colhead'>{$lang['userdetails_s_sl']}</td><td class='colhead'>{$lang['userdetails_s_up']}" . ($site_config['ratio_free'] ? '' : "{$lang['userdetails_s_down']}") . "</td><td class='colhead'>{$lang['userdetails_s_tsize']}</td>" . "<td class='colhead'>{$lang['userdetails_ratio']}</td><td class='colhead'>{$lang['userdetails_client']}</td></tr>";
        while ($arr = mysqli_fetch_assoc($res)) {
            //=== speed color red fast green slow ;)
            if ($arr['upspeed'] > 0) {
                $ul_speed = ($arr['upspeed'] > 0 ? mksize($arr['upspeed']) : ($arr['seedtime'] > 0 ? mksize($arr['uploaded'] / ($arr['seedtime'] + $arr['leechtime'])) : mksize(0)));
            } else {
                $ul_speed = mksize(($arr['uploaded'] / ($arr['l_a'] - $arr['s'] + 1)));
            }
            if ($arr['downspeed'] > 0) {
                $dl_speed = ($arr['downspeed'] > 0 ? mksize($arr['downspeed']) : ($arr['leechtime'] > 0 ? mksize($arr['downloaded'] / $arr['leechtime']) : mksize(0)));
            } else {
                $dl_speed = mksize(($arr['downloaded'] / ($arr['c'] - $arr['s'] + 1)));
            }
            switch (true) {
                case $dl_speed > 600:
                    $dlc = 'red';
                    break;

                case $dl_speed > 300:
                    $dlc = 'orange';
                    break;

                case $dl_speed > 200:
                    $dlc = 'yellow';
                    break;

                case $dl_speed < 100:
                    $dlc = 'Chartreuse';
                    break;
            }
            if ($arr['downloaded'] > 0) {
                $ratio = number_format($arr['uploaded'] / $arr['downloaded'], 3);
                $ratio = "<span style='color: " . get_ratio_color($ratio) . ";'><b>{$lang['userdetails_s_ratio']}</b><br>$ratio</span>";
            } elseif ($arr['uploaded'] > 0) {
                $ratio = $lang['userdetails_inf'];
            } else {
                $ratio = 'N/A';
            }
            if (XBT_TRACKER === false) {
                $HTMLOUT .= "<tr><td>" . ($arr['owner'] == $id ? "<b><span style='color: orange'>{$lang['userdetails_s_towner']}</font></b><br>" : '' . ($arr['complete_date'] != '0' ? "<b><font color='lightgreen;'>{$lang['userdetails_s_fin']}</span></b><br>" : "<b><span class='has-text-danger'>{$lang['userdetails_s_nofin']}</span></b><br>") . '') . "<img src='{$site_config['pic_baseurl']}caticons/" . get_categorie_icons() . "/" . htmlsafechars($arr['image']) . "' alt='" . htmlsafechars($arr['name']) . "' title='" . htmlsafechars($arr['name']) . "' /></td>" . "
    <td><a class='altlink' href='{$site_config['baseurl']}/details.php?id=" . (int)$arr['torrentid'] . "'><b>" . htmlsafechars($arr['torrent_name']) . '</b></a>' . ($arr['complete_date'] != '0' ? "<br><span style='color: yellow'>{$lang['userdetails_s_started']}" . get_date($arr['start_date'], 0, 1) . '</font><br>' : "<font color='yellow'>{$lang['userdetails_s_started']}" . get_date($arr['start_date'], 0, 1) . "</font><br><font color='orange'>{$lang['userdetails_s_laction']}" . get_date($arr['last_action'], 0, 1) . '</font>' . get_date($arr['complete_date'], 0, 1) . ' ' . ($arr['complete_date'] == '0' ? '' . ($arr['owner'] == $id ? '' : '[ ' . mksize($arr['size'] - $arr['downloaded']) . "{$lang['userdetails_s_still']}]") . '' : '') . '') . $lang['userdetails_s_finished'] . get_date($arr['complete_date'], 0, 1) . '' . ($arr['complete_date'] != '0' ? "<br><font color='silver'>{$lang['userdetails_s_ttod']}" . ($arr['leechtime'] != '0' ? mkprettytime($arr['leechtime']) : mkprettytime($arr['c'] - $arr['s']) . '') . "</font> <font color='$dlc'>[ {$lang['userdetails_s_dled']} $dl_speed ]</font><br>" : '<br>') . "<font color='lightblue'>" . ($arr['seedtime'] != '0' ? $lang['userdetails_s_tseed'] . mkprettytime($arr['seedtime']) . " </font><font color='$dlc'> " : $lang['userdetails_s_tseedn']) . "</font><font color='lightgreen'> [ {$lang['userdetails_s_uspeed']} " . $ul_speed . ' ] </font>' . ($arr['complete_date'] == '0' ? "<br><font color='$dlc;'>{$lang['userdetails_s_dspeed']}$dl_speed</span>" : '') . '</td>' . "
    <td>{$lang['userdetails_s_seed']}" . (int)$arr['seeders'] . "<br>{$lang['userdetails_s_leech']}" . (int)$arr['leechers'] . "</td><td><span style='color: lightgreen'>{$lang['userdetails_s_upld']}<br><b>" . mksize($arr['uploaded']) . '</b></font>' . ($site_config['ratio_free'] ? '' : "<br><font color='orange'>{$lang['userdetails_s_dld']}<br><b>" . mksize($arr['downloaded']) . '</b></font>') . "</td><td>" . mksize($arr['size']) . '' . ($site_config['ratio_free'] ? '' : "<br>{$lang['userdetails_s_diff']}<br><font color='orange'><b>" . mksize($arr['size'] - $arr['downloaded']) . '</b></font>') . "</td><td>" . $ratio . '<br>' . ($arr['seeder'] == 'yes' ? "<font color='lightgreen'><b>{$lang['userdetails_s_seeding']}</b></font>" : "<span class='has-text-danger'><b>{$lang['userdetails_s_nseeding']}</b></font>") . "</td><td>" . htmlsafechars($arr['agent']) . "<br>{$lang['userdetails_s_port']}" . (int)$arr['port'] . '<br>' . ($arr['connectable'] == 'yes' ? "<b>{$lang['userdetails_s_conn']}</b> <font color='lightgreen;'>{$lang['userdetails_yes']}</span>" : "<b>{$lang['userdetails_s_conn']}</b> <font color='red'><b>{$lang['userdetails_no']}</b></span>") . "</td></tr>\n";
            } else {
                $HTMLOUT .= "<tr><td>" . ($arr['owner'] == $id ? "<b><span style='color: orange'>{$lang['userdetails_s_towner']}</font></b><br>" : '' . ($arr['completedtime'] != '0' ? "<b><font color='lightgreen;'>{$lang['userdetails_s_fin']}</span></b><br>" : "<b><span class='has-text-danger'>{$lang['userdetails_s_nofin']}</span></b><br>") . '') . "<img src='{$site_config['pic_baseurl']}caticons/" . get_categorie_icons() . "/" . htmlsafechars($arr['image']) . "' alt='" . htmlsafechars($arr['name']) . "' title='" . htmlsafechars($arr['name']) . "' /></td>" . "
    <td><a class='altlink' href='{$site_config['baseurl']}/details.php?id=" . (int)$arr['fid'] . "'><b>" . htmlsafechars($arr['torrent_name']) . '</b></a>' . ($arr['completedtime'] != '0' ? "<br><span style='color: yellow'>{$lang['userdetails_s_started']}" . get_date($arr['started'], 0, 1) . '</font><br>' : "<font color='yellow'>started:" . get_date($arr['started'], 0, 1) . "</font><br><font color='orange'>{$lang['userdetails_s_laction']}" . get_date($arr['mtime'], 0, 1) . '</font>' . get_date($arr['completedtime'], 0, 1) . ' ' . ($arr['completedtime'] == '0' ? '' . ($arr['owner'] == $id ? '' : '[ ' . mksize($arr['size'] - $arr['downloaded']) . "{$lang['userdetails_s_still']}]") . '' : '') . '') . "{$lang['userdetails_s_finished']}" . get_date($arr['completedtime'], 0, 1) . '' . ($arr['completedtime'] != '0' ? "<br><font color='silver'>{$lang['userdetails_s_ttod']}" . ($arr['leechtime'] != '0' ? mkprettytime($arr['leechtime']) : mkprettytime($arr['c'] - $arr['s']) . '') . "</font> <font color='$dlc'>[ {$lang['userdetails_s_dled']} $dl_speed ]</font><br>" : '<br>') . "<font color='lightblue'>" . ($arr['seedtime'] != '0' ? "{$lang['userdetails_s_tseed']}" . mkprettytime($arr['seedtime']) . " </font><font color='$dlc'> " : "{$lang['userdetails_s_tseedn']}") . "</font><font color='lightgreen'> [{$lang['userdetails_s_uspeed']}" . $ul_speed . ' ] </font>' . ($arr['completedtime'] == '0' ? "<br><font color='$dlc;'>{$lang['userdetails_s_dspeed']} $dl_speed</span>" : '') . '</td>' . "
    <td>{$lang['userdetails_s_seed']}" . (int)$arr['seeders'] . "<br>{$lang['userdetails_s_leech']}" . (int)$arr['leechers'] . "</td><td><span style='color: lightgreen'>{$lang['userdetails_s_upld']}<br><b>" . mksize($arr['uploaded']) . '</b></font>' . ($site_config['ratio_free'] ? '' : "<br><font color='orange'>{$lang['userdetails_s_dld']}<br><b>" . mksize($arr['downloaded']) . '</b></font>') . "</td><td>" . mksize($arr['size']) . '' . ($site_config['ratio_free'] ? '' : "<br>{$lang['userdetails_s_diff']}<br><font color='orange'><b>" . mksize($arr['size'] - $arr['downloaded']) . '</b></font>') . "</td><td>" . $ratio . '<br>' . ($arr['active'] == 1 ? "<font color='lightgreen'><b>{$lang['userdetails_s_seeding']}</b></font>" : "<span class='has-text-danger'><b>{$lang['userdetails_s_nseeding']}</b></font>") . "</td><td>" . htmlsafechars($arr['peer_id']) . '<br>' . ($arr['connectable'] == 1 ? "<b>{$lang['userdetails_s_conn']}</b> <font color='lightgreen;'>{$lang['userdetails_yes']}</span>" : "<b>{$lang['userdetails_s_conn']}</b> <font color='red'><b>{$lang['userdetails_no']}</b></span>") . "</td></tr>\n";
            }
        }
        $HTMLOUT .= "</table></td></tr>\n";
    } else {
        $HTMLOUT .= tr("<b>{$lang['userdetails_snatched']}</b><br>", "[ <a href=\"userdetails.php?id=$id&amp;snatched_table=1#snatched_table\" class=\"sublink\">{$lang['userdetails_s_show']}</a> ]  - $count_snatched <span class='has-text-danger'><b>{$lang['userdetails_s_staff']}</b></span>", 1);
    }
}
