<?php

declare(strict_types = 1);

use Pu239\Database;
use Pu239\Message;

$user = check_user_status();
require_once INCL_DIR . 'function_users.php';
global $container, $site_config;

$show_pm_avatar = ($user['opt2'] & user_options_2::SHOW_PM_AVATAR) === user_options_2::SHOW_PM_AVATAR;
$message_class = $container->get(Message::class);
$fluent = $container->get(Database::class);
if ($mailbox > 1) {
    $arr_box_name = $fluent->from('pmboxes')
                           ->select(null)
                           ->select('name')
                           ->where('userid = ?', $user['id'])
                           ->where('boxnumber = ?', $mailbox)
                           ->fetch('name');
    if (empty($arr_box_name)) {
        stderr($lang['pm_error'], $lang['pm_mailbox_invalid']);
    }
    $mailbox_name = format_comment($arr_box_name);
    $other_box_info = '
        <div class="has-text-centered top20">
            <span class="has-text-danger">' . $lang['pm_mailbox_asterisc'] . '</span>
            <span class="has-text-weight-bold right10">' . $lang['pm_mailbox_note'] . '</span>' . $lang['pm_mailbox_max'] . '
            <span class="has-text-weight-bold">' . $maxbox . '</span>' . $lang['pm_mailbox_either'] . '
            <span class="has-text-weight-bold">' . $lang['pm_mailbox_sentbox'] . '.</span>
            <span class="has-text-danger">' . $lang['pm_mailbox_asterisc'] . '</span>
        </div>';
}

$total_count = $message_class->get_total_count($user['id']);
$filled = $total_count > 0 ? ($total_count / $maxbox) * 100 : 0;
$mailbox_pic = get_percent_completed_image(round($filled), $maxpic);
$num_messages = number_format($filled, 0);
$link = $site_config['paths']['baseurl'] . '/messages.php?action=view_mailbox&amp;box=' . $mailbox . '&amp;order_by=' . $order_by . $desc_asc . '&amp;';
$count = $message_class->get_count($user['id'], $mailbox, false);
$pager = pager($perpage, $count, $link);

$messages = $message_class->get_messages($user['id'], $mailbox, $pager['pdo']['limit'], $pager['pdo']['offset'], $order_by . (isset($_GET['ASC']) ? '' : ' DESC'));
$HTMLOUT .= "
    $top_links
    <a id='pm'></a>
        <div class='level-center-center'>
            <span class='size_2'>{$total_count} / {$maxbox}</span>
            <span class='size_7 left20 right20 has-text-weight-bold'>{$mailbox_name}</span>
            <span class='size_2'>{$lang['pm_mailbox_full']}{$num_messages}{$lang['pm_mailbox_full1']}</span>
         </div>
        <div class='margin20'>$mailbox_pic</div>" . insertJumpTo($mailbox, $user['id']) . $other_box_info . ($count > $perpage ? $pager['pagertop'] : '') . "
        <form action='{$site_config['paths']['baseurl']}/messages.php' method='post' name='checkme' enctype='multipart/form-data' accept-charset='utf-8'>
            <div class='table-wrapper'>
            <table class='table table-bordered table-striped top20'>
                <thead>
                    <tr>
                        <th class='has-text-centered w-1'>
                            <input type='hidden' name='action' value='move_or_delete_multi'>
                            Mailbox
                        </th>
                        <th class='min-150'>
                            <a href='{$site_config['paths']['baseurl']}/messages.php?action=view_mailbox&amp;box={$mailbox}" . ($perpage == 20 ? '' : '&amp;perpage=' . $perpage) . ($perpage < $count ? '&amp;page=' . $page : '') . "&amp;order_by=subject{$desc_asc}#pm' class='tooltipper' title='{$lang['pm_mailbox_sorder']}{$desc_asc_2}'>{$lang['pm_mailbox_subject']}
                            </a>
                        </th>
                        <th class='has-text-centered'>
                            <a href='{$site_config['paths']['baseurl']}/messages.php?action=view_mailbox&amp;box={$mailbox}" . ($perpage == 20 ? '' : '&amp;perpage=' . $perpage) . ($perpage < $count ? '&amp;page=' . $page : '') . "&amp;order_by=username{$desc_asc}#pm' class='tooltipper' title='{$lang['pm_mailbox_morder']}{$desc_asc_2}'>" . ($mailbox === $site_config['pm']['sent'] ? $lang['pm_search_sent_to'] : $lang['pm_search_sender']) . "
                            </a>
                        </th>
                        <th class='has-text-centered'>
                            <a href='{$site_config['paths']['baseurl']}/messages.php?action=view_mailbox&amp;box={$mailbox}" . ($perpage == 20 ? '' : '&amp;perpage=' . $perpage) . ($perpage < $count ? '&amp;page=' . $page : '') . "&amp;order_by=added{$desc_asc}#pm' class='tooltipper' title='{$lang['pm_mailbox_dorder']} {$desc_asc_2}'>{$lang['pm_mailbox_date']}
                            </a>
                        </th>
                        <th class='has-text-centered w-1'><input type='checkbox' id='checkThemAll' class='tooltipper' title='Select All'></th>
                    </tr>
                </thead>
                <tbody>";
