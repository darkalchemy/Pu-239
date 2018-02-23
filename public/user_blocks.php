<?php

require_once dirname(__FILE__, 2).DIRECTORY_SEPARATOR.'include'.DIRECTORY_SEPARATOR.'bittorrent.php';
require_once INCL_DIR.'html_functions.php';
require_once INCL_DIR.'user_functions.php';
check_user_status();
global $CURUSER, $site_config, $cache, $session;

$lang = load_language('global');
$id = (isset($_GET['id']) ? $_GET['id'] : $CURUSER['id']);
if (!is_valid_id($id) || $CURUSER['class'] < UC_STAFF) {
    $id = $CURUSER['id'];
}
if ($CURUSER['class'] < UC_STAFF && 'no' == $CURUSER['got_blocks']) {
    $session->set('is-danger', 'Go to your Karma bonus page and buy this unlock before trying to access it.');
    header('Location: '.$site_config['baseurl'].'/index.php');
    die();
}

if ('POST' == $_SERVER['REQUEST_METHOD']) {
    $updateset = [];
    $setbits_index_page = $clrbits_index_page = $setbits_global_stdhead = $clrbits_global_stdhead = $setbits_userdetails_page = $clrbits_userdetails_page = 0;
    //==Index
    if (isset($_POST['ie_alert'])) {
        $setbits_index_page |= block_index::IE_ALERT;
    } else {
        $clrbits_index_page |= block_index::IE_ALERT;
    }
    if (isset($_POST['news'])) {
        $setbits_index_page |= block_index::NEWS;
    } else {
        $clrbits_index_page |= block_index::NEWS;
    }
    if (isset($_POST['ajaxchat'])) {
        $setbits_index_page |= block_index::AJAXCHAT;
    } else {
        $clrbits_index_page |= block_index::AJAXCHAT;
    }
    if (isset($_POST['trivia'])) {
        $setbits_index_page |= block_index::TRIVIA;
    } else {
        $clrbits_index_page |= block_index::TRIVIA;
    }
    if (isset($_POST['active_users'])) {
        $setbits_index_page |= block_index::ACTIVE_USERS;
    } else {
        $clrbits_index_page |= block_index::ACTIVE_USERS;
    }
    if (isset($_POST['last_24_active_users'])) {
        $setbits_index_page |= block_index::LAST_24_ACTIVE_USERS;
    } else {
        $clrbits_index_page |= block_index::LAST_24_ACTIVE_USERS;
    }
    if (isset($_POST['irc_active_users'])) {
        $setbits_index_page |= block_index::IRC_ACTIVE_USERS;
    } else {
        $clrbits_index_page |= block_index::IRC_ACTIVE_USERS;
    }
    if (isset($_POST['birthday_active_users'])) {
        $setbits_index_page |= block_index::BIRTHDAY_ACTIVE_USERS;
    } else {
        $clrbits_index_page |= block_index::BIRTHDAY_ACTIVE_USERS;
    }
    if (isset($_POST['stats'])) {
        $setbits_index_page |= block_index::STATS;
    } else {
        $clrbits_index_page |= block_index::STATS;
    }
    if (isset($_POST['disclaimer'])) {
        $setbits_index_page |= block_index::DISCLAIMER;
    } else {
        $clrbits_index_page |= block_index::DISCLAIMER;
    }
    if (isset($_POST['latest_user'])) {
        $setbits_index_page |= block_index::LATEST_USER;
    } else {
        $clrbits_index_page |= block_index::LATEST_USER;
    }
    if (isset($_POST['latestcomments'])) {
        $setbits_index_page |= block_index::LATESTCOMMENTS;
    } else {
        $clrbits_index_page |= block_index::LATESTCOMMENTS;
    }

    if (isset($_POST['forumposts'])) {
        $setbits_index_page |= block_index::FORUMPOSTS;
    } else {
        $clrbits_index_page |= block_index::FORUMPOSTS;
    }
    if (isset($_POST['latest_torrents'])) {
        $setbits_index_page |= block_index::LATEST_TORRENTS;
    } else {
        $clrbits_index_page |= block_index::LATEST_TORRENTS;
    }
    if (isset($_POST['latest_torrents_scroll'])) {
        $setbits_index_page |= block_index::LATEST_TORRENTS_SCROLL;
    } else {
        $clrbits_index_page |= block_index::LATEST_TORRENTS_SCROLL;
    }
    if (isset($_POST['announcement'])) {
        $setbits_index_page |= block_index::ANNOUNCEMENT;
    } else {
        $clrbits_index_page |= block_index::ANNOUNCEMENT;
    }
    if (isset($_POST['donation_progress'])) {
        $setbits_index_page |= block_index::DONATION_PROGRESS;
    } else {
        $clrbits_index_page |= block_index::DONATION_PROGRESS;
    }
    if (isset($_POST['advertisements'])) {
        $setbits_index_page |= block_index::ADVERTISEMENTS;
    } else {
        $clrbits_index_page |= block_index::ADVERTISEMENTS;
    }
    if (isset($_POST['radio'])) {
        $setbits_index_page |= block_index::RADIO;
    } else {
        $clrbits_index_page |= block_index::RADIO;
    }
    if (isset($_POST['torrentfreak'])) {
        $setbits_index_page |= block_index::TORRENTFREAK;
    } else {
        $clrbits_index_page |= block_index::TORRENTFREAK;
    }
    if (isset($_POST['christmas_gift'])) {
        $setbits_index_page |= block_index::CHRISTMAS_GIFT;
    } else {
        $clrbits_index_page |= block_index::CHRISTMAS_GIFT;
    }
    if (isset($_POST['active_poll'])) {
        $setbits_index_page |= block_index::ACTIVE_POLL;
    } else {
        $clrbits_index_page |= block_index::ACTIVE_POLL;
    }
    if (isset($_POST['movie_ofthe_week'])) {
        $setbits_index_page |= block_index::MOVIEOFWEEK;
    } else {
        $clrbits_index_page |= block_index::MOVIEOFWEEK;
    }
    //==Stdhead
    if (isset($_POST['stdhead_freeleech'])) {
        $setbits_global_stdhead |= block_stdhead::STDHEAD_FREELEECH;
    } else {
        $clrbits_global_stdhead |= block_stdhead::STDHEAD_FREELEECH;
    }
    if (isset($_POST['stdhead_demotion'])) {
        $setbits_global_stdhead |= block_stdhead::STDHEAD_DEMOTION;
    } else {
        $clrbits_global_stdhead |= block_stdhead::STDHEAD_DEMOTION;
    }
    if (isset($_POST['stdhead_newpm'])) {
        $setbits_global_stdhead |= block_stdhead::STDHEAD_NEWPM;
    } else {
        $clrbits_global_stdhead |= block_stdhead::STDHEAD_NEWPM;
    }
    if (isset($_POST['stdhead_staff_message'])) {
        $setbits_global_stdhead |= block_stdhead::STDHEAD_STAFF_MESSAGE;
    } else {
        $clrbits_global_stdhead |= block_stdhead::STDHEAD_STAFF_MESSAGE;
    }
    if (isset($_POST['stdhead_reports'])) {
        $setbits_global_stdhead |= block_stdhead::STDHEAD_REPORTS;
    } else {
        $clrbits_global_stdhead |= block_stdhead::STDHEAD_REPORTS;
    }
    if (isset($_POST['stdhead_uploadapp'])) {
        $setbits_global_stdhead |= block_stdhead::STDHEAD_UPLOADAPP;
    } else {
        $clrbits_global_stdhead |= block_stdhead::STDHEAD_UPLOADAPP;
    }
    if (isset($_POST['stdhead_happyhour'])) {
        $setbits_global_stdhead |= block_stdhead::STDHEAD_HAPPYHOUR;
    } else {
        $clrbits_global_stdhead |= block_stdhead::STDHEAD_HAPPYHOUR;
    }
    if (isset($_POST['stdhead_crazyhour'])) {
        $setbits_global_stdhead |= block_stdhead::STDHEAD_CRAZYHOUR;
    } else {
        $clrbits_global_stdhead |= block_stdhead::STDHEAD_CRAZYHOUR;
    }
    if (isset($_POST['stdhead_bugmessage'])) {
        $setbits_global_stdhead |= block_stdhead::STDHEAD_BUG_MESSAGE;
    } else {
        $clrbits_global_stdhead |= block_stdhead::STDHEAD_BUG_MESSAGE;
    }
    if (isset($_POST['stdhead_freeleech_contribution'])) {
        $setbits_global_stdhead |= block_stdhead::STDHEAD_FREELEECH_CONTRIBUTION;
    } else {
        $clrbits_global_stdhead |= block_stdhead::STDHEAD_FREELEECH_CONTRIBUTION;
    }
    //==Userdetails
    if (isset($_POST['userdetails_flush'])) {
        $setbits_userdetails_page |= block_userdetails::FLUSH;
    } else {
        $clrbits_userdetails_page |= block_userdetails::FLUSH;
    }
    if (isset($_POST['userdetails_joined'])) {
        $setbits_userdetails_page |= block_userdetails::JOINED;
    } else {
        $clrbits_userdetails_page |= block_userdetails::JOINED;
    }
    if (isset($_POST['userdetails_online_time'])) {
        $setbits_userdetails_page |= block_userdetails::ONLINETIME;
    } else {
        $clrbits_userdetails_page |= block_userdetails::ONLINETIME;
    }
    if (isset($_POST['userdetails_browser'])) {
        $setbits_userdetails_page |= block_userdetails::BROWSER;
    } else {
        $clrbits_userdetails_page |= block_userdetails::BROWSER;
    }
    if (isset($_POST['userdetails_reputation'])) {
        $setbits_userdetails_page |= block_userdetails::REPUTATION;
    } else {
        $clrbits_userdetails_page |= block_userdetails::REPUTATION;
    }
    if (isset($_POST['userdetails_user_hits'])) {
        $setbits_userdetails_page |= block_userdetails::PROFILE_HITS;
    } else {
        $clrbits_userdetails_page |= block_userdetails::PROFILE_HITS;
    }
    if (isset($_POST['userdetails_birthday'])) {
        $setbits_userdetails_page |= block_userdetails::BIRTHDAY;
    } else {
        $clrbits_userdetails_page |= block_userdetails::BIRTHDAY;
    }
    if (isset($_POST['userdetails_birthday'])) {
        $setbits_userdetails_page |= block_userdetails::BIRTHDAY;
    } else {
        $clrbits_userdetails_page |= block_userdetails::BIRTHDAY;
    }
    if (isset($_POST['userdetails_contact_info'])) {
        $setbits_userdetails_page |= block_userdetails::CONTACT_INFO;
    } else {
        $clrbits_userdetails_page |= block_userdetails::CONTACT_INFO;
    }
    if (isset($_POST['userdetails_iphistory'])) {
        $setbits_userdetails_page |= block_userdetails::IPHISTORY;
    } else {
        $clrbits_userdetails_page |= block_userdetails::IPHISTORY;
    }
    if (isset($_POST['userdetails_traffic'])) {
        $setbits_userdetails_page |= block_userdetails::TRAFFIC;
    } else {
        $clrbits_userdetails_page |= block_userdetails::TRAFFIC;
    }
    if (isset($_POST['userdetails_share_ratio'])) {
        $setbits_userdetails_page |= block_userdetails::SHARE_RATIO;
    } else {
        $clrbits_userdetails_page |= block_userdetails::SHARE_RATIO;
    }
    if (isset($_POST['userdetails_seedtime_ratio'])) {
        $setbits_userdetails_page |= block_userdetails::SEEDTIME_RATIO;
    } else {
        $clrbits_userdetails_page |= block_userdetails::SEEDTIME_RATIO;
    }
    if (isset($_POST['userdetails_seedbonus'])) {
        $setbits_userdetails_page |= block_userdetails::SEEDBONUS;
    } else {
        $clrbits_userdetails_page |= block_userdetails::SEEDBONUS;
    }
    if (isset($_POST['userdetails_irc_stats'])) {
        $setbits_userdetails_page |= block_userdetails::IRC_STATS;
    } else {
        $clrbits_userdetails_page |= block_userdetails::IRC_STATS;
    }
    if (isset($_POST['userdetails_connectable_port'])) {
        $setbits_userdetails_page |= block_userdetails::CONNECTABLE_PORT;
    } else {
        $clrbits_userdetails_page |= block_userdetails::CONNECTABLE_PORT;
    }
    if (isset($_POST['userdetails_avatar'])) {
        $setbits_userdetails_page |= block_userdetails::AVATAR;
    } else {
        $clrbits_userdetails_page |= block_userdetails::AVATAR;
    }
    if (isset($_POST['userdetails_userclass'])) {
        $setbits_userdetails_page |= block_userdetails::USERCLASS;
    } else {
        $clrbits_userdetails_page |= block_userdetails::USERCLASS;
    }
    if (isset($_POST['userdetails_gender'])) {
        $setbits_userdetails_page |= block_userdetails::GENDER;
    } else {
        $clrbits_userdetails_page |= block_userdetails::GENDER;
    }
    if (isset($_POST['userdetails_freestuffs'])) {
        $setbits_userdetails_page |= block_userdetails::FREESTUFFS;
    } else {
        $clrbits_userdetails_page |= block_userdetails::FREESTUFFS;
    }
    if (isset($_POST['userdetails_comments'])) {
        $setbits_userdetails_page |= block_userdetails::COMMENTS;
    } else {
        $clrbits_userdetails_page |= block_userdetails::COMMENTS;
    }
    if (isset($_POST['userdetails_forumposts'])) {
        $setbits_userdetails_page |= block_userdetails::FORUMPOSTS;
    } else {
        $clrbits_userdetails_page |= block_userdetails::FORUMPOSTS;
    }
    if (isset($_POST['userdetails_invitedby'])) {
        $setbits_userdetails_page |= block_userdetails::INVITEDBY;
    } else {
        $clrbits_userdetails_page |= block_userdetails::INVITEDBY;
    }
    if (isset($_POST['userdetails_torrents_block'])) {
        $setbits_userdetails_page |= block_userdetails::TORRENTS_BLOCK;
    } else {
        $clrbits_userdetails_page |= block_userdetails::TORRENTS_BLOCK;
    }
    if (isset($_POST['userdetails_completed'])) {
        $setbits_userdetails_page |= block_userdetails::COMPLETED;
    } else {
        $clrbits_userdetails_page |= block_userdetails::COMPLETED;
    }
    if (isset($_POST['userdetails_snatched_staff'])) {
        $setbits_userdetails_page |= block_userdetails::SNATCHED_STAFF;
    } else {
        $clrbits_userdetails_page |= block_userdetails::SNATCHED_STAFF;
    }
    if (isset($_POST['userdetails_userinfo'])) {
        $setbits_userdetails_page |= block_userdetails::USERINFO;
    } else {
        $clrbits_userdetails_page |= block_userdetails::USERINFO;
    }
    if (isset($_POST['userdetails_showpm'])) {
        $setbits_userdetails_page |= block_userdetails::SHOWPM;
    } else {
        $clrbits_userdetails_page |= block_userdetails::SHOWPM;
    }
    if (isset($_POST['userdetails_report_user'])) {
        $setbits_userdetails_page |= block_userdetails::REPORT_USER;
    } else {
        $clrbits_userdetails_page |= block_userdetails::REPORT_USER;
    }
    if (isset($_POST['userdetails_user_status'])) {
        $setbits_userdetails_page |= block_userdetails::USERSTATUS;
    } else {
        $clrbits_userdetails_page |= block_userdetails::USERSTATUS;
    }
    if (isset($_POST['userdetails_user_comments'])) {
        $setbits_userdetails_page |= block_userdetails::USERCOMMENTS;
    } else {
        $clrbits_userdetails_page |= block_userdetails::USERCOMMENTS;
    }
    if (isset($_POST['userdetails_show_friends'])) {
        $setbits_userdetails_page |= block_userdetails::SHOWFRIENDS;
    } else {
        $clrbits_userdetails_page |= block_userdetails::SHOWFRIENDS;
    }
    //== set n clear
    if ($setbits_index_page) {
        $updateset[] = 'index_page = (index_page | '.$setbits_index_page.')';
    }
    if ($clrbits_index_page) {
        $updateset[] = 'index_page = (index_page & ~'.$clrbits_index_page.')';
    }
    if ($setbits_global_stdhead) {
        $updateset[] = 'global_stdhead = (global_stdhead | '.$setbits_global_stdhead.')';
    }
    if ($clrbits_global_stdhead) {
        $updateset[] = 'global_stdhead = (global_stdhead & ~'.$clrbits_global_stdhead.')';
    }
    if ($setbits_userdetails_page) {
        $updateset[] = 'userdetails_page = (userdetails_page | '.$setbits_userdetails_page.')';
    }
    if ($clrbits_userdetails_page) {
        $updateset[] = 'userdetails_page = (userdetails_page & ~'.$clrbits_userdetails_page.')';
    }
    if (!empty($updateset) && count($updateset)) {
        sql_query('UPDATE user_blocks SET '.implode(',', $updateset).' WHERE userid = '.sqlesc($id)) or sqlerr(__FILE__, __LINE__);
        $cache->delete('blocks_'.$id);
        $session->set('is-success', 'User Blocks Successfully Updated');
        unset($_POST);
        header('Location: '.$site_config['baseurl'].'/user_blocks.php');
        die();
    }
}

