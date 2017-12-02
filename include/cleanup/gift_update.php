<?php
/**
 * @param $data
 */
function gift_update($data)
{
    global $site_config, $queries, $cache;
    set_time_limit(1200);
    ignore_user_abort(true);
    $res = sql_query("SELECT id, modcomment FROM users WHERE gotgift='yes'") or sqlerr(__FILE__, __LINE__);
    $users_buffer = [];
    if (mysqli_num_rows($res) > 0) {
        while ($arr = mysqli_fetch_assoc($res)) {
            $users_buffer[] = '(' . $arr['id'] . ', \'no\')';
            $cache->update_row('user' . $arr['id'], [
                'gotgift' => 'no',
            ], $site_config['expires']['user_cache']);
        }
        $count = count($users_buffer);
        if ($count > 0) {
            sql_query('INSERT INTO users (id, gotgift) VALUES ' . implode(', ', $users_buffer) . ' ON DUPLICATE KEY UPDATE gotgift = VALUES(gotgift)') or sqlerr(__FILE__, __LINE__);
        }
        if ($data['clean_log']) {
            write_log('Cleanup - Reset ' . $count . ' members Christmas Gift');
        }
        unset($users_buffer, $count);
    }
    if ($data['clean_log'] && $queries > 0) {
        write_log("Christmas Gift Cleanup: Completed using $queries queries");
    }
}
