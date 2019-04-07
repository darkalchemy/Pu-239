<?php

use MatthiasMullie\Scrapbook\Exception\UnbegunTransaction;

/**
 * @param $data
 *
 * @throws \Envms\FluentPDO\Exception
 * @throws UnbegunTransaction
 */
function leechwarn_update($data)
{
    $time_start = microtime(true);
    global $site_config, $cache, $fluent, $message_stuffs;

    set_time_limit(1200);
    ignore_user_abort(true);
    $dt = TIME_NOW;

    $minratio = 0.3;
    $base_ratio = 0.0;
    $downloaded = 10 * 1024 * 1024 * 1024;

    $res = $fluent->from('users')
                  ->select(null)
                  ->select('id')
                  ->select('modcomment')
                  ->where('enabled = ?', 'yes')
                  ->where('class = ?', UC_MIN)
                  ->where('leechwarn = 0')
                  ->where('uploaded / downloaded < ?', $minratio)
                  ->where('uploaded / downloaded>? ', $base_ratio)
                  ->where('downloaded>= ?', $downloaded)
                  ->where('immunity = 0');

    $length = 3 * 7;
    $leechwarn = $dt + ($length * 86400);
    $subject = 'Auto leech warned';
    $msg = 'You have been warned and your download rights have been removed due to your low ratio. You need to get a ratio of 0.5 within the next 3 weeks or your Account will be disabled.';
    $i = 0;
    $values = [];
    foreach ($res as $arr) {
        $modcomment = $arr['modcomment'];
        $modcomment = get_date($dt, 'DATE', 1) . " - Automatically Leech warned and downloads disabled By System.\n" . $modcomment;
        $values[] = [
            'sender' => 0,
            'receiver' => $arr['id'],
            'added' => $dt,
            'msg' => $msg,
            'subject' => $subject,
        ];
        $set = [
            'leechwarn' => $leechwarn,
            'downloadpos' => 0,
            'modcomment' => $modcomment,
        ];
        ++$i;
        $fluent->update('users')
               ->set($set)
               ->where('id=?', $arr['id'])
               ->execute();

        $cache->update_row('user_' . $arr['id'], $set, $site_config['expires']['user_cache']);
    }

    $count = count($values);
    if ($count) {
        ++$i;
        $message_stuffs->insert($values);
    }

    $minratio = 0.5;
    $res = $fluent->from('users')
                  ->select(null)
                  ->select('id')
                  ->select('modcomment')
                  ->where('leechwarn>1')
                  ->where('downloadpos = 0')
                  ->where('uploaded / downloaded>= ? ', $minratio);

    $subject = 'Auto leech warning removed';
    $msg = "Your warning for a low ratio has been removed and your downloads enabled. We highly recommend you to keep your ratio positive to avoid being automatically warned again.\n";
    foreach ($res as $arr) {
        $modcomment = $arr['modcomment'];
        $modcomment = get_date($dt, 'DATE', 1) . " - Leech warn removed and download enabled By System.\n" . $modcomment;
        $values[] = [
            'sender' => 0,
            'receiver' => $arr['id'],
            'added' => $dt,
            'msg' => $msg,
            'subject' => $subject,
        ];
        $set = [
            'leechwarn' => 0,
            'downloadpos' => 1,
            'modcomment' => $modcomment,
        ];
        ++$i;
        $fluent->update('users')
               ->set($set)
               ->where('id=?', $arr['id'])
               ->execute();

        $cache->update_row('user_' . $arr['id'], $set, $site_config['expires']['user_cache']);
    }
    if (!empty($values)) {
        $message_stuffs->insert($values);
    }
    $res = $fluent->from('users')
                  ->select(null)
                  ->select('id')
                  ->select('modcomment')
                  ->where('leechwarn>1')
                  ->where('leechwarn != 0')
                  ->where('leechwarn < ?', $dt);

    foreach ($res as $arr) {
        $modcomment = $arr['modcomment'];
        $modcomment = get_date($dt, 'DATE', 1) . " - User disabled - Low ratio.\n" . $modcomment;
        $set = [
            'leechwarn' => 0,
            'enabled' => 'no',
            'modcomment' => $modcomment,
        ];
        ++$i;
        $fluent->update('users')
               ->set($set)
               ->where('id=?', $arr['id'])
               ->execute();

        $cache->delete('user_' . $arr['id']);
        $cache->set('forced_logout_' . $arr['id'], TIME_NOW, 2591999);
    }

    $time_end = microtime(true);
    $run_time = $time_end - $time_start;
    $text = " Run time: $run_time seconds";
    echo $text . "\n";
    if ($data['clean_log'] && $i > 0) {
        write_log('Cleanup - Removed Pirate status from ' . $count . ' members');
        write_log("Pirate Status Cleanup: Completed using $i queries" . $text);
    }
}
