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
 * @throws DependencyException
 * @throws NotFoundException
 * @throws UnbegunTransaction
 * @throws \Delight\Auth\AuthError
 * @throws \Delight\Auth\NotLoggedInException
 * @throws \Envms\FluentPDO\Exception
 * @throws \PHPMailer\PHPMailer\Exception
 * @throws \Spatie\Image\Exceptions\InvalidManipulation
 */
function immunity_update($data)
{
    global $container, $site_config;

    $time_start = microtime(true);
    $dt = TIME_NOW;
    $fluent = $container->get(Database::class);
    $res = $fluent->from('users')
                  ->select(null)
                  ->select('id')
                  ->select('modcomment')
                  ->where('immunity < ?', $dt)
                  ->where('immunity > 1');

    $subject = 'Immunity status expired.';
    $msg = "Your Immunity status has timed out and has been auto-removed by the system. If you would like to have it again, exchange some Karma Bonus Points again. Cheers!\n";

    $values = [];
    $cache = $container->get(Cache::class);
    foreach ($res as $arr) {
        $modcomment = $arr['modcomment'];
        $modcomment = get_date((int) $dt, 'DATE', 1) . " - Immunity Status Automatically Removed By System.\n" . $modcomment;
        $values[] = [
            'receiver' => $arr['id'],
            'added' => $dt,
            'msg' => $msg,
            'subject' => $subject,
        ];
        $set = [
            'immunity' => 0,
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
        write_log('Cleanup - Removed Immunity status from ' . $count . ' members');
        write_log('Immunity Status Cleanup: Completed' . $text);
    }
}
