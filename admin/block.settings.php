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
/*Block settings by elephant*/
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
$stdfoot = array(
    /** include js **/
    'js' => array(
        'custom-form-elements'
    )
);
$stdhead = array(
    /** include css **/
    'css' => array(
        'global_blocks'
    )
);
require_once (CLASS_DIR . 'class_check.php');
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
$lang = array_merge($lang, load_language('ad_block_settings'));
$block_set_cache = CACHE_DIR . 'block_settings_cache.php';
if ('POST' == $_SERVER['REQUEST_METHOD']) {
    unset($_POST['submit']);
    block_cache();
    exit;
}
/////////////////////////////
//	cache block function
/////////////////////////////
function block_cache()
{
    global $block_set_cache, $lang;
    $block_out = "<" . "?php\n\n\$BLOCKS = array(\n";
    foreach ($_POST as $k => $v) {
        $block_out.= ($k == 'block_undefined') ? "\t'{$k}' => '" . htmlsafechars($v) . "',\n" : "\t'{$k}' => " . intval($v) . ",\n";
    }
    $block_out.= "\n);\n\n?" . ">";
    if (is_file($block_set_cache) && is_writable(pathinfo($block_set_cache, PATHINFO_DIRNAME))) {
        $filenum = fopen($block_set_cache, 'w');
        ftruncate($filenum, 0);
        fwrite($filenum, $block_out);
        fclose($filenum);
    }
    redirect('staffpanel.php?tool=block.settings&amp;action=block.settings', $lang['block_updated'], 3);
}
function get_cache_array()
{
    return array(
        'ie_user_alert' => 1,
        'active_users_on' => 1,
        'active_24h_users_on' => 1,
        'active_irc_users_on' => 1,
        'active_birthday_users_on' => 1,
        'disclaimer_on' => 1,
        'shoutbox_on' => 1,
        'staff_shoutbox_on' => 1,
        'news_on' => 1,
        'stats_on' => 1,
        'latest_user_on' => 1,
        'forum_posts_on' => 1,
        'latest_torrents_on' => 1,
        'latest_torrents_scroll_on' => 1,
        'announcement' => 1,
        'donation_progress_on' => 1,
        'ads_on' => 1,
        'radio_on' => 1,
        'torrentfreak_on' => 1,
        'xmas_gift_on' => 1,
        'active_poll_on' => 1,
        'movie_ofthe_week_on' => 1,
        'global_demotion_on' => 1,
        'global_staff_warn_on' => 1,
        'global_message_on' => 1,
        'global_staff_uploadapp_on' => 1,
        'global_staff_report_on' => 1,
        'global_freeleech_on' => 1,
        'global_happyhour_on' => 1,
        'global_crazyhour_on' => 1,
        'global_freeleech_contribution_on' => 1,
        'global_bug_message_on' => 1,
        'userdetails_login_link_on' => 1,
        'userdetails_flush_on' => 1,
        'userdetails_joined_on' => 1,
        'userdetails_online_time_on' => 1,
        'userdetails_browser_on' => 1,
        'userdetails_reputation_on' => 1,
        'userdetails_profile_hits_on' => 1,
        'userdetails_birthday_on' => 1,
        'userdetails_contact_info_on' => 1,
        'userdetails_iphistory_on' => 1,
        'userdetails_traffic_on' => 1,
        'userdetails_share_ratio_on' => 1,
        'userdetails_seedtime_ratio_on' => 1,
        'userdetails_seedbonus_on' => 1,
        'userdetails_irc_stats_on' => 1,
        'userdetails_connectable_port_on' => 1,
        'userdetails_avatar_on' => 1,
        'userdetails_userclass_on' => 1,
        'userdetails_gender_on' => 1,
        'userdetails_freestuffs_on' => 1,
        'userdetails_comments_on' => 1,
        'userdetails_forumposts_on' => 1,
        'userdetails_invitedby_on' => 1,
        'userdetails_torrents_block_on' => 1,
        'userdetails_completed_on' => 1,
        'userdetails_snatched_staff_on' => 1,
        'userdetails_userinfo_on' => 1,
        'userdetails_showpm_on' => 1,
        'userdetails_report_user_on' => 1,
        'userdetails_user_status_on' => 1,
        'userdetails_user_comments_on' => 1
    );
}
if (!is_file($block_set_cache)) {
    $BLOCKS = get_cache_array();
} else {
    require_once $block_set_cache;
    if (!is_array($BLOCKS)) {
        $BLOCKS = get_cache_array();
    }
}
$HTMLOUT = '';
$HTMLOUT.= '
    <div>'.$lang['block_global'].'</div><br />
    <div><br />
    <form action="staffpanel.php?tool=block.settings&amp;action=block.settings" method="post">
    <div><h1>'.$lang['block_index'].'</h1></div>
    <table width="100%" border="0" cellpadding="5" cellspacing="0"><tr>
    <td width="60%">
    <b>'.$lang['block_iealert'].'</b><div><hr style="color:#A83838;" size="1" /></div>
    <div>'.$lang['block_iealert_set'].'</div></td>
    <td width="40%"><div style="width: auto;" align="right"><#ie_user_alert#></div></td>
    </tr></table>
    
    <table width="100%" border="0" cellpadding="5" cellspacing="0"><tr>
    <td width="60%">
    <b>'.$lang['block_news'].'</b><div><hr style="color:#A83838;" size="1" /></div>
    <div>'.$lang['block_news_set'].'</div></td>
    <td width="40%"><div style="width: auto;" align="right"><#news_on#></div></td>
    </tr></table>

    <table width="100%" border="0" cellpadding="5" cellspacing="0"><tr>
    <td width="60%">
    <b>'.$lang['block_shout'].'</b><div><hr style="color:#A83838;" size="1" /></div>
    <div>'.$lang['block_shout_set'].'</div></td>
    <td width="40%"><div style="width: auto;" align="right"><#shoutbox_on#></div></td>
    </tr></table>

    <table width="100%" border="0" cellpadding="5" cellspacing="0"><tr>
    <td width="60%">
    <b>'.$lang['block_staff_shout'].'</b><div><hr style="color:#A83838;" size="1" /></div>
    <div>'.$lang['block_staff_shout_set'].'</div></td>
    <td width="40%"><div style="width: auto;" align="right"><#staff_shoutbox_on#></div></td>
    </tr></table>

    <table width="100%" border="0" cellpadding="5" cellspacing="0"><tr>
    <td width="60%">
    <b>'.$lang['block_active_user'].'</b><div><hr style="color:#A83838;" size="1" /></div>
    <div>'.$lang['block_active_user_set'].'</div></td>
    <td width="40%"><div style="width: auto;" align="right"><#active_users_on#></div></td>
    </tr></table>
    
    <table width="100%" border="0" cellpadding="5" cellspacing="0"><tr>
    <td width="60%">
    <b>'.$lang['block_active_user24'].'</b><div><hr style="color:#A83838;" size="1" /></div>
    <div>'.$lang['block_active_user24_set'].'</div></td>
    <td width="40%"><div style="width: auto;" align="right"><#active_24h_users_on#></div></td>
    </tr></table>
    
    <table width="100%" border="0" cellpadding="5" cellspacing="0"><tr>
    <td width="60%">
    <b>'.$lang['block_active_irc'].'</b><div><hr style="color:#A83838;" size="1" /></div>
    <div>'.$lang['block_active_irc_set'].'</div></td>
    <td width="40%"><div style="width: auto;" align="right"><#active_irc_users_on#></div></td>
    </tr></table>
    
    <table width="100%" border="0" cellpadding="5" cellspacing="0"><tr>
    <td width="60%">
    <b>'.$lang['block_birthday'].'</b><div><hr style="color:#A83838;" size="1" /></div>
    <div>'.$lang['block_birthday_set'].'</div></td>
    <td width="40%"><div style="width: auto;" align="right"><#active_birthday_users_on#></div></td>
    </tr></table>
    
    <table width="100%" border="0" cellpadding="5" cellspacing="0"><tr>
    <td width="60%">
    <b>'.$lang['block_stats'].'</b><div><hr style="color:#A83838;" size="1" /></div>
    <div>'.$lang['block_stats_set'].'</div></td>
    <td width="40%"><div style="width: auto;" align="right"><#stats_on#></div></td>
    </tr></table>
    
    <table width="100%" border="0" cellpadding="5" cellspacing="0"><tr>
    <td width="60%">
    <b>'.$lang['block_disclaimer'].'</b><div><hr style="color:#A83838;" size="1" /></div>
    <div>'.$lang['block_disclaimer_set'].'</div></td>
    <td width="40%"><div style="width: auto;" align="right"><#disclaimer_on#></div></td>
    </tr></table>  
    
    <table width="100%" border="0" cellpadding="5" cellspacing="0"><tr>
    <td width="60%">
    <b>'.$lang['block_latest_users'].'</b><div><hr style="color:#A83838;" size="1" /></div>
    <div>'.$lang['block_latest_users_set'].'</div></td>
    <td width="40%"><div style="width: auto;" align="right"><#latest_user_on#></div></td>
    </tr></table>
    
    <table width="100%" border="0" cellpadding="5" cellspacing="0"><tr>
    <td width="60%">
    <b>'.$lang['block_forum_post'].'</b><div><hr style="color:#A83838;" size="1" /></div>
    <div>'.$lang['block_forum_post_set'].'</div></td>
    <td width="40%"><div style="width: auto;" align="right"><#forum_posts_on#></div></td>
    </tr></table>
    
    <table width="100%" border="0" cellpadding="5" cellspacing="0"><tr>
    <td width="60%">
    <b>'.$lang['block_torrents'].'</b><div><hr style="color:#A83838;" size="1" /></div>
    <div>'.$lang['block_torrents_set'].'</div></td>
    <td width="40%"><div style="width: auto;" align="right"><#latest_torrents_on#></div></td>
    </tr></table>
    
    <table width="100%" border="0" cellpadding="5" cellspacing="0"><tr>
    <td width="60%">
    <b>'.$lang['block_torrents_scroll'].'</b><div><hr style="color:#A83838;" size="1" /></div>
    <div>'.$lang['block_torrents_scroll_set'].'</div></td>
    <td width="40%"><div style="width: auto;" align="right"><#latest_torrents_scroll_on#></div></td>
    </tr></table>
    
    <table width="100%" border="0" cellpadding="5" cellspacing="0"><tr>
    <td width="60%">
    <b>'.$lang['block_announcement'].'</b><div><hr style="color:#A83838;" size="1" /></div>
    <div>'.$lang['block_announcement_set'].'</div></td>
    <td width="40%"><div style="width: auto;" align="right"><#announcement_on#></div></td>
    </tr></table>
    
    <table width="100%" border="0" cellpadding="5" cellspacing="0"><tr>
    <td width="60%">
    <b>'.$lang['block_donation'].'</b><div><hr style="color:#A83838;" size="1" /></div>
    <div>'.$lang['block_donation_set'].'</div></td>
    <td width="40%"><div style="width: auto;" align="right"><#donation_progress_on#></div></td>
    </tr></table>
    
    <table width="100%" border="0" cellpadding="5" cellspacing="0"><tr>
    <td width="60%">
    <b>'.$lang['block_advertise'].'</b><div><hr style="color:#A83838;" size="1" /></div>
    <div>'.$lang['block_advertise_set'].'</div></td>
    <td width="40%"><div style="width: auto;" align="right"><#ads_on#></div></td>
    </tr></table>
    
    <table width="100%" border="0" cellpadding="5" cellspacing="0"><tr>
    <td width="60%">
    <b>'.$lang['block_radio'].'</b><div><hr style="color:#A83838;" size="1" /></div>
    <div>'.$lang['block_radio_set'].'</div></td>
    <td width="40%"><div style="width: auto;" align="right"><#radio_on#></div></td>
    </tr></table>
    
    <table width="100%" border="0" cellpadding="5" cellspacing="0"><tr>
    <td width="60%">
    <b>'.$lang['block_torrent_freak'].'</b><div><hr style="color:#A83838;" size="1" /></div>
    <div>'.$lang['block_torrent_freak_set'].'</div></td>
    <td width="40%"><div style="width: auto;" align="right"><#torrentfreak_on#></div></td>
    </tr></table>
    
    <table width="100%" border="0" cellpadding="5" cellspacing="0"><tr>
    <td width="60%">
    <b>'.$lang['block_xmas'].'</b><div><hr style="color:#A83838;" size="1" /></div>
    <div>'.$lang['block_xmas_set'].'</div></td>
    <td width="40%"><div style="width: auto;" align="right"><#xmas_gift_on#></div></td>
    </tr></table>
    
    <table width="100%" border="0" cellpadding="5" cellspacing="0"><tr>
    <td width="60%">
    <b>'.$lang['block_poll'].'</b><div><hr style="color:#A83838;" size="1" /></div>
    <div>'.$lang['block_poll_set'].'</div></td>
    <td width="40%"><div style="width: auto;" align="right"><#active_poll_on#></div></td>
    </tr></table>

    <table width="100%" border="0" cellpadding="5" cellspacing="0"><tr>
    <td width="60%">
    <b>'.$lang['block_movie'].'</b><div><hr style="color:#A83838;" size="1" /></div>
    <div>'.$lang['block_movie_set'].'</div></td>
    <td width="40%"><div style="width: auto;" align="right"><#movie_ofthe_week_on#></div></td>
    </tr></table>
    
    <div><h1>'.$lang['block_stdhead_settings'].'</h1></div>
    
    <table width="100%" border="0" cellpadding="5" cellspacing="0"><tr>
    <td width="60%">
    <b>'.$lang['block_freelech'].'</b><div><hr style="color:#A83838;" size="1" /></div>
    <div>'.$lang['block_freelech_set'].'</div></td>
    <td width="40%"><div style="width: auto;" align="right"><#global_freeleech_on#></div></td>
    </tr></table>
    
    <table width="100%" border="0" cellpadding="5" cellspacing="0"><tr>
    <td width="60%">
    <b>'.$lang['block_demotion'].'</b><div><hr style="color:#A83838;" size="1" /></div>
    <div>'.$lang['block_demotion_set'].'</div></td>
    <td width="40%"><div style="width: auto;" align="right"><#global_demotion_on#></div></td>
    </tr></table>

    <table width="100%" border="0" cellpadding="5" cellspacing="0"><tr>
    <td width="60%">
    <b>'.$lang['block_message'].'</b><div><hr style="color:#A83838;" size="1" /></div>
    <div>'.$lang['block_message_set'].'</div></td>
    <td width="40%"><div style="width: auto;" align="right"><#global_message_on#></div></td>
    </tr></table>

    <table width="100%" border="0" cellpadding="5" cellspacing="0"><tr>
    <td width="60%">
    <b>'.$lang['block_staff_warnings'].'</b><div><hr style="color:#A83838;" size="1" /></div>
    <div>'.$lang['block_staff_warnings_set'].'</div></td>
    <td width="40%"><div style="width: auto;" align="right"><#global_staff_warn_on#></div></td>
    </tr></table>
    
    <table width="100%" border="0" cellpadding="5" cellspacing="0"><tr>
    <td width="60%">
    <b>'.$lang['block_staff_reports'].'</b><div><hr style="color:#A83838;" size="1" /></div>
    <div>'.$lang['block_staff_reports_set'].'</div></td>
    <td width="40%"><div style="width: auto;" align="right"><#global_staff_report_on#></div></td>
    </tr></table>
    
    <table width="100%" border="0" cellpadding="5" cellspacing="0"><tr>
    <td width="60%">
    <b>'.$lang['block_upload_alert'].'</b><div><hr style="color:#A83838;" size="1" /></div>
    <div>'.$lang['block_upload_alert_set'].'</div></td>
    <td width="40%"><div style="width: auto;" align="right"><#global_staff_uploadapp_on#></div></td>
    </tr></table>
    
    <table width="100%" border="0" cellpadding="5" cellspacing="0"><tr>
    <td width="60%">
    <b>'.$lang['block_happyhour'].'</b><div><hr style="color:#A83838;" size="1" /></div>
    <div>'.$lang['block_happyhour_set'].'</div></td>
    <td width="40%"><div style="width: auto;" align="right"><#global_happyhour_on#></div></td>
    </tr></table>
    
    <table width="100%" border="0" cellpadding="5" cellspacing="0"><tr>
    <td width="60%">
    <b>'.$lang['block_crazyhour'].'</b><div><hr style="color:#A83838;" size="1" /></div>
    <div>'.$lang['block_crazyhour_set'].'</div></td>
    <td width="40%"><div style="width: auto;" align="right"><#global_crazyhour_on#></div></td>
    </tr></table>
    
    <table width="100%" border="0" cellpadding="5" cellspacing="0"><tr>
    <td width="60%">
    <b>'.$lang['block_bug'].'</b><div><hr style="color:#A83838;" size="1" /></div>
    <div>'.$lang['block_bug_set'].'</div></td>
    <td width="40%"><div style="width: auto;" align="right"><#global_bug_message_on#></div></td>
    </tr></table>

    <table width="100%" border="0" cellpadding="5" cellspacing="0"><tr>
    <td width="60%">
    <b>'.$lang['block_karma_contributions'].'</b><div><hr style="color:#A83838;" size="1" /></div>
    <div>'.$lang['block_karma_contributions_set'].'</div></td>
    <td width="40%"><div style="width: auto;" align="right"><#global_freeleech_contribution_on#></div></td>
    </tr></table>
    
    <div><h1>'.$lang['block_userdetails'].'</h1></div>
    
    <table width="100%" border="0" cellpadding="5" cellspacing="0"><tr>
    <td width="60%">
    <b>'.$lang['block_login_link'].'</b><div><hr style="color:#A83838;" size="1" /></div>
    <div>'.$lang['block_login_link_set'].'</div></td>
    <td width="40%"><div style="width: auto;" align="right"><#userdetails_login_link_on#></div></td>
    </tr></table>
    
    <table width="100%" border="0" cellpadding="5" cellspacing="0"><tr>
    <td width="60%">
    <b>'.$lang['block_flush'].'</b><div><hr style="color:#A83838;" size="1" /></div>
    <div>'.$lang['block_flush_set'].'</div></td>
    <td width="40%"><div style="width: auto;" align="right"><#userdetails_flush_on#></div></td>
    </tr></table>
    
    <table width="100%" border="0" cellpadding="5" cellspacing="0"><tr>
    <td width="60%">
    <b>'.$lang['block_joined'].'</b><div><hr style="color:#A83838;" size="1" /></div>
    <div>'.$lang['block_joined_set'].'</div></td>
    <td width="40%"><div style="width: auto;" align="right"><#userdetails_joined_on#></div></td>
    </tr></table>
    
    <table width="100%" border="0" cellpadding="5" cellspacing="0"><tr>
    <td width="60%">
    <b>'.$lang['block_user_online_time'].'</b><div><hr style="color:#A83838;" size="1" /></div>
    <div>'.$lang['block_user_online_time_set'].'</div></td>
    <td width="40%"><div style="width: auto;" align="right"><#userdetails_online_time_on#></div></td>
    </tr></table>
    
    <table width="100%" border="0" cellpadding="5" cellspacing="0"><tr>
    <td width="60%">
    <b>'.$lang['block_browser_os'].'</b><div><hr style="color:#A83838;" size="1" /></div>
    <div>'.$lang['block_browser_os_set'].'</div></td>
    <td width="40%"><div style="width: auto;" align="right"><#userdetails_browser_on#></div></td>
    </tr></table>
    
    <table width="100%" border="0" cellpadding="5" cellspacing="0"><tr>
    <td width="60%">
    <b>'.$lang['block_reputation'].'</b><div><hr style="color:#A83838;" size="1" /></div>
    <div>'.$lang['block_reputation_set'].'</div></td>
    <td width="40%"><div style="width: auto;" align="right"><#userdetails_reputation_on#></div></td>
    </tr></table>
    
    <table width="100%" border="0" cellpadding="5" cellspacing="0"><tr>
    <td width="60%">
    <b>'.$lang['block_userhits'].'</b><div><hr style="color:#A83838;" size="1" /></div>
    <div>'.$lang['block_userhits_set'].'</div></td>
    <td width="40%"><div style="width: auto;" align="right"><#userdetails_profile_hits_on#></div></td>
    </tr></table>
    
    <table width="100%" border="0" cellpadding="5" cellspacing="0"><tr>
    <td width="60%">
    <b>'.$lang['block_birthday_userdetails'].'</b><div><hr style="color:#A83838;" size="1" /></div>
    <div>'.$lang['block_birthday_userdetails_set'].'</div></td>
    <td width="40%"><div style="width: auto;" align="right"><#userdetails_birthday_on#></div></td>
    </tr></table>
    
    <table width="100%" border="0" cellpadding="5" cellspacing="0"><tr>
    <td width="60%">
    <b>'.$lang['block_contact'].'</b><div><hr style="color:#A83838;" size="1" /></div>
    <div>'.$lang['block_contact_set'].'</div></td>
    <td width="40%"><div style="width: auto;" align="right"><#userdetails_contact_info_on#></div></td>
    </tr></table>
    
    <table width="100%" border="0" cellpadding="5" cellspacing="0"><tr>
    <td width="60%">
    <b>'.$lang['block_iphistory'].'</b><div><hr style="color:#A83838;" size="1" /></div>
    <div>'.$lang['block_iphistory_set'].'</div></td>
    <td width="40%"><div style="width: auto;" align="right"><#userdetails_iphistory_on#></div></td>
    </tr></table>
    
    <table width="100%" border="0" cellpadding="5" cellspacing="0"><tr>
    <td width="60%">
    <b>'.$lang['block_traffic'].'</b><div><hr style="color:#A83838;" size="1" /></div>
    <div>'.$lang['block_traffic_set'].'</div></td>
    <td width="40%"><div style="width: auto;" align="right"><#userdetails_traffic_on#></div></td>
    </tr></table>
    
    <table width="100%" border="0" cellpadding="5" cellspacing="0"><tr>
    <td width="60%">
    <b>'.$lang['block_shareratio'].'</b><div><hr style="color:#A83838;" size="1" /></div>
    <div>'.$lang['block_shareratio_set'].'</div></td>
    <td width="40%"><div style="width: auto;" align="right"><#userdetails_share_ratio_on#></div></td>
    </tr></table>
    
    <table width="100%" border="0" cellpadding="5" cellspacing="0"><tr>
    <td width="60%">
    <b>'.$lang['block_seedtime'].'</b><div><hr style="color:#A83838;" size="1" /></div>
    <div>'.$lang['block_seedtime_set'].'</div></td>
    <td width="40%"><div style="width: auto;" align="right"><#userdetails_seedtime_ratio_on#></div></td>
    </tr></table>
    
    <table width="100%" border="0" cellpadding="5" cellspacing="0"><tr>
    <td width="60%">
    <b>'.$lang['block_seedbonus'].'</b><div><hr style="color:#A83838;" size="1" /></div>
    <div>'.$lang['block_seedbonus_set'].'</div></td>
    <td width="40%"><div style="width: auto;" align="right"><#userdetails_seedbonus_on#></div></td>
    </tr></table>
    
    <table width="100%" border="0" cellpadding="5" cellspacing="0"><tr>
    <td width="60%">
    <b>'.$lang['block_ircstats'].'</b><div><hr style="color:#A83838;" size="1" /></div>
    <div>'.$lang['block_ircstats_set'].'</div></td>
    <td width="40%"><div style="width: auto;" align="right"><#userdetails_irc_stats_on#></div></td>
    </tr></table>
    
    <table width="100%" border="0" cellpadding="5" cellspacing="0"><tr>
    <td width="60%">
    <b>'.$lang['block_conn_port'].'</b><div><hr style="color:#A83838;" size="1" /></div>
    <div>'.$lang['block_conn_port_set'].'</div></td>
    <td width="40%"><div style="width: auto;" align="right"><#userdetails_connectable_port_on#></div></td>
    </tr></table>
    
    <table width="100%" border="0" cellpadding="5" cellspacing="0"><tr>
    <td width="60%">
    <b>'.$lang['block_avatar'].'</b><div><hr style="color:#A83838;" size="1" /></div>
    <div>'.$lang['block_avatar_set'].'</div></td>
    <td width="40%"><div style="width: auto;" align="right"><#userdetails_avatar_on#></div></td>
    </tr></table>
    
    <table width="100%" border="0" cellpadding="5" cellspacing="0"><tr>
    <td width="60%">
    <b>'.$lang['block_userclass'].'</b><div><hr style="color:#A83838;" size="1" /></div>
    <div>'.$lang['block_userclass_set'].'</div></td>
    <td width="40%"><div style="width: auto;" align="right"><#userdetails_userclass_on#></div></td>
    </tr></table>
    
    <table width="100%" border="0" cellpadding="5" cellspacing="0"><tr>
    <td width="60%">
    <b>'.$lang['block_gender'].'</b><div><hr style="color:#A83838;" size="1" /></div>
    <div>'.$lang['block_gender_set'].'</div></td>
    <td width="40%"><div style="width: auto;" align="right"><#userdetails_gender_on#></div></td>
    </tr></table>
    
    <table width="100%" border="0" cellpadding="5" cellspacing="0"><tr>
    <td width="60%">
    <b>'.$lang['block_freeslot_freelech'].'</b><div><hr style="color:#A83838;" size="1" /></div>
    <div>'.$lang['block_freeslot_freelech_set'].'</div></td>
    <td width="40%"><div style="width: auto;" align="right"><#userdetails_freestuffs_on#></div></td>
    </tr></table>
    
    <table width="100%" border="0" cellpadding="5" cellspacing="0"><tr>
    <td width="60%">
    <b>'.$lang['block_torrent_comment'].'</b><div><hr style="color:#A83838;" size="1" /></div>
    <div>'.$lang['block_torrent_comment_set'].'</div></td>
    <td width="40%"><div style="width: auto;" align="right"><#userdetails_comments_on#></div></td>
    </tr></table>
    
    <table width="100%" border="0" cellpadding="5" cellspacing="0"><tr>
    <td width="60%">
    <b>'.$lang['block_forum_user_post'].'</b><div><hr style="color:#A83838;" size="1" /></div>
    <div>'.$lang['block_forum_user_post_set'].'</div></td>
    <td width="40%"><div style="width: auto;" align="right"><#userdetails_forumposts_on#></div></td>
    </tr></table>
    
    <table width="100%" border="0" cellpadding="5" cellspacing="0"><tr>
    <td width="60%">
    <b>'.$lang['block_invitedby'].'</b><div><hr style="color:#A83838;" size="1" /></div>
    <div>'.$lang['block_invitedby_set'].'</div></td>
    <td width="40%"><div style="width: auto;" align="right"><#userdetails_invitedby_on#></div></td>
    </tr></table>
    
    <table width="100%" border="0" cellpadding="5" cellspacing="0"><tr>
    <td width="60%">
    <b>'.$lang['block_torrent_info'].'</b><div><hr style="color:#A83838;" size="1" /></div>
    <div>'.$lang['block_torrent_info_set'].'</div></td>
    <td width="40%"><div style="width: auto;" align="right"><#userdetails_torrents_block_on#></div></td>
    </tr></table>
    
    <table width="100%" border="0" cellpadding="5" cellspacing="0"><tr>
    <td width="60%">
    <b>'.$lang['block_completed'].'</b><div><hr style="color:#A83838;" size="1" /></div>
    <div>'.$lang['block_completed_set'].'</div></td>
    <td width="40%"><div style="width: auto;" align="right"><#userdetails_completed_on#></div></td>
    </tr></table>
    
    <table width="100%" border="0" cellpadding="5" cellspacing="0"><tr>
    <td width="60%">
    <b>'.$lang['block_staff_snatched'].'</b><div><hr style="color:#A83838;" size="1" /></div>
    <div>'.$lang['block_staff_snatched_set'].'</div></td>
    <td width="40%"><div style="width: auto;" align="right"><#userdetails_snatched_staff_on#></div></td>
    </tr></table>
    
    <table width="100%" border="0" cellpadding="5" cellspacing="0"><tr>
    <td width="60%">
    <b>'.$lang['block_user_info'].'</b><div><hr style="color:#A83838;" size="1" /></div>
    <div>'.$lang['block_user_info_set'].'</div></td>
    <td width="40%"><div style="width: auto;" align="right"><#userdetails_userinfo_on#></div></td>
    </tr></table>
    
    <table width="100%" border="0" cellpadding="5" cellspacing="0"><tr>
    <td width="60%">
    <b>'.$lang['block_show_pm'].'</b><div><hr style="color:#A83838;" size="1" /></div>
    <div>'.$lang['block_show_pm_set'].'</div></td>
    <td width="40%"><div style="width: auto;" align="right"><#userdetails_showpm_on#></div></td>
    </tr></table>
    
    <table width="100%" border="0" cellpadding="5" cellspacing="0"><tr>
    <td width="60%">
    <b>'.$lang['block_report'].'</b><div><hr style="color:#A83838;" size="1" /></div>
    <div>'.$lang['block_report_set'].'</div></td>
    <td width="40%"><div style="width: auto;" align="right"><#userdetails_report_user_on#></div></td>
    </tr></table>
    
    <table width="100%" border="0" cellpadding="5" cellspacing="0"><tr>
    <td width="60%">
    <b>'.$lang['block_user_status'].'</b><div><hr style="color:#A83838;" size="1" /></div>
    <div>'.$lang['block_user_status_set'].'</div></td>
    <td width="40%"><div style="width: auto;" align="right"><#userdetails_user_status_on#></div></td>
    </tr></table>
    
    <table width="100%" border="0" cellpadding="5" cellspacing="0"><tr>
    <td width="60%">
    <b>'.$lang['block_user_comments'].'</b><div><hr style="color:#A83838;" size="1" /></div>
    <div>'.$lang['block_user_comments_set'].'</div></td>
    <td width="40%"><div style="width: auto;" align="right"><#userdetails_user_comments_on#></div></td>
    </tr></table>
    <input type="submit" name="submit" value=" '.$lang['block_submit'].' " class="btn" tabindex="2" accesskey="s" />
    </form>
    </div>';
