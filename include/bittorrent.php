<?php
$start = microtime(true);

if (!file_exists(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'config.php')) {
    header('Location: /install/index.php');
    die();
}

if (!file_exists(dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . '.env')) {
    echo 'missing .env file, please copy .env.example to .env and then edit .env';
    die();
}

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'define.php';
require_once INCL_DIR . 'config.php';
require_once INCL_DIR . 'site_config.php';
require_once VENDOR_DIR . 'autoload.php';

$dotenv = new Dotenv\Dotenv(ROOT_DIR);
$dotenv->load();

use Blocktrail\CryptoJSAES\CryptoJSAES;

require_once INCL_DIR . 'database.php';
require_once INCL_DIR . 'files.php';

require_once CACHE_DIR . 'free_cache.php';
require_once CACHE_DIR . 'class_config.php';
require_once INCL_DIR . 'password_functions.php';
$cache = new CACHE();

// start session on every page request
sessionStart();

$pu239_version = new SebastianBergmann\Version(
    '0.1',
    ROOT_DIR
);

$site_config['version'] = $pu239_version->getVersion();

/**
 * Class curuser
 */
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
require_once INCL_DIR . 'site_settings.php';

$load = sys_getloadavg();
if ($load[0] > 20) {
    die(
    "Load is too high.
        Don't continuously refresh, or you will just make the problem last longer"
    );
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

/**
 * @param $in
 *
 * @return bool|string
 */
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

/**
 * @param string $txt
 *
 * @return mixed|string
 */
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

/**
 * @param array $ids
 *
 * @return bool|string
 */
function PostKey($ids = [])
{
    global $site_config;
    if (!is_array($ids)) {
        return false;
    }

    return hash('sha256', $site_config['tracker_post_key'] . join('', $ids) . $site_config['tracker_post_key']);
}

/**
 * @param $ids
 * @param $key
 *
 * @return bool
 */
function CheckPostKey($ids, $key)
{
    global $site_config;
    if (!is_array($ids) or !$key) {
        return false;
    }

    return $key == hash('sha256', $site_config['tracker_post_key'] . join('', $ids) . $site_config['tracker_post_key']);
}

/**
 * @param $ip
 *
 * @return bool
 */
function validip($ip)
{
    return filter_var($ip, FILTER_VALIDATE_IP, [
        'flags' => FILTER_FLAG_NO_PRIV_RANGE,
        FILTER_FLAG_NO_RES_RANGE,
    ]) ? true : false;
}

/**
 * @return mixed
 */
function getip()
{
    if (IP_LOGGING) {
        return $_SERVER['REMOTE_ADDR'];
    }
    return '127.0.0.1';
}

/**
 * @param bool $autoclean
 */
function dbconn($autoclean = true)
{
    if (!@($GLOBALS['___mysqli_ston'] = mysqli_connect($_ENV['DB_HOST'], $_ENV['DB_USERNAME'], $_ENV['DB_PASSWORD'], $_ENV['DB_DATABASE'], $_ENV['DB_PORT']))) {
        switch (((is_object($GLOBALS['___mysqli_ston'])) ? mysqli_errno($GLOBALS['___mysqli_ston']) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false))) {
            case 1040:
            case 2002:
                if ($_SERVER['REQUEST_METHOD'] == 'GET') {
                    die("<html><head><meta http-equiv='refresh' content=\"5 $_SERVER[REQUEST_URI]\"></head><body><table width='100%' height='100%'><tr><td><h3>The server load is very high at the moment. Retrying, please wait...</h3></td></tr></table></body></html>");
                } else {
                    die('Too many users. Please press the Refresh button in your browser to retry.');
                }
            // no break
            default:
                die('[' . ((is_object($GLOBALS['___mysqli_ston'])) ? mysqli_errno($GLOBALS['___mysqli_ston']) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)) . '] dbconn: mysqli_connect: ' . ((is_object($GLOBALS['___mysqli_ston'])) ? mysqli_error($GLOBALS['___mysqli_ston']) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
        }
    }
    if ($autoclean) {
        register_shutdown_function('autoclean');
    }
}

/**
 * @param $id
 */
function status_change($id)
{
    sql_query('UPDATE announcement_process SET status = 0 WHERE user_id = ' . sqlesc($id) . ' AND status = 1') or sqlerr(__FILE__, __LINE__);
}

/**
 * @param        $var
 * @param string $addtext
 *
 * @return string
 */
function hashit($var, $addtext = '')
{
    return md5('Th15T3xt' . $addtext . $var . $addtext . 'is5add3dto66uddy6he@water...');
}

/**
 * @param        $ip
 * @param string $reason
 *
 * @return bool
 */
function check_bans($ip, &$reason = '')
{
    global $cache;
    if (empty($ip)) {
        return false;
    }
    $key = 'bans_' . $ip;
    $ban = $cache->get($key);
    if ($ban === false || is_null($ban)) {
        $nip = sqlesc($ip);
        $ban_sql = sql_query('SELECT comment FROM bans WHERE (INET6_NTOA(first) <= ' . $nip . ' AND INET6_NTOA(last) >= ' . $nip . ') LIMIT 1') or sqlerr(__FILE__, __LINE__);
        if (mysqli_num_rows($ban_sql)) {
            $comment = mysqli_fetch_row($ban_sql);
            $reason = 'Manual Ban (' . $comment[0] . ')';
            $cache->set($key, $reason, 86400); // 86400 // banned

            return true;
        }
        ((mysqli_free_result($ban_sql) || (is_object($ban_sql) && (get_class($ban_sql) == 'mysqli_result'))) ? true : false);
        $cache->set($key, 0, 86400);

        return false;
    } elseif (!$ban) {
        return false;
    } else {
        $reason = $ban;

        return true;
    }
}

/**
 * @param      $id
 * @param bool $updatedb
 */
function logincookie($id, $updatedb = true)
{
    if ($updatedb) {
        sql_query("UPDATE users SET last_login = " . TIME_NOW . " WHERE id = " . sqlesc($id)) or sqlerr(__file__, __line__);
    }
}

function userlogin()
{
    global $site_config, $cache, $CURBLOCK, $mood, $whereis, $CURUSER;
    unset($GLOBALS['CURUSER']);

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

    $ip = getip();

    $users_data = get_user_data($id);
    if (!isset($users_data['perms']) || (!($users_data['perms'] & bt_options::PERMS_BYPASS_BAN))) {
        $banned = false;
        if (check_bans($ip, $reason)) {
            $banned = true;
        }
        if ($banned) {
            header('Content-Type: text/html; charset=utf-8');
            echo "<!doctype html>
<html>
<head>
<meta charset='utf-8'>
<meta http-equiv='X-UA-Compatible' content='IE=edge'>
<meta name='viewport' content='width=device-width, initial-scale=1'>
<title>Forbidden</title>
</head>
<body>
    <h1>403 Forbidden</h1>
    <h1>Unauthorized IP address!</h1>h1>
    <p>Reason: <strong>' . htmlsafechars($reason) . '</strong></p>
</body>
</html>";
            die;
        }
    }

    if ($users_data['class'] >= UC_STAFF) {
        $allowed_ID = $site_config['is_staff']['allowed'];
        if (!in_array(((int)$users_data['id']), $allowed_ID, true)) {
            $msg = 'Fake Account Detected: Username: ' . htmlsafechars($users_data['username']) . ' - userID: ' . (int)$users_data['id'] . ' - UserIP : ' . getip();
            // Demote and disable
            sql_query("UPDATE users SET enabled = 'no', class = 0 WHERE id =" . sqlesc($users_data['id'])) or sqlerr(__FILE__, __LINE__);
            $cache->update_row('user' . $users_data['id'], [
                'enabled' => 'no',
                'class'   => 0,
            ], $site_config['expires']['user_cache']);
            write_log($msg);
            $salty = salty();
            header("Location: {$site_config['baseurl']}/logout.php?hash_please={$salty}");
            die;
        }
    }

    $ustatus = $cache->get('userstatus_' . $id);
    if ($ustatus === false || is_null($ustatus)) {
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
        $cache->add('userstatus_' . $id, $ustatus, $site_config['expires']['u_status']); // 30 days
    }
    $users_data['last_status'] = $ustatus['last_status'];
    $users_data['last_update'] = $ustatus['last_update'];
    $users_data['archive'] = $ustatus['archive'];
    if ($users_data['ssluse'] > 1 && !isset($_SERVER['HTTPS']) && !defined('NO_FORCE_SSL')) {
        $site_config['baseurl'] = str_replace('http', 'https', $site_config['baseurl']);
        header('Location: ' . $site_config['baseurl'] . $_SERVER['REQUEST_URI']);
        die();
    }
    $blocks_key = 'blocks_' . $users_data['id'];

    $CURBLOCK = $cache->get($blocks_key);
    if ($CURBLOCK === false || is_null($CURBLOCK)) {
        $c_sql = sql_query('SELECT * FROM user_blocks WHERE userid = ' . sqlesc($users_data['id'])) or sqlerr(__FILE__, __LINE__);
        if (mysqli_num_rows($c_sql) == 0) {
            sql_query('INSERT INTO user_blocks(userid) VALUES (' . sqlesc($users_data['id']) . ')') or sqlerr(__FILE__, __LINE__);
            $c_sql = sql_query('SELECT * FROM user_blocks WHERE userid = ' . sqlesc($users_data['id'])) or sqlerr(__FILE__, __LINE__);
        }
        $CURBLOCK = mysqli_fetch_assoc($c_sql);
        $CURBLOCK['index_page'] = (int)$CURBLOCK['index_page'];
        $CURBLOCK['global_stdhead'] = (int)$CURBLOCK['global_stdhead'];
        $CURBLOCK['userdetails_page'] = (int)$CURBLOCK['userdetails_page'];
        $cache->set($blocks_key, $CURBLOCK, 0);
    }
    $where_is['username'] = htmlsafechars($users_data['username']);
    $whereis_array = [
        'index'            => '%s is viewing the <a href="%s">home page</a>',
        'browse'           => '%s is viewing the <a href="%s">torrents page</a>',
        'requests'         => '%s is viewing the <a href="%s">requests page</a>',
        'upload'           => '%s is viewing the <a href="%s">upload page</a>',
        'casino'           => '%s is viewing the <a href="%s">casino page</a>',
        'blackjack'        => '%s is viewing the <a href="%s">blackjack page</a>',
        'bet'              => '%s is viewing the <a href="%s">bet page</a>',
        'forums'           => '%s is viewing the <a href="%s">forums page</a>',
        'chat'             => '%s is viewing the <a href="%s">irc page</a>',
        'topten'           => '%s is viewing the <a href="%s">statistics page</a>',
        'faq'              => '%s is viewing the <a href="%s">faq page</a>',
        'rules'            => '%s is viewing the <a href="%s">rules page</a>',
        'staff'            => '%s is viewing the <a href="%s">staff page</a>',
        'announcement'     => '%s is viewing the <a href="%s">announcements page</a>',
        'usercp'           => '%s is viewing the <a href="%s">usercp page</a>',
        'offers'           => '%s is viewing the <a href="%s">offers page</a>',
        'pm_system'        => '%s is viewing the <a href="%s">mailbox page</a>',
        'userdetails'      => '%s is viewing the <a href="%s">personal profile page</a>',
        'details'          => '%s is viewing the <a href="%s">torrents details page</a>',
        'games'            => '%s is viewing the <a href="%s">games page</a>',
        'arcade'           => '%s is viewing the <a href="%s">arcade page</a>',
        'flash'            => '%s is playing a <a href="%s">flash game</a>',
        'arcade_top_score' => '%s is viewing the <a href="%s">arcade top scores page</a>',
        'staffpanel'       => '%s is viewing the <a href="%s">Staff Panel</a>',
        'unknown'          => '%s location is unknown',
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
    $new_time = TIME_NOW - $users_data['last_access_numb'];
    $update_time = 0;
    if ($new_time < 300) {
        $userupdate0 = 'onlinetime = onlinetime + ' . $new_time;
        $update_time = $new_time;
    }
    $userupdate1 = 'last_access_numb = ' . TIME_NOW;
    $update_time = ($users_data['onlinetime'] + $update_time);
    if (($users_data['last_access'] != '0') && (($users_data['last_access']) < (TIME_NOW - 180))) {
        sql_query('UPDATE users
                    SET where_is =' . sqlesc($whereis) . ', last_access=' . TIME_NOW . ", $userupdate0, $userupdate1
                    WHERE id = " . sqlesc($users_data['id'])) or sqlerr(__FILE__, __LINE__);
        $cache->update_row('user' . $users_data['id'], [
            'last_access'      => TIME_NOW,
            'onlinetime'       => $update_time,
            'last_access_numb' => TIME_NOW,
            'where_is'         => $whereis,
        ], $site_config['expires']['user_cache']);
    }
    if ($users_data['override_class'] < $users_data['class']) {
        $users_data['class'] = $users_data['override_class'];
    }
    $GLOBALS['CURUSER'] = $users_data;
    get_template();
    $mood = create_moods();
}

/**
 * @return string
 */
function get_charset()
{
    global $CURUSER;
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
    global $site_config, $cache;
    $cleanup_timer = $cache->get('cleanup_timer_');
    if ($cleanup_timer === false || is_null($cleanup_timer)) {
        $cache->set('cleanup_timer_', 5, 1); // runs only every 1 second

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

        if ($site_config['newsrss_on']) {
            $tfreak_cron = $cache->get('tfreak_cron_');
            if ($tfreak_cron === false || is_null($tfreak_cron)) {
                $tfreak_news = $cache->get('tfreak_news_links_');
                if ($tfreak_news === false || is_null($tfreak_news)) {
                    $sql = sql_query("SELECT link FROM newsrss") or sqlerr(__FILE__, __LINE__);
                    while ($tfreak_new = mysqli_fetch_assoc($sql)) {
                        $tfreak_news[] = $tfreak_new['link'];
                    }
                    $cache->set('tfreak_news_links_', $tfreak_news, 86400);
                }

                if (user_exists($site_config['chatBotID'])) {
                    $cache->set('tfreak_cron_', TIME_NOW, 30);
                    require_once INCL_DIR . 'newsrss.php';
                    if (empty($tfreak_news)) {
                        github_shout();
                        foxnews_shout();
                        tfreak_shout();
                    } else {
                        github_shout($tfreak_news);
                        foxnews_shout($tfreak_news);
                        tfreak_shout($tfreak_news);
                    }
                }
            }
        }
    }
}

/**
 * @return mixed
 */
function get_stylesheet()
{
    global $site_config, $CURUSER;
    return isset($CURUSER['stylesheet']) ? $CURUSER['stylesheet'] : $site_config['stylesheet'];
}

/**
 * @return mixed
 */
function get_category_icons()
{
    global $site_config, $CURUSER;
    return isset($CURUSER['categorie_icon']) ? $CURUSER['categorie_icon'] : $site_config['categorie_icon'];
}

/**
 * @return mixed
 */
function get_language()
{
    global $site_config, $CURUSER;
    return isset($CURUSER['language']) ? $CURUSER['language'] : $site_config['language'];
}


/**
 *
 */
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
        /**
         * @param string $title
         * @param null   $stdhead
         *
         * @return string
         */
        function stdhead($title = '', $stdhead = null)
        {
            $css_incl = '';
            if (!empty($stdhead['css'])) {
                foreach ($stdhead['css'] as $CSS) {
                    $css_incl .= "
                <link rel='stylesheet' href='{$CSS}' />";
                }
            }
            $html = "
            <html>
            <head>
            <title>$title</title>$css_incl
            </head>
            <body>";

            return $html;
        }
    }
    if (!function_exists('stdfoot')) {
        function stdfoot($stdfoot = false)
        {
            $htmlfoot = '';
            if (!empty($stdfoot['js'])) {
                foreach ($stdfoot['js'] as $JS) {
                    $htmlfoot .= "
                <script src='{$JS}'></script>";
                }
            }

            $htmlfoot .= "
            </body>
            </html>";

            return $htmlfoot;
        }
    }
    if (!function_exists('stdmsg')) {
        /**
         * @param      $heading
         * @param      $text
         * @param null $class
         *
         * @return string|void
         */
        function stdmsg($heading, $text, $class = null)
        {
            require_once INCL_DIR . 'html_functions.php';

            $htmlout = '';
            if ($heading) {
                $htmlout .= "
                <h2>$heading</h2>";
            }
            $htmlout .= "
                <p>$text</p>";

            return main_div($htmlout, "$class bottom20");
        }
    }
    if (!function_exists('StatusBar')) {
        /**
         * @return string
         */
        function StatusBar()
        {
            global $CURUSER, $lang;

            return "{$lang['gl_msg_welcome']}, {$CURUSER['username']}";
        }
    }
}

/**
 * @param $userid
 * @param $key
 *
 * @return array|string
 */
function make_freeslots($userid, $key)
{
    global $cache;
    $slot = $cache->get($key . $userid);
    if ($slot === false || is_null($slot)) {
        $res_slots = sql_query('SELECT * FROM freeslots WHERE userid = ' . sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
        $slot = [];
        if (mysqli_num_rows($res_slots)) {
            while ($rowslot = mysqli_fetch_assoc($res_slots)) {
                $slot[] = $rowslot;
            }
        }
        $cache->set($key . $userid, $slot, 86400 * 7);
    }

    return $slot;
}

/**
 * @param $userid
 * @param $key
 *
 * @return array|string
 */
function make_bookmarks($userid, $key)
{
    global $cache;
    $book = $cache->get($key . $userid);
    if ($book === false || is_null($book)) {
        $res_books = sql_query('SELECT * FROM bookmarks WHERE userid = ' . sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
        $book = [];
        if (mysqli_num_rows($res_books)) {
            while ($rowbook = mysqli_fetch_assoc($res_books)) {
                $book[] = $rowbook;
            }
        }
        $cache->set($key . $userid, $book, 86400 * 7); // 7 days
    }

    return $book;
}

/**
 * @return array|string
 */
function genrelist()
{
    global $cache, $site_config;
    //if (($ret = $cache->get('genrelist')) == false) {
    $ret = [];
    $res = sql_query('SELECT id, image, name, ordered FROM categories ORDER BY ordered') or sqlerr(__FILE__, __LINE__);
    while ($row = mysqli_fetch_assoc($res)) {
        $ret[] = $row;
    }
    $cache->set('genrelist', $ret, $site_config['expires']['genrelist']);
    //}

    return $ret;
}

/**
 * @param bool $force
 *
 * @return array|string
 */
function create_moods($force = false)
{
    global $cache;
    $key = 'moods';
    if (($mood = $cache->get($key)) === false || $force) {
        $res_moods = sql_query('SELECT * FROM moods ORDER BY id ASC') or sqlerr(__FILE__, __LINE__);
        $mood = [];
        if (mysqli_num_rows($res_moods)) {
            while ($rmood = mysqli_fetch_assoc($res_moods)) {
                $mood['image'][$rmood['id']] = $rmood['image'];
                $mood['name'][$rmood['id']] = $rmood['name'];
            }
        }
        $cache->set($key, $mood, 86400);
    }
    return $mood;
}

//== delete
/**
 * @param      $keys
 * @param bool $keyname
 *
 * @return bool
 */
function delete_id_keys($keys, $keyname = false)
{
    global $cache;
    if (!(is_array($keys) || $keyname)) { // if no key given or not an array
        return false;
    } else {
        foreach ($keys as $id) { // proceed
            $cache->delete($keyname . $id);
        }
    }

    return true;
}

/**
 * @param $x
 *
 * @return string
 */
function unesc($x)
{
    if (get_magic_quotes_gpc()) {
        return stripslashes($x);
    }

    return $x;
}

/**
 * @param     $bytes
 * @param int $dec
 *
 * @return string
 */
function mksize($bytes, $dec = 2)
{
    $neg = 1;
    if ($bytes < 0) {
        $neg = -1;
    }

    $bytes = abs($bytes);
    $bytes = max(0, (int)$bytes);

    if ($bytes < 1024000) {
        return number_format($neg * $bytes / 1024, $dec) . ' KB';
    } //Kilobyte
    elseif ($bytes < 1048576000) {
        return number_format($neg * $bytes / 1048576, $dec) . ' MB';
    } //Megabyte
    elseif ($bytes < 1073741824000) {
        return number_format($neg * $bytes / 1073741824, $dec) . ' GB';
    } //Gigebyte
    elseif ($bytes < 1099511627776000) {
        return number_format($neg * $bytes / 1099511627776, $dec) . ' TB';
    } //Terabyte
    elseif ($bytes < 1125899906842624000) {
        return number_format($neg * $bytes / 1125899906842624, $dec) . ' PB';
    } //Petabyte
    elseif ($bytes < 1152921504606846976000) {
        return number_format($neg * $bytes / 1152921504606846976, $dec) . ' EB';
    } //Exabyte
    elseif ($bytes < 1180591620717411303424000) {
        return number_format($neg * $bytes / 1180591620717411303424, $dec) . ' ZB';
    } //Zettabyte
    else {
        return number_format($neg * $bytes / 1208925819614629174706176, $dec) . ' YB';
    } //Yottabyte
}

/**
 * @param $s
 *
 * @return string
 */
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

/**
 * @param $vars
 *
 * @return int
 */
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

/**
 * @param $name
 *
 * @return int
 */
function validfilename($name)
{
    return preg_match('/^[^\0-\x1f:\\\\\/?*\xff#<>|]+$/si', $name);
}

/**
 * @param $email
 *
 * @return int
 */
function validemail($email)
{
    return preg_match('/^[\w.-]+@([\w.-]+\.)+[a-z]{2,6}$/is', $email);
}

/**
 * @param $x
 *
 * @return int|string
 */
function sqlesc($x)
{
    if (is_integer($x)) {
        return (int)$x;
    }

    return sprintf('\'%s\'', mysqli_real_escape_string($GLOBALS['___mysqli_ston'], $x));
}

/**
 * @param $x
 *
 * @return int|string
 */
function sqlesc_noquote($x)
{
    if (is_integer($x)) {
        return (int)$x;
    }

    return mysqli_real_escape_string($GLOBALS['___mysqli_ston'], $x);
}

/**
 * @param $x
 *
 * @return mixed
 */
function sqlwildcardesc($x)
{
    return str_replace([
                           '%',
                           '_'
                       ], [
                           '\\%',
                           '\\_'
                       ], mysqli_real_escape_string($GLOBALS['___mysqli_ston'], $x));
}

/**
 * @param int $code
 */
function httperr($code = 404)
{
    header('HTTP/1.0 404 Not found');
    echo '<h1>$code - Not Found</h1>';
    echo '<p>Sorry pal :(</p>';
    die();
}

function loggedinorreturn()
{
    global $CURUSER, $site_config, $cache;
    if (!$CURUSER) {
        if ($id = getSessionVar('userID')) {
            $CURUSER = $cache->get('user' . $id);
        } else {
            header("Location: {$site_config['baseurl']}/login.php?returnto=" . urlencode($_SERVER['REQUEST_URI']));
            die();
        }
    }
}

/**
 * @param $s
 *
 * @return mixed
 */
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

/**
 * @param        $table
 * @param string $suffix
 *
 * @return int
 */
function get_row_count($table, $suffix = '')
{
    if ($suffix) {
        $suffix = " $suffix";
    }
    ($r = sql_query("SELECT COUNT(*) FROM $table$suffix")) or die(((is_object($GLOBALS['___mysqli_ston'])) ? mysqli_error($GLOBALS['___mysqli_ston']) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
    ($a = mysqli_fetch_row($r)) or die(((is_object($GLOBALS['___mysqli_ston'])) ? mysqli_error($GLOBALS['___mysqli_ston']) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));

    return (int)$a[0];
}

/**
 * @param $table
 * @param $suffix
 * @param $where
 *
 * @return bool
 */
function get_one_row($table, $suffix, $where)
{
    $sql = "SELECT $suffix FROM $table $where";
    $r = sql_query($sql) or sqlerr(__FILE__, __LINE__);
    $a = mysqli_fetch_row($r);
    if (isset($a[0])) {
        return $a[0];
    } else {
        return false;
    }
}

/**
 * @param      $heading
 * @param      $text
 * @param null $class
 */
function stderr($heading, $text, $class = null)
{
    $htmlout = stdhead();
    $htmlout .= stdmsg($heading, $text, $class);
    $htmlout .= stdfoot();
    echo $htmlout;
    die();
}

// Basic MySQL error handler
/**
 * @param string $file
 * @param string $line
 */
function sqlerr($file = '', $line = '')
{
    global $site_config, $CURUSER;
    $the_error = ((is_object($GLOBALS['___mysqli_ston'])) ? mysqli_error($GLOBALS['___mysqli_ston']) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false));
    $the_error_no = ((is_object($GLOBALS['___mysqli_ston'])) ? mysqli_errno($GLOBALS['___mysqli_ston']) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false));
    if (SQL_DEBUG == 0) {
        die();
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
    die();
}

/**
 * @return false|string
 */
function get_dt_num()
{
    return gmdate('YmdHis');
}

/**
 * @param $text
 */
function write_log($text)
{
    $text = sqlesc($text);
    $added = TIME_NOW;
    sql_query("INSERT INTO sitelog (added, txt) VALUES ($added, $text)") or sqlerr(__FILE__, __LINE__);
}

/**
 * @param $s
 *
 * @return false|int
 */
function sql_timestamp_to_unix_timestamp($s)
{
    return mktime(substr($s, 11, 2), substr($s, 14, 2), substr($s, 17, 2), substr($s, 5, 2), substr($s, 8, 2), substr($s, 0, 4));
}

/**
 * @param int $unix
 *
 * @return array
 */
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

/**
 * @return int
 */
function get_time_offset()
{
    global $CURUSER, $site_config;

    $r = (($CURUSER['time_offset'] != '') ? $CURUSER['time_offset'] : $site_config['time_offset']) * 3600;
    if ($site_config['time_adjust']) {
        $r += ($site_config['time_adjust'] * 60);
    }
    if ($CURUSER['dst_in_use']) {
        $r += 3600;
    }

    return $r;
}

/**
 * @param      $date
 * @param      $method
 * @param int  $norelative
 * @param int  $full_relative
 * @param bool $calc
 *
 * @return false|mixed|string
 */
function get_date($date, $method, $norelative = 0, $full_relative = 0, $calc = false)
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
    if ($full_relative && ($norelative != 1) && !$calc) {
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
    } elseif ($site_config['time_use_relative'] && ($norelative != 1) && !$calc) {
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
    } elseif ($calc) {
        $years = intval($date / 31536000);
        $date -= $years * 31536000;
        $days = intval($date / 86400);
        $date -= $days * 86400;
        $hours = intval($date / 3600);
        $date -= $hours * 3600;
        $mins = intval($date / 60);
        //$secs = $date - ($mins * 60);
        $text = [];
        if ($years > 0) {
            $text[] = number_format($years) . " years";
        }
        if ($days > 0) {
            $text[] = number_format($days) . " days";
        }
        if ($hours > 0) {
            $text[] = number_format($hours) . " hours";
        }
        if ($mins > 0) {
            $text[] = number_format($mins) . " min";
        }
        if (!empty($text)) {
            return implode(', ', $text);
        }
    } else {
        return gmdate($time_options[$method], ($date + $GLOBALS['offset']));
    }
}

/**
 * @param $num
 *
 * @return string|void
 */
function ratingpic($num)
{
    global $site_config;
    $r = round($num * 2) / 2;
    if ($r < 1 || $r > 5) {
        return;
    }

    return "<img src=\"{$site_config['pic_baseurl']}ratings/{$r}.gif\" border=\"0\" alt=\"Rating: $num / 5\" title=\"Rating: $num / 5\" />";
}

/**
 * @param $hash
 *
 * @return string
 */
function hash_pad($hash)
{
    return str_pad($hash, 20);
}

//== cutname = Laffin
/**
 * @param     $txt
 * @param int $len
 *
 * @return string
 */
function CutName($txt, $len = 40)
{
    return strlen($txt) > $len ? substr($txt, 0, $len - 1) . '...' : $txt;
}

/**
 * @param     $txt
 * @param int $len
 *
 * @return string
 */
function CutName_B($txt, $len = 20)
{
    return strlen($txt) > $len ? substr($txt, 0, $len - 1) . '...' : $txt;
}

/**
 * @param string $file
 *
 * @return array
 */
function load_language($file = '')
{
    global $site_config, $CURUSER;
    $lang = [];
    if (!isset($GLOBALS['CURUSER']) || empty($GLOBALS['CURUSER']['language'])) {
        if (!file_exists(LANG_DIR . "{$site_config['language']}/lang_{$file}.php")) {
            stderr('System Error', "Can't find language files({$site_config['language']})");
        }
        include_once LANG_DIR . "{$site_config['language']}/lang_{$file}.php";
    } elseif (!file_exists(LANG_DIR . "{$CURUSER['language']}/lang_{$file}.php")) {
        if (!file_exists(LANG_DIR . "1/lang_{$file}.php")) {
            stderr('System Error', "Can't find language files({$CURUSER['language']} and 1)");
        }
        include_once LANG_DIR . "1/lang_{$file}.php";
    } else {
        include_once LANG_DIR . "{$CURUSER['language']}/lang_{$file}.php";
    }
    return $lang;
}

/**
 * @param $table
 */
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

/**
 * @param      $query
 * @param bool $log
 *
 * @return bool|mysqli_result
 */
function sql_query($query, $log = true)
{
    global $query_stat, $queries;

    $query_start_time = microtime(true);
    $result = mysqli_query($GLOBALS['___mysqli_ston'], $query);
    $query_end_time = microtime(true);
    $query_stat[] = [
        'seconds' => number_format($query_end_time - $query_start_time, 6),
        'query'   => $query,
    ];
    $queries = count($query_stat);

    return $result;
}

/**
 * @param $p
 *
 * @return string
 */
function get_percent_completed_image($p)
{
    global $site_config;
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

    return "<img src='{$site_config['pic_baseurl']}{$img}.gif' alt='percent' />";
}

/**
 * @param $ar
 *
 * @return array|string
 */
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
            VALUES (' . sqlesc($http_agent) . ', ' . ipToStorageFormat($ip, true) . ', ' . sqlesc($http_referer) . ', ' . sqlesc($http_page) . ', ' . sqlesc(TIME_NOW) . ')') or sqlerr(__FILE__, __LINE__);
    }
}

/**
 * @param       $query
 * @param array $default_value
 *
 * @return array|bool|string
 */
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

/**
 * @param $userid
 * @param $amount
 * @param $type
 */
function write_bonus_log($userid, $amount, $type)
{
    $added = TIME_NOW;
    $donation_type = $type;
    sql_query('INSERT INTO bonuslog (id, donation, type, added_at)
                VALUES(' . sqlesc($userid) . ', ' . sqlesc($amount) . ', ' . sqlesc($donation_type) . ", $added)") or sqlerr(__FILE__, __LINE__);
}

/**
 * @param     $bytes
 * @param int $dec
 *
 * @return string
 */
function human_filesize($bytes, $dec = 2)
{
    $size = [
        'B',
        'kB',
        'MB',
        'GB',
        'TB',
        'PB',
        'EB',
        'ZB',
        'YB'
    ];
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
        ini_set('default_charset', $site_config['char_set']);

        // Start the session:
        session_start();
    }

    // Create a new AUTH token.
    if (!getSessionVar('auth')) {
        setSessionVar('auth', make_password(32));
    }

    // Create a new CSRF token.
    if (!getSessionVar($site_config['session_csrf'])) {
        setSessionVar($site_config['session_csrf'], make_password(32));
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
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
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

/**
 * @param      $token
 * @param null $key
 * @param bool $regen
 *
 * @return bool
 */
function validateToken($token, $key = null, $regen = false)
{
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
            setSessionVar($key, make_password(32));
        }
        return true;
    }
    return false;
}

/**
 * @param      $key
 * @param      $value
 * @param null $prefix
 */
function setSessionVar($key, $value, $prefix = null)
{
    global $site_config;
    if ($prefix === null) {
        $prefix = $site_config['sessionKeyPrefix'];
    }
    if (in_array($key, $site_config['notifications'])) {
        $current = getSessionVar($key);
        if ($current) {
            if (!in_array($value, $current)) {
                $_SESSION[$prefix . $key] = array_merge($current, [$value]);
            }
        } else {
            $_SESSION[$prefix . $key] = [$value];
        }
    } else {
        unsetSessionVar($key);
        $_SESSION[$prefix . $key] = $value;
    }
}

/**
 * @param      $key
 * @param null $prefix
 *
 * @return null
 */
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

/**
 * @param      $key
 * @param null $prefix
 */
function unsetSessionVar($key, $prefix = null)
{
    global $site_config;
    if ($prefix === null) {
        $prefix = $site_config['sessionKeyPrefix'];
    }

    // Set the session value:
    unset($_SESSION[$prefix . $key]);
}

/**
 * @return null|string
 *
 * @throws Exception
 */
function salty()
{
    $auth = getSessionVar('auth');
    if (empty($auth)) {
        $auth = make_password(32);
    }
    setSessionVar('salt', make_passhash($auth));

    return $auth;
}

/**
 * @param $text
 *
 * @return string
 */
function replace_unicode_strings($text)
{
    $text = str_replace([
                            '“',
                            '”'
                        ], '"', $text);
    $text = str_replace([
                            '&quot;',
                            '&lsquo;',
                            '‘',
                            '&rsquo;',
                            '’'
                        ], "'", $text);
    $text = str_replace([
                            '&ldquo;',
                            '“',
                            '&rdquo;',
                            '”'
                        ], '"', $text);
    $text = str_replace([
                            '&#8212;',
                            '–'
                        ], '-', $text);
    $text = str_replace('&amp;', '&#38;', $text);
    return html_entity_decode(htmlentities($text, ENT_QUOTES));
}

/**
 * @param $userid
 *
 * @return array|string
 */
function getPmCount($userid)
{
    global $cache, $site_config;

    $pmCount = $cache->get('inbox_' . $userid);
    if ($pmCount === false || is_null($pmCount)) {
        $res = sql_query('SELECT COUNT(id) FROM messages WHERE receiver = ' . sqlesc($userid) . " AND unread = 'yes' AND location = 1") or sqlerr(__LINE__, __FILE__);
        $result = mysqli_fetch_row($res);
        $pmCount = (int)$result[0];
        $cache->set('inbox_' . $userid, $pmCount, $site_config['expires']['unread']);
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
    referer();
    if (!validateToken(getSessionVar('auth'), 'auth')) {
        destroySession();
        header('Location: login.php');
        die();
    }
    loggedinorreturn();
    parked();
    suspended();
}

/**
 * @param int $minVal
 * @param int $maxVal
 *
 * @return string
 */
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

/**
 * @param $user_id
 *
 * @return bool
 */
function user_exists($user_id)
{
    global $cache;
    $userlist = $cache->get('userlist_' . $user_id);
    if ($userlist === false || is_null($userlist)) {
        $query = "SELECT id FROM users WHERE id = " . sqlesc($user_id);
        $res = sql_query($query) or sqlerr(__FILE__, __LINE__);
        $res = mysqli_fetch_assoc($res);
        if (empty($res)) {
            return false;
        }
        $cache->set('userlist_' . $user_id, $res, 86400);
    }
    return true;
}

/**
 * @return array|null|string
 */
function get_poll()
{
    global $CURUSER, $cache, $site_config, $fluent;

    //$poll_data = $cache->get('poll_data_' . $CURUSER['id']);
    //if ($poll_data === false || is_null($poll_data)) {
    $poll_data = $fluent->from('polls')
        ->orderBy('start_date DESC')
        ->limit(1)
        ->fetch();

    $vote_data = $fluent->from('poll_voters')
        ->select(null)
        ->select('INET6_NTOA(ip) AS ip')
        ->select('user_id')
        ->select('vote_date')
        ->where('user_id = ?', $CURUSER['id'])
        ->where('poll_id = ?', $poll_data['pid'])
        ->limit('1')
        ->fetch();
    $poll_data['ip'] = $vote_data['ip'];
    $poll_data['user_id'] = $vote_data['user_id'];
    $poll_data['vote_date'] = $vote_data['vote_date'];

    $cache->set('poll_data_' . $CURUSER['id'], $poll_data, $site_config['expires']['poll_data']);
    //}

    return $poll_data;
}

/**
 * @param     $list
 * @param int $times
 *
 * @return array
 */
function shuffle_assoc($list, $times = 1)
{
    if (!is_array($list)) {
        return $list;
    }

    $keys = array_keys($list);
    foreach (range(0, $times) as $number) {
        shuffle($keys);
    }
    $random = [];
    foreach ($keys as $key) {
        $random[$key] = $list[$key];
    }
    return $random;
}

/**
 * @return mixed
 */
function get_scheme()
{
    if (isset($_SERVER['REQUEST_SCHEME'])) {
        return $_SERVER['REQUEST_SCHEME'];
    }
    return null;
}

/**
 * @param $array
 * @param $cols
 *
 * @return array
 */
function array_msort($array, $cols)
{
    $colarr = [];
    foreach ($cols as $col => $order) {
        $colarr[$col] = [];
        foreach ($array as $k => $row) {
            $colarr[$col]['_' . $k] = strtolower($row[$col]);
        }
    }
    $eval = 'array_multisort(';
    foreach ($cols as $col => $order) {
        $eval .= '$colarr[\'' . $col . '\'],' . $order . ',';
    }
    $eval = substr($eval, 0, -1) . ');';
    eval($eval);
    $ret = [];
    foreach ($colarr as $col => $arr) {
        foreach ($arr as $k => $v) {
            $k = substr($k, 1);
            if (!isset($ret[$k])) {
                $ret[$k] = $array[$k];
            }
            $ret[$k][$col] = $array[$k][$col];
        }
    }
    return $ret;
}

/**
 * @return array|string
 */
function countries()
{
    global $cache, $site_config;
    $ret = $cache->get('countries_arr');
    if ($ret === false || is_null($ret)) {
        $res = sql_query('SELECT id, name, flagpic FROM countries ORDER BY name ASC') or sqlerr(__FILE__, __LINE__);
        while ($row = mysqli_fetch_assoc($res)) {
            $ret[] = $row;
        }
        $cache->set('countries_arr', $ret, $site_config['expires']['user_flag']);
    }

    return $ret;
}

/**
 * @param string $separator
 * @param string $home
 *
 * @return string
 */
function breadcrumbs($separator = '', $home = 'Home')
{
    global $site_config;
    $path = array_filter(explode('/', parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)));
    $query = parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY);
    $base = $site_config['baseurl'] . '/';
    $breadcrumbs = ["<li><a href='$base'>$home</a></li>"];
    $keys = array_keys($path);
    $last = end($keys);
    $action = [];

    if (!empty($query)) {
        $action = explode('=', $query);
    }
    if (!empty($action[0]) && $action[0] === 'action') {
        $last = '';
    }

    foreach ($path as $x => $crumb) {
        $title = ucwords(str_replace([
                                         '.php',
                                         '_'
                                     ], [
                                         '',
                                         ' '
                                     ], $crumb));
        if ($x != $last) {
            $breadcrumbs[] = "<li><a href='$base$crumb'>$title</a></li>";
        } else {
            $breadcrumbs[] = $title;
        }
    }

    if (!empty($action[0]) && $action[0] === 'action') {
        $type = explode('&', str_replace([
                                             '-',
                                             '_'
                                         ], ' ', $action[1]));
        $breadcrumbs[] = ucwords($type[0]);
    }

//    if (!empty($query)) {
//        $query_str = '';
//        if (getSessionVar('query_str')) {
//            $query_str = getSessionVar('query_str');
//        }
//    }

    /*
    if (!empty($query)) {
    $query_str = '';
    if (getSessionVar('query_str')) {
        $query_str = getSessionVar('query_str');
    }

    $action = explode('=', $query);
    if ($action[0] === 'action') {
        if ($action[1] === 'view_topic&topic_id') {
            $breadcrumbs[] = "<a href='{$base}forums.php?{$query_str}'>Forum</a>";
        } elseif ($action[1] === 'add') {
            $breadcrumbs[] = "<a href='{$base}staffpanel.php?{$query_str}'>Forum</a>";
        }
        $type = explode('&', str_replace('view', '', $action[1]));
        if (!empty($action[2])) {
            $breadcrumbs[] = ucwords(str_replace(['_', '-'], ' ', $type[0])) . ' #' . $action[2];
        } else {
            array_pop($breadcrumbs);
            $breadcrumbs[] = ucwords(str_replace(['_', '-'], ' ', $type[0]));
        }
    } elseif ($action[0] === 'tool') {
        $type = explode('&', str_replace('&mode', '', $action[1]));
        $breadcrumbs[] = ucwords(str_replace(['_', '-'], ' ', $type[0]));
    } elseif ($action[0] === 'id') {
        if (in_array('details.php', $path)) {
            array_pop($breadcrumbs);
            $breadcrumbs[] = "<a href='{$base}browse.php?{$query_str}'>Browse</a>";
            $breadcrumbs[] = "Torrent Details";
        } elseif (in_array('userdetails.php', $path)) {
            array_pop($breadcrumbs);
            $breadcrumbs[] = "User Details";
        }
    } elseif ($action[0] === 'search' || strpos($query, 'searchin')) {
        array_pop($breadcrumbs);
        $breadcrumbs[] = "Browse";
    } elseif ($action[0] === 'do') {
        array_pop($breadcrumbs);
        $breadcrumbs[] = "Invite";
    }
    }
*/
    $current = "<li class='is-active'><a href='#' aria-current='page'><span class='has-text-white'>" . end($breadcrumbs) . "</span></a></li>";
    array_pop($breadcrumbs);
    $breadcrumbs[] = $current;

    setSessionVar('query_str', parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY));
    return implode($separator, $breadcrumbs);
}

