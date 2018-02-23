<?php

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'user_functions.php';
require_once INCL_DIR . 'password_functions.php';
require_once CLASS_DIR . 'class_browser.php';
dbconn();
global $CURUSER, $site_config, $fluent, $cache, $session;

if (!$CURUSER) {
    get_template();
}
$lang = array_merge(load_language('global'), load_language('takelogin'));

function failedloginscheck()
{
    global $site_config;
    $ip          = getip();
    $res         = sql_query('SELECT SUM(attempts), ip FROM failedlogins WHERE ip = ' . ipToStorageFormat($ip)) or sqlerr(__FILE__, __LINE__);
    list($total) = mysqli_fetch_row($res);
    if ($total >= $site_config['failedlogins']) {
        sql_query("UPDATE failedlogins SET banned = 'yes' WHERE ip = " . ipToStorageFormat($ip)) or sqlerr(__FILE__, __LINE__);
        stderr('Login Locked!', 'You have <b>Exceeded</b> the allowed maximum login attempts without successful login, therefore your ip address <b>(' . htmlsafechars($ip) . ')</b> has been locked for 24 hours.');
    }
}

$user_id = '';
extract($_POST);
unset($_POST);
extract($_GET);
unset($_GET);
if (!empty($bot) && !empty($auth)) {
    $user_id = $fluent->from('users')
        ->select(null)
        ->select('id')
        ->where('class > ? AND username = ? AND auth = ? AND uploadpos = 1 AND suspended = "no"', UC_UPLOADER, $bot, $auth)
        ->fetch('id');
}
if (empty($user_id)) {
    if (empty($username)) {
        stderr('Error', "Username can't be blank");
    }
    if (empty($password)) {
        stderr('Error', "Password can't be blank");
    }
    if ($site_config['captcha_on'] && empty($captchaSelection)) {
        stderr('Error', 'Select a captcha image');
    }
    if (empty($submitme) || 'Login' != $submitme) {
        stderr('Error', 'You missed, you plonker!');
    }

    if ($site_config['captcha_on']) {
        if (empty($captchaSelection) || $session->get('simpleCaptchaAnswer') != $captchaSelection) {
            $url = 'login.php';
            if (!empty($_SERVER['HTTP_REFERER'])) {
                $url = htmlsafechars($_SERVER['HTTP_REFERER']);
            }

            header("Location: $url");
            die();
        }
    }
}

/**
 * @param string $text
 */
function bark($text = 'Username or password incorrect')
{
    global $lang, $site_config, $cache;

    $sha      = hash('sha256', $_SERVER['REMOTE_ADDR']);
    $dict_key = 'dictbreaker_' . $sha;
    $flood    = $cache->get($dict_key);
    if (false === $flood || is_null($flood)) {
        $cache->set($dict_key, 'flood_check', 20);
    } else {
        die('Minimum 8 seconds between login attempts :)');
    }
    stderr($lang['tlogin_failed'], $text);
}

failedloginscheck();
$row = $fluent->from('users')
    ->select(null)
    ->select('id')
    ->select('INET6_NTOA(ip) AS ip')
    ->select('passhash')
    ->select('perms')
    ->select('ssluse')
    ->select('enabled')
    ->select('status')
    ->where('username = ?', $username)
    ->fetch();

$userid     = $row['id'];
$ip_escaped = ipToStorageFormat(getip());
$ip         = getip();
$added      = TIME_NOW;
if (false === $row) {
    $fail = (@mysqli_fetch_row(sql_query("SELECT COUNT(id) from failedlogins where ip = $ip_escaped"))) or sqlerr(__FILE__, __LINE__);
    if (0 == $fail[0]) {
        sql_query("INSERT INTO failedlogins (ip, added, attempts) VALUES ($ip_escaped, $added, 1)") or sqlerr(__FILE__, __LINE__);
    } else {
        sql_query("UPDATE failedlogins SET attempts = attempts + 1 where ip = $ip_escaped") or sqlerr(__FILE__, __LINE__);
    }
    bark();
}

