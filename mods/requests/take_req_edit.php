<?php
if (!defined('IN_REQUESTS')) exit('No direct script access allowed');
/**
 |--------------------------------------------------------------------------|
 |   https://github.com/Bigjoos/                			    |
 |--------------------------------------------------------------------------|
 |   Licence Info: GPL			                                    |
 |--------------------------------------------------------------------------|
 |   Copyright (C) 2010 U-232 V4					    |
 |--------------------------------------------------------------------------|
 |   A bittorrent tracker source based on TBDev.net/tbsource/bytemonsoon.   |
 |--------------------------------------------------------------------------|
 |   Project Leaders: Mindless,putyn.					    |
 |--------------------------------------------------------------------------|
  _   _   _   _   _     _   _   _   _   _   _     _   _   _   _
 / \ / \ / \ / \ / \   / \ / \ / \ / \ / \ / \   / \ / \ / \ / \
( U | - | 2 | 3 | 2 )-( S | o | u | r | c | e )-( C | o | d | e )
 \_/ \_/ \_/ \_/ \_/   \_/ \_/ \_/ \_/ \_/ \_/   \_/ \_/ \_/ \_/
 */
$res = sql_query("SELECT userid, cat FROM requests WHERE id = $id") or sqlerr(__FILE__, __LINE__);
$num = mysqli_fetch_assoc($res);
if ($CURUSER['id'] != $num['userid'] && $CURUSER['class'] < UC_MODERATOR) stderr("{$lang['error_error']}", "{$lang['error_denied']}");
$request = (isset($_POST['requesttitle']) ? htmlspecialchars($_POST['requesttitle']) : '');
$pic = '';
if (!empty($_POST['picture'])) {
    if (!preg_match('/^https?:\/\/([a-zA-Z0-9\-\_]+\.)+([a-zA-Z]{1,5}[^\.])(\/[^<>]+)+\.(jpg|jpeg|gif|png|tif|tiff|bmp)$/i', $_POST['picture'])) stderr("{$lang['error_error']}", "{$lang['error_image']}");
    $picture = $_POST['picture'];
    //    $picture2 = trim(urldecode($picture));
    //    $headers  = get_headers($picture2);
    //    if (strpos($headers[0], '200') === false)
    //        $picture = $INSTALLER09['baseurl'].'/pic/notfound.png';
    $pic = "[img]" . $picture . "[/img]\n";
}
$descr = "$pic";
$descr.= isset($_POST['body']) ? $_POST['body'] : '';
if (!$descr) stderr("{$lang['error_error']}", "{$lang['error_descr']}");
$cat = (isset($_POST['category']) ? (int)$_POST['category'] : ($num['cat'] != '' ? $num['cat'] : 0));
if (!is_valid_id($cat)) stderr("{$lang['error_error']}", "{$lang['error_cat']}");
$request = sqlesc($request);
$descr = sqlesc($descr);
$filledby = isset($_POST['filledby']) ? (int)$_POST['filledby'] : 0;
$filled = isset($_POST['filled']) ? $_POST['filled'] : 0;
$torrentid = isset($_POST['torrentid']) ? (int)$_POST['torrentid'] : 0;
if ($filled) {
    if (!is_valid_id($torrentid)) stderr("{$lang['error_error']}", "{$lang['error_invalid_torrent']}");
    // could play around here if want to allow own requests or to fill as System, etc. =]
    //if ($CURUSER['id'] == $filledby)
    //stderr('Error', 'ID is your own. Cannot fill your own Requests.');
    //$filledby = 0;
    //else {
    $res = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT id FROM users WHERE id = " . $filledby);
    if (mysqli_num_rows($res) == 0) stderr("{$lang['error_error']}", "{$lang['error_no_user']}");
    //  }
    $res = sql_query("SELECT id FROM torrents WHERE id = " . $torrentid);
    if (mysqli_num_rows($res) == 0) stderr("{$lang['error_error']}", "{$lang['error_no_torrent']}");
    sql_query("UPDATE requests SET cat = $cat, request = $request, descr = $descr, filledby = $filledby, torrentid=$torrentid WHERE id = $id") or sqlerr(__FILE__, __LINE__);
} else sql_query("UPDATE requests SET cat = $cat, filledby = 0, request = $request, descr = $descr, torrentid = 0 WHERE id = $id") or sqlerr(__FILE__, __LINE__);
header("Refresh: 0; url=viewrequests.php?id=$id&req_details");
?>
