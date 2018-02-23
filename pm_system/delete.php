<?php

global $CURUSER, $site_config, $lang, $cache;

$res = sql_query('SELECT receiver, sender, urgent, unread, saved, location FROM messages WHERE id = '.sqlesc($pm_id)) or sqlerr(__FILE__, __LINE__);
$message = mysqli_fetch_assoc($res);
if ($message['receiver'] == $CURUSER['id'] && 'yes' == $message['urgent'] && 'yes' == $message['unread']) {
    stderr($lang['pm_error'], ''.$lang['pm_delete_err'].'<a class="altlink" href="pm_system.php?action=view_message&id='.$pm_id.'">'.$lang['pm_delete_msg'].'</a> to message.');
}
if ($message['receiver'] == $CURUSER['id'] || $message['sender'] == $CURUSER['id'] && PM_DELETED == $message['location']) {
    sql_query('DELETE FROM messages WHERE id = '.sqlesc($pm_id)) or sqlerr(__FILE__, __LINE__);
    $cache->delete('inbox_'.$CURUSER['id']);
} elseif ($message['receiver'] == $CURUSER['id']) {
    sql_query("UPDATE messages SET location = 0, unread = 'no' WHERE id = ".sqlesc($pm_id)) or sqlerr(__FILE__, __LINE__);
    $cache->delete('inbox_'.$CURUSER['id']);
} elseif ($message['sender'] == $CURUSER['id'] && PM_DELETED != $message['location']) {
    sql_query("UPDATE messages SET saved = 'no' WHERE id = ".sqlesc($id)) or sqlerr(__FILE__, __LINE__);
    $cache->delete('inbox_'.$CURUSER['id']);
}
if (0 === mysqli_affected_rows($GLOBALS['___mysqli_ston'])) {
    stderr($lang['pm_error'], ''.$lang['pm_error'].'<a class="altlink" href="'.$site_config['baseurl'].'/pm_system.php?action=view_message&id='.$pm_id.'>'.$lang['pm_delete_back'].'</a>'.$lang['pm_delete_msg'].'');
}
header('Location: pm_system.php?action=view_mailbox&deleted=1');
die();
