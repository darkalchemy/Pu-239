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
 * @throws DependencyException
 * @throws NotFoundException
 * @throws \Envms\FluentPDO\Exception
 * @throws UnbegunTransaction
 */
function birthday_update($data)
{
    global $container, $site_config;

    $time_start = microtime(true);
    require_once INCL_DIR . 'function_users.php';
    $dt = TIME_NOW;
    $date = getdate();
    $fluent = $container->get(Database::class);
    $users = $fluent->from('users')
                    ->select(null)
                    ->select('id')
                    ->select('class')
                    ->select('username')
                    ->select('uploaded')
                    ->where('MONTH(birthday) = ?', $date['mon'])
                    ->where('DAYOFMONTH(birthday) = ?', $date['mday']);

    $count = 0;
    $msgs = [];
    if (!empty($users)) {
        $subject = "It's your birthday!!";
        $cache = $container->get(Cache::class);
        foreach ($users as $arr) {
            $msg = 'Hey there <span class="' . get_user_class_name((int) $arr['class'], true) . '">' . htmlsafechars($arr['username']) . "</span> happy birthday, hope you have a good day. We awarded you 10 gig...Njoi.\n";
            $msgs[] = [
                'sender' => 0,
                'poster' => 0,
                'receiver' => $arr['id'],
                'added' => $dt,
                'msg' => $msg,
                'subject' => $subject,
            ];
            if (!empty($user)) {
                $cache->update_row('user_' . $arr['id'], [
                    'uploaded' => $arr['uploaded'] + 10737418240,
                ], $site_config['expires']['user_cache']);
            }
        }
        $count = count($msgs);
        if ($count > 0) {
            $messages_class = $container->get(Message::class);
            if ($count > 100) {
                foreach (array_chunk($msgs, 150) as $t) {
                    echo 'Inserting ' . count($t) . " messages\n";
                    $messages_class->insert($t);
                }
            } else {
                $messages_class->insert($msgs);
            }

            $set = [
                'uploaded' => new Literal('uploaded + 10737418240'),
            ];
            $fluent->update('users')
                   ->set($set)
                   ->where('MONTH(birthday) = ?', $date['mon'])
                   ->where('DAYOFMONTH(birthday) = ?', $date['mday'])
                   ->execute();
        }
    }
    $time_end = microtime(true);
    $run_time = $time_end - $time_start;
    $text = " Run time: $run_time seconds";
    echo $text . "\n";
    if ($data['clean_log']) {
        write_log("Birthday Cleanup: Pm'd' " . $count . ' member(s) and awarded a birthday prize' . $text);
    }
}
