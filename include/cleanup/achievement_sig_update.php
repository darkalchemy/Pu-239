<?php
/**
 * @param $data
 */
function achievement_sig_update($data)
{
    global $site_config, $queries, $mc1;
    set_time_limit(1200);
    ignore_user_abort(true);
    // Updated Signature Setter Achievement
    $res = sql_query("SELECT userid, sigset FROM usersachiev WHERE sigset = 1 AND sigach = 0") or sqlerr(__FILE__, __LINE__);
    $msg_buffer = $usersachiev_buffer = $achievements_buffer = [];
    if (mysqli_num_rows($res) > 0) {
        $subject = sqlesc('New Achievement Earned!');
        $msg = sqlesc('Congratulations, you have just earned the [b]Signature Setter[/b] achievement. :) [img]' . $site_config['baseurl'] . '/images/achievements/signature.png[/img]');
        while ($arr = mysqli_fetch_assoc($res)) {
            $dt = TIME_NOW;
            $points = random_int(1, 3);
            $msgs_buffer[] = '(0,' . $arr['userid'] . ',' . TIME_NOW . ', ' . sqlesc($msg) . ', ' . sqlesc($subject) . ')';
            $achievements_buffer[] = '(' . $arr['userid'] . ', ' . TIME_NOW . ', \'Signature Setter\', \'signature.png\' , \'User has successfully set a signature on profile settings.\')';
            $usersachiev_buffer[] = '(' . $arr['userid'] . ',1, ' . $points . ')';
            $mc1->delete_value('inbox_new_' . $arr['userid']);
            $mc1->delete_value('inbox_new_sb_' . $arr['userid']);
            $mc1->delete_value('user_achievement_points_' . $arr['userid']);
        }
        $count = count($achievements_buffer);
        if ($count > 0) {
            sql_query('INSERT INTO messages (sender,receiver,added,msg,subject) VALUES ' . implode(', ', $msgs_buffer)) or sqlerr(__FILE__, __LINE__);
            sql_query('INSERT INTO achievements (userid, date, achievement, icon, description) VALUES ' . implode(', ', $achievements_buffer) . ' ON DUPLICATE key UPDATE date=values(date),achievement=values(achievement),icon=values(icon),description=values(description)') or sqlerr(__FILE__, __LINE__);
            sql_query('INSERT INTO usersachiev (userid, sigach, achpoints) VALUES ' . implode(', ', $usersachiev_buffer) . ' ON DUPLICATE key UPDATE sigach=values(sigach), achpoints=achpoints+values(achpoints)') or sqlerr(__FILE__, __LINE__);
        }
        if ($data['clean_log'] && $queries > 0) {
            write_log("Achievements Cleanup: Signature Setter Completed using $queries queries. Signature Setter Achievements awarded to - " . $count . ' Member(s)');
        }
        unset($usersachiev_buffer, $achievement_buffer, $msgs_buffer, $count);
    }
}
