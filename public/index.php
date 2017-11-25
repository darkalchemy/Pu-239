<?php
require_once realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..') . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'user_functions.php';
require_once INCL_DIR . 'bbcode_functions.php';
require_once ROOT_DIR . 'polls.php';
require_once CLASS_DIR . 'class_user_options.php';
require_once CLASS_DIR . 'class_user_options_2.php';
check_user_status();
global $CURUSER, $site_config, $cache, $BLOCKS;

$stdhead = [
    'css' => [
        get_file('index_css'),
    ],
];

$stdfoot = [
    'js' => [
        get_file('index_js'),
    ],
];
$lang = array_merge(
    load_language('global'),
    load_language('index'),
    load_language('trivia')
);
if (isset($_GET['act']) && $_GET['act'] == 'Arcade' && isset($_POST['gname'])) {
    include_once INCL_DIR . 'arcade.php';
}

$unread = getPmCount($CURUSER['id']);
if ($unread >= 1) {
    setSessionVar(
        'is-link',
        [
            'message' => "You have $unread new message" . plural($unread) . " in your Inbox",
            'link'    => "{$site_config['baseurl']}/pm_system.php",
        ]
    );
}

$HTMLOUT = '';
//Start Portals Div
if (curuser::$blocks['index_page'] & block_index::IE_ALERT
    && $BLOCKS['ie_user_alert']
) {
    $HTMLOUT .= "<div class='container is-fluid portlet' id='IE_ALERT'>";
    include_once BLOCK_DIR . 'index/ie_user.php';
    $HTMLOUT .= '</div>';
}

//if (curuser::$blocks['index_page'] & block_index::ANNOUNCEMENT
// && $BLOCKS['announcement_on']
//) {
//    $HTMLOUT .= "<div class='container is-fluid portlet' id='ANNOUNCEMENT'>";
//    include_once BLOCK_DIR . 'index/announcement.php';
//    $HTMLOUT .= '</div>';
//}

if (curuser::$blocks['index_page'] & block_index::AJAXCHAT
    && $BLOCKS['ajaxchat_on'] && $CURUSER['chatpost'] === 1
) {
    $HTMLOUT .= "<div class='container is-fluid portlet' id='AJAXCHAT'>";
    include_once BLOCK_DIR . 'index/ajaxchat.php';
    $HTMLOUT .= '</div>';
}

if (curuser::$blocks['index_page'] & block_index::TRIVIA
    && $BLOCKS['trivia_on']
) {
    $HTMLOUT .= "<div class='container is-fluid portlet' id='TRIVIA'>";
    include_once BLOCK_DIR . 'index/trivia.php';
    $HTMLOUT .= '</div>';
}

if (curuser::$blocks['index_page'] & block_index::NEWS
    && $BLOCKS['news_on']
) {
    $HTMLOUT .= "<div class='container is-fluid portlet' id='NEWS'>";
    include_once BLOCK_DIR . 'index/news.php';
    $HTMLOUT .= '</div>';
}

if (curuser::$blocks['index_page'] & block_index::ADVERTISEMENTS
    && $BLOCKS['ads_on']
) {
    $HTMLOUT .= "<div class='container is-fluid portlet' id='ADVERTISEMENTS'>";
    include_once BLOCK_DIR . 'index/advertise.php';
    $HTMLOUT .= '</div>';
}

if (curuser::$blocks['index_page'] & block_index::FORUMPOSTS
    && $BLOCKS['forum_posts_on']
) {
    $HTMLOUT .= "<div class='container is-fluid portlet' id='FORUMPOSTS'>";
    include_once BLOCK_DIR . 'index/forum_posts.php';
    $HTMLOUT .= '</div>';
}

if (curuser::$blocks['index_page'] & block_index::LATESTCOMMENTS
    && $BLOCKS['latest_comments_on']
) {
    $HTMLOUT .= "<div class='container is-fluid portlet' id='LATESTCOMMENTS'>";
    include_once BLOCK_DIR . 'index/comments.php';
    $HTMLOUT .= '</div>';
}

if (curuser::$blocks['index_page'] & block_index::MOVIEOFWEEK
    && $BLOCKS['movie_ofthe_week_on']
) {
    $HTMLOUT .= "<div class='container is-fluid portlet' id='MOVIEOFWEEK'>";
    include_once BLOCK_DIR . 'index/mow.php';
    $HTMLOUT .= '</div>';
}

if (curuser::$blocks['index_page'] & block_index::LATEST_TORRENTS
    && $BLOCKS['latest_torrents_on']
) {
    $HTMLOUT .= "<div class='container is-fluid portlet' id='LATEST_TORRENTS'>";
    include_once BLOCK_DIR . 'index/latest_torrents.php';
    $HTMLOUT .= '</div>';
}

