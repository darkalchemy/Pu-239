<?php

/**
 * @param $data
 *
 * @throws \Envms\FluentPDO\Exception
 * @throws \MatthiasMullie\Scrapbook\Exception\UnbegunTransaction
 */
function warned_update($data)
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
        ->where('warned < ?', $dt)
        ->where('warned > 1');

    $subject = 'Warning expired.';
    $msg = "Your Warning has timed out and has been auto-removed by the system. Cheers!\n";
    $msgs = [];
    foreach ($res as $arr) {
        $comment = get_date($dt, 'DATE', 1) . " - Warning Automatically Removed By System.\n";
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
                'warned' => 0,
                'modcomment' => $modcomment,
            ], $site_config['expires']['user_cache']);
        }
    }

    $count = count($msgs);
    if ($count) {
        $message_stuffs->insert($msgs);
        $set = [
            'warned' => 0,
            'modcomment' => new Envms\FluentPDO\Literal("CONCAT(\"$comment\", modcomment)"),
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
