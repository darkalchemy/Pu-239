<?php

declare(strict_types = 1);

use DI\DependencyException;
use DI\NotFoundException;
use Envms\FluentPDO\Literal;
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
function warned_update($data)
{
    global $container, $site_config;

    $fluent = $container->get(Database::class);

    $time_start = microtime(true);
    $dt = TIME_NOW;

    $res = $fluent->from('users')
                  ->select(null)
                  ->select('id')
                  ->select('modcomment')
                  ->where('warned < ?', $dt)
                  ->where('warned > 1');

    $subject = 'Warning expired.';
    $msg = "Your Warning has timed out and has been auto-removed by the system. Cheers!\n";
    $msgs = [];
    $comment = get_date((int) $dt, 'DATE', 1) . " - Warning Automatically Removed By System.\n";
    foreach ($res as $arr) {
        $modcomment = $comment . $arr['modcomment'];
        $msgs[] = [
            'sender' => 0,
            'receiver' => $arr['id'],
            'added' => $dt,
            'msg' => $msg,
            'subject' => $subject,
        ];

        $cache = $container->get(Cache::class);
        $user = $cache->get('user_' . $arr['id']);
        if (!empty($user)) {
            $cache->update_row('user_' . $arr['id'], [
                'warned' => 0,
                'modcomment' => $modcomment,
            ], $site_config['expires']['user_cache']);
        }
    }

    $count = count($msgs);
    if ($count) {
        $messages_class = $container->get(Message::class);
        $messages_class->insert($msgs);
        $set = [
            'warned' => 0,
            'modcomment' => new Literal("CONCAT(\"$comment\", modcomment)"),
        ];

        $fluent->update('users')
               ->set($set)
               ->where('warned < ?', $dt)
               ->where('warned > 1')
               ->execute();
    }

    $time_end = microtime(true);
    $run_time = $time_end - $time_start;
    $text = " Run time: $run_time seconds";
    echo $text . "\n";
    if ($data['clean_log']) {
        write_log('Cleanup - Removed Warning from ' . $count . ' members' . $text);
    }
}
