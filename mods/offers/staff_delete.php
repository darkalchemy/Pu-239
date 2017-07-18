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
if ($CURUSER['class'] >= UC_MODERATOR) {
    if (empty($_POST['deloff'])) stderr('ERROR', "Don't leave any fields blank.");
    sql_query("DELETE FROM offers WHERE id IN (" . implode(", ", array_map("sqlesc", $_POST['deloff'])) . ")");
    sql_query("DELETE FROM voted_offers WHERE offerid IN (" . implode(", ", array_map("sqlesc", $_POST['deloff'])) . ")");
    sql_query("DELETE FROM comments WHERE offer IN (" . implode(", ", array_map("sqlesc", $_POST['deloff'])) . ")");
    header('Refresh: 0; url=viewoffers.php');
    die();
} else stderr('ERROR', 'tweedle-dee tweedle-dum');
?>
