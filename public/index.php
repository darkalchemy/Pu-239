<?php

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'user_functions.php';
require_once INCL_DIR . 'html_functions.php';
require_once INCL_DIR . 'bbcode_functions.php';
require_once INCL_DIR . 'share_images.php';
require_once ROOT_DIR . 'polls.php';
require_once CLASS_DIR . 'class_user_options.php';
require_once CLASS_DIR . 'class_user_options_2.php';
require_once INCL_DIR . 'torrent_hover.php';
check_user_status();
global $CURUSER, $site_config, $BLOCKS, $fluent, $cache, $session, $message_stuffs, $torrent_stuffs;

$stdfoot = [
    'js' => [
        get_file_name('scroller_js'),
        get_file_name('slider_js'),
        get_file_name('trivia_js'),
        get_file_name('parallax_js'),
    ],
];

$lang = array_merge(load_language('global'), load_language('index'), load_language('trivia'));

if (isset($_GET['act']) && $_GET['act'] === 'Arcade' && isset($_POST['gname'])) {
    include_once INCL_DIR . 'arcade.php';
}
$HTMLOUT = '';

$unread = $message_stuffs->get_count($CURUSER['id']);
if ($unread >= 1) {
    $session->set('is-link', [
        'message' => "You have $unread unread message" . plural($unread) . ' in your Inbox',
        'link' => "{$site_config['baseurl']}/messages.php",
    ]);
}

$poll_data = get_poll();
if (!empty($poll_data['pid']) && empty($poll_data['user_id'])) {
    $HTMLOUT .= "
<script>
    window.addEventListener('load', function(){
        var headerHeight = $('#navbar').outerHeight() + 10;
        var target = '#poll';
        var scrollToPosition = $(target).offset().top - headerHeight;
        $('html, body').animate({
            scrollTop: scrollToPosition
        }, animate_duration, 'swing');
        location.hash = '#poll';
    });
</script>";
}

$above_columns = [
    'slider',
];
$below_columns = [
    'disclaimer',
];
$left_column = [
    'tfreak_feed',
];
$center_column = [
    'ajaxchat',
    'torrents_scroller',
    'torrents_mow',
    'staffpicks',
    'torrents_top',
    'latest_torrents',
    'forum_posts',
    'site_stats',
    'site_poll',
];
$right_column = [
    'trivia',
    'advertise',
    'site_news',
    'posted_comments',
    'latest_user',
    'birthday_users',
    'active_users_irc',
    'active_users',
    'active_users_24',
    'christmas_gift',
    'site_radio',
];

$christmas_gift = $posted_comments = $advertise = $active_users = $active_users_irc = $birthday_users = $active_users_24 = $forum_posts = $staffpicks = $disclaimer = $trivia = $slider = $ajaxchat = '';
$tfreak_feed = $torrents_top = $site_stats = $site_radio = $site_poll = $site_news = $torrents_mow = $latest_user = $torrents_scroller = $latest_torrents = '';
$available_columns = array_merge($above_columns, $left_column, $center_column, $right_column, $below_columns);

if (in_array('slider', $available_columns) && $torrent_stuffs->get_torrent_count() >= 10) {
    if (curuser::$blocks['index_page'] & block_index::LATEST_TORRENTS_SLIDER && $BLOCKS['latest_torrents_slider_on']) {
        include_once BLOCK_DIR . 'index' . DIRECTORY_SEPARATOR . 'latest_torrents_slider.php';
    }
}

if (in_array('ajaxchat', $available_columns)) {
    if (curuser::$blocks['index_page'] & block_index::AJAXCHAT && $BLOCKS['ajaxchat_on'] && $CURUSER['chatpost'] === 1) {
        include_once BLOCK_DIR . 'index' . DIRECTORY_SEPARATOR . 'ajaxchat.php';
    } elseif (curuser::$blocks['index_page'] & block_index::AJAXCHAT && $BLOCKS['ajaxchat_on'] && $CURUSER['chatpost'] != 1) {
        $ajaxchat .= main_div("<div class='has-text-centered padding20 bg-02 round5'>You have been banned from AJAX Chat!</div>", 'bg-00');
    }
}

