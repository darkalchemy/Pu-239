<?php

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'user_functions.php';
require_once INCL_DIR . 'html_functions.php';
require_once INCL_DIR . 'pager_functions.php';
check_user_status();
global $session, $CURUSER, $site_config;

$lang = array_merge(load_language('global'), load_language('snatches'));
$HTMLOUT = '';
if (empty($_GET['id'])) {
    $session->set('is-warning', 'Invalid Information');
    header("Location: {$site_config['baseurl']}/index.php");
    die();
}
$id = (int) $_GET['id'];
if (!is_valid_id($id)) {
    stderr('Error', 'It appears that you have entered an invalid id.');
}
$res = sql_query('SELECT id, name FROM torrents WHERE id = ' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
$arr = mysqli_fetch_assoc($res);
if (!$arr) {
    stderr('Error', 'It appears that there is no torrent with that id.');
}
$res = sql_query('SELECT COUNT(id) FROM snatched WHERE complete_date !=0 AND torrentid =' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
$row = mysqli_fetch_row($res);
$count = $row[0];
$perpage = 15;
$pager = pager($perpage, $count, "snatches.php?id=$id&amp;");
if (!$count) {
    stderr('No snatches', "It appears that there are currently no snatches for the torrent <a href='{$site_config['baseurl']}/details.php?id=" . (int) $arr['id'] . "'>" . htmlsafechars($arr['name']) . '</a>.');
}
$HTMLOUT .= "<h1 class='has-text-centered'>Snatches for torrent <a href='{$site_config['baseurl']}/details.php?id=" . (int) $arr['id'] . "'>" . htmlsafechars($arr['name']) . "</a></h1>\n";
$HTMLOUT .= "<h3 class='has-text-centered'>Currently {$row['0']} snatch" . ($row[0] == 1 ? '' : 'es') . "</h3>\n";
if ($count > $perpage) {
    $HTMLOUT .= $pager['pagertop'];
}
$header = "
        <tr>
            <th class='has-text-left'>{$lang['snatches_username']}</th>
            <th class='has-text-right'>{$lang['snatches_uploaded']}</th>
            <th class='has-text-right'>{$lang['snatches_upspeed']}</th>
            " . ($site_config['ratio_free'] ? '' : "<th class='has-text-right'>{$lang['snatches_downloaded']}</th>") . '
            ' . ($site_config['ratio_free'] ? '' : "<th class='has-text-right'>{$lang['snatches_downspeed']}</th>") . "
            <th class='has-text-right'>{$lang['snatches_ratio']}</th>
            <th class='has-text-right'>{$lang['snatches_completed']}</th>
            <th class='has-text-right'>{$lang['snatches_seedtime']}</th>
            <th class='has-text-right'>{$lang['snatches_leechtime']}</th>
            <th class='has-text-centered'>{$lang['snatches_lastaction']}</th>
            <th class='has-text-centered'>{$lang['snatches_completedat']}</th>
            <th class='has-text-centered'>{$lang['snatches_announced']}</th>
        </tr>";
$res = sql_query('
            SELECT s.*, u.paranoia, t.anonymous AS anonymous1, u.anonymous AS anonymous2, size, timesann, owner
            FROM snatched AS s
            INNER JOIN users AS u ON s.userid = u.id
            INNER JOIN torrents AS t ON s.torrentid = t.id
            WHERE s.complete_date !=0 AND s.torrentid = ' . sqlesc($id) . '
            ORDER BY complete_date DESC ' . $pager['limit']) or sqlerr(__FILE__, __LINE__);
$body = '';
while ($arr = mysqli_fetch_assoc($res)) {
    $upspeed = ($arr['upspeed'] > 0 ? mksize($arr['upspeed']) : ($arr['seedtime'] > 0 ? mksize($arr['uploaded'] / ($arr['seedtime'] + $arr['leechtime'])) : mksize(0)));
    $downspeed = ($arr['downspeed'] > 0 ? mksize($arr['downspeed']) : ($arr['leechtime'] > 0 ? mksize($arr['downloaded'] / $arr['leechtime']) : mksize(0)));
    $ratio = ($arr['downloaded'] > 0 ? number_format($arr['uploaded'] / $arr['downloaded'], 3) : ($arr['uploaded'] > 0 ? 'Inf.' : '---'));
    $completed = sprintf('%.2f%%', 100 * (1 - ($arr['to_go'] / $arr['size'])));
    $snatchuser = (isset($arr['userid']) ? format_username($arr['userid']) : "{$lang['snatches_unknown']}");
    $username = (($arr['anonymous2'] === 'yes' or $arr['paranoia'] >= 2) ? ($CURUSER['class'] < UC_STAFF && $arr['userid'] != $CURUSER['id'] ? '' : $snatchuser . ' - ') . "<i>{$lang['snatches_anon']}</i>" : $snatchuser);
    $body .= "
        <tr>
            <td class='has-text-left'>{$username}</td>
            <td class='has-text-right'>" . mksize($arr['uploaded']) . "</td>
            <td class='has-text-right'>" . htmlsafechars($upspeed) . '/s</td>
            ' . ($site_config['ratio_free'] ? '' : "<td class='has-text-right'>" . mksize($arr['downloaded']) . '</td>') . '
            ' . ($site_config['ratio_free'] ? '' : "<td class='has-text-right'>" . htmlsafechars($downspeed) . '/s</td>') . "
            <td class='has-text-right'>" . htmlsafechars($ratio) . "</td>
            <td class='has-text-right'>" . htmlsafechars($completed) . "</td>
            <td class='has-text-right'>" . mkprettytime($arr['seedtime']) . "</td>
            <td class='has-text-right'>" . mkprettytime($arr['leechtime']) . "</td>
            <td class='has-text-centered'>" . get_date($arr['last_action'], '', 0, 1) . "</td>
            <td class='has-text-centered'>" . get_date($arr['complete_date'], '', 0, 1) . "</td>
            <td class='has-text-centered'>" . (int) $arr['timesann'] . '</td>
        </tr>';
}

$HTMLOUT .= main_table($body, $header);
if ($count > $perpage) {
    $HTMLOUT .= $pager['pagerbottom'];
}
echo stdhead('Snatches') . wrapper($HTMLOUT) . stdfoot();