/**
 * @param $link
 * @param $text
 *
 * @return string
 */
function bubble($link, $text)
{
    $id = uniqid('id_');
    $bubble = "
        <span class='dt-tooltipper-small size_5 has-text-primary' data-tooltip-content='#{$id}'>
            $link
        </span>
        <div class='tooltip_templates'>
            <span id='$id'>
                $text
            </span>
        </div>";

    return $bubble;
}

/**
 * @param $ip
 *
 * @return string
 */
function make_nice_address($ip)
{
    $dom = @gethostbyaddr($ip);
    if ($dom == $ip || @gethostbyname($dom) != $ip) {
        return $ip;
    } else {
        return $ip . '<br>' . $dom;
    }
}

/**
 * @param $val
 *
 * @return int|string
 */
function return_bytes($val)
{
    if ($val == '') {
        return 0;
    }
    $val = strtolower(trim($val));
    $last = $val[strlen($val) - 1];
    $val = rtrim($val, $last);

    switch ($last) {
        case 'g':
            $val *= (1024 * 1024 * 1024);
            break;
        case 'm':
            $val *= (1024 * 1024);
            break;
        case 'k':
            $val *= 1024;
            break;
    }

    return $val;
}

/**
 * @param $int
 *
 * @return string
 */
function plural($int)
{
    if ($int != 1) {
        return 's';
    }
}

