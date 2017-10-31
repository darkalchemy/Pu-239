<?php
require_once realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..') . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'user_functions.php';
check_user_status();
$lang = array_merge(load_language('global'), load_language('setclass'));
$HTMLOUT = '';
if ($CURUSER['class'] < UC_STAFF or $CURUSER['override_class'] != 255) {
    stderr('Error', 'wots the story ?');
}
if (isset($_GET['action']) && htmlsafechars($_GET['action']) == 'editclass') { //Process the querystring - No security checks are done as a temporary class higher
    //then the actual class mean absoluetly nothing.
    $newclass = (int)$_GET['class'];
    $returnto = htmlsafechars($_GET['returnto']);
    sql_query('UPDATE users SET override_class = ' . sqlesc($newclass) . ' WHERE id = ' . sqlesc($CURUSER['id'])); // Set temporary class
    $mc1->begin_transaction('MyUser_' . $CURUSER['id']);
    $mc1->update_row(false, [
        'override_class' => $newclass,
    ]);
    $mc1->commit_transaction($site_config['expires']['curuser']);
    $mc1->begin_transaction('user' . $CURUSER['id']);
    $mc1->update_row(false, [
        'override_class' => $newclass,
    ]);
    $mc1->commit_transaction($site_config['expires']['user_cache']);
    header("Location: {$site_config['baseurl']}/" . $returnto);
    exit();
}
// HTML Code to allow changes to current class
$HTMLOUT .= "<br>
<font size='4'><b>{$lang['set_class_allow']}</b></font>
<br><br>
<form method='get' action='{$site_config['baseurl']}/setclass.php'>
	<input type='hidden' name='action' value='editclass' />
	<input type='hidden' name='returnto' value='userdetails.php?id=" . (int)$CURUSER['id'] . "' />
	<table class='table table-bordered table-striped'>
	<tr>
	<td>Class</td>
	<td>
	<select name='class'>";
$maxclass = $CURUSER['class'] - 1;
for ($i = 0; $i <= $maxclass; ++$i) {
    if (trim(get_user_class_name($i)) != '') {
        $HTMLOUT .= "<option value='$i" . "'>" . get_user_class_name($i) . "</option>\n";
    }
}
$HTMLOUT .= "</select></td></tr>
		<tr><td colspan='3'><input type='submit' class='button' value='{$lang['set_class_ok']}' /></td></tr>
	</table>
</form>
<br>";
echo stdhead("{$lang['set_class_temp']}") . $HTMLOUT . stdfoot();
