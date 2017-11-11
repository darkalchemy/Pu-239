<?php
/**
 * @param $data
 */
function gift_update($data)
{
    global $site_config, $queries, $mc1;
    set_time_limit(1200);
    ignore_user_abort(true);
    $res = sql_query("SELECT id, modcomment FROM users WHERE gotgift='yes'") or sqlerr(__FILE__, __LINE__);
    $users_buffer = [];
    if (mysqli_num_rows($res) > 0) {
        while ($arr = mysqli_fetch_assoc($res)) {
            $users_buffer[] = '(' . $arr['id'] . ', \'no\')';
            $mc1->begin_transaction('user' . $arr['id']);
            $mc1->update_row(false, [
                'gotgift' => 'no',
            ]);
            $mc1->commit_transaction($site_config['expires']['user_cache']);
            $mc1->begin_transaction('MyUser_' . $arr['id']);
            $mc1->update_row(false, [
                'gotgift' => 'no',
            ]);
            $mc1->commit_transaction($site_config['expires']['curuser']);
        }
        $count = count($users_buffer);
        if ($count > 0) {
            sql_query('INSERT INTO users (id, gotgift) VALUES ' . implode(', ', $users_buffer) . ' ON DUPLICATE key UPDATE gotgift=values(gotgift)') or sqlerr(__FILE__, __LINE__);
        }
        if ($data['clean_log']) {
            write_log('Cleanup - Reset ' . $count . ' members Christmas Gift');
        }
        unset($users_buffer, $count);
    }
    //==End
    if ($data['clean_log'] && $queries > 0) {
        write_log("Christmas Gift Cleanup: Completed using $queries queries");
    }
}