if (in_array('trivia', $available_columns) && curuser::$blocks['index_page'] & block_index::TRIVIA && $BLOCKS['trivia_on']) {
    include_once BLOCK_DIR . 'index' . DIRECTORY_SEPARATOR . 'trivia.php';
}

if (in_array('forum_posts', $available_columns) && curuser::$blocks['index_page'] & block_index::FORUMPOSTS && $BLOCKS['forum_posts_on']) {
    include_once BLOCK_DIR . 'index' . DIRECTORY_SEPARATOR . 'forum_posts.php';
}

if (in_array('staffpicks', $available_columns) && curuser::$blocks['index_page'] & block_index::STAFF_PICKS && $BLOCKS['staff_picks_on']) {
    include_once BLOCK_DIR . 'index' . DIRECTORY_SEPARATOR . 'staff_picks.php';
}

if (in_array('latest_user', $available_columns) && curuser::$blocks['index_page'] & block_index::LATEST_USER && $BLOCKS['latest_user_on']) {
    include_once BLOCK_DIR . 'index' . DIRECTORY_SEPARATOR . 'latest_user.php';
}

if (in_array('birthday_users', $available_columns) && curuser::$blocks['index_page'] & block_index::BIRTHDAY_ACTIVE_USERS && $BLOCKS['active_birthday_users_on']) {
    include_once BLOCK_DIR . 'index' . DIRECTORY_SEPARATOR . 'active_birthday_users.php';
}

if (in_array('active_users_irc', $available_columns) && curuser::$blocks['index_page'] & block_index::IRC_ACTIVE_USERS && $BLOCKS['active_irc_users_on']) {
    include_once BLOCK_DIR . 'index' . DIRECTORY_SEPARATOR . 'active_irc_users.php';
}

if (in_array('active_users', $available_columns) && curuser::$blocks['index_page'] & block_index::ACTIVE_USERS && $BLOCKS['active_users_on']) {
    include_once BLOCK_DIR . 'index' . DIRECTORY_SEPARATOR . 'active_users.php';
}

if (in_array('active_users_24', $available_columns) && curuser::$blocks['index_page'] & block_index::LAST_24_ACTIVE_USERS && $BLOCKS['active_24h_users_on']) {
    include_once BLOCK_DIR . 'index' . DIRECTORY_SEPARATOR . 'active_24h_users.php';
}

if (in_array('site_poll', $available_columns) && !empty($poll_data) && curuser::$blocks['index_page'] & block_index::ACTIVE_POLL && $BLOCKS['active_poll_on']) {
    include_once BLOCK_DIR . 'index' . DIRECTORY_SEPARATOR . 'poll.php';
}

if (in_array('site_stats', $available_columns) && curuser::$blocks['index_page'] & block_index::STATS && $BLOCKS['stats_on']) {
    include_once BLOCK_DIR . 'index' . DIRECTORY_SEPARATOR . 'stats.php';
}

if (in_array('christmas_gift', $available_columns) && Christmas()) {
    if (curuser::$blocks['index_page'] & block_index::CHRISTMAS_GIFT && $BLOCKS['christmas_gift_on']) {
        include_once BLOCK_DIR . 'index' . DIRECTORY_SEPARATOR . 'gift.php';
    }
}

if (in_array('site_radio', $available_columns) && curuser::$blocks['index_page'] & block_index::RADIO && $BLOCKS['radio_on']) {
    include_once BLOCK_DIR . 'index' . DIRECTORY_SEPARATOR . 'radio.php';
}

