<?php

declare(strict_types = 1);

use Pu239\Message;
use Pu239\User;

require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_bbcode.php';
require_once CLASS_DIR . 'class_check.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
$stdhead = [
    'css' => [
        get_file_name('sceditor_css'),
    ],
];
$stdfoot = [
    'js' => [
        get_file_name('mass_bonus_js'),
        get_file_name('sceditor_js'),
    ],
];
$lang = array_merge($lang, load_language('ad_bonus_for_members'));
global $container, $site_config;

$user_stuffs = $container->get(User::class);
$message_stuffs = $container->get(Message::class);
$dt = TIME_NOW;
$h1_thingie = $HTMLOUT = '';
$good_stuff = [
    'upload_credit',
    'karma',
    'freeslots',
    'invite',
    'pm',
];

$action = !empty($_POST['bonus_options_1']) && in_array($_POST['bonus_options_1'], $good_stuff) ? $_POST['bonus_options_1'] : '';
$free_for = !empty($_POST['free_for_classes']) && is_array($_POST['free_for_classes']) ? '(' . implode(', ', $_POST['free_for_classes']) . ')' : '';
if (empty($free_for)) {
    $action = '';
    unset($_POST);
}
global $CURUSER;

switch ($action) {
    case 'upload_credit':
        $GB = isset($_POST['GB']) ? (int) $_POST['GB'] : 0;
        if ($GB < 1073741824 || $GB > 53687091200) {
            stderr($lang['bonusmanager_up_err'], $lang['bonusmanager_up_err1']);
        }
        $bonus_added = $GB / 1073741824;
        $res_GB = sql_query('SELECT id, uploaded, modcomment FROM users WHERE enabled = "yes" AND suspended = "no" AND class IN ' . $free_for) or sqlerr(__FILE__, __LINE__);
        $pm_values = $user_values = [];
        if (mysqli_num_rows($res_GB) > 0) {
            while ($arr_GB = mysqli_fetch_assoc($res_GB)) {
                $GB_new = $arr_GB['uploaded'] + $GB;
                $modcomment = $arr_GB['modcomment'];
                $modcomment = get_date((int) $dt, 'DATE', 1) . ' - ' . $bonus_added . $lang['bonusmanager_up_modcomment'] . $modcomment;
                $msg = "{$lang['bonusmanager_up_addedmsg']}{$bonus_added}{$lang['bonusmanager_up_addedmsg1']}{$site_config['site']['name']}{$lang['bonusmanager_up_addedmsg2']}{$lang['bonusmanager_up_addedmsg22']}{$GB} {$GB_new}";
                $pm_values[] = [
                    'receiver' => (int) $arr_GB['id'],
                    'added' => $dt,
                    'msg' => $msg,
                    'subject' => $lang['bonusmanager_up_added'],
                ];
                $set = [
                    'uploaded' => $GB_new,
                    'modcomment' => $modcomment,
                ];
                $user_stuffs->update($set, (int) $arr_GB['id']);
            }
            $count = count($pm_values);
            if ($count > 0) {
                $message_stuffs->insert($pm_values);
                write_log($lang['bonusmanager_up_writelog'] . $count . $lang['bonusmanager_up_writelog1'] . $CURUSER['username']);
            }
            unset($pm_values, $user_values, $user_updates, $count);
        }
        header('Location: ' . $_SERVER['PHP_SELF'] . '?tool=mass_bonus_for_members&action=mass_bonus_for_members&GB=1');
        die();
        break;

    case 'karma':
        $karma = isset($_POST['karma']) ? (int) $_POST['karma'] : 0;
        if ($karma < 100 || $karma > 5000) {
            stderr($lang['bonusmanager_karma_err'], $lang['bonusmanager_karma_err1']);
        }
        $res_karma = sql_query('SELECT id, seedbonus, modcomment FROM users WHERE enabled = "yes" AND suspended = "no" AND class IN ' . $free_for) or sqlerr(__FILE__, __LINE__);
        $pm_values = $user_values = [];
        if (mysqli_num_rows($res_karma) > 0) {
            $msg = $lang['bonusmanager_karma_addedmsg'] . $karma . $lang['bonusmanager_karma_addedmsg1'] . $site_config['site']['name'] . $lang['bonusmanager_karma_addedmsg2'];
            while ($arr_karma = mysqli_fetch_assoc($res_karma)) {
                $karma_new = $arr_karma['seedbonus'] + $karma;
                $modcomment = $arr_karma['modcomment'];
                $modcomment = get_date((int) $dt, 'DATE', 1) . ' - ' . $karma . $lang['bonusmanager_karma_modcomment'] . $modcomment;
                $pm_values[] = [
                    'receiver' => (int) $arr_karma['id'],
                    'added' => $dt,
                    'msg' => $msg,
                    'subject' => $lang['bonusmanager_karma_added'],
                ];
                $set = [
                    'seedbonus' => $karma_new,
                    'modcomment' => $modcomment,
                ];
                $user_stuffs->update($set, (int) $arr_karma['id']);
            }
            $count = count($pm_values);
            if ($count > 0) {
                $message_stuffs->insert($pm_values);
                write_log($lang['bonusmanager_karma_writelog'] . $count . $lang['bonusmanager_karma_writelog1'] . $CURUSER['username']);
            }
            unset($pm_values, $user_values, $user_updates, $count);
        }
        header('Location: ' . $_SERVER['PHP_SELF'] . '?tool=mass_bonus_for_members&action=mass_bonus_for_members&karma=1');
        die();
        break;

    case 'freeslots':
        $freeslots = isset($_POST['freeslots']) ? (int) $_POST['freeslots'] : 0;
        if ($freeslots < 1 || $freeslots > 50) {
            stderr($lang['bonusmanager_freeslots_err'], $lang['bonusmanager_freeslots_err1']);
        }
        $res_freeslots = sql_query('SELECT id, freeslots, modcomment FROM users WHERE enabled = "yes" AND suspended = "no" AND class IN ' . $free_for) or sqlerr(__FILE__, __LINE__);
        $pm_values = $user_values = [];
        if (mysqli_num_rows($res_freeslots) > 0) {
            $msg = $lang['bonusmanager_freeslots_addedmsg'] . $freeslots . $lang['bonusmanager_freeslots_addedmsg1'] . $site_config['site']['name'] . $lang['bonusmanager_freeslots_addedmsg2'];
            while ($arr_freeslots = mysqli_fetch_assoc($res_freeslots)) {
                $freeslots_new = $arr_freeslots['freeslots'] + $freeslots;
                $modcomment = $arr_freeslots['modcomment'];
                $modcomment = get_date((int) $dt, 'DATE', 1) . ' - ' . $freeslots . $lang['bonusmanager_freeslots_modcomment'] . $modcomment;
                $pm_values[] = [
                    'receiver' => (int) $arr_freeslots['id'],
                    'added' => $dt,
                    'msg' => $msg,
                    'subject' => $lang['bonusmanager_freeslots_added'],
                ];
                $set = [
                    'freeslots' => $freeslots_new,
                    'modcomment' => $modcomment,
                ];
                $user_stuffs->update($set, (int) $arr_freeslots['id']);
            }
            $count = count($pm_values);
            if ($count > 0) {
                $message_stuffs->insert($pm_values);
                write_log($lang['bonusmanager_freeslots_writelog'] . $count . $lang['bonusmanager_freeslots_writelog1'] . $CURUSER['username']);
            }
            unset($pm_values, $user_values, $user_updates, $count);
        }
        header('Location: ' . $_SERVER['PHP_SELF'] . '?tool=mass_bonus_for_members&action=mass_bonus_for_members&freeslots=1');
        die();
        break;

    case 'invite':
        $invites = isset($_POST['invites']) ? (int) $_POST['invites'] : 0;
        if ($invites < 1 || $invites > 50) {
            stderr($lang['bonusmanager_invite_err'], $lang['bonusmanager_invite_err1']);
        }
        $res_invites = sql_query('SELECT id, invites, modcomment FROM users WHERE enabled = "yes" AND suspended = "no" AND invite_on = "yes" AND class IN ' . $free_for);
        $pm_buffer = $users_buffer = [];
        if (mysqli_num_rows($res_invites) > 0) {
            $msg = $lang['bonusmanager_invite_addedmsg'] . $invites . $lang['bonusmanager_invite_addedmsg1'] . $site_config['site']['name'] . $lang['bonusmanager_invite_addedmsg2'];
            while ($arr_invites = mysqli_fetch_assoc($res_invites)) {
                $invites_new = $arr_invites['invites'] + $invites;
                $modcomment = $arr_invites['modcomment'];
                $modcomment = get_date((int) $dt, 'DATE', 1) . ' - ' . $invites . $lang['bonusmanager_invite_modcomment'] . $modcomment;
                $pm_values[] = [
                    'receiver' => (int) $arr_invites['id'],
                    'added' => $dt,
                    'msg' => $msg,
                    'subject' => $lang['bonusmanager_invite_added'],
                ];
                $set = [
                    'invites' => $invites_new,
                    'modcomment' => $modcomment,
                ];
                $user_stuffs->update($set, (int) $arr_invites['id']);
            }
            $count = count($pm_values);
            if ($count > 0) {
                $message_stuffs->insert($pm_values);
                write_log($lang['bonusmanager_invite_writelog'] . $count . $lang['bonusmanager_invite_writelog1'] . $CURUSER['username']);
            }
            unset($pm_values, $user_values, $user_updates, $count);
        }
        header('Location: ' . $_SERVER['PHP_SELF'] . '?tool=mass_bonus_for_members&action=mass_bonus_for_members&invites=1');
        die();
        break;

    case 'pm':
        if (!isset($_POST['subject'])) {
            stderr($lang['bonusmanager_pm_err'], $lang['bonusmanager_pm_err1']);
        }
        if (!isset($_POST['body'])) {
            stderr($lang['bonusmanager_pm_err'], $lang['bonusmanager_pm_err2']);
        }
        $res_pms = sql_query('SELECT id FROM users WHERE enabled = "yes" AND suspended = "no" AND class IN ' . $free_for);
        $pm_values = [];
        if (mysqli_num_rows($res_pms) > 0) {
            while ($arr_pms = mysqli_fetch_assoc($res_pms)) {
                $pm_values[] = [
                    'receiver' => (int) $arr_pms['id'],
                    'added' => $dt,
                    'msg' => htmlsafechars($_POST['body']),
                    'subject' => htmlsafechars($_POST['subject']),
                ];
            }
            $count = count($pm_values);
            if ($count > 0) {
                $message_stuffs->insert($pm_values);
                write_log($lang['bonusmanager_pm_writelog'] . $count . $lang['bonusmanager_pm_writelog1'] . $CURUSER['username']);
            }
            unset($pm_values, $count);
        }
        header('Location: ' . $_SERVER['PHP_SELF'] . '?tool=mass_bonus_for_members&action=mass_bonus_for_members&pm=1');
        die();
        break;
}

