<?php

declare(strict_types = 1);

use DI\DependencyException;
use DI\NotFoundException;
use Pu239\Cache;
use Pu239\User;

/**
 * @param int $userid
 *
 * @throws DependencyException
 * @throws NotFoundException
 * @throws \Envms\FluentPDO\Exception
 *
 * @return bool
 */
function account_delete(int $userid)
{
    global $container;

    if (empty($userid)) {
        return false;
    }
    $user_stuffs = $container->get(User::class);
    $user = $user_stuffs->getUserFromId($userid);
    $username = $user['username'];
    $cache = $container->get(Cache::class);
    $cache->delete('all_users_');
    $cache->delete('user_' . $userid);

    sql_query("DELETE FROM users WHERE id = $userid") or sqlerr(__FILE__, __LINE__);
    sql_query("DELETE FROM staffmessages WHERE sender = $userid") or sqlerr(__FILE__, __LINE__);
    sql_query("DELETE FROM staffmessages_answers WHERE sender = $userid") or sqlerr(__FILE__, __LINE__);
    sql_query("DELETE FROM messages WHERE sender = $userid") or sqlerr(__FILE__, __LINE__);
    sql_query("DELETE FROM messages WHERE receiver = $userid") or sqlerr(__FILE__, __LINE__);

    return $username;
}
