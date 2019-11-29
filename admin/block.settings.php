<?php

declare(strict_types = 1);

use Pu239\Session;

require_once CLASS_DIR . 'class_check.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
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
    $block_out = "<?php\n\ndeclare(strict_types = 1);\n\n\$BLOCKS = [\n";
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

    $session->set('is-success', _('Block Settings Have Been Updated!'));
    $session->set('is-success', "Don't forget to run\n\nphp bin/uglify.php\n\nto update the css/js files.");
    unset($_POST, $block_out, $block_set_cache);
    $cache->delete('site_blocks_');
    sleep(3);
    header('Location: ' . $_SERVER['PHP_SELF'] . '?tool=block.settings');
    die();
}

$HTMLOUT = "
    <form action='{$_SERVER['PHP_SELF']}?tool=block.settings' method='post' enctype='multipart/form-data' accept-charset='utf-8'>
        <h1 class='has-text-centered'>" . _('Global Block Settings') . "</h1>
        <div class='bg-02'>
        <fieldset id='user_blocks_index' class='header'>
            <legend class='flipper has-text-primary padding20 left10'><i class='icon-down-open size_4 right5' aria-hidden='true'></i>" . _('Index Display Settings') . "</legend>
            <div>
                <div class='level-center'>";

$contents = [];
$contents[] = "
                            <div class='w-100 has-text-centered'>" . _('Enable IE alert?') . "</div>
                            <div class='slideThree'><#ie_user_alert#></div>
                            <div class='w-100 has-text-centered'>" . _("Set this option to 'On' if you want to enable the IE user alert.") . '</div>';

$contents[] = "
                            <div class='w-100 has-text-centered'>" . _('Enable Cooker?') . "</div>
                            <div class='slideThree'><#cooker_on#></div>
                            <div class='w-100 has-text-centered'>" . _("Set this option to 'On' if you want to enable the Cooker.") . '</div>';

$contents[] = "
                            <div class='w-100 has-text-centered'>" . _('Enable Requests?') . "</div>
                            <div class='slideThree'><#requests_on#></div>
                            <div class='w-100 has-text-centered'>" . _("Set this option to 'On' if you want to enable the Requests.") . '</div>';

$contents[] = "
                            <div class='w-100 has-text-centered'>" . _('Enable Offers?') . "</div>
                            <div class='slideThree'><#offers_on#></div>
                            <div class='w-100 has-text-centered'>" . _("Set this option to 'On' if you want to enable the Offers.") . '</div>';

$contents[] = "
                            <div class='w-100 has-text-centered'>" . _('Enable News?') . "</div>
                            <div class='slideThree'><#news_on#></div>
                            <div class='w-100 has-text-centered'>" . _("Set this option to 'On' if you want to enable the News Block.") . '</div>';

$contents[] = "
                            <div class='w-100 has-text-centered'>" . _('Enable AJAX Chat?') . "</div>
                            <div class='slideThree'><#ajaxchat_on#></div>
                            <div class='w-100 has-text-centered'>" . _("Set this option to 'On' if you want to enable AJAX Chat.") . '</div>';

$contents[] = "
                            <div class='w-100 has-text-centered'>" . _('Enable Trivia?') . "</div>
                            <div class='slideThree'><#trivia_on#></div>
                            <div class='w-100 has-text-centered'>" . _("Set this option to 'On' if you want to enable Trivia.") . '</div>';

$contents[] = "
                            <div class='w-100 has-text-centered'>" . _('Enable Active Users?') . "</div>
                            <div class='slideThree'><#active_users_on#></div>
                            <div class='w-100 has-text-centered'>" . _("Set this option to 'On' if you want to enable the Active Users.") . '</div>';

$contents[] = "
                            <div class='w-100 has-text-centered'>" . _('Enable Active Users Over 24hours?') . "</div>
                            <div class='slideThree'><#active_24h_users_on#></div>
                            <div class='w-100 has-text-centered'>" . _("Set this option to 'On' if you want to enable the Active Users visited over 24hours.") . '</div>';

$contents[] = "
                            <div class='w-100 has-text-centered'>" . _('Enable Active Irc Users?') . "</div>
                            <div class='slideThree'><#active_irc_users_on#></div>
                            <div class='w-100 has-text-centered'>" . _("Set this option to 'On' if you want to enable the Active Irc Users.") . '</div>';

