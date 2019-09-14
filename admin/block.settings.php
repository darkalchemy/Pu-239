<?php

declare(strict_types = 1);

use Pu239\Session;

require_once CLASS_DIR . 'class_check.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
$lang = array_merge($lang, load_language('ad_block_settings'));

$list = [
    'ie_user_alert',
    'news_on',
    'ajaxchat_on',
    'trivia_on',
    'active_users_on',
    'active_24h_users_on',
    'active_irc_users_on',
    'active_birthday_users_on',
    'stats_on',
    'disclaimer_on',
    'latest_user_on',
    'latest_comments_on',
    'forum_posts_on',
    'staff_picks_on',
    'latest_torrents_on',
    'latest_movies_on',
    'latest_tv_on',
    'latest_torrents_scroll_on',
    'latest_torrents_slider_on',
    'announcement_on',
    'donation_progress_on',
    'ads_on',
    'torrentfreak_on',
    'christmas_gift_on',
    'active_poll_on',
    'movie_ofthe_week_on',
    'global_freeleech_on',
    'global_demotion_on',
    'global_message_on',
    'global_staff_warn_on',
    'global_staff_report_on',
    'global_staff_uploadapp_on',
    'global_happyhour_on',
    'global_crazyhour_on',
    'global_bug_message_on',
    'global_freeleech_contribution_on',
    'global_staff_menu_on',
    'global_flash_messages_on',
    'userdetails_flush_on',
    'userdetails_joined_on',
    'userdetails_online_time_on',
    'userdetails_browser_on',
    'userdetails_reputation_on',
    'userdetails_profile_hits_on',
    'userdetails_birthday_on',
    'userdetails_contact_info_on',
    'userdetails_iphistory_on',
    'userdetails_traffic_on',
    'userdetails_share_ratio_on',
    'userdetails_seedtime_ratio_on',
    'userdetails_seedbonus_on',
    'userdetails_irc_stats_on',
    'userdetails_connectable_port_on',
    'userdetails_avatar_on',
    'userdetails_userclass_on',
    'userdetails_gender_on',
    'userdetails_freestuffs_on',
    'userdetails_comments_on',
    'userdetails_forumposts_on',
    'userdetails_invitedby_on',
    'userdetails_torrents_block_on',
    'userdetails_completed_on',
    'userdetails_snatched_staff_on',
    'userdetails_userinfo_on',
    'userdetails_showpm_on',
    'userdetails_report_user_on',
    'userdetails_user_status_on',
    'userdetails_user_comments_on',
    'userdetails_showfriends_on',
    'fanart_api_on',
    'tmdb_api_on',
    'imdb_api_on',
    'bluray_com_api_on',
    'google_books_api_on',
    'tvmaze_api_on',
    'anime_api_on',
    'cooker_on',
    'requests_on',
    'offers_on',
];

global $container, $site_config;

$session = $container->get(Session::class);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $updated = [];
    $filename = CACHE_DIR . 'block_settings_cache.php';
    $block_out = '<' . "?php\n\ndeclare(strict_types = 1);\n\n\$BLOCKS = [\n";
    foreach ($_POST as $k => $v) {
        $updated[] = $k;
        $block_out .= ($k === 'block_undefined') ? "\t'{$k}' => '" . htmlsafechars($v) . "',\n" : "\t'{$k}' => " . (int) $v . ",\n";
    }
    $missed = array_diff($list, $updated);
    foreach ($missed as $k) {
        $block_out .= ($k === 'block_undefined') ? "\t'{$k}' => '" . htmlsafechars($v) . "',\n" : "\t'{$k}' => 0,\n";
    }
    $block_out .= '];';
    file_put_contents($filename, $block_out);
    clearstatcache(true, $filename);

    $session->set('is-success', $lang['block_updated']);
    $session->set('is-success', "Don't forget to run\n\nphp bin/uglify.php\n\nto update the css/js files.");
    unset($_POST, $block_out, $block_set_cache);
    $cache->delete('site_blocks_');
    sleep(3);
    header('Location: ' . $_SERVER['PHP_SELF'] . '?tool=block.settings');
    die();
}

