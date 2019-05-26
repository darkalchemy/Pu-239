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
function expired_signup_update($data)
{
    global $container, $site_config;

    $time_start = microtime(true);
    $dt = TIME_NOW;
    $deadtime = $dt - $site_config['signup']['timeout'];
    $res = sql_query("SELECT id, username FROM users WHERE status != 0 AND registered < $deadtime ORDER BY username DESC") or sqlerr(__FILE__, __LINE__);
    $cache = $container->get(Cache::class);
    if (mysqli_num_rows($res) != 0) {
        while ($arr = mysqli_fetch_assoc($res)) {
            $userid = $arr['id'];
            $res_del = sql_query('DELETE FROM users WHERE id = ' . sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
            $cache->delete('user_' . $userid);
            if ($data['clean_log']) {
                write_log("Expired Signup Cleanup: User: {$arr['username']} was deleted");
            }
        }
    }

    $time_end = microtime(true);
    $run_time = $time_end - $time_start;
    $text = " Run time: $run_time seconds";
    echo $text . "\n";
    if ($data['clean_log']) {
        write_log('Expired Signup Completed' . $text);
    }
}
