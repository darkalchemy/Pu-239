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
function happyhour_update($data)
{
    global $site_config;

    $time_start = microtime(true);
    require_once INCL_DIR . 'function_happyhour.php';
    $f = $site_config['paths']['happyhour'];
    $happy = unserialize(file_get_contents($f));
    $happyHour = strtotime($happy['time']);
    $curDate = TIME_NOW;
    $happyEnd = $happyHour + 3600;
    if ($happy['status'] == 0 && $site_config['bonus']['happy_hour']) {
        if ($data['clean_log']) {
            write_log('Happy hour was @ ' . get_date((int) $happyHour, 'LONG', 1, 0) . ' and Catid ' . $happy['catid']['id'] . ' ');
        }
        happyFile('set');
    } elseif (($curDate > $happyEnd) && $happy['status'] == 1) {
        happyFile('reset');
    }
    $time_end = microtime(true);
    $run_time = $time_end - $time_start;
    $text = " Run time: $run_time seconds";
    echo $text . "\n";
    if ($data['clean_log']) {
        write_log('Happyhour Cleanup: Completed' . $text);
    }
}
