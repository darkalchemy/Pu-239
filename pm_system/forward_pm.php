<?php
global $CURUSER;

use Nette\Mail\Message;
use Nette\Mail\SendmailMailer;

$res = sql_query('SELECT * FROM messages WHERE id = ' . sqlesc($pm_id)) or sqlerr(__FILE__, __LINE__);
$message = mysqli_fetch_assoc($res);
if (mysqli_num_rows($res) === 0) {
    stderr($lang['pm_error'], $lang['pm_forwardpm_notfound']);
}
if ($message['receiver'] == $CURUSER['id'] && $message['sender'] == $CURUSER['id']) {
    stderr($lang['pm_error'], $lang['pm_forwardpm_gentleman']);
}

$res_username = sql_query('SELECT id, class, acceptpms, notifs, email FROM users WHERE LOWER(username) = LOWER(' . sqlesc(htmlsafechars($_POST['to'])) . ') LIMIT 1');
$to_username = mysqli_fetch_assoc($res_username);
if (mysqli_num_rows($res_username) === 0) {
    stderr($lang['pm_error'], $lang['pm_forwardpm_nomember']);
}

$res_count = sql_query('SELECT COUNT(id) FROM messages WHERE receiver = ' . sqlesc($to_username['id']) . ' AND location = 1') or sqlerr(__FILE__, __LINE__);
if (mysqli_num_rows($res_count) > ($maxbox * 6) && $CURUSER['class'] < UC_STAFF) {
    stderr($lang['pm_forwardpm_srry'], $lang['pm_forwardpm_full']);
}

if ($CURUSER['suspended'] === 'yes') {
    $res = sql_query('SELECT class FROM users WHERE id = ' . sqlesc($to_username['id'])) or sqlerr(__FILE__, __LINE__);
    $row = mysqli_fetch_assoc($res);
    if ($row['class'] < UC_STAFF) {
        stderr($lang['pm_error'], $lang['pm_forwardpm_account']);
    }
}

if ($CURUSER['class'] < UC_STAFF) {
    if ($to_username['acceptpms'] === 'no') {
        stderr($lang['pm_error'], $lang['pm_forwardpm_dont_accept']);
    }
    $res2 = sql_query('SELECT id FROM blocks WHERE userid=' . sqlesc($to_username['id']) . ' AND blockid=' . sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);
    if (mysqli_num_rows($res2) === 1) {
        stderr($lang['pm_forwardpm_refused'], $lang['pm_forwardpm_blocked']);
    }
    if ($to_username['acceptpms'] === 'friends') {
        $res2 = sql_query('SELECT * FROM friends WHERE userid=' . sqlesc($to_username['id']) . ' AND friendid=' . sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);
        if (mysqli_num_rows($res2) != 1) {
            stderr($lang['pm_forwardpm_refused'], $lang['pm_forwardpm_accept']);
        }
    }
}

$subject = htmlsafechars($_POST['subject']);
$first_from = (valid_username($_POST['first_from']) ? htmlsafechars($_POST['first_from']) : '');
$body = "\n\n" . $_POST['body'] . "\n\n{$lang['pm_forwardpm_0']}[b]" . $first_from . "{$lang['pm_forwardpm_1']}[/b] \"" . htmlsafechars($message['subject']) . "\"{$lang['pm_forwardpm_2']}" . $message['msg'] . "\n";
sql_query('INSERT INTO `messages` (`sender`, `receiver`, `added`, `subject`, `msg`, `unread`, `location`, `saved`, `poster`, `urgent`) 
                        VALUES (' . sqlesc($CURUSER['id']) . ', ' . sqlesc($to_username['id']) . ', ' . TIME_NOW . ', ' . sqlesc($subject) . ', ' . sqlesc($body) . ', \'yes\', 1, ' . sqlesc($save) . ', 0, ' . sqlesc($urgent) . ')') or sqlerr(__FILE__, __LINE__);
$cache->increment('inbox_' . $to_username['id']);
if (mysqli_affected_rows($GLOBALS['___mysqli_ston']) === 0) {
    stderr($lang['pm_error'], $lang['pm_forwardpm_msg_fwd']);
}

if (strpos($to_username['notifs'], '[pm]') !== false) {
    $username = htmlsafechars($CURUSER['username']);
    $body = "<html>
<head>
    <meta http-equiv='Content-Type' content='text/html; charset=utf-8'>
    <title>{$site_config['site_name']} PM received</title>
</head>
<body>
<p>{$lang['pm_forwardpm_pmfrom']} $username{$lang['pm_forwardpm_exc']}</p>
<p>{$lang['pm_forwardpm_url']}</p>
<p>{$site_config['baseurl']}/pm_system.php</p>
<p>--{$site_config['site_name']}</p>
</body>
</html>";

    $mail = new Message;
    $mail->setFrom("{$site_config['site_email']}", "{$site_config['chatBotName']}")
        ->addTo($to_username['email'])
        ->setReturnPath($site_config['site_email'])
        ->setSubject("{$lang['pm_forwardpm_pmfrom']} $username {$lang['pm_forwardpm_exc']}")
        ->setHtmlBody($body);

    $mailer = new SendmailMailer;
    $mailer->commandArgs = "-f{$site_config['site_email']}";
    $mailer->send($mail);
}
header('Location: pm_system.php?action=view_mailbox&forwarded=1');
die();
