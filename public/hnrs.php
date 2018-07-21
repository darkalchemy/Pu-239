<?php

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'user_functions.php';
require_once INCL_DIR . 'bbcode_functions.php';
require_once INCL_DIR . 'html_functions.php';
require_once INCL_DIR . 'function_onlinetime.php';
require_once CLASS_DIR . 'class_user_options.php';
require_once CLASS_DIR . 'class_user_options_2.php';
check_user_status();
global $site_config, $CURUSER, $lang, $cache, $session;

$HTMLOUT = '';

$lang = array_merge(load_language('global'), load_language('userdetails'));

if ($CURUSER['class'] < UC_MIN) {
    stderr('Sorry', 'You must be at least a User.');
}

if (isset($_GET['id']) && $CURUSER['class'] >= UC_STAFF) {
    $userid = (int) $_GET['id'];
} else {
    $userid = $CURUSER['id'];
}

$query = 'SELECT seedbonus, uploaded, downloaded, bonuscomment FROM users WHERE id = ' . sqlesc($userid);
$res = sql_query($query) or sqlerr(__FILE__, __LINE__);
$result = mysqli_fetch_assoc($res);

$upload = $result['uploaded'];
$download = $result['downloaded'];
$diff = $upload - $download;
$bonuscomment = $result['bonuscomment'];
if ($CURUSER['id'] === $userid || $CURUSER['class'] >= UC_ADMINISTRATOR) {
    $bp = (int) $result['seedbonus'];
} else {
    $bp = 0;
}
$cost = get_one_row('bonus', 'points', "WHERE bonusname = 'Ratio Fix'");

unset($_GET['bytes']);

