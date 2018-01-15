<?php
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'user_functions.php';
require_once INCL_DIR . 'password_functions.php';
require_once INCL_DIR . 'bbcode_functions.php';
require_once INCL_DIR . 'function_bemail.php';
dbconn();
global $CURUSER, $site_config, $cache, $lang, $fluent;

if (!$CURUSER) {
    get_template();
}

$cache->delete('userlist_' . $site_config['chatBotID']);
$ip = getip();
if (!$site_config['openreg']) {
    stderr('Sorry', 'Invite only - Signups are closed presently if you have an invite code click <a href="' . $site_config['baseurl'] . '/invite_signup.php"><b> Here</b></a>');
}
$users_count = $fluent->from('users')
    ->select(null)
    ->select('COUNT(id) AS count')
    ->fetch('count');

if ($users_count >= $site_config['maxusers']) {
    stderr($lang['takesignup_error'], $lang['takesignup_limit']);
}
$lang = array_merge(load_language('global'), load_language('takesignup'));
if (!mkglobal('wantusername:wantpassword:passagain:email' . ($site_config['captcha_on'] ? ':captchaSelection:' : ':') . 'submitme:passhint:hintanswer:country')) {
    stderr($lang['takesignup_user_error'], $lang['takesignup_form_data']);
}
if ($submitme != 'X') {
    stderr('Ha Ha', 'You Missed, You plonker!');
}
if ($site_config['captcha_on']) {
    if (empty($captchaSelection) || getSessionVar('simpleCaptchaAnswer') != $captchaSelection) {
        header('Location: signup.php');
        die();
    }
}