if (empty($messages)) {
    $HTMLOUT .= "
        <tr>
            <td colspan='5' class='has-text-centered'>
                <div>{$lang['pm_mailbox_nomsg']}{$mailbox_name}</div>
            </td>
        </tr>";
} else {
    foreach ($messages as $row) {
        if ($mailbox === $site_config['pm']['drafts'] || $row['id'] === 0 || $row['sender'] === $user['id'] || $row['poster'] === $user['id']) {
            $friends = '';
        } else {
            if ($row['friend'] > 0) {
                $friends = '
                    <a href="' . $site_config['paths']['baseurl'] . '/friends.php?action=delete&amp;type=friend&amp;targetid=' . $row['id'] . '">
                        <small><i class="icon-minus has-text-danger tooltipper" title="' . $lang['pm_mailbox_removef'] . '"></i></small>
                    </a>';
            } elseif ($row['blocked'] > 0) {
                $friends = '
                    <a href="' . $site_config['paths']['baseurl'] . '/friends.php?action=delete&amp;type=block&amp;targetid=' . $row['id'] . '">
                        <small><i class="icon-minus has-text-danger tooltipper" title="' . $lang['pm_mailbox_removeb'] . '"></i></small>
                    </a>';
            } else {
                $friends = '
                    <a href="' . $site_config['paths']['baseurl'] . '/friends.php?action=add&amp;type=friend&amp;targetid=' . $row['id'] . '">
                        <small><i class="icon-user-plus icon has-text-success tooltipper" title="' . $lang['pm_mailbox_addf'] . '"></i></small>
                    </a>
                    <a href="' . $site_config['paths']['baseurl'] . '/friends.php?action=add&amp;type=block&amp;targetid=' . $row['id'] . '">
                        <small><i class="icon-user-times icon has-text-danger tooltipper" title="' . $lang['pm_mailbox_addb'] . '"></i></small>
                    </a>';
            }
        }
        $subject = !empty($row['subject']) ? format_comment($row['subject']) : $lang['pm_search_nosubject'];
        $who_sent_it = $row['id'] === 0 || $row['id'] === 2 ? '<span style="font-weight: bold;">' . $lang['pm_forward_system'] . '</span>' : format_username($row['id']) . $friends;
        $read_unread = $row['unread'] === 'yes' ? '<img src="' . $site_config['paths']['images_baseurl'] . 'pn_inboxnew.gif" title="' . $lang['pm_mailbox_unreadmsg'] . '" alt="' . $lang['pm_mailbox_unread'] . '">' : '<img src="' . $site_config['paths']['images_baseurl'] . 'pn_inbox.gif" title="' . $lang['pm_mailbox_readmsg'] . '" alt="' . $lang['pm_mailbox_read'] . '">';
        $extra = ($row['unread'] === 'yes' ? $lang['pm_mailbox_char1'] . '<span style="color: red;">' . $lang['pm_mailbox_unread'] . '</span>' . $lang['pm_mailbox_char2'] : '') . ($row['urgent'] === 'yes' ? '<span style="color: red;">' . $lang['pm_mailbox_urgent'] . '</span>' : '');
        $avatar = $show_pm_avatar ? get_avatar($row) : '';
        $HTMLOUT .= '
                <tr>
                    <td class="has-text-centered">' . $read_unread . '</td>
                    <td class="min-350"><a class="is-link"  href="' . $site_config['paths']['baseurl'] . '/messages.php?action=view_message&amp;id=' . $row['message_id'] . '">' . $subject . '</a> ' . $extra . '</td>
                    <td class="has-text-centered w-15 mw-150">' . $avatar . $who_sent_it . ($user['class'] >= UC_STAFF && $row['sender'] == 0 && $row['poster'] != 0 && $row['poster'] != $user['id'] ? ' [' . format_username($row['poster']) . ']' : '') . '</td>
                    <td class="has-text-centered w-15 mw-150">' . get_date($row['added'], '') . '</td>
                    <td class="has-text-centered">
                        <input type="checkbox" name="pm[]" value="' . $row['message_id'] . '">
                    </td>
                </tr>';
    }
}