if (in_array('torrents_scroller', $available_columns) && $torrent_stuffs->get_torrent_count() >= 10) {
    if (curuser::$blocks['index_page'] & block_index::LATEST_TORRENTS_SCROLL && $BLOCKS['latest_torrents_scroll_on']) {
        include_once BLOCK_DIR . 'index' . DIRECTORY_SEPARATOR . 'latest_torrents_scroll.php';
    }
}

if (in_array('torrents_top', $available_columns) && curuser::$blocks['index_page'] & block_index::LATEST_TORRENTS && $BLOCKS['latest_torrents_on']) {
    include_once BLOCK_DIR . 'index' . DIRECTORY_SEPARATOR . 'top_torrents.php';
}

if (in_array('latest_torrents', $available_columns) && curuser::$blocks['index_page'] & block_index::LATEST_TORRENTS && $BLOCKS['latest_torrents_on']) {
    include_once BLOCK_DIR . 'index' . DIRECTORY_SEPARATOR . 'latest_torrents.php';
}

if (in_array('site_news', $available_columns) && curuser::$blocks['index_page'] & block_index::NEWS && $BLOCKS['news_on']) {
    include_once BLOCK_DIR . 'index' . DIRECTORY_SEPARATOR . 'news.php';
}

if (in_array('advertise', $available_columns) && curuser::$blocks['index_page'] & block_index::ADVERTISEMENTS && $BLOCKS['ads_on']) {
    include_once BLOCK_DIR . 'index' . DIRECTORY_SEPARATOR . 'advertise.php';
}

if (in_array('posted_comments', $available_columns) && curuser::$blocks['index_page'] & block_index::LATESTCOMMENTS && $BLOCKS['latest_comments_on']) {
    include_once BLOCK_DIR . 'index' . DIRECTORY_SEPARATOR . 'comments.php';
}

if (in_array('torrents_mow', $available_columns) && curuser::$blocks['index_page'] & block_index::MOVIEOFWEEK && $BLOCKS['movie_ofthe_week_on']) {
    include_once BLOCK_DIR . 'index' . DIRECTORY_SEPARATOR . 'mow.php';
}

if (in_array('tfreak_feed', $available_columns) && curuser::$blocks['index_page'] & block_index::TORRENTFREAK && $BLOCKS['torrentfreak_on'] && $site_config['newsrss_on']) {
    include_once BLOCK_DIR . 'index' . DIRECTORY_SEPARATOR . 'torrentfreak.php';
}

if (in_array('disclaimer', $available_columns) && curuser::$blocks['index_page'] & block_index::DISCLAIMER && $BLOCKS['disclaimer_on']) {
    include_once BLOCK_DIR . 'index' . DIRECTORY_SEPARATOR . 'disclaimer.php';
}

foreach ($above_columns as $item) {
    $HTMLOUT .= wrap_it($item, $$item);
}

$HTMLOUT .= "
<div class='columns parallax is-variable is-0-mobile is-0-tablet is-2-desktop'>
    <div class='column is-2-desktop fl-3'>
        <div id='left_column' class='left_column'>";

foreach ($left_column as $item) {
    $HTMLOUT .= wrap_it($item, $$item);
}

$HTMLOUT .= "
        </div>
    </div>
    <div class='column is-6-desktop fl-1'>
        <div id='center_column'>";

foreach ($center_column as $item) {
    $HTMLOUT .= wrap_it($item, $$item);
}

$HTMLOUT .= "
        </div>
    </div>
    <div class='column is-4-desktop fl-2'>
        <div id='right_column' class='right_column'>";

foreach ($right_column as $item) {
    $HTMLOUT .= wrap_it($item, $$item);
}

$HTMLOUT .= '
        </div>
    </div>
</div>';

foreach ($below_columns as $item) {
    $HTMLOUT .= wrap_it($item, $$item);
}

function wrap_it($item, $data)
{
    if (!empty($data)) {
        return "
    <div class='portlet' id='" . strtoupper($item) . "'>{$data}
    </div>";
    }
}

echo stdhead('Home') . $HTMLOUT . stdfoot($stdfoot);
