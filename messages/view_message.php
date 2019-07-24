<?php

declare(strict_types = 1);

use Pu239\User;

$user = check_user_status();
global $container, $site_config;

$subject = $friends = '';

$res = sql_query('SELECT m.*, f.id AS friend, b.id AS blocked
                            FROM messages AS m LEFT JOIN friends AS f ON f.userid=' . sqlesc($user['id']) . ' AND f.friendid=m.sender
                            LEFT JOIN blocks AS b ON b.userid=' . sqlesc($user['id']) . ' AND b.blockid=m.sender WHERE m.id=' . sqlesc($pm_id) . ' AND (receiver = ' . sqlesc($user['id']) . ' OR (sender = ' . sqlesc($user['id']) . ' AND (saved = \'yes\' || unread= \'yes\'))) LIMIT 1') or sqlerr(__FILE__, __LINE__);
$message = mysqli_fetch_assoc($res);
if (!$message) {
    stderr($lang['pm_error'], $lang['pm_viewmsg_err']);
}
$users_class = $container->get(User::class);
$arr_user_stuff = $users_class->getUserFromId((int) $message['sender'] === $user['id'] ? (int) $message['receiver'] : (int) $message['sender']);
$id = $arr_user_stuff['id'];
sql_query('UPDATE messages SET unread = "no" WHERE id = ' . sqlesc($pm_id) . ' AND receiver = ' . sqlesc($user['id']) . ' LIMIT 1') or sqlerr(__FILE__, __LINE__);
$cache->decrement('inbox_' . $user['id']);
if ($message['friend'] > 0) {
    $friends = '
                    <a href="' . $site_config['paths']['baseurl'] . '/friends.php?action=delete&amp;type=friend&amp;targetid=' . (int) $message['id'] . '">
                        <small><i class="icon-minus has-text-danger tooltipper" title="' . $lang['pm_mailbox_removef'] . '"></i></small>
                    </a>';
} elseif ($message['blocked'] > 0) {
    $friends = '
                    <a href="' . $site_config['paths']['baseurl'] . '/friends.php?action=delete&amp;type=block&amp;targetid=' . (int) $message['id'] . '">
                        <small><i class="icon-minus has-text-danger tooltipper" title="' . $lang['pm_mailbox_removeb'] . '"></i></small>
                    </a>';
} else {
    $friends = '
                    <a href="' . $site_config['paths']['baseurl'] . '/friends.php?action=add&amp;type=friend&amp;targetid=' . (int) $message['id'] . '">
                        <small><i class="icon-user-plus icon has-text-success tooltipper" title="' . $lang['pm_mailbox_addf'] . '"></i></small>
                    </a>
                    <a href="' . $site_config['paths']['baseurl'] . '/friends.php?action=add&amp;type=block&amp;targetid=' . (int) $message['id'] . '">
                        <small><i class="icon-user-times icon has-text-danger tooltipper" title="' . $lang['pm_mailbox_addb'] . '"></i></small>
                    </a>';
}

$avatar = get_avatar($arr_user_stuff);

if ($message['location'] > 1) {
    //== get name of PM box if not in or out
    $res_box_name = sql_query('SELECT name FROM pmboxes WHERE userid=' . sqlesc($user['id']) . ' AND boxnumber=' . sqlesc($mailbox) . ' LIMIT 1') or sqlerr(__FILE__, __LINE__);
    $arr_box_name = mysqli_fetch_row($res_box_name);
    if (mysqli_num_rows($res) === 0) {
        stderr($lang['pm_error'], $lang['pm_mailbox_invalid']);
    }
    $mailbox_name = htmlsafechars($arr_box_name[0]);
    $other_box_info = '<p><span style="color: red;">' . $lang['pm_mailbox_asterisc'] . '</span><span style="font-weight: bold;">' . $lang['pm_mailbox_note'] . '</span>
                                           ' . $lang['pm_mailbox_max'] . '<span style="font-weight: bold;">' . $maxbox . '</span>' . $lang['pm_mailbox_either'] . '
                                            <span style="font-weight: bold;">' . $lang['pm_mailbox_inbox'] . '</span>' . $lang['pm_mailbox_or'] . '<span style="font-weight: bold;">' . $lang['pm_mailbox_sentbox'] . '</span>.</p>';
}

$HTMLOUT .= "
    <div class='portlet'>" . ($message['draft'] === 'yes' ? "
        <h1>{$lang['pm_viewmsg_tdraft']}</h1>" : "
        <h1>{$lang['pm_viewmsg_mailbox']}{$mailbox_name}</h1>") . "
        $top_links
        <table class='table table-bordered top20 bottom20'>
            <tr class='no_hover'>
                <td colspan='2'>
                    <h2>{$lang['pm_send_subject']} " . ($message['subject'] !== '' ? htmlsafechars($message['subject']) : $lang['pm_search_nosubject']) . "</h2>
                </td>
            </tr>
            <tr class='no_hover'>
                <td colspan='2'>
                    <span>" . ($message['sender'] === $user['id'] ? $lang['pm_viewmsg_to'] : $lang['pm_viewmsg_from']) . ': </span>' . ($arr_user_stuff['id'] == 0 ? $lang['pm_viewmsg_sys'] : format_username((int) $arr_user_stuff['id'])) . "{$friends}
                    <br><span>{$lang['pm_viewmsg_sent']}: </span>" . get_date((int) $message['added'], '') . (((int) $message['sender'] === $user['id'] && $message['unread'] === 'yes') ? $lang['pm_mailbox_char1'] . "<span class='has-text-danger'>{$lang['pm_mailbox_unread']}</span>{$lang['pm_mailbox_char2']}" : '') . ($message['urgent'] === 'yes' ? "<span class='has-text-danger'>{$lang['pm_mailbox_urgent']}</span>" : '') . "
                </td>
            </tr>
            <tr class='no_hover'>
                <td class='has-text-centered w-15 mw-150'>{$avatar}</td>
                <td>" . format_comment($message['msg'], false) . "</td>
            </tr>
            <tr class='no_hover'>
                <td colspan='2'>
                    <div class='level-center-center has-text-centered'>
                        <form action='{$site_config['paths']['baseurl']}/messages.php' method='post' enctype='multipart/form-data' accept-charset='utf-8'>
                            <input type='hidden' name='id' value='{$pm_id}'>
                            <input type='hidden' name='action' value='move'>
                            " . get_all_boxes((int) $message['location'], $user['id']) . "
                            <input type='submit' class='button is-small margin10' value='{$lang['pm_viewmsg_move']}'>
                            <a href='{$site_config['paths']['baseurl']}/messages.php?action=delete&amp;id={$pm_id}' class='button is-small margin10'>{$lang['pm_viewmsg_delete']}</a>" . ($message['draft'] === 'no' ? "
                            <a href='{$site_config['paths']['baseurl']}/messages.php?action=save_or_edit_draft&amp;id={$pm_id}' class='button is-small margin10'>{$lang['pm_viewmsg_sdraft']}</a>" . (($id < 1 || $message['sender'] === $user['id']) ? '' : "
                            <a href='{$site_config['paths']['baseurl']}/messages.php?action=send_message&amp;receiver={$message['sender']}&amp;replyto={$pm_id}' class='button is-small margin10'>{$lang['pm_viewmsg_reply']}</a>
                            <a href='{$site_config['paths']['baseurl']}/messages.php?action=forward&amp;id={$pm_id}' class='button is-small margin10'>{$lang['pm_viewmsg_fwd']}</a>") : "
                            <a href='{$site_config['paths']['baseurl']}/messages.php?action=save_or_edit_draft&amp;edit=1&amp;id={$pm_id}' class='button is-small margin10'>{$lang['pm_viewmsg_dedit']}</a>
                            <a href='{$site_config['paths']['baseurl']}/messages.php?action=use_draft&amp;send=1&amp;id={$pm_id}' class='button is-small margin10'>{$lang['pm_viewmsg_duse']}</a>") . "
                        </form>
                    </div>
                </td>
            </tr>
        </table>
        <div class='has-text-centered top20 bottom20'>
            " . insertJumpTo(0, $user['id']) . '
        </div>
    </div>';
