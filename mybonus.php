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
require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'include'.DIRECTORY_SEPARATOR.'bittorrent.php');
require_once(INCL_DIR.'user_functions.php');
require_once(INCL_DIR.'html_functions.php');
dbconn(false);
loggedinorreturn();
/*********************************************************************
09 Seedbonus - Credits to Sir_Snugglebunny also the original coders
Updated for 09 - Nov 28th 2009
**********************************************************************/
$lang = array_merge( load_language('global'), load_language('mybonus') );
$stdhead = array(/** include css **/'css' => array('forums'));
if ($INSTALLER09['seedbonus_on'] == 0
//AND $CURUSER['class'] < UC_MAX*/
)
stderr('Information', 'The Karma bonus system is currently offline for maintainance work');

if (function_exists('parked'))
parked();

$HTMLOUT = $class = '';

function I_smell_a_rat($var){
 if ((0 + $var) == 1)
 	$var = 0 + $var;
 else
 	stderr("Error", "I smell a rat!");
}



/////////freeleech
if (isset($_GET["freeleech_success"]) && $_GET["freeleech_success"]){
$freeleech_success = 0 + $_GET["freeleech_success"];
if($freeleech_success != '1' && $freeleech_success != '2')
stderr("Error", "I smell a rat on freeleech!");
if($freeleech_success == '1'){

if ($_GET["norefund"] != '0') {
$HTMLOUT .="<table width='80%'><tr><td class='colhead' align='left' colspan='2'><h1>Success!</h1></td></tr><tr>".
"<td align='left' class='one'><img src='{$INSTALLER09['pic_base_url']}/smilies/karma.gif' alt='good_karma' title='Good Karma' /></td><td align='left' class='one'><b>Congratulations! </b>
{$CURUSER['username']} you have set the tracker <b>Free Leech !</b> <img src='{$INSTALLER09['pic_base_url']}smilies/w00t.gif' alt='w00t' title='W00t' /><br /><br />Remaining ".htmlsafechars($_GET['norefund'])." points have been contributed towards the next freeleech period automatically!".
"<br /> click to go back to your <a class='altlink' href='{$INSTALLER09['baseurl']}/mybonus.php'>Karma Bonus Point</a> page.<br /><br />".
"</td></tr></table>";
echo stdhead($CURUSER['username'] . "'s Karma Bonus Points Page", true, $stdhead) . $HTMLOUT . stdfoot();
} else {
$HTMLOUT .="<table width='80%'><tr><td class='colhead' align='left' colspan='2'><h1>Success!</h1></td></tr><tr>".
"<td align='left' class='one'><img src='{$INSTALLER09['pic_base_url']}smilies/karma.gif' alt='good_karma' title='Good Karma' /></td><td align='left' class='one'><b>Congratulations! </b>
{$CURUSER['username']} you have set the tracker <b>Free Leech !</b> <img src='{$INSTALLER09['pic_base_url']}smilies/w00t.gif' alt='w00t' title='W00t' /><br />".
"<br /> click to go back to your <a class='altlink' href='{$INSTALLER09['baseurl']}/mybonus.php'>Karma Bonus Point</a> page.<br /><br />".
"</td></tr></table>";
echo stdhead($CURUSER['username'] . "'s Karma Bonus Points Page", true, $stdhead) . $HTMLOUT . stdfoot();
}

die;
}
if($freeleech_success == '2'){
$HTMLOUT .="<table width='80%'><tr><td class='colhead' align='left' colspan='2'><h1>Success!</h1></td></tr><tr>".
"<td align='left' class='one'><img src='{$INSTALLER09['pic_base_url']}smilies/karma.gif' alt='good_karma' title='Good Karma' /></td><td align='left' class='one'><b>Congratulations! </b>".
"{$CURUSER['username']} you have contributed towards making the tracker Free Leech ! <img src='{$INSTALLER09['pic_base_url']}smilies/w00t.gif' alt='w00t' title='W00t' /><br />".
"<br /> click to go back to your <a class='altlink' href='{$INSTALLER09['baseurl']}/mybonus.php'>Karma Bonus Point</a> page.<br /><br />".
"</td></tr></table>";
echo stdhead($CURUSER['username'] . "'s Karma Bonus Points Page", true, $stdhead) . $HTMLOUT . stdfoot();
die;
}
}
////////doubleup
if (isset($_GET["doubleup_success"]) && $_GET["doubleup_success"]){
$doubleup_success = 0 + $_GET["doubleup_success"];
if($doubleup_success != '1' && $doubleup_success != '2')
stderr("Error", "I smell a rat on freeleech!");
if($doubleup_success == '1'){

if ($_GET["norefund"] != '0') {
$HTMLOUT .="<table width='80%'><tr><td class='colhead' align='left' colspan='2'><h1>Success!</h1></td></tr><tr>".
"<td align='left' class='one'><img src='{$INSTALLER09['pic_base_url']}smilies/karma.gif' alt='good_karma' title='Good Karma' /></td><td align='left' class='one'><b>Congratulations! </b>
{$CURUSER['username']} you have set the tracker <b>Double Up !</b> <img src='{$INSTALLER09['pic_base_url']}smilies/w00t.gif' alt='w00t' title='W00t' /><br /><br />Remaining ".htmlsafechars($_GET['norefund'])." points have been contributed towards the next Double upload period automatically!".
"<br /> click to go back to your <a class='altlink' href='{$INSTALLER09['baseurl']}/mybonus.php'>Karma Bonus Point</a> page.<br /><br />".
"</td></tr></table>";
echo stdhead($CURUSER['username'] . "'s Karma Bonus Points Page", true, $stdhead) . $HTMLOUT . stdfoot();
} else {
$HTMLOUT .="<table width='80%'><tr><td class='colhead' align='left' colspan='2'><h1>Success!</h1></td></tr><tr>".
"<td align='left' class='one'><img src='{$INSTALLER09['pic_base_url']}smilies/karma.gif' alt='good_karma' title='Good Karma' /></td><td align='left' class='one'><b>Congratulations! </b>
{$CURUSER['username']} you have set the tracker <b>Double Up !</b> <img src={$INSTALLER09['pic_base_url']}smilies/w00t.gif alt='w00t' title='W00t' /><br />".
"<b /r> click to go back to your <a class='altlink' href='{$INSTALLER09['baseurl']}/mybonus.php'>Karma Bonus Point</a> page.<br /><br />".
"</td></tr></table>";
echo stdhead($CURUSER['username'] . "'s Karma Bonus Points Page", true, $stdhead) . $HTMLOUT . stdfoot();
}

die;
}
if($doubleup_success == '2'){
$HTMLOUT .="<table width='80%'><tr><td class='colhead' align='left' colspan='2'><h1>Success!</h1></td></tr><tr>".
"<td align='left' class='one'><img src='{$INSTALLER09['pic_base_url']}smilies/karma.gif' alt='good_karma' title='Good Karma' /></td><td align='left' class='one'><b>Congratulations! </b>".
"{$CURUSER['username']} you have contributed towards making the tracker Double Upload ! <img src='{$INSTALLER09['pic_base_url']}smilies/w00t.gif' alt='w00t' title='W00t' /><br />".
"<br /> click to go back to your <a class='altlink' href='{$INSTALLER09['baseurl']}/mybonus.php'>Karma Bonus Point</a> page.<br /><br />".
"</td></tr></table>";
echo stdhead($CURUSER['username'] . "'s Karma Bonus Points Page", true, $stdhead) . $HTMLOUT . stdfoot();
die;
}
}
/////////Halfdownload
if (isset($_GET["halfdown_success"]) && $_GET["halfdown_success"]){
$halfdown_success = 0 + $_GET["halfdown_success"];
if($halfdown_success != '1' && $halfdown_success != '2')
stderr("Error", "I smell a rat on halfdownload!");
if($halfdown_success == '1'){

if ($_GET["norefund"] != '0') {
$HTMLOUT .="<table width='80%'><tr><td class='colhead' align='left' colspan='2'><h1>Success!</h1></td></tr><tr>".
"<td align='left' class='one'><img src='{$INSTALLER09['pic_base_url']}smilies/karma.gif' alt='good_karma' title='Good Karma' /></td><td align='left' class='one'><b>Congratulations! </b>
{$CURUSER['username']} you have set the tracker <b>Half Download !</b> <img src='{$INSTALLER09['pic_base_url']}smilies/w00t.gif' alt='w00t' title='W00t' /><br /><br />Remaining ".htmlsafechars($_GET['norefund'])." points have been contributed towards the next Half download period automatically!".
"<br /> click to go back to your <a class='altlink' href='{$INSTALLER09['baseurl']}/mybonus.php'>Karma Bonus Point</a> page.<br /><br />".
"</td></tr></table>";
echo stdhead($CURUSER['username'] . "'s Karma Bonus Points Page", true, $stdhead) . $HTMLOUT . stdfoot();
} else {
$HTMLOUT .="<table width='80%'><tr><td class='colhead' align='left' colspan='2'><h1>Success!</h1></td></tr><tr>".
"<td align='left' class='one'><img src='{$INSTALLER09['pic_base_url']}smilies/karma.gif' alt='good_karma' title='Good Karma' /></td><td align='left' class='one'><b>Congratulations! </b>
{$CURUSER['username']} you have set the tracker <b>Half Download !</b> <img src='{$INSTALLER09['pic_base_url']}smilies/w00t.gif' alt='w00t' title='W00t' /><br />".
"<br /> click to go back to your <a class='altlink' href='{$INSTALLER09['baseurl']}/mybonus.php'>Karma Bonus Point</a> page.<br /><br />".
"</td></tr></table>";
echo stdhead($CURUSER['username'] . "'s Karma Bonus Points Page", true, $stdhead) . $HTMLOUT . stdfoot();
}

die;
}
if($halfdown_success == '2'){
$HTMLOUT .="<table width='80%'><tr><td class='colhead' align='left' colspan='2'><h1>Success!</h1></td></tr><tr>".
"<td align='left' class='one'><img src='{$INSTALLER09['pic_base_url']}smilies/karma.gif' alt='good_karma' title='Good Karma' /></td><td align='left' class='one'><b>Congratulations! </b>".
"{$CURUSER['username']} you have contributed towards making the tracker Half Download ! <img src='{$INSTALLER09['pic_base_url']}smilies/w00t.gif' alt='w00t' title='W00t' /><br />".
"<br /> click to go back to your <a class='altlink' href='{$INSTALLER09['baseurl']}/mybonus.php'>Karma Bonus Point</a> page.<br /><br />".
"</td></tr></table>";
echo stdhead($CURUSER['username'] . "'s Karma Bonus Points Page", true, $stdhead) . $HTMLOUT . stdfoot();
die;
}
}
//////////