$HTMLOUT = "
    <form action='{$_SERVER['PHP_SELF']}?tool=block.settings' method='post' enctype='multipart/form-data' accept-charset='utf-8'>
        <h1 class='has-text-centered'>{$lang['block_global']}</h1>
        <div class='bg-02'>
        <fieldset id='user_blocks_index' class='header'>
            <legend class='flipper has-text-primary padding20 left10'><i class='icon-down-open size_4 right5' aria-hidden='true'></i>{$lang['block_index']}</legend>
            <div>
                <div class='level-center'>";

$contents[] = "
                            <div class='w-100 has-text-centered'>{$lang['block_iealert']}</div>
                            <div class='slideThree'><#ie_user_alert#></div>
                            <div class='w-100 has-text-centered'>{$lang['block_iealert_set']}</div>";

$contents[] = "
                            <div class='w-100 has-text-centered'>{$lang['block_cooker']}</div>
                            <div class='slideThree'><#cooker_on#></div>
                            <div class='w-100 has-text-centered'>{$lang['block_cooker_set']}</div>";

$contents[] = "
                            <div class='w-100 has-text-centered'>{$lang['block_requests']}</div>
                            <div class='slideThree'><#requests_on#></div>
                            <div class='w-100 has-text-centered'>{$lang['block_requests_set']}</div>";

$contents[] = "
                            <div class='w-100 has-text-centered'>{$lang['block_offers']}</div>
                            <div class='slideThree'><#offers_on#></div>
                            <div class='w-100 has-text-centered'>{$lang['block_offers_set']}</div>";

$contents[] = "
                            <div class='w-100 has-text-centered'>{$lang['block_news']}</div>
                            <div class='slideThree'><#news_on#></div>
                            <div class='w-100 has-text-centered'>{$lang['block_news_set']}</div>";

$contents[] = "
                            <div class='w-100 has-text-centered'>{$lang['block_ajaxchat']}</div>
                            <div class='slideThree'><#ajaxchat_on#></div>
                            <div class='w-100 has-text-centered'>{$lang['block_ajaxchat_set']}</div>";

$contents[] = "
                            <div class='w-100 has-text-centered'>{$lang['block_trivia']}</div>
                            <div class='slideThree'><#trivia_on#></div>
                            <div class='w-100 has-text-centered'>{$lang['block_trivia_set']}</div>";

$contents[] = "
                            <div class='w-100 has-text-centered'>{$lang['block_active_user']}</div>
                            <div class='slideThree'><#active_users_on#></div>
                            <div class='w-100 has-text-centered'>{$lang['block_active_user_set']}</div>";

$contents[] = "
                            <div class='w-100 has-text-centered'>{$lang['block_active_user24']}</div>
                            <div class='slideThree'><#active_24h_users_on#></div>
                            <div class='w-100 has-text-centered'>{$lang['block_active_user24_set']}</div>";

$contents[] = "
                            <div class='w-100 has-text-centered'>{$lang['block_active_irc']}</div>
                            <div class='slideThree'><#active_irc_users_on#></div>
                            <div class='w-100 has-text-centered'>{$lang['block_active_irc_set']}</div>";

$contents[] = "
                            <div class='w-100 has-text-centered'>{$lang['block_birthday']}</div>
                            <div class='slideThree'><#active_birthday_users_on#></div>
                            <div class='w-100 has-text-centered'>{$lang['block_birthday_set']}</div>";

$contents[] = "
                            <div class='w-100 has-text-centered'>{$lang['block_stats']}</div>
                            <div class='slideThree'><#stats_on#></div>
                            <div class='w-100 has-text-centered'>{$lang['block_stats_set']}</div>";

$contents[] = "
                            <div class='w-100 has-text-centered'>{$lang['block_disclaimer']}</div>
                            <div class='slideThree'><#disclaimer_on#></div>
                            <div class='w-100 has-text-centered'>{$lang['block_disclaimer_set']}</div>";

$contents[] = "
                            <div class='w-100 has-text-centered'>{$lang['block_latest_users']}</div>
                            <div class='slideThree'><#latest_user_on#></div>
                            <div class='w-100 has-text-centered'>{$lang['block_latest_users_set']}</div>";

$contents[] = "
                            <div class='w-100 has-text-centered'>{$lang['block_latest_comments']}</div>
                            <div class='slideThree'><#latest_comments_on#></div>
                            <div class='w-100 has-text-centered'>{$lang['block_latest_comments_set']}</div>";

