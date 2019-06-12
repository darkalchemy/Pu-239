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
function sendpmpos_update($data)
{
    global $container, $site_config;

    $time_start = microtime(true);
    $dt = TIME_NOW;
    $fluent = $container->get(Database::class);
    $users = $fluent->from('users')
                    ->select(null)
                    ->select('id')
                    ->select('modcomment')
                    ->where('sendpmpos > 1')
                    ->where('sendpmpos < ?', $dt)
                    ->fetchAll();

    $msgs_buffer = $users_buffer = [];
    $count = count($users);
    if ($count > 0) {
        $comment = get_date((int) $dt, 'DATE', 1) . " - PM ban Removed By System.\n";
        $subject = 'PM ban expired.';
        $msg = "Your PM ban has expired and has been auto-removed by the system.\n";
        $cache = $container->get(Cache::class);
        foreach ($users as $arr) {
            $modcomment = $comment . $arr['modcomment'];
            $msgs_buffer[] = [
                'sender' => 0,
                'receiver' => $arr['id'],
                'added' => $dt,
                'msg' => $msg,
                'subject' => $subject,
            ];
            $user = $cache->get('user_' . $arr['id']);
            if (!empty($user)) {
                $cache->update_row('user_' . $arr['id'], [
                    'sendpmpos' => 1,
                    'modcomment' => $modcomment,
                ], $site_config['expires']['user_cache']);
            }
        }
        $count = count($users_buffer);
        if ($count > 0) {
            $messages_class = $container->get(Message::class);
            $messages_class->insert($msgs_buffer);
            $set = [
                'sendpmpos' => 1,
                'modcomment' => new Literal("CONCAT(\"$comment\", modcomment)"),
            ];
            $fluent->update('users')
                   ->set($set)
                   ->where('sendpmpos > 1')
                   ->where('sendpmpos < ?', $dt)
                   ->execute();
        }
        $time_end = microtime(true);
        $run_time = $time_end - $time_start;
        $text = " Run time: $run_time seconds";
        echo $text . "\n";
        if ($data['clean_log']) {
            write_log('Cleanup - Removed PM ban from ' . $count . ' members' . $text);
        }
    }
}
