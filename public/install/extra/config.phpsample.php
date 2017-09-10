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
$INSTALLER09['char_set'] = 'UTF-8'; //also to be used site wide in meta tags
if (ini_get('default_charset') != $INSTALLER09['char_set']) {
    ini_set('default_charset', $INSTALLER09['char_set']);
}

//== Windows fix
if (!function_exists('sys_getloadavg')) {
    function sys_getloadavg()
    {
        return [0, 0, 0];
    }
}
7.1.9-1+ub
/* Compare php version for date/time stuff etc! */
if (version_compare(PHP_VERSION, '7.1.0', '>=')) {
    date_default_timezone_set('UTC');
}
define('TIME_NOW', time());
$INSTALLER09['time_adjust'] = 0;
$INSTALLER09['time_offset'] = '0';
$INSTALLER09['time_use_relative'] = 1;
$INSTALLER09['time_use_relative_format'] = '{--}, h:i A';
$INSTALLER09['time_joined'] = 'j-F y';
$INSTALLER09['time_short'] = 'jS F Y - h:i A';
$INSTALLER09['time_long'] = 'M j Y, h:i A';
$INSTALLER09['time_tiny'] = '';
$INSTALLER09['time_date'] = '';
//== DB setup
$INSTALLER09['mysql_host'] = '#mysql_host';
$INSTALLER09['mysql_user'] = '#mysql_user';
$INSTALLER09['mysql_pass'] = '#mysql_pass';
$INSTALLER09['mysql_db'] = '#mysql_db';
//== Cookie setup
$INSTALLER09['sessionName'] = '#sessionName'; // A single word that identifies this install.
$INSTALLER09['cookie_prefix'] = '#cookie_prefix' . '_'; // This allows you to have multiple trackers, eg for demos, testing etc.
$INSTALLER09['cookie_path'] = '#cookie_path'; // ATTENTION: You should never need this unless the above applies eg: /tbdev
$INSTALLER09['cookie_domain'] = '#cookie_domain'; // set to eg: .somedomain.com or is subdomain set to: .sub.somedomain.com
$INSTALLER09['cookie_lifetime'] = '#cookie_lifetime'; // length of time cookies will be valid
$INSTALLER09['domain'] = '.' . '#domain';
$INSTALLER09['sessionCookieSecure'] = '#sessionCookieSecure'; // using HTTPS only? then set this
$INSTALLER09['sessionKeyPrefix'] = '#cookie_prefix' . '_'; // usefull if serving multiple sites
$INSTALLER09['session_csrf'] = 'csrf_token'; // usefull if serving multiple sites
//== Memcache expires
$INSTALLER09['expires']['latestuser'] = 0; // 0 = infinite
$INSTALLER09['expires']['MyPeers_'] = 120; // 60 = 60 seconds
$INSTALLER09['expires']['unread'] = 86400; // 86400 = 1 day
$INSTALLER09['expires']['alerts'] = 0; // 0 = infinite
$INSTALLER09['expires']['searchcloud'] = 0; // 0 = infinite
$INSTALLER09['expires']['user_cache'] = 30 * 86400; // 30 days
$INSTALLER09['expires']['curuser'] = 30 * 86400; // 30 days
$INSTALLER09['expires']['u_status'] = 30 * 84600; // 30x86400 = 30 days
$INSTALLER09['expires']['u_stats'] = 300; // 300 = 5 min
$INSTALLER09['expires']['u_stats_xbt'] = 30; // 30 seconds
$INSTALLER09['expires']['user_status'] = 30 * 84600; // 30x86400 = 30 days
$INSTALLER09['expires']['user_stats'] = 300; // 300 = 5 min
$INSTALLER09['expires']['user_stats_xbt'] = 30; // 30 seconds
$INSTALLER09['expires']['MyPeers_xbt_'] = 30;
$INSTALLER09['expires']['announcement'] = 600; // 600 = 10 min
$INSTALLER09['expires']['forum_posts'] = 0;
$INSTALLER09['expires']['torrent_comments'] = 900; // 900 = 15 min
$INSTALLER09['expires']['latestposts'] = 0; // 900 = 15 min
$INSTALLER09['expires']['top5_torrents'] = 0; // 0 = infinite
$INSTALLER09['expires']['last5_torrents'] = 0; // 0 = infinite
$INSTALLER09['expires']['scroll_torrents'] = 0; // 0 = infinite
$INSTALLER09['expires']['torrent_details'] = 30 * 86400; // = 30 days
$INSTALLER09['expires']['torrent_details_text'] = 30 * 86400; // = 30 days
$INSTALLER09['expires']['insertJumpTo'] = 30 * 86400; // = 30 days
$INSTALLER09['expires']['get_all_boxes'] = 30 * 86400; // = 30 days
$INSTALLER09['expires']['thumbsup'] = 0; // 0 = infinite
$INSTALLER09['expires']['iphistory'] = 900; // 900 = 15 min
$INSTALLER09['expires']['newpoll'] = 0; // 900 = 15 min
$INSTALLER09['expires']['genrelist'] = 30 * 86400; // 30x86400 = 30 days
$INSTALLER09['expires']['genrelist2'] = 30 * 86400; // 30x86400 = 30 days
$INSTALLER09['expires']['poll_data'] = 900; // 300 = 5 min
$INSTALLER09['expires']['torrent_data'] = 900; // 900 = 15 min
$INSTALLER09['expires']['user_flag'] = 86400 * 28; // 900 = 15 min
$INSTALLER09['expires']['shit_list'] = 900; // 900 = 15 min
$INSTALLER09['expires']['port_data'] = 900; // 900 = 15 min
$INSTALLER09['expires']['port_data_xbt'] = 900; // 900 = 15 min
$INSTALLER09['expires']['user_peers'] = 900; // 900 = 15 min
$INSTALLER09['expires']['user_friends'] = 900; // 900 = 15 min
$INSTALLER09['expires']['user_hash'] = 900; // 900 = 15 min
$INSTALLER09['expires']['user_blocks'] = 900; // 900 = 15 min
$INSTALLER09['expires']['hnr_data'] = 300; // 300 = 5 min
$INSTALLER09['expires']['snatch_data'] = 300; // 300 = 5 min
$INSTALLER09['expires']['user_snatches_data'] = 300; // 300 = 5 min
$INSTALLER09['expires']['staff_snatches_data'] = 300; // 300 = 5 min
$INSTALLER09['expires']['user_snatches_complete'] = 300; // 300 = 5 min
$INSTALLER09['expires']['completed_torrents'] = 300; // 300 = 5 min
$INSTALLER09['expires']['activeusers'] = 60; // 60 = 1 minutes
$INSTALLER09['expires']['forum_users'] = 30; // 60 = 1 minutes
$INSTALLER09['expires']['section_view'] = 30; // 60 = 1 minutes
$INSTALLER09['expires']['child_boards'] = 900; // 60 = 1 minutes
$INSTALLER09['expires']['sv_child_boards'] = 900; // 60 = 1 minutes
$INSTALLER09['expires']['forum_insertJumpTo'] = 3600; // = 1 hour
$INSTALLER09['expires']['last_post'] = 0; // infinite
$INSTALLER09['expires']['sv_last_post'] = 0; // infinite
$INSTALLER09['expires']['last_read_post'] = 0; // infinite
$INSTALLER09['expires']['sv_last_read_post'] = 0; // infinite
$INSTALLER09['expires']['last24'] = 3600; // 3600 = 1 hours
$INSTALLER09['expires']['activeircusers'] = 300; // 300 = 5 min
$INSTALLER09['expires']['birthdayusers'] = 43200; //== 43200 = 12 hours
$INSTALLER09['expires']['news_users'] = 3600; // 3600 = 1 hours
$INSTALLER09['expires']['user_invitees'] = 900; // 900 = 15 min
$INSTALLER09['expires']['ip_data'] = 900; // 900 = 15 min
$INSTALLER09['expires']['latesttorrents'] = 0; // 0 = infinite
$INSTALLER09['expires']['invited_by'] = 900; // 900 = 15 min
$INSTALLER09['expires']['user_torrents'] = 900; // 900 = 15 min
$INSTALLER09['expires']['user_seedleech'] = 900; // 900 = 15 min
$INSTALLER09['expires']['radio'] = 0; // 0 = infinite
$INSTALLER09['expires']['total_funds'] = 0; // 0 = infinite
$INSTALLER09['expires']['latest_news'] = 0; // 0 = infinite
$INSTALLER09['expires']['site_stats'] = 300; // 300 = 5 min
$INSTALLER09['expires']['share_ratio'] = 900; // 900 = 15 min
$INSTALLER09['expires']['share_ratio_xbt'] = 900; // 900 = 15 min
$INSTALLER09['expires']['checked_by'] = 0; // 0 = infinite
$INSTALLER09['expires']['sanity'] = 0; // 0 = infinite
$INSTALLER09['expires']['movieofweek'] = 300; // 604800 = 1 week
$INSTALLER09['expires']['browse_where'] = 60; // 60 = 60 seconds
$INSTALLER09['expires']['torrent_xbt_data'] = 300; // 300 = 5 min
$INSTALLER09['expires']['ismoddin'] = 0; // 0 = infinite
//== Tracker configs
$INSTALLER09['tracker_post_key'] = 'lsdflksfda4545frwe35@kk';
$INSTALLER09['max_torrent_size'] = 3 * 1024 * 1024;
$INSTALLER09['announce_interval'] = 60 * 30;
$INSTALLER09['signup_timeout'] = 86400 * 3;
$INSTALLER09['autoclean_interval'] = 1800;
$INSTALLER09['sub_max_size'] = 500 * 1024;
$INSTALLER09['minvotes'] = 1;
$INSTALLER09['max_dead_torrent_time'] = 6 * 3600;
$INSTALLER09['language'] = 1;
// Site Bot
$INSTALLER09['chatBotID'] = 2;
$INSTALLER09['chatBotName'] = 'CraftyBOT'; // Change to your Bot's Name
$INSTALLER09['chatBotRole'] = 100;
$INSTALLER09['staffpanel_online'] = 1;
$INSTALLER09['irc_autoshout_on'] = 1;
$INSTALLER09['crazy_hour'] = false; //== Off for XBT
$INSTALLER09['happy_hour'] = false; //== Off for XBT
$INSTALLER09['mods']['slots'] = true;
$INSTALLER09['votesrequired'] = 15;
$INSTALLER09['catsperrow'] = 7;
$INSTALLER09['maxwidth'] = '90%';
//== Latest posts limit
$INSTALLER09['latest_posts_limit'] = 5; //query limit for latest forum posts on index
//latest torrents limit
$INSTALLER09['latest_torrents_limit'] = 5;
$INSTALLER09['latest_torrents_limit_2'] = 5;
$INSTALLER09['latest_torrents_limit_scroll'] = 20;
/* Settings **/
$INSTALLER09['reports'] = 1; // 1/0 on/off
$INSTALLER09['karma'] = 1; // 1/0 on/off
$INSTALLER09['BBcode'] = 1; // 1/0 on/off
$INSTALLER09['inviteusers'] = 10000;
$INSTALLER09['flood_time'] = 900; //comment/forum/pm flood limit
$INSTALLER09['readpost_expiry'] = 14 * 86400; // 14 days
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
$INSTALLER09['cache'] = ROOT_DIR . 'cache';
$INSTALLER09['backup_dir'] = INCL_DIR . 'backup';
$INSTALLER09['dictbreaker'] = ROOT_DIR . 'dictbreaker';
$INSTALLER09['torrent_dir'] = ROOT_DIR . 'torrents'; // must be writable for httpd user
$INSTALLER09['sub_up_dir'] = ROOT_DIR . 'uploadsub'; // must be writable for httpd user
$INSTALLER09['flood_file'] = INCL_DIR . 'settings' . DIRECTORY_SEPARATOR . 'limitfile.txt';
$INSTALLER09['nameblacklist'] = ROOT_DIR . 'cache' . DIRECTORY_SEPARATOR . 'nameblacklist.txt';
$INSTALLER09['happyhour'] = CACHE_DIR . 'happyhour' . DIRECTORY_SEPARATOR . 'happyhour.txt';
$INSTALLER09['sql_error_log'] = ROOT_DIR . 'sqlerr_logs' . DIRECTORY_SEPARATOR . 'sql_err_' . date('M_D_Y') . '.log';
//== XBT or PHP announce
if (XBT_TRACKER == true) {
    $INSTALLER09['xbt_prefix'] = '#announce_urls:2710/';
    $INSTALLER09['xbt_suffix'] = '/announce';
    $INSTALLER09['announce_urls'][] = '#announce_urls:2710/announce';
} else {
    $INSTALLER09['announce_urls'] = [];
    $INSTALLER09['announce_urls'][] = '#announce_urls';
    $INSTALLER09['announce_urls'][] = '#announce_https';
}
if (isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'] == '') {
    $_SERVER['HTTP_HOST'] = $_SERVER['SERVER_NAME'];
}
$INSTALLER09['baseurl'] = 'http'.(isset($_SERVER['HTTPS']) && (bool) $_SERVER['HTTPS'] == true ? 's' : '').'://'.$_SERVER['HTTP_HOST'];
//== Email for sender/return path.
$INSTALLER09['site_email'] = '#site_email';
$INSTALLER09['site_name'] = '#site_name';
$INSTALLER09['msg_alert'] = 1; // saves a query when off
$INSTALLER09['report_alert'] = 1; // saves a query when off
$INSTALLER09['staffmsg_alert'] = 1; // saves a query when off
$INSTALLER09['uploadapp_alert'] = 1; // saves a query when off
$INSTALLER09['bug_alert'] = 1; // saves a query when off
$INSTALLER09['pic_base_url'] = './pic/';
$INSTALLER09['stylesheet'] = 1;
$INSTALLER09['categorie_icon'] = 1;
$INSTALLER09['comment_min_class'] = 4; //minim class to be checked when posting comments
$INSTALLER09['comment_check'] = 1; //set it to 0 if you wanna allow commenting with out staff checking
//for subs & youtube mode
$INSTALLER09['movie_cats'] = [
    3,
    5,
    6,
    10,
    11,
];
$INSTALLER09['slider_cats'] = [
    3,
    5,
    6,
    10,
    11,
];
$INSTALLER09['moviecats'] = '3,5,6,10,11';
$youtube_pattern = "/^http(s)?\:\/\/www\.youtube\.com\/watch\?v\=[\w-]{11}/i";
//== set this to size of user avatars
$INSTALLER09['av_img_height'] = 100;
$INSTALLER09['av_img_width'] = 100;
//== set this to size of user signatures
$INSTALLER09['sig_img_height'] = 100;
$INSTALLER09['sig_img_width'] = 500;

