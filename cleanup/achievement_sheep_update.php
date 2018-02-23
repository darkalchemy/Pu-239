<?php
/**
 * @param $data
 *
 * @throws Exception
 */
function achievement_sheep_update($data)
{
    global $site_config, $queries, $cache;

    $lang = load_language('ad_cleanup_manager');

    set_time_limit(1200);
    ignore_user_abort(true);

    $res = sql_query('SELECT userid, sheepyset FROM usersachiev WHERE sheepyset = 1 AND sheepyach = 0') or sqlerr(__FILE__, __LINE__);
    $msgs_buffer = $usersachiev_buffer = $achievements_buffer = [];
    if (mysqli_num_rows($res) > 0) {
        $subject = sqlesc($lang['doc_achiev_earned']);
        $msg = sqlesc('Congratulations, you have just earned the [b]Sheep Fondler[/b] achievement. :) [img]'.$site_config['pic_baseurl'].'achievements/sheepfondler.png[/img]');
        while ($arr = mysqli_fetch_assoc($res)) {
            $dt = TIME_NOW;
            $points = random_int(1, 3);
            $msgs_buffer[] = "(0, {$arr['userid']} , $dt, $msg, $subject)";
            $achievements_buffer[] = '('.$arr['userid'].', '.$dt.', \'Sheep Fondler\', \'sheepfondler.png\' , \'User has been caught touching the sheep at least 1 time.\')';
            $usersachiev_buffer[] = '('.$arr['userid'].',1, '.$points.')';
            $cache->increment('inbox_'.$arr['userid']);
            $cache->delete('user_achievement_points_'.$arr['userid']);
        }
        $count = count($achievements_buffer);
        if ($count > 0) {
            sql_query('INSERT INTO messages (sender,receiver,added,msg,subject) VALUES '.implode(', ', $msgs_buffer)) or sqlerr(__FILE__, __LINE__);
            sql_query('INSERT INTO achievements (userid, date, achievement, icon, description) VALUES '.implode(', ', $achievements_buffer).' ON DUPLICATE KEY UPDATE date = VALUES(date),achievement = VALUES(achievement),icon = VALUES(icon),description = VALUES(description)') or sqlerr(__FILE__, __LINE__);
            sql_query('INSERT INTO usersachiev (userid, sheepach, achpoints) VALUES '.implode(', ', $usersachiev_buffer).' ON DUPLICATE KEY UPDATE sheepach = VALUES(sheepach), achpoints=achpoints + VALUES(achpoints)') or sqlerr(__FILE__, __LINE__);
        }
        if ($data['clean_log'] && $queries > 0) {
            write_log("Achievements Cleanup: Sheep Fondler Completed using $queries queries. Sheep Fondling Achievements awarded to - ".$count.' Member(s)');
        }
        unset($usersachiev_buffer, $achievement_buffer, $msgs_buffer, $count);
    }
}
