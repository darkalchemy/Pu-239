<?php

declare(strict_types = 1);

require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_html.php';

use Pu239\Message;
use Pu239\User;

$subject = $msg = '';
global $container, $CURUSER, $site_config;

$messages_class = $container->get(Message::class);
$users_class = $container->get(User::class);

if (isset($_POST['buttonval']) && $_POST['buttonval'] === _('Send')) {
    flood_limit('messages');
    $receiver = isset($_POST['receiver']) ? (int) $_POST['receiver'] : 0;
    $subject = htmlsafechars($_POST['subject']);
    $msg = trim($_POST['body']);
    $save = isset($_POST['save']) && (int) $_POST['save'] === 1 ? 'yes' : 'no';
    $delete = isset($_POST['delete']) && (int) $_POST['delete'] != 0 ? (int) $_POST['delete'] : 0;
    $urgent = isset($_POST['urgent']) && $_POST['urgent'] === 'yes' && $CURUSER['class'] >= UC_STAFF ? 'yes' : 'no';
    $returnto = htmlsafechars(isset($_POST['returnto']) ? $_POST['returnto'] : '');
    $arr_receiver = $users_class->getUserFromId($receiver);

    if (!is_valid_id((int) $_POST['receiver']) || !is_valid_id($arr_receiver['id'])) {
        stderr(_('Error'), _('Member not found!!!'));
    }
    if (!isset($_POST['body'])) {
        stderr(_('Error'), _('No body text... Please enter something to send!'));
    }
    if ($CURUSER['status'] === 5) {
        $row = $users_class->getUserFromId($receiver);
        if ($row['class'] < UC_STAFF) {
            stderr(_('Error'), _('Your account is suspended, you may only contact staff members!'));
        }
    }
    $count = $messages_class->get_count($receiver, 1, true);
    if ($count > ($maxbox * 6) && $CURUSER['class'] < UC_STAFF) {
        stderr(_('Sorry'), _('Members mailbox is full.'));
    }

    if ($CURUSER['class'] < UC_STAFF) {
        $should_i_send_this = $arr_receiver['acceptpms'] === 'yes' ? 'yes' : ($arr_receiver['acceptpms'] === 'no' ? 'no' : ($arr_receiver['acceptpms'] === 'friends' ? 'friends' : ''));
        switch ($should_i_send_this) {
            case 'yes':
                $r = sql_query('SELECT id FROM blocks WHERE userid = ' . sqlesc($receiver) . ' AND blockid = ' . sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);
                $block = mysqli_fetch_row($r);
                if ($block[0] > 0) {
                    stderr(_('Refused'), htmlsafechars($arr_receiver['username']) . _(' has blocked PMs from you.'));
                }
                break;

            case 'friends':
                $r = sql_query('SELECT id FROM friends WHERE userid = ' . sqlesc($receiver) . ' AND friendid = ' . sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);
                $friend = mysqli_fetch_row($r);
                if ($friend[0] == 0) {
                    stderr('Refused', htmlsafechars($arr_receiver['username']) . ' only accepts PMs from members in their friends list.');
                }
                break;

            case 'no':
                stderr(_('Refused'), htmlsafechars($arr_receiver['username']) . _(' does not accept PMs.'));
                break;
        }
    }
    $dt = TIME_NOW;
    $msgs_buffer[] = [
        'sender' => $CURUSER['id'],
        'poster' => $CURUSER['id'],
        'receiver' => $receiver,
        'added' => $dt,
        'msg' => $msg,
        'subject' => $subject,
        'saved' => $save,
        'location' => 1,
        'urgent' => $urgent,
    ];
    $messageg_id = $messages_class->insert($msgs_buffer, false);
    if (!empty($_FILES)) {
        require_once FORUM_DIR . 'attachment.php';
        // TODO replace simple timestamp with microtime(true) to ensure unique timestamp
        // or generate a uniquid and create a new field in messages
        $uploaded = upload_attachments($dt);
        $extension_error = $uploaded[0];
        $size_error = $uploaded[1];
    }
    if (!empty($messageg_id) && !empty($arr_receiver['notifs']) && strpos($arr_receiver['notifs'], '[pm]') !== false) {
        $username = htmlsafechars($CURUSER['username']);
        $title = $site_config['site']['name'];
        $msg = doc_head("{$title} PM Received") . '
</head>
<body>
<p>' . _('You have received a PM from ') . " $username!</p>
<p>" . _('You can use the URL below to view the message (you may have to login).') . "</p>
<p>{$site_config['paths']['baseurl']}/messages.php?action=view_message&id={$messageg_id}</p>
<p>--{$site_config['site']['name']}</p>
</body>
</html>";

        send_mail($arr_receiver['email'], _('You have received a PM from ') . " $username!", $msg, strip_tags($msg));
    }
    if ($delete != 0) {
        $set = [
            'location' => 0,
        ];
        $message = $messages_class->get_by_id($delete);
        if (!empty($message)) {
            if ($message['receiver'] != $CURUSER['id']) {
                stderr(_('Quote!'), _('Thou spongy prick-eared bag of guts!'));
            }
            if ($save != 'yes') {
                $messages_class->delete($delete, $message['receiver']);
                $messages_class->update($set, $delete);
            } else {
                $values = [
                    'location' => 0,
                ];
                $messages_class->update($values, $delete);
            }
        }
    }
    if ($returnto) {
        header('Location: ' . $returnto);
    } else {
        header('Location: ' . $_SERVER['PHP_SELF'] . '?action=view_mailbox&sent=1');
    }
    die();
}

