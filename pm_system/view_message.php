<?php
global $h1_thingie, $lang;

$subject = $friends = '';
//=== Get the message
$res = sql_query('SELECT m.*, f.id AS friend, b.id AS blocked
                            FROM messages AS m LEFT JOIN friends AS f ON f.userid = ' . sqlesc($CURUSER['id']) . ' AND f.friendid = m.sender
                            LEFT JOIN blocks AS b ON b.userid = ' . sqlesc($CURUSER['id']) . ' AND b.blockid = m.sender WHERE m.id = ' . sqlesc($pm_id) . ' AND (receiver=' . sqlesc($CURUSER['id']) . ' OR (sender=' . sqlesc($CURUSER['id']) . ' AND (saved = \'yes\' || unread= \'yes\'))) LIMIT 1') or sqlerr(__FILE__, __LINE__);
$message = mysqli_fetch_assoc($res);
if (!$res) {
    stderr($lang['pm_error'], $lang['pm_viewmsg_err']);
}
$res_user_stuff = sql_query('SELECT id, username, uploaded, warned, suspended, enabled, donor, class, avatar, leechwarn, chatpost, pirate, king, opt1, opt2 FROM users WHERE id=' . ($message['sender'] === $CURUSER['id'] ? sqlesc($message['receiver']) : sqlesc($message['sender']))) or sqlerr(__FILE__, __LINE__);
$arr_user_stuff = mysqli_fetch_assoc($res_user_stuff);
$id = (int)$arr_user_stuff['id'];
sql_query('UPDATE messages SET unread="no" WHERE id = ' . sqlesc($pm_id) . ' AND receiver = ' . sqlesc($CURUSER['id']) . ' LIMIT 1') or sqlerr(__FILE__, __LINE__);
$cache->decrement('inbox_' . $CURUSER['id']);
if ($message['friend'] > 0) {
    $friends = $lang['pm_mailbox_char1'] . '<span class="size_2"><a href="' . $site_config['baseurl'] . '/friends.php?action=delete&amp;type=friend&amp;targetid=' . $id . '">' . $lang['pm_mailbox_removef'] . '</a></span>' . $lang['pm_mailbox_char2'];
} elseif ($message['blocked'] > 0) {
    $friends = $lang['pm_mailbox_char1'] . '<span class="size_2"><a href="' . $site_config['baseurl'] . '/friends.php?action=delete&amp;type=block&amp;targetid=' . $id . '">' . $lang['pm_mailbox_removeb'] . '</a></span>' . $lang['pm_mailbox_char2'];
} elseif ($id > 0) {
    $friends = $lang['pm_mailbox_char1'] . '<span class="size_2"><a href="' . $site_config['baseurl'] . '/friends.php?action=add&amp;type=friend&amp;targetid=' . $id . '">' . $lang['pm_mailbox_addf'] . '</a></span>' . $lang['pm_mailbox_char2'] . '
                               ' . $lang['pm_mailbox_char1'] . '<span class="size_2"><a href="' . $site_config['baseurl'] . '/friends.php?action=add&amp;type=block&amp;targetid=' . $id . '">' . $lang['pm_mailbox_addb'] . '</a></span>' . $lang['pm_mailbox_char2'];
}
/*
    $avatar = ($CURUSER['avatars'] === 'no' ? '' : (empty($arr_user_stuff['avatar']) ? '
    <img width="80" src="' .$site_config['pic_baseurl'] . 'forumicons/default_avatar.gif" alt="no avatar" />' : (($arr_user_stuff['offensive_avatar'] === 'yes' && $CURUSER['view_offensive_avatar'] === 'no') ?
    '<img width="80" src="' .$site_config['pic_baseurl'] . 'fuzzybunny.gif" alt="fuzzy!" />' : '<a href="'.htmlsafechars($arr_user_stuff['avatar']).'"><img width="80" src="'.htmlsafechars($arr_user_stuff['avatar']).'" alt="avatar" /></a>')));
*/
$avatar = (!$CURUSER['opt1'] & user_options::AVATARS ? '' : (empty($arr_user_stuff['avatar']) ? '
    <img width="80" src="' . $site_config['pic_baseurl'] . 'forumicons/default_avatar.gif" alt="no avatar" />' : (($arr_user_stuff['opt1'] & user_options::OFFENSIVE_AVATAR && !$CURUSER['opt1'] & user_options::VIEW_OFFENSIVE_AVATAR) ? '<img width="80" src="' . $site_config['pic_baseurl'] . 'fuzzybunny.gif" alt="fuzzy!" />' : '<a href="' . htmlsafechars($arr_user_stuff['avatar']) . '"><img width="80" src="' . htmlsafechars($arr_user_stuff['avatar']) . '" alt="avatar" /></a>')));

if ($message['location'] > 1) {
    //== get name of PM box if not in or out
    $res_box_name = sql_query('SELECT name FROM pmboxes WHERE userid = ' . sqlesc($CURUSER['id']) . ' AND boxnumber=' . sqlesc($mailbox) . ' LIMIT 1') or sqlerr(__FILE__, __LINE__);
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
    <div class='container is-fluid portlet'>
        $h1_thingie" . ($message['draft'] === 'yes' ? "
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
                    <span>" . ($message['sender'] === $CURUSER['id'] ? $lang['pm_viewmsg_to'] : $lang['pm_viewmsg_from']) . ": </span>" .
    ($arr_user_stuff['id'] == 0 ? $lang['pm_viewmsg_sys'] : format_username($arr_user_stuff['id'])) . "{$friends}
                    <br><span>{$lang['pm_viewmsg_sent']}: </span>" . get_date($message['added'], '') . (($message['sender'] === $CURUSER['id'] && $message['unread'] == 'yes') ? $lang['pm_mailbox_char1'] . "<span class='has-text-red'>{$lang['pm_mailbox_unread']}</span>{$lang['pm_mailbox_char2']}" : '') . ($message['urgent'] === 'yes' ? "<span class='has-text-red'>{$lang['pm_mailbox_urgent']}</span>" : '') . "
                </td>
            </tr>
            <tr class='no_hover'>
                <td id='photocol'>{$avatar}</td>
                <td>" . format_comment($message['msg'], false) . "</td>
            </tr>
            <tr class='no_hover'>
                <td colspan='2'>
                    <div class='has-text-centered flex flex-justify-center'>
                        <form action='./pm_system.php' method='post'>
                            <input type='hidden' name='id' value='{$pm_id}' />
                            <input type='hidden' name='action' value='{$lang['pm_viewmsg_to']}' />
                            <span class='right10'>{$lang['pm_search_move_to']}</span>
                            " . get_all_boxes() . "
                            <input type='submit' class='button is-small left10' value='{$lang['pm_viewmsg_move']}' />
                        </form>
                    </div>
                    <div class='has-text-centered flex flex-center top20'>
                        <a href='{$site_config['baseurl']}/pm_system.php?action=delete&amp;id={$pm_id}'>
                            <input type='submit' class='button is-small' value='{$lang['pm_viewmsg_delete']}' />
                        </a>" . ($message['draft'] === 'no' ? "
                        <a href='{$site_config['baseurl']}/pm_system.php?action=save_or_edit_draft&amp;id={$pm_id}'>
                            <input type='submit' class='button is-small left10' value='{$lang['pm_viewmsg_sdraft']}' />
                        </a>" . (($id < 1 || $message['sender'] === $CURUSER['id']) ? '' : "
                        <a href='{$site_config['baseurl']}/pm_system.php?action=send_message&amp;receiver={$message['sender']}&amp;replyto={$pm_id}'>
                            <input type='submit' class='button is-small left10' value='{$lang['pm_viewmsg_reply']}' />
                        </a>
                        <a href='{$site_config['baseurl']}/pm_system.php?action=forward&amp;id={$pm_id}'>
                            <input type='submit' class='button is-small left10' value='{$lang['pm_viewmsg_fwd']}' />
                        </a>") : "
                        <a href='{$site_config['baseurl']}/pm_system.php?action=save_or_edit_draft&amp;edit=1&amp;id={$pm_id}'>
                            <input type='submit' class='button is-small left10' value='{$lang['pm_viewmsg_dedit']}' />
                        </a>
                        <a href='{$site_config['baseurl']}/pm_system.php?action=use_draft&amp;send=1&amp;id={$pm_id}'>
                            <input type='submit' class='button is-small left10' value='{$lang['pm_viewmsg_duse']}' />
                        </a>") . "
                    </div>
                </td>
            </tr>
        </table>
        <div class='has-text-centered top20 bottom20'>
            " . insertJumpTo(0) . "
        </div>
    </div>";
