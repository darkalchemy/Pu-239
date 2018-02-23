<?php

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'user_functions.php';
require_once INCL_DIR . 'password_functions.php';
require_once INCL_DIR . 'bbcode_functions.php';
require_once INCL_DIR . 'function_bemail.php';
dbconn();
get_template();
global $site_config, $lang, $fluent, $cache, $session;

use Nette\Mail\Message;
use Nette\Mail\SendmailMailer;

$wantusername = $wantpassword = $passagain = $email = $user_timezone = $year = $month = $day = $passhint = '';
$hintanswer   = $country   = $gender   = $rulesverify   = $faqverify   = $ageverify   = $captchaSelection   = $submitme   = '';
$session->set('signup_variables', serialize($_POST));

extract($_POST);
unset($_POST);

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

$required = [
    'wantusername',
    'wantpassword',
    'passagain',
    'email',
    'user_timezone',
    'year',
    'month',
    'day',
    'passhint',
    'hintanswer',
    'country',
    'gender',
    'rulesverify',
    'faqverify',
    'ageverify',
    'submitme',
];

foreach ($required as $field) {
    if (empty($$field)) {
        $session->set('is-warning', "[h2]{$lang['takesignup_form_data']}[/h2][p]All fields must be completed[/p]");
        header("Location: {$site_config['baseurl']}/signup.php");
        die();
    }
}

if ('X' != $submitme) {
    $session->set('is-warning', '[h2]You clicked the wrong button.[/h2]');
    header("Location: {$site_config['baseurl']}/signup.php");
    die();
}

if ($site_config['captcha_on']) {
    if (empty($captchaSelection) || $session->get('simpleCaptchaAnswer') != $captchaSelection) {
        $session->set('is-warning', '[h2]The captcha selection was incorrect.[/h2]');
        header("Location: {$site_config['baseurl']}/signup.php");
        die();
    }
}

if (999999 == $country) {
    $session->set('is-warning', '[h2]Please select your country[/h2]');
    header("Location: {$site_config['baseurl']}/signup.php");
    die();
}
if (!blacklist($wantusername)) {
    $session->set('is-warning', '[h2]' . sprintf($lang['takesignup_badusername'], htmlsafechars($wantusername)) . '[/h2]');
    header("Location: {$site_config['baseurl']}/signup.php");
    die();
}
if (strlen($wantusername) > 64) {
    $session->set('is-warning', '[h2]Sorry, username is too long (max is 64 chars)[/h2]');
    header("Location: {$site_config['baseurl']}/signup.php");
    die();
}
if ($wantpassword != $passagain) {
    $session->set('is-warning', "[h2]{$lang['takesignup_nomatch']}[/h2]");
    header("Location: {$site_config['baseurl']}/signup.php");
    die();
}
if (strlen($wantpassword) < 6) {
    $session->set('is-warning', "[h2]{$lang['takesignup_pass_short']}[/h2]");
    header("Location: {$site_config['baseurl']}/signup.php");
    die();
}
if (strlen($wantpassword) > 72) {
    $session->set('is-warning', "[h2]{$lang['takesignup_pass_long']}[/h2]");
    header("Location: {$site_config['baseurl']}/signup.php");
    die();
}
if ($wantpassword == $wantusername) {
    $session->set('is-warning', "[h2]{$lang['takesignup_same']}[/h2]");
    header("Location: {$site_config['baseurl']}/signup.php");
    die();
}
if (!validemail($email)) {
    $session->set('is-warning', "[h2]{$lang['takesignup_validemail']}[/h2]");
    header("Location: {$site_config['baseurl']}/signup.php");
    die();
}
if (!valid_username($wantusername)) {
    $session->set('is-warning', "[h2]{$lang['takesignup_invalidname']}[/h2]");
    header("Location: {$site_config['baseurl']}/signup.php");
    die();
}
if (empty($day) || empty($month) || empty($year)) {
    $session->set('is-warning', '[h2]You have to fill in your birthday.[/h2]');
    header("Location: {$site_config['baseurl']}/signup.php");
    die();
}
if (checkdate($month, $day, $year)) {
    $birthday = $year . '-' . $month . '-' . str_pad($day, 2, '0', STR_PAD_LEFT);
} else {
    $session->set('is-warning', '[h2]You have to fill in your birthday correctly.[/h2]');
    header("Location: {$site_config['baseurl']}/signup.php");
    die();
}
if ((date('Y') - $year) < 18) {
    $session->set('is-warning', '[h2]You must be at least 18 years old to register.[/h2]');
    header("Location: {$site_config['baseurl']}/signup.php");
    die();
}
if (!(isset($country))) {
    $session->set('is-warning', '[h2]You have to set your country.[/h2]');
    header("Location: {$site_config['baseurl']}/signup.php");
    die();
}
$country = isset($country) && is_valid_id($country) ? intval($country) : 0;
$gender  = isset($gender) ? htmlsafechars($gender) : '';
if ('yes' != $rulesverify || 'yes' != $faqverify || 'yes' != $ageverify) {
    $session->set('is-warning', "[h2]{$lang['takesignup_qualify']}[/h2]");
    header("Location: {$site_config['baseurl']}/signup.php");
    die();
}

