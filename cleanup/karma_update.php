<?php
/**
 * @param $data
 *
 * @throws \MatthiasMullie\Scrapbook\Exception\UnbegunTransaction
 */
function karma_update($data)
{
    global $site_config, $queries, $cache;

    set_time_limit(1200);
    ignore_user_abort(true);
    $count = $total = 0;

    if (1 == $site_config['seedbonus_on']) {
        $users_buffer = [];
        $bmt          = $site_config['bonus_max_torrents'];
        $What_id      = (XBT_TRACKER ? 'fid' : 'torrent');
        $What_user_id = (XBT_TRACKER ? 'uid' : 'userid');
        $What_Table   = (XBT_TRACKER ? 'xbt_files_users' : 'peers');
        $What_Where   = (XBT_TRACKER ? '`left` = 0 AND `active` = 1' : "seeder = 'yes' AND connectable = 'yes'");
        $sql          = "SELECT COUNT($What_id) As tcount, $What_user_id, seedbonus, users.id AS users_id, users.username
                FROM $What_Table
                LEFT JOIN users ON users.id = $What_user_id
                WHERE $What_Where
                GROUP BY $What_user_id";
        $res = sql_query($sql) or sqlerr(__FILE__, __LINE__);
        if (mysqli_num_rows($res) > 0) {
            while ($arr = mysqli_fetch_assoc($res)) {
                if ($arr['tcount'] >= $bmt) {
                    $arr['tcount'] = $bmt;
                }
                $Buffer_User = (XBT_TRACKER ? $arr['uid'] : $arr['userid']);
                if ($arr['users_id'] == $Buffer_User && null != $arr['users_id']) {
                    $bonus = $site_config['bonus_per_duration'] * $arr['tcount'];
                    $total += $bonus;
                    $update['seedbonus'] = $arr['seedbonus'] + $bonus;
                    $users_buffer[]      = "($Buffer_User, " . sqlesc($arr['username']) . ", {$update['seedbonus']}, '', '')";
                    $cache->update_row('user' . $Buffer_User, [
                        'seedbonus' => $update['seedbonus'],
                    ], $site_config['expires']['user_cache']);
                }
            }
            $count = count($users_buffer);

            if ($count > 0) {
                $sql = 'INSERT INTO users (id, username, seedbonus, email, ip) VALUES ' . implode(', ', $users_buffer) . ' 
                        ON DUPLICATE KEY UPDATE seedbonus = VALUES(seedbonus)';
                sql_query($sql) or sqlerr(__FILE__, __LINE__);
            }
            if ($data['clean_log']) {
                write_log('Cleanup - ' . $count . ' user' . plural($count) . ' received seedbonus totaling ' . $total . ' karma');
            }
        }
        unset($users_buffer, $update, $count, $arr, $total, $Buffer_User, $sql, $res);
    }
    if ($data['clean_log'] && $queries > 0) {
        write_log("Karma Cleanup: Completed using $queries queries");
    }
}
