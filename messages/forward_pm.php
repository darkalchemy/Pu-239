<?php

declare(strict_types = 1);

require_once INCL_DIR . 'function_html.php';

use Pu239\Database;
use Pu239\Message;
use Pu239\User;

global $container, $CURUSER, $site_config;

flood_limit('messages');
$messages_class = $container->get(Message::class);
$message = $messages_class->get_by_id($pm_id);
$fluent = $container->get(Database::class);
if (empty($message)) {
    stderr(_('Error'), _('Message Not Found!'));
}
if ($message['receiver'] == $CURUSER['id'] && $message['sender'] == $CURUSER['id']) {
    stderr(_('Error'), _('He be as good a gentleman as the devil is, as Lucifer and Beelzebub himself.'));
}
$users_class = $container->get(User::class);
$to_user = $users_class->getUserFromId((int) $users_class->getUserIdFromName((string) $_POST['to']));
if (empty($to_user)) {
    stderr(_('Error'), _('Sorry, there is no member with that username.'));
}

$count = $messages_class->get_count($to_user['id'], 1, false);
if ($count > ($maxbox * 6) && !has_access($CURUSER['class'], UC_STAFF, '')) {
    stderr(_('Sorry'), _('Members mailbox is full.'));
}

if ($CURUSER['status'] === 5) {
    if (!has_access($to_user['class'], UC_STAFF, '')) {
        stderr(_('Error'), _('Your account is suspended, you may only forward PMs to staff!'));
    }
}

if (!has_access($CURUSER['class'], UC_STAFF, '')) {
    if ($to_user['acceptpms'] === 'no') {
        stderr(_('Error'), _("This user dosen't accept PMs."));
    }
    $blocked = $fluent->from('blocks')
                      ->select(null)
                      ->select('id')
                      ->where('userid = ?', $to_user['id'])
                      ->where('blockid = ?', $CURUSER['id'])
                      ->fetch();
    if (!$blocked) {
        stderr(_('Refused'), _('This member has blocked PMs from you.'));
    }
    if ($to_user['acceptpms'] === 'friends') {
        $friend = $fluent->from('friends')
                         ->select(null)
                         ->select('id')
                         ->where('userid = ?', $to_user['id'])
                         ->where('friendid = ?', $CURUSER['id'])
                         ->fetch();
        if (!$friend) {
            stderr(_('Refused'), _('This member only accepts PMs from members on their friends list.'));
        }
    }
}

$subject = htmlsafechars($_POST['subject']);
$first_from = valid_username($_POST['first_from']) ? htmlsafechars($_POST['first_from']) : '';
$msg = "\n\n" . $_POST['body'] . "\n\n" . _fe("-------- Original Message from [b]{0} :: [/b]{1}\n{3}", $first_from, htmlsafechars($message['subject']), $message['msg']);

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
$result = $messages_class->insert($msgs_buffer);
if (!$result) {
    stderr(_('Error'), _("Message couldn't be forwarded!"));
}

if (strpos($to_user['notifs'], '[pm]') !== false) {
    $username = htmlsafechars($CURUSER['username']);
    $title = $site_config['site']['name'];
    $body = doc_head("{$title} PM received") . '
</head>
<body>
<p>' . _fe('You have received a PM from %s!', $username) . '</p>
<p>' . _('You can use the URL below to view the message (you may have to login).') . "</p>
<p>{$site_config['paths']['baseurl']}/messages.php</p>
<p>--{$site_config['site']['name']}</p>
</body>
</html>";

    send_mail($to_user['email'], _fe('You have received a PM from {0}!', $username), $body, strip_tags($body));
}
header('Location: ' . $_SERVER['PHP_SELF'] . '?action=view_mailbox&forwarded=1');
die();
