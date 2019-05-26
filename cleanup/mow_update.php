<?php

declare(strict_types = 1);

use DI\DependencyException;
use DI\NotFoundException;
use Pu239\Cache;
use Pu239\Database;

/**
 * @param $data
 *
 * @throws DependencyException
 * @throws NotFoundException
 * @throws \Envms\FluentPDO\Exception
 */
function mow_update($data)
{
    global $container, $site_config;

    $time_start = microtime(true);
    $fluent = $container->get(Database::class);
    $mow = $fluent->from('torrents')
                  ->select(null)
                  ->select('id')
                  ->select('name')
                  ->where('times_completed > 10')
                  ->where('category', $site_config['categories']['movie'])
                  ->orderBy('RAND()')
                  ->limit(1)
                  ->fetch();

    if (!empty($mow)) {
        $set = [
            'value_u' => $mow['id'],
            'value_i' => TIME_NOW,
        ];
        if ($data['clean_log']) {
            write_log('Torrent [' . (int) $mow['id'] . '] [' . htmlentities($mow['name']) . "] was set 'Best Film of the Week' by system");
        }
    } else {
        $set = [
            'value_u' => 0,
            'value_i' => TIME_NOW,
        ];
        if ($data['clean_log']) {
            write_log("'Best Film of the Week' was emptied by system");
        }
    }
    $fluent->update('avps')
           ->set($set)
           ->where("avps.arg = 'bestfilmofweek'")
           ->execute();
    $cache = $container->get(Cache::class);
    $cache->delete('motw_');
    $time_end = microtime(true);
    $run_time = $time_end - $time_start;
    $text = " Run time: $run_time seconds";
    echo $text . "\n";
    if ($data['clean_log']) {
        write_log('Movie of the Week Cleanup: Completed.' . $text);
    }
}
