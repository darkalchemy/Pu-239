<?php

/**
 * @param $data
 */
function expired_signup_update($data)
{
    $time_start = microtime(true);
    dbconn();
    global $site_config, $queries, $cache;

    set_time_limit(1200);
    ignore_user_abort(true);

    $dt = TIME_NOW;
    $deadtime = $dt - $site_config['signup_timeout'];
    $res = sql_query("SELECT id, username, added, downloaded, uploaded, last_access, class, donor, warned, enabled, status FROM users WHERE status = 'pending' AND added < $deadtime AND last_login < $deadtime AND last_access < $deadtime ORDER BY username DESC") or sqlerr(__FILE__, __LINE__);
    if (mysqli_num_rows($res) != 0) {
        while ($arr = mysqli_fetch_assoc($res)) {
            $userid = $arr['id'];
            $res_del = sql_query('DELETE FROM users WHERE id = ' . sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
            $cache->delete('user' . $userid);
            if ($data['clean_log']) {
                write_log("Expired Signup Cleanup: User: {$arr['username']} was deleted");
            }
        }
    }

    $time_end = microtime(true);
    $run_time = $time_end - $time_start;
    $text = " Run time: $run_time seconds";
    echo $text . "\n";
    if ($data['clean_log'] && $queries > 0) {
        write_log("Expired Signup Completed using $queries queries" . $text);
    }
}
