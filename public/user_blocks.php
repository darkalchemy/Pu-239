<?php

declare(strict_types = 1);

use Delight\Auth\Auth;
use Envms\FluentPDO\Literal;
use Pu239\Cache;
use Pu239\Database;
use Pu239\Session;
use Pu239\User;

require_once __DIR__ . '/../include/bittorrent.php';
require_once INCL_DIR . 'function_html.php';
require_once INCL_DIR . 'function_users.php';
$curuser = check_user_status();
global $container, $site_config, $BLOCKS;

$auth = $container->get(Auth::class);
$session = $container->get(Session::class);
$users_class = $container->get(User::class);
$cache = $container->get(Cache::class);
if (isset($_GET['id']) && is_valid_id((int) $_GET['id']) && $curuser['class'] >= UC_STAFF) {
    $id = (int) $_GET['id'];
} else {
    $id = $curuser['id'];
}
$user = $users_class->getUserFromId($id);
if ($user['class'] < UC_STAFF && $user['got_blocks'] === 'no') {
    $session->set('is-link', 'You will have to unlock this before you can access it.');
    header('Location: ' . $site_config['paths']['baseurl'] . '/index.php');
    die();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $addset = $removeset = [];
    $setbits_index_page = $clrbits_index_page = $setbits_global_stdhead = $clrbits_global_stdhead = $setbits_userdetails_page = $clrbits_userdetails_page = 0;
    //==Index
    if (isset($_POST['ie_alert'])) {
        $setbits_index_page |= class_blocks_index::IE_ALERT;
    } else {
        $clrbits_index_page |= class_blocks_index::IE_ALERT;
    }
    if (isset($_POST['news'])) {
        $setbits_index_page |= class_blocks_index::NEWS;
    } else {
        $clrbits_index_page |= class_blocks_index::NEWS;
    }
    if (isset($_POST['ajaxchat'])) {
        $setbits_index_page |= class_blocks_index::AJAXCHAT;
    } else {
        $clrbits_index_page |= class_blocks_index::AJAXCHAT;
    }
    if (isset($_POST['trivia'])) {
        $setbits_index_page |= class_blocks_index::TRIVIA;
    } else {
        $clrbits_index_page |= class_blocks_index::TRIVIA;
    }
    if (isset($_POST['active_users'])) {
        $setbits_index_page |= class_blocks_index::ACTIVE_USERS;
    } else {
        $clrbits_index_page |= class_blocks_index::ACTIVE_USERS;
    }
    if (isset($_POST['last_24_active_users'])) {
        $setbits_index_page |= class_blocks_index::LAST_24_ACTIVE_USERS;
    } else {
        $clrbits_index_page |= class_blocks_index::LAST_24_ACTIVE_USERS;
    }
    if (isset($_POST['irc_active_users'])) {
        $setbits_index_page |= class_blocks_index::IRC_ACTIVE_USERS;
    } else {
        $clrbits_index_page |= class_blocks_index::IRC_ACTIVE_USERS;
    }
    if (isset($_POST['cooker'])) {
        $setbits_index_page |= class_blocks_index::COOKER;
    } else {
        $clrbits_index_page |= class_blocks_index::COOKER;
    }
    if (isset($_POST['requests'])) {
        $setbits_index_page |= class_blocks_index::REQUESTS;
    } else {
        $clrbits_index_page |= class_blocks_index::REQUESTS;
    }
    if (isset($_POST['offers'])) {
        $setbits_index_page |= class_blocks_index::OFFERS;
    } else {
        $clrbits_index_page |= class_blocks_index::OFFERS;
    }
    if (isset($_POST['birthday_active_users'])) {
        $setbits_index_page |= class_blocks_index::BIRTHDAY_ACTIVE_USERS;
    } else {
        $clrbits_index_page |= class_blocks_index::BIRTHDAY_ACTIVE_USERS;
    }
    if (isset($_POST['stats'])) {
        $setbits_index_page |= class_blocks_index::STATS;
    } else {
        $clrbits_index_page |= class_blocks_index::STATS;
    }
    if (isset($_POST['disclaimer'])) {
        $setbits_index_page |= class_blocks_index::DISCLAIMER;
    } else {
        $clrbits_index_page |= class_blocks_index::DISCLAIMER;
    }
    if (isset($_POST['latest_user'])) {
        $setbits_index_page |= class_blocks_index::LATEST_USER;
    } else {
        $clrbits_index_page |= class_blocks_index::LATEST_USER;
    }
    if (isset($_POST['latestcomments'])) {
        $setbits_index_page |= class_blocks_index::LATESTCOMMENTS;
    } else {
        $clrbits_index_page |= class_blocks_index::LATESTCOMMENTS;
    }

    if (isset($_POST['forumposts'])) {
        $setbits_index_page |= class_blocks_index::FORUMPOSTS;
    } else {
        $clrbits_index_page |= class_blocks_index::FORUMPOSTS;
    }
    if (isset($_POST['staff_picks'])) {
        $setbits_index_page |= class_blocks_index::STAFF_PICKS;
    } else {
        $clrbits_index_page |= class_blocks_index::STAFF_PICKS;
    }
    if (isset($_POST['latest_torrents'])) {
        $setbits_index_page |= class_blocks_index::LATEST_TORRENTS;
    } else {
        $clrbits_index_page |= class_blocks_index::LATEST_TORRENTS;
    }
    if (isset($_POST['latest_movies'])) {
        $setbits_index_page |= class_blocks_index::LATEST_MOVIES;
    } else {
        $clrbits_index_page |= class_blocks_index::LATEST_MOVIES;
    }
    if (isset($_POST['latest_tv'])) {
        $setbits_index_page |= class_blocks_index::LATEST_TV;
    } else {
        $clrbits_index_page |= class_blocks_index::LATEST_TV;
    }
    if (isset($_POST['latest_torrents_scroll'])) {
        $setbits_index_page |= class_blocks_index::LATEST_TORRENTS_SCROLL;
    } else {
        $clrbits_index_page |= class_blocks_index::LATEST_TORRENTS_SCROLL;
    }
    if (isset($_POST['latest_torrents_slider'])) {
        $setbits_index_page |= class_blocks_index::LATEST_TORRENTS_SLIDER;
    } else {
        $clrbits_index_page |= class_blocks_index::LATEST_TORRENTS_SLIDER;
    }
    if (isset($_POST['announcement'])) {
        $setbits_index_page |= class_blocks_index::ANNOUNCEMENT;
    } else {
        $clrbits_index_page |= class_blocks_index::ANNOUNCEMENT;
    }
    if (isset($_POST['donation_progress'])) {
        $setbits_index_page |= class_blocks_index::DONATION_PROGRESS;
    } else {
        $clrbits_index_page |= class_blocks_index::DONATION_PROGRESS;
    }
    if (isset($_POST['advertisements'])) {
        $setbits_index_page |= class_blocks_index::ADVERTISEMENTS;
    } else {
        $clrbits_index_page |= class_blocks_index::ADVERTISEMENTS;
    }
    if (isset($_POST['torrentfreak'])) {
        $setbits_index_page |= class_blocks_index::TORRENTFREAK;
    } else {
        $clrbits_index_page |= class_blocks_index::TORRENTFREAK;
    }
    if (isset($_POST['christmas_gift'])) {
        $setbits_index_page |= class_blocks_index::CHRISTMAS_GIFT;
    } else {
        $clrbits_index_page |= class_blocks_index::CHRISTMAS_GIFT;
    }
    if (isset($_POST['active_poll'])) {
        $setbits_index_page |= class_blocks_index::ACTIVE_POLL;
    } else {
        $clrbits_index_page |= class_blocks_index::ACTIVE_POLL;
    }
    if (isset($_POST['movie_ofthe_week'])) {
        $setbits_index_page |= class_blocks_index::MOVIEOFWEEK;
    } else {
        $clrbits_index_page |= class_blocks_index::MOVIEOFWEEK;
    }
    //==Stdhead
    if (isset($_POST['stdhead_freeleech'])) {
        $setbits_global_stdhead |= class_blocks_stdhead::STDHEAD_FREELEECH;
    } else {
        $clrbits_global_stdhead |= class_blocks_stdhead::STDHEAD_FREELEECH;
    }
    if (isset($_POST['stdhead_demotion'])) {
        $setbits_global_stdhead |= class_blocks_stdhead::STDHEAD_DEMOTION;
    } else {
        $clrbits_global_stdhead |= class_blocks_stdhead::STDHEAD_DEMOTION;
    }
    if (isset($_POST['stdhead_newpm'])) {
        $setbits_global_stdhead |= class_blocks_stdhead::STDHEAD_NEWPM;
    } else {
        $clrbits_global_stdhead |= class_blocks_stdhead::STDHEAD_NEWPM;
    }
    if (isset($_POST['stdhead_staff_message'])) {
        $setbits_global_stdhead |= class_blocks_stdhead::STDHEAD_STAFF_MESSAGE;
    } else {
        $clrbits_global_stdhead |= class_blocks_stdhead::STDHEAD_STAFF_MESSAGE;
    }
    if (isset($_POST['stdhead_reports'])) {
        $setbits_global_stdhead |= class_blocks_stdhead::STDHEAD_REPORTS;
    } else {
        $clrbits_global_stdhead |= class_blocks_stdhead::STDHEAD_REPORTS;
    }
    if (isset($_POST['stdhead_uploadapp'])) {
        $setbits_global_stdhead |= class_blocks_stdhead::STDHEAD_UPLOADAPP;
    } else {
        $clrbits_global_stdhead |= class_blocks_stdhead::STDHEAD_UPLOADAPP;
    }
    if (isset($_POST['stdhead_happyhour'])) {
        $setbits_global_stdhead |= class_blocks_stdhead::STDHEAD_HAPPYHOUR;
    } else {
        $clrbits_global_stdhead |= class_blocks_stdhead::STDHEAD_HAPPYHOUR;
    }
    if (isset($_POST['stdhead_crazyhour'])) {
        $setbits_global_stdhead |= class_blocks_stdhead::STDHEAD_CRAZYHOUR;
    } else {
        $clrbits_global_stdhead |= class_blocks_stdhead::STDHEAD_CRAZYHOUR;
    }
    if (isset($_POST['stdhead_bugmessage'])) {
        $setbits_global_stdhead |= class_blocks_stdhead::STDHEAD_BUG_MESSAGE;
    } else {
        $clrbits_global_stdhead |= class_blocks_stdhead::STDHEAD_BUG_MESSAGE;
    }
    if (isset($_POST['stdhead_freeleech_contribution'])) {
        $setbits_global_stdhead |= class_blocks_stdhead::STDHEAD_FREELEECH_CONTRIBUTION;
    } else {
        $clrbits_global_stdhead |= class_blocks_stdhead::STDHEAD_FREELEECH_CONTRIBUTION;
    }
    //==Userdetails
    if (isset($_POST['userdetails_flush'])) {
        $setbits_userdetails_page |= class_blocks_userdetails::FLUSH;
    } else {
        $clrbits_userdetails_page |= class_blocks_userdetails::FLUSH;
    }
    if (isset($_POST['userdetails_joined'])) {
        $setbits_userdetails_page |= class_blocks_userdetails::JOINED;
    } else {
        $clrbits_userdetails_page |= class_blocks_userdetails::JOINED;
    }
    if (isset($_POST['userdetails_online_time'])) {
        $setbits_userdetails_page |= class_blocks_userdetails::ONLINETIME;
    } else {
        $clrbits_userdetails_page |= class_blocks_userdetails::ONLINETIME;
    }
    if (isset($_POST['userdetails_browser'])) {
        $setbits_userdetails_page |= class_blocks_userdetails::BROWSER;
    } else {
        $clrbits_userdetails_page |= class_blocks_userdetails::BROWSER;
    }
    if (isset($_POST['userdetails_reputation'])) {
        $setbits_userdetails_page |= class_blocks_userdetails::REPUTATION;
    } else {
        $clrbits_userdetails_page |= class_blocks_userdetails::REPUTATION;
    }
    if (isset($_POST['userdetails_user_hits'])) {
        $setbits_userdetails_page |= class_blocks_userdetails::PROFILE_HITS;
    } else {
        $clrbits_userdetails_page |= class_blocks_userdetails::PROFILE_HITS;
    }
    if (isset($_POST['userdetails_birthday'])) {
        $setbits_userdetails_page |= class_blocks_userdetails::BIRTHDAY;
    } else {
        $clrbits_userdetails_page |= class_blocks_userdetails::BIRTHDAY;
    }
    if (isset($_POST['userdetails_birthday'])) {
        $setbits_userdetails_page |= class_blocks_userdetails::BIRTHDAY;
    } else {
        $clrbits_userdetails_page |= class_blocks_userdetails::BIRTHDAY;
    }
    if (isset($_POST['userdetails_contact_info'])) {
        $setbits_userdetails_page |= class_blocks_userdetails::CONTACT_INFO;
    } else {
        $clrbits_userdetails_page |= class_blocks_userdetails::CONTACT_INFO;
    }
    // used only in userdetails.php
    if (isset($_POST['userdetails_avatar'])) {
        $setbits_userdetails_page |= class_blocks_userdetails::AVATAR;
    } else {
        $clrbits_userdetails_page |= class_blocks_userdetails::AVATAR;
    }
    if (isset($_POST['userdetails_iphistory'])) {
        $setbits_userdetails_page |= class_blocks_userdetails::IPHISTORY;
    } else {
        $clrbits_userdetails_page |= class_blocks_userdetails::IPHISTORY;
    }
    if (isset($_POST['userdetails_traffic'])) {
        $setbits_userdetails_page |= class_blocks_userdetails::TRAFFIC;
    } else {
        $clrbits_userdetails_page |= class_blocks_userdetails::TRAFFIC;
    }
    if (isset($_POST['userdetails_share_ratio'])) {
        $setbits_userdetails_page |= class_blocks_userdetails::SHARE_RATIO;
    } else {
        $clrbits_userdetails_page |= class_blocks_userdetails::SHARE_RATIO;
    }
    if (isset($_POST['userdetails_seedtime_ratio'])) {
        $setbits_userdetails_page |= class_blocks_userdetails::SEEDTIME_RATIO;
    } else {
        $clrbits_userdetails_page |= class_blocks_userdetails::SEEDTIME_RATIO;
    }
    if (isset($_POST['userdetails_seedbonus'])) {
        $setbits_userdetails_page |= class_blocks_userdetails::SEEDBONUS;
    } else {
        $clrbits_userdetails_page |= class_blocks_userdetails::SEEDBONUS;
    }
    if (isset($_POST['userdetails_irc_stats'])) {
        $setbits_userdetails_page |= class_blocks_userdetails::IRC_STATS;
    } else {
        $clrbits_userdetails_page |= class_blocks_userdetails::IRC_STATS;
    }
    if (isset($_POST['userdetails_connectable_port'])) {
        $setbits_userdetails_page |= class_blocks_userdetails::CONNECTABLE_PORT;
    } else {
        $clrbits_userdetails_page |= class_blocks_userdetails::CONNECTABLE_PORT;
    }
    if (isset($_POST['userdetails_userclass'])) {
        $setbits_userdetails_page |= class_blocks_userdetails::USERCLASS;
    } else {
        $clrbits_userdetails_page |= class_blocks_userdetails::USERCLASS;
    }
    if (isset($_POST['userdetails_gender'])) {
        $setbits_userdetails_page |= class_blocks_userdetails::GENDER;
    } else {
        $clrbits_userdetails_page |= class_blocks_userdetails::GENDER;
    }
    if (isset($_POST['userdetails_freestuffs'])) {
        $setbits_userdetails_page |= class_blocks_userdetails::FREESTUFFS;
    } else {
        $clrbits_userdetails_page |= class_blocks_userdetails::FREESTUFFS;
    }
    if (isset($_POST['userdetails_comments'])) {
        $setbits_userdetails_page |= class_blocks_userdetails::COMMENTS;
    } else {
        $clrbits_userdetails_page |= class_blocks_userdetails::COMMENTS;
    }
    if (isset($_POST['userdetails_forumposts'])) {
        $setbits_userdetails_page |= class_blocks_userdetails::FORUMPOSTS;
    } else {
        $clrbits_userdetails_page |= class_blocks_userdetails::FORUMPOSTS;
    }
    if (isset($_POST['userdetails_invitedby'])) {
        $setbits_userdetails_page |= class_blocks_userdetails::INVITEDBY;
    } else {
        $clrbits_userdetails_page |= class_blocks_userdetails::INVITEDBY;
    }
    if (isset($_POST['userdetails_torrents_block'])) {
        $setbits_userdetails_page |= class_blocks_userdetails::TORRENTS_BLOCK;
    } else {
        $clrbits_userdetails_page |= class_blocks_userdetails::TORRENTS_BLOCK;
    }
    if (isset($_POST['userdetails_completed'])) {
        $setbits_userdetails_page |= class_blocks_userdetails::COMPLETED;
    } else {
        $clrbits_userdetails_page |= class_blocks_userdetails::COMPLETED;
    }
    if (isset($_POST['userdetails_snatched_staff'])) {
        $setbits_userdetails_page |= class_blocks_userdetails::SNATCHED_STAFF;
    } else {
        $clrbits_userdetails_page |= class_blocks_userdetails::SNATCHED_STAFF;
    }
    if (isset($_POST['userdetails_userinfo'])) {
        $setbits_userdetails_page |= class_blocks_userdetails::USERINFO;
    } else {
        $clrbits_userdetails_page |= class_blocks_userdetails::USERINFO;
    }
    if (isset($_POST['userdetails_showpm'])) {
        $setbits_userdetails_page |= class_blocks_userdetails::SHOWPM;
    } else {
        $clrbits_userdetails_page |= class_blocks_userdetails::SHOWPM;
    }
    if (isset($_POST['userdetails_report_user'])) {
        $setbits_userdetails_page |= class_blocks_userdetails::REPORT_USER;
    } else {
        $clrbits_userdetails_page |= class_blocks_userdetails::REPORT_USER;
    }
    if (isset($_POST['userdetails_user_status'])) {
        $setbits_userdetails_page |= class_blocks_userdetails::USERSTATUS;
    } else {
        $clrbits_userdetails_page |= class_blocks_userdetails::USERSTATUS;
    }
    if (isset($_POST['userdetails_user_comments'])) {
        $setbits_userdetails_page |= class_blocks_userdetails::USERCOMMENTS;
    } else {
        $clrbits_userdetails_page |= class_blocks_userdetails::USERCOMMENTS;
    }
    if (isset($_POST['userdetails_show_friends'])) {
        $setbits_userdetails_page |= class_blocks_userdetails::SHOWFRIENDS;
    } else {
        $clrbits_userdetails_page |= class_blocks_userdetails::SHOWFRIENDS;
    }
    //== set n clear
    if ($setbits_index_page) {
        $addset['index_page'] = new Literal('index_page | ' . $setbits_index_page);
    }
    if ($clrbits_index_page) {
        $removeset['index_page'] = new Literal('index_page & ~' . $clrbits_index_page);
    }
    if ($setbits_global_stdhead) {
        $addset['global_stdhead'] = new Literal('global_stdhead | ' . $setbits_global_stdhead);
    }
    if ($clrbits_global_stdhead) {
        $removeset['global_stdhead'] = new Literal('global_stdhead & ~' . $clrbits_global_stdhead);
    }
    if ($setbits_userdetails_page) {
        $addset['userdetails_page'] = new Literal('userdetails_page | ' . $setbits_userdetails_page);
    }
    if ($clrbits_userdetails_page) {
        $removeset['userdetails_page'] = new Literal('userdetails_page & ~' . $clrbits_userdetails_page);
    }
    if (!empty($addset) || !empty($removeset)) {
        $fluent = $container->get(Database::class);
        if (!empty($addset)) {
            $query = $fluent->update('user_blocks')
                            ->set($addset)
                            ->where('userid = ?', $id)
                            ->execute();
        }
        if (!empty($removeset)) {
            $fluent->update('user_blocks')
                   ->set($removeset)
                   ->where('userid = ?', $id)
                   ->execute();
        }
        $blocks = $fluent->from('user_blocks')
                         ->select(null)
                         ->select('index_page')
                         ->select('global_stdhead')
                         ->select('userdetails_page')
                         ->where('userid = ?', $id)
                         ->fetch();

        $update['blocks'] = $blocks;
        $cache->update_row('user_' . $id, $update);
        $session->set('is-success', 'User Blocks Successfully Updated');
        unset($_POST);
        header('Location: ' . $_SERVER['PHP_SELF']);
        die();
    }
}

