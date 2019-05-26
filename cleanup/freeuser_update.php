<?php

declare(strict_types = 1);

use MatthiasMullie\Scrapbook\Exception\UnbegunTransaction;
use Pu239\Database;
use Pu239\Message;
use Pu239\User;

/**
 * @param $data
 *
 * @throws Exception
 * @throws UnbegunTransaction
 */
function freeuser_update($data)
{
    global $container;

    $time_start = microtime(true);
    $fluent = $container->get(Database::class);
    $query = $fluent->from('users')
                    ->select(null)
                    ->select('id')
                    ->select('modcomment')
                    ->where('free_switch > 1')
                    ->where('free_switch < ?', TIME_NOW);

    $dt = TIME_NOW;
    $subject = 'Freeleech expired.';
    $msg = "Your freeleech has expired and has been auto-removed by the system. If you would like to have it again, exchange some Karma Bonus Points.\n";
    $values = $set = $update = [];
    $user_stuffs = $container->get(User::class);
    foreach ($query as $arr) {
        $modcomment = $arr['modcomment'];
        $modcomment = get_date((int) $dt, 'DATE', 1) . " - Freeleech Removed By System.\n" . $modcomment;
        $values[] = [
            'sender' => 0,
            'receiver' => $arr['id'],
            'added' => $dt,
            'msg' => $msg,
            'subject' => $subject,
        ];
        $set = [
            'free_switch' => 0,
            'modcomment' => $modcomment,
        ];
        $user_stuffs->update($set, $arr['id']);
    }

    $count = count($values);
    if ($count) {
        $message_stuffs = $container->get(Message::class);
        $message_stuffs->insert($values);
    }

    if ($data['clean_log']) {
        write_log('Cleanup - Removed Freeleech from ' . $count . ' members');
    }

    $time_end = microtime(true);
    $run_time = $time_end - $time_start;
    $text = " Run time: $run_time seconds";
    echo $text . "\n";
    if ($data['clean_log']) {
        write_log('Freeleech Cleanup: Completed' . $text);
    }
}
