<?php

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'user_functions.php';
require_once INCL_DIR . 'password_functions.php';
require_once INCL_DIR . 'bbcode_functions.php';
require_once INCL_DIR . 'function_bemail.php';
dbconn();
global $CURUSER, $site_config, $lang, $fluent, $cache, $session;

if (!$CURUSER) {
    get_template();
}

$cache->delete('userlist_' . $site_config['chatBotID']);
$ip = getip();
if (!$site_config['openreg_invites']) {
    stderr('Sorry', 'Invite Signups are closed presently');
}
$users_count = $fluent->from('users')
    ->select(null)
    ->select('COUNT(id) AS count')
    ->fetch('count');

if ($users_count >= $site_config['maxusers']) {
    stderr($lang['takesignup_error'], $lang['takesignup_limit']);
}
$lang = array_merge(load_language('global'), load_language('takesignup'));
if (!mkglobal('wantusername:wantpassword:passagain:invite:submitme:passhint:hintanswer:country')) {
    stderr($lang['takesignup_user_error'], $lang['takesignup_form_data']);
}
if ($submitme != 'X') {
    stderr('Ha Ha', 'You Missed, You plonker!');
}

if (!empty($_ENV['RECAPTCHA_SECRET_KEY'])) {
    $response = !empty($_POST['g-recaptcha-response']) ? $_POST['g-recaptcha-response'] : '';
    if ($response === '') {
        header('Location: login.php');
        exit();
    }
    $ip = getip();
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
        $session->set('is-warning', '[h2]reCAPTCHA was incorrect.[/h2]');
        header("Location: {$site_config['baseurl']}/invite_signup.php");
        die();
    }
}

if (empty($wantusername) || empty($wantpassword) || empty($invite) || empty($passhint) || empty($hintanswer) || empty($country)) {
    stderr($lang['takesignup_user_error'], $lang['takesignup_blank']);
}
if (empty($country)) {
    stderr($lang['takesignup_user_error'], 'Please select your country');
}
if (!blacklist($wantusername)) {
    stderr($lang['takesignup_user_error'], sprintf($lang['takesignup_badusername'], htmlsafechars($wantusername)));
}
if (strlen($wantusername) > 64) {
    stderr('Error', 'Sorry, username is too long (max is 64 chars)');
}
if ($wantpassword !== $passagain) {
    stderr($lang['takesignup_user_error'], $lang['takesignup_nomatch']);
}
if (strlen($wantpassword) < 6) {
    stderr($lang['takesignup_user_error'], $lang['takesignup_pass_short']);
}
if ($wantpassword === $wantusername) {
    stderr($lang['takesignup_user_error'], $lang['takesignup_same']);
}
if (!valid_username($wantusername)) {
    stderr($lang['takesignup_user_error'], $lang['takesignup_invalidname']);
}
if (!(isset($_POST['day']) || isset($_POST['month']) || isset($_POST['year']))) {
    stderr('Error', 'You have to fill in your birthday.');
}
if (checkdate($_POST['month'], $_POST['day'], $_POST['year'])) {
    $birthday = $_POST['year'] . '-' . $_POST['month'] . '-' . $_POST['day'];
} else {
    stderr('Error', 'You have to fill in your birthday correctly.');
}
if ((date('Y') - $_POST['year']) < 17) {
    stderr('Error', 'You must be at least 18 years old to register.');
}
if (!(isset($_POST['country']))) {
    stderr('Error', 'You have to set your country.');
}
$country = (((isset($_POST['country']) && is_valid_id($_POST['country'])) ? intval($_POST['country']) : 0));
$gender = isset($_POST['gender'], $_POST['gender']) ? htmlsafechars($_POST['gender']) : '';
if ($_POST['rulesverify'] != 'yes' || $_POST['faqverify'] != 'yes' || $_POST['ageverify'] != 'yes') {
    stderr($lang['takesignup_failed'], $lang['takesignup_qualify']);
}

if ($site_config['dupeip_check_on']) {
    $ip_count = $fluent->from('users')
        ->select(null)
        ->select('COUNT(id) AS count')
        ->where('ip = ?', inet_pton($ip))
        ->fetch('count');
    if ($ip_count != 0) {
        stderr('Error', 'The ip ' . htmlsafechars($ip) . ' is already in use. We only allow one account per ip address.');
        die();
    }
}
if (isset($_POST['user_timezone']) && preg_match('#^\-?\d{1,2}(?:\.\d{1,2})?$#', $_POST['user_timezone'])) {
    $time_offset = (int) $_POST['user_timezone'];
} else {
    $time_offset = isset($site_config['time_offset']) ? (int) $site_config['time_offset'] : 0;
}

$dst_in_use = localtime(TIME_NOW + ($time_offset * 3600), true);