$all_classes_check_boxes = '
    <div class="level-center">';
for ($i = UC_MIN; $i <= UC_MAX; ++$i) {
    $all_classes_check_boxes .= '
        <div>
            <input type="checkbox" name="free_for_classes[]" value="' . $i . '" checked>
            <span style="font-weight: bold;color: #' . get_user_class_color($i) . ';">' . get_user_class_name($i) . '</span>
        </div>';
}
$all_classes_check_boxes .= '
    </div>';

//dd($all_classes_check_boxes);
$bonus_GB = '<select name="GB">
        <option class="head" value="">' . $lang['bonusmanager_up_add'] . '</option>
        <option class="body" value="1073741824">' . $lang['bonusmanager_up_1gb'] . '</option>
        <option class="body" value="2147483648">' . $lang['bonusmanager_up_2gb'] . '</option>
        <option class="body" value="3221225472">' . $lang['bonusmanager_up_3gb'] . '</option>
        <option class="body" value="4294967296">' . $lang['bonusmanager_up_4gb'] . '</option>
        <option class="body" value="5368709120">' . $lang['bonusmanager_up_5gb'] . '</option>
        <option class="body" value="6442450944">' . $lang['bonusmanager_up_6gb'] . '</option>
        <option class="body" value="7516192768">' . $lang['bonusmanager_up_7gb'] . '</option>
        <option class="body" value="8589934592">' . $lang['bonusmanager_up_8gb'] . '</option>
        <option class="body" value="9663676416">' . $lang['bonusmanager_up_9gb'] . '</option>
        <option class="body" value="10737418240">' . $lang['bonusmanager_up_10gb'] . '</option>
        <option class="body" value="16106127360">' . $lang['bonusmanager_up_15gb'] . '</option>
        <option class="body" value="21474836480">' . $lang['bonusmanager_up_20gb'] . '</option>
        <option class="body" value="26843545600">' . $lang['bonusmanager_up_25gb'] . '</option>
        <option class="body" value="32212254720">' . $lang['bonusmanager_up_30gb'] . '</option>
        <option class="body" value="53687091200">' . $lang['bonusmanager_up_50gb'] . '</option>
        </select>' . $lang['bonusmanager_up_amount'] . ' ';
