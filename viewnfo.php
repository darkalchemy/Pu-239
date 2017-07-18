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
require_once (INCL_DIR . 'bbcode_functions.php');
dbconn(false);
loggedinorreturn();
$lang = array_merge(load_language('global') , load_language('viewnfo'));
/*
$stdhead = array(
    /** include css **/
/*
    'css' => array(
        'viewnfo'
    )
);
*/
$id = (int) $_GET["id"];
if ($CURUSER['class'] < UC_POWER_USER || !is_valid_id($id))
die;
$r = sql_query("SELECT name, nfo FROM torrents WHERE id=".sqlesc($id)) or sqlerr(__FILE__, __LINE__);
$a = mysqli_fetch_assoc($r) or die("{$lang['text_puke']}");
$HTMLOUT = '';
$HTMLOUT .= "
<div>
<div>{$lang['text_nfofor']}<a href='{$INSTALLER09['baseurl']}/details.php?id=$id'>".htmlsafechars($a['name'])."</a></div>
<div>{$lang['text_forbest']}<a href='ftp://{$_SERVER['HTTP_HOST']}/misc/linedraw.ttf'>{$lang['text_linedraw']}</a>{$lang['text_font']}</div>
<div>
<table border='1' cellspacing='0' cellpadding='5'>
<tr>
<td class='text'>\n";
$HTMLOUT .= " <pre>" . format_urls(htmlsafechars($a['nfo'])) . "</pre>\n";
$HTMLOUT .= " </td>
</tr>
</table>\n";
$HTMLOUT .= " </div>
</div>";
// , true, $stdhead
echo stdhead($lang['text_stdhead']) . $HTMLOUT . stdfoot(); 
?>
