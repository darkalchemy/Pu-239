<?php
//=== Delete a single message first make sure it's not an unread urgent staff message
$res = sql_query('SELECT receiver, sender, urgent, unread, saved, location FROM messages WHERE id=' . sqlesc($pm_id)) or sqlerr(__FILE__, __LINE__);
$message = mysqli_fetch_assoc($res);
//=== make sure they aren't deleting a staff message...
if ($message['receiver'] == $CURUSER['id'] && $message['urgent'] == 'yes' && $message['unread'] == 'yes') {
    stderr($lang['pm_error'], '' . $lang['pm_delete_err'] . '<a class="altlink" href="pm_system.php?action=view_message&id=' . $pm_id . '">' . $lang['pm_delete_msg'] . '</a> to message.');
}
//=== make sure message isn't saved before deleting it, or just update location
if ($message['receiver'] == $CURUSER['id'] /* && $message['saved'] == 'no'*/ || $message['sender'] == $CURUSER['id'] && $message['location'] == PM_DELETED) {
    sql_query('DELETE FROM messages WHERE id=' . sqlesc($pm_id)) or sqlerr(__FILE__, __LINE__);
    $mc1->delete_value('inbox_new_' . $CURUSER['id']);
    $mc1->delete_value('inbox_new_sb_' . $CURUSER['id']);
} elseif ($message['receiver'] == $CURUSER['id'] /* && $message['saved'] == 'yes'*/) {
    sql_query('UPDATE messages SET location=0, unread=\'no\' WHERE id=' . sqlesc($pm_id)) or sqlerr(__FILE__, __LINE__);
    $mc1->delete_value('inbox_new_' . $CURUSER['id']);
    $mc1->delete_value('inbox_new_sb_' . $CURUSER['id']);
} elseif ($message['sender'] == $CURUSER['id'] && $message['location'] != PM_DELETED) {
    sql_query('UPDATE messages SET saved=\'no\' WHERE id=' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
    $mc1->delete_value('inbox_new_' . $CURUSER['id']);
    $mc1->delete_value('inbox_new_sb_' . $CURUSER['id']);
}
//=== see if it worked :D
if (mysqli_affected_rows($GLOBALS['___mysqli_ston']) === 0) {
    stderr($lang['pm_error'], '' . $lang['pm_error'] . '<a class="altlink" href="pm_system.php?action=view_message&id=' . $pm_id . '>' . $lang['pm_delete_back'] . '</a>' . $lang['pm_delete_msg'] . '');
}
header('Location: pm_system.php?action=view_mailbox&deleted=1');
exit();
