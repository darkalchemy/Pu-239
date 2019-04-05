<?php

/**
 * @param $data
 *
 * @throws \MatthiasMullie\Scrapbook\Exception\UnbegunTransaction
 */
function karma_update($data)
{
    $time_start = microtime(true);
    dbconn();
    global $site_config, $queries, $cache, $fluent;

    set_time_limit(1200);
    ignore_user_abort(true);
    $count = $total = 0;

    if ($site_config['bonus']['on']) {
        $users_buffer = [];
        $bmt = $site_config['bonus']['max_torrents'];
        //        $sql = $fluent->from('peers')
        //            ->select(null)
        //            ->select('COUNT(torrent) AS tcount)
        //            ->select('seedbonus')
        //            ->select('users.username')

        $sql = "SELECT COUNT(torrent) As tcount, userid, seedbonus, users.username
                FROM peers
                LEFT JOIN users ON users.id=userid
                WHERE seeder = 'yes' AND connectable = 'yes'
                GROUP BY userid, seedbonus, username";
        $res = sql_query($sql) or sqlerr(__FILE__, __LINE__);
        if (mysqli_num_rows($res) > 0) {
            while ($arr = mysqli_fetch_assoc($res)) {
                if ($arr['tcount'] >= $bmt) {
                    $arr['tcount'] = $bmt;
                }
                $Buffer_User = $arr['userid'];
                if ($arr['userid'] == $Buffer_User && $arr['userid'] != null) {
                    $bonus = $site_config['bonus']['per_duration'] * $arr['tcount'];
                    $total += $bonus;
                    $update['seedbonus'] = $arr['seedbonus'] + $bonus;
                    $users_buffer[] = "($Buffer_User, " . sqlesc($arr['username']) . ", {$update['seedbonus']}, '', '')";
                    $cache->update_row('user_' . $Buffer_User, [
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
    $time_end = microtime(true);
    $run_time = $time_end - $time_start;
    $text = " Run time: $run_time seconds";
    echo $text . "\n";
    if ($data['clean_log'] && $queries > 0) {
        write_log("Karma Cleanup: Completed using $queries queries" . $text);
    }
}
