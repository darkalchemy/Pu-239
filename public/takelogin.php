<?php
require_once realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..') . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'user_functions.php';
require_once INCL_DIR . 'password_functions.php';
require_once CLASS_DIR . 'class_browser.php';
dbconn();
global $CURUSER, $site_config, $cache;

if (!$CURUSER) {
    get_template();
}
$lang = array_merge(load_language('global'), load_language('takelogin'));

function failedloginscheck()
{
    global $site_config;
    $total = 0;
    $ip = getip();
    $res = sql_query('SELECT SUM(attempts) FROM failedlogins WHERE ip = ' . ipToStorageFormat($ip)) or sqlerr(__FILE__, __LINE__);
    list($total) = mysqli_fetch_row($res);
    if ($total >= $site_config['failedlogins']) {
        sql_query("UPDATE failedlogins SET banned = 'yes' WHERE ip = " . ipToStorageFormat($ip)) or sqlerr(__FILE__, __LINE__);
        stderr('Login Locked!', 'You have <b>Exceeded</b> the allowed maximum login attempts without successful login, therefore your ip address <b>(' . htmlsafechars($ip) . ')</b> has been locked for 24 hours.');
    }
}

if (!mkglobal('username:password' . ($site_config['captcha_on'] ? ':captchaSelection:' : ':') . 'submitme')) {
    die('Something went wrong');
}
if ($submitme != 'X') {
    stderr('Ha Ha', 'You Missed, You plonker !');
}
if ($site_config['captcha_on']) {
    if (empty($captchaSelection) || getSessionVar('simpleCaptchaAnswer') != $captchaSelection) {
        $url = 'login.php';
        if (!empty($_SERVER['HTTP_REFERER'])) {
            $url = htmlsafechars($_SERVER['HTTP_REFERER']);
        }

        header("Location: $url");
        exit();
    }
}
/**
 * @param string $text
 */
function bark($text = 'Username or password incorrect')
{
    global $lang, $cache;
    $sha = sha1($_SERVER['REMOTE_ADDR']);
    $dict_key = 'dictbreaker:::' . $sha;
    $flood = $cache->get($dict_key);
    if ($flood === false) {
        $cache->set($dict_key, 'flood_check', 20);
    } else {
        die('Minimum 8 seconds between login attempts :)');
    }
    stderr($lang['tlogin_failed'], $text);
}