switch (true){
case (isset($_GET['up_success'])):
I_smell_a_rat($_GET['up_success']);

$amt = (float)$_GET['amt'];

switch ($amt) {
case $amt == 275.0:
$amt = '1 GB';
break;
case $amt == 350.0:
$amt = '2.5 GB';
break;
case $amt == 550.0:
$amt = '5 GB';
break;
case $amt == 1000.0:
$amt = '10 GB';
break;
case $amt == 2000.0:
$amt = '25 GB';
break;
case $amt == 4000.0:
$amt = '50 GB';
break;
case $amt == 8000.0:
$amt = '100 GB';
break;
case $amt == 40000.0:
$amt = '520 GB';
break;
default:
$amt = '1 TB';
}

$HTMLOUT .= "<table align='center' width='80%'><tr><td class='colhead' align='left' colspan='2'><h1>Success!</h1></td></tr><tr>
<td class='one' align='left'><img src='{$INSTALLER09['pic_base_url']}smilies/karma.gif' alt='good_karma' title='Good karma' /></td>
<td class='one' align='left'><b>Congratulations ! </b>".$CURUSER['username']." you have just increased your upload amount by ".$amt."!
<img src='{$INSTALLER09['pic_base_url']}smilies/w00t.gif' alt='w00t' title='w00t' /><br /><br /><br /><br /> click to go back to your 
<a class='altlink' href='{$INSTALLER09['baseurl']}/mybonus.php'>Karma Bonus Point</a> page.<br /><br /></td></tr></table>";
echo stdhead($CURUSER['username'] . "'s Karma Bonus Points Page", true, $stdhead) . $HTMLOUT . stdfoot();
die;

case (isset($_GET['anonymous_success'])):{
I_smell_a_rat($_GET['anonymous_success']);
}
$HTMLOUT .= "<table align='center' width='80%'><tr><td class='colhead' align='left' colspan='2'><h1>Success!</h1></td></tr><tr>
<td class='one' align='left'><img src='{$INSTALLER09['pic_base_url']}smilies/karma.gif' alt='good_karma' title='Good karma' /></td>
<td class='one' align='left'><b>Congratulations ! </b>".$CURUSER['username']." you have just purchased Anonymous profile for 14 days!
<img src='{$INSTALLER09['pic_base_url']}smilies/w00t.gif' alt='w00t' title='w00t' /><br /><br /><br /><br /> click to go back to your 
<a class='altlink' href='{$INSTALLER09['baseurl']}/mybonus.php'>Karma Bonus Point</a> page.<br /><br /></td></tr></table>";
echo stdhead($CURUSER['username'] . "'s Karma Bonus Points Page", true, $stdhead) . $HTMLOUT . stdfoot();
die;

case (isset($_GET['parked_success'])):{
I_smell_a_rat($_GET['parked_success']);
}
$HTMLOUT .= "<table align='center' width='80%'><tr><td class='colhead' align='left' colspan='2'><h1>Success!</h1></td></tr><tr>
<td class='one' align='left'><img src='{$INSTALLER09['pic_base_url']}smilies/karma.gif' alt='good_karma' title='Good karma' /></td>
<td class='one' align='left'><b>Congratulations ! </b>".$CURUSER['username']." you have just purchased parked option for your profile !
<img src='{$INSTALLER09['pic_base_url']}smilies/w00t.gif' alt='w00t' title='w00t' /><br /><br /><br /><br /> click to go back to your 
<a class='altlink' href='{$INSTALLER09['baseurl']}/mybonus.php'>Karma Bonus Point</a> page.<br /><br /></td></tr></table>";
echo stdhead($CURUSER['username'] . "'s Karma Bonus Points Page", true, $stdhead) . $HTMLOUT . stdfoot();
die;

case (isset($_GET['freeyear_success'])):{
I_smell_a_rat($_GET['freeyear_success']);
}
$HTMLOUT .= "<table align='center' width='80%'><tr><td class='colhead' align='left' colspan='2'><h1>Success!</h1></td></tr><tr>
<td class='one' align='left'><img src='{$INSTALLER09['pic_base_url']}smilies/karma.gif' alt='good_karma' title='Good karma' /></td>
<td class='one' align='left'><b>Congratulations ! </b>".$CURUSER['username']." you have just purchased freeleech for one year!
<img src='{$INSTALLER09['pic_base_url']}smilies/w00t.gif' alt='w00t' title='w00t' /><br /><br /><br /><br /> click to go back to your 
<a class='altlink' href='{$INSTALLER09['baseurl']}/mybonus.php'>Karma Bonus Point</a> page.<br /><br /></td></tr></table>";
echo stdhead($CURUSER['username'] . "'s Karma Bonus Points Page", true, $stdhead) . $HTMLOUT . stdfoot();
die;

case (isset($_GET['freeslots_success'])):{
I_smell_a_rat($_GET['freeslots_success']);
}

$HTMLOUT .= "<table align='center' width='80%'><tr><td class='colhead' align='left' colspan='2'><h1>Success!</h1></td></tr><tr>
<td class='one' align='left'><img src='{$INSTALLER09['pic_base_url']}smilies/karma.gif' alt='good_karma' title='Good karma' /></td>
<td class='one' align='left'><b>Congratulations ! </b>".$CURUSER['username']." you have got your self 3 freeleech slots!!
<img src='{$INSTALLER09['pic_base_url']}smilies/w00t.gif' alt='w00t' title='w00t' /><br /><br /><br /><br /> click to go back to your 
<a class='altlink' href='{$INSTALLER09['baseurl']}/mybonus.php'>Karma Bonus Point</a> page.<br /><br /></td></tr></table>";
echo stdhead($CURUSER['username'] . "'s Karma Bonus Points Page", true, $stdhead) . $HTMLOUT . stdfoot();
die;

case (isset($_GET['itrade_success'])):{
I_smell_a_rat($_GET['itrade_success']);
}

$HTMLOUT .= "<table align='center' width='80%'><tr><td class='colhead' align='left' colspan='2'><h1>Success!</h1></td></tr><tr>
<td class='one' align='left'><img src='{$INSTALLER09['pic_base_url']}smilies/karma.gif' alt='good_karma' title='Good karma' /></td>
<td class='one' align='left'><b>Congratulations ! </b>".$CURUSER['username']." you have got your self 200 points !!
<img src='{$INSTALLER09['pic_base_url']}smilies/w00t.gif' alt='w00t' title='w00t' /><br /><br /><br /><br /> click to go back to your 
<a class='altlink' href='{$INSTALLER09['baseurl']}/mybonus.php'>Karma Bonus Point</a> page.<br /><br /></td></tr></table>";
echo stdhead($CURUSER['username'] . "'s Karma Bonus Points Page", true, $stdhead) . $HTMLOUT . stdfoot();
die;

case (isset($_GET['itrade2_success'])):{
I_smell_a_rat($_GET['itrade2_success']);
}

$HTMLOUT .= "<table align='center' width='80%'><tr><td class='colhead' align='left' colspan='2'><h1>Success!</h1></td></tr><tr>
<td class='one' align='left'><img src='{$INSTALLER09['pic_base_url']}smilies/karma.gif' alt='good_karma' title='Good Karma' /></td>
<td class='one' align='left'><b>Sorry ! </b>".$CURUSER['username']." you just got yourself 2 freeslots !!
<img src='{$INSTALLER09['pic_base_url']}smilies/w00t.gif' alt='W00t' title='W00t' /><br /><br /><br /><br /> click to go back to your 
<a class='altlink' href='{$INSTALLER09['baseurl']}/mybonus.php'>Karma Bonus Point</a> page.<br /><br /></td></tr></table>";
echo stdhead($CURUSER['username'] . "'s Karma Bonus Points Page", true, $stdhead) . $HTMLOUT . stdfoot();
die;

case (isset($_GET['pirate_success'])):{
I_smell_a_rat($_GET['pirate_success']);
} 
$HTMLOUT .="<table align='center' width='80%'><tr><td class='colhead' align='left' colspan='2'><h1>Success!</h1></td></tr>
<tr><td align='left' class='one'><img src='{$INSTALLER09['pic_base_url']}smilies/pirate2.gif' alt='good_karma' title='Good karma' /></td><td align='left' class='one'>
<b>Congratulations! </b>".$CURUSER['username']." you have got yourself Pirate Status and Freeleech for two weeks! <img src='{$INSTALLER09['pic_base_url']}smilies/w00t.gif' alt='w00t' title='w00t' /><br />
<br /> Click to go back to your <a class='altlink' href='{$INSTALLER09['baseurl']}/mybonus.php'>Karma Points</a> page.<br /><br /></td></tr></table>";
echo stdhead($CURUSER['username'] . "'s Karma Bonus Points Page", true, $stdhead) . $HTMLOUT . stdfoot();
die;

case (isset($_GET['king_success'])):{
I_smell_a_rat($_GET['king_success']);
} 
$HTMLOUT .="<table align='center' width='80%'><tr><td class='colhead' align='left' colspan='2'><h1>Success!</h1></td></tr>
<tr><td align='left' class='one'><img src='{$INSTALLER09['pic_base_url']}smilies/king.gif' alt='good_karma' title='Good karma' /></td><td align='left' class='one'>
<b>Congratulations! </b>".$CURUSER['username']." you have got yourself King Status and Freeleech for one month! <img src='{$INSTALLER09['pic_base_url']}smilies/w00t.gif' alt='w00t' title='w00t' /><br />
<br /> Click to go back to your <a class='altlink' href='{$INSTALLER09['baseurl']}/mybonus.php'>Karma Points</a> page.<br /><br /></td></tr></table>";
echo stdhead($CURUSER['username'] . "'s Karma Bonus Points Page", true, $stdhead) . $HTMLOUT . stdfoot();
die;

case (isset($_GET['dload_success']));{
I_smell_a_rat($_GET['dload_success']);
}

$amt = (float)$_GET['amt'];

switch ($amt) {
case $amt == 150.0:
    $amt = '1 GB';
    break;
case $amt == 300.0:
    $amt = '2.5 GB';
    break;
default:
    $amt = '5 GB';
}

$HTMLOUT .="<table width='80%'><tr><td class='colhead' align='left' colspan='2'><h1>Success!</h1></td></tr><tr>".
"<td class='one align='left'><img src='{$INSTALLER09['pic_base_url']}smilies/karma.gif' alt='good_karma' title='Good Karma' /></td>".
"<td class='one' align='left'><b>Congratulations ! </b>".$CURUSER['username']." you have just decreased your download amount by ".$amt."!".
"<img src='{$INSTALLER09['pic_base_url']}smilies/w00t.gif' alt='w00t' /><br /><br /><br /><br /> click to go back to your ".
"<a class='altlink' href='{$INSTALLER09['baseurl']}/mybonus.php'>Karma Bonus Point</a> page.<br /><br /></td></tr></table>";
echo stdhead($CURUSER['username'] . "'s Karma Bonus Points Page", true, $stdhead) . $HTMLOUT . stdfoot();
die;

case (isset($_GET['class_success'])):
I_smell_a_rat($_GET['class_success']);
 
$HTMLOUT .="<table align='center' width='80%'><tr><td class='colhead' align='left' colspan='2'><h1>Success!</h1></td></tr>
<tr><td align='left' class='one'><img src='{$INSTALLER09['pic_base_url']}smilies/karma.gif' alt='good_karma' title='Good karma' /></td><td align='left' class='one'>
<b>Congratulations! </b>".$CURUSER['username']." you have got yourself VIP Status for one month! <img src='{$INSTALLER09['pic_base_url']}smilies/w00t.gif' alt='w00t' title='w00t' /><br />
<br /> Click to go back to your <a class='altlink' href='{$INSTALLER09['baseurl']}/mybonus.php'>Karma Points</a> page.<br /><br /></td></tr></table>";
echo stdhead($CURUSER['username'] . "'s Karma Bonus Points Page", true, $stdhead) . $HTMLOUT . stdfoot();
die;

case (isset($_GET['smile_success'])):
I_smell_a_rat($_GET['smile_success']);
 
$HTMLOUT .="<table align='center' width='80%'><tr><td class='colhead' align='left' colspan='2'><h1>Success!</h1></td></tr>
<tr><td align='left' class='one'><img src='{$INSTALLER09['pic_base_url']}smilies/karma.gif' alt='good_karma' title='Good karma' /></td><td align='left' class='one'>
<b>Congratulations! </b>".$CURUSER['username']." you have got yourself a set of custom smilies for one month! <img src='{$INSTALLER09['pic_base_url']}smilies/w00t.gif' alt='w00t' title='w00t' /><br />
<br /> Click to go back to your <a class='altlink' href='{$INSTALLER09['baseurl']}/mybonus.php'>Karma Points</a> page.<br /><br /></td></tr></table>";
echo stdhead($CURUSER['username'] . "'s Karma Bonus Points Page", true, $stdhead) . $HTMLOUT . stdfoot();
die;

case (isset($_GET['warning_success'])):
I_smell_a_rat($_GET['warning_success']);
 
$HTMLOUT .="<table align='center' width='80%'><tr><td class='colhead' align='left' colspan='2'><h1>Success!</h1></td></tr>
<tr><td align='left' class='one'><img src='{$INSTALLER09['pic_base_url']}smilies/karma.gif' alt='good_karma' title='Good karma' /></td><td align='left' class='one'>
<b>Congratulations! </b>".$CURUSER['username']." you have removed your warning for the low price of 1000 points!! <img src='{$INSTALLER09['pic_base_url']}smilies/w00t.gif' alt='w00t' title='w00t' /><br />
<br /> Click to go back to your <a class='altlink' href='{$INSTALLER09['baseurl']}/mybonus.php'>Karma Points</a> page.<br /><br /></td></tr></table>";
echo stdhead($CURUSER['username'] . "'s Karma Bonus Points Page", true, $stdhead) . $HTMLOUT . stdfoot();
die;

case (isset($_GET['invite_success'])):
I_smell_a_rat($_GET['invite_success']);
 
$HTMLOUT .="<table align='center' width='80%'><tr><td class='colhead' align='left' colspan='2'><h1>Success!</h1></td></tr><tr><td align='left' class='one'>
<img src='{$INSTALLER09['pic_base_url']}smilies/karma.gif' alt='good_karma' title='Good karma' /></td><td align='left' class='one'>
<b>Congratulations! </b>".$CURUSER['username']." you have got your self 3 new invites! <img src='{$INSTALLER09['pic_base_url']}smilies/w00t.gif' alt='w00t' title='w00t' /><br /><br />
click to go back to your <a class='altlink' href='{$INSTALLER09['baseurl']}/mybonus.php'>Karma Bonus Point</a> page.<br /><br /></td></tr></table>";
echo stdhead($CURUSER['username'] . "'s Karma Bonus Points Page", true, $stdhead) . $HTMLOUT . stdfoot();
die;


case (isset($_GET['freeslots_success'])):
I_smell_a_rat($_GET['freeslots_success']);

$HTMLOUT .="<table align='center' width='80%'><tr><td class='colhead' align='left' colspan='2'><h1>Success!</h1></td></tr><tr><td align='left' class='one'>
<img src='{$INSTALLER09['pic_base_url']}smilies/karma.gif' alt='good_karma' title='Good karma' /></td><td align='left' class='one'>
<b>Congratulations! </b>".$CURUSER['username']." you have got your self 3 freeleech slots! <img src='{$INSTALLER09['pic_base_url']}smilies/w00t.gif' alt='w00t' title='w00t' /><br /><br />
click to go back to your <a class='altlink' href='{$INSTALLER09['baseurl']}/mybonus.php'>Karma Bonus Point</a> page.<br /><br /></td></tr></table>";
echo stdhead($CURUSER['username'] . "'s Karma Bonus Points Page", true, $stdhead) . $HTMLOUT . stdfoot();
die;

case (isset($_GET['title_success'])):
I_smell_a_rat($_GET['title_success']);
 
$HTMLOUT .="<table align='center' width='80%'><tr><td class='colhead' align='left' colspan='2'><h1>Success!</h1></td></tr><tr>
<td align='left' class='one'><img src='{$INSTALLER09['pic_base_url']}smilies/karma.gif' alt='good_karma' title='Good karma' /></td><td align='left' class='one'>
<b>Congratulations! </b>".$CURUSER['username']." you are now known as <b>".$CURUSER['title']."</b>! <img src='{$INSTALLER09['pic_base_url']}smilies/w00t.gif' alt='w00t' title='w00t' /><br />
<br /> click to go back to your <a class='altlink' href='{$INSTALLER09['baseurl']}/mybonus.php'>Karma Bonus Point</a> page.<br /><br /></td></tr></table>";
echo stdhead($CURUSER['username'] . "'s Karma Bonus Points Page", true, $stdhead) . $HTMLOUT . stdfoot();
die;

case (isset($_GET['ratio_success'])):
I_smell_a_rat($_GET['ratio_success']);

$HTMLOUT .="<table align='center' width='80%'><tr><td class='colhead' align='left' colspan='2'><h1>Success!</h1></td></tr><tr>
<td align='left' class='one'><img src='{$INSTALLER09['pic_base_url']}smilies/karma.gif' alt='good_karma' title='Good karma' /></td><td align='left' class='one'><b>Congratulations! </b> ".$CURUSER['username']." you
have gained a 1 to 1 ratio on the selected torrent, and the difference in MB has been added to your total upload! <img src='{$INSTALLER09['pic_base_url']}smilies/w00t.gif' alt='w00t' title='w00t' /><br />
<br /> click to go back to your <a class='altlink' href='{$INSTALLER09['baseurl']}/mybonus.php'>Karma Bonus Point</a> page.<br /><br />
</td></tr></table>";
echo stdhead($CURUSER['username'] . "'s Karma Bonus Points Page", true, $stdhead) . $HTMLOUT . stdfoot();
die;

case (isset($_GET['gift_fail'])):
I_smell_a_rat($_GET['gift_fail']);

$HTMLOUT .="<table align='center' width='80%'><tr><td class='colhead' align='left' colspan='2'><h1>Huh?</h1></td></tr><tr><td align='left' class='one'>
<img src='{$INSTALLER09['pic_base_url']}smilies/cry.gif' alt='bad_karma' title='Bad karma' /></td><td align='left' class='one'><b>Not so fast there Mr. fancy pants!</b><br />
<b>".$CURUSER['username']."...</b> you can not spread the karma to yourself...<br />If you want to spread the love, pick another user! <br />
<br /> click to go back to your <a class='altlink' href='{$INSTALLER09['baseurl']}/mybonus.php'>Karma Bonus Point</a> page.<br /><br /></td></tr></table>";
echo stdhead($CURUSER['username'] . "'s Karma Bonus Points Page", true, $stdhead) . $HTMLOUT . stdfoot();
die;

case (isset($_GET['gift_fail_user'])):
I_smell_a_rat($_GET['gift_fail_user']);

$HTMLOUT .="<table align='center' width='80%'><tr><td class='colhead' align='left' colspan='2'><h1>Error</h1></td></tr><tr><td align='left' class='one'>
<img src='{$INSTALLER09['pic_base_url']}smilies/cry.gif' alt='bad_karma' title='Bad karma' /></td><td align='left' class='one'><b>Sorry ".$CURUSER['username']."...</b>
<br /> No User with that username <br /><br /> click to go back to your <a class='altlink' href='{$INSTALLER09['baseurl']}/mybonus.php'>Karma Bonus Point</a> page.
<br /><br /></td></tr></table>";
echo stdhead($CURUSER['username'] . "'s Karma Bonus Points Page", true, $stdhead) . $HTMLOUT . stdfoot();
die;

case (isset($_GET['bump_success']) && $_GET['bump_success'] == 1):
$res_free = sql_query('SELECT id,name FROM torrents WHERE id = '.sqlesc(0 + $_GET['t_name'])) or sqlerr(__FILE__, __LINE__);
$arr_free = mysqli_fetch_assoc($res_free);
stderr('Success!', '<img src="pic/smilies/karma.gif" alt="good karma" /> <b>Congratulations '.$CURUSER['username'].'!!!</b> <img src="pic/smilies/karma.gif" alt="good karma" /><br />  you have ReAnimated the torrent <b><a class="altlink" href="details.php?id='.$arr_free['id'].'">'.htmlsafechars($arr_free['name']).'</a></b>! Bringing it back to page one! <img src="pic/smilies/w00t.gif" alt="w00t" /><br /><br />
Click to go back to your <a class="altlink" href="mybonus.php">Karma Points</a> page.<br /><br />');
echo stdhead($CURUSER['username'] . "'s Karma Bonus Points Page", true, $stdhead) . $HTMLOUT . stdfoot();
die;

case (isset($_GET['gift_fail_points'])):
I_smell_a_rat($_GET['gift_fail_points']);

$HTMLOUT .="<table align='center' width='80%'><tr><td class='colhead' align='left' colspan='2'><h1>Oops!</h1></td></tr><tr><td align='left' class='one'>
<img src='{$INSTALLER09['pic_base_url']}smilies/cry.gif' alt='oups' title='Bad karma' /></td><td align='left' class='one'><b>Sorry </b>".$CURUSER['username']." you dont have enough Karma points
<br /> go back to your <a class='altlink' href='{$INSTALLER09['baseurl']}/mybonus.php'>Karma Bonus Point</a> page.<br /><br /></td></tr></table>";
echo stdhead($CURUSER['username'] . "'s Karma Bonus Points Page", true, $stdhead) . $HTMLOUT . stdfoot();
die;

case (isset($_GET['gift_success'])): 
I_smell_a_rat($_GET['gift_success']);
 
$HTMLOUT  .="<table align='center' width='80%'><tr><td class='colhead' align='left' colspan='2'><h1>Success!</h1></td></tr><tr><td align='left' class='one'>
<img src='{$INSTALLER09['pic_base_url']}smilies/karma.gif' alt='good_karma' title='Good karma' /></td><td align='left' class='one'><b>Congratulations! ".$CURUSER['username']." </b>
you have spread the Karma well.<br /><br />Member <b>".htmlsafechars($_GET['usernamegift'])."</b> will be pleased with your kindness!<br /><br />This is the message that was sent:<br />
<b>Subject:</b> Someone Loves you!<br /> <p>You have been given a gift of <b>".(0 + $_GET['gift_amount_points'])."</b> Karma points by ".$CURUSER['username']."</p><br />
You may also <a class='altlink' href='{$INSTALLER09['baseurl']}/pm_system.php?action=send_message&amp;receiver=".(0 + $_GET['gift_id'])."'>send ".htmlsafechars($_GET['usernamegift'])." a message as well</a>, or go back to your <a class='altlink' href='mybonus.php'>Karma Bonus Point</a> page.<br /><br /></td></tr></table>";
echo stdhead($CURUSER['username'] . "'s Karma Bonus Points Page", true, $stdhead) . $HTMLOUT . stdfoot();
die;

case (isset($_GET['bounty_success'])):{
I_smell_a_rat($_GET['bounty_success']);
} 
$HTMLOUT .="<table align='center' width='80%'><tr><td class='colhead' align='left' colspan='2'><h1>Success!</h1></td></tr>
<tr><td align='left' class='one'><img src='{$INSTALLER09['pic_base_url']}smilies/pirate2.gif' alt='good_karma' title='Good karma' /></td><td align='left' class='one'>
<b>Congratulations! </b>".$CURUSER['username']." you have got yourself bounty and robbed many users of there reputation points! <img src='{$INSTALLER09['pic_base_url']}smilies/w00t.gif' alt='w00t' title='w00t' /><br />
<br /> Click to go back to your <a class='altlink' href='{$INSTALLER09['baseurl']}/mybonus.php'>Karma Points</a> page.<br /><br /></td></tr></table>";
echo stdhead($CURUSER['username'] . "'s Karma Bonus Points Page", true, $stdhead) . $HTMLOUT . stdfoot();
die;

case (isset($_GET['reputation_success'])):
I_smell_a_rat($_GET['reputation_success']);

$HTMLOUT .="<table align='center' width='80%'><tr><td class='colhead' align='left' colspan='2'><h1>Success!</h1></td></tr><tr><td align='left' class='one'>
<img src='{$INSTALLER09['pic_base_url']}smilies/karma.gif' alt='good_karma' title='Good karma' /></td><td align='left' class='one'>
<b>Congratulations! </b>".$CURUSER['username']." you have got your 100 rep points! <img src='{$INSTALLER09['pic_base_url']}smilies/w00t.gif' alt='w00t' title='w00t' /><br /><br />
click to go back to your <a class='altlink' href='{$INSTALLER09['baseurl']}/mybonus.php'>Karma Bonus Point</a> page.<br /><br /></td></tr></table>";
echo stdhead($CURUSER['username'] . "'s Karma Bonus Points Page", true, $stdhead) . $HTMLOUT . stdfoot();
die;

case (isset($_GET['immunity_success'])):
I_smell_a_rat($_GET['immunity_success']);

$HTMLOUT .="<table align='center' width='80%'><tr><td class='colhead' align='left' colspan='2'><h1>Success!</h1></td></tr><tr><td align='left' class='one'>
<img src='{$INSTALLER09['pic_base_url']}smilies/yay.gif' alt='good_karma' title='Good karma' /></td><td align='left' class='one'>
<b>Congratulations! </b>".$CURUSER['username']." you have got yourself immuntiy from auto hit and run warnings and auto leech warnings ! <img src='{$INSTALLER09['pic_base_url']}smilies/w00t.gif' alt='w00t' title='w00t' /><br /><br />
click to go back to your <a class='altlink' href='{$INSTALLER09['baseurl']}/mybonus.php'>Karma Bonus Point</a> page.<br /><br /></td></tr></table>";
echo stdhead($CURUSER['username'] . "'s Karma Bonus Points Page", true, $stdhead) . $HTMLOUT . stdfoot();
die;

case (isset($_GET['userblocks_success'])):
I_smell_a_rat($_GET['userblocks_success']);

$HTMLOUT .="<table align='center' width='80%'><tr><td class='colhead' align='left' colspan='2'><h1>Success!</h1></td></tr><tr><td align='left' class='one'>
<img src='{$INSTALLER09['pic_base_url']}smilies/karma.gif' alt='good_karma' title='Good karma' /></td><td align='left' class='one'>
<b>Congratulations! </b>".$CURUSER['username']." you have got yourself access to control the site user blocks! <img src='{$INSTALLER09['pic_base_url']}smilies/w00t.gif' alt='w00t' title='w00t' /><br /><br />
click to go back to your <a class='altlink' href='{$INSTALLER09['baseurl']}/mybonus.php'>Karma Bonus Point</a> page.<br /><br /></td></tr></table>";
echo stdhead($CURUSER['username'] . "'s Karma Bonus Points Page", true, $stdhead) . $HTMLOUT . stdfoot();
die;

case (isset($_GET['user_unlocks_success'])):
I_smell_a_rat($_GET['user_unlocks_success']);

$HTMLOUT .="<table align='center' width='80%'><tr><td class='colhead' align='left' colspan='2'><h1>Success!</h1></td></tr><tr><td align='left' class='one'>
<img src='{$INSTALLER09['pic_base_url']}smilies/karma.gif' alt='good_karma' title='Good karma' /></td><td align='left' class='one'>
<b>Congratulations! </b>".$CURUSER['username']." you have got yourself unlocked bonus moods for use on site! <img src='{$INSTALLER09['pic_base_url']}smilies/w00t.gif' alt='w00t' title='w00t' /><br /><br />
click to go back to your <a class='altlink' href='{$INSTALLER09['baseurl']}/mybonus.php'>Karma Bonus Point</a> page.<br /><br /></td></tr></table>";
echo stdhead($CURUSER['username'] . "'s Karma Bonus Points Page", true, $stdhead) . $HTMLOUT . stdfoot();
die;

}

//=== exchange
if (isset($_GET['exchange'])){
I_smell_a_rat($_GET['exchange']);

$userid = (int)$CURUSER['id'];
if (!is_valid_id($userid))
stderr("Error", "That is not your user ID!");

$option = (int) $_POST['option'];

$res_points = sql_query("SELECT * FROM bonus WHERE id =" . sqlesc($option));
$arr_points = mysqli_fetch_assoc($res_points);

$art = htmlsafechars($arr_points['art']);
$points = (float)$arr_points['points'];
$minpoints = (float)$arr_points['minpoints'];

if ($CURUSER['seedbonus'] <= 0)
stderr("Error", "I smell a rat!");

if ($points <= 0)
stderr("Error", "I smell a rat!");

$sql = sql_query('SELECT uploaded, downloaded, seedbonus, bonuscomment, free_switch, warned, invites, freeslots, reputation '.
                       'FROM users '.
                       'WHERE id = '.sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
      $User = mysqli_fetch_assoc($sql);

$bonus = (float)$User['seedbonus'];
$seedbonus = ($bonus-$points);
$upload = (float)$User['uploaded'];
$download = (float)$User['downloaded'];
$bonuscomment = htmlsafechars($User['bonuscomment']);
$free_switch = (int)$User['free_switch'];
$warned = (int)$User['warned'];
$reputation = (int)$User['reputation'];

if($bonus < $minpoints)
stderr("Sorry", "you do not have enough Karma points!");

switch ($art){

case 'traffic':
//=== trade for one upload credit
$up = $upload + $arr_points['menge'];
$bonuscomment = get_date( TIME_NOW, 'DATE', 1 ) . " - " .$points. " Points for upload bonus.\n " .$bonuscomment;
sql_query("UPDATE users SET uploaded = ".sqlesc($upload + $arr_points['menge']).", seedbonus = ".sqlesc($seedbonus).", bonuscomment = ".sqlesc($bonuscomment)." WHERE id = ".sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
$mc1->begin_transaction('userstats_'.$userid);
$mc1->update_row(false, array('uploaded' => $upload+$arr_points['menge'], 'seedbonus' => $seedbonus));
$mc1->commit_transaction($INSTALLER09['expires']['u_stats']);
$mc1->begin_transaction('user_stats_'.$userid);
$mc1->update_row(false, array('uploaded' => $upload+$arr_points['menge'], 'seedbonus' => $seedbonus, 'bonuscomment' => $bonuscomment));
$mc1->commit_transaction($INSTALLER09['expires']['user_stats']);
header("Refresh: 0; url={$INSTALLER09['baseurl']}/mybonus.php?up_success=1&amt=$points");
die;
break;
 
case 'reputation':
//=== trade for reputation
if ($CURUSER['class'] < UC_POWER_USER || $User['reputation'] >= 5000)
stderr("Error", "Time shall unfold what plighted cunning hides\n\nWho cover faults, at last shame them derides...Sorry your not a Power User or you already have to many rep points :-P<br />go back to your <a class='altlink' href='{$INSTALLER09['baseurl']}/mybonus.php'>Karma Bonus Point</a> page and think that one over.");
$rep = $reputation + $arr_points['menge'];
$bonuscomment = get_date( TIME_NOW, 'DATE', 1 ) . " - " .$points. " Points for 100 rep points.\n " .$bonuscomment;
sql_query("UPDATE users SET reputation = ".sqlesc($rep).", seedbonus = ".sqlesc($seedbonus).", bonuscomment = ".sqlesc($bonuscomment)." WHERE id = ".sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
$mc1->begin_transaction('user'.$userid);
$mc1->update_row(false, array('reputation' => $rep));
$mc1->commit_transaction($INSTALLER09['expires']['user_cache']);
$mc1->begin_transaction('MyUser_'.$userid);
$mc1->update_row(false, array('reputation' => $rep));
$mc1->commit_transaction($INSTALLER09['expires']['curuser']);
$mc1->begin_transaction('userstats_'.$userid);
$mc1->update_row(false, array('seedbonus' => $seedbonus));
$mc1->commit_transaction($INSTALLER09['expires']['u_stats']);
$mc1->begin_transaction('user_stats_'.$userid);
$mc1->update_row(false, array('seedbonus' => $seedbonus, 'bonuscomment' => $bonuscomment));
$mc1->commit_transaction($INSTALLER09['expires']['user_stats']);
header("Refresh: 0; url={$INSTALLER09['baseurl']}/mybonus.php?reputation_success=1");
die;
break;

case 'immunity':
//=== trade for immunity
if ($CURUSER['class'] < UC_POWER_USER || $User['reputation'] < 3000)
stderr("Error", "Time shall unfold what plighted cunning hides\n\nWho cover faults, at last shame them derides...Sorry your not a Power User or you dont have enough rep :-P<br />go back to your <a class='altlink' href='{$INSTALLER09['baseurl']}/mybonus.php'>Karma Bonus Point</a> page and think that one over.");
$bonuscomment = get_date( TIME_NOW, 'DATE', 1 ) . " - " .$points. " Points for 1 years immunity status.\n " .$bonuscomment;
$immunity = (86400 * 365 + TIME_NOW);
sql_query("UPDATE users SET immunity = ".sqlesc($immunity).", seedbonus = ".sqlesc($seedbonus).", bonuscomment = ".sqlesc($bonuscomment)." WHERE id = ".sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
$mc1->begin_transaction('user'.$userid);
$mc1->update_row(false, array('immunity' => $immunity));
$mc1->commit_transaction($INSTALLER09['expires']['user_cache']);
$mc1->begin_transaction('MyUser_'.$userid);
$mc1->update_row(false, array('immunity' => $immunity));
$mc1->commit_transaction($INSTALLER09['expires']['curuser']);
$mc1->begin_transaction('userstats_'.$userid);
$mc1->update_row(false, array('seedbonus' => $seedbonus));
$mc1->commit_transaction($INSTALLER09['expires']['u_stats']);
$mc1->begin_transaction('user_stats_'.$userid);
$mc1->update_row(false, array('seedbonus' => $seedbonus, 'bonuscomment' => $bonuscomment));
$mc1->commit_transaction($INSTALLER09['expires']['user_stats']);
header("Refresh: 0; url={$INSTALLER09['baseurl']}/mybonus.php?immunity_success=1");
die;
break;

case 'userblocks':
//=== trade for userblock access
$reputation = $User['reputation'];
if ($CURUSER['class'] < UC_POWER_USER || $User['reputation'] < 50)
stderr("Error", "Time shall unfold what plighted cunning hides\n\nWho cover faults, at last shame them derides...Sorry your not a Power User or you dont have enough rep points yet - Minimum 50 required :-P<br />go back to your <a class='altlink' href='{$INSTALLER09['baseurl']}/mybonus.php'>Karma Bonus Point</a> page and think that one over.");
$bonuscomment = get_date( TIME_NOW, 'DATE', 1 ) . " - " .$points. " Points for user blocks access.\n " .$bonuscomment;
sql_query("UPDATE users SET got_blocks = 'yes', seedbonus = ".sqlesc($seedbonus).", bonuscomment = ".sqlesc($bonuscomment)." WHERE id = ".sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
$mc1->begin_transaction('user'.$userid);
$mc1->update_row(false, array('got_blocks' => 'yes'));
$mc1->commit_transaction($INSTALLER09['expires']['user_cache']);
$mc1->begin_transaction('MyUser_'.$userid);
$mc1->update_row(false, array('got_blocks' => 'yes'));
$mc1->commit_transaction($INSTALLER09['expires']['curuser']);
$mc1->begin_transaction('userstats_'.$userid);
$mc1->update_row(false, array('seedbonus' => $seedbonus));
$mc1->commit_transaction($INSTALLER09['expires']['u_stats']);
$mc1->begin_transaction('user_stats_'.$userid);
$mc1->update_row(false, array('seedbonus' => $seedbonus, 'bonuscomment' => $bonuscomment));
$mc1->commit_transaction($INSTALLER09['expires']['user_stats']);
header("Refresh: 0; url={$INSTALLER09['baseurl']}/mybonus.php?userblocks_success=1");
die;
break;

case 'userunlock':
//=== trade for user_unlocks access
$reputation = $User['reputation'];
if ($CURUSER['class'] < UC_POWER_USER || $User['reputation'] < 50)
stderr("Error", "Time shall unfold what plighted cunning hides\n\nWho cover faults, at last shame them derides...Sorry your not a Power User or you dont have enough rep points yet - Minimum 50 required :-P<br />go back to your <a class='altlink' href='{$INSTALLER09['baseurl']}/mybonus.php'>Karma Bonus Point</a> page and think that one over.");
$bonuscomment = get_date( TIME_NOW, 'DATE', 1 ) . " - " .$points. " Points for user unlocks access.\n " .$bonuscomment;
sql_query("UPDATE users SET got_moods = 'yes', seedbonus = ".sqlesc($seedbonus).", bonuscomment = ".sqlesc($bonuscomment)." WHERE id = ".sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
$mc1->begin_transaction('user'.$userid);
$mc1->update_row(false, array('got_moods' => 'yes'));
$mc1->commit_transaction($INSTALLER09['expires']['user_cache']);
$mc1->begin_transaction('MyUser_'.$userid);
$mc1->update_row(false, array('got_moods' => 'yes'));
$mc1->commit_transaction($INSTALLER09['expires']['curuser']);
$mc1->begin_transaction('userstats_'.$userid);
$mc1->update_row(false, array('seedbonus' => $seedbonus));
$mc1->commit_transaction($INSTALLER09['expires']['u_stats']);
$mc1->begin_transaction('user_stats_'.$userid);
$mc1->update_row(false, array('seedbonus' => $seedbonus, 'bonuscomment' => $bonuscomment));
$mc1->commit_transaction($INSTALLER09['expires']['user_stats']);
header("Refresh: 0; url={$INSTALLER09['baseurl']}/mybonus.php?user_unlocks_success=1");
die;
break;

case 'anonymous':
//=== trade for 14 days Anonymous profile
$anonymous_until = (86400 * 14 + TIME_NOW);
if ($CURUSER['anonymous_until'] >= 1)
stderr("Error", "Time shall unfold what plighted cunning hides\n\nWho cover faults, at last shame them derides.");
$bonuscomment = get_date( TIME_NOW, 'DATE', 1 ) . " - " .$points. " Points for 14 days Anonymous profile.\n " .$bonuscomment;
sql_query("UPDATE users SET anonymous_until = ".sqlesc($anonymous_until).", seedbonus = ".sqlesc($seedbonus).", bonuscomment = ".sqlesc($bonuscomment)." WHERE id = ".sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
$mc1->begin_transaction('user'.$userid);
$mc1->update_row(false, array('anonymous_until' => $anonymous_until));
$mc1->commit_transaction($INSTALLER09['expires']['user_cache']);
$mc1->begin_transaction('MyUser_'.$userid);
$mc1->update_row(false, array('anonymous_until' => $anonymous_until));
$mc1->commit_transaction($INSTALLER09['expires']['curuser']);
$mc1->begin_transaction('userstats_'.$userid);
$mc1->update_row(false, array('seedbonus' => $seedbonus));
$mc1->commit_transaction($INSTALLER09['expires']['u_stats']);
$mc1->begin_transaction('user_stats_'.$userid);
$mc1->update_row(false, array('seedbonus' => $seedbonus, 'bonuscomment' => $bonuscomment));
$mc1->commit_transaction($INSTALLER09['expires']['user_stats']);
header("Refresh: 0; url={$INSTALLER09['baseurl']}/mybonus.php?anonymous_success=1");
die;
break;

case 'parked':
//=== trade for parked option
$parked_until = 1;
if ($CURUSER['parked_until'] == 1)
stderr("Error", "Time shall unfold what plighted cunning hides\n\nWho cover faults, at last shame them derides.");
$bonuscomment = get_date( TIME_NOW, 'DATE', 1 ) . " - " .$points. " Points for 14 days Anonymous profile.\n " .$bonuscomment;
sql_query("UPDATE users SET parked_until = ".sqlesc($parked_until).", seedbonus = ".sqlesc($seedbonus).", bonuscomment = ".sqlesc($bonuscomment)." WHERE id = ".sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
$mc1->begin_transaction('user'.$userid);
$mc1->update_row(false, array('parked_until' => $parked_until));
$mc1->commit_transaction($INSTALLER09['expires']['user_cache']);
$mc1->begin_transaction('MyUser_'.$userid);
$mc1->update_row(false, array('parked_until' => $parked_until));
$mc1->commit_transaction($INSTALLER09['expires']['curuser']);
$mc1->begin_transaction('userstats_'.$userid);
$mc1->update_row(false, array('seedbonus' => $seedbonus));
$mc1->commit_transaction($INSTALLER09['expires']['u_stats']);
$mc1->begin_transaction('user_stats_'.$userid);
$mc1->update_row(false, array('seedbonus' => $seedbonus, 'bonuscomment' => $bonuscomment));
$mc1->commit_transaction($INSTALLER09['expires']['user_stats']);
header("Refresh: 0; url={$INSTALLER09['baseurl']}/mybonus.php?parked_success=1");
die;
break;

case 'traffic2':
//=== trade for download credit
$down = $download - $arr_points['menge'];
if ($CURUSER['downloaded'] == 0)
stderr("Error", "Time shall unfold what plighted cunning hides\n\nWho cover faults, at last shame them derides.");
$bonuscomment = get_date( TIME_NOW, 'DATE', 1 ) . " - " .$points. " Points for download credit removal.\n " .$bonuscomment;
sql_query("UPDATE users SET downloaded = ".sqlesc($download - $arr_points['menge']).", seedbonus = ".sqlesc($seedbonus).", bonuscomment = ".sqlesc($bonuscomment)." WHERE id = ".sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
$mc1->begin_transaction('userstats_'.$userid);
$mc1->update_row(false, array('downloaded' => $download - $arr_points['menge'], 'seedbonus' => $seedbonus));
$mc1->commit_transaction($INSTALLER09['expires']['u_stats']);
$mc1->begin_transaction('user_stats_'.$userid);
$mc1->update_row(false, array('downloaded' => $download - $arr_points['menge'], 'seedbonus' => $seedbonus, 'bonuscomment' => $bonuscomment));
$mc1->commit_transaction($INSTALLER09['expires']['user_stats']);
header("Refresh: 0; url={$INSTALLER09['baseurl']}/mybonus.php?dload_success=1&amt=$points");
die;
break;

case 'freeyear':
//=== trade for years freeleech
$free_switch = (365 * 86400 + TIME_NOW);
if ($User['free_switch'] != 0)
stderr("Error", "Time shall unfold what plighted cunning hides\n\nWho cover faults, at last shame them derides.");
$bonuscomment = get_date( TIME_NOW, 'DATE', 1 ) . " - " .$points. " Points for One year of freeleech.\n " .$bonuscomment;
sql_query("UPDATE users SET free_switch = ".sqlesc($free_switch).", seedbonus = ".sqlesc($seedbonus).", bonuscomment = ".sqlesc($bonuscomment)." WHERE id = ".sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
$mc1->begin_transaction('user'.$userid);
$mc1->update_row(false, array('free_switch' => $free_switch));
$mc1->commit_transaction($INSTALLER09['expires']['user_cache']);
$mc1->begin_transaction('MyUser_'.$userid);
$mc1->update_row(false, array('free_switch' => $free_switch));
$mc1->commit_transaction($INSTALLER09['expires']['curuser']);
$mc1->begin_transaction('userstats_'.$userid);
$mc1->update_row(false, array('seedbonus' => $seedbonus));
$mc1->commit_transaction($INSTALLER09['expires']['u_stats']);
$mc1->begin_transaction('user_stats_'.$userid);
$mc1->update_row(false, array('seedbonus' => $seedbonus, 'bonuscomment' => $bonuscomment));
$mc1->commit_transaction($INSTALLER09['expires']['user_stats']);
header("Refresh: 0; url={$INSTALLER09['baseurl']}/mybonus.php?freeyear_success=1");
die;
break;

case 'freeslots':
//=== trade for freeslots
$freeslots = (int)$User['freeslots'];
$slots = $freeslots + $arr_points['menge'];
$bonuscomment = get_date( TIME_NOW, 'DATE', 1 ) . " - " .$points. " Points for freeslots.\n " .$bonuscomment;
sql_query("UPDATE users SET freeslots = ".sqlesc($slots).", seedbonus = ".sqlesc($seedbonus).", bonuscomment = ".sqlesc($bonuscomment)." WHERE id = ".sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
$mc1->begin_transaction('user'.$userid);
$mc1->update_row(false, array('freeslots' => $slots));
$mc1->commit_transaction($INSTALLER09['expires']['user_cache']);
$mc1->begin_transaction('MyUser_'.$userid);
$mc1->update_row(false, array('freeslots' => $slots));
$mc1->commit_transaction($INSTALLER09['expires']['curuser']);
$mc1->begin_transaction('userstats_'.$userid);
$mc1->update_row(false, array('seedbonus' => $seedbonus));
$mc1->commit_transaction($INSTALLER09['expires']['u_stats']);
$mc1->begin_transaction('user_stats_'.$userid);
$mc1->update_row(false, array('seedbonus' => $seedbonus, 'bonuscomment' => $bonuscomment));
$mc1->commit_transaction($INSTALLER09['expires']['user_stats']);
header("Refresh: 0; url={$INSTALLER09['baseurl']}/mybonus.php?freeslots_success=1");
die;
break;

case 'itrade':
//=== trade for points
$invites = (int)$User['invites'];
$karma = $User['seedbonus'] + 200;
$inv = $invites - 1;
if ($CURUSER['invites'] == 0)
stderr("Error", "Time shall unfold what plighted cunning hides\n\nWho cover faults, at last shame them derides.");
$bonuscomment = get_date( TIME_NOW, 'DATE', 1 ) . " - " .$points. " invites for bonus points.\n" .$bonuscomment;
sql_query("UPDATE users SET invites = ".sqlesc($inv).", seedbonus = ".sqlesc($karma).", bonuscomment = ".sqlesc($bonuscomment)." WHERE id = ".sqlesc($userid)." AND invites =".sqlesc($invites)) or sqlerr(__FILE__, __LINE__);
$mc1->begin_transaction('user'.$userid);
$mc1->update_row(false, array('invites' => $inv));
$mc1->commit_transaction($INSTALLER09['expires']['user_cache']);
$mc1->begin_transaction('MyUser_'.$userid);
$mc1->update_row(false, array('invites' => $inv));
$mc1->commit_transaction($INSTALLER09['expires']['curuser']);
$mc1->begin_transaction('userstats_'.$userid);
$mc1->update_row(false, array('seedbonus' => $karma));
$mc1->commit_transaction($INSTALLER09['expires']['u_stats']);
$mc1->begin_transaction('user_stats_'.$userid);
$mc1->update_row(false, array('seedbonus' => $karma, 'bonuscomment' => $bonuscomment));
$mc1->commit_transaction($INSTALLER09['expires']['user_stats']);
header("Refresh: 0; url={$INSTALLER09['baseurl']}/mybonus.php?itrade_success=1");
die;
break;

case 'itrade2':
//=== trade for slots
$invites = (int)$User['invites'];
$slots = (int)$User['freeslots'];
$inv = $invites - 1;
$fslot = $slots + 2;
if ($CURUSER['invites'] == 0)
stderr("Error", "Time shall unfold what plighted cunning hides\n\nWho cover faults, at last shame them derides.");
$bonuscomment = get_date( TIME_NOW, 'DATE', 1 ) . " - " .$points. " invites for bonus points.\n" .$bonuscomment;
sql_query("UPDATE users SET invites = ".sqlesc($inv).", freeslots =".sqlesc($fslot).", bonuscomment = ".sqlesc($bonuscomment)." WHERE id = ".sqlesc($userid)." AND invites = ".sqlesc($invites)) or sqlerr(__FILE__, __LINE__);
$mc1->begin_transaction('user'.$userid);
$mc1->update_row(false, array('invites' => $inv, 'freeslots' => $fslot));
$mc1->commit_transaction($INSTALLER09['expires']['user_cache']);
$mc1->begin_transaction('MyUser_'.$userid);
$mc1->update_row(false, array('invites' => $inv, 'freeslots' => $fslot));
$mc1->commit_transaction($INSTALLER09['expires']['curuser']);
$mc1->begin_transaction('userstats_'.$userid);
$mc1->update_row(false, array('seedbonus' => $seedbonus));
$mc1->commit_transaction($INSTALLER09['expires']['u_stats']);
$mc1->begin_transaction('user_stats_'.$userid);
$mc1->update_row(false, array('seedbonus' => $seedbonus, 'bonuscomment' => $bonuscomment));
$mc1->commit_transaction($INSTALLER09['expires']['user_stats']);
header("Refresh: 0; url={$INSTALLER09['baseurl']}/mybonus.php?itrade2_success=1");
die;
break;

case 'pirate':
//=== trade for 2 weeks pirate status 
if ($CURUSER['pirate'] != 0 OR $CURUSER['king'] != 0)
stderr("Error", "Now why would you want to add what you already have?<br />go back to your <a class='altlink' href='{$INSTALLER09['baseurl']}/mybonus.php'>Karma Bonus Point</a> page and think that one over.");
$pirate = (86400 * 14 + TIME_NOW);
$free_switch = (14 * 86400 + TIME_NOW);
$bonuscomment = get_date( TIME_NOW, 'DATE', 1 ) . " - " .$points. " Points for 2 weeks Pirate + freeleech Status.\n " .$bonuscomment;
sql_query("UPDATE users SET free_switch = ".sqlesc($free_switch).", pirate = ".sqlesc($pirate).", seedbonus = ".sqlesc($seedbonus).", bonuscomment = ".sqlesc($bonuscomment)." WHERE id = ".sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
$mc1->begin_transaction('user'.$userid);
$mc1->update_row(false, array('free_switch' => $free_switch, 'pirate' => $pirate));
$mc1->commit_transaction($INSTALLER09['expires']['user_cache']);
$mc1->begin_transaction('MyUser_'.$userid);
$mc1->update_row(false, array('free_switch' => $free_switch, 'pirate' => $pirate));
$mc1->commit_transaction($INSTALLER09['expires']['curuser']);
$mc1->begin_transaction('userstats_'.$userid);
$mc1->update_row(false, array('seedbonus' => $seedbonus));
$mc1->commit_transaction($INSTALLER09['expires']['u_stats']);
$mc1->begin_transaction('user_stats_'.$userid);
$mc1->update_row(false, array('seedbonus' => $seedbonus, 'bonuscomment' => $bonuscomment));
$mc1->commit_transaction($INSTALLER09['expires']['user_stats']);
header("Refresh: 0; url={$INSTALLER09['baseurl']}/mybonus.php?pirate_success=1");
die;
break;

case 'bounty':
//=== trade for pirates bounty
$thief_id = $CURUSER['id'];
$thief_name = $CURUSER['username'];
$thief_rep = (int)$User['reputation'];
$thief_bonus = (float)$User['seedbonus'];
$rep_to_steal = $points/1000;
$new_bonus = $thief_bonus-$points;

$pm = array();
$pm['subject'] = sqlesc("You just got robbed by %s");
$pm['subject_thief'] = sqlesc("Theft summary");
$pm['message'] = sqlesc("Hey\nWe are sorry to announce that you have been robbed by [url=".$INSTALLER09['baseurl']."/userdetails.php?id=%d]%s[/url]\nNow your total reputation is [b]%d[/b]\n[color=#ff0000]This is normal and you should not worry, if you have enough bonus points you can rob other people[/color]");
$pm['message_thief'] = sqlesc("Hey %s\nYou robbed:\n%s\nYour total reputation is now [b]%d[/b] but you lost [b]%d[/b] karma points ");
$foo = array(50=>3,100=>3,150=>4,200=>5,250=>5,300=>6);
$user_limit = isset($foo[$rep_to_steal]) ? $foo[$rep_to_steal] : 0;
$qr = sql_query('select id,username,reputation,seedbonus FROM users WHERE id <> '.$thief_id.' AND reputation >= '.$rep_to_steal.' ORDER BY RAND() LIMIT '.$user_limit) or sqlerr(__FILE__, __LINE__);
$update_users = $pms = $robbed_user = array();
while($ar = mysqli_fetch_assoc($qr)){
	$new_rep = $ar['reputation']-$rep_to_steal;
	$update_users[] = '('.$ar['id'].','.($ar['reputation']-$rep_to_steal).','.$ar['seedbonus'].')';
	$pms[] = '('.$INSTALLER09['bot_id'].','.$ar['id'].','.TIME_NOW.','.sprintf($pm['subject'],$thief_name).','.sprintf($pm['message'],$thief_id,$thief_name,$new_rep).')';
	$robbed_users[] = sprintf('[url='.$INSTALLER09['baseurl'].'/userdetails.php?id=%d]%s[/url]',$ar['id'],$ar['username']);
   //== cache updates ???
   $mc1->begin_transaction('MyUser_'.$ar['id']);
   $mc1->update_row(false, array('reputation' => $ar['reputation']-$rep_to_steal));   
   $mc1->commit_transaction($INSTALLER09['expires']['curuser']);
   $mc1->begin_transaction('user'.$ar['id']);
   $mc1->update_row(false, array('reputation' => $ar['reputation']-$rep_to_steal));   
   $mc1->commit_transaction($INSTALLER09['expires']['user_cache']);
   
   $mc1->begin_transaction('userstats_'.$ar['id']);
   $mc1->update_row(false, array('seedbonus' => $ar['seedbonus']));   
   $mc1->commit_transaction($INSTALLER09['expires']['u_stats']);
   $mc1->begin_transaction('user_stats_'.$ar['id']);
   $mc1->update_row(false, array('seedbonus' => $ar['seedbonus']));   
   $mc1->commit_transaction($INSTALLER09['expires']['user_stats']);
   //$mc1->delete_value('inbox_new_'.$pms);
   //$mc1->delete_value('inbox_new_sb_'.$pms);
   // end
}
if(count($update_users)) {
	$new_bonus = $thief_bonus-$points;
	$new_rep = $thief_rep+($user_limit*$rep_to_steal);
	$update_users[] = '('.$thief_id.','.$new_rep.','.$new_bonus.')';
	$pms[] = '(0,'.$thief_id.','.TIME_NOW.','.$pm['subject_thief'].','.sprintf($pm['message_thief'],$thief_name,join("\n",$robbed_users),$new_rep,$points).')';
	sql_query('INSERT INTO users(id,reputation,seedbonus) VALUES '.join(',',$update_users).' ON DUPLICATE KEY UPDATE reputation=values(reputation),seedbonus=values(seedbonus) ') or sqlerr(__FILE__,__LINE__);
	sql_query('INSERT INTO messages(sender,receiver,added,subject,msg) VALUES '.join(',',$pms)) or sqlerr(__FILE__,__LINE__);
   //== cache updates ???
   $mc1->begin_transaction('MyUser_'.$thief_id);
   $mc1->update_row(false, array('reputation' => $new_rep));   
   $mc1->commit_transaction($INSTALLER09['expires']['curuser']);
   $mc1->begin_transaction('user'.$thief_id);
   $mc1->update_row(false, array('reputation' => $new_rep));   
   $mc1->commit_transaction($INSTALLER09['expires']['user_cache']);
   
   $mc1->begin_transaction('userstats_'.$thief_id);
   $mc1->update_row(false, array('seedbonus' => $new_bonus));   
   $mc1->commit_transaction($INSTALLER09['expires']['u_stats']);
   $mc1->begin_transaction('user_stats_'.$thief_id);
   $mc1->update_row(false, array('seedbonus' => $new_bonus));   
   $mc1->commit_transaction($INSTALLER09['expires']['user_stats']);
   //$mc1->delete_value('inbox_new_'.$pms);
   //$mc1->delete_value('inbox_new_sb_'.$pms);
}
header("Refresh: 0; url={$INSTALLER09['baseurl']}/mybonus.php?bounty_success=1");
die;
break;

case 'king':
//=== trade for one month king status 
if ($CURUSER['king'] != 0 OR $CURUSER['pirate'] != 0)
stderr("Error", "Now why would you want to add what you already have?<br />go back to your <a class='altlink' href='{$INSTALLER09['baseurl']}/mybonus.php'>Karma Bonus Point</a> page and think that one over.");
$king = (86400 * 30 + TIME_NOW);
$free_switch = (30 * 86400 + TIME_NOW);
$bonuscomment = get_date( TIME_NOW, 'DATE', 1 ) . " - " .$points. " Points for 1 month King + freeleech Status.\n " .$bonuscomment;
sql_query("UPDATE users SET free_switch = ".sqlesc($free_switch).", king = ".sqlesc($king).", seedbonus = ".sqlesc($seedbonus).", bonuscomment = ".sqlesc($bonuscomment)." WHERE id = ".sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
$mc1->begin_transaction('user'.$userid);
$mc1->update_row(false, array('free_switch' => $free_switch, 'king' => $king));
$mc1->commit_transaction($INSTALLER09['expires']['user_cache']);
$mc1->begin_transaction('MyUser_'.$userid);
$mc1->update_row(false, array('free_switch' => $free_switch, 'king' => $king));
$mc1->commit_transaction($INSTALLER09['expires']['curuser']);
$mc1->begin_transaction('userstats_'.$userid);
$mc1->update_row(false, array('seedbonus' => $seedbonus));
$mc1->commit_transaction($INSTALLER09['expires']['u_stats']);
$mc1->begin_transaction('user_stats_'.$userid);
$mc1->update_row(false, array('seedbonus' => $seedbonus, 'bonuscomment' => $bonuscomment));
$mc1->commit_transaction($INSTALLER09['expires']['user_stats']);
header("Refresh: 0; url={$INSTALLER09['baseurl']}/mybonus.php?king_success=1");
die;
break;

//--- Freeleech
case 'freeleech':
$points2 = 59999; //== Adjust so that its you can only contribute 1 point under the the bonus option amount doubled - current 30000 x 2 = 60000 - 1 = 59999
$pointspool = (int)$arr_points['pointspool'];
$donation = (int)$_POST['donate'];
$seedbonus = ($bonus - $donation);
if($bonus < $donation || $donation <= 0 || $donation > $points2){
stderr("Error", " <br />Points: ".(float)$donation." <br /> Bonus: ".(float)$bonus." <br /> Donation: ".(float)$donation." <br />Time shall unfold what plighted cunning hides\n\nWho cover faults, at last shame them derides.<br /> Click to go back to your <a class='altlink' href='./mybonus.php'>Karma Bonus Point</a> page.<br />");
die;
}
if(($pointspool + $donation) >= $arr_points["points"]){
$now = TIME_NOW;
$end = (86400 * 3 + TIME_NOW);
$message = sqlesc("FreeLeech [ON]");
sql_query("INSERT INTO events (userid, overlayText, startTime, endTime, displayDates, freeleechEnabled) VALUES (".sqlesc($userid).", $message, $now, $end, 1, 1)") or sqlerr(__FILE__, __LINE__);
$norefund = ($donation + $pointspool) % $points;
$bonuscomment = get_date( TIME_NOW, 'DATE', 1 ) . " - " .$donation. " Points contributed for freeleech.\n " .$bonuscomment;
sql_query("UPDATE users SET seedbonus = ".sqlesc($seedbonus).",  bonuscomment = ".sqlesc($bonuscomment)." WHERE id = ".sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
sql_query("UPDATE bonus SET pointspool = ".sqlesc($norefund)." WHERE id = '11' LIMIT 1") or sqlerr(__FILE__, __LINE__);
$mc1->begin_transaction('userstats_'.$userid);
$mc1->update_row(false, array('seedbonus' => $seedbonus));
$mc1->commit_transaction($INSTALLER09['expires']['u_stats']);
$mc1->begin_transaction('user_stats_'.$userid);
$mc1->update_row(false, array('seedbonus' => $seedbonus, 'bonuscomment' => $bonuscomment));
$mc1->commit_transaction($INSTALLER09['expires']['user_stats']);
$mc1->delete_value('freecontribution_');
$mc1->delete_value('top_donators_');
$mc1->delete_value('freeleech_counter');
$mc1->delete_value('freeleech_counter_alerts_');
$mc1->delete_value('freecontribution_datas_');
$mc1->delete_value('freecontribution_datas_alerts_');
write_bonus_log($CURUSER["id"], $donation, $type = "freeleech");
$msg = $CURUSER['username']. " Donated ".$donation." karma point".($donation > 1?'s':'')." into the freeleech contribution pot and has activated freeleech for 3 days ". $donation ."/".$points.'';
$mc1->delete_value('shoutbox_');
autoshout($msg);
header("Refresh: 0; url={$INSTALLER09['baseurl']}//mybonus.php?freeleech_success=1&norefund=$norefund");
die;
} else {
// add to the pool
sql_query("UPDATE bonus SET pointspool = pointspool + ".sqlesc($donation)." WHERE id = '11' LIMIT 1") or sqlerr(__FILE__, __LINE__);
$bonuscomment = get_date( TIME_NOW, 'DATE', 1 ) . " - " .$donation. " Points contributed for freeleech.\n " .$bonuscomment;
sql_query("UPDATE users SET seedbonus = ".sqlesc($seedbonus).", bonuscomment = ".sqlesc($bonuscomment)." WHERE id = ".sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
$mc1->begin_transaction('userstats_'.$userid);
$mc1->update_row(false, array('seedbonus' => $seedbonus));
$mc1->commit_transaction($INSTALLER09['expires']['u_stats']);
$mc1->begin_transaction('user_stats_'.$userid);
$mc1->update_row(false, array('seedbonus' => $seedbonus, 'bonuscomment' => $bonuscomment));
$mc1->commit_transaction($INSTALLER09['expires']['user_stats']);
$mc1->delete_value('freecontribution_');
$mc1->delete_value('top_donators_');
$mc1->delete_value('freeleech_counter');
$mc1->delete_value('freeleech_counter_alerts_');
$mc1->delete_value('freecontribution_datas_');
$mc1->delete_value('freecontribution_datas_alerts_');
write_bonus_log($CURUSER["id"], $donation, $type = "freeleech");
$Remaining = ($arr_points['points'] - $arr_points['pointspool'] - $donation);
$msg = $CURUSER['username']. " Donated ".$donation." karma point".($donation > 1?'s':'')." into the freeleech contribution pot ! * Only [b]".htmlsafechars($Remaining)."[/b] more karma point".($Remaining > 1?'s':'')." to go! * [color=green][b]Freeleech contribution:[/b][/color] [url={$INSTALLER09['baseurl']}/mybonus.php]". $donation ."/".$points.'[/url]';
$mc1->delete_value('shoutbox_');
autoshout($msg);
header("Refresh: 0; url={$INSTALLER09['baseurl']}/mybonus.php?freeleech_success=2");
die;
}
die;
break;

//--- doubleupload
case 'doubleup':
$points2 = 59999; //== Adjust so that its you can only contribute 1 point under the the bonus option amount doubled - current 30000 x 2 = 60000 - 1 = 59999
$pointspool = (int)$arr_points['pointspool'];
$donation = (int)$_POST['donate'];
$seedbonus = ($bonus - $donation);
if($bonus < $donation || $donation <= 0 || $donation > $points2){
stderr("Error", " <br />Points: ".(float)$donation." <br /> Bonus: ".(float)$bonus." <br /> Donation: ".(float)$donation." <br />Time shall unfold what plighted cunning hides\n\nWho cover faults, at last shame them derides.<br /> Click to go back to your <a class='altlink' href='./mybonus.php'>Karma Bonus Point</a> page.<br />");
die;
}
if(($pointspool + $donation) >= $arr_points["points"]){
$now = TIME_NOW;
$end = (86400 * 3 + TIME_NOW);
$message = sqlesc("DoubleUpload [ON]");
sql_query("INSERT INTO events(userid, overlayText, startTime, endTime, displayDates, duploadEnabled) VALUES (".sqlesc($userid).", $message, $now, $end, 1, 1)") or sqlerr(__FILE__, __LINE__);
$norefund = ($donation + $pointspool) % $points;
$bonuscomment = get_date( TIME_NOW, 'DATE', 1 ) . " - " .$donation. " Points contributed for doubleupload.\n " .$bonuscomment;
sql_query("UPDATE users SET seedbonus = ".sqlesc($seedbonus).",  bonuscomment = ".sqlesc($bonuscomment)." WHERE id = ".sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
sql_query("UPDATE bonus SET pointspool = ".sqlesc($norefund)." WHERE id = '12' LIMIT 1") or sqlerr(__FILE__, __LINE__);
$mc1->begin_transaction('userstats_'.$userid);
$mc1->update_row(false, array('seedbonus' => $seedbonus));
$mc1->commit_transaction($INSTALLER09['expires']['u_stats']);
$mc1->begin_transaction('user_stats_'.$userid);
$mc1->update_row(false, array('seedbonus' => $seedbonus, 'bonuscomment' => $bonuscomment));
$mc1->commit_transaction($INSTALLER09['expires']['user_stats']);
$mc1->delete_value('freecontribution_');
$mc1->delete_value('top_donators2_');
$mc1->delete_value('doubleupload_counter');
$mc1->delete_value('doubleupload_counter_alerts_');
$mc1->delete_value('freecontribution_datas_');
$mc1->delete_value('freecontribution_datas_alerts_');
write_bonus_log($CURUSER["id"], $donation, $type = "doubleupload");
$msg = $CURUSER['username']. " Donated ".$donation." karma point".($donation > 1?'s':'')." into the double upload contribution pot and has activated Double Upload for 3 days ". $donation ."/".$points.'';
$mc1->delete_value('shoutbox_');
autoshout($msg);
header("Refresh: 0; url={$INSTALLER09['baseurl']}/mybonus.php?doubleup_success=1&norefund=$norefund");
die;
} else {
// add to the pool
sql_query("UPDATE bonus SET pointspool = pointspool + ".sqlesc($donation)." WHERE id = '12' LIMIT 1") or sqlerr(__FILE__, __LINE__);
$bonuscomment = get_date( TIME_NOW, 'DATE', 1 ) . " - " .$donation. " Points contributed for doubleupload.\n " .$bonuscomment;
sql_query("UPDATE users SET seedbonus = ".sqlesc($seedbonus).", bonuscomment = ".sqlesc($bonuscomment)." WHERE id = ".sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
$mc1->begin_transaction('userstats_'.$userid);
$mc1->update_row(false, array('seedbonus' => $seedbonus));
$mc1->commit_transaction($INSTALLER09['expires']['u_stats']);
$mc1->begin_transaction('user_stats_'.$userid);
$mc1->update_row(false, array('seedbonus' => $seedbonus, 'bonuscomment' => $bonuscomment));
$mc1->commit_transaction($INSTALLER09['expires']['user_stats']);
$mc1->delete_value('freecontribution_');
$mc1->delete_value('top_donators2_');
$mc1->delete_value('doubleupload_counter');
$mc1->delete_value('doubleupload_counter_alerts_');
$mc1->delete_value('freecontribution_datas_');
$mc1->delete_value('freecontribution_datas_alerts_');
write_bonus_log($CURUSER["id"], $donation, $type = "doubleupload");
$Remaining = ($arr_points['points'] - $arr_points['pointspool'] - $donation);
$msg = $CURUSER['username']. " Donated ".$donation." karma point".($donation > 1?'s':'')." into the double upload contribution pot ! * Only [b]".htmlsafechars($Remaining)."[/b] more karma point".($Remaining > 1?'s':'')." to go! * [color=green][b]Double upload contribution:[/b][/color] [url={$INSTALLER09['baseurl']}/mybonus.php]". $donation ."/".$points.'[/url]';
$mc1->delete_value('shoutbox_');
autoshout($msg);
header("Refresh: 0; url={$INSTALLER09['baseurl']}/mybonus.php?doubleup_success=2");
die;
}
die;
break;

//---Halfdownload
case 'halfdown':
$points2 = 59999; //== Adjust so that its you can only contribute 1 point under the the bonus option amount doubled - current 30000 x 2 = 60000 - 1 = 59999
$pointspool = (int)$arr_points['pointspool'];
$donation = (int)$_POST['donate'];
$seedbonus = ($bonus - $donation);
if($bonus < $donation || $donation <= 0 || $donation > $points2){
stderr("Error", " <br />Points: ".(float)$donation." <br /> Bonus: ".(float)$bonus." <br /> Donation: ".(float)$donation." <br />Time shall unfold what plighted cunning hides\n\nWho cover faults, at last shame them derides.<br /> Click to go back to your <a class='altlink' href='./mybonus.php'>Karma Bonus Point</a> page.<br />");
die;
}
if(($pointspool + $donation) >= $arr_points["points"]){
$now = TIME_NOW;
$end = (86400 * 3 + TIME_NOW);
$message = sqlesc("HalfDownload [ON]");
sql_query("INSERT INTO events(userid, overlayText, startTime, endTime, displayDates, hdownEnabled) VALUES (".sqlesc($userid).", $message, $now, $end, 1, 1)") or sqlerr(__FILE__, __LINE__);
$norefund = ($donation + $pointspool) % $points;
$bonuscomment = get_date( TIME_NOW, 'DATE', 1 ) . " - " .$donation. " Points contributed for Halfdownload.\n " .$bonuscomment;
sql_query("UPDATE users SET seedbonus = ".sqlesc($seedbonus).",  bonuscomment = ".sqlesc($bonuscomment)." WHERE id = ".sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
sql_query("UPDATE bonus SET pointspool = ".sqlesc($norefund)." WHERE id = '13' LIMIT 1") or sqlerr(__FILE__, __LINE__);
$mc1->begin_transaction('userstats_'.$userid);
$mc1->update_row(false, array('seedbonus' => $seedbonus));
$mc1->commit_transaction($INSTALLER09['expires']['u_stats']);
$mc1->begin_transaction('user_stats_'.$userid);
$mc1->update_row(false, array('seedbonus' => $seedbonus, 'bonuscomment' => $bonuscomment));
$mc1->commit_transaction($INSTALLER09['expires']['user_stats']);
$mc1->delete_value('freecontribution_');
$mc1->delete_value('top_donators3_');
$mc1->delete_value('halfdownload_counter');
$mc1->delete_value('halfdownload_counter_alerts_');
$mc1->delete_value('freecontribution_datas_');
$mc1->delete_value('freecontribution_datas_alerts_');
write_bonus_log($CURUSER["id"], $donation, $type = "halfdownload");
$msg = $CURUSER['username']. " Donated ".$donation." karma point".($donation > 1?'s':'')." into the half download contribution pot and has activated half download for 3 days ". $donation ."/".$points.'';
$mc1->delete_value('shoutbox_');
autoshout($msg);
header("Refresh: 0; url={$INSTALLER09['baseurl']}/mybonus.php?halfdown_success=1&norefund=$norefund");
die;
} else {
// add to the pool
sql_query("UPDATE bonus SET pointspool = pointspool + ".sqlesc($donation)." WHERE id = '13' LIMIT 1") or sqlerr(__FILE__, __LINE__);
$bonuscomment = get_date( TIME_NOW, 'DATE', 1 ) . " - " .$points. " Points contributed for halfdownload.\n " .$bonuscomment;
sql_query("UPDATE users SET seedbonus = ".sqlesc($seedbonus).", bonuscomment = ".sqlesc($bonuscomment)." WHERE id = ".sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
$mc1->begin_transaction('userstats_'.$userid);
$mc1->update_row(false, array('seedbonus' => $seedbonus));
$mc1->commit_transaction($INSTALLER09['expires']['u_stats']);
$mc1->begin_transaction('user_stats_'.$userid);
$mc1->update_row(false, array('seedbonus' => $seedbonus, 'bonuscomment' => $bonuscomment));
$mc1->commit_transaction($INSTALLER09['expires']['user_stats']);
$mc1->delete_value('freecontribution_');
$mc1->delete_value('top_donators3_');
$mc1->delete_value('halfdownload_counter');
$mc1->delete_value('halfdownload_counter_alerts_');
$mc1->delete_value('freecontribution_datas_');
$mc1->delete_value('freecontribution_datas_alerts_');
write_bonus_log($CURUSER["id"], $donation, $type = "halfdownload");
$Remaining = ($arr_points['points'] - $arr_points['pointspool'] - $donation);
$msg = $CURUSER['username']. " Donated ".$donation." karma point".($donation > 1?'s':'')." into the half download contribution pot ! * Only [b]".htmlsafechars($Remaining)."[/b] more karma point".($Remaining > 1?'s':'')." to go! * [color=green][b]Half download contribution:[/b][/color] [url={$INSTALLER09['baseurl']}/mybonus.php]". $donation ."/".$points.'[/url]';
$mc1->delete_value('shoutbox_');
autoshout($msg);
header("Refresh: 0; url={$INSTALLER09['baseurl']}/mybonus.php?halfdown_success=2");
die;
}
die;
break;

case 'ratio':
//=== trade for one torrent 1:1 ratio
$torrent_number = (int)$_POST['torrent_id'];
$res_snatched = sql_query("SELECT s.uploaded, s.downloaded, t.name FROM snatched AS s LEFT JOIN torrents AS t ON t.id = s.torrentid WHERE s.userid = ".sqlesc($userid)." AND torrentid = ".sqlesc($torrent_number)." LIMIT 1") or sqlerr(__FILE__, __LINE__);
$arr_snatched = mysqli_fetch_assoc($res_snatched);
if ($arr_snatched['size'] > 6442450944)
stderr("Error", "One to One ratio only works on torrents smaller then 6GB!<br /><br />Back to your <a class='altlink' href='{$INSTALLER09['baseurl']}/mybonus.php'>Karma Bonus Points</a> page.");
if ($arr_snatched['name'] == '')
stderr("Error", "No torrent with that ID!<br />Back to your <a class='altlink' href='{$INSTALLER09['baseurl']}/mybonus.php'>Karma Bonus Points</a> page.");
if ($arr_snatched['uploaded'] >= $arr_snatched['downloaded'])
stderr("Error", "Your ratio on that torrent is fine, you must have selected the wrong torrent ID.<br />Back to your <a class='altlink' href='{$INSTALLER09['baseurl']}/mybonus.php'>Karma Bonus Points</a> page.");
sql_query("UPDATE snatched SET uploaded = ".sqlesc($arr_snatched['downloaded'])." WHERE userid = ".sqlesc($userid)." AND torrentid = ".sqlesc($torrent_number)) or sqlerr(__FILE__, __LINE__);
$difference = $arr_snatched['downloaded'] - $arr_snatched['uploaded'];
$bonuscomment = get_date( TIME_NOW, 'DATE', 1 ) . " - " .$points. " Points for 1 to 1 ratio on torrent: ".htmlsafechars($arr_snatched['name'])." ".$torrent_number.", ".$difference." added .\n " .$bonuscomment;
sql_query("UPDATE users SET uploaded = ".sqlesc($upload + $difference).", bonuscomment = ".sqlesc($bonuscomment).", seedbonus = ".sqlesc($seedbonus)." WHERE id = ".sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
$mc1->begin_transaction('userstats_'.$userid);
$mc1->update_row(false, array('uploaded' => $upload + $difference, 'seedbonus' => $seedbonus));
$mc1->commit_transaction($INSTALLER09['expires']['u_stats']);
$mc1->begin_transaction('user_stats_'.$userid);
$mc1->update_row(false, array('uploaded' => $upload + $difference, 'seedbonus' => $seedbonus, 'bonuscomment' => $bonuscomment));
$mc1->commit_transaction($INSTALLER09['expires']['user_stats']);
header("Refresh: 0; url={$INSTALLER09['baseurl']}/mybonus.php?ratio_success=1");
die;
break;

case 'bump':
//=== Reanimate a torrent
$torrent_number = isset($_POST['torrent_id']) ? intval($_POST['torrent_id']) : 0;
$res_free = sql_query('SELECT name FROM torrents WHERE id = '.sqlesc($torrent_number)) or sqlerr(__FILE__, __LINE__);
$arr_free = mysqli_fetch_assoc($res_free);
if ($arr_free['name'] == '')
stderr('Error', 'No torrent with that ID!<br /><br />Back to your <a class="altlink" href="karma_bonus.php">Karma Points</a> page.');
$free_time = (7 * 86400 + TIME_NOW);
$bonuscomment = get_date( TIME_NOW, 'DATE', 1 ) . " - " .$points. " Points to Reanimate torrent: ".$arr_free['name'].".\n " .$bonuscomment;
sql_query('UPDATE users SET bonuscomment = '.sqlesc($bonuscomment).', seedbonus = '.sqlesc($seedbonus).' WHERE id = '.sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
sql_query('UPDATE torrents SET bump = \'yes\', free='.sqlesc($free_time).', added = '.TIME_NOW.' WHERE id = '.sqlesc($torrent_number)) or sqlerr(__FILE__, __LINE__);
$mc1->begin_transaction('userstats_'.$userid);
$mc1->update_row(false, array('seedbonus' => $seedbonus));
$mc1->commit_transaction($INSTALLER09['expires']['u_stats']);
$mc1->begin_transaction('user_stats_'.$userid);
$mc1->update_row(false, array('seedbonus' => $seedbonus, 'bonuscomment' => $bonuscomment));
$mc1->commit_transaction($INSTALLER09['expires']['user_stats']);
$mc1->begin_transaction('torrent_details_'.$torrent_number);
$mc1->update_row(false, array('added' => TIME_NOW, 'bump' => 'yes', 'free' => $free_time));
$mc1->commit_transaction(0);
header("Refresh: 0; url={$INSTALLER09['baseurl']}/mybonus.php?bump_success=1&t_name={$torrent_number}");
die;
break;

case 'class':
//=== trade for one month VIP status 
if ($CURUSER['class'] > UC_VIP)
stderr("Error", "Now why would you want to lower yourself to VIP?<br />go back to your <a class='altlink' href='{$INSTALLER09['baseurl']}/mybonus.php'>Karma Bonus Point</a> page and think that one over.");
$vip_until = (86400 * 28 + TIME_NOW);
$bonuscomment = get_date( TIME_NOW, 'DATE', 1 ) . " - " .$points. " Points for 1 month VIP Status.\n " .$bonuscomment;
sql_query("UPDATE users SET class = ".UC_VIP.", vip_added = 'yes', vip_until = ".sqlesc($vip_until).", seedbonus = ".sqlesc($seedbonus).", bonuscomment = ".sqlesc($bonuscomment)." WHERE id = ".sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
$mc1->begin_transaction('user'.$userid);
$mc1->update_row(false, array('class' => 2, 'vip_added' => 'yes', 'vip_until' => $vip_until));
$mc1->commit_transaction($INSTALLER09['expires']['user_cache']);
$mc1->begin_transaction('MyUser_'.$userid);
$mc1->update_row(false, array('class' => 2, 'vip_added' => 'yes', 'vip_until' => $vip_until));
$mc1->commit_transaction($INSTALLER09['expires']['curuser']);
$mc1->begin_transaction('userstats_'.$userid);
$mc1->update_row(false, array('seedbonus' => $seedbonus));
$mc1->commit_transaction($INSTALLER09['expires']['u_stats']);
$mc1->begin_transaction('user_stats_'.$userid);
$mc1->update_row(false, array('seedbonus' => $seedbonus, 'bonuscomment' => $bonuscomment));
$mc1->commit_transaction($INSTALLER09['expires']['user_stats']);
header("Refresh: 0; url={$INSTALLER09['baseurl']}/mybonus.php?class_success=1");
die;
break;

case 'warning':
//=== trade for removal of warning :P
if ($CURUSER['warned'] == 0)
stderr("Error", "How can we remove a warning that isn't there?<br />go back to your <a class='altlink' href='{$INSTALLER09['baseurl']}/mybonus.php'>Karma Bonus Point</a> page and think that one over.");
$bonuscomment = get_date( TIME_NOW, 'DATE', 1 ) . " - " .$points. " Points for removing warning.\n " .$bonuscomment;
$res_warning = sql_query("SELECT modcomment FROM users WHERE id =".sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
$arr = mysqli_fetch_assoc($res_warning);
$modcomment = htmlsafechars($arr['modcomment']);
$modcomment = get_date( TIME_NOW, 'DATE', 1 ) . " - Warning removed by - Bribe with Karma.\n". $modcomment;
$modcom = sqlesc($modcomment);
sql_query("UPDATE users SET warned = '0', seedbonus = ".sqlesc($seedbonus).", bonuscomment = ".sqlesc($bonuscomment).", modcomment = ".sqlesc($modcom)." WHERE id = ".sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
$dt = sqlesc(TIME_NOW);
$subject = sqlesc("Warning removed by Karma.");
$msg = sqlesc("Your warning has been removed by the big Karma payoff... Please keep on your best behaviour from now on.\n");
sql_query("INSERT INTO messages (sender, receiver, added, msg, subject) VALUES(0, ".sqlesc($userid).", $dt, $msg, $subject)") or sqlerr(__FILE__, __LINE__);
$mc1->begin_transaction('user'.$userid);
$mc1->update_row(false, array('warned' => 0));
$mc1->commit_transaction($INSTALLER09['expires']['user_cache']);
$mc1->begin_transaction('MyUser_'.$userid);
$mc1->update_row(false, array('warned' => 0));
$mc1->commit_transaction($INSTALLER09['expires']['curuser']);
$mc1->begin_transaction('userstats_'.$userid);
$mc1->update_row(false, array('seedbonus' => $seedbonus));
$mc1->commit_transaction($INSTALLER09['expires']['u_stats']);
$mc1->begin_transaction('user_stats_'.$userid);
$mc1->update_row(false, array('seedbonus' => $seedbonus, 'bonuscomment' => $bonuscomment, 'modcomment' => $modcomment));
$mc1->commit_transaction($INSTALLER09['expires']['user_stats']);
$mc1->delete_value('inbox_new_'.$userid);
$mc1->delete_value('inbox_new_sb_'.$userid);
header("Refresh: 0; url={$INSTALLER09['baseurl']}/mybonus.php?warning_success=1");
die;
break;

case 'smile':
//=== trade for one month special smilies :P
$smile_until = (86400 * 28 + TIME_NOW);
$bonuscomment = get_date( TIME_NOW, 'DATE', 1 ) . " - " .$points. " Points for 1 month of custom smilies.\n " .$bonuscomment;
sql_query("UPDATE users SET smile_until = ".sqlesc($smile_until).", seedbonus = ".sqlesc($seedbonus).", bonuscomment = ".sqlesc($bonuscomment)." WHERE id = ".sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
$mc1->begin_transaction('user'.$userid);
$mc1->update_row(false, array('smile_until' => $smile_until));
$mc1->commit_transaction($INSTALLER09['expires']['user_cache']);
$mc1->begin_transaction('MyUser_'.$userid);
$mc1->update_row(false, array('smile_until' => $smile_until));
$mc1->commit_transaction($INSTALLER09['expires']['curuser']);
$mc1->begin_transaction('userstats_'.$userid);
$mc1->update_row(false, array('seedbonus' => $seedbonus));
$mc1->commit_transaction($INSTALLER09['expires']['u_stats']);
$mc1->begin_transaction('user_stats_'.$userid);
$mc1->update_row(false, array('seedbonus' => $seedbonus, 'bonuscomment' => $bonuscomment));
$mc1->commit_transaction($INSTALLER09['expires']['user_stats']);
header("Refresh: 0; url={$INSTALLER09['baseurl']}/mybonus.php?smile_success=1");
die;
break;

case 'invite':
//=== trade for invites
$invites = (int)$User['invites'];
$inv = $invites + 3;
$bonuscomment = get_date( TIME_NOW, 'DATE', 1 ) . " - " .$points. " Points for invites.\n " .$bonuscomment;
sql_query("UPDATE users SET invites = ".sqlesc($inv).", seedbonus = ".sqlesc($seedbonus).", bonuscomment = ".sqlesc($bonuscomment)." WHERE id = ".sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
$mc1->begin_transaction('user'.$userid);
$mc1->update_row(false, array('invites' => $inv));
$mc1->commit_transaction($INSTALLER09['expires']['user_cache']);
$mc1->begin_transaction('MyUser_'.$userid);
$mc1->update_row(false, array('invites' => $inv));
$mc1->commit_transaction($INSTALLER09['expires']['curuser']);
$mc1->begin_transaction('userstats_'.$userid);
$mc1->update_row(false, array('seedbonus' => $seedbonus));
$mc1->commit_transaction($INSTALLER09['expires']['u_stats']);
$mc1->begin_transaction('user_stats_'.$userid);
$mc1->update_row(false, array('seedbonus' => $seedbonus, 'bonuscomment' => $bonuscomment));
$mc1->commit_transaction($INSTALLER09['expires']['user_stats']);
header("Refresh: 0; url={$INSTALLER09['baseurl']}/mybonus.php?invite_success=1");
die;
break;

case 'title':
//=== trade for special title
/**** the $words array are words that you DO NOT want the user to have... use to filter "bad words" & user class...
the user class is just for show, but what the hell :p Add more or edit to your liking.
*note if they try to use a restricted word, they will recieve the special title "I just wasted my karma" *****/
$title = strip_tags(htmlsafechars($_POST['title']));
$words = array('fuck', 'shit', 'Moderator', 'Administrator', 'Admin', 'pussy', 'Sysop', 'cunt', 'nigger', 'VIP', 'Super User', 'Power User', 'ADMIN', 'SYSOP', 'MODERATOR', 'ADMINISTRATOR');
$title = str_replace($words, "I just wasted my karma", $title);
$bonuscomment = get_date( TIME_NOW, 'DATE', 1 ) . " - " .$points. " Points for custom title. Old title was {$CURUSER['title']} new title is ".$title.".\n " .$bonuscomment;
sql_query("UPDATE users SET title = ".sqlesc($title).", seedbonus = ".sqlesc($seedbonus).", bonuscomment = ".sqlesc($bonuscomment)." WHERE id = ".sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
$mc1->begin_transaction('user'.$userid);
$mc1->update_row(false, array('title' => $title));
$mc1->commit_transaction($INSTALLER09['expires']['user_cache']);
$mc1->begin_transaction('MyUser_'.$userid);
$mc1->update_row(false, array('title' => $title));
$mc1->commit_transaction($INSTALLER09['expires']['curuser']);
$mc1->begin_transaction('userstats_'.$userid);
$mc1->update_row(false, array('seedbonus' => $seedbonus));
$mc1->commit_transaction($INSTALLER09['expires']['u_stats']);
$mc1->begin_transaction('user_stats_'.$userid);
$mc1->update_row(false, array('seedbonus' => $seedbonus, 'bonuscomment' => $bonuscomment));
$mc1->commit_transaction($INSTALLER09['expires']['user_stats']);
header("Refresh: 0; url={$INSTALLER09['baseurl']}/mybonus.php?title_success=1");
die;
break;

case 'gift_1':
//=== trade for giving the gift of karma
$points = 0 + $_POST['bonusgift'];
$usernamegift = htmlsafechars($_POST['username']);
$res = sql_query("SELECT id,seedbonus,bonuscomment,username FROM users WHERE username=" . sqlesc($usernamegift));
$arr = mysqli_fetch_assoc($res);
$useridgift = (int)$arr['id'];
$userseedbonus = (float)$arr['seedbonus'];
$bonuscomment_gift = htmlsafechars($arr['bonuscomment']);
$usernamegift = htmlsafechars($arr['username']);

$check_me = array(100,200,300,400,500,1000,5000,10000,20000,50000,100000);
if (!in_array($points, $check_me))
stderr("Error", "I smell a rat!");

if($bonus >= $points){
$bonuscomment = get_date( TIME_NOW, 'DATE', 1 ) . " - " .$points. " Points as gift to $usernamegift .\n " .$bonuscomment;
$bonuscomment_gift = get_date( TIME_NOW, 'DATE', 1 ) . " - recieved " .$points. " Points as gift from {$CURUSER['username']} .\n " .$bonuscomment_gift;
$seedbonus = $bonus-$points;
$giftbonus1 = $userseedbonus + $points;
if ($userid == $useridgift){
header("Refresh: 0; url={$INSTALLER09['baseurl']}/mybonus.php?gift_fail=1");
die;
}
if (!$useridgift){
header("Refresh: 0; url={$INSTALLER09['baseurl']}/mybonus.php?gift_fail_user=1");
die;
}
sql_query("SELECT bonuscomment,id FROM users WHERE id = ".sqlesc($useridgift)) or sqlerr(__FILE__, __LINE__);
//=== and to post to the person who gets the gift!
sql_query("UPDATE users SET seedbonus = ".sqlesc($seedbonus).", bonuscomment = ".sqlesc($bonuscomment)." WHERE id = ".sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
sql_query("UPDATE users SET seedbonus = ".sqlesc($giftbonus1).", bonuscomment = ".sqlesc($bonuscomment_gift)." WHERE id = ".sqlesc($useridgift));
$mc1->begin_transaction('userstats_'.$userid);
$mc1->update_row(false, array('seedbonus' => $seedbonus));
$mc1->commit_transaction($INSTALLER09['expires']['u_stats']);
$mc1->begin_transaction('user_stats_'.$userid);
$mc1->update_row(false, array('seedbonus' => $seedbonus, 'bonuscomment' => $bonuscomment));
$mc1->commit_transaction($INSTALLER09['expires']['user_stats']);
$mc1->begin_transaction('userstats_'.$useridgift);
$mc1->update_row(false, array('seedbonus' => $giftbonus1));
$mc1->commit_transaction($INSTALLER09['expires']['u_stats']);
$mc1->begin_transaction('user_stats_'.$useridgift);
$mc1->update_row(false, array('seedbonus' => $giftbonus1, 'bonuscomment' => $bonuscomment_gift));
$mc1->commit_transaction($INSTALLER09['expires']['user_stats']);
//===send message
$subject = sqlesc("Someone Loves you"); 
$added = sqlesc(TIME_NOW);
$msg = sqlesc("You have been given a gift of $points Karma points by ".$CURUSER['username']);
sql_query("INSERT INTO messages (sender, subject, receiver, msg, added) VALUES(0, $subject, $useridgift, $msg, $added)") or sqlerr(__FILE__, __LINE__);
$mc1->delete_value('inbox_new_'.$useridgift);
$mc1->delete_value('inbox_new_sb_'.$useridgift);
header("Refresh: 0; url={$INSTALLER09['baseurl']}/mybonus.php?gift_success=1&gift_amount_points=$points&usernamegift=$usernamegift&gift_id=$useridgift");
die;
}
else{
header("Refresh: 0; url={$INSTALLER09['baseurl']}/mybonus.php?gift_fail_points=1");
die;
}
break;
}
}

//==== This is the default page
$HTMLOUT .="<div class='roundedCorners' style='text-align:left;width:80%;border:1px solid black;padding:5px;'>
<div style='background:transparent;height:25px;'>
<span style='font-weight:bold;font-size:12pt;'> Karma Bonus Point's system :</span></div>";
//== 09 Ezeros freeleech contribution - Bigjoos.Ezero
$fpoints = $dpoints = $hpoints = $freeleech_enabled = $double_upload_enabled = $half_down_enabled = $top_donators = $top_donators2 = $top_donators3 = $count1 ='';
// eZER0's mod for bonus contribution
// Limited this to 3 because of performance reasons and i wanted to go through last 3 events, anyway the most we can have
// is that halfdownload is enabled, double upload is enabled as well as freeleech !
    if (XBT_TRACKER == false) {
    if(($scheduled_events = $mc1->get_value('freecontribution_datas_')) === false) {
    $scheduled_events = mysql_fetch_all("SELECT * from `events` ORDER BY `startTime` DESC LIMIT 3;", array());
    $mc1->cache_value('freecontribution_datas_', $scheduled_events, 3 * 86400);
    }

    if (is_array($scheduled_events)){
        foreach ($scheduled_events as $scheduled_event) {
            if (is_array($scheduled_event) && array_key_exists('startTime', $scheduled_event) &&
                array_key_exists('endTime', $scheduled_event)){
                $startTime = 0;
                $endTime = 0;
                $startTime = $scheduled_event['startTime'];
                $endTime = $scheduled_event['endTime'];
                if (TIME_NOW < $endTime && TIME_NOW > $startTime){
                    if (array_key_exists('freeleechEnabled', $scheduled_event)) {
                        $freeleechEnabled = $scheduled_event['freeleechEnabled'];
                        if ($scheduled_event['freeleechEnabled']){
                            $freeleech_start_time = $scheduled_event['startTime'];
                            $freeleech_end_time = $scheduled_event['endTime'];
                            $freeleech_enabled = true;
                        }
                    }
                    if (array_key_exists('duploadEnabled', $scheduled_event)){
                        $duploadEnabled = $scheduled_event['duploadEnabled'];
                        if ($scheduled_event['duploadEnabled']){
                            $double_upload_start_time = $scheduled_event['startTime'];
                            $double_upload_end_time = $scheduled_event['endTime'];
                            $double_upload_enabled = true;
                        }
                    }
                    if (array_key_exists('hdownEnabled', $scheduled_event)) {
                        $hdownEnabled = $scheduled_event['hdownEnabled'];    
                        if ($scheduled_event['hdownEnabled']){
                            $half_down_start_time = $scheduled_event['startTime'];
                            $half_down_end_time = $scheduled_event['endTime'];
                            $half_down_enabled = true;
                        }
                        }
                        }
                        }
                        }
                        }
//$mc1->delete_value('freecontribution_datas_');
//=== freeleech contribution meter
//$target_fl = 30000;
//=== get total points
if(($freeleech_counter = $mc1->get_value('freeleech_counter')) === false) {
	$total_fl = sql_query('SELECT SUM(pointspool) AS pointspool, points FROM bonus WHERE id =11');
    $fl_total_row = mysqli_fetch_assoc($total_fl);
    $percent_fl = number_format($fl_total_row['pointspool'] / $fl_total_row['points'] * 100, 2);
    $mc1->cache_value('freeleech_counter', $percent_fl, 0);
    } else
    $percent_fl = $freeleech_counter;
			switch ($percent_fl)
			{
	   			case $percent_fl >= 90:
			$font_color_fl = '<strong><font color="green">'.number_format($percent_fl).' %</font></strong>';
				break; 
				   case $percent_fl >= 80:
			$font_color_fl = '<strong><font color="lightgreen">'.number_format($percent_fl).' %</font></strong>';
				break;
				   case $percent_fl >= 70:
			$font_color_fl = '<strong><font color="jade">'.number_format($percent_fl).' %</font></strong>';
				break;
				   case $percent_fl >= 50:
			$font_color_fl = '<strong><font color="turquoise">'.number_format($percent_fl).' %</font></strong>';
				break;
				   case $percent_fl >= 40:
			$font_color_fl  = '<strong><font color="lightblue">'.number_format($percent_fl).' %</font></strong>';
				break;
				   case $percent_fl >= 30:
			$font_color_fl = '<strong><font color="yellow">'.number_format($percent_fl).' %</font></strong>';
				break;
				   case $percent_fl >= 20:
			$font_color_fl = '<strong><font color="orange">'.number_format($percent_fl).' %</font></strong>';
				break;				
				   case $percent_fl < 20:
			$font_color_fl = '<strong><font color="red">'.number_format($percent_fl).' %</font></strong>';
				break;
			}
//$mc1->delete_value('freeleech_counter');
//=== get total points
//$target_du = 30000;
if(($doubleupload_counter = $mc1->get_value('doubleupload_counter')) === false) {
	$total_du = sql_query('SELECT SUM(pointspool) AS pointspool, points FROM bonus WHERE id =12');
    $du_total_row = mysqli_fetch_assoc($total_du);
    $percent_du = number_format($du_total_row['pointspool'] / $du_total_row['points'] * 100, 2);
    $mc1->cache_value('doubleupload_counter', $percent_du, 0);
    } else
    $percent_du = $doubleupload_counter;
        switch ($percent_du)
			{
	   			case $percent_du >= 90:
			$font_color_du = '<strong><font color="green">'.number_format($percent_du).' %</font></strong>';
				break; 
				   case $percent_du >= 80:
			$font_color_du = '<strong><font color="lightgreen">'.number_format($percent_du).' %</font></strong>';
				break;
				   case $percent_du >= 70:
			$font_color_du = '<strong><font color="jade">'.number_format($percent_du).' %</font></strong>';
				break;
				   case $percent_du >= 50:
			$font_color_du = '<strong><font color="turquoise">'.number_format($percent_du).' %</font></strong>';
				break;
				   case $percent_du >= 40:
			$font_color_du  = '<strong><font color="lightblue">'.number_format($percent_du).' %</font></strong>';
				break;
				   case $percent_du >= 30:
			$font_color_du = '<strong><font color="yellow">'.number_format($percent_du).' %</font></strong>';
				break;
				   case $percent_du >= 20:
			$font_color_du = '<strong><font color="orange">'.number_format($percent_du).' %</font></strong>';
				break;				
				   case $percent_du < 20:
			$font_color_du = '<strong><font color="red">'.number_format($percent_du).' %</font></strong>';
            break;
			}
//=== get total points
//$target_hd = 30000;
if(($halfdownload_counter = $mc1->get_value('halfdownload_counter')) === false) {
	$total_hd = sql_query('SELECT SUM(pointspool) AS pointspool, points FROM bonus WHERE id =13');
    $hd_total_row = mysqli_fetch_assoc($total_hd);
    $percent_hd = number_format($hd_total_row['pointspool'] / $hd_total_row['points'] * 100, 2);
    $mc1->cache_value('halfdownload_counter', $percent_hd, 0);
    } else
    $percent_hd = $halfdownload_counter;
        switch ($percent_hd)
			{
	   			case $percent_hd >= 90:
			$font_color_hd = '<strong><font color="green">'.number_format($percent_hd).'&nbsp;%</font></strong>';
				break; 
				   case $percent_hd >= 80:
			$font_color_hd = '<strong><font color="lightgreen">'.number_format($percent_hd).'&nbsp;%</font></strong>';
				break;
				   case $percent_hd >= 70:
			$font_color_hd = '<strong><font color="jade">'.number_format($percent_hd).'&nbsp;%</font></strong>';
				break;
				   case $percent_hd >= 50:
			$font_color_hd = '<strong><font color="turquoise">'.number_format($percent_hd).'&nbsp;%</font></strong>';
				break;
				   case $percent_hd >= 40:
			$font_color_hd  = '<strong><font color="lightblue">'.number_format($percent_hd).'&nbsp;%</font></strong>';
				break;
				   case $percent_hd >= 30:
			$font_color_hd = '<strong><font color="yellow">'.number_format($percent_hd).'&nbsp;%</font></strong>';
				break;
				   case $percent_hd >= 20:
			$font_color_hd = '<strong><font color="orange">'.number_format($percent_hd).'&nbsp;%</font></strong>';
				break;				
				   case $percent_hd < 20:
			$font_color_hd = '<strong><font color="red">'.number_format($percent_hd).'&nbsp;%</font></strong>';
            break;
			}
 
    if($freeleech_enabled){
        $fstatus = "<strong><font color='green'>&nbsp;ON&nbsp;</font></strong>";
    } else {
        $fstatus = $font_color_fl ."";
    }
    if($double_upload_enabled){
        $dstatus = "<strong><font color='green'>&nbsp;ON&nbsp;</font></strong>";
    } else {
        $dstatus = $font_color_du ."";
    }
    if($half_down_enabled){
        $hstatus = "<strong><font color='green'>&nbsp;ON&nbsp;</font></strong>";
    } else {
        $hstatus = $font_color_hd ."";
    }
    }
    //==09 Ezeros freeleech contribution top 10 - pdq.Bigjoos
    if(($top_donators = $mc1->get_value('top_donators_')) === false) {
    $a = sql_query("SELECT bonuslog.id, SUM(bonuslog.donation) AS total, users.username, users.id AS userid, users.pirate, users.king, users.class, users.donor, users.warned, users.leechwarn, users.enabled, users.chatpost FROM bonuslog LEFT JOIN users ON bonuslog.id=users.id WHERE bonuslog.type = 'freeleech' GROUP BY bonuslog.id ORDER BY total DESC LIMIT 10;") or sqlerr(__FILE__, __LINE__);
    while($top_donator = mysqli_fetch_assoc($a))
    $top_donators[] = $top_donator;
    $mc1->cache_value('top_donators_', $top_donators, 0);
    }
    if (count($top_donators) > 0)
    {
    $top_donator = "<h4>Top 10 Contributors </h4>\n";
    if ($top_donators)
    {
    foreach($top_donators as $a) {
    $top_donators_id = (int)$a["id"];
    $damount_donated = (int)$a["total"];
    //$top_donators_username = htmlsafechars($a['username']);
    $user_stuff = $a;
    $user_stuff['id'] = (int)$a['userid'];
    $top_donator .= "<a href='{$INSTALLER09['baseurl']}/userdetails.php?id=$top_donators_id'>" . format_username($user_stuff) . "</a> [$damount_donated]<br />";
    }
    } else {
    //== If there are no donators
    if (empty($top_donators))
    $top_donator .= "Nobodys contibuted yet !!";
    }
    }
    //$mc1->delete_value('top_donators_');
    //==
    if(($top_donators2 = $mc1->get_value('top_donators2_')) === false) {
    $b = sql_query("SELECT bonuslog.id, SUM(bonuslog.donation) AS total, users.username, users.id AS userid, users.pirate, users.king, users.class, users.donor, users.warned, users.leechwarn, users.enabled, users.chatpost FROM bonuslog LEFT JOIN users ON bonuslog.id=users.id WHERE bonuslog.type = 'doubleupload' GROUP BY bonuslog.id ORDER BY total DESC LIMIT 10;") or sqlerr(__FILE__, __LINE__);
    while($top_donator2 = mysqli_fetch_assoc($b))
    $top_donators2[] = $top_donator2;
    $mc1->cache_value('top_donators2_', $top_donators2, 0);
    }
    if (count($top_donators2) > 0)
    {
    $top_donator2 = "<h4>Top 10 Contributors </h4>\n";
    if ($top_donators2)
    {
    foreach($top_donators2 as $b) {
    $top_donators2_id = (int)$b["id"];
    $damount_donated2 = (int)$b["total"];
    //$top_donators2_username = htmlsafechars($b['username']);
    $user_stuff = $b;
    $user_stuff['id'] = (int)$b['userid'];
    $top_donator2 .= "<a href='{$INSTALLER09['baseurl']}/userdetails.php?id=$top_donators2_id'>" . format_username($user_stuff) . "</a> [$damount_donated2]<br />";
    }
    } else {
    //== If there are no donators
    if (empty($top_donators2))
    $top_donator2 .= "Nobodys contibuted yet !!";
    }
    }
    //$mc1->delete_value('top_donators2_');
    //==
    if(($top_donators3 = $mc1->get_value('top_donators3_')) === false) {
    $c = sql_query("SELECT bonuslog.id, SUM(bonuslog.donation) AS total, users.username, users.id AS userid, users.pirate, users.king, users.class, users.donor, users.warned, users.leechwarn, users.enabled, users.chatpost FROM bonuslog LEFT JOIN users ON bonuslog.id=users.id WHERE bonuslog.type = 'halfdownload' GROUP BY bonuslog.id ORDER BY total DESC LIMIT 10;") or sqlerr(__FILE__, __LINE__);
    while($top_donator3 = mysqli_fetch_assoc($c))
    $top_donators3[] = $top_donator3;
    $mc1->cache_value('top_donators3_', $top_donators3, 0);
    }
    if (count($top_donators3) > 0)
    {
    $top_donator3 = "<h4>Top 10 Contributors </h4>\n";
    if ($top_donators3)
    {
    foreach($top_donators3 as $c) {
    $top_donators3_id = (int)$c["id"];
    $damount_donated3 = (int)$c["total"];
    //$top_donators3_username = htmlsafechars($c['username']);
    $user_stuff = $c;
    $user_stuff['id'] = (int)$c['userid'];
    $top_donator3 .= "<a href='{$INSTALLER09['baseurl']}/userdetails.php?id=$top_donators3_id'>" . format_username($user_stuff) . "</a> [$damount_donated3]<br />";
    }
    } else {
    //== If there are no donators
    if (empty($top_donators3))
    $top_donator3 .= "Nobodys contibuted yet !!";
    }
    }
    //$mc1->delete_value('top_donators3_');
    //==End
            if (XBT_TRACKER == false) {
            //== Show the percentages
            $HTMLOUT .="<div align='center' style='background:transparent;height:25px;'>&nbsp;FreeLeech&nbsp;[&nbsp;";
            if($freeleech_enabled){
            $HTMLOUT .="<font color=\"green\"><strong>&nbsp;ON</strong></font>&nbsp;".get_date($freeleech_start_time, 'DATE') . "&nbsp;-&nbsp;" .get_date($freeleech_end_time, 'DATE');
            } else {
            $HTMLOUT .="<strong>{$fstatus}</strong>";
            }
            $HTMLOUT .="&nbsp;]";
    
            $HTMLOUT .="&nbsp;DoubleUpload&nbsp;[&nbsp;";
            if($double_upload_enabled){
            $HTMLOUT .="<font color=\"green\"><strong>&nbsp;ON</strong></font>&nbsp;".get_date($double_upload_start_time, 'DATE') . "&nbsp;-&nbsp;" .get_date($double_upload_end_time, 'DATE');
            } else {
            $HTMLOUT .="<strong>{$dstatus}</strong>";
            }
            $HTMLOUT .="&nbsp;]";
            
            $HTMLOUT .="&nbsp;Half Download&nbsp;[&nbsp;";
            if($half_down_enabled){
            $HTMLOUT .="<font color=\"green\"><strong>&nbsp;ON</strong></font>&nbsp;".get_date($half_down_start_time, 'DATE') . "&nbsp;-&nbsp;" .get_date($half_down_end_time, 'DATE');
            } else {
            $HTMLOUT .="<strong>{$hstatus}</strong>";
            }
            $HTMLOUT .="&nbsp;]</div>";
            //==End
            }
            $bonus = (float)$CURUSER['seedbonus'];
            $HTMLOUT .="
            <table align='center' width='100%' border='1' cellspacing='0' cellpadding='5'>
            <tr>
            <td align='center' colspan='4' style='background:transparent;height:25px;'>
            Exchange your <a class='altlink' href='{$INSTALLER09['baseurl']}/mybonus.php'>Karma Bonus Points</a> [ current ".$bonus." ] for goodies!
            <br /><br />[ If no buttons appear, you have not earned enough bonus points to trade. ]<br /><br />
            </td></tr>
            <tr>
            <td style='background:transparent;height:25px;' align='left'>Description</td>
            <td style='background:transparent;height:25px;' align='center'>Points</td>
            <td style='background:transparent;height:25px;' align='center'>Trade</td></tr>";

            $res = sql_query("SELECT * FROM bonus WHERE enabled = 'yes' ORDER BY id ASC") or sqlerr(__FILE__, __LINE__);
            while ($gets = mysqli_fetch_assoc($res)){
            //=======change colors
            $count1= (++$count1)%2;
            $class = ($count1 == 0 ? 'one':'two');
            $otheroption = "
            <div class='".$class."'><b>Username:</b>
            <input type='text' name='username' size='20' maxlength='24' /></div>
            <div class='".$class."'> <b>to be given: </b>
            <select name='bonusgift'> 
            <option value='100.0'> 100.0</option> 
            <option value='200.0'> 200.0</option> 
            <option value='300.0'> 300.0</option> 
            <option value='400.0'> 400.0</option>
            <option value='500.0'> 500.0</option>
            <option value='1000.0'> 1000.0</option>
            <option value='5000.0'> 5000.0</option>
            <option value='10000.0'> 20000.0</option>
            <option value='20000.0'> 20000.0</option>
            <option value='50000.0'> 50000.0</option>
            <option value='100000.0'> 100000.0</option>
            </select> Karma points!</div>";

  switch (true){
 	case ($gets['id'] == 5):
 	$HTMLOUT .="<tr><td align='left' class='".$class."'><form action='{$INSTALLER09['baseurl']}/mybonus.php?exchange=1' method='post'><input type='hidden' name='option' value='".(int)$gets['id']."' /><input type='hidden' name='art' value='".htmlsafechars($gets['art'])."' /><h1><font color='#000000'>".htmlsafechars($gets['bonusname'])."</font></h1>".htmlsafechars($gets['description'])."<br /><br />Enter the <b>Special Title</b> you would like to have <input type='text' name='title' size='30' maxlength='30' /> click Exchange! </td><td align='center' class='".$class."'>".(float)$gets['points']."</td>";
  break;
  case ($gets['id'] == 7):
  $HTMLOUT .="<tr><td align='left' class='".$class."'><form action='{$INSTALLER09['baseurl']}/mybonus.php?exchange=1' method='post'><input type='hidden' name='option' value='".(int)$gets['id']."' /> <input type='hidden' name='art' value='".htmlsafechars($gets['art'])."' /><h1><font color='#000000'>".htmlsafechars($gets['bonusname'])."</font></h1>".htmlsafechars($gets['description'])."<br /><br />Enter the <b>username</b> of the person you would like to send karma to, and select how many points you want to send and click Exchange!<br />".$otheroption."</td><td align=center class='".$class."'>min.<br />".(float)$gets['points']."<br />max.<br />100000.0</td>";
  break;
  case ($gets['id'] == 9):
  $HTMLOUT .="<tr><td align='left' class='".$class."'><form action='{$INSTALLER09['baseurl']}/mybonus.php?exchange=1' method='post'><input type='hidden' name='option' value='".(int)$gets['id']."' /> <input type='hidden' name='art' value='".htmlsafechars($gets['art'])."' /><h1><font color='#000000'>".htmlsafechars($gets['bonusname'])."</font></h1>".htmlsafechars($gets['description'])."</td><td align='center' class='".$class."'>min.<br />".(float)$gets['points']."</td>";
  break;
  case ($gets['id'] == 10):
  $HTMLOUT .="<tr><td align='left' class='".$class."'><form action='{$INSTALLER09['baseurl']}/mybonus.php?exchange=1' method='post'><input type='hidden' name='option' value='".(int)$gets['id']."' /> <input type='hidden' name='art' value='".htmlsafechars($gets['art'])."' /><h1><font color='#000000'>".htmlsafechars($gets['bonusname'])."</font></h1>".htmlsafechars($gets['description'])."<br /><br />Enter the <b>ID number of the Torrent:</b> <input type='text' name='torrent_id' size='4' maxlength='8' /> you would like to buy a 1 to 1 ratio on.</td><td align='center' class='".$class."'>min.<br />".(float)$gets['points']."</td>";
  break;
  case ($gets['id'] == 11):
  $HTMLOUT .="<tr><td align='left' class='".$class."'><form action='{$INSTALLER09['baseurl']}/mybonus.php?exchange=1' method='post'><input type='hidden' name='option' value='".(int)$gets['id']."' /> <input type='hidden' name='art' value='".htmlsafechars($gets['art'])."' /><h1><font color=\"#000000\">".htmlsafechars($gets["bonusname"])."</font></h1>".htmlsafechars($gets['description'])."<br />".$top_donator."<br />Enter the <b>amount to contribute</b><input type='text' name='donate' size='10' maxlength='10' /></td><td align='center' class='".$class."'>" .(float)$gets['minpoints']." <br /></td>";
  break;
  case ($gets['id'] == 12):
  $HTMLOUT .="<tr><td align='left' class='".$class."'><form action='{$INSTALLER09['baseurl']}/mybonus.php?exchange=1' method='post'><input type='hidden' name='option' value='".(int)$gets['id']."' /> <input type='hidden' name='art' value='".htmlsafechars($gets['art'])."' /><h1><font color=\"#000000\">".htmlsafechars($gets["bonusname"])."</font></h1>".htmlsafechars($gets['description'])."<br />".$top_donator2."<br />Enter the <b>amount to contribute</b><input type='text' name='donate' size='10' maxlength='10' /></td><td align='center' class='".$class."'>".(float)$gets['minpoints']." <br /></td>";
  break;
  case ($gets['id'] == 13):
  $HTMLOUT .="<tr><td align='left' class='".$class."'><form action='{$INSTALLER09['baseurl']}/mybonus.php?exchange=1' method='post'><input type='hidden' name='option' value='".(int)$gets['id']."' /><input type='hidden' name='art' value='".htmlsafechars($gets['art'])."' /><h1><font color=\"#000000\">".htmlsafechars($gets["bonusname"])."</font></h1>".htmlsafechars($gets['description'])."<br />".$top_donator3."<br />Enter the <b>amount to contribute</b><input type='text' name='donate' size='10' maxlength='10' /></td><td align='center' class='".$class."'>".(float)$gets['minpoints']." <br /></td>";
  break;
  case ($gets['id'] == 34):
  $HTMLOUT .="<tr><td align='left' class='".$class."'><form action='{$INSTALLER09['baseurl']}/mybonus.php?exchange=1' method='post'><input type='hidden' name='option' value='".(int)$gets['id']."' /><input type='hidden' name='art' value='".htmlsafechars($gets['art'])."' /><h1><font color='#000000'>".htmlsafechars($gets['bonusname'])."</font></h1>".htmlsafechars($gets['description'])."<br /><br />Enter the <b>ID number of the Torrent:</b> <input type='text' name='torrent_id' size='4' maxlength='8' /> you would like to bump.</td><td align='center' class='".$class."'>min.<br />".(float)$gets['points']."</td>";
  break;
  default:
  $HTMLOUT .="<tr><td align='left' class='".$class."'><form action='{$INSTALLER09['baseurl']}/mybonus.php?exchange=1' method='post'><input type='hidden' name='option' value='".(int)$gets['id']."' /><input type='hidden' name='art' value='".htmlsafechars($gets['art'])."' /><h1><font color='#000000'>".htmlsafechars($gets['bonusname'])."</font></h1>".htmlsafechars($gets['description'])."</td><td align='center' class='".$class."'>".(float)$gets['points']."</td>";
  }


  if($bonus >= $gets['points'] OR $bonus >= $gets['minpoints']) {
  switch (true){
  case ($gets['id'] == 7):
  $HTMLOUT .="<td class='".$class."'><input class='button' type='submit' name='submit' value='Karma Gift!' /></td></form>";
  break;
  case ($gets['id'] == 11 ):
  $HTMLOUT .="<td align='center' class='".$class."'>". ((float)$gets['points'] - (float)$gets['pointspool']) . " <br />Points needed! <br /><input class='button' type='submit' name='submit' value='Contribute!' /></td></form>";
  break;
  case ($gets['id'] == 12 ):
  $HTMLOUT .="<td align='center' class='".$class."'>". ((float)$gets['points'] - (float)$gets['pointspool']) . " <br />Points needed! <br /><input class='button' type='submit' name='submit' value='Contribute!' /></td></form>";
  break;
  case ($gets['id'] == 13 ):
  $HTMLOUT .="<td align='center' class='".$class."'>". ((float)$gets['points'] - (float)$gets['pointspool']) . " <br />Points needed! <br /><input class='button' type='submit' name='submit' value='Contribute!' /></td></form>";
  break;
  default:
  $HTMLOUT .="<td class='".$class."'><input class='button' type='submit' name='submit' value='Exchange!' /></td></form>";
  }
  }
  else 
  $HTMLOUT .="<td class='".$class."' align='center'><b>Not Enough Karma</b></td>";
  }

  $HTMLOUT .="</tr></table>
  <div style='background:transparent;height:25px;'>
  <span style='font-weight:bold;font-size:12pt;'>What the hell are these Karma Bonus points,
  and how do I get them?</span></div>
  <table align='center' width='100%'>
  <tr>
  <td class='one'>For every hour that you seed a torrent, you are awarded with 1 Karma Bonus Point... <br />
  If you save up enough of them, you can trade them in for goodies like bonus GB(s) to increase your upload stats,<br /> 
  also to get more invites, or doing the real Karma booster... give them to another user !<br />
  This is awarded on a per torrent basis (max of 1000) even if there are no leechers on the Torrent you are seeding! <br />
  <h1>Other things that will get you karma points : </h1>
  &#186;&nbsp;Uploading a new torrent = 15 points
  <br />&#186;&nbsp;Filling a request = 10 points
  <br />&#186;&nbsp;Comment on torrent = 3 points
  <br />&#186;&nbsp;Saying thanks = 2 points
  <br />&#186;&nbsp;Rating a torrent = 2 points
  <br />&#186;&nbsp;Making a post = 1 point
  <br />&#186;&nbsp;Starting a topic = 2 points 
  <br />
  <h1>Some things that will cost you karma points:</h1>
  <br />
  &#186;&nbsp;Upload credit
  <br />&#186;&nbsp;Custom title
  <br />&#186;&nbsp;One month VIP status
  <br />&#186;&nbsp;A 1:1 ratio on a torrent
  <br />&#186;&nbsp;Buying off your warning
  <br />&#186;&nbsp;One month custom smilies for the forums and comments
  <br />&#186;&nbsp;Getting extra invites
  <br />&#186;&nbsp;Getting extra freeslots
  <br />&#186;&nbsp;Giving a gift of karma points to another user
  <br />&#186;&nbsp;Asking for a re-seed
  <br />&#186;&nbsp;Making a request
  <br />&#186;&nbsp;Freeleech, Doubleupload, Halfdownload contribution
  <br />&#186;&nbsp;Anonymous profile
  <br />&#186;&nbsp;Download reduction
  <br />&#186;&nbsp;Freeleech for a year
  <br />&#186;&nbsp;Pirate or King status
  <br />&#186;&nbsp;Unlocking parked option
  <br />&#186;&nbsp;Pirates bounty
  <br />&#186;&nbsp;Reputation points
  <br />&#186;&nbsp;Userblocks
  <br />&#186;&nbsp;Bump a torrent
  <br />&#186;&nbsp;User immuntiy
  <br />&#186;&nbsp;User unlocks
  <br />&#186;&nbsp;But keep in mind that everything that can get you karma can also be lost...<br /><br />
  Ie : if you up a torrent then delete it, you will gain and then lose 15 points, making a post and having it deleted will do the same... and there are other hidden bonus karma points all 
  over the site which is another way to help out your ratio ! 
  <br /><br />&#186;&nbsp;*Please note, staff can give or take away points for breaking the rules, or doing good for the community.
  <br />
  <div align='center'><br />
  <a class='altlink' href='{$INSTALLER09['baseurl']}/index.php'><b>Back to homepage</b></a></div>
  </td></tr></table></div>";

echo stdhead($CURUSER['username'] . "'s Karma Bonus Points Page", true, $stdhead) . $HTMLOUT . stdfoot();
?>