$email_count = $fluent->from('users')
    ->select(null)
    ->select('COUNT(id) AS count')
    ->where('email = ?', $email)
    ->fetch('count');
if (0 != $email_count) {
    $session->set('is-warning', "[h2]{$lang['takesignup_email_used']}[/h2]");
    header("Location: {$site_config['baseurl']}/signup.php");
    die();
}

if ($site_config['dupeip_check_on']) {
    $ip_count = $fluent->from('users')
        ->select(null)
        ->select('COUNT(id) AS count')
        ->where('ip = ?', inet_pton($ip))
        ->fetch('count');
    if (0 != $ip_count) {
        $session->set('is-warning', '[h2]The ip ' . htmlsafechars($ip) . ' is already in use. We only allow one account per ip address.[/h2]');
        header("Location: {$site_config['baseurl']}/signup.php");
        die();
    }
}
if (isset($user_timezone) && preg_match('#^\-?\d{1,2}(?:\.\d{1,2})?$#', $user_timezone)) {
    $time_offset = (int) $user_timezone;
} else {
    $time_offset = isset($site_config['time_offset']) ? (int) $site_config['time_offset'] : 0;
}

$dst_in_use = localtime(TIME_NOW + ($time_offset * 3600), true);

check_banned_emails($email);

$values = [
    'username'     => $wantusername,
    'torrent_pass' => make_password(32),
    'auth'         => make_password(32),
    'apikey'       => make_password(32),
    'passhash'     => make_passhash($wantpassword),
    'birthday'     => $birthday,
    'country'      => $country,
    'gender'       => $gender,
    'stylesheet'   => $site_config['stylesheet'],
    'passhint'     => $passhint,
    'hintanswer'   => make_passhash($hintanswer),
    'email'        => $email,
    'ip'           => $ip,
    'added'        => TIME_NOW,
    'last_access'  => TIME_NOW,
    'time_offset'  => $time_offset,
    'dst_in_use'   => $dst_in_use['tm_isdst'],
    'free_switch'  => (XBT_TRACKER ? '0' : TIME_NOW + 14 * 86400),
    'ip'           => inet_pton($ip),
    'status'       => (0 === $users_count || (!$site_config['email_confirm'] && $site_config['auto_confirm']) ? 'confirmed' : 'pending'),
    'class'        => (0 === $users_count ? UC_SYSOP : UC_USER),
];

if (0 == $users_count) {
    $values['seedbonus'] = 1000000;
    $values['invites']   = 1000;
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
    $secret  = make_password(30);
    $token   = make_passhash($secret);
    $psecret = "&token=$secret";
    $alt_id  = make_password(16);
    $values  = [
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
if (0 === $users_count) {
    $cache->delete('staff_settings_');
}

$added   = TIME_NOW;
$subject = 'Welcome';
$msg     = 'Hey there ' . htmlsafechars($wantusername) . "! Welcome to {$site_config['site_name']}! :clap2: \n\n Please ensure your connectable before downloading or uploading any torrents\n - If your unsure then please use the forum and Faq or pm admin onsite.\n\nBe aware that the users database is deleted every few days.\n\ncheers {$site_config['site_name']} staff.\n";
$values  = [
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
$cache->set('latestuser', (int) $user_id, $site_config['expires']['latestuser']);
write_log('User account ' . (int) $user_id . ' (' . htmlsafechars($wantusername) . ') was created');

if ($user_id > 2 && 1 == $site_config['autoshout_on']) {
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

    $mail = new Message();
    $mail->setFrom("{$site_config['site_email']}", "{$site_config['chatBotName']}")
        ->addTo($email)
        ->setReturnPath($site_config['site_email'])
        ->setSubject("{$site_config['site_name']} {$lang['takesignup_confirm']}")
        ->setHtmlBody($body);

    $mailer              = new SendmailMailer();
    $mailer->commandArgs = "-f{$site_config['site_email']}";
    $mailer->send($mail);
}

if ($site_config['auto_confirm']) {
    clearUserCache($user_id);
    $session->set('userID', $user_id);
}

$session->unset('signup_variables');
header("Location: {$site_config['baseurl']}/ok.php?type=" . (0 === $users_count ? 'sysop' : ($site_config['email_confirm'] ? 'signup&email=' . urlencode($email) : 'confirm')));
