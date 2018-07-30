<?php

/**
 * @param $data
 *
 * @throws \MatthiasMullie\Scrapbook\Exception\UnbegunTransaction
 */
function avatarpos_update($data)
{
    global $site_config, $cache, $fluent;

    set_time_limit(1200);
    ignore_user_abort(true);
    $dt = TIME_NOW;

    $res = $fluent->from('users')
        ->select(null)
        ->select('id')
        ->select('modcomment')
        ->where('avatarpos < ?', $dt)
        ->where('avatarpos > 1');

    $subject = 'Avatar ban expired.';
    $msg = "Your Avatar ban has timed out and has been auto-removed by the system. If you would like to have it again, exchange some Karma Bonus Points again. Cheers!\n";
    $i = 0;
    $values = [];
    foreach ($res as $arr) {
        $modcomment = $arr['modcomment'];
        $modcomment = get_date($dt, 'DATE', 1) . " - Avatar ban Automatically Removed By System.\n" . $modcomment;
        $values[] = [
            'sender' => 0,
            'receiver,' => $arr['id'],
            'added,' => $dt,
            'msg,' => $msg,
            'subject' => $subject,
        ];
        $set = [
            'avatarpos' => 0,
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
        write_log('Cleanup - Removed Avatar ban from ' . $count . ' members');
        write_log("Avatar ban Cleanup: Completed using $i queries");
    }
}
