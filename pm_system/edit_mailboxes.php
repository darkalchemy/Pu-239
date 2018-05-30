<?php

global $CURUSER, $site_config, $lang, $cache, $h1_thingie;

$all_my_boxes = $curuser_cache = $user_cache = $categories = '';
if (isset($_POST['action2'])) {
    $good_actions = [
        'add',
        'edit_boxes',
        'change_pm',
        'message_settings',
    ];
    $action2 = (isset($_POST['action2']) ? strip_tags($_POST['action2']) : '');
    $worked  = $deleted  = '';
    if (!in_array($action2, $good_actions)) {
        stderr($lang['pm_error'], $lang['pm_edmail_error']);
    }

    switch ($action2) {
        case 'change_pm':
            $change_pm_number = (isset($_POST['change_pm_number']) ? intval($_POST['change_pm_number']) : 20);
            sql_query('UPDATE users SET pms_per_page = ' . sqlesc($change_pm_number) . ' WHERE id = ' . sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);
            $cache->update_row('user' . $CURUSER['id'], [
                'pms_per_page' => $change_pm_number,
            ], $site_config['expires']['user_cache']);
            header('Location: pm_system.php?action=edit_mailboxes&pm=1');
            die();
            break;

        case 'add':
            if ($_POST['new'] === '') {
                stderr($lang['pm_error'], $lang['pm_edmail_err']);
            }
            $res     = sql_query('SELECT boxnumber FROM pmboxes WHERE userid = ' . sqlesc($CURUSER['id']) . ' ORDER BY boxnumber  DESC LIMIT 1') or sqlerr(__FILE__, __LINE__);
            $box_arr = mysqli_fetch_row($res);
            $box     = ($box_arr[0] < 2 ? 2 : ($box_arr[0] + 1));
            $new_box = $_POST['new'];
            foreach ($new_box as $key => $add_it) {
                if (valid_username($add_it) && $add_it !== '') {
                    $name = htmlsafechars($add_it);
                    sql_query('INSERT INTO pmboxes (userid, name, boxnumber) VALUES (' . sqlesc($CURUSER['id']) . ', ' . sqlesc($name) . ', ' . sqlesc($box) . ')') or sqlerr(__FILE__, __LINE__);
                    $cache->delete('get_all_boxes_' . $CURUSER['id']);
                    $cache->delete('insertJumpTo' . $CURUSER['id']);
                }
                ++$box;
                $worked = '&boxes=1';
            }
            header('Location: pm_system.php?action=edit_mailboxes' . $worked);
            die();
            break;

        case 'edit_boxes':
            $res = sql_query('SELECT * FROM pmboxes WHERE userid=' . sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);
            if (mysqli_num_rows($res) === 0) {
                stderr($lang['pm_error'], $lang['pm_edmail_err1']);
            }
            while ($row = mysqli_fetch_assoc($res)) {
                if (valid_username($_POST['edit' . $row['id']]) && $_POST['edit' . $row['id']] !== '' && $_POST['edit' . $row['id']] !== $row['name']) {
                    $name = htmlsafechars($_POST['edit' . $row['id']]);
                    sql_query('UPDATE pmboxes SET name=' . sqlesc($name) . ' WHERE id=' . sqlesc($row['id']) . ' LIMIT 1') or sqlerr(__FILE__, __LINE__);
                    $cache->delete('get_all_boxes_' . $CURUSER['id']);
                    $cache->delete('insertJumpTo' . $CURUSER['id']);
                    $worked = '&name=1';
                }
                if ($_POST['edit' . $row['id']] == '') {
                    $remove_messages_res = sql_query('SELECT id FROM messages WHERE location=' . sqlesc($row['boxnumber']) . '  AND receiver=' . sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);
                    while ($remove_messages_arr = mysqli_fetch_assoc($remove_messages_res)) {
                        sql_query('UPDATE messages SET location=1 WHERE id=' . sqlesc($remove_messages_arr['id'])) or sqlerr(__FILE__, __LINE__);
                    }
                    sql_query('DELETE FROM pmboxes WHERE id=' . sqlesc($row['id']) . '  LIMIT 1') or sqlerr(__FILE__, __LINE__);
                    $cache->delete('get_all_boxes_' . $CURUSER['id']);
                    $cache->delete('insertJumpTo' . $CURUSER['id']);
                    $deleted = '&box_delete=1';
                }
            }
            header('Location: pm_system.php?action=edit_mailboxes' . $deleted . $worked);
            die();
            break;

        case 'message_settings':
            $updateset                       = [];
            $change_pm_number                = (isset($_POST['change_pm_number']) ? intval($_POST['change_pm_number']) : 20);
            $updateset[]                     = 'pms_per_page = ' . sqlesc($change_pm_number);
            $curuser_cache['pms_per_page']   = $change_pm_number;
            $user_cache['pms_per_page']      = $change_pm_number;
            $show_pm_avatar = ((isset($_POST['show_pm_avatar']) && $_POST['show_pm_avatar'] === 'yes') ? 'yes' : 'no');
            $updateset[]                     = 'show_pm_avatar = ' . sqlesc($show_pm_avatar);
            $curuser_cache['show_pm_avatar'] = $show_pm_avatar;
            $user_cache['show_pm_avatar']    = $show_pm_avatar;
            $acceptpms = ((isset($_POST['acceptpms']) && $_POST['acceptpms'] === 'yes') ? 'yes' : ((isset($_POST['acceptpms']) && $_POST['acceptpms'] === 'friends') ? 'friends' : 'no'));
            $updateset[]                     = 'acceptpms = ' . sqlesc($acceptpms);
            $curuser_cache['acceptpms']      = $acceptpms;
            $user_cache['acceptpms']         = $acceptpms;
            $save_pms                        = ((isset($_POST['save_pms'])) ? 'yes' : 'no');
            $updateset[]                     = 'savepms = ' . sqlesc($save_pms);
            $curuser_cache['savepms']        = $save_pms;
            $user_cache['savepms']           = $save_pms;
            $deletepms                       = ((isset($_POST['deletepms']) && $_POST['deletepms'] == 'yes') ? 'yes' : 'no');
            $updateset[]                     = 'deletepms = ' . sqlesc($deletepms);
            $curuser_cache['deletepms']      = $deletepms;
            $user_cache['deletepms']         = $deletepms;
            $pmnotif                         = (isset($_POST['pmnotif']) ? $_POST['pmnotif'] : '');
            $emailnotif                      = (isset($_POST['emailnotif']) ? $_POST['emailnotif'] : '');
            $notifs                          = ($pmnotif == 'yes' ? $lang['pm_edmail_pm_1'] : '');
            $notifs .= ($emailnotif == 'yes' ? $lang['pm_edmail_email_1'] : '');
            $cats = genrelist();
            $r    = sql_query('SELECT id FROM categories') or sqlerr(__FILE__, __LINE__);
            $rows = mysqli_num_rows($r);
            for ($i = 0; $i < $rows; ++$i) {
                $a = mysqli_fetch_assoc($r);
                if (isset($_POST["cat{$a['id']}"]) && $_POST["cat{$a['id']}"] === 'yes') {
                    $notifs .= "[cat{$a['id']}]";
                }
            }
            $updateset[]             = 'notifs = ' . sqlesc($notifs) . '';
            $curuser_cache['notifs'] = $notifs;
            $user_cache['notifs']    = $notifs;
            if ($user_cache) {
                $cache->update_row('user' . $CURUSER['id'], $user_cache, $site_config['expires']['user_cache']);
            }
            sql_query('UPDATE users SET ' . implode(', ', $updateset) . ' WHERE id = ' . sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);
            $worked = '&pms=1';
            header('Location: pm_system.php?action=edit_mailboxes' . $worked);
            die();
            break;
    }
}

