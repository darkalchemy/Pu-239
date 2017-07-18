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
/*
+------------------------------------------------
|   $Date$ 10022011
|   $Revision$ 1.0
|   $Author$ pdq,Bigjoos
|   $User block system
|   
+------------------------------------------------
*/
require_once (dirname(__FILE__) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php');
require_once (INCL_DIR . 'html_functions.php');
require_once (INCL_DIR . 'user_functions.php');
dbconn(false);
loggedinorreturn();
$stdfoot = array(
    /** include js **/
    'js' => array(
        'custom-form-elements'
    )
);
$stdhead = array(
    /** include css **/
    'css' => array(
        'user_blocks',
        'checkbox',
        'hide'
    )
);
$lang = load_language('global');
$id = (isset($_GET['id']) ? $_GET['id'] : $CURUSER['id']);
if (!is_valid_id($id) || $CURUSER['class'] < UC_STAFF) $id = $CURUSER['id'];
if ($CURUSER['got_blocks'] == 'no') {
    stderr($lang['gl_error'], "Time shall unfold what plighted cunning hides\n\nWho cover faults, at last shame them derides.... Go to your Karma bonus page and buy this unlock before trying to access it.");
    die;
}
    //$mc1->delete_value('blocks::' . $id);
    
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $updateset = array();
    $setbits_index_page = $clrbits_index_page = $setbits_global_stdhead = $clrbits_global_stdhead = $setbits_userdetails_page = $clrbits_userdetails_page = 0;
    //==Index
    if (isset($_POST['ie_alert'])) $setbits_index_page|= block_index::IE_ALERT;
    else $clrbits_index_page|= block_index::IE_ALERT;
    if (isset($_POST['news'])) $setbits_index_page|= block_index::NEWS;
    else $clrbits_index_page|= block_index::NEWS;
    if (isset($_POST['shoutbox'])) $setbits_index_page|= block_index::SHOUTBOX;
    else $clrbits_index_page|= block_index::SHOUTBOX;
    if (isset($_POST['active_users'])) $setbits_index_page|= block_index::ACTIVE_USERS;
    else $clrbits_index_page|= block_index::ACTIVE_USERS;
    if (isset($_POST['last_24_active_users'])) $setbits_index_page|= block_index::LAST_24_ACTIVE_USERS;
    else $clrbits_index_page|= block_index::LAST_24_ACTIVE_USERS;
    if (isset($_POST['irc_active_users'])) $setbits_index_page|= block_index::IRC_ACTIVE_USERS;
    else $clrbits_index_page|= block_index::IRC_ACTIVE_USERS;
    if (isset($_POST['birthday_active_users'])) $setbits_index_page|= block_index::BIRTHDAY_ACTIVE_USERS;
    else $clrbits_index_page|= block_index::BIRTHDAY_ACTIVE_USERS;
    if (isset($_POST['stats'])) $setbits_index_page|= block_index::STATS;
    else $clrbits_index_page|= block_index::STATS;
    if (isset($_POST['disclaimer'])) $setbits_index_page|= block_index::DISCLAIMER;
    else $clrbits_index_page|= block_index::DISCLAIMER;
    if (isset($_POST['latest_user'])) $setbits_index_page|= block_index::LATEST_USER;
    else $clrbits_index_page|= block_index::LATEST_USER;
    if (isset($_POST['forumposts'])) $setbits_index_page|= block_index::FORUMPOSTS;
    else $clrbits_index_page|= block_index::FORUMPOSTS;
    if (isset($_POST['latest_torrents'])) $setbits_index_page|= block_index::LATEST_TORRENTS;
    else $clrbits_index_page|= block_index::LATEST_TORRENTS;
    if (isset($_POST['latest_torrents_scroll'])) $setbits_index_page|= block_index::LATEST_TORRENTS_SCROLL;
    else $clrbits_index_page|= block_index::LATEST_TORRENTS_SCROLL;
    if (isset($_POST['announcement'])) $setbits_index_page|= block_index::ANNOUNCEMENT;
    else $clrbits_index_page|= block_index::ANNOUNCEMENT;
    if (isset($_POST['donation_progress'])) $setbits_index_page|= block_index::DONATION_PROGRESS;
    else $clrbits_index_page|= block_index::DONATION_PROGRESS;
    if (isset($_POST['advertisements'])) $setbits_index_page|= block_index::ADVERTISEMENTS;
    else $clrbits_index_page|= block_index::ADVERTISEMENTS;
    if (isset($_POST['radio'])) $setbits_index_page|= block_index::RADIO;
    else $clrbits_index_page|= block_index::RADIO;
    if (isset($_POST['torrentfreak'])) $setbits_index_page|= block_index::TORRENTFREAK;
    else $clrbits_index_page|= block_index::TORRENTFREAK;
    if (isset($_POST['xmas_gift'])) $setbits_index_page|= block_index::XMAS_GIFT;
    else $clrbits_index_page|= block_index::XMAS_GIFT;
    if (isset($_POST['active_poll'])) $setbits_index_page|= block_index::ACTIVE_POLL;
    else $clrbits_index_page|= block_index::ACTIVE_POLL;
    if (isset($_POST['staff_shoutbox'])) $setbits_index_page|= block_index::STAFF_SHOUT;
    else $clrbits_index_page|= block_index::STAFF_SHOUT;
    if (isset($_POST['movie_ofthe_week'])) $setbits_index_page|= block_index::MOVIEOFWEEK;
    else $clrbits_index_page|= block_index::MOVIEOFWEEK;
    //==Stdhead
    if (isset($_POST['stdhead_freeleech'])) $setbits_global_stdhead|= block_stdhead::STDHEAD_FREELEECH;
    else $clrbits_global_stdhead|= block_stdhead::STDHEAD_FREELEECH;
    if (isset($_POST['stdhead_demotion'])) $setbits_global_stdhead|= block_stdhead::STDHEAD_DEMOTION;
    else $clrbits_global_stdhead|= block_stdhead::STDHEAD_DEMOTION;
    if (isset($_POST['stdhead_newpm'])) $setbits_global_stdhead|= block_stdhead::STDHEAD_NEWPM;
    else $clrbits_global_stdhead|= block_stdhead::STDHEAD_NEWPM;
    if (isset($_POST['stdhead_staff_message'])) $setbits_global_stdhead|= block_stdhead::STDHEAD_STAFF_MESSAGE;
    else $clrbits_global_stdhead|= block_stdhead::STDHEAD_STAFF_MESSAGE;
    if (isset($_POST['stdhead_reports'])) $setbits_global_stdhead|= block_stdhead::STDHEAD_REPORTS;
    else $clrbits_global_stdhead|= block_stdhead::STDHEAD_REPORTS;
    if (isset($_POST['stdhead_uploadapp'])) $setbits_global_stdhead|= block_stdhead::STDHEAD_UPLOADAPP;
    else $clrbits_global_stdhead|= block_stdhead::STDHEAD_UPLOADAPP;
    if (isset($_POST['stdhead_happyhour'])) $setbits_global_stdhead|= block_stdhead::STDHEAD_HAPPYHOUR;
    else $clrbits_global_stdhead|= block_stdhead::STDHEAD_HAPPYHOUR;
    if (isset($_POST['stdhead_crazyhour'])) $setbits_global_stdhead|= block_stdhead::STDHEAD_CRAZYHOUR;
    else $clrbits_global_stdhead|= block_stdhead::STDHEAD_CRAZYHOUR;
    if (isset($_POST['stdhead_bugmessage'])) $setbits_global_stdhead|= block_stdhead::STDHEAD_BUG_MESSAGE;
    else $clrbits_global_stdhead|= block_stdhead::STDHEAD_BUG_MESSAGE;
    if (isset($_POST['stdhead_freeleech_contribution'])) $setbits_global_stdhead|= block_stdhead::STDHEAD_FREELEECH_CONTRIBUTION;
    else $clrbits_global_stdhead|= block_stdhead::STDHEAD_FREELEECH_CONTRIBUTION;
    //==Userdetails
    if (isset($_POST['userdetails_login_link'])) $setbits_userdetails_page|= block_userdetails::LOGIN_LINK;
    else $clrbits_userdetails_page|= block_userdetails::LOGIN_LINK;
    if (isset($_POST['userdetails_flush'])) $setbits_userdetails_page|= block_userdetails::FLUSH;
    else $clrbits_userdetails_page|= block_userdetails::FLUSH;
    if (isset($_POST['userdetails_joined'])) $setbits_userdetails_page|= block_userdetails::JOINED;
    else $clrbits_userdetails_page|= block_userdetails::JOINED;
    if (isset($_POST['userdetails_online_time'])) $setbits_userdetails_page|= block_userdetails::ONLINETIME;
    else $clrbits_userdetails_page|= block_userdetails::ONLINETIME;
    if (isset($_POST['userdetails_browser'])) $setbits_userdetails_page|= block_userdetails::BROWSER;
    else $clrbits_userdetails_page|= block_userdetails::BROWSER;
    if (isset($_POST['userdetails_reputation'])) $setbits_userdetails_page|= block_userdetails::REPUTATION;
    else $clrbits_userdetails_page|= block_userdetails::REPUTATION;
    if (isset($_POST['userdetails_user_hits'])) $setbits_userdetails_page|= block_userdetails::PROFILE_HITS;
    else $clrbits_userdetails_page|= block_userdetails::PROFILE_HITS;
    if (isset($_POST['userdetails_birthday'])) $setbits_userdetails_page|= block_userdetails::BIRTHDAY;
    else $clrbits_userdetails_page|= block_userdetails::BIRTHDAY;
    if (isset($_POST['userdetails_birthday'])) $setbits_userdetails_page|= block_userdetails::BIRTHDAY;
    else $clrbits_userdetails_page|= block_userdetails::BIRTHDAY;
    if (isset($_POST['userdetails_contact_info'])) $setbits_userdetails_page|= block_userdetails::CONTACT_INFO;
    else $clrbits_userdetails_page|= block_userdetails::CONTACT_INFO;
    if (isset($_POST['userdetails_iphistory'])) $setbits_userdetails_page|= block_userdetails::IPHISTORY;
    else $clrbits_userdetails_page|= block_userdetails::IPHISTORY;
    if (isset($_POST['userdetails_traffic'])) $setbits_userdetails_page|= block_userdetails::TRAFFIC;
    else $clrbits_userdetails_page|= block_userdetails::TRAFFIC;
    if (isset($_POST['userdetails_share_ratio'])) $setbits_userdetails_page|= block_userdetails::SHARE_RATIO;
    else $clrbits_userdetails_page|= block_userdetails::SHARE_RATIO;
    if (isset($_POST['userdetails_seedtime_ratio'])) $setbits_userdetails_page|= block_userdetails::SEEDTIME_RATIO;
    else $clrbits_userdetails_page|= block_userdetails::SEEDTIME_RATIO;
    if (isset($_POST['userdetails_seedbonus'])) $setbits_userdetails_page|= block_userdetails::SEEDBONUS;
    else $clrbits_userdetails_page|= block_userdetails::SEEDBONUS;
    if (isset($_POST['userdetails_irc_stats'])) $setbits_userdetails_page|= block_userdetails::IRC_STATS;
    else $clrbits_userdetails_page|= block_userdetails::IRC_STATS;
    if (isset($_POST['userdetails_connectable_port'])) $setbits_userdetails_page|= block_userdetails::CONNECTABLE_PORT;
    else $clrbits_userdetails_page|= block_userdetails::CONNECTABLE_PORT;
    if (isset($_POST['userdetails_avatar'])) $setbits_userdetails_page|= block_userdetails::AVATAR;
    else $clrbits_userdetails_page|= block_userdetails::AVATAR;
    if (isset($_POST['userdetails_userclass'])) $setbits_userdetails_page|= block_userdetails::USERCLASS;
    else $clrbits_userdetails_page|= block_userdetails::USERCLASS;
    if (isset($_POST['userdetails_gender'])) $setbits_userdetails_page|= block_userdetails::GENDER;
    else $clrbits_userdetails_page|= block_userdetails::GENDER;
    if (isset($_POST['userdetails_freestuffs'])) $setbits_userdetails_page|= block_userdetails::FREESTUFFS;
    else $clrbits_userdetails_page|= block_userdetails::FREESTUFFS;
    if (isset($_POST['userdetails_comments'])) $setbits_userdetails_page|= block_userdetails::COMMENTS;
    else $clrbits_userdetails_page|= block_userdetails::COMMENTS;
    if (isset($_POST['userdetails_forumposts'])) $setbits_userdetails_page|= block_userdetails::FORUMPOSTS;
    else $clrbits_userdetails_page|= block_userdetails::FORUMPOSTS;
    if (isset($_POST['userdetails_invitedby'])) $setbits_userdetails_page|= block_userdetails::INVITEDBY;
    else $clrbits_userdetails_page|= block_userdetails::INVITEDBY;
    if (isset($_POST['userdetails_torrents_block'])) $setbits_userdetails_page|= block_userdetails::TORRENTS_BLOCK;
    else $clrbits_userdetails_page|= block_userdetails::TORRENTS_BLOCK;
    if (isset($_POST['userdetails_completed'])) $setbits_userdetails_page|= block_userdetails::COMPLETED;
    else $clrbits_userdetails_page|= block_userdetails::COMPLETED;
    if (isset($_POST['userdetails_snatched_staff'])) $setbits_userdetails_page|= block_userdetails::SNATCHED_STAFF;
    else $clrbits_userdetails_page|= block_userdetails::SNATCHED_STAFF;
    if (isset($_POST['userdetails_userinfo'])) $setbits_userdetails_page|= block_userdetails::USERINFO;
    else $clrbits_userdetails_page|= block_userdetails::USERINFO;
    if (isset($_POST['userdetails_showpm'])) $setbits_userdetails_page|= block_userdetails::SHOWPM;
    else $clrbits_userdetails_page|= block_userdetails::SHOWPM;
    if (isset($_POST['userdetails_report_user'])) $setbits_userdetails_page|= block_userdetails::REPORT_USER;
    else $clrbits_userdetails_page|= block_userdetails::REPORT_USER;
    if (isset($_POST['userdetails_user_status'])) $setbits_userdetails_page|= block_userdetails::USERSTATUS;
    else $clrbits_userdetails_page|= block_userdetails::USERSTATUS;
    if (isset($_POST['userdetails_user_comments'])) $setbits_userdetails_page|= block_userdetails::USERCOMMENTS;
    else $clrbits_userdetails_page|= block_userdetails::USERCOMMENTS;
    //== set n clear
    if ($setbits_index_page) $updateset[] = 'index_page = (index_page | ' . $setbits_index_page . ')';
    if ($clrbits_index_page) $updateset[] = 'index_page = (index_page & ~' . $clrbits_index_page . ')';
    if ($setbits_global_stdhead) $updateset[] = 'global_stdhead = (global_stdhead | ' . $setbits_global_stdhead . ')';
    if ($clrbits_global_stdhead) $updateset[] = 'global_stdhead = (global_stdhead & ~' . $clrbits_global_stdhead . ')';
    if ($setbits_userdetails_page) $updateset[] = 'userdetails_page = (userdetails_page | ' . $setbits_userdetails_page . ')';
    if ($clrbits_userdetails_page) $updateset[] = 'userdetails_page = (userdetails_page & ~' . $clrbits_userdetails_page . ')';
    if (count($updateset)) sql_query('UPDATE user_blocks SET ' . implode(',', $updateset) . ' WHERE userid = ' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
    $mc1->delete_value('blocks::' . $id);
    header('Location: ' . $INSTALLER09['baseurl'] . '/user_blocks.php');
    exit();
}
//==Index
$checkbox_index_ie_alert = ((curuser::$blocks['index_page'] & block_index::IE_ALERT) ? ' checked="checked"' : '');
$checkbox_index_news = ((curuser::$blocks['index_page'] & block_index::NEWS) ? ' checked="checked"' : '');
$checkbox_index_shoutbox = ((curuser::$blocks['index_page'] & block_index::SHOUTBOX) ? ' checked="checked"' : '');
$checkbox_index_active_users = ((curuser::$blocks['index_page'] & block_index::ACTIVE_USERS) ? ' checked="checked"' : '');
$checkbox_index_active_24h_users = ((curuser::$blocks['index_page'] & block_index::LAST_24_ACTIVE_USERS) ? ' checked="checked"' : '');
$checkbox_index_active_irc_users = ((curuser::$blocks['index_page'] & block_index::IRC_ACTIVE_USERS) ? ' checked="checked"' : '');
$checkbox_index_active_birthday_users = ((curuser::$blocks['index_page'] & block_index::BIRTHDAY_ACTIVE_USERS) ? ' checked="checked"' : '');
$checkbox_index_stats = ((curuser::$blocks['index_page'] & block_index::STATS) ? ' checked="checked"' : '');
$checkbox_index_disclaimer = ((curuser::$blocks['index_page'] & block_index::DISCLAIMER) ? ' checked="checked"' : '');
$checkbox_index_latest_user = ((curuser::$blocks['index_page'] & block_index::LATEST_USER) ? ' checked="checked"' : '');
$checkbox_index_latest_forumposts = ((curuser::$blocks['index_page'] & block_index::FORUMPOSTS) ? ' checked="checked"' : '');
$checkbox_index_latest_torrents = ((curuser::$blocks['index_page'] & block_index::LATEST_TORRENTS) ? ' checked="checked"' : '');
$checkbox_index_latest_torrents_scroll = ((curuser::$blocks['index_page'] & block_index::LATEST_TORRENTS_SCROLL) ? ' checked="checked"' : '');
$checkbox_index_announcement = ((curuser::$blocks['index_page'] & block_index::ANNOUNCEMENT) ? ' checked="checked"' : '');
$checkbox_index_donation_progress = ((curuser::$blocks['index_page'] & block_index::DONATION_PROGRESS) ? ' checked="checked"' : '');
$checkbox_index_ads = ((curuser::$blocks['index_page'] & block_index::ADVERTISEMENTS) ? ' checked="checked"' : '');
$checkbox_index_radio = ((curuser::$blocks['index_page'] & block_index::RADIO) ? ' checked="checked"' : '');
$checkbox_index_torrentfreak = ((curuser::$blocks['index_page'] & block_index::TORRENTFREAK) ? ' checked="checked"' : '');
$checkbox_index_xmasgift = ((curuser::$blocks['index_page'] & block_index::XMAS_GIFT) ? ' checked="checked"' : '');
$checkbox_index_active_poll = ((curuser::$blocks['index_page'] & block_index::ACTIVE_POLL) ? ' checked="checked"' : '');
$checkbox_index_staffshoutbox = ((curuser::$blocks['index_page'] & block_index::STAFF_SHOUT) ? ' checked="checked"' : '');
$checkbox_index_mow = ((curuser::$blocks['index_page'] & block_index::MOVIEOFWEEK) ? ' checked="checked"' : '');
//==Stdhead
$checkbox_global_freeleech = ((curuser::$blocks['global_stdhead'] & block_stdhead::STDHEAD_FREELEECH) ? ' checked="checked"' : '');
$checkbox_global_demotion = ((curuser::$blocks['global_stdhead'] & block_stdhead::STDHEAD_DEMOTION) ? ' checked="checked"' : '');
$checkbox_global_message_alert = ((curuser::$blocks['global_stdhead'] & block_stdhead::STDHEAD_NEWPM) ? ' checked="checked"' : '');
$checkbox_global_staff_message_alert = ((curuser::$blocks['global_stdhead'] & block_stdhead::STDHEAD_STAFF_MESSAGE) ? ' checked="checked"' : '');
$checkbox_global_staff_report = ((curuser::$blocks['global_stdhead'] & block_stdhead::STDHEAD_REPORTS) ? ' checked="checked"' : '');
$checkbox_global_staff_uploadapp = ((curuser::$blocks['global_stdhead'] & block_stdhead::STDHEAD_UPLOADAPP) ? ' checked="checked"' : '');
$checkbox_global_happyhour = ((curuser::$blocks['global_stdhead'] & block_stdhead::STDHEAD_HAPPYHOUR) ? ' checked="checked"' : '');
$checkbox_global_crazyhour = ((curuser::$blocks['global_stdhead'] & block_stdhead::STDHEAD_CRAZYHOUR) ? ' checked="checked"' : '');
$checkbox_global_bugmessage = ((curuser::$blocks['global_stdhead'] & block_stdhead::STDHEAD_BUG_MESSAGE) ? ' checked="checked"' : '');
$checkbox_global_freeleech_contribution = ((curuser::$blocks['global_stdhead'] & block_stdhead::STDHEAD_FREELEECH_CONTRIBUTION) ? ' checked="checked"' : '');
//==Userdetails
$checkbox_userdetails_login_link = ((curuser::$blocks['userdetails_page'] & block_userdetails::LOGIN_LINK) ? ' checked="checked"' : '');
$checkbox_userdetails_flush = ((curuser::$blocks['userdetails_page'] & block_userdetails::FLUSH) ? ' checked="checked"' : '');
$checkbox_userdetails_joined = ((curuser::$blocks['userdetails_page'] & block_userdetails::JOINED) ? ' checked="checked"' : '');
$checkbox_userdetails_onlinetime = ((curuser::$blocks['userdetails_page'] & block_userdetails::ONLINETIME) ? ' checked="checked"' : '');
$checkbox_userdetails_browser = ((curuser::$blocks['userdetails_page'] & block_userdetails::BROWSER) ? ' checked="checked"' : '');
$checkbox_userdetails_reputation = ((curuser::$blocks['userdetails_page'] & block_userdetails::REPUTATION) ? ' checked="checked"' : '');
$checkbox_userdetails_userhits = ((curuser::$blocks['userdetails_page'] & block_userdetails::PROFILE_HITS) ? ' checked="checked"' : '');
$checkbox_userdetails_birthday = ((curuser::$blocks['userdetails_page'] & block_userdetails::BIRTHDAY) ? ' checked="checked"' : '');
$checkbox_userdetails_contact_info = ((curuser::$blocks['userdetails_page'] & block_userdetails::CONTACT_INFO) ? ' checked="checked"' : '');
$checkbox_userdetails_iphistory = ((curuser::$blocks['userdetails_page'] & block_userdetails::IPHISTORY) ? ' checked="checked"' : '');
$checkbox_userdetails_traffic = ((curuser::$blocks['userdetails_page'] & block_userdetails::TRAFFIC) ? ' checked="checked"' : '');
$checkbox_userdetails_shareratio = ((curuser::$blocks['userdetails_page'] & block_userdetails::SHARE_RATIO) ? ' checked="checked"' : '');
$checkbox_userdetails_seedtime_ratio = ((curuser::$blocks['userdetails_page'] & block_userdetails::SEEDTIME_RATIO) ? ' checked="checked"' : '');
$checkbox_userdetails_seedbonus = ((curuser::$blocks['userdetails_page'] & block_userdetails::SEEDBONUS) ? ' checked="checked"' : '');
$checkbox_userdetails_irc_stats = ((curuser::$blocks['userdetails_page'] & block_userdetails::IRC_STATS) ? ' checked="checked"' : '');
$checkbox_userdetails_connectable = ((curuser::$blocks['userdetails_page'] & block_userdetails::CONNECTABLE_PORT) ? ' checked="checked"' : '');
$checkbox_userdetails_avatar = ((curuser::$blocks['userdetails_page'] & block_userdetails::AVATAR) ? ' checked="checked"' : '');
$checkbox_userdetails_userclass = ((curuser::$blocks['userdetails_page'] & block_userdetails::USERCLASS) ? ' checked="checked"' : '');
$checkbox_userdetails_gender = ((curuser::$blocks['userdetails_page'] & block_userdetails::GENDER) ? ' checked="checked"' : '');
$checkbox_userdetails_freestuffs = ((curuser::$blocks['userdetails_page'] & block_userdetails::FREESTUFFS) ? ' checked="checked"' : '');
$checkbox_userdetails_torrent_comments = ((curuser::$blocks['userdetails_page'] & block_userdetails::COMMENTS) ? ' checked="checked"' : '');
$checkbox_userdetails_forumposts = ((curuser::$blocks['userdetails_page'] & block_userdetails::FORUMPOSTS) ? ' checked="checked"' : '');
$checkbox_userdetails_invitedby = ((curuser::$blocks['userdetails_page'] & block_userdetails::INVITEDBY) ? ' checked="checked"' : '');
$checkbox_userdetails_torrents_block = ((curuser::$blocks['userdetails_page'] & block_userdetails::TORRENTS_BLOCK) ? ' checked="checked"' : '');
$checkbox_userdetails_completed = ((curuser::$blocks['userdetails_page'] & block_userdetails::COMPLETED) ? ' checked="checked"' : '');
$checkbox_userdetails_snatched_staff = ((curuser::$blocks['userdetails_page'] & block_userdetails::SNATCHED_STAFF) ? ' checked="checked"' : '');
$checkbox_userdetails_userinfo = ((curuser::$blocks['userdetails_page'] & block_userdetails::USERINFO) ? ' checked="checked"' : '');
$checkbox_userdetails_showpm = ((curuser::$blocks['userdetails_page'] & block_userdetails::SHOWPM) ? ' checked="checked"' : '');
$checkbox_userdetails_report = ((curuser::$blocks['userdetails_page'] & block_userdetails::REPORT_USER) ? ' checked="checked"' : '');
$checkbox_userdetails_userstatus = ((curuser::$blocks['userdetails_page'] & block_userdetails::USERSTATUS) ? ' checked="checked"' : '');
$checkbox_userdetails_usercomments = ((curuser::$blocks['userdetails_page'] & block_userdetails::USERCOMMENTS) ? ' checked="checked"' : '');
$HTMLOUT = '';
$HTMLOUT.= '
 <div class="container"><form action="" method="post">        
        <fieldset><legend>Home Page Settings</legend></fieldset>
		<div class="row-fluid">
		<!-- 	<div class="toggle-slide toggle-select active"><div style="width: 118px; margin-left: 0px; transition: margin-left 250ms ease-out 0s;" class="toggle-inner"><div style="height: 22px; width: 59px; text-align: center; line-height: 22px;" class="toggle-on active">ON</div><div style="height: 22px; width: 22px; margin-left: -11px; display: none;" class="toggle-blob"></div><div style="height: 22px; width: 59px; text-align: center; line-height: 22px;" class="toggle-off">OFF</div></div></div>
Slide THREE -->
        <!--<span>Check this option if you want to enable the News Block.</span><div class="slideThree">	
	<input type="checkbox" value="yes" id="slideThree" name="news" ' . $checkbox_index_news . ' />
	<label for="slideThree"></label></div>-->
    <!--<div class="example select">
        <h4>Select Type</h4>
        <div class="toggle toggle-select" data-type="select" style="width: 118px;">
            <div class="toggle-slide toggle-select">
                <div class="toggle-inner" style="width: 118px; margin-left: 0px; transition: margin-left 250ms ease-out 0s;">
                    <div class="toggle-on" style="height: 22px; width: 59px; text-align: center; line-height: 22px;">
                        ON
                    </div>
                    <div class="toggle-blob" style="height: 22px; width: 22px; margin-left: -11px; display: none;"></div>
                    <div class="toggle-off active" style="height: 22px; width: 59px; text-align: center; line-height: 22px;">
                        OFF
                    </div>
                </div>
            </div>
        </div>
        <code class="language-javascript"></code>
    </div> -->

        <div class="span3 offset1">
		<table class="table table-bordered">
		<tr>
        <td>
        <b>Enable IE alert?</b>
        <div class="slideThree"> <input type="checkbox" id="ie_alert" name="ie_alert" value="yes"' . $checkbox_index_ie_alert . ' />
        <label for="ie_alert"></label></div>
        <div><hr style="color:#A83838;" size="1" /></div>
        <span>Check this option if you want to enable the IE user alert.</span>
		</td>
		</tr>
		</table>
		</div>
        
		<div class="span3 offset0">
        <table class="table table-bordered">
		<tr>
        <td>
        <b>Enable News?</b>
        <!-- Slide THREE -->
        <div class="slideThree">	
	    <input type="checkbox" id="slideThree" value="yes" name="news"' . $checkbox_index_news . ' />
	    <label for="slideThree"></label></div>
        <div><hr style="color:#A83838;" size="1" /></div>
        <span>Check this option if you want to enable the News Block.</span>
		</td>
        </tr>
		</table>
		</div>

		<div class="span3 offset0">
        <table class="table table-bordered">
		<tr>
        <td>
        <b>Enable Shoutbox?</b>
        <div class="slideThree">
        <input type="checkbox" id="slideThree1" name="shoutbox" value="yes"' . $checkbox_index_shoutbox . ' />
        <label for="slideThree1"></label></div>
        <div><hr style="color:#A83838;" size="1" /></div>
        <span>Check this option if you want to enable the Shoutbox.</span>
		</td>
        </tr>
		</table>
		</div>
		</div>';
if ($CURUSER['class'] >= UC_STAFF) {
    $HTMLOUT.= '
		<div class="row-fluid">
		<div class="span3 offset1">
        <table class="table table-bordered">
		<tr>
        <td>
        <b>Enable Staff Shoutbox?</b>
		<div class="slideThree">
        <input type="checkbox" id="slideThree2" name="staff_shoutbox" value="yes"' . $checkbox_index_staffshoutbox . ' />
        <label for="slideThree2"></label></div>
        <div><hr style="color:#A83838;" size="1" /></div>
        <span>Check this option if you want to enable the Staff Shoutbox.</span>
		</td>
        </tr>
		</table>
		</div>';
}
$HTMLOUT.= '		
		<div class="span3 offset0">
        <table class="table table-bordered">
		<tr>
        <td>
        <b>Enable Active Users?</b>
		<div class="slideThree"> <input type="checkbox" id="active_users" name="active_users" value="yes"' . $checkbox_index_active_users . ' />
		<label for="active_users"></label></div>
        <div><hr style="color:#A83838;" size="1" /></div>
        <span>Check this option if you want to enable the Active Users.</span>
		</td>
        </tr>
		</table>
		</div>

		<div class="span3 offset0">
        <table class="table table-bordered">
		<tr>
        <td>
        <b>Enable Active Users Over 24hours?</b>
        <div class="slideThree"> <input type="checkbox" id="active_users2" name="last_24_active_users" value="yes"' . $checkbox_index_active_24h_users . ' />
		<label for="active_users2"></label></div>
        <div><hr style="color:#A83838;" size="1" /></div>
        <span>Check this option if you want to enable the Active Users visited over 24hours.</span>
		</td>
        </tr>
		</table>
		</div>
    
		<div class="row-fluid">
		<div class="span3 offset1">
        <table class="table table-bordered">
		<tr>
        <td>
        <b>Enable Active Irc Users?</b>
        <div class="slideThree"> <input type="checkbox" id="active_users3" name="irc_active_users" value="yes"' . $checkbox_index_active_irc_users . ' />
		<label for="active_users3"></label></div>
        <div><hr style="color:#A83838;" size="1" /></div>
        <span>Check this option if you want to enable the Active Irc Users.</span>
		</td>
        </tr>
		</table>
		</div>
      
		<div class="span3 offset0">
        <table class="table table-bordered">
		<tr>
        <td>
        <b>Enable Birthday Users?</b>
        <div class="slideThree"> <input type="checkbox" id="birthday_active_users" name="birthday_active_users" value="yes"' . $checkbox_index_active_birthday_users . ' />
		<label for="birthday_active_users"></label></div>
        <div><hr style="color:#A83838;" size="1" /></div>
        <span>Check this option if you want to enable the Active Birthday Users.</span>
		</td>
        </tr>
		</table>
		</div>
    
		<div class="span3 offset0">
        <table class="table table-bordered">
		<tr>
        <td>
        <b>Enable Site Stats?</b>
        <div class="slideThree"> <input type="checkbox" id="stats" name="stats" value="yes"' . $checkbox_index_stats . ' /><label for="stats"></label></div>
        <div><hr style="color:#A83838;" size="1" /></div>
        <span>Check this option if you want to enable the Stats.</span>
		</td>
        </tr>
		</table>
		</div>
		</div>
		<div class="row-fluid">
		<div class="span3 offset1">
        <table class="table table-bordered">
		<tr>
        <td>
        <b>Enable Disclaimer?</b>
        <div class="slideThree"> <input type="checkbox" id="disclaimer" name="disclaimer" value="yes"' . $checkbox_index_disclaimer . ' /><label for="disclaimer"></label></div>
		<div><hr style="color:#A83838;" size="1" /></div>
        <span>Check this option if you want to enable Disclaimer.</span>
		</td>
        </tr>
		</table>
		</div>
    
		<div class="span3 offset0">
        <table class="table table-bordered">
		<tr>
        <td>
        <b>Enable Latest User?</b>
		<div class="slideThree"> <input type="checkbox" id="latest_user" name="latest_user" value="yes"' . $checkbox_index_latest_user . ' /><label for="latest_user"></label></div>
		<div><hr style="color:#A83838;" size="1" /></div>
        <span>Check this option if you want to enable Latest User.</span>
  		</td>
        </tr>
		</table>
		</div>
    
		<div class="span3 offset0">
        <table class="table table-bordered">
		<tr>
        <td>
        <b>Enable Latest Forum Posts?</b>
		<div class="slideThree"> <input type="checkbox" id="forumposts" name="forumposts" value="yes"' . $checkbox_index_latest_forumposts . ' /><label for="forumposts"></label></div>
		<div><hr style="color:#A83838;" size="1" /></div>
        <span>Check this option if you want to enable latest Forum Posts.</span>
 		</td>
        </tr>
		</table>
		</div>
		</div>
		<div class="row-fluid">
		<div class="span3 offset1">
        <table class="table table-bordered">
		<tr>
        <td>
        <b>Enable Latest torrents?</b>
        <div class="slideThree"> <input type="checkbox" id="latest_torrents" name="latest_torrents" value="yes"' . $checkbox_index_latest_torrents . ' /><label for="latest_torrents"></label></div>
		<div><hr style="color:#A83838;" size="1" /></div>
        <span>Check this option if you want to enable latest torrents.</span>
  		</td>
        </tr>
		</table>
		</div>
    
		<div class="span3 offset0">
        <table class="table table-bordered">
		<tr>
        <td>
        <b>Enable Latest torrents scroll?</b>
        <div class="slideThree"> <input type="checkbox" id="latest_torrents_scroll" name="latest_torrents_scroll" value="yes"' . $checkbox_index_latest_torrents_scroll . ' /><label for="latest_torrents_scroll"></label></div>
		<div><hr style="color:#A83838;" size="1" /></div>
        <span>Check this option if you want to enable latest torrents marquee.</span>
 		</td>
        </tr>
		</table>
		</div>
    
		<div class="span3 offset0">
        <table class="table table-bordered">
		<tr>
        <td>
        <b>Enable Announcement?</b>
		<div class="slideThree"> <input type="checkbox" id="announcement" name="announcement" value="yes"' . $checkbox_index_announcement . ' /><label for="announcement"></label></div>
		<div><hr style="color:#A83838;" size="1" /></div>
        <span>Check this option if you want to enable the Announcement Block.</span>
  		</td>
        </tr>
		</table>
		</div>
		</div>
 		<div class="row-fluid">   
		<div class="span3 offset1">
        <table class="table table-bordered">
		<tr>
        <td>
        <b>Enable Donation Progress?</b>
        <div class="slideThree"> <input type="checkbox" id="donation_progress" name="donation_progress" value="yes"' . $checkbox_index_donation_progress . ' /><label for="donation_progress"></label></div>
 		<div><hr style="color:#A83838;" size="1" /></div>
        <span>Check this option if you want to enable the Donation Progress.</span>
 		</td>
        </tr>
		</table>
		</div>
    
		<div class="span3 offset0">
        <table class="table table-bordered">
		<tr>
        <td>
        <b>Enable Advertisements?</b>
        <div class="slideThree"> <input type="checkbox" id="advertisements" name="advertisements" value="yes"' . $checkbox_index_ads . ' /><label for="advertisements"></label></div>
		<div><hr style="color:#A83838;" size="1" /></div>
        <span>Check this option if you want to enable the Advertisements.</span>
  		</td>
        </tr>
		</table>
		</div>
    
		<div class="span3 offset0">
        <table class="table table-bordered">
		<tr>
        <td>
        <b>Enable Radio?</b>
        <div class="slideThree"> <input type="checkbox" id="radio" name="radio" value="yes"' . $checkbox_index_radio . ' /><label for="radio"></label></div>
 		<div><hr style="color:#A83838;" size="1" /></div>
        <span>Check this option if you want to enable the site radio.</span>
 		</td>
        </tr>
		</table>
		</div>
		</div>
 		<div class="row-fluid">    
		<div class="span3 offset1">
        <table class="table table-bordered">
		<tr>
        <td>
        <b>Enable Torrent Freak?</b>
		<div class="slideThree"> <input type="checkbox" id="torrentfreak" name="torrentfreak" value="yes"' . $checkbox_index_torrentfreak . ' /><label for="torrentfreak"></label></div>
		<div><hr style="color:#A83838;" size="1" /></div>
        <span>Check this option if you want to enable the torrent freak news.</span>
  		</td>
        </tr>
		</table>
		</div>
    
		<div class="span3 offset0">
        <table class="table table-bordered">
		<tr>
        <td>
        <b>Enable Xmas Gift?</b>
        <div class="slideThree"> <input type="checkbox" id="xmas_gift" name="xmas_gift" value="yes"' . $checkbox_index_xmasgift . ' /><label for="xmas_gift"></label></div>
		<div><hr style="color:#A83838;" size="1" /></div>
        <span>Check this option if you want to enable the Christmas Gift.</span>
  		</td>
        </tr>
		</table>
		</div>
    
		<div class="span3 offset0">
        <table class="table table-bordered">
		<tr>
        <td>
        <b>Enable Poll?</b>
        <div class="slideThree"> <input type="checkbox" id="active_poll" name="active_poll" value="yes"' . $checkbox_index_active_poll . ' /><label for="active_poll"></label></div>
		<div><hr style="color:#A83838;" size="1" /></div>
        <span>Check this option if you want to enable the Active Poll.</span>
 		</td>
        </tr>
		</table>
		</div>
		</div>
 		<div class="row-fluid">    
		<div class="span3 offset1">
        <table class="table table-bordered">
		<tr>
        <td>
        <b>Enable Movie of the week?</b>
        <div class="slideThree"> <input type="checkbox" id="index_movie_ofthe_week" name="movie_ofthe_week" value="yes"' . $checkbox_index_mow . ' /><label for="index_movie_ofthe_week"></label></div>
		<div><hr style="color:#A83838;" size="1" /></div>
        <span>Check this option if you want to enable the MOvie of the week.</span>
 		</td>
        </tr>
		</table>
		</div>
    </div>
		
 		 
	<div class="span7 offset1">
	<input class="btn btn-primary" type="submit" name="submit" value="Submit" tabindex="2" accesskey="s" /></div><br /><br />
    <div class="container">
    <fieldset><legend>Site Alert Settings</legend></fieldset>
		<div class="row-fluid">   
		<div class="span3 offset1">
        <table class="table table-bordered">
		<tr>
        <td>
        <b>Freeleech?</b>
        <div class="slideThree"> <input type="checkbox" id="stdhead_freeleech" name="stdhead_freeleech" value="yes"' . $checkbox_global_freeleech . ' /><label for="stdhead_freeleech"></label></div>
		<div><hr style="color:#A83838;" size="1" /></div>
        <span>Enable "freeleech mark" in stdhead</span>
        </td>
        </tr>
		</table>
		</div>
		<div class="span3 offset0">
        <table class="table table-bordered">
		<tr>
        <td>
        <b>Staff Reports?</b>
        <div class="slideThree"> <input type="checkbox" id="stdhead_reports" name="stdhead_reports" value="yes"' . $checkbox_global_staff_report . ' /><label for="stdhead_reports"></label></div>
		<div><hr style="color:#A83838;" size="1" /></div>
        <span>Enable reports alert in stdhead</span>
        </td>
        </tr>
		</table>
		</div>
    
        <div class="span3 offset0">
        <table class="table table-bordered">
		<tr>
        <td>
        <b>Upload App Alert?</b>
        <div class="slideThree"> <input type="checkbox" id="stdhead_uploadapp" name="stdhead_uploadapp" value="yes"' . $checkbox_global_staff_uploadapp . ' /><label for="stdhead_uploadapp"></label></div>
		<div><hr style="color:#A83838;" size="1" /></div>
        <span>Enable upload application alerts in stdhead</span>
        </td>
        </tr>
		</table>
		</div>
		</div>

';
if ($CURUSER['class'] >= UC_STAFF) {
    $HTMLOUT.= '		<div class="row-fluid">   
		<div class="span3 offset1">
        <table class="table table-bordered">
		<tr>
        <td>
        <b>Demotion</b>
        <div class="slideThree"> <input type="checkbox" id="stdhead_demotion" name="stdhead_demotion" value="yes"' . $checkbox_global_demotion . ' /><label for="stdhead_demotion"></label></div>
		<div><hr style="color:#A83838;" size="1" /></div>
        <span>Enable the global demotion alerts block in stdhead</span>
        </td>
        </tr>
		</table>
		</div>';
}
if ($CURUSER['class'] >= UC_STAFF) {
    $HTMLOUT.= '<div class="span3 offset0">
        <table class="table table-bordered">
		<tr>
        <td>
        <b>Staff Warning?</b>
        <div class="slideThree"> <input type="checkbox" id="stdhead_staff_message" name="stdhead_staff_message" value="yes"' . $checkbox_global_staff_message_alert . ' /><label for="stdhead_staff_message"></label></div>
		<div><hr style="color:#A83838;" size="1" /></div>
        <span>Shows if there is a new message for staff alert in stdhead </span>
        </td>
        </tr>
		</table>
		</div>
		';
}
if ($CURUSER['class'] >= UC_STAFF) {
    $HTMLOUT.= '<div class="span3 offset0">
        <table class="table table-bordered">
		<tr>
        <td>
        <b>Bug Alert Message?</b>
        <div class="slideThree"> <input type="checkbox" id="stdhead_bugmessage" name="stdhead_bugmessage" value="yes"' . $checkbox_global_bugmessage . ' /><label for="stdhead_bugmessage"></label></div>
		<div><hr style="color:#A83838;" size="1" /></div>
        <span>Enable Bug Message alerts in stdhead</span>
        </td>
        </tr>
		</table>
		</div>
		</div>';
}
$HTMLOUT.= '
<div class="row-fluid">
<div class="span3 offset1">
        <table class="table table-bordered">
		<tr>
        <td>
        <b>Message block?</b>
        <div class="slideThree"> <input type="checkbox" id="stdhead_newpm" name="stdhead_newpm" value="yes"' . $checkbox_global_message_alert . ' /><label for="stdhead_newpm"></label></div>
 		<div><hr style="color:#A83838;" size="1" /></div>
        <span>Enable message alerts in stdhead</span>
       </td>
        </tr>
		</table>
		</div>

		<div class="span3 offset0">
        <table class="table table-bordered">
		<tr>
        <td>
        <b>Happyhour?</b>
        <div class="slideThree"> <input type="checkbox" id="stdhead_happyhour" name="stdhead_happyhour" value="yes"' . $checkbox_global_happyhour . ' /><label for="stdhead_happyhour"></label></div>
		<div><hr style="color:#A83838;" size="1" /></div>
        <span>Enable happy hour alerts in stdhead</span>
        </td>
        </tr>
		</table>
		</div>
    
        <div class="span3 offset0">
        <table class="table table-bordered">
		<tr>
        <td>
        <b>CrazyHour?</b>
        <div class="slideThree"> <input type="checkbox" id="stdhead_crazyhour" name="stdhead_crazyhour" value="yes"' . $checkbox_global_crazyhour . ' /><label for="stdhead_crazyhour"></label></div>
		<div><hr style="color:#A83838;" size="1" /></div>
        <span>Enable crazyhour alerts in stdhead</span>
        </td>
        </tr>
		</table>
		</div>

        <div class="row-fluid">
        <div class="span3 offset1">
        <table class="table table-bordered">
		<tr>
        <td>
        <b>Karma Contributions</b>
        <div class="slideThree"> <input type="checkbox" id="stdhead_freeleech_contribution" name="stdhead_freeleech_contribution" value="yes"' . $checkbox_global_freeleech_contribution . ' /><label for="stdhead_freeleech_contribution"></label></div>
		<div><hr style="color:#A83838;" size="1" /></div>
        <span>Enable karma contribution status alert in stdhead</span>
        </td>
        </tr>
		</table>
		</div>
		</div>
        
        
        </div>
        ';

        $HTMLOUT.= '  
        <div class="span7 offset1">
        <input class="btn btn-primary" type="submit" name="submit" value="Submit" tabindex="2" accesskey="s" /></div><br /><br />     
		<div class="container">
        <fieldset><legend>Userdetails Settings</legend></fieldset>
		<div class="row-fluid">
        <div class="span3 offset1">
        <table class="table table-bordered">
		<tr>
        <td>
        <b>Login link?</b>
        <div class="slideThree"> <input type="checkbox" id="userdetails_login_link" name="userdetails_login_link" value="yes"' . $checkbox_userdetails_login_link . ' /><label for="userdetails_login_link"></label></div>
		<div><hr style="color:#A83838;" size="1" /></div>
        <span>Enable quick login link</span>
        </td>
        </tr>
		</table>
		</div>
        
        <div class="span3 offset0">
        <table class="table table-bordered">
		<tr>
        <td>
        <b>Flush torrents?</b>
        <div class="slideThree"> <input type="checkbox" id="userdetails_flush" name="userdetails_flush" value="yes"' . $checkbox_userdetails_flush . ' /><label for="userdetails_flush"></label></div>
        <div><hr style="color:#A83838;" size="1" /></div>
        <span>Enable flush torrents</span>
        </td>
        </tr>
		</table>
		</div>
        
        <div class="span3 offset0">
        <table class="table table-bordered">
		<tr>
        <td>
        <b>Join date?</b>
        <div class="slideThree"> <input type="checkbox" id="userdetails_joined" name="userdetails_joined" value="yes"' . $checkbox_userdetails_joined . ' /><label for="userdetails_joined"></label></div>
        <div><hr style="color:#A83838;" size="1" /></div>
        <span>Enable join date</span>
        </td>
        </tr>
		</table>
		</div>
        </div>
		<div class="row-fluid">
        <div class="span3 offset1">
        <table class="table table-bordered">
		<tr>
        <td>
        <b>Online time?</b>
        <div class="slideThree"> <input type="checkbox" id="userdetails_online_time" name="userdetails_online_time" value="yes"' . $checkbox_userdetails_onlinetime . ' /><label for="userdetails_online_time"></label></div>
		<div><hr style="color:#A83838;" size="1" /></div>
        <span>Enable online time</span>
        </td>
        </tr>
		</table>
		</div>
        
        <div class="span3 offset0">
        <table class="table table-bordered">
		<tr>
        <td>
        <b>Browser?</b>
        <div class="slideThree"> <input type="checkbox" id="userdetails_browser" name="userdetails_browser" value="yes"' . $checkbox_userdetails_browser . ' /><label for="userdetails_browser"></label></div>
        <div><hr style="color:#A83838;" size="1" /></div>
        <span>Enable browser and os detection</span>
        </td>
        </tr>
		</table>
		</div>
        
        <div class="span3 offset0">
        <table class="table table-bordered">
		<tr>
        <td>
        <b>Reputation?</b>
        <div class="slideThree"> <input type="checkbox" id="userdetails_reputation" name="userdetails_reputation" value="yes"' . $checkbox_userdetails_reputation . ' /><label for="userdetails_reputation"></label></div>
		<div><hr style="color:#A83838;" size="1" /></div>
        <span>Enable add reputation link</span>
        </td>
        </tr>
		</table>
		</div>
		</div>
		<div class="row-fluid">
        <div class="span3 offset1">
        <table class="table table-bordered">
		<tr>
        <td>
        <b>Profile hits?</b>
        <div class="slideThree"> <input type="checkbox" id="userdetails_user_hits" name="userdetails_user_hits" value="yes"' . $checkbox_userdetails_userhits . ' /><label for="userdetails_user_hits"></label></div>
		<div><hr style="color:#A83838;" size="1" /></div>
        <span>Enable user hits</span>
        </td>
        </tr>
		</table>
		</div>
        
         <div class="span3 offset0">
        <table class="table table-bordered">
		<tr>
        <td>
        <b>Birthday?</b>
        <div class="slideThree"> <input type="checkbox" id="userdetails_birthday" name="userdetails_birthday" value="yes"' . $checkbox_userdetails_birthday . ' /><label for="userdetails_birthday"></label></div>
		<div><hr style="color:#A83838;" size="1" /></div>
        <span>Enable birthdate and age</span>
        </td>
        </tr>
		</table>
		</div>
        
        <div class="span3 offset0">
        <table class="table table-bordered">
		<tr>
        <td>
        <b>Contact?</b>
        <div class="slideThree"> <input type="checkbox" id="userdetails_contact_info" name="userdetails_contact_info" value="yes"' . $checkbox_userdetails_contact_info . ' /><label for="userdetails_contact_info"></label></div>
		<div><hr style="color:#A83838;" size="1" /></div>
        <span>Enable contact infos</span>
       </td>
        </tr>
		</table>
		</div>
		</div>
        <div class="row-fluid"> 
        <div class="span3 offset1">
        <table class="table table-bordered">
		<tr>
        <td>
        <b>IP history?</b>
        <div class="slideThree"> <input type="checkbox" id="userdetails_iphistory" name="userdetails_iphistory" value="yes"' . $checkbox_userdetails_iphistory . ' /><label for="userdetails_iphistory"></label></div>
		<div><hr style="color:#A83838;" size="1" /></div>
        <span>Enable ip history lists</span>
        </td>
        </tr>
		</table>
		</div>
        
        <div class="span3 offset0">
        <table class="table table-bordered">
		<tr>
        <td>
        <b>User traffic?</b>
        <div class="slideThree"> <input type="checkbox" id="userdetails_traffic" name="userdetails_traffic" value="yes"' . $checkbox_userdetails_traffic . ' /><label for="userdetails_traffic"></label></div>
		<div><hr style="color:#A83838;" size="1" /></div>
        <span>Enable uploaded and download</span>
        </td>
        </tr>
		</table>
		</div>
        
        <div class="span3 offset0">
        <table class="table table-bordered">
		<tr>
        <td>
        <b>Share ratio?</b>
        <div class="slideThree"> <input type="checkbox" id="userdetails_share_ratio" name="userdetails_share_ratio" value="yes"' . $checkbox_userdetails_shareratio . ' /><label for="userdetails_share_ratio"></label></div>
		<div><hr style="color:#A83838;" size="1" /></div>
        <span>Enable share ratio</span>
        </td>
        </tr>
		</table>
		</div>
		</div>
        <div class="row-fluid">
        <div class="span3 offset1">
        <table class="table table-bordered">
		<tr>
        <td>
        <b>Seed time ratio?</b>
        <div class="slideThree"> <input type="checkbox" id="userdetails_seedtime_ratio" name="userdetails_seedtime_ratio" value="yes"' . $checkbox_userdetails_seedtime_ratio . ' /><label for="userdetails_seedtime_ratio"></label></div>
		<div><hr style="color:#A83838;" size="1" /></div>
        <span>Enable seed time per torrent average ratio</span>
        </td>
        </tr>
		</table>
		</div>
        
        <div class="span3 offset0">
        <table class="table table-bordered">
		<tr>
        <td>
        <b>Seedbonus?</b>
        <div class="slideThree"> <input type="checkbox" id="userdetails_seedbonus" name="userdetails_seedbonus" value="yes"' . $checkbox_userdetails_seedbonus . ' /><label for="userdetails_seedbonus"></label></div>
		<div><hr style="color:#A83838;" size="1" /></div>
        <span>Enable seed bonus</span>
         </td>
        </tr>
		</table>
		</div>
       
        <div class="span3 offset0">
        <table class="table table-bordered">
		<tr>
        <td>
        <b>IRC stats?</b>
        <div class="slideThree"> <input type="checkbox" id="userdetails_irc_stats" name="userdetails_irc_stats" value="yes"' . $checkbox_userdetails_irc_stats . ' /><label for="userdetails_irc_stats"></label></div>
		<div><hr style="color:#A83838;" size="1" /></div>
        <span>Enable irc online stats</span>
        </td>
        </tr>
		</table>
		</div>
		</div>
        <div class="row-fluid">
        <div class="span3 offset1">
        <table class="table table-bordered">
		<tr>
        <td>
        <b>Connectable?</b>
        <div class="slideThree"> <input type="checkbox" id="userdetails_connectable_port" name="userdetails_connectable_port" value="yes"' . $checkbox_userdetails_connectable . ' /><label for="userdetails_connectable_port"></label></div>
		<div><hr style="color:#A83838;" size="1" /></div>
        <span>Enable connectable and port</span>
        </td>
        </tr>
		</table>
		</div>
        
        <div class="span3 offset0">
        <table class="table table-bordered">
		<tr>
        <td>
         <b>Avatar?</b>
        <div class="slideThree"> <input type="checkbox" id="userdetails_avatar" name="userdetails_avatar" value="yes"' . $checkbox_userdetails_avatar . ' /><label for="userdetails_avatar"></label></div>
		 <div><hr style="color:#A83838;" size="1" /></div>
        <span>Enable avatar</span>
        </td>
        </tr>
		</table>
		</div>
        
        <div class="span3 offset0">
        <table class="table table-bordered">
		<tr>
        <td>
         <b>Userclass?</b>
        <div class="slideThree"> <input type="checkbox" id="userdetails_userclass" name="userdetails_userclass" value="yes"' . $checkbox_userdetails_userclass . ' /><label for="userdetails_userclass"></label></div>
	     <div><hr style="color:#A83838;" size="1" /></div>
        <span>Enable userclass</span>
        </td>
        </tr>
		</table>
		</div>
		</div>
        <div class="row-fluid">
        <div class="span3 offset1">
        <table class="table table-bordered">
		<tr>
        <td>
        <b>Gender?</b>
        <div class="slideThree"> <input type="checkbox" id="userdetails_gender" name="userdetails_gender" value="yes"' . $checkbox_userdetails_gender . ' /><label for="userdetails_gender"></label></div>
		<div><hr style="color:#A83838;" size="1" /></div>
        <span>Enable gender</span>
        </td>
        </tr>
		</table>
		</div>
        
        <div class="span3 offset0">
        <table class="table table-bordered">
		<tr>
        <td>
        <b>Free stuffs?</b>
        <div class="slideThree"> <input type="checkbox" id="userdetails_freestuffs" name="userdetails_freestuffs" value="yes"' . $checkbox_userdetails_freestuffs . ' /><label for="userdetails_freestuffs"></label></div>
		<div><hr style="color:#A83838;" size="1" /></div>
        <span>Enable freeslots and freeleech status</span>
        </td>
        </tr>
		</table>
		</div>
        
        <div class="span3 offset0">
        <table class="table table-bordered">
		<tr>
        <td>
        <b>Comments?</b>
        <div class="slideThree"> <input type="checkbox" id="userdetails_comments" name="userdetails_comments" value="yes"' . $checkbox_userdetails_torrent_comments . ' /><label for="userdetails_comments"></label></div>
		<div><hr style="color:#A83838;" size="1" /></div>
        <span>Enable torrent comments history</span>
        </td>
        </tr>
		</table>
		</div>
		</div>
        <div class="row-fluid">
        <div class="span3 offset1">
        <table class="table table-bordered">
		<tr>
        <td>
        <b>Forumposts?</b>
        <div class="slideThree"> <input type="checkbox" id="userdetails_forumposts" name="userdetails_forumposts" value="yes"' . $checkbox_userdetails_forumposts . ' /><label for="userdetails_forumposts"></label></div>
		<div><hr style="color:#A83838;" size="1" /></div>
        <span>Enable forum posts history</span>
        </td>
        </tr>
		</table>
		</div>
        
        <div class="span3 offset0">
        <table class="table table-bordered">
		<tr>
        <td>
        <b>Invited by?</b>
        <div class="slideThree"> <input type="checkbox" id="userdetails_invitedby" name="userdetails_invitedby" value="yes"' . $checkbox_userdetails_invitedby . ' /><label for="userdetails_invitedby"></label></div>
		<div><hr style="color:#A83838;" size="1" /></div>
        <span>Enable invited by list</span>
        </td>
        </tr>
		</table>
		</div>
        
        <div class="span3 offset0">
        <table class="table table-bordered">
		<tr>
        <td>
        <b>Torrents blocks?</b>
        <div class="slideThree"> <input type="checkbox" id="userdetails_torrents_block" name="userdetails_torrents_block" value="yes"' . $checkbox_userdetails_torrents_block . ' /><label for="userdetails_torrents_block"></label></div>
		<div><hr style="color:#A83838;" size="1" /></div>
        <span>Enable seeding, leeching, snatched and uploaded torrents</span>
                </td>
        </tr>
		</table>
		</div>
		</div>
		</div>
		<div class="row-fluid">
		<div class="span3 offset1">
        <table class="table table-bordered">
		<tr>
        <td>
        <b>Staff snatched?</b>
        <div class="slideThree"> <input type="checkbox" id="userdetails_snatched_staff" name="userdetails_snatched_staff" value="yes"' . $checkbox_userdetails_snatched_staff . ' /><label for="userdetails_snatched_staff"></label></div>
		<div><hr style="color:#A83838;" size="1" /></div>
        <span>Enable staff snatchlist</span>
        </td>
        </tr>
		</table>
		</div>
        
        <div class="span3 offset0">
        <table class="table table-bordered">
		<tr>
        <td>
        <b>User info?</b>
        <div class="slideThree"> <input type="checkbox" id="userdetails_userinfo" name="userdetails_userinfo" value="yes"' . $checkbox_userdetails_userinfo . ' /><label for="userdetails_userinfo"></label></div>
		<div><hr style="color:#A83838;" size="1" /></div>
        <span>Enable user info</span>
        </td>
        </tr>
		</table>
		</div>
        
        <div class="span3 offset0">
        <table class="table table-bordered">
		<tr>
        <td>
        <b>Show pm?</b>
        <div class="slideThree"> <input type="checkbox" id="userdetails_showpm" name="userdetails_showpm" value="yes"' . $checkbox_userdetails_showpm . ' /><label for="userdetails_showpm"></label></div>
		<div><hr style="color:#A83838;" size="1" /></div>
        <span>Enable send message button</span>
        </td>
        </tr>
		</table>
		</div>
		</div>
        <div class="row-fluid">
        <div class="span3 offset1">
        <table class="table table-bordered">
		<tr>
        <td>
        <b>Report user?</b>
        <div class="slideThree"> <input type="checkbox" id="userdetails_report_user" name="userdetails_report_user" value="yes"' . $checkbox_userdetails_report . ' /><label for="userdetails_report_user"></label></div>
		<div><hr style="color:#A83838;" size="1" /></div>
        <span>Enable report users button</span>
        </td>
        </tr>
		</table>
		</div>
        
        <div class="span3 offset0">
        <table class="table table-bordered">
		<tr>
        <td>
        <b>User status?</b>
        <div class="slideThree"> <input type="checkbox" id="userdetails_user_status" name="userdetails_user_status" value="yes"' . $checkbox_userdetails_userstatus . ' /><label for="userdetails_user_status"></label></div>
		<div><hr style="color:#A83838;" size="1" /></div>
        <span>Enable user status</span>
        </td>
        </tr>
		</table>
		</div>
        
        <div class="span3 offset0">
        <table class="table table-bordered">
		<tr>
        <td>
        <b>User comments?</b>
        <div class="slideThree"> <input type="checkbox" id="userdetails_user_comments" name="userdetails_user_comments" value="yes"' . $checkbox_userdetails_usercomments . ' /><label for="userdetails_user_comments"></label></div>
		<div><hr style="color:#A83838;" size="1" /></div>
        <span>Enable user comments</span>
                </td>
        </tr>
		</table>
		</div>
		</div>
		';
if ($CURUSER['class'] >= UC_STAFF) {
    $HTMLOUT.= '
	<div class="row-fluid">
        <div class="span3 offset1">
        <table class="table table-bordered">
		<tr>
        <td>
        <b>Completed?</b>
        <div class="slideThree"> <input type="checkbox" id="userdetails_completed" name="userdetails_completed" value="yes"' . $checkbox_userdetails_completed . ' /><label for="userdetails_completed"></label></div>
		<div><hr style="color:#A83838;" size="1" /></div>
        <span>Enable completed torrents</span>
               </td>
        </tr>
		</table>
		</div>
		</div>
';
}
$HTMLOUT.= '<div class="span7 offset1">
<input class="btn btn-primary" type="submit" name="submit" value="Submit" tabindex="2" accesskey="s" /></div></div></div></form></div>';
echo stdhead("User Blocks Config", true, $stdhead) . $HTMLOUT . stdfoot($stdfoot);
?>
