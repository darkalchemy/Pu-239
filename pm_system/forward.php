<?php
$body = '';
if (!defined('BUNNY_PM_SYSTEM')) {
    setSessionVar('error', 'Access Not Allowed');
    header("Location: {$INSTALLER09['baseurl']}/index.php");
    exit();
}
//=== Get the info
$res = sql_query('SELECT * FROM messages WHERE id=' . sqlesc($pm_id)) or sqlerr(__FILE__, __LINE__);
$message = mysqli_fetch_assoc($res);
if ($message['sender'] == $CURUSER['id'] && $message['sender'] == $CURUSER['id'] || mysqli_num_rows($res) === 0) {
    stderr($lang['pm_error'], $lang['pm_forward_err']);
}
//=== if not from curuser then get who from
if ($message['sender'] !== $CURUSER['id']) {
    $res_forward = sql_query('SELECT username FROM users WHERE id=' . sqlesc($message['sender'])) or sqlerr(__FILE__, __LINE__);
    $arr_forward = mysqli_fetch_assoc($res_forward);
    $forwarded_username = ($message['sender'] === 0 ? $lang['pm_forward_system'] : (mysqli_num_rows($res_forward) === 0 ? $lang['pm_forward_unknow'] : $arr_forward['username']));
} else {
    $forwarded_username = htmlsafechars($CURUSER['username']);
}
//=== print out the forwarding page
$HTMLOUT .= '<h1>' . $lang['pm_forward_fwd'] . '' . htmlsafechars($message['subject']) . '</h1>
        <form action="pm_system.php" method="post">
        <input type="hidden" name="id" value="' . $pm_id . '" />
        <input type="hidden" name="action" value="forward_pm" />
    <table class="table table-bordered">
    <tr>
        <td align="left" colspan="2" class="colhead" valign="top"><h1>' . $lang['pm_forward_fwd_msg'] . '
        <img src="pic/arrow_next.gif" alt=":" />' . $lang['pm_forward_fwd'] . '' . htmlsafechars($message['subject']) . '</h1></td>
    </tr>
    <tr>
        <td align="right" class="one" valign="top"><span style="font-weight: bold;">' . $lang['pm_forward_to'] . '</span></td>
        <td align="left" class="one" valign="top"><input type="text" name="to" value="' . $lang['pm_forward_user'] . '" class="member" onfocus="this.value=\'\';" /></td>
    </tr>
    <tr>
        <td align="right" class="one" valign="top"><span style="font-weight: bold;">' . $lang['pm_forward_original'] . '</span></td>
        <td align="left" class="one" valign="top"><span style="font-weight: bold;">' . $forwarded_username . '</span></td>
    </tr>
    <tr>
        <td align="right" class="one" valign="top"><span style="font-weight: bold;">' . $lang['pm_forward_from'] . '</span></td>
        <td align="left" class="one" valign="top"><span style="font-weight: bold;">' . $CURUSER['username'] . '</span></td>
    </tr>
    <tr>
        <td align="right" class="one" valign="top"><span style="font-weight: bold;">' . $lang['pm_forward_subject'] . '</span></td>
        <td align="left" class="one" valign="top"><input type="text" class="text_default" name="subject" value="' . $lang['pm_forward_fwd'] . '' . htmlsafechars($message['subject']) . '" /></td>
    </tr>
    <tr>
        <td align="center" class="one"></td>
        <td align="left" class="two">' . $lang['pm_forward_org_msg'] . '' . $forwarded_username . '' . $lang['pm_forward_org_msg1'] . '<br>' . format_comment($message['msg']) . '</td>
    </tr>
    <tr>
        <td align="right" class="one" valign="top"></td>
        <td align="left" class="one"><span style="font-weight: bold;">' . $lang['pm_forward_appear'] . '</span></td>
    </tr>
    <tr>
        <td align="right" class="one" valign="top"><span style="font-weight: bold;">' . $lang['pm_forward_message'] . '</span></td>
        <td align="left" class="one" valign="top">' . BBcode($body, false) . '</td>
    </tr>
    <tr>
        <td colspan="2" align="center" class="one">' . ($CURUSER['class'] >= UC_STAFF ? '<span class="label label-danger">' . $lang['pm_forward_mark'] . '</span>
        <input type="checkbox" name="urgent" value="yes" />&#160;' : '') . '' . $lang['pm_forward_save'] . '
        <input type="checkbox" name="save" value="1" />
        <input type="hidden" name="first_from" value="' . $forwarded_username . '" /> 
        <input type="submit" class="btn btn-primary" name="move" value="' . $lang['pm_forward_btn'] . '" /></td>
    </tr>
    </table></form>';
