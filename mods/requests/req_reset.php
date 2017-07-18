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
$HTMLOUT.= "<table class='main' width='750px' border='0' cellspacing='0' cellpadding='0'>" . "<tr><td class='embedded'>\n";
$res = sql_query("SELECT userid, filledby, request, torrentid FROM requests WHERE id = $id") or sqlerr(__FILE__, __LINE__);
$arr = mysqli_fetch_assoc($res);
if (($CURUSER['id'] == $arr['userid']) || ($CURUSER['class'] >= UC_MODERATOR) || ($CURUSER['id'] == $arr['filledby'])) {
    if ($INSTALLER09['karma'] && isset($CURUSER['seedbonus']) && $arr['torrentid'] != 0) sql_query("UPDATE users SET seedbonus = seedbonus-" . $INSTALLER09['req_comment_bonus'] . " WHERE id = $arr[filledby]") or sqlerr(__FILE__, __LINE__);
    sql_query("UPDATE requests SET torrentid = 0, filledby = 0 WHERE id = $id") or sqlerr(__FILE__, __LINE__);
    $HTMLOUT.= "<h1 align='center'>{$lang['reset_success']}</h1>" . "<p align='center'>{$lang['add_request']} $id (" . htmlspecialchars($arr['request']) . "){$lang['reset_successfully']}</p>
<p align='center'><a class='altlink' href='viewrequests.php'><b>{$lang['req_view_all']}</b></a></p><br /><br />";
} else {
    $HTMLOUT.= "<table>
<tr><td class='colhead' align='left'><h1>{$lang['error_error']}</h1></td></tr><tr><td align='left'>" . "{$lang['reset_sorry']}<br /><br /></td></tr>
</table>";
}
$HTMLOUT.= "</td></tr></table>\n";
/////////////////////// HTML OUTPUT //////////////////////////////
echo stdhead('Reset Request') . $HTMLOUT . stdfoot();
?>