$karma_drop_down = '
        <select name="karma">
        <option class="head" value="">' . $lang['bonusmanager_karma_add'] . '</option>';
$i = 100;
while ($i <= 5000) {
    $karma_drop_down .= '<option class="body" value="' . $i . '.0">' . $i . ' ' . $lang['bonusmanager_karma_points'] . '</option>';
    $i = ($i < 1000 ? $i = $i + 100 : $i = $i + 500);
}
$karma_drop_down .= '</select> ' . $lang['bonusmanager_karma_amount'] . ' ';
$free_leech_slot_drop_down = '
        <select name="freeslots">
        <option class="head" value="">' . $lang['bonusmanager_freeslots_add'] . '</option>';
$i = 1;
while ($i <= 50) {
    $free_leech_slot_drop_down .= '<option class="body" value="' . $i . '.0">' . $i . $lang['bonusmanager_freeslots_freeslot'] . ($i !== 1 ? 's' : '') . '</option>';
    $i = ($i < 10 ? $i = $i + 1 : $i = $i + 5);
}
$free_leech_slot_drop_down .= '</select>' . $lang['bonusmanager_freeslots_amount'] . ' ';
$invites_drop_down = '
        <select name="invites">
        <option class="head" value="">' . $lang['bonusmanager_invite_add'] . '</option>';