/**
 * @param $ip
 *
 * @return string
 */
function ipToStorageFormat($ip)
{
    $ip = empty($ip) ? '10.10.10.10' : $ip;
    if (!filter_var($ip, FILTER_VALIDATE_IP)) {
        $ip = '10.10.10.10';
    }
    return '0x' . bin2hex(inet_pton($ip));
}

/**
 * @param      $username
 * @param bool $ajax
 *
 * @return bool
 */
function valid_username($username, $ajax = false)
{
    global $lang;

    if ($username == '') {
        return false;
    }
    $namelength = strlen($username);
    if ($namelength < 3 || $namelength > 64) {
        if ($ajax) {
            return "<span style='color: #cc0000;'>{$lang['takesignup_username_length']}</span> - $namelength characters";
        } else {
            stderr($lang['takesignup_user_error'], $lang['takesignup_username_length']);
        }
    }

    if (!ctype_alnum($username)) {
        if ($ajax) {
            echo "<span style='color: #cc0000;'>{$lang['takesignup_allowed_chars']}</span>";
            die();
        }
        return false;
    }
    return true;
}

/**
 * @param bool $celebrate
 *
 * @return bool
 */
function Christmas($celebrate = true)
{
    $upperBound = new DateTime("Dec 31");
    $lowerBound = new DateTime("Dec 1");
    $checkDate = new DateTime(date('M d', strtotime("Today")));

    if ($celebrate && $checkDate >= $lowerBound && $checkDate <= $upperBound) {
        return true;
    }
    return false;
}

