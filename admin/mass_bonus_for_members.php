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
global $container, $site_config;

$users_class = $container->get(User::class);
$messages_class = $container->get(Message::class);
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
            stderr(_('Error'), _('You forgot to select an amount!'));
        }
        $bonus_added = $GB / 1073741824;
        $res_GB = sql_query('SELECT id, uploaded, modcomment FROM users WHERE status = 0 AND class IN ' . $free_for) or sqlerr(__FILE__, __LINE__);
        $pm_values = $user_values = [];
        if (mysqli_num_rows($res_GB) > 0) {
            while ($arr_GB = mysqli_fetch_assoc($res_GB)) {
                $GB_new = $arr_GB['uploaded'] + $GB;
                $modcomment = $arr_GB['modcomment'];
                $modcomment = get_date((int) $dt, 'DATE', 1) . ' - ' . $bonus_added . _('GB Mass Bonus added - AutoSystem.
') . $modcomment;
                $msg = '' . _('Hey,
 we have decided to add') . " $bonus_added " . _('GB upload credit to all classes.
 Cheers') . " {$site_config['site']['name']} " . _('Staff') . '';
                $pm_values[] = [
                    'receiver' => (int) $arr_GB['id'],
                    'added' => $dt,
                    'msg' => $msg,
                    'subject' => _('Upload added'),
                ];
                $set = [
                    'uploaded' => $GB_new,
                    'modcomment' => $modcomment,
                ];
                $users_class->update($set, (int) $arr_GB['id']);
            }
            $count = count($pm_values);
            if ($count > 0) {
                $messages_class->insert($pm_values);
                write_log('' . _('Staff mass bonus - added upload credit to') . " $count " . _('members in all classes by') . " {$CURUSER['username']}");
            }
            unset($pm_values, $user_values, $user_updates, $count);
        }
        header('Location: ' . $_SERVER['PHP_SELF'] . '?tool=mass_bonus_for_members&action=mass_bonus_for_members&GB=1');
        die();
        break;

    case 'karma':
        $karma = isset($_POST['karma']) ? (int) $_POST['karma'] : 0;
        if ($karma < 100 || $karma > 5000) {
            stderr(_('Error'), _('You forgot to select an amount!'));
        }
        $res_karma = sql_query('SELECT id, seedbonus, modcomment FROM users WHERE status = 0 AND class IN ' . $free_for) or sqlerr(__FILE__, __LINE__);
        $pm_values = $user_values = [];
        if (mysqli_num_rows($res_karma) > 0) {
            $msg = '' . _('Hey,
 we have decided to add') . " $karma  " . _('Karma bonus points to all classes.
 Cheers') . " {$site_config['site']['name']} " . _('staff') . '';
            while ($arr_karma = mysqli_fetch_assoc($res_karma)) {
                $karma_new = $arr_karma['seedbonus'] + $karma;
                $modcomment = $arr_karma['modcomment'];
                $modcomment = get_date((int) $dt, 'DATE', 1) . ' - ' . $karma . _('Mass Bonus Karma Points added - AutoSystem.
') . $modcomment;
                $pm_values[] = [
                    'receiver' => (int) $arr_karma['id'],
                    'added' => $dt,
                    'msg' => $msg,
                    'subject' => _('Karma added'),
                ];
                $set = [
                    'seedbonus' => $karma_new,
                    'modcomment' => $modcomment,
                ];
                $users_class->update($set, (int) $arr_karma['id']);
            }
            $count = count($pm_values);
            if ($count > 0) {
                $messages_class->insert($pm_values);
                write_log('' . _('Staff mass bonus - added karma points to') . " $count " . _('members in all classes by') . " {$CURUSER['username']}");
            }
            unset($pm_values, $user_values, $user_updates, $count);
        }
        header('Location: ' . $_SERVER['PHP_SELF'] . '?tool=mass_bonus_for_members&action=mass_bonus_for_members&karma=1');
        die();
        break;

    case 'freeslots':
        $freeslots = isset($_POST['freeslots']) ? (int) $_POST['freeslots'] : 0;
        if ($freeslots < 1 || $freeslots > 50) {
            stderr(_('Error'), _('You forgot to select an amount!'));
        }
        $res_freeslots = sql_query('SELECT id, freeslots, modcomment FROM users WHERE status = 0 AND class IN ' . $free_for) or sqlerr(__FILE__, __LINE__);
        $pm_values = $user_values = [];
        if (mysqli_num_rows($res_freeslots) > 0) {
            $msg = '' . _('Hey,
 we have decided to add') . " $freeslots " . _('free slots to all classes.
 Cheers') . " {$site_config['site']['name']} " . _('staff') . '';
            while ($arr_freeslots = mysqli_fetch_assoc($res_freeslots)) {
                $freeslots_new = $arr_freeslots['freeslots'] + $freeslots;
                $modcomment = $arr_freeslots['modcomment'];
                $modcomment = get_date((int) $dt, 'DATE', 1) . ' - ' . $freeslots . _('Free Leech Slots Mass Bonus added - AutoSystem.
') . $modcomment;
                $pm_values[] = [
                    'receiver' => (int) $arr_freeslots['id'],
                    'added' => $dt,
                    'msg' => $msg,
                    'subject' => _('Free Slots added'),
                ];
                $set = [
                    'freeslots' => $freeslots_new,
                    'modcomment' => $modcomment,
                ];
                $users_class->update($set, (int) $arr_freeslots['id']);
            }
            $count = count($pm_values);
            if ($count > 0) {
                $messages_class->insert($pm_values);
                write_log('' . _('Staff mass bonus - added freeslots to') . " $count " . _('members in all classes by') . " {$CURUSER['username']}");
            }
            unset($pm_values, $user_values, $user_updates, $count);
        }
        header('Location: ' . $_SERVER['PHP_SELF'] . '?tool=mass_bonus_for_members&action=mass_bonus_for_members&freeslots=1');
        die();
        break;

    case 'invite':
        $invites = isset($_POST['invites']) ? (int) $_POST['invites'] : 0;
        if ($invites < 1 || $invites > 50) {
            stderr(_('Error'), _('You forgot to select an amount!'));
        }
        $res_invites = sql_query("SELECT id, invites, modcomment FROM users WHERE status = 0 AND invite_on = 'yes' AND class IN " . $free_for);
        $pm_buffer = $users_buffer = [];
        if (mysqli_num_rows($res_invites) > 0) {
            $msg = '' . _('Hey,
 we have decided to add') . " $invites " . _('invites to all classes.
 Cheers') . " {$site_config['site']['name']} " . _('staff') . '';
            while ($arr_invites = mysqli_fetch_assoc($res_invites)) {
                $invites_new = $arr_invites['invites'] + $invites;
                $modcomment = $arr_invites['modcomment'];
                $modcomment = get_date((int) $dt, 'DATE', 1) . ' - ' . $invites . _('Invites Mass Bonus added - AutoSystem.
') . $modcomment;
                $pm_values[] = [
                    'receiver' => (int) $arr_invites['id'],
                    'added' => $dt,
                    'msg' => $msg,
                    'subject' => _('Invites added'),
                ];
                $set = [
                    'invites' => $invites_new,
                    'modcomment' => $modcomment,
                ];
                $users_class->update($set, (int) $arr_invites['id']);
            }
            $count = count($pm_values);
            if ($count > 0) {
                $messages_class->insert($pm_values);
                write_log('' . _('Staff mass bonus - added invites to') . " $count " . _('members in all classes by') . " {$CURUSER['username']}");
            }
            unset($pm_values, $user_values, $user_updates, $count);
        }
        header('Location: ' . $_SERVER['PHP_SELF'] . '?tool=mass_bonus_for_members&action=mass_bonus_for_members&invites=1');
        die();
        break;

    case 'pm':
        if (!isset($_POST['subject'])) {
            stderr(_('Error'), _('No subject text... Please enter something to send!'));
        }
        if (!isset($_POST['body'])) {
            stderr(_('Error'), _('No body text... Please enter something to send!'));
        }
        $res_pms = sql_query('SELECT id FROM users WHERE status = 0 AND class IN ' . $free_for);
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
                $messages_class->insert($pm_values);
                write_log('' . _('Mass pm sent to') . " $count " . _('members in all classes by') . " {$CURUSER['username']}");
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

$bonus_GB = '<select name="GB">
        <option class="head" value="">' . _('Add Upload Credit') . '</option>
        <option class="body" value="1073741824">' . _('1 GB') . '</option>
        <option class="body" value="2147483648">' . _('2 GB') . '</option>
        <option class="body" value="3221225472">' . _('3 GB') . '</option>
        <option class="body" value="4294967296">' . _('4 GB') . '</option>
        <option class="body" value="5368709120">' . _('5 GB') . '</option>
        <option class="body" value="6442450944">' . _('6 GB') . '</option>
        <option class="body" value="7516192768">' . _('7 GB') . '</option>
        <option class="body" value="8589934592">' . _('8 GB') . '</option>
        <option class="body" value="9663676416">' . _('9 GB') . '</option>
        <option class="body" value="10737418240">' . _('10 GB') . '</option>
        <option class="body" value="16106127360">' . _('15 GB') . '</option>
        <option class="body" value="21474836480">' . _('20 GB') . '</option>
        <option class="body" value="26843545600">' . _('25 GB') . '</option>
        <option class="body" value="32212254720">' . _('30GB') . '</option>
        <option class="body" value="53687091200">' . _('50 GB') . '</option>
        </select>' . _('select amount of bonus GB to add to members upload credit.') . ' ';
$karma_drop_down = '
        <select name="karma">
        <option class="head" value="">' . _('Add Karma Bonus Points') . '</option>';
$i = 100;
while ($i <= 5000) {
    $karma_drop_down .= '<option class="body" value="' . $i . '.0">' . $i . ' ' . _('Karma Points') . '</option>';
    $i = ($i < 1000 ? $i = $i + 100 : $i = $i + 500);
}
$karma_drop_down .= '</select> ' . _('select amount of Karma Bonus Points to add.') . ' ';
$free_leech_slot_drop_down = '
        <select name="freeslots">
        <option class="head" value="">' . _('Add freeslots') . '</option>';
$i = 1;
while ($i <= 50) {
    $free_leech_slot_drop_down .= '<option class="body" value="' . $i . '.0">' . $i . _('freeslot') . ($i !== 1 ? 's' : '') . '</option>';
    $i = ($i < 10 ? $i = $i + 1 : $i = $i + 5);
}
$free_leech_slot_drop_down .= '</select>' . _('select amount of freeslots to add.') . ' ';
$invites_drop_down = '
        <select name="invites">
        <option class="head" value="">' . _('Add Invites') . '</option>';
$i = 1;
while ($i <= 50) {
    $invites_drop_down .= '<option class="body" value="' . $i . '.0">' . $i . ' ' . _('Add Invites') . ($i !== 1 ? 's' : '') . '</option>';
    $i = ($i < 10 ? $i = $i + 1 : $i = $i + 5);
}
$invites_drop_down .= '</select>' . _('select amount of invites to add.') . '';

$subject = isset($_POST['subject']) ? htmlsafechars($_POST['subject']) : _('Mass Pm');
$body = isset($_POST['body']) ? htmlsafechars($_POST['body']) : _('Your text here');
$pm_drop_down = '
                <table class="w-100">
                    <tr>
                        <td colspan="2">' . _('Send message') . '</td>
                    </tr>
                    <tr>
                        <td><span class="has-text-weight-bold">' . _('Subject:') . '</span></td>
                        <td>
                            <input type="hidden" name="pm" value="pm">
                            <input name="subject" type="text" class="w-100" value="' . $subject . '">
                        </td>
                    </tr>
                    <tr>
                        <td><span class="has-text-weight-bold">' . _('Body:') . '</span></td>
                        <td class="is-paddingless">' . BBcode($body, '', 300) . '</td>
                    </tr>
                </table>';
$drop_down = '
        <select name="bonus_options_1" id="bonus_options_1">
        <option value="">' . _('Select Bonus Type') . '</option>
        <option value="upload_credit">' . _('Upload Credit') . '</option>
        <option value="karma">' . _('Karma Points') . '</option>
        <option value="freeslots">' . _('Free Leech Slots') . '</option>
        <option value="invite">' . _('Invites') . '</option>
        <option value="pm">' . _('Pm') . '</option>
        <option value="">' . _('Reset bonus type') . '</option>
        </select>';

$h1_thingie .= (isset($_GET['GB']) ? ($_GET['GB'] === 1 ? '<h2>' . _('Bonus GB added to all enabled members') . '</h2>' : '<h2>' . _('Bonus GB added to all enabled members') . '</h2>') : '');
$h1_thingie .= (isset($_GET['karma']) ? ($_GET['karma'] === 1 ? '<h2>' . _('Bonus Karma added to all enabled members') . '</h2>' : '<h2>' . _('Bonus Karma added to selected member classes') . '</h2>') : '');
$h1_thingie .= (isset($_GET['freeslots']) ? ($_GET['freeslots'] === 1 ? '<h2>' . _('Bonus Free Leech Slots added to all enabled members') . '</h2>' : '<h2>' . _('Bonus Free Leech Slots added to selected member classes') . '</h2>') : '');
$h1_thingie .= (isset($_GET['invites']) ? ($_GET['invites'] === 1 ? '<h2>' . _('Bonus invites added to all enabled members') . '</h2>' : '<h2>' . _('Bonus invites added to selected member classes') . '</h2>') : '');
$h1_thingie .= (isset($_GET['pm']) ? ($_GET['pm'] === 1 ? '<h2>' . _('Mass pm sent to all enabled members') . '</h2>' : '<h2>' . _('Mass pm sent to selected member classes') . '</h2>') : '');
$HTMLOUT .= '<h1 class="has-text-centered">' . $site_config['site']['name'] . ' ' . _('Mass Bonus') . '</h1>' . $h1_thingie;
$HTMLOUT .= '
    <form name="inputform" method="post" action="' . $_SERVER['PHP_SELF'] . '?tool=mass_bonus_for_members&amp;action=mass_bonus_for_members" enctype="multipart/form-data" accept-charset="utf-8">';
$body = '
        <tr>
            <td class="colhead" colspan="2">' . _('Mass bonus for all or selected members:') . '</td>
        </tr>
        <tr>
            <td><span class="has-text-weight-bold">' . _('Apply bonus to:') . '</span></td>
            <td>
                <div>' . $all_classes_check_boxes . '</div>
            </td>
        </tr>
        <tr>
            <td class="w-25"><span class="has-text-weight-bold">' . _('Bonus Type:') . '</span></td>
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
                <div class="has-text-centered margin20">' . _("*** Please note, pm's are automatically sent to all users awarded by the script.") . '</div>
                <div class="has-text-centered margin20">
                    <input type="submit" class="button is-small" value="' . _('Do it') . '">
                </div>
            </td>
        </tr>';
$HTMLOUT .= main_table($body) . '
    </form>';

$title = _('Bonus Manager');
$breadcrumbs = [
    "<a href='{$site_config['paths']['baseurl']}/staffpanel.php'>" . _('Staff Panel') . '</a>',
    "<a href='{$_SERVER['PHP_SELF']}'>$title</a>",
];
echo stdhead($title, $stdhead, 'page-wrapper', $breadcrumbs) . wrapper($HTMLOUT) . stdfoot($stdfoot);
