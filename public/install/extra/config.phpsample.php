<?php
const REQUIRED_PHP = 70000, REQUIRED_PHP_VERSION = '7.0.0';
if (PHP_VERSION_ID < REQUIRED_PHP) {
    die('PHP ' . REQUIRED_PHP_VERSION . ' or higher is required.');
}
if (PHP_INT_SIZE < 8) {
    die('A 64bit or higher OS + Processor is required.');
}
if (get_magic_quotes_gpc() || get_magic_quotes_runtime() || ini_get('magic_quotes_sybase')) {
    die('PHP is configured incorrectly. Turn off magic quotes.');
}
if (ini_get('register_long_arrays') || ini_get('register_globals') || ini_get('safe_mode')) {
    die('PHP is configured incorrectly. Turn off safe_mode, register_globals and register_long_arrays.');
}

//==charset
$site_config['char_set'] = 'UTF-8'; //also to be used site wide in meta tags

//== Windows fix
if (!function_exists('sys_getloadavg')) {
    function sys_getloadavg()
    {
        return [0, 0, 0];
    }
}

if (version_compare(PHP_VERSION, REQUIRED_PHP_VERSION, '>=')) {
    date_default_timezone_set('UTC');
}

$site_config['time_adjust'] = 0;
$site_config['time_offset'] = '0';
$site_config['time_use_relative'] = 1;
$site_config['time_use_relative_format'] = '{--}, h:i:s A';
$site_config['time_joined'] = 'j-F y';
$site_config['time_short'] = 'jS F Y - h:i:s A';
$site_config['time_long'] = 'M j Y, h:i:s A';
$site_config['time_tiny'] = '';
$site_config['time_date'] = '';
//== Cookie setup
$site_config['sessionName'] = '#sessionName'; // A single word that identifies this install.
$site_config['cookie_prefix'] = '#cookie_prefix_'; // This allows you to have multiple trackers, eg for demos, testing etc.
$site_config['cookie_path'] = '#cookie_path'; // generally '/' is good
$site_config['cookie_domain'] = '.#cookie_domain'; // set to eg: .somedomain.com or is subdomain set to: .sub.somedomain.com
$site_config['cookie_lifetime'] = #cookie_lifetime; // length of time cookies will be valid
$site_config['domain'] = '#domain';
$site_config['sessionCookieSecure'] = #sessionCookieSecure; // using HTTPS only? then set this
$site_config['sessionKeyPrefix'] = '#cookie_prefix_'; // useful if serving multiple sites
$site_config['session_csrf'] = 'csrf_token'; // useful if serving multiple sites
// Cache Expires
// 0 = permanent (doesn't expires);
// 1 - 2591999 (30 days) = relative time, in seconds from now;
// 2592000 and over = absolute time, unix timestamp
$site_config['expires']['latestuser'] = 0; // 0 = infinite
$site_config['expires']['MyPeers_'] = 120; // 60 = 60 seconds
$site_config['expires']['unread'] = 86400; // 86400 = 1 day
$site_config['expires']['alerts'] = 0; // 0 = infinite
$site_config['expires']['searchcloud'] = 0; // 0 = infinite
$site_config['expires']['user_cache'] = 2591999; // 30 days
$site_config['expires']['curuser'] = 2591999; // 30 days
$site_config['expires']['u_status'] = 2591999; // 30 days
$site_config['expires']['u_stats'] = 300; // 300 = 5 min
$site_config['expires']['u_stats_xbt'] = 30; // 30 seconds
$site_config['expires']['user_status'] = 2591999; // 30 days
$site_config['expires']['user_stats'] = 300; // 300 = 5 min
$site_config['expires']['user_stats_xbt'] = 30; // 30 seconds
$site_config['expires']['MyPeers_xbt_'] = 30;
$site_config['expires']['announcement'] = 600; // 600 = 10 min
$site_config['expires']['forum_posts'] = 0;
$site_config['expires']['torrent_comments'] = 900; // 900 = 15 min
$site_config['expires']['latestposts'] = 0; // 900 = 15 min
$site_config['expires']['top5_torrents'] = 0; // 0 = infinite
$site_config['expires']['last5_torrents'] = 0; // 0 = infinite
$site_config['expires']['scroll_torrents'] = 0; // 0 = infinite
$site_config['expires']['torrent_details'] = 2591999; // 30 days
$site_config['expires']['torrent_details_text'] = 2591999; // 30 days
$site_config['expires']['insertJumpTo'] = 2591999; // 30 days
$site_config['expires']['get_all_boxes'] = 2591999; // 30 days
$site_config['expires']['thumbsup'] = 0; // 0 = infinite
$site_config['expires']['iphistory'] = 900; // 900 = 15 min
$site_config['expires']['newpoll'] = 0; // 900 = 15 min
$site_config['expires']['genrelist'] = 2591999; // 30 days
$site_config['expires']['genrelist2'] = 2591999; // 30 days
$site_config['expires']['poll_data'] = 900; // 300 = 5 min
$site_config['expires']['torrent_data'] = 900; // 900 = 15 min
$site_config['expires']['user_flag'] = 86400 * 28; // 900 = 15 min
$site_config['expires']['shit_list'] = 900; // 900 = 15 min
$site_config['expires']['port_data'] = 900; // 900 = 15 min
$site_config['expires']['port_data_xbt'] = 900; // 900 = 15 min
$site_config['expires']['user_peers'] = 900; // 900 = 15 min
$site_config['expires']['user_friends'] = 900; // 900 = 15 min
$site_config['expires']['user_hash'] = 900; // 900 = 15 min
$site_config['expires']['user_blocks'] = 900; // 900 = 15 min
$site_config['expires']['hnr_data'] = 300; // 300 = 5 min
$site_config['expires']['snatch_data'] = 300; // 300 = 5 min
$site_config['expires']['user_snatches_data'] = 300; // 300 = 5 min
$site_config['expires']['staff_snatches_data'] = 300; // 300 = 5 min
$site_config['expires']['user_snatches_complete'] = 300; // 300 = 5 min
$site_config['expires']['completed_torrents'] = 300; // 300 = 5 min
$site_config['expires']['activeusers'] = 60; // 60 = 1 minutes
$site_config['expires']['forum_users'] = 30; // 60 = 1 minutes
$site_config['expires']['section_view'] = 30; // 60 = 1 minutes
$site_config['expires']['child_boards'] = 900; // 60 = 1 minutes
$site_config['expires']['sv_child_boards'] = 900; // 60 = 1 minutes
$site_config['expires']['forum_insertJumpTo'] = 3600; // = 1 hour
$site_config['expires']['last_post'] = 0; // infinite
$site_config['expires']['sv_last_post'] = 0; // infinite
$site_config['expires']['last_read_post'] = 0; // infinite
$site_config['expires']['sv_last_read_post'] = 0; // infinite
$site_config['expires']['last24'] = 3600; // 3600 = 1 hours
$site_config['expires']['activeircusers'] = 300; // 300 = 5 min
$site_config['expires']['birthdayusers'] = 43200; //== 43200 = 12 hours
$site_config['expires']['news_users'] = 3600; // 3600 = 1 hours
$site_config['expires']['user_invitees'] = 900; // 900 = 15 min
$site_config['expires']['ip_data'] = 900; // 900 = 15 min
$site_config['expires']['latesttorrents'] = 0; // 0 = infinite
$site_config['expires']['invited_by'] = 900; // 900 = 15 min
$site_config['expires']['user_torrents'] = 900; // 900 = 15 min
$site_config['expires']['user_seedleech'] = 900; // 900 = 15 min
$site_config['expires']['radio'] = 0; // 0 = infinite
$site_config['expires']['total_funds'] = 0; // 0 = infinite
$site_config['expires']['latest_news'] = 0; // 0 = infinite
$site_config['expires']['site_stats'] = 300; // 300 = 5 min
$site_config['expires']['share_ratio'] = 900; // 900 = 15 min
$site_config['expires']['share_ratio_xbt'] = 900; // 900 = 15 min
$site_config['expires']['checked_by'] = 0; // 0 = infinite
$site_config['expires']['sanity'] = 0; // 0 = infinite
$site_config['expires']['movieofweek'] = 300; // 604800 = 1 week
$site_config['expires']['browse_where'] = 60; // 60 = 60 seconds
$site_config['expires']['torrent_xbt_data'] = 300; // 300 = 5 min
$site_config['expires']['ismoddin'] = 0; // 0 = infinite
//== Tracker configs
$site_config['tracker_post_key'] = 'lsdflksfda4545frwe35@kk';
$site_config['max_torrent_size'] = 3 * 1024 * 1024;
$site_config['announce_interval'] = 60 * 30;
$site_config['signup_timeout'] = 86400 * 3;
$site_config['autoclean_interval'] = 1800;
$site_config['sub_max_size'] = 500 * 1024;
$site_config['minvotes'] = 1;
$site_config['max_dead_torrent_time'] = 6 * 3600;
$site_config['language'] = 1;
// Site Bot
$site_config['chatBotID'] = 2;
$site_config['chatBotName'] = '#bot_username';
$site_config['chatBotRole'] = 100;
$site_config['staffpanel_online'] = 1;
$site_config['irc_autoshout_on'] = 1;
$site_config['crazy_hour'] = false; //== Off for XBT
$site_config['happy_hour'] = false; //== Off for XBT
$site_config['mods']['slots'] = true;
$site_config['votesrequired'] = 15;
$site_config['catsperrow'] = 7;
$site_config['maxwidth'] = '90%';
//== Latest posts limit
$site_config['latest_posts_limit'] = 5; //query limit for latest forum posts on index
//latest torrents limit
$site_config['latest_torrents_limit'] = 5;
$site_config['latest_torrents_limit_2'] = 5;
$site_config['latest_torrents_limit_scroll'] = 20;
/* Settings **/
$site_config['reports'] = 1; // 1/0 on/off
$site_config['karma'] = 1; // 1/0 on/off
$site_config['BBcode'] = 1; // 1/0 on/off
$site_config['inviteusers'] = 10000;
$site_config['flood_time'] = 900; //comment/forum/pm flood limit
$site_config['readpost_expiry'] = 14 * 86400; // 14 days

