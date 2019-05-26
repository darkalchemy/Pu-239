<?php

declare(strict_types = 1);

use Envms\FluentPDO\Literal;
use Pu239\Cache;
use Pu239\Database;
use Pu239\User;

global $container, $CURUSER, $site_config;

$all_my_boxes = $user_cache = $categories = '';
$user_stuffs = $container->get(User::class);
$fluent = $container->get(Database::class);
$cache = $container->get(Cache::class);
if (isset($_POST['action2'])) {
    $good_actions = [
        'add',
        'edit_boxes',
        'change_pm',
        'message_settings',
    ];
    $action2 = (isset($_POST['action2']) ? strip_tags($_POST['action2']) : '');
    $worked = $deleted = '';
    if (!in_array($action2, $good_actions)) {
        stderr($lang['pm_error'], $lang['pm_edmail_error']);
    }

    switch ($action2) {
        case 'change_pm':
            $change_pm_number = (isset($_POST['change_pm_number']) ? intval($_POST['change_pm_number']) : 20);
            $set = [
                'pms_per_page' => $change_pm_number,
            ];
            $user_stuffs->update($set, $CURUSER['id']);
            header('Location: messages.php?action=edit_mailboxes&pm=1');
            die();

        case 'add':
            if ($_POST['new'] === '') {
                stderr($lang['pm_error'], $lang['pm_edmail_err']);
            }
            $boxnumber = $fluent->from('pmboxes')
                                ->select(null)
                                ->select('MAX(boxnumber) AS boxnumber')
                                ->fetch('boxnumber');
            $box = $boxnumber < 2 ? 2 : $boxnumber++;
            $new_box = $_POST['new'];
            foreach ($new_box as $key => $add_it) {
                if (valid_username($add_it) && $add_it !== '') {
                    $name = htmlsafechars($add_it);
                    $values = [
                        'userid' => $CURUSER['id'],
                        'name' => $name,
                        'boxnumber' => $box,
                    ];
                    $fluent->insertInto('pmboxes')
                           ->values($values)
                           ->execute();
                    $cache->delete('get_all_boxes_' . $CURUSER['id']);
                    $cache->delete('insertJumpTo_' . $CURUSER['id']);
                }
                ++$box;
                $worked = '&boxes=1';
            }
            header('Location: messages.php?action=edit_mailboxes' . $worked);
            die();
            break;

        case 'edit_boxes':
            $boxes = $fluent->from('pmboxes')
                            ->where('userid = ?', $CURUSER['id'])
                            ->fetchAll();

            if (empty($boxes)) {
                stderr($lang['pm_error'], $lang['pm_edmail_err1']);
            }
            foreach ($boxes as $row) {
                if (valid_username($_POST['edit' . $row['id']]) && $_POST['edit' . $row['id']] !== '' && $_POST['edit' . $row['id']] !== $row['name']) {
                    $name = htmlsafechars($_POST['edit' . $row['id']]);
                    $set = [
                        'name' => $name,
                    ];
                    $fluent->update('pmboxes')
                           ->set($set)
                           ->where('id = ?', $row['id'])
                           ->execute();
                    $cache->delete('get_all_boxes_' . $CURUSER['id']);
                    $cache->delete('insertJumpTo_' . $CURUSER['id']);
                    $worked = '&name=1';
                }
                if ($_POST['edit' . $row['id']] == '') {
                    $set = [
                        'location' => 1,
                    ];
                    $message_stuffs->update_location($set, $row['boxnumber'], $CURUSER['id']);
                    $fluent->delete('pmboxes')
                           ->where('id = ?', $row['id'])
                           ->execute();
                    $cache->delete('get_all_boxes_' . $CURUSER['id']);
                    $cache->delete('insertJumpTo_' . $CURUSER['id']);
                    $deleted = '&box_delete=1';
                }
            }
            header('Location: messages.php?action=edit_mailboxes' . $deleted . $worked);
            die();
            break;

        case 'message_settings':
            $set = [];
            $change_pm_number = (isset($_POST['change_pm_number']) ? intval($_POST['change_pm_number']) : 20);
            $setbits = $clrbits = 0;
            if ($_POST['show_pm_avatar'] === 'yes') {
                $setbits |= user_options_2::SHOW_PM_AVATAR;
            } else {
                $clrbits |= user_options_2::SHOW_PM_AVATAR;
            }
            $acceptpms = isset($_POST['acceptpms']) && $_POST['acceptpms'] === 'yes' ? 'yes' : (isset($_POST['acceptpms']) && $_POST['acceptpms'] === 'friends' ? 'friends' : 'no');
            $save_pms = isset($_POST['save_pms']) ? 'yes' : 'no';
            $deletepms = isset($_POST['deletepms']) && $_POST['deletepms'] === 'yes' ? 'yes' : 'no';
            $pmnotif = isset($_POST['pmnotif']) ? $_POST['pmnotif'] : '';
            $emailnotif = isset($_POST['emailnotif']) ? $_POST['emailnotif'] : '';
            $notifs = $pmnotif == 'yes' ? $lang['pm_edmail_pm_1'] : '';
            $notifs .= $emailnotif == 'yes' ? $lang['pm_edmail_email_1'] : '';
            $category_ids = $fluent->from('categories')
                                   ->select(null)
                                   ->select('id')
                                   ->fetchAll();

            $rows = count($category_ids);
            for ($i = 0; $i < $rows; ++$i) {
                $a = $category_ids[$i]['id'];
                if (isset($_POST["cat{$a}"]) && $_POST["cat{$a}"] === 'yes') {
                    $notifs .= "[cat{$a}]";
                }
            }

            if ($setbits || $clrbits) {
                $set = [
                    'opt2' => new Literal("(opt2 | {$setbits}) & ~{$clrbits}"),
                ];
                $user_stuffs->update($set, $CURUSER['id'], false);
            }
            unset($set);
            $set = [
                'pms_per_page' => $change_pm_number,
                'acceptpms' => $acceptpms,
                'savepms' => $save_pms,
                'deletepms' => $deletepms,
                'notifs' => $notifs,
            ];
            $user_stuffs->update($set, $CURUSER['id']);
            $user_stuffs->getUserFromId($CURUSER['id'], true);
            $worked = '&pms=1';
            header('Location: messages.php?action=edit_mailboxes' . $worked);
            die();
    }
}