if (!password_verify($password, $row['passhash'])) {
    $fail = (@mysqli_fetch_row(sql_query("SELECT COUNT(id), ip from failedlogins where ip = $ip_escaped"))) or sqlerr(__FILE__, __LINE__);
    if (0 == $fail[0]) {
        sql_query("INSERT INTO failedlogins (ip, added, attempts) VALUES ($ip_escaped, $added, 1)") or sqlerr(__FILE__, __LINE__);
    } else {
        sql_query("UPDATE failedlogins SET attempts = attempts + 1 where ip=$ip_escaped") or sqlerr(__FILE__, __LINE__);
    }
    $subject = 'Failed login';
    $msg     = "[color=red]Security alert[/color]\n Account: ID=" . $userid . ' Somebody (probably you, ' . htmlsafechars($username) . ' !) tried to login but failed!' . "\nTheir [b]Ip Address [/b] was : " . htmlsafechars($ip) . "\n If this wasn't you please report this event to a {$site_config['site_name']} staff member\n - Thank you.\n";
    $sql     = 'INSERT INTO messages (sender, receiver, msg, subject, added) VALUES(0, ' . sqlesc($userid) . ', ' . sqlesc($msg) . ', ' . sqlesc($subject) . ", $added);";
    $res     = sql_query($sql) or sqlerr(__FILE__, __LINE__);
    $cache->increment('inbox_' . $userid);
    bark("<b>Error</b>: Username or password entry incorrect <br>Have you forgotten your password? <a href='{$site_config['baseurl']}/resetpw.php'><b>Recover</b></a> your password !");
}

if ('no' == $row['enabled']) {
    bark($lang['tlogin_disabled']);
}
if ('pending' == $row['status']) {
    if ($site_config['email_confirm']) {
        bark('You have not confirmed your amail address. Please use the link in the email that you should have received.');
    }
    bark('Your account has not been confirmed.');
}
sql_query("DELETE FROM failedlogins WHERE ip = $ip_escaped");
$row['perms'] = (int) $row['perms'];
$no_log_ip    = ($row['perms'] & bt_options::PERMS_NO_IP);
if ($no_log_ip) {
    $ip = '127.0.0.1';
}
$ip_escaped = ipToStorageFormat($ip);

if (!$no_log_ip) {
    $res = sql_query("SELECT * FROM ips WHERE ip = $ip_escaped AND userid = " . sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
    if (0 == mysqli_num_rows($res)) {
        sql_query('INSERT INTO ips (userid, ip, lastlogin, type) VALUES (' . sqlesc($userid) . ", $ip_escaped , $added, 'Login')") or sqlerr(__FILE__, __LINE__);
        $cache->delete('ip_history_' . $userid);
    } else {
        sql_query("UPDATE ips SET lastlogin=$added WHERE ip = $ip_escaped AND userid = " . sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
        $cache->delete('ip_history_' . $userid);
    }
}
if (isset($use_ssl) && 1 == $use_ssl && !isset($_SERVER['HTTPS'])) {
    $site_config['baseurl'] = str_replace('http', 'https', $site_config['baseurl']);
}

$ssl_value = (isset($perm_ssl) && 1 == $perm_ssl ? 'ssluse = 2' : 'ssluse = 1');
$ssluse    = (2 == $row['ssluse'] ? 2 : 1);
$ua        = getBrowser();
$browser   = 'Browser: ' . $ua['name'] . ' ' . $ua['version'] . '. Os: ' . $ua['platform'] . '. Agent : ' . $ua['userAgent'];

sql_query('UPDATE users SET browser = ' . sqlesc($browser) . ", $ssl_value, ip = $ip_escaped, last_access = " . TIME_NOW . ', last_login = ' . TIME_NOW . ' WHERE id = ' . sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
$cache->update_row('user' . $userid, [
    'browser'     => $browser,
    'ip'          => $ip,
    'ssluse'      => $ssluse,
    'last_access' => TIME_NOW,
    'last_login'  => TIME_NOW,
], $site_config['expires']['user_cache']);

$session->unset('simpleCaptchaAnswer');
$session->set('userID', $userid);
$session->set('username', $username);
$session->set('remembered_by_cookie', false);
logincookie($userid);

$expires   = !empty($remember) ? 365 * 86400 : 900;
$selector  = make_password(16);
$validator = make_password(32);
$values    = [
    'hash' => hash('sha512', $validator),
    'uid'  => $userid,
];

$cache->set('remember_' . $selector, $values, TIME_NOW + $expires);
$cookies = new DarkAlchemy\Pu239\Cookie('remember');
$cookies->set("$selector:$validator", TIME_NOW + $expires);

if (isset($returnto)) {
    header("Location: {$site_config['baseurl']}" . urldecode($returnto));
} else {
    header("Location: {$site_config['baseurl']}/index.php");
}