//==Index
$checkbox_index_ie_alert = ((curuser::$blocks['index_page'] & block_index::IE_ALERT) ? ' checked' : '');
$checkbox_index_news = ((curuser::$blocks['index_page'] & block_index::NEWS) ? ' checked' : '');
$checkbox_index_ajaxchat = ((curuser::$blocks['index_page'] & block_index::AJAXCHAT) ? ' checked' : '');
$checkbox_index_active_users = ((curuser::$blocks['index_page'] & block_index::ACTIVE_USERS) ? ' checked' : '');
$checkbox_index_trivia = ((curuser::$blocks['index_page'] & block_index::TRIVIA) ? ' checked' : '');
$checkbox_index_active_24h_users = ((curuser::$blocks['index_page'] & block_index::LAST_24_ACTIVE_USERS) ? ' checked' : '');
$checkbox_index_active_irc_users = ((curuser::$blocks['index_page'] & block_index::IRC_ACTIVE_USERS) ? ' checked' : '');
$checkbox_index_active_birthday_users = ((curuser::$blocks['index_page'] & block_index::BIRTHDAY_ACTIVE_USERS) ? ' checked' : '');
$checkbox_index_stats = ((curuser::$blocks['index_page'] & block_index::STATS) ? ' checked' : '');
$checkbox_index_disclaimer = ((curuser::$blocks['index_page'] & block_index::DISCLAIMER) ? ' checked' : '');
$checkbox_index_latest_user = ((curuser::$blocks['index_page'] & block_index::LATEST_USER) ? ' checked' : '');
$checkbox_index_latest_comments = ((curuser::$blocks['index_page'] & block_index::LATESTCOMMENTS) ? ' checked' : '');
$checkbox_index_latest_forumposts = ((curuser::$blocks['index_page'] & block_index::FORUMPOSTS) ? ' checked' : '');
$checkbox_index_latest_torrents = ((curuser::$blocks['index_page'] & block_index::LATEST_TORRENTS) ? ' checked' : '');
$checkbox_index_latest_torrents_scroll = ((curuser::$blocks['index_page'] & block_index::LATEST_TORRENTS_SCROLL) ? ' checked' : '');
$checkbox_index_announcement = ((curuser::$blocks['index_page'] & block_index::ANNOUNCEMENT) ? ' checked' : '');
$checkbox_index_donation_progress = ((curuser::$blocks['index_page'] & block_index::DONATION_PROGRESS) ? ' checked' : '');
$checkbox_index_ads = ((curuser::$blocks['index_page'] & block_index::ADVERTISEMENTS) ? ' checked' : '');
$checkbox_index_radio = ((curuser::$blocks['index_page'] & block_index::RADIO) ? ' checked' : '');
$checkbox_index_torrentfreak = ((curuser::$blocks['index_page'] & block_index::TORRENTFREAK) ? ' checked' : '');
$checkbox_index_christmasgift = ((curuser::$blocks['index_page'] & block_index::CHRISTMAS_GIFT) ? ' checked' : '');
$checkbox_index_active_poll = ((curuser::$blocks['index_page'] & block_index::ACTIVE_POLL) ? ' checked' : '');
$checkbox_index_mow = ((curuser::$blocks['index_page'] & block_index::MOVIEOFWEEK) ? ' checked' : '');
//==Stdhead
$checkbox_global_freeleech = ((curuser::$blocks['global_stdhead'] & block_stdhead::STDHEAD_FREELEECH) ? ' checked' : '');
$checkbox_global_demotion = ((curuser::$blocks['global_stdhead'] & block_stdhead::STDHEAD_DEMOTION) ? ' checked' : '');
$checkbox_global_message_alert = ((curuser::$blocks['global_stdhead'] & block_stdhead::STDHEAD_NEWPM) ? ' checked' : '');
$checkbox_global_staff_message_alert = ((curuser::$blocks['global_stdhead'] & block_stdhead::STDHEAD_STAFF_MESSAGE) ? ' checked' : '');
$checkbox_global_staff_report = ((curuser::$blocks['global_stdhead'] & block_stdhead::STDHEAD_REPORTS) ? ' checked' : '');
$checkbox_global_staff_uploadapp = ((curuser::$blocks['global_stdhead'] & block_stdhead::STDHEAD_UPLOADAPP) ? ' checked' : '');
$checkbox_global_happyhour = ((curuser::$blocks['global_stdhead'] & block_stdhead::STDHEAD_HAPPYHOUR) ? ' checked' : '');
$checkbox_global_crazyhour = ((curuser::$blocks['global_stdhead'] & block_stdhead::STDHEAD_CRAZYHOUR) ? ' checked' : '');
$checkbox_global_bugmessage = ((curuser::$blocks['global_stdhead'] & block_stdhead::STDHEAD_BUG_MESSAGE) ? ' checked' : '');
$checkbox_global_freeleech_contribution = ((curuser::$blocks['global_stdhead'] & block_stdhead::STDHEAD_FREELEECH_CONTRIBUTION) ? ' checked' : '');
//==Userdetails
$checkbox_userdetails_flush = ((curuser::$blocks['userdetails_page'] & block_userdetails::FLUSH) ? ' checked' : '');
$checkbox_userdetails_joined = ((curuser::$blocks['userdetails_page'] & block_userdetails::JOINED) ? ' checked' : '');
$checkbox_userdetails_onlinetime = ((curuser::$blocks['userdetails_page'] & block_userdetails::ONLINETIME) ? ' checked' : '');
$checkbox_userdetails_browser = ((curuser::$blocks['userdetails_page'] & block_userdetails::BROWSER) ? ' checked' : '');
$checkbox_userdetails_reputation = ((curuser::$blocks['userdetails_page'] & block_userdetails::REPUTATION) ? ' checked' : '');
$checkbox_userdetails_userhits = ((curuser::$blocks['userdetails_page'] & block_userdetails::PROFILE_HITS) ? ' checked' : '');
$checkbox_userdetails_birthday = ((curuser::$blocks['userdetails_page'] & block_userdetails::BIRTHDAY) ? ' checked' : '');
$checkbox_userdetails_contact_info = ((curuser::$blocks['userdetails_page'] & block_userdetails::CONTACT_INFO) ? ' checked' : '');
$checkbox_userdetails_iphistory = ((curuser::$blocks['userdetails_page'] & block_userdetails::IPHISTORY) ? ' checked' : '');
$checkbox_userdetails_traffic = ((curuser::$blocks['userdetails_page'] & block_userdetails::TRAFFIC) ? ' checked' : '');
$checkbox_userdetails_shareratio = ((curuser::$blocks['userdetails_page'] & block_userdetails::SHARE_RATIO) ? ' checked' : '');
$checkbox_userdetails_seedtime_ratio = ((curuser::$blocks['userdetails_page'] & block_userdetails::SEEDTIME_RATIO) ? ' checked' : '');
$checkbox_userdetails_seedbonus = ((curuser::$blocks['userdetails_page'] & block_userdetails::SEEDBONUS) ? ' checked' : '');
$checkbox_userdetails_irc_stats = ((curuser::$blocks['userdetails_page'] & block_userdetails::IRC_STATS) ? ' checked' : '');
$checkbox_userdetails_connectable = ((curuser::$blocks['userdetails_page'] & block_userdetails::CONNECTABLE_PORT) ? ' checked' : '');
$checkbox_userdetails_avatar = ((curuser::$blocks['userdetails_page'] & block_userdetails::AVATAR) ? ' checked' : '');
$checkbox_userdetails_userclass = ((curuser::$blocks['userdetails_page'] & block_userdetails::USERCLASS) ? ' checked' : '');
$checkbox_userdetails_gender = ((curuser::$blocks['userdetails_page'] & block_userdetails::GENDER) ? ' checked' : '');
$checkbox_userdetails_freestuffs = ((curuser::$blocks['userdetails_page'] & block_userdetails::FREESTUFFS) ? ' checked' : '');
$checkbox_userdetails_torrent_comments = ((curuser::$blocks['userdetails_page'] & block_userdetails::COMMENTS) ? ' checked' : '');
$checkbox_userdetails_forumposts = ((curuser::$blocks['userdetails_page'] & block_userdetails::FORUMPOSTS) ? ' checked' : '');
$checkbox_userdetails_invitedby = ((curuser::$blocks['userdetails_page'] & block_userdetails::INVITEDBY) ? ' checked' : '');
$checkbox_userdetails_torrents_block = ((curuser::$blocks['userdetails_page'] & block_userdetails::TORRENTS_BLOCK) ? ' checked' : '');
$checkbox_userdetails_completed = ((curuser::$blocks['userdetails_page'] & block_userdetails::COMPLETED) ? ' checked' : '');
$checkbox_userdetails_snatched_staff = ((curuser::$blocks['userdetails_page'] & block_userdetails::SNATCHED_STAFF) ? ' checked' : '');
$checkbox_userdetails_userinfo = ((curuser::$blocks['userdetails_page'] & block_userdetails::USERINFO) ? ' checked' : '');
$checkbox_userdetails_showpm = ((curuser::$blocks['userdetails_page'] & block_userdetails::SHOWPM) ? ' checked' : '');
$checkbox_userdetails_report = ((curuser::$blocks['userdetails_page'] & block_userdetails::REPORT_USER) ? ' checked' : '');
$checkbox_userdetails_userstatus = ((curuser::$blocks['userdetails_page'] & block_userdetails::USERSTATUS) ? ' checked' : '');
$checkbox_userdetails_usercomments = ((curuser::$blocks['userdetails_page'] & block_userdetails::USERCOMMENTS) ? ' checked' : '');
$checkbox_userdetails_showfriends = ((curuser::$blocks['userdetails_page'] & block_userdetails::SHOWFRIENDS) ? ' checked' : '');