$contents[] = "
                            <div class='w-100 has-text-centered'>{$lang['block_forum_post']}</div>
                            <div class='slideThree'><#forum_posts_on#></div>
                            <div class='w-100 has-text-centered'>{$lang['block_forum_post_set']}</div>";

$contents[] = "
                            <div class='w-100 has-text-centered'>{$lang['block_staff_picks']}</div>
                            <div class='slideThree'><#staff_picks_on#></div>
                            <div class='w-100 has-text-centered'>{$lang['block_staff_picks_set']}</div>";

$contents[] = "
                            <div class='w-100 has-text-centered'>{$lang['block_torrents']}</div>
                            <div class='slideThree'><#latest_torrents_on#></div>
                            <div class='w-100 has-text-centered'>{$lang['block_torrents_set']}</div>";

$contents[] = "
                            <div class='w-100 has-text-centered'>{$lang['block_torrents_movies']}</div>
                            <div class='slideThree'><#latest_movies_on#></div>
                            <div class='w-100 has-text-centered'>{$lang['block_torrents_movies_set']}</div>";

$contents[] = "
                            <div class='w-100 has-text-centered'>{$lang['block_torrents_tv']}</div>
                            <div class='slideThree'><#latest_tv_on#></div>
                            <div class='w-100 has-text-centered'>{$lang['block_torrents_tv_set']}</div>";

$contents[] = "
                            <div class='w-100 has-text-centered'>{$lang['block_torrents_scroll']}</div>
                            <div class='slideThree'><#latest_torrents_scroll_on#></div>
                            <div class='w-100 has-text-centered'>{$lang['block_torrents_scroll_set']}</div>";

$contents[] = "
                            <div class='w-100 has-text-centered'>{$lang['block_torrents_slider']}</div>
                            <div class='slideThree'><#latest_torrents_slider_on#></div>
                            <div class='w-100 has-text-centered'>{$lang['block_torrents_slider_set']}</div>";

$contents[] = "
                            <div class='w-100 has-text-centered'>{$lang['block_announcement']}</div>
                            <div class='slideThree'><#announcement_on#></div>
                            <div class='w-100 has-text-centered'>{$lang['block_announcement_set']}</div>";

$contents[] = "
                            <div class='w-100 has-text-centered'>{$lang['block_donation']}</div>
                            <div class='slideThree'><#donation_progress_on#></div>
                            <div class='w-100 has-text-centered'>{$lang['block_donation_set']}</div>";

$contents[] = "
                            <div class='w-100 has-text-centered'>{$lang['block_advertise']}</div>
                            <div class='slideThree'><#ads_on#></div>
                            <div class='w-100 has-text-centered'>{$lang['block_advertise_set']}</div>";

$contents[] = "
                            <div class='w-100 has-text-centered'>{$lang['block_torrent_freak']}</div>
                            <div class='slideThree'><#torrentfreak_on#></div>
                            <div class='w-100 has-text-centered'>{$lang['block_torrent_freak_set']}</div>";

$contents[] = "
                            <div class='w-100 has-text-centered'>{$lang['block_christmas']}</div>
                            <div class='slideThree'><#christmas_gift_on#></div>
                            <div class='w-100 has-text-centered'>{$lang['block_christmas_set']}</div>";

$contents[] = "
                            <div class='w-100 has-text-centered'>{$lang['block_poll']}</div>
                            <div class='slideThree'><#active_poll_on#></div>
                            <div class='w-100 has-text-centered'>{$lang['block_poll_set']}</div>";

$contents[] = "
                            <div class='w-100 has-text-centered'>{$lang['block_movie']}</div>
                            <div class='slideThree'><#movie_ofthe_week_on#></div>
                            <div class='w-100 has-text-centered'>{$lang['block_movie_set']}</div>";

$level1 = "
                <div class='level-center'>";

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

$HTMLOUT .= main_div($level1);
$HTMLOUT .= "
                </div>
            <div class='has-text-centered margin20'>
                <input class='button is-small' type='submit' name='submit' value='{$lang['block_submit']}'>
            </div>
        </fieldset>
    </div>
    <div class='bg-02 top20'>
        <fieldset id='user_blocks_stdhead' class='header'>
            <legend class='flipper has-text-primary padding20 left10'><i class='icon-down-open size_4 right5' aria-hidden='true'></i>{$lang['block_stdhead_settings']}</legend>
            <div>
                <div class='level-center'>";

