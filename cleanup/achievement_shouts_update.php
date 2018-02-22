<?php
/**
 * @param $data
 *
 * @throws Exception
 */
function achievement_shouts_update($data)
{
    global $site_config, $queries;

$cache = new DarkAlchemy\Pu239\Cache();
    $lang = load_language('ad_cleanup_manager');

    set_time_limit(1200);
    ignore_user_abort(true);

    $res = sql_query("SELECT userid, dailyshouts, dailyshoutlvl FROM usersachiev WHERE dailyshouts >= 10") or sqlerr(__FILE__, __LINE__);
    $msgs_buffer = $usersachiev_buffer = $achievements_buffer = [];
    if (mysqli_num_rows($res) > 0) {
        $dt = TIME_NOW;
        $subject = sqlesc($lang['doc_achiev_earned']);
        $points = random_int(1, 3);
        $var1 = 'dailyshoutlvl';
        while ($arr = mysqli_fetch_assoc($res)) {
            $shouts = (int)$arr['dailyshouts'];
            $lvl = (int)$arr['dailyshoutlvl'];
            if ($shouts >= 1 && $lvl == 0) {
                $msg = sqlesc('Congratulations, you have just earned the [b]AJAX Chat Spammer Level 1[/b] achievement. :) [img]' . $site_config['pic_baseurl'] . 'achievements/spam1.png[/img]');
                $msgs_buffer[] = "(0, {$arr['userid']} , $dt, $msg, $subject)";
                $achievements_buffer[] = '(' . $arr['userid'] . ', ' . $dt . ', \'AJAX Chat Spammer LVL1\', \'spam1.png\' , \'Made at least 10 posts to AJAX Chat today.\')';
                $usersachiev_buffer[] = '(' . $arr['userid'] . ',1, ' . $points . ')';
                $cache->increment('inbox_' . $arr['userid']);
                $cache->delete('user_achievement_points_' . $arr['userid']);
            }
            if ($shouts >= 25 && $lvl == 1) {
                $msg = sqlesc('Congratulations, you have just earned the [b]AJAX Chat Spammer Level 2[/b] achievement. :) [img]' . $site_config['pic_baseurl'] . 'achievements/spam2.png[/img]');
                $msgs_buffer[] = "(0, {$arr['userid']} , $dt, $msg, $subject)";
                $achievements_buffer[] = '(' . $arr['userid'] . ', ' . $dt . ', \'AJAX Chat Spammer LVL2\', \'spam2.png\' , \'Made at least 25 posts to AJAX Chat today.\')';
                $usersachiev_buffer[] = '(' . $arr['userid'] . ',2, ' . $points . ')';
                $cache->increment('inbox_' . $arr['userid']);
                $cache->delete('user_achievement_points_' . $arr['userid']);
            }
            if ($shouts >= 50 && $lvl == 2) {
                $msg = sqlesc('Congratulations, you have just earned the [b]AJAX Chat Spammer Level 3[/b] achievement. :) [img]' . $site_config['pic_baseurl'] . 'achievements/spam3.png[/img]');
                $msgs_buffer[] = "(0, {$arr['userid']} , $dt, $msg, $subject)";
                $achievements_buffer[] = '(' . $arr['userid'] . ', ' . $dt . ', \'AJAX Chat Spammer LVL3\', \'spam3.png\' , \'Made at least 50 posts to AJAX Chat today.\')';
                $usersachiev_buffer[] = '(' . $arr['userid'] . ',3, ' . $points . ')';
                $cache->increment('inbox_' . $arr['userid']);
                $cache->delete('user_achievement_points_' . $arr['userid']);
            }
            if ($shouts >= 75 && $lvl == 3) {
                $msg = sqlesc('Congratulations, you have just earned the [b]AJAX Chat Spammer Level 4[/b] achievement. :) [img]' . $site_config['pic_baseurl'] . 'achievements/spam4.png[/img]');
                $msgs_buffer[] = "(0, {$arr['userid']} , $dt, $msg, $subject)";
                $achievements_buffer[] = '(' . $arr['userid'] . ', ' . $dt . ', \'AJAX Chat Spammer LVL4\', \'spam4.png\' , \'Made at least 75 posts to AJAX Chat today.\')';
                $usersachiev_buffer[] = '(' . $arr['userid'] . ',4, ' . $points . ')';
                $cache->increment('inbox_' . $arr['userid']);
                $cache->delete('user_achievement_points_' . $arr['userid']);
            }
            if ($shouts >= 100 && $lvl == 4) {
                $msg = sqlesc('Congratulations, you have just earned the [b]AJAX Chat Spammer Level 5[/b] achievement. :) [img]' . $site_config['pic_baseurl'] . 'achievements/spam5.png[/img]');
                $msgs_buffer[] = "(0, {$arr['userid']} , $dt, $msg, $subject)";
                $achievements_buffer[] = '(' . $arr['userid'] . ', ' . $dt . ', \'AJAX Chat Spammer LVL5\', \'spam5.png\' , \'Made at least 100 posts to AJAX Chat today.\')';
                $usersachiev_buffer[] = '(' . $arr['userid'] . ',5, ' . $points . ')';
                $cache->increment('inbox_' . $arr['userid']);
                $cache->delete('user_achievement_points_' . $arr['userid']);
            }
        }
        $count = count($achievements_buffer);
        if ($count > 0) {
            sql_query('INSERT INTO messages (sender,receiver,added,msg,subject) VALUES ' . implode(', ', $msgs_buffer)) or sqlerr(__FILE__, __LINE__);
            sql_query('INSERT INTO achievements (userid, date, achievement, icon, description) VALUES ' . implode(', ', $achievements_buffer) . ' ON DUPLICATE KEY UPDATE date = VALUES(date),achievement = VALUES(achievement),icon = VALUES(icon),description = VALUES(description)') or sqlerr(__FILE__, __LINE__);
            sql_query("INSERT INTO usersachiev (userid, $var1, achpoints) VALUES " . implode(', ', $usersachiev_buffer) . " ON DUPLICATE KEY UPDATE $var1 = VALUES($var1), achpoints=achpoints + VALUES(achpoints)") or sqlerr(__FILE__, __LINE__);
        }
        if ($data['clean_log'] && $queries > 0) {
            write_log("Achievements Cleanup: Shouter Completed using $queries queries. Shouter Achievements awarded to - " . $count . ' Member(s)');
        }
        unset($usersachiev_buffer, $achievements_buffer, $msgs_buffer, $count);
    }
}
