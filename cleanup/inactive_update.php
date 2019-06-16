<?php

declare(strict_types = 1);

use DI\DependencyException;
use DI\NotFoundException;
use Pu239\User;

/**
 * @param $data
 *
 * @throws DependencyException
 * @throws NotFoundException
 * @throws \Envms\FluentPDO\Exception
 */
function inactive_update($data)
{
    global $container;

    $time_start = microtime(true);
    $user_class = $container->get(User::class);

    $unconfirmed = TIME_NOW - (2 * 86400); //unconfrimed more 48 hours
    $inactive = TIME_NOW - (180 * 86400); // inactive more than 6 months
    $parked = TIME_NOW - (365 * 86400); // parked more than 1 year
    $users = $user_class->get_inactives($unconfirmed, $inactive, $parked, UC_STAFF);
    if (!empty($users)) {
        $user_class->delete_users($users);
    }

    $time_end = microtime(true);
    $run_time = $time_end - $time_start;
    $text = " Run time: $run_time seconds";
    echo $text . "\n";
    if ($data['clean_log']) {
        write_log('Inactive Cleanup: Completed' . $text);
    }
}
