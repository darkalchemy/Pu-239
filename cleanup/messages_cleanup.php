<?php

declare(strict_types = 1);

use DI\DependencyException;
use DI\NotFoundException;
use Pu239\Message;

/**
 * @param $data
 *
 * @throws DependencyException
 * @throws NotFoundException
 * @throws \Envms\FluentPDO\Exception
 */
function pms_cleanup($data)
{
    global $container;

    $time_start = microtime(true);
    $secs = 90 * 86400;
    $dt = TIME_NOW - $secs;
    $messages_class = $container->get(Message::class);
    $messages = $messages_class->delete_old_messages($dt);

    $time_end = microtime(true);
    $run_time = $time_end - $time_start;
    $text = " Run time: $run_time seconds";
    echo $text . "\n";
    if ($data['clean_log'] && !empty($messages)) {
        write_log('PMs Cleanup completed' . $text);
    }
}
