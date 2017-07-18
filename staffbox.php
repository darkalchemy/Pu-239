<?php
/**
 |--------------------------------------------------------------------------|
 |   https://github.com/Bigjoos/                			    |
 |--------------------------------------------------------------------------|
 |   Licence Info: GPL			                                    |
 |--------------------------------------------------------------------------|
 |   Copyright (C) 2010 U-232 V4					    |
 |--------------------------------------------------------------------------|
 |   A bittorrent tracker source based on TBDev.net/tbsource/bytemonsoon.   |
 |--------------------------------------------------------------------------|
 |   Project Leaders: Mindless,putyn.					    |
 |--------------------------------------------------------------------------|
  _   _   _   _   _     _   _   _   _   _   _     _   _   _   _
 / \ / \ / \ / \ / \   / \ / \ / \ / \ / \ / \   / \ / \ / \ / \
( U | - | 2 | 3 | 2 )-( S | o | u | r | c | e )-( C | o | d | e )
 \_/ \_/ \_/ \_/ \_/   \_/ \_/ \_/ \_/ \_/ \_/   \_/ \_/ \_/ \_/
 */
require_once (dirname(__FILE__) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php');
require_once (INCL_DIR . 'user_functions.php');
require_once (INCL_DIR . 'bbcode_functions.php');
require_once (INCL_DIR . 'pager_functions.php');
require_once (INCL_DIR . 'html_functions.php');
dbconn(false);
loggedinorreturn();
function mkint($x)
{
    return 0 + $x;
}
$lang = array_merge(load_language('global') , load_language('staffbox'));
$stdfoot = array(
    /** include js **/
    'js' => array(
        'staffcontact'
    )
);
$stdhead = array(
    /** include css **/
    'css' => array(
        'staffbox'
    )
);
if ($CURUSER['class'] < UC_STAFF) stderr($lang['staffbox_err'], $lang['staffbox_class']);
$valid_do = array(
    'view',
    'delete',
    'setanswered',
    'restart',
    ''
);
$do = isset($_GET['do']) && in_array($_GET['do'], $valid_do) ? $_GET['do'] : (isset($_POST['do']) && in_array($_POST['do'], $valid_do) ? $_POST['do'] : '');
$id = isset($_GET['id']) ? (int)$_GET['id'] : (isset($_POST['id']) && is_array($_POST['id']) ? array_map('mkint', $_POST['id']) : 0);
$message = isset($_POST['message']) && !empty($_POST['message']) ? htmlsafechars($_POST['message']) : '';
$reply = isset($_POST['reply']) && $_POST['reply'] == 1 ? true : false;
switch ($do) {
case 'delete':
    if ($id > 0) {
        if (sql_query('DELETE FROM staffmessages WHERE id IN (' . join(',', $id) . ')')) {
            $mc1->delete_value('staff_mess_');
            header('Refresh: 2; url=' . $_SERVER['PHP_SELF']);
            stderr($lang['staffbox_success'], $lang['staffbox_delete_ids']);
        } else stderr($lang['staffbox_err'], sprintf($lang['staffbox_sql_err'], ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false))));
    } else stderr($lang['staffbox_err'], $lang['staffbox_odd_err']);
    break;

case 'setanswered':
    if ($id > 0) {
        if ($reply && empty($message)) {
            stderr($lang['staffbox_err'], $lang['staffbox_no_message']);
            exit;
        }
        $q1 = sql_query('SELECT s.msg,s.sender,s.subject,u.username FROM staffmessages as s LEFT JOIN users as u ON s.sender=u.id WHERE s.id IN (' . join(',', $id) . ')') or sqlerr(__FILE__, __LINE__);
        $a = mysqli_fetch_assoc($q1);
        $response = htmlsafechars($message) . "\n---" . htmlsafechars($a['username']) . " wrote ---\n" . htmlsafechars($a['msg']);
        sql_query('INSERT INTO messages(sender,receiver,added,subject,msg) VALUES(' . sqlesc($CURUSER['id']) . ',' . sqlesc($a['sender']) . ',' . TIME_NOW . ',' . sqlesc('RE: ' . $a['subject']) . ',' . sqlesc($response) . ')') or sqlerr(__FILE__, __LINE__);
        $mc1->delete_value('inbox_new_' . $a['sender']);
        $mc1->delete_value('inbox_new_sb_' . $a['sender']);
        $message = ', answer=' . sqlesc($message);
        if (sql_query('UPDATE staffmessages SET answered=\'1\', answeredby=' . sqlesc($CURUSER['id']) . ' ' . $message . ' WHERE id IN (' . join(',', $id) . ')')) {
            $mc1->delete_value('staff_mess_');
            header('Refresh: 2; url=' . $_SERVER['PHP_SELF']);
            stderr($lang['staffbox_success'], $lang['staffbox_setanswered_ids']);
        } else stderr($lang['staffbox_err'], sprintf($lang['staffbox_sql_err'], ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false))));
    } else stderr($lang['staffbox_err'], $lang['staffbox_odd_err']);
    break;