$contents[] = "
                            <div class='w-100 has-text-centered'>" . _('Enable Birthday Users?') . "</div>
                            <div class='slideThree'><#active_birthday_users_on#></div>
                            <div class='w-100 has-text-centered'>" . _("Set this option to 'On' if you want to enable the Active Birthday Users.") . '</div>';

$contents[] = "
                            <div class='w-100 has-text-centered'>" . _('Enable Site Stats?') . "</div>
                            <div class='slideThree'><#stats_on#></div>
                            <div class='w-100 has-text-centered'>" . _("Set this option to 'On' if you want to enable the Stats.") . '</div>';

$contents[] = "
                            <div class='w-100 has-text-centered'>" . _('Enable Disclaimer?') . "</div>
                            <div class='slideThree'><#disclaimer_on#></div>
                            <div class='w-100 has-text-centered'>" . _("Set this option to 'On' if you want to enable Disclaimer.") . '</div>';

$contents[] = "
                            <div class='w-100 has-text-centered'>" . _('Enable Latest User?') . "</div>
                            <div class='slideThree'><#latest_user_on#></div>
                            <div class='w-100 has-text-centered'>" . _("Set this option to 'On' if you want to enable Latest User.") . '</div>';

$contents[] = "
                            <div class='w-100 has-text-centered'>" . _('Enable Latest Comments?') . "</div>
                            <div class='slideThree'><#latest_comments_on#></div>
                            <div class='w-100 has-text-centered'>" . _("Set this option to 'On' if you want to enable Latest Comments.") . '</div>';

$contents[] = "
                            <div class='w-100 has-text-centered'>" . _('Enable Latest Forum Posts?') . "</div>
                            <div class='slideThree'><#forum_posts_on#></div>
                            <div class='w-100 has-text-centered'>" . _("Set this option to 'On' if you want to enable latest Forum Posts.") . '</div>';

$contents[] = "
                            <div class='w-100 has-text-centered'>" . _('Enable Staff Picks') . "</div>
                            <div class='slideThree'><#staff_picks_on#></div>
                            <div class='w-100 has-text-centered'>" . _("Set this option to 'On' if you want to enable staff picks.") . '</div>';

$contents[] = "
                            <div class='w-100 has-text-centered'>" . _('Enable Latest Torrents?') . "</div>
                            <div class='slideThree'><#latest_torrents_on#></div>
                            <div class='w-100 has-text-centered'>" . _("Set this option to 'On' if you want to enable latest torrents.") . '</div>';

$contents[] = "
                            <div class='w-100 has-text-centered'>" . _('Enable Latest Movie Torrents?') . "</div>
                            <div class='slideThree'><#latest_movies_on#></div>
                            <div class='w-100 has-text-centered'>" . _("Set this option to 'On' if you want to enable the latest movie torrents.") . '</div>';

$contents[] = "
                            <div class='w-100 has-text-centered'>" . _('Enable Latest TV Torrents?') . "</div>
                            <div class='slideThree'><#latest_tv_on#></div>
                            <div class='w-100 has-text-centered'>" . _("Set this option to 'On' if you want to enable the latest tv torrents.") . '</div>';

$contents[] = "
                            <div class='w-100 has-text-centered'>" . _('Enable Latest torrents scroll?') . "</div>
                            <div class='slideThree'><#latest_torrents_scroll_on#></div>
                            <div class='w-100 has-text-centered'>" . _("Set this option to 'On' if you want to enable latest torrents marquee.") . '</div>';

$contents[] = "
                            <div class='w-100 has-text-centered'>" . _('Enable Latest torrents slider?') . "</div>
                            <div class='slideThree'><#latest_torrents_slider_on#></div>
                            <div class='w-100 has-text-centered'>" . _("Set this option to 'On' if you want to enable latest torrents banner slider.") . '</div>';

$contents[] = "
                            <div class='w-100 has-text-centered'>" . _('Enable Announcement?') . "</div>
                            <div class='slideThree'><#announcement_on#></div>
                            <div class='w-100 has-text-centered'>" . _("Set this option to 'On' if you want to enable the Announcement Block.") . '</div>';

$contents[] = "
                            <div class='w-100 has-text-centered'>" . _('Enable Donation Progress?') . "</div>
                            <div class='slideThree'><#donation_progress_on#></div>
                            <div class='w-100 has-text-centered'>" . _("Set this option to 'On' if you want to enable the Donation Progress.") . '</div>';

