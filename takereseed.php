<?php
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
//made by putyn @tbdev.net
require_once (dirname(__FILE__) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php');
dbconn();
loggedinorreturn();
global $INSTALLER09;
$pm_what = isset($_POST["pm_what"]) && $_POST["pm_what"] == "last10" ? "last10" : "owner";
$reseedid = 0 + $_POST["reseedid"];
$uploader = 0 + $_POST["uploader"];
$use_subject = true;
$subject = "Request reseed!";
$pm_msg = "User " . $CURUSER["username"] . " asked for a reseed on torrent " . $INSTALLER09['baseurl'] . "/details.php?id=" . $reseedid . " !\nThank You!";
$pms = array();
if ($pm_what == "last10") {
    $res = sql_query("SELECT snatched.userid, snatched.torrentid FROM snatched  where snatched.torrentid =" . sqlesc($reseedid) . " AND snatched.seeder='yes' LIMIT 10") or sqlerr(__FILE__, __LINE__);
    while ($row = mysqli_fetch_assoc($res)) $pms[] = "(0," . sqlesc($row["userid"]) . "," . TIME_NOW . "," . sqlesc($pm_msg) . ($use_subject ? "," . sqlesc($subject) : "") . ")";
} elseif ($pm_what == "owner") $pms[] = "(0,$uploader," . TIME_NOW . "," . sqlesc($pm_msg) . ($use_subject ? "," . sqlesc($subject) : "") . ")";
if (count($pms) > 0) sql_query("INSERT INTO messages (sender, receiver, added, msg " . ($use_subject ? ", subject" : "") . " ) VALUES " . join(",", $pms)) or sqlerr(__FILE__, __LINE__);
sql_query("UPDATE torrents set last_reseed=" . TIME_NOW . " WHERE id=" . sqlesc($reseedid)) or sqlerr(__FILE__, __LINE__);
$mc1->begin_transaction('torrent_details_' . $reseedid);
$mc1->update_row(false, array(
    'last_reseed' => TIME_NOW
));
$mc1->commit_transaction($INSTALLER09['expires']['torrent_details']);
if ($INSTALLER09['seedbonus_on'] == 1) {
    //===remove karma
    sql_query("UPDATE users SET seedbonus = seedbonus-10.0 WHERE id = " . sqlesc($CURUSER["id"])) or sqlerr(__FILE__, __LINE__);
    $update['seedbonus'] = ($CURUSER['seedbonus'] - 10);
    $mc1->begin_transaction('userstats_' . $CURUSER["id"]);
    $mc1->update_row(false, array(
        'seedbonus' => $update['seedbonus']
    ));
    $mc1->commit_transaction($INSTALLER09['expires']['u_stats']);
    $mc1->begin_transaction('user_stats_' . $CURUSER["id"]);
    $mc1->update_row(false, array(
        'seedbonus' => $update['seedbonus']
    ));
    $mc1->commit_transaction($INSTALLER09['expires']['user_stats']);
    //===end
    
}
header("Refresh: 0; url=./details.php?id=$reseedid&reseed=1");
?>