if (curuser::$blocks['index_page'] & block_index::LATEST_TORRENTS_SCROLL
    && $BLOCKS['latest_torrents_scroll_on']
) {
    $HTMLOUT .= "<div class='container is-fluid portlet' 
id='LATEST_TORRENTS_SCROLL'>";
    include_once BLOCK_DIR . 'index/latest_torrents_scroll.php';
    $HTMLOUT .= "</div>";
}

if (curuser::$blocks['index_page'] & block_index::STATS
    && $BLOCKS['stats_on']
) {
    $HTMLOUT .= "<div class='container is-fluid portlet' id='STATS'>";
    include_once BLOCK_DIR . 'index/stats.php';
    $HTMLOUT .= '</div>';
}

if (curuser::$blocks['index_page'] & block_index::ACTIVE_USERS
    && $BLOCKS['active_users_on']
) {
    $HTMLOUT .= "<div class='container is-fluid portlet' id='ACTIVE_USERS'>";
    include_once BLOCK_DIR . 'index/active_users.php';
    $HTMLOUT .= '</div>';
}

if (curuser::$blocks['index_page'] & block_index::IRC_ACTIVE_USERS
    && $BLOCKS['active_irc_users_on']
) {
    $HTMLOUT .= "<div class='container is-fluid portlet' id='IRC_ACTIVE_USERS'>";
    include_once BLOCK_DIR . 'index/active_irc_users.php';
    $HTMLOUT .= '</div>';
}

if (curuser::$blocks['index_page'] & block_index::LAST_24_ACTIVE_USERS
    && $BLOCKS['active_24h_users_on']
) {
    $HTMLOUT .= "<div class='container is-fluid portlet' id='LAST_24_ACTIVE_USERS'>";
    include_once BLOCK_DIR . 'index/active_24h_users.php';
    $HTMLOUT .= '</div>';
}

if (curuser::$blocks['index_page'] & block_index::BIRTHDAY_ACTIVE_USERS
    && $BLOCKS['active_birthday_users_on']
) {
    $HTMLOUT .= "<div class='container is-fluid portlet' 
id='BIRTHDAY_ACTIVE_USERS'>";
    include_once BLOCK_DIR . 'index/active_birthday_users.php';
    $HTMLOUT .= '</div>';
}

if (curuser::$blocks['index_page'] & block_index::LATEST_USER
    && $BLOCKS['latest_user_on']
) {
    $HTMLOUT .= "<div class='container is-fluid portlet' id='LATEST_USER'>";
    include_once BLOCK_DIR . 'index/latest_user.php';
    $HTMLOUT .= '</div>';
}

$poll_data = get_poll();
if (!empty($poll_data) && curuser::$blocks['index_page'] & block_index::ACTIVE_POLL
    && $BLOCKS['active_poll_on']
) {
    $HTMLOUT .= "<div class='container is-fluid portlet' id='ACTIVE_POLL'>";
    include_once BLOCK_DIR . 'index/poll.php';
    $HTMLOUT .= '</div>';
}

if (curuser::$blocks['index_page'] & block_index::CHRISTMAS_GIFT
    && $BLOCKS['christmas_gift_on']
) {
    $HTMLOUT .= "<div class='container is-fluid portlet' id='CHRISTMAS_GIFT'>";
    include_once BLOCK_DIR . 'index/gift.php';
    $HTMLOUT .= '</div>';
}

if (curuser::$blocks['index_page'] & block_index::RADIO
    && $BLOCKS['radio_on']
) {
    $HTMLOUT .= "<div class='container is-fluid portlet' id='RADIO'>";
    include_once BLOCK_DIR . 'index/radio.php';
    $HTMLOUT .= '</div>';
}

if (curuser::$blocks['index_page'] & block_index::TORRENTFREAK
    && $BLOCKS['torrentfreak_on']
) {
    $HTMLOUT .= "<div class='container is-fluid portlet' id='TORRENTFREAK'>";
    include_once BLOCK_DIR . 'index/torrentfreak.php';
    $HTMLOUT .= '</div>';
}

if (curuser::$blocks['index_page'] & block_index::DISCLAIMER
    && $BLOCKS['disclaimer_on']
) {
    $HTMLOUT .= "<div class='container is-fluid portlet' id='DISCLAIMER'>";
    include_once BLOCK_DIR . 'index/disclaimer.php';
    $HTMLOUT .= '</div>';
}

if (curuser::$blocks['index_page'] & block_index::DONATION_PROGRESS
    && $BLOCKS['donation_progress_on']
) {
    $HTMLOUT .= "<div class='container is-fluid portlet' id='DONATIONS'>";
    include_once BLOCK_DIR . 'index/donations.php';
    $HTMLOUT .= '</div>';
}

echo stdhead('Home', true, $stdhead) . $HTMLOUT . stdfoot($stdfoot);
