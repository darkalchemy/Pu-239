<?php
/**
 * @param $data
 */
function birthday_update($data)
{
    global $site_config, $queries, $cache;
    set_time_limit(1200);
    ignore_user_abort(true);
    $current_date = getdate();
    $res = sql_query('SELECT id, username, class, donor, title, warned, enabled, chatpost, leechwarn, pirate, king, uploaded, birthday FROM users WHERE MONTH(birthday) = ' . sqlesc($current_date['mon']) . ' AND DAYOFMONTH(birthday) = ' . sqlesc($current_date['mday']) . ' ORDER BY username ASC') or sqlerr(__FILE__, __LINE__);
    $msgs_buffer = $users_buffer = [];
    if (mysqli_num_rows($res) > 0) {
        while ($arr = mysqli_fetch_assoc($res)) {
            $msg = 'Hey there  ' . htmlsafechars($arr['username']) . " happy birthday, hope you have a good day we awarded you 10 gig...Njoi.\n";
            $subject = 'Its your birthday!!';
            $msgs_buffer[] = '(0,' . $arr['id'] . ', ' . TIME_NOW . ', ' . sqlesc($msg) . ', ' . sqlesc($subject) . ')';
            $users_buffer[] = '(' . $arr['id'] . ', 10737418240)';
            $update['uploaded'] = ($arr['uploaded'] + 10737418240);
            $cache->update_row('userstats_' . $arr['id'], [
                'uploaded' => $update['uploaded'],
            ], $site_config['expires']['u_stats']);
            $cache->update_row('user_stats_' . $arr['id'], [
                'uploaded' => $update['uploaded'],
            ], $site_config['expires']['user_stats']);
        }
        $count = count($users_buffer);
        if ($data['clean_log'] && $count > 0) {
            sql_query('INSERT INTO messages (sender,receiver,added,msg,subject) VALUES ' . implode(', ', $msgs_buffer)) or sqlerr(__FILE__, __LINE__);
            sql_query('INSERT INTO users (id, uploaded) VALUES ' . implode(', ', $users_buffer) . ' ON DUPLICATE KEY UPDATE uploaded=uploaded + VALUES(uploaded)') or sqlerr(__FILE__, __LINE__);
        }
        if ($data['clean_log']) {
            write_log("Birthday Cleanup: Pm'd' " . $count . ' member(s) and awarded a birthday prize');
        }
        unset($users_buffer, $msgs_buffer, $count);
    }
}
