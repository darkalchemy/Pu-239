<?php

declare(strict_types = 1);

use Pu239\Message;
use Pu239\User;

global $container, $CURUSER, $site_config;

$body = '';
$messages_class = $container->get(Message::class);
$message = $messages_class->get_by_id($pm_id);

if ($message['sender'] == $CURUSER['id'] && $message['sender'] == $CURUSER['id'] || empty($message)) {
    stderr(_('Error'), _('Come, you are a tedious fool.'));
}

if ($message['sender'] !== $CURUSER['id']) {
    $users_class = $container->get(User::class);
    $for_username = $users_class->get_item('username', $message['sender']);
    $forwarded_username = ($message['sender'] === 0 ? _('System') : (!$for_username ? _('Un-known') : htmlsafechars($for_username)));
} else {
    $forwarded_username = htmlsafechars($CURUSER['username']);
}

$HTMLOUT .= '<h1>' . _('Fwd: ') . '' . htmlsafechars($message['subject']) . '</h1>
        <form action="messages.php" method="post" accept-charset="utf-8">
        <input type="hidden" name="id" value="' . $pm_id . '">
        <input type="hidden" name="action" value="forward_pm">
    <table class="table table-bordered">
    <tr>
        <td colspan="2" class="colhead"><h1>' . _('forward message ') . '
        <img src="' . $site_config['paths']['images_baseurl'] . 'arrow_next.gif" alt=":">' . _('Fwd: ') . '' . htmlsafechars($message['subject']) . '</h1></td>
    </tr>
    <tr>
        <td><span>' . _('To:') . '</span></td>
        <td><input type="text" name="to" value="' . _('Enter Username') . '" class="member" onfocus="this.value=\'\';"></td>
    </tr>
    <tr>
        <td><span>' . _('Orignal Sender:') . '</span></td>
        <td><span>' . $forwarded_username . '</span></td>
    </tr>
    <tr>
        <td><span>' . _('From:') . '</span></td>
        <td><span>' . $CURUSER['username'] . '</span></td>
    </tr>
    <tr>
        <td><span>' . _('Subject:') . '</span></td>
        <td><input type="text" class="w-100" name="subject" value="' . _('Fwd: ') . '' . htmlsafechars($message['subject']) . '"></td>
    </tr>
    <tr>
        <td></td>
        <td>' . _('-------- Original Message from ') . '' . $forwarded_username . '' . _(': --------') . '<br>' . format_comment($message['msg']) . '</td>
    </tr>
    <tr>
        <td></td>
        <td><span>' . _('You can add your own message, it will appear above the PM being forwarded.') . '</span></td>
    </tr>
    <tr>
        <td><span>' . _('Message:') . '</span></td>
        <td class="is-paddingless">' . BBcode($body) . '</td>
    </tr>
    <tr>
        <td colspan="2">' . ($CURUSER['class'] >= UC_STAFF ? '<span class="label label-danger">' . _('Mark as URGENT!') . '</span>
        <input type="checkbox" name="urgent" value="yes"> ;' : '') . '' . _(' Save Message ') . '
        <input type="checkbox" name="save" value="1">
        <input type="hidden" name="first_from" value="' . $forwarded_username . '">
        <input type="submit" class="button is-small" name="move" value="' . _('Forward') . '"></td>
    </tr>
    </table></form>';
