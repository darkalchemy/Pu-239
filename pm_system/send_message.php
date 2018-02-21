<?php
global $CURUSER, $site_config, $lang;

$cache = new Cache();

use Nette\Mail\Message;
use Nette\Mail\SendmailMailer;

$subject = $body = '';
flood_limit('messages');

if (isset($_POST['buttonval']) && $_POST['buttonval'] == $lang['pm_send_btn']) {
    $receiver = sqlesc(isset($_POST['receiver']) ? intval($_POST['receiver']) : 0);
    $subject = sqlesc(htmlsafechars($_POST['subject']));
    $body = sqlesc(trim($_POST['body']));
    $save = isset($_POST['save']) && $_POST['save'] === 1 ? 'yes' : 'no';
    $delete = sqlesc((isset($_POST['delete']) && $_POST['delete'] !== 0) ? intval($_POST['delete']) : 0);
    $urgent = sqlesc((isset($_POST['urgent']) && $_POST['urgent'] === 'yes' && $CURUSER['class'] >= UC_STAFF) ? 'yes' : 'no');
    $returnto = htmlsafechars(isset($_POST['returnto']) ? $_POST['returnto'] : '');
    //$returnto = htmlsafechars($_POST['returnto']);

    $res_receiver = sql_query('SELECT id, acceptpms, notifs, email, class, username FROM users WHERE id=' . sqlesc($receiver)) or sqlerr(__FILE__, __LINE__);
    $arr_receiver = mysqli_fetch_assoc($res_receiver);
    if (!is_valid_id(intval($_POST['receiver'])) || !is_valid_id($arr_receiver['id'])) {
        stderr($lang['pm_error'], $lang['pm_send_not_found']);
    }
    if (!isset($_POST['body'])) {
        stderr($lang['pm_error'], $lang['pm_send_nobody']);
    }
    if ($CURUSER['suspended'] === 'yes') {
        $res = sql_query('SELECT class FROM users WHERE id = ' . sqlesc($receiver)) or sqlerr(__FILE__, __LINE__);
        $row = mysqli_fetch_assoc($res);
        if ($row['class'] < UC_STAFF) {
            stderr($lang['pm_error'], $lang['pm_send_your_acc']);
        }
    }
    $res_count = sql_query('SELECT COUNT(*) FROM messages WHERE receiver = ' . sqlesc($receiver) . ' AND location = 1') or sqlerr(__FILE__, __LINE__);
    $arr_count = mysqli_fetch_row($res_count);
    if (mysqli_num_rows($res_count) > ($maxbox * 6) && $CURUSER['class'] < UC_STAFF) {
        stderr($lang['pm_forwardpm_srry'], $lang['pm_forwardpm_full']);
    }

    if ($CURUSER['class'] < UC_STAFF) {
        $should_i_send_this = ($arr_receiver['acceptpms'] == 'yes' ? 'yes' : ($arr_receiver['acceptpms'] == 'no' ? 'no' : ($arr_receiver['acceptpms'] == 'friends' ? 'friends' : '')));
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
    sql_query('INSERT INTO messages (poster, sender, receiver, added, msg, subject, saved, location, urgent) VALUES 
                            (' .
              sqlesc($CURUSER['id']) . ', ' .
              sqlesc($CURUSER['id']) . ', ' .
              sqlesc($receiver) . ', ' .
              TIME_NOW . ', ' .
              $body . ', ' .
              $subject . ', ' .
              sqlesc($save) . ', 
        1,' .
              $urgent . ')') or sqlerr(__FILE__, __LINE__);
    $cache->increment('inbox_' . $receiver);
    if (mysqli_affected_rows($GLOBALS['___mysqli_ston']) === 0) {
        stderr($lang['pm_error'], $lang['pm_send_wasnt']);
    }
    if (strpos($arr_receiver['notifs'], '[pm]') !== false) {
        $username = htmlsafechars($CURUSER['username']);
        $body = "<html>
<head>
    <meta http-equiv='Content-Type' content='text/html; charset=utf-8'>
    <title>{$site_config['site_name']} PM received</title>
</head>
<body>
<p>{$lang['pm_forwardpm_pmfrom']} $username!</p>
<p>{$lang['pm_forwardpm_url']}</p>
<p>{$site_config['baseurl']}/pm_system.php</p>
<p>--{$site_config['site_name']}</p>
</body>
</html>";

        $mail = new Message;
        $mail->setFrom("{$site_config['site_email']}", "{$site_config['chatBotName']}")
            ->addTo($arr_receiver['email'])
            ->setReturnPath($site_config['site_email'])
            ->setSubject("{$lang['pm_forwardpm_pmfrom']} $username!")
            ->setHtmlBody($body);

        $mailer = new SendmailMailer;
        $mailer->commandArgs = "-f{$site_config['site_email']}";
        $mailer->send($mail);
    }
    if ($delete != 0) {
        $res = sql_query('SELECT saved, receiver FROM messages WHERE id=' . sqlesc($delete)) or sqlerr(__FILE__, __LINE__);
        if (mysqli_num_rows($res) > 0) {
            $arr = mysqli_fetch_assoc($res);
            if ($arr['receiver'] != $CURUSER['id']) {
                stderr($lang['pm_send_quote'], $lang['pm_send_thou']);
            }
            if ($arr['saved'] == 'no') {
                sql_query('DELETE FROM messages WHERE id = ' . sqlesc($delete)) or sqlerr(__FILE__, __LINE__);
            } elseif ($arr['saved'] == 'yes') {
                sql_query('UPDATE messages SET location = 0 WHERE id = ' . sqlesc($delete)) or sqlerr(__FILE__, __LINE__);
            }
        }
    }
    if ($returnto) {
        header('Location: ' . $returnto);
    } else {
        header('Location: pm_system.php?action=view_mailbox&sent=1');
    }
    die();
}

$receiver = (isset($_GET['receiver']) ? intval($_GET['receiver']) : (isset($_POST['receiver']) ? intval($_POST['receiver']) : null));
$replyto = (isset($_GET['replyto']) ? intval($_GET['replyto']) : (isset($_POST['replyto']) ? intval($_POST['replyto']) : 0));
$returnto = htmlsafechars(isset($_POST['returnto']) ? $_POST['returnto'] : '');
if (!$receiver) {
    $all_users = $cache->get('all_users_');
    if ($all_users === false || is_null($all_users)) {
        $sql = "SELECT id, username, class FROM users WHERE acceptpms != 'no' ORDER BY LOWER(username)";
        $res = sql_query($sql) or sqlerr(__FILE__, __LINE__);
        $ids = [];
        while ($all_user = mysqli_fetch_assoc($res)) {
            $all_users[] = $all_user;
        }
        $cache->set('all_users_', $all_users, 86400);
    }
}
if ($receiver && !is_valid_id($receiver)) {
    stderr($lang['pm_error'], $lang['pm_send_mid']);
}
if ($receiver) {
    $res_member = sql_query('SELECT username FROM users WHERE id = ' . sqlesc($receiver)) or sqlerr(__FILE__, __LINE__);
    $arr_member = mysqli_fetch_row($res_member);
}

if ($replyto != 0) {
    if (!valid_username($arr_member[0])) {
        stderr($lang['pm_error'], $lang['pm_send_mid']);
    }

    $res_old_message = sql_query('SELECT receiver, sender, subject, msg FROM messages WHERE id = ' . sqlesc($replyto)) or sqlerr(__FILE__, __LINE__);
    $arr_old_message = mysqli_fetch_assoc($res_old_message);

    if ($arr_old_message['sender'] == $CURUSER['id']) {
        stderr($lang['pm_error'], $lang['pm_send_slander']);
    }
    if ($arr_old_message['receiver'] == $CURUSER['id']) {
        $body .= "\n\n\n{$lang['pm_send_wrote0']}$arr_member[0]{$lang['pm_send_wrote']}\n$arr_old_message[msg]\n";
        $subject = $lang['pm_send_re'] . htmlsafechars($arr_old_message['subject']);
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $subject = trim($_POST['subject']);
    $body = trim($_POST['body']);
}

$avatar = (($CURUSER['avatars'] === 'no') ? '' : (empty($CURUSER['avatar']) ? '
        <img width="80" src="' . $site_config['pic_baseurl'] . 'forumicons/default_avatar.gif" alt="no avatar" />' : (($CURUSER['offensive_avatar'] === 'yes' && $CURUSER['view_offensive_avatar'] === 'no') ? '<img width="80" src="' . $site_config['pic_baseurl'] . 'fuzzybunny.gif" alt="fuzzy!" />' : '<img width="80" src="' . htmlsafechars($CURUSER['avatar']) . '" alt="avatar" />')));

$HTMLOUT .= $top_links . '
    <form name="compose" method="post" action="pm_system.php">
        <input type="hidden" name="action" value="send_message" />';
if ($receiver) {
    $HTMLOUT .= '
        <input type="hidden" name="returnto" value="' . $returnto . '" />
        <input type="hidden" name="replyto" value="' . $replyto . '" />
        <input type="hidden" name="receiver" value="' . $receiver . '" />
        <h1>' . $lang['pm_send_msgto'] . '<a class="altlink" href="' . $site_config['baseurl'] . '/userdetails.php?id=' . $receiver . '">' . $arr_member[0] . '</a></h1>';
} else {
    $HTMLOUT .= "
        <input type='hidden' name='returnto' value='$returnto' />
        <input type='hidden' name='replyto' value='$replyto' />
        <h1>{$lang['pm_send_msgto']}<select name='receiver'>";
    foreach ($all_users as $all_user) {
        if ($CURUSER['id'] != $all_user['id']) {
            $HTMLOUT .= "
        <option value='{$all_user['id']}' class='" . get_user_class_name($all_user['class'], true) . "'>{$all_user['username']}</option>";
        }
    }

    $HTMLOUT .= '
        </select></h1>';
}
$HTMLOUT .= '
        <table class="table table-bordered">
            <tr class="no_hover">
                <td colspan="2" class="colhead">' . $lang['pm_send_sendmsg'] . '</td>
            </tr>
            <tr class="no_hover">
                <td><span style="font-weight: bold;">' . $lang['pm_send_subject'] . '</span></td>
                <td><input name="subject" type="text" class="text_default" value="' . $subject . '" /></td>
            </tr>
            <tr class="no_hover">
                <td><span style="font-weight: bold;">' . $lang['pm_send_body'] . '</span></td>
                <td>' . BBcode($body) . '</td>
            </tr>
            <tr class="no_hover">
                <td colspan="2">
                    <div class="has-text-centered">
                        ' . ($CURUSER['class'] >= UC_STAFF ? '
                        <input type="checkbox" name="urgent" value="yes" ' . ((isset($_POST['urgent']) && $_POST['urgent'] === 'yes') ? ' checked' : '') . ' /> 
                        <span class="right10">' . $lang['pm_send_mark'] . '</span>' : '');
if ($replyto) {
    $HTMLOUT .= '
                        <input type="checkbox" name="delete" value="' . $replyto . '"' . ($CURUSER['deletepms'] == 'yes' ? ' checked' : '') . ' />' . $lang['pm_send_delete'];
}
$HTMLOUT .= '
                        <input type="checkbox" name="save" value="1" checked />' . $lang['pm_send_savepm'] . '
                    </div>
                    <div class="has-text-centered">
                        <input type="submit" class="button is-small" name="buttonval" value="' . ((isset($_POST['draft']) && $_POST['draft'] == 1) ? $lang['pm_send_save'] : $lang['pm_send_btn']) . '" />
                    </div>
                </td>
            </tr>
        </table>
    </form>';