$form = $level1 = $level2 = '';
$contents = [];
$form .= "
    <form action='' method='post'>
        <div class='bg-02'>
        <fieldset id='user_blocks_home' class='header'>
            <legend class='flipper has-text-primary'><i class='fa icon-up-open size_3' aria-is_hidden='true'></i>Home Page Settings</legend>
            <div>";

$level1 .= "
                <div class='level-center is-inline-flex'>";

$contents[] = "
                <div class='w-100'>Enable IE alert?</div>
                <div class='slideThree'>
                    <input type='checkbox' id='ie_alert' name='ie_alert' value='yes' $checkbox_index_ie_alert />
                    <label for='ie_alert'></label>
                </div>
                <div class='w-100'>Check this option if you want to enable the IE user alert.</div>";

$contents[] = "
                <div class='w-100'>Enable News?</div>
                <div class='slideThree'>
                    <input type='checkbox' id='news' name='news' value='yes' $checkbox_index_news />
                    <label for='news'></label>
                </div>
                <div class='w-100'>Check this option if you want to enable the News Block.</div>";

$contents[] = "
                <div class='w-100'>Enable AJAX Chat?</div>
                <div class='slideThree'>
                    <input type='checkbox' id='ajaxchat' name='ajaxchat' value='yes' $checkbox_index_ajaxchat />
                    <label for='ajaxchat'></label>
                </div>
                <div class='w-100'>Check this option if you want to enable the AJAX Chat.</div>";

