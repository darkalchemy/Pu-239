<?php

$starttime = microtime(true);

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'define.php';
require_once INCL_DIR . 'config.php';
require_once INCL_DIR . 'common_functions.php';
require_once INCL_DIR . 'site_config.php';
require_once VENDOR_DIR . 'autoload.php';

$dotenv = new Dotenv\Dotenv(ROOT_DIR);
$dotenv->load();

$free = json_decode(file_get_contents(CACHE_DIR . 'free_cache.php'), true);
require_once INCL_DIR . 'password_functions.php';
$cache = new DarkAlchemy\Pu239\Cache();
$fluent = new DarkAlchemy\Pu239\Database();
$session = new DarkAlchemy\Pu239\Session();
require_once INCL_DIR . 'site_settings.php';
$user_stuffs = new DarkAlchemy\Pu239\User();
$torrent_stuffs = new DarkAlchemy\Pu239\Torrent();
$image_stuffs = new DarkAlchemy\Pu239\Image();
$comment_stuffs = new DarkAlchemy\Pu239\Comment();
$failed_logins = new DarkAlchemy\Pu239\FailedLogin();
$message_stuffs = new DarkAlchemy\Pu239\Message();
$ip_stuffs = new DarkAlchemy\Pu239\IP();
$ban_stuffs = new DarkAlchemy\Pu239\Ban();
$searchcloud_stuffs = new DarkAlchemy\Pu239\Searchcloud();
$post_stuffs = new DarkAlchemy\Pu239\Post();
$referer_stuffs = new DarkAlchemy\Pu239\Referer();
$achievement_stuffs = new DarkAlchemy\Pu239\Achievement();
$usersachiev_stuffs = new DarkAlchemy\Pu239\Usersachiev();
$pollvoter_stuffs = new DarkAlchemy\Pu239\PollVoter();
$happylog_stuffs = new DarkAlchemy\Pu239\HappyLog();
$snatched_stuffs = new DarkAlchemy\Pu239\Snatched();

if (SOCKET) {
    $mysqli = new mysqli($_ENV['DB_HOST'], $_ENV['DB_USERNAME'], $_ENV['DB_PASSWORD'], $_ENV['DB_DATABASE'], null, $_ENV['DB_SOCKET']);
} else {
    $mysqli = new mysqli($_ENV['DB_HOST'], $_ENV['DB_USERNAME'], $_ENV['DB_PASSWORD'], $_ENV['DB_DATABASE'], $_ENV['DB_PORT']);
}

require_once CACHE_DIR . 'class_config.php';
$session->start();

/**
 * Class curuser.
 */
class curuser
{
    public static $blocks = [];
}

$CURBLOCK = &curuser::$blocks;
require_once CLASS_DIR . 'class_blocks_index.php';
require_once CLASS_DIR . 'class_blocks_stdhead.php';
require_once CLASS_DIR . 'class_blocks_userdetails.php';
require_once CLASS_DIR . 'class_blocks_apis.php';
require_once CLASS_DIR . 'class_bt_options.php';
require_once CACHE_DIR . 'block_settings_cache.php';
require_once INCL_DIR . 'database.php';

if (!$site_config['in_production']) {
    $pu239_version = new SebastianBergmann\Version('0.5', ROOT_DIR);
    $site_config['version'] = $pu239_version->getVersion();
}

