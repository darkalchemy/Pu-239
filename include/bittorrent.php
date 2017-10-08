<?php
$start = microtime(true);

if (!file_exists(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'config.php')) {
    header('Location: /install');
    exit();
}

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'config.php';
require_once INCL_DIR . 'files.php';

// start session on every page request
sessionStart();

require_once CACHE_DIR . 'free_cache.php';
require_once CACHE_DIR . 'class_config.php';
//==Start memcache
require_once CLASS_DIR . 'class_cache.php';
$mc1 = new CACHE();

//==Block class
class curuser
{
    public static $blocks = [];
}

$CURBLOCK = &curuser::$blocks;
require_once CLASS_DIR . 'class_blocks_index.php';
require_once CLASS_DIR . 'class_blocks_stdhead.php';
require_once CLASS_DIR . 'class_blocks_userdetails.php';
require_once CLASS_DIR . 'class_bt_options.php';
require_once CACHE_DIR . 'block_settings_cache.php';
require_once INCL_DIR . 'password_functions.php';
require_once INCL_DIR . 'site_config.php';

$load = sys_getloadavg();
if ($load[0] > 20) {
    die('Load is too high, Dont continuously refresh, or you will just make the problem last longer');
}
if (preg_match('/(?:\< *(?:java|script)|script\:|\+document\.)/i', serialize($_SERVER))) {
    die('Forbidden');
}
if (preg_match('/(?:\< *(?:java|script)|script\:|\+document\.)/i', serialize($_GET))) {
    die('Forbidden');
}
if (preg_match('/(?:\< *(?:java|script)|script\:|\+document\.)/i', serialize($_POST))) {
    die('Forbidden');
}
if (preg_match('/(?:\< *(?:java|script)|script\:|\+document\.)/i', serialize($_COOKIE))) {
    die('Forbidden');
}
function cleanquotes(&$in)
{
    if (is_array($in)) {
        return array_walk($in, 'cleanquotes');
    }

    return $in = stripslashes($in);
}

if (get_magic_quotes_gpc()) {
    array_walk($_GET, 'cleanquotes');
    array_walk($_POST, 'cleanquotes');
    array_walk($_COOKIE, 'cleanquotes');
    array_walk($_REQUEST, 'cleanquotes');
}
function htmlsafechars($txt = '')
{
    $txt = preg_replace('/&(?!#[0-9]+;)(?:amp;)?/s', '&amp;', $txt);
    $txt = str_replace([
        '<',
        '>',
        '"',
        "'",
    ], [
        '&lt;',
        '&gt;',
        '&quot;',
        '&#039;',
    ], $txt);

    return $txt;
}

function PostKey($ids = [])
{
    global $site_config;
    if (!is_array($ids)) {
        return false;
    }

    return md5($site_config['tracker_post_key'] . join('', $ids) . $site_config['tracker_post_key']);
}

function CheckPostKey($ids, $key)
{
    global $site_config;
    if (!is_array($ids) or !$key) {
        return false;
    }

    return $key == md5($site_config['tracker_post_key'] . join('', $ids) . $site_config['tracker_post_key']);
}

function validip($ip)
{
    return filter_var($ip, FILTER_VALIDATE_IP, [
        'flags' => FILTER_FLAG_NO_PRIV_RANGE,
        FILTER_FLAG_NO_RES_RANGE,
    ]) ? true : false;
}

function getip()
{
    foreach ([
                 'HTTP_CLIENT_IP',
                 'HTTP_X_FORWARDED_FOR',
                 'HTTP_X_FORWARDED',
                 'HTTP_X_CLUSTER_CLIENT_IP',
                 'HTTP_FORWARDED_FOR',
                 'HTTP_FORWARDED',
                 'REMOTE_ADDR',
             ] as $key) {
        if (array_key_exists($key, $_SERVER) === true) {
            foreach (array_map('trim', explode(',', $_SERVER[$key])) as $ip) {
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                    return $ip;
                }
            }
        }
    }
}

