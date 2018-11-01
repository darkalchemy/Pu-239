<?php

require_once INCL_DIR . 'user_functions.php';
require_once INCL_DIR . 'html_functions.php';
require_once INCL_DIR . 'pager_functions.php';
require_once CLASS_DIR . 'class_check.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
global $site_config, $lang;

$lang = array_merge($lang, load_language('ad_snatched_torrents'));
$HTMLOUT = '';
/**
 * @param $st
 *
 * @return string
 */
function get_snatched_color($st)
{
    global $lang;
    $secs = $st;
    $mins = floor($st / 60);
    $hours = floor($mins / 60);
    $days = floor($hours / 24);
    $week = floor($days / 7);
    $month = floor($week / 4);
    if ($month > 0) {
        $week_elapsed = floor(($st - ($month * 4 * 7 * 24 * 60 * 60)) / (7 * 24 * 60 * 60));
        $days_elapsed = floor(($st - ($week * 7 * 24 * 60 * 60)) / (24 * 60 * 60));
        //$hours_elapsed = floor(($st - ($days * 24 * 60 * 60)) / (60 * 60));
        //$mins_elapsed = floor(($st - ($hours * 60 * 60)) / 60);
        //$secs_elapsed = floor($st - $mins * 60);

        return "<span style='color: lime;'><b>$month months.<br>$week_elapsed W. $days_elapsed D.</b></span>";
    }
    if ($week > 0) {
        $days_elapsed = floor(($st - ($week * 7 * 24 * 60 * 60)) / (24 * 60 * 60));
        $hours_elapsed = floor(($st - ($days * 24 * 60 * 60)) / (60 * 60));
        $mins_elapsed = floor(($st - ($hours * 60 * 60)) / 60);
        $secs_elapsed = floor($st - $mins * 60);

        return "<span style='color: lime;'><b>$week W. $days_elapsed D.<br>$hours_elapsed:$mins_elapsed:$secs_elapsed</b></span>";
    }
    if ($days > 2) {
        $hours_elapsed = floor(($st - ($days * 24 * 60 * 60)) / (60 * 60));
        $mins_elapsed = floor(($st - ($hours * 60 * 60)) / 60);
        $secs_elapsed = floor($st - $mins * 60);

        return "<span style='color: lime;'><b>$days D.<br>$hours_elapsed:$mins_elapsed:$secs_elapsed</b></span>";
    }
    if ($days > 1) {
        $hours_elapsed = floor(($st - ($days * 24 * 60 * 60)) / (60 * 60));
        $mins_elapsed = floor(($st - ($hours * 60 * 60)) / 60);
        $secs_elapsed = floor($st - $mins * 60);

        return "<span style='color: green;'><b>$days D.<br>$hours_elapsed:$mins_elapsed:$secs_elapsed</b></span>";
    }
    if ($days > 0) {
        $hours_elapsed = floor(($st - ($days * 24 * 60 * 60)) / (60 * 60));
        $mins_elapsed = floor(($st - ($hours * 60 * 60)) / 60);
        $secs_elapsed = floor($st - $mins * 60);

        return "<span style='color: #CCFFCC;'><b>$days D.<br>$hours_elapsed:$mins_elapsed:$secs_elapsed</b></span>";
    }
    if ($hours > 12) {
        $mins_elapsed = floor(($st - ($hours * 60 * 60)) / 60);
        $secs_elapsed = floor($st - $mins * 60);

        return "<span style='color: yellow;'><b>$hours:$mins_elapsed:$secs_elapsed</b></span>";
    }
    if ($hours > 0) {
        $mins_elapsed = floor(($st - ($hours * 60 * 60)) / 60);
        $secs_elapsed = floor($st - $mins * 60);

        return "<span class='has-text-danger'><b>$hours:$mins_elapsed:$secs_elapsed</b></span>";
    }
    if ($mins > 0) {
        $secs_elapsed = floor($st - $mins * 60);

        return "<span class='has-text-danger'><b>0:$mins:$secs_elapsed</b></span>";
    }
    if ($secs > 0) {
        return "<span class='has-text-danger'><b>0:0:$secs</b></span>";
    }

    return "<span class='has-text-danger'><b>{$lang['ad_snatched_torrents_none']}<br>{$lang['ad_snatched_torrents_reported']}</b></span>";
}