//==Index
$checkbox_index_ie_alert = $user['blocks']['index_page'] & class_blocks_index::IE_ALERT ? 'checked' : '';
$checkbox_index_news = $user['blocks']['index_page'] & class_blocks_index::NEWS ? 'checked' : '';
$checkbox_index_ajaxchat = $user['blocks']['index_page'] & class_blocks_index::AJAXCHAT ? 'checked' : '';
$checkbox_index_active_users = $user['blocks']['index_page'] & class_blocks_index::ACTIVE_USERS ? 'checked' : '';
$checkbox_index_trivia = $user['blocks']['index_page'] & class_blocks_index::TRIVIA ? 'checked' : '';
$checkbox_index_active_24h_users = $user['blocks']['index_page'] & class_blocks_index::LAST_24_ACTIVE_USERS ? 'checked' : '';
$checkbox_index_active_irc_users = $user['blocks']['index_page'] & class_blocks_index::IRC_ACTIVE_USERS ? 'checked' : '';
$checkbox_index_active_birthday_users = $user['blocks']['index_page'] & class_blocks_index::BIRTHDAY_ACTIVE_USERS ? 'checked' : '';
$checkbox_index_stats = $user['blocks']['index_page'] & class_blocks_index::STATS ? 'checked' : '';
$checkbox_index_cooker = $user['blocks']['index_page'] & class_blocks_index::COOKER ? 'checked' : '';
$checkbox_index_requests = $user['blocks']['index_page'] & class_blocks_index::REQUESTS ? 'checked' : '';
$checkbox_index_offers = $user['blocks']['index_page'] & class_blocks_index::OFFERS ? 'checked' : '';
$checkbox_index_disclaimer = $user['blocks']['index_page'] & class_blocks_index::DISCLAIMER ? 'checked' : '';
$checkbox_index_latest_user = $user['blocks']['index_page'] & class_blocks_index::LATEST_USER ? 'checked' : '';
$checkbox_index_latest_comments = $user['blocks']['index_page'] & class_blocks_index::LATESTCOMMENTS ? 'checked' : '';
$checkbox_index_latest_forumposts = $user['blocks']['index_page'] & class_blocks_index::FORUMPOSTS ? 'checked' : '';
$checkbox_index_staff_picks = $user['blocks']['index_page'] & class_blocks_index::STAFF_PICKS ? 'checked' : '';
$checkbox_index_latest_torrents = $user['blocks']['index_page'] & class_blocks_index::LATEST_TORRENTS ? 'checked' : '';
$checkbox_index_latest_movies = $user['blocks']['index_page'] & class_blocks_index::LATEST_MOVIES ? 'checked' : '';
$checkbox_index_latest_tv = $user['blocks']['index_page'] & class_blocks_index::LATEST_TV ? 'checked' : '';
$checkbox_index_latest_torrents_scroll = $user['blocks']['index_page'] & class_blocks_index::LATEST_TORRENTS_SCROLL ? 'checked' : '';
$checkbox_index_latest_torrents_slider = $user['blocks']['index_page'] & class_blocks_index::LATEST_TORRENTS_SLIDER ? 'checked' : '';
$checkbox_index_announcement = $user['blocks']['index_page'] & class_blocks_index::ANNOUNCEMENT ? 'checked' : '';
$checkbox_index_donation_progress = $user['blocks']['index_page'] & class_blocks_index::DONATION_PROGRESS ? 'checked' : '';
$checkbox_index_ads = $user['blocks']['index_page'] & class_blocks_index::ADVERTISEMENTS ? 'checked' : '';
$checkbox_index_torrentfreak = $user['blocks']['index_page'] & class_blocks_index::TORRENTFREAK ? 'checked' : '';
$checkbox_index_christmasgift = $user['blocks']['index_page'] & class_blocks_index::CHRISTMAS_GIFT ? 'checked' : '';
$checkbox_index_active_poll = $user['blocks']['index_page'] & class_blocks_index::ACTIVE_POLL ? 'checked' : '';
$checkbox_index_mow = $user['blocks']['index_page'] & class_blocks_index::MOVIEOFWEEK ? 'checked' : '';
//==Stdhead
$checkbox_global_freeleech = $user['blocks']['global_stdhead'] & class_blocks_stdhead::STDHEAD_FREELEECH ? 'checked' : '';
$checkbox_global_demotion = $user['blocks']['global_stdhead'] & class_blocks_stdhead::STDHEAD_DEMOTION ? 'checked' : '';
$checkbox_global_message_alert = $user['blocks']['global_stdhead'] & class_blocks_stdhead::STDHEAD_NEWPM ? 'checked' : '';
$checkbox_global_staff_message_alert = $user['blocks']['global_stdhead'] & class_blocks_stdhead::STDHEAD_STAFF_MESSAGE ? 'checked' : '';
$checkbox_global_staff_report = $user['blocks']['global_stdhead'] & class_blocks_stdhead::STDHEAD_REPORTS ? 'checked' : '';
$checkbox_global_staff_uploadapp = $user['blocks']['global_stdhead'] & class_blocks_stdhead::STDHEAD_UPLOADAPP ? 'checked' : '';
$checkbox_global_happyhour = $user['blocks']['global_stdhead'] & class_blocks_stdhead::STDHEAD_HAPPYHOUR ? 'checked' : '';
$checkbox_global_crazyhour = $user['blocks']['global_stdhead'] & class_blocks_stdhead::STDHEAD_CRAZYHOUR ? 'checked' : '';
$checkbox_global_bugmessage = $user['blocks']['global_stdhead'] & class_blocks_stdhead::STDHEAD_BUG_MESSAGE ? 'checked' : '';
$checkbox_global_freeleech_contribution = $user['blocks']['global_stdhead'] & class_blocks_stdhead::STDHEAD_FREELEECH_CONTRIBUTION ? 'checked' : '';
//==Userdetails
$checkbox_userdetails_flush = $user['blocks']['userdetails_page'] & class_blocks_userdetails::FLUSH ? 'checked' : '';
$checkbox_userdetails_joined = $user['blocks']['userdetails_page'] & class_blocks_userdetails::JOINED ? 'checked' : '';
$checkbox_userdetails_onlinetime = $user['blocks']['userdetails_page'] & class_blocks_userdetails::ONLINETIME ? 'checked' : '';
$checkbox_userdetails_browser = $user['blocks']['userdetails_page'] & class_blocks_userdetails::BROWSER ? 'checked' : '';
$checkbox_userdetails_reputation = $user['blocks']['userdetails_page'] & class_blocks_userdetails::REPUTATION ? 'checked' : '';
$checkbox_userdetails_userhits = $user['blocks']['userdetails_page'] & class_blocks_userdetails::PROFILE_HITS ? 'checked' : '';
$checkbox_userdetails_birthday = $user['blocks']['userdetails_page'] & class_blocks_userdetails::BIRTHDAY ? 'checked' : '';
$checkbox_userdetails_contact_info = $user['blocks']['userdetails_page'] & class_blocks_userdetails::CONTACT_INFO ? 'checked' : '';
$checkbox_userdetails_avatar = $user['blocks']['userdetails_page'] & class_blocks_userdetails::AVATAR ? 'checked' : '';
$checkbox_userdetails_iphistory = $user['blocks']['userdetails_page'] & class_blocks_userdetails::IPHISTORY ? 'checked' : '';
$checkbox_userdetails_traffic = $user['blocks']['userdetails_page'] & class_blocks_userdetails::TRAFFIC ? 'checked' : '';
$checkbox_userdetails_shareratio = $user['blocks']['userdetails_page'] & class_blocks_userdetails::SHARE_RATIO ? 'checked' : '';
$checkbox_userdetails_seedtime_ratio = $user['blocks']['userdetails_page'] & class_blocks_userdetails::SEEDTIME_RATIO ? 'checked' : '';
$checkbox_userdetails_seedbonus = $user['blocks']['userdetails_page'] & class_blocks_userdetails::SEEDBONUS ? 'checked' : '';
$checkbox_userdetails_irc_stats = $user['blocks']['userdetails_page'] & class_blocks_userdetails::IRC_STATS ? 'checked' : '';
$checkbox_userdetails_connectable = $user['blocks']['userdetails_page'] & class_blocks_userdetails::CONNECTABLE_PORT ? 'checked' : '';
$checkbox_userdetails_userclass = $user['blocks']['userdetails_page'] & class_blocks_userdetails::USERCLASS ? 'checked' : '';
$checkbox_userdetails_gender = $user['blocks']['userdetails_page'] & class_blocks_userdetails::GENDER ? 'checked' : '';
$checkbox_userdetails_freestuffs = $user['blocks']['userdetails_page'] & class_blocks_userdetails::FREESTUFFS ? 'checked' : '';
$checkbox_userdetails_torrent_comments = $user['blocks']['userdetails_page'] & class_blocks_userdetails::COMMENTS ? 'checked' : '';
$checkbox_userdetails_forumposts = $user['blocks']['userdetails_page'] & class_blocks_userdetails::FORUMPOSTS ? 'checked' : '';
$checkbox_userdetails_invitedby = $user['blocks']['userdetails_page'] & class_blocks_userdetails::INVITEDBY ? 'checked' : '';
$checkbox_userdetails_torrents_block = $user['blocks']['userdetails_page'] & class_blocks_userdetails::TORRENTS_BLOCK ? 'checked' : '';
$checkbox_userdetails_completed = $user['blocks']['userdetails_page'] & class_blocks_userdetails::COMPLETED ? 'checked' : '';
$checkbox_userdetails_snatched_staff = $user['blocks']['userdetails_page'] & class_blocks_userdetails::SNATCHED_STAFF ? 'checked' : '';
$checkbox_userdetails_userinfo = $user['blocks']['userdetails_page'] & class_blocks_userdetails::USERINFO ? 'checked' : '';
$checkbox_userdetails_showpm = $user['blocks']['userdetails_page'] & class_blocks_userdetails::SHOWPM ? 'checked' : '';
$checkbox_userdetails_report = $user['blocks']['userdetails_page'] & class_blocks_userdetails::REPORT_USER ? 'checked' : '';
$checkbox_userdetails_userstatus = $user['blocks']['userdetails_page'] & class_blocks_userdetails::USERSTATUS ? 'checked' : '';
$checkbox_userdetails_usercomments = $user['blocks']['userdetails_page'] & class_blocks_userdetails::USERCOMMENTS ? 'checked' : '';
$checkbox_userdetails_showfriends = $user['blocks']['userdetails_page'] & class_blocks_userdetails::SHOWFRIENDS ? 'checked' : '';

