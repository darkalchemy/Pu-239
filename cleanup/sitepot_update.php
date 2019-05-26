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
function sitepot_update($data)
{
    global $container;

    $time_start = microtime(true);
    $set = [
        'value_i' => 0,
        'value_s' => '0',
    ];
    $fluent = $container->get(Database::class);
    $fluent->update('avps')
           ->set($set)
           ->where('arg = ?', 'sitepot')
           ->where('value_u < ?', TIME_NOW)
           ->where('value_s = ?', '1')
           ->execute();
    $cache = $container->get(Cache::class);
    $cache->delete('Sitepot_');
    $time_end = microtime(true);
    $run_time = $time_end - $time_start;
    $text = " Run time: $run_time seconds";
    echo $text . "\n";
    if ($data['clean_log']) {
        write_log('Sitepot Cleanup completed' . $text);
    }
}