/**
 * @param $id
 *
 * @return bool|mixed
 */
function get_user_data(int $id)
{
    global $cache, $fluent, $site_config;
    $users_data = $cache->get('user' . $id);
    if ($users_data === false || is_null($users_data)) {
        $users_data = $fluent->from('users')
            ->select('INET6_NTOA(ip) AS ip')
            ->where('id = ?', $id)
            ->fetch();
        unset($users_data['hintanswer']);
        unset($users_data['passhash']);

        $cache->set('user' . $id, $users_data, $site_config['expires']['user_cache']);
    }

    return $users_data;
}

/**
 * @return string
 */
function show_php_version()
{
    preg_match('/^(\d+\.\d+\.\d+).*$/', phpversion(), $match);
    if (!empty($match[1])) {
        return $match[1];
    }

    return phpversion();
}

/**
 * @return mixed
 */
function get_anonymous_name()
{
    global $site_config;

    $names = str_replace(', ', ',', $site_config['anonymous_names']);
    $array = explode(',', $names);
    $index = array_rand($array);
    $anon = $array[$index];

    return $anon;
}

/**
 * @param $url
 *
 * @return string
 */
function image_proxy($url)
{
    global $site_config;


    if (empty($url) || preg_match('#' . $site_config['domain'] . '#', $url)) {
        return $url;
    }
    if (!empty($site_config['image_proxy'])) {
        $encrypted = CryptoJSAES::encrypt($url, $site_config['image_proxy_key']);
        return $site_config['image_proxy'] . base64_encode($encrypted);
    }
    return $url;
}

