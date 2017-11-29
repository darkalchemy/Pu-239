<?php
/**
 * @param $data
 */
function irc_update($data)
{
    global $site_config, $queries, $cache;
    set_time_limit(1200);
    ignore_user_abort(true);
    $res = sql_query("SELECT id, seedbonus, irctotal FROM users WHERE onirc = 'yes'") or sqlerr(__FILE__, __LINE__);
    if (mysqli_num_rows($res) > 0) {
        while ($arr = mysqli_fetch_assoc($res)) {
            $users_buffer[] = '(' . $arr['id'] . ',0.225,' . $site_config['autoclean_interval'] . ')'; // .250 karma
            //$users_buffer[] = '('.$arr['id'].',15728640,'.$site_config['autoclean_interval'].')'; // 15 mb
            $update['seedbonus'] = ($arr['seedbonus'] + 0.225);
            $update['irctotal'] = ($arr['irctotal'] + $site_config['autoclean_interval']);
            $cache->update_row('user' . $arr['id'], [
                'irctotal' => $update['irctotal'],
            ], $site_config['expires']['user_cache']);
            $cache->update_row('user_stats' . $arr['id'], [
                'seedbonus' => $update['seedbonus'],
            ], $site_config['expires']['user_stats']);
            $cache->update_row('userstats_' . $arr['id'], [
                'seedbonus' => $update['seedbonus'],
            ], $site_config['expires']['u_stats']);
        }
        $count = count($users_buffer);
        if ($count > 0) {
            sql_query('INSERT INTO users (id,seedbonus,irctotal) VALUES ' . implode(', ', $users_buffer) . ' ON DUPLICATE KEY UPDATE seedbonus=seedbonus + VALUES(seedbonus),irctotal=irctotal + VALUES(irctotal)') or sqlerr(__FILE__, __LINE__);
        }
        if ($data['clean_log']) {
            write_log('Cleanup ' . $count . ' users idling on IRC');
        }
        unset($users_buffer, $update, $count);
    }
    if ($data['clean_log'] && $queries > 0) {
        write_log("Irc Cleanup: Completed using $queries queries");
    }
}
