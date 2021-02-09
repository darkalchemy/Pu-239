<?php

declare(strict_types = 1);

use Delight\Auth\Auth;
use Delight\Auth\AuthError;
use Delight\Auth\NotLoggedInException;
use DI\DependencyException;
use DI\NotFoundException;
use MatthiasMullie\Scrapbook\Exception\UnbegunTransaction;
use Pu239\Ban;
use Pu239\Cache;
use Pu239\Database;
use Pu239\ImageProxy;
use Pu239\IP;
use Pu239\Referrer;
use Pu239\Roles;
use Pu239\Session;
use Pu239\Settings;
use Pu239\Sitelog;
use Pu239\User;
use Rakit\Validation\Validator;
use Spatie\Image\Exceptions\InvalidManipulation;

$starttime = microtime(true);
require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'define.php';
require_once INCL_DIR . 'app.php';
global $container;
$env = $container->get('env');
$settings = $container->get(Settings::class);
$site_config = $settings->get_settings();
ini_set('error_log', PHPERROR_LOGS_DIR . 'error.log');

require_once INCL_DIR . 'function_common.php';
require_once INCL_DIR . 'database.php';
require_once INCL_DIR . 'function_users.php';
require_once CLASS_DIR . 'class_blocks_index.php';
require_once CLASS_DIR . 'class_blocks_stdhead.php';
require_once CLASS_DIR . 'class_blocks_userdetails.php';
require_once CLASS_DIR . 'class_blocks_apis.php';
require_once CACHE_DIR . 'block_settings_cache.php';
require_once INCL_DIR . 'function_translate.php';

if (!PRODUCTION) {
    $file = ROOT_DIR . 'package.json';
    $site_config['sourcecode']['version'] = 'Pu-239';
    if (file_exists($file)) {
        $contents = json_decode(file_get_contents($file), true);
        $pu239_version = new SebastianBergmann\Version($contents['version'], ROOT_DIR);
        $site_config['sourcecode']['version'] = $pu239_version->getVersion();
    }
}
$cache = $container->get(Cache::class);
$cores = $cache->get('cores_');
if (!$cores) {
    $cores = `grep -c processor /proc/cpuinfo`;
    $cores = empty($cores) ? 1 : (int) $cores;
    $cache->set('cores_', $cores, 0);
}

$load = sys_getloadavg();
if ($load[0] > $cores * 2) {
    die("Load is too high. Don't continuously refresh, or you will just make the problem last longer");
}

if (preg_match('/(?:\< *(?:java|script)|script\:|\+document\.)/i', serialize(array_merge($_SERVER, $_GET, $_POST, $_COOKIE)))) {
    die('Forbidden');
}

/**
 * @param string $txt
 * @param bool   $strip
 *
 * @return mixed|string|null
 */
