<?php

declare(strict_types = 1);

use Pu239\Database;
use Pu239\Session;
use Pu239\Snatched;
use Pu239\Torrent;
use Pu239\User;

require_once __DIR__ . '/../include/bittorrent.php';
require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_bbcode.php';
require_once INCL_DIR . 'function_html.php';
require_once INCL_DIR . 'function_onlinetime.php';
require_once CLASS_DIR . 'class_user_options.php';
require_once CLASS_DIR . 'class_user_options_2.php';
check_user_status();
$HTMLOUT = '';
$lang = array_merge(load_language('global'), load_language('userdetails'));
global $container, $CURUSER, $site_config;

if ($CURUSER['class'] < UC_MIN) {
    stderr('Sorry', 'You must be at least a User.');
}

if (isset($_GET['id']) && $CURUSER['class'] >= UC_STAFF) {
    $userid = (int) $_GET['id'];
} else {
    $userid = $CURUSER['id'];
}
$user_stuffs = $container->get(User::class);
$user_stuff = $user_stuffs->getUserFromId($userid);
$diff = $user_stuff['uploaded'] - $user_stuff['downloaded'];
if ($CURUSER['id'] === $userid || $CURUSER['class'] >= UC_ADMINISTRATOR) {
    $bp = $user_stuff['seedbonus'];
} else {
    $bp = 0;
}
$fluent = $container->get(Database::class);
$ratio_fix = $fluent->from('bonus')
                    ->select(null)
                    ->select('points')
                    ->where('bonusname = "Ratio Fix"')
                    ->where('enabled = "yes"')
                    ->fetch('points');

$cost = (!$ratio_fix) ? 0 : (int) $ratio_fix;
$session = $container->get(Session::class);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST['sid']) || empty($_POST['tid']) || empty($_POST['userid'])) {
        $session->set('is-danger', 'Invalid POST resquest');
    } else {
        $torrent_stuffs = $container->get(Torrent::class);
        $torrent = $torrent_stuffs->get($_POST['tid']);
        if (!$torrent) {
            $session->set('is-danger', 'No torrent with that ID!');
            header("Location: {$_SERVER['PHP_SELF']}");
            die();
        }
        $snatched_stuffs = $container->get(Snatched::class);
        $snatched = $snatched_stuffs->get_snatched($_POST['userid'], $_POST['tid']);
        if (!$snatched || $snatched['id'] != $_POST['sid']) {
            $session->set('is-danger', 'No snatched torrent with that ID!');
            header("Location: {$_SERVER['PHP_SELF']}");
            die();
        }
        if (!empty($_POST['seed'])) {
            if ($cost > $bp) {
                $session->set('is-danger', 'You do not have enough bonus points!');
                header("Location: {$_SERVER['PHP_SELF']}");
                die();
            }
            $set = [
                'hit_and_run' => 0,
                'mark_of_cain' => 'no',
                'seedtime' => $_POST['seed'],
            ];
            $snatched_stuffs->update($set, $_POST['tid'], $_POST['userid']);
            $bonuscomment = get_date((int) TIME_NOW, 'DATE', 1) . " - $cost Points for a seedtime fix on torrent: {$_POST['tid']} =>" . htmlsafechars($torrent['name']) . ".\n{$user_stuff['bonuscomment']}";
            $set = [
                'seedbonus' => $user_stuff['seedbonus'] - $cost,
                'bonuscomment' => $bonuscomment,
            ];
            $user_stuffs->update($set, $_POST['userid']);
            $cache->delete('userhnrs_' . $userid);
            $session->set('is-success', 'You have successfully removed the HnR for this torrent!');
        } elseif (!empty($_POST['bytes'])) {
            $downloaded = $site_config['site']['ratio_free'] ? $torrent['size'] : $snatched['downloaded'];
            $bytes = $downloaded - $snatched['uploaded'];
            if ($diff < $bytes) {
                $session->set('is-danger', 'You do not have enough upload credit!');
                header("Location: {$_SERVER['PHP_SELF']}");
                die();
            }
            $set = [
                'hit_and_run' => 0,
                'mark_of_cain' => 'no',
                'uploaded' => $snatched['downloaded'],
            ];
            $snatched_stuffs->update($set, $_POST['tid'], $_POST['userid']);
            $bonuscomment = get_date((int) TIME_NOW, 'DATE', 1) . ' - ' . mksize($bytes) . " upload credit for a ratio fix on torrent: {$_POST['tid']} =>" . htmlsafechars($torrent['name']) . ".\n{$user_stuff['bonuscomment']}";
            $set = [
                'uploaded' => $user_stuff['uploaded'] - $bytes,
                'bonuscomment' => $bonuscomment,
            ];
            $user_stuffs->update($set, $_POST['userid']);
            $cache->delete('userhnrs_' . $userid);
            $session->set('is-success', 'You have successfully removed the HnR for this torrent!');
        }
    }
    unset($_POST);
}

