<?php

/**
 * @param $userid
 *
 * @return string
 *
 * @throws \Envms\FluentPDO\Exception
 */
function account_delete($userid)
{
    global $cache, $user_stuffs;

    if (empty($userid)) {
        return false;
    }
    $user = $user_stuffs->getUserFromId($userid);
    $username = $user['username'];
    $cache->delete('all_users_');
    $cache->delete('user' . $userid);

    sql_query("DELETE FROM users WHERE id = $userid") or sqlerr(__FILE__, __LINE__);
    sql_query("DELETE FROM staffmessages WHERE sender = $userid") or sqlerr(__FILE__, __LINE__);
    sql_query("DELETE FROM staffmessages_answers WHERE sender = $userid") or sqlerr(__FILE__, __LINE__);
    sql_query("DELETE FROM messages WHERE sender = $userid") or sqlerr(__FILE__, __LINE__);
    sql_query("DELETE FROM messages WHERE receiver = $userid") or sqlerr(__FILE__, __LINE__);

    return $username;
}
