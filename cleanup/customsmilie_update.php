<?php

/**
 * @param $data
 *
 * @throws \MatthiasMullie\Scrapbook\Exception\UnbegunTransaction
 */
function customsmilie_update($data)
{
    global $site_config, $cache, $fluent;

    set_time_limit(1200);
    ignore_user_abort(true);

    $dt = TIME_NOW;
    $res = $fluent->from('users')
        ->select(null)
        ->select('id')
        ->select('name')
        ->select('modcomment')
        ->where('smile_until < ?', $dt)
        ->where('smile_until != 0');

    $subject = 'Custom smilies expired.';
    $msg = "Your Custom smilies have timed out and has been auto-removed by the system. If you would like to have them again, exchange some Karma Bonus Points again. Cheers!\n";
    $i = 0;
    foreach ($res as $arr) {
        $modcomment = $arr['modcomment'];
        $modcomment = get_date($dt, 'DATE', 1) . " - Custom smilies Automatically Removed By System.\n" . $modcomment;
        $values[] = [
            'sender' => 0,
            'receiver,' => $arr['id'],
            'added,' => $dt,
            'msg,' => $msg,
            'subject' => $subject,
        ];
        $set = [
            'smile_until' => 0,
            'modcomment' => $modcom,
        ];
        $i++;
        $fluent->update('users')
            ->set($set)
            ->where('id = ?', $arr['id'])
            ->execute();

        $cache->update_row('user' . $arr['id'], [
            'smile_until' => 0,
            'modcomment'  => $modcomment,
        ], $site_config['expires']['user_cache']);
        $cache->increment('inbox_' . $arr['id']);
    }

    $count = count($values);
    if ($count > 0) {
        $i++;
        $fluent->insertInto('messages')
            ->values($values)
            ->execute();
    }
    if ($data['clean_log'] && $i > 0) {
        write_log('Cleanup - Removed Custom smilies from ' . $count . ' members');
        write_log("Custom Smilie Cleanup: Completed using $i queries");
    }
}
