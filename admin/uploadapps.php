<?php

declare(strict_types = 1);

use Pu239\Cache;
use Pu239\Database;

require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_pager.php';
require_once CLASS_DIR . 'class_check.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
$lang = array_merge($lang, load_language('uploadapps'));
global $container, $site_config, $CURUSER;

$fluent = $container->get(Database::class);
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

if ($action === 'app' || $action === 'show') {
    if ($action === 'show') {
        $hide = "<a href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=uploadapps&amp;action=app'>{$lang['uploadapps_hide']}</a>";
        $res = $fluent->from('uploadapp')
                      ->select('users.uploaded')
                      ->select('users.downloaded')
                      ->select('users.added')
                      ->select('users.class')
                      ->leftJoin('users ON uploadapp.userid=users.id')
                      ->where('status = "pending"')
                      ->fetchAll();
    } else {
        $hide = "<a href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=uploadapps&amp;action=show'>{$lang['uploadapps_show']}</a>";
        $res = $fluent->from('uploadapp')
                      ->select('users.uploaded')
                      ->select('users.downloaded')
                      ->select('users.added')
                      ->select('users.class')
                      ->leftJoin('users ON uploadapp.userid=users.id')
                      ->where('uploadapp.status = "pending"')
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
        <form method='post' action='{$site_config['paths']['baseurl']}/staffpanel.php?tool=uploadapps&amp;action=takeappdelete' accept-charset='utf-8'>";
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
                $status = "<span style='color: green;'>{$lang['uploadapps_accepted']}</span>";
            } elseif ($arr['status'] === 'rejected') {
                $status = "<span class='has-text-danger'>{$lang['uploadapps_rejected']}</span>";
            } else {
                $status = "<span style='color: blue;'>{$lang['uploadapps_pending']}</span>";
            }
            $membertime = get_date((int) $arr['added'], '', 0, 1);
            $elapsed = get_date((int) $arr['applied'], '', 0, 1);
            $body .= "
            <tr>
                <td>{$elapsed}</td>
                <td><a href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=uploadapps&amp;action=viewapp&amp;id=" . (int) $arr['id'] . "'>{$lang['uploadapps_viewapp']}</a></td>
                <td>" . format_username((int) $arr['userid']) . "</td>
                <td>{$membertime}</td>
                <td>" . get_user_class_name($arr['class']) . '</td>
                <td>' . mksize($arr['uploaded']) . '</td>
                <td>' . member_ratio($arr['uploaded'], $site_config['site']['ratio_free'] ? '0' : $arr['downloaded']) . "</td>
                <td>{$status}</td>
                <td><input type=\"checkbox\" name=\"deleteapp[]\" value=\"" . (int) $arr['id'] . '"></td>
            </tr>';
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
}

