<?php

declare(strict_types = 1);

use Pu239\Cache;
use Pu239\Database;
use Pu239\Message;
use Pu239\Session;
use Pu239\User;

require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_pager.php';
require_once CLASS_DIR . 'class_check.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
$lang = array_merge($lang, load_language('uploadapps'));
global $container, $site_config, $CURUSER;

$session = $container->get(Session::class);
$fluent = $container->get(Database::class);
$cache = $container->get(Cache::class);
$messages_class = $container->get(Message::class);
$possible_actions = [
    'show',
    'viewapp',
    'acceptapp',
    'rejectapp',
    'takeappdelete',
    'app',
];
$action = (isset($_GET['action']) ? htmlsafechars($_GET['action']) : '');
if (!in_array($action, $possible_actions)) {
    stderr($lang['uploadapps_error'], $lang['uploadapps_ruffian']);
}
$dt = TIME_NOW;
$HTMLOUT = $where = $where1 = '';

if ($action === 'takeappdelete') {
    if (empty($_POST['deleteapp'])) {
        stderr($lang['uploadapps_silly'], $lang['uploadapps_twix']);
    } else {
        $ids = $_POST['deleteapp'];
        if (!is_array($ids)) {
            $session->set('is-warning', $lang['uploadapps_twix']);
        }
        $in = str_repeat('?,', count($ids) - 1) . '?';
        $fluent->deleteFrom('uploadapp')
               ->where('id IN (' . $in . ')', $ids)
               ->execute();
        $cache->delete('new_uploadapp_');
        $session->set('is-success', $lang['uploadapps_deletedsuc']);
    }
    $action = 'show';
} elseif ($action === 'acceptapp') {
    $id = (int) $_POST['id'];
    if (!is_valid_id($id)) {
        stderr($lang['uploadapps_error'], $lang['uploadapps_noid']);
    }
    $arr = $fluent->from('uploadapp AS a')
                  ->select(null)
                  ->select('a.userid AS uid')
                  ->select('a.id')
                  ->select('u.modcomment')
                  ->select('u.username')
                  ->leftJoin('users AS u ON a.userid = u.id')
                  ->where('a.id = ?', $id)
                  ->fetch();

    $note = htmlsafechars($_POST['note']);
    $subject = $lang['uploadapps_subject'];
    $msg = "{$lang['uploadapps_msg']}\n\n{$lang['uploadapps_msg_note']} $note";
    $msg1 = "{$lang['uploadapps_msg_user']} [url={$site_config['paths']['baseurl']}/userdetails.php?id=" . (int) $arr['uid'] . "][b]{$arr['username']}[/b][/url] {$lang['uploadapps_msg_been']} {$CURUSER['username']}.";
    $modcomment = get_date((int) $dt, 'DATE', 1) . $lang['uploadapps_modcomment'] . $CURUSER['username'] . '.' . ($arr['modcomment'] != '' ? "\n" : '') . "{$arr['modcomment']}";
    $update = [
        'status' => 'accepted',
        'comment' => $note,
        'moderator' => $CURUSER['username'],
    ];
    $fluent->update('uploadapp')
           ->set($update)
           ->where('id = ?', $id)
           ->execute();
    $user_class = $container->get(User::class);
    $update = [
        'class' => UC_UPLOADER,
        'modcomment' => $modcomment,
    ];
    $user_class->update($update, $arr['uid']);
    $msgs_buffer[] = [
        'poster' => $CURUSER['id'],
        'receiver' => $arr['uid'],
        'added' => $dt,
        'msg' => $msg,
        'subject' => $subject,
    ];
    foreach ($site_config['is_staff'] as $staff) {
        $msgs_buffer[] = [
            'poster' => $CURUSER['id'],
            'receiver' => $staff,
            'added' => $dt,
            'msg' => $msg1,
            'subject' => $subject,
        ];
    }
    if (!empty($msgs_buffer)) {
        $messages_class->insert($msgs_buffer);
    }
    $cache->delete('new_uploadapp_');
    $session->set('is-success', $lang['uploadapps_app_msg']);
    $action = 'show';
}
if ($action === 'rejectapp') {
    $id = (int) $_POST['id'];
    if (!is_valid_id($id)) {
        stderr($lang['uploadapps_error'], $lang['uploadapps_no_up']);
    }
    $arr = $fluent->from('uploadapp')
                  ->select(null)
                  ->select('userid AS uid')
                  ->select('id')
                  ->where('id = ?', $id)
                  ->fetch();

    $reason = htmlsafechars($_POST['reason']);
    $subject = $lang['uploadapps_subject'];
    $msg = "{$lang['uploadapps_rej_no']}\n\n{$lang['uploadapps_rej_reason']} $reason";
    $msgs_buffer[] = [
        'poster' => $CURUSER['id'],
        'receiver' => $arr['uid'],
        'added' => $dt,
        'msg' => $msg,
        'subject' => $subject,
    ];
    $update = [
        'status' => 'rejected',
        'comment' => $reason,
        'moderator' => $CURUSER['username'],
    ];
    $fluent->update('uploadapp')
           ->set($update)
           ->where('id = ?', $id)
           ->execute();
    $messages_class->insert($msgs_buffer);
    $cache->delete('new_uploadapp_');
    $session->set('is-success', $lang['uploadapps_app_rejbeen']);
    $action = 'show';
}

