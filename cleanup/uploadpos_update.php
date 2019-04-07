<?php

use MatthiasMullie\Scrapbook\Exception\UnbegunTransaction;

/**
 * @param $data
 *
 * @throws \Envms\FluentPDO\Exception
 * @throws UnbegunTransaction
 */
function uploadpos_update($data)
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
                  ->where('uploadpos < ?', $dt)
                  ->where('uploadpos>1');

    $subject = 'Upload Ban expired.';
    $msg = "Your Upload Ban has timed out and has been auto-removed by the system. If you would like to have it again, exchange some Karma Bonus Points again. Cheers!\n";
    $msgs = [];
    foreach ($res as $arr) {
        $comment = get_date($dt, 'DATE', 1) . " - Upload Ban Automatically Removed By System.\n";
        $modcomment = $comment . $arr['modcomment'];
        $msgs[] = [
            'sender' => 0,
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
        $message_stuffs->insert($msgs);
        $set = [
            'uploadpos' => 1,
            'modcomment' => new Envms\FluentPDO\Literal("CONCAT(\"$comment\", modcomment)"),
        ];

        $fluent->update('users')
               ->set($set)
               ->where('uploadpos < ?', $dt)
               ->where('uploadpos>1')
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
