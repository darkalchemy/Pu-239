<?php

declare(strict_types = 1);

use DI\DependencyException;
use DI\NotFoundException;
use Pu239\IP;

/**
 * @param $data
 *
 * @throws DependencyException
 * @throws NotFoundException
 * @throws \Envms\FluentPDO\Exception
 */
function ip_update($data)
{
    $time_start = microtime(true);
    $dt = TIME_NOW - 365 * 86400;
    global $container;

    $ips = $container->get(IP::class);
    $ips->delete_by_age($dt);
    $time_end = microtime(true);
    $run_time = $time_end - $time_start;
    $text = " Run time: $run_time seconds";
    echo $text . "\n";
    if ($data['clean_log']) {
        write_log('IP Cleanup: Completed' . $text);
    }
}
