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
//==made by putyn
if (!defined('IN_INSTALLER09_ADMIN')) {
    $HTMLOUT = '';
    $HTMLOUT.= "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\"
		\"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
		<html xmlns='http://www.w3.org/1999/xhtml'>
		<head>
		<title>Error!</title>
		</head>
		<body>
	<div style='font-size:33px;color:white;background-color:red;text-align:center;'>Incorrect access<br />You cannot access this file directly.</div>
	</body></html>";
    echo $HTMLOUT;
    exit();
}
require_once (INCL_DIR . 'user_functions.php');
require_once (INCL_DIR . 'html_functions.php');
require_once (CLASS_DIR . 'class_check.php');
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
$lang = array_merge($lang, load_language('ad_floodlimit'));
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $limits = isset($_POST['limit']) && is_array($_POST['limit']) ? $_POST['limit'] : 0;
    foreach ($limits as $class => $limit) if ($limit == 0) unset($limits[$class]);
    if (file_put_contents($INSTALLER09['flood_file'], serialize($limits))) {
        header('Refresh: 2; url=/staffpanel.php?tool=floodlimit');
        stderr($lang['floodlimit_success'], $lang['floodlimit_saved']);
    } else stderr($lang['floodlimit_stderr'],$lang['floodlimit_wentwrong'] . $_file . $lang['floodlimit_exist']);
} else {
    if (!file_exists($INSTALLER09['flood_file']) || !is_array($limit = unserialize(file_get_contents($INSTALLER09['flood_file'])))) $limit = array();
    $out = begin_main_frame() . begin_frame($lang['floodlimit_editflood']);
    $out.= '<form method=\'post\' action=\'\' ><table width=\'60%\' align=\'center\'><tr><td class=\'colhead\'>'.$lang['floodlimit_userclass'].'</td><td class=\'colhead\'>'.$lang['floodlimit_limit'].'</td></tr>';
    for ($i = UC_MIN; $i <= UC_MAX; $i++) $out.= '<tr><td align=\'left\'>' . get_user_class_name($i) . '</td><td><input name=\'limit[' . $i . ']\' type=\'text\' size=\'10\' value=\'' . (isset($limit[$i]) ? $limit[$i] : 0) . '\'/></td></tr>';
    $out.= '<tr><td colspan=\'2\'>'.$lang['floodlimit_note'].'</td></tr><tr><td colspan=\'2\' class=\'colhead\'><input type=\'submit\' value=\''.$lang['floodlimit_save'].'\' /></td></tr>';
    $out.= '</table></form>' . end_frame() . end_main_frame();
    echo (stdhead($lang['floodlimit_std']) . $out . stdfoot());
}
?>
