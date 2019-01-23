<?php

global $site_config;

$site_settings = $hnr_settings = $staff_settings = [];

if (PHP_INT_SIZE < 8) {
    die('A 64bit or higher OS + Processor is required.');
}
$site_config['variant'] = 'Pu-239';

// charset
$site_config['char_set'] = 'UTF-8';

// Windows fix
if (!function_exists('sys_getloadavg')) {
    /**
     * @return array
     */
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
require_once CACHE_DIR . 'class_config.php';
require_once CONFIG_DIR . 'expires.php';
require_once CONFIG_DIR . 'hnr.php';

// Tracker configs
$site_config['max_torrent_size'] = 3 * 1024 * 1024;
$site_config['announce_interval'] = 60 * 30;
$site_config['min_interval'] = 60 * 15;
$site_config['connectable_check'] = true;
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
$site_config['reports'] = true;
$site_config['karma'] = true;
$site_config['BBcode'] = true;
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

$site_config['msg_alert'] = true;
$site_config['report_alert'] = true;
$site_config['staffmsg_alert'] = true;
$site_config['uploadapp_alert'] = true;
$site_config['bug_alert'] = true;
$site_config['pic_baseurl'] = '.' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR;
$site_config['pic_baseurl_chat'] = $site_config['baseurl'] . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR;
$site_config['stylesheet'] = 1;
$site_config['categorie_icon'] = 1;
$site_config['comment_check'] = true;
// for imdb, tmdb, fanart, subs, youtube
$site_config['movie_cats'] = [
    13,
    14,
    15,
    16,
    17,
];
$site_config['tv_cats'] = [
    18,
    19,
    20,
    21,
];
$site_config['ebook_cats'] = [
    40,
    41,
    42,
    43,
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

$site_config['tagline'] = [
    'banner' => $site_config['variant'] . ' Code',
    'tagline' => 'Making progress, 1 day at a time...',
];
$site_config['video_banners'] = [];
$site_config['banners'] = [
//    'site_banner_01.png',
//    'site_banner_02.png',
];
$site_config['query_limit'] = 65536; // mysql placeholder limit

// Auto Lottery, you must first start a lottery, then this will restart it based on the settings below
$site_config['auto_lotto'] = [
    'enable' => false,
    'use_prize_fund' => true,
    'prize_fund' => 1000000,
    'ticket_amount' => 100,
    'ticket_amount_type' => 'seedbonus',
    'user_tickets' => 10,
    'class_allowed' => [
        0,
        1,
        2,
        3,
        4,
        5,
        6,
    ],
    'total_winners' => 5,
    'duration' => 7,
];

$site_config['upload_min_class'] = UC_UPLOADER; // min class allowed to upload
$site_config['imdb_language'] = 'en-US';
$site_config['tmdb_language'] = 'en';
$site_config['tmdb_movie_language'] = 'en-US';
$site_config['tmdb_movie_region'] = 'US';
// if this array is empty, then all images will be retrieved from fanart.tv, else only specific languages
$site_config['image_lang'] = [
    'en',
];

$site_config['staff_allowed'] = [
    'fast_delete' => UC_MAX,
    'fast_edit' => UC_STAFF,
    'staff_picks' => UC_MAX,
    'show_edited_by' => UC_MAX,
    'torrents_disable_comments' => UC_MAX,
    'enable_invincible' => UC_MAX,
    'lock_topics' => UC_MAX,
];

$site_config['db_backup_use_gzip'] = true;
$site_config['db_use_gzip'] = true;
$site_config['db_backup_gzip_path'] = '/bin/gzip';
$site_config['db_backup_write_to_log'] = true;
$site_config['db_backup_mysqldump_path'] = '/usr/bin/mysqldump';
$site_config['anonymizer_url'] = 'https://nullrefer.com/?';
$site_config['anonymous_names'] = ['Tom Sawyer', 'Keyser Söze', 'Capt. Kirk', 'Walter Cronkite', 'El Rushbo', 'Simple Simon'];
$site_config['auto_confirm'] = true;
$site_config['autoshout_on'] = true;
$site_config['backgrounds_on_all_pages'] = true;
$site_config['bonus_irc_per_duration'] = .25;
$site_config['bonus_max_torrents'] = 100;
$site_config['bonus_per_comment'] = 3;
$site_config['bonus_per_delete'] = 15;
$site_config['bonus_per_download'] = 20;
$site_config['bonus_per_duration'] = .25;
$site_config['bonus_per_post'] = 5;
$site_config['bonus_per_rating'] = 5;
$site_config['bonus_per_thanks'] = 5;
$site_config['bonus_per_topic'] = 8;
$site_config['bonus_per_upload'] = 15;
$site_config['bucket_allowed'] = true;
$site_config['coders_log_allowed_ext'] = ['php', 'css', 'js'];
$site_config['dupeip_check_on'] = false;
$site_config['email_confirm'] = false;
$site_config['failedlogins'] = 5;
$site_config['forums_online'] = true;
$site_config['image_proxy'] = true;
$site_config['in_production'] = false;
$site_config['invites'] = 5000;
$site_config['maxusers'] = 10000;
$site_config['newsrss_on'] = true;
$site_config['openreg'] = true;
$site_config['openreg_invites'] = true;
$site_config['seedbonus_on'] = true;
$site_config['site_online'] = true;
$site_config['totalneeded'] = 100;
$site_config['use_12_hour'] = true;

$site_config['sql_debug'] = true;
$site_config['ip_logging'] = false;
$site_config['require_connectable'] = true;
$site_config['socket'] = false;
$site_config['nfo_size'] = 65536;

$site_config['pm_deleted'] = 0;
$site_config['pm_inbox'] = 1;
$site_config['pm_sentbox'] = -1;
$site_config['pm_drafts'] = -2;

$site_config['crazy_hour'] = false;
$site_config['happy_hour'] = false;
$site_config['ratio_free'] = false;

$site_config['min_to_play'] = UC_POWER_USER;
