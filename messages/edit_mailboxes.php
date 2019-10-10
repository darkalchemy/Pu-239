<?php

declare(strict_types = 1);

use Envms\FluentPDO\Literal;
use Pu239\Cache;
use Pu239\Database;
use Pu239\Message;
use Pu239\User;

require_once INCL_DIR . 'function_categories.php';
global $container, $CURUSER, $site_config;

$all_my_boxes = $user_cache = $categories = '';
$users_class = $container->get(User::class);
$messages_class = $container->get(Message::class);
$fluent = $container->get(Database::class);
$cache = $container->get(Cache::class);
if (isset($_POST['action2'])) {
    $good_actions = [
        'add',
        'edit_boxes',
        'change_pm',
        'message_settings',
    ];
    $action2 = isset($_POST['action2']) ? strip_tags($_POST['action2']) : '';
    $worked = $deleted = '';
    if (!in_array($action2, $good_actions)) {
        stderr(_('Error'), _("His wit's as thick as a Tewkesbury mustard."));
    }

    switch ($action2) {
        case 'change_pm':
            $change_pm_number = isset($_POST['change_pm_number']) ? (int) $_POST['change_pm_number'] : 20;
            $set = [
                'pms_per_page' => $change_pm_number,
            ];
            $users_class->update($set, $CURUSER['id']);
            header('Location: ' . $_SERVER['PHP_SELF'] . '?action=edit_mailboxes&pm=1');
            die();

        case 'add':
            if ($_POST['new'] === '') {
                stderr(_('Error'), _('to add new PM boxes you MUST enter at least one PM box name!'));
            }
            $boxnumber = $fluent->from('pmboxes')
                                ->select(null)
                                ->select('MAX(boxnumber) AS boxnumber')
                                ->fetch('boxnumber');
            $box = $boxnumber < 2 ? 2 : $boxnumber++;
            $new_box = preg_replace('/[^\da-z\-_]/i', '', $_POST['new']);
            foreach ($new_box as $key => $add_it) {
                $add_it = preg_replace('/[^\da-z\-_]/i', '', $add_it);
                if (!empty($add_it)) {
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
            header('Location: ' . $_SERVER['PHP_SELF'] . '?action=edit_mailboxes' . $worked);
            die();
            break;

        case 'edit_boxes':
            $boxes = $fluent->from('pmboxes')
                            ->where('userid = ?', $CURUSER['id'])
                            ->fetchAll();

            if (empty($boxes)) {
                stderr(_('Error'), _('No Mailboxes to edit'));
            }
            foreach ($boxes as $row) {
                $name = htmlsafechars(preg_replace('/[^\da-z\-_]/i', '', $_POST['edit' . $row['id']]));
                if (!empty($name) && $name !== $row['name']) {
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
                } elseif (empty($name)) {
                    $set = [
                        'location' => 1,
                    ];
                    $messages_class->update_location($set, (int) $row['boxnumber'], $CURUSER['id']);
                    $fluent->delete('pmboxes')
                           ->where('id = ?', $row['id'])
                           ->execute();
                    $cache->delete('get_all_boxes_' . $CURUSER['id']);
                    $cache->delete('insertJumpTo_' . $CURUSER['id']);
                    $deleted = '&box_delete=1';
                }
            }
            header('Location: ' . $_SERVER['PHP_SELF'] . '?action=edit_mailboxes' . $deleted . $worked);
            die();
            break;

        case 'message_settings':
            $set = [];
            $change_pm_number = isset($_POST['change_pm_number']) ? (int) $_POST['change_pm_number'] : 20;
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
            $notifs = $pmnotif == 'yes' ? '[pm]' : '';
            $notifs .= $emailnotif == 'yes' ? '[email]' : '';
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
                $users_class->update($set, $CURUSER['id'], false);
            }
            unset($set);
            $set = [
                'pms_per_page' => $change_pm_number,
                'acceptpms' => $acceptpms,
                'savepms' => $save_pms,
                'deletepms' => $deletepms,
                'notifs' => $notifs,
            ];
            $users_class->update($set, $CURUSER['id']);
            $users_class->getUserFromId($CURUSER['id'], true);
            $worked = '&pms=1';
            header('Location: ' . $_SERVER['PHP_SELF'] . '?action=edit_mailboxes' . $worked);
            die();
    }
}

$boxes = $fluent->from('pmboxes')
                ->where('userid = ?', $CURUSER['id'])
                ->orderBy('boxnumber')
                ->fetchAll();
$count_boxes = !empty($boxes) ? count($boxes) : 0;

if (!empty($boxes)) {
    foreach ($boxes as $row) {
        $messages = $messages_class->get_count($CURUSER['id'], (int) $row['boxnumber'], false);
        $all_my_boxes .= '
                    <tr>
                        <td colspan="2">
                            ' . _('Box #') . ((int) $row['boxnumber'] - 1) . ' <span>' . htmlsafechars($row['name']) . ':</span>
                            <input type="text" name="edit' . ((int) $row['id']) . '" value="' . htmlsafechars($row['name']) . '" class="w-100">[ ' . _pfe('contains {0} message', 'contains {0} messages', $messages) . ' ]
                        </td>
                    </tr>';
    }
    $all_my_boxes .= '
                    <tr>
                        <td colspan="2">' . _('You may edit the names of your PM boxes here.') . '<br>
                        ' . _('If you wish to delete 1 or more PM boxes, remove the name from the text field leaving it blank.') . '</td>
                    </tr>
                    <tr>
                        <td colspan="2"><span>' . _('Please note!') . '</span>
                        <ul>
                            <li>' . _('If you delete the name of one or more boxes, all messages in that directory will be sent to your inbox!') . '</li>
                            <li>' . _fe('If you wish to delete the messages as well, you can do that from the {0}main page{1}.', '<a class="is-link" href="' . $site_config['paths']['baseurl'] . '/messages.php?action=view_mailbox">', '</a>') . '</li>
                        </ul></td>
                    </tr>
                    <tr>
                        <td colspan="2" class="has-text-centered">
                            <input type="submit" class="button is-small margin20" value="' . _('Edit') . '">
                        </td>
                    </tr>';
} else {
    $all_my_boxes .= '
                    <tr>
                        <td><span>' . _('There are currently no PM boxes to edit.') . '</span></td>
                    </tr>';
}

$per_page_drop_down = '<select name="change_pm_number">';
$i = 20;
while ($i <= ($maxbox > 200 ? 200 : $maxbox)) {
    $per_page_drop_down .= '<option class="body" value="' . $i . '" ' . ($CURUSER['pms_per_page'] == $i ? 'selected' : '') . '>' . $i . _('%d PMs per page', $i) . '</option>';
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
                            <img class='caticon' src='{$site_config['paths']['images_baseurl']}caticons/{$CURUSER['categorie_icon']}/" . htmlsafechars($a['image']) . "' alt='" . htmlsafechars($a['name']) . "'>
                        </a>
                    </span>" : "
                    <span class='left10'>" . htmlsafechars($a['name']) . '</span>';

            $categories .= "
                <span class='margin10 bordered level-center bg-02 tooltipper' title='" . htmlsafechars($a['name']) . "'>
                    <input name='cat{$a['id']}' type='checkbox' " . (!empty($CURUSER['notifs']) && strpos($CURUSER['notifs'], "[cat{$a['id']}]") !== false ? 'checked' : '') . " value='yes'>$image
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
$HTMLOUT .= $top_links . '<h1>' . _('Mailbox Manager') . ' / ' . _('Message Settings') . '</h1>
        <form action="messages.php" method="post" accept-charset="utf-8">
        <input type="hidden" name="action" value="edit_mailboxes">
        <input type="hidden" name="action2" value="add">
        <h2 class="has-text-centered">' . _('Add Mail Boxes') . '</h2>';
$body = '
            <tr>
                <td colspan="2" class="has-text-centered">
                    ' . _pfe('As a {0}, you may have up to {1} PM Box, other than your in, sent and draft boxes.', 'As a {0}, you may have up to {1} PM Boxes, other than your in, sent and draft boxes.', get_user_class_name((int) $CURUSER['class']), $maxboxes) . '<br>' . _pfe('Currently you have {0} custom box. You may add up to {1} additional mailboxes.', 'Currently you have {0} custom boxes. You may add up to {1}additional mailboxes.', $count_boxes, $maxboxes - $count_boxes) . '
                    <p class="top10">
                        <span>' . _fe('The following characters can be used: {0}. All other characters will be ignored.', 'a-z, A-Z, 1-9, - and _') . '
                    </p>
                </td>
            </tr>';

for ($i = 1; $i < 6; ++$i) {
    $body .= '
            <tr>
                <td><span>box ' . $i . ':</span></td>
                <td><input type="text" name="new[]" class="w-100" minlength="3" maxlength="15"></td>
            </tr>';
}

$body .= '
            <tr>
                <td colspan="2" class="has-text-centered">
                    ' . _('Only fill in add as many boxes that you would like to add and click "Add"') . '<br>
                    ' . _('Blank entries will be ignored.') . '<br>
                    <input type="submit" class="button is-small margin20" name="move" value="' . _('Add') . '">
                </td>
            </tr>
        </form>';

$HTMLOUT .= main_table($body);
$HTMLOUT .= '<h2 class="top20 has-text-centered">' . _('Edit / Delete Mail Boxes') . '</h2>';
$HTMLOUT .= '
        <form action="' . $site_config['paths']['baseurl'] . '/messages.php" method="post" accept-charset="utf-8">
        <input type="hidden" name="action" value="edit_mailboxes">
        <input type="hidden" name="action2" value="edit_boxes">';
$HTMLOUT .= main_table($all_my_boxes);
$HTMLOUT .= '
        </form>';
$cache->delete('user_' . $CURUSER['id']);
$show_pm_avatar = ($CURUSER['opt2'] & user_options_2::SHOW_PM_AVATAR) === user_options_2::SHOW_PM_AVATAR;
$HTMLOUT .= '<h2 class="top20 has-text-centered">' . _('Message Settings') . '</h2>';
$HTMLOUT .= main_table('
    <tr>
        <td class="w-25"><span>' . _('PMs per page:') . '</span></td>
        <td>
        <form action="messages.php" method="post" accept-charset="utf-8">
        <input type="hidden" name="action" value="edit_mailboxes">
        <input type="hidden" name="action2" value="message_settings">
        ' . $per_page_drop_down . ' [ ' . _('Select how many PMs you would like to see per page.') . ' ]</td>
    </tr>
    <tr>
        <td><span>' . _('Avatars:') . '</span></td>
        <td>
        <select name="show_pm_avatar">
        <option value="yes" ' . ($show_pm_avatar ? 'selected' : '') . '>' . _('show avatars on view mailbox') . '</option>
        <option value="no" ' . (!$show_pm_avatar ? 'selected' : '') . '>' . _("don't show avatars on view mailbox") . '</option>
        </select> [ ' . _('Show avatars when viewing your mailboxes.') . ' ] </td>
    </tr>
    <tr>
        <td><span>' . _('Accept PMs:') . '</span></td>
        <td>
        <input type="radio" name="acceptpms" ' . ($CURUSER['acceptpms'] === 'yes' ? 'checked' : '') . ' value="yes">' . _('All (except blocks)') . '
        <input type="radio" name="acceptpms" ' . ($CURUSER['acceptpms'] === 'friends' ? 'checked' : '') . ' value="friends">' . _('Friends only') . '
        <input type="radio" name="acceptpms" ' . ($CURUSER['acceptpms'] === 'no' ? 'checked' : '') . ' value="no">' . _('Staff only') . '</td>
    </tr>
    <tr>
        <td><span>' . _('Save PMs:') . '</span></td>
        <td><input type="checkbox" name="save_pms" ' . ($CURUSER['savepms'] === 'yes' ? 'checked' : '') . ' value="yes"> [' . _("Default for 'Save PM to Sentbox'") . ' ] </td>
    </tr>
    <tr>
        <td><span>' . _('Delete PMs:') . '</span></td>
        <td><input type="checkbox" name="deletepms" ' . ($CURUSER['deletepms'] === 'yes' ? 'checked' : '') . ' value="yes"> [ ' . _("Default for 'Delete PM on reply'") . ' ] </td>
    </tr>
    <tr>
        <td><span>' . _('Email notification:') . '</span></td>
        <td><input type="checkbox" name="pmnotif" ' . (!empty($CURUSER['notifs']) && strpos($CURUSER['notifs'], '[pm]') !== false ? 'checked' : '') . '  value="yes">' . _('Notify me when I have received a PM') . '</td>
    </tr>
    <tr>
        <td></td>
        <td><input type="checkbox" name="emailnotif" ' . (!empty($CURUSER['notifs']) && strpos($CURUSER['notifs'], '[email]') !== false ? 'checked' : '') . '  value="yes">' . _('Notify me when a torrent is uploaded in one of my default browsing categories.') . '</td>
    </tr>
    <tr>
        <td><span>' . _('Categories:') . '</span></td>
        <td><a class="is-link"  title="' . _('Click for more info') . '" id="cat_open">' . _('show / hide categories') . '</a>[ ' . _('for torrent notifications') . ']
        <div id="defcat" class="is_hidden">' . _('Your default categories can be changed here as well.') . '<br>' . $categories . '</div></td>
    </tr>
    <tr>
        <td colspan="2" class="has-text-centered">
        <input type="submit" class="button is-small margin20" value="' . _('Change') . '"></form></td>
    </tr>
    </table></form>');