$contents[] = "
                            <div class='w-100 has-text-centered'>" . _('Enable Advertisements?') . "</div>
                            <div class='slideThree'><#ads_on#></div>
                            <div class='w-100 has-text-centered'>" . _("Set this option to 'On' if you want to enable the Advertisements.") . '</div>';

$contents[] = "
                            <div class='w-100 has-text-centered'>" . _('Enable Torrent Freak?') . "</div>
                            <div class='slideThree'><#torrentfreak_on#></div>
                            <div class='w-100 has-text-centered'>" . _("Set this option to 'On' if you want to enable the torrent freak news.") . '</div>';

$contents[] = "
                            <div class='w-100 has-text-centered'>" . _('Enable Christmas Gift?') . "</div>
                            <div class='slideThree'><#christmas_gift_on#></div>
                            <div class='w-100 has-text-centered'>" . _("Set this option to 'On' if you want to enable the Christmas Gift.") . '</div>';

$contents[] = "
                            <div class='w-100 has-text-centered'>" . _('Enable Poll?') . "</div>
                            <div class='slideThree'><#active_poll_on#></div>
                            <div class='w-100 has-text-centered'>" . _("Set this option to 'On' if you want to enable the Active Poll.") . '</div>';

$contents[] = "
                            <div class='w-100 has-text-centered'>" . _('Enable Movie of the week?') . "</div>
                            <div class='slideThree'><#movie_ofthe_week_on#></div>
                            <div class='w-100 has-text-centered'>" . _("Set this option to 'On' if you want to enable the movie of the week.") . '</div>';

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
                <input class='button is-small' type='submit' name='submit' value='" . _('Submit') . "'>
            </div>
        </fieldset>
    </div>
    <div class='bg-02 top20'>
        <fieldset id='user_blocks_stdhead' class='header'>
            <legend class='flipper has-text-primary padding20 left10'><i class='icon-down-open size_4 right5' aria-hidden='true'></i>" . _('Stdhead Display Settings') . "</legend>
            <div>
                <div class='level-center'>";

$contents = [];
$contents[] = "
                            <div class='w-100 has-text-centered'>" . _('Freeleech?') . "</div>
                            <div class='slideThree'><#global_freeleech_on#></div>
                            <div class='w-100 has-text-centered'>" . _("Enable 'freeleech mark' in stdhead") . '</div>';

$contents[] = "
                            <div class='w-100 has-text-centered'>" . _('Demotion') . "</div>
                            <div class='slideThree'><#global_demotion_on#></div>
                            <div class='w-100 has-text-centered'>" . _('Enable the global demotion alert block') . '</div>';

$contents[] = "
                            <div class='w-100 has-text-centered'>" . _('Message block?') . "</div>
                            <div class='slideThree'><#global_message_on#></div>
                            <div class='w-100 has-text-centered'>" . _('Enable message alert block') . '</div>';

$contents[] = "
                            <div class='w-100 has-text-centered'>" . _('Staff Warning?') . "</div>
                            <div class='slideThree'><#global_staff_warn_on#></div>
                            <div class='w-100 has-text-centered'>" . _('Shows a warning if there is a new message for staff') . '</div>';

$contents[] = "
                            <div class='w-100 has-text-centered'>" . _('Staff Reports?') . "</div>
                            <div class='slideThree'><#global_staff_report_on#></div>
                            <div class='w-100 has-text-centered'>" . _('Enable reports alert in stdhead') . '</div>';

$contents[] = "
                            <div class='w-100 has-text-centered'>" . _('Upload App Alert?') . "</div>
                            <div class='slideThree'><#global_staff_uploadapp_on#></div>
                            <div class='w-100 has-text-centered'>" . _('Enable upload application alerts in stdhead') . '</div>';

$contents[] = "
                            <div class='w-100 has-text-centered'>" . _('Happyhour?') . "</div>
                            <div class='slideThree'><#global_happyhour_on#></div>
                            <div class='w-100 has-text-centered'>" . _('Enable happy hour alerts in stdhead') . '</div>';

$contents[] = "
                            <div class='w-100 has-text-centered'>" . _('CrazyHour?') . "</div>
                            <div class='slideThree'><#global_crazyhour_on#></div>
                            <div class='w-100 has-text-centered'>" . _('Enable crazyhour alerts in stdhead') . '</div>';

