<?php

declare(strict_types = 1);

use DI\DependencyException;
use DI\NotFoundException;
use MatthiasMullie\Scrapbook\Exception\UnbegunTransaction;
use Pu239\Cache;
use Pu239\Database;
use Pu239\Message;

/**
 * @param $data
 *
 * @throws UnbegunTransaction
 * @throws DependencyException
 * @throws NotFoundException
 * @throws \Envms\FluentPDO\Exception
 */
function anonymous_update($data)
{
    global $container, $site_config;

    $time_start = microtime(true);
    $dt = TIME_NOW;
    $fluent = $container->get(Database::class);
    $res = $fluent->from('users')
                  ->select(null)
                  ->select('id')
                  ->select('modcomment')
                  ->where('anonymous_until < ?', $dt)
                  ->where('anonymous_until != 0');

    $subject = 'Anonymous status expired.';
    $msg = "Your Anonymous status has timed out and has been auto-removed by the system. If you would like to have it again, exchange some Karma Bonus Points again. Cheers!\n";
    $values = [];
    $cache = $container->get(Cache::class);
    foreach ($res as $arr) {
        $modcomment = $arr['modcomment'];
        $modcomment = get_date((int) $dt, 'DATE', 1) . " - Anonymous Status Automatically Removed By System.\n" . $modcomment;
        $values[] = [
            'receiver' => $arr['id'],
            'added' => $dt,
            'msg' => $msg,
            'subject' => $subject,
        ];
        $set = [
            'anonymous_until' => 0,
            'modcomment' => $modcomment,
        ];
        $fluent->update('users')
               ->set($set)
               ->where('id = ?', $arr['id'])
               ->execute();

        $cache->update_row('user_' . $arr['id'], $set, $site_config['expires']['user_cache']);
    }

    $count = count($values);
    if ($count > 0) {
        $messages_class = $container->get(Message::class);
        $messages_class->insert($values);
    }
    $time_end = microtime(true);
    $run_time = $time_end - $time_start;
    $text = " Run time: $run_time seconds";
    echo $text . "\n";
    if ($data['clean_log']) {
        write_log('Cleanup - Removed Anonymous status from ' . $count . ' members');
        write_log('Anonymous Status Cleanup: Completed' . $text);
    }
}