$form = $level1 = $level2 = '';
$contents = [];
$form .= "
    <form action='' method='post' enctype='multipart/form-data' accept-charset='utf-8'>
        <div class='bg-02'>
        <fieldset id='user_blocks_home' class='header'>
            <legend class='flipper has-text-primary padding20 left10'><i class='icon-down-open size_4 right5' aria-hidden='true'></i>Home Page Settings</legend>
            <div>";

$level1 .= "
                <div class='level-center'>";
if ($BLOCKS['ie_user_alert']) {
    $contents[] = "
                <div class='w-100 has-text-centered'>" . _('Enable IE alert?') . "</div>
                <div class='slideThree'>
                    <input type='checkbox' id='ie_alert' name='ie_alert' value='yes' $checkbox_index_ie_alert>
                    <label for='ie_alert'></label>
                </div>
                <div class='w-100 has-text-centered'>" . _('Check this option if you want to enable the IE user alert.') . '</div>';
}

if ($BLOCKS['news_on']) {
    $contents[] = "
                <div class='w-100 has-text-centered'>" . _('Enable News?') . "</div>
                <div class='slideThree'>
                    <input type='checkbox' id='news' name='news' value='yes' $checkbox_index_news>
                    <label for='news'></label>
                </div>
                <div class='w-100 has-text-centered'>" . _('Check this option if you want to enable the News Block.') . '</div>';
}

