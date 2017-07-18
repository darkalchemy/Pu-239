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
if ($CURUSER['class'] >= UC_MODERATOR) {
    if (empty($_POST['delreq'])) stderr("{$lang['error_error']}", "{$lang['error_empty']}");
    sql_query("DELETE FROM requests WHERE id IN (" . implode(", ", array_map("sqlesc", $_POST['delreq'])) . ")");
    sql_query("DELETE FROM voted_requests WHERE requestid IN (" . implode(", ", array_map("sqlesc", $_POST['delreq'])) . ")");
    sql_query("DELETE FROM comments WHERE request IN (" . implode(", ", array_map("sqlesc", $_POST['delreq'])) . ")");
    header('Refresh: 0; url=viewrequests.php');
    die();
} else stderr("{$lang['error_error']}", "{$lang['error_dee']}");
?>