if (empty($wantusername) || empty($wantpassword) || empty($email) || empty($passhint) || empty($hintanswer) || empty($country)) {
    stderr($lang['takesignup_user_error'], $lang['takesignup_blank']);
}
if ($country == 999999) {
    stderr($lang['takesignup_user_error'], 'Please select your country');
}
if (!blacklist($wantusername)) {
    stderr($lang['takesignup_user_error'], sprintf($lang['takesignup_badusername'], htmlsafechars($wantusername)));
}
if (strlen($wantusername) > 64) {
    stderr('Error', 'Sorry, username is too long (max is 64 chars)');
}
if ($wantpassword != $passagain) {
    stderr($lang['takesignup_user_error'], $lang['takesignup_nomatch']);
}
if (strlen($wantpassword) < 6) {
    stderr($lang['takesignup_user_error'], $lang['takesignup_pass_short']);
}
if (strlen($wantpassword) > 100) {
    stderr($lang['takesignup_user_error'], $lang['takesignup_pass_long']);
}
if ($wantpassword == $wantusername) {
    stderr($lang['takesignup_user_error'], $lang['takesignup_same']);
}
if (!validemail($email)) {
    stderr($lang['takesignup_user_error'], $lang['takesignup_validemail']);
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
$gender = isset($_POST['gender']) && isset($_POST['gender']) ? htmlsafechars($_POST['gender']) : '';
if ($_POST['rulesverify'] != 'yes' || $_POST['faqverify'] != 'yes' || $_POST['ageverify'] != 'yes') {
    stderr($lang['takesignup_failed'], $lang['takesignup_qualify']);
}

$email_count = $fluent->from('users')
    ->select(null)
    ->select('COUNT(id) AS count')
    ->where('email = ?', $email)
    ->fetch('count');
if ($email_count != 0) {
    stderr($lang['takesignup_user_error'], $lang['takesignup_email_used']);
    die();
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
    $time_offset = (int)$_POST['user_timezone'];
} else {
    $time_offset = isset($site_config['time_offset']) ? (int)$site_config['time_offset'] : 0;
}

$dst_in_use = localtime(TIME_NOW + ($time_offset * 3600), true);

$wantpasshash = make_passhash($wantpassword);
$wanthintanswer = make_passhash($hintanswer);
$user_frees = (XBT_TRACKER ? '0' : TIME_NOW + 14 * 86400);
$torrent_pass = make_torrentpass();
check_banned_emails($email);

$values = [
    'username'     => $wantusername,
    'torrent_pass' => $torrent_pass,
    'passhash'     => $wantpasshash,
    'birthday'     => $birthday,
    'country'      => $country,
    'gender'       => $gender,
    'stylesheet'   => $site_config['stylesheet'],
    'passhint'     => $passhint,
    'hintanswer'   => $wanthintanswer,
    'email'        => $email,
    'ip'           => $ip,
    'added'        => TIME_NOW,
    'last_access'  => TIME_NOW,
    'time_offset'  => $time_offset,
    'dst_in_use'   => $dst_in_use['tm_isdst'],
    'free_switch'  => $user_frees,
    'ip'           => inet_pton($ip),
    'status'       => ($users_count === 0 || (!$site_config['email_confirm'] && $site_config['auto_confirm']) ? 'confirmed' : 'pending'),
    'class'        => ($users_count === 0 ? UC_SYSOP : UC_USER),
];

if ($users_count == 0) {
    $values['seedbonus'] = 1000000;
    $values['invites'] = 1000;
}

$user_id = $fluent->insertInto('users')
    ->values($values)
    ->execute();

if (!$user_id) {
    stderr($lang['takesignup_user_error'], $lang['takesignup_user_exists']);
    die();
}

$fluent->insertInto('usersachiev')
    ->values(['userid' => $user_id])
    ->execute();

$psecret = '';
if ($users_count > 0 && $site_config['email_confirm']) {
    $secret = make_password(30);
    $token = make_passhash($secret);
    $psecret = "&token=$secret";
    $alt_id = make_password(16);
    $values = [
        'email' => $email,
        'token' => $token,
        'id'    => $alt_id,
    ];
    $fluent->insertInto('tokens')
        ->values($values)
        ->execute();
}

$cache->delete('birthdayusers');
$cache->delete('chat_users_list');
if ($users_count === 0) {
    $cache->delete('staff_settings_');
}

$added = TIME_NOW;
$subject = 'Welcome';
$msg = 'Hey there ' . htmlsafechars($wantusername) . "! Welcome to {$site_config['site_name']}! :clap2: \n\n Please ensure your connectable before downloading or uploading any torrents\n - If your unsure then please use the forum and Faq or pm admin onsite.\n\nBe aware that the users database is deleted every few days.\n\ncheers {$site_config['site_name']} staff.\n";
$values = [
    'sender'   => 0,
    'subject'  => $subject,
    'receiver' => $user_id,
    'msg'      => $msg,
    'added'    => $added,
];

$fluent->insertInto('messages')
    ->values($values)
    ->execute();

$cache->delete('all_users_');
$cache->set('latestuser', (int)$user_id, $site_config['expires']['latestuser']);
write_log('User account ' . (int)$user_id . ' (' . htmlsafechars($wantusername) . ') was created');

if ($user_id > 2 && $site_config['autoshout_on'] == 1) {
    $message = "Welcome New {$site_config['site_name']} Member: [user]" . htmlsafechars($wantusername) . '[/user]';
    autoshout($message);
}

if ($users_count > 0 && $site_config['email_confirm']) {
    $body = str_replace([
                            '<#SITENAME#>',
                            '<#USEREMAIL#>',
                            '<#IP_ADDRESS#>',
                            '<#REG_LINK#>',
                        ], [
                            $site_config['site_name'],
                            $email,
                            $ip,
                            "{$site_config['baseurl']}/confirm.php?id=$alt_id$psecret",
                        ], $lang['takesignup_email_body']);
    mail($email, "{$site_config['site_name']} {$lang['takesignup_confirm']}", $body, "{$lang['takesignup_from']} {$site_config['site_email']}");
} else {
    clearUserCache($user_id);
    setSessionVar('userID', $user_id);
}

header("Location: {$site_config['baseurl']}/ok.php?type=" . ($users_count === 0 ? 'sysop' : ($site_config['email_confirm'] ? 'signup&email=' . urlencode($email) : 'confirm')));