if ($action === 'app' || $action === 'show') {
    if ($action === 'show') {
        $hide = "<a href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=uploadapps&amp;action=app'>{$lang['uploadapps_hide']}</a>";
        $res = $fluent->from('uploadapp AS a')
                      ->select('u.uploaded')
                      ->select('u.downloaded')
                      ->select('u.registered')
                      ->select('u.class')
                      ->leftJoin('users AS u ON a.userid = u.id')
                      ->where('a.status != "pending"')
                      ->fetchAll();
    } else {
        $hide = "<a href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=uploadapps&amp;action=show'>{$lang['uploadapps_show']}</a>";
        $res = $fluent->from('uploadapp AS a')
                      ->select('u.uploaded')
                      ->select('u.downloaded')
                      ->select('u.registered')
                      ->select('u.class')
                      ->leftJoin('users AS u ON a.userid = u.id')
                      ->where('a.status = "pending"')
                      ->fetchAll();
    }

    $count = count($res);
    $perpage = 15;
    $pager = pager($perpage, $count, $site_config['paths']['baseurl'] . '/staffpanel.php?tool=uploadapps&amp;');
    $HTMLOUT .= "
        <div class='bottom20'>
            <ul class='level-center bg-06'>
                <li class='is-link margin10'>$hide</li>
            </ul>
        </div>
        <h1 class='has-text-centered'>{$lang['uploadapps_applications']}</h1>";
    if ($count == 0) {
        $HTMLOUT .= main_div($lang['uploadapps_noapps'], null, 'padding20 has-text-centered');
    } else {
        $HTMLOUT .= "
        <form method='post' action='{$_SERVER['PHP_SELF']}?tool=uploadapps&amp;action=takeappdelete' accept-charset='utf-8'>";
        if ($count > $perpage) {
            $HTMLOUT .= $pager['pagertop'];
        }
        $heading = "
            <tr>
                <th>{$lang['uploadapps_applied']}</th>
                <th>{$lang['uploadapps_application']}</th>
                <th>{$lang['uploadapps_username']}</th>
                <th>{$lang['uploadapps_joined']}</th>
                <th>{$lang['uploadapps_class']}</th>
                <th>{$lang['uploadapps_upped']}</th>
                <th>{$lang['uploadapps_ratio']}</th>
                <th>{$lang['uploadapps_status']}</th>
                <th>{$lang['uploadapps_delete']}</th>
            </tr>";
        $body = '';
        foreach ($res as $arr) {
            if ($arr['status'] === 'accepted') {
                $status = "<span class='has-text-success'>{$lang['uploadapps_accepted']}</span>";
            } elseif ($arr['status'] === 'rejected') {
                $status = "<span class='has-text-danger'>{$lang['uploadapps_rejected']}</span>";
            } else {
                $status = "<span class='has-text-info'>{$lang['uploadapps_pending']}</span>";
            }
            $membertime = get_date((int) $arr['registered'], '', 0, 1);
            $elapsed = get_date((int) $arr['applied'], '', 0, 1);
            $body .= "
            <tr>
                <td>{$elapsed}</td>
                <td><a href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=uploadapps&amp;action=viewapp&amp;id=" . (int) $arr['id'] . "'>{$lang['uploadapps_viewapp']}</a></td>
                <td>" . format_username($arr['userid']) . "</td>
                <td>{$membertime}</td>
                <td>" . get_user_class_name($arr['class']) . '</td>
                <td>' . mksize($arr['uploaded']) . '</td>
                <td>' . member_ratio($arr['uploaded'], $arr['downloaded']) . "</td>
                <td>{$status}</td>
                <td><input type='checkbox' name='deleteapp[]' value='" . $arr['id'] . "'></td>
            </tr>";
        }

        $HTMLOUT .= main_table($body, $heading) . "
            <div class='has-text-centered margin20'>
                <input type='submit' value='Delete' class='button is-small'>
            </div>
        </form>";
        if ($count > $perpage) {
            $HTMLOUT .= $pager['pagerbottom'];
        }
    }
} elseif ($action === 'viewapp') {
    $id = (int) $_GET['id'];
    $arr = $fluent->from('uploadapp AS a')
                  ->select('u.uploaded')
                  ->select('u.downloaded')
                  ->select('u.registered')
                  ->select('u.class')
                  ->leftJoin('users AS u ON a.userid = u.id')
                  ->where('a.id = ?', $id)
                  ->fetch();

    $membertime = get_date((int) $arr['registered'], '', 0, 1);
    $elapsed = get_date((int) $arr['applied'], '', 0, 1);
    $HTMLOUT .= '
    <h1>Uploader application</h1>';
    $table = "
        <tr>
            <td class='w-25'>{$lang['uploadapps_username1']}</td>
            <td>" . format_username((int) $arr['userid']) . "</a></td>
        </tr>
        <tr>
            <td>{$lang['uploadapps_joined']}</td>
            <td>" . htmlsafechars($membertime) . "</td>
        </tr>
        <tr>
            <td>{$lang['uploadapps_upped1']}</td>
            <td>" . htmlsafechars(mksize($arr['uploaded'])) . '</td>
        </tr>' . ($site_config['site']['ratio_free'] ? '' : "
        <tr>
            <td>{$lang['uploadapps_downed']}</td>
            <td>" . htmlsafechars(mksize($arr['downloaded'])) . '</td>
        </tr>') . "
        <tr>
            <td>{$lang['uploadapps_ratio1']}</td>
            <td>" . member_ratio($arr['uploaded'], $arr['downloaded']) . "</td>
        </tr>
        <tr>
            <td>{$lang['uploadapps_connectable']}</td>
            <td>" . htmlsafechars($arr['connectable']) . "</td>
        </tr>
        <tr>
            <td>{$lang['uploadapps_class1']}</td>
            <td>" . get_user_class_name((int) $arr['class']) . "</td>
        </tr>
        <tr>
            <td>{$lang['uploadapps_applied1']}</td>
            <td>" . htmlsafechars($elapsed) . "</td>
        </tr>
        <tr>
            <td>{$lang['uploadapps_upspeed']}</td>
            <td>" . htmlsafechars($arr['speed']) . "</td>
        </tr>
        <tr>
            <td>{$lang['uploadapps_offer']}</td>
            <td>" . htmlsafechars($arr['offer']) . "</td>
        </tr>
        <tr>
            <td>{$lang['uploadapps_why']}</td>
            <td>" . htmlsafechars($arr['reason']) . "</td>
        </tr>
        <tr>
            <td>{$lang['uploadapps_uploader']}</td>
            <td>" . htmlsafechars($arr['sites']) . '</td>
        </tr>';
    if ($arr['sitenames'] != '') {
        $table .= "
        <tr>
            <td>{$lang['uploadapps_sites']}</td>
            <td>" . htmlsafechars($arr['sitenames']) . "</td>
        </tr>
        <tr>
            <td>{$lang['uploadapps_axx']}</td>
            <td>" . htmlsafechars($arr['scene']) . "</td>
        </tr>
        <tr>
            <td>
                {$lang['uploadapps_create']}
            </td>
            <td>" . htmlsafechars($arr['creating']) . "</td>
        <tr>
            <td>{$lang['uploadapps_seeding']}</td>
            <td>" . htmlsafechars($arr['seeding']) . '</td>
        </tr>';
    }
    if ($arr['status'] === 'pending') {
        $div1 = "
            <h2>{$lang['uploadapps_note']}</h2>
            <form method='post' action='{$_SERVER['PHP_SELF']}?tool=uploadapps&amp;action=acceptapp' accept-charset='utf-8'>
                <input name='id' type='hidden' value='{$arr['id']}'>
                <input type='text' name='note' class='w-100'>
                <div class='has-text-centered'>
                    <input type='submit' value='{$lang['uploadapps_accept']}' class='button is-small margin20'>
                </div>
            </form>";
        $div2 = "
            <h2>{$lang['uploadapps_reason']}</h2>
            <form method='post' action='{$_SERVER['PHP_SELF']}?tool=uploadapps&amp;action=rejectapp' accept-charset='utf-8'>
                <input name='id' type='hidden' value='{$arr['id']}'>
                <input type='text' name='reason' class='w-100'>
                <div class='has-text-centered'>
                    <input type='submit' value='{$lang['uploadapps_reject']}' class='button is-small margin20'>
                </div>
            </form>";
        $HTMLOUT .= main_table($table) . main_div($div1, 'top20', 'padding20') . main_div($div2, 'top20', 'padding20');
    } else {
        $table = "
        <tr>
            <td colspan='2'>
                {$lang['uploadapps_application']} " . ($arr['status'] === 'accepted' ? 'accepted' : 'rejected') . ' by <b>' . htmlsafechars($arr['moderator']) . "</b><br>{$lang['uploadapps_comm']}" . htmlsafechars($arr['comment']) . "
            </td>
        </tr>
        <div>
            <a href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=uploadapps&amp;action=app'>{$lang['uploadapps_return']}</a>
        </div>";
        $HTMLOUT .= main_table($table);
    }
}

echo stdhead($lang['uploadapps_stdhead']) . wrapper($HTMLOUT) . stdfoot();
