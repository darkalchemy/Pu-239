<?php

require_once INCL_DIR . 'function_html.php';
global $CURUSER, $message_stuffs, $user_stuffs, $fluent, $site_config;

use Nette\Mail\Message;
use Nette\Mail\SendmailMailer;

flood_limit('messages');
$message = $message_stuffs->get_by_id($pm_id);
if (empty($message)) {
    stderr($lang['pm_error'], $lang['pm_forwardpm_notfound']);
}
if ($message['receiver'] == $CURUSER['id'] && $message['sender'] == $CURUSER['id']) {
    stderr($lang['pm_error'], $lang['pm_forwardpm_gentleman']);
}

$to_user = $user_stuffs->getUserFromId($user_stuffs->getUserIdFromName($_POST['to']));
if (empty($to_user)) {
    stderr($lang['pm_error'], $lang['pm_forwardpm_nomember']);
}

$count = $message_stuffs->get_count($to_user['id'], 1);
if ($count > ($maxbox * 6) && $CURUSER['class'] < UC_STAFF) {
    stderr($lang['pm_forwardpm_srry'], $lang['pm_forwardpm_full']);
}

if ($CURUSER['suspended'] === 'yes') {
    if ($to_user['class'] < UC_STAFF) {
        stderr($lang['pm_error'], $lang['pm_forwardpm_account']);
    }
}

if ($CURUSER['class'] < UC_STAFF) {
    if ($to_user['acceptpms'] === 'no') {
        stderr($lang['pm_error'], $lang['pm_forwardpm_dont_accept']);
    }
    $blocked = $fluent->from('blocks')
                      ->select(null)
                      ->select('id')
                      ->where('userid=?', $to_user['id'])
                      ->where('blockid=?', $CURUSER['id'])
                      ->fetch();
    if (!$blocked) {
        stderr($lang['pm_forwardpm_refused'], $lang['pm_forwardpm_blocked']);
    }
    if ($to_user['acceptpms'] === 'friends') {
        $friend = $fluent->from('friends')
                         ->select(null)
                         ->select('id')
                         ->where('userid=?', $to_user['id'])
                         ->where('friendid=?', $CURUSER['id'])
                         ->fetch();
        if (!$friend) {
            stderr($lang['pm_forwardpm_refused'], $lang['pm_forwardpm_accept']);
        }
    }
}

$subject = htmlsafechars($_POST['subject']);
$first_from = (valid_username($_POST['first_from']) ? htmlsafechars($_POST['first_from']) : '');
$msg = "\n\n" . $_POST['body'] . "\n\n{$lang['pm_forwardpm_0']}[b]" . $first_from . "{$lang['pm_forwardpm_1']}[/b] \"" . htmlsafechars($message['subject']) . "\"{$lang['pm_forwardpm_2']}" . $message['msg'] . "\n";

$msgs_buffer[] = [
    'sender' => $CURUSER['id'],
    'poster' => $CURUSER['id'],
    'receiver' => $to_user['id'],
    'added' => TIME_NOW,
    'msg' => $msg,
    'subject' => $subject,
    'saved' => $save,
    'urgent' => $urgent,
];
$result = $message_stuffs->insert($msgs_buffer);
if (!$result) {
    stderr($lang['pm_error'], $lang['pm_forwardpm_msg_fwd']);
}

if (strpos($to_user['notifs'], '[pm]') !== false) {
    $username = htmlsafechars($CURUSER['username']);
    $title = $site_config['site']['name'];
    $body = doc_head() . "
    <meta property='og:title' content='{$title}'>
    <title>{$title} PM received</title>
</head>
<body>
<p>{$lang['pm_forwardpm_pmfrom']} $username{$lang['pm_forwardpm_exc']}</p>
<p>{$lang['pm_forwardpm_url']}</p>
<p>{$site_config['paths']['baseurl']}/messages.php</p>
<p>--{$site_config['site']['name']}</p>
</body>
</html>";

    $mail = new Message();
    $mail->setFrom("{$site_config['site']['email']}", "{$site_config['chatbot']['name']}")
         ->addTo($to_user['email'])
         ->setReturnPath($site_config['site']['email'])
         ->setSubject("{$lang['pm_forwardpm_pmfrom']} $username {$lang['pm_forwardpm_exc']}")
         ->setHtmlBody($body);

    $mailer = new SendmailMailer();
    $mailer->commandArgs = "-f{$site_config['site']['email']}";
    $mailer->send($mail);
}
header('Location: messages.php?action=view_mailbox&forwarded=1');
die();
