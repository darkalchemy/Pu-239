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
require_once (CACHE_DIR . 'timezones.php');
require_once (CLASS_DIR . 'page_verify.php');
dbconn();
global $CURUSER;
if (!$CURUSER) {
    get_template();
}
$stdfoot = array(
    /** include js **/
    'js' => array(
        'check',
        'jquery.pstrength-min.1.2',
        'jquery.simpleCaptcha-0.2'
    )
);
$lang = array_merge(load_language('global') , load_language('signup'));
$newpage = new page_verify();
$newpage->create('tkIs');
$res = sql_query("SELECT COUNT(*) FROM users") or sqlerr(__FILE__, __LINE__);
$arr = mysqli_fetch_row($res);
if ($arr[0] >= $INSTALLER09['invites']) stderr("Sorry", "The current user account limit (" . number_format($INSTALLER09['invites']) . ") has been reached. Inactive accounts are pruned all the time, please check back again later...");
if (!$INSTALLER09['openreg_invites']) stderr('Sorry', 'Invite Signups are closed presently');
// TIMEZONE STUFF
$offset = (string)$INSTALLER09['time_offset'];
$time_select = "<select name='user_timezone'>";
foreach ($TZ as $off => $words) {
    if (preg_match("/^time_(-?[\d\.]+)$/", $off, $match)) {
        $time_select.= $match[1] == $offset ? "<option value='{$match[1]}' selected='selected'>$words</option>\n" : "<option value='{$match[1]}'>$words</option>\n";
    }
}
$time_select.= "</select>";
// TIMEZONE END
$HTMLOUT = $year = $month = $day = '';
$HTMLOUT.= "
    <script type='text/javascript'>
    /*<![CDATA[*/
    $(function() {
    $('.password').pstrength();
    });
    /*]]>*/
    </script>";
// Normal Entry Point...
$value = array(
    '...',
    '...',
    '...',
    '...',
    '...',
    '...'
);
$value[rand(1, count($value) - 1) ] = 'X';
$HTMLOUT.= "
    <script type='text/javascript'>
	  $(document).ready(function () {
	  $('#captchainvite').simpleCaptcha();
    });
    </script>
    <p>{$lang['signup_cookies']}</p>
    <form method='post' action='{$INSTALLER09['baseurl']}/take_invite_signup.php'>
    <table border='1' cellspacing='0' cellpadding='10'>
    <tr><td align='right' class='heading'>{$lang['signup_uname']}</td><td align='left'><input type='text' size='40' name='wantusername' id='wantusername' onblur='checkit();' /><div id='namecheck'></div></td></tr>
    <tr><td align='right' class='heading'>{$lang['signup_pass']}</td><td align='left'><input class='password' type='password' size='40' name='wantpassword' /></td></tr>
    <tr><td align='right' class='heading'>{$lang['signup_passa']}</td><td align='left'><input type='password' size='40' name='passagain' /></td></tr>
    <tr><td align='right' class='heading'>{$lang['signup_invcode']}</td><td align='left'><input type='text' size='40' name='invite' /></td></tr>
    <tr valign='top'><td align='right' class='heading'>{$lang['signup_email']}</td><td align='left'><input type='text' size='40' name='email' />
    <table width='250' border='0' cellspacing='0' cellpadding='0'><tr><td class='embedded'><span style='font-size: 1em;'>{$lang['signup_valemail']}</span></td></tr>
    </table>
    </td></tr>
    <tr><td align='right' class='heading'>{$lang['signup_timez']}</td><td align='left'>{$time_select}</td></tr>";
