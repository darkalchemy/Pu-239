<?php
function ajax_chat_cleanup($data)
{
    global $site_config, $queries, $mc1;
    require_once INCL_DIR.'user_functions.php';
    set_time_limit(1200);
    ignore_user_abort(true);
    $res = sql_query("SELECT id, channel, ttl, text FROM ajax_chat_messages
        				WHERE ttl > 0 AND UNIX_TIMESTAMP(dateTime) + ttl <= UNIX_TIMESTAMP(NOW())") or sqlerr(__FILE__, __LINE__);

    while ($row = mysqli_fetch_assoc($res)) {
        if (strpos($row['text'], '/delete') === false) {
            sql_query("INSERT INTO ajax_chat_messages (userID, userName, userRole, channel, dateTime, ttl, ip, text) VALUES (2, " . sqlesc($site_config['chatBotName']) ." ," . sqlesc($site_config['chatBotRole']) . ", " . $row['channel'] . ", NOW(), 300, '', '/delete " . $row['id'] . "')") or sqlerr(__FILE__, __LINE__);
        }
        sql_query('DELETE FROM ajax_chat_messages WHERE id = ' . $row['id']) or sqlerr(__FILE__, __LINE__);
    }

    if ($data['clean_log'] && $queries > 0) {
        write_log("AJAX Chat Cleanup: Autoshout posts Deleted using $queries queries");
    }
}