if ($BLOCKS['ajaxchat_on']) {
    $contents[] = "
                <div class='w-100 has-text-centered'>" . _('Enable AJAX Chat?') . "</div>
                <div class='slideThree'>
                    <input type='checkbox' id='ajaxchat' name='ajaxchat' value='yes' $checkbox_index_ajaxchat>
                    <label for='ajaxchat'></label>
                </div>
                <div class='w-100 has-text-centered'>" . _('Check this option if you want to enable the AJAX Chat.') . '</div>';
}

if ($BLOCKS['active_users_on']) {
    $contents[] = "
                <div class='w-100 has-text-centered'>" . _('Enable Active Users?') . "</div>
                <div class='slideThree'>
                    <input type='checkbox' id='active_users' name='active_users' value='yes' $checkbox_index_active_users>
                    <label for='active_users'></label>
                </div>
                <div class='w-100 has-text-centered'>" . _('Check this option if you want to enable the Active Users.') . '</div>';
}

if ($BLOCKS['active_24h_users_on']) {
    $contents[] = "
                <div class='w-100 has-text-centered'>" . _('Enable Active Users Over 24hours?') . "</div>
                <div class='slideThree'>
                    <input type='checkbox' id='last_24_active_users' name='last_24_active_users' value='yes' $checkbox_index_active_24h_users>
                    <label for='last_24_active_users'></label>
                </div>
                <div class='w-100 has-text-centered'>" . _('Check this option if you want to enable the Active Users visited over 24hours.') . '</div>';
}