$site_config['cache'] = ROOT_DIR . 'cache';
$site_config['backup_dir'] = INCL_DIR . 'backup';
$site_config['dictbreaker'] = ROOT_DIR . 'dictbreaker';
$site_config['torrent_dir'] = ROOT_DIR . 'torrents'; // must be writable for httpd user
$site_config['sub_up_dir'] = ROOT_DIR . 'uploadsub'; // must be writable for httpd user
$site_config['flood_file'] = INCL_DIR . 'settings' . DIRECTORY_SEPARATOR . 'limitfile.txt';
$site_config['nameblacklist'] = ROOT_DIR . 'cache' . DIRECTORY_SEPARATOR . 'nameblacklist.txt';
$site_config['happyhour'] = CACHE_DIR . 'happyhour' . DIRECTORY_SEPARATOR . 'happyhour.txt';
$site_config['sql_error_log'] = SQLERROR_LOGS_DIR . 'sql_err_' . date('M_D_Y') . '.log';
//== XBT or PHP announce
if (XBT_TRACKER == true) {
    $site_config['xbt_prefix'] = '#announce_urls:2710/';
    $site_config['xbt_suffix'] = '/announce';
    $site_config['announce_urls'][] = '#announce_urls:2710/announce';
} else {
    $site_config['announce_urls'] = [];
    $site_config['announce_urls'][] = '#announce_urls';
    $site_config['announce_urls'][] = '#announce_https';
}
if (isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'] == '') {
    $_SERVER['HTTP_HOST'] = $_SERVER['SERVER_NAME'];
}

