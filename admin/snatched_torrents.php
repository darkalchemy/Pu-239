<?php

require_once INCL_DIR . 'user_functions.php';
require_once INCL_DIR . 'html_functions.php';
require_once INCL_DIR . 'pager_functions.php';
require_once CLASS_DIR . 'class_check.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
global $site_config, $lang, $fluent;

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

$What_Value = 'WHERE complete_date != "0"';
$count = $fluent->from('snatched')
    ->select(null)
    ->select('COUNT(*) AS count')
    ->where('complete_date > 0')
    ->fetch('count');

$HTMLOUT .= "
    <h1 class='has-text-centered'>{$lang['ad_snatched_torrents_allsnatched']}</h1>
    <div class='has-text-centered size_4 bottom20'>{$lang['ad_snatched_torrents_currently']}&#160;" . htmlsafechars($count) . "&#160;{$lang['ad_snatched_torrents_snatchedtor']}</div>";
$res = sql_query('SELECT COUNT(id) FROM snatched') or sqlerr(__FILE__, __LINE__);
$row = mysqli_fetch_row($res);
$count = $row[0];
$snatchedperpage = 15;
$pager = pager($snatchedperpage, $count, 'staffpanel.php?tool=snatched_torrents&amp;action=snatched_torrents&amp;');
if ($count > $snatchedperpage) {
    $HTMLOUT .= $pager['pagertop'];
}
$sql = 'SELECT sn.userid, sn.id, sn.torrentid, sn.timesann, sn.hit_and_run, sn.mark_of_cain, sn.uploaded, sn.downloaded, sn.start_date, sn.complete_date, sn.seeder, sn.leechtime, sn.seedtime, u.username, t.name ' . 'FROM snatched AS sn ' . 'LEFT JOIN users AS u ON u.id=sn.userid ' . "LEFT JOIN torrents AS t ON t.id=sn.torrentid WHERE complete_date != '0'" . 'ORDER BY sn.complete_date DESC ' . $pager['limit'];
$result = sql_query($sql) or sqlerr(__FILE__, __LINE__);
if (mysqli_num_rows($result) != 0) {
    $heading = "
    <tr>
        <th class='w-1'>{$lang['ad_snatched_torrents_name']}</th>
        <th class='w-1'>{$lang['ad_snatched_torrents_torname']}</th>
        <th class='w-1'>{$lang['ad_snatched_torrents_hnr']}</th>
        <th class='w-1'>{$lang['ad_snatched_torrents_marked']}</th>
        <th class='w-1'>{$lang['ad_snatched_torrents_announced']}</th>
        <th class='w-1'>{$lang['ad_snatched_torrents_upload']}</th>" . ($site_config['ratio_free'] ? '' : "
        <th class='w-1'>{$lang['ad_snatched_torrents_download']}</th>") . "
        <th class='w-1'>{$lang['ad_snatched_torrents_seedtime']}</th>
        <th class='w-1'>{$lang['ad_snatched_torrents_leechtime']}</th>
        <th class='w-1'>{$lang['ad_snatched_torrents_startdate']}</th>
        <th class='w-1'>{$lang['ad_snatched_torrents_enddate']}</th>
        <th class='w-1'>{$lang['ad_snatched_torrents_seeding']}</th>
    </tr>";
    $body = '';
    while ($row = mysqli_fetch_assoc($result)) {
        $smallname = substr(htmlsafechars($row['name']), 0, 25);
        if ($smallname != htmlsafechars($row['name'])) {
            $smallname .= '...';
        }
        $body .= '
    <tr>
        <td>' . format_username($row['userid']) . "</td>
        <td><a href='{$site_config['baseurl']}/details.php?id=" . (int) $row['torrentid'] . "'><b>" . $smallname . '</b></a></td>
        <td><b>' . get_date($row['hit_and_run'], 'LONG', 0, 1) . '</b></td>
        <td><b>' . htmlsafechars($row['mark_of_cain']) . '</b></td>
        <td><b>' . htmlsafechars($row['timesann']) . '</b></td>
        <td><b>' . mksize($row['uploaded']) . '</b></td>' . ($site_config['ratio_free'] ? '' : '
        <td><b>' . mksize($row['downloaded']) . '</b></td>') . '
        <td><b>' . get_snatched_color($row['seedtime']) . '</b></td>
        <td><b>' . mkprettytime($row['leechtime']) . '</b></td>
        <td><b>' . get_date($row['start_date'], 'LONG', 0, 1) . '</b></td>';
        if ($row['complete_date'] > 0) {
            $body .= '
        <td><b>' . get_date($row['complete_date'], 'LONG', 0, 1) . '</b></td>';
        } else {
            $body .= "
        <td><b><span class='has-text-danger'>{$lang['ad_snatched_torrents_ncomplete']}</span></b></td></tr>";
        }
        $body .= '
        <td><b>' . ($row['seeder'] === 'yes' ? "<i class='icon-ok icon has-text-success tooltipper' title='{$lang['ad_snatched_torrents_yes']}'></i>" : "<i class='icon-trash-empty icon has-text-danger tooltipper' title='{$lang['ad_snatched_torrents_no']}'></i>") . '</b></td>
    </tr>';
    }
    $HTMLOUT .= main_table($body, $heading);
} else {
    $HTMLOUT .= stdmsg('', $lang['ad_snatched_torrents_nothing']);
}
if ($count > $snatchedperpage) {
    $HTMLOUT .= $pager['pagerbottom'];
}
echo stdhead($lang['ad_snatched_torrents_stdhead']) . wrapper($HTMLOUT) . stdfoot();
die();
