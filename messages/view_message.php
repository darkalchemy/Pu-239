<?php

declare(strict_types = 1);

use Pu239\Database;
use Pu239\User;

$user = check_user_status();
$image = placeholder_image();
global $container, $site_config;

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
    stderr(_('Error'), _('You do not have permission to view this message.'));
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
            <a class='is-link tooltipper' href='{$site_config['paths']['baseurl']}/forums.php?action=download_attachment&amp;id={$file['id']}' title='" . _('Download Attachment') . " #{$i}' target='_blank'>" . htmlsafechars($file['file_name']) . "</a>
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
                        <small><i class="icon-minus has-text-danger tooltipper" title="' . _('remove from friends') . '"></i></small>
                    </a>';
} elseif ($message['blocked'] > 0) {
    $friends = '
                    <a href="' . $site_config['paths']['baseurl'] . '/friends.php?action=delete&amp;type=block&amp;targetid=' . (int) $message['id'] . '">
                        <small><i class="icon-minus has-text-danger tooltipper" title="' . _('remove from blocks') . '"></i></small>
                    </a>';
} else {
    $friends = '
                    <a href="' . $site_config['paths']['baseurl'] . '/friends.php?action=add&amp;type=friend&amp;targetid=' . (int) $message['id'] . '">
                        <small><i class="icon-user-plus icon has-text-success tooltipper" title="' . _('add to friends') . '"></i></small>
                    </a>
                    <a href="' . $site_config['paths']['baseurl'] . '/friends.php?action=add&amp;type=block&amp;targetid=' . (int) $message['id'] . '">
                        <small><i class="icon-user-times icon has-text-danger tooltipper" title="' . _('add to blocks') . '"></i></small>
                    </a>';
}

$avatar = get_avatar($arr_user_stuff);
if (($message['receiver'] != $user['id'] || $message['sender'] === $user['id']) && strpos($_SERVER['HTTP_REFERER'], 'box=-1') !== false) {
    $mailbox = -1;
} else {
    $mailbox = $message['location'];
}
if ($message['location'] > 1) {
    $name = $fluent->from('pmboxes')
                   ->select(null)
                   ->select('name')
                   ->where('userid = ?', $user['id'])
                   ->where('boxnumber = ?', $mailbox)
                   ->fetch('name');
    if (empty($name)) {
        stderr(_('Error'), _('Invalid mailbox'));
    }
    $mailbox_name = htmlsafechars($name);
    $other_box_info = '
        <div class="has-text-centered top20">
            <span class="has-text-danger">***</span>
            <span class="right10 left10">' . _fe("please note: you have a maximum of {0} PM's for all mail boxes that are not sentbox.", $maxbox) . '</span>
            <span class="has-text-danger">***</span>
        </div>';
} else {
    $mailbox_name = ($mailbox === $site_config['pm']['inbox'] ? _('Inbox') : ($mailbox === $site_config['pm']['sent'] ? _('Sentbox') : ($mailbox === $site_config['pm']['deleted'] ? _('Deleted') : _('Drafts'))));
}

$HTMLOUT .= "
    <div class='portlet'>" . ($message['draft'] === 'yes' ? '
        <h1>' . _('This is a draft') . '</h1>' : '
        <h1>' . _('Mailbox: ') . "{$mailbox_name}</h1>") . "
        $top_links";

