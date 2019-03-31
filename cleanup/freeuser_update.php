<?php

/**
 * @param $data
 *
 * @throws Exception
 * @throws \MatthiasMullie\Scrapbook\Exception\UnbegunTransaction
 */
function freeuser_update($data)
{
    $time_start = microtime(true);
    global $site_config, $queries, $fluent, $message_stuffs, $user_stuffs;

    set_time_limit(1200);
    ignore_user_abort(true);

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
    foreach ($query as $arr) {
        $modcomment = $arr['modcomment'];
        $modcomment = get_date($dt, 'DATE', 1) . " - Freeleech Removed By System.\n" . $modcomment;
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
        $message_stuffs->insert($values);
    }

    if ($data['clean_log']) {
        write_log('Cleanup - Removed Freeleech from ' . $count . ' members');
    }

    $time_end = microtime(true);
    $run_time = $time_end - $time_start;
    $text = " Run time: $run_time seconds";
    echo $text . "\n";
    if ($data['clean_log'] && $queries > 0) {
        write_log("Freeleech Cleanup: Completed using $queries queries" . $text);
    }
}
