<?php

global $CURUSER, $lang;

if ($CURUSER['class'] >= UC_MODERATOR) {
    if (empty($_POST['delreq'])) {
        stderr("{$lang['error_error']}", "{$lang['error_empty']}");
    }
    sql_query('DELETE FROM requests WHERE id IN ('.implode(', ', array_map('sqlesc', $_POST['delreq'])).')');
    //sql_query('DELETE FROM voted_requests WHERE requestid IN (' . implode(', ', array_map('sqlesc', $_POST['delreq'])) . ')');
    sql_query('DELETE FROM comments WHERE request IN ('.implode(', ', array_map('sqlesc', $_POST['delreq'])).')');
    header('Refresh: 0; url=viewrequests.php');
    die();
} else {
    stderr("{$lang['error_error']}", "{$lang['error_dee']}");
}