$contents = [];
$contents[] = "
                            <div class='w-100 has-text-centered'>{$lang['block_freeleech']}</div>
                            <div class='slideThree'><#global_freeleech_on#></div>
                            <div class='w-100 has-text-centered'>{$lang['block_freeleech_set']}</div>";

$contents[] = "
                            <div class='w-100 has-text-centered'>{$lang['block_demotion']}</div>
                            <div class='slideThree'><#global_demotion_on#></div>
                            <div class='w-100 has-text-centered'>{$lang['block_demotion_set']}</div>";

$contents[] = "
                            <div class='w-100 has-text-centered'>{$lang['block_message']}</div>
                            <div class='slideThree'><#global_message_on#></div>
                            <div class='w-100 has-text-centered'>{$lang['block_message_set']}</div>";

$contents[] = "
                            <div class='w-100 has-text-centered'>{$lang['block_staff_warnings']}</div>
                            <div class='slideThree'><#global_staff_warn_on#></div>
                            <div class='w-100 has-text-centered'>{$lang['block_staff_warnings_set']}</div>";

$contents[] = "
                            <div class='w-100 has-text-centered'>{$lang['block_staff_reports']}</div>
                            <div class='slideThree'><#global_staff_report_on#></div>
                            <div class='w-100 has-text-centered'>{$lang['block_staff_reports_set']}</div>";

$contents[] = "
                            <div class='w-100 has-text-centered'>{$lang['block_upload_alert']}</div>
                            <div class='slideThree'><#global_staff_uploadapp_on#></div>
                            <div class='w-100 has-text-centered'>{$lang['block_upload_alert_set']}</div>";

$contents[] = "
                            <div class='w-100 has-text-centered'>{$lang['block_happyhour']}</div>
                            <div class='slideThree'><#global_happyhour_on#></div>
                            <div class='w-100 has-text-centered'>{$lang['block_happyhour_set']}</div>";

$contents[] = "
                            <div class='w-100 has-text-centered'>{$lang['block_crazyhour']}</div>
                            <div class='slideThree'><#global_crazyhour_on#></div>
                            <div class='w-100 has-text-centered'>{$lang['block_crazyhour_set']}</div>";

$contents[] = "
                            <div class='w-100 has-text-centered'>{$lang['block_bug']}</div>
                            <div class='slideThree'><#global_bug_message_on#></div>
                            <div class='w-100 has-text-centered'>{$lang['block_bug_set']}</div>";

$contents[] = "
                            <div class='w-100 has-text-centered'>{$lang['block_karma_contributions']}</div>
                            <div class='slideThree'><#global_freeleech_contribution_on#></div>
                            <div class='w-100 has-text-centered'>{$lang['block_karma_contributions_set']}</div>";

$contents[] = "
                            <div class='w-100 has-text-centered'>{$lang['block_staff_menu']}</div>
                            <div class='slideThree'><#global_staff_menu_on#></div>
                            <div class='w-100 has-text-centered'>{$lang['block_staff_menu_set']}</div>";

$contents[] = "
                            <div class='w-100 has-text-centered'>{$lang['block_flash_messages']}</div>
                            <div class='slideThree'><#global_flash_messages_on#></div>
                            <div class='w-100 has-text-centered'>{$lang['block_flash_messages_set']}</div>";

$level2 = "
                <div class='level-center'>";

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

$HTMLOUT .= main_div($level2);
$HTMLOUT .= "
                </div>
            <div class='has-text-centered margin20'>
                <input class='button is-small' type='submit' name='submit' value='{$lang['block_submit']}'>
            </div>
        </fieldset>
        </div>
        <div class='bg-02 top20'>
        <fieldset id='user_blocks_userdetails' class='header'>
            <legend class='flipper has-text-primary padding20 left10'><i class='icon-down-open size_4 right5' aria-hidden='true'></i>{$lang['block_userdetails']}</legend>
            <div>
                <div class='level-center'>";

$contents = [];

