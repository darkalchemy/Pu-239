<?php
const REQUIRED_PHP = 70100, REQUIRED_PHP_VERSION = '7.1.0';
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
define('SQL_DEBUG', 1);
define('XBT_TRACKER', false);
//==charset
$site_config['char_set'] = 'UTF-8'; //also to be used site wide in meta tags
if (ini_get('default_charset') != $site_config['char_set']) {
    ini_set('default_charset', $site_config['char_set']);
}

//== Windows fix
if (!function_exists('sys_getloadavg')) {
    function sys_getloadavg()
    {
        return [0, 0, 0];
    }
}

/* Compare php version for date/time stuff etc! */
if (version_compare(PHP_VERSION, '7.1.0', '>=')) {
    date_default_timezone_set('UTC');
}
define('TIME_NOW', time());
$site_config['production'] = false; // use minified css/js or not
$site_config['time_adjust'] = 0;
$site_config['time_offset'] = '0';
$site_config['time_use_relative'] = 1;
$site_config['time_use_relative_format'] = '{--}, h:i A';
$site_config['time_joined'] = 'j-F y';
$site_config['time_short'] = 'jS F Y - h:i A';
$site_config['time_long'] = 'M j Y, h:i A';
$site_config['time_tiny'] = '';
$site_config['time_date'] = '';
//== DB setup
$site_config['mysql_host'] = '#mysql_host';
$site_config['mysql_user'] = '#mysql_user';
$site_config['mysql_pass'] = '#mysql_pass';
$site_config['mysql_db'] = '#mysql_db';
//== Cookie setup
$site_config['sessionName'] = '#sessionName'; // A single word that identifies this install.
$site_config['cookie_prefix'] = '#cookie_prefix_'; // This allows you to have multiple trackers, eg for demos, testing etc.
$site_config['cookie_path'] = '#cookie_path'; // ATTENTION: You should never need this unless the above applies eg: /tbdev
$site_config['cookie_domain'] = '.#cookie_domain'; // set to eg: .somedomain.com or is subdomain set to: .sub.somedomain.com
$site_config['cookie_lifetime'] = #cookie_lifetime; // length of time cookies will be valid
$site_config['domain'] = '#domain';
$site_config['sessionCookieSecure'] = #sessionCookieSecure; // using HTTPS only? then set this
$site_config['sessionKeyPrefix'] = '#cookie_prefix_'; // usefull if serving multiple sites
$site_config['session_csrf'] = 'csrf_token'; // usefull if serving multiple sites
//== Memcache expires
$site_config['expires']['latestuser'] = 0; // 0 = infinite
$site_config['expires']['MyPeers_'] = 120; // 60 = 60 seconds
$site_config['expires']['unread'] = 86400; // 86400 = 1 day
$site_config['expires']['alerts'] = 0; // 0 = infinite
$site_config['expires']['searchcloud'] = 0; // 0 = infinite
$site_config['expires']['user_cache'] = 30 * 86400; // 30 days
$site_config['expires']['curuser'] = 30 * 86400; // 30 days
$site_config['expires']['u_status'] = 30 * 84600; // 30x86400 = 30 days
$site_config['expires']['u_stats'] = 300; // 300 = 5 min
$site_config['expires']['u_stats_xbt'] = 30; // 30 seconds
$site_config['expires']['user_status'] = 30 * 84600; // 30x86400 = 30 days
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
$site_config['expires']['torrent_details'] = 30 * 86400; // = 30 days
$site_config['expires']['torrent_details_text'] = 30 * 86400; // = 30 days
$site_config['expires']['insertJumpTo'] = 30 * 86400; // = 30 days
$site_config['expires']['get_all_boxes'] = 30 * 86400; // = 30 days
$site_config['expires']['thumbsup'] = 0; // 0 = infinite
$site_config['expires']['iphistory'] = 900; // 900 = 15 min
$site_config['expires']['newpoll'] = 0; // 900 = 15 min
$site_config['expires']['genrelist'] = 30 * 86400; // 30x86400 = 30 days
$site_config['expires']['genrelist2'] = 30 * 86400; // 30x86400 = 30 days
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
$site_config['chatBotName'] = 'CraftyBOT'; // Change to your Bot's Name
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
/* define dirs **/
define('INCL_DIR', dirname(__FILE__) . DIRECTORY_SEPARATOR);
define('ROOT_DIR', realpath(INCL_DIR . '..' . DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
define('ADMIN_DIR', ROOT_DIR . 'admin' . DIRECTORY_SEPARATOR);
define('FORUM_DIR', ROOT_DIR . 'forums' . DIRECTORY_SEPARATOR);
define('PM_DIR', ROOT_DIR . 'pm_system' . DIRECTORY_SEPARATOR);
define('PIMP_DIR', ROOT_DIR . 'PimpMyLog' . DIRECTORY_SEPARATOR);
define('CACHE_DIR', ROOT_DIR . 'cache' . DIRECTORY_SEPARATOR);
define('MODS_DIR', ROOT_DIR . 'mods' . DIRECTORY_SEPARATOR);
define('LANG_DIR', ROOT_DIR . 'lang' . DIRECTORY_SEPARATOR);
define('TEMPLATE_DIR', ROOT_DIR . 'templates' . DIRECTORY_SEPARATOR);
define('BLOCK_DIR', ROOT_DIR . 'blocks' . DIRECTORY_SEPARATOR);
define('IMDB_DIR', ROOT_DIR . 'imdb' . DIRECTORY_SEPARATOR);
define('CLASS_DIR', INCL_DIR . 'class' . DIRECTORY_SEPARATOR);
define('CLEAN_DIR', INCL_DIR . 'cleanup' . DIRECTORY_SEPARATOR);
define('LOGVIEW_DIR', ADMIN_DIR . 'Log_Viewer' . DIRECTORY_SEPARATOR);
$site_config['cache'] = ROOT_DIR . 'cache';
$site_config['backup_dir'] = INCL_DIR . 'backup';
$site_config['dictbreaker'] = ROOT_DIR . 'dictbreaker';
$site_config['torrent_dir'] = ROOT_DIR . 'torrents'; // must be writable for httpd user
$site_config['sub_up_dir'] = ROOT_DIR . 'uploadsub'; // must be writable for httpd user
$site_config['flood_file'] = INCL_DIR . 'settings' . DIRECTORY_SEPARATOR . 'limitfile.txt';
$site_config['nameblacklist'] = ROOT_DIR . 'cache' . DIRECTORY_SEPARATOR . 'nameblacklist.txt';
$site_config['happyhour'] = CACHE_DIR . 'happyhour' . DIRECTORY_SEPARATOR . 'happyhour.txt';
$site_config['sql_error_log'] = ROOT_DIR . 'sqlerr_logs' . DIRECTORY_SEPARATOR . 'sql_err_' . date('M_D_Y') . '.log';
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
$site_config['baseurl'] = 'http'.(isset($_SERVER['HTTPS']) && (bool) $_SERVER['HTTPS'] == true ? 's' : '').'://'.$_SERVER['HTTP_HOST'];
//== Email for sender/return path.
$site_config['site_email'] = '#site_email';
$site_config['site_name'] = '#site_name';
$site_config['msg_alert'] = 1; // saves a query when off
$site_config['report_alert'] = 1; // saves a query when off
$site_config['staffmsg_alert'] = 1; // saves a query when off
$site_config['uploadapp_alert'] = 1; // saves a query when off
$site_config['bug_alert'] = 1; // saves a query when off
$site_config['pic_base_url'] = './images/';
$site_config['stylesheet'] = 1;
$site_config['categorie_icon'] = 1;
$site_config['comment_min_class'] = 4; //minim class to be checked when posting comments
$site_config['comment_check'] = 1; //set it to 0 if you wanna allow commenting with out staff checking
//for subs & youtube mode
$site_config['movie_cats'] = [
    3,
    5,
    6,
    10,
    11,
];
$site_config['slider_cats'] = [
    3,
    5,
    6,
    10,
    11,
];
$site_config['moviecats'] = '3,5,6,10,11';
$youtube_pattern = "/^http(s)?\:\/\/www\.youtube\.com\/watch\?v\=[\w-]{11}/i";
//== set this to size of user avatars
$site_config['av_img_height'] = 100;
$site_config['av_img_width'] = 100;
//== set this to size of user signatures
$site_config['sig_img_height'] = 100;
$site_config['sig_img_width'] = 500;

//== Image uploads
define('BITBUCKET_DIR', ROOT_DIR . 'bucket' . DIRECTORY_SEPARATOR);
define('AVATAR_DIR', BITBUCKET_DIR . 'avatar' . DIRECTORY_SEPARATOR);
$site_config['bucket_allowed'] = 0;
$site_config['allowed_ext'] = [
    'image/gif',
    'image.jpg',
    'image/jpeg',
];
$site_config['bucket_maxsize'] = 1024 * 1024 * (ini_get('upload_max_filesize') >= ini_get('post_max_filesize') ? ini_get('upload_max_filesize') : ini_get('post_max_filesize'));
//==Class check by pdq
$site_config['site']['owner'] = 1;
//== Salt - change this
$site_config['site']['salt'] = 'jgutyxcjsak2';
//= Change staff pin daily or weekly
$site_config['staff']['staff_pin'] = 'uFie0y3Ihjkij8'; // should be mix of u/l case and min 12 chars length
//= Change owner pin daily or weekly
$site_config['staff']['owner_pin'] = 'jjko4kuogqhjj0'; // should be mix of u/l case and min 12 chars length
//== Staff forum ID for autopost
$site_config['staff']['forumid'] = 2; // this forum ID should exist and be a staff forum
$site_config['staff_forums'] = [
    1,
    2,
]; // these forum ID's' should exist and be a staff forum's to stop autoshouts
$site_config['variant'] = 'Pu-239';
define('TBVERSION', $site_config['variant']);

// Arcade Games
$site_config['arcade_games'] = array('asteroids', 'breakout', 'frogger', 'galaga', 'hexxagon', 'invaders', 'moonlander', 'pacman', 'psol', 'simon', 'snake', 'tetris', 'autobahn', 'ghosts-and-goblins', 'joust', 'ms-pac-man');
$site_config['arcade_games_names'] = array('Asteroids', 'Breakout', 'Frogger', 'Galaga', 'Hexxagon', 'Space Invaders', 'Moonlander', 'Pacman', 'Pyramid Solitaire', 'Simon', 'Snake', 'Tetris', 'Autobahn', 'Ghosts\'n Goblins', 'Joust', 'Ms. Pac-Man');
$site_config['top_score_points'] = 1000;

$site_config['bad_words'] = ['fuck', 'shit', 'Moderator', 'Administrator', 'Admin', 'pussy', 'Sysop', 'cunt', 'nigger', 'VIP', 'Super User', 'Power User', 'ADMIN', 'SYSOP', 'MODERATOR', 'ADMINISTRATOR'];

