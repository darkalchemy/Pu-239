<?php

declare(strict_types = 1);

use Pu239\Message;

global $container, $CURUSER;

$subject = $draft = '';
if (!empty($_POST['buttonval']) && $_POST['buttonval'] === 'Save draft') {
    if (empty($_POST['subject'])) {
        stderr(_('Error'), _('To save a message in your draft folder, it must have a subject!'));
    }
    if (empty($_POST['body'])) {
        stderr(_('Error'), _('To save a message in your draft folder, it must have some body text!'));
    }
    $msg = $_POST['body'];
    $subject = strip_tags($_POST['subject']);

    $msgs_buffer[] = [
        'sender' => $CURUSER['id'],
        'poster' => $CURUSER['id'],
        'receiver' => $CURUSER['id'],
        'added' => TIME_NOW,
        'msg' => $msg,
        'subject' => $subject,
        'draft' => 'yes',
        'location' => -2,
        'saved' => 'yes',
        'unread' => 'no',
    ];
    $messages_class = $container->get(Message::class);
    $new_draft_id = $messages_class->insert($msgs_buffer);
    if (!$new_draft_id) {
        stderr(_('Error'), _("Message wasn't saved!"));
    }
    header('Location: ' . $_SERVER['PHP_SELF'] . '?action=view_message&new_draft=1&id=' . $new_draft_id);
    die();
}

$HTMLOUT .= $top_links . '
    <h1>' . _('New Draft') . '</h1>' . '
        <form name="compose" action="messages.php" method="post" accept-charset="utf-8">
        <input type="hidden" name="id" value="' . $pm_id . '">
        <input type="hidden" name="action" value="new_draft">
    <table class="table table-bordered">
    <tr>
        <td><span style="font-weight: bold;">' . _('Subject') . ':</span></td>
        <td><input type="text" class="w-100" name="subject" value="' . $subject . '"></td>
    </tr>
    <tr>
        <td><span style="font-weight: bold;">' . _('Body') . ':</span></td>
        <td class="is-paddingless">' . BBcode($draft) . '</td>
    </tr>
    <tr>
        <td colspan="2">
            <div class="has-text-centered">
                <input type="submit" class="button is-small" name="buttonval" value="' . _('Save Draft') . '">
            </div>
        </td>
    </tr>
    </table></form>';
