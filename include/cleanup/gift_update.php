<?php
function gift_update($data)
{
    global $INSTALLER09, $queries, $mc1;
    set_time_limit(0);
    ignore_user_abort(1);
    $res = sql_query("SELECT id, modcomment FROM users WHERE gotgift='yes'") or sqlerr(__FILE__, __LINE__);
    $users_buffer = [];
    if (mysqli_num_rows($res) > 0) {
        while ($arr = mysqli_fetch_assoc($res)) {
            $users_buffer[] = '(' . $arr['id'] . ', \'no\')';
            $mc1->begin_transaction('user' . $arr['id']);
            $mc1->update_row(false, [
                'gotgift' => 'no',
            ]);
            $mc1->commit_transaction($INSTALLER09['expires']['user_cache']);
            $mc1->begin_transaction('MyUser_' . $arr['id']);
            $mc1->update_row(false, [
                'gotgift' => 'no',
            ]);
            $mc1->commit_transaction($INSTALLER09['expires']['curuser']);
        }
        $count = count($users_buffer);
        if ($count > 0) {
            sql_query('INSERT INTO users (id, gotgift) VALUES ' . implode(', ', $users_buffer) . ' ON DUPLICATE key UPDATE gotgift=values(gotgift)') or sqlerr(__FILE__, __LINE__);
            write_log('Cleanup - Reset ' . $count . ' members Christmas Gift');
        }
        unset($users_buffer, $count);
    }
    //==End
    if ($queries > 0) {
        write_log("Christmas Gift Cleanup: Completed using $queries queries");
    }
    if (false !== mysqli_affected_rows($GLOBALS['___mysqli_ston'])) {
        $data['clean_desc'] = mysqli_affected_rows($GLOBALS['___mysqli_ston']) . ' items deleted/updated';
    }
    if ($data['clean_log']) {
        cleanup_log($data);
    }
}