$contents[] = "
                <div class='w-100'>Enable Active Users?</div>
                <div class='slideThree'>
                    <input type='checkbox' id='active_users' name='active_users' value='yes' $checkbox_index_active_users />
                    <label for='active_users'></label>
                </div>
                <div class='w-100'>Check this option if you want to enable the Active Users.</div>";

$contents[] = "
                <div class='w-100'>Enable Active Users Over 24hours?</div>
                <div class='slideThree'>
                    <input type='checkbox' id='last_24_active_users' name='last_24_active_users' value='yes' $checkbox_index_active_24h_users />
                    <label for='last_24_active_users'></label>
                </div>
                <div class='w-100'>Check this option if you want to enable the Active Users visited over 24hours.</div>";

$contents[] = "
                <div class='w-100'>Enable Active Irc Users?</div>
                <div class='slideThree'>
                    <input type='checkbox' id='irc_active_users' name='irc_active_users' value='yes' $checkbox_index_active_irc_users />
                    <label for='irc_active_users'></label>
                </div>
                <div class='w-100'>Check this option if you want to enable the Active Irc Users.</div>";

$contents[] = "
                <div class='w-100'>Enable Birthday Users?</div>
                <div class='slideThree'>
                    <input type='checkbox' id='birthday_active_users' name='birthday_active_users' value='yes' $checkbox_index_active_birthday_users />
                    <label for='birthday_active_users'></label>
                </div>
                <div class='w-100'>Check this option if you want to enable the Active Birthday Users.</div>";