$body = "
            <tr class='no_hover'>
                <td colspan='2'>
                    <h2>" . _('Subject') . ': ' . ($message['subject'] !== '' ? htmlsafechars($message['subject']) : _('No Subject')) . "</h2>
                </td>
            </tr>
            <tr class='no_hover'>
                <td colspan='2'>
                    <span>" . ($message['sender'] === $user['id'] ? _('To') : _('From')) . ': </span>' . ($arr_user_stuff['id'] == 0 ? _('System') : format_username((int) $arr_user_stuff['id'])) . "{$friends}
                    <br><span>" . _('Sent') . ': </span>' . get_date((int) $message['added'], '') . (((int) $message['sender'] === $user['id'] && $message['unread'] === 'yes') ? "[ <span class='has-text-danger'>" . _('Unread') . '</span> ]' : '') . ($message['urgent'] === 'yes' ? "<span class='has-text-danger'>" . _('URGENT!') . '</span>' : '') . "
                </td>
            </tr>
            <tr class='no_hover'>
		    <td colspan='2'>
                <div class='w-100 padding20'>
                    <div class='columns'>
                        <div class='column round10 bg-02 is-2-desktop is-3-tablet is-12-mobile has-text-centered'>
                            {$avatar}<br>" . format_username($message['sender']) . (empty($message['title']) ? '' : "
                            <div class='size_3'>[" . format_comment($message['title']) . ']</div>') . ($message['last_access'] > TIME_NOW - 300 ? "
                            <div class='level-center-center'><img src='{$image}' data-src='{$site_config['paths']['images_baseurl']}forums/online.gif' alt='" . _('Online') . "' title='" . _('Online') . "' class='tooltipper icon is-small lazy'>" . _('Online') . '</div>' : "
                            <div class='level-center-center'><img src='{$image}' data-src='{$site_config['paths']['images_baseurl']}forums/offline.gif' alt='" . _('Offline') . "' title='" . _('Offline') . "' class='tooltipper icon is-small lazy'>" . _('Offline') . '</div>') . '
                            <div>' . _('Karma') . ': ' . number_format((float) $message['seedbonus']) . '</div>' . (!empty($message['website']) ? "
                            <div>
                                <a href='" . format_comment($message['website']) . "' target='_blank' title='" . _('click to go to website') . "'>
                                    <img src='{$image}' data-src='{$site_config['paths']['images_baseurl']}forums/website.gif' alt='website' class='tooltipper emoticon lazy'>
                                </a>
                            </div>" : '') . ($message['show_email'] === 'yes' ? "
                            <div>
                                <a href='mailto:" . format_comment($message['email']) . "'  title='" . _('click to email') . "' target='_blank'>
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
            </tr>
            <tr class='no_hover'>
                <td colspan='2'>
                    <div class='level-center-center has-text-centered'>
                        <form action='{$site_config['paths']['baseurl']}/messages.php' method='post' enctype='multipart/form-data' accept-charset='utf-8'>
                            <input type='hidden' name='id' value='{$pm_id}'>
                            <input type='hidden' name='action' value='move'>
                            " . get_all_boxes((int) $message['location'], $user['id']) . "
                            <input type='submit' class='button is-small margin10' value='" . _('move') . "'>
                            <a href='{$site_config['paths']['baseurl']}/messages.php?action=delete&amp;id={$pm_id}' class='button is-small margin10'>" . _('delete') . '</a>' . ($message['draft'] === 'no' ? "
                            <a href='{$site_config['paths']['baseurl']}/messages.php?action=save_or_edit_draft&amp;id={$pm_id}' class='button is-small margin10'>" . _('save as draft') . '</a>' . (($id < 1 || $message['sender'] === $user['id']) ? '' : "
                            <a href='{$site_config['paths']['baseurl']}/messages.php?action=send_message&amp;receiver={$message['sender']}&amp;replyto={$pm_id}' class='button is-small margin10'>" . _('reply') . "</a>
                            <a href='{$site_config['paths']['baseurl']}/messages.php?action=forward&amp;id={$pm_id}' class='button is-small margin10'>" . _('fwd') . '</a>') : "
                            <a href='{$site_config['paths']['baseurl']}/messages.php?action=save_or_edit_draft&amp;edit=1&amp;id={$pm_id}' class='button is-small margin10'>" . _('edit draft') . "</a>
                            <a href='{$site_config['paths']['baseurl']}/messages.php?action=use_draft&amp;send=1&amp;id={$pm_id}' class='button is-small margin10'>" . _('use draft') . '</a>') . '
                        </form>
                    </div>
                </td>
            </tr>';
$HTMLOUT .= main_table($body) . "
        <div class='has-text-centered top20 bottom20'>
            " . insertJumpTo(0, $user['id']) . '
        </div>
    </div>';
$breadcrumbs = [
    "<a href='{$site_config['paths']['baseurl']}/messages.php'>" . _('Private Messages') . '</a>',
    "<a href='{$site_config['paths']['baseurl']}/messages.php?action=view_mailbox&box={$mailbox}'>{$mailbox_name}</a>",
    "<a href='{$site_config['paths']['baseurl']}/messages.php?action=view_message&id={$pm_id}'>" . format_comment($message['subject']) . '</a>',
];
