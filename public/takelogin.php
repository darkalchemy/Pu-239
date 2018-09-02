<?php

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'user_functions.php';
require_once INCL_DIR . 'password_functions.php';
require_once CLASS_DIR . 'class_browser.php';
dbconn();
global $CURUSER, $site_config, $cache, $session, $user_stuffs, $failed_logins, $message_stuffs, $ip_stuffs;

if (!$CURUSER) {
    get_template();
}
$lang = array_merge(load_language('global'), load_language('takelogin'));

function failedloginscheck()
{
    global $site_config, $failed_logins;

    $ip = getip(true);
    $total = $failed_logins->get($ip);
    if ($total >= $site_config['failedlogins']) {
        $set = ['banned' => 'yes'];
        $failed_logins->set($set, $ip);
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
    $user_id = $user_stuffs->get_bot_id(UC_UPLOADER, $bot, $auth);
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
$row = $user_stuffs->get_login($username);
$userid = $row['id'];
$ip = getip();
if ($row === false) {
    $values = [
        'ip' => inet_pton($ip),
        'added' => TIME_NOW,
        'attempts' => 1,
    ];
    $update = [
        'added' => TIME_NOW,
        'attempts' => new Envms\FluentPDO\Literal('attempts + 1'),
    ];

    $failed_logins->insert($values, $update);
    bark();
}
$ip_escaped = ipToStorageFormat($ip);
$added = TIME_NOW;
if (!password_verify($password, $row['passhash'])) {
    $values = [
        'ip' => inet_pton($ip),
        'added' => TIME_NOW,
        'attempts' => 1,
    ];
    $update = [
        'added' => TIME_NOW,
        'attempts' => new Envms\FluentPDO\Literal('attempts + 1'),
    ];

    $failed_logins->insert($values, $update);
    $values = [
        'sender' => 0,
        'receiver' => $userid,
        'msg' => "[size=7][color=red]Security Alert[/color][/size][br]Account ID: {$userid}[br][b]Ip Address[/b]: " . htmlsafechars($ip) . '[br]Somebody (' . htmlsafechars($username) . ") tried to login but failed![br]If this wasn't you please report this event to a {$site_config['site_name']} staff member.[br][br]Thank you.[br]",
        'subject' => 'Failed login',
        'added' => TIME_NOW,
    ];
    $message_stuffs->insert($values);
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
        $set = [
            'passhash' => make_passhash($password),
        ];
        $user_stuffs->set($set, $row['id']);
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
$failed_logins->delete($ip);
$row['perms'] = (int) $row['perms'];
$no_log_ip = ($row['perms'] & bt_options::PERMS_NO_IP);
if ($no_log_ip) {
    $ip = '127.0.0.1';
}

if (!$no_log_ip) {
    $values = [
        'userid' => $userid,
        'ip' => inet_pton($ip),
        'lastlogin' => TIME_NOW,
        'type' => 'Login',
    ];
    $update = [
        'lastlogin' => TIME_NOW,
    ];

    $ip_stuffs->insert($values, $update, $userid);
}

$ua = getBrowser();
$browser = 'Browser: ' . $ua['name'] . ' ' . $ua['version'] . '. Os: ' . $ua['platform'] . '. Agent : ' . $ua['userAgent'];
$set = [
    'browser' => $browser,
    'ip' => inet_pton($ip),
    'last_access' => TIME_NOW,
    'last_login' => TIME_NOW,
];
$user_stuffs->update($set, $userid);
$cache->update_row('user' . $userid, [
    'ip' => $ip,
], $site_config['expires']['user_cache']);

$session->set('userID', $userid);
$session->set('username', $username);
$session->set('remembered_by_cookie', false);

$expires = !empty($remember) ? $site_config['expires']['remember_me'] * 86400 : $site_config['cookie_lifetime'] * 60;
$expires = $expires >= 900 ? $expires : 900;
$user_stuffs->set_remember($userid, $expires);

if (isset($returnto)) {
    header("Location: {$site_config['baseurl']}" . urldecode($returnto));
} else {
    header("Location: {$site_config['baseurl']}/index.php");
}
