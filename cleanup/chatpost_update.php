<?php

/**
 * @param $data
 *
 * @throws \Envms\FluentPDO\Exception
 * @throws \MatthiasMullie\Scrapbook\Exception\UnbegunTransaction
 */
function chatpost_update($data)
{
    $time_start = microtime(true);
    global $site_config, $cache, $fluent, $message_stuffs;

    set_time_limit(1200);
    ignore_user_abort(true);
    $dt = TIME_NOW;

    $res = $fluent->from('users')
        ->select(null)
        ->select('id')
        ->select('modcomment')
        ->where('chatpost < ?', $dt)
        ->where('chatpost > 1');

    $subject = 'Chat ban expired.';
    $msg = "Your Chat ban has timed out and has been auto-removed by the system. If you would like to have it again, exchange some Karma Bonus Points again. Cheers!\n";
    $i = 0;
    $msgs_buffer = [];
    foreach ($res as $arr) {
        $modcomment = $arr['modcomment'];
        $modcomment = get_date($dt, 'DATE', 1) . " - Chat ban Automatically Removed By System.\n" . $modcomment;
        $msgs_buffer[] = [
            'sender' => 0,
            'receiver' => $arr['id'],
            'added' => $dt,
            'msg' => $msg,
            'subject' => $subject,
        ];
        $set = [
            'chatpost' => 1,
            'modcomment' => $modcomment,
        ];
        ++$i;
        $fluent->update('users')
            ->set($set)
            ->where('id = ?', $arr['id'])
            ->execute();

        $cache->update_row('user' . $arr['id'], $set, $site_config['expires']['user_cache']);
    }

    $count = count($msgs_buffer);
    if ($count > 0) {
        ++$i;
        $message_stuffs->insert($msgs_buffer);
    }
    $time_end = microtime(true);
    $run_time = $time_end - $time_start;
    $text = " Run time: $run_time seconds";
    echo $text . "\n";
    if ($data['clean_log'] && $i > 0) {
        write_log('Cleanup - Removed Chat ban from ' . $count . ' members');
        write_log("Chat ban Cleanup: Completed using $i queries" . $text);
    }
}
