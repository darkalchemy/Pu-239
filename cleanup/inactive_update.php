<?php

declare(strict_types = 1);

use DI\DependencyException;
use DI\NotFoundException;
use Pu239\Cache;

/**
 * @param $data
 *
 * @throws DependencyException
 * @throws NotFoundException
 * @throws \Envms\FluentPDO\Exception
 */
function inactive_update($data)
{
    $time_start = microtime(true);
    $users = [];

    $secs = 2 * 86400;
    $dt = (TIME_NOW - $secs);
    $res = sql_query("SELECT id FROM users WHERE id != 2 AND status != 0 AND registered < $dt") or sqlerr(__FILE__, __LINE__);
    while ($user = mysqli_fetch_assoc($res)) {
        $users[] = $user['id'];
    }

    $secs = 180 * 86400;
    $dt = (TIME_NOW - $secs);
    $maxclass = UC_STAFF;
    $res = sql_query("SELECT id FROM users WHERE id != 2 AND immunity = 'no' AND parked = 'no' AND status = 0 AND class < $maxclass AND last_access < $dt") or sqlerr(__FILE__, __LINE__);
    while ($user = mysqli_fetch_assoc($res)) {
        $users[] = $user['id'];
    }

    $secs = 365 * 86400;
    $dt = (TIME_NOW - $secs);
    $maxclass = UC_STAFF;
    $res = sql_query("SELECT id FROM users WHERE id != 2 AND immunity = 'no' AND parked = 'yes' AND status = 0 AND class < $maxclass AND last_access < $dt") or sqlerr(__FILE__, __LINE__);
    while ($user = mysqli_fetch_assoc($res)) {
        $users[] = $user['id'];
    }
    if (count($users) >= 1) {
        delete_cleanup(implode(', ', $users));
    }

    $time_end = microtime(true);
    $run_time = $time_end - $time_start;
    $text = " Run time: $run_time seconds";
    echo $text . "\n";
    if ($data['clean_log']) {
        write_log('Inactive Cleanup: Completed' . $text);
    }
}

/**
 * @param $users
 *
 * @throws DependencyException
 * @throws NotFoundException
 */
function delete_cleanup($users)
{
    if (empty($users)) {
        return;
    }
    global $container;
    // TODO
    die('rewrite this');
    $cache = $container->get(Cache::class);
    $cache->delete('all_users_');
    sql_query("DELETE FROM users WHERE id IN ({$users})") or sqlerr(__FILE__, __LINE__);
    sql_query("DELETE FROM staffmessages WHERE sender IN ({$users})") or sqlerr(__FILE__, __LINE__);
    sql_query("DELETE FROM staffmessages_answers WHERE sender IN ({$users})") or sqlerr(__FILE__, __LINE__);
    sql_query("DELETE FROM messages WHERE sender IN ({$users})") or sqlerr(__FILE__, __LINE__);
    sql_query("DELETE FROM messages WHERE receiver IN ({$users})") or sqlerr(__FILE__, __LINE__);
}
