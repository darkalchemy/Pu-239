<?php

/**
 * @param $data
 *
 * @throws Exception
 * @throws \MatthiasMullie\Scrapbook\Exception\UnbegunTransaction
 */
function birthday_update($data)
{
    global $site_config, $queries, $fluent;

$cache = new DarkAlchemy\Pu239\Cache();

    set_time_limit(1200);
    ignore_user_abort(true);

    $current_date = getdate();
    $dt = TIME_NOW;

    $query = $fluent->from('users')
        ->select(null)
        ->select('id')
        ->select('class')
        ->select('username')
        ->select('uploaded')
        ->where('MONTH(birthday) = ?', $current_date['mon'])
        ->where('DAYOFMONTH(birthday) = ?', $current_date['mday']);

    $msgs_buffer = $users_buffer = $values = [];
    foreach ($query as $arr) {
        $msg = 'Hey there <span class="' . get_user_class_name($arr['class'], true) . '">'   . htmlsafechars($arr['username']) . "</span> happy birthday, hope you have a good day. We awarded you 10 gig...Njoi.\n";
        $subject = 'Its your birthday!!';
        $msgs_buffer[] = '(0,' . $arr['id'] . ', ' . TIME_NOW . ', ' . sqlesc($msg) . ', ' . sqlesc($subject) . ')';
        $update['uploaded'] = ($arr['uploaded'] + 10737418240);
        $cache->update_row('user' . $arr['id'], [
            'uploaded' => $update['uploaded'],
        ], $site_config['expires']['user_cache']);

        $set = [
            'uploaded' => $update['uploaded'],
        ];

        $fluent->update('users')
            ->set($set)
            ->where('id = ?', $arr['id'])
            ->execute();

        $values[] = [
            'sender'   => 0,
            'receiver' => $arr['id'],
            'added'    => $dt,
            'msg'      => $msg,
            'subject'  => $subject,
        ];
        $cache->increment('inbox_' . $arr['id']);
    }
    $count = count($values);
    if ($data['clean_log'] && $count > 0) {
        if ($count > 0) {
            $fluent->insertInto('messages')
                ->values($values)
                ->execute();
        }
    }
    if ($data['clean_log'] && $queries > 0) {
        write_log("Birthday Cleanup: Pm'd' " . $count . ' member(s) and awarded a birthday prize');
    }
    unset($users_buffer, $msgs_buffer, $count);
}
