<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'user_functions.php';
require_once INCL_DIR . 'pager_functions.php';
check_user_status();
$lang = array_merge(load_language('global'), load_language('snatches'));
$HTMLOUT = '';
if (empty($_GET['id'])) {
    setSessionVar('error', 'Invalid Information');
    header("Location: {$INSTALLER09['baseurl']}/index.php");
    exit();
}
$id = (int)$_GET['id'];
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
    stderr('No snatches', "It appears that there are currently no snatches for the torrent <a href='details.php?id=" . (int)$arr['id'] . "'>" . htmlsafechars($arr['name']) . '</a>.');
}
$HTMLOUT .= "<h1>Snatches for torrent <a href='{$INSTALLER09['baseurl']}/details.php?id=" . (int)$arr['id'] . "'>" . htmlsafechars($arr['name']) . "</a></h1>\n";
$HTMLOUT .= "<h2>Currently {$row['0']} snatch" . ($row[0] == 1 ? '' : 'es') . "</h2>\n";
if ($count > $perpage) {
    $HTMLOUT .= $pager['pagertop'];
}
$HTMLOUT .= "<table width='78%'border='0' cellspacing='0' cellpadding='5'>
<tr>
<td class='colhead text-left'>{$lang['snatches_username']}</td>
<td class='colhead text-center'>{$lang['snatches_connectable']}</td>
<td class='colhead text-right'>{$lang['snatches_uploaded']}</td>
<td class='colhead text-right'>{$lang['snatches_upspeed']}</td>
" . ($INSTALLER09['ratio_free'] ? '' : "<td class='colhead text-right'>{$lang['snatches_downloaded']}</td>") . '
' . ($INSTALLER09['ratio_free'] ? '' : "<td class='colhead text-right'>{$lang['snatches_downspeed']}</td>") . "
<td class='colhead text-right'>{$lang['snatches_ratio']}</td>
<td class='colhead text-right'>{$lang['snatches_completed']}</td>
<td class='colhead text-right'>{$lang['snatches_seedtime']}</td>
<td class='colhead text-right'>{$lang['snatches_leechtime']}</td>
<td class='colhead text-center'>{$lang['snatches_lastaction']}</td>
<td class='colhead text-center'>{$lang['snatches_completedat']}</td>
<td class='colhead text-center'>{$lang['snatches_client']}</td>
<td class='colhead text-center'>{$lang['snatches_port']}</td>
<td class='colhead text-center'>{$lang['snatches_announced']}</td>
</tr>\n";
$res = sql_query('SELECT s.*, s.userid AS su, torrents.username as username1, users.username as username2, users.paranoia, torrents.anonymous as anonymous1, users.anonymous as anonymous2, size, parked, warned, enabled, class, chatpost, leechwarn, donor, timesann, owner FROM snatched AS s INNER JOIN users ON s.userid = users.id INNER JOIN torrents ON s.torrentid = torrents.id WHERE complete_date !=0 AND torrentid = ' . sqlesc($id) . ' ORDER BY complete_date DESC ' . $pager['limit']) or sqlerr(__FILE__, __LINE__);
while ($arr = mysqli_fetch_assoc($res)) {
    $upspeed = ($arr['upspeed'] > 0 ? mksize($arr['upspeed']) : ($arr['seedtime'] > 0 ? mksize($arr['uploaded'] / ($arr['seedtime'] + $arr['leechtime'])) : mksize(0)));
    $downspeed = ($arr['downspeed'] > 0 ? mksize($arr['downspeed']) : ($arr['leechtime'] > 0 ? mksize($arr['downloaded'] / $arr['leechtime']) : mksize(0)));
    $ratio = ($arr['downloaded'] > 0 ? number_format($arr['uploaded'] / $arr['downloaded'], 3) : ($arr['uploaded'] > 0 ? 'Inf.' : '---'));
    $completed = sprintf('%.2f%%', 100 * (1 - ($arr['to_go'] / $arr['size'])));
    $snatchuser = (isset($arr['username2']) ? ("<a href='userdetails.php?id=" . (int)$arr['userid'] . "'><b>" . htmlsafechars($arr['username2']) . '</b></a>') : "{$lang['snatches_unknown']}");
    $username = (($arr['anonymous2'] == 'yes' or $arr['paranoia'] >= 2) ? ($CURUSER['class'] < UC_STAFF && $arr['userid'] != $CURUSER['id'] ? '' : $snatchuser . ' - ') . "<i>{$lang['snatches_anon']}</i>" : $snatchuser);
    //if($arr['owner'] != $arr['su']){
    $HTMLOUT .= "<tr>
  <td class='text-left'>{$username}</td>
  <td class='text-center'>" . ($arr['connectable'] == 'yes' ? "<font color='green'>Yes</font>" : "<font color='red'>No</font>") . "</td>
  <td class='text-right'>" . mksize($arr['uploaded']) . "</td>
  <td class='text-right'>" . htmlsafechars($upspeed) . '/s</td>
  ' . ($INSTALLER09['ratio_free'] ? '' : "<td class='text-right'>" . mksize($arr['downloaded']) . '</td>') . '
  ' . ($INSTALLER09['ratio_free'] ? '' : "<td class='text-right'>" . htmlsafechars($downspeed) . '/s</td>') . "
  <td class='text-right'>" . htmlsafechars($ratio) . "</td>
  <td class='text-right'>" . htmlsafechars($completed) . "</td>
  <td class='text-right'>" . mkprettytime($arr['seedtime']) . "</td>
  <td class='text-right'>" . mkprettytime($arr['leechtime']) . "</td>
  <td class='text-center'>" . get_date($arr['last_action'], '', 0, 1) . "</td>
  <td class='text-center'>" . get_date($arr['complete_date'], '', 0, 1) . "</td>
  <td class='text-center'>" . htmlsafechars($arr['agent']) . "</td>
  <td class='text-center'>" . (int)$arr['port'] . "</td>
  <td class='text-center'>" . (int)$arr['timesann'] . "</td>
  </tr>\n";
}
//}
$HTMLOUT .= "</table>\n";
if ($count > $perpage) {
    $HTMLOUT .= $pager['pagerbottom'];
}
echo stdhead('Snatches') . $HTMLOUT . stdfoot();
die;
