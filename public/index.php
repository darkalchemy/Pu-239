<?php

declare(strict_types = 1);

use Pu239\Message;
use Pu239\PollVoter;
use Pu239\Session;
use Pu239\Torrent;

require_once __DIR__ . '/../include/bittorrent.php';
require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_html.php';
require_once INCL_DIR . 'function_bbcode.php';
require_once INCL_DIR . 'function_polls.php';
require_once CLASS_DIR . 'class_user_options.php';
require_once CLASS_DIR . 'class_user_options_2.php';
require_once INCL_DIR . 'function_torrent_hover.php';
$user = check_user_status();
$stdhead = [
    'css' => [
        get_file_name('index_css'),
    ],
];
$stdfoot = [
    'js' => [
        get_file_name('parallax_js'),
        has_access($user['class'], UC_STAFF, '') ? get_file_name('offer_js') : '',
    ],
];
$lang = array_merge(load_language('global'), load_language('index'), load_language('trivia'));
if ((isset($_GET['act']) && $_GET['act'] === 'Arcade' && isset($_POST['gname'])) || (isset($_POST['module']) && $_POST['module'] === 'pnFlashGames')) {
    include_once INCL_DIR . 'arcade.php';
}
$HTMLOUT = '';
global $container, $site_config;

$messages_class = $container->get(Message::class);
$unread = $messages_class->get_count($user['id'], $site_config['pm']['inbox'], true);

if ($unread >= 1) {
    $session = $container->get(Session::class);
    $session->set('is-link', [
        'message' => "You have $unread unread message" . plural($unread) . ' in your Inbox',
        'link' => "{$site_config['paths']['baseurl']}/messages.php",
    ]);
}

$pollvoter_class = $container->get(PollVoter::class);
$poll_data = $pollvoter_class->get_user_poll($user['id']);
if ($site_config['poll']['forced'] && !empty($poll_data['pid']) && empty($poll_data['user_id'])) {
    $stdfoot['js'] = array_merge($stdfoot['js'], [
        get_file_name('scroll_to_poll_js'),
    ]);
}
$above_columns = [
    'glide',
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
    'cooker',
    'requests',
    'offers',
    'torrents_mow',
    'staffpicks',
    'torrents_top',
    'latest_torrents',
    'latest_movies',
    'latest_tv',
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
];
$christmas_gift = $posted_comments = $advertise = $active_users = $active_users_irc = $birthday_users = $active_users_24 = $forum_posts = $staffpicks = $disclaimer = $trivia = $glide = $ajaxchat = '';
$tfreak_feed = $torrents_top = $site_stats = $site_poll = $site_news = $torrents_mow = $latest_user = $torrents_scroller = $latest_torrents = $latest_movies = $latest_tv = $cooker = $requests = $offers = '';
$available_columns = array_merge($above_columns, $left_column, $center_column, $right_column, $below_columns);
$remove_columns = $user['class'] < UC_STAFF ? $site_config['site']['staff_blocks'] : [];
$torrents_class = $container->get(Torrent::class);
$available_columns = $user['status'] === 0 ? array_diff($available_columns, $remove_columns) : [];
$dir = BLOCK_DIR . 'index' . DIRECTORY_SEPARATOR;
if (in_array('glide', $available_columns) && $torrents_class->get_torrent_count() >= 10 && $user['blocks']['index_page'] & block_index::LATEST_TORRENTS_SLIDER && $BLOCKS['latest_torrents_slider_on']) {
    $stdfoot = array_merge_recursive($stdfoot, [
        'js' => [
            get_file_name('glider_js'),
        ],
    ]);
    include_once $dir . 'latest_torrents_glide.php';
} else {
    $remove_columns[] = 'glide';
}

if (in_array('ajaxchat', $available_columns) && $user['blocks']['index_page'] & block_index::AJAXCHAT && $BLOCKS['ajaxchat_on'] && $user['chatpost'] === 1) {
    $stdfoot = array_merge_recursive($stdfoot, [
        'js' => [
            get_file_name('trivia_js'),
        ],
    ]);
    include_once $dir . 'ajaxchat.php';
} else {
    $remove_columns[] = 'ajaxchat';
}

if (in_array('trivia', $available_columns) && $user['blocks']['index_page'] & block_index::TRIVIA && $BLOCKS['trivia_on']) {
    include_once $dir . 'trivia.php';
} else {
    $remove_columns[] = 'trivia';
}