function htmlsafechars(string $txt, bool $strip = true)
{
    $txt = trim($txt);
    $txt = htmlentities($txt, ENT_QUOTES, 'UTF-8');
    $txt = $strip ? filter_var($txt, FILTER_SANITIZE_STRING) : filter_var($txt, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    return $txt;
}

/**
 *
 * @param int $user_id
 *
 * @throws DependencyException
 * @throws NotFoundException
 * @throws \Envms\FluentPDO\Exception
 *
 * @return string
 */
function getip(int $user_id): string
{
    global $site_config, $container, $cache;

    if (!$site_config['site']['ip_logging']) {
        return '127.0.0.1';
    }
    $auth = $container->get(Auth::class);
    $userid = $auth->getUserId();
    $ips_class = $container->get(IP::class);
    if ($user_id === 0 || $user_id === $userid) {
        $ip = $auth->getIpAddress();
    } else {
        $ip = $ips_class->get_current($user_id);
    }

    if ($userid) {
        $user_class = $container->get(User::class);
        $user = $user_class->getUserFromId($userid);
        $no_log_ip = isset($user) && $user['perms'] & PERMS_NO_IP;
    }
    if (!validip($ip) || $no_log_ip) {
        return '127.0.0.1';
    }

    return $ip;
}

/**
 * @throws DependencyException
 * @throws NotFoundException
 * @throws \Envms\FluentPDO\Exception
 *
 * @return mixed
 *
 *
 */
function get_stylesheet()
{
    global $container, $site_config;

    $auth = $container->get(Auth::class);
    $userid = (int) $auth->getUserId();
    $user = [];
    if (!empty($userid)) {
        $users_class = $container->get(User::class);
        $user = $users_class->getUserFromId($userid);
        if (empty($user)) {
            return $site_config['site']['stylesheet'];
        }
    }

    $style = isset($user['stylesheet']) ? $user['stylesheet'] : $site_config['site']['stylesheet'];

    $cache = $container->get(Cache::class);
    $class_config = $cache->get('class_config_' . $style);
    foreach ($class_config as $arr) {
        if ($arr['name'] !== 'UC_STAFF' && $arr['name'] !== 'UC_MIN' && $arr['name'] !== 'UC_MAX') {
            $site_config['class_realnames'][$arr['value']] = str_replace('UC_', '', $arr['name']);
            $site_config['class_names'][$arr['value']] = $arr['classname'];
            $site_config['class_colors'][$arr['value']] = $arr['classcolor'];
            $site_config['class_images'][$arr['value']] = $site_config['paths']['images_baseurl'] . "class/{$arr['classpic']}";
        }
    }

    return $style;
}

/**
 * @return mixed
 */
function get_category_icons()
{
    global $CURUSER, $site_config;

    return isset($CURUSER['categorie_icon']) ? $CURUSER['categorie_icon'] : $site_config['site']['cat_icons'];
}

/**
 * @throws NotLoggedInException
 * @throws UnbegunTransaction
 * @throws \Envms\FluentPDO\Exception
 * @throws AuthError
 * @throws DependencyException
 * @throws NotFoundException
 *
 * @return mixed
 *
 *
 */
function get_language()
{
    global $container, $site_config;

    $auth = $container->get(Auth::class);
    if ($auth->isLoggedIn()) {
        $user = check_user_status();

        return $user['language'];
    }

    return $site_config['language']['site'];
}

function get_template()
{
    global $CURUSER, $site_config;

    if (!empty($CURUSER)) {
        if (file_exists(TEMPLATE_DIR . "{$CURUSER['stylesheet']}/template.php")) {
            require_once TEMPLATE_DIR . "{$CURUSER['stylesheet']}/template.php";
        } else {
            if (isset($site_config)) {
                if (file_exists(TEMPLATE_DIR . "{$site_config['site']['stylesheet']}/template.php")) {
                    require_once TEMPLATE_DIR . "{$site_config['site']['stylesheet']}/template.php";
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
        if (file_exists(TEMPLATE_DIR . "{$site_config['site']['stylesheet']}/template.php")) {
            require_once TEMPLATE_DIR . "{$site_config['site']['stylesheet']}/template.php";
        } else {
            echo 'Sorry, Templates do not seem to be working properly and missing some code. Please report this to the programmers/owners.';
        }
    }
}

/**
 *
 * @param int    $userid
 * @param string $key
 * @param bool   $clear
 *
 * @throws DependencyException
 * @throws NotFoundException
 * @throws \Envms\FluentPDO\Exception
 *
 * @return array|bool|mixed
 *
 *
 */
function make_freeslots(int $userid, string $key, bool $clear)
{
    global $container;

    $cache = $container->get(Cache::class);
    if ($clear) {
        $cache->delete($key . $userid);
    }
    $slot = $cache->get($key . $userid);
    if ($slot === false || is_null($slot)) {
        $fluent = $container->get(Database::class);
        $slot = $fluent->from('freeslots')
            ->where('userid = ?', $userid)
            ->fetchAll();

        $cache->set($key . $userid, $slot, 86400 * 7);
    }

    return $slot;
}

/**
 * @param $x
 *
 * @return string
 */
function unesc($x)
{
    return stripslashes($x);
}

/**
 * @param $bytes
 * @param int    $decimals
 * @param string $system
 *
 * @return string
 */
function mksize($bytes, int $decimals = 2, string $system = 'metric')
{
    if (empty($bytes)) {
        return '0B';
    }
    $mod = ($system === 'binary') ? 1024 : 1000;

    $units = [
        'binary' => [
            'B',
            'KiB',
            'MiB',
            'GiB',
            'TiB',
            'PiB',
            'EiB',
            'ZiB',
            'YiB',
        ],
        'metric' => [
            'B',
            'kB',
            'MB',
            'GB',
            'TB',
            'PB',
            'EB',
            'ZB',
            'YB',
        ],
    ];

    $factor = floor((strlen((string) $bytes) - 1) / 3);

    return sprintf("%.{$decimals}f%s", $bytes / pow($mod, $factor), $units[$system][$factor]);
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
 * @param             $heading
 * @param             $text
 * @param string|null $outer_class
 * @param string|null $inner_class
 * @param array       $breadcrumbs
 *
 * @throws AuthError
 * @throws DependencyException
 * @throws InvalidManipulation
 * @throws NotFoundException
 * @throws NotLoggedInException
 * @throws UnbegunTransaction
 * @throws \Envms\FluentPDO\Exception
 */
function stderr($heading, $text, ?string $outer_class = null, ?string $inner_class = null, array $breadcrumbs = [])
{
    $page = isset($_SERVER['PHP_SELF']) ? $_SERVER['PHP_SELF'] : '';
    $self = isset($_SERVER['PHP_SELF']) ? ucfirst(str_replace([
        '/',
        '.php',
    ], '', $_SERVER['PHP_SELF'])) : '';
    $title = !empty($heading) ? $heading : _('Error');
    if (empty($breadcrumbs)) {
        $breadcrumbs = [
            "<a href='$page'>$self</a>",
            "<a href='{$_SERVER['PHP_SELF']}'>$title</a>",
        ];
    }
    echo stdhead($title, [], 'page_wrapper', $breadcrumbs) . stdmsg($heading, $text, $outer_class, $inner_class) . stdfoot();
    die();
}

/**
 * @param $text
 *
 * @throws DependencyException
 * @throws NotFoundException
 * @throws \Envms\FluentPDO\Exception
 */
function write_log($text)
{
    global $container;

    $sitelog = $container->get(Sitelog::class);
    $values = [
        'added' => TIME_NOW,
        'txt' => $text,
    ];
    $sitelog->insert($values);
}

/**
 * @throws NotFoundException
 * @throws DependencyException
 *
 * @return int
 *
 *
 */
function get_userid()
{
    global $container;

    $auth = $container->get(Auth::class);
    $userid = $auth->getUserId();
    if ($userid) {
        return $userid;
    }

    return 0;
}

/**
 * @throws NotFoundException
 * @throws \Envms\FluentPDO\Exception
 * @throws DependencyException
 *
 * @return float|int
 *
 *
 */
function get_time_offset()
{
    global $container, $site_config;

    $user = [];
    $userid = get_userid();
    if ($userid) {
        $user_class = $container->get(User::class);
        $user = $user_class->getUserFromId($userid);
    }
    $r = isset($user['time_offset']) ? $user['time_offset'] * 3600 : $site_config['time']['offset'] * 3600;
    if ($site_config['time']['adjust']) {
        $r += $site_config['time']['adjust'] * 60;
    }
    if (isset($user['dst_in_use']) && $user['dst_in_use']) {
        $r += 3600;
    }

    return $r;
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

    $num = number_format($num, 1);
    return "<img src='{$site_config['paths']['images_baseurl']}{$r}.gif' alt='Rating: $num / 5' title='Users have rated this: $num / 5' class='tooltipper'>";
}

/**
 * @param $txt
 * @param int $len
 *
 * @return string
 */
function CutName(string $txt, int $len = 40)
{
    return strlen($txt) > $len ? substr($txt, 0, $len - 4) . '...' : $txt;
}

/**
 * @param $table
 *
 * @throws Exception
 */
function flood_limit($table)
{
    global $container, $site_config, $CURUSER;

    $session = $container->get(Session::class);
    if (!file_exists($site_config['paths']['flood_file']) || !is_array($max = json_decode(file_get_contents($site_config['paths']['flood_file'])))) {
        return;
    }
    if (!isset($max[$CURUSER['class']])) {
        return;
    }
    $last_post = $session->get($table);
    if (empty($last_post)) {
        $session->set($table, [
            TIME_NOW,
            1,
        ]);

        return;
    }

    if ($last_post[1] > $max[$CURUSER['class']] && TIME_NOW - $last_post[0] < $site_config['flood']['time']) {
        stderr(_('Error'), _fe('Anti-Flood limit in effect - you need to wait - {0}', mkprettytime($site_config['flood']['time'] - (TIME_NOW - $last_post[0]))));
    }

    $count = $last_post[1] + 1;
    $floodtime = $last_post[0];
    if ($site_config['flood']['time'] < $last_post[0] - TIME_NOW) {
        $count = 1;
        $floodtime = TIME_NOW;
    }
    $session->set($table, [
        $floodtime,
        $count,
    ]);
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

    return "<img src='{$site_config['paths']['images_baseurl']}{$img}.gif' alt='percent'>";
}

/**
 * @throws DependencyException
 * @throws NotFoundException
 * @throws \Envms\FluentPDO\Exception
 */
function referer()
{
    global $container;

    $referrer_class = $container->get(Referrer::class);
    $http_referer = !empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
    if (filter_var($http_referer, FILTER_VALIDATE_URL)) {
        if (!empty($_SERVER['HTTP_HOST']) && !empty($http_referer) && strstr($http_referer, $_SERVER['HTTP_HOST']) === false) {
            $http_agent = $_SERVER['HTTP_USER_AGENT'];
            $http_page = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'];
            if (!empty($_SERVER['QUERY_STRING'])) {
                $http_page .= '?' . $_SERVER['QUERY_STRING'];
            }
            $values = [
                'browser' => $http_agent,
                'referer' => $http_referer,
                'page' => $http_page,
                'date' => TIME_NOW,
            ];
            $referrer_class->insert($values);
        }
    }
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

/**
 * @param $user
 *
 * @throws DependencyException
 * @throws NotFoundException
 */
function parked($user)
{
    global $container, $site_config;

    if ($user['status'] === 1) {
        $session = $container->get(Session::class);
        $session->set('is-warning', _('Your account is currently parked.'));
        if (!preg_match('/(usercp|takeeditcp)/', $_SERVER['REQUEST_URI'])) {
            header('Location: ' . $site_config['paths']['baseurl'] . '/usercp.php?action=security');
            die();
        }
    }
}

/**
 * @param $user
 *
 * @throws DependencyException
 * @throws NotFoundException
 */
function suspended($user)
{
    global $container, $site_config;

    if ($user['status'] === 5) {
        $session = $container->get(Session::class);
        $session->set('is-warning', _('Your account is currently suspended.'));
        if (!preg_match('/messages/', $_SERVER['REQUEST_URI'])) {
            header('Location: ' . $site_config['paths']['baseurl'] . '/messages.php');
            die();
        }
    }
}

/**
 * @param int $userid
 *
 * @throws AuthError
 * @throws DependencyException
 * @throws NotFoundException
 * @throws NotLoggedInException
 * @throws \Envms\FluentPDO\Exception
 */
function force_logout(int $userid)
{
    global $container;

    $cache = $container->get(Cache::class);
    $forced_logout = $cache->get('forced_logout_' . $userid);

    if ($forced_logout) {
        $user = $container->get(User::class);
        $user->delete_user_cache([$userid]);
        unset($GLOBALS['CURUSER']);
        $user->logout($userid, true);
    }
}

/**
 *
 * @param string $type
 *
 * @throws AuthError
 * @throws DependencyException
 * @throws NotFoundException
 * @throws NotLoggedInException
 * @throws UnbegunTransaction
 * @throws \Envms\FluentPDO\Exception
 *
 * @return bool|mixed|User
 *
 *
 */
function check_user_status(string $type = 'browse')
{
    global $container, $site_config;

    $auth = $container->get(Auth::class);
    if ($auth->isLoggedIn()) {
        $user_class = $container->get(User::class);
        $userid = $auth->id();
        $users_data = $user_class->getUserFromId($userid);
        if ($site_config['site']['ip_logging'] || !($users_data['perms'] & PERMS_NO_IP)) {
            insert_update_ip($type, $userid);
        }
        $session = $container->get(Session::class);
        if (!$site_config['site']['online']) {
            if ($users_data['class'] < UC_STAFF) {
                die('Site is down for maintenance, please check back again later... thanks<br>');
            } elseif ($users_data['class'] >= UC_STAFF) {
                $session->set('is-danger', 'Site is currently offline, only staff can access site.');
            }
        }
        if (!($users_data['perms'] & PERMS_BYPASS_BAN)) {
            $bans_class = $container->get(Ban::class);
            if ($bans_class->check_bans(getip($userid))) {
                $update = [
                    'status' => 2,
                ];
                $users_data['status'] = 2;
                $user_class->update($update, $userid);
                $cache = $container->get(Cache::class);
                $cache->set('forced_logout_' . $userid, TIME_NOW);
            }
        }
        force_logout($userid);
        $user_class->update_last_access($userid);
        $session->set('UserRole', $users_data['class']);
        $session->set('scheme', get_scheme());
        $GLOBALS['CURUSER'] = $users_data;
        get_template();
        referer();
        parked($users_data);
        suspended($users_data);
    }
    if ($type != 'login' && empty($users_data)) {
        $returnto = '';
        if (!empty($_SERVER['REQUEST_URI'])) {
            $returnto = '?returnto=' . urlencode($_SERVER['REQUEST_URI']);
        }
        header("Location: {$site_config['paths']['baseurl']}/login.php" . $returnto);
        die();
    }

    if (empty($users_data)) {
        return [
            'torrent_pass' => '',
        ];
    }

    return $users_data;
}

/**
 *
 * @param int         $userclass
 * @param int         $class
 * @param string|null $role
 *
 * @throws DependencyException
 * @throws NotFoundException
 *
 * @return bool
 *
 *
 */
function has_access(int $userclass, int $class, ?string $role)
{
    global $container;

    $auth = $container->get(Auth::class);
    if (!empty($role)) {
        if ($role === 'coder') {
            if ($userclass >= $class || $auth->hasRole(Roles::CODER)) {
                return true;
            }
        } elseif ($role === 'forum_mod') {
            if ($userclass >= $class || $auth->hasRole(Roles::FORUM_MOD)) {
                return true;
            }
        } elseif ($role === 'torrent_mod') {
            if ($userclass >= $class || $auth->hasRole(Roles::TORRENT_MOD)) {
                return true;
            }
        } elseif ($role === 'uploader') {
            if ($userclass >= $class && $auth->hasRole(Roles::UPLOADER)) {
                return true;
            }
        } elseif ($role === 'internal') {
            if ($userclass >= $class && $auth->hasRole(Roles::INTERNAL)) {
                return true;
            }
        } else {
            return false;
        }
    } elseif ($userclass >= $class) {
        return true;
    }

    return false;
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
 * @throws DependencyException
 * @throws NotFoundException
 * @throws \Envms\FluentPDO\Exception
 *
 * @return bool
 *
 *
 */
function user_exists($user_id)
{
    global $container;

    $users_class = $container->get(User::class);
    $user = $users_class->getUserFromId($user_id);
    if (!empty($user)) {
        return true;
    }

    return false;
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
            $colarr[$col]['_' . $k] = strtolower((string) $row[$col]);
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
 * @throws DependencyException
 * @throws NotFoundException
 * @throws \Envms\FluentPDO\Exception
 *
 * @return array|bool|mixed
 *
 *
 */
function countries()
{
    global $container, $site_config;

    $cache = $container->get(Cache::class);
    $countries = $cache->get('countries_arr_');
    if ($countries === false || is_null($countries)) {
        $fluent = $container->get(Database::class);
        $countries = $fluent->from('countries')
            ->select(null)
            ->select('id')
            ->select('name')
            ->select('flagpic')
            ->orderBy('name')
            ->fetchAll();

        $cache->set('countries_arr_', $countries, $site_config['expires']['user_flag']);
    }

    return $countries;
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
            <div id='$id'>";
    if ($title) {
        $bubble .= "
                <div class='size_6 has-text-success has-text-centered bottom20'>
                    $title
                </div>";
    }
    $bubble .= "
                $text
            </div>
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
 *
 * @param string $username
 * @param bool   $ajax
 * @param bool   $in_use
 *
 * @throws AuthError
 * @throws DependencyException
 * @throws InvalidManipulation
 * @throws NotFoundException
 * @throws NotLoggedInException
 * @throws UnbegunTransaction
 * @throws \Envms\FluentPDO\Exception
 *
 * @return bool|string
 *
 *
 */
function valid_username(string $username, bool $ajax = false, bool $in_use = false)
{
    global $container, $site_config;

    $validator = $container->get(Validator::class);
    $check = [
        'username' => $username,
    ];
    $validation = $validator->validate($check, [
        'username' => 'required|between:3,64',
    ]);
    if ($validation->fails()) {
        if ($ajax) {
            echo "<div class='has-text-danger bottom20'><i class='icon-thumbs-down icon' aria-hidden='true'></i>" . _('Username too long or too short') . '</div> 3 - 64 characters';
            die();
        } else {
            stderr(_('Error'), _('Username too long or too short'));
        }
    }
    if (!preg_match("/^[\p{L}\p{M}\p{N}]+$/u", urldecode($username))) {
        if ($ajax) {
            echo "<div class='has-text-danger'><i class='icon-thumbs-down icon' aria-hidden='true'></i>" . _('Invalid characters used.') . '</div>';
            die();
        }

        return false;
    }
    if (preg_match('/' . urldecode($username) . '/i', strtolower(implode('|', $site_config['site']['badwords'])))) {
        if ($ajax) {
            echo "<div class='has-text-danger bottom20'><i class='icon-thumbs-down icon' aria-hidden='true'></i>" . _('Username not allowed.') . '</div>';
            die();
        }

        return false;
    }
    if ($in_use) {
        $user = $container->get(User::class);
        if ($user->get_count_by_username(htmlsafechars($username))) {
            if ($ajax) {
                echo "<div class='has-text-danger tooltipper bottom20' title='" . _('Username is not Available') . "'><i class='icon-thumbs-down icon' aria-hidden='true'></i>" . _fe('Sorry... Username - {0} is already in use.', format_comment($_GET['wantusername'])) . '</div>';
                die();
            }

            return false;
        } else {
            if ($ajax) {
                echo "<div class='has-text-success tooltipper bottom20' title='" . _('Username is Available') . "'><i class='icon-thumbs-up icon' aria-hidden='true'></i><b>" . _('Username is Available') . '</b></div>';
                die();
            }
        }
    }

    return true;
}

/**
 * @param bool $celebrate
 *
 * @throws Exception
 *
 * @return bool
 *
 *
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

    $index = array_rand($site_config['anonymous']['names']);
    $anon = $site_config['anonymous']['names'][$index];

    return $anon;
}

/**
 *
 * @param string   $url
 * @param bool     $image
 * @param int|null $width
 * @param int|null $height
 * @param int|null $quality
 *
 * @throws DependencyException
 * @throws InvalidManipulation
 * @throws NotFoundException
 * @throws \Envms\FluentPDO\Exception
 *
 * @return string
 *
 *
 */
function url_proxy(string $url, bool $image = false, ?int $width = null, ?int $height = null, ?int $quality = null)
{
    global $container, $site_config;

    if (empty($url)) {
        return $url;
    }
    if ((stripos($url, $site_config['session']['domain']) !== false || stripos($url, $site_config['paths']['images_baseurl']) !== false || stripos($url, $site_config['paths']['baseurl']) !== false) && stripos($url, 'logo') === false) {
        if (stripos($url, 'img.php') === false) {
            return $url;
        }
    }
    if (!$image) {
        return (!empty($site_config['site']['anonymizer_url']) ? $site_config['site']['anonymizer_url'] : '') . $url;
    }
    if ($site_config['site']['image_proxy']) {
        $image_proxy = $container->get(ImageProxy::class);
        $image = $image_proxy->get_image($url, $width, $height, $quality);

        if (!empty($image)) {
            return $site_config['paths']['images_baseurl'] . 'proxy/' . $image;
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
 *
 * @param string $name
 *
 * @throws DependencyException
 * @throws NotFoundException
 * @throws \Envms\FluentPDO\Exception
 *
 * @return bool|mixed|null
 *
 *
 */
function get_show_id(string $name)
{
    global $container;

    $cache = $container->get(Cache::class);
    if (empty($name)) {
        return null;
    }
    $name = get_show_name($name);
    $hash = hash('sha256', $name);
    $id_array = $cache->get('tvshow_ids_' . $hash);
    if ($id_array === false || is_null($id_array)) {
        $fluent = $container->get(Database::class);
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
 *
 * @param string $imdbid
 *
 * @throws DependencyException
 * @throws NotFoundException
 * @throws \Envms\FluentPDO\Exception
 *
 * @return bool|mixed|null
 *
 *
 */
function get_show_id_by_imdb(string $imdbid)
{
    global $container;

    $cache = $container->get(Cache::class);
    if (empty($imdbid)) {
        return null;
    }
    $id_array = $cache->get('tvshow_ids_' . $imdbid);
    if ($id_array === false || is_null($id_array)) {
        $fluent = $container->get(Database::class);
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
 * @throws NotFoundException
 * @throws \Envms\FluentPDO\Exception
 * @throws DependencyException
 *
 * @return false|mixed|string
 *
 *
 */
function time24to12($timestamp, $sec = false)
{
    if ($sec) {
        return get_date((int) $timestamp, 'WITH_SEC', 1, 0);
    }

    return get_date((int) $timestamp, 'WITHOUT_SEC', 1, 0);
}

/**
 * @param $path
 * @param $human
 * @param $count
 *
 * @return array|int|string
 */
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
    $query = preg_replace('/\b(WHERE|FROM|GROUP BY|HAVING|ORDER BY|LIMIT|OFFSET|UNION|ON DUPLICATE KEY UPDATE|VALUES|SET)\b/i', "\n$0", $query);
    $query = preg_replace('/\b(INNER|OUTER|LEFT|RIGHT|FULL|CASE|WHEN|END|ELSE|AND)\b/i', "\n\t$0", $query);
    $query = preg_replace("/\s+\n/", "\n", $query); // remove trailing spaces
    return $query;
}

/**
 *
 * @param string $type
 * @param int    $userid
 *
 * @throws \Envms\FluentPDO\Exception
 * @throws DependencyException
 * @throws NotFoundException
 *
 * @return bool
 *
 *
 */
function insert_update_ip(string $type, int $userid)
{
    global $container;
    $ips_class = $container->get(IP::class);
    $ips_class->insert($userid, $type, getip($userid));

    return true;
}

/**
 *
 * @param string    $url
 * @param bool|null $fresh
 * @param bool|null $async
 *
 * @throws \Envms\FluentPDO\Exception
 * @throws DependencyException
 * @throws NotFoundException
 *
 * @return bool|mixed|string
 *
 *
 */
function fetch(string $url, ?bool $fresh = true, ?bool $async = false)
{
    global $container;

    $expires = 86400;
    $cache = $container->get(Cache::class);
    $key = hash('sha256', $url);
    $file = URL_CACHE_DIR . $key . '.cache';
    $gzip = $file . '.gz';
    if (!$fresh) {
        $result = $cache->get($key);
        if (empty($result) && file_exists($gzip)) {
            if (filemtime($gzip) <= (time() - $expires)) {
                unlink($gzip);
            } else {
                $result = file_get_contents('compress.zlib://' . $gzip);
            }
        }
        if (!empty($result)) {
            return $result;
        }
    }
    $client = new GuzzleHttp\Client([
        'curl' => [
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        ],
        'synchronous' => $async,
        'http_errors' => false,
        'headers' => [
            'User-Agent' => get_random_useragent(),
        ],
        'verify' => false,
    ]);
    try {
        if ($res = $client->request('GET', $url)) {
            if ($res->getStatusCode() === 200) {
                $contents = $res->getBody()->getContents();
                if (!$fresh) {
                    $cache->set($key, $contents, $expires);
                    file_put_contents($file, $contents);
                    if (gzCompressFile($file)) {
                        unlink($file);
                    }
                }

                return $contents;
            }
        }
    } catch (GuzzleHttp\Exception\GuzzleException $e) {
    }
    if (!$fresh) {
        $cache->set($key, 'No Results', $expires);
    }

    return false;
}

/**
 * @param $source
 * @param int $level
 *
 * @return false|string
 */
function gzCompressFile($source, $level = 9)
{
    $dest = $source . '.gz';
    $mode = 'wb' . $level;
    $error = false;
    if ($fp_out = gzopen($dest, $mode)) {
        if ($fp_in = fopen($source, 'rb')) {
            while (!feof($fp_in)) {
                gzwrite($fp_out, fread($fp_in, 1024 * 512));
            }
            fclose($fp_in);
        } else {
            $error = true;
        }
        gzclose($fp_out);
    } else {
        $error = true;
    }
    if ($error) {
        return false;
    } else {
        return $dest;
    }
}

/**
 *
 * @param bool $details
 *
 * @throws \Envms\FluentPDO\Exception
 * @throws DependencyException
 * @throws NotFoundException
 *
 * @return mixed|string
 *
 *
 */
function get_body_image(bool $details)
{
    global $container, $imdb_id;

    $cache = $container->get(Cache::class);
    $fluent = $container->get(Database::class);
    $image = '';
    if ($details && !empty($imdb_id)) {
        $images = $cache->get('backgrounds_' . $imdb_id);
        if ($images === false || is_null($images)) {
            $images = $fluent->from('images')
                ->select(null)
                ->select('url')
                ->where('type = "background"')
                ->where('imdb_id = ?', $imdb_id)
                ->fetchAll();
            if (!empty($images)) {
                $cache->set('backgrounds_' . $imdb_id, $images, 86400);
            } else {
                $cache->set('backgrounds_' . $imdb_id, [], 3600);
            }
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
            ->where('type = "background"');

        $backgrounds = [];
        foreach ($results as $background) {
            $backgrounds[] = $background['url'];
        }
        if (!empty($backgrounds)) {
            $cache->set('backgrounds_', $backgrounds, 86400);
        } else {
            $cache->set('backgrounds_', [], 86400);
        }
    }

    $image = '';
    if (!empty($backgrounds)) {
        shuffle($backgrounds);
        $image = array_pop($backgrounds);
        if (count($backgrounds) <= 3) {
            $cache->delete('backgrounds_');
        } else {
            $cache->set('backgrounds_', $backgrounds, 86400);
        }
    }

    return $image;
}

/**
 * @throws DependencyException
 * @throws NotFoundException
 * @throws \Envms\FluentPDO\Exception
 *
 * @return bool|mixed
 *
 *
 */
function get_random_useragent()
{
    global $container, $site_config;

    $cache = $container->get(Cache::class);

    $browsers = $cache->get('browser_user_agents_');
    if ($browsers === false || is_null($browsers)) {
        $fluent = $container->get(Database::class);
        $results = $fluent->from('users')
            ->select(null)
            ->select('DISTINCT browser AS browser')
            ->where('browser IS NOT null')
            ->limit(100)
            ->fetchAll();
        $browsers = [];
        if (empty($results)) {
            $browsers = [
                0 => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/67.0.3396.99 Safari/537.36',
            ];
        } else {
            foreach ($results as $result) {
                preg_match('/Agent : (.*)/', $result['browser'], $match);
                if (!empty($match[1])) {
                    $browsers[] = $match[1];
                }
            }
        }
        $cache->set('browser_user_agents_', $browsers, $site_config['expires']['browser_user_agent']);
    }
    shuffle($browsers);

    return $browsers[0];
}

function clear_di_cache()
{
    if (file_exists(DI_CACHE_DIR)) {
        if (php_sapi_name() === 'cli') {
            passthru('sudo rm -r ' . DI_CACHE_DIR);
        } else {
            if (file_exists(DI_CACHE_DIR . 'CompiledContainer.php')) {
                unlink(DI_CACHE_DIR . 'CompiledContainer.php');
            }
            rmdir(DI_CACHE_DIR);
        }
    }
}

if (!file_exists(TEMPLATE_DIR . get_stylesheet() . DIRECTORY_SEPARATOR . 'files.php')) {
    die('Please run php bin/uglify.php to generate the required files');
}
require_once TEMPLATE_DIR . get_stylesheet() . DIRECTORY_SEPARATOR . 'files.php';
