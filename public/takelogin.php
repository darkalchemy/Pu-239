<?php

require_once __DIR__ . '/../include/bittorrent.php';
require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_password.php';
require_once INCL_DIR . 'function_recaptcha.php';
require_once CLASS_DIR . 'class_browser.php';
dbconn();
global $CURUSER, $site_config, $cache, $session, $user_stuffs, $failed_logins, $message_stuffs, $ip_stuffs;

$dt = TIME_NOW;
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
        stderr('Login Locked!', 'You have <b>Exceeded</b> the allowed maximum login attempts without successful login, therefore your ip address <b>(' . htmlsafechars($ip) . ')</b> has been locked for 24 hours.', null);
    }
}

$user_id = '';
$response = !empty($_POST['token']) ? $_POST['token'] : '';
extract($_POST);
unset($_POST);
if (!empty($bot) && !empty($auth) && !empty($torrent_pass)) {
    $user_id = $user_stuffs->get_bot_id($site_config['upload_min_class'], $bot, $torrent_pass, $auth);
}

if (empty($user_id)) {
    if (empty($username)) {
        stderr('Error', "Username can't be blank", null);
    }
    if (empty($password)) {
        stderr('Error', "Password can't be blank", null);
    }
    if (!empty($_ENV['RECAPTCHA_SECRET_KEY'])) {
        $result = verify_recaptcha($response);
        if ($result !== 'valid') {
            $session->set('is-warning', "[h2]reCAPTCHA failed. {$result}[/h2]");
            header("Location: {$site_config['baseurl']}/login.php");
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

    $sha = hash('sha256', getip(true));
    $dict_key = 'dictbreaker_' . $sha;
    $flood = $cache->get($dict_key);
    if ($flood === false || is_null($flood)) {
        $cache->set($dict_key, 'flood_check', 10);
    } else {
        die('Minimum 10 seconds between login attempts :)');
    }
    stderr($lang['tlogin_failed'], $text, null);
}

failedloginscheck();
$row = $user_stuffs->get_login($username);
$userid = $row['id'];
$ip = getip(true);
if ($row === false) {
    $values = [
        'ip' => inet_pton($ip),
        'added' => $dt,
        'attempts' => 1,
    ];
    $update = [
        'added' => $dt,
        'attempts' => new Envms\FluentPDO\Literal('attempts + 1'),
    ];

    $failed_logins->insert($values, $update);
    bark();
}
if (!password_verify($password, $row['passhash'])) {
    $values = [
        'ip' => inet_pton($ip),
        'added' => $dt,
        'attempts' => 1,
    ];
    $update = [
        'added' => $dt,
        'attempts' => new Envms\FluentPDO\Literal('attempts + 1'),
    ];

    $failed_logins->insert($values, $update);
    unset($values);
    $values[] = [
        'sender' => 0,
        'receiver' => $userid,
        'msg' => "[size=7][color=red]Security Alert[/color][/size][br]Account ID: {$userid}[br][b]Ip Address[/b]: " . htmlsafechars($ip) . '[br]Somebody (' . htmlsafechars($username) . ") tried to login but failed![br]If this wasn't you please report this event to a {$site_config['site_name']} staff member.[br][br]Thank you.[br]",
        'subject' => 'Failed login',
        'added' => $dt,
    ];
    $message_stuffs->insert($values);
    bark("<b>Error</b>: Username or password entry incorrect <br>Have you forgotten your password? <a href='{$site_config['baseurl']}/resetpw.php'><b>Recover</b></a> your password !");
} else {
    rehash_password($row['passhash'], $password, $userid);
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
        'ip' => $ip,
        'userid' => $userid,
        'type' => 'login',
        'lastlogin' => $dt,
    ];
    $update = [
        'lastlogin' => $dt,
    ];

    $ip_stuffs->insert_update($values, $update, $userid);
}

$ua = getBrowser();
$browser = 'Browser: ' . $ua['name'] . ' ' . $ua['version'] . '. Os: ' . $ua['platform'] . '. Agent : ' . $ua['userAgent'];
$set = [
    'browser' => $browser,
    'ip' => inet_pton($ip),
    'last_access' => $dt,
    'last_login' => $dt,
];
$user_stuffs->update($set, $userid);
$cache->update_row('user_' . $userid, [
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
