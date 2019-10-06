<?php

declare(strict_types = 1);

use Pu239\Message;

global $container, $CURUSER;

$save_or_edit = (isset($_POST['edit']) ? 'edit' : (isset($_GET['edit']) ? 'edit' : 'save'));
$messages_class = $container->get(Message::class);
if (isset($_POST['buttonval']) && $_POST['buttonval'] === 'Save as draft') {
    if (empty($_POST['subject'])) {
        stderr(_('Error'), _('To save a message in your draft folder, it must have a subject!'));
    }
    if (empty($_POST['body'])) {
        stderr(_('Error'), _('To save a message in your draft folder, it must have body text!'));
    }
    $dt = TIME_NOW;
    $msg = $_POST['body'];
    $subject = strip_tags($_POST['subject']);
    if ($save_or_edit === 'save') {
        $values = [
            'sender' => $CURUSER['id'],
            'receiver' => $CURUSER['id'],
            'added' => $dt,
            'msg' => $msg,
            'subject' => $subject,
            'location' => -2,
            'draft' => 'yes',
            'unread' => 'no',
            'saved' => 'yes',
        ];
        $result = $messages_class->insert($values);
    }
    if ($save_or_edit === 'edit') {
        $update = [
            'msg' => $msg,
            'subject' => $subject,
        ];
        $result = $messages_class->update($update, $pm_id);
    }
    if (!$result) {
        stderr(_('Error'), _("Draft wasn't saved!"));
    }
    header('Location: ' . $_SERVER['PHP_SELF'] . '?action=view_mailbox&box=-2&new_draft=1');
    die();
}

$message = $messages_class->get_by_id($pm_id);
$subject = htmlsafechars($message['subject']);
$draft = $message['msg'];

$HTMLOUT .= '
<legend>' . _('Save / Edit Draft:') . $subject . '</legend>' . $top_links . '
<form name="compose" action="messages.php" method="post" accept-charset="utf-8">
    <input type="hidden" name="id" value="' . $pm_id . '">
    <input type="hidden" name="' . $save_or_edit . '" value="1">
    <input type="hidden" name="action" value="save_or_edit_draft">
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
            <td colspan="2" class="has-text-centered">
                <input type="submit" class="button is-small" name="buttonval" value="' . _('Save as Draft') . '">
            </td>
        </tr>
    </table>
</form>';