$i = 1;
while ($i <= 50) {
    $invites_drop_down .= '<option class="body" value="' . $i . '.0">' . $i . ' ' . $lang['bonusmanager_invite_add'] . ($i !== 1 ? 's' : '') . '</option>';
    $i = ($i < 10 ? $i = $i + 1 : $i = $i + 5);
}
$invites_drop_down .= '</select>' . $lang['bonusmanager_invite_amount'] . '';

$subject = isset($_POST['subject']) ? htmlsafechars($_POST['subject']) : $lang['bonusmanager_pm_masspm'];
$body = isset($_POST['body']) ? htmlsafechars($_POST['body']) : $lang['bonusmanager_pm_texthere'];
$pm_drop_down = '
                <table class="w-100">
                    <tr>
                        <td colspan="2">' . $lang['bonusmanager_pm_send'] . '</td>
                    </tr>
                    <tr>
                        <td><span class="has-text-weight-bold">' . $lang['bonusmanager_pm_subject'] . '</span></td>
                        <td>
                            <input type="hidden" name="pm" value="pm">
                            <input name="subject" type="text" class="w-100" value="' . $subject . '">
                        </td>
                    </tr>
                    <tr>
                        <td><span class="has-text-weight-bold">' . $lang['bonusmanager_pm_body'] . '</span></td>
                        <td class="is-paddingless">' . BBcode($body, '', 300) . '</td>
                    </tr>
                </table>';
