<?php

function cleanup_log($data)
{
    $text = sqlesc($data['clean_title']);
    $added = TIME_NOW;
    $ip = sqlesc($_SERVER['REMOTE_ADDR']);
    $desc = sqlesc($data['clean_desc']);
    sql_query("INSERT INTO cleanup_log (clog_event, clog_time, clog_ip, clog_desc) VALUES ($text, $added, $ip, {$desc})") or sqlerr(__FILE__, __LINE__);
}
function docleanup($data)
{
    global $INSTALLER09, $queries, $mc1;
    require_once INCL_DIR.'user_functions.php';
    set_time_limit(1200);
    ignore_user_abort(1);
    $res = sql_query("SELECT id, channel, ttl FROM ajax_chat_messages
        				WHERE ttl > 0 AND UNIX_TIMESTAMP(dateTime) + ttl <= UNIX_TIMESTAMP(NOW())") or sqlerr(__FILE__, __LINE__);

    while ($row = $res->fetch_row()) {
        sql_query("INSERT INTO ajax_chat_messages (userID, userName, userRole, channel, dateTime, ttl, ip, text) VALUES (2, " . sqlesc($INSTALLER09['chatBotName']) ." ," . sqlesc($INSTALLER09['chatBotRole']) . ", " . $row[1] . ", NOW(), 240, '', '/delete " . $row[0] . "')") or sqlerr(__FILE__, __LINE__);
        sql_query('DELETE FROM ajax_chat_messages WHERE id = ' . $row[0]) or sqlerr(__FILE__, __LINE__);
    }
}