if (isset($_GET['torrentid'])) {
    $query = 'SELECT seedbonus, uploaded, downloaded, bonuscomment FROM users WHERE id = ' . sqlesc($userid);
    $res = sql_query($query) or sqlerr(__FILE__, __LINE__);
    $result = mysqli_fetch_assoc($res);

    $upload = $result['uploaded'];
    $download = $result['downloaded'];
    $diff = $upload - $download;
    $bonuscomment = $result['bonuscomment'];
    if ($CURUSER['id'] === $userid || $CURUSER['class'] >= UC_ADMINISTRATOR) {
        $bp = (int) $result['seedbonus'];
    } else {
        $bp = 0;
    }
    $cost = get_one_row('bonus', 'points', "WHERE bonusname = 'Ratio Fix'");
    $seedbonus = $bp - $cost;
    if ($cost > $bp) {
        stderr('Error', "You do not have enough bonus points!<br><br>Back to your <a class='altlink' href='hnrs.php'>Hit and Runs</a> page.");
    }
    $torrent_number = (int) $_GET['torrentid'];
    $res_snatched = sql_query('SELECT s.uploaded, s.downloaded, t.name, t.size FROM snatched AS s LEFT JOIN torrents AS t ON t.id = s.torrentid WHERE s.userid = ' . sqlesc($userid) . ' AND torrentid = ' . sqlesc($torrent_number) . ' LIMIT 1') or sqlerr(__FILE__, __LINE__);
    $arr_snatched = mysqli_fetch_assoc($res_snatched);
    $downloaded = $site_config['ratio_free'] ? (int) $arr_snatched['size'] : (int) $arr_snatched['downloaded'];
    if ($arr_snatched['name'] == '') {
        stderr('Error', "No torrent with that ID!<br>Back to your <a class='altlink' href='hnrs.php'>Hit and Runs</a> page.");
    }
    $download_amt = $site_config['ratio_free'] ? ', downloaded = ".sqlesc($downloaded)."' : '';
    sql_query("UPDATE snatched SET hit_and_run = 0, mark_of_cain = 'no' WHERE userid = " . sqlesc($userid) . ' AND torrentid = ' . sqlesc($torrent_number)) or sqlerr(__FILE__, __LINE__);
    $bonuscomment = get_date(TIME_NOW, 'DATE', 1) . ' - ' . $cost . ' Points for 1 to 1 ratio on torrent: ' . htmlsafechars($arr_snatched['name']) . ' ' . $torrent_number . ".\n " . $bonuscomment;
    sql_query('UPDATE users SET bonuscomment = ' . sqlesc($bonuscomment) . ', seedbonus = ' . sqlesc($seedbonus) . ' WHERE id = ' . sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
    $cache->update_row('user' . $userid, [
        'seedbonus' => $seedbonus,
        'bonuscomment' => $bonuscomment,
    ], $site_config['expires']['user_cache']);
    $cache->delete('userhnrs_' . $userid);
    header('Refresh: 0; url=hnrs.php?userid=' . $userid . '&ratio_success=1');
    die();
}

$completed = $count2 = $dlc = '';
if (!XBT_TRACKER) {
    $r = sql_query(
        "SELECT
                t.name,
                t.added AS torrent_added,
                s.complete_date AS c,
                s.downspeed,
                s.seedtime,
                s.seeder,
                s.torrentid AS tid,
                s.id,
                c.id AS category,
                c.image,
                c.name AS catname,
                s.uploaded,
                s.downloaded,
                s.hit_and_run,
                s.mark_of_cain,
                s.complete_date,
                s.last_action,
                t.seeders,
                t.leechers,
                t.owner,
                t.size,
                s.start_date AS st,
                s.start_date
                FROM snatched AS s
                JOIN torrents AS t ON t.id = s.torrentid
                JOIN categories AS c ON c.id = t.category
                WHERE (hit_and_run != 0 OR mark_of_cain = 'yes') AND s.seeder='no' AND s.finished='yes' AND userid=" . sqlesc($userid) . ' AND t.owner != ' . sqlesc($userid) . '
                ORDER BY s.id DESC'
    ) or sqlerr(__FILE__, __LINE__);
} else {
    $r = sql_query("SELECT torrents.name, torrents.added AS torrent_added, xbt_files_users.started AS st, xbt_files_users.completedtime AS c, xbt_files_users.downspeed, xbt_files_users.seedtime, xbt_files_users.active,
                            xbt_files_users.left, xbt_files_users.fid AS tid, categories.id AS category, categories.image, categories.name AS catname, xbt_files_users.uploaded, xbt_files_users.downloaded, xbt_files_users.hit_and_run,
                            xbt_files_users.mark_of_cain, xbt_files_users.completedtime, xbt_files_users.mtime, xbt_files_users.uid, torrents.seeders, torrents.leechers, torrents.owner, torrents.size
                        FROM xbt_files_users JOIN torrents ON torrents.id = xbt_files_users.fid
                        JOIN categories ON categories.id = torrents.category
                        WHERE xbt_files_users.completed>='1' AND uid=" . sqlesc($userid) . ' AND torrents.owner != ' . sqlesc($userid) . ' ORDER BY xbt_files_users.fid DESC') or sqlerr(__FILE__, __LINE__);
}

//=== completed
$completed .= '<h1>Hit and Runs for: ' . format_username($userid) . '</h1>';
if (mysqli_num_rows($r) > 0) {
    $header = "
        <tr>
            <th>{$lang['userdetails_type']}</th>
            <th>{$lang['userdetails_name']}</th>
            <th class='has-text-center'>{$lang['userdetails_s']}</th>
            <th class='has-text-center'>{$lang['userdetails_l']}</th>
            <th class='has-text-center'>{$lang['userdetails_ul']}</th>
            " . ($site_config['ratio_free'] ? "
            <th class='has-text-center'>{$lang['userdetails_size']}</th>" : "
            <th class='has-text-center'>{$lang['userdetails_dl']}</th>") . "
            <th class='has-text-center'>{$lang['userdetails_ratio']}</th>
            <th class='has-text-center'>{$lang['userdetails_wcompleted']}</th>
            <th class='has-text-center'>{$lang['userdetails_laction']}</th>
            <th class='has-text-center'>{$lang['userdetails_speed']}</th>
            <th class='has-text-center'>Buyout</th>
        </tr>";
    $body = '';
    while ($a = mysqli_fetch_assoc($r)) {
        $What_Id = (XBT_TRACKER ? $a['tid'] : $a['id']);
        $torrent_needed_seed_time = ($a['st'] - $a['torrent_added']);
        switch (true) {
            case $CURUSER['class'] <= $site_config['hnr_config']['firstclass']:
                $days_3 = $site_config['hnr_config']['_3day_first'] * 3600;
                $days_14 = $site_config['hnr_config']['_14day_first'] * 3600;
                $days_over_14 = $site_config['hnr_config']['_14day_over_first'] * 3600;
                break;

            case $CURUSER['class'] < $site_config['hnr_config']['secondclass']:
                $days_3 = $site_config['hnr_config']['_3day_second'] * 3600;
                $days_14 = $site_config['hnr_config']['_14day_second'] * 3600;
                $days_over_14 = $site_config['hnr_config']['_14day_over_second'] * 3600;
                break;

            case $CURUSER['class'] >= $site_config['hnr_config']['thirdclass']:
                $days_3 = $site_config['hnr_config']['_3day_third'] * 3600;
                $days_14 = $site_config['hnr_config']['_14day_third'] * 3600;
                $days_over_14 = $site_config['hnr_config']['_14day_over_third'] * 3600;
                break;

            default:
                $days_3 = $site_config['hnr_config']['_3day_first'] * 3600; //== 1 days
                $days_14 = $site_config['hnr_config']['_14day_first'] * 3600; //== 1 days
                $days_over_14 = $site_config['hnr_config']['_14day_over_first'] * 3600; //== 1 day
        }
        switch (true) {
            case ($a['st'] - $a['torrent_added']) < $site_config['hnr_config']['torrentage1'] * 86400:
                $minus_ratio = ($days_3 - $a['seedtime']);
                break;

            case ($a['st'] - $a['torrent_added']) < $site_config['hnr_config']['torrentage2'] * 86400:
                $minus_ratio = ($days_14 - $a['seedtime']);
                break;

            case ($a['st'] - $a['torrent_added']) >= $site_config['hnr_config']['torrentage3'] * 86400:
                $minus_ratio = ($days_over_14 - $a['seedtime']);
                break;

            default:
                $minus_ratio = ($days_over_14 - $a['seedtime']);
        }
        $color = (($minus_ratio > 0 && $a['uploaded'] < $a['downloaded']) ? get_ratio_color($minus_ratio) : 'limegreen');
        $minus_ratio = mkprettytime($minus_ratio);

        $dl_speed = $a['downloaded'] / ($a['c'] - $a['st'] + 1);
        switch (true) {
            case $dl_speed < 104857:
                $dlc = 'Lime';
                break;
            case $dl_speed < 524288:
                $dlc = 'Chartreuse';
                break;
            case $dl_speed < 1048576:
                $dlc = 'yellow';
                break;
            case $dl_speed < 5242880:
                $dlc = 'orange';
                break;
            case $dl_speed < 10485760:
                $dlc = '#E75480';
                break;
            case $dl_speed > 10485760:
                $dlc = 'red';
                break;
        }

        $dl_speed = mksize($dl_speed);
        //=== mark of cain / hit and run
        $checkbox_for_delete = ($CURUSER['class'] >= UC_STAFF ? " [<a href='" . $site_config['baseurl'] . '/userdetails.php?id=' . $userid . '&amp;delete_hit_and_run=' . (int) $What_Id . "'>{$lang['userdetails_c_remove']}</a>]" : '');
        $mark_of_cain = ($a['mark_of_cain'] === 'yes' ? "<img src='{$site_config['pic_baseurl']}moc.gif' width='40px' alt='{$lang['userdetails_c_mofcain']}' title='{$lang['userdetails_c_tmofcain']}' />" . $checkbox_for_delete : '');
        $hit_n_run = ($a['hit_and_run'] > 0 ? "<img src='{$site_config['pic_baseurl']}hnr.gif' width='40px' alt='{$lang['userdetails_c_hitrun']}' title='{$lang['userdetails_c_hitrun1']}' />" : '');
        $needs_seed = $a['hit_and_run'] + 86400 > time() ? ' in ' . mkprettytime($a['hit_and_run'] + 86400 - time()) : '';

        if ($bp >= $cost && $a['size'] <= 6442450944) {
            $buyout = "<a href='hnrs.php?userid=" . $userid . '&amp;torrentid=' . (int) $a['tid'] . "'><span class='has-text-lime' title='Buyout with Bonus Points'>" . number_format($cost, 0) . ' bp</span></a>';
        } else {
            $buyout = '';
        }

        $a_downloaded = $site_config['ratio_free'] ? (int) $a['size'] : (int) $a['downloaded'];
        $bytes = $a_downloaded - (int) $a['uploaded'];
        if ($diff >= $bytes) {
            $buybytes = "<a href='hnrs.php?userid=" . $userid . '&amp;torrentid=' . (int) $a['tid'] . "&amp;bytes=$bytes'><span class='has-text-lime' title='Buyout with Upload Credit'>" . mksize($bytes) . '</span></a>';
        } else {
            $buybytes = '';
        }

        $or = $buyout != '' && $buybytes != '' ? 'or' : '';
        //            $sucks = $buyout == '' && $buybytes == '' ? "Seed for $minus_ratio" : "or Seed for $minus_ratio";
        $sucks = $buyout == '' ? "Seed for $minus_ratio" : "or Seed for $minus_ratio";

        if (!XBT_TRACKER) {
            $body .= "
        <tr>
            <td style='padding: 5px'><img height='42px' class='tnyrad' src='{$site_config['pic_baseurl']}caticons/{$CURUSER['categorie_icon']}/{$a['image']}' alt='{$a['name']}' title='{$a['name']}' /></td>
            <td align='left'><a class='altlink' href='details.php?id=" . (int) $a['tid'] . "&amp;hit=1'><b>" . htmlsafechars($a['name']) . "</b></a>
                <br><span style='color: .$color.'>  " . (($CURUSER['class'] >= UC_STAFF || $CURUSER['id'] == $userid) ? "{$lang['userdetails_c_seedfor']}</font>: " . mkprettytime($a['seedtime']) . (($minus_ratio != '0:00') ? "<br>{$lang['userdetails_c_should']}" . $minus_ratio . '&#160;&#160;' : '') . ($a['seeder'] === 'yes' ? "&#160;<font color='limegreen;'> [<b>{$lang['userdetails_c_seeding']}</b>]</span>" : $hit_n_run . '&#160;' . $mark_of_cain . $needs_seed) : '') . "
            </td>
            <td class='has-text-center'>" . (int) $a['seeders'] . "</td>
            <td class='has-text-center'>" . (int) $a['leechers'] . "</td>
            <td class='has-text-center'>" . mksize($a['uploaded']) . '</td>
            ' . ($site_config['ratio_free'] ? "<td class='has-text-center'>" . mksize($a['size']) . '</td>' : "<td class='has-text-center'>" . mksize($a['downloaded']) . '</td>') . "
            <td class='has-text-center'>" . ($a['downloaded'] > 0 ? "<span style='color: " . get_ratio_color(number_format($a['uploaded'] / $a['downloaded'], 3)) . ";'>" . number_format($a['uploaded'] / $a['downloaded'], 3) . '</span>' : ($a['uploaded'] > 0 ? 'Inf.' : '---')) . "<br></td>
            <td class='has-text-center'>" . get_date($a['complete_date'], 'DATE') . "</td>
            <td class='has-text-center'>" . get_date($a['last_action'], 'DATE') . "</td>
            <td class='has-text-center'><span style='color: $dlc;'>{$lang['userdetails_c_dled']}<br>{$dl_speed}ps</span></td>
            <td class='has-text-center'>$buyout $sucks</td>
        </tr>";
        } else {
            $body .= "
        <tr>
            <td style='padding: 5px'><img  height='42px' class='tnyrad' src='{$site_config['pic_baseurl']}caticons/{$CURUSER['categorie_icon']}/{$a['image']}' alt='{$a['name']}' title='{$a['name']}' /></td>
            <td align='left'><a class='altlink' href='details.php?id=" . (int) $a['tid'] . "&amp;hit=1'><b>" . htmlsafechars($a['name']) . "</b></a>
                <br><span style='color: .$color.'>  " . (($CURUSER['class'] >= UC_STAFF || $CURUSER['id'] == $userid) ? "{$lang['userdetails_c_seedfor']}</font>: " . mkprettytime($a['seedtime']) . (($minus_ratio != '0:00' && $a['uploaded'] < $a['downloaded']) ? "<br>{$lang['userdetails_c_should']}" . $minus_ratio . '&#160;&#160;' : '') . ($a['active'] == 1 && $a['left'] == 0 ? "&#160;<font color='limegreen;'> [<b>{$lang['userdetails_c_seeding']}</b>]</span>" : $hit_n_run . $needs_seed) : '') . "
            </td>
            <td class='has-text-center'>" . (int) $a['seeders'] . "</td>
            <td class='has-text-center'>" . (int) $a['leechers'] . "</td>
            <td class='has-text-center'>" . mksize($a['uploaded']) . '</td>
            ' . ($site_config['ratio_free'] ? "<td class='has-text-center'>" . mksize($a['size']) . '</td>' : "<td class='has-text-center'>" . mksize($a['downloaded']) . '</td>') . "
            <td class='has-text-center'>" . ($a['downloaded'] > 0 ? "<span style='color: " . get_ratio_color(number_format($a['uploaded'] / $a['downloaded'], 3)) . ";'>" . number_format($a['uploaded'] / $a['downloaded'], 3) . '</span>' : ($a['uploaded'] > 0 ? $lang['userdetails_c_inf'] : '---')) . "<br></td>
            <td class='has-text-center'>" . get_date($a['completedtime'], 'DATE') . "</td>
            <td class='has-text-center'>" . get_date($a['mtime'], 'DATE') . "</td>
            <td class='has-text-center'><span style='color: $dlc;'>[{$lang['userdetails_c_dled']}$dl_speed ]</span></td>
            <td class='has-text-center'>$buyout $sucks</td>
        </tr>";
        }
    }
    $completed .= main_table($body, $header);
} else {
    $session->set('is-success', format_username($userid) . " {$lang['userdetails_no_hnrs']}");
}
echo stdhead('HnRs') . wrapper($completed) . stdfoot();
