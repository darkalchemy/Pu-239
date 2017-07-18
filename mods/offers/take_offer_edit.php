<?php
if (!defined('IN_OFFERS')) exit('No direct script access allowed');
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
$res = sql_query("SELECT userid, cat FROM offers WHERE id = $id") or sqlerr(__FILE__, __LINE__);
$num = mysqli_fetch_assoc($res);
if ($CURUSER['id'] != $num['userid'] && $CURUSER['class'] < UC_MODERATOR) stderr('Error', 'Access denied.');
$offer = (isset($_POST['offertitle']) ? htmlspecialchars($_POST['offertitle']) : '');
$pic = '';
if (!empty($_POST['picture'])) {
    if (!preg_match('/^https?:\/\/([a-zA-Z0-9\-\_]+\.)+([a-zA-Z]{1,5}[^\.])(\/[^<>]+)+\.(jpg|jpeg|gif|png|tif|tiff|bmp)$/i', $_POST['picture'])) stderr('Error', "Picture MUST be in jpg, gif or png format. Make sure you include http:// in the URL.");
    $picture = $_POST['picture'];
    //    $picture2 = trim(urldecode($picture));
    //    $headers  = get_headers($picture2);
    //    if (strpos($headers[0], '200') === false)
    //        $picture = $TBDEV['baseurl'].'/pic/notfound.png';
    $pic = "[img]" . $picture . "[/img]\n";
}
$descr = "$pic";
$descr.= isset($_POST['body']) ? $_POST['body'] : '';
if (!$descr) stderr('Error', 'You must enter a description!');
$cat = (isset($_POST['category']) ? (int)$_POST['category'] : ($num['cat'] != '' ? $num['cat'] : 0));
if (!is_valid_id($cat)) stderr('Error', 'You must select a category to put the request in!');
$offer = sqlesc($offer);
$descr = sqlesc($descr);
$acceptedby = isset($_POST['acceptedby']) ? (int)$_POST['acceptedby'] : 0;
$filled = isset($_POST['filled']) ? $_POST['filled'] : 0;
$torrentid = isset($_POST['torrentid']) ? (int)$_POST['torrentid'] : 0;
if ($filled) {
    if (!is_valid_id($torrentid)) stderr('Error', 'Not a valid torrent ID!');
    // could play around here if want to allow own requests or to fill as System, etc. =]
    //if ($CURUSER['id'] == $filledby)
    //stderr('Error', 'ID is your own. Cannot fill your own Requests.');
    //$filledby = 0;
    //else {
    $res = sql_query("SELECT id FROM users WHERE id = " . $filledby);
    if (mysqli_num_rows($res) == 0) stderr('Error', 'ID doesn\'t match any users, try again');
    //  }
    $res = sql_query("SELECT id FROM torrents WHERE id = " . $torrentid);
    if (mysqli_num_rows($res) == 0) stderr('Error', 'ID doesn\'t match any torrents, try again');
    sql_query("UPDATE offers SET cat = $cat, offer = $offer, descr = $descr, acceptedby = $acceptedby, torrentid=$torrentid WHERE id = $id") or sqlerr(__FILE__, __LINE__);
} else sql_query("UPDATE offers SET cat = $cat, acceptedby = 0, offer = $offer, descr = $descr, torrentid = 0 WHERE id = $id") or sqlerr(__FILE__, __LINE__);
header("Refresh: 0; url=viewoffers.php?id=$id&offer_details");
?>
