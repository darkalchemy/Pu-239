<?php

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'user_functions.php';
require_once INCL_DIR . 'pager_functions.php';
check_user_status();
global $CURUSER, $site_config, $session;

$lang    = array_merge(load_language('global'), load_language('snatches'));
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
$res     = sql_query('SELECT COUNT(fid) FROM xbt_files_users WHERE completedtime !=0 AND fid =' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
$row     = mysqli_fetch_row($res);
$count   = $row[0];
$perpage = 15;
$pager   = pager($perpage, $count, "snatches.php?id=$id&amp;");
if (!$count) {
    stderr('No snatches', "It appears that there are currently no snatches for the torrent <a href='details.php?id=" . (int) $arr['id'] . "'>" . htmlsafechars($arr['name']) . '</a>.');
}
$HTMLOUT .= "<h1>Snatches for torrent <a href='{$site_config['baseurl']}/details.php?id=" . (int) $arr['id'] . "'>" . htmlsafechars($arr['name']) . "</a></h1>\n";
$HTMLOUT .= "<h2>Currently {$row['0']} snatch" . ($row[0] == 1 ? '' : 'es') . "</h2>\n";
if ($count > $perpage) {
    $HTMLOUT .= $pager['pagertop'];
}
$HTMLOUT .= "<table width='78%'>
<tr>
<td class='colhead'>{$lang['snatches_username']}</td>
<td class='colhead'>{$lang['snatches_uploaded']}</td>
" . ($site_config['ratio_free'] ? '' : "<td class='colhead'>{$lang['snatches_downloaded']}</td>") . "
<td class='colhead'>{$lang['snatches_ratio']}</td>
<td class='colhead'>{$lang['snatches_seedtime']}</td>
<td class='colhead'>{$lang['snatches_leechtime']}</td>
<td class='colhead'>{$lang['snatches_lastaction']}</td>
<td class='colhead'>{$lang['snatches_announced']}</td>
<td class='colhead'>Active</td>
<td class='colhead'>{$lang['snatches_completed']}</td>
</tr>\n";
$res = sql_query('SELECT x.*, x.uid AS xu, torrents.username AS username1, users.username AS username2, users.paranoia, torrents.anonymous AS anonymous1, users.anonymous AS anonymous2, size, parked, warned, enabled, class, chatpost, leechwarn, donor, uid FROM xbt_files_users AS x INNER JOIN users ON x.uid = users.id INNER JOIN torrents ON x.fid = torrents.id WHERE fid = ' . sqlesc($id) . ' AND completedtime !=0 ORDER BY fid DESC ' . $pager['limit']) or sqlerr(__FILE__, __LINE__);
while ($arr = mysqli_fetch_assoc($res)) {
    $ratio      = ($arr['downloaded'] > 0 ? number_format($arr['uploaded'] / $arr['downloaded'], 3) : ($arr['uploaded'] > 0 ? 'Inf.' : '---'));
    $upspeed    = ($arr['upspeed'] > 0 ? mksize($arr['upspeed']) : ($arr['seedtime'] > 0 ? mksize($arr['uploaded'] / ($arr['seedtime'] + $arr['leechtime'])) : mksize(0)));
    $downspeed  = ($arr['downspeed'] > 0 ? mksize($arr['downspeed']) : ($arr['leechtime'] > 0 ? mksize($arr['downloaded'] / $arr['leechtime']) : mksize(0)));
    $active = ($arr['active'] == 1 ? $active = "<img src='" . $site_config['pic_baseurl'] . "aff_tick.gif' alt='Yes' title='Yes' />" : $active = "<img src='" . $site_config['pic_baseurl'] . "aff_cross.gif' alt='No' title='No' />");
    $completed  = ($arr['completed'] >= 1 ? $completed = "<img src='" . $site_config['pic_baseurl'] . "aff_tick.gif' alt='Yes' title='Yes' />" : $completed = "<img src='" . $site_config['pic_baseurl'] . "aff_cross.gif' alt='No' title='No' />");
    $snatchuser = (isset($arr['username2']) ? ("<a href='userdetails.php?id=" . (int) $arr['uid'] . "'><b>" . htmlsafechars($arr['username2']) . '</b></a>') : "{$lang['snatches_unknown']}");
    $username = (($arr['anonymous2'] === 'yes' || $arr['paranoia'] >= 2) ? ($CURUSER['class'] < UC_STAFF && $arr['uid'] != $CURUSER['id'] ? '' : $snatchuser . ' - ') . "<i>{$lang['snatches_anon']}</i>" : $snatchuser);
    $HTMLOUT .= "<tr>
  <td>{$username}</td>
  <td>" . mksize($arr['uploaded']) . '</td>
  ' . ($site_config['ratio_free'] ? '' : '<td>' . mksize($arr['downloaded']) . '</td>') . '
  <td>' . htmlsafechars($ratio) . '</td>
  <td>' . mkprettytime($arr['seedtime']) . '</td>
  <td>' . mkprettytime($arr['leechtime']) . '</td>
  <td>' . get_date($arr['mtime'], '', 0, 1) . '</td>
  <td>' . (int) $arr['announced'] . '</td>
  <td>' . $active . '</td>
  <td>' . $completed . "</td>
  </tr>\n";
}
$HTMLOUT .= "</table>\n";
if ($count > $perpage) {
    $HTMLOUT .= $pager['pagerbottom'];
}
echo stdhead('Snatches') . $HTMLOUT . stdfoot();
die();