/**
 * @param string $name
 *
 * @return string
 */
function get_show_name(string $name)
{
    if (preg_match("/^(.*)S\d+(E\d+)?/i", $name, $tmp)) {
        return trim(str_replace([
                                    '.',
                                    '_'
                                ], ' ', $tmp[1]));
    } else {
        return trim(str_replace([
                                    '.',
                                    '_'
                                ], ' ', $name));
    }
}

/**
 * @param string $name
 * @param string $type
 *
 * @return null|string
 */
function get_show_id(string $name, string $type)
{
    global $cache, $fluent;

    if (empty($name) || empty($type)) {
        return null;
    }
    $name = get_show_name($name);
    $hash = hash('sha512', $name);
    $id_array = $cache->get('tvshow_ids_' . $hash);
    if ($id_array === false || is_null($id_array)) {
        $id_array = $fluent->from('tvmaze')
            ->where('MATCH (name) AGAINST (? IN NATURAL LANGUAGE MODE)', $name)
            ->fetch();
        if ($id_array) {
            $cache->set('tvshow_ids_' . $hash, $id_array, 0);
        }
    }
    if ($id_array) {
        extract($id_array);
        return $$type;
    }
    return null;
}

/**
 * @param $h24
 * @param $min
 *
 * @return string
 */
function time24to12($h24, $min)
{
    if ($h24 === 0) {
        $newhour = 12;
    } elseif ($h24 <= 12) {
        $newhour = $h24;
    } elseif ($h24 > 12) {
        $newhour = $h24 - 12;
    }
    return ($h24 < 12) ? $newhour . ":$min am" : $newhour . ":$min pm";
}

if (file_exists(ROOT_DIR . 'public' . DIRECTORY_SEPARATOR . 'install')) {
    setSessionVar('is-danger', "[h1]This site is vulnerable until you delete the install directory[/h1][p]rm -r " . ROOT_DIR . "public" . DIRECTORY_SEPARATOR . "install" . DIRECTORY_SEPARATOR . "[/p]");
}
