<?php

declare(strict_types = 1);

use Envms\FluentPDO\Literal;
use Pu239\Cache;
use Pu239\Database;
use Pu239\Message;
use Pu239\Roles;
use Pu239\Session;
use Pu239\User;

require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_pager.php';
require_once CLASS_DIR . 'class_check.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
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
    stderr(_('Error'), _('A ruffian that will swear, drink, dance, revel the night, rob, murder and commit the oldest of ins the newest kind of ways.'));
}
$dt = TIME_NOW;
$HTMLOUT = $where = $where1 = '';

if ($action === 'takeappdelete') {
    if (empty($_POST['deleteapp'])) {
        stderr(_('Silly Rabbit'), _("Twix are for kids.. Check at least one application stupid...You can't delete nothing!"));
    } else {
        $ids = $_POST['deleteapp'];
        if (!is_array($ids)) {
            $session->set('is-warning', _("Twix are for kids.. Check at least one application stupid...You can't delete nothing!"));
        }
        $in = str_repeat('?,', count($ids) - 1) . '?';
        $fluent->deleteFrom('uploadapp')
               ->where('id IN (' . $in . ')', $ids)
               ->execute();
        $cache->delete('new_uploadapp_');
        $session->set('is-success', _('The upload applications were successfully deleted.'));
    }
    $action = 'show';
} elseif ($action === 'acceptapp') {
    $id = (int) $_POST['id'];
    if (!is_valid_id($id)) {
        stderr(_('Error'), _('It appears that there is no uploader application with that ID.'));
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
    $subject = _('Uploader Promotion');
    $msg = sprintf($lang['uploadapps_msg'], "[url={$site_config['paths']['baseurl']}/rules.php]" . _('guidelines on uploading') . '[/url]') . "\n\n" . _('Note: ') . " $note";
    $msg1 = '' . _('User') . " [url={$site_config['paths']['baseurl']}/userdetails.php?id=" . (int) $arr['uid'] . "][b]{$arr['username']}[/b][/url] " . _('has been promoted to Uploader by') . " {$CURUSER['username']}.";
    $modcomment = get_date((int) $dt, 'DATE', 1) . _(" - Promoted to 'Uploader' by ") . $CURUSER['username'] . '.' . ($arr['modcomment'] != '' ? "\n" : '') . "{$arr['modcomment']}";
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
    $clrbits = 0;
    $setbits |= Roles::UPLOADER;
    $update = [
        'roles_mask' => new Literal('((roles_mask | ' . $setbits . ') & ~' . $clrbits . ')'),
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
    $session->set('is-success', _('The application was successfully accepted. The user has been promoted and has been sent a PM notification.'));
    $action = 'show';
}
if ($action === 'rejectapp') {
    $id = (int) $_POST['id'];
    if (!is_valid_id($id)) {
        stderr(_('Error'), _('It appears that there is no uploader application with that ID.'));
    }
    $arr = $fluent->from('uploadapp')
                  ->select(null)
                  ->select('userid AS uid')
                  ->select('id')
                  ->where('id = ?', $id)
                  ->fetch();

    $reason = htmlsafechars($_POST['reason']);
    $subject = _('Uploader Promotion');
    $msg = '' . _('Sorry, your uploader application has been rejected. It appears that you are not qualified enough to become uploader.') . "\n\n" . _('Reason:') . " $reason";
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
    $session->set('is-success', _('The application was successfully rejected. The user has been sent a PM notification.'));
    $action = 'show';
}

if ($action === 'app' || $action === 'show') {
    if ($action === 'show') {
        $hide = "<a href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=uploadapps&amp;action=app'>" . _('Hide accepted/rejected') . '</a>';
        $res = $fluent->from('uploadapp AS a')
                      ->select('u.uploaded')
                      ->select('u.downloaded')
                      ->select('u.registered')
                      ->select('u.class')
                      ->leftJoin('users AS u ON a.userid = u.id')
                      ->where('a.status != "pending"')
                      ->fetchAll();
    } else {
        $hide = "<a href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=uploadapps&amp;action=show'>" . _('Show accepted/rejected') . '</a>';
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
        <h1 class='has-text-centered'>" . _('Uploader applications') . '</h1>';
    if ($count == 0) {
        $HTMLOUT .= main_div(_('There are currently no uploader applications'), null, 'padding20 has-text-centered');
    } else {
        $HTMLOUT .= "
        <form method='post' action='{$_SERVER['PHP_SELF']}?tool=uploadapps&amp;action=takeappdelete' enctype='multipart/form-data' accept-charset='utf-8'>";
        if ($count > $perpage) {
            $HTMLOUT .= $pager['pagertop'];
        }
        $heading = '
            <tr>
                <th>' . _('Applied') . '</th>
                <th>' . _('Application') . '</th>
                <th>' . _('Username') . '</th>
                <th>' . _('Joined') . '</th>
                <th>' . _('Class') . '</th>
                <th>' . _('Uploaded') . '</th>
                <th>' . _('Ratio') . '</th>
                <th>' . _('Status') . '</th>
                <th>' . _('Delete') . '</th>
            </tr>';
        $body = '';
        foreach ($res as $arr) {
            if ($arr['status'] === 'accepted') {
                $status = "<span class='has-text-success'>" . _('Accepted') . '</span>';
            } elseif ($arr['status'] === 'rejected') {
                $status = "<span class='has-text-danger'>" . _('Rejected') . '</span>';
            } else {
                $status = "<span class='has-text-info'>" . _('Pending') . '</span>';
            }
            $membertime = get_date((int) $arr['registered'], '', 0, 1);
            $elapsed = get_date((int) $arr['applied'], '', 0, 1);
            $body .= "
            <tr>
                <td>{$elapsed}</td>
                <td><a href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=uploadapps&amp;action=viewapp&amp;id=" . (int) $arr['id'] . "'>" . _('View application') . '</a></td>
                <td>' . format_username((int) $arr['userid']) . "</td>
                <td>{$membertime}</td>
                <td>" . get_user_class_name((int) $arr['class']) . '</td>
                <td>' . mksize($arr['uploaded']) . '</td>
                <td>' . member_ratio((float) $arr['uploaded'], (float) $arr['downloaded']) . "</td>
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
            <td class='w-25'>" . _('My username is') . '</td>
            <td>' . format_username((int) $arr['userid']) . '</a></td>
        </tr>
        <tr>
            <td>' . _('Joined') . '</td>
            <td>' . htmlsafechars($membertime) . '</td>
        </tr>
        <tr>
            <td>' . _('My upload amount is') . '</td>
            <td>' . htmlsafechars(mksize($arr['uploaded'])) . '</td>
        </tr>' . ($site_config['site']['ratio_free'] ? '' : '
        <tr>
            <td>' . _('My download amount is') . '</td>
            <td>' . htmlsafechars(mksize($arr['downloaded'])) . '</td>
        </tr>') . '
        <tr>
            <td>' . _('My ratio is') . '</td>
            <td>' . member_ratio($arr['uploaded'], $arr['downloaded']) . '</td>
        </tr>
        <tr>
            <td>' . _('I am connectable') . '</td>
            <td>' . htmlsafechars($arr['connectable']) . '</td>
        </tr>
        <tr>
            <td>' . _('My current userclass is') . '</td>
            <td>' . get_user_class_name((int) $arr['class']) . '</td>
        </tr>
        <tr>
            <td>' . _('I applied') . '</td>
            <td>' . htmlsafechars($elapsed) . '</td>
        </tr>
        <tr>
            <td>' . _('My upload speed is') . '</td>
            <td>' . htmlsafechars($arr['speed']) . '</td>
        </tr>
        <tr>
            <td>' . _('What I have to offer') . '</td>
            <td>' . htmlsafechars($arr['offer']) . '</td>
        </tr>
        <tr>
            <td>' . _('Why I should be promoted') . '</td>
            <td>' . htmlsafechars($arr['reason']) . '</td>
        </tr>
        <tr>
            <td>' . _('I am an uploader at other sites') . '</td>
            <td>' . htmlsafechars($arr['sites']) . '</td>
        </tr>';
    if ($arr['sitenames'] != '') {
        $table .= '
        <tr>
            <td>' . _('Those sites are') . '</td>
            <td>' . htmlsafechars($arr['sitenames']) . '</td>
        </tr>
        <tr>
            <td>' . _('I have scene access') . '</td>
            <td>' . htmlsafechars($arr['scene']) . '</td>
        </tr>
        <tr>
            <td>
                ' . _('I know how to create, upload and seed torrents') . '
            </td>
            <td>' . htmlsafechars($arr['creating']) . '</td>
        <tr>
            <td>' . _('I understand that I have to keep seeding my torrents until there are at least two other seeders') . '</td>
            <td>' . htmlsafechars($arr['seeding']) . '</td>
        </tr>';
    }
    if ($arr['status'] === 'pending') {
        $div1 = '
            <h2>' . _('Note: (optional)') . "</h2>
            <form method='post' action='{$_SERVER['PHP_SELF']}?tool=uploadapps&amp;action=acceptapp' enctype='multipart/form-data' accept-charset='utf-8'>
                <input name='id' type='hidden' value='{$arr['id']}'>
                <input type='text' name='note' class='w-100'>
                <div class='has-text-centered'>
                    <input type='submit' value='" . _('Accept') . "' class='button is-small margin20'>
                </div>
            </form>";
        $div2 = '
            <h2>' . _('Reason: (optional)') . "</h2>
            <form method='post' action='{$_SERVER['PHP_SELF']}?tool=uploadapps&amp;action=rejectapp' enctype='multipart/form-data' accept-charset='utf-8'>
                <input name='id' type='hidden' value='{$arr['id']}'>
                <input type='text' name='reason' class='w-100'>
                <div class='has-text-centered'>
                    <input type='submit' value='" . _('Reject') . "' class='button is-small margin20'>
                </div>
            </form>";
        $HTMLOUT .= main_table($table) . main_div($div1, 'top20', 'padding20') . main_div($div2, 'top20', 'padding20');
    } else {
        $table = "
        <tr>
            <td colspan='2'>
                " . _('Application') . ' ' . ($arr['status'] === 'accepted' ? 'accepted' : 'rejected') . ' by <b>' . htmlsafechars($arr['moderator']) . '</b><br>' . _('Comment: ') . '' . htmlsafechars($arr['comment']) . "
            </td>
        </tr>
        <div>
            <a href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=uploadapps&amp;action=app'>" . _('Return to uploader applications page') . '</a>
        </div>';
        $HTMLOUT .= main_table($table);
    }
}
$title = _('Uploader Application');
$breadcrumbs = [
    "<a href='{$site_config['paths']['baseurl']}/staffpanel.php'>" . _('Staff Panel') . '</a>',
    "<a href='{$_SERVER['PHP_SELF']}'>$title</a>",
];
echo stdhead($title, [], 'page-wrapper', $breadcrumbs) . wrapper($HTMLOUT) . stdfoot();
