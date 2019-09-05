<?php

declare(strict_types = 1);

use Pu239\Cache;
use Pu239\Database;
use Pu239\Message;
use Pu239\Session;

require_once __DIR__ . '/../include/bittorrent.php';
require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_bbcode.php';
require_once INCL_DIR . 'function_pager.php';
require_once INCL_DIR . 'function_html.php';
$user = check_user_status();
$lang = array_merge(load_language('global'), load_language('staffbox'));
global $container, $site_config;

$dt = TIME_NOW;
$session = $container->get(Session::class);
if ($user['class'] < UC_STAFF) {
    $session->set('is-danger', $lang['staffbox_class']);
    header('Location: ' . $site_config['paths']['baseurl']);
    die();
}
$valid_do = [
    'view',
    'delete',
    'setanswered',
    'restart',
    '',
];
$do = isset($_GET['do']) && in_array($_GET['do'], $valid_do) ? $_GET['do'] : (isset($_POST['do']) && in_array($_POST['do'], $valid_do) ? $_POST['do'] : '');
$id = isset($_GET['id']) ? (int) $_GET['id'] : (isset($_POST['id']) && is_array($_POST['id']) ? array_map('intval', $_POST['id']) : 0);
$message = isset($_POST['message']) && !empty($_POST['message']) ? htmlsafechars($_POST['message']) : '';
$subject = isset($_POST['subject']) && !empty($_POST['subject']) ? htmlsafechars($_POST['subject']) : '';
$reply = isset($_POST['reply']) && $_POST['reply'] == 1 ? true : false;
$HTMLOUT = '';
$cache = $container->get(Cache::class);
switch ($do) {
    case 'delete':
        if ($id > 0) {
            if (sql_query('DELETE FROM staffmessages WHERE id IN (' . implode(', ', $id) . ')')) {
                $cache->delete('staff_mess_');
                header('Refresh: 2; url=' . $_SERVER['PHP_SELF']);
                $session->set('is-success', $lang['staffbox_delete_ids']);
                header("Location: {$_SERVER['PHP_SELF']}");
                die();
            } else {
                $session->set('is-warning', sprintf($lang['staffbox_sql_err'], ((is_object($mysqli)) ? mysqli_error($mysqli) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false))));
                header("Location: {$_SERVER['PHP_SELF']}");
                die();
            }
        } else {
            $session->set('is-warning', $lang['staffbox_odd_err']);
            header("Location: {$_SERVER['PHP_SELF']}");
            die();
        }
        break;

    case 'setanswered':
        if ($id > 0) {
            if ($reply && empty($message)) {
                $session->set('is-warning', $lang['staffbox_no_message']);
                header("Location: {$_SERVER['PHP_SELF']}");
                die();
            }
            $q1 = sql_query('SELECT s.msg,s.sender,s.subject,u.username FROM staffmessages AS s LEFT JOIN users AS u ON s.sender=u.id WHERE s.id IN (' . implode(', ', $id) . ')') or sqlerr(__FILE__, __LINE__);
            $a = mysqli_fetch_assoc($q1);
            $msg = htmlsafechars($message) . "\n---" . htmlsafechars($a['username']) . " wrote ---\n" . htmlsafechars($a['msg']);

            $msgs_buffer[] = [
                'sender' => $user['id'],
                'poster' => $user['id'],
                'receiver' => $a['sender'],
                'added' => $dt,
                'msg' => $msg,
                'subject' => 'RE: ' . $subject,
            ];
            $messages_class = $container->get(Message::class);
            $messages_class->insert($msgs_buffer);
            $message = ', answer=' . sqlesc($message);
            if (sql_query('UPDATE staffmessages SET answered=' . TIME_NOW . ', answeredby=' . sqlesc($user['id']) . ' ' . $message . ' WHERE id IN (' . implode(', ', $id) . ')')) {
                $cache->delete('staff_mess_');
                $session->set('is-success', $lang['staffbox_setanswered_ids']);
                header("Location: {$_SERVER['PHP_SELF']}");
                die();
            } else {
                $session->set('is-warning', sprintf($lang['staffbox_sql_err'], ((is_object($mysqli)) ? mysqli_error($mysqli) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false))));
                header("Location: {$_SERVER['PHP_SELF']}");
                die();
            }
        } else {
            $session->set('is-warning', $lang['staffbox_odd_err']);
            header("Location: {$_SERVER['PHP_SELF']}");
            die();
        }
        break;

    case 'view':
        if ($id > 0) {
            $q2 = sql_query('SELECT s.id, s.added, s.msg, s.subject, s.answered, s.answer, s.answeredby, s.sender, s.answer, u.username, u2.username AS username2 FROM staffmessages AS s LEFT JOIN users AS u ON s.sender = u.id LEFT JOIN users AS u2 ON s.answeredby = u2.id  WHERE s.id=' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
            if (mysqli_num_rows($q2) == 1) {
                $a = mysqli_fetch_assoc($q2);
                $HTMLOUT .= "
                    <h1 class='has-text-centered'>{$lang['staffbox_pm_view']}</h1>" . main_div("
                    <form action='{$_SERVER['PHP_SELF']}' method='post' enctype='multipart/form-data' accept-charset='utf-8'>
                        <div class='bordered top20 bottom20 bg-00'>
                            <div>{$lang['staffbox_pm_from']}: " . format_username((int) $a['sender']) . ' at ' . get_date((int) $a['added'], 'LONG', 1, 0) . "</div>
                            <div>{$lang['staffbox_pm_subject']}: " . format_comment($a['subject']) . "</div>
                            <div>{$lang['staffbox_pm_answered']}: " . ($a['answeredby'] > 0 ? format_username((int) $a['answeredby']) . ' at ' . get_date((int) $a['answered'], 'LONG', 1, 0) : '<span>No</span>') . "</div>
                        </div>
                        <div class='bordered top20 bottom20 bg-00'>" . format_comment($a['msg']) . "
                        </div>
                        <div class='bordered top20 bottom20 bg-00'>
                            {$lang['staffbox_pm_answer']} " . ($a['answeredby'] == 0 ? "
                            <textarea rows='5' class='w-100' name='message'></textarea>" : ($a['answer'] ? format_comment($a['answer']) : "<b>{$lang['staffbox_pm_noanswer']}</b>")) . "
                        </div>
                        <div class='has-text-centered top20'>
                            <select name='do'>
                                <option value='setanswered' " . ($a['answeredby'] > 0 ? 'disabled' : '') . ">{$lang['staffbox_pm_reply']}</option>
                                <option value='restart' " . ($a['answeredby'] != $user['id'] ? 'disabled' : '') . ">{$lang['staffbox_pm_restart']}</option>
                                <option value='delete'>{$lang['staffbox_pm_delete']}</option>
                            </select>
                            <input type='hidden' name='subject' value='" . htmlsafechars($a['subject']) . "'>
                            <input type='hidden' name='reply' value='1'>
                            <input type='hidden' name='id[]' value='" . (int) $a['id'] . "'>
                            <input type='submit' class='button is-small' value='{$lang['staffbox_confirm']}'>
                        </div>
                    </form>");
                echo stdhead('StaffBox') . wrapper($HTMLOUT) . stdfoot();
            } else {
                $session->set('is-warning', $lang['staffbox_msg_noid']);
                header("Location: {$_SERVER['PHP_SELF']}");
                die();
            }
        } else {
            $session->set('is-warning', $lang['staffbox_odd_err']);
            header("Location: {$_SERVER['PHP_SELF']}");
            die();
        }
        break;

    case 'restart':
        if ($id > 0) {
            if (sql_query("UPDATE staffmessages SET answered='0', answeredby='0' WHERE id IN (" . implode(', ', $id) . ')')) {
                $cache->delete('staff_mess_');
                header('Refresh: 2; url=' . $_SERVER['PHP_SELF']);
                $session->set('is-success', $lang['staffbox_restart_ids']);
                header("Location: {$_SERVER['PHP_SELF']}");
                die();
            } else {
                $session->set('is-warning', sprintf($lang['staffbox_sql_err'], ((is_object($mysqli)) ? mysqli_error($mysqli) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false))));
                header("Location: {$_SERVER['PHP_SELF']}");
                die();
            }
        } else {
            $session->set('is-warning', $lang['staffbox_odd_err']);
            header("Location: {$_SERVER['PHP_SELF']}");
            die();
        }
        break;

    default:
        $fluent = $container->get(Database::class);
        $count_msgs = $fluent->from('staffmessages')
                             ->select(null)
                             ->select('COUNT(id) AS count')
                             ->fetch('count');

        $perpage = 15;
        $pager = pager($perpage, $count_msgs, 'staffbox.php?');
        if (!$count_msgs) {
            $session->set('is-warning', $lang['staffbox_no_msgs']);
            header('Location: ' . $site_config['paths']['baseurl']);
            die();
        } else {
            $HTMLOUT .= "
                    <h1 class='has-text-centered'>{$lang['staffbox_info']}</h1>
                    <form method='post' name='staffbox' action='{$_SERVER['PHP_SELF']}' enctype='multipart/form-data' accept-charset='utf-8'>";
            $HTMLOUT .= $count_msgs > $perpage ? $pager['pagertop'] : '';
            $head = "
                        <tr>
                            <th>{$lang['staffbox_subject']}</th>
                            <th>{$lang['staffbox_sender']}</th>
                            <th>{$lang['staffbox_added']}</th>
                            <th>{$lang['staffbox_answered']}</th>
                            <th><input type='checkbox' id='checkThemAll'></th>
                        </tr>";
            $r = sql_query('SELECT s.id, s.added, s.subject, s.answered, s.answeredby, s.sender, s.answer, u.username, u2.username AS username2 FROM staffmessages AS s LEFT JOIN users AS u ON s.sender = u.id LEFT JOIN users AS u2 ON s.answeredby = u2.id ORDER BY id DESC ' . $pager['limit']) or sqlerr(__FILE__, __LINE__);
            $body = '
                    <tbody>';
            while ($a = mysqli_fetch_assoc($r)) {
                $body .= "
                        <tr>
                            <td><a href='" . $_SERVER['PHP_SELF'] . '?do=view&amp;id=' . (int) $a['id'] . "'>" . htmlsafechars($a['subject']) . '</a></td>
                            <td><b>' . ($a['username'] ? format_username((int) $a['sender']) : 'Unknown[' . (int) $a['sender'] . ']') . '</b></td>
                            <td>' . get_date((int) $a['added'], 'DATE', 1) . "<br><span class='small'>" . get_date((int) $a['added'], 'LONG', 1, 0) . '</span></td>
                            <td><b>' . ($a['answeredby'] > 0 ? 'by ' . format_username((int) $a['answeredby']) . '<br>' . get_date((int) $a['answered'], 'LONG', 1, 0) : '<span>No</span>') . "</b></td>
                            <td><input type='checkbox' name='id[]' value='" . (int) $a['id'] . "'></td>
                        </tr>";
            }
            $body .= '
                    </tbody>';
            $HTMLOUT .= main_table($body, $head);
            $HTMLOUT .= "
                <div class='has-text-centered top20 bottom20'>
                    <select name='do'>
                        <option value='delete'>{$lang['staffbox_do_delete']}</option>
                        <option value='setanswered'>{$lang['staffbox_do_set']}</option>
                    </select>
                    <input type='submit' class='button is-small' value='{$lang['staffbox_confirm']}'>
                </div>
            </form>";
            $HTMLOUT .= $count_msgs > $perpage ? $pager['pagerbottom'] : '';
            $HTMLOUT = wrapper($HTMLOUT);
        }
        echo stdhead($lang['staffbox_head']) . wrapper($HTMLOUT) . stdfoot();
}
