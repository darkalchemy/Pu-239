<?php

declare(strict_types = 1);

use Pu239\Cache;
use Pu239\Database;
use Pu239\Message;
use Pu239\Session;
use Pu239\User;

require_once __DIR__ . '/../include/bittorrent.php';
require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_html.php';
require_once INCL_DIR . 'function_pager.php';
require_once INCL_DIR . 'function_bbcode.php';
check_user_status();
$HTMLOUT = '';
$lang = array_merge(load_language('global'), load_language('bugs'));
global $container, $site_config, $CURUSER;

$possible_actions = [
    'viewbug',
    'bugs',
    'add',
];
$action = isset($_GET['action']) ? htmlsafechars($_GET['action']) : (isset($_POST['action']) ? htmlsafechars($_POST['action']) : 'bugs');
if (!in_array($action, $possible_actions)) {
    stderr('Error', $lang['bugs_how']);
}
$dt = TIME_NOW;
$fluent = $container->get(Database::class);
$messages_class = $container->get(Message::class);
$user_class = $container->get(User::class);
$cache = $container->get(Cache::class);
$session = $container->get(Session::class);
if ($action === 'viewbug') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if ($CURUSER['class'] < UC_MAX) {
            stderr($lang['stderr_error'], $lang['stderr_only_coder']);
        }
        $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
        $status = isset($_POST['status']) ? htmlsafechars($_POST['status']) : '';
        $comment = !empty($_POST['comment']) ? htmlsafechars($_POST['comment']) : '';
        if (!$id || !is_valid_id($id)) {
            stderr($lang['stderr_error'], $lang['stderr_invalid_id']);
        }
        $bug = $fluent->from('bugs')
                      ->where('id = ?', $id)
                      ->fetch();
        $user = $user_class->getUserFromId($bug['sender']);
        $precomment = "\n[precode]{$comment}[/precode]";
        switch ($status) {
            case 'fixed':
                $msg = 'Hello ' . htmlsafechars($user['username']) . ".\nYour bug: [b]" . htmlsafechars($bug['title']) . "[/b] has been treated by one of our coders, and is done.\n\nWe would like to thank you and therefore we have added [b]2 GB[/b] to your upload total :].\n\nBest regards, {$site_config['site']['name']}'s coders.\n\n\n$precomment";
                $update = [
                    'uploaded' => $user['uploaded'] + (1024 * 1024 * 1024 * 2),
                ];
                $user_class->update($update, $user['id']);
                break;

            case 'ignored':
                $msg = 'Hello ' . htmlsafechars($user['username']) . ".\nYour bug: [b]" . htmlsafechars($bug['title']) . "[/b] has been ignored by one of our coder.\n\nPossibly it was not a bug.\n\nBest regards, {$site_config['site']['name']}'s coders.\n\n\n$precomment";
                break;

            case 'na':
                $msg = $comment;
        }
        $msgs_buffer[] = [
            'receiver' => $user['id'],
            'added' => $dt,
            'msg' => $msg,
            'subject' => 'Response to your Bug Report',
        ];
        $messages_class->insert($msgs_buffer);
        $update = [
            'status' => $status,
            'staff' => $CURUSER['id'],
            'comment' => !empty($_POST['comment']) ? htmlsafechars($_POST['comment']) : '',
        ];
        $fluent->update('bugs')
               ->set($update)
               ->where('id = ?', $id)
               ->execute();
        $cache->delete('bug_mess_');
        header("location: {$_SERVER['PHP_SELF']}?action=bugs");
    }
    $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
    if (!$id || !is_valid_id($id)) {
        stderr($lang['stderr_error'], $lang['stderr_invalid_id']);
    }
    if ($CURUSER['class'] < UC_STAFF) {
        stderr($lang['stderr_error'], 'Only staff can view bugs.');
    }
    $bug = $fluent->from('bugs AS b')
                  ->select('u.username')
                  ->select('u.class')
                  ->select('s.username AS st')
                  ->select('s.class AS stclass')
                  ->leftJoin('users AS u ON b.sender = u.id')
                  ->leftJoin('users AS s ON b.staff = u.id')
                  ->where('b.id = ?', $id)
                  ->fetch();

    $title = htmlsafechars($bug['title']);
    $added = get_date($bug['added'], 'LONG', 0, 1);
    $addedby = format_username($bug['sender']) . '<i>(' . get_user_class_name($bug['class']) . ')</i>';
    $comment = !empty($bug['comment']) ? format_comment($bug['comment']) : '';
    $problem = !empty($bug['problem']) ? format_comment($bug['problem']) : '';
    switch ($bug['priority']) {
        case 'low':
            $priority = "<span class='has-text-green'>{$lang['low']}</span>";
            break;

        case 'high':
            $priority = "<span class='has-text-danger'>{$lang['high']}</span>";
            break;

        case 'veryhigh':
            $priority = "<span class='has-text-danger'><b><u>{$lang['veryhigh']}</u></b></span>";
            break;
    }
    switch ($bug['status']) {
        case 'fixed':
            $status = "<span class='has-text-green'><b>{$lang['fixed']}</b></span>";
            break;

        case 'ignored':
            $status = "<span class='has-text-orange'><b>{$lang['ignored']}</b></span>";
            break;

        default:
            $status = "
            <select name='status'>
                <option value='na'>{$lang['select_one']}</option>
                <option value='fixed'>{$lang['fix_problem']}</option>
                <option value='ignored'>{$lang['ignore_problem']}</option>
            </select>";
    }
    switch (!empty($bug['staff']) && !empty($bug['stclass'])) {
        case 0:
            $by = '';
            break;

        default:
            $by = format_username($bug['staff']) . ' <i>(' . get_user_class_name($bug['stclass']) . ')</i>';
    }
    $HTMLOUT .= "
        <form method='post' action='{$_SERVER['PHP_SELF']}?action=viewbug' accept-charset='utf-8'>
            <input type='hidden' name='id' value='" . $bug['id'] . "'>";
    $body = "
            <tr>
                <td class='rowhead'>{$lang['title']}:</td>
                <td>{$title}</td>
            </tr>
            <tr>
                <td class='rowhead'>{$lang['added']} / {$lang['by']}</td>
                <td>{$added} / {$addedby}</td>
            </tr>
            <tr>
                <td class='rowhead'>{$lang['priority']}</td>
                <td>" . $priority . "</td>
            </tr>
            <tr class='no_hover'>
                <td class='rowhead'>{$lang['problem_bug']}</td>
                <td><div class='margin20 code'>{$problem}</div></td>
            </tr>
            <tr>
                <td class='rowhead'>{$lang['status']} / {$lang['by']}</td>
                <td>{$status} - {$by}</td>
            </tr>
            <tr class='no_hover'>
                <td class='rowhead'>{$lang['problem_comment']}</td>
                <td><textarea name='comment' class='w-100' rows='6'>$comment</textarea></td>
            </tr>";
    if ($bug['status'] === 'na') {
        $body .= "
            <tr>
                <td colspan='2' class='has-text-centered'>
                    <input type='submit' value='{$lang['submit_btn_fix']}' class='button is-small'>
                </td>
            </tr>";
    }
    $HTMLOUT .= main_table($body) . "
        </form>
        <div class='has-text-centered margin20'>
            <a href='{$_SERVER['PHP_SELF']}?action=bugs' class='button is-small'>{$lang['go_back']}</a>
        </div>";
} elseif ($action === 'bugs') {
    if ($CURUSER['class'] < UC_STAFF) {
        stderr($lang['stderr_error'], $lang['stderr_only_staff_can_view']);
    }
    $count = $fluent->from('bugs')
                    ->select(null)
                    ->select('COUNT(id) AS count')
                    ->fetch('count');
    $perpage = 25;
    $pager = pager($perpage, $count, $site_config['paths']['baseurl'] . '/bugs.php?action=bugs&amp;');
    $bugs = $fluent->from('bugs AS b')
                   ->select('u.username')
                   ->select('u.class')
                   ->select('s.username AS st')
                   ->select('s.class AS stclass')
                   ->leftJoin('users AS u ON b.sender = u.id')
                   ->leftJoin('users AS s ON b.staff = s.id')
                   ->orderBy('b.added DESC')
                   ->limit($pager['pdo']['limit'])
                   ->offset($pager['pdo']['offset'])
                   ->fetchAll();

    $na_count = $fluent->from('bugs')
                       ->select(null)
                       ->select('COUNT(id) AS count')
                       ->where('status = "na"')
                       ->fetch('count');

    if ($count > 0) {
        $HTMLOUT .= $count > $perpage ? $pager['pagertop'] : '';
        $HTMLOUT .= "
        <h1 class='has-text-centered'>" . sprintf($lang['h1_count_bugs'], $na_count, ($na_count > 1 ? 's' : '')) . "</h1>
        <div class='has-text-centered size_3'>{$lang['delete_when']}</div>";
        $heading = "        
    <tr>
        <th>{$lang['title']}</th>
        <th>{$lang['added']} / {$lang['by']}</th>
        <th>{$lang['priority']}</th>
        <th>{$lang['status']}</th>
        <th>{$lang['coder']}</th>
        <th>{$lang['comment']}</th>
    </tr>";
        $body = '';
        foreach ($bugs as $q1) {
            switch ($q1['priority']) {
                case 'low':
                    $priority = "<span class='has-text-green'>{$lang['low']}</span>";
                    break;

                case 'high':
                    $priority = "<span class='has-text-danger'>{$lang['high']}</span>";
                    break;

                case 'veryhigh':
                    $priority = "<span class='has-text-danger'><b><u>{$lang['veryhigh']}</u></b></span>";
                    break;
            }
            switch ($q1['status']) {
                case 'fixed':
                    $status = "<span class='has-text-green'><b>{$lang['fixed']}</b></span>";
                    break;

                case 'ignored':
                    $status = "<span class='has-text-orange'><b>{$lang['ignored']}</b></span>";
                    break;

                default:
                    $status = "<span class='has-text-gold'><b>N/A</b></span>";
                    break;
            }
            $body .= "
    <tr>
        <td class='w-25'><a href='?action=viewbug&amp;id=" . $q1['id'] . "'>" . format_comment($q1['title']) . "</a></td>
        <td nowrap='nowrap'>" . get_date($q1['added'], 'TINY') . ' / ' . format_username($q1['sender']) . "</td>
        <td>{$priority}</td>
        <td>{$status}</td>
        <td>" . ($q1['status'] != 'na' ? format_username($q1['staff']) : '---') . "</td>
        <td class='w-25'>" . (!empty($q1['comment']) ? format_comment($q1['comment']) : '---') . '</td>
    </tr>';
        }
        $HTMLOUT .= main_table($body, $heading);
        $HTMLOUT .= $count > $perpage ? $pager['pagerbottom'] : '';
    } else {
        $session->set('is-warning', $lang['no_bugs']);
        header('Location: ' . $site_config['paths']['baseurl']);
        die();
    }
} elseif ($action === 'add') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $title = htmlsafechars($_POST['title']);
        $priority = htmlsafechars($_POST['priority']);
        $problem = htmlsafechars($_POST['problem']);
        if (empty($title) || empty($priority) || empty($problem)) {
            stderr($lang['stderr_error'], $lang['stderr_missing']);
        }
        if (strlen($problem) < 20) {
            stderr($lang['stderr_error'], $lang['stderr_problem_min']);
        }
        if (strlen($title) < 5) {
            stderr($lang['stderr_error'], $lang['stderr_title_min']);
        }
        $values = [
            'title' => $title,
            'priority' => $priority,
            'problem' => $problem,
            'sender' => $CURUSER['id'],
            'added' => $dt,
        ];
        $result = $fluent->insertInto('bugs')
                         ->values($values)
                         ->execute();
        $cache->delete('bug_mess_');
        if ($result) {
            stderr($lang['stderr_sucess'], sprintf($lang['stderr_sucess_2'], $priority));
        } else {
            stderr($lang['stderr_error'], $lang['stderr_something_is_wrong']);
        }
    }
    $HTMLOUT .= "
    <form method='post' action='{$_SERVER['PHP_SELF']}?action=add' accept-charset='utf-8'>";
    $body = "
        <tr>
            <td class='rowhead'>{$lang['title']}:</td>
            <td><input type='text' name='title' class='w-100'><br>{$lang['proper_title']}</td>
        </tr>
        <tr>
            <td class='rowhead'>{$lang['problem_bug']}:</td>
            <td><textarea class='w-100' rows='10' name='problem'></textarea><br>{$lang['describe_problem']}</td>
        </tr>
        <tr>
            <td class='rowhead'>{$lang['priority']}:</td>
            <td>
                <select name='priority'>
                    <option value='0'>{$lang['select_one']}</option>
                    <option value='low'>{$lang['low']}</option>
                    <option value='high'>{$lang['high']}</option>
                    <option value='veryhigh'>{$lang['veryhigh']}</option>
                </select>
                <br>{$lang['only_veryhigh_when']}
            </td>
        </tr>
        <tr>
            <td colspan='2' class='has-text-centered'><input type='submit' value='{$lang['submit_btn_send']}' class='button is-small'></td>
        </tr>";
    $HTMLOUT .= main_table($body) . '
    </form>';
}
echo stdhead($lang['header']) . wrapper($HTMLOUT) . stdfoot();
