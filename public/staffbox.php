<?php

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'user_functions.php';
require_once INCL_DIR . 'bbcode_functions.php';
require_once INCL_DIR . 'pager_functions.php';
require_once INCL_DIR . 'html_functions.php';
check_user_status();
global $CURUSER, $site_config, $cache, $session;

/**
 * @param $x
 *
 * @return int
 */
function mkint($x)
{
    return (int) $x;
}

$lang = array_merge(load_language('global'), load_language('staffbox'));
if ($CURUSER['class'] < UC_STAFF) {
    $session->set('is-danger', $lang['staffbox_class']);
    header('Location: index.php');
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
$id = isset($_GET['id']) ? (int) $_GET['id'] : (isset($_POST['id']) && is_array($_POST['id']) ? array_map('mkint', $_POST['id']) : 0);
$message = isset($_POST['message']) && !empty($_POST['message']) ? htmlsafechars($_POST['message']) : '';
$reply = isset($_POST['reply']) && $_POST['reply'] == 1 ? true : false;
$HTMLOUT = '';
switch ($do) {
    case 'delete':
        if ($id > 0) {
            if (sql_query('DELETE FROM staffmessages WHERE id IN (' . join(',', $id) . ')')) {
                $cache->delete('staff_mess_');
                header('Refresh: 2; url=' . $_SERVER['PHP_SELF']);
                $session->set('is-success', $lang['staffbox_delete_ids']);
                header("Location: {$_SERVER['PHP_SELF']}");
                die();
            } else {
                $session->set('is-warning', sprintf($lang['staffbox_sql_err'], ((is_object($GLOBALS['___mysqli_ston'])) ? mysqli_error($GLOBALS['___mysqli_ston']) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false))));
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
            $q1 = sql_query('SELECT s.msg,s.sender,s.subject,u.username FROM staffmessages AS s LEFT JOIN users AS u ON s.sender=u.id WHERE s.id IN (' . join(',', $id) . ')') or sqlerr(__FILE__, __LINE__);
            $a = mysqli_fetch_assoc($q1);
            $response = htmlsafechars($message) . "\n---" . htmlsafechars($a['username']) . " wrote ---\n" . htmlsafechars($a['msg']);
            sql_query('INSERT INTO messages(sender,receiver,added,subject,msg) VALUES(' . sqlesc($CURUSER['id']) . ',' . sqlesc($a['sender']) . ',' . TIME_NOW . ',' . sqlesc('RE: ' . $a['subject']) . ',' . sqlesc($response) . ')') or sqlerr(__FILE__, __LINE__);
            $cache->increment('inbox_' . $a['sender']);
            $message = ', answer=' . sqlesc($message);
            if (sql_query('UPDATE staffmessages SET answered=\'1\', answeredby=' . sqlesc($CURUSER['id']) . ' ' . $message . ' WHERE id IN (' . join(',', $id) . ')')) {
                $cache->delete('staff_mess_');
                $session->set('is-success', $lang['staffbox_setanswered_ids']);
                header("Location: {$_SERVER['PHP_SELF']}");
                die();
            } else {
                $session->set('is-warning', sprintf($lang['staffbox_sql_err'], ((is_object($GLOBALS['___mysqli_ston'])) ? mysqli_error($GLOBALS['___mysqli_ston']) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false))));
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
            $q2 = sql_query('SELECT s.id, s.added, s.msg, s.subject, s.answered, s.answer, s.answeredby, s.sender, s.answer, u.username, u2.username AS username2 FROM staffmessages AS s LEFT JOIN users AS u ON s.sender = u.id LEFT JOIN users AS u2 ON s.answeredby = u2.id  WHERE s.id = ' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
            if (mysqli_num_rows($q2) == 1) {
                $a = mysqli_fetch_assoc($q2);
                $HTMLOUT .= "
                    <h1 class='has-text-centered'>{$lang['staffbox_pm_view']}</h1>" . main_div("
                    <form action='{$_SERVER['PHP_SELF']}' method='post'>
                        <div class='bordered top20 bottom20 bg-00'>
                            <div>{$lang['staffbox_pm_from']}: " . format_username($a['sender']) . ' at ' . get_date($a['added'], 'DATE', 1) . "</div>
                            <div>{$lang['staffbox_pm_subject']}: " . htmlsafechars($a['subject']) . "</div>
                            <div>{$lang['staffbox_pm_answered']}: " . ($a['answeredby'] > 0 ? format_username($a['answeredby']) : '<span>No</span>') . "</div>
                        </div>
                        <div class='bordered top20 bottom20 bg-00'>" .
                                                                                                                           format_comment($a['msg']) . "
                        </div>
                        <div class='bordered top20 bottom20 bg-00'>
                            {$lang['staffbox_pm_answer']} " . ($a['answeredby'] == 0 ? "
                            <textarea rows='5' class='w-100' name='message' ></textarea>" : ($a['answer'] ? format_comment($a['answer']) : "<b>{$lang['staffbox_pm_noanswer']}</b>")) . "
                        </div>
                        <div class='has-text-centered top20'>
                            <select name='do'>
                                <option value='setanswered' " . ($a['answeredby'] > 0 ? 'disabled' : '') . ">{$lang['staffbox_pm_reply']}</option>
                                <option value='restart' " . ($a['answeredby'] != $CURUSER['id'] ? 'disabled' : '') . ">{$lang['staffbox_pm_restart']}</option>
                                <option value='delete'>{$lang['staffbox_pm_delete']}</option>
                            </select>
                            <input type='hidden' name='reply' value='1' />
                            <input type='hidden' name='id[]' value='" . (int) $a['id'] . "' />
                            <input type='submit' class='button is-small' value='{$lang['staffbox_confirm']}' />
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
            if (sql_query("UPDATE staffmessages SET answered='0', answeredby='0' WHERE id IN (" . join(',', $id) . ')')) {
                $cache->delete('staff_mess_');
                header('Refresh: 2; url=' . $_SERVER['PHP_SELF']);
                $session->set('is-success', $lang['staffbox_restart_ids']);
                header("Location: {$_SERVER['PHP_SELF']}");
                die();
            } else {
                $session->set('is-warning', sprintf($lang['staffbox_sql_err'], ((is_object($GLOBALS['___mysqli_ston'])) ? mysqli_error($GLOBALS['___mysqli_ston']) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false))));
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
        $count_msgs = get_row_count('staffmessages');
        $perpage = 15;
        $pager = pager($perpage, $count_msgs, 'staffbox.php?');
        if (!$count_msgs) {
            $session->set('is-warning', $lang['staffbox_no_msgs']);
            header('Location: index.php');
            die();
        } else {
            $HTMLOUT .= "
                    <h1 class='has-text-centered'>{$lang['staffbox_info']}</h1>
                    <form method='post' name='staffbox' action='{$_SERVER['PHP_SELF']}'>";
            $HTMLOUT .= $pager['pagertop'];
            $head = "
                        <tr>
                            <th>{$lang['staffbox_subject']}</th>
                            <th>{$lang['staffbox_sender']}</th>
                            <th>{$lang['staffbox_added']}</th>
                            <th>{$lang['staffbox_answered']}</th>
                            <th><input type='checkbox' id='checkThemAll' /></th>
                        </tr>";
            $r = sql_query('SELECT s.id, s.added, s.subject, s.answered, s.answeredby, s.sender, s.answer, u.username, u2.username AS username2 FROM staffmessages AS s LEFT JOIN users AS u ON s.sender = u.id LEFT JOIN users AS u2 ON s.answeredby = u2.id ORDER BY id DESC ' . $pager['limit']) or sqlerr(__FILE__, __LINE__);
            $body = '
                    <tbody>';
            while ($a = mysqli_fetch_assoc($r)) {
                $body .= "
                        <tr>
                            <td><a href='" . $_SERVER['PHP_SELF'] . '?do=view&amp;id=' . (int) $a['id'] . "'>" . htmlsafechars($a['subject']) . '</a></td>
                            <td><b>' . ($a['username'] ? format_username($a['sender']) : 'Unknown[' . (int) $a['sender'] . ']') . '</b></td>
                            <td>' . get_date($a['added'], 'DATE', 1) . "<br><span class='small'>" . get_date($a['added'], 0, 1) . '</span></td>
                            <td><b>' . ($a['answeredby'] > 0 ? 'by ' . format_username($a['answeredby']) : '<span>No</span>') . "</b></td>
                            <td><input type='checkbox' name='id[]' value='" . (int) $a['id'] . "' /></td>
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
                    <input type='submit' class='button is-small' value='{$lang['staffbox_confirm']}' />
                </div>
            </form>";
            $HTMLOUT .= $pager['pagerbottom'];
            $HTMLOUT = wrapper($HTMLOUT);
        }
        echo stdhead($lang['staffbox_head']) . wrapper($HTMLOUT) . stdfoot();
}
