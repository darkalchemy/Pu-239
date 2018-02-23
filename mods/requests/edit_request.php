<?php

global $CURUSER, $site_config, $lang;

$rs = sql_query("SELECT r.*, c.id AS catid, c.name AS catname FROM requests AS r LEFT JOIN categories AS c ON (c.id=r.cat) WHERE r.id = $id") or sqlerr(__FILE__, __LINE__);
$numz = mysqli_fetch_assoc($rs);
if ($CURUSER['id'] != $numz['userid'] && $CURUSER['class'] < UC_MODERATOR) {
    stderr("{$lang['error_error']}", "{$lang['error_not_yours1']}");
}
$s = htmlspecialchars($numz['request']);
$catid = $numz['catid'];
$body = htmlspecialchars($numz['descr']);
$catname = $numz['catname'];
$s2 = "<select name='category'><option value='$catid'> $catname </option>\n";
foreach ($cats as $row) {
    $s2 .= "<option value='".$row['id']."'>".htmlspecialchars($row['name'])."</option>\n";
}
$s2 .= "</select>\n";
$HTMLOUT .= "<br>
<form method='post' name='compose' action='viewrequests.php?id=$id&amp;take_req_edit'><a name='add' id='add'></a>
<table ><tr><td colspan='2'>
<h1>{$lang['details_edit']} $s</h1>
</td></tr>
<tr><td><b>{$lang['add_title']}</b></td>
<td><input type='text' size='40' name='requesttitle' value='{$s}' /><b> {$lang['req_type']}</b> $s2</td></tr>
<tr><td><b>{$lang['add_image']}</b></td><td>
<input type='text' name='picture' size='80' value='' />
<br>{$lang['add_direct_link']}</td></tr>
<tr><td><b>{$lang['add_description']}</b></td>

<td>";
if ($site_config['textbbcode']) {
    require_once INCL_DIR.'bbcode_functions.php';
    $HTMLOUT .= textbbcode('edit_request', 'body', $body);
} else {
    $HTMLOUT .= "<textarea name='body' rows='10' cols='60'>$body</textarea>";
}
$HTMLOUT .= '</td></tr>';
if ($CURUSER['class'] >= UC_MODERATOR) {
    $HTMLOUT .= "<tr><td colspan='2'>{$lang['edit_staff']}</td></tr>
    <tr><td><b>{$lang['details_filled']}</b></td>
    <td><input type='checkbox' name='filled'".(0 != $numz['torrentid'] ? ' checked' : '')." /></td></tr>
    <tr><td><b>{$lang['edit_filled_by']}</b></td><td>
    <input type='text' size='10' value='$numz[filledby]' name='filledby' /></td></tr>
    <tr><td>
    <b>{$lang['edit_torrent_id']}</b></td><td><input type='text' size='10' name='torrentid' value='$numz[torrentid]' /></td></tr>";
}
$HTMLOUT .= "<tr><td colspan='2'><input type='submit' value='{$lang['details_edit']}' class='button is-small' /></td></tr></table></form><br>\n";
/////////////////////// HTML OUTPUT //////////////////////////////
echo stdhead('Edit Request').$HTMLOUT.stdfoot();
