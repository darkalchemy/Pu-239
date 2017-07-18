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
$rs = sql_query("SELECT o.*, c.id AS catid, c.name AS catname FROM offers AS o LEFT JOIN categories AS c ON (c.id=o.cat) WHERE o.id = $id") or sqlerr(__FILE__, __LINE__);
$numz = mysqli_fetch_assoc($rs);
if ($CURUSER['id'] != $numz['userid'] && $CURUSER['class'] < UC_MODERATOR) stderr('Error!', 'This is not your offer to edit.');
$s = htmlspecialchars($numz['offer']);
$catid = $numz['catid'];
$body = htmlspecialchars($numz['descr']);
$catname = $numz['catname'];
$s2 = "<select name='category'><option value='$catid'> $catname </option>\n";
foreach ($cats as $row) $s2.= "<option value='" . $row['id'] . "'>" . htmlspecialchars($row['name']) . "</option>\n";
$s2.= "</select>\n";
$HTMLOUT.= "<br />
<form method='post' name='compose' action='viewoffers.php?id=$id&amp;take_offer_edit'><a name='add' id='add'></a>
<table border='1' cellspacing='0' cellpadding='5'><tr><td align='left' colspan='2'>
<h1 align='center'>Edit Offer : $s</h1>
</td></tr>
<tr><td align='right'><b>Title</b></td>
<td align='left'><input type='text' size='40' name='offertitle' value='{$s}' /><b> Type</b> $s2</td></tr>
<tr><td align='right' valign='top'><b>Image</b></td><td align='left'>
<input type='text' name='picture' size='80' value='' />
<br />(Direct link to image. NO TAG NEEDED! Will be shown in description)</td></tr>
<tr><td align='right'><b>Description</b></td>

<td align='left'>";
if ($INSTALLER09['textbbcode']) {
    require_once (INCL_DIR . 'bbcode_functions.php');
    $HTMLOUT.= textbbcode('edit_offer', 'body', $body);
} else $HTMLOUT.= "<textarea name='body' rows='10' cols='60'>$body</textarea>";
$HTMLOUT.= '</td></tr>';
if ($CURUSER['class'] >= UC_MODERATOR) {
    $HTMLOUT.= "<tr><td align='center' colspan='2'>Staff Only</td></tr>
    <tr><td align='right'><b>Filled</b></td>
    <td><input type='checkbox' name='filled'" . ($numz['torrentid'] != 0 ? " checked='checked'" : '') . " /></td></tr>
    <tr><td align='right'><b>Accepted by ID</b></td><td>
    <input type='text' size='10' value='$numz[acceptedby]' name='acceptedby' /></td></tr>
    <tr><td align='right'>
    <b>Torrent ID</b></td><td><input type='text' size='10' name='torrentid' value='$numz[torrentid]' /></td></tr>";
}
$HTMLOUT.= "<tr><td align='center' colspan='2'><input type='submit' value='Edit Offer' class='btn' /></td></tr></table></form><br />\n";
/////////////////////// HTML OUTPUT //////////////////////////////
echo stdhead('Edit Offer') . $HTMLOUT . stdfoot();
?>