$drop_down = '
        <select name="bonus_options_1" id="bonus_options_1">
        <option value="">' . $lang['bonusmanager_select'] . '</option>
        <option value="upload_credit">' . $lang['bonusmanager_select_upload'] . '</option>
        <option value="karma">' . $lang['bonusmanager_select_karma'] . '</option>
        <option value="freeslots">' . $lang['bonusmanager_select_freeslots'] . '</option>
        <option value="invite">' . $lang['bonusmanager_select_invite'] . '</option>
        <option value="pm">' . $lang['bonusmanager_select_pm'] . '</option>
        <option value="">' . $lang['bonusmanager_reset'] . '</option>
        </select>';

$h1_thingie .= (isset($_GET['GB']) ? ($_GET['GB'] === 1 ? '<h2>' . $lang['bonusmanager_h1_upload'] . '</h2>' : '<h2>' . $lang['bonusmanager_h1_upload'] . '</h2>') : '');
$h1_thingie .= (isset($_GET['karma']) ? ($_GET['karma'] === 1 ? '<h2>' . $lang['bonusmanager_h1_karma'] . '</h2>' : '<h2>' . $lang['bonusmanager_h1_karma1'] . '</h2>') : '');
$h1_thingie .= (isset($_GET['freeslots']) ? ($_GET['freeslots'] === 1 ? '<h2>' . $lang['bonusmanager_h1_freeslot'] . '<h2>' : '<h2>' . $lang['bonusmanager_h1_freeslot1'] . '</h2>') : '');
$h1_thingie .= (isset($_GET['invites']) ? ($_GET['invites'] === 1 ? '<h2>' . $lang['bonusmanager_h1_invite'] . '</h2>' : '<h2>' . $lang['bonusmanager_h1_invite1'] . '</h2>') : '');
$h1_thingie .= (isset($_GET['pm']) ? ($_GET['pm'] === 1 ? '<h2>' . $lang['bonusmanager_h1_pm'] . '</h2>' : '<h2>' . $lang['bonusmanager_h1_pm1'] . '</h2>') : '');
$HTMLOUT .= '<h1 class="has-text-centered">' . $site_config['site']['name'] . ' ' . $lang['bonusmanager_mass_bonus'] . '</h1>' . $h1_thingie;
$HTMLOUT .= '
    <form name="inputform" method="post" action="' . $_SERVER['PHP_SELF'] . '?tool=mass_bonus_for_members&amp;action=mass_bonus_for_members" enctype="multipart/form-data" accept-charset="utf-8">';
$body = '
        <tr>
            <td class="colhead" colspan="2">' . $lang['bonusmanager_mass_bonus_selected'] . '</td>
        </tr>
        <tr>
            <td><span class="has-text-weight-bold">' . $lang['bonusmanager_apply_bonus'] . '</span></td>
            <td>
                <div>' . $all_classes_check_boxes . '</div>
            </td>
        </tr>
        <tr>
            <td class="w-25"><span class="has-text-weight-bold">' . $lang['bonusmanager_bonus_type'] . '</span></td>
            <td>' . $drop_down . '
                <div id="div_upload_credit" class="select_me"><br>' . $bonus_GB . '</div>
                <div id="div_karma" class="select_me"><br>' . $karma_drop_down . '</div>
                <div id="div_freeslots" class="select_me"><br>' . $free_leech_slot_drop_down . '</div>
                <div id="div_invite" class="select_me"><br>' . $invites_drop_down . '</div>
                <div id="div_pm" class="select_me"><br>' . $pm_drop_down . '</div>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <div class="has-text-centered margin20">' . $lang['bonusmanager_note'] . '</div>
                <div class="has-text-centered margin20">
                    <input type="submit" class="button is-small" value="' . $lang['bonusmanager_doit'] . '">
                </div>
            </td>
        </tr>';
$HTMLOUT .= main_table($body) . '
    </form>';

echo stdhead($lang['bonusmanager_h1_upload'], $stdhead) . wrapper($HTMLOUT) . stdfoot($stdfoot);