$res = sql_query('SELECT * FROM pmboxes WHERE userid=' . sqlesc($CURUSER['id']) . ' ORDER BY name ASC') or sqlerr(__FILE__, __LINE__);
if (mysqli_num_rows($res) > 0) {
    while ($row = mysqli_fetch_assoc($res)) {
        $res_count = sql_query('SELECT COUNT(id) FROM messages WHERE  location = ' . sqlesc($row['boxnumber']) . ' AND receiver = ' . sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);
        $arr_count = mysqli_fetch_row($res_count);
        $messages  = (int) $arr_count[0];
        $all_my_boxes .= '
                    <tr>
                        <td>
                        <form action="pm_system.php" method="post">
                        <input type="hidden" name="action" value="edit_mailboxes" />
                        <input type="hidden" name="action2" value="edit_boxes" />' . $lang['pm_edmail_box'] . '' . ((int) $row['boxnumber'] - 1) . ' <span style="font-weight: bold;">' . htmlsafechars($row['name']) . ':</span></td>
                        <td colspan="2"><input type="text" name="edit' . ((int) $row['id']) . '" value="' . htmlsafechars($row['name']) . '" class="w-100" />' . $lang['pm_edmail_contain'] . '' . htmlsafechars($messages) . '' . $lang['pm_edmail_messages'] . '</td>
                    </tr>';
    }
    $all_my_boxes .= '
                    <tr>
                        <td></td>
                        <td colspan="2">' . $lang['pm_edmail_names'] . '<br>
                        ' . $lang['pm_edmail_if'] . '</td>
                    </tr>
                    <tr>
                        <td></td>
                        <td colspan="2"><span style="font-weight: bold;">' . $lang['pm_edmail_note'] . '</span>
                        <ul>
                            <li>' . $lang['pm_edmail_if1'] . '</li>
                            <li>' . $lang['pm_edmail_if2'] . '<a class="altlink" href="pm_system.php?action=view_mailbox">' . $lang['pm_edmail_main'] . '</a>.</li>
                        </ul></td>
                    </tr>
                    <tr>
                        <td colspan="3">
                        <input type="submit" class="button is-small" value="' . $lang['pm_edmail_edit'] . '" /></form></td>
                    </tr>';
} else {
    $all_my_boxes .= '
                    <tr>
                        <td></td>
                        <td colspan="2"><span style="font-weight: bold;">' . $lang['pm_edmail_nobox'] . '</span><br></td>
                    </tr>';
}
//=== per page drop down
$per_page_drop_down = '<select name="change_pm_number">';
$i                  = 20;
while ($i <= ($maxbox > 200 ? 200 : $maxbox)) {
    $per_page_drop_down .= '<option class="body" value="' . $i . '" ' . ($CURUSER['pms_per_page'] == $i ? ' selected' : '') . '>' . $i . '' . $lang['pm_edmail_perpage'] . '</option>';
    $i = ($i < 100 ? $i = $i + 10 : $i = $i + 25);
}
$per_page_drop_down .= '</select>';
//==cats
$r = sql_query('SELECT id, image, name FROM categories ORDER BY name') or sqlerr(__FILE__, __LINE__);
if (mysqli_num_rows($r) > 0) {
    $categories .= "<table><tr>\n";
    $i = 0;
    while ($a = mysqli_fetch_assoc($r)) {
        $categories .= ($i && $i % 2 == 0) ? '</tr><tr>' : '';
        $categories .= "<td class='bottom' style='padding-right: 5px'><input name='cat" . (int)$a['id'] . "' type='checkbox' " . (strpos($CURUSER['notifs'], "[cat{$a['id']}]") !== false ? " checked" : '') . " value='yes' />&#160;<a class='catlink' href='browse.php?cat=" . (int)$a['id'] . "'><img src='{$site_config['pic_baseurl']}caticons/" . get_category_icons() . "/" . htmlsafechars($a['image']) . "' alt='" . htmlsafechars($a['name']) . "' title='" . htmlsafechars($a['name']) . "' /></a>&#160;" . htmlspecialchars($a['name']) . "</td>\n";
        ++$i;
    }
    $categories .= "</tr></table>\n";
}
//=== make up page
$HTMLOUT .= '
<script>
/*<![CDATA[*/
$(document).ready(function()    {
//=== cats
$("#cat_open").click(function() {
  $("#cat").slideToggle("slow", function() {

  });
});
});
/*]]>*/
</script>';
$HTMLOUT .= $top_links . '<h1>' . $lang['pm_edmail_title'] . '</h1>' . $h1_thingie . '
        <form action="pm_system.php" method="post">
        <input type="hidden" name="action" value="edit_mailboxes" />
        <input type="hidden" name="action2" value="add" />
    <table class="table table-bordered">
    <tr>
        <td class="colhead" colspan="3"><h1>' . $lang['pm_edmail_add_mbox'] . '</h1></td>
    </tr>
    <tr>
        <td></td>
        <td colspan="2">' . $lang['pm_edmail_as_a'] . '' . get_user_class_name($CURUSER['class']) . $lang['pm_edmail_you_may'] . $maxboxes . $lang['pm_edmail_pm_box'] . ($maxboxes !== 1 ? $lang['pm_edmail_pm_boxes'] : '') . '' . $lang['pm_edmail_other'] . '<br>' . $lang['pm_edmail_currently'] . '' . mysqli_num_rows($res) . $lang['pm_edmail_custom'] . (mysqli_num_rows($res) !== 1 ? $lang['pm_edmail_custom_es'] : '') . $lang['pm_edmail_may_add'] . ($maxboxes - mysqli_num_rows($res)) . '' . $lang['pm_edmail_more_extra'] . '<br><br>
        <span style="font-weight: bold;">' . $lang['pm_edmail_following'] . '</span>' . $lang['pm_edmail_chars'] . '<br></td>
    </tr>';
