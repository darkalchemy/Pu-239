<?php

declare(strict_types = 1);

use DI\DependencyException;
use DI\NotFoundException;
use MatthiasMullie\Scrapbook\Exception\UnbegunTransaction;
use Pu239\Cache;

/**
 * @param $data
 *
 * @throws DependencyException
 * @throws NotFoundException
 * @throws \Envms\FluentPDO\Exception
 * @throws UnbegunTransaction
 */
function irc_update($data)
{
    global $container, $site_config;

    $time_start = microtime(true);
    $users_buffer = [];

    $res = sql_query("SELECT id, seedbonus, irctotal FROM users WHERE onirc = 'yes'") or sqlerr(__FILE__, __LINE__);
    if (mysqli_num_rows($res) > 0) {
        $cache = $container->get(Cache::class);
        while ($arr = mysqli_fetch_assoc($res)) {
            $users_buffer[] = '(' . $arr['id'] . ', ' . $site_config['bonus']['irc_per_duration'] . ', ' . $site_config['irc']['autoclean_interval'] . ')';
            $update['seedbonus'] = ($arr['seedbonus'] + $site_config['bonus']['irc_per_duration']);
            $update['irctotal'] = ($arr['irctotal'] + $site_config['irc']['autoclean_interval']);
            $cache->update_row('user_' . $arr['id'], [
                'irctotal' => $update['irctotal'],
                'seedbonus' => $update['seedbonus'],
            ], $site_config['expires']['user_cache']);
        }
        $count = count($users_buffer);
        if ($count > 0) {
            sql_query('INSERT INTO users (id,seedbonus,irctotal) VALUES ' . implode(', ', $users_buffer) . ' ON DUPLICATE KEY UPDATE seedbonus=seedbonus + VALUES(seedbonus),irctotal=irctotal + VALUES(irctotal)') or sqlerr(__FILE__, __LINE__);
        }
        if ($data['clean_log']) {
            write_log('Cleanup ' . $count . ' users idling on IRC');
        }
        unset($users_buffer, $update, $count);
    }
    $time_end = microtime(true);
    $run_time = $time_end - $time_start;
    $text = " Run time: $run_time seconds";
    echo $text . "\n";
    if ($data['clean_log']) {
        write_log('Irc Cleanup: Completed' . $text);
    }
}