if (in_array('forum_posts', $available_columns) && $user['blocks']['index_page'] & block_index::FORUMPOSTS && $BLOCKS['forum_posts_on']) {
    include_once $dir . 'forum_posts.php';
} else {
    $remove_columns[] = 'forum_posts';
}

if (in_array('staffpicks', $available_columns) && $user['blocks']['index_page'] & block_index::STAFF_PICKS && $BLOCKS['staff_picks_on']) {
    include_once $dir . 'staff_picks.php';
} else {
    $remove_columns[] = 'staffpicks';
}

if (in_array('latest_user', $available_columns) && $user['blocks']['index_page'] & block_index::LATEST_USER && $BLOCKS['latest_user_on']) {
    include_once $dir . 'latest_user.php';
} else {
    $remove_columns[] = 'latest_user';
}

if (in_array('birthday_users', $available_columns) && $user['blocks']['index_page'] & block_index::BIRTHDAY_ACTIVE_USERS && $BLOCKS['active_birthday_users_on']) {
    include_once $dir . 'active_birthday_users.php';
} else {
    $remove_columns[] = 'birthday_users';
}

if (in_array('active_users_irc', $available_columns) && $user['blocks']['index_page'] & block_index::IRC_ACTIVE_USERS && $BLOCKS['active_irc_users_on']) {
    include_once $dir . 'active_irc_users.php';
} else {
    $remove_columns[] = 'active_users_irc';
}

if (in_array('active_users', $available_columns) && $user['blocks']['index_page'] & block_index::ACTIVE_USERS && $BLOCKS['active_users_on']) {
    include_once $dir . 'active_users.php';
} else {
    $remove_columns[] = 'active_users';
}

if (in_array('cooker', $available_columns) && $user['blocks']['index_page'] & block_index::COOKER && $BLOCKS['cooker_on']) {
    include_once $dir . 'cooker.php';
} else {
    $remove_columns[] = 'cooker';
}

if (in_array('requests', $available_columns) && $user['blocks']['index_page'] & block_index::REQUESTS && $BLOCKS['requests_on']) {
    include_once $dir . 'requests.php';
} else {
    $remove_columns[] = 'requests';
}

if (in_array('offers', $available_columns) && $user['blocks']['index_page'] & block_index::OFFERS && $BLOCKS['offers_on']) {
    include_once $dir . 'offers.php';
} else {
    $remove_columns[] = 'offers';
}

if (in_array('active_users_24', $available_columns) && $user['blocks']['index_page'] & block_index::LAST_24_ACTIVE_USERS && $BLOCKS['active_24h_users_on']) {
    include_once $dir . 'active_24h_users.php';
} else {
    $remove_columns[] = 'active_users_24';
}

if (in_array('site_poll', $available_columns) && !empty($poll_data) && $user['blocks']['index_page'] & block_index::ACTIVE_POLL && $BLOCKS['active_poll_on']) {
    include_once $dir . 'poll.php';
} else {
    $remove_columns[] = 'site_poll';
}

if (in_array('site_stats', $available_columns) && $user['blocks']['index_page'] & block_index::STATS && $BLOCKS['stats_on']) {
    include_once $dir . 'stats.php';
} else {
    $remove_columns[] = 'site_stats';
}

if (in_array('christmas_gift', $available_columns) && Christmas() && $user['blocks']['index_page'] & block_index::CHRISTMAS_GIFT && $BLOCKS['christmas_gift_on']) {
    include_once $dir . 'gift.php';
} else {
    $remove_columns[] = 'christmas_gift';
}

if (in_array('torrents_scroller', $available_columns) && $torrents_class->get_torrent_count() >= 10 && $user['blocks']['index_page'] & block_index::LATEST_TORRENTS_SCROLL && $BLOCKS['latest_torrents_scroll_on']) {
    $stdfoot = array_merge_recursive($stdfoot, [
        'js' => [
            get_file_name('scroller_js'),
        ],
    ]);
    include_once $dir . 'latest_torrents_scroll.php';
} else {
    $remove_columns[] = 'torrents_scroller';
}

if (in_array('torrents_top', $available_columns) && $user['blocks']['index_page'] & block_index::LATEST_TORRENTS && $BLOCKS['latest_torrents_on']) {
    include_once $dir . 'top_torrents.php';
} else {
    $remove_columns[] = 'torrents_top';
}

if (in_array('latest_torrents', $available_columns) && $user['blocks']['index_page'] & block_index::LATEST_TORRENTS && $BLOCKS['latest_torrents_on']) {
    include_once $dir . 'latest_torrents.php';
} else {
    $remove_columns[] = 'latest_torrents';
}