if ($action === 'viewapp') {
    $id = (int) $_GET['id'];
    $arr = $fluent->from('uploadapp')
                  ->select('users.uploaded')
                  ->select('users.downloaded')
                  ->select('users.added')
                  ->select('users.class')
                  ->leftJoin('users ON uploadapp.userid=users.id')
                  ->where('uploadapp.id = ?', $id)
                  ->fetch();

    $membertime = get_date((int) $arr['added'], '', 0, 1);
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
            <td>" . member_ratio($arr['uploaded'], $site_config['site']['ratio_free'] ? 0 : $arr['downloaded']) . "</td>
        </tr>
        <tr>
            <td>{$lang['uploadapps_connectable']}</td>
            <td>" . htmlsafechars($arr['connectable']) . "</td>
        </tr>
        <tr>
            <td>{$lang['uploadapps_class1']}</td>
            <td>" . get_user_class_name($arr['class']) . "</td>
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
            <form method='post' action='{$site_config['paths']['baseurl']}/staffpanel.php?tool=uploadapps&amp;action=acceptapp' accept-charset='utf-8'>
                <input name='id' type='hidden' value='{$arr['id']}'>
                <input type='text' name='note' class='w-100'>
                <div class='has-text-centered'>
                    <input type='submit' value='{$lang['uploadapps_accept']}' class='button is-small margin20'>
                </div>
            </form>";
        $div2 = "
            <h2>{$lang['uploadapps_reason']}</h2>
            <form method='post' action='{$site_config['paths']['baseurl']}/staffpanel.php?tool=uploadapps&amp;action=rejectapp' accept-charset='utf-8'>
                <input name='id' type='hidden' value='{$arr['id']}'>
                <input type='text' name='reason' class='w-100'>
                <div class='has-text-centered'>
                    <input type='submit' value='{$lang['uploadapps_reject']}' class='button is-small margin20'>
                </div>
            </form>";
        $HTMLOUT .= main_table($table) . main_div($div1, 'top20') . main_div($div2, 'top20');
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
$cache = $container->get(Cache::class);
if ($action === 'acceptapp') {
    $id = (int) $_POST['id'];
    if (!is_valid_id($id)) {
        stderr($lang['uploadapps_error'], $lang['uploadapps_noid']);
    }
    $arr = $fluent->from('uploadapp')
                  ->select(null)
                  ->select('uploadapp.userid AS uid')
                  ->select('uploadapp.id')
                  ->select('users.modcomment')
                  ->leftJoin('users ON uploadapp.userid=users.id')
                  ->where('uploadapp.id = ?', $id)
                  ->fetch();

    $note = htmlsafechars($_POST['note']);
    $subject = $lang['uploadapps_subject'];
    $msg = "{$lang['uploadapps_msg']}\n\n{$lang['uploadapps_msg_note']} $note";
    $msg1 = "{$lang['uploadapps_msg_user']} [url={$site_config['paths']['baseurl']}/userdetails.php?id=" . (int) $arr['uid'] . "][b]{$arr['username']}[/b][/url] {$lang['uploadapps_msg_been']} {$CURUSER['username']}.";
    $modcomment = get_date((int) $dt, 'DATE', 1) . $lang['uploadapps_modcomment'] . $CURUSER['username'] . '.' . ($arr['modcomment'] != '' ? "\n" : '') . "{$arr['modcomment']}";
    sql_query("UPDATE uploadapp SET status = 'accepted', comment = " . sqlesc($note) . ', moderator = ' . sqlesc($CURUSER['username']) . ' WHERE id=' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
    sql_query('UPDATE users SET class = ' . UC_UPLOADER . ', modcomment = ' . sqlesc($modcomment) . ' WHERE id=' . sqlesc($arr['uid']) . ' AND class < ' . UC_STAFF) or sqlerr(__FILE__, __LINE__);
    $cache->update_row('user_' . $arr['uid'], [
        'class' => 3,
        'modcomment' => $modcomment,
    ], $site_config['expires']['user_cache']);
    $msgs_buffer[] = [
        'sender' => 0,
        'poster' => $CURUSER['id'],
        'receiver' => $arr['uid'],
        'added' => $dt,
        'msg' => $msg,
        'subject' => $subject,
    ];
    $subres = sql_query('SELECT id FROM users WHERE class>= ' . UC_STAFF) or sqlerr(__FILE__, __LINE__);
    while ($subarr = mysqli_fetch_assoc($subres)) {
        $msgs_buffer[] = [
            'sender' => 0,
            'poster' => $CURUSER['id'],
            'receiver' => $subarr['id'],
            'added' => $dt,
            'msg' => $msg1,
            'subject' => $subject,
        ];
    }
    if (!empty($msgs_buffer)) {
        $message_stuffs->insert($msgs_buffer);
    }
    $cache->delete('new_uploadapp_');
    stderr($lang['uploadapps_app_accepted'], "{$lang['uploadapps_app_msg']} {$lang['uploadapps_app_click']} <a href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=uploadapps&amp;action=app'><b>{$lang['uploadapps_app_here']}</b></a> {$lang['uploadapps_app_return']}");
}
if ($action === 'rejectapp') {
    $id = (int) $_POST['id'];
    if (!is_valid_id($id)) {
        stderr($lang['uploadapps_error'], $lang['uploadapps_no_up']);
    }
    $res = sql_query('SELECT uploadapp.id, users.id AS uid FROM uploadapp INNER JOIN users ON uploadapp.userid=users.id WHERE uploadapp.id=' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
    $arr = mysqli_fetch_assoc($res);
    $reason = htmlsafechars($_POST['reason']);
    $subject = $lang['uploadapps_subject'];
    $msg = "{$lang['uploadapps_rej_no']}\n\n{$lang['uploadapps_rej_reason']} $reason";
    $msgs_buffer[] = [
        'sender' => 0,
        'poster' => $CURUSER['id'],
        'receiver' => $arr['uid'],
        'added' => $dt,
        'msg' => $msg,
        'subject' => $subject,
    ];

    sql_query("UPDATE uploadapp SET status = 'rejected', comment = " . sqlesc($reason) . ', moderator = ' . sqlesc($CURUSER['username']) . ' WHERE id=' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
    $message_stuffs->insert($msgs_buffer);
    $cache->delete('new_uploadapp_');
    stderr($lang['uploadapps_app_rej'], "{$lang['uploadapps_app_rejbeen']} {$lang['uploadapps_app_click']} <a href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=uploadapps&amp;action=app'><b>{$lang['uploadapps_app_here']}</b></a>{$lang['uploadapps_app_return']}");
}
//== Delete applications
if ($action === 'takeappdelete') {
    if (empty($_POST['deleteapp'])) {
        stderr($lang['uploadapps_silly'], $lang['uploadapps_twix']);
    } else {
        sql_query('DELETE FROM uploadapp WHERE id IN (' . implode(', ', $_POST['deleteapp']) . ') ') or sqlerr(__FILE__, __LINE__);
        $cache->delete('new_uploadapp_');
        stderr($lang['uploadapps_deleted'], "{$lang['uploadapps_deletedsuc']} {$lang['uploadapps_app_click']} <a href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=uploadapps&amp;action=app'><b>{$lang['uploadapps_app_here']}</b></a>{$lang['uploadapps_app_return']}");
    }
}
echo stdhead($lang['uploadapps_stdhead']) . wrapper($HTMLOUT) . stdfoot();