$load = sys_getloadavg();
$cores = $cache->get('cores_');
if ($cores === false || is_null($cores)) {
    $cores = `grep -c processor /proc/cpuinfo`;
    $cores = empty($cores) ? 1 : (int) $cores;
    $cache->set('cores_', $cores, 0);
}
if ($load[0] > $cores * 2) {
    die("Load is too high. Don't continuously refresh, or you will just make the problem last longer");
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

    return hash('sha256', $site_config['tracker_post_key'] . implode('', $ids) . $site_config['tracker_post_key']);
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
    if (!is_array($ids) || !$key) {
        return false;
    }

    return $key == hash('sha256', $site_config['tracker_post_key'] . implode('', $ids) . $site_config['tracker_post_key']);
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
 * @param bool $login
 *
 * @return mixed
 */
function getip($login = false)
{
    global $CURUSER;

    $ip = !empty($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1';
    if (!validip($ip)) {
        $ip = '127.0.0.1';
    }
    $no_log_ip = $CURUSER['perms'] & bt_options::PERMS_NO_IP;
    if ($login || (IP_LOGGING && !$no_log_ip)) {
        return $ip;
    }

    return '127.0.0.1';
}

function dbconn()
{
    global $site_config, $mysqli;

    if ($mysqli->connect_error) {
        die('Connect Error (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);
    }
}

/**
 * @param int $id
 *
 * @throws \Envms\FluentPDO\Exception
 */
function status_change(int $id)
{
    global $fluent;

    $set = [
        'status' => 0,
    ];
    $fluent->update('announcement_process')
           ->set($set)
           ->where('user_id = ?', $id)
           ->where('status = 1')
           ->execute();
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
        ((mysqli_free_result($ban_sql) || (is_object($ban_sql) && (get_class($ban_sql) === 'mysqli_result'))) ? true : false);
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
 * @return bool
 *
 * @throws Exception
 * @throws \MatthiasMullie\Scrapbook\Exception\Exception
 * @throws \MatthiasMullie\Scrapbook\Exception\ServerUnhealthy
 * @throws \MatthiasMullie\Scrapbook\Exception\UnbegunTransaction
 */
function userlogin()
{
    global $site_config, $CURBLOCK, $mood, $whereis, $CURUSER, $cache, $session, $user_stuffs;

    unset($GLOBALS['CURUSER']);

    if (isset($CURUSER)) {
        return true;
    }

    $id = $user_stuffs->getUserId();
    if (!$id) {
        $session->destroy();
    }
    $forced_logout = $cache->get('forced_logout_' . $id);
    if ($forced_logout) {
        $last_access = $session->get('last_access');
        if (!empty($last_access) && $last_access <= $forced_logout) {
            $session->destroy();
        }
    }

    $ip = getip(true);

    $users_data = $user_stuffs->getUserFromId($id);
    if (empty($users_data)) {
        $session->destroy();
    }

    if (!$site_config['site_online'] && $users_data['class'] < UC_STAFF) {
        $session->destroy();
    }

    if (!isset($users_data['perms']) || (!($users_data['perms'] & bt_options::PERMS_BYPASS_BAN))) {
        $banned = false;
        if (check_bans($ip, $reason)) {
            $banned = true;
        }
        if ($banned) {
            require_once INCL_DIR . 'html_functions.php';
            header('Content-Type: text/html; charset=' . $site_config['char_set']);
            echo doc_head() . "
<title>Forbidden</title>
</head>
<body>
    <h1>403 Forbidden</h1>
    <h1>Unauthorized IP address!</h1>
    <p>Reason: <strong>' . htmlsafechars($reason) . '</strong></p>
</body>
</html>";
            $session->destroy();
        }
    }
    if ($users_data['class'] >= UC_STAFF) {
        if (!in_array($users_data['id'], $site_config['is_staff']['allowed'], true)) {
            require_once INCL_DIR . 'function_autopost.php';
            $msg = 'Fake Account Detected: Username: ' . htmlsafechars($users_data['username']) . ' - userID: ' . (int) $users_data['id'] . ' - UserIP : ' . getip();
            sql_query("UPDATE users SET enabled = 'no', class = 0 WHERE id =" . sqlesc($users_data['id'])) or sqlerr(__FILE__, __LINE__);
            $cache->update_row('user' . $users_data['id'], [
                'enabled' => 'no',
                'class' => 0,
            ], $site_config['expires']['user_cache']);
            write_log($msg);
            $body = "User: [url={$site_config['baseurl']}/userdetails.php?id={$users_data['id']}][class=user]{$users_data['username']}[/class][/url] - {$ip}[br]Class {$users_data['class']}[br]Current page: {$_SERVER['PHP_SELF']}[br]Previous page: " . (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'no referer') . '[br]Action: ' . $_SERVER['REQUEST_URI'] . '[br] Member has been disabled and demoted by class check system.';
            $subject = 'Fake Account Detected!';
            auto_post($subject, $body);
            $session->set('is-danger', 'This account has been banned');
            header("Location: {$site_config['baseurl']}/logout.php");
            die();
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
                'archive' => '',
            ];
        }
        $cache->set('userstatus_' . $id, $ustatus, $site_config['expires']['u_status']); // 30 days
    }
    $users_data['last_status'] = $ustatus['last_status'];
    $users_data['last_update'] = $ustatus['last_update'];
    $users_data['archive'] = $ustatus['archive'];
    $blocks_key = 'blocks_' . $users_data['id'];

    $CURBLOCK = $cache->get($blocks_key);
    if ($CURBLOCK === false || is_null($CURBLOCK)) {
        $c_sql = sql_query('SELECT * FROM user_blocks WHERE userid = ' . sqlesc($users_data['id'])) or sqlerr(__FILE__, __LINE__);
        if (mysqli_num_rows($c_sql) == 0) {
            sql_query('INSERT INTO user_blocks(userid) VALUES (' . sqlesc($users_data['id']) . ')') or sqlerr(__FILE__, __LINE__);
            $c_sql = sql_query('SELECT * FROM user_blocks WHERE userid = ' . sqlesc($users_data['id'])) or sqlerr(__FILE__, __LINE__);
        }
        $CURBLOCK = mysqli_fetch_assoc($c_sql);
        $CURBLOCK['index_page'] = (int) $CURBLOCK['index_page'];
        $CURBLOCK['global_stdhead'] = (int) $CURBLOCK['global_stdhead'];
        $CURBLOCK['userdetails_page'] = (int) $CURBLOCK['userdetails_page'];
        $cache->set($blocks_key, $CURBLOCK, 0);
    }
    $where_is['username'] = htmlsafechars($users_data['username']);
    $whereis_array = [
        'index' => '%s is viewing the <a href="%s">Home Page</a>',
        'browse' => '%s is viewing the <a href="%s">Torrents Browse Page</a>',
        'catalog' => '%s is viewing the <a href="%s">Torrents Catalog Page</a>',
        'offers' => '%s is viewing the <a href="%s">Offers</a>',
        'requests' => '%s is viewing the <a href="%s">Requests</a>',
        'upload' => '%s is viewing the <a href="%s">Upload Torrent Page</a>',
        'casino' => '%s is playing in the <a href="%s">Casino</a>',
        'blackjack' => '%s is playing the <a href="%s">Blackjack</a>',
        'bet' => '%s is making a <a href="%s">Bet</a>',
        'forums' => '%s is viewing the <a href="%s">Forums</a>',
        'chat' => '%s is viewing the <a href="%s">IRC</a>',
        'topten' => '%s is viewing the <a href="%s">Statistics</a>',
        'faq' => '%s is viewing the <a href="%s">FAQ</a>',
        'rules' => '%s is viewing the <a href="%s">Rules</a>',
        'staff' => '%s is viewing the <a href="%s">Staff Page</a>',
        'announcement' => '%s is viewing the <a href="%s">Announcements/a>',
        'usercp' => '%s is viewing the <a href="%s">Users Control Panel</a>',
        'messages' => '%s is viewing the <a href="%s">Mailbox</a>',
        'userdetails' => '%s is viewing the <a href="%s">Personal Profile</a>',
        'details' => '%s is viewing the <a href="%s">Torrents Detail</a>',
        'games' => '%s is viewing the <a href="%s">Games</a>',
        'arcade' => '%s is viewing the <a href="%s">Arcade</a>',
        'flash' => '%s is playing a <a href="%s">Flash Game</a>',
        'arcade_top_score' => '%s is viewing the <a href="%s">Arcade Top Scores</a>',
        'staffpanel' => '%s is viewing the <a href="%s">Staff Panel</a>',
        'movies' => '%s is viewing the <a href="%s">Movies and TV</a>',
        'needseeds' => '%s is viewing the <a href="%s">Need Seeds Page</a>',
        'bitbucket' => '%s is viewing the <a href="%s">Bitbucket</a>',
        'mybonus' => '%s is viewing the <a href="%s">Karma Store</a>',
        'getrss' => '%s is viewing the <a href="%s">RSS</a>',
        'rsstfreak' => '%s is viewing the <a href="%s">Torrent Freak Page</a>',
        'wiki' => '%s is viewing the <a href="%s">Wiki Page</a>',
        'lottery' => '%s is playing the <a href="%s">Lottery</a>',
        'bookmarks' => '%s is viewing the <a href="%s">Bookmarks Page</a>',
        'sharemarks' => '%s is viewing the <a href="%s">Sharemarks Page</a>',
        'friends' => '%s is viewing the <a href="%s">Friends List</a>',
        'users' => '%s is searching the <a href="%s">Users</a>',
        'tmovies' => '%s is viewing the <a href="%s">Movies</a>',
        'unknown' => '%s location is unknown',
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
    $session->set('last_access', TIME_NOW);
    $userupdate1 = 'last_access_numb = ' . TIME_NOW;
    $update_time = ($users_data['onlinetime'] + $update_time);
    if (($users_data['last_access'] != '0') && (($users_data['last_access']) < (TIME_NOW - 180))) {
        sql_query('UPDATE users
                    SET where_is =' . sqlesc($whereis) . ', last_access = ' . TIME_NOW . ", $userupdate0, $userupdate1
                    WHERE id = " . sqlesc($users_data['id'])) or sqlerr(__FILE__, __LINE__);
        $cache->update_row('user' . $users_data['id'], [
            'last_access' => TIME_NOW,
            'onlinetime' => $update_time,
            'last_access_numb' => TIME_NOW,
            'where_is' => $whereis,
        ], $site_config['expires']['user_cache']);
    }
    if ($users_data['override_class'] < $users_data['class']) {
        $users_data['class'] = $users_data['override_class'];
    }
    $session->set('use_12_hour', $users_data['use_12_hour']);
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

    $lang_charset = isset($CURUSER['language']) ? $CURUSER['language'] : 0;
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

/**
 * @return int
 *
 * @throws \Envms\FluentPDO\Exception
 */
function get_stylesheet()
{
    global $site_config, $user_stuffs, $session, $fluent, $cache;

    $user = '';
    $userid = $session->get('userID');
    if (!empty($userid)) {
        $user = $user_stuffs->getUserFromId($userid);
    }

    $style = isset($user['stylesheet']) ? (int) $user['stylesheet'] : (int) $site_config['stylesheet'];

    $class_config = $cache->get('class_config_' . $style);
    if ($class_config === false || is_null($class_config)) {
        $class_config = $fluent->from('class_config')
                               ->orderBy('value ASC')
                               ->where('template = ?', $style)
                               ->fetchAll();

        $cache->set('class_config_' . $style, $class_config, 86400);
    }
    foreach ($class_config as $arr) {
        if ($arr['name'] !== 'UC_STAFF' && $arr['name'] !== 'UC_MIN' && $arr['name'] !== 'UC_MAX') {
            $site_config['class_names'][$arr['value']] = $arr['classname'];
            $site_config['class_colors'][$arr['value']] = $arr['classcolor'];
            $site_config['class_images'][$arr['value']] = $site_config['pic_baseurl'] . "class/{$arr['classpic']}";
        }
    }

    return $style;
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

function get_template()
{
    global $CURUSER, $site_config;

    if (!empty($CURUSER)) {
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
}

/**
 * @param $userid
 * @param $key
 *
 * @return array|bool|mixed
 *
 * @throws \Envms\FluentPDO\Exception
 */
function make_freeslots($userid, $key)
{
    global $cache, $fluent;

    $slot = $cache->get($key . $userid);
    if ($slot === false || is_null($slot)) {
        $slot = $fluent->from('freeslots')
                       ->where('userid = ?', $userid)
                       ->fetchAll();

        $cache->set($key . $userid, $slot, 86400 * 7);
    }

    return $slot;
}

/**
 * @param $userid
 * @param $key
 *
 * @return array|bool|mixed
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
 * @return array|bool|mixed
 *
 * @throws \Envms\FluentPDO\Exception
 */
function genrelist(bool $grouped)
{
    global $site_config, $cache, $fluent;

    if ($grouped) {
        $ret = $cache->get('genrelist_grouped');
        if ($ret === false || is_null($ret)) {
            $parents = $fluent->from('categories')
                ->where('parent_id = 0')
                ->orderBy('ordered');
            foreach ($parents as $parent) {
                $children = $fluent->from('categories')
                    ->where('parent_id = ?', $parent['id'])
                    ->orderBy('ordered')
                    ->fetchAll();

                $parent['children'] = $children;
                $ret[] = $parent;
            }

            $cache->set('genrelist_grouped', $ret, $site_config['expires']['genrelist']);
        }
    } else {
        $ret = $cache->get('genrelist_ordered');
        if ($ret === false || is_null($ret)) {
            $cats = $fluent->from('categories AS c')
                ->select('p.name AS parent_name')
                ->leftJoin('categories AS p ON c.parent_id = p.id')
                ->orderBy('ordered');

            foreach ($cats as $cat) {
                if (!empty($cat['parent_name'])) {
                    $cat['name'] = $cat['parent_name'] . '::' . $cat['name'];
                }
                $ret[] = $cat;
            }

            $cache->set('genrelist_ordered', $ret, $site_config['expires']['genrelist']);
        }
    }

    return $ret;
}

/**
 * @param bool $force
 *
 * @return array|bool|mixed
 */
function create_moods($force = false)
{
    global $cache;

    $mood = $cache->get('moods');
    if ($mood === false || is_null($mood) || $force === true) {
        $res_moods = sql_query('SELECT * FROM moods ORDER BY id ASC') or sqlerr(__FILE__, __LINE__);
        $mood = [];
        if (mysqli_num_rows($res_moods)) {
            while ($rmood = mysqli_fetch_assoc($res_moods)) {
                $mood['image'][$rmood['id']] = $rmood['image'];
                $mood['name'][$rmood['id']] = $rmood['name'];
            }
        }
        $cache->set('moods', $mood, 86400);
    }

    return $mood;
}

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
 * @param $size
 *
 * @return string
 */
function mksize($size)
{
    for ($i = 0; ($size / 1024) > 0.9; $i++, $size /= 1024) {
    }

    return round($size, [0, 0, 1, 2, 2, 3, 3, 4, 4][$i]) . ' ' . ['B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'][$i];
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
    $email = filter_var($email, FILTER_SANITIZE_EMAIL);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return null;
    }

    return $email;
}

/**
 * @param $x
 *
 * @return int|string
 */
function sqlesc($x)
{
    global $mysqli;

    if (is_integer($x)) {
        return (int) $x;
    }

    return sprintf('\'%s\'', mysqli_real_escape_string($mysqli, $x));
}

/**
 * @param $x
 *
 * @return int|string
 */
function sqlesc_noquote($x)
{
    global $mysqli;

    if (is_integer($x)) {
        return (int) $x;
    }

    return mysqli_real_escape_string($mysqli, $x);
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
    global $mysqli;

    if ($suffix) {
        $suffix = " $suffix";
    }
    ($r = sql_query("SELECT COUNT(*) FROM $table$suffix")) or die(((is_object($mysqli)) ? mysqli_error($mysqli) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
    ($a = mysqli_fetch_row($r)) or die(((is_object($mysqli)) ? mysqli_error($mysqli) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));

    return (int) $a[0];
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
 *
 * @throws Exception
 */
function stderr($heading, $text, $class = 'bottom20')
{
    echo stdhead() . stdmsg($heading, $text, $class) . stdfoot();
    die();
}

/**
 * @param string $file
 * @param string $line
 */
function sqlerr($file = '', $line = '')
{
    global $site_config, $CURUSER, $mysqli;

    $the_error = ((is_object($mysqli)) ? mysqli_error($mysqli) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false));
    $the_error_no = ((is_object($mysqli)) ? mysqli_errno($mysqli) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false));
    if (SQL_DEBUG == 0) {
        die();
    } elseif ($site_config['sql_error_log'] && SQL_DEBUG == 1) {
        $_error_string = "\n===================================================";
        $_error_string .= "\n Date: " . date('r');
        $_error_string .= "\n Error Number: " . $the_error_no;
        $_error_string .= "\n Error: " . $the_error;
        $_error_string .= "\n IP Address: " . getip();
        $_error_string .= "\n in file " . $file . ' on line ' . $line;
        $_error_string .= "\n URL:" . !empty($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : 'Empty';
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
        'day' => $day,
        'month' => $month,
        'year' => $year,
        'hour' => $hour,
        'minute' => $min,
        'ampm' => $ampm,
    ];
}

/**
 * @return int
 */
function get_time_offset()
{
    global $CURUSER, $site_config;

    $r = !empty($CURUSER['time_offset']) ? $CURUSER['time_offset'] * 3600 : $site_config['time_offset'] * 3600;
    if ($site_config['time_adjust']) {
        $r += $site_config['time_adjust'] * 60;
    }
    if (isset($CURUSER['dst_in_use']) && $CURUSER['dst_in_use']) {
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
function get_date(int $date, $method, $norelative = 0, $full_relative = 0, $calc = false)
{
    global $site_config;

    static $offset_set = 0;
    static $today_time = 0;
    static $yesterday_time = 0;
    static $tomorrow_time = 0;
    $time_options = [
        'JOINED' => $site_config['time_joined'],
        'SHORT' => $site_config['time_short'],
        'LONG' => $site_config['time_long'],
        'TINY' => $site_config['time_tiny'] ? $site_config['time_tiny'] : 'j M Y - G:i',
        'WITH_SEC' => $site_config['time_with_seconds'],
        'WITHOUT_SEC' => $site_config['time_without_seconds'],
        'DATE' => $site_config['time_date'] ? $site_config['time_date'] : 'j M Y',
        'FORM' => $site_config['time_form'] ? $site_config['time_form'] : 'Y-m-d',
        'TIME' => $site_config['time_time'],
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
            $tomorrow_time = gmdate('d,m,Y', ((TIME_NOW + 86400) + $GLOBALS['offset']));
        }
        $offset_set = 1;
    }
    if ($site_config['time_use_relative'] == 3) {
        $full_relative = 1;
    }
    if ($full_relative && $norelative != 1 && !$calc) {
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
    } elseif ($site_config['time_use_relative'] && $norelative != 1 && !$calc) {
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
            if ($method === 'WITHOUT_SEC') {
                return str_replace('{--}', 'Today', gmdate($site_config['time_use_relative_format_without_seconds'], ($date + $GLOBALS['offset'])));
            }

            return str_replace('{--}', 'Today', gmdate($site_config['time_use_relative_format'], ($date + $GLOBALS['offset'])));
        } elseif ($this_time == $yesterday_time) {
            if ($method === 'WITHOUT_SEC') {
                return str_replace('{--}', 'Yesterday', gmdate($site_config['time_use_relative_format_without_seconds'], ($date + $GLOBALS['offset'])));
            }

            return str_replace('{--}', 'Yesterday', gmdate($site_config['time_use_relative_format'], ($date + $GLOBALS['offset'])));
        } elseif ($this_time == $tomorrow_time) {
            if ($method === 'WITHOUT_SEC') {
                return str_replace('{--}', 'Tomorrow', gmdate($site_config['time_use_relative_format_without_seconds'], ($date + $GLOBALS['offset'])));
            }

            return str_replace('{--}', 'Tomorrow', gmdate($site_config['time_use_relative_format'], ($date + $GLOBALS['offset'])));
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
        $secs = $date - ($mins * 60);
        $text = [];
        if ($years > 0) {
            $text[] = number_format($years) . ' year' . plural($years);
        }
        if ($days > 0) {
            $text[] = number_format($days) . ' day' . plural($days);
        }
        if ($hours > 0) {
            $text[] = number_format($hours) . ' hour' . plural($hours);
        }
        if ($mins > 0) {
            $text[] = number_format($mins) . ' min' . plural($mins);
        }
        if ($secs > 0) {
            $text[] = number_format($secs) . ' sec' . plural($secs);
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
 * @return string|null
 */
function ratingpic($num)
{
    global $site_config;

    $r = round($num * 2) / 2;
    if ($r < 1 || $r > 5) {
        return null;
    }

    return "<img src='{$site_config['pic_baseurl']}/{$r}.gif' alt='Rating: $num / 5' title='Users have rated this: $num / 5' class='tooltipper'>";
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

    $site_lang = get_language();
    $lang = [];
    if (file_exists(LANG_DIR . "{$site_lang}/lang_{$file}.php")) {
        include_once LANG_DIR . "{$site_lang}/lang_{$file}.php";
    } elseif (file_exists(LANG_DIR . "1/lang_{$file}.php")) {
        include_once LANG_DIR . "1/lang_{$file}.php";
    } else {
        stderr('System Error', "Can't find language file specified(user or site)");
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
        'posts' => 'posts.userid',
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
    global $query_stat, $queries, $mysqli;
    dbconn();

    if (SQL_DEBUG) {
        $query_start_time = microtime(true);

        mysqli_set_charset($mysqli, 'utf8');
        $result = mysqli_query($mysqli, $query);
        $query_end_time = microtime(true);
        $query_stat[] = [
            'seconds' => number_format($query_end_time - $query_start_time, 6),
            'query' => formatQuery($query),
        ];
        $queries = count($query_stat);
    } else {
        $result = mysqli_query($mysqli, $query);
    }

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

    return "<img src='{$site_config['pic_baseurl']}{$img}.gif' alt='percent'>";
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
    global $referer_stuffs;

    $http_referer = getenv('HTTP_REFERER');
    if (!empty($_SERVER['HTTP_HOST']) && strstr($http_referer, $_SERVER['HTTP_HOST']) === false && $http_referer != '') {
        $ip = getip(true);
        $http_agent = $_SERVER['HTTP_USER_AGENT'];
        $http_page = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'];
        if (!empty($_SERVER['QUERY_STRING'])) {
            $http_page .= '?' . $_SERVER['QUERY_STRING'];
        }
        $values = [
            'browser' => $http_agent,
            'ip' => inet_pton($ip),
            'referer' => $http_referer,
            'page' => $http_page,
            'date' => TIME_NOW,
        ];
        $referer_stuffs->insert($values);
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
    global $mysqli;

    $r = @sql_query($query);
    $result = [];
    if ($err = ((is_object($mysqli)) ? mysqli_error($mysqli) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false))) {
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
 * @param $text
 *
 * @return string
 */
function replace_unicode_strings($text)
{
    $text = str_replace([
        '“',
        '”',
    ], '"', $text);
    $text = str_replace([
        '&quot;',
        '&lsquo;',
        '‘',
        '&rsquo;',
        '’',
    ], "'", $text);
    $text = str_replace([
        '&ldquo;',
        '“',
        '&rdquo;',
        '”',
    ], '"', $text);
    $text = str_replace([
        '&#8212;',
        '–',
    ], '-', $text);
    $text = str_replace('&amp;', '&#38;', $text);

    return html_entity_decode(htmlentities($text, ENT_QUOTES));
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

/**
 * @throws Exception
 * @throws \MatthiasMullie\Scrapbook\Exception\Exception
 * @throws \MatthiasMullie\Scrapbook\Exception\ServerUnhealthy
 * @throws \MatthiasMullie\Scrapbook\Exception\UnbegunTransaction
 */
function check_user_status()
{
    global $session;

    userlogin();
    if (!$session->validateToken($session->get('auth'), 'auth')) {
        $session->destroy();
    }
    referer();
    parked();
    suspended();
    insert_update_ip();
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
    return strtolower(sprintf('#%02X%02X%02X', $r, $g, $b));
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
        $query = 'SELECT id FROM users WHERE id = ' . sqlesc($user_id);
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
 * @return bool|mixed
 *
 * @throws \Envms\FluentPDO\Exception
 */
function get_poll()
{
    global $CURUSER, $site_config, $fluent, $cache;

    $poll_data = $cache->get('poll_data_' . $CURUSER['id']);
    if ($poll_data === false || is_null($poll_data)) {
        $poll_data = $fluent->from('polls')
                            ->orderBy('start_date DESC')
                            ->limit(1)
                            ->fetch();

        if (!empty($poll_data)) {
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
            $poll_data['time'] = TIME_NOW;

            $cache->set('poll_data_' . $CURUSER['id'], $poll_data, $site_config['expires']['poll_data']);
        }
    }

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
 * @param $array
 * @param $cols
 *
 * @return array
 */
function array_msort(array $array, array $cols)
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
 * @return array|bool|mixed
 */
function countries()
{
    global $site_config, $cache;

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
 * @param      $link
 * @param      $text
 * @param bool $title
 *
 * @return string
 */
function bubble($link, $text, $title = false)
{
    $id = uniqid('id_');
    $bubble = "
        <span class='dt-tooltipper-large size_5 has-text-primary' data-tooltip-content='#{$id}'>
            $link
        </span>
        <div class='tooltip_templates'>
            <span id='$id'>";
    if ($title) {
        $bubble .= "
                <div class='size_6 has-text-green has-text-centered bottom20'>
                    $title
                </div>";
    }
    $bubble .= "
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
 * @param $int
 *
 * @return string
 */
function plural(int $int)
{
    if ($int !== 1) {
        return 's';
    }

    return false;
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

    if ($username === '') {
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

    if (!preg_match("/^[\p{L}\p{N}]+$/u", urldecode($username))) {
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
 *
 * @throws Exception
 */
function Christmas($celebrate = true)
{
    $upperBound = new DateTime('Dec 26');
    $lowerBound = new DateTime('Dec 1');
    $checkDate = new DateTime(date('M d', strtotime('Today')));

    if ($celebrate && $checkDate >= $lowerBound && $checkDate <= $upperBound) {
        return true;
    }

    return false;
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
 * @param      $url
 * @param bool $image
 * @param null $width
 * @param null $height
 * @param null $quality
 *
 * @return string
 *
 * @throws \Spatie\Image\Exceptions\InvalidManipulation
 */
function url_proxy($url, $image = false, $width = null, $height = null, $quality = null)
{
    global $site_config;

    if (empty($url) || preg_match('#' . preg_quote($site_config['domain']) . '#', $url) || preg_match('#' . preg_quote($site_config['pic_baseurl']) . '#', $url)) {
        return $url;
    }
    if (!$image) {
        return (!empty($site_config['anonymizer_url']) ? $site_config['anonymizer_url'] : '') . $url;
    }
    if ($site_config['image_proxy']) {
        $image_proxy = new DarkAlchemy\Pu239\ImageProxy();
        $image = $image_proxy->get_image($url, $width, $height, $quality);

        if (!$image) {
            return $site_config['pic_baseurl'] . 'noposter.png';
        } else {
            return $site_config['pic_baseurl'] . 'proxy/' . $image;
        }
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
        $name = trim(str_replace([
            '.',
            '_',
            '-',
        ], ' ', $tmp[1]));
    } else {
        $name = trim(str_replace([
            '.',
            '_',
            '-',
        ], ' ', $name));
    }

    return preg_replace('/\s+/', ' ', $name);
}

/**
 * @param string $name
 *
 * @return bool|mixed|null
 *
 * @throws \Envms\FluentPDO\Exception
 */
function get_show_id(string $name)
{
    global $fluent, $cache;

    if (empty($name)) {
        return null;
    }
    $name = get_show_name($name);
    $hash = hash('sha512', $name);
    $id_array = $cache->get('tvshow_ids_' . $hash);
    if ($id_array === false || is_null($id_array)) {
        $items = $fluent->from('tvmaze')
                        ->where('MATCH (name) AGAINST (? IN NATURAL LANGUAGE MODE)', $name)
                        ->fetchAll();
        if ($items) {
            $id_array = $items[0];
            foreach ($items as $item) {
                if (strtolower($item['name']) === strtolower($name)) {
                    $id_array = $item;
                }
            }
            $cache->set('tvshow_ids_' . $hash, $id_array, 0);
        }
    }

    if (!empty($id_array)) {
        return $id_array;
    }

    return false;
}

/**
 * @param string $imdbid
 *
 * @return bool|mixed|null
 *
 * @throws \Envms\FluentPDO\Exception
 */
function get_show_id_by_imdb(string $imdbid)
{
    global $fluent, $cache;

    if (empty($imdbid)) {
        return null;
    }
    $id_array = $cache->get('tvshow_ids_' . $imdbid);
    if ($id_array === false || is_null($id_array)) {
        $id_array = $fluent->from('tvmaze')
                           ->where('imdb_id = ?', $imdbid)
                           ->fetch();
        if ($id_array) {
            $cache->set('tvshow_ids_' . $imdbid, $id_array, 0);
        }
    }

    if (!empty($id_array)) {
        return $id_array;
    }

    return false;
}

/**
 * @param      $timestamp
 * @param bool $sec
 *
 * @return false|mixed|string
 */
function time24to12($timestamp, $sec = false)
{
    if ($sec) {
        return get_date($timestamp, 'WITH_SEC', 1, 1);
    }

    return get_date($timestamp, 'WITHOUT_SEC', 1, 1);
}

function GetDirectorySize($path, $human, $count)
{
    $bytestotal = $files = 0;
    $path = realpath($path);
    if ($path !== false && !empty($path) && is_dir($path)) {
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS)) as $object) {
            $bytestotal += $object->getSize();
            ++$files;
        }
    }

    if ($count) {
        if ($human) {
            return [
                mksize($bytestotal),
                $files,
            ];
        }

        return [
            $bytestotal,
            $files,
        ];
    }
    if ($human) {
        return mksize($bytestotal);
    }

    return $bytestotal;
}

/**
 * @param $query
 *
 * @return string|string[]|null
 */
function formatQuery($query)
{
    $query = preg_replace('/\b(WHERE|FROM|GROUP BY|HAVING|ORDER BY|LIMIT|OFFSET|UNION|ON DUPLICATE KEY UPDATE|VALUES|SET)\b/',
        "\n$0", $query);
    $query = preg_replace('/\b(INNER|OUTER|LEFT|RIGHT|FULL|CASE|WHEN|END|ELSE|AND)\b/', "\n\t$0", $query);
    $query = preg_replace("/\s+\n/", "\n", $query); // remove trailing spaces
    return $query;
}

/**
 * @return bool
 *
 * @throws \Envms\FluentPDO\Exception
 */
function insert_update_ip()
{
    global $CURUSER, $cache, $ip_stuffs;

    if (empty($CURUSER)) {
        return false;
    }
    $added = TIME_NOW;
    $values = [
        'ip' => getip(),
        'userid' => $CURUSER['id'],
        'type' => 'browse',
        'lastbrowse' => $added,
    ];
    $update = [
        'lastbrowse' => $added,
    ];
    $ip_stuffs->insert_update($values, $update, $CURUSER['id']);
}

/**
 * @param $url
 *
 * @return bool|string
 */
function fetch($url)
{
    global $site_config;

    $client = new GuzzleHttp\Client([
        'curl' => [
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        ],
        'synchronous' => true,
        'http_errors' => false,
        'headers' => [
            'User-Agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36',
        ],
        'verify' => false,
    ]);
    try {
        if ($res = $client->request('GET', $url)) {
            if ($res->getStatusCode() === 200) {
                return $res->getBody()
                           ->getContents();
            }
        } else {
            return false;
        }
    } catch (GuzzleHttp\Exception\GuzzleException $e) {
        return false;
    }

    return false;
}

/**
 * @param      $details
 * @param bool $portrait
 *
 * @return bool|mixed|string
 *
 * @throws \Envms\FluentPDO\Exception
 */
function get_body_image($details, $portrait = false)
{
    global $cache, $fluent, $torrent;

    $image = '';
    if ($details && !empty($torrent['imdb_id'])) {
        $images = $cache->get('backgrounds_' . $torrent['imdb_id']);
        if ($images === false || is_null($images)) {
            $images = $fluent->from('images')
                             ->select(null)
                             ->select('url')
                             ->where('type = "background"')
                             ->where('imdb_id = ?', $torrent['imdb_id'])
                             ->fetchAll();

            $cache->set('backgrounds_' . $torrent['imdb_id'], $images, 86400);
        }

        if (!empty($images)) {
            shuffle($images);
            $image = $images[0]['url'];
        }

        return $image;
    }

    $backgrounds = $cache->get('backgrounds_');
    if ($backgrounds === false || is_null($backgrounds)) {
        $results = $fluent->from('images')
                          ->select(null)
                          ->select('url')
                          ->where('type = ?', 'background');

        $backgrounds = [];
        foreach ($results as $background) {
            $backgrounds[] = $background['url'];
        }
        if (!empty($backgrounds)) {
            $cache->set('backgrounds_', $backgrounds, 86400);
        }
    }

    if (!empty($backgrounds)) {
        shuffle($backgrounds);
        $image = array_pop($backgrounds);
        if (count($backgrounds) <= 3) {
            $cache->delete('backgrounds_');
        } else {
            $cache->set('backgrounds_', $backgrounds, 86400);
        }

        return $image;
    }

    $cache->delete('backgrounds_');

    return false;
}

if (!file_exists(TEMPLATE_DIR . get_stylesheet() . DIRECTORY_SEPARATOR . 'files.php')) {
    dd('Error', 'Please run php bin/uglify.php to generate the required files');
}

require_once TEMPLATE_DIR . get_stylesheet() . DIRECTORY_SEPARATOR . 'files.php';