$select_inv = sql_query('SELECT sender, receiver, status, email FROM invite_codes WHERE code = ' . sqlesc($invite)) or sqlerr(__FILE__, __LINE__);
$rows = mysqli_num_rows($select_inv);
$assoc = mysqli_fetch_assoc($select_inv);
if ($rows == 0) {
    stderr('Error', "Invite not found.\nPlease request a invite from one of our members.");
}
if ($assoc['receiver'] != 0) {
    stderr('Error', "Invite already taken.\nPlease request a new one from your inviter.");
}
$email = $assoc['email'];
$email_count = $fluent->from('users')
    ->select(null)
    ->select('COUNT(id) AS count')
    ->where('email = ?', $email)
    ->fetch('count');
if ($email_count != 0) {
    stderr($lang['takesignup_user_error'], $lang['takesignup_email_used']);
    die();
}
$wantpasshash = make_passhash($wantpassword);
$wanthintanswer = make_passhash($hintanswer);
$user_frees = (XBT_TRACKER ? '0' : TIME_NOW + 14 * 86400);
$torrent_pass = make_password(32);
$auth = make_password(32);
$apikey = make_password(32);
check_banned_emails($email);

$new_user = sql_query('INSERT INTO users (username, passhash, torrent_pass, auth, apikey, passhint, hintanswer, birthday, invitedby, email, added, last_access, last_login, time_offset, dst_in_use, free_switch, ip, status) VALUES (' . implode(',', array_map('sqlesc', [
        $wantusername,
        $wantpasshash,
        $torrent_pass,
        $auth,
        $apikey,
        $passhint,
        $wanthintanswer,
        $birthday,
        (int) $assoc['sender'],
        $email,
        TIME_NOW,
        TIME_NOW,
        TIME_NOW,
        $time_offset,
        $dst_in_use['tm_isdst'],
        $user_frees,
        $ip,
        'confirmed',
    ])) . ')');
$id = 0;
while ($id == 0) {
    usleep(500);
    $id = get_one_row('users', 'id', 'WHERE username = ' . sqlesc($wantusername));
}
sql_query('INSERT INTO usersachiev (userid) VALUES (' . sqlesc($id) . ')') or sqlerr(__FILE__, __LINE__);
sql_query('UPDATE usersachiev SET invited = invited + 1 WHERE userid = ' . sqlesc($assoc['sender'])) or sqlerr(__FILE__, __LINE__);
$msg = "Welcome New {$site_config['site_name']} Member : - [user]" . htmlsafechars($wantusername) . '[/user]';
if (!$new_user) {
    if (((is_object($GLOBALS['___mysqli_ston'])) ? mysqli_errno($GLOBALS['___mysqli_ston']) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)) == 1062) {
        stderr('Error', 'Username already exists!');
    }
}

$sender = (int) $assoc['sender'];
$added = TIME_NOW;
$msg = sqlesc("Hey there [you] ! :wave:\nIt seems that someone you invited to {$site_config['site_name']} has arrived ! :clap2: \n\n Please go to your [url={$site_config['baseurl']}/invite.php]Invite page[/url] to confirm them so they can log in.\n\ncheers\n");
$subject = sqlesc('Someone you invited has arrived!');
sql_query("INSERT INTO messages (sender, subject, receiver, msg, added) VALUES (0, $subject, " . sqlesc($sender) . ", $msg, $added)") or sqlerr(__FILE__, __LINE__);
$cache->increment('inbox_' . $sender);

sql_query('UPDATE invite_codes SET receiver = ' . sqlesc($id) . ', status = "Confirmed" WHERE sender = ' . sqlesc((int) $assoc['sender']) . ' AND code = ' . sqlesc($invite)) or sqlerr(__FILE__, __LINE__);
$latestuser_cache['id'] = (int) $id;
$latestuser_cache['username'] = $wantusername;
$latestuser_cache['class'] = '0';
$latestuser_cache['donor'] = 'no';
$latestuser_cache['warned'] = '0';
$latestuser_cache['enabled'] = 'yes';
$latestuser_cache['chatpost'] = '1';
$latestuser_cache['leechwarn'] = '0';
$latestuser_cache['pirate'] = '0';
$latestuser_cache['king'] = '0';
$cache->delete('all_users_');

$cache->set('latestuser', $latestuser_cache, 0, $site_config['expires']['latestuser']);
$cache->delete('birthdayusers');
$cache->delete('chat_users_list');
write_log('User account ' . htmlsafechars($wantusername) . ' was created!');
if ($id > 2 && $site_config['autoshout_on'] == 1) {
    $msg = "Welcome New {$site_config['site_name']} Member: [user]" . htmlsafechars($wantusername) . '[/user]';
    autoshout($msg);
}

header("Location: {$site_config['baseurl']}/ok.php?type=confirm");
die();
