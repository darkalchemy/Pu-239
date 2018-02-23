<?php

/**
 * @param $data
 *
 * @throws Exception
 * @throws \MatthiasMullie\Scrapbook\Exception\UnbegunTransaction
 */
function freeuser_update($data)
{
    global $site_config, $queries, $fluent, $cache;

    set_time_limit(1200);
    ignore_user_abort(true);

    $query = $fluent->from('users')
        ->select(null)
        ->select('id')
        ->select('modcomment')
        ->where('free_switch > 1')
        ->where('free_switch < ?', TIME_NOW);

    $values = $msgs_buffer = $set = [];
    $dt = TIME_NOW;
    $subject = 'Freeleech expired.';
    $msg = "Your freeleech has expired and has been auto-removed by the system.\n";
    foreach ($query as $arr) {
        $modcomment = $arr['modcomment'];
        $modcomment = get_date($dt, 'DATE', 1)." - Freeleech Removed By System.\n".$modcomment;
        $modcom = sqlesc($modcomment);
        $values[] = [
            'sender' => 0,
            'receiver' => $arr['id'],
            'added' => $dt,
            'msg' => $msg,
            'subject' => $subject,
        ];
        $cache->increment('inbox_'.$arr['id']);
        $set = [
            'free_switch' => 0,
            'modcomment' => $modcom,
        ];

        $fluent->update('users')
            ->set($set)
            ->where('id = ?', $arr['id'])
            ->execute();

        $cache->update_row('user'.$arr['id'], [
            'free_switch' => 0,
            'modcomment' => $modcomment,
        ], $site_config['expires']['user_cache']);
        $cache->increment('inbox_'.$arr['id']);
    }
    $count = count($values);
    if ($count > 0) {
        $fluent->insertInto('messages')
            ->values($values)
            ->execute();
    }
    if ($data['clean_log']) {
        write_log('Cleanup - Removed Freeleech from '.$count.' members');
    }

    if ($data['clean_log'] && $queries > 0) {
        write_log("Freelech Cleanup: Completed using $queries queries");
    }
}
