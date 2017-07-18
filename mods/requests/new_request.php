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
$request = (isset($_POST['requesttitle']) ? $_POST['requesttitle'] : '');
if ($request == '') stderr("{$lang['error_error']}", "{$lang['error_title']}");
$cat = (isset($_POST['category']) ? (int)$_POST['category'] : 0);
if (!is_valid_id($cat)) stderr("{$lang['error_error']}", "{$lang['error_cat']}");
$descrmain = (isset($_POST['body']) ? $_POST['body'] : '');
if (!$descrmain) stderr("{$lang['error_error']}", "{$lang['error_descr']}");
$pic = '';
if (!empty($_POST['picture'])) {
    if (!preg_match('/^https?:\/\/([a-zA-Z0-9\-\_]+\.)+([a-zA-Z]{1,5}[^\.])(\/[^<>]+)+\.(jpg|jpeg|gif|png|tif|tiff|bmp)$/i', $_POST['picture'])) stderr("{$lang['error_error']}", "{$lang['error_image']}");
    $picture = $_POST['picture'];
    //    $picture2 = trim(urldecode($_POST['picture']));
    //    $headers  = get_headers($picture2);
    //    if (strpos($headers[0], '200') === false)
    //        $picture = $INSTALLER09['baseurl'].'/pic/notfound.png';
    $pic = "[img]" . $picture . "[/img]\n";
}
$descr = "$pic";
$descr.= "$descrmain";
$request2 = sqlesc($request);
$descr = sqlesc($descr);
sql_query("INSERT INTO requests (hits, userid, cat, request, descr, added) VALUES(1,$CURUSER[id], $cat, $request2, $descr, " . TIME_NOW . ")") or sqlerr(__FILE__, __LINE__);
$id = ((is_null($___mysqli_res = mysqli_insert_id($GLOBALS["___mysqli_ston"]))) ? false : $___mysqli_res);
sql_query("INSERT INTO voted_requests VALUES(0, $id, $CURUSER[id])") or sqlerr();
if ($INSTALLER09['karma'] && isset($CURUSER['seedbonus'])) sql_query("UPDATE users SET seedbonus = seedbonus-" . $INSTALLER09['req_cost_bonus'] . " WHERE id = $CURUSER[id]") or sqlerr(__FILE__, __LINE__);
write_log("Request (" . $request . ") was added to the Request section by $CURUSER[username]");
if ($INSTALLER09['autoshout_on'] == 1) {
    /** Shout announce **/
    $message = " [b][color=blue]New request[/color][/b]  [url={$INSTALLER09['baseurl']}/viewrequests.php?id=$id&req_details] " . $request . "[/url]  ";
    autoshout($message);
}
header("Refresh: 0; url=viewrequests.php?id=$id&req_details");
?>
