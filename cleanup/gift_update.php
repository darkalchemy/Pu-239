<?php

declare(strict_types = 1);

use DI\DependencyException;
use DI\NotFoundException;
use MatthiasMullie\Scrapbook\Exception\UnbegunTransaction;
use Pu239\Database;
use Pu239\User;

/**
 * @param $data
 *
 * @throws UnbegunTransaction
 * @throws DependencyException
 * @throws NotFoundException
 * @throws \Envms\FluentPDO\Exception
 * @throws Exception
 */
function gift_update($data)
{
    global $container;

    $time_start = microtime(true);
    if (Christmas()) {
        die();
    }
    $fluent = $container->get(Database::class);
    $query = $fluent->from('users')
                    ->select(null)
                    ->select('id')
                    ->where('gotgift = "yes"')
                    ->fetchAll();

    $set = [
        'gotgift' => 'no',
    ];
    if (!empty($query)) {
        $count = count($query);
        $users_class = $container->get(User::class);
        foreach ($query as $userid) {
            $users_class->update($set, $userid['id']);
        }
        $time_end = microtime(true);
        $run_time = $time_end - $time_start;
        $text = " Run time: $run_time seconds";
        echo $text . "\n";
        if ($data['clean_log']) {
            write_log("Christmas Gift Cleanup: Completed, reset $count users' gift status" . $text);
        }
    }
}