$contents[] = "
                <div class='w-100'>Enable Site Stats?</div>
                <div class='slideThree'>
                    <input type='checkbox' id='stats' name='stats' value='yes' $checkbox_index_stats />
                <label for='stats'></label></div>
                <div class='w-100'>Check this option if you want to enable the Stats.</div>";

$contents[] = "
                <div class='w-100'>Enable Trivia?</div>
                <div class='slideThree'>
                    <input type='checkbox' id='trivia' name='trivia' value='yes' $checkbox_index_trivia />
                    <label for='trivia'></label>
                </div>
                <div class='w-100'>Check this option if you want to enable the Trivia Game.</div>";

$contents[] = "
                <div class='w-100'>Enable Disclaimer?</div>
                <div class='slideThree'>
                    <input type='checkbox' id='disclaimer' name='disclaimer' value='yes' $checkbox_index_disclaimer />
                    <label for='disclaimer'></label>
                </div>
                <div class='w-100'>Check this option if you want to enable Disclaimer.</div>";

$contents[] = "
                <div class='w-100'>Enable Latest User?</div>
                <div class='slideThree'>
                    <input type='checkbox' id='latest_user' name='latest_user' value='yes' $checkbox_index_latest_user />
                    <label for='latest_user'></label>
                </div>
                <div class='w-100'>Check this option if you want to enable Latest User.</div>";

$contents[] = "
                <div class='w-100'>Enable Latest Comments?</div>
                <div class='slideThree'> 
                    <input type='checkbox' id='latestcomments' name='latestcomments' value='yes' $checkbox_index_latest_comments /> 
                    <label for='latestcomments'></label>
                </div>
                <div class='w-100'>Check this option if you want to enable latest Comments.</div>";

$contents[] = "
                <div class='w-100'>Enable Latest Forum Posts?</div>
                <div class='slideThree'> 
                    <input type='checkbox' id='forumposts' name='forumposts' value='yes' $checkbox_index_latest_forumposts />
                    <label for='forumposts'></label>
                </div>
                <div class='w-100'>Check this option if you want to enable latest Forum Posts.</div>";

$contents[] = "
                <div class='w-100'>Enable Latest torrents?</div>
                <div class='slideThree'> 
                    <input type='checkbox' id='latest_torrents' name='latest_torrents' value='yes' $checkbox_index_latest_torrents />
                    <label for='latest_torrents'></label>
                </div>
                <div class='w-100'>Check this option if you want to enable latest torrents.</div>";

