<?php

/**
 * @param $data
 *
 * @throws \MatthiasMullie\Scrapbook\Exception\UnbegunTransaction
 */
function uploadpos_update($data)
{
    global $site_config, $cache, $fluent;

    set_time_limit(1200);
    ignore_user_abort(true);
    $dt = TIME_NOW;

    $res = $fluent->from('users')
        ->select(null)
        ->select('id')
        ->select('modcomment')
        ->where('uploadpos < ?', $dt)
        ->where('uploadpos > 1');

    $subject = 'Upload Ban expired.';
    $msg = "Your Upload Ban has timed out and has been auto-removed by the system. If you would like to have it again, exchange some Karma Bonus Points again. Cheers!\n";
    $i = 0;
    $values = [];
    foreach ($res as $arr) {
        $modcomment = $arr['modcomment'];
        $modcomment = get_date($dt, 'DATE', 1) . " - Upload Ban Automatically Removed By System.\n" . $modcomment;
        $values[] = [
            'sender' => 0,
            'receiver,' => $arr['id'],
            'added,' => $dt,
            'msg,' => $msg,
            'subject' => $subject,
        ];
        $set = [
            'uploadpos' => 1,
            'modcomment' => $modcomment,
        ];
        ++$i;
        $fluent->update('users')
            ->set($set)
            ->where('id = ?', $arr['id'])
            ->execute();

        $cache->update_row('user' . $arr['id'], $set, $site_config['expires']['user_cache']);
        $cache->increment('inbox_' . $arr['id']);
    }

    $count = count($values);
    if ($count > 0) {
        ++$i;
        $fluent->insertInto('messages')
            ->values($values)
            ->execute();
    }
    if ($data['clean_log'] && $i > 0) {
        write_log('Cleanup - Removed Upload Ban from ' . $count . ' members');
        write_log("Upload Ban Cleanup: Completed using $i queries");
    }
}
