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
 * @throws UnbegunTransaction
 * @throws \Delight\Auth\AuthError
 * @throws \Delight\Auth\NotLoggedInException
 * @throws \Envms\FluentPDO\Exception
 * @throws \PHPMailer\PHPMailer\Exception
 * @throws \Spatie\Image\Exceptions\InvalidManipulation
 */
function uploadpos_update($data)
{
    global $container, $site_config;

    $time_start = microtime(true);
    $dt = TIME_NOW;
    $fluent = $container->get(Database::class);
    $res = $fluent->from('users')
                  ->select(null)
                  ->select('id')
                  ->select('modcomment')
                  ->where('uploadpos < ?', $dt)
                  ->where('uploadpos > 1');

    $subject = 'Upload Ban expired.';
    $msg = "Your Upload Ban has timed out and has been auto-removed by the system. If you would like to have it again, exchange some Karma Bonus Points again. Cheers!\n";
    $msgs = [];
    $cache = $container->get(Cache::class);
    $comment = get_date((int) $dt, 'DATE', 1) . " - Upload Ban Automatically Removed By System.\n";
    foreach ($res as $arr) {
        $modcomment = $comment . $arr['modcomment'];
        $msgs[] = [
            'receiver' => $arr['id'],
            'added' => $dt,
            'msg' => $msg,
            'subject' => $subject,
        ];
        $user = $cache->get('user_' . $arr['id']);
        if (!empty($user)) {
            $cache->update_row('user_' . $arr['id'], [
                'uploadpos' => 1,
                'modcomment' => $modcomment,
            ], $site_config['expires']['user_cache']);
        }
    }

    $count = count($msgs);
    if ($count) {
        $messages_class = $container->get(Message::class);
        $messages_class->insert($msgs);
        $set = [
            'uploadpos' => 1,
            'modcomment' => new Literal("CONCAT(\"$comment\", modcomment)"),
        ];

        $fluent->update('users')
               ->set($set)
               ->where('uploadpos < ?', $dt)
               ->where('uploadpos > 1')
               ->execute();
    }

    $time_end = microtime(true);
    $run_time = $time_end - $time_start;
    $text = " Run time: $run_time seconds";
    echo $text . "\n";
    if ($data['clean_log']) {
        write_log('Cleanup - Removed Upload Ban from ' . $count . ' members' . $text);
    }
}