function dbconn($autoclean = true)
{
    global $site_config;
    if (!@($GLOBALS['___mysqli_ston'] = mysqli_connect($site_config['mysql_host'], $site_config['mysql_user'], $site_config['mysql_pass']))) {
        switch (((is_object($GLOBALS['___mysqli_ston'])) ? mysqli_errno($GLOBALS['___mysqli_ston']) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false))) {
            case 1040:
            case 2002:
                if ($_SERVER['REQUEST_METHOD'] == 'GET') {
                    die("<html><head><meta http-equiv='refresh' content=\"5 $_SERVER[REQUEST_URI]\"></head><body><table border='0' width='100%' height='100%'><tr><td><h3>The server load is very high at the moment. Retrying, please wait...</h3></td></tr></table></body></html>");
                } else {
                    die('Too many users. Please press the Refresh button in your browser to retry.');
                }
            // no break
            default:
                die('[' . ((is_object($GLOBALS['___mysqli_ston'])) ? mysqli_errno($GLOBALS['___mysqli_ston']) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)) . '] dbconn: mysql_connect: ' . ((is_object($GLOBALS['___mysqli_ston'])) ? mysqli_error($GLOBALS['___mysqli_ston']) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
        }
    }
    ((bool)mysqli_query($GLOBALS['___mysqli_ston'], "USE {$site_config['mysql_db']}")) or die('dbconn: mysql_select_db: ' . ((is_object($GLOBALS['___mysqli_ston'])) ? mysqli_error($GLOBALS['___mysqli_ston']) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
    if ($autoclean) {
        register_shutdown_function('autoclean');
    }
}

function status_change($id)
{
    sql_query('UPDATE announcement_process SET status = 0 WHERE user_id = ' . sqlesc($id) . ' AND status = 1') or sqlerr(__FILE__, __LINE__);
}

function hashit($var, $addtext = '')
{
    return md5('Th15T3xt' . $addtext . $var . $addtext . 'is5add3dto66uddy6he@water...');
}

function check_bans($ip, &$reason = '')
{
    global $site_config, $mc1;
    if (empty($ip)) {
        return false;
    }
    $key = 'bans:::' . $ip;
    if (($ban = $mc1->get_value($key)) === false) {
        $nip = ipToStorageFormat($ip);
        $ban_sql = sql_query('SELECT comment FROM bans WHERE (first <= ' . sqlesc($nip) . ' AND last >= ' . sqlesc($nip) . ') LIMIT 1') or sqlerr(__FILE__, __LINE__);
        if (mysqli_num_rows($ban_sql)) {
            $comment = mysqli_fetch_row($ban_sql);
            $reason = 'Manual Ban (' . $comment[0] . ')';
            $mc1->cache_value($key, $reason, 86400); // 86400 // banned

            return true;
        }
        ((mysqli_free_result($ban_sql) || (is_object($ban_sql) && (get_class($ban_sql) == 'mysqli_result'))) ? true : false);
        $mc1->cache_value($key, 0, 86400); // 86400 // not banned

        return false;
    } elseif (!$ban) {
        return false;
    } else {
        $reason = $ban;

        return true;
    }
}

function logincookie($id, $updatedb = true)
{
    if ($updatedb) {
        sql_query("UPDATE users SET last_login = " . TIME_NOW . " WHERE id = " . sqlesc($id)) or sqlerr(__file__, __line__);
    }
}

function userlogin()
{
    global $site_config, $mc1, $CURBLOCK, $mood, $whereis, $CURUSER;
    unset($GLOBALS['CURUSER']);
    $dt = TIME_NOW;
    $ip = getip();
    $nip = ipToStorageFormat($ip);
    $ipf = $_SERVER['REMOTE_ADDR'];
    if (isset($CURUSER)) {
        return;
    }
    if (!$site_config['site_online']) {
        return;
    }
    $id = getSessionVar('userID');
    if (!$id) {
        return;
    }
    if (($row = $mc1->get_value('MyUser_' . $id)) === false) {
        $user_fields_ar_int = [
            'id',
            'added',
            'last_login',
            'last_access',
            'curr_ann_last_check',
            'curr_ann_id',
            'stylesheet',
            'class',
            'override_class',
            'language',
            'av_w',
            'av_h',
            'country',
            'warned',
            'torrentsperpage',
            'topicsperpage',
            'postsperpage',
            'ajaxchat_height',
            'reputation',
            'dst_in_use',
            'auto_correct_dst',
            'chatpost',
            'smile_until',
            'vip_until',
            'freeslots',
            'free_switch',
            'reputation',
            'invites',
            'invitedby',
            'uploadpos',
            'forumpost',
            'downloadpos',
            'immunity',
            'leechwarn',
            'last_browse',
            'sig_w',
            'sig_h',
            'forum_access',
            'hit_and_run_total',
            'donoruntil',
            'donated',
            'vipclass_before',
            'passhint',
            'avatarpos',
            'sendpmpos',
            'invitedate',
            'anonymous_until',
            'pirate',
            'king',
            'ssluse',
            'paranoia',
            'parked_until',
            'bjwins',
            'bjlosses',
            'irctotal',
            'last_access_numb',
            'onlinetime',
            'hits',
            'comments',
            'categorie_icon',
            'perms',
            'mood',
            'pms_per_page',
            'watched_user',
            'game_access',
            'opt1',
            'opt2',
            'can_leech',
            'wait_time',
            'torrents_limit',
            'peers_limit',
        ];
        $user_fields_ar_float = [
            'time_offset',
            'total_donated',
        ];
        $user_fields_ar_str = [
            'username',
            'torrent_pass',
            'email',
            'status',
            'privacy',
            'info',
            'acceptpms',
            'ip',
            'avatar',
            'title',
            'notifs',
            'enabled',
            'donor',
            'deletepms',
            'savepms',
            'vip_added',
            'invite_rights',
            'anonymous',
            'disable_reason',
            'clear_new_tag_manually',
            'signatures',
            'signature',
            'highspeed',
            'hnrwarn',
            'parked',
            'support',
            'supportfor',
            'invitees',
            'invite_on',
            'subscription_pm',
            'gender',
            'viewscloud',
            'tenpercent',
            'avatars',
            'offavatar',
            'hidecur',
            'signature_post',
            'forum_post',
            'avatar_rights',
            'offensive_avatar',
            'view_offensive_avatar',
            'google_talk',
            'msn',
            'aim',
            'yahoo',
            'website',
            'icq',
            'show_email',
            'gotgift',
            'suspended',
            'warn_reason',
            'onirc',
            'birthday',
            'got_blocks',
            'pm_on_delete',
            'commentpm',
            'split',
            'browser',
            'got_moods',
            'show_pm_avatar',
            'watched_user_reason',
            'staff_notes',
            'where_is',
            'forum_sort',
            'browse_icons',
        ];
        $user_fields = implode(', ', array_merge($user_fields_ar_int, $user_fields_ar_float, $user_fields_ar_str));
        $res = sql_query('SELECT ' . $user_fields . ' ' . 'FROM users ' . 'WHERE id = ' . sqlesc($id) . ' ' . "AND enabled = 'yes' " . "AND status = 'confirmed'") or sqlerr(__FILE__, __LINE__);
        if (mysqli_num_rows($res) == 0) {
            $salty = salty('i think you might be lost');
            header("Location: {$site_config['baseurl']}/logout.php?hash_please={$salty}");
            return;
        }
        $row = mysqli_fetch_assoc($res);
        foreach ($user_fields_ar_int as $i) {
            $row[$i] = (int)$row[$i];
        }
        foreach ($user_fields_ar_float as $i) {
            $row[$i] = (float)$row[$i];
        }
        foreach ($user_fields_ar_str as $i) {
            $row[$i] = $row[$i];
        }
        $mc1->cache_value('MyUser_' . $id, $row, $site_config['expires']['curuser']);
        unset($res);
    }
    if (!isset($row['perms']) || (!($row['perms'] & bt_options::PERMS_BYPASS_BAN))) {
        $banned = false;
        if (check_bans($ip, $reason)) {
            $banned = true;
        } else {
            if ($ip != $ipf) {
                if (check_bans($ipf, $reason)) {
                    $banned = true;
                }
            }
        }
        if ($banned) {
            header('Content-Type: text/html; charset=utf-8');
            echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
      <html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en"><head>
      <title>Forbidden</title>
      </head><body>
      <h1>403 Forbidden</h1>Unauthorized IP address!
      <p>Reason: <strong>' . htmlsafechars($reason) . '</strong></p>
      </body></html>';
            die;
        }
    }
    if ($row['class'] >= UC_STAFF) {
        $allowed_ID = $site_config['is_staff']['allowed'];
        if (!in_array(((int)$row['id']), $allowed_ID, true)) {
            $msg = 'Fake Account Detected: Username: ' . htmlsafechars($row['username']) . ' - userID: ' . (int)$row['id'] . ' - UserIP : ' . getip();
            // Demote and disable
            sql_query("UPDATE users SET enabled = 'no', class = 0 WHERE id =" . sqlesc($row['id'])) or sqlerr(__FILE__, __LINE__);
            $mc1->begin_transaction('MyUser_' . $row['id']);
            $mc1->update_row(false, [
                'enabled' => 'no',
                'class'   => 0,
            ]);
            $mc1->commit_transaction($site_config['expires']['curuser']);
            $mc1->begin_transaction('user' . $row['id']);
            $mc1->update_row(false, [
                'enabled' => 'no',
                'class'   => 0,
            ]);
            $mc1->commit_transaction($site_config['expires']['user_cache']);
            write_log($msg);
            $salty = salty('i think you might be lost');
            header("Location: {$site_config['baseurl']}/logout.php?hash_please={$salty}");
            die;
        }
    }
    $What_Cache = (XBT_TRACKER == true ? 'userstats_xbt_' : 'userstats_');
    if (($stats = $mc1->get_value($What_Cache . $id)) === false) {
        $What_Expire = (XBT_TRACKER == true ? $site_config['expires']['u_stats_xbt'] : $site_config['expires']['u_stats']);
        $stats_fields_ar_int = [
            'uploaded',
            'downloaded',
        ];
        $stats_fields_ar_float = [
            'seedbonus',
        ];
        $stats_fields_ar_str = [
            'modcomment',
            'bonuscomment',
        ];
        $stats_fields = implode(', ', array_merge($stats_fields_ar_int, $stats_fields_ar_float, $stats_fields_ar_str));
        $s = sql_query('SELECT ' . $stats_fields . ' FROM users WHERE id = ' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
        $stats = mysqli_fetch_assoc($s);
        foreach ($stats_fields_ar_int as $i) {
            $stats[$i] = (int)$stats[$i];
        }
        foreach ($stats_fields_ar_float as $i) {
            $stats[$i] = (float)$stats[$i];
        }
        foreach ($stats_fields_ar_str as $i) {
            $stats[$i] = $stats[$i];
        }
        $mc1->cache_value($What_Cache . $id, $stats, $What_Expire);
    }
    $row['seedbonus'] = $stats['seedbonus'];
    $row['uploaded'] = $stats['uploaded'];
    $row['downloaded'] = $stats['downloaded'];
    if (($ustatus = $mc1->get_value('userstatus_' . $id)) === false) {
        $sql2 = sql_query('SELECT * FROM ustatus WHERE userid = ' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
        if (mysqli_num_rows($sql2)) {
            $ustatus = mysqli_fetch_assoc($sql2);
        } else {
            $ustatus = [
                'last_status' => '',
                'last_update' => 0,
                'archive'     => '',
            ];
        }
        $mc1->add_value('userstatus_' . $id, $ustatus, $site_config['expires']['u_status']); // 30 days
    }
    $row['last_status'] = $ustatus['last_status'];
    $row['last_update'] = $ustatus['last_update'];
    $row['archive'] = $ustatus['archive'];
    if ($row['ssluse'] > 1 && !isset($_SERVER['HTTPS']) && !defined('NO_FORCE_SSL')) {
        $site_config['baseurl'] = str_replace('http', 'https', $site_config['baseurl']);
        header('Location: ' . $site_config['baseurl'] . $_SERVER['REQUEST_URI']);
        exit();
    }
    $blocks_key = 'blocks::' . $row['id'];
    if (($CURBLOCK = $mc1->get_value($blocks_key)) === false) {
        $c_sql = sql_query('SELECT * FROM user_blocks WHERE userid = ' . sqlesc($row['id'])) or sqlerr(__FILE__, __LINE__);
        if (mysqli_num_rows($c_sql) == 0) {
            sql_query('INSERT INTO user_blocks(userid) VALUES(' . sqlesc($row['id']) . ')') or sqlerr(__FILE__, __LINE__);
            header('Location: index.php');
            exit();
        }
        $CURBLOCK = mysqli_fetch_assoc($c_sql);
        $CURBLOCK['index_page'] = (int)$CURBLOCK['index_page'];
        $CURBLOCK['global_stdhead'] = (int)$CURBLOCK['global_stdhead'];
        $CURBLOCK['userdetails_page'] = (int)$CURBLOCK['userdetails_page'];
        $mc1->cache_value($blocks_key, $CURBLOCK, 0);
    }
    $where_is['username'] = htmlsafechars($row['username']);
    $whereis_array = [
        'index'        => '%s is viewing the <a href="%s">home page</a>',
        'browse'       => '%s is viewing the <a href="%s">torrents page</a>',
        'requests'     => '%s is viewing the <a href="%s">requests page</a>',
        'upload'       => '%s is viewing the <a href="%s">upload page</a>',
        'casino'       => '%s is viewing the <a href="%s">casino page</a>',
        'blackjack'    => '%s is viewing the <a href="%s">blackjack page</a>',
        'bet'          => '%s is viewing the <a href="%s">bet page</a>',
        'forums'       => '%s is viewing the <a href="%s">forums page</a>',
        'chat'         => '%s is viewing the <a href="%s">irc page</a>',
        'topten'       => '%s is viewing the <a href="%s">statistics page</a>',
        'faq'          => '%s is viewing the <a href="%s">faq page</a>',
        'rules'        => '%s is viewing the <a href="%s">rules page</a>',
        'staff'        => '%s is viewing the <a href="%s">staff page</a>',
        'announcement' => '%s is viewing the <a href="%s">announcements page</a>',
        'usercp'       => '%s is viewing the <a href="%s">usercp page</a>',
        'offers'       => '%s is viewing the <a href="%s">offers page</a>',
        'pm_system'    => '%s is viewing the <a href="%s">mailbox page</a>',
        'userdetails'  => '%s is viewing the <a href="%s">personal profile page</a>',
        'details'      => '%s is viewing the <a href="%s">torrents details page</a>',
        'games'        => '%s is viewing the <a href="%s">games page</a>',
        'arcade'       => '%s is viewing the <a href="%s">arcade page</a>',
        'flash'        => '%s is playing a <a href="%s">flash game</a>',
        'arcade_top_score' => '%s is viewing the <a href="%s">arcade top scores page</a>',
        'unknown'      => '%s location is unknown',
    ];
    if (preg_match('/\/(.*?)\.php/is', $_SERVER['REQUEST_URI'], $whereis_temp)) {
        if (isset($whereis_array[$whereis_temp[1]])) {
            $whereis = sprintf($whereis_array[$whereis_temp[1]], $where_is['username'], htmlsafechars($_SERVER['REQUEST_URI']));
        } else {
            $whereis = sprintf($whereis_array['unknown'], $where_is['username']);
        }
    } else {
        $whereis = sprintf($whereis_array['unknown'], $where_is['username']);
    }
    $userupdate0 = 'onlinetime = onlinetime + 0';
    $new_time = TIME_NOW - $row['last_access_numb'];
    $update_time = 0;
    if ($new_time < 300) {
        $userupdate0 = 'onlinetime = onlinetime + ' . $new_time;
        $update_time = $new_time;
    }
    $userupdate1 = 'last_access_numb = ' . TIME_NOW;
    $update_time = ($row['onlinetime'] + $update_time);
    if (($row['last_access'] != '0') && (($row['last_access']) < (TIME_NOW - 180))) {
        sql_query('UPDATE users
                    SET where_is =' . sqlesc($whereis) . ', last_access=' . TIME_NOW . ", $userupdate0, $userupdate1
                    WHERE id = " . sqlesc($row['id'])) or sqlerr(__FILE__, __LINE__);
        $mc1->begin_transaction('MyUser_' . $row['id']);
        $mc1->update_row(false, [
            'last_access'      => TIME_NOW,
            'onlinetime'       => $update_time,
            'last_access_numb' => TIME_NOW,
            'where_is'         => $whereis,
        ]);
        $mc1->commit_transaction($site_config['expires']['curuser']);
        $mc1->begin_transaction('user' . $row['id']);
        $mc1->update_row(false, [
            'last_access'      => TIME_NOW,
            'onlinetime'       => $update_time,
            'last_access_numb' => TIME_NOW,
            'where_is'         => $whereis,
        ]);
        $mc1->commit_transaction($site_config['expires']['user_cache']);
    }
    if ($row['override_class'] < $row['class']) {
        $row['class'] = $row['override_class'];
    }
    $GLOBALS['CURUSER'] = $row;
    get_template();
    $mood = create_moods();
}

function charset()
{
    global $CURUSER, $site_config;
    $lang_charset = $CURUSER['language'];
    switch ($lang_charset) {
        case $lang_charset == 2:
            return 'ISO-8859-1';
        case $lang_charset == 3:
            return 'ISO-8859-17';
        case $lang_charset == 4:
            return 'ISO-8859-15';
        default:
            return 'UTF-8';
    }
}

function autoclean()
{
    global $site_config, $mc1;
    if (($cleanup_timer = $mc1->get_value('cleanup_timer_')) === false) {
        $mc1->cache_value('cleanup_timer_', 5, 1); // runs only every 1 second

        $now = TIME_NOW;
        $sql = sql_query("SELECT * FROM cleanup WHERE clean_on = 1 AND clean_time < {$now} ORDER BY clean_time ASC, clean_increment DESC LIMIT 0, 1") or sqlerr(__FILE__, __LINE__);
        $row = mysqli_fetch_assoc($sql);
        if ($row['clean_id']) {
            $next_clean = intval($row['clean_time'] + $row['clean_increment']);
            if ($row['clean_id'] == 82) {
                $next_clean = ceil(TIME_NOW / 300) * 300;
            }
            sql_query('UPDATE cleanup SET clean_time = ' . sqlesc($next_clean) . ' WHERE clean_id = ' . sqlesc($row['clean_id'])) or sqlerr(__FILE__, __LINE__);
            if (file_exists(CLEAN_DIR . $row['clean_file'])) {
                require_once CLEAN_DIR . $row['clean_file'];
                if (function_exists($row['function_name'])) {
                    register_shutdown_function($row['function_name'], $row);
                }
            }
        }

        if (($tfreak_cron = $mc1->get_value('tfreak_cron_')) === false) {
            if (($tfreak_news = $mc1->get_value('tfreak_news_links_')) === false) {
                $sql = sql_query("SELECT link FROM newsrss") or sqlerr(__FILE__, __LINE__);
                while ($tfreak_new = mysqli_fetch_assoc($sql)) {
                    $tfreak_news[] = $tfreak_new['link'];
                }
                $mc1->cache_value('tfreak_news_links_', $tfreak_news, 86400);
            }
            $mc1->cache_value('tfreak_cron_', TIME_NOW, 60);
            require_once INCL_DIR . 'newsrss.php';
            $github = github_shout($tfreak_news);
            $fox = foxnews_shout($tfreak_news);
            $tfreak = tfreak_shout($tfreak_news);
        }
    }
}

function get_stylesheet()
{
    global $site_config, $CURUSER;
    return isset($CURUSER['stylesheet']) ? $CURUSER['stylesheet'] : $site_config['stylesheet'];
}

function get_categorie_icons()
{
    global $site_config, $CURUSER;
    return isset($CURUSER['categorie_icon']) ? $CURUSER['categorie_icon'] : $site_config['categorie_icon'];
}

function get_language()
{
    global $site_config, $CURUSER;
    return isset($CURUSER['language']) ? $CURUSER['language'] : $site_config['language'];
}

function get_template()
{
    global $CURUSER, $site_config;
    if (isset($CURUSER)) {
        if (file_exists(TEMPLATE_DIR . "{$CURUSER['stylesheet']}/template.php")) {
            require_once TEMPLATE_DIR . "{$CURUSER['stylesheet']}/template.php";
        } else {
            if (isset($site_config)) {
                if (file_exists(TEMPLATE_DIR . "{$site_config['stylesheet']}/template.php")) {
                    require_once TEMPLATE_DIR . "{$site_config['stylesheet']}/template.php";
                } else {
                    echo 'Sorry, Templates do not seem to be working properly and missing some code. Please report this to the programmers/owners.';
                }
            } else {
                if (file_exists(TEMPLATE_DIR . '1/template.php')) {
                    require_once TEMPLATE_DIR . '1/template.php';
                } else {
                    echo 'Sorry, Templates do not seem to be working properly and missing some code. Please report this to the programmers/owners.';
                }
            }
        }
    } else {
        if (file_exists(TEMPLATE_DIR . "{$site_config['stylesheet']}/template.php")) {
            require_once TEMPLATE_DIR . "{$site_config['stylesheet']}/template.php";
        } else {
            echo 'Sorry, Templates do not seem to be working properly and missing some code. Please report this to the programmers/owners.';
        }
    }
    if (!function_exists('stdhead')) {
        echo 'stdhead function missing';
        function stdhead($title = '', $message = true)
        {
            return "<html><head><title>$title</title></head><body>";
        }
    }
    if (!function_exists('stdfoot')) {
        echo 'stdfoot function missing';
        function stdfoot()
        {
            return '</body></html>';
        }
    }
    if (!function_exists('stdmsg')) {
        echo 'stdmgs function missing';
        function stdmsg($title, $message)
        {
            return '<b>' . $title . "</b><br>$message";
        }
    }
    if (!function_exists('StatusBar')) {
        echo 'StatusBar function missing';
        function StatusBar()
        {
            global $CURUSER, $lang;

            return "{$lang['gl_msg_welcome']}, {$CURUSER['username']}";
        }
    }
}

function make_freeslots($userid, $key)
{
    global $mc1, $site_config;
    if (($slot = $mc1->get_value($key . $userid)) === false) {
        $res_slots = sql_query('SELECT * FROM freeslots WHERE userid = ' . sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
        $slot = [];
        if (mysqli_num_rows($res_slots)) {
            while ($rowslot = mysqli_fetch_assoc($res_slots)) {
                $slot[] = $rowslot;
            }
        }
        $mc1->cache_value($key . $userid, $slot, 86400 * 7);
    }

    return $slot;
}

function make_bookmarks($userid, $key)
{
    global $mc1, $site_config;
    if (($book = $mc1->get_value($key . $userid)) === false) {
        $res_books = sql_query('SELECT * FROM bookmarks WHERE userid = ' . sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
        $book = [];
        if (mysqli_num_rows($res_books)) {
            while ($rowbook = mysqli_fetch_assoc($res_books)) {
                $book[] = $rowbook;
            }
        }
        $mc1->cache_value($key . $userid, $book, 86400 * 7); // 7 days
    }

    return $book;
}

function genrelist()
{
    global $mc1, $site_config;
    if (($ret = $mc1->get_value('genrelist')) == false) {
        $ret = [];
        $res = sql_query('SELECT id, image, name FROM categories ORDER BY name') or sqlerr(__FILE__, __LINE__);
        while ($row = mysqli_fetch_assoc($res)) {
            $ret[] = $row;
        }
        $mc1->cache_value('genrelist', $ret, $site_config['expires']['genrelist']);
    }

    return $ret;
}

function create_moods($force = false)
{
    global $mc1, $site_config;
    $key = 'moods';
    if (($mood = $mc1->get_value($key)) === false || $force) {
        $res_moods = sql_query('SELECT * FROM moods ORDER BY id ASC') or sqlerr(__FILE__, __LINE__);
        $mood = [];
        if (mysqli_num_rows($res_moods)) {
            while ($rmood = mysqli_fetch_assoc($res_moods)) {
                $mood['image'][$rmood['id']] = $rmood['image'];
                $mood['name'][$rmood['id']] = $rmood['name'];
            }
        }
        $mc1->cache_value($key, $mood, 86400);
    }
    return $mood;
}

//== delete
function delete_id_keys($keys, $keyname = false)
{
    global $mc1;
    if (!(is_array($keys) || $keyname)) { // if no key given or not an array
        return false;
    } else {
        foreach ($keys as $id) { // proceed
            $mc1->delete_value($keyname . $id);
        }
    }

    return true;
}

function unesc($x)
{
    if (get_magic_quotes_gpc()) {
        return stripslashes($x);
    }

    return $x;
}

//Extended mksize Function
function mksize($bytes)
{
    $bytes = max(0, (int)$bytes);

    if ($bytes < 1024000) {
        return number_format($bytes / 1024, 2) . ' KB';
    } //Kilobyte
    elseif ($bytes < 1048576000) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } //Megabyte
    elseif ($bytes < 1073741824000) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } //Gigebyte
    elseif ($bytes < 1099511627776000) {
        return number_format($bytes / 1099511627776, 3) . ' TB';
    } //Terabyte
    elseif ($bytes < 1125899906842624000) {
        return number_format($bytes / 1125899906842624, 3) . ' PB';
    } //Petabyte
    elseif ($bytes < 1152921504606846976000) {
        return number_format($bytes / 1152921504606846976, 3) . ' EB';
    } //Exabyte
    elseif ($bytes < 1180591620717411303424000) {
        return number_format($bytes / 1180591620717411303424, 3) . ' ZB';
    } //Zettabyte
    else {
        return number_format($bytes / 1208925819614629174706176, 3) . ' YB';
    } //Yottabyte
}

function mkprettytime($s)
{
    if ($s < 0) {
        $s = 0;
    }
    $t = [];
    foreach ([
                 '60:sec',
                 '60:min',
                 '24:hour',
                 '0:day',
             ] as $x) {
        $y = explode(':', $x);
        if ($y[0] > 1) {
            $v = $s % $y[0];
            $s = floor($s / $y[0]);
        } else {
            $v = $s;
        }
        $t[$y[1]] = $v;
    }
    if ($t['day']) {
        return $t['day'] . 'd ' . sprintf('%02d:%02d:%02d', $t['hour'], $t['min'], $t['sec']);
    }
    if ($t['hour']) {
        return sprintf('%d:%02d:%02d', $t['hour'], $t['min'], $t['sec']);
    }

    return sprintf('%d:%02d', $t['min'], $t['sec']);
}

function mkglobal($vars)
{
    if (!is_array($vars)) {
        $vars = explode(':', $vars);
    }
    foreach ($vars as $v) {
        if (isset($_GET[$v])) {
            $GLOBALS[$v] = unesc($_GET[$v]);
        } elseif (isset($_POST[$v])) {
            $GLOBALS[$v] = unesc($_POST[$v]);
        } else {
            return 0;
        }
    }

    return 1;
}

function validfilename($name)
{
    return preg_match('/^[^\0-\x1f:\\\\\/?*\xff#<>|]+$/si', $name);
}

function validemail($email)
{
    return preg_match('/^[\w.-]+@([\w.-]+\.)+[a-z]{2,6}$/is', $email);
}

function sqlesc($x)
{
    if (is_integer($x)) {
        return (int)$x;
    }

    return sprintf('\'%s\'', mysqli_real_escape_string($GLOBALS['___mysqli_ston'], $x));
}

function sqlwildcardesc($x)
{
    return str_replace(['%', '_'], ['\\%', '\\_'], mysqli_real_escape_string($GLOBALS['___mysqli_ston'], $x));
}

function httperr($code = 404)
{
    header('HTTP/1.0 404 Not found');
    echo '<h1>Not Found</h1>';
    echo '<p>Sorry pal :(</p>';
    exit();
}


function loggedinorreturn()
{
    global $CURUSER, $site_config, $mc1;
    if (!$CURUSER) {
        if ($id = getSessionVar('userID')) {
            $user = $mc1->get_value('MyUser_' . $id);
            $CURUSER = $user;
        } else {
            header("Location: {$site_config['baseurl']}/login.php?returnto=" . urlencode($_SERVER['REQUEST_URI']));
            exit();
        }
    }
}

function searchfield($s)
{
    return preg_replace([
        '/[^a-z0-9]/si',
        '/^\s*/s',
        '/\s*$/s',
        '/\s+/s',
    ], [
        ' ',
        '',
        '',
        ' ',
    ], $s);
}

function get_row_count($table, $suffix = '')
{
    if ($suffix) {
        $suffix = " $suffix";
    }
    ($r = sql_query("SELECT COUNT(*) FROM $table$suffix")) or die(((is_object($GLOBALS['___mysqli_ston'])) ? mysqli_error($GLOBALS['___mysqli_ston']) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
    ($a = mysqli_fetch_row($r)) or die(((is_object($GLOBALS['___mysqli_ston'])) ? mysqli_error($GLOBALS['___mysqli_ston']) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));

    return (int)$a[0];
}

function get_one_row($table, $suffix, $where)
{
    $r = sql_query("SELECT $suffix FROM $table $where") or sqlerr(__FILE__, __LINE__);
    $a = mysqli_fetch_row($r);
    if (isset($a[0])) {
        return $a[0];
    } else {
        return false;
    }
}

function stderr($heading, $text)
{
    $htmlout = stdhead();
    $htmlout .= stdmsg($heading, $text);
    $htmlout .= stdfoot();
    echo $htmlout;
    exit();
}

// Basic MySQL error handler
function sqlerr($file = '', $line = '')
{
    global $site_config, $CURUSER;
    $the_error = ((is_object($GLOBALS['___mysqli_ston'])) ? mysqli_error($GLOBALS['___mysqli_ston']) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false));
    $the_error_no = ((is_object($GLOBALS['___mysqli_ston'])) ? mysqli_errno($GLOBALS['___mysqli_ston']) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false));
    if (SQL_DEBUG == 0) {
        exit();
    } elseif ($site_config['sql_error_log'] && SQL_DEBUG == 1) {
        $_error_string = "\n===================================================";
        $_error_string .= "\n Date: " . date('r');
        $_error_string .= "\n Error Number: " . $the_error_no;
        $_error_string .= "\n Error: " . $the_error;
        $_error_string .= "\n IP Address: " . $_SERVER['REMOTE_ADDR'];
        $_error_string .= "\n in file " . $file . ' on line ' . $line;
        $_error_string .= "\n URL:" . $_SERVER['REQUEST_URI'];
        $_error_string .= "\n Username: {$CURUSER['username']}[{$CURUSER['id']}]";
        if ($FH = @fopen($site_config['sql_error_log'], 'a')) {
            @fwrite($FH, $_error_string);
            @fclose($FH);
        }
        echo '<html><head><title>MySQLI Error</title>
                    <style>P,BODY{ font-family:arial,sans-serif; font-size:11px; }</style></head><body>
                       <blockquote><h1>MySQLI Error</h1><b>There appears to be an error with the database.</b><br>
                       You can try to refresh the page by clicking <a href="javascript:window.location=window.location;">here</a>
                  </body></html>';
    } else {
        $the_error = "\nSQL error: " . $the_error . "\n";
        $the_error .= 'SQL error code: ' . $the_error_no . "\n";
        $the_error .= 'Date: ' . date("l dS \of F Y h:i:s A");
        $out = "<html>\n<head>\n<title>MySQLI Error</title>\n
                   <style>P,BODY{ font-family:arial,sans-serif; font-size:11px; }</style>\n</head>\n<body>\n
                   <blockquote>\n<h1>MySQLI Error</h1><b>There appears to be an error with the database.</b><br>
                   You can try to refresh the page by clicking <a href=\"javascript:window.location=window.location;\">here</a>.
                   <br><br><b>Error Returned</b><br>
                   <form name='mysql'><textarea rows=\"15\" cols=\"60\">" . htmlsafechars($the_error, ENT_QUOTES) . '</textarea></form><br>We apologise for any inconvenience</blockquote></body></html>';
        echo $out;
    }
    exit();
}

function get_dt_num()
{
    return gmdate('YmdHis');
}

function write_log($text)
{
    $text = sqlesc($text);
    $added = TIME_NOW;
    sql_query("INSERT INTO sitelog (added, txt) VALUES ($added, $text)") or sqlerr(__FILE__, __LINE__);
}

function sql_timestamp_to_unix_timestamp($s)
{
    return mktime(substr($s, 11, 2), substr($s, 14, 2), substr($s, 17, 2), substr($s, 5, 2), substr($s, 8, 2), substr($s, 0, 4));
}

function unixstamp_to_human($unix = 0)
{
    $offset = get_time_offset();
    $tmp = gmdate('j,n,Y,G,i,A', $unix + $offset);
    list($day, $month, $year, $hour, $min, $ampm) = explode(',', $tmp);

    return [
        'day'    => $day,
        'month'  => $month,
        'year'   => $year,
        'hour'   => $hour,
        'minute' => $min,
        'ampm'   => $ampm,
    ];
}

function get_time_offset()
{
    global $CURUSER, $site_config;
    $r = 0;
    $r = (($CURUSER['time_offset'] != '') ? $CURUSER['time_offset'] : $site_config['time_offset']) * 3600;
    if ($site_config['time_adjust']) {
        $r += ($site_config['time_adjust'] * 60);
    }
    if ($CURUSER['dst_in_use']) {
        $r += 3600;
    }

    return $r;
}

function get_date($date, $method, $norelative = 0, $full_relative = 0)
{
    global $site_config;
    static $offset_set = 0;
    static $today_time = 0;
    static $yesterday_time = 0;
    $time_options = [
        'JOINED' => $site_config['time_joined'],
        'SHORT'  => $site_config['time_short'],
        'LONG'   => $site_config['time_long'],
        'TINY'   => $site_config['time_tiny'] ? $site_config['time_tiny'] : 'j M Y - G:i',
        'DATE'   => $site_config['time_date'] ? $site_config['time_date'] : 'j M Y',
    ];
    if (!$date) {
        return '--';
    }
    if (empty($method)) {
        $method = 'LONG';
    }
    if ($offset_set == 0) {
        $GLOBALS['offset'] = get_time_offset();
        if ($site_config['time_use_relative']) {
            $today_time = gmdate('d,m,Y', (TIME_NOW + $GLOBALS['offset']));
            $yesterday_time = gmdate('d,m,Y', ((TIME_NOW - 86400) + $GLOBALS['offset']));
        }
        $offset_set = 1;
    }
    if ($site_config['time_use_relative'] == 3) {
        $full_relative = 1;
    }
    if ($full_relative && ($norelative != 1)) {
        $diff = TIME_NOW - $date;
        if ($diff < 3600) {
            if ($diff < 120) {
                return '< 1 minute ago';
            } else {
                return sprintf('%s minutes ago', intval($diff / 60));
            }
        } elseif ($diff < 7200) {
            return '< 1 hour ago';
        } elseif ($diff < 86400) {
            return sprintf('%s hours ago', intval($diff / 3600));
        } elseif ($diff < 172800) {
            return '< 1 day ago';
        } elseif ($diff < 604800) {
            return sprintf('%s days ago', intval($diff / 86400));
        } elseif ($diff < 1209600) {
            return '< 1 week ago';
        } elseif ($diff < 3024000) {
            return sprintf('%s weeks ago', intval($diff / 604900));
        } else {
            return gmdate($time_options[$method], ($date + $GLOBALS['offset']));
        }
    } elseif ($site_config['time_use_relative'] && ($norelative != 1)) {
        $this_time = gmdate('d,m,Y', ($date + $GLOBALS['offset']));
        if ($site_config['time_use_relative'] == 2) {
            $diff = TIME_NOW - $date;
            if ($diff < 3600) {
                if ($diff < 120) {
                    return '< 1 minute ago';
                } else {
                    return sprintf('%s minutes ago', intval($diff / 60));
                }
            }
        }
        if ($this_time == $today_time) {
            return str_replace('{--}', 'Today', gmdate($site_config['time_use_relative_format'], ($date + $GLOBALS['offset'])));
        } elseif ($this_time == $yesterday_time) {
            return str_replace('{--}', 'Yesterday', gmdate($site_config['time_use_relative_format'], ($date + $GLOBALS['offset'])));
        } else {
            return gmdate($time_options[$method], ($date + $GLOBALS['offset']));
        }
    } else {
        return gmdate($time_options[$method], ($date + $GLOBALS['offset']));
    }
}

function ratingpic($num)
{
    global $site_config;
    $r = round($num * 2) / 2;
    if ($r < 1 || $r > 5) {
        return;
    }

    return "<img src=\"{$site_config['pic_base_url']}ratings/{$r}.gif\" border=\"0\" alt=\"Rating: $num / 5\" title=\"Rating: $num / 5\" />";
}

function hash_pad($hash)
{
    return str_pad($hash, 20);
}

//== cutname = Laffin
function CutName($txt, $len = 40)
{
    return strlen($txt) > $len ? substr($txt, 0, $len - 1) . '...' : $txt;
}

function CutName_B($txt, $len = 20)
{
    return strlen($txt) > $len ? substr($txt, 0, $len - 1) . '...' : $txt;
}

function load_language($file = '')
{
    global $site_config, $CURUSER;
    if (!isset($GLOBALS['CURUSER']) or empty($GLOBALS['CURUSER']['language'])) {
        if (!file_exists(LANG_DIR . "{$site_config['language']}/lang_{$file}.php")) {
            stderr('System Error', "Can't find language files");
        }
        require_once LANG_DIR . "{$site_config['language']}/lang_{$file}.php";

        return $lang;
    }
    if (!file_exists(LANG_DIR . "{$CURUSER['language']}/lang_{$file}.php")) {
        stderr('System Error', "Can't find language files");
    } else {
        require_once LANG_DIR . "{$CURUSER['language']}/lang_{$file}.php";
    }

    return $lang;
}

function flood_limit($table)
{
    global $CURUSER, $site_config, $lang;
    if (!file_exists($site_config['flood_file']) || !is_array($max = unserialize(file_get_contents($site_config['flood_file'])))) {
        return;
    }
    if (!isset($max[$CURUSER['class']])) {
        return;
    }
    $tb = [
        'posts'    => 'posts.userid',
        'comments' => 'comments.user',
        'messages' => 'messages.sender',
    ];
    $q = sql_query('SELECT min(' . $table . '.added) as first_post, count(' . $table . '.id) as how_many FROM ' . $table . ' WHERE ' . $tb[$table] . ' = ' . $CURUSER['id'] . ' AND ' . TIME_NOW . ' - ' . $table . '.added < ' . $site_config['flood_time']) or sqlerr(__FILE__, __LINE__);
    $a = mysqli_fetch_assoc($q);
    if ($a['how_many'] > $max[$CURUSER['class']]) {
        stderr($lang['gl_sorry'], $lang['gl_flood_msg'] . '' . mkprettytime($site_config['flood_time'] - (TIME_NOW - $a['first_post'])));
    }
}

function sql_query($query, $log = true)
{
    global $query_stat, $queries, $site_config;
    if ($site_config['log_queries'] && $log) {
        $sql = "INSERT INTO queries (query, dateTime) VALUES (" . sqlesc(preg_replace('/[ \t\n\t\r]+/', ' ', preg_replace('/\s*$^\s*/m', ' ', $query))) . ", NOW())";
        sql_query($sql, false);
    }
    $query_start_time = microtime(true); // Start time
    $result = mysqli_query($GLOBALS['___mysqli_ston'], $query);
    $query_end_time = microtime(true); // End time
    $querytime = ($query_end_time - $query_start_time);
    $query_stat[] = [
        'seconds' => number_format($query_end_time - $query_start_time, 6),
        'query'   => $query,
    ];
    $queries = count($query_stat);

    return $result;
}

function get_percent_completed_image($p)
{
    $img = 'progress-';
    switch (true) {
        case $p >= 100:
            $img .= 5;
            break;

        case ($p >= 0) && ($p <= 10):
            $img .= 0;
            break;

        case ($p >= 11) && ($p <= 40):
            $img .= 1;
            break;

        case ($p >= 41) && ($p <= 60):
            $img .= 2;
            break;

        case ($p >= 61) && ($p <= 80):
            $img .= 3;
            break;

        case ($p >= 81) && ($p <= 99):
            $img .= 4;
            break;
    }

    return '<img src="./images/' . $img . '.gif" alt="percent" />';
}

function strip_tags_array($ar)
{
    if (is_array($ar)) {
        foreach ($ar as $k => $v) {
            $ar[strip_tags($k)] = strip_tags($v);
        }
    } else {
        $ar = strip_tags($ar);
    }

    return $ar;
}

function referer()
{
    $http_referer = getenv('HTTP_REFERER');
    if (!empty($_SERVER['HTTP_HOST']) && (strstr($http_referer, $_SERVER['HTTP_HOST']) == false) && ($http_referer != '')) {
        $ip = $_SERVER['REMOTE_ADDR'];
        $http_agent = $_SERVER['HTTP_USER_AGENT'];
        $http_page = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'];
        if (!empty($_SERVER['QUERY_STRING'])) {
            $http_page .= '?' . $_SERVER['QUERY_STRING'];
        }
        sql_query('INSERT INTO referrers (browser, ip, referer, page, date)
            VALUES (' . sqlesc($http_agent) . ', ' . sqlesc($ip) . ', ' . sqlesc($http_referer) . ', ' . sqlesc($http_page) . ', ' . sqlesc(TIME_NOW) . ')') or sqlerr(__FILE__, __LINE__);
    }
}

function mysql_fetch_all($query, $default_value = [])
{
    $r = @sql_query($query);
    $result = [];
    if ($err = ((is_object($GLOBALS['___mysqli_ston'])) ? mysqli_error($GLOBALS['___mysqli_ston']) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false))) {
        return $err;
    }
    if (@mysqli_num_rows($r)) {
        while ($row = mysqli_fetch_array($r)) {
            $result[] = $row;
        }
    }
    if (count($result) == 0) {
        return $default_value;
    }

    return $result;
}

function write_bonus_log($userid, $amount, $type)
{
    $added = TIME_NOW;
    $donation_type = $type;
    sql_query('INSERT INTO bonuslog (id, donation, type, added_at)
                VALUES(' . sqlesc($userid) . ', ' . sqlesc($amount) . ', ' . sqlesc($donation_type) . ", $added)") or sqlerr(__FILE__, __LINE__);
}

function human_filesize($bytes, $dec = 2)
{
    $size = ['B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
    $factor = floor((strlen($bytes) - 1) / 3);

    return sprintf("%.{$dec}f", $bytes / pow(1024, $factor)) . @$size[$factor];
}

function sessionStart()
{
    global $site_config;
    if (!session_id()) {
        // Set the session name:
        session_name($site_config['sessionName']);

        // Set session cookie parameters:
        session_set_cookie_params(
            $site_config['cookie_lifetime'] * 86400,
            $site_config['cookie_path'],
            $site_config['cookie_domain'],
            $site_config['sessionCookieSecure']
        );

        // enforce php settings before start session
        ini_set('session.use_strict_mode', 1);
        ini_set('session.use_trans_sid', 0);

        // Start the session:
        session_start();
    }

    // Create a new AUTH token.
    if (!getSessionVar('auth')) {
        setSessionVar('auth', bin2hex(random_bytes(32)));
    }

    // Create a new CSRF token.
    if (!getSessionVar($site_config['session_csrf'])) {
        setSessionVar($site_config['session_csrf'], bin2hex(random_bytes(32)));
    }

    // Make sure we have a canary set and Regenerate session ID every five minutes:
    if (!getSessionVar('canary') || getSessionVar('canary') >= TIME_NOW - 300) {
        regenerateSessionID();
        setSessionVar('canary', TIME_NOW);
    }
}

function destroySession()
{
    sessionStart();
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params['path'], $params['domain'],
            $params['secure'], $params['httponly']
        );
    }

    session_destroy();
}

function regenerateSessionID()
{
    if (!session_id()) {
        session_regenerate_id(false);
    }
}

function validateToken($token, $key = null, $regen = false) {
    global $site_config;
    if ($key === null) {
        $key = $site_config['session_csrf'];
    }
    if (empty($token)) {
        return false;
    }
    if (hash_equals(getSessionVar($key), $token)) {
        if ($regen) {
            unsetSessionVar($key);
            setSessionVar($key, bin2hex(random_bytes(32)));
        }
        return true;
    }
    return false;
}

function ipToStorageFormat($ip)
{
    if (function_exists('inet_pton')) {
        // ipv4 & ipv6:
        return @inet_pton($ip);
    }

    // Only ipv4:
    return @pack('N', @ip2long($ip));
}

function ipFromStorageFormat($ip)
{
    if (function_exists('inet_ntop')) {
        // ipv4 & ipv6:
        return @inet_ntop($ip);
    }
    // Only ipv4:
    $unpacked = @unpack('Nlong', $ip);
    if (isset($unpacked['long'])) {
        return @long2ip($unpacked['long']);
    }

    return null;
}

function setSessionVar($key, $value, $prefix = null)
{
    global $site_config;
    if ($prefix === null) {
        $prefix = $site_config['sessionKeyPrefix'];
    }

    // Set the session value:
    if (getSessionVar($key, $prefix)) {
        unsetSessionVar($key);
    }
    $_SESSION[$prefix . $key] = $value;
}

function getSessionVar($key, $prefix = null)
{
    global $site_config;
    if (empty($key)) {
        return null;
    }

    if ($prefix === null) {
        $prefix = $site_config['sessionKeyPrefix'];
    }

    // Return the session value if existing:
    if (isset($_SESSION[$prefix . $key])) {
        return $_SESSION[$prefix . $key];
    } else {
        return null;
    }
}

function unsetSessionVar($key, $prefix = null)
{
    global $site_config;
    if ($prefix === null) {
        $prefix = $site_config['sessionKeyPrefix'];
    }

    // Set the session value:
    unset($_SESSION[$prefix . $key]);
}

function salty($username)
{
    global $site_config;
    return bin2hex(random_bytes(64));
}

function replace_unicode_strings($text)
{
    $text = str_replace(['“', '”'], '"', $text);
    $text = str_replace(['&quot;', '&lsquo;', '‘', '&rsquo;', '’'], "'", $text);
    $text = str_replace(['&ldquo;', '“', '&rdquo;', '”'], '"', $text);
    $text = str_replace(['&#8212;', '–'], '-', $text);
    $text = str_replace('&amp;', '&#38;', $text);
    return html_entity_decode(htmlentities($text, ENT_QUOTES));
}

function getPmCount($userid)
{
    global $mc1, $site_config;
    if (($pmCount = $mc1->get_value('inbox_new_' . $userid)) === false) {
        $res = sql_query('SELECT COUNT(id) FROM messages WHERE receiver = ' . sqlesc($userid) . " AND unread = 'yes' AND location = 1") or sqlerr(__LINE__, __FILE__);
        $result = mysqli_fetch_row($res);
        $pmCount = $result[0];
        $mc1->cache_value('inbox_new_' . $userid, $pmCount, $site_config['expires']['unread']);
    }

    return $pmCount;
}

function parked()
{
    global $CURUSER;
    if ($CURUSER['parked'] == 'yes') {
        stderr('Error', '<b>Your account is currently parked.</b>');
    }
}

function suspended()
{
    global $CURUSER;
    if ($CURUSER['suspended'] == 'yes') {
        stderr('Error', '<b>Your account is currently suspended.</b>');
    }
}

function check_user_status()
{
    dbconn();
    userlogin();
    global $CURUSER;
    referer();
    if (!validateToken(getSessionVar('auth'), 'auth')) {
        destroySession();
        header('Location: login.php');
        exit();
    }
    loggedinorreturn();
    parked();
    suspended();
}

function random_color($minVal = 0, $maxVal = 255)
{
    // Make sure the parameters will result in valid colours
    $minVal = $minVal < 0 || $minVal > 255 ? 0 : $minVal;
    $maxVal = $maxVal < 0 || $maxVal > 255 ? 255 : $maxVal;

    // Generate 3 values
    $r = mt_rand($minVal, $maxVal);
    $g = mt_rand($minVal, $maxVal);
    $b = mt_rand($minVal, $maxVal);

    // Return a hex colour ID string
    return sprintf('#%02X%02X%02X', $r, $g, $b);

}

function user_exists($user_id)
{
    global $mc1;
    if (($userlist = $mc1->get_value('userlist_' . $user_id)) === false) {
        $query = "SELECT id FROM users WHERE id = " . sqlesc($user_id);
        $res = sql_query($query) or sqlerr(__FILE__, __LINE__);
        $res = mysqli_fetch_assoc($res);
        if (empty($res)) {
            return false;
        }
        $mc1->cache_value('userlist_' . $userid, $res, 86400);
    }
    return true;
}

function get_poll()
{
    global $CURUSER, $mc1;
    if (($poll_data = $mc1->get_value('poll_data_' . $CURUSER['id'])) === false) {
        $query = sql_query('SELECT * FROM polls
                            LEFT JOIN poll_voters ON polls.pid = poll_voters.poll_id
                            AND poll_voters.user_id = ' . sqlesc($CURUSER['id']) . '
                            ORDER BY polls.start_date DESC
                            LIMIT 1');
        if (!mysqli_num_rows($query)) {
            return '';
        }
        while ($row = mysqli_fetch_assoc($query)) {
            $poll_data = $row;
        }
        $mc1->cache_value('poll_data_' . $CURUSER['id'], $poll_data, $site_config['expires']['poll_data']);
    }
    return $poll_data;
}

function shuffle_assoc($list, $times = 1)
{
    if (!is_array($list)) {
        return $list;
    }

    $keys = array_keys($list);
    foreach (range(0, $times) as $number) {
        shuffle($keys);
    }
    $random = array();
    foreach ($keys as $key) {
        $random[$key] = $list[$key];
    }
    return $random;
}

function make_torrentpass()
{
    global $mc1;
    $passes = [];
    if (($passes = $mc1->get_value('torrent_passes_')) === false) {
        $sql = "SELECT torrent_pass FROM users";
        $query = sql_query($sql) or sqlerr(__FILE__, __LINE__);
        while ($row = mysqli_fetch_assoc($query)) {
            $passes[] = $row['torrent_pass'];
        }
        $sql = "SELECT torrent_pass FROM torrent_pass";
        $query = sql_query($sql) or sqlerr(__FILE__, __LINE__);
        while ($row = mysqli_fetch_assoc($query)) {
            $passes[] = $row['torrent_pass'];
        }
        $mc1->cache_value('torrent_passes_', $passes, 86400);
    }

    $tpass = make_password(16);
    while (in_array($tpass, $passes)) {
        $tpass = make_password(16);
    }
    $passes[] = $tpass;
    $mc1->cache_value('torrent_passes_', $passes, 86400);
    return $tpass;
}

function get_scheme()
{
    if (isset($_SERVER['REQUEST_SCHEME'])) {
        $scheme = $_SERVER['REQUEST_SCHEME'];
    }
    return $scheme;
}

function countries()
{
    global $mc1, $site_config;
    if (($ret = $mc1->get_value('countries::arr')) === false) {
        $res = sql_query('SELECT id, name, flagpic FROM countries ORDER BY name ASC') or sqlerr(__FILE__, __LINE__);
        while ($row = mysqli_fetch_assoc($res)) {
            $ret[] = $row;
        }
        $mc1->cache_value('countries::arr', $ret, $site_config['expires']['user_flag']);
    }

    return $ret;
}

if (file_exists('install')) {
    $HTMLOUT = "<!DOCTYPE html>
<html>
<head>
    <meta charset='utf-8'>
    <meta http-equiv='X-UA-Compatible' content='IE=edge'>
    <meta name='viewport' content='width=device-width, initial-scale=1'>
    <title>Warning</title>
</head>
<body style='background: grey;'>
    <div style='font-size:33px;color:white;background-color:red;text-align:center;'>
        Delete the install directory
        <p>' . ROOT_DIR. 'public' . DIRECTORY_SEPARATOR . 'install' . DIRECTORY_SEPARATOR . '</p>
    </div>
</body>
</html>";
    echo $HTMLOUT;
    exit();
}