case 'view':
    if ($id > 0) {
        $q2 = sql_query('SELECT s.id, s.added, s.msg, s.subject, s.answered, s.answer, s.answeredby, s.sender, s.answer, u.username, u2.username as username2 FROM staffmessages as s LEFT JOIN users as u ON s.sender = u.id LEFT JOIN users as u2 ON s.answeredby = u2.id  WHERE s.id = ' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
        if (mysqli_num_rows($q2) == 1) {
            $a = mysqli_fetch_assoc($q2);
            $HTMLOUT = begin_main_frame() . begin_frame($lang['staffbox_pm_view']);
            $HTMLOUT.= "<form action='" . $_SERVER['PHP_SELF'] . "' method='post'>
					      <div class='global_icon_sb'><img src='images/global.design/helpdesk.png' alt='' title='Helpdesk' class='global_image' width='25'/></div>
                <div class='global_head_sb'>Helpdesk</div><br />
                <div class='global_text_sb'><br /><br />
								<table width='90%' border='1' cellspacing='0' cellpadding='5' align='center'>
								 <tr><td>{$lang['staffbox_pm_from']}&nbsp;<a href='userdetails.php?id=" . (int)$a['sender'] . "'>" . htmlsafechars($a['username']) . "</a> at " . get_date($a['added'], 'DATE', 1) . "<br/>
								 {$lang['staffbox_pm_subject']} : <b>" . htmlsafechars($a['subject']) . "</b><br/>
								 {$lang['staffbox_pm_answered']} : <b>" . ($a['answeredby'] > 0 ? "<a href='userdetails.php?id=" . (int)$a['answeredby'] . "'>" . htmlsafechars($a['username2']) . "</a>" : "<span style='color:#ff0000'>No</span>") . "</b>
								</td></tr>
								<tr><td>" . format_comment($a['msg']) . "
								</td></tr>
								<tr><td>{$lang['staffbox_pm_answer']}<br/>
									" . ($a['answeredby'] == 0 ? "<textarea rows='5' cols='75' name='message' ></textarea>" : ($a['answer'] ? format_comment($a['answer']) : "<b>{$lang['staffbox_pm_noanswer']}</b>")) . "
								</td></tr>
								<tr><td align='left'>
									<select name='do'>
										<option value='setanswered' " . ($a['answeredby'] > 0 ? 'disabled=\'disabled\'' : "") . ">{$lang['staffbox_pm_reply']}</option>
										<option value='restart' " . ($a['answeredby'] != $CURUSER['id'] ? 'disabled=\'disabled\'' : "") . ">{$lang['staffbox_pm_restart']}</option>
										<option value='delete'>{$lang['staffbox_pm_delete']}</option>
									</select>
									<input type='hidden' name='reply' value='1'/>
									<input type='hidden' name='id[]' value='" . (int)$a['id'] . "'/><input type='submit' value='{$lang['staffbox_confirm']}' />
									</td></tr>
								</table>
								</div></form>";
            $HTMLOUT.= end_frame() . end_main_frame();
            echo (stdhead('StaffBox', true, $stdhead) . $HTMLOUT . stdfoot());
        } else stderr($lang['staffbox_err'], $lang['staffbox_msg_noid']);
    } else stderr($lang['staffbox_err'], $lang['staffbox_odd_err']);
    break;

case 'restart':
    if ($id > 0) {
        if (sql_query('UPDATE staffmessages SET answered=\'0\', answeredby=\'0\' WHERE id IN (' . join(',', $id) . ')')) {
            $mc1->delete_value('staff_mess_');
            header('Refresh: 2; url=' . $_SERVER['PHP_SELF']);
            stderr($lang['staffbox_success'], $lang['staffbox_restart_ids']);
        } else stderr($lang['staffbox_err'], sprintf($lang['staffbox_sql_err'], ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false))));
    } else stderr($lang['staffbox_err'], $lang['staffbox_odd_err']);
    break;