//== Image uploads
define('BITBUCKET_DIR', ROOT_DIR . 'bucket' . DIRECTORY_SEPARATOR);
define('AVATAR_DIR', BITBUCKET_DIR . 'avatar' . DIRECTORY_SEPARATOR);
$INSTALLER09['bucket_allowed'] = 0;
$INSTALLER09['allowed_ext'] = [
    'image/gif',
    'image.jpg',
    'image/jpeg',
];
$INSTALLER09['bucket_maxsize'] = 1024 * 1024 * (ini_get('upload_max_filesize') >= ini_get('post_max_filesize') ? ini_get('upload_max_filesize') : ini_get('post_max_filesize'));
//==Class check by pdq
$INSTALLER09['site']['owner'] = 1;
//== Salt - change this
$INSTALLER09['site']['salt'] = 'jgutyxcjsak2';
//= Change staff pin daily or weekly
$INSTALLER09['staff']['staff_pin'] = 'uFie0y3Ihjkij8'; // should be mix of u/l case and min 12 chars length
//= Change owner pin daily or weekly
$INSTALLER09['staff']['owner_pin'] = 'jjko4kuogqhjj0'; // should be mix of u/l case and min 12 chars length
//== Staff forum ID for autopost
$INSTALLER09['staff']['forumid'] = 2; // this forum ID should exist and be a staff forum
$INSTALLER09['staff_forums'] = [
    1,
    2,
]; // these forum ID's' should exist and be a staff forum's to stop autoshouts
$INSTALLER09['variant'] = 'Pu-239';
define('TBVERSION', $INSTALLER09['variant']);

// Arcade Games
$INSTALLER09['arcade_games'] = array('asteroids', 'breakout', 'frogger', 'galaga', 'hexxagon', 'invaders', 'moonlander', 'pacman', 'psol', 'simon', 'snake', 'tetris', 'autobahn', 'ghosts-and-goblins', 'joust', 'ms-pac-man');
$INSTALLER09['arcade_games_names'] = array('Asteroids', 'Breakout', 'Frogger', 'Galaga', 'Hexxagon', 'Space Invaders', 'Moonlander', 'Pacman', 'Pyramid Solitaire', 'Simon', 'Snake', 'Tetris', 'Autobahn', 'Ghosts\'n Goblins', 'Joust', 'Ms. Pac-Man');
$INSTALLER09['top_score_points'] = 1000;

$INSTALLER09['bad_words'] = ['fuck', 'shit', 'Moderator', 'Administrator', 'Admin', 'pussy', 'Sysop', 'cunt', 'nigger', 'VIP', 'Super User', 'Power User', 'ADMIN', 'SYSOP', 'MODERATOR', 'ADMINISTRATOR'];