$site_config['baseurl'] = get_scheme() . '://' . $_SERVER['HTTP_HOST'];
//== Email for sender/return path.
$site_config['site_email'] = '#site_email';
$site_config['site_name'] = '#site_name';
$site_config['msg_alert'] = 1; // saves a query when off
$site_config['report_alert'] = 1; // saves a query when off
$site_config['staffmsg_alert'] = 1; // saves a query when off
$site_config['uploadapp_alert'] = 1; // saves a query when off
$site_config['bug_alert'] = 1; // saves a query when off
$site_config['pic_base_url'] = $site_config['baseurl'] . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR;
$site_config['stylesheet'] = 1;
$site_config['categorie_icon'] = 1;
$site_config['comment_min_class'] = 4; //minim class to be checked when posting comments
$site_config['comment_check'] = 1; //set it to 0 if you wanna allow commenting with out staff checking
//for subs & youtube mode
$site_config['movie_cats'] = [
    3,
    10,
    11,
];
$site_config['slider_cats'] = [
    3,
    10,
    11,
];
$youtube_pattern = "/^http(s)?\:\/\/www\.youtube\.com\/watch\?v\=[\w-]{11}/i";
//== set this to size of user avatars
$site_config['av_img_height'] = 100;
$site_config['av_img_width'] = 100;
//== set this to size of user signatures
$site_config['sig_img_height'] = 100;
$site_config['sig_img_width'] = 500;