$HTMLOUT = preg_replace_callback("|<#(.*?)#>|", "template_out", $HTMLOUT);
echo stdhead($lang['block_stdhead'], true, $stdhead) , $HTMLOUT, stdfoot($stdfoot);
function template_out($matches)
{
    global $BLOCKS, $lang;
    return $lang['block_yes']. '<input name="' . $matches[1] . '" value="1" ' . ($BLOCKS[$matches[1]] == 1 ? 'checked="checked"' : "") . ' type="radio" />&nbsp;&nbsp;&nbsp;<input name="' . $matches[1] . '" value="0" ' . ($BLOCKS[$matches[1]] == 1 ? "" : 'checked="checked"') . ' type="radio" /> ' .$lang['block_no'];
}
function redirect($url, $text, $time = 2)
{
    global $INSTALLER09, $lang;
    $page_title = "{$lang['block_page_title']}";
    $page_detail = "<em>{$lang['block_redir']}</em>";
    $html = "
		<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\"
		\"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
		<html xmlns='http://www.w3.org/1999/xhtml'>
		<head>
		<meta http-equiv='refresh' content=\"{$time}; url={$INSTALLER09['baseurl']}/{$url}\" />
		<title>{$lang['block_stdhead']}</title>
    <link rel='stylesheet' href='./templates/1/1.css' type='text/css' />
    </head>
    <body>
    <div>
	  <div>{$lang['block_redir1']}</div>
		<div style='padding:8px'>
		<div style='font-size:12px'>$text
		<br />
		<br />
		<a href='{$INSTALLER09['baseurl']}/{$url}'>Click here if not redirected...</a>
		</div>
		</div>
		</div></body></html>";
    echo $html;
    exit;
}
?>