$contents[] = "
                <div class='w-100'>Enable Latest torrents scroll?</div>
                <div class='slideThree'> 
                    <input type='checkbox' id='latest_torrents_scroll' name='latest_torrents_scroll' value='yes' $checkbox_index_latest_torrents_scroll />
                    <label for='latest_torrents_scroll'></label>
                </div>
                <div class='w-100'>Check this option if you want to enable latest torrents marquee.</div>";

$contents[] = "
                <div class='w-100'>Enable Announcement?</div>
                <div class='slideThree'> 
                    <input type='checkbox' id='announcement' name='announcement' value='yes' $checkbox_index_announcement />
                    <label for='announcement'></label>
                </div>
                <div class='w-100'>Check this option if you want to enable the Announcement Block.</div>";

$contents[] = "
                <div class='w-100'>Enable Donation Progress?</div>
                <div class='slideThree'> 
                    <input type='checkbox' id='donation_progress' name='donation_progress' value='yes' $checkbox_index_donation_progress />
                    <label for='donation_progress'></label>
                </div>
                <div class='w-100'>Check this option if you want to enable the Donation Progress.</div>";

$contents[] = "
                <div class='w-100'>Enable Advertisements?</div>
                <div class='slideThree'> 
                    <input type='checkbox' id='advertisements' name='advertisements' value='yes' $checkbox_index_ads />
                    <label for='advertisements'></label>
                </div>
                <div class='w-100'>Check this option if you want to enable the Advertisements.</div>";

$contents[] = "
                <div class='w-100'>Enable Radio?</div>
                <div class='slideThree'> 
                    <input type='checkbox' id='radio' name='radio' value='yes' $checkbox_index_radio />
                    <label for='radio'></label>
                </div>
                <div class='w-100'>Check this option if you want to enable the site radio.</div>";

$contents[] = "
                <div class='w-100'>Enable Torrent Freak?</div>
                <div class='slideThree'> 
                    <input type='checkbox' id='torrentfreak' name='torrentfreak' value='yes' $checkbox_index_torrentfreak />
                    <label for='torrentfreak'></label>
                </div>
                <div class='w-100'>Check this option if you want to enable the torrent freak news.</div>";

$contents[] = "
                <div class='w-100'>Enable Christmas Gift?</div>
                <div class='slideThree'> 
                    <input type='checkbox' id='christmas_gift' name='christmas_gift' value='yes' $checkbox_index_christmasgift />
                    <label for='christmas_gift'></label>
                </div>
                <div class='w-100'>Check this option if you want to enable the Christmas Gift.</div>";

$contents[] = "
                <div class='w-100'>Enable Poll?</div>
                <div class='slideThree'> 
                    <input type='checkbox' id='active_poll' name='active_poll' value='yes' $checkbox_index_active_poll />
                    <label for='active_poll'></label>
                </div>
                <div class='w-100'>Check this option if you want to enable the Active Poll.</div>";

$contents[] = "
                <div class='w-100'>Enable Movie of the week?</div>
                <div class='slideThree'> 
                    <input type='checkbox' id='movie_ofthe_week' name='movie_ofthe_week' value='yes' $checkbox_index_mow />
                    <label for='movie_ofthe_week'></label>
                </div>
                <div class='w-100'>Check this option if you want to enable the MOvie of the week.</div>";

foreach ($contents as $content) {
    $level1 .= "
                <div class='margin10 w-20'>
                    <span class='bordered level-center bg-02'>
                        $content
                    </span>
                </div>";
}
$level1 .= '
            </div>';
$form .= main_div($level1);
$form .= "
                    <div class='has-text-centered margin20'>
                        <input class='button' type='submit' name='submit' value='Submit' />
                    </div>
        </fieldset>
        </div>
        <div class='bg-02 top20'>
        <fieldset id='user_blocks_site' class='header'>
            <legend class='flipper has-text-primary'><i class='fa icon-up-open size_3' aria-is_hidden='true'></i>Site Alert Settings</legend>
            <div>";

$level2 .= "
                <div class='level-center is-inline-flex'>";

$contents = [];
$contents[] = "
                <div class='w-100'>Freeleech?</div>
                <div class='slideThree'> 
                    <input type='checkbox' id='stdhead_freeleech' name='stdhead_freeleech' value='yes' $checkbox_global_freeleech />
                    <label for='stdhead_freeleech'></label>
                </div>
                <div class='w-100'>Enable 'freeleech mark' in stdhead</div>";

if ($CURUSER['class'] >= UC_STAFF) {
    $contents[] = "
                    <div class='w-100'>Staff Reports?</div>
                    <div class='slideThree'> 
                        <input type='checkbox' id='stdhead_reports' name='stdhead_reports' value='yes' $checkbox_global_staff_report />
                        <label for='stdhead_reports'></label>
                    </div>
                    <div class='w-100'>Enable reports alert in stdhead</div>";

    $contents[] = "
                    <div class='w-100'>Upload App Alert?</div>
                    <div class='slideThree'> 
                        <input type='checkbox' id='stdhead_uploadapp' name='stdhead_uploadapp' value='yes' $checkbox_global_staff_uploadapp />
                        <label for='stdhead_uploadapp'></label>
                    </div>
                    <div class='w-100'>Enable upload application alerts in stdhead</div>";

    $contents[] = "
                    <div class='w-100'>Demotion</div>
                    <div class='slideThree'>
                        <input type='checkbox' id='stdhead_demotion' name='stdhead_demotion' value='yes' $checkbox_global_demotion />
                        <label for='stdhead_demotion'></label>
                    </div>
                    <div class='w-100'>Enable the global demotion alerts block in stdhead</div>";

    $contents[] = "
                    <div class='w-100'>Staff Warning?</div>
                    <div class='slideThree'> 
                        <input type='checkbox' id='stdhead_staff_message' name='stdhead_staff_message' value='yes' $checkbox_global_staff_message_alert /> 
                        <label for='stdhead_staff_message'></label>
                    </div>
                    <div class='w-100'>Shows if there is a new message for staff alert in stdhead</div>";

    $contents[] = "
                    <div class='w-100'>Bug Alert Message?</div>
                    <div class='slideThree'>    
                        <input type='checkbox' id='stdhead_bugmessage' name='stdhead_bugmessage' value='yes' $checkbox_global_bugmessage />
                        <label for='stdhead_bugmessage'></label>
                    </div>
                    <div class='w-100'>Enable Bug Message alerts in stdhead</div>";
}
$contents[] = "
                <div class='w-100'>Message block?</div>
                <div class='slideThree'> 
                    <input type='checkbox' id='stdhead_newpm' name='stdhead_newpm' value='yes' $checkbox_global_message_alert />
                    <label for='stdhead_newpm'></label>
                    </div>
                <div class='w-100'>Enable message alerts in stdhead</div>";

