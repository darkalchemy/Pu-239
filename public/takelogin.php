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

    $ip = getip(true);
    $res = sql_query('SELECT SUM(attempts), ip FROM failedlogins WHERE ip = ' . ipToStorageFormat($ip)) or sqlerr(__FILE__, __LINE__);
    list($total) = mysqli_fetch_row($res);
    if ($total >= $site_config['failedlogins']) {
        sql_query("UPDATE failedlogins SET banned = 'yes' WHERE ip = " . ipToStorageFormat($ip)) or sqlerr(__FILE__, __LINE__);
        stderr('Login Locked!', 'You have <b>Exceeded</b> the allowed maximum login attempts without successful login, therefore your ip address <b>(' . htmlsafechars($ip) . ')</b> has been locked for 24 hours.');
    }
}

$user_id = '';
$response = !empty($_POST['g-recaptcha-response']) ? $_POST['g-recaptcha-response'] : '';
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
    if (empty($submitme) || $submitme != 'Login') {
        stderr('Error', 'You missed, you plonker!');
    }

    if (!empty($_ENV['RECAPTCHA_SECRET_KEY'])) {
        if ($response === '') {
            header('Location: login.php');
            exit();
        }
        $ip = getip(true);
        $url = 'https://www.google.com/recaptcha/api/siteverify';
        $params = [
            'secret' => $_ENV['RECAPTCHA_SECRET_KEY'],
            'response' => $response,
            'remoteip' => $ip,
        ];
        $query = http_build_query($params);
        $contextData = [
            'method' => 'POST',
            'header' => "Content-Type: application/x-www-form-urlencoded\r\n" . "Connection: close\r\n" . 'Content-Length: ' . strlen($query) . "\r\n",
            'content' => $query,
        ];
        $context = stream_context_create(['http' => $contextData]);
        $result = file_get_contents($url, false, $context);
        $responseKeys = json_decode($result, true);
        if (intval($responseKeys['success']) !== 1) {
            stderr('Error', 'reCAPTCHA Failed');
        }
    }
}

/**
 * @param string $text
 */