$contents[] = "
                            <div class='w-100 has-text-centered'>{$lang['block_showfriends']}</div>
                            <div class='slideThree'><#userdetails_showfriends_on#></div>
                            <div class='w-100 has-text-centered'>{$lang['block_showfriends_set']}</div>";

$contents[] = "
                            <div class='w-100 has-text-centered'>{$lang['block_flush']}</div>
                            <div class='slideThree'><#userdetails_flush_on#></div>
                            <div class='w-100 has-text-centered'>{$lang['block_flush_set']}</div>";

$contents[] = "
                            <div class='w-100 has-text-centered'>{$lang['block_joined']}</div>
                            <div class='slideThree'><#userdetails_joined_on#></div>
                            <div class='w-100 has-text-centered'>{$lang['block_joined_set']}</div>";

$contents[] = "
                            <div class='w-100 has-text-centered'>{$lang['block_user_online_time']}</div>
                            <div class='slideThree'><#userdetails_online_time_on#></div>
                            <div class='w-100 has-text-centered'>{$lang['block_user_online_time_set']}</div>";

$contents[] = "
                            <div class='w-100 has-text-centered'>{$lang['block_browser_os']}</div>
                            <div class='slideThree'><#userdetails_browser_on#></div>
                            <div class='w-100 has-text-centered'>{$lang['block_browser_os_set']}</div>";

$contents[] = "
                            <div class='w-100 has-text-centered'>{$lang['block_reputation']}</div>
                            <div class='slideThree'><#userdetails_reputation_on#></div>
                            <div class='w-100 has-text-centered'>{$lang['block_reputation_set']}</div>";

$contents[] = "
                            <div class='w-100 has-text-centered'>{$lang['block_userhits']}</div>
                            <div class='slideThree'><#userdetails_profile_hits_on#></div>
                            <div class='w-100 has-text-centered'>{$lang['block_userhits_set']}</div>";

$contents[] = "
                            <div class='w-100 has-text-centered'>{$lang['block_birthday_userdetails']}</div>
                            <div class='slideThree'><#userdetails_birthday_on#></div>
                            <div class='w-100 has-text-centered'>{$lang['block_birthday_userdetails_set']}</div>";

$contents[] = "
                            <div class='w-100 has-text-centered'>{$lang['block_contact']}</div>
                            <div class='slideThree'><#userdetails_contact_info_on#></div>
                            <div class='w-100 has-text-centered'>{$lang['block_contact_set']}</div>";

$contents[] = "
                            <div class='w-100 has-text-centered'>{$lang['block_iphistory']}</div>
                            <div class='slideThree'><#userdetails_iphistory_on#></div>
                            <div class='w-100 has-text-centered'>{$lang['block_iphistory_set']}</div>";

$contents[] = "
                            <div class='w-100 has-text-centered'>{$lang['block_traffic']}</div>
                            <div class='slideThree'><#userdetails_traffic_on#></div>
                            <div class='w-100 has-text-centered'>{$lang['block_traffic_set']}</div>";

$contents[] = "
                            <div class='w-100 has-text-centered'>{$lang['block_shareratio']}</div>
                            <div class='slideThree'><#userdetails_share_ratio_on#></div>
                            <div class='w-100 has-text-centered'>{$lang['block_shareratio_set']}</div>";

$contents[] = "
                            <div class='w-100 has-text-centered'>{$lang['block_seedtime']}</div>
                            <div class='slideThree'><#userdetails_seedtime_ratio_on#></div>
                            <div class='w-100 has-text-centered'>{$lang['block_seedtime_set']}</div>";

$contents[] = "
                            <div class='w-100 has-text-centered'>{$lang['block_seedbonus']}</div>
                            <div class='slideThree'><#userdetails_seedbonus_on#></div>
                            <div class='w-100 has-text-centered'>{$lang['block_seedbonus_set']}</div>";

$contents[] = "
                            <div class='w-100 has-text-centered'>{$lang['block_ircstats']}</div>
                            <div class='slideThree'><#userdetails_irc_stats_on#></div>
                            <div class='w-100 has-text-centered'>{$lang['block_ircstats_set']}</div>";

$contents[] = "
                            <div class='w-100 has-text-centered'>{$lang['block_conn_port']}</div>
                            <div class='slideThree'><#userdetails_connectable_port_on#></div>
                            <div class='w-100 has-text-centered'>{$lang['block_conn_port_set']}</div>";

