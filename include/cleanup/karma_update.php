<?php
/**
 * @param $data
 */
function karma_update($data)
{
    global $site_config, $queries, $mc1;
    set_time_limit(1200);
    ignore_user_abort(true);
    if ($site_config['seedbonus_on'] == 1) {
        $bmt = get_one_row('site_config', 'value', 'WHERE name = "bonux_max_torrents"');
        $What_id = (XBT_TRACKER == true ? 'fid' : 'torrent');
        $What_user_id = (XBT_TRACKER == true ? 'uid' : 'userid');
        $What_Table = (XBT_TRACKER == true ? 'xbt_files_users' : 'peers');
        $What_Where = (XBT_TRACKER == true ? '`left` = 0 AND `active` = 1' : "seeder = 'yes' AND connectable = 'yes'");
        $res = sql_query('SELECT COUNT(' . $What_id . ') As tcount, ' . $What_user_id . ', seedbonus, users.id As users_id FROM ' . $What_Table . ' LEFT JOIN users ON users.id = ' . $What_user_id . ' WHERE ' . $What_Where . ' GROUP BY ' . $What_user_id) or sqlerr(__FILE__, __LINE__);
        if (mysqli_num_rows($res) > 0) {
            while ($arr = mysqli_fetch_assoc($res)) {
                if ($arr['tcount'] >= $bmt) {
                    $arr['tcount'] = $bmt;
                }
                $Buffer_User = (XBT_TRACKER == true ? $arr['uid'] : $arr['userid']);
                if ($arr['users_id'] == $Buffer_User && $arr['users_id'] != null) {
                    $users_buffer[] = '(' . $Buffer_User . ', ' . $site_config['bonus_per_duration'] . ' * ' . $arr['tcount'] . ')';
                    $update['seedbonus'] = ($arr['seedbonus'] + $site_config['bonus_per_duration'] * $arr['tcount']);
                    $mc1->begin_transaction('userstats_' . $Buffer_User);
                    $mc1->update_row(false, [
                        'seedbonus' => $update['seedbonus'],
                    ]);
                    $mc1->commit_transaction($site_config['expires']['u_stats']);
                    $mc1->begin_transaction('user_stats_' . $Buffer_User);
                    $mc1->update_row(false, [
                        'seedbonus' => $update['seedbonus'],
                    ]);
                    $mc1->commit_transaction($site_config['expires']['user_stats']);
                }
            }
            $count = count($users_buffer);
            if ($count > 0) {
                sql_query('INSERT INTO users (id,seedbonus) VALUES ' . implode(', ', $users_buffer) . ' ON DUPLICATE key UPDATE seedbonus=seedbonus+values(seedbonus)') or sqlerr(__FILE__, __LINE__);
            }
            if ($data['clean_log']) {
                write_log('Cleanup - ' . $count . ' users received seedbonus');
            }
            unset($users_buffer, $update, $count);
        }
    }
    //== End
    if ($data['clean_log'] && $queries > 0) {
        write_log("Karma Cleanup: Completed using $queries queries");
    }
}