$boxes = $fluent->from('pmboxes')
                ->where('userid = ?', $CURUSER['id'])
                ->orderBy('name ASC')
                ->fetchAll();
$count_boxes = !empty($boxes) ? count($boxes) : 0;

if (!empty($boxes)) {
    foreach ($boxes as $row) {
        $messages = $message_stuffs->get_count($CURUSER['id'], $row['boxnumber']);
        $all_my_boxes .= '
                    <tr>
                        <td colspan="2">
                            ' . $lang['pm_edmail_box'] . '' . ((int) $row['boxnumber'] - 1) . ' <span>' . htmlsafechars($row['name']) . ':</span>
                            <input type="text" name="edit' . ((int) $row['id']) . '" value="' . htmlsafechars($row['name']) . '" class="w-100">' . $lang['pm_edmail_contain'] . $messages . $lang['pm_edmail_messages'] . '
                        </td>
                    </tr>';
    }
    $all_my_boxes .= '
                    <tr>
                        <td colspan="2">' . $lang['pm_edmail_names'] . '<br>
                        ' . $lang['pm_edmail_if'] . '</td>
                    </tr>
                    <tr>
                        <td colspan="2"><span>' . $lang['pm_edmail_note'] . '</span>
                        <ul>
                            <li>' . $lang['pm_edmail_if1'] . '</li>
                            <li>' . $lang['pm_edmail_if2'] . '<a class="altlink" href="messages.php?action=view_mailbox">' . $lang['pm_edmail_main'] . '</a>.</li>
                        </ul></td>
                    </tr>
                    <tr>
                        <td colspan="2" class="has-text-centered">
                            <input type="submit" class="button is-small margin20" value="' . $lang['pm_edmail_edit'] . '">
                        </td>
                    </tr>';
} else {
    $all_my_boxes .= '
                    <tr>
                        <td><span>' . $lang['pm_edmail_nobox'] . '</span></td>
                    </tr>';
}

