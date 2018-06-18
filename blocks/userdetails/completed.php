<?php

global $CURUSER, $site_config, $user;

if ($site_config['hnr_config']['hnr_online'] == 1 && $user['paranoia'] < 2 || $CURUSER['id'] == $id || $CURUSER['class'] >= (UC_MIN + 1)) {
    $completed = $count2 = $dlc = '';
    if (!XBT_TRACKER) {
        $r = sql_query("SELECT torrents.name, torrents.added AS torrent_added, snatched.complete_date AS c, snatched.downspeed, snatched.seedtime, snatched.seeder, snatched.torrentid AS tid, snatched.id, categories.id AS category, categories.image, categories.name AS catname, snatched.uploaded, snatched.downloaded, snatched.hit_and_run, snatched.mark_of_cain, snatched.complete_date, snatched.last_action, torrents.seeders, torrents.leechers, torrents.owner, snatched.start_date AS st, snatched.start_date FROM snatched JOIN torrents ON torrents.id = snatched.torrentid JOIN categories ON categories.id = torrents.category WHERE snatched.finished = 'yes' AND userid = " . sqlesc($id) . ' AND torrents.owner != ' . sqlesc($id) . ' ORDER BY snatched.id DESC') or sqlerr(__FILE__, __LINE__);
    } else {
        $r = sql_query("SELECT torrents.name, torrents.added AS torrent_added, xbt_files_users.started AS st, xbt_files_users.completedtime AS c, xbt_files_users.downspeed, xbt_files_users.seedtime, xbt_files_users.active, xbt_files_users.left, xbt_files_users.fid AS tid, categories.id AS category, categories.image, categories.name AS catname, xbt_files_users.uploaded, xbt_files_users.downloaded, xbt_files_users.hit_and_run, xbt_files_users.mark_of_cain, xbt_files_users.completedtime, xbt_files_users.mtime, xbt_files_users.uid, torrents.seeders, torrents.leechers, torrents.owner FROM xbt_files_users JOIN torrents ON torrents.id = xbt_files_users.fid JOIN categories ON categories.id = torrents.category WHERE xbt_files_users.completed >= '1' AND uid = " . sqlesc($id) . ' AND torrents.owner != ' . sqlesc($id) . ' ORDER BY xbt_files_users.fid DESC') or sqlerr(__FILE__, __LINE__);
    }
    if (mysqli_num_rows($r) > 0) {
        $heading .= "
        <tr>
            <th>{$lang['userdetails_type']}</th>
            <th>{$lang['userdetails_name']}</th>
            <th>{$lang['userdetails_s']}</th>
            <th>{$lang['userdetails_l']}</th>
            <th>{$lang['userdetails_ul']}</th>
            " . ($site_config['ratio_free'] ? '' : "
            <th>{$lang['userdetails_dl']}</th>") . "
            <th>{$lang['userdetails_ratio']}</th>
            <th>{$lang['userdetails_wcompleted']}</th>
            <th>{$lang['userdetails_laction']}</th>
            <th>{$lang['userdetails_speed']}</th>
        </tr>";
        $body = '';
        while ($a = mysqli_fetch_assoc($r)) {
            $What_Id = (XBT_TRACKER ? $a['tid'] : $a['id']);
            $torrent_needed_seed_time = ($a['st'] - $a['torrent_added']);
            switch (true) {
                case $user['class'] <= $site_config['hnr_config']['firstclass']:
                    $days_3 = $site_config['hnr_config']['_3day_first'] * 3600;
                    $days_14 = $site_config['hnr_config']['_14day_first'] * 3600;
                    $days_over_14 = $site_config['hnr_config']['_14day_over_first'] * 3600;
                    break;
                case $user['class'] < $site_config['hnr_config']['secondclass']:
                    $days_3 = $site_config['hnr_config']['_3day_second'] * 3600;
                    $days_14 = $site_config['hnr_config']['_14day_second'] * 3600;
                    $days_over_14 = $site_config['hnr_config']['_14day_over_second'] * 3600;
                    break;
                case $user['class'] >= $site_config['hnr_config']['secondclass'] && $user['class'] < $site_config['hnr_config']['thirdclass']:
                    $days_3 = $site_config['hnr_config']['_3day_second'] * 3600;
                    $days_14 = $site_config['hnr_config']['_14day_second'] * 3600;
                    $days_over_14 = $site_config['hnr_config']['_14day_over_second'] * 3600;
                    break;
                case $user['class'] >= $site_config['hnr_config']['thirdclass']:
                    $days_3 = $site_config['hnr_config']['_3day_third'] * 3600;
                    $days_14 = $site_config['hnr_config']['_14day_third'] * 3600;
                    $days_over_14 = $site_config['hnr_config']['_14day_over_third'] * 3600;
                    break;
                default:
                    $days_3 = 0;
                    $days_14 = 0;
                    $days_over_14 = 0;
            }
            $foo = $a['downloaded'] > 0 ? $a['uploaded'] / $a['downloaded'] : 0;
            switch (true) {
                case ($a['st'] - $a['torrent_added']) < $site_config['hnr_config']['torrentage1'] * 86400:
                    $minus_ratio = ($days_3 - $a['seedtime']) - ($foo * 3 * 86400);
                    break;

                case ($a['st'] - $a['torrent_added']) < $site_config['hnr_config']['torrentage2'] * 86400:
                    $minus_ratio = ($days_14 - $a['seedtime']) - ($foo * 2 * 86400);
                    break;

                case ($a['st'] - $a['torrent_added']) >= $site_config['hnr_config']['torrentage3'] * 86400:
                    $minus_ratio = ($days_over_14 - $a['seedtime']) - ($foo * 86400);
                    break;
            }
            $foo = $a['downloaded'] > 0 ? $a['uploaded'] / $a['downloaded'] : 0;
            switch (true) {
                case ($a['st'] - $a['torrent_added']) < 7 * 86400:
                    $minus_ratio = ($days_3 - $a['seedtime']) - ($foo * 3 * 86400);
                    break;

                case ($a['st'] - $a['torrent_added']) < 21 * 86400:
                    $minus_ratio = ($days_14 - $a['seedtime']) - ($foo * 2 * 86400);
                    break;

                case ($a['st'] - $a['torrent_added']) >= 21 * 86400:
                    $minus_ratio = ($days_over_14 - $a['seedtime']) - ($foo * 86400);
                    break;
            }
            $color = (($minus_ratio > 0 && $a['uploaded'] < $a['downloaded']) ? get_ratio_color($minus_ratio) : 'limegreen');
            $minus_ratio = mkprettytime($minus_ratio);
            if ($a['downspeed'] > 0) {
                $dl_speed = ($a['downspeed'] > 0 ? mksize($a['downspeed']) : ($a['leechtime'] > 0 ? mksize($a['downloaded'] / $a['leechtime']) : mksize(0)));
            } else {
                $dl_speed = mksize(($a['downloaded'] / ($a['c'] - $a['st'] + 1)));
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
            $checkbox_for_delete = ($CURUSER['class'] >= UC_STAFF ? " [<a href='" . $site_config['baseurl'] . '/userdetails.php?id=' . $id . '&amp;delete_hit_and_run=' . (int) $What_Id . "'>{$lang['userdetails_c_remove']}</a>]" : '');
            $mark_of_cain = ($a['mark_of_cain'] == 'yes' ? "<img src='{$site_config['pic_baseurl']}moc.gif' width='40px' alt='{$lang['userdetails_c_mofcain']}' title='{$lang['userdetails_c_tmofcain']}' />" . $checkbox_for_delete : '');
            $hit_n_run = ($a['hit_and_run'] > 0 ? "<img src='{$site_config['pic_baseurl']}hnr.gif' width='40px' alt='{$lang['userdetails_c_hitrun']}' title='{$lang['userdetails_c_hitrun1']}' />" : '');
            if (!XBT_TRACKER) {
                $body .= "
            <tr>
                <td style='padding: 0;'><img src='{$site_config['pic_baseurl']}caticons/" . get_category_icons() . "/{$a['image']}' alt='{$a['name']}' title='{$a['name']}' /></td>
                <td>
                    <a class='altlink' href='{$site_config['baseurl']}/details.php?id=" . (int) $a['tid'] . "&amp;hit=1'><b>" . htmlsafechars($a['name']) . '</b></a>
                    <br><span>  ' . (($CURUSER['class'] >= UC_STAFF || $user['id'] == $CURUSER['id']) ? "{$lang['userdetails_c_seedfor']}</span>: " . mkprettytime($a['seedtime']) . (($minus_ratio != '0:00' && $a['uploaded'] < $a['downloaded']) ? "<br>{$lang['userdetails_c_should']}" . $minus_ratio . '&#160;&#160;' : '') . ($a['seeder'] === 'yes' ? "&#160;<span class='has-text-lime'> [<b>{$lang['userdetails_c_seeding']}</b>]</span>" : $hit_n_run . '&#160;' . $mark_of_cain) : '') . '</td>
                <td>' . (int) $a['seeders'] . '</td>
                <td>' . (int) $a['leechers'] . '</td>
                <td>' . mksize($a['uploaded']) . '</td>
                ' . ($site_config['ratio_free'] ? '' : '
                <td>' . mksize($a['downloaded']) . '</td>') . '
                <td>' . ($a['downloaded'] > 0 ? "<span style='color: " . get_ratio_color(number_format($a['uploaded'] / $a['downloaded'], 3)) . ";'>" . number_format($a['uploaded'] / $a['downloaded'], 3) . '</span>' : ($a['uploaded'] > 0 ? 'Inf.' : '---')) . '<br></td>
                <td>' . get_date($a['complete_date'], 'DATE') . '</td>
                <td>' . get_date($a['last_action'], 'DATE') . "</td>
                <td><span style='color: $dlc;'>[{$lang['userdetails_c_dled']}$dl_speed ]</span></td>
            </tr>";
            } else {
                $body .= "
            <tr>
                <td style='padding: 0;'><img src='{$site_config['pic_baseurl']}caticons/" . get_category_icons() . "/{$a['image']}' alt='{$a['name']}' title='{$a['name']}' /></td>
                <td><a class='altlink' href='{$site_config['baseurl']}/details.php?id=" . (int) $a['tid'] . "&amp;hit=1'><b>" . htmlsafechars($a['name']) . '</b></a>
                <br><span>  ' . (($CURUSER['class'] >= UC_STAFF || $user['id'] == $CURUSER['id']) ? "{$lang['userdetails_c_seedfor']}</span>: " . mkprettytime($a['seedtime']) . (($minus_ratio != '0:00' && $a['uploaded'] < $a['downloaded']) ? "<br>{$lang['userdetails_c_should']}" . $minus_ratio . '&#160;&#160;' : '') . ($a['active'] == 1 && $a['left'] == 0 ? "&#160;<span class='has-text-lime'> [<b>{$lang['userdetails_c_seeding']}</b>]</span>" : $hit_n_run) : '') . '</td>
                <td>' . (int) $a['seeders'] . '</td>
                <td>' . (int) $a['leechers'] . '</td>
                <td>' . mksize($a['uploaded']) . '</td>
                ' . ($site_config['ratio_free'] ? '' : '
                <td>' . mksize($a['downloaded']) . '</td>') . '
                <td>' . ($a['downloaded'] > 0 ? "<span style='color: " . get_ratio_color(number_format($a['uploaded'] / $a['downloaded'], 3)) . ";'>" . number_format($a['uploaded'] / $a['downloaded'], 3) . '</span>' : ($a['uploaded'] > 0 ? $lang['userdetails_c_inf'] : '---')) . '<br></td>
                <td>' . get_date($a['completedtime'], 'DATE') . '</td>
                <td>' . get_date($a['mtime'], 'DATE') . "</td>
                <td><span style='color: $dlc;'>[{$lang['userdetails_c_dled']}$dl_speed ]</span></td>
            </tr>";
            }
        }
        $completed = main_table($body, $heading);
    }

    if (($completed && $CURUSER['class'] >= (UC_MIN + 1)) || ($completed && $user['id'] == $CURUSER['id'])) {
        if (!isset($_GET['completed'])) {
            $table_data .= "
            <tr>
                <td>Completed Torrents</td>
                <td>
                <a id='completed-torrents-hash'></a>
                <fieldset id='completed-torrents' class='header'>
                    <legend class='flipper size_4'><i class='fa icon-down-open' aria-hidden='true'></i>View Completed Torrents</legend>
                    $completed
                </fieldset>
                </td>
            </tr>";
        }
    }
}