$completed = $count2 = $dlc = '';
$hnrs = $fluent->from('snatched AS s')
               ->select(null)
               ->select('t.name')
               ->select('t.added AS torrent_added')
               ->select('s.complete_date AS c')
               ->select('s.downspeed')
               ->select('s.seedtime')
               ->select('s.seeder')
               ->select('s.torrentid AS tid')
               ->select('s.id AS sid')
               ->select('c.id AS category')
               ->select('c.image')
               ->select('c.name AS catname')
               ->select('p.name AS parent_name')
               ->select('s.uploaded')
               ->select('s.downloaded')
               ->select('s.hit_and_run')
               ->select('s.mark_of_cain')
               ->select('s.complete_date')
               ->select('s.last_action')
               ->select('t.seeders')
               ->select('t.leechers')
               ->select('t.owner')
               ->select('t.size')
               ->select('s.start_date AS st')
               ->select('s.start_date')
               ->leftJoin('torrents AS t ON t.id=s.torrentid')
               ->leftJoin('categories AS c ON c.id=t.category')
               ->leftJoin('categories AS p ON c.parent_id=p.id')
               ->where('(s.hit_and_run != 0 OR s.mark_of_cain = "yes")')
               ->where('s.seeder = "no"')
               ->where('s.finished = "yes"')
               ->where('s.userid=?', $userid)
               ->where('t.owner != ?', $userid)
               ->orderBy('s.id DESC')
               ->fetchAll();

