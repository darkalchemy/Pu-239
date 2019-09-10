<?php

declare(strict_types = 1);

use Pu239\Database;
use Pu239\User;

$user = check_user_status();
$image = placeholder_image();
global $container, $site_config, $lang;

$lang = array_merge($lang, load_language('messages'), load_language('forums'), load_language('forums_global'));
$subject = $friends = '';
$fluent = $container->get(Database::class);
$message = $fluent->from('messages AS m')
                  ->select('f.id AS friend')
                  ->select('b.id AS blocked')
                  ->select('a.id AS attachment')
                  ->select('u.title')
                  ->select('u.last_access')
                  ->select('u.show_email')
                  ->select('u.email')
                  ->select('u.website')
                  ->select('u.seedbonus')
                  ->where('m.id = ?', $pm_id)
                  ->leftJoin('friends AS f ON f.userid = ? AND f.friendid = m.sender', $user['id'])
                  ->leftJoin('blocks AS b ON b.userid = ? AND b.blockid = m.sender', $user['id'])
                  ->leftJoin('attachments AS a ON m.added = a.post_id')
                  ->leftJoin('users AS u ON m.sender = u.id')
                  ->fetch();
if (empty($message) || ($message['receiver'] != $user['id'] && $message['sender'] != $user['id'])) {
    stderr($lang['pm_error'], $lang['pm_viewmsg_err']);
}
$attachment = '';
if (!empty($message['attachment'])) {
    $attachments = $fluent->from('attachments')
                          ->where('post_id = ?', $message['added'])
                          ->fetchAll();
    $i = 0;
    foreach ($attachments as $file) {
        ++$i;
        $attachment .= "
        <span>
            <a class='is-link tooltipper' href='{$site_config['paths']['baseurl']}/forums.php?action=download_attachment&amp;id={$file['id']}' title='{$lang['messages_download_attachment']} #{$i}' target='_blank'>" . htmlsafechars($file['file_name']) . "</a>
            <span class='has-text-weight-bold size_2'>[" . mksize($file['size']) . ']</span>
        </span>';
    }
}
$users_class = $container->get(User::class);
$arr_user_stuff = $users_class->getUserFromId((int) $message['sender'] === $user['id'] ? (int) $message['receiver'] : (int) $message['sender']);
$id = $arr_user_stuff['id'];
$update = [
    'unread' => 'no',
];
$fluent->update('messages')
       ->set($update)
       ->where('id = ?', $pm_id)
       ->where('receiver = ?', $user['id'])
       ->execute();
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
    $name = $fluent->from('pmboxes')
                   ->select(null)
                   ->select('name')
                   ->where('userid = ?', $user['id'])
                   ->where('boxnumber = ?', $mailbox)
                   ->fetch('name');
    if (empty($name)) {
        stderr($lang['pm_error'], $lang['pm_mailbox_invalid']);
    }
    $mailbox_name = htmlsafechars($name);
    $other_box_info = '<p><span style="color: red;">' . $lang['pm_mailbox_asterisc'] . '</span><span style="font-weight: bold;">' . $lang['pm_mailbox_note'] . '</span>
                                           ' . $lang['pm_mailbox_max'] . '<span style="font-weight: bold;">' . $maxbox . '</span>' . $lang['pm_mailbox_either'] . '
                                            <span style="font-weight: bold;">' . $lang['pm_mailbox_inbox'] . '</span>' . $lang['pm_mailbox_or'] . '<span style="font-weight: bold;">' . $lang['pm_mailbox_sentbox'] . '</span>.</p>';
}

$HTMLOUT .= "
    <div class='portlet'>" . ($message['draft'] === 'yes' ? "
        <h1>{$lang['pm_viewmsg_tdraft']}</h1>" : "
        <h1>{$lang['pm_viewmsg_mailbox']}{$mailbox_name}</h1>") . "
        $top_links";

$body = "
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
		    <td colspan='2'>
                <div class='w-100 padding20'>
                    <div class='columns'>
                        <div class='column round10 bg-02 is-2-desktop is-3-tablet is-12-mobile has-text-centered'>
                            {$avatar}<br>" . format_username($message['sender']) . (empty($message['title']) ? '' : "
                            <div class='size_3'>[" . format_comment($message['title']) . ']</div>') . ($message['last_access'] > TIME_NOW - 300 ? "
                            <div><img src='{$image}' data-src='{$site_config['paths']['images_baseurl']}forums/online.gif' alt='{$lang['fe_online']}' title='{$lang['fe_online']}' class='tooltipper icon is-small lazy'>{$lang['fe_online']}</div>" : "
                            <div><img src='{$image}' data-src='{$site_config['paths']['images_baseurl']}forums/offline.gif' alt='{$lang['fe_offline']}' title='{$lang['fe_offline']}' class='tooltipper icon is-small lazy'>{$lang['fe_offline']}</div>") . "
                            <div>{$lang['fe_karma']}: " . number_format((float) $message['seedbonus']) . '</div>' . (!empty($message['website']) ? "
                            <div>
                                <a href='" . format_comment($message['website']) . "' target='_blank' title='{$lang['fe_click_to_go_to_website']}'>
                                    <img src='{$image}' data-src='{$site_config['paths']['images_baseurl']}forums/website.gif' alt='website' class='tooltipper emoticon lazy'>
                                </a>
                            </div>" : '') . ($message['show_email'] === 'yes' ? "
                            <div>
                                <a href='mailto:" . format_comment($message['email']) . "'  title='{$lang['fe_click_to_email']}' target='_blank'>
                                    <i class='icon-mail icon tooltipper' aria-hidden='true' title='email'><i>
                                </a>
                            </div>" : '') . "
                        </div>
                        <div class='column round10 bg-02 left20'>
                            <div class='flex-vertical comments h-100'>
                                <div>" . format_comment($message['msg'], false) . "</div>
                                <div>{$attachment}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </td>

            <!--
                <td class='has-text-centered min-150 mw-150'>{$avatar}</td>
                <td>
                    <div class='flex-vertical comments h-100 padding10'>
                        <div>" . format_comment($message['msg'], false) . "</div>
                        <div>$attachment</div>
                    </div>
                </td>
            -->
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
                            <a href='{$site_config['paths']['baseurl']}/messages.php?action=use_draft&amp;send=1&amp;id={$pm_id}' class='button is-small margin10'>{$lang['pm_viewmsg_duse']}</a>") . '
                        </form>
                    </div>
                </td>
            </tr>';
$HTMLOUT .= main_table($body) . "
        <div class='has-text-centered top20 bottom20'>
            " . insertJumpTo(0, $user['id']) . '
        </div>
    </div>';