$receiver = isset($_GET['receiver']) ? (int) $_GET['receiver'] : (isset($_POST['receiver']) ? (int) $_POST['receiver'] : 0);
$replyto = isset($_GET['replyto']) ? (int) $_GET['replyto'] : (isset($_POST['replyto']) ? (int) $_POST['replyto'] : 0);
$returnto = htmlsafechars(isset($_POST['returnto']) ? $_POST['returnto'] : '');
if ($receiver && !is_valid_id($receiver)) {
    stderr(_('Error'), _('No member with that ID!'));
}
$arr_member = [];
if ($receiver) {
    $arr_member = $users_class->getUserFromId($receiver);
}

if ($replyto != 0) {
    if (!valid_username($arr_member['username'])) {
        stderr(_('Error'), _('No member with that ID!'));
    }
    $message = $messages_class->get_by_id($replyto);
    if ($message['sender'] == $CURUSER['id']) {
        stderr(_('Error'), _('Invalid ID'));
    }
    if ($message['receiver'] == $CURUSER['id']) {
        $msg .= "\n\n\n-------- {$arr_member['username']} " . _('wrote') . ": --------\n{$message['msg']}\n";
        $subject = (!preg_match('#' . _('Re: ') . '#i', $message['subject']) ? _('Re: ') : '') . htmlsafechars($message['subject']);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject = trim($_POST['subject']);
    $msg = trim($_POST['body']);
}

$HTMLOUT .= $top_links . "
    <form name='compose' method='post' action='{$site_config['paths']['baseurl']}/messages.php' enctype='multipart/form-data' accept-charset='utf-8'>
        <input type='hidden' name='action' value='send_message'>";

if ($receiver) {
    $HTMLOUT .= '
        <input type="hidden" name="returnto" value="' . $returnto . '">
        <input type="hidden" name="replyto" value="' . $replyto . '">
        <input type="hidden" name="receiver" value="' . $receiver . '">
        <h1>Send ' . _('Message to ') . format_username((int) $receiver) . '</h1>';
} else {
    $HTMLOUT .= "
        <input type='hidden' name='returnto' value='$returnto'>
        <input type='hidden' name='replyto' value='$replyto'>
        <input type='hidden' name='receiver' id='receiver' value=''>
        <h1>
            Send " . _('Message to ') . "
            <input type='text' id='user_search' maxlength='64' class='w-50' placeholder='" . _('Begin typing username') . "' onkeyup='usersearch()'>
        </h1>";
}
$HTMLOUT .= "
        <div id='autocomplete' class='w-100 bottom10'>
            <div class='padding20 bg-00 round10 bordered autofill'>
                <div id='autocomplete_list' class='margin10'>
                </div>
            </div>
        </div>";

$HTMLOUT .= '
        <table class="table table-bordered">
            <tr class="no_hover">
                <td><span style="font-weight: bold;">' . _('Subject') . ':</span></td>
                <td><input name="subject" type="text" class="w-100" value="' . $subject . '"></td>
            </tr>
            <tr class="no_hover">
                <td><span style="font-weight: bold;">' . _('Body') . ':</span></td>
                <td class="is-paddingless">' . BBcode($msg) . '</td>
            </tr>
            <tr class="no_hover">
                <td colspan="2">
                    <div class="has-text-centered">
                        ' . ($CURUSER['class'] >= UC_STAFF ? '
                        <input type="checkbox" name="urgent" value="yes" ' . ((isset($_POST['urgent']) && $_POST['urgent'] === 'yes') ? 'checked' : '') . '> 
                        <span class="right10">' . _('Mark as URGENT!') . '</span>' : '');
if ($replyto) {
    $HTMLOUT .= '
                        <input type="checkbox" name="delete" value="' . $replyto . '" ' . ($CURUSER['deletepms'] === 'yes' ? 'checked' : '') . '>' . _('Delete PM');
}
$disabled = empty($receiver) && empty($returnto) ? ' disabled' : '';
$accepted_file_types = str_replace('|', ', ', $site_config['forum_config']['accepted_file_types']);
$HTMLOUT .= '
                        <input type="checkbox" name="save" value="1" checked>' . _('Save PM ') . '
                    </div>
                    <div class="level-center-center padding20">
                        <span class="size_6 right10">' . _('Add Attachments') . '</span>
                        <input type="file" size="30" name="attachment[]" accept="' . $accepted_file_types . '" multiple>
                    </div>
                    <div class="has-text-centered">
                        <input type="submit" class="button is-small" id="button" name="buttonval" value="' . ((isset($_POST['draft']) && $_POST['draft'] == 1) ? _('Save') : _('Send')) . '"' . $disabled . '>
                    </div>
                </td>
            </tr>
        </table>
    </form>';
