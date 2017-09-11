<?php
function expired_signup_update($data)
{
    global $site_config, $queries, $mc1;
    set_time_limit(1200);
    ignore_user_abort(true);
    $deadtime = TIME_NOW - $site_config['signup_timeout'];
    $res = sql_query("SELECT id, username, added, downloaded, uploaded, last_access, class, donor, warned, enabled, status FROM users WHERE status = 'pending' AND added < $deadtime AND last_login < $deadtime AND last_access < $deadtime ORDER BY username DESC");
    if (mysqli_num_rows($res) != 0) {
        while ($arr = mysqli_fetch_assoc($res)) {
            $userid = $arr['id'];
            $res_del = sql_query('DELETE FROM users WHERE id=' . sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
            $mc1->delete_value('MyUser_' . $userid);
            $mc1->delete_value('user' . $userid);
            write_log("Expired Signup Cleanup: User: {$arr['username']} was deleted");
        }
    }

    if ($queries > 0) {
        write_log("Expired Signup clean-------------------- Expired Signup cleanup Complete using $queries queries --------------------");
    }
    if (false !== mysqli_affected_rows($GLOBALS['___mysqli_ston'])) {
        $data['clean_desc'] = mysqli_affected_rows($GLOBALS['___mysqli_ston']) . ' items deleted/updated';
    }
    if ($data['clean_log']) {
        cleanup_log($data);
    }
}
