<?php

/**
 * @param $data
 */
function ajax_chat_cleanup($data)
{
    $time_start = microtime(true);
    dbconn();
    global $site_config, $queries;

    require_once INCL_DIR . 'function_users.php';
    set_time_limit(1200);
    ignore_user_abort(true);

    $res = sql_query('SELECT id, channel, ttl, text FROM ajax_chat_messages
                        WHERE ttl > 0 AND UNIX_TIMESTAMP(dateTime) + ttl <= UNIX_TIMESTAMP(NOW())') or sqlerr(__FILE__, __LINE__);

    while ($row = mysqli_fetch_assoc($res)) {
        if (strpos($row['text'], '/delete') === false) {
            sql_query('INSERT INTO ajax_chat_messages (userID, userName, userRole, channel, dateTime, ttl, ip, text) VALUES (2, ' . sqlesc($site_config['chatBotName']) . ' ,' . sqlesc($site_config['chatBotRole']) . ', ' . $row['channel'] . ", NOW(), 300, '', '/delete " . $row['id'] . "')") or sqlerr(__FILE__, __LINE__);
        }
        sql_query('DELETE FROM ajax_chat_messages WHERE id = ' . $row['id']) or sqlerr(__FILE__, __LINE__);
    }

    sql_query("UPDATE ajax_chat_messages SET text = REPLACE(REPLACE(text, '[/img]', '[/url]'), '[img]', '[url]') WHERE text LIKE '%[img]%[/img]%' AND dateTime <= NOW() - INTERVAL 1 DAY;") or sqlerr(__FILE__, __LINE__);

    $time_end = microtime(true);
    $run_time = $time_end - $time_start;
    $text = " Run time: $run_time seconds";
    echo $text . "\n";
    if ($data['clean_log'] && $queries > 0) {
        write_log("AJAX Chat Cleanup: Autoshout posts Deleted using $queries queries" . $text);
    }
}