$What_Table = (XBT_TRACKER ? 'xbt_files_users' : 'snatched');
$What_Value = (XBT_TRACKER ? 'WHERE completedtime != "0"' : 'WHERE complete_date != "0"');
$count = number_format(get_row_count($What_Table, $What_Value));
$HTMLOUT .= "<h2>{$lang['ad_snatched_torrents_allsnatched']}</h2>
<font class='small'>{$lang['ad_snatched_torrents_currently']}&#160;" . htmlsafechars($count) . "&#160;{$lang['ad_snatched_torrents_snatchedtor']}</font>";
$HTMLOUT .= begin_main_frame();
$Which_ID = (XBT_TRACKER ? 'fid' : 'id');
$Which_Table = (XBT_TRACKER ? 'xbt_files_users' : 'snatched');
$res = sql_query("SELECT COUNT($Which_ID) FROM $Which_Table") or sqlerr(__FILE__, __LINE__);
$row = mysqli_fetch_row($res);
$count = $row[0];
$snatchedperpage = 15;
$pager = pager($snatchedperpage, $count, 'staffpanel.php?tool=snatched_torrents&amp;action=snatched_torrents&amp;');
if ($count > $snatchedperpage) {
    $HTMLOUT .= $pager['pagertop'];
}
if (XBT_TRACKER) {
    $sql = 'SELECT x.uid, x.fid, x.announced, x.completedtime, x.leechtime, x.seedtime, x.uploaded, x.downloaded, x.started, u.username, t.seeders, t.name ' . 'FROM xbt_files_users AS x ' . 'LEFT JOIN users AS u ON u.id=x.uid ' . "LEFT JOIN torrents AS t ON t.id=x.fid WHERE completedtime != '0'" . ' ORDER BY x.completedtime DESC ' . $pager['limit'];
} else {
    $sql = 'SELECT sn.userid, sn.id, sn.torrentid, sn.timesann, sn.hit_and_run, sn.mark_of_cain, sn.uploaded, sn.downloaded, sn.start_date, sn.complete_date, sn.seeder, sn.leechtime, sn.seedtime, u.username, t.name ' . 'FROM snatched AS sn ' . 'LEFT JOIN users AS u ON u.id=sn.userid ' . "LEFT JOIN torrents AS t ON t.id=sn.torrentid WHERE complete_date != '0'" . 'ORDER BY sn.complete_date DESC ' . $pager['limit'];
}
$result = sql_query($sql) or sqlerr(__FILE__, __LINE__);
if (mysqli_num_rows($result) != 0) {
    if (XBT_TRACKER) {
        $HTMLOUT .= "<table width='100%' >
<tr>
<td class='colhead' width='1%'>{$lang['ad_snatched_torrents_name']}</td>
<td class='colhead' width='1%'>{$lang['ad_snatched_torrents_torname']}</td>
<td class='colhead' width='1%'>{$lang['ad_snatched_torrents_announced']}</td>
<td class='colhead' width='1%'>{$lang['ad_snatched_torrents_upload']}</td>
" . (RATIO_FREE ? '' : "<td class='colhead' width='1%'>{$lang['ad_snatched_torrents_download']}</td>") . "
<td class='colhead' width='1%'>{$lang['ad_snatched_torrents_seedtime']}</td>
<td class='colhead' width='1%'>{$lang['ad_snatched_torrents_leechtime']}</td>
<td class='colhead' width='1%'>{$lang['ad_snatched_torrents_startdate']}</td>
<td class='colhead' width='1%'>{$lang['ad_snatched_torrents_enddate']}</td>
<td class='colhead' width='1%'>{$lang['ad_snatched_torrents_seeding']}</td>
</tr>";
    } else {
        $HTMLOUT .= "<table width='100%' >
<tr>
<td class='colhead' width='1%'>{$lang['ad_snatched_torrents_name']}</td>
<td class='colhead' width='1%'>{$lang['ad_snatched_torrents_torname']}</td>
<td class='colhead' width='1%'>{$lang['ad_snatched_torrents_hnr']}</td>
<td class='colhead' width='1%'>{$lang['ad_snatched_torrents_marked']}</td>
<td class='colhead' width='1%'>{$lang['ad_snatched_torrents_announced']}</td>
<td class='colhead' width='1%'>{$lang['ad_snatched_torrents_upload']}</td>
" . (RATIO_FREE ? '' : "<td class='colhead' width='1%'>{$lang['ad_snatched_torrents_download']}</td>") . "
<td class='colhead' width='1%'>{$lang['ad_snatched_torrents_seedtime']}</td>
<td class='colhead' width='1%'>{$lang['ad_snatched_torrents_leechtime']}</td>
<td class='colhead' width='1%'>{$lang['ad_snatched_torrents_startdate']}</td>
<td class='colhead' width='1%'>{$lang['ad_snatched_torrents_enddate']}</td>
<td class='colhead' width='1%'>{$lang['ad_snatched_torrents_seeding']}</td>
</tr>";
    }
    while ($row = mysqli_fetch_assoc($result)) {
        $smallname = substr(htmlsafechars($row['name']), 0, 25);
        if ($smallname != htmlsafechars($row['name'])) {
            $smallname .= '...';
        }
        if (XBT_TRACKER) {
            $HTMLOUT .= '<tr><td>' . format_username($row['uid']) . "</td>
<td><a href='{$site_config['baseurl']}/details.php?id=" . (int) $row['fid'] . "'><b>" . $smallname . '</b></a></td>
<td><b>' . htmlsafechars($row['announced']) . '</b></td>
<td><b>' . mksize($row['uploaded']) . '</b></td>
' . (RATIO_FREE ? '' : '<td><b>' . mksize($row['downloaded']) . '</b></td>') . '
<td><b>' . get_snatched_color($row['seedtime']) . '</b></td>
<td><b>' . mkprettytime($row['leechtime']) . '</b></td><td><b>' . get_date($row['started'], 'LONG', 0, 1) . '</b></td>';
            if ($row['completedtime'] > 0) {
                $HTMLOUT .= '<td><b>' . get_date($row['completedtime'], 'LONG', 0, 1) . '</b></td>';
            } else {
                $HTMLOUT .= "<td><b><span class='has-text-danger'>{$lang['ad_snatched_torrents_ncomplete']}</span></b></td>";
            }
            $HTMLOUT .= '<td>' . ($row['seeders'] >= 1 ? "<img src='" . $site_config['pic_baseurl'] . "aff_tick.gif' alt='{$lang['ad_snatched_torrents_yes']}' title='{$lang['ad_snatched_torrents_yes']}' />" : "<img src='" . $site_config['pic_baseurl'] . "aff_cross.gif' alt='{$lang['ad_snatched_torrents_no']}' title='{$lang['ad_snatched_torrents_no']}' />") . '</td></tr>';
        } else {
            $HTMLOUT .= '<tr><td>' . format_username($row['userid']) . "</td>
<td><a href='{$site_config['baseurl']}/details.php?id=" . (int) $row['torrentid'] . "'><b>" . $smallname . '</b></a></td>
<td><b>' . get_date($row['hit_and_run'], 'LONG', 0, 1) . '</b></td>
<td><b>' . htmlsafechars($row['mark_of_cain']) . '</b></td>
<td><b>' . htmlsafechars($row['timesann']) . '</b></td>
<td><b>' . mksize($row['uploaded']) . '</b></td>
' . (RATIO_FREE ? '' : '<td><b>' . mksize($row['downloaded']) . '</b></td>') . '
<td><b>' . get_snatched_color($row['seedtime']) . '</b></td>
<td><b>' . mkprettytime($row['leechtime']) . '</b></td>
<td><b>' . get_date($row['start_date'], 'LONG', 0, 1) . '</b></td>';
            if ($row['complete_date'] > 0) {
                $HTMLOUT .= '<td><b>' . get_date($row['complete_date'], 'LONG', 0, 1) . '</b></td>';
            } else {
                $HTMLOUT .= "<td><b><span class='has-text-danger'>{$lang['ad_snatched_torrents_ncomplete']}</span></b></td></tr>";
            }
            $HTMLOUT .= '<td><b>' . ($row['seeder'] === 'yes' ? "<img src='" . $site_config['pic_baseurl'] . "aff_tick.gif' alt='{$lang['ad_snatched_torrents_yes']}' title='{$lang['ad_snatched_torrents_yes']}' />" : "<img src='" . $site_config['pic_baseurl'] . "aff_cross.gif' alt='{$lang['ad_snatched_torrents_no']}' title='{$lang['ad_snatched_torrents_no']}' />") . '</b></td></tr>';
        }
    }
    $HTMLOUT .= '</table>';
} else {
    $HTMLOUT .= "{$lang['ad_snatched_torrents_nothing']}";
}
if ($count > $snatchedperpage) {
    $HTMLOUT .= $pager['pagerbottom'];
}
$HTMLOUT .= end_main_frame();
echo stdhead($lang['ad_snatched_torrents_stdhead']) . wrapper($HTMLOUT) . stdfoot();
die();