$contents[] = "
                <div class='w-100'>Happyhour?</div>
                <div class='slideThree'> 
                    <input type='checkbox' id='stdhead_happyhour' name='stdhead_happyhour' value='yes' $checkbox_global_happyhour />
                    <label for='stdhead_happyhour'></label>
                </div>
                <div class='w-100'>Enable happy hour alerts in stdhead</div>";

$contents[] = "        
                <div class='w-100'>CrazyHour?</div>
                <div class='slideThree'> 
                    <input type='checkbox' id='stdhead_crazyhour' name='stdhead_crazyhour' value='yes' $checkbox_global_crazyhour />
                    <label for='stdhead_crazyhour'></label></div>
                <div class='w-100'>Enable crazyhour alerts in stdhead</div>";

$contents[] = "        
                <div class='w-100'>Karma Contributions</div>
                <div class='slideThree'> 
                    <input type='checkbox' id='stdhead_freeleech_contribution' name='stdhead_freeleech_contribution' value='yes' $checkbox_global_freeleech_contribution />
                    <label for='stdhead_freeleech_contribution'></label>
                </div>
                <div class='w-100'>Enable karma contribution status alert in stdhead</div>";

foreach ($contents as $content) {
    $level2 .= "
                <div class='margin10 w-20'>
                    <span class='bordered level-center bg-02'>
                        $content
                    </span>
                </div>";
}
$level2 .= '
            </div>';
$form .= main_div($level2);
$form .= "
                    <div class='has-text-centered margin20'>
                        <input class='button' type='submit' name='submit' value='Submit' />
                    </div>
        </fieldset>
        </div>
        <div class='bg-02 top20'>
        <fieldset id='user_blocks_user' class='header'>
            <legend class='flipper has-text-primary'><i class='fa icon-up-open size_3' aria-is_hidden='true'></i>Userdetails Page Settings</legend>
            <div>";

$level3 = "
                <div class='level-center is-inline-flex'>";

$contents = [];
$contents[] = "
                <div class='w-100'>Flush torrents?</div>
                <div class='slideThree'> <input type='checkbox' id='userdetails_flush' name='userdetails_flush' value='yes' $checkbox_userdetails_flush />
                <label for='userdetails_flush'></label></div>
                <div class='w-100'>Enable flush torrents</div>";

$contents[] = "
                <div class='w-100'>Join date?</div>
                <div class='slideThree'> <input type='checkbox' id='userdetails_joined' name='userdetails_joined' value='yes' $checkbox_userdetails_joined />
                <label for='userdetails_joined'></label></div>
                <div class='w-100'>Enable join date</div>";

$contents[] = "
                <div class='w-100'>Online time?</div>
                <div class='slideThree'> <input type='checkbox' id='userdetails_online_time' name='userdetails_online_time' value='yes' $checkbox_userdetails_onlinetime />
                <label for='userdetails_online_time'></label></div>
                <div class='w-100'>Enable online time</div>";

$contents[] = "
                <div class='w-100'>Browser?</div>
                <div class='slideThree'> <input type='checkbox' id='userdetails_browser' name='userdetails_browser' value='yes' $checkbox_userdetails_browser />
                <label for='userdetails_browser'></label></div>
                <div class='w-100'>Enable browser and os detection</div>";

$contents[] = "
                <div class='w-100'>Reputation?</div>
                <div class='slideThree'> <input type='checkbox' id='userdetails_reputation' name='userdetails_reputation' value='yes' $checkbox_userdetails_reputation />
                <label for='userdetails_reputation'></label></div>
                <div class='w-100'>Enable add reputation link</div>";

$contents[] = "
                <div class='w-100'>Profile hits?</div>
                <div class='slideThree'> <input type='checkbox' id='userdetails_user_hits' name='userdetails_user_hits' value='yes' $checkbox_userdetails_userhits />
                <label for='userdetails_user_hits'></label></div>
                <div class='w-100'>Enable user hits</div>";

$contents[] = "
                <div class='w-100'>Birthday?</div>
                <div class='slideThree'> <input type='checkbox' id='userdetails_birthday' name='userdetails_birthday' value='yes' $checkbox_userdetails_birthday />
                <label for='userdetails_birthday'></label></div>
                <div class='w-100'>Enable birthdate and age</div>";

$contents[] = "
                <div class='w-100'>Contact?</div>
                <div class='slideThree'> <input type='checkbox' id='userdetails_contact_info' name='userdetails_contact_info' value='yes' $checkbox_userdetails_contact_info />
                <label for='userdetails_contact_info'></label></div>
                <div class='w-100'>Enable contact infos</div>";

$contents[] = "
                <div class='w-100'>IP history?</div>
                <div class='slideThree'> <input type='checkbox' id='userdetails_iphistory' name='userdetails_iphistory' value='yes' $checkbox_userdetails_iphistory />
                <label for='userdetails_iphistory'></label></div>
                <div class='w-100'>Enable ip history lists</div>";

$contents[] = "
                <div class='w-100'>User traffic?</div>
                <div class='slideThree'> <input type='checkbox' id='userdetails_traffic' name='userdetails_traffic' value='yes' $checkbox_userdetails_traffic />
                <label for='userdetails_traffic'></label></div>
                <div class='w-100'>Enable uploaded and download</div>";

$contents[] = "
                <div class='w-100'>Share ratio?</div>
                <div class='slideThree'> <input type='checkbox' id='userdetails_share_ratio' name='userdetails_share_ratio' value='yes' $checkbox_userdetails_shareratio />
                <label for='userdetails_share_ratio'></label></div>
                <div class='w-100'>Enable share ratio</div>";

$contents[] = "
                <div class='w-100'>Seed time ratio?</div>
                <div class='slideThree'> <input type='checkbox' id='userdetails_seedtime_ratio' name='userdetails_seedtime_ratio' value='yes' $checkbox_userdetails_seedtime_ratio />
                <label for='userdetails_seedtime_ratio'></label></div>
                <div class='w-100'>Enable seed time per torrent average ratio</div>";

$contents[] = "
                <div class='w-100'>Seedbonus?</div>
                <div class='slideThree'> <input type='checkbox' id='userdetails_seedbonus' name='userdetails_seedbonus' value='yes' $checkbox_userdetails_seedbonus />
                <label for='userdetails_seedbonus'></label></div>
                <div class='w-100'>Enable seed bonus</div>";

$contents[] = "
                <div class='w-100'>IRC stats?</div>
                <div class='slideThree'> <input type='checkbox' id='userdetails_irc_stats' name='userdetails_irc_stats' value='yes' $checkbox_userdetails_irc_stats />
                <label for='userdetails_irc_stats'></label></div>
                <div class='w-100'>Enable irc online stats</div>";