default:
    $count_msgs = get_row_count('staffmessages');
    $perpage = 4;
    $pager = pager($perpage, $count_msgs, 'staffbox.php?');
    if (!$count_msgs) stderr($lang['staffbox_err'], $lang['staffbox_no_msgs']);
    else {
        $HTMLOUT = begin_main_frame() . begin_frame($lang['staffbox_info']);
        $HTMLOUT.= "<form method='post' name='staffbox' action='" . $_SERVER['PHP_SELF'] . "'>
	<div class='global_icon_sb'><img src='images/global.design/helpdesk.png' alt='' title='Helpdesk' class='global_image' width='25'/></div>
  <div class='global_head_sb'>Helpdesk</div>
  <div class='global_text_sb'><br />";
        $HTMLOUT.= $pager['pagertop'];
        $HTMLOUT.= "<table width='80%' border='1' cellspacing='0' cellpadding='5' align='center'>";
        $HTMLOUT.= "<tr>
                 <td class='colhead' align='center' width='100%'>{$lang['staffbox_subject']}</td>
                 <td class='colhead' align='center'>{$lang['staffbox_sender']}</td>
                 <td class='colhead' align='center'>{$lang['staffbox_added']}</td>
                 <td class='colhead' align='center'>{$lang['staffbox_answered']}</td>
                 <td class='colhead' align='center'><input type='checkbox' name='t' onclick=\"checkbox('staffbox')\" /></td>
                </tr>";
        $r = sql_query('SELECT s.id, s.added, s.subject, s.answered, s.answeredby, s.sender, s.answer, u.username, u2.username as username2 FROM staffmessages as s LEFT JOIN users as u ON s.sender = u.id LEFT JOIN users as u2 ON s.answeredby = u2.id ORDER BY id desc ' . $pager['limit']) or sqlerr(__FILE__, __LINE__);
        while ($a = mysqli_fetch_assoc($r)) $HTMLOUT.= "<tr>
                   <td align='center'><a href='" . $_SERVER['PHP_SELF'] . "?do=view&amp;id=" . (int)$a['id'] . "'>" . htmlsafechars($a['subject']) . "</a></td>
                   <td align='center'><b>" . ($a['username'] ? "<a href='userdetails.php?id=" . (int)$a['sender'] . "'>" . htmlsafechars($a['username']) . "</a>" : "Unknown[" . (int)$a['sender'] . "]") . "</b></td>
                   <td align='center' nowrap='nowrap'>" . get_date($a['added'], 'DATE', 1) . "<br/><span class='small'>" . get_date($a['added'], 0, 1) . "</span></td>
				   <td align='center'><b>" . ($a['answeredby'] > 0 ? "by <a href='userdetails.php?id=" . (int)$a['answeredby'] . "'>" . htmlsafechars($a['username2']) . "</a>" : "<span style='color:#ff0000'>No</span>") . "</b></td>
                   <td align='center'><input type='checkbox' name='id[]' value='" . (int)$a['id'] . "' /></td>
                  </tr>\n";
        $HTMLOUT.= "<tr><td align='right' colspan='5'>
					<select name='do'>
						<option value='delete'>{$lang['staffbox_do_delete']}</option>
						<option value='setanswered'>{$lang['staffbox_do_set']}</option>
					</select>
					<input type='submit' value='{$lang['staffbox_confirm']}' /></td></tr>
				</table></div></form>";
        $HTMLOUT.= $pager['pagerbottom'];
        $HTMLOUT.= end_frame() . end_main_frame();
    }
    echo stdhead($lang['staffbox_head'], true, $stdhead) . $HTMLOUT . stdfoot($stdfoot);
}
?>