if ($BLOCKS['cooker_on']) {
    $contents[] = "
                <div class='w-100 has-text-centered'>" . _('Enable Cooker?') . "</div>
                <div class='slideThree'>
                    <input type='checkbox' id='cooker' name='cooker' value='yes' $checkbox_index_cooker>
                    <label for='cooker'></label>
                </div>
                <div class='w-100 has-text-centered'>" . _('Check this option if you want to enable the Cooker.') . '</div>';
}

if ($BLOCKS['requests_on']) {
    $contents[] = "
                <div class='w-100 has-text-centered'>" . _('Enable Requests?') . "</div>
                <div class='slideThree'>
                    <input type='checkbox' id='requests' name='requests' value='yes' $checkbox_index_requests>
                    <label for='requests'></label>
                </div>
                <div class='w-100 has-text-centered'>" . _('Check this option if you want to enable the Requests.') . '</div>';
}

if ($BLOCKS['offers_on']) {
    $contents[] = "
                <div class='w-100 has-text-centered'>" . _('Enable Offers?') . "</div>
                <div class='slideThree'>
                    <input type='checkbox' id='offers' name='offers' value='yes' $checkbox_index_offers>
                    <label for='offers'></label>
                </div>
                <div class='w-100 has-text-centered'>" . _('Check this option if you want to enable the Cooker.') . '</div>';
}

if ($BLOCKS['active_irc_users_on']) {
    $contents[] = "
                <div class='w-100 has-text-centered'>" . _('Enable Active Irc Users?') . "</div>
                <div class='slideThree'>
                    <input type='checkbox' id='irc_active_users' name='irc_active_users' value='yes' $checkbox_index_active_irc_users>
                    <label for='irc_active_users'></label>
                </div>
                <div class='w-100 has-text-centered'>" . _('Check this option if you want to enable the Active Irc Users.') . '</div>';
}

if ($BLOCKS['active_birthday_users_on']) {
    $contents[] = "
                <div class='w-100 has-text-centered'>" . _('Enable Birthday Users?') . "</div>
                <div class='slideThree'>
                    <input type='checkbox' id='birthday_active_users' name='birthday_active_users' value='yes' $checkbox_index_active_birthday_users>
                    <label for='birthday_active_users'></label>
                </div>
                <div class='w-100 has-text-centered'>" . _('Check this option if you want to enable the Active Birthday Users.') . '</div>';
}

if ($BLOCKS['stats_on']) {
    $contents[] = "
                <div class='w-100 has-text-centered'>" . _('Enable Site Stats?') . "</div>
                <div class='slideThree'>
                    <input type='checkbox' id='stats' name='stats' value='yes' $checkbox_index_stats>
                <label for='stats'></label></div>
                <div class='w-100 has-text-centered'>" . _('Check this option if you want to enable the Stats.') . '</div>';
}

if ($BLOCKS['trivia_on']) {
    $contents[] = "
                <div class='w-100 has-text-centered'>" . _('Enable Trivia?') . "</div>
                <div class='slideThree'>
                    <input type='checkbox' id='trivia' name='trivia' value='yes' $checkbox_index_trivia>
                    <label for='trivia'></label>
                </div>
                <div class='w-100 has-text-centered'>" . _('Check this option if you want to enable the Trivia Game.') . '</div>';
}

if ($BLOCKS['disclaimer_on']) {
    $contents[] = "
                <div class='w-100 has-text-centered'>" . _('Enable Disclaimer?') . "</div>
                <div class='slideThree'>
                    <input type='checkbox' id='disclaimer' name='disclaimer' value='yes' $checkbox_index_disclaimer>
                    <label for='disclaimer'></label>
                </div>
                <div class='w-100 has-text-centered'>" . _('Check this option if you want to enable Disclaimer.') . '</div>';
}

if ($BLOCKS['latest_user_on']) {
    $contents[] = "
                <div class='w-100 has-text-centered'>" . _('Enable Latest User?') . "</div>
                <div class='slideThree'>
                    <input type='checkbox' id='latest_user' name='latest_user' value='yes' $checkbox_index_latest_user>
                    <label for='latest_user'></label>
                </div>
                <div class='w-100 has-text-centered'>" . _('Check this option if you want to enable Latest User.') . '</div>';
}

if ($BLOCKS['latest_comments_on']) {
    $contents[] = "
                <div class='w-100 has-text-centered'>" . _('Enable Latest Comments?') . "</div>
                <div class='slideThree'>
                    <input type='checkbox' id='latestcomments' name='latestcomments' value='yes' $checkbox_index_latest_comments>
                    <label for='latestcomments'></label>
                </div>
                <div class='w-100 has-text-centered'>" . _('Check this option if you want to enable latest Comments.') . '</div>';
}

if ($BLOCKS['forum_posts_on']) {
    $contents[] = "
                <div class='w-100 has-text-centered'>" . _('Enable Latest Forum Posts?') . "</div>
                <div class='slideThree'>
                    <input type='checkbox' id='forumposts' name='forumposts' value='yes' $checkbox_index_latest_forumposts>
                    <label for='forumposts'></label>
                </div>
                <div class='w-100 has-text-centered'>" . _('Check this option if you want to enable latest Forum Posts.') . '</div>';
}

if ($BLOCKS['staff_picks_on']) {
    $contents[] = "
                <div class='w-100 has-text-centered'>" . _('Enable Staff Picks?') . "</div>
                <div class='slideThree'>
                    <input type='checkbox' id='staff_picks' name='staff_picks' value='yes' $checkbox_index_staff_picks>
                    <label for='staff_picks'></label>
                </div>
                <div class='w-100 has-text-centered'>" . _('Check this option if you want to enable staff_picks.') . '</div>';
}

if ($BLOCKS['latest_torrents_on']) {
    $contents[] = "
                <div class='w-100 has-text-centered'>" . _('Enable Latest Torrents?') . "</div>
                <div class='slideThree'>
                    <input type='checkbox' id='latest_torrents' name='latest_torrents' value='yes' $checkbox_index_latest_torrents>
                    <label for='latest_torrents'></label>
                </div>
                <div class='w-100 has-text-centered'>" . _('Check this option if you want to enable latest torrents.') . '</div>';
}

if ($BLOCKS['latest_movies_on']) {
    $contents[] = "
                <div class='w-100 has-text-centered'>" . _('Enable Latest Movie Torrents?') . "</div>
                <div class='slideThree'>
                    <input type='checkbox' id='latest_movies' name='latest_movies' value='yes' $checkbox_index_latest_movies>
                    <label for='latest_movies'></label>
                </div>
                <div class='w-100 has-text-centered'>" . _('Check this option if you want to enable latest movie torrents.') . '</div>';
}

if ($BLOCKS['latest_tv_on']) {
    $contents[] = "
                <div class='w-100 has-text-centered'>" . _('Enable Latest TV Torrents?') . "</div>
                <div class='slideThree'>
                    <input type='checkbox' id='latest_tv' name='latest_tv' value='yes' $checkbox_index_latest_tv>
                    <label for='latest_tv'></label>
                </div>
                <div class='w-100 has-text-centered'>" . _('Check this option if you want to enable latest tv torrents.') . '</div>';
}

if ($BLOCKS['latest_torrents_scroll_on']) {
    $contents[] = "
                <div class='w-100 has-text-centered'>" . _('Enable Latest Torrents scroll?') . "</div>
                <div class='slideThree'>
                    <input type='checkbox' id='latest_torrents_scroll' name='latest_torrents_scroll' value='yes' $checkbox_index_latest_torrents_scroll>
                    <label for='latest_torrents_scroll'></label>
                </div>
                <div class='w-100 has-text-centered'>" . _('Check this option if you want to enable latest torrents marquee.') . '</div>';
}

