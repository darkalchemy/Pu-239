<?php
function customsmilie_update($data)
{
    global $site_config, $queries, $mc1;
    set_time_limit(1200);
    ignore_user_abort(true);
    //=== Updated remove custom smilies by Bigjoos/pdq:)
    $res = sql_query('SELECT id, username, modcomment FROM users WHERE smile_until < ' . TIME_NOW . " AND smile_until <> '0'") or sqlerr(__FILE__, __LINE__);
    $msgs_buffer = $users_buffer = [];
    if (mysqli_num_rows($res) > 0) {
        $subject = 'Custom smilies expired.';
        $msg = "Your Custom smilies have timed out and has been auto-removed by the system. If you would like to have them again, exchange some Karma Bonus Points again. Cheers!\n";
        while ($arr = mysqli_fetch_assoc($res)) {
            $modcomment = $arr['modcomment'];
            $modcomment = get_date(TIME_NOW, 'DATE', 1) . " - Custom smilies Automatically Removed By System.\n" . $modcomment;
            $modcom = sqlesc($modcomment);
            $username = sqlesc($arr['username']);
            $msgs_buffer[] = '(0,' . $arr['id'] . ',' . TIME_NOW . ', ' . sqlesc($msg) . ', ' . sqlesc($subject) . ' )';
            $users_buffer[] = "({$arr['id']}, {$username}, 0, {$modcom})";
            $mc1->begin_transaction('user' . $arr['id']);
            $mc1->update_row(false, [
                'smile_until' => 0,
            ]);
            $mc1->commit_transaction($site_config['expires']['user_cache']);
            $mc1->begin_transaction('user_stats_' . $arr['id']);
            $mc1->update_row(false, [
                'modcomment' => $modcomment,
            ]);
            $mc1->commit_transaction($site_config['expires']['user_stats']);
            $mc1->begin_transaction('MyUser_' . $arr['id']);
            $mc1->update_row(false, [
                'smile_until' => 0,
            ]);
            $mc1->commit_transaction($site_config['expires']['curuser']);
            $mc1->delete_value('inbox_new_' . $arr['id']);
            $mc1->delete_value('inbox_new_sb_' . $arr['id']);
        }
        $count = count($users_buffer);
        if ($data['clean_log'] && $count > 0) {
            sql_query('INSERT INTO messages (sender, receiver, added, msg, subject) VALUES ' . implode(', ', $msgs_buffer)) or sqlerr(__FILE__, __LINE__);
            sql_query('INSERT INTO users (id, username, smile_until, modcomment) VALUES ' . implode(', ', $users_buffer) . ' ON DUPLICATE KEY UPDATE smile_until = VALUES(smile_until), modcomment = VALUES(modcomment)') or sqlerr(__FILE__, __LINE__);
            write_log('Cleanup - Removed Custom smilies from ' . $count . ' members');
        }
        unset($users_buffer, $msgs_buffer, $count);
    }
    //==
    if ($data['clean_log'] && $queries > 0) {
        write_log("Custom Smilie Cleanup: Completed using $queries queries");
    }
}
