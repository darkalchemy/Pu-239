<?php

global $site_config;

$site_settings = $hnr_settings = $staff_settings = [];

if (PHP_INT_SIZE < 8) {
    die('A 64bit or higher OS + Processor is required.');
}
$site_config['variant'] = 'Pu-239';

// charset
$site_config['char_set'] = 'UTF-8'; // also to be used site wide in meta tags

// Windows fix
if (!function_exists('sys_getloadavg')) {
    function sys_getloadavg()
    {
        return [
            0,
            0,
            0,
        ];
    }
}

date_default_timezone_set('UTC');

// Cache Expires
// 0 = permanent (doesn't expires);
// 1 - 2591999 (30 days) = relative time, in seconds from now;
// 2592000 and over = absolute time, unix timestamp
$site_config['expires']['remember_me'] = 14; // number of days to keep long term cookie
$site_config['expires']['latestuser'] = 3600; // 3600 = 1 hour
$site_config['expires']['motw'] = 3600; // 3600 = 1 hour
$site_config['expires']['peers_'] = 120; // 120 = 2 minutes
$site_config['expires']['unread'] = 86400; // 86400 = 1 day
$site_config['expires']['alerts'] = 86400; // 86400 = 1 day
$site_config['expires']['searchcloud'] = 86400; // 86400 = 1 day
$site_config['expires']['user_cache'] = 2591999; // 30 days
$site_config['expires']['curuser'] = 2591999; // 30 days
$site_config['expires']['u_status'] = 2591999; // 30 days
$site_config['expires']['user_status'] = 2591999; // 30 days
$site_config['expires']['announcement'] = 600; // 600 = 10 min
$site_config['expires']['forum_posts'] = 86400;
$site_config['expires']['torrent_comments'] = 900; // 900 = 15 min
$site_config['expires']['latestposts'] = 300; // 300 = 5 min
$site_config['expires']['top5_torrents'] = 3600; // 3600 = 60 min
$site_config['expires']['last5_torrents'] = 3600; // 3600 = 60 min
$site_config['expires']['scroll_torrents'] = 3600; // 3600 = 60 min
$site_config['expires']['slider_torrents'] = 3600; // 3600 = 60 min
$site_config['expires']['torrent_details'] = 2591999; // 30 days
$site_config['expires']['torrent_details_text'] = 2591999; // 30 days
$site_config['expires']['insertJumpTo'] = 2591999; // 30 days
$site_config['expires']['get_all_boxes'] = 2591999; // 30 days
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
$site_config['expires']['activeusers'] = 300; // 300 = 5 min
$site_config['expires']['forum_users'] = 60; // 60 = 1 minutes
$site_config['expires']['section_view'] = 60; // 60 = 1 minutes
$site_config['expires']['child_boards'] = 900; // 60 = 1 minutes
$site_config['expires']['sv_child_boards'] = 900; // 60 = 1 minutes
$site_config['expires']['forum_insertJumpTo'] = 3600; // = 1 hour
$site_config['expires']['last_post'] = 86400; // 86400 = 1 day
$site_config['expires']['sv_last_post'] = 86400; // 86400 = 1 day
$site_config['expires']['last_read_post'] = 86400; // 86400 = 1 day
$site_config['expires']['sv_last_read_post'] = 86400; // 86400 = 1 day
$site_config['expires']['last24'] = 300; // 300 = 5 min
$site_config['expires']['latestcomments'] = 300; // 300 = 5 min
$site_config['expires']['activeircusers'] = 300; // 300 = 5 min
$site_config['expires']['birthdayusers'] = 43200; // 43200 = 12 hours
$site_config['expires']['news_users'] = 3600; // 3600 = 1 hours
$site_config['expires']['user_invitees'] = 900; // 900 = 15 min
$site_config['expires']['ip_data'] = 900; // 900 = 15 min
$site_config['expires']['latesttorrents'] = 86400; // 86400 = 1 day
$site_config['expires']['invited_by'] = 900; // 900 = 15 min
$site_config['expires']['user_torrents'] = 900; // 900 = 15 min
$site_config['expires']['user_seedleech'] = 900; // 900 = 15 min
$site_config['expires']['radio'] = 0; // 0 = infinite
$site_config['expires']['total_funds'] = 3600; // 3600 = 1 hour
$site_config['expires']['latest_news'] = 3600; // 3600 = 1 hour
$site_config['expires']['site_stats'] = 300; // 300 = 5 min
$site_config['expires']['share_ratio'] = 900; // 900 = 15 min
$site_config['expires']['share_ratio_xbt'] = 900; // 900 = 15 min
$site_config['expires']['checked_by'] = 0; // 0 = infinite
$site_config['expires']['sanity'] = 0; // 0 = infinite
$site_config['expires']['movieofweek'] = 604800; // 604800 = 1 week
$site_config['expires']['browse_where'] = 60; // 60 = 60 seconds
$site_config['expires']['torrent_xbt_data'] = 300; // 300 = 5 min
$site_config['expires']['ismoddin'] = 0; // 0 = infinite
$site_config['expires']['book_info'] = 604800; // 604800 = 1 week
$site_config['expires']['browser_user_agent'] = 86400;
$site_config['expires']['staff_picks'] = 86400;
// Tracker configs
$site_config['max_torrent_size'] = 3 * 1024 * 1024;
$site_config['announce_interval'] = 60 * 30;
$site_config['signup_timeout'] = 86400 * 3;
$site_config['sub_max_size'] = 500 * 1024;
$site_config['minvotes'] = 1;
$site_config['max_dead_torrent_time'] = 6 * 3600;
$site_config['language'] = 1;
// Site Bot
$site_config['chatBotID'] = 2;
$site_config['chatBotRole'] = 100;
$site_config['staffpanel_online'] = 1;
$site_config['irc_autoshout_on'] = 1;
$site_config['mods']['slots'] = true;
$site_config['votesrequired'] = 15;
$site_config['catsperrow'] = 7;
// Latest posts limit
$site_config['latest_posts_limit'] = 5; // query limit for latest forum posts on index
// latest torrents limit
$site_config['latest_torrents_limit'] = 5;
$site_config['latest_torrents_limit_2'] = 5;
$site_config['staff_picks_limit'] = 5;
$site_config['latest_torrents_limit_scroll'] = 25;
$site_config['latest_torrents_limit_slider'] = 25;
/* Settings **/
$site_config['reports'] = 1; // 1/0 on/off
$site_config['karma'] = 1; // 1/0 on/off
$site_config['BBcode'] = 1; // 1/0 on/off
$site_config['inviteusers'] = 10000;
$site_config['flood_time'] = 900; // comment/forum/pm flood limit
$site_config['readpost_expiry'] = 14 * 86400; // 14 days