failedloginscheck();
$res = sql_query('SELECT id, ip, passhash, perms, ssluse, enabled FROM users WHERE username = ' . sqlesc($username) . " AND status = 'confirmed'");
$row = mysqli_fetch_assoc($res);
$ip_escaped = ipToStorageFormat(getip());
$ip = getip();
$added = TIME_NOW;
if (!$row) {
    $fail = (@mysqli_fetch_row(sql_query("SELECT COUNT(id) from failedlogins where ip = $ip_escaped"))) or sqlerr(__FILE__, __LINE__);
    if ($fail[0] == 0) {
        sql_query("INSERT INTO failedlogins (ip, added, attempts) VALUES ($ip_escaped, $added, 1)") or sqlerr(__FILE__, __LINE__);
    } else {
        sql_query("UPDATE failedlogins SET attempts = attempts + 1 where ip = $ip_escaped") or sqlerr(__FILE__, __LINE__);
    }
    bark();
}
if (!password_verify($password, $row['passhash'])) {
    $fail = (@mysqli_fetch_row(sql_query("SELECT COUNT(id) from failedlogins where ip=$ip_escaped"))) or sqlerr(__FILE__, __LINE__);
    if ($fail[0] == 0) {
        sql_query("INSERT INTO failedlogins (ip, added, attempts) VALUES ($ip_escaped, $added, 1)") or sqlerr(__FILE__, __LINE__);
    } else {
        sql_query("UPDATE failedlogins SET attempts = attempts + 1 where ip=$ip_escaped") or sqlerr(__FILE__, __LINE__);
    }
    $to = ((int)$row['id']);
    $subject = 'Failed login';
    $msg = "[color=red]Security alert[/color]\n Account: ID=" . (int)$row['id'] . ' Somebody (probably you, ' . htmlsafechars($username) . ' !) tried to login but failed!' . "\nTheir [b]Ip Address [/b] was : " . htmlsafechars($ip) . "\n If this wasn't you please report this event to a {$site_config['site_name']} staff member\n - Thank you.\n";
    $sql = 'INSERT INTO messages (sender, receiver, msg, subject, added) VALUES(0, ' . sqlesc($to) . ', ' . sqlesc($msg) . ', ' . sqlesc($subject) . ", $added);";
    $res = sql_query($sql) or sqlerr(__FILE__, __LINE__);
    $cache->increment('inbox_' . $row['id']);
    bark("<b>Error</b>: Username or password entry incorrect <br>Have you forgotten your password? <a href='{$site_config['baseurl']}/resetpw.php'><b>Recover</b></a> your password !");
}
if ($row['enabled'] == 'no') {
    bark($lang['tlogin_disabled']);
}
sql_query("DELETE FROM failedlogins WHERE ip = $ip_escaped");
$userid = (int)$row['id'];
$row['perms'] = (int)$row['perms'];
//== Start ip logger - Melvinmeow, Mindless, pdq
$no_log_ip = ($row['perms'] & bt_options::PERMS_NO_IP);
if ($no_log_ip) {
    $ip = '127.0.0.1';
    $ip_escaped = ipToStorageFormat($ip);
}
if (!$no_log_ip) {
    $res = sql_query("SELECT * FROM ips WHERE ip = $ip_escaped AND userid = " . sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
    if (mysqli_num_rows($res) == 0) {
        sql_query('INSERT INTO ips (userid, ip, lastlogin, type) VALUES (' . sqlesc($userid) . ", $ip_escaped , $added, 'Login')") or sqlerr(__FILE__, __LINE__);
        $cache->delete('ip_history_' . $userid);
    } else {
        sql_query("UPDATE ips SET lastlogin=$added WHERE ip = $ip_escaped AND userid = " . sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
        $cache->delete('ip_history_' . $userid);
    }
} // End Ip logger
if (isset($_POST['use_ssl']) && $_POST['use_ssl'] == 1 && !isset($_SERVER['HTTPS'])) {
    $site_config['baseurl'] = str_replace('http', 'https', $site_config['baseurl']);
}
$ssl_value = (isset($_POST['perm_ssl']) && $_POST['perm_ssl'] == 1 ? 'ssluse = 2' : 'ssluse = 1');
$ssluse = ($row['ssluse'] == 2 ? 2 : 1);
// output browser
$ua = getBrowser();
$browser = 'Browser: ' . $ua['name'] . ' ' . $ua['version'] . '. Os: ' . $ua['platform'] . '. Agent : ' . $ua['userAgent'];
sql_query('UPDATE users SET browser = ' . sqlesc($browser) . ', ' . $ssl_value . ', ip = ' . $ip_escaped . ', last_access = ' . TIME_NOW . ', last_login = ' . TIME_NOW . ' WHERE id = ' . sqlesc($row['id'])) or sqlerr(__FILE__, __LINE__);
$cache->update_row('MyUser_' . $row['id'], [
    'browser'     => $browser,
    'ip'          => $ip,
    'ssluse'      => $ssluse,
    'last_access' => TIME_NOW,
    'last_login'  => TIME_NOW,
], $site_config['expires']['curuser']);
$cache->update_row('user' . $row['id'], [
    'browser'     => $browser,
    'ip'          => $ip,
    'ssluse'      => $ssluse,
    'last_access' => TIME_NOW,
    'last_login'  => TIME_NOW,
], $site_config['expires']['user_cache']);

unsetSessionVar('simpleCaptchaAnswer');
unsetSessionVar('simpleCaptchaTimestamp');
setSessionVar('userID', $row['id']);
logincookie($row['id']);

if (isset($_POST['returnto'])) {
    header("Location: {$site_config['baseurl']}" . urldecode($_POST['returnto']));
} else {
    header("Location: {$site_config['baseurl']}/index.php");
}
