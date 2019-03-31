<?php

global $message_stuffs, $user_stuffs, $CURUSER;

$body = '';
$message = $message_stuffs->get_by_id($pm_id);

if ($message['sender'] == $CURUSER['id'] && $message['sender'] == $CURUSER['id'] || empty($message)) {
    stderr($lang['pm_error'], $lang['pm_forward_err']);
}

if ($message['sender'] !== $CURUSER['id']) {
    $for_username = $user_stuffs->get_item('username', $message['sender']);
    $forwarded_username = ($message['sender'] === 0 ? $lang['pm_forward_system'] : (!$for_username ? $lang['pm_forward_unknow'] : htmlsafechars($for_username)));
} else {
    $forwarded_username = htmlsafechars($CURUSER['username']);
}

$HTMLOUT .= '<h1>' . $lang['pm_forward_fwd'] . '' . htmlsafechars($message['subject']) . '</h1>
        <form action="messages.php" method="post" accept-charset="utf-8">
        <input type="hidden" name="id" value="' . $pm_id . '">
        <input type="hidden" name="action" value="forward_pm">
    <table class="table table - bordered">
    <tr>
        <td colspan="2" class="colhead"><h1>' . $lang['pm_forward_fwd_msg'] . '
        <img src="' . $site_config['pic_baseurl'] . 'arrow_next . gif" alt=":">' . $lang['pm_forward_fwd'] . '' . htmlsafechars($message['subject']) . '</h1></td>
    </tr>
    <tr>
        <td><span>' . $lang['pm_forward_to'] . '</span></td>
        <td><input type="text" name="to" value="' . $lang['pm_forward_user'] . '" class="member" onfocus="this . value = \'\';"></td>
    </tr>
    <tr>
        <td><span>' . $lang['pm_forward_original'] . '</span></td>
        <td><span>' . $forwarded_username . '</span></td>
    </tr>
    <tr>
        <td><span>' . $lang['pm_forward_from'] . '</span></td>
        <td><span>' . $CURUSER['username'] . '</span></td>
    </tr>
    <tr>
        <td><span>' . $lang['pm_forward_subject'] . '</span></td>
        <td><input type="text" class="w-100" name="subject" value="' . $lang['pm_forward_fwd'] . '' . htmlsafechars($message['subject']) . '"></td>
    </tr>
    <tr>
        <td></td>
        <td>' . $lang['pm_forward_org_msg'] . '' . $forwarded_username . '' . $lang['pm_forward_org_msg1'] . '<br>' . format_comment($message['msg']) . '</td>
    </tr>
    <tr>
        <td></td>
        <td><span>' . $lang['pm_forward_appear'] . '</span></td>
    </tr>
    <tr>
        <td><span>' . $lang['pm_forward_message'] . '</span></td>
        <td class="is-paddingless">' . BBcode($body) . '</td>
    </tr>
    <tr>
        <td colspan="2">' . ($CURUSER['class'] >= UC_STAFF ? '<span class="label label-danger">' . $lang['pm_forward_mark'] . '</span>
        <input type="checkbox" name="urgent" value="yes"> ;' : '') . '' . $lang['pm_forward_save'] . '
        <input type="checkbox" name="save" value="1">
        <input type="hidden" name="first_from" value="' . $forwarded_username . '">
        <input type="submit" class="button is-small" name="move" value="' . $lang['pm_forward_btn'] . '"></td>
    </tr>
    </table></form>';