$site_config['sub_up_dir'] = ROOT_DIR . 'uploadsub'; // must be writable for httpd user
$site_config['flood_file'] = INCL_DIR . 'settings' . DIRECTORY_SEPARATOR . 'limitfile.txt';
$site_config['nameblacklist'] = CACHE_DIR . 'nameblacklist.txt';
$site_config['happyhour'] = CACHE_DIR . 'happyhour.cache';
$site_config['sql_error_log'] = SQLERROR_LOGS_DIR . 'sql_err_' . date('Y_m_d', TIME_NOW) . '.log';

if (empty($_SERVER['HTTP_HOST'])) {
    $_SERVER['HTTP_HOST'] = !empty($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : $site_config['domain'];
}

$site_config['baseurl'] = get_scheme() . '://' . $_SERVER['HTTP_HOST'];

$site_config['msg_alert'] = 1; // saves a query when off
$site_config['report_alert'] = 1; // saves a query when off
$site_config['staffmsg_alert'] = 1; // saves a query when off
$site_config['uploadapp_alert'] = 1; // saves a query when off
$site_config['bug_alert'] = 1; // saves a query when off
$site_config['pic_baseurl'] = '.' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR;
$site_config['pic_baseurl_chat'] = $site_config['baseurl'] . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR;
$site_config['stylesheet'] = 1;
$site_config['categorie_icon'] = 1;
$site_config['comment_min_class'] = 4; // min class to be checked when posting comments
$site_config['comment_check'] = 1; // set it to 0 if you wanna allow commenting with out staff checking
// for imdb, tmdb, fanart, subs, youtube
$site_config['movie_cats'] = [
    2,
    3,
    10,
    11,
];
$site_config['slider_cats'] = [
    2,
    3,
    10,
    11,
];
$site_config['tv_cats'] = [
    5,
];
$site_config['ebook_cats'] = [
    15,
];

$youtube_pattern = "/^http(s)?\:\/\/www\.youtube\.com\/watch\?v\=[\w-]{11}/i";

// Image uploads
$site_config['allowed_exif_types'] = [
    IMAGETYPE_GIF,
    IMAGETYPE_JPEG,
    IMAGETYPE_PNG,
    IMAGETYPE_WEBP,
]; // one for each allowed_ext below

$site_config['allowed_ext'] = [
    'image/gif',
    'image/jpg',
    'image/jpeg',
    'image/png',
    'image/webp',
];

// should match above
$site_config['allowed_formats'] = [
    '.gif',
    '.jpg',
    '.jpeg',
    '.png',
    '.webp',
];

$upload_max_filesize = ini_get('upload_max_filesize') !== null ? return_bytes(ini_get('upload_max_filesize')) : 0;
$post_max_filesize = ini_get('post_max_filesize') !== null ? return_bytes(ini_get('post_max_filesize')) : 0;
$site_config['bucket_maxsize'] = $upload_max_filesize >= $post_max_filesize ? $upload_max_filesize : $post_max_filesize;
$site_config['site']['owner'] = 1;
$site_config['adminer_allowed_ids'] = [
    1,
];

$site_config['staff']['forumid'] = 1;

// Arcade Games
$site_config['arcade_games'] = [
    'asteroids',
    'breakout',
    'frogger',
    'galaga',
    'hexxagon',
    'invaders',
    'moonlander',
    'pacman',
    'psol',
    'simon',
    'snake',
    'tetris',
    'autobahn',
    'ghosts-and-goblins',
    'joust',
    'ms-pac-man',
];
$site_config['arcade_games_names'] = [
    'Asteroids',
    'Breakout',
    'Frogger',
    'Galaga',
    'Hexxagon',
    'Space Invaders',
    'Moonlander',
    'Pacman',
    'Pyramid Solitaire',
    'Simon',
    'Snake',
    'Tetris',
    'Autobahn',
    'Ghosts\'n Goblins',
    'Joust',
    'Ms. Pac-Man',
];
$site_config['top_score_points'] = 1000;

$site_config['bad_words'] = [
    'fuck',
    'shit',
    'Moderator',
    'Administrator',
    'Admin',
    'pussy',
    'Sysop',
    'cunt',
    'nigger',
    'VIP',
    'Super User',
    'Power User',
    'ADMIN',
    'SYSOP',
    'MODERATOR',
    'ADMINISTRATOR',
];
$site_config['notifications'] = [
    'is-danger',
    'is-warning',
    'is-success',
    'is-info',
    'is-link',
];

$site_config['video_banners'] = [];
$site_config['banners'] = [];
$site_config['query_limit'] = 65536; // mysql placeholder limit