//== Image uploads
$site_config['bucket_allowed'] = 0;
$site_config['allowed_ext'] = [
    'image/gif',
    'image.jpg',
    'image/jpeg',
];
$upload_max_filesize = ini_get('upload_max_filesize') !== null ? return_bytes(ini_get('upload_max_filesize')) : 0;
$post_max_filesize = ini_get('post_max_filesize') !== null ? return_bytes(ini_get('post_max_filesize')) : 0;
$site_config['bucket_maxsize'] = $upload_max_filesize >= $post_max_filesize ? $upload_max_filesize : $post_max_filesize;
$site_config['site']['owner'] = 1;
$site_config['site']['salt'] = '#pass1';
$site_config['staff']['staff_pin'] = '#pass2';
$site_config['staff']['owner_pin'] = '#pass3';

$site_config['staff']['forumid'] = 2;
$site_config['staff_forums'] = [
    1,
    2,
];

$site_config['variant'] = 'Pu-239';

// Arcade Games
$site_config['arcade_games'] = ['asteroids', 'breakout', 'frogger', 'galaga', 'hexxagon', 'invaders', 'moonlander', 'pacman', 'psol', 'simon', 'snake', 'tetris', 'autobahn', 'ghosts-and-goblins', 'joust', 'ms-pac-man'];
$site_config['arcade_games_names'] = ['Asteroids', 'Breakout', 'Frogger', 'Galaga', 'Hexxagon', 'Space Invaders', 'Moonlander', 'Pacman', 'Pyramid Solitaire', 'Simon', 'Snake', 'Tetris', 'Autobahn', 'Ghosts\'n Goblins', 'Joust', 'Ms. Pac-Man'];
$site_config['top_score_points'] = 1000;

$site_config['bad_words'] = ['fuck', 'shit', 'Moderator', 'Administrator', 'Admin', 'pussy', 'Sysop', 'cunt', 'nigger', 'VIP', 'Super User', 'Power User', 'ADMIN', 'SYSOP', 'MODERATOR', 'ADMINISTRATOR'];
$site_config['notifications'] = ['is-danger', 'is-warning', 'is-success', 'is-info', 'is-link'];
