<?php

declare(strict_types = 1);

use DI\DependencyException;
use DI\NotFoundException;

/**
 * @param $data
 *
 * @throws DependencyException
 * @throws NotFoundException
 * @throws \Envms\FluentPDO\Exception
 */
function ajax_chat_cleanup($data)
{
    global $site_config;

    $time_start = microtime(true);
    require_once INCL_DIR . 'function_users.php';
    $res = sql_query('SELECT id, channel, ttl, text FROM ajax_chat_messages
                        WHERE ttl > 0 AND UNIX_TIMESTAMP(dateTime) + ttl <= UNIX_TIMESTAMP(NOW())') or sqlerr(__FILE__, __LINE__);

    while ($row = mysqli_fetch_assoc($res)) {
        if (strpos($row['text'], '/delete') === false) {
            sql_query('INSERT INTO ajax_chat_messages (userID, userName, userRole, channel, dateTime, ttl, text) VALUES (2, ' . sqlesc($site_config['chatbot']['name']) . ' ,' . sqlesc($site_config['chatbot']['role']) . ', ' . $row['channel'] . ", NOW(), 300, '/delete " . $row['id'] . "')") or sqlerr(__FILE__, __LINE__);
        }
        sql_query('DELETE FROM ajax_chat_messages WHERE id=' . $row['id']) or sqlerr(__FILE__, __LINE__);
    }

    sql_query("UPDATE ajax_chat_messages SET text = REPLACE(REPLACE(text, '[/img]', '[/url]'), '[img]', '[url]') WHERE text LIKE '%[img]%[/img]%' AND dateTime <= NOW() - INTERVAL 1 DAY;") or sqlerr(__FILE__, __LINE__);

    $time_end = microtime(true);
    $run_time = $time_end - $time_start;
    $text = " Run time: $run_time seconds";
    echo $text . "\n";
    if ($data['clean_log']) {
        write_log('AJAX Chat Cleanup: Autoshout posts Deleted' . $text);
    }
}