$contents[] = "
                            <div class='w-100 has-text-centered'>" . _('Bug Message Alert?') . "</div>
                            <div class='slideThree'><#global_bug_message_on#></div>
                            <div class='w-100 has-text-centered'>" . _('Enable Bug message alerts in stdhead') . '</div>';

$contents[] = "
                            <div class='w-100 has-text-centered'>" . _('Karma Contributions') . "</div>
                            <div class='slideThree'><#global_freeleech_contribution_on#></div>
                            <div class='w-100 has-text-centered'>" . _('Enable karma contribution status alert in stdhead') . '</div>';

$contents[] = "
                            <div class='w-100 has-text-centered'>" . _('Staff Menu') . "</div>
                            <div class='slideThree'><#global_staff_menu_on#></div>
                            <div class='w-100 has-text-centered'>" . _('Enable Staff Menu in Navbar') . '</div>';

$contents[] = "
                            <div class='w-100 has-text-centered'>" . _('Flash Messages') . "</div>
                            <div class='slideThree'><#global_flash_messages_on#></div>
                            <div class='w-100 has-text-centered'>" . _('Enable Flash Messages on page refresh') . '</div>';

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
                <input class='button is-small' type='submit' name='submit' value='" . _('Submit') . "'>
            </div>
        </fieldset>
        </div>
        <div class='bg-02 top20'>
        <fieldset id='user_blocks_userdetails' class='header'>
            <legend class='flipper has-text-primary padding20 left10'><i class='icon-down-open size_4 right5' aria-hidden='true'></i>" . _('Userdetails Display Settings') . "</legend>
            <div>
                <div class='level-center'>";

$contents = [];
$contents[] = "
                            <div class='w-100 has-text-centered'>" . _('Show Friends?') . "</div>
                            <div class='slideThree'><#userdetails_showfriends_on#></div>
                            <div class='w-100 has-text-centered'>" . _('Enable Show Friends') . '</div>';

$contents[] = "
                            <div class='w-100 has-text-centered'>" . _('Flush Torrents?') . "</div>
                            <div class='slideThree'><#userdetails_flush_on#></div>
                            <div class='w-100 has-text-centered'>" . _('Enable flush torrents') . '</div>';

$contents[] = "
                            <div class='w-100 has-text-centered'>" . _('Joined date?') . "</div>
                            <div class='slideThree'><#userdetails_joined_on#></div>
                            <div class='w-100 has-text-centered'>" . _('Enable join date') . '</div>';

$contents[] = "
                            <div class='w-100 has-text-centered'>" . _('User online time?') . "</div>
                            <div class='slideThree'><#userdetails_online_time_on#></div>
                            <div class='w-100 has-text-centered'>" . _('Enable user online time') . '</div>';

$contents[] = "
                            <div class='w-100 has-text-centered'>" . _('Browser and OS detect?') . "</div>
                            <div class='slideThree'><#userdetails_browser_on#></div>
                            <div class='w-100 has-text-centered'>" . _('Enable browser and os detection') . '</div>';

$contents[] = "
                            <div class='w-100 has-text-centered'>" . _('Reputation?') . "</div>
                            <div class='slideThree'><#userdetails_reputation_on#></div>
                            <div class='w-100 has-text-centered'>" . _('Enable reputation link') . '</div>';

$contents[] = "
                            <div class='w-100 has-text-centered'>" . _('Userhits?') . "</div>
                            <div class='slideThree'><#userdetails_profile_hits_on#></div>
                            <div class='w-100 has-text-centered'>" . _('Enable user hits') . '</div>';

$contents[] = "
                            <div class='w-100 has-text-centered'>" . _('Birthday?') . "</div>
                            <div class='slideThree'><#userdetails_birthday_on#></div>
                            <div class='w-100 has-text-centered'>" . _('Enable birthday display') . '</div>';

$contents[] = "
                            <div class='w-100 has-text-centered'>" . _('Contact info?') . "</div>
                            <div class='slideThree'><#userdetails_contact_info_on#></div>
                            <div class='w-100 has-text-centered'>" . _('Enable contact info') . '</div>';

$contents[] = "
                            <div class='w-100 has-text-centered'>" . _('IP history?') . "</div>
                            <div class='slideThree'><#userdetails_iphistory_on#></div>
                            <div class='w-100 has-text-centered'>" . _('Enable IP history') . '</div>';

