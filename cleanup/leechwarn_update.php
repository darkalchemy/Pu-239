<?php
/**
 * @param $data
 *
 * @throws \MatthiasMullie\Scrapbook\Exception\UnbegunTransaction
 */
function leechwarn_update($data)
{
    global $site_config, $queries, $cache;
    set_time_limit(1200);
    ignore_user_abort(true);

    $minratio = 0.3;
    $base_ratio = 0.0;
    $downloaded = 10 * 1024 * 1024 * 1024; // + 10 GB
    $length = 3 * 7; // Give 3 weeks to let them sort there shit
    $res = sql_query("SELECT id, modcomment FROM users WHERE enabled='yes' AND class = " . UC_USER . " AND leechwarn = '0' AND uploaded / downloaded < $minratio AND uploaded / downloaded > $base_ratio AND downloaded >= $downloaded AND immunity = '0'") or sqlerr(__FILE__, __LINE__);
    $msgs_buffer = $users_buffer = [];
    $dt = TIME_NOW;
    if (mysqli_num_rows($res) > 0) {
        $subject = 'Auto leech warned';
        $msg = 'You have been warned and your download rights have been removed due to your low ratio. You need to get a ratio of 0.5 within the next 3 weeks or your Account will be disabled.';
        $leechwarn = $dt + ($length * 86400);
        while ($arr = mysqli_fetch_assoc($res)) {
            $modcomment = $arr['modcomment'];
            $modcomment = get_date($dt, 'DATE', 1) . " - Automatically Leech warned and downloads disabled By System.\n" . $modcomment;
            $modcom = sqlesc($modcomment);
            $msgs_buffer[] = '(0,' . $arr['id'] . ', ' . $dt . ', ' . sqlesc($msg) . ', ' . sqlesc($subject) . ')';
            $users_buffer[] = '(' . $arr['id'] . ',' . $leechwarn . ',\'0\', ' . $modcom . ')';
            $update['leechwarn'] = ($leechwarn);
            $cache->update_row('user' . $arr['id'], [
                'leechwarn'   => $update['leechwarn'],
                'downloadpos' => 0,
                'modcomment'  => $modcomment,
            ], $site_config['expires']['user_cache']);
            $cache->increment('inbox_' . $arr['id']);
        }
        $count = count($users_buffer);
        if ($count > 0) {
            sql_query('INSERT INTO messages (sender,receiver,added,msg,subject) VALUES ' . implode(', ', $msgs_buffer)) or sqlerr(__FILE__, __LINE__);
            sql_query('INSERT INTO users (id, leechwarn, downloadpos, modcomment) VALUES ' . implode(', ', $users_buffer) . ' ON DUPLICATE KEY UPDATE leechwarn = VALUES(leechwarn),downloadpos = VALUES(downloadpos),modcomment = VALUES(modcomment)') or sqlerr(__FILE__, __LINE__);
            write_log('Cleanup: System applied auto leech Warning(s) to  ' . $count . ' Member(s)');
        }
        unset($users_buffer, $msgs_buffer, $update, $count);
    }
    $minratio = 0.5; // ratio > 0.5
    $res = sql_query("SELECT id, modcomment FROM users WHERE downloadpos = '0' AND leechwarn > '1' AND uploaded / downloaded >= $minratio") or sqlerr(__FILE__, __LINE__);
    $msgs_buffer = $users_buffer = [];
    if (mysqli_num_rows($res) > 0) {
        $subject = 'Auto leech warning removed';
        $msg = "Your warning for a low ratio has been removed and your downloads enabled. We highly recommend you to keep your ratio positive to avoid being automatically warned again.\n";
        while ($arr = mysqli_fetch_assoc($res)) {
            $modcomment = $arr['modcomment'];
            $modcomment = get_date($dt, 'DATE', 1) . " - Leech warn removed and download enabled By System.\n" . $modcomment;
            $modcom = sqlesc($modcomment);
            $msgs_buffer[] = '(0,' . $arr['id'] . ',' . $dt . ', ' . sqlesc($msg) . ',  ' . sqlesc($subject) . ')';
            $users_buffer[] = '(' . $arr['id'] . ', \'0\', \'1\', ' . $modcom . ')';
            $cache->update_row('user' . $arr['id'], [
                'leechwarn'   => 0,
                'downloadpos' => 1,
                'modcomment'  => $modcomment,
            ], $site_config['expires']['user_cache']);
            $cache->increment('inbox_' . $arr['id']);
        }
        $count = count($users_buffer);
        if ($count > 0) {
            sql_query('INSERT INTO messages (sender,receiver,added,msg,subject) VALUES ' . implode(', ', $msgs_buffer)) or sqlerr(__FILE__, __LINE__);
            sql_query('INSERT INTO users (id, leechwarn, downloadpos, modcomment) VALUES ' . implode(', ', $users_buffer) . ' ON DUPLICATE KEY UPDATE leechwarn = VALUES(leechwarn),downloadpos = VALUES(downloadpos),modcomment = VALUES(modcomment)') or sqlerr(__FILE__, __LINE__);
        }
        if ($data['clean_log']) {
            write_log('Cleanup: System removed auto leech Warning(s) and renabled download(s) - ' . $count . ' Member(s)');
        }
        unset($users_buffer, $msgs_buffer, $count);
    }
    $res = sql_query("SELECT id, modcomment FROM users WHERE leechwarn > '1' AND leechwarn < " . $dt . " AND leechwarn <> '0' ") or sqlerr(__FILE__, __LINE__);
    $users_buffer = [];
    if (mysqli_num_rows($res) > 0) {
        while ($arr = mysqli_fetch_assoc($res)) {
            $modcomment = $arr['modcomment'];
            $modcomment = get_date($dt, 'DATE', 1) . " - User disabled - Low ratio.\n" . $modcomment;
            $modcom = sqlesc($modcomment);
            $users_buffer[] = '(' . $arr['id'] . ' , \'0\', \'no\', ' . $modcom . ')';
            $cache->update_row('user' . $arr['id'], [
                'leechwarn'  => 0,
                'enabled'    => 'no',
                'modcomment' => $modcomment,
            ], $site_config['expires']['user_cache']);
        }
        $count = count($users_buffer);
        if ($count > 0) {
            sql_query('INSERT INTO users (id, leechwarn, enabled, modcomment) VALUES ' . implode(', ', $users_buffer) . ' ON DUPLICATE KEY UPDATE class = VALUES(class),leechwarn = VALUES(leechwarn),enabled = VALUES(enabled),modcomment = VALUES(modcomment)') or sqlerr(__FILE__, __LINE__);
        }
        if ($data['clean_log']) {
            write_log('Cleanup: Disabled ' . $count . ' Member(s) - Leechwarns expired');
        }
        unset($users_buffer, $count);
    }
    if ($data['clean_log'] && $queries > 0) {
        write_log("Leechwarn Cleanup: Completed using $queries queries");
    }
}
