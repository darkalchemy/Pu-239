<?php

require_once dirname(__FILE__, 2).DIRECTORY_SEPARATOR.'include'.DIRECTORY_SEPARATOR.'bittorrent.php';
require_once INCL_DIR.'user_functions.php';
check_user_status();
global $CURUSER, $site_config, $cache;

$lang = array_merge(load_language('global'), load_language('setclass'));
$HTMLOUT = '';
if ($CURUSER['class'] < UC_STAFF || 255 != $CURUSER['override_class']) {
    stderr('Error', 'whats the story?');
}
if (isset($_GET['action']) && 'editclass' == htmlsafechars($_GET['action'])) {
    $newclass = (int) $_GET['class'];
    $returnto = htmlsafechars($_GET['returnto']);
    sql_query('UPDATE users SET override_class = '.sqlesc($newclass).' WHERE id = '.sqlesc($CURUSER['id']));
    $cache->update_row('user'.$CURUSER['id'], [
        'override_class' => $newclass,
    ], $site_config['expires']['user_cache']);
    header("Location: {$site_config['baseurl']}/".$returnto);
    die();
}

$HTMLOUT .= "<br>
<span class='size_4'><b>{$lang['set_class_allow']}</b></span>
<br><br>
<form method='get' action='{$site_config['baseurl']}/setclass.php'>
    <input type='hidden' name='action' value='editclass' />
    <input type='hidden' name='returnto' value='userdetails.php?id=".(int) $CURUSER['id']."' />
    <table class='table table-bordered table-striped'>
    <tr>
    <td>Class</td>
    <td>
    <select name='class'>";
$maxclass = $CURUSER['class'] - 1;
for ($i = 0; $i <= $maxclass; ++$i) {
    if ('' != trim(get_user_class_name($i))) {
        $HTMLOUT .= "<option value='$i"."'>".get_user_class_name($i)."</option>\n";
    }
}
$HTMLOUT .= "</select></td></tr>
        <tr><td colspan='3'><input type='submit' class='button is-small' value='{$lang['set_class_ok']}' /></td></tr>
    </table>
</form>
<br>";
echo stdhead("{$lang['set_class_temp']}").$HTMLOUT.stdfoot();
