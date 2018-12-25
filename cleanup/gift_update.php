<?php

/**
 * @param $data
 *
 * @throws \MatthiasMullie\Scrapbook\Exception\UnbegunTransaction
 */
function gift_update($data)
{
    $time_start = microtime(true);
    global $cache, $user_stuffs, $fluent;

    set_time_limit(1200);
    ignore_user_abort(true);

//    if (Christmas()) {
//        die();
//    }
    $query = $fluent->from('users')
        ->select(null)
        ->select('id')
        ->where('gotgift = "yes"')
        ->fetchAll();

    $set = [
        'gotgift' => 'no',
    ];
    if (!empty($query)) {
        $count = count($query);
        foreach ($query as $userid) {
            $user_stuffs->update($set, $userid['id']);
        }
        $time_end = microtime(true);
        $run_time = $time_end - $time_start;
        $text = " Run time: $run_time seconds";
        echo $text . "\n";
        if ($data['clean_log']) {
            write_log("Christmas Gift Cleanup: Completed, reset $count users' gift status" . $text);
        }
    }
}