$contents[] = "
                            <div class='w-100 has-text-centered'>" . _('Traffic?') . "</div>
                            <div class='slideThree'><#userdetails_traffic_on#></div>
                            <div class='w-100 has-text-centered'>" . _('Enable uploaded and downloaded') . '</div>';

$contents[] = "
                            <div class='w-100 has-text-centered'>" . _('Share ratio?') . "</div>
                            <div class='slideThree'><#userdetails_share_ratio_on#></div>
                            <div class='w-100 has-text-centered'>" . _('Enable share ratio') . '</div>';

$contents[] = "
                            <div class='w-100 has-text-centered'>" . _('Seed time ratio?') . "</div>
                            <div class='slideThree'><#userdetails_seedtime_ratio_on#></div>
                            <div class='w-100 has-text-centered'>" . _('Enable seedtime ratio') . '</div>';

$contents[] = "
                            <div class='w-100 has-text-centered'>" . _('Seedbonus?') . "</div>
                            <div class='slideThree'><#userdetails_seedbonus_on#></div>
                            <div class='w-100 has-text-centered'>" . _('Enable seedbonus') . '</div>';

$contents[] = "
                            <div class='w-100 has-text-centered'>" . _('IRC stats?') . "</div>
                            <div class='slideThree'><#userdetails_irc_stats_on#></div>
                            <div class='w-100 has-text-centered'>" . _('Enable irc stats') . '</div>';

$contents[] = "
                            <div class='w-100 has-text-centered'>" . _('Connectable and port?') . "</div>
                            <div class='slideThree'><#userdetails_connectable_port_on#></div>
                            <div class='w-100 has-text-centered'>" . _('Enable connectable and port') . '</div>';

$contents[] = "
                            <div class='w-100 has-text-centered'>" . _('Avatar?') . "</div>
                            <div class='slideThree'><#userdetails_avatar_on#></div>
                            <div class='w-100 has-text-centered'>" . _('Enable avatar') . '</div>';

$contents[] = "
                            <div class='w-100 has-text-centered'>" . _('Userclass?') . "</div>
                            <div class='slideThree'><#userdetails_userclass_on#></div>
                            <div class='w-100 has-text-centered'>" . _('Enable userclass') . '</div>';

$contents[] = "
                            <div class='w-100 has-text-centered'>" . _('Gender?') . "</div>
                            <div class='slideThree'><#userdetails_gender_on#></div>
                            <div class='w-100 has-text-centered'>" . _('Enable gender') . '</div>';

$contents[] = "
                            <div class='w-100 has-text-centered'>" . _('Freeslots and Freeleech?') . "</div>
                            <div class='slideThree'><#userdetails_freestuffs_on#></div>
                            <div class='w-100 has-text-centered'>" . _('Enable freeslots and freeleech') . '</div>';

$contents[] = "
                            <div class='w-100 has-text-centered'>" . _('Torrent comments?') . "</div>
                            <div class='slideThree'><#userdetails_comments_on#></div>
                            <div class='w-100 has-text-centered'>" . _('Enable torrent comments history') . '</div>';

$contents[] = "
                            <div class='w-100 has-text-centered'>" . _('Forum posts?') . "</div>
                            <div class='slideThree'><#userdetails_forumposts_on#></div>
                            <div class='w-100 has-text-centered'>" . _('Enable forum posts history') . '</div>';

$contents[] = "
                            <div class='w-100 has-text-centered'>" . _('Invited by?') . "</div>
                            <div class='slideThree'><#userdetails_invitedby_on#></div>
                            <div class='w-100 has-text-centered'>" . _('Enable invited by') . '</div>';

$contents[] = "
                            <div class='w-100 has-text-centered'>" . _('Torrent info?') . "</div>
                            <div class='slideThree'><#userdetails_torrents_block_on#></div>
                            <div class='w-100 has-text-centered'>" . _('Enable seeding, leeching, snatched, uploaded torrents') . '</div>';

$contents[] = "
                            <div class='w-100 has-text-centered'>" . _('Completed?') . "</div>
                            <div class='slideThree'><#userdetails_completed_on#></div>
                            <div class='w-100 has-text-centered'>" . _('Enable completed torrents') . '</div>';

$contents[] = "
                            <div class='w-100 has-text-centered'>" . _('Staff snatched?') . "</div>
                            <div class='slideThree'><#userdetails_snatched_staff_on#></div>
                            <div class='w-100 has-text-centered'>" . _('Enable staff snatched torrents') . '</div>';