if ($BLOCKS['latest_torrents_slider_on']) {
    $contents[] = "
                <div class='w-100 has-text-centered'>" . _('Enable Latest Torrents slider?') . "</div>
                <div class='slideThree'>
                    <input type='checkbox' id='latest_torrents_slider' name='latest_torrents_slider' value='yes' $checkbox_index_latest_torrents_slider>
                    <label for='latest_torrents_slider'></label>
                </div>
                <div class='w-100 has-text-centered'>" . _('Check this option if you want to enable latest torrents banner slider.') . '</div>';
}

if ($BLOCKS['announcement_on']) {
    $contents[] = "
                <div class='w-100 has-text-centered'>" . _('Enable Announcement?') . "</div>
                <div class='slideThree'>
                    <input type='checkbox' id='announcement' name='announcement' value='yes' $checkbox_index_announcement>
                    <label for='announcement'></label>
                </div>
                <div class='w-100 has-text-centered'>" . _('Check this option if you want to enable the Announcement Block.') . '</div>';
}

if ($BLOCKS['donation_progress_on']) {
    $contents[] = "
                <div class='w-100 has-text-centered'>" . _('Enable Donation Progress?') . "</div>
                <div class='slideThree'>
                    <input type='checkbox' id='donation_progress' name='donation_progress' value='yes' $checkbox_index_donation_progress>
                    <label for='donation_progress'></label>
                </div>
                <div class='w-100 has-text-centered'>" . _('Check this option if you want to enable the Donation Progress.') . '</div>';
}

if ($BLOCKS['ads_on']) {
    $contents[] = "
                <div class='w-100 has-text-centered'>" . _('Enable Advertisements?') . "</div>
                <div class='slideThree'>
                    <input type='checkbox' id='advertisements' name='advertisements' value='yes' $checkbox_index_ads>
                    <label for='advertisements'></label>
                </div>
                <div class='w-100 has-text-centered'>" . _('Check this option if you want to enable the Advertisements.') . '</div>';
}

if ($BLOCKS['torrentfreak_on']) {
    $contents[] = "
                <div class='w-100 has-text-centered'>" . _('Enable Torrent Freak?') . "</div>
                <div class='slideThree'>
                    <input type='checkbox' id='torrentfreak' name='torrentfreak' value='yes' $checkbox_index_torrentfreak>
                    <label for='torrentfreak'></label>
                </div>
                <div class='w-100 has-text-centered'>" . _('Check this option if you want to enable the torrent freak news.') . '</div>';
}

if ($BLOCKS['christmas_gift_on']) {
    $contents[] = "
                <div class='w-100 has-text-centered'>" . _('Enable Christmas Gift?') . "</div>
                <div class='slideThree'>
                    <input type='checkbox' id='christmas_gift' name='christmas_gift' value='yes' $checkbox_index_christmasgift>
                    <label for='christmas_gift'></label>
                </div>
                <div class='w-100 has-text-centered'>" . _('Check this option if you want to enable the Christmas Gift.') . '</div>';
}

if ($BLOCKS['active_poll_on']) {
    $contents[] = "
                <div class='w-100 has-text-centered'>" . _('Enable Poll?') . "</div>
                <div class='slideThree'>
                    <input type='checkbox' id='active_poll' name='active_poll' value='yes' $checkbox_index_active_poll>
                    <label for='active_poll'></label>
                </div>
                <div class='w-100 has-text-centered'>" . _('Check this option if you want to enable the Active Poll.') . '</div>';
}

if ($BLOCKS['movie_ofthe_week_on']) {
    $contents[] = "
                <div class='w-100 has-text-centered'>" . _('Enable Movie of the week?') . "</div>
                <div class='slideThree'>
                    <input type='checkbox' id='movie_ofthe_week' name='movie_ofthe_week' value='yes' $checkbox_index_mow>
                    <label for='movie_ofthe_week'></label>
                </div>
                <div class='w-100 has-text-centered'>" . _('Check this option if you want to enable the MOvie of the week.') . '</div>';
}

