<?php

global $CURUSER, $site_config, $lang;

$count_snatched = $count2 = $dlc = '';
if ($CURUSER['class'] >= UC_STAFF) {
    $heading = $body = '';
    $table_data .= "
            <tr>
                <td>
                    {$lang['userdetails_snatched']}
                </td>
                <td>
                    <a id='snatched-staff-hash'></a>
                    <fieldset id='snatched-staff' class='header'>
                        <legend class='flipper size_4'><i class='fa icon-down-open' aria-hidden='true'></i><span class='has-text-red'>*Staff Only*</span> View Snatched Torrents</legend>";
    if (!XBT_TRACKER) {
        $res = sql_query('SELECT sn.start_date AS s, sn.complete_date AS c, sn.last_action AS l_a, sn.seedtime AS s_t, sn.seedtime, sn.leechtime AS l_t, sn.leechtime, sn.downspeed, sn.upspeed, sn.uploaded, sn.downloaded, sn.torrentid, sn.start_date, sn.complete_date, sn.seeder, sn.last_action, sn.connectable, sn.agent, sn.seedtime, sn.port, cat.name, cat.image, t.size, t.seeders, t.leechers, t.owner, t.name AS torrent_name ' . 'FROM snatched AS sn ' . 'LEFT JOIN torrents AS t ON t.id = sn.torrentid ' . 'LEFT JOIN categories AS cat ON cat.id = t.category ' . 'WHERE sn.userid=' . sqlesc($id) . ' ORDER BY sn.start_date DESC LIMIT 0, 15') or sqlerr(__FILE__, __LINE__);
    } else {
        $res = sql_query('SELECT x.started AS s, x.completedtime AS c, x.mtime AS l_a, x.seedtime AS s_t, x.seedtime, x.leechtime AS l_t, x.leechtime, x.downspeed, x.upspeed, x.uploaded, x.downloaded, x.fid, x.started, x.completedtime, x.active, x.mtime, x.connectable, x.peer_id, cat.name, cat.image, t.size, t.seeders, t.leechers, t.owner, t.name AS torrent_name ' . 'FROM xbt_files_users AS x ' . 'LEFT JOIN torrents AS t ON t.id = x.fid ' . 'LEFT JOIN categories AS cat ON cat.id = t.category ' . 'WHERE x.uid=' . sqlesc($id) . ' ORDER BY x.started DESC LIMIT 0, 15') or sqlerr(__FILE__, __LINE__);
    }
    $heading .= "
                    <tr>
                        <th>{$lang['userdetails_s_cat']}</th>
                        <th>{$lang['userdetails_s_torr']}</th>
                        <th>{$lang['userdetails_s_sl']}</th>
                        <th>{$lang['userdetails_s_up']}" . ($site_config['ratio_free'] ? '' : "{$lang['userdetails_s_down']}") . "</th>
                        <th>{$lang['userdetails_s_tsize']}</th>
                        <th>{$lang['userdetails_ratio']}</th>
                        <th>{$lang['userdetails_client']}</th>
                    </tr>";
    while ($arr = mysqli_fetch_assoc($res)) {
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
        if (!XBT_TRACKER) {
            $body .= '
            <tr>
                <td>' . ($arr['owner'] == $id ? "
                    <b><span class='has-text-orange'>{$lang['userdetails_s_towner']}</span></b><br>" : '' . ('0' != $arr['complete_date'] ? "
                    <b><span class='has-text-lightgreen'>{$lang['userdetails_s_fin']}</span></b><br>" : "
                    <b><span class='has-text-red'>{$lang['userdetails_s_nofin']}</span></b><br>") . '') . "
                    <img src='{$site_config['pic_baseurl']}caticons/" . get_category_icons() . '/' . htmlsafechars($arr['image']) . "' alt='" . htmlsafechars($arr['name']) . "' title='" . htmlsafechars($arr['name']) . "' class='tooltipper' />
                </td>
                <td>
                    <a class='altlink' href='{$site_config['baseurl']}/details.php?id=" . (int) $arr['torrentid'] . "'><b>" . htmlsafechars($arr['torrent_name']) . '</b></a>' . ('0' != $arr['complete_date'] ? "<br>
                    <span class='has-text-yellow'>{$lang['userdetails_s_started']}" . get_date($arr['start_date'], 0, 1) . "</span><br>
                    <span class='has-text-orange'>{$lang['userdetails_s_laction']} " . get_date($arr['last_action'], 0, 1) . '</span>' .
                    ('0' == $arr['complete_date'] ? ($arr['owner'] == $id ? '' : '[ ' . mksize($arr['size'] - $arr['downloaded']) . "{$lang['userdetails_s_still']}]") : '') : '') . '<br>' . $lang['userdetails_s_finished'] . get_date($arr['complete_date'], 0, 1) . '' . ('0' != $arr['complete_date'] ? "<br>
                    <span style='color: silver;'>{$lang['userdetails_s_ttod']}" . ('0' != $arr['leechtime'] ? mkprettytime($arr['leechtime']) : mkprettytime($arr['c'] - $arr['s']) . '') . "</span>
                    <span style='color: $dlc'>[ {$lang['userdetails_s_dled']} $dl_speed ]</span><br>" : '<br>') . "
                    <span class='has-text-lightblue'>" . ('0' != $arr['seedtime'] ? $lang['userdetails_s_tseed'] . mkprettytime($arr['seedtime']) . " </span>
                    <span style='color: $dlc;'> " : $lang['userdetails_s_tseedn']) . "</span>
                    <span class='has-text-lightgreen'> [ {$lang['userdetails_s_uspeed']} " . $ul_speed . ' ] </span>' . ('0' == $arr['complete_date'] ? "<br>
                    <span style='color: $dlc;'>{$lang['userdetails_s_dspeed']}$dl_speed</span>" : '') . "
                </td>
                <td>{$lang['userdetails_s_seed']}" . (int) $arr['seeders'] . "<br>{$lang['userdetails_s_leech']}" . (int) $arr['leechers'] . "</td>
                <td>
                    <span class='has-text-lightgreen'>{$lang['userdetails_s_upld']}<br><b>" . mksize($arr['uploaded']) . '</b></span>' . ($site_config['ratio_free'] ? '' : "<br>
                    <span class='has-text-orange'>{$lang['userdetails_s_dld']}<br><b>" . mksize($arr['downloaded']) . '</b></span>') . '
                </td>
                <td>' . mksize($arr['size']) . '' . ($site_config['ratio_free'] ? '' : "<br>{$lang['userdetails_s_diff']}<br>
                    <span class='has-text-orange'><b>" . mksize($arr['size'] - $arr['downloaded']) . '</b></span>') . '
                </td>
                <td>' . $ratio . '<br>' . ('yes' == $arr['seeder'] ? "
                    <span class='has-text-lightgreen'><b>{$lang['userdetails_s_seeding']}</b></span>" : "
                    <span class='has-text-red'><b>{$lang['userdetails_s_nseeding']}</b></span>") . '
                </td>
                <td>' . htmlsafechars($arr['agent']) . "<br>{$lang['userdetails_s_port']}" . (int) $arr['port'] . '<br>' . ('yes' == $arr['connectable'] ? "<b>{$lang['userdetails_s_conn']}</b> 
                    <span class='has-text-lightgreen'>{$lang['userdetails_yes']}</span>" : "<b>{$lang['userdetails_s_conn']}</b> 
                    <span class='has-text-red'><b>{$lang['userdetails_no']}</b></span>") . '
                </td>
            </tr>';
        } else {
            $body .= '
            <tr>
                <td>' . ($arr['owner'] == $id ? "<b><span class='has-text-orange'>{$lang['userdetails_s_towner']}</span></b><br>" : '' . ('0' != $arr['completedtime'] ? "<b><span class='has-text-lightgreen'>{$lang['userdetails_s_fin']}</span></b><br>" : "<b><span class='has-text-red'>{$lang['userdetails_s_nofin']}</span></b><br>") . '') . "<img src='{$site_config['pic_baseurl']}caticons/" . get_category_icons() . '/' . htmlsafechars($arr['image']) . "' alt='" . htmlsafechars($arr['name']) . "' title='" . htmlsafechars($arr['name']) . "' /></td>" . "
                <td><a class='altlink' href='{$site_config['baseurl']}/details.php?id=" . (int) $arr['fid'] . "'><b>" . htmlsafechars($arr['torrent_name']) . '</b></a>' . ('0' != $arr['completedtime'] ? "<br><span style='color: yellow'>{$lang['userdetails_s_started']}" . get_date($arr['started'], 0, 1) . '</span><br>' : "<span color='yellow'>started:" . get_date($arr['started'], 0, 1) . "</span><br><span class='has-text-orange'>{$lang['userdetails_s_laction']}" . get_date($arr['mtime'], 0, 1) . '</span>' . get_date($arr['completedtime'], 0, 1) . ' ' . ('0' == $arr['completedtime'] ? '' . ($arr['owner'] == $id ? '' : '[ ' . mksize($arr['size'] - $arr['downloaded']) . "{$lang['userdetails_s_still']}]") . '' : '') . '') . "{$lang['userdetails_s_finished']}" . get_date($arr['completedtime'], 0, 1) . '' . ('0' != $arr['completedtime'] ? "<br><span color='silver'>{$lang['userdetails_s_ttod']}" . ('0' != $arr['leechtime'] ? mkprettytime($arr['leechtime']) : mkprettytime($arr['c'] - $arr['s']) . '') . "</span> <span color='$dlc'>[ {$lang['userdetails_s_dled']} $dl_speed ]</span><br>" : '<br>') . "<span color='lightblue'>" . ('0' != $arr['seedtime'] ? "{$lang['userdetails_s_tseed']}" . mkprettytime($arr['seedtime']) . " </span><span color='$dlc'> " : "{$lang['userdetails_s_tseedn']}") . "</span><span class='has-text-lightgreen'> [{$lang['userdetails_s_uspeed']}" . $ul_speed . ' ] </span>' . ('0' == $arr['completedtime'] ? "<br><span color='$dlc;'>{$lang['userdetails_s_dspeed']} $dl_speed</span>" : '') . '</td>' . "
                <td>{$lang['userdetails_s_seed']}" . (int) $arr['seeders'] . "<br>{$lang['userdetails_s_leech']}" . (int) $arr['leechers'] . "</td><td><span style='color: lightgreen'>{$lang['userdetails_s_upld']}<br><b>" . mksize($arr['uploaded']) . '</b></span>' . ($site_config['ratio_free'] ? '' : "<br><span class='has-text-orange'>{$lang['userdetails_s_dld']}<br><b>" . mksize($arr['downloaded']) . '</b></span>') . '</td><td>' . mksize($arr['size']) . '' . ($site_config['ratio_free'] ? '' : "<br>{$lang['userdetails_s_diff']}<br><span class='has-text-orange'><b>" . mksize($arr['size'] - $arr['downloaded']) . '</b></span>') . '</td><td>' . $ratio . '<br>' . (1 == $arr['active'] ? "<span class='has-text-lightgreen'><b>{$lang['userdetails_s_seeding']}</b></span>" : "<span class='has-text-red'><b>{$lang['userdetails_s_nseeding']}</b></span>") . '</td><td>' . htmlsafechars($arr['peer_id']) . '<br>' . (1 == $arr['connectable'] ? "<b>{$lang['userdetails_s_conn']}</b> <span class='has-text-lightgreen'>{$lang['userdetails_yes']}</span>" : "<b>{$lang['userdetails_s_conn']}</b> <span class='has-text-red'><b>{$lang['userdetails_no']}</b></span>") . '</td>
            </tr>';
        }
    }
    $table_data .= main_table($body, $heading);
    $table_data .= '
                </fieldset>
            </td>
        </tr>';
}