$per_page_drop_down = '<select name="change_pm_number">';
$i = 20;
while ($i <= ($maxbox > 200 ? 200 : $maxbox)) {
    $per_page_drop_down .= '<option class="body" value="' . $i . '"' . ($CURUSER['pms_per_page'] == $i ? ' selected' : '') . '>' . $i . '' . $lang['pm_edmail_perpage'] . '</option>';
    $i = ($i < 100 ? $i = $i + 10 : $i = $i + 25);
}
$per_page_drop_down .= '</select>';

$category_set = genrelist(false);

$i = 0;
if (!empty($category_set)) {
    foreach ($category_set as $a) {
        if ($a['parent_id'] != 0) {
            $image = !empty($a['image']) && $CURUSER['opt2'] & user_options_2::BROWSE_ICONS ? "
                    <span class='left10'>
                        <a href='{$site_config['paths']['baseurl']}/browse.php?c{$a['id']}'>
                            <img class='caticon' src='{$site_config['paths']['images_baseurl']}caticons/{$CURUSER['categorie_icon']}/" . htmlsafechars($a['image']) . "'alt='" . htmlsafechars($a['name']) . "'>
                        </a>
                    </span>" : "
                    <span class='left10'>" . htmlsafechars($a['name']) . '</span>';

            $categories .= "
                <span class='margin10 bordered level-center bg-02 tooltipper' title='" . htmlsafechars($a['name']) . "'>
                    <input name='cat{$a['id']}' type='checkbox' " . (!empty($CURUSER['notifs']) && strpos($CURUSER['notifs'], "[cat{$a['id']}]") !== false ? ' checked' : '') . " value='yes'>$image
                </span>";
        } else {
            if ($i++ > 0) {
                $categories .= '
                </div>';
            }
            $categories .= "
                <div class='level-center bg-02 round10 top10'>";
        }
    }
}
$HTMLOUT .= $top_links . '<h1>' . $lang['pm_edmail_title'] . '</h1>' . $h1_thingie . '
        <form action="messages.php" method="post" accept-charset="utf-8">
        <input type="hidden" name="action" value="edit_mailboxes">
        <input type="hidden" name="action2" value="add">
        <h2 class="has-text-centered">' . $lang['pm_edmail_add_mbox'] . '</h2>';
$body = '
            <tr>
                <td colspan="2" class="has-text-centered">
                    ' . $lang['pm_edmail_as_a'] . '' . get_user_class_name($CURUSER['class']) . $lang['pm_edmail_you_may'] . $maxboxes . $lang['pm_edmail_pm_box'] . ($maxboxes !== 1 ? $lang['pm_edmail_pm_boxes'] : '') . '' . $lang['pm_edmail_other'] . '<br>' . $lang['pm_edmail_currently'] . $count_boxes . $lang['pm_edmail_custom'] . ($count_boxes !== 1 ? $lang['pm_edmail_custom_es'] : '') . $lang['pm_edmail_may_add'] . ($maxboxes - $count_boxes) . '' . $lang['pm_edmail_more_extra'] . '
                    <p class="top10">
                        <span>' . $lang['pm_edmail_following'] . '</span>' . $lang['pm_edmail_chars'] . '
                    </p>
                </td>
            </tr>';

for ($i = 1; $i < 6; ++$i) {
    $body .= '
            <tr>
                <td><span>box ' . $i . ':</span></td>
                <td><input type="text" name="new[]" class="w-100" maxlength="100"></td>
            </tr>';
}

$body .= '
            <tr>
                <td colspan="2" class="has-text-centered">
                    ' . $lang['pm_edmail_only_fill'] . '<br>
                    ' . $lang['pm_edmail_blank'] . '<br>
                    <input type="submit" class="button is-small margin20" name="move" value="' . $lang['pm_edmail_add'] . '">
                </td>
            </tr>
        </form>';

$HTMLOUT .= main_table($body);
$HTMLOUT .= '<h2 class="top20 has-text-centered">' . $lang['pm_edmail_ed_del'] . '</h2>';
$HTMLOUT .= '
        <form action="messages.php" method="post" accept-charset="utf-8">
        <input type="hidden" name="action" value="edit_mailboxes">
        <input type="hidden" name="action2" value="edit_boxes">';