//==09 Birthday mod
$year.= "<select name=\"year\">";
$year.= "<option value=\"0000\">{$lang['signup_year']}</option>";
$i = "2030";
while ($i >= 1950) {
    $year.= "<option value=\"" . $i . "\">" . $i . "</option>";
    $i--;
}
$year.= "</select>";
$month.= "<select name=\"month\">
    <option value=\"00\">{$lang['signup_month']}</option>
    <option value=\"01\">{$lang['signup_jan']}</option>
    <option value=\"02\">{$lang['signup_feb']}</option>
    <option value=\"03\">{$lang['signup_mar']}</option>
    <option value=\"04\">{$lang['signup_apr']}</option>
    <option value=\"05\">{$lang['signup_may']}</option>
    <option value=\"06\">{$lang['signup_jun']}</option>
    <option value=\"07\">{$lang['signup_jul']}</option>
    <option value=\"08\">{$lang['signup_aug']}</option>
    <option value=\"09\">{$lang['signup_sep']}</option>
    <option value=\"10\">{$lang['signup_oct']}</option>
    <option value=\"11\">{$lang['signup_nov']}</option>
    <option value=\"12\">{$lang['signup_dec']}</option>
    </select>";
$day.= "<select name=\"day\">";
$day.= "<option value=\"00\">{$lang['signup_day']}</option>";
$i = 1;
while ($i <= 31) {
    if ($i < 10) {
        $day.= "<option value=\"0" . $i . "\">0" . $i . "</option>";
    } else {
        $day.= "<option value=\"" . $i . "\">" . $i . "</option>";
    }
    $i++;
}
$day.= "</select>";
$HTMLOUT.= "<tr><td align='right' class='heading'>{$lang['signup_birth']}<span style='color:red'>*</span></td><td align='left'>" . $year . $month . $day . "</td></tr>";
//==End
//==Passhint
$passhint = "";
$questions = array(
    array(
        "id" => "1",
        "question" => "{$lang['signup_q1']}"
    ) ,
    array(
        "id" => "2",
        "question" => "{$lang['signup_q2']}"
    ) ,
    array(
        "id" => "3",
        "question" => "{$lang['signup_q3']}"
    ) ,
    array(
        "id" => "4",
        "question" => "{$lang['signup_q4']}"
    ) ,
    array(
        "id" => "5",
        "question" => "{$lang['signup_q5']}"
    ) ,
    array(
        "id" => "6",
        "question" => "{$lang['signup_q6']}"
    )
);
foreach ($questions as $sph) {
    $passhint.= "<option value='" . $sph['id'] . "'>" . $sph['question'] . "</option>\n";
}
$HTMLOUT.= "<tr><td align='right' class='heading'>{$lang['signup_select']}</td><td align='left'><select name='passhint'>\n$passhint\n</select></td></tr>
		<tr><td align='right' class='heading'>{$lang['signup_enter']}</td><td align='left'><input type='text' size='40'  name='hintanswer' /><br /><span style='font-size: 1em;'>{$lang['signup_this_answer']}<br />{$lang['signup_this_answer1']}</span></td></tr>	
      <tr><td align='right' class='heading'></td><td align='left'>
      <input type='checkbox' name='rulesverify' value='yes' /> {$lang['signup_rules']}<br />
      <input type='checkbox' name='faqverify' value='yes' /> {$lang['signup_faq']}<br />
      <input type='checkbox' name='ageverify' value='yes' /> {$lang['signup_age']}</td></tr>
      " . ($INSTALLER09['captcha_on'] ? "<tr><td align='center' class='rowhead' colspan='2' id='captchainvite'></td></tr>" : "") . "
      <tr><td align='center' colspan='2'>{$lang['signup_click']} <strong>{$lang['signup_x']}</strong> {$lang['signup_click1']}</td></tr><tr>
      <td colspan='2' align='center'>";
for ($i = 0; $i < count($value); $i++) {
    $HTMLOUT.= "<input name=\"submitme\" type=\"submit\" value=\"" . $value[$i] . "\" class=\"btn\" />";
}
$HTMLOUT.= "</td></tr></table></form>";
echo stdhead('Invites') . $HTMLOUT . stdfoot($stdfoot);
?>
