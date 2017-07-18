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
$HTMLOUT.= "<table class='main' width='750px' border='0' cellspacing='0' cellpadding='0'>" . "<tr><td class='embedded'>\n";
$res = sql_query("SELECT userid, acceptedby, offer, torrentid FROM offers WHERE id = $id") or sqlerr(__FILE__, __LINE__);
$arr = mysqli_fetch_assoc($res);
if (($CURUSER['id'] == $arr['userid']) || ($CURUSER['class'] >= UC_MODERATOR) || ($CURUSER['id'] == $arr['acceptedby'])) {
    if ($INSTALLER09['karma'] && isset($CURUSER['seedbonus']) && $arr['torrentid'] != 0) sql_query("UPDATE users SET seedbonus = seedbonus-" . $INSTALLER09['offer_comment_bonus'] . " WHERE id = $arr[acceptedby]") or sqlerr(__FILE__, __LINE__);
    sql_query("UPDATE OFFERSs SET torrentid = 0, acceptedby = 0 WHERE id = $id") or sqlerr(__FILE__, __LINE__);
    $HTMLOUT.= "<h1 align='center'>Success!</h1>" . "<p align='center'>Offer $id (" . htmlspecialchars($arr['offer']) . ") successfully reset.</p>
<p align='center'><a class='altlink' href='viewoffers.php'><b>View all offers</b></a></p><br /><br />";
} else {
    $HTMLOUT.= "<table>
<tr><td class='colhead' align='left'><h1>Error!</h1></td></tr><tr><td align='left'>" . "Sorry, cannot reset a offer when you are not the owner, staff or person filling it.<br /><br /></td></tr>
</table>";
}
$HTMLOUT.= "</td></tr></table>\n";
/////////////////////// HTML OUTPUT //////////////////////////////
echo stdhead('Reset Offer') . $HTMLOUT . stdfoot();
?>