$completed .= '
<h1>Hit and Runs for: ' . format_username((int) $userid) . '</h1>';
if (count($hnrs) > 0) {
    $heading = "
        <tr>
            <th>{$lang['userdetails_type']}</th>
            <th>{$lang['userdetails_name']}</th>
            <th class='has-text-centered'>{$lang['userdetails_s']}</th>
            <th class='has-text-centered'>{$lang['userdetails_l']}</th>
            <th class='has-text-centered'>{$lang['userdetails_ul']}</th>
            " . ($site_config['site']['ratio_free'] ? "
            <th class='has-text-centered'>{$lang['userdetails_size']}</th>" : "
            <th class='has-text-centered'>{$lang['userdetails_dl']}</th>") . "
            <th class='has-text-centered'>{$lang['userdetails_ratio']}</th>
            <th class='has-text-centered'>{$lang['userdetails_wcompleted']}</th>
            <th class='has-text-centered'>{$lang['userdetails_laction']}</th>
            <th class='has-text-centered'>{$lang['userdetails_speed']}</th>
            <th class='has-text-centered'>Buyout</th>
        </tr>";
    $body = '';
    foreach ($hnrs as $a) {
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
            case $site_config['hnr_config']['torrentage1'] * 86400 > ($a['st'] - $a['torrent_added']):
                $minus_ratio = ($days_3 - $a['seedtime']);
                break;

            case $site_config['hnr_config']['torrentage2'] * 86400 > ($a['st'] - $a['torrent_added']):
                $minus_ratio = ($days_14 - $a['seedtime']);
                break;

            case $site_config['hnr_config']['torrentage3'] * 86400 <= ($a['st'] - $a['torrent_added']):
                $minus_ratio = ($days_over_14 - $a['seedtime']);
                break;

            default:
                $minus_ratio = ($days_over_14 - $a['seedtime']);
        }
        $color = (($minus_ratio > 0 && $a['uploaded'] < $a['downloaded']) ? get_ratio_color($minus_ratio) : 'limegreen');
        $need_to_seed = mkprettytime($minus_ratio);

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
        $checkbox_for_delete = ($CURUSER['class'] >= UC_STAFF && $CURUSER['id'] != $userid ? " [<a href='" . $site_config['paths']['baseurl'] . '/userdetails.php?id=' . $userid . '&amp;delete_hit_and_run=' . (int) $a['sid'] . "'>{$lang['userdetails_c_remove']}</a>]" : '');
        $mark_of_cain = ($a['mark_of_cain'] === 'yes' ? "<img src='{$site_config['paths']['images_baseurl']}moc.gif' width='40px' alt='{$lang['userdetails_c_mofcain']}' class='tooltipper' title='{$lang['userdetails_c_tmofcain']}'>" . $checkbox_for_delete : '');
        $hit_n_run = ($a['hit_and_run'] > 0 ? "<img src='{$site_config['paths']['images_baseurl']}hnr.gif' width='40px' alt='{$lang['userdetails_c_hitrun']}' class='tooltipper' title='{$lang['userdetails_c_hitrun1']}'>" : '');
        $needs_seed = time() < $a['hit_and_run'] + 86400 ? ' in ' . mkprettytime($a['hit_and_run'] + 86400 - time()) : '';

        if ($bp >= $cost && $cost != 0) {
            $buyout = "
            <form method='post' action='{$site_config['paths']['baseurl']}/hnrs.php' accept-charset='utf-8'>
                <input type='hidden' name='seed' value='{$minus_ratio}'>
                <input type='hidden' name='sid' value='{$a['sid']}'>
                <input type='hidden' name='tid' value='{$a['tid']}'>
                <input type='hidden' name='userid' value='{$userid}'>
                <div class='padding10 has-text-centered'>
                    <input type='submit' value='Seedtime Fix' class='button is-small tooltipper' title='Buyout with {$cost} bonus points to fix the seedtime for this torrent'>
                </div>
            </form>";
        } else {
            $buyout = '';
        }

        $a_downloaded = $site_config['site']['ratio_free'] ? $a['size'] : $a['downloaded'];
        $bytes = $a_downloaded - $a['uploaded'];
        if ($diff >= $bytes) {
            $buybytes = "
            <form method='post' action='{$site_config['paths']['baseurl']}/hnrs.php' accept-charset='utf-8'>
                <input type='hidden' name='bytes' value='{$bytes}'>
                <input type='hidden' name='sid' value='{$a['sid']}'>
                <input type='hidden' name='tid' value='{$a['tid']}'>
                <input type='hidden' name='userid' value='{$userid}'>
                <div class='padding10 has-text-centered'>
                    <input type='submit' value='Ratio Fix' class='button is-small tooltipper' title='Buyout with " . mksize($bytes) . " upload credit to fix the ratio for this torrent'>
                </div>
            </form>";
        } else {
            $buybytes = '';
        }

        $or = $buyout != '' && $buybytes != '' ? 'or' : '';
        $sucks = $buyout == '' ? "Seed for $need_to_seed" : "or<br>Seed for $need_to_seed";
        $a['cat'] = $a['parent_name'] . '::' . $a['catname'];
        $caticon = !empty($a['image']) ? "<img height='42px' class='tnyrad tooltipper' src='{$site_config['paths']['images_baseurl']}caticons/{$CURUSER['categorie_icon']}/{$a['image']}' alt='{$a['cat']}' title='{$a['name']}'>" : $a['cat'];
        $body .= "
        <tr>
            <td style='padding: 5px'>$caticon</td>
            <td><a class='is-link' href='details.php?id=" . (int) $a['tid'] . "&amp;hit=1'><b>" . htmlsafechars($a['name']) . "</b></a>
                <br><span style='color: .$color.'>  " . (($CURUSER['class'] >= UC_STAFF || $CURUSER['id'] == $userid) ? "{$lang['userdetails_c_seedfor']}</font>: " . mkprettytime($a['seedtime']) . (($need_to_seed != '0:00') ? "<br>{$lang['userdetails_c_should']}" . $need_to_seed . '&#160;&#160;' : '') . ($a['seeder'] === 'yes' ? "&#160;<font color='limegreen;'> [<b>{$lang['userdetails_c_seeding']}</b>]</span>" : $hit_n_run . '&#160;' . $mark_of_cain . $needs_seed) : '') . "
            </td>
            <td class='has-text-centered'>" . (int) $a['seeders'] . "</td>
            <td class='has-text-centered'>" . (int) $a['leechers'] . "</td>
            <td class='has-text-centered'>" . mksize($a['uploaded']) . '</td>
            ' . ($site_config['site']['ratio_free'] ? "<td class='has-text-centered'>" . mksize($a['size']) . '</td>' : "<td class='has-text-centered'>" . mksize($a['downloaded']) . '</td>') . "
            <td class='has-text-centered'>" . ($a['downloaded'] > 0 ? "<span style='color: " . get_ratio_color(number_format($a['uploaded'] / $a['downloaded'], 3)) . ";'>" . number_format($a['uploaded'] / $a['downloaded'], 3) . '</span>' : ($a['uploaded'] > 0 ? 'Inf.' : '---')) . "<br></td>
            <td class='has-text-centered'>" . get_date((int) $a['complete_date'], 'DATE') . "</td>
            <td class='has-text-centered'>" . get_date((int) $a['last_action'], 'DATE') . "</td>
            <td class='has-text-centered'><span style='color: $dlc;'>{$lang['userdetails_c_dled']}<br>{$dl_speed}ps</span></td>
            <td class='has-text-centered'>{$buyout}{$buybytes}{$sucks}</td>
        </tr>";
    }
    $completed .= main_table($body, $heading);
} else {
    $session->set('is-success', '[color=#' . get_user_class_color($user_stuff['class']) . ']' . $user_stuff['username'] . "[/color] {$lang['userdetails_no_hnrs']}");
    $completed = main_div("<div class='padding20'>" . format_username((int) $userid) . ' ' . $lang['userdetails_no_hnrs'] . '</div>');
}
echo stdhead('HnRs') . wrapper($completed, 'has-text-centered') . stdfoot();