//=== make loop for oh let's say 5 boxes...
for ($i = 1; $i < 6; ++$i) {
    $HTMLOUT .= '
            <tr>
                <td><span style="font-weight: bold;">box ' . $i . ':</span></td>
                <td><input type="text" name="new[]" class="w-100" maxlength="100" /></td>
                <td></td>
            </tr>';
}
$HTMLOUT .= '
    <tr>
        <td></td>
        <td>' . $lang['pm_edmail_only_fill'] . '<br>
        ' . $lang['pm_edmail_blank'] . '</td>
        <td><input type="submit" class="button is-small" name="move" value="' . $lang['pm_edmail_add'] . '" /></form></td>
    </tr>
    <tr>
        <td class="colhead" colspan="3"><h1>' . $lang['pm_edmail_ed_del'] . '</h1></td>
    </tr>
        ' . $all_my_boxes . '
    <tr>
        <td class="colhead" colspan="3"><h1>' . $lang['pm_edmail_msg_settings'] . '</h1></td>
    </tr>
    <tr>
        <td><span style="font-weight: bold;">' . $lang['pm_edmail_pm_page'] . '</span></td>
        <td>
        <form action="pm_system.php" method="post">
        <input type="hidden" name="action" value="edit_mailboxes" />
        <input type="hidden" name="action2" value="message_settings" />
        ' . $per_page_drop_down . '' . $lang['pm_edmail_s_how_many'] . '</td>
        <td></td>
    </tr>
    <tr>
        <td><span style="font-weight: bold;">' . $lang['pm_edmail_av'] . '</span></td>
        <td>
        <select name="show_pm_avatar">
        <option value="yes" ' . ($CURUSER['show_pm_avatar'] === 'yes' ? ' selected' : '') . '>' . $lang['pm_edmail_show_av'] . '</option>
        <option value="no" ' . ($CURUSER['show_pm_avatar'] === 'no' ? ' selected' : '') . '>' . $lang['pm_edmail_dshow_av'] . '</option>
        </select>' . $lang['pm_edmail_show_av_box'] . '</td>
        <td></td>
    </tr>
    <tr>
        <td><span style="font-weight: bold;">' . $lang['pm_edmail_accept'] . '</span></td>
        <td>
        <input type="radio" name="acceptpms" ' . ($CURUSER['acceptpms'] === 'yes' ? ' checked' : '') . ' value="yes" />' . $lang['pm_edmail_all'] . '
        <input type="radio" name="acceptpms" ' . ($CURUSER['acceptpms'] === 'friends' ? ' checked' : '') . ' value="friends" />' . $lang['pm_edmail_friend'] . '
        <input type="radio" name="acceptpms" ' . ($CURUSER['acceptpms'] === 'no' ? ' checked' : '') . ' value="no" />' . $lang['pm_edmail_staff'] . '</td>
        <td></td>
    </tr>
    <tr>
        <td><span style="font-weight: bold;">' . $lang['pm_edmail_save'] . '</span></td>
        <td><input type="checkbox" name="save_pms" ' . ($CURUSER['savepms'] === 'yes' ? ' checked' : '') . '  />' . $lang['pm_edmail_default'] . '</td>
        <td></td>
    </tr>
    <tr>
        <td><span style="font-weight: bold;">' . $lang['pm_edmail_del_pms'] . '</span></td>
        <td><input type="checkbox" name="deletepms" ' . ($CURUSER['deletepms'] === 'yes' ? ' checked' : '') . ' />' . $lang['pm_edmail_default_r'] . '</td>
        <td></td>
    </tr>
    <tr>
        <td><span style="font-weight: bold;">' . $lang['pm_edmail_email_notif'] . '</span></td>
        <td><input type="checkbox" name="pmnotif" ' . (strpos($CURUSER['notifs'], $lang['pm_edmail_pm_1']) !== false ? ' checked' : '') . '  value="yes" />' . $lang['pm_edmail_notify'] . '</td>
        <td></td>
    </tr>
    <tr>
        <td></td>
        <td><input type="checkbox" name="emailnotif" ' . (strpos($CURUSER['notifs'], $lang['pm_edmail_email_1']) !== false ? ' checked' : '') . '  value="yes" />' . $lang['pm_edmail_notify1'] . '</td>
        <td></td>
    </tr>
    <tr>
        <td><span style="font-weight: bold;">' . $lang['pm_edmail_cats'] . '</span></td>
        <td><a class="altlink"  title="' . $lang['pm_edmail_clickmore'] . '" id="cat_open" style="font-weight:bold;cursor:pointer;">' . $lang['pm_edmail_show_hide'] . '</a>' . $lang['pm_edmail_torr'] . '
        <div id="cat" style="display:none;">' . $lang['pm_edmail_def_cats'] . '<br>' . $categories . '</div></td>
        <td></td>
    </tr>
    <tr>
        <td colspan="3">
        <input type="submit" class="button is-small" value="' . $lang['pm_edmail_change'] . '" /></form></td>
    </tr>
    </table></form>';
