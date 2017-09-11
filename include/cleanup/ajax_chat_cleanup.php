<?php
function ajax_chat_cleanup($data)
{
    global $site_config, $queries, $mc1;
    require_once INCL_DIR.'user_functions.php';
    set_time_limit(1200);
    ignore_user_abort(true);
    $res = sql_query("SELECT id, channel, ttl FROM ajax_chat_messages
        				WHERE ttl > 0 AND UNIX_TIMESTAMP(dateTime) + ttl <= UNIX_TIMESTAMP(NOW())") or sqlerr(__FILE__, __LINE__);

    while ($row = $res->fetch_row()) {
        sql_query("INSERT INTO ajax_chat_messages (userID, userName, userRole, channel, dateTime, ttl, ip, text) VALUES (2, " . sqlesc($site_config['chatBotName']) ." ," . sqlesc($site_config['chatBotRole']) . ", " . $row[1] . ", NOW(), 240, '', '/delete " . $row[0] . "')") or sqlerr(__FILE__, __LINE__);
        sql_query('DELETE FROM ajax_chat_messages WHERE id = ' . $row[0]) or sqlerr(__FILE__, __LINE__);
    }

    if ($queries > 0) {
        write_log("AJAX Chat Cleanup: Autoshout posts Deleted using $queries queries");
    }
}