$contents[] = "
                <div class='w-100'>Connectable?</div>
                <div class='slideThree'> <input type='checkbox' id='userdetails_connectable_port' name='userdetails_connectable_port' value='yes' $checkbox_userdetails_connectable />
                <label for='userdetails_connectable_port'></label></div>
                <div class='w-100'>Enable connectable and port</div>";

$contents[] = "
                 <div class='w-100'>Avatar?</div>
                <div class='slideThree'> <input type='checkbox' id='userdetails_avatar' name='userdetails_avatar' value='yes' $checkbox_userdetails_avatar />
                <label for='userdetails_avatar'></label></div>
                 <div class='w-100'>Enable avatar</div>";

$contents[] = "
                 <div class='w-100'>Userclass?</div>
                <div class='slideThree'> <input type='checkbox' id='userdetails_userclass' name='userdetails_userclass' value='yes' $checkbox_userdetails_userclass />
                <label for='userdetails_userclass'></label></div>
                 <div class='w-100'>Enable userclass</div>";

$contents[] = "
                <div class='w-100'>Gender?</div>
                <div class='slideThree'> <input type='checkbox' id='userdetails_gender' name='userdetails_gender' value='yes' $checkbox_userdetails_gender />
                <label for='userdetails_gender'></label></div>
                <div class='w-100'>Enable gender</div>";

$contents[] = "
                <div class='w-100'>Free stuffs?</div>
                <div class='slideThree'> <input type='checkbox' id='userdetails_freestuffs' name='userdetails_freestuffs' value='yes' $checkbox_userdetails_freestuffs />
                <label for='userdetails_freestuffs'></label></div>
                <div class='w-100'>Enable freeslots and freeleech status</div>";

$contents[] = "
                <div class='w-100'>Comments?</div>
                <div class='slideThree'> <input type='checkbox' id='userdetails_comments' name='userdetails_comments' value='yes' $checkbox_userdetails_torrent_comments />
                <label for='userdetails_comments'></label></div>
                <div class='w-100'>Enable torrent comments history</div>";

$contents[] = "
                <div class='w-100'>Forumposts?</div>
                <div class='slideThree'> <input type='checkbox' id='userdetails_forumposts' name='userdetails_forumposts' value='yes' $checkbox_userdetails_forumposts />
                <label for='userdetails_forumposts'></label></div>
                <div class='w-100'>Enable forum posts history</div>";

$contents[] = "
                <div class='w-100'>Invited by?</div>
                <div class='slideThree'> <input type='checkbox' id='userdetails_invitedby' name='userdetails_invitedby' value='yes' $checkbox_userdetails_invitedby />
                <label for='userdetails_invitedby'></label></div>
                <div class='w-100'>Enable invited by list</div>";

$contents[] = "
                <div class='w-100'>Torrents blocks?</div>
                <div class='slideThree'> <input type='checkbox' id='userdetails_torrents_block' name='userdetails_torrents_block' value='yes' $checkbox_userdetails_torrents_block />
                <label for='userdetails_torrents_block'></label></div>
                <div class='w-100'>Enable seeding, leeching, snatched and uploaded torrents</div>";

$contents[] = "
                <div class='w-100'>Staff snatched?</div>
                <div class='slideThree'> <input type='checkbox' id='userdetails_snatched_staff' name='userdetails_snatched_staff' value='yes' $checkbox_userdetails_snatched_staff />
                <label for='userdetails_snatched_staff'></label></div>
                <div class='w-100'>Enable staff snatchlist</div>";

$contents[] = "
                <div class='w-100'>User info?</div>
                <div class='slideThree'> <input type='checkbox' id='userdetails_userinfo' name='userdetails_userinfo' value='yes' $checkbox_userdetails_userinfo />
                <label for='userdetails_userinfo'></label></div>
                <div class='w-100'>Enable user info</div>";

$contents[] = "
                <div class='w-100'>Show PM?</div>
                <div class='slideThree'> 
                    <input type='checkbox' id='userdetails_showpm' name='userdetails_showpm' value='yes' $checkbox_userdetails_showpm />
                    <label for='userdetails_showpm'></label>
                </div>
                <div class='w-100'>Enable send message button</div>";

$contents[] = "
                <div class='w-100'>Show Friends</div>
                <div class='slideThree'> 
                    <input type='checkbox' id='userdetails_show_friends' name='userdetails_show_friends' value='yes' $checkbox_userdetails_showfriends />
                <label for='userdetails_show_friends'></label></div>
                <div class='w-100'>Enable show friends button</div>";

$contents[] = "
                <div class='w-100'>Report user?</div>
                <div class='slideThree'> 
                    <input type='checkbox' id='userdetails_report_user' name='userdetails_report_user' value='yes' $checkbox_userdetails_report />
                    <label for='userdetails_report_user'></label>
                </div>
                <div class='w-100'>Enable report users button</div>";

$contents[] = "
                <div class='w-100'>User status?</div>
                <div class='slideThree'> <input type='checkbox' id='userdetails_user_status' name='userdetails_user_status' value='yes' $checkbox_userdetails_userstatus />
                <label for='userdetails_user_status'></label></div>
                <div class='w-100'>Enable user status</div>";

$contents[] = "
                <div class='w-100'>User comments?</div>
                <div class='slideThree'> <input type='checkbox' id='userdetails_user_comments' name='userdetails_user_comments' value='yes' $checkbox_userdetails_usercomments />
                <label for='userdetails_user_comments'></label></div>
                <div class='w-100'>Enable user comments</div>";

if ($CURUSER['class'] >= UC_STAFF) {
    $contents[] = "
        <div class='w-100'>Completed?</div>
        <div class='slideThree'> <input type='checkbox' id='userdetails_completed' name='userdetails_completed' value='yes' $checkbox_userdetails_completed />
        <label for='userdetails_completed'></label></div>
        <div class='w-100'>Enable completed torrents</div>";
}

foreach ($contents as $content) {
    $level3 .= "
                <div class='margin10 w-20'>
                    <span class='bordered level-center bg-02'>
                        $content
                    </span>
                </div>";
}
$contents = [];
$level3 .= '
            </div>';
$form .= main_div($level3);
$form .= "
                    <div class='has-text-centered margin20'>
                        <input class='button' type='submit' name='submit' value='Submit' />
                    </div>
        </fieldset>
        </div>";

$form .= '
    </form>';

$HTMLOUT = wrapper($form);
echo stdhead('User Blocks Config', true).$HTMLOUT.stdfoot();