$per_page_drop_down = '<form action="' . $site_config['paths']['baseurl'] . '/messages.php" method="post"><select name="amount_per_page" onchange="location=this.options[this.selectedIndex].value;" accept-charset="utf-8">';
$i = 20;
while ($i <= ($maxbox > 200 ? 200 : $maxbox)) {
    $per_page_drop_down .= '<option class="body" value="' . $link . '&amp;change_pm_number=' . $i . '"  ' . ($user['pms_per_page'] == $i ? ' selected' : '') . '>' . $i . $lang['pm_edmail_perpage'] . '</option>';
    $i = ($i < 100 ? $i = $i + 10 : $i = $i + 25);
}
$per_page_drop_down .= '</select><input type="hidden" name="box" value="' . $mailbox . '"></form>';

$show_pm_avatar_drop_down = '
    <form method="post" action="messages.php" accept-charset="utf-8">
        <select name="show_pm_avatar" onchange="location=this.options[this.selectedIndex].value;">
            <option value="' . $link . '&amp;show_pm_avatar=yes" ' . ($show_pm_avatar ? 'selected' : '') . '>' . $lang['pm_edmail_show_av'] . '</option>
            <option value="' . $link . '&amp;show_pm_avatar=no" ' . (!$show_pm_avatar ? 'selected' : '') . '>' . $lang['pm_mailbox_dontav'] . '</option>
        </select>
            <input type="hidden" name="box" value="' . $mailbox . '"></form>';

$HTMLOUT .= (!empty($messages) ? "
    <tr>
        <td colspan='5'>
            <div class='level-center-center'>
                <input type='submit' class='button is-small right10' name='move' value='{$lang['pm_search_move_to']}'> " . get_all_boxes($mailbox, $user['id']) . " or
                <input type='submit' class='button is-small left10 right10' name='delete' value='{$lang['pm_search_delete']}'>{$lang['pm_search_selected']}
            </div>
        </td>
    </tr>
    <tr>
        <td colspan='5'>
            <div class='level-center'>
                <span><img src='{$site_config['paths']['images_baseurl']}pn_inboxnew.gif' title='{$lang['pm_mailbox_unreadmsg']}' alt='{$lang['pm_mailbox_unread']}'>{$lang['pm_mailbox_unreadmsgs']}</span>
                <span><img src='{$site_config['paths']['images_baseurl']}pn_inbox.gif' title='{$lang['pm_mailbox_readmsg']}' alt='{$lang['pm_mailbox_read']}'>'{$lang['pm_mailbox_readmsgs']}</span>
                {$per_page_drop_down}
                {$show_pm_avatar_drop_down}
            </div>
        </td>
    </tr>" : '') . '
    </table>
    </div>
        ' . ($count > $perpage ? $pager['pagerbottom'] . '<br>' : '') . '
    </form>';