if (in_array('latest_movies', $available_columns) && $user['blocks']['index_page'] & block_index::LATEST_MOVIES && $BLOCKS['latest_movies_on']) {
    include_once $dir . 'latest_movies.php';
} else {
    $remove_columns[] = 'latest_movies';
}

if (in_array('latest_tv', $available_columns) && $user['blocks']['index_page'] & block_index::LATEST_TV && $BLOCKS['latest_tv_on']) {
    include_once $dir . 'latest_tv.php';
} else {
    $remove_columns[] = 'latest_tv';
}

if (in_array('site_news', $available_columns) && $user['blocks']['index_page'] & block_index::NEWS && $BLOCKS['news_on']) {
    include_once $dir . 'news.php';
} else {
    $remove_columns[] = 'site_news';
}

if (in_array('advertise', $available_columns) && $user['blocks']['index_page'] & block_index::ADVERTISEMENTS && $BLOCKS['ads_on']) {
    include_once $dir . 'advertise.php';
} else {
    $remove_columns[] = 'advertise';
}

if (in_array('posted_comments', $available_columns) && $user['blocks']['index_page'] & block_index::LATESTCOMMENTS && $BLOCKS['latest_comments_on']) {
    include_once $dir . 'comments.php';
} else {
    $remove_columns[] = 'posted_comments';
}

if (in_array('torrents_mow', $available_columns) && $user['blocks']['index_page'] & block_index::MOVIEOFWEEK && $BLOCKS['movie_ofthe_week_on']) {
    include_once $dir . 'mow.php';
} else {
    $remove_columns[] = 'torrents_mow';
}

if (in_array('tfreak_feed', $available_columns) && $user['blocks']['index_page'] & block_index::TORRENTFREAK && $BLOCKS['torrentfreak_on'] && $site_config['newsrss']['tfreak']) {
    include_once $dir . 'torrentfreak.php';
} else {
    $remove_columns[] = 'tfreak_feed';
}

if (in_array('disclaimer', $available_columns) && $user['blocks']['index_page'] & block_index::DISCLAIMER && $BLOCKS['disclaimer_on']) {
    include_once $dir . 'disclaimer.php';
} else {
    $remove_columns[] = 'disclaimer';
}

foreach ($remove_columns as $item) {
    $above_columns = array_values(array_diff($above_columns, [$item]));
    $left_column = array_values(array_diff($left_column, [$item]));
    $center_column = array_values(array_diff($center_column, [$item]));
    $right_column = array_values(array_diff($right_column, [$item]));
    $below_columns = array_values(array_diff($below_columns, [$item]));
}

foreach ($above_columns as $item) {
    $HTMLOUT .= wrap_it($item, ${$item});
}

$middle = 'is-8-desktop';
$HTMLOUT .= "
<div id='parallax' class='columns is-desktop is-variable is-0-mobile is-0-tablet is-1-desktop'>";
if (!empty($left_column)) {
    $middle = 'is-6-desktop';
    $HTMLOUT .= "
    <div class='column is-2-desktop fl-3'>
        <div id='left_column' class='left_column'>";

    foreach ($left_column as $item) {
        $HTMLOUT .= wrap_it($item, ${$item});
    }

    $HTMLOUT .= '
        </div>
    </div>';
} else {
    $HTMLOUT .= "
    <div class='column is-hidden fl-3'>
        <div id='left_column' class='left_column'>
        </div>
    </div>";
}

$HTMLOUT .= "
    <div class='column $middle fl-1'>
        <div id='center_column'>";

foreach ($center_column as $item) {
    $HTMLOUT .= wrap_it($item, ${$item});
}

$HTMLOUT .= "
        </div>
    </div>
    <div class='column is-4-desktop fl-2'>
        <div id='right_column' class='right_column'>";

foreach ($right_column as $item) {
    $HTMLOUT .= wrap_it($item, ${$item});
}

$HTMLOUT .= '
        </div>
    </div>
</div>';

foreach ($below_columns as $item) {
    $HTMLOUT .= wrap_it($item, ${$item});
}

/**
 * @param $item
 * @param $data
 *
 * @return string
 */
function wrap_it($item, $data)
{
    $class = $item === 'tfreak_feed' ? '' : 'portlet';
    if (!empty($data)) {
        return "
    <div class='$class' id='" . strtolower($item) . "_'>{$data}
    </div>";
    }
}

echo stdhead('Home', $stdhead) . $HTMLOUT . stdfoot($stdfoot);
