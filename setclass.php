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
require_once (dirname(__FILE__) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php');
require_once (INCL_DIR . 'user_functions.php');
dbconn(false);
loggedinorreturn();
$lang = array_merge(load_language('global') , load_language('setclass'));
$HTMLOUT = "";
if ($CURUSER['class'] < UC_STAFF OR $CURUSER['override_class'] != 255) stderr("Error", "wots the story ?");
if (isset($_GET["action"]) && htmlsafechars($_GET["action"]) == "editclass") //Process the querystring - No security checks are done as a temporary class higher
{
    //then the actual class mean absoluetly nothing.
    $newclass = (int) $_GET['class'];
    $returnto = htmlsafechars($_GET['returnto']);
    sql_query("UPDATE users SET override_class = " . sqlesc($newclass) . " WHERE id = " . sqlesc($CURUSER['id'])); // Set temporary class
    $mc1->begin_transaction('MyUser_' . $CURUSER['id']);
    $mc1->update_row(false, array(
        'override_class' => $newclass
    ));
    $mc1->commit_transaction($INSTALLER09['expires']['curuser']);
    $mc1->begin_transaction('user' . $CURUSER['id']);
    $mc1->update_row(false, array(
        'override_class' => $newclass
    ));
    $mc1->commit_transaction($INSTALLER09['expires']['user_cache']);
    header("Location: {$INSTALLER09['baseurl']}/" . $returnto);
    die();
}
// HTML Code to allow changes to current class
$HTMLOUT.= "<br />
<font size='4'><b>{$lang['set_class_allow']}</b></font>
<br /><br />
<form method='get' action='{$INSTALLER09['baseurl']}/setclass.php'>
	<input type='hidden' name='action' value='editclass' />
	<input type='hidden' name='returnto' value='userdetails.php?id=" . (int)$CURUSER['id'] . "' />
	<table width='150' border='2' cellspacing='5' cellpadding='5'>
	<tr>
	<td>Class</td>
	<td align='left'>
	<select name='class'>";
$maxclass = $CURUSER['class'] - 1;
for ($i = 0; $i <= $maxclass; ++$i) if (trim(get_user_class_name($i)) != "") $HTMLOUT.= "<option value='$i" . "'>" . get_user_class_name($i) . "</option>\n";
$HTMLOUT.= "</select></td></tr>
		<tr><td colspan='3' align='center'><input type='submit' class='btn' value='{$lang['set_class_ok']}' /></td></tr>
	</table>
</form>
<br />";
echo stdhead("{$lang['set_class_temp']}") . $HTMLOUT . stdfoot();
?>
