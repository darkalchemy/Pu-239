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
$res = sql_query('SELECT userid, request FROM requests WHERE id = ' . $id) or sqlerr(__FILE__, __LINE__);
$num = mysqli_fetch_assoc($res);
if ($CURUSER['id'] != $num['userid'] && $CURUSER['class'] < UC_MODERATOR) stderr("{$lang['error_error']}", "{$lang['error_not_yours']}");
if (!isset($_GET['sure'])) stderr("{$lang['details_delete']}", "{$lang['del_req']}\n <a class='altlink' href='viewrequests.php?id=$id&amp;del_req&amp;sure=1'>{$lang['add_rules6']}</a>{$lang['del_req1']}", false);
else {
    sql_query('DELETE FROM requests WHERE id = ' . $id) or sqlerr(__FILE__, __LINE__);
    sql_query('DELETE FROM voted_requests WHERE requestid = ' . $id) or sqlerr(__FILE__, __LINE__);
    sql_query('DELETE FROM comments WHERE request = ' . $id) or sqlerr(__FILE__, __LINE__);
    write_log('Request: ' . $id . ' (' . $num['request'] . ') was deleted from the Request section by ' . $CURUSER['username']);
    header('Refresh: 0; url=viewrequests.php');
}
?>
