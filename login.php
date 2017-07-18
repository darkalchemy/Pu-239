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
require_once (CLASS_DIR . 'page_verify.php');
dbconn();
global $CURUSER;
if (!$CURUSER) {
    get_template();
}
ini_set('session.use_trans_sid', '0');
$stdfoot = array(
    /** include js **/
    'js' => array(
        'jquery.simpleCaptcha-0.2'
    )
);
$lang = array_merge(load_language('global') , load_language('login'));
$newpage = new page_verify();
$newpage->create('takelogin');
$left = $total = '';
//== 09 failed logins
function left()
{
    global $INSTALLER09;
    $total = 0;
    $ip = getip();
    $fail = sql_query("SELECT SUM(attempts) FROM failedlogins WHERE ip=" . sqlesc($ip)) or sqlerr(__FILE__, __LINE__);
    list($total) = mysqli_fetch_row($fail);
    $left = $INSTALLER09['failedlogins'] - $total;
    if ($left <= 2) $left = "<span style='color:red'>{$left}</span>";
    else $left = "<span style='color:green'>{$left}</span>";
    return $left;
}
//== End Failed logins
$HTMLOUT = '';
unset($returnto);
if (!empty($_GET["returnto"])) {
    $returnto = htmlsafechars($_GET["returnto"]);
    if (!isset($_GET["nowarn"])) {
        $HTMLOUT.= "<div class='login-container center-block'><h1>{$lang['login_not_logged_in']}</h1>\n";
        $HTMLOUT.= "{$lang['login_error']}";
        $HTMLOUT.= "<h4>{$lang['login_cookies']}</h4>
        <h4>{$lang['login_cookies1']}</h4>
        <h4>
        <b>[{$INSTALLER09['failedlogins']}]</b> {$lang['login_failed']}<br />{$lang['login_failed_1']} <b> " . left() . " </b> {$lang['login_failed_2']}</h4></div>";
    }
}
$got_ssl = isset($_SERVER['HTTPS']) && (bool)$_SERVER['HTTPS'] == true ? true : false;
//== click X by Retro
$value = array(
    '...',
    '...',
    '...',
    '...',
    '...',
    '...'
);
$value[rand(1, count($value) - 1) ] = 'X';
$HTMLOUT.= "<script type='text/javascript'>
	  /*<![CDATA[*/
	  $(document).ready(function () {
	  $('#captchalogin').simpleCaptcha();
    });
    /*]]>*/
    </script>
<div class='login-container center-block'>
    <form class='well form-inline' method='post' action='takelogin.php'>
<table class='table  table-bordered center-block'>    
<tr>
    <td>{$lang['login_username']}</td><td align='left'><input type='text' size='40' name='username' /></td></tr>
    <tr><td>{$lang['login_password']}</td><td align='left'><input type='password' size='40' name='password' /></td></tr>
    <tr><td>{$lang['login_use_ssl']}</td>
    <td>
    <label class='label label-inverse' for='ssl'>{$lang['login_ssl1']}&nbsp;<input type='checkbox' name='use_ssl' " . ($got_ssl ? "checked='checked'" : "disabled='disabled' title='SSL connection not available'") . " value='1' id='ssl'/></label><br/>
    <label class='label label-inverse' for='ssl2'>{$lang['login_ssl2']}&nbsp;<input type='checkbox' name='perm_ssl' " . ($got_ssl ? "" : "disabled='disabled' title='SSL connection not available'") . " value='1' id='ssl2'/></label>
    </td>
    </tr>" . ($INSTALLER09['captcha_on'] ? "<tr><td align='center' class='rowhead' colspan='2' id='captchalogin'></td></tr>" : "") . "
    <tr><td colspan='2'><em class='center-block'>{$lang['login_click']}<strong>{$lang['login_x']}</strong></em></td></tr>
    <tr><td colspan='2'>";
for ($i = 0; $i < count($value); $i++) {
    $HTMLOUT.= "<span style='margin-left:9%; float:left;'><input name=\"submitme\" type=\"submit\" value=\"{$value[$i]}\" class=\"btn btn-small\" /></span>";
}
if (isset($returnto)) $HTMLOUT.= "<input type='hidden' name='returnto' value='" . htmlsafechars($returnto) . "' />\n";
$HTMLOUT.= "</td></tr><tr><td colspan='2' class='center-block'><span class='offset1'><em class='btn btn-mini'><strong>{$lang['login_signup']}</strong></em>&nbsp;&nbsp;&nbsp;&nbsp;<em class='btn btn-mini'><strong>{$lang['login_forgot']}</strong></em>&nbsp;&nbsp;&nbsp;&nbsp;<em class='btn btn-mini'><strong>{$lang['login_forgot_1']}</strong></em></span></td></tr></table></form></div>";
echo stdhead("{$lang['login_login_btn']}", true) . $HTMLOUT . stdfoot($stdfoot);
?>
