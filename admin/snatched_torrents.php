<?php

declare(strict_types = 1);

use Pu239\Database;

require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_html.php';
require_once INCL_DIR . 'function_pager.php';
require_once CLASS_DIR . 'class_check.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
$lang = array_merge($lang, load_language('ad_snatched_torrents'));
global $site_config;

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

        return "<span class='has-text-success'><b>$month months.<br>$week_elapsed W. $days_elapsed D.</b></span>";
    }
    if ($week > 0) {
        $days_elapsed = floor(($st - ($week * 7 * 24 * 60 * 60)) / (24 * 60 * 60));
        $hours_elapsed = floor(($st - ($days * 24 * 60 * 60)) / (60 * 60));
        $mins_elapsed = floor(($st - ($hours * 60 * 60)) / 60);
        $secs_elapsed = floor($st - $mins * 60);

        return "<span class='has-text-success'><b>$week W. $days_elapsed D.<br>$hours_elapsed:$mins_elapsed:$secs_elapsed</b></span>";
    }
    if ($days > 2) {
        $hours_elapsed = floor(($st - ($days * 24 * 60 * 60)) / (60 * 60));
        $mins_elapsed = floor(($st - ($hours * 60 * 60)) / 60);
        $secs_elapsed = floor($st - $mins * 60);

        return "<span class='has-text-success'><b>$days D.<br>$hours_elapsed:$mins_elapsed:$secs_elapsed</b></span>";
    }
    if ($days > 1) {
        $hours_elapsed = floor(($st - ($days * 24 * 60 * 60)) / (60 * 60));
        $mins_elapsed = floor(($st - ($hours * 60 * 60)) / 60);
        $secs_elapsed = floor($st - $mins * 60);

        return "<span class='is-success'><b>$days D.<br>$hours_elapsed:$mins_elapsed:$secs_elapsed</b></span>";
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

global $container;

$fluent = $container->get(Database::class);
$count = $fluent->from('snatched')
                ->select(null)
                ->select('COUNT(id) AS count')
                ->fetch('count');

$HTMLOUT .= "
    <h1 class='has-text-centered'>{$lang['ad_snatched_torrents_allsnatched']}</h1>
    <div class='has-text-centered size_4 bottom20'>{$lang['ad_snatched_torrents_currently']}&#160;" . $count . "&#160;{$lang['ad_snatched_torrents_snatchedtor']}</div>";
$snatchedperpage = 25;
$pager = pager($snatchedperpage, $count, 'staffpanel.php?tool=snatched_torrents&amp;action=snatched_torrents&amp;');
if ($count > $snatchedperpage) {
    $HTMLOUT .= $pager['pagertop'];
}
$snatched = $fluent->from('snatched AS sn')
                   ->select('t.name')
                   ->leftJoin('torrents AS t ON sn.torrentid = t.id')
                   ->orderBy('sn.complete_date DESC')
                   ->orderBy('sn.start_date DESC')
                   ->limit($pager['pdo']['limit'])
                   ->offset($pager['pdo']['offset']);

if ($count > 0) {
    $heading = "
    <tr>
        <th>{$lang['ad_snatched_torrents_name']}</th>
        <th class='min-150'>{$lang['ad_snatched_torrents_torname']}</th>
        <th>{$lang['ad_snatched_torrents_hnr']}</th>
        <th>{$lang['ad_snatched_torrents_marked']}</th>
        <th>{$lang['ad_snatched_torrents_announced']}</th>
        <th>{$lang['ad_snatched_torrents_upload']}</th>" . ($site_config['site']['ratio_free'] ? '' : "
        <th>{$lang['ad_snatched_torrents_download']}</th>") . "
        <th>{$lang['ad_snatched_torrents_seedtime']}</th>
        <th>{$lang['ad_snatched_torrents_leechtime']}</th>
        <th>{$lang['ad_snatched_torrents_startdate']}</th>
        <th>{$lang['ad_snatched_torrents_enddate']}</th>
        <th>{$lang['ad_snatched_torrents_seeding']}</th>
    </tr>";
    $body = '';
    foreach ($snatched as $row) {
        $smallname = substr(format_comment($row['name']), 0, 35);
        if ($smallname != format_comment($row['name'])) {
            $smallname .= '...';
        }
        $body .= '
    <tr>
        <td>' . format_username($row['userid']) . "</td>
        <td><a href='{$site_config['paths']['baseurl']}/details.php?id=" . (int) $row['torrentid'] . "'><b>" . $smallname . '</b></a></td>
        <td class="has-text-centered"><b>' . get_date((int) $row['hit_and_run'], 'LONG', 0, 1) . '</b></td>
        <td class="has-text-centered"><b>' . format_comment($row['mark_of_cain']) . '</b></td>
        <td class="has-text-centered"><b>' . $row['timesann'] . '</b></td>
        <td class="has-text-centered"><b>' . mksize($row['uploaded']) . '</b></td>' . ($site_config['site']['ratio_free'] ? '' : '
        <td class="has-text-centered"><b>' . mksize($row['downloaded']) . '</b></td>') . '
        <td class="has-text-centered"><b>' . get_snatched_color($row['seedtime']) . '</b></td>
        <td class="has-text-centered"><b>' . mkprettytime($row['leechtime']) . '</b></td>
        <td class="has-text-centered"><b>' . get_date((int) $row['start_date'], 'LONG', 0, 1) . '</b></td>';
        if ($row['complete_date'] > 0) {
            $body .= '
        <td class="has-text-centered"><b>' . get_date((int) $row['complete_date'], 'LONG', 0, 1) . '</b></td>';
        } else {
            $body .= "
        <td class='has-text-centered'><b><span class='has-text-danger'>{$lang['ad_snatched_torrents_ncomplete']}</span></b></td>";
        }
        $body .= '
        <td class="has-text-centered"><b>' . ($row['seeder'] === 'yes' ? "<i class='icon-thumbs-up icon has-text-success tooltipper' title='{$lang['ad_snatched_torrents_yes']}'></i>" : "<i class='icon-thumbs-down icon has-text-danger tooltipper' title='{$lang['ad_snatched_torrents_no']}'></i>") . '</b></td>
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