function bark($text = 'Username or password incorrect')
{
    global $lang, $site_config, $cache;

    $sha = hash('sha256', getip(true));
    $dict_key = 'dictbreaker_' . $sha;
    $flood = $cache->get($dict_key);
    if ($flood === false || is_null($flood)) {
        $cache->set($dict_key, 'flood_check', 10);
    } else {
        die('Minimum 10 seconds between login attempts :)');
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

$userid = $row['id'];
$ip_escaped = ipToStorageFormat(getip(true));
$ip = getip();
$added = TIME_NOW;
if ($row === false) {
    $fail = (@mysqli_fetch_row(sql_query("SELECT COUNT(id) from failedlogins where ip = $ip_escaped"))) or sqlerr(__FILE__, __LINE__);
    if ($fail[0] == 0) {
        sql_query("INSERT INTO failedlogins (ip, added, attempts) VALUES ($ip_escaped, $added, 1)") or sqlerr(__FILE__, __LINE__);
    } else {
        sql_query("UPDATE failedlogins SET attempts = attempts + 1 where ip = $ip_escaped") or sqlerr(__FILE__, __LINE__);
    }
    bark();
}

if (!password_verify($password, $row['passhash'])) {
    $fail = (@mysqli_fetch_row(sql_query("SELECT COUNT(id), ip from failedlogins where ip = $ip_escaped"))) or sqlerr(__FILE__, __LINE__);
    if ($fail[0] == 0) {
        sql_query("INSERT INTO failedlogins (ip, added, attempts) VALUES ($ip_escaped, $added, 1)") or sqlerr(__FILE__, __LINE__);
    } else {
        sql_query("UPDATE failedlogins SET attempts = attempts + 1 where ip=$ip_escaped") or sqlerr(__FILE__, __LINE__);
    }
    $subject = 'Failed login';
    $msg = "[color=red]Security alert[/color]\n Account: ID=" . $userid . ' Somebody (probably you, ' . htmlsafechars($username) . ' !) tried to login but failed!' . "\nTheir [b]Ip Address [/b] was : " . htmlsafechars($ip) . "\n If this wasn't you please report this event to a {$site_config['site_name']} staff member\n - Thank you.\n";
    $sql = 'INSERT INTO messages (sender, receiver, msg, subject, added) VALUES(0, ' . sqlesc($userid) . ', ' . sqlesc($msg) . ', ' . sqlesc($subject) . ", $added);";
    $res = sql_query($sql) or sqlerr(__FILE__, __LINE__);
    $cache->increment('inbox_' . $userid);
    bark("<b>Error</b>: Username or password entry incorrect <br>Have you forgotten your password? <a href='{$site_config['baseurl']}/resetpw.php'><b>Recover</b></a> your password !");
} else {
    if (PHP_VERSION_ID >= 70200 && @password_hash('secret_password', PASSWORD_ARGON2I)) {
        $algo = PASSWORD_ARGON2I;
        $options = [
            'memory_cost' => !empty($site_config['password_memory_cost']) ? $site_config['password_memory_cost'] : 2048,
            'time_cost' => !empty($site_config['password_time_cost']) ? $site_config['password_time_cost'] : 12,
            'threads' => !empty($site_config['password_threads']) ? $site_config['password_threads'] : 4,
        ];
    } else {
        $algo = PASSWORD_BCRYPT;
        $options = [
            'cost' => !empty($site_config['password_cost']) ? $site_config['password_cost'] : 12,
        ];
    }
    if (password_needs_rehash($row['passhash'], $algo, $options)) {
        sql_query('UPDATE users SET passhash = ' . sqlesc(make_passhash($password)) . ' WHERE id = ' . sqlesc($row['id'])) or sqlerr(__FILE__, __LINE__);
    }
}

if ($row['enabled'] === 'no') {
    bark($lang['tlogin_disabled']);
}
if ($row['status'] === 'pending') {
    if ($site_config['email_confirm']) {
        bark('You have not confirmed your amail address. Please use the link in the email that you should have received.');
    }
    bark('Your account has not been confirmed.');
}
sql_query("DELETE FROM failedlogins WHERE ip = $ip_escaped");
$row['perms'] = (int) $row['perms'];
$no_log_ip = ($row['perms'] & bt_options::PERMS_NO_IP);
if ($no_log_ip) {
    $ip = '127.0.0.1';
}
$ip_escaped = ipToStorageFormat($ip);

if (!$no_log_ip) {
    $res = sql_query("SELECT * FROM ips WHERE ip = $ip_escaped AND userid = " . sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
    if (mysqli_num_rows($res) == 0) {
        sql_query('INSERT INTO ips (userid, ip, lastlogin, type) VALUES (' . sqlesc($userid) . ", $ip_escaped , $added, 'Login')") or sqlerr(__FILE__, __LINE__);
        $cache->delete('ip_history_' . $userid);
    } else {
        sql_query("UPDATE ips SET lastlogin=$added WHERE ip = $ip_escaped AND userid = " . sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
        $cache->delete('ip_history_' . $userid);
    }
}

$ssluse = isset($use_ssl) && $use_ssl == 1 ? 1 : 0;
$ua = getBrowser();
$browser = 'Browser: ' . $ua['name'] . ' ' . $ua['version'] . '. Os: ' . $ua['platform'] . '. Agent : ' . $ua['userAgent'];

sql_query('UPDATE users SET browser = ' . sqlesc($browser) . ', ssluse = ' . sqlesc($ssluse) . ", ip = $ip_escaped, last_access = " . TIME_NOW . ', last_login = ' . TIME_NOW . ' WHERE id = ' . sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
$cache->update_row('user' . $userid, [
    'browser' => $browser,
    'ip' => $ip,
    'ssluse' => $ssluse,
    'last_access' => TIME_NOW,
    'last_login' => TIME_NOW,
], $site_config['expires']['user_cache']);

$session->set('userID', $userid);
$session->set('username', $username);
$session->set('remembered_by_cookie', false);
logincookie($userid);

$expires = !empty($remember) ? 365 * 86400 : 900;
$selector = make_password(16);
$validator = make_password(32);
$values = [
    'hash' => hash('sha512', $validator),
    'uid' => $userid,
];

$cache->set('remember_' . $selector, $values, TIME_NOW + $expires);
$cookies = new DarkAlchemy\Pu239\Cookie('remember');
$cookies->set("$selector:$validator", TIME_NOW + $expires);

if (isset($returnto)) {
    header("Location: {$site_config['baseurl']}" . urldecode($returnto));
} else {
    header("Location: {$site_config['baseurl']}/index.php");
}
