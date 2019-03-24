<?php

/**
 * @param $data
 *
 * @throws \Envms\FluentPDO\Exception
 * @throws \MatthiasMullie\Scrapbook\Exception\UnbegunTransaction
 */
function funds_update($data)
{
    $time_start = microtime(true);
    dbconn();
    global $site_config, $queries, $cache, $message_stuffs;

    set_time_limit(1200);
    ignore_user_abort(true);

    $secs = 30 * 86400;
    $dt = sqlesc(TIME_NOW - $secs);
    sql_query("DELETE FROM funds WHERE added < $dt") or sqlerr(__FILE__, __LINE__);
    $cache->delete('totalfunds_');
    $dt = TIME_NOW;
    $res = sql_query("SELECT id, modcomment, vipclass_before FROM users WHERE donor = 'yes' AND donoruntil < " . $dt . " AND donoruntil <> '0'") or sqlerr(__FILE__, __LINE__);
    $msgs_buffer = $users_buffer = [];
    if (mysqli_num_rows($res) > 0) {
        $subject = 'Donor status removed by system.';
        $msg = "Your Donor status has timed out and has been auto-removed by the system, and your Vip status has been removed. We would like to thank you once again for your support to {$site_config['site_name']}. If you wish to re-new your donation, Visit the site donate link. Cheers!\n";
        while ($arr = mysqli_fetch_assoc($res)) {
            $modcomment = $arr['modcomment'];
            $modcomment = get_date($dt, 'DATE', 1) . " - Donation status Automatically Removed By System.\n" . $modcomment;
            $modcom = sqlesc($modcomment);
            $msgs_buffer[] = [
                'sender' => 0,
                'receiver' => $arr['id'],
                'added' => $dt,
                'msg' => $msg,
                'subject' => $subject,
            ];
            $users_buffer[] = '(' . $arr['id'] . ',' . $arr['vipclass_before'] . ',\'no\',\'0\', ' . $modcom . ')';
            $update['class'] = ($arr['vipclass_before']);
            $cache->update_row('user_' . $arr['id'], [
                'class' => $update['class'],
                'donor' => 'no',
                'donoruntil' => 0,
                'modcomment' => $modcomment,
            ], $site_config['expires']['user_cache']);
        }
        $count = count($users_buffer);
        if ($data['clean_log'] && $count > 0) {
            $message_stuffs->insert($msgs_buffer);
            sql_query('INSERT INTO users (id, class, donor, donoruntil, modcomment) VALUES ' . implode(', ', $users_buffer) . ' ON DUPLICATE KEY UPDATE class = VALUES(class),
            donor = VALUES(donor),donoruntil = VALUES(donoruntil),modcomment = VALUES(modcomment)') or sqlerr(__FILE__, __LINE__);
        }
        if ($data['clean_log']) {
            write_log('Cleanup: Donation status expired - ' . $count . ' Member(s)');
        }
        unset($users_buffer, $msgs_buffer, $update, $count);
    }
    $time_end = microtime(true);
    $run_time = $time_end - $time_start;
    $text = " Run time: $run_time seconds";
    echo $text . "\n";
    if ($data['clean_log'] && $queries > 0) {
        write_log("Delete Old Funds Cleanup: Completed using $queries queries" . $text);
    }
}
