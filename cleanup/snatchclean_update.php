<?php

declare(strict_types = 1);

use DI\DependencyException;
use DI\NotFoundException;
use Pu239\Snatched;

/**
 * @param $data
 *
 * @throws DependencyException
 * @throws NotFoundException
 * @throws \Envms\FluentPDO\Exception
 */
function snatchclean_update($data)
{
    global $container;

    $time_start = microtime(true);
    $days = 90;
    $dt = TIME_NOW - ($days * 86400);
    $snatched_class = $container->get(Snatched::class);
    $snatched_class->delete_stale($dt);
    $snatched_class->update_seeder();
    $time_end = microtime(true);
    $run_time = $time_end - $time_start;
    $text = " Run time: $run_time seconds";
    echo $text . "\n";

    if ($data['clean_log']) {
        write_log("Snatch List Cleanup: Removed snatches not active for $days days." . $text);
    }
}