$contents[] = "
                            <div class='w-100 has-text-centered'>{$lang['block_avatar']}</div>
                            <div class='slideThree'><#userdetails_avatar_on#></div>
                            <div class='w-100 has-text-centered'>{$lang['block_avatar_set']}</div>";

$contents[] = "
                            <div class='w-100 has-text-centered'>{$lang['block_userclass']}</div>
                            <div class='slideThree'><#userdetails_userclass_on#></div>
                            <div class='w-100 has-text-centered'>{$lang['block_userclass_set']}</div>";

$contents[] = "
                            <div class='w-100 has-text-centered'>{$lang['block_gender']}</div>
                            <div class='slideThree'><#userdetails_gender_on#></div>
                            <div class='w-100 has-text-centered'>{$lang['block_gender_set']}</div>";

$contents[] = "
                            <div class='w-100 has-text-centered'>{$lang['block_freeslot_freeleech']}</div>
                            <div class='slideThree'><#userdetails_freestuffs_on#></div>
                            <div class='w-100 has-text-centered'>{$lang['block_freeslot_freeleech_set']}</div>";

$contents[] = "
                            <div class='w-100 has-text-centered'>{$lang['block_torrent_comment']}</div>
                            <div class='slideThree'><#userdetails_comments_on#></div>
                            <div class='w-100 has-text-centered'>{$lang['block_torrent_comment_set']}</div>";

$contents[] = "
                            <div class='w-100 has-text-centered'>{$lang['block_forum_user_post']}</div>
                            <div class='slideThree'><#userdetails_forumposts_on#></div>
                            <div class='w-100 has-text-centered'>{$lang['block_forum_user_post_set']}</div>";

$contents[] = "
                            <div class='w-100 has-text-centered'>{$lang['block_invitedby']}</div>
                            <div class='slideThree'><#userdetails_invitedby_on#></div>
                            <div class='w-100 has-text-centered'>{$lang['block_invitedby_set']}</div>";

$contents[] = "
                            <div class='w-100 has-text-centered'>{$lang['block_torrent_info']}</div>
                            <div class='slideThree'><#userdetails_torrents_block_on#></div>
                            <div class='w-100 has-text-centered'>{$lang['block_torrent_info_set']}</div>";

$contents[] = "
                            <div class='w-100 has-text-centered'>{$lang['block_completed']}</div>
                            <div class='slideThree'><#userdetails_completed_on#></div>
                            <div class='w-100 has-text-centered'>{$lang['block_completed_set']}</div>";

$contents[] = "
                            <div class='w-100 has-text-centered'>{$lang['block_staff_snatched']}</div>
                            <div class='slideThree'><#userdetails_snatched_staff_on#></div>
                            <div class='w-100 has-text-centered'>{$lang['block_staff_snatched_set']}</div>";

$contents[] = "
                            <div class='w-100 has-text-centered'>{$lang['block_user_info']}</div>
                            <div class='slideThree'><#userdetails_userinfo_on#></div>
                            <div class='w-100 has-text-centered'>{$lang['block_user_info_set']}</div>";

$contents[] = "
                            <div class='w-100 has-text-centered'>{$lang['block_show_pm']}</div>
                            <div class='slideThree'><#userdetails_showpm_on#></div>
                            <div class='w-100 has-text-centered'>{$lang['block_show_pm_set']}</div>";

$contents[] = "
                            <div class='w-100 has-text-centered'>{$lang['block_report']}</div>
                            <div class='slideThree'><#userdetails_report_user_on#></div>
                            <div class='w-100 has-text-centered'>{$lang['block_report_set']}</div>";

$contents[] = "
                            <div class='w-100 has-text-centered'>{$lang['block_user_status']}</div>
                            <div class='slideThree'><#userdetails_user_status_on#></div>
                            <div class='w-100 has-text-centered'>{$lang['block_user_status_set']}</div>";

$contents[] = "
                            <div class='w-100 has-text-centered'>{$lang['block_user_comments']}</div>
                            <div class='slideThree'><#userdetails_user_comments_on#></div>
                            <div class='w-100 has-text-centered'>{$lang['block_user_comments_set']}</div>";

