<?php

declare(strict_types = 1);

use DI\DependencyException;
use DI\NotFoundException;
use Pu239\Database;

/**
 * @param $data
 *
 * @throws DependencyException
 * @throws NotFoundException
 * @throws \Envms\FluentPDO\Exception
 */
function user_update($data)
{
    global $container;

    $time_start = microtime(true);
    $dt = TIME_NOW;
    $fluent = $container->get(Database::class);
    $fluent->update('freeslots')
           ->set(['addedup' => 0])
           ->where('addedup != 0')
           ->where('addedup < ?', $dt)
           ->execute();

    $fluent->update('freeslots')
           ->set(['addedfree' => 0])
           ->where('addedfree != 0')
           ->where('addedfree < ?', $dt)
           ->execute();

    $fluent->deleteFrom('freeslots')
           ->where('addedup = 0')
           ->where('addedfree = 0')
           ->execute();

    $fluent->update('torrents')
           ->set(['free' => 0])
           ->where('free > 1')
           ->where('free < ?', $dt)
           ->execute();

    $fluent->update('users')
           ->set(['downloadpos' => 1])
           ->where('downloadpos > 1')
           ->where('downloadpos < ?', $dt)
           ->execute();

    $fluent->update('users')
           ->set(['uploadpos' => 1])
           ->where('uploadpos > 1')
           ->where('uploadpos < ?', $dt)
           ->execute();

    $fluent->update('users')
           ->set(['chatpost' => 1])
           ->where('chatpost > 1')
           ->where('chatpost < ?', $dt)
           ->execute();

    $fluent->update('users')
           ->set(['avatarpos' => 1])
           ->where('avatarpos > 1')
           ->where('avatarpos < ?', $dt)
           ->execute();

    $fluent->update('users')
           ->set(['immunity' => 0])
           ->where('immunity > 1')
           ->where('immunity < ?', $dt)
           ->execute();

    $fluent->update('users')
           ->set(['warned' => 0])
           ->where('warned > 1')
           ->where('warned < ?', $dt)
           ->execute();

    $fluent->update('users')
           ->set(['pirate' => 0])
           ->where('pirate > 1')
           ->where('pirate < ?', $dt)
           ->execute();

    $fluent->update('users')
           ->set(['king' => 0])
           ->where('king > 1')
           ->where('king < ?', $dt)
           ->execute();

    $time_end = microtime(true);
    $run_time = $time_end - $time_start;
    $text = " Run time: $run_time seconds";
    echo $text . "\n";
    if ($data['clean_log']) {
        write_log('User Cleanup completed' . $text);
    }
}
