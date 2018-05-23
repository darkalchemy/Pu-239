<?php
/**
 * @param $data
 *
 * @throws Exception
 */
function achievement_request_update($data)
{
    global $site_config, $queries, $cache;

    set_time_limit(1200);
    ignore_user_abort(true);

    $res         = sql_query('SELECT userid, reqfilled, reqlvl FROM usersachiev WHERE reqfilled >= 1') or sqlerr(__FILE__, __LINE__);
    $msgs_buffer = $usersachiev_buffer = $achievements_buffer = [];
    if (mysqli_num_rows($res) > 0) {
        $dt      = TIME_NOW;
        $subject = sqlesc('New Achievement Earned!');
        $points  = random_int(1, 3);
        $var1    = 'reqlvl';
        while ($arr = mysqli_fetch_assoc($res)) {
            $reqfilled = (int) $arr['reqfilled'];
            $lvl       = (int) $arr['reqlvl'];
            if ($reqfilled >= 1 && 0 == $lvl) {
                $msg                   = sqlesc('Congratulations, you have just earned the [b]Request Filler LVL1[/b] achievement. :) [img]' . $site_config['pic_baseurl'] . 'achievements/reqfiller1.png[/img]');
                $msgs_buffer[]         = "(0, {$arr['userid']} , $dt, $msg, $subject)";
                $achievements_buffer[] = '(' . $arr['userid'] . ', ' . $dt . ', \'Request Filler LVL1\', \'reqfiller1.png\' , \'Filled at least 1 request from the request page.\')';
                $usersachiev_buffer[]  = '(' . $arr['userid'] . ',1, ' . $points . ')';
                $cache->increment('inbox_' . $arr['userid']);
                $cache->delete('user_achievement_points_' . $arr['userid']);
            }
            if ($reqfilled >= 5 && 1 == $lvl) {
                $msg                   = sqlesc('Congratulations, you have just earned the [b]Request Filler LVL2[/b] achievement. :) [img]' . $site_config['pic_baseurl'] . 'achievements/reqfiller2.png[/img]');
                $msgs_buffer[]         = "(0, {$arr['userid']} , $dt, $msg, $subject)";
                $achievements_buffer[] = '(' . $arr['userid'] . ', ' . $dt . ', \'Request Filler LVL2\', \'reqfiller2.png\' , \'Filled at least 5 requests from the request page.\')';
                $usersachiev_buffer[]  = '(' . $arr['userid'] . ',2, ' . $points . ')';
                $cache->increment('inbox_' . $arr['userid']);
            }
            if ($reqfilled >= 10 && 2 == $lvl) {
                $msg                   = sqlesc('Congratulations, you have just earned the [b]Request Filler LVL3[/b] achievement. :) [img]' . $site_config['pic_baseurl'] . 'achievements/reqfiller3.png[/img]');
                $msgs_buffer[]         = "(0, {$arr['userid']} , $dt, $msg, $subject)";
                $achievements_buffer[] = '(' . $arr['userid'] . ', ' . $dt . ', \'Request Filler LVL3\', \'reqfiller3.png\' , \'Filled at least 10 requests from the request page.\')';
                $usersachiev_buffer[]  = '(' . $arr['userid'] . ',3, ' . $points . ')';
                $cache->increment('inbox_' . $arr['userid']);
                $cache->delete('user_achievement_points_' . $arr['userid']);
            }
            if ($reqfilled >= 25 && 3 == $lvl) {
                $msg                   = sqlesc('Congratulations, you have just earned the [b]Request Filler LVL4[/b] achievement. :) [img]' . $site_config['pic_baseurl'] . 'achievements/reqfiller4.png[/img]');
                $msgs_buffer[]         = "(0, {$arr['userid']} , $dt, $msg, $subject)";
                $achievements_buffer[] = '(' . $arr['userid'] . ', ' . $dt . ', \'Request Filler LVL4\', \'reqfiller4.png\' , \'Filled at least 25 requests from the request page.\')';
                $usersachiev_buffer[]  = '(' . $arr['userid'] . ',4, ' . $points . ')';
                $cache->increment('inbox_' . $arr['userid']);
                $cache->delete('user_achievement_points_' . $arr['userid']);
            }
            if ($reqfilled >= 50 && 4 == $lvl) {
                $msg                   = sqlesc('Congratulations, you have just earned the [b]Request Filler LVL5[/b] achievement. :) [img]' . $site_config['pic_baseurl'] . 'achievements/reqfiller5.png[/img]');
                $msgs_buffer[]         = "(0, {$arr['userid']} , $dt, $msg, $subject)";
                $achievements_buffer[] = '(' . $arr['userid'] . ', ' . $dt . ', \'Request Filler LVL5\', \'reqfiller5.png\' , \'Filled at least 50 requests from the request page.\')';
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
            write_log("Achievements Cleanup: Request Filler Completed using $queries queries. Request Filler Achievements awarded to - " . $count . ' Member(s)');
        }
        unset($usersachiev_buffer, $achievements_buffer, $msgs_buffer, $count);
    }
}
