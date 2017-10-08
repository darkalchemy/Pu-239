<?php
function freeuser_update($data)
{
    global $site_config, $queries, $mc1;
    set_time_limit(1200);
    ignore_user_abort(true);
    $res = sql_query('SELECT id, modcomment FROM users WHERE free_switch > 1 AND free_switch < ' . TIME_NOW) or sqlerr(__FILE__, __LINE__);
    $msgs_buffer = $users_buffer = [];
    if (mysqli_num_rows($res) > 0) {
        $subject = 'Freeleech expired.';
        $msg = "Your freeleech has expired and has been auto-removed by the system.\n";
        while ($arr = mysqli_fetch_assoc($res)) {
            $modcomment = $arr['modcomment'];
            $modcomment = get_date(TIME_NOW, 'DATE', 1) . " - Freeleech Removed By System.\n" . $modcomment;
            $modcom = sqlesc($modcomment);
            $msgs_buffer[] = '(0,' . $arr['id'] . ',' . TIME_NOW . ', ' . sqlesc($msg) . ', ' . sqlesc($subject) . ' )';
            $users_buffer[] = '(' . $arr['id'] . ', \'0\', ' . $modcom . ')';
            $mc1->begin_transaction('user' . $arr['id']);
            $mc1->update_row(false, [
                'free_switch' => 0,
            ]);
            $mc1->commit_transaction($site_config['expires']['user_cache']);
            $mc1->begin_transaction('user_stats_' . $arr['id']);
            $mc1->update_row(false, [
                'modcomment' => $modcomment,
            ]);
            $mc1->commit_transaction($site_config['expires']['user_stats']);
            $mc1->begin_transaction('MyUser_' . $arr['id']);
            $mc1->update_row(false, [
                'free_switch' => 0,
            ]);
            $mc1->commit_transaction($site_config['expires']['curuser']);
            $mc1->delete_value('inbox_new_' . $arr['id']);
            $mc1->delete_value('inbox_new_sb_' . $arr['id']);
        }
        $count = count($users_buffer);
        if ($data['clean_log'] && $count > 0) {
            sql_query('INSERT INTO messages (sender,receiver,added,msg,subject) VALUES ' . implode(', ', $msgs_buffer)) or sqlerr(__FILE__, __LINE__);
            sql_query('INSERT INTO users (id, free_switch, modcomment) VALUES ' . implode(', ', $users_buffer) . ' ON DUPLICATE key UPDATE free_switch=values(free_switch), modcomment=values(modcomment)') or sqlerr(__FILE__, __LINE__);
        }
        if ($data['clean_log']) {
            write_log('Cleanup - Removed Freeleech from ' . $count . ' members');
        }
        unset($users_buffer, $msgs_buffer, $count);
    }
    if ($data['clean_log'] && $queries > 0) {
        write_log("Freelech Cleanup: Completed using $queries queries");
    }
}