$level3 = "
                <div class='level-center'>";

foreach ($contents as $content) {
    $level3 .= "
                    <div class='margin10 w-15 min-150'>
                        <span class='bordered level-center bg-02'>
                            $content
                        </span>
                    </div>";
}

$level3 .= '
                </div>';

$HTMLOUT .= main_div($level3);
$HTMLOUT .= "
                </div>
            <div class='has-text-centered margin20'>
                <input class='button is-small' type='submit' name='submit' value='{$lang['block_submit']}'>
            </div>
        </fieldset>
    </div>
    <div class='bg-02 top20'>
        <fieldset id='user_blocks_apis' class='header'>
            <legend class='flipper has-text-primary padding20 left10'><i class='icon-down-open size_4 right5' aria-hidden='true'></i>{$lang['block_apis_settings']}</legend>
            <div>
                <div class='level-center'>";

$contents = [];
$contents[] = "
                            <div class='w-100 has-text-centered'>{$lang['fanart_api']}</div>
                            <div class='slideThree'><#fanart_api_on#></div>
                            <div class='w-100 has-text-centered'>{$lang['fanart_api_set']}</div>";

$contents[] = "
                            <div class='w-100 has-text-centered'>{$lang['tmdb_api']}</div>
                            <div class='slideThree'><#tmdb_api_on#></div>
                            <div class='w-100 has-text-centered'>{$lang['tmdb_api_set']}</div>";

$contents[] = "
                            <div class='w-100 has-text-centered'>{$lang['imdb_api']}</div>
                            <div class='slideThree'><#imdb_api_on#></div>
                            <div class='w-100 has-text-centered'>{$lang['imdb_api_set']}</div>";

$contents[] = "
                            <div class='w-100 has-text-centered'>{$lang['bluray_com_api']}</div>
                            <div class='slideThree'><#bluray_com_api_on#></div>
                            <div class='w-100 has-text-centered'>{$lang['bluray_com_api_set']}</div>";

$contents[] = "
                            <div class='w-100 has-text-centered'>{$lang['google_books_api']}</div>
                            <div class='slideThree'><#google_books_api_on#></div>
                            <div class='w-100 has-text-centered'>{$lang['google_books_api_set']}</div>";

$contents[] = "
                            <div class='w-100 has-text-centered'>{$lang['tvmaze_api']}</div>
                            <div class='slideThree'><#tvmaze_api_on#></div>
                            <div class='w-100 has-text-centered'>{$lang['tvmaze_api_set']}</div>";

$contents[] = "
                            <div class='w-100 has-text-centered'>{$lang['anidb_api']}</div>
                            <div class='slideThree'><#anime_api_on#></div>
                            <div class='w-100 has-text-centered'>{$lang['anidb_api_set']}</div>";

$level4 = "
                <div class='level-center'>";

foreach ($contents as $content) {
    $level4 .= "
                    <div class='margin10 w-15 min-150'>
                        <span class='bordered level-center bg-02'>
                            $content
                        </span>
                    </div>";
}

$level4 .= '
                </div>';

$HTMLOUT .= main_div($level4);
$HTMLOUT .= "
                    </div>
                <div class='has-text-centered margin20'>
                    <input class='button is-small' type='submit' name='submit' value='{$lang['block_submit']}'>
                </div>
            </fieldset>
        </div>
    </form>";

$HTMLOUT = preg_replace_callback('|<#(.*?)#>|', 'template_out', $HTMLOUT);
echo stdhead($lang['block_stdhead']) . wrapper($HTMLOUT) . stdfoot();

/**
 * @param $matches
 *
 * @return string
 */
function template_out($matches)
{
    $BLOCKS = [];
    if (!is_file(CACHE_DIR . 'block_settings_cache.php')) {
        $BLOCKS = [];
    } else {
        include CACHE_DIR . 'block_settings_cache.php';
        if (!is_array($BLOCKS)) {
            $BLOCKS = [];
        }
    }

    return "
                                <input type='checkbox' id='{$matches[1]}' name='{$matches[1]}' value='1' " . (!empty($BLOCKS[$matches[1]]) && $BLOCKS[$matches[1]] == 1 ? 'checked' : '') . "> 
                                <label for='{$matches[1]}'></label>";
}
