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
$res = sql_query('SELECT * FROM voted_offers WHERE offerid = ' . $id . ' and userid = ' . $CURUSER['id']) or sqlerr(__FILE__, __LINE__);
$arr = mysqli_fetch_assoc($res);
if ($arr) {
    $HTMLOUT.= "
<h3>You've Already Voted</h3>
<p style='text-decoration:underline;'>1 vote per offer is allowed</p>
<p><a class='altlink' href='viewoffers.php?id=$id&amp;offer_details'><b>offer details</b></a> | 
<a class='altlink' href='viewoffers.php'><b>all offers</b></a></p>
<br /><br />";
} else {
    sql_query('UPDATE offers SET hits = hits+1 WHERE id=' . $id) or sqlerr(__FILE__, __LINE__);
    if (mysqli_affected_rows($GLOBALS["___mysqli_ston"])) {
        sql_query('INSERT INTO voted_offers VALUES(0, ' . $id . ', ' . $CURUSER['id'] . ')') or sqlerr(__FILE__, __LINE__);
        $HTMLOUT.= "
<h3>Vote accepted</h3>
<p style='text-decoration:underline;'>Successfully voted for offer $id</p>
<p><a class='altlink' href='viewoffers.php?id=$id&amp;offer_details'><b>offer details</b></a> |
<a class='altlink' href='viewoffers.php'><b>all offers</b></a></p>
<br /><br />";
    } else {
        $HTMLOUT.= "
<h3>Error</h3>
<p style='text-decoration:underline;'>No such ID $id</p>
<p><a class='altlink' href='viewoffers.php?id=$id&amp;offer_details'><b>offer details</b></a> |
<a class='altlink' href='viewoffers.php'><b>all offers</b></a></p>
<br /><br />";
    }
}
/////////////////////// HTML OUTPUT //////////////////////////////
echo stdhead('Vote') . $HTMLOUT . stdfoot();
?>
