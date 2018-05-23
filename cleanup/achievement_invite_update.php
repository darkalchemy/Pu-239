<?php
/**
 * @param $data
 *
 * @throws Exception
 */
function achievement_invite_update($data)
{
    global $site_config, $queries, $cache;

    set_time_limit(1200);
    ignore_user_abort(true);

    $res         = sql_query('SELECT userid, invited, inviterach FROM usersachiev WHERE invited >= 1') or sqlerr(__FILE__, __LINE__);
    $msgs_buffer = $usersachiev_buffer = $achievements_buffer = [];
    if (mysqli_num_rows($res) > 0) {
        $dt      = TIME_NOW;
        $subject = sqlesc('New Achievement Earned!');
        $points  = random_int(1, 3);
        $var1    = 'inviterach';
        while ($arr = mysqli_fetch_assoc($res)) {
            $invited = (int) $arr['invited'];
            $lvl     = (int) $arr['inviterach'];
            if ($invited >= 1 && 0 == $lvl) {
                $msg                   = sqlesc('Congratulations, you have just earned the [b]Inviter Level 1[/b] achievement. :) [img]' . $site_config['pic_baseurl'] . 'achievements/invite1.png[/img]');
                $msgs_buffer[]         = "(0, {$arr['userid']} , $dt, $msg, $subject)";
                $achievements_buffer[] = '(' . $arr['userid'] . ', ' . $dt . ', \'Inviter LVL1\', \'invite1.png\' , \'Invited at least 1 new user to the site.\')';
                $usersachiev_buffer[]  = '(' . $arr['userid'] . ',1, ' . $points . ')';
                $cache->increment('inbox_' . $arr['userid']);
                $cache->delete('user_achievement_points_' . $arr['userid']);
            }
            if ($invited >= 2 && 1 == $lvl) {
                $msg                   = sqlesc('Congratulations, you have just earned the [b]Inviter Level 2[/b] achievement. :) [img]' . $site_config['pic_baseurl'] . 'achievements/invite2.png[/img]');
                $msgs_buffer[]         = "(0, {$arr['userid']} , $dt, $msg, $subject)";
                $achievements_buffer[] = '(' . $arr['userid'] . ', ' . $dt . ', \'Inviter LVL2\', \'invite2.png\' , \'Invited at least 2 new users to the site.\')';
                $usersachiev_buffer[]  = '(' . $arr['userid'] . ',2, ' . $points . ')';
                $cache->increment('inbox_' . $arr['userid']);
            }
            if ($invited >= 3 && 2 == $lvl) {
                $msg                   = sqlesc('Congratulations, you have just earned the [b]Inviter Level 3[/b] achievement. :) [img]' . $site_config['pic_baseurl'] . 'achievements/invite3.png[/img]');
                $msgs_buffer[]         = "(0, {$arr['userid']} , $dt, $msg, $subject)";
                $achievements_buffer[] = '(' . $arr['userid'] . ', ' . $dt . ', \'Inviter LVL3\', \'invite3.png\' , \'Invited at least 3 new users to the site.\')';
                $usersachiev_buffer[]  = '(' . $arr['userid'] . ',3, ' . $points . ')';
                $cache->increment('inbox_' . $arr['userid']);
                $cache->delete('user_achievement_points_' . $arr['userid']);
            }
            if ($invited >= 5 && 3 == $lvl) {
                $msg                   = sqlesc('Congratulations, you have just earned the [b]Inviter Level 4[/b] achievement. :) [img]' . $site_config['pic_baseurl'] . 'achievements/invite4.png[/img]');
                $msgs_buffer[]         = "(0, {$arr['userid']} , $dt, $msg, $subject)";
                $achievements_buffer[] = '(' . $arr['userid'] . ', ' . $dt . ', \'Inviter LVL4\', \'invite4.png\' , \'Invited at least 5 new users to the site.\')';
                $usersachiev_buffer[]  = '(' . $arr['userid'] . ',4, ' . $points . ')';
                $cache->increment('inbox_' . $arr['userid']);
                $cache->delete('user_achievement_points_' . $arr['userid']);
            }
            if ($invited >= 10 && 4 == $lvl) {
                $msg                   = sqlesc('Congratulations, you have just earned the [b]Inviter Level 5[/b] achievement. :) [img]' . $site_config['pic_baseurl'] . 'achievements/invite5.png[/img]');
                $msgs_buffer[]         = "(0, {$arr['userid']} , $dt, $msg, $subject)";
                $achievements_buffer[] = '(' . $arr['userid'] . ', ' . $dt . ', \'Inviter LVL5\', \'invite5.png\' , \'Invited at least 10 new users to the site.\')';
                $usersachiev_buffer[]  = '(' . $arr['userid'] . ',5, ' . $points . ')';
                $cache->increment('inbox_' . $arr['userid']);
                $cache->delete('user_achievement_points_' . $arr['userid']);
            }
        }
        $count = count($achievements_buffer);
        if ($count > 0) {
            sql_query('INSERT INTO messages (sender,receiver,added,msg,subject) VALUES ' . implode(', ', $msgs_buffer))                                                                                                                                                                 or sqlerr(__FILE__, __LINE__);
            sql_query('INSERT INTO achievements (userid, date, achievement, icon, description) VALUES ' . implode(', ', $achievements_buffer) . ' ON DUPLICATE KEY UPDATE date = VALUES(date),achievement = VALUES(achievement),icon = VALUES(icon),description = VALUES(description)') or sqlerr(__FILE__, __LINE__);
            sql_query("INSERT INTO usersachiev (userid, $var1, achpoints) VALUES " . implode(', ', $usersachiev_buffer) . " ON DUPLICATE KEY UPDATE $var1 = VALUES($var1), achpoints=achpoints + VALUES(achpoints)")                                                                    or sqlerr(__FILE__, __LINE__);
        }
        if ($data['clean_log'] && $queries > 0) {
            write_log("Achievements Cleanup: Inviter Completed using $queries queries. Inviter Achievements awarded to - " . $count . ' Member(s)');
        }
        unset($usersachiev_buffer, $achievements_buffer, $msgs_buffer, $count);
    }
}