foreach ($contents as $content) {
    $level1 .= "
                <div class='margin10 w-15 min-150'>
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
                        <input class='button is-small' type='submit' name='submit' value='Submit'>
                    </div>
        </fieldset>
        </div>
        <div class='bg-02 top20'>
        <fieldset id='user_blocks_site' class='header'>
            <legend class='flipper has-text-primary padding20 left10'><i class='icon-down-open size_4 right5' aria-hidden='true'></i>Site Alert Settings</legend>
            <div>";

$level2 .= "
                <div class='level-center'>";

$contents = [];
if ($BLOCKS['global_freeleech_on']) {
    $contents[] = "
                <div class='w-100 has-text-centered'>" . _('Freeleech?') . "</div>
                <div class='slideThree'>
                    <input type='checkbox' id='stdhead_freeleech' name='stdhead_freeleech' value='yes' $checkbox_global_freeleech>
                    <label for='stdhead_freeleech'></label>
                </div>
                <div class='w-100 has-text-centered'>" . _("Enable 'freeleech mark' in stdhead") . '</div>';
}
if ($user['class'] >= UC_STAFF) {
    if ($BLOCKS['global_staff_report_on']) {
        $contents[] = "
                    <div class='w-100 has-text-centered'>" . _('Staff Reports?') . "</div>
                    <div class='slideThree'>
                        <input type='checkbox' id='stdhead_reports' name='stdhead_reports' value='yes' $checkbox_global_staff_report>
                        <label for='stdhead_reports'></label>
                    </div>
                    <div class='w-100 has-text-centered'>" . _('Enable reports alert in stdhead') . '</div>';
    }

    if ($BLOCKS['global_staff_uploadapp_on']) {
        $contents[] = "
                    <div class='w-100 has-text-centered'>" . _('Upload App Alert?') . "</div>
                    <div class='slideThree'>
                        <input type='checkbox' id='stdhead_uploadapp' name='stdhead_uploadapp' value='yes' $checkbox_global_staff_uploadapp>
                        <label for='stdhead_uploadapp'></label>
                    </div>
                    <div class='w-100 has-text-centered'>" . _('Enable upload application alerts in stdhead') . '</div>';
    }

    if ($BLOCKS['global_demotion_on']) {
        $contents[] = "
                    <div class='w-100 has-text-centered'>" . _('Demotion') . "</div>
                    <div class='slideThree'>
                        <input type='checkbox' id='stdhead_demotion' name='stdhead_demotion' value='yes' $checkbox_global_demotion>
                        <label for='stdhead_demotion'></label>
                    </div>
                    <div class='w-100 has-text-centered'>" . _('Enable the global demotion alerts block in stdhead') . '</div>';
    }

    if ($BLOCKS['global_staff_warn_on']) {
        $contents[] = "
                    <div class='w-100 has-text-centered'>" . _('Staff Warning?') . "</div>
                    <div class='slideThree'>
                        <input type='checkbox' id='stdhead_staff_message' name='stdhead_staff_message' value='yes' $checkbox_global_staff_message_alert> 
                        <label for='stdhead_staff_message'></label>
                    </div>
                    <div class='w-100 has-text-centered'>" . _('Shows if there is a new message for staff alert in stdhead') . '</div>';
    }

    if ($BLOCKS['global_bug_message_on']) {
        $contents[] = "
                    <div class='w-100 has-text-centered'>" . _('Bug Alert Message?') . "</div>
                    <div class='slideThree'>   
                        <input type='checkbox' id='stdhead_bugmessage' name='stdhead_bugmessage' value='yes' $checkbox_global_bugmessage>
                        <label for='stdhead_bugmessage'></label>
                    </div>
                    <div class='w-100 has-text-centered'>" . _('Enable Bug Message alerts in stdhead') . '</div>';
    }
}

if ($BLOCKS['global_message_on']) {
    $contents[] = "
                <div class='w-100 has-text-centered'>" . _('Message block?') . "</div>
                <div class='slideThree'>
                    <input type='checkbox' id='stdhead_newpm' name='stdhead_newpm' value='yes' $checkbox_global_message_alert>
                    <label for='stdhead_newpm'></label>
                    </div>
                <div class='w-100 has-text-centered'>" . _('Enable message alerts in stdhead') . '</div>';
}

if ($BLOCKS['global_happyhour_on']) {
    $contents[] = "
                <div class='w-100 has-text-centered'>" . _('Happyhour?') . "</div>
                <div class='slideThree'>
                    <input type='checkbox' id='stdhead_happyhour' name='stdhead_happyhour' value='yes' $checkbox_global_happyhour>
                    <label for='stdhead_happyhour'></label>
                </div>
                <div class='w-100 has-text-centered'>" . _('Enable happy hour alerts in stdhead') . '</div>';
}

if ($BLOCKS['global_crazyhour_on']) {
    $contents[] = "
                <div class='w-100 has-text-centered'>" . _('CrazyHour?') . "</div>
                <div class='slideThree'>
                    <input type='checkbox' id='stdhead_crazyhour' name='stdhead_crazyhour' value='yes' $checkbox_global_crazyhour>
                    <label for='stdhead_crazyhour'></label></div>
                <div class='w-100 has-text-centered'>" . _('Enable crazyhour alerts in stdhead') . '</div>';
}

if ($BLOCKS['global_freeleech_contribution_on']) {
    $contents[] = "
                <div class='w-100 has-text-centered'>" . _('Karma Contributions') . "</div>
                <div class='slideThree'>
                    <input type='checkbox' id='stdhead_freeleech_contribution' name='stdhead_freeleech_contribution' value='yes' $checkbox_global_freeleech_contribution>
                    <label for='stdhead_freeleech_contribution'></label>
                </div>
                <div class='w-100 has-text-centered'>" . _('Enable karma contribution status alert in stdhead') . '</div>';
}
foreach ($contents as $content) {
    $level2 .= "
                <div class='margin10 w-15 min-150'>
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
                        <input class='button is-small' type='submit' name='submit' value='Submit'>
                    </div>
        </fieldset>
        </div>
        <div class='bg-02 top20'>
        <fieldset id='user_blocks_user' class='header'>
            <legend class='flipper has-text-primary padding20 left10'><i class='icon-down-open size_4 right5' aria-hidden='true'></i>Userdetails Page Settings</legend>
            <div>";

$level3 = "
                <div class='level-center'>";

$contents = [];

if ($BLOCKS['userdetails_flush_on']) {
    $contents[] = "
                <div class='w-100 has-text-centered'>" . _('Flush torrents?') . "</div>
                <div class='slideThree'><input type='checkbox' id='userdetails_flush' name='userdetails_flush' value='yes' $checkbox_userdetails_flush>
                <label for='userdetails_flush'></label></div>
                <div class='w-100 has-text-centered'>" . _('Enable flush torrents') . '</div>';
}

if ($BLOCKS['userdetails_joined_on']) {
    $contents[] = "
                <div class='w-100 has-text-centered'>" . _('Join date?') . "</div>
                <div class='slideThree'><input type='checkbox' id='userdetails_joined' name='userdetails_joined' value='yes' $checkbox_userdetails_joined>
                <label for='userdetails_joined'></label></div>
                <div class='w-100 has-text-centered'>" . _('Enable join date') . '</div>';
}

if ($BLOCKS['userdetails_online_time_on']) {
    $contents[] = "
                <div class='w-100 has-text-centered'>" . _('Online time?') . "</div>
                <div class='slideThree'><input type='checkbox' id='userdetails_online_time' name='userdetails_online_time' value='yes' $checkbox_userdetails_onlinetime>
                <label for='userdetails_online_time'></label></div>
                <div class='w-100 has-text-centered'>" . _('Enable online time') . '</div>';
}

if ($BLOCKS['userdetails_browser_on']) {
    $contents[] = "
                <div class='w-100 has-text-centered'>" . _('Browser?') . "</div>
                <div class='slideThree'><input type='checkbox' id='userdetails_browser' name='userdetails_browser' value='yes' $checkbox_userdetails_browser>
                <label for='userdetails_browser'></label></div>
                <div class='w-100 has-text-centered'>" . _('Enable browser and os detection') . '</div>';
}

if ($BLOCKS['userdetails_reputation_on']) {
    $contents[] = "
                <div class='w-100 has-text-centered'>" . _('Reputation?') . "</div>
                <div class='slideThree'><input type='checkbox' id='userdetails_reputation' name='userdetails_reputation' value='yes' $checkbox_userdetails_reputation>
                <label for='userdetails_reputation'></label></div>
                <div class='w-100 has-text-centered'>" . _('Enable add reputation link') . '</div>';
}

if ($BLOCKS['userdetails_profile_hits_on']) {
    $contents[] = "
                <div class='w-100 has-text-centered'>" . _('Profile hits?') . "</div>
                <div class='slideThree'><input type='checkbox' id='userdetails_user_hits' name='userdetails_user_hits' value='yes' $checkbox_userdetails_userhits>
                <label for='userdetails_user_hits'></label></div>
                <div class='w-100 has-text-centered'>" . _('Enable user hits') . '</div>';
}

if ($BLOCKS['userdetails_birthday_on']) {
    $contents[] = "
                <div class='w-100 has-text-centered'>" . _('Birthday?') . "</div>
                <div class='slideThree'><input type='checkbox' id='userdetails_birthday' name='userdetails_birthday' value='yes' $checkbox_userdetails_birthday>
                <label for='userdetails_birthday'></label></div>
                <div class='w-100 has-text-centered'>" . _('Enable birthdate and age') . '</div>';
}

if ($BLOCKS['userdetails_contact_info_on']) {
    $contents[] = "
                <div class='w-100 has-text-centered'>" . _('Contact?') . "</div>
                <div class='slideThree'><input type='checkbox' id='userdetails_contact_info' name='userdetails_contact_info' value='yes' $checkbox_userdetails_contact_info>
                <label for='userdetails_contact_info'></label></div>
                <div class='w-100 has-text-centered'>" . _('Enable contact infos') . '</div>';
}

if ($BLOCKS['userdetails_avatar_on']) {
    $contents[] = "
                <div class='w-100 has-text-centered'>" . _('Avatar?') . "</div>
                <div class='slideThree'><input type='checkbox' id='userdetails_avatar' name='userdetails_avatar' value='yes' $checkbox_userdetails_avatar>
                <label for='userdetails_avatar'></label></div>
                <div class='w-100 has-text-centered'>" . _('Enable Avatar') . '</div>';
}

if ($BLOCKS['userdetails_iphistory_on']) {
    $contents[] = "
                <div class='w-100 has-text-centered'>" . _('IP history?') . "</div>
                <div class='slideThree'><input type='checkbox' id='userdetails_iphistory' name='userdetails_iphistory' value='yes' $checkbox_userdetails_iphistory>
                <label for='userdetails_iphistory'></label></div>
                <div class='w-100 has-text-centered'>" . _('Enable ip history lists') . '</div>';
}

if ($BLOCKS['userdetails_traffic_on']) {
    $contents[] = "
                <div class='w-100 has-text-centered'>" . _('User traffic?') . "</div>
                <div class='slideThree'><input type='checkbox' id='userdetails_traffic' name='userdetails_traffic' value='yes' $checkbox_userdetails_traffic>
                <label for='userdetails_traffic'></label></div>
                <div class='w-100 has-text-centered'>" . _('Enable uploaded and download') . '</div>';
}

if ($BLOCKS['userdetails_share_ratio_on']) {
    $contents[] = "
                <div class='w-100 has-text-centered'>" . _('Share ratio?') . "</div>
                <div class='slideThree'><input type='checkbox' id='userdetails_share_ratio' name='userdetails_share_ratio' value='yes' $checkbox_userdetails_shareratio>
                <label for='userdetails_share_ratio'></label></div>
                <div class='w-100 has-text-centered'>" . _('Enable share ratio') . '</div>';
}

if ($BLOCKS['userdetails_seedtime_ratio_on']) {
    $contents[] = "
                <div class='w-100 has-text-centered'>" . _('Seed time ratio?') . "</div>
                <div class='slideThree'><input type='checkbox' id='userdetails_seedtime_ratio' name='userdetails_seedtime_ratio' value='yes' $checkbox_userdetails_seedtime_ratio>
                <label for='userdetails_seedtime_ratio'></label></div>
                <div class='w-100 has-text-centered'>" . _('Enable seed time per torrent average ratio') . '</div>';
}

if ($BLOCKS['userdetails_seedbonus_on']) {
    $contents[] = "
                <div class='w-100 has-text-centered'>" . _('Seedbonus?') . "</div>
                <div class='slideThree'><input type='checkbox' id='userdetails_seedbonus' name='userdetails_seedbonus' value='yes' $checkbox_userdetails_seedbonus>
                <label for='userdetails_seedbonus'></label></div>
                <div class='w-100 has-text-centered'>" . _('Enable seedbonus') . '</div>';
}

if ($BLOCKS['userdetails_irc_stats_on']) {
    $contents[] = "
                <div class='w-100 has-text-centered'>" . _('IRC stats?') . "</div>
                <div class='slideThree'><input type='checkbox' id='userdetails_irc_stats' name='userdetails_irc_stats' value='yes' $checkbox_userdetails_irc_stats>
                <label for='userdetails_irc_stats'></label></div>
                <div class='w-100 has-text-centered'>" . _('Enable irc online stats') . '</div>';
}

if ($BLOCKS['userdetails_connectable_port_on']) {
    $contents[] = "
                <div class='w-100 has-text-centered'>" . _('Connectable?') . "</div>
                <div class='slideThree'><input type='checkbox' id='userdetails_connectable_port' name='userdetails_connectable_port' value='yes' $checkbox_userdetails_connectable>
                <label for='userdetails_connectable_port'></label></div>
                <div class='w-100 has-text-centered'>" . _('Enable connectable and port') . '</div>';
}

if ($BLOCKS['userdetails_userclass_on']) {
    $contents[] = "
                 <div class='w-100 has-text-centered'>" . _('Userclass?') . "</div>
                <div class='slideThree'><input type='checkbox' id='userdetails_userclass' name='userdetails_userclass' value='yes' $checkbox_userdetails_userclass>
                <label for='userdetails_userclass'></label></div>
                 <div class='w-100 has-text-centered'>" . _('Enable userclass') . '</div>';
}

if ($BLOCKS['userdetails_gender_on']) {
    $contents[] = "
                <div class='w-100 has-text-centered'>" . _('Gender?') . "</div>
                <div class='slideThree'><input type='checkbox' id='userdetails_gender' name='userdetails_gender' value='yes' $checkbox_userdetails_gender>
                <label for='userdetails_gender'></label></div>
                <div class='w-100 has-text-centered'>" . _('Enable gender') . '</div>';
}

if ($BLOCKS['userdetails_freestuffs_on']) {
    $contents[] = "
                <div class='w-100 has-text-centered'>" . _('Free stuffs?') . "</div>
                <div class='slideThree'><input type='checkbox' id='userdetails_freestuffs' name='userdetails_freestuffs' value='yes' $checkbox_userdetails_freestuffs>
                <label for='userdetails_freestuffs'></label></div>
                <div class='w-100 has-text-centered'>" . _('Enable freeslots and freeleech status') . '</div>';
}

if ($BLOCKS['userdetails_comments_on']) {
    $contents[] = "
                <div class='w-100 has-text-centered'>" . _('Comments?') . "</div>
                <div class='slideThree'><input type='checkbox' id='userdetails_comments' name='userdetails_comments' value='yes' $checkbox_userdetails_torrent_comments>
                <label for='userdetails_comments'></label></div>
                <div class='w-100 has-text-centered'>" . _('Enable torrent comments history') . '</div>';
}

if ($BLOCKS['userdetails_forumposts_on']) {
    $contents[] = "
                <div class='w-100 has-text-centered'>" . _('Forumposts?') . "</div>
                <div class='slideThree'><input type='checkbox' id='userdetails_forumposts' name='userdetails_forumposts' value='yes' $checkbox_userdetails_forumposts>
                <label for='userdetails_forumposts'></label></div>
                <div class='w-100 has-text-centered'>" . _('Enable forum posts history') . '</div>';
}

if ($BLOCKS['userdetails_invitedby_on']) {
    $contents[] = "
                <div class='w-100 has-text-centered'>" . _('Invited by?') . "</div>
                <div class='slideThree'><input type='checkbox' id='userdetails_invitedby' name='userdetails_invitedby' value='yes' $checkbox_userdetails_invitedby>
                <label for='userdetails_invitedby'></label></div>
                <div class='w-100 has-text-centered'>" . _('Enable invited by list') . '</div>';
}

if ($BLOCKS['userdetails_torrents_block_on']) {
    $contents[] = "
                <div class='w-100 has-text-centered'>" . _('Torrents blocks?') . "</div>
                <div class='slideThree'><input type='checkbox' id='userdetails_torrents_block' name='userdetails_torrents_block' value='yes' $checkbox_userdetails_torrents_block>
                <label for='userdetails_torrents_block'></label></div>
                <div class='w-100 has-text-centered'>" . _('Enable seeding, leeching, snatched and uploaded torrents') . '</div>';
}

if ($BLOCKS['userdetails_snatched_staff_on']) {
    $contents[] = "
                <div class='w-100 has-text-centered'>" . _('Staff snatched?') . "</div>
                <div class='slideThree'><input type='checkbox' id='userdetails_snatched_staff' name='userdetails_snatched_staff' value='yes' $checkbox_userdetails_snatched_staff>
                <label for='userdetails_snatched_staff'></label></div>
                <div class='w-100 has-text-centered'>" . _('Enable staff snatchlist') . '</div>';
}

if ($BLOCKS['userdetails_userinfo_on']) {
    $contents[] = "
                <div class='w-100 has-text-centered'>" . _('User info?') . "</div>
                <div class='slideThree'><input type='checkbox' id='userdetails_userinfo' name='userdetails_userinfo' value='yes' $checkbox_userdetails_userinfo>
                <label for='userdetails_userinfo'></label></div>
                <div class='w-100 has-text-centered'>" . _('Enable user info') . '</div>';
}

if ($BLOCKS['userdetails_showpm_on']) {
    $contents[] = "
                <div class='w-100 has-text-centered'>" . _('Show PM?') . "</div>
                <div class='slideThree'>
                    <input type='checkbox' id='userdetails_showpm' name='userdetails_showpm' value='yes' $checkbox_userdetails_showpm>
                    <label for='userdetails_showpm'></label>
                </div>
                <div class='w-100 has-text-centered'>" . _('Enable send message button') . '</div>';
}

if ($BLOCKS['userdetails_showfriends_on']) {
    $contents[] = "
                <div class='w-100 has-text-centered'>" . _('Show Friends') . "</div>
                <div class='slideThree'>
                    <input type='checkbox' id='userdetails_show_friends' name='userdetails_show_friends' value='yes' $checkbox_userdetails_showfriends>
                <label for='userdetails_show_friends'></label></div>
                <div class='w-100 has-text-centered'>" . _('Enable show friends button') . '</div>';
}

if ($BLOCKS['userdetails_report_user_on']) {
    $contents[] = "
                <div class='w-100 has-text-centered'>" . _('Report user?') . "</div>
                <div class='slideThree'>
                    <input type='checkbox' id='userdetails_report_user' name='userdetails_report_user' value='yes' $checkbox_userdetails_report>
                    <label for='userdetails_report_user'></label>
                </div>
                <div class='w-100 has-text-centered'>" . _('Enable report users button') . '</div>';
}

if ($BLOCKS['userdetails_user_status_on']) {
    $contents[] = "
                <div class='w-100 has-text-centered'>" . _('User status?') . "</div>
                <div class='slideThree'><input type='checkbox' id='userdetails_user_status' name='userdetails_user_status' value='yes' $checkbox_userdetails_userstatus>
                <label for='userdetails_user_status'></label></div>
                <div class='w-100 has-text-centered'>" . _('Enable user status') . '</div>';
}

if ($BLOCKS['userdetails_user_comments_on']) {
    $contents[] = "
                <div class='w-100 has-text-centered'>" . _('User comments?') . "</div>
                <div class='slideThree'><input type='checkbox' id='userdetails_user_comments' name='userdetails_user_comments' value='yes' $checkbox_userdetails_usercomments>
                <label for='userdetails_user_comments'></label></div>
                <div class='w-100 has-text-centered'>" . _('Enable user comments') . '</div>';
}
if ($user['class'] >= UC_STAFF) {
    if ($BLOCKS['news_on']) {
        $contents[] = "
        <div class='w-100 has-text-centered'>" . _('Completed?') . "</div>
        <div class='slideThree'><input type='checkbox' id='userdetails_completed' name='userdetails_completed' value='yes' $checkbox_userdetails_completed>
        <label for='userdetails_completed'></label></div>
        <div class='w-100 has-text-centered'>" . _('Enable completed torrents') . '</div>';
    }
}

foreach ($contents as $content) {
    $level3 .= "
                <div class='margin10 w-15 min-150'>
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
                        <input class='button is-small' type='submit' name='submit' value='Submit'>
                    </div>
        </fieldset>
        </div>";

$form .= '
    </form>';

$HTMLOUT = wrapper($form);
$title = _('User Blocks');
$breadcrumbs = [
    "<a href='{$_SERVER['PHP_SELF']}'>$title</a>",
];
echo stdhead($title, [], 'page-wrapper', $breadcrumbs) . wrapper($HTMLOUT) . stdfoot();
