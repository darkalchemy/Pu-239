<?php

require_once INCL_DIR . 'user_functions.php';
require_once INCL_DIR . 'html_functions.php';
global $CURUSER, $site_config, $lang, $cache, $user_stuffs, $message_stuffs;

use Nette\Mail\Message;
use Nette\Mail\SendmailMailer;

$subject = $msg = '';
flood_limit('messages');

if (isset($_POST['buttonval']) && $_POST['buttonval'] == $lang['pm_send_btn']) {
    $receiver = isset($_POST['receiver']) ? intval($_POST['receiver']) : 0;
    $subject = htmlsafechars($_POST['subject']);
    $msg = trim($_POST['body']);
    $save = isset($_POST['save']) && $_POST['save'] === 1 ? 'yes' : 'no';
    $delete = isset($_POST['delete']) && $_POST['delete'] !== 0 ? intval($_POST['delete']) : 0;
    $urgent = isset($_POST['urgent']) && $_POST['urgent'] === 'yes' && $CURUSER['class'] >= UC_STAFF ? 'yes' : 'no';
    $returnto = htmlsafechars(isset($_POST['returnto']) ? $_POST['returnto'] : '');
    $arr_receiver = $user_stuffs->getUserFromId($receiver);

    if (!is_valid_id(intval($_POST['receiver'])) || !is_valid_id($arr_receiver['id'])) {
        stderr($lang['pm_error'], $lang['pm_send_not_found']);
    }
    if (!isset($_POST['body'])) {
        stderr($lang['pm_error'], $lang['pm_send_nobody']);
    }
    if ($CURUSER['suspended'] === 'yes') {
        $row = $user_stuffs->getUserFromId($receiver);
        if ($row['class'] < UC_STAFF) {
            stderr($lang['pm_error'], $lang['pm_send_your_acc']);
        }
    }
    $count = $message_stuffs->get_count($receiver, 1);
    if ($count > ($maxbox * 6) && $CURUSER['class'] < UC_STAFF) {
        stderr($lang['pm_forwardpm_srry'], $lang['pm_forwardpm_full']);
    }

    if ($CURUSER['class'] < UC_STAFF) {
        $should_i_send_this = ($arr_receiver['acceptpms'] === 'yes' ? 'yes' : ($arr_receiver['acceptpms'] === 'no' ? 'no' : ($arr_receiver['acceptpms'] === 'friends' ? 'friends' : '')));
        switch ($should_i_send_this) {
            case 'yes':
                $r = sql_query('SELECT id FROM blocks WHERE userid = ' . sqlesc($receiver) . ' AND blockid = ' . sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);
                $block = mysqli_fetch_row($r);
                if ($block[0] > 0) {
                    stderr($lang['pm_forwardpm_refused'], htmlsafechars($arr_receiver['username']) . $lang['pm_send_blocked']);
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
                stderr($lang['pm_forwardpm_refused'], htmlsafechars($arr_receiver['username']) . $lang['pm_send_doesnt']);
                break;
        }
    }
    $msgs_buffer[] = [
        'sender' => $CURUSER['id'],
        'poster' => $CURUSER['id'],
        'receiver' => $receiver,
        'added' => TIME_NOW,
        'msg' => $msg,
        'subject' => $subject,
        'saved' => $save,
        'location' => 1,
        'urgent' => $urgent,
    ];
    $message_stuffs->insert($msgs_buffer);
    if (strpos($arr_receiver['notifs'], '[pm]') !== false) {
        $username = htmlsafechars($CURUSER['username']);
        $title = $site_config['site_name'];
        $msg = doc_head() . "
    <meta property='og:title' content='{$title}'>
    <title>{$title} PM received</title>
</head>
<body>
<p>{$lang['pm_forwardpm_pmfrom']} $username!</p>
<p>{$lang['pm_forwardpm_url']}</p>
<p>{$site_config['baseurl']}/messages.php</p>
<p>--{$site_config['site_name']}</p>
</body>
</html>";

        $mail = new Message();
        $mail->setFrom("{$site_config['site_email']}", "{$site_config['chatBotName']}")
            ->addTo($arr_receiver['email'])
            ->setReturnPath($site_config['site_email'])
            ->setSubject("{$lang['pm_forwardpm_pmfrom']} $username!")
            ->setHtmlBody($msg);

        $mailer = new SendmailMailer();
        $mailer->commandArgs = "-f{$site_config['site_email']}";
        $mailer->send($mail);
    }
    if ($delete != 0) {
        $message = $message_stuffs->get_buy_id($delete);
        if ($message) {
            $arr = mysqli_fetch_assoc($res);
            if ($message['receiver'] != $CURUSER['id']) {
                stderr($lang['pm_send_quote'], $lang['pm_send_thou']);
            }
            if ($message['saved'] === 'no') {
                $message_stuffs->delete($delete, $message['receiver']);
            } elseif ($message['saved'] === 'yes') {
                $values = [
                    'location' => 0,
                ];
                $message_stuffs->update($values, $delete);
            }
        }
    }
    if ($returnto) {
        header('Location: ' . $returnto);
    } else {
        header('Location: messages.php?action=view_mailbox&sent=1');
    }
    die();
}

$receiver = (isset($_GET['receiver']) ? intval($_GET['receiver']) : (isset($_POST['receiver']) ? intval($_POST['receiver']) : null));
$replyto = (isset($_GET['replyto']) ? intval($_GET['replyto']) : (isset($_POST['replyto']) ? intval($_POST['replyto']) : 0));
$returnto = htmlsafechars(isset($_POST['returnto']) ? $_POST['returnto'] : '');
if ($receiver && !is_valid_id($receiver)) {
    stderr($lang['pm_error'], $lang['pm_send_mid']);
}
if ($receiver) {
    $arr_member = $user_stuffs->getUserFromId($receiver);
}

if ($replyto != 0) {
    if (!valid_username($arr_member['username'])) {
        stderr($lang['pm_error'], $lang['pm_send_mid']);
    }

    $message = $message_stuffs->get_by_id($replyto);
    if ($message['sender'] == $CURUSER['id']) {
        stderr($lang['pm_error'], $lang['pm_send_slander']);
    }
    if ($message['receiver'] == $CURUSER['id']) {
        $msg .= "\n\n\n{$lang['pm_send_wrote0']}{$arr_member['username']}{$lang['pm_send_wrote']}\n{$arr_old_message['msg']}\n";
        $subject = $lang['pm_send_re'] . htmlsafechars($arr_old_message['subject']);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject = trim($_POST['subject']);
    $msg = trim($_POST['body']);
}

$HTMLOUT .= $top_links . "
    <form name='compose' method='post' action='messages.php'>
        <input type='hidden' name='action' value='send_message'>";

if ($receiver) {
    $HTMLOUT .= '
        <input type="hidden" name="returnto" value="' . $returnto . '">
        <input type="hidden" name="replyto" value="' . $replyto . '">
        <input type="hidden" name="receiver" value="' . $receiver . '">
        <h1>Send ' . $lang['pm_send_msgto'] . format_username($receiver) . '</h1>';
} else {
    $HTMLOUT .= "
        <input type='hidden' name='returnto' value='$returnto'>
        <input type='hidden' name='replyto' value='$replyto'>
        <input type='hidden' name='receiver' id='receiver' value=''>
        <h1>
            Send {$lang['pm_send_msgto']}
            <input type='text' id='user_search' class='w-50' data-csrf='" . $session->get('csrf_token') . "' placeholder='Begin typing username' onkeyup='usersearch()'>
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
                <td><span style="font-weight: bold;">' . $lang['pm_send_subject'] . '</span></td>
                <td><input name="subject" type="text" class="w-100" value="' . $subject . '"></td>
            </tr>
            <tr class="no_hover">
                <td><span style="font-weight: bold;">' . $lang['pm_send_body'] . '</span></td>
                <td class="is-paddingless">' . BBcode($msg) . '</td>
            </tr>
            <tr class="no_hover">
                <td colspan="2">
                    <div class="has-text-centered">
                        ' . ($CURUSER['class'] >= UC_STAFF ? '
                        <input type="checkbox" name="urgent" value="yes" ' . ((isset($_POST['urgent']) && $_POST['urgent'] === 'yes') ? ' checked' : '') . '> 
                        <span class="right10">' . $lang['pm_send_mark'] . '</span>' : '');
if ($replyto) {
    $HTMLOUT .= '
                        <input type="checkbox" name="delete" value="' . $replyto . '"' . ($CURUSER['deletepms'] === 'yes' ? ' checked' : '') . '>' . $lang['pm_send_delete'];
}
$disabled = empty($receiver) && empty($returnto) ? ' disabled' : '';
$HTMLOUT .= '
                        <input type="checkbox" name="save" value="1" checked>' . $lang['pm_send_savepm'] . '
                    </div>
                    <div class="has-text-centered">
                        <input type="submit" class="button is-small" id="button" name="buttonval" value="' . ((isset($_POST['draft']) && $_POST['draft'] == 1) ? $lang['pm_send_save'] : $lang['pm_send_btn']) . '"' . $disabled . '>
                    </div>
                </td>
            </tr>
        </table>
    </form>';
