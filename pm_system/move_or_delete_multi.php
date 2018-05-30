<?php

global $CURUSER, $site_config, $lang, $cache;

$pm_messages = $_POST['pm'];
if (isset($_POST['move'])) {
    if (is_valid_id($pm_messages)) {
        sql_query('UPDATE messages SET saved = "yes", location = ' . sqlesc($mailbox) . ' WHERE id = ' . sqlesc($pm_messages) . ' AND receiver = ' . sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);
    } else {
        sql_query('UPDATE messages SET saved = "yes", location = ' . sqlesc($mailbox) . ' WHERE id IN (' . implode(', ', array_map('sqlesc', $pm_messages)) . ') AND receiver =' . sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);
    }
    if (mysqli_affected_rows($GLOBALS['___mysqli_ston']) === 0) {
        stderr($lang['pm_error'], $lang['pm_move_err']);
    }
    header('Location: ?action=view_mailbox&multi_move=1&box=' . $mailbox);
    die();
}
if (isset($_POST['delete'])) {
    $pm_messages = $_POST['pm'];
    foreach ($pm_messages as $id) {
        $res     = sql_query('SELECT * FROM messages WHERE id = ' . sqlesc($id));
        $message = mysqli_fetch_assoc($res);
        if ($message['receiver'] == $CURUSER['id'] && $message['urgent'] === 'yes' && $message['unread'] === 'yes') {
            stderr($lang['pm_error'], '' . $lang['pm_delete_err'] . '<a class="altlink" href="' . $site_config['baseurl'] . '/pm_system.php?action=view_message&id=' . $pm_id . '">' . $lang['pm_delete_back'] . '</a>' . $lang['pm_delete_msg'] . '');
        }
        if ($message['receiver'] == $CURUSER['id'] || $message['sender'] == $CURUSER['id'] && $message['location'] == PM_DELETED) {
            sql_query('DELETE FROM messages WHERE id = ' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
        } elseif ($message['receiver'] == $CURUSER['id']) {
            sql_query('UPDATE messages SET location = 0, unread = "no" WHERE id = ' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
        } elseif ($message['sender'] == $CURUSER['id'] && $message['location'] != PM_DELETED) {
            sql_query('UPDATE messages SET saved = "no" WHERE id = ' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
        }
        $cache->delete('inbox_' . $CURUSER['id']);
    }

    if (mysqli_affected_rows($GLOBALS['___mysqli_ston']) === 0) {
        stderr($lang['pm_error'], $lang['pm_delete_err_multi']);
    }
    if (isset($_POST['returnto'])) {
        header('Location: pm_system.php?action=' . $_POST['returnto'] . '&multi_delete=1');
    } elseif (isset($_POST['draft_section'])) {
        header('Location: pm_system.php?action=viewdrafts&multi_delete=1');
    } else {
        header('Location: pm_system.php?action=view_mailbox&multi_delete=1&box=' . $mailbox);
    }
    die();
}