$HTMLOUT .= main_table($all_my_boxes);
$HTMLOUT .= '
        </form>';
$cache->delete('user_' . $CURUSER['id']);
$show_pm_avatar = ($CURUSER['opt2'] & user_options_2::SHOW_PM_AVATAR) === user_options_2::SHOW_PM_AVATAR;
$HTMLOUT .= '<h2 class="top20 has-text-centered">' . $lang['pm_edmail_msg_settings'] . '</h2>';
$HTMLOUT .= main_table('
    <tr>
        <td class="w-25"><span>' . $lang['pm_edmail_pm_page'] . '</span></td>
        <td>
        <form action="messages.php" method="post" accept-charset="utf-8">
        <input type="hidden" name="action" value="edit_mailboxes">
        <input type="hidden" name="action2" value="message_settings">
        ' . $per_page_drop_down . '' . $lang['pm_edmail_s_how_many'] . '</td>
    </tr>
    <tr>
        <td><span>' . $lang['pm_edmail_av'] . '</span></td>
        <td>
        <select name="show_pm_avatar">
        <option value="yes"' . ($show_pm_avatar ? ' selected' : '') . '>' . $lang['pm_edmail_show_av'] . '</option>
        <option value="no"' . (!$show_pm_avatar ? ' selected' : '') . '>' . $lang['pm_edmail_dshow_av'] . '</option>
        </select>' . $lang['pm_edmail_show_av_box'] . '</td>
    </tr>
    <tr>
        <td><span>' . $lang['pm_edmail_accept'] . '</span></td>
        <td>
        <input type="radio" name="acceptpms"' . ($CURUSER['acceptpms'] === 'yes' ? ' checked' : '') . ' value="yes">' . $lang['pm_edmail_all'] . '
        <input type="radio" name="acceptpms"' . ($CURUSER['acceptpms'] === 'friends' ? ' checked' : '') . ' value="friends">' . $lang['pm_edmail_friend'] . '
        <input type="radio" name="acceptpms"' . ($CURUSER['acceptpms'] === 'no' ? ' checked' : '') . ' value="no">' . $lang['pm_edmail_staff'] . '</td>
    </tr>
    <tr>
        <td><span>' . $lang['pm_edmail_save'] . '</span></td>
        <td><input type="checkbox" name="save_pms"' . ($CURUSER['savepms'] === 'yes' ? ' checked' : '') . ' value="yes">' . $lang['pm_edmail_default'] . '</td>
    </tr>
    <tr>
        <td><span>' . $lang['pm_edmail_del_pms'] . '</span></td>
        <td><input type="checkbox" name="deletepms"' . ($CURUSER['deletepms'] === 'yes' ? ' checked' : '') . ' value="yes">' . $lang['pm_edmail_default_r'] . '</td>
    </tr>
    <tr>
        <td><span>' . $lang['pm_edmail_email_notif'] . '</span></td>
        <td><input type="checkbox" name="pmnotif"' . (!empty($CURUSER['notifs']) && strpos($CURUSER['notifs'], $lang['pm_edmail_pm_1']) !== false ? ' checked' : '') . '  value="yes">' . $lang['pm_edmail_notify'] . '</td>
    </tr>
    <tr>
        <td></td>
        <td><input type="checkbox" name="emailnotif"' . (!empty($CURUSER['notifs']) && strpos($CURUSER['notifs'], $lang['pm_edmail_email_1']) !== false ? ' checked' : '') . '  value="yes">' . $lang['pm_edmail_notify1'] . '</td>
    </tr>
    <tr>
        <td><span>' . $lang['pm_edmail_cats'] . '</span></td>
        <td><a class="altlink"  title="' . $lang['pm_edmail_clickmore'] . '" id="cat_open">' . $lang['pm_edmail_show_hide'] . '</a>' . $lang['pm_edmail_torr'] . '
        <div id="defcat" class="is_hidden">' . $lang['pm_edmail_def_cats'] . '<br>' . $categories . '</div></td>
    </tr>
    <tr>
        <td colspan="2" class="has-text-centered">
        <input type="submit" class="button is-small margin20" value="' . $lang['pm_edmail_change'] . '"></form></td>
    </tr>
    </table></form>');