$contents[] = "
                            <div class='w-100 has-text-centered'>" . _('User info?') . "</div>
                            <div class='slideThree'><#userdetails_userinfo_on#></div>
                            <div class='w-100 has-text-centered'>" . _('Enable user info') . '</div>';

$contents[] = "
                            <div class='w-100 has-text-centered'>" . _('Show pm?') . "</div>
                            <div class='slideThree'><#userdetails_showpm_on#></div>
                            <div class='w-100 has-text-centered'>" . _('Enable send message button') . '</div>';

$contents[] = "
                            <div class='w-100 has-text-centered'>" . _('Report?') . "</div>
                            <div class='slideThree'><#userdetails_report_user_on#></div>
                            <div class='w-100 has-text-centered'>" . _('Enable report user') . '</div>';

$contents[] = "
                            <div class='w-100 has-text-centered'>" . _('User status?') . "</div>
                            <div class='slideThree'><#userdetails_user_status_on#></div>
                            <div class='w-100 has-text-centered'>" . _('Enable user status') . '</div>';

$contents[] = "
                            <div class='w-100 has-text-centered'>" . _('Usercomments?') . "</div>
                            <div class='slideThree'><#userdetails_user_comments_on#></div>
                            <div class='w-100 has-text-centered'>" . _('Enable usercomments') . '</div>';

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
                <input class='button is-small' type='submit' name='submit' value='" . _('Submit') . "'>
            </div>
        </fieldset>
    </div>
    <div class='bg-02 top20'>
        <fieldset id='user_blocks_apis' class='header'>
            <legend class='flipper has-text-primary padding20 left10'><i class='icon-down-open size_4 right5' aria-hidden='true'></i>" . _('APIs Display Settings') . "</legend>
            <div>
                <div class='level-center'>";

$contents = [];
$contents[] = "
                            <div class='w-100 has-text-centered'>" . _('FANART.tv API') . "</div>
                            <div class='slideThree'><#fanart_api_on#></div>
                            <div class='w-100 has-text-centered'>" . _('Enable FANART.tv API') . '</div>';

$contents[] = "
                            <div class='w-100 has-text-centered'>" . _('The Movie Database API') . "</div>
                            <div class='slideThree'><#tmdb_api_on#></div>
                            <div class='w-100 has-text-centered'>" . _('Enable The Movie Database (TMDb) API') . '</div>';

$contents[] = "
                            <div class='w-100 has-text-centered'>" . _('IMDb API') . "</div>
                            <div class='slideThree'><#imdb_api_on#></div>
                            <div class='w-100 has-text-centered'>" . _('Enable IMDb API') . '</div>';

$contents[] = "
                            <div class='w-100 has-text-centered'>" . _('Bluray.com RSS API') . "</div>
                            <div class='slideThree'><#bluray_com_api_on#></div>
                            <div class='w-100 has-text-centered'>" . _('Enable Bluray.com RSS API') . '</div>';

$contents[] = "
                            <div class='w-100 has-text-centered'>" . _('Google Books API') . "</div>
                            <div class='slideThree'><#google_books_api_on#></div>
                            <div class='w-100 has-text-centered'>" . _('Enable Google Books API') . '</div>';

$contents[] = "
                            <div class='w-100 has-text-centered'>" . _('TvMaze API') . "</div>
                            <div class='slideThree'><#tvmaze_api_on#></div>
                            <div class='w-100 has-text-centered'>" . _('Enable TvMaze API') . '</div>';

$contents[] = "
                            <div class='w-100 has-text-centered'>" . _('AniDb API') . "</div>
                            <div class='slideThree'><#anime_api_on#></div>
                            <div class='w-100 has-text-centered'>" . _('Enable AniDb API') . '</div>';

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
                    <input class='button is-small' type='submit' name='submit' value='" . _('Submit') . "'>
                </div>
            </fieldset>
        </div>
    </form>";

$HTMLOUT = preg_replace_callback('|<#(.*?)#>|', 'template_out', $HTMLOUT);
$title = _('Block Settings');
$breadcrumbs = [
    "<a href='{$site_config['paths']['baseurl']}/staffpanel.php'>" . _('Staff Panel') . '</a>',
    "<a href='{$_SERVER['PHP_SELF']}'>$title</a>",
];
echo stdhead($title, [], 'page-wrapper', $breadcrumbs) . wrapper($HTMLOUT) . stdfoot();

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
