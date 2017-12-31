<?php
/**
 * @param $data
 *
 * @throws Exception
 */
function achievement_sticky_update($data)
{
    global $site_config, $queries, $cache, $lang;
    set_time_limit(1200);
    ignore_user_abort(true);

    $res = sql_query("SELECT userid, stickyup, stickyachiev FROM usersachiev WHERE stickyup >= 1") or sqlerr(__FILE__, __LINE__);
    $msgs_buffer = $usersachiev_buffer = $achievements_buffer = [];
    if (mysqli_num_rows($res) > 0) {
        $dt = TIME_NOW;
        $subject = sqlesc($lang['doc_achiev_earned']);
        $points = random_int(1, 3);
        $var1 = 'stickyachiev';
        while ($arr = mysqli_fetch_assoc($res)) {
            $stickyup = (int)$arr['stickyup'];
            $lvl = (int)$arr['stickyachiev'];
            if ($stickyup >= 1 && $lvl == 0) {
                $msg = sqlesc('Congratulations, you have just earned the [b]Stick Em Up LVL1[/b] achievement. :) [img]' . $site_config['baseurl'] . '/images/achievements/sticky1.png[/img]');
                $msgs_buffer[] = "(0, {$arr['userid']} , $dt, $msg, $subject)";
                $achievements_buffer[] = '(' . $arr['userid'] . ', ' . $dt . ', \'Stick Em Up LVL1\', \'sticky1.png\' , \'Uploading at least 1 sticky torrent to the site.\')';
                $usersachiev_buffer[] = '(' . $arr['userid'] . ',1, ' . $points . ')';
                $cache->increment('inbox_' . $arr['userid']);
                $cache->delete('user_achievement_points_' . $arr['userid']);
            }
            if ($stickyup >= 5 && $lvl == 1) {
                $msg = sqlesc('Congratulations, you have just earned the [b]Stick Em Up LVL2[/b] achievement. :) [img]' . $site_config['baseurl'] . '/images/achievements/sticky2.png[/img]');
                $msgs_buffer[] = "(0, {$arr['userid']} , $dt, $msg, $subject)";
                $achievements_buffer[] = '(' . $arr['userid'] . ', ' . $dt . ', \'Stick Em Up LVL2\', \'sticky2.png\' , \'Uploading at least 5 sticky torrents to the site.\')';
                $usersachiev_buffer[] = '(' . $arr['userid'] . ',2, ' . $points . ')';
                $cache->increment('inbox_' . $arr['userid']);
                $cache->delete('user_achievement_points_' . $arr['userid']);
            }
            if ($stickyup >= 10 && $lvl == 2) {
                $msg = sqlesc('Congratulations, you have just earned the [b]Stick Em Up LVL3[/b] achievement. :) [img]' . $site_config['baseurl'] . '/images/achievements/sticky3.png[/img]');
                $msgs_buffer[] = "(0, {$arr['userid']} , $dt, $msg, $subject)";
                $achievements_buffer[] = '(' . $arr['userid'] . ', ' . $dt . ', \'Stick Em Up LVL3\', \'sticky3.png\' , \'Uploading at least 10 sticky torrents to the site.\')';
                $usersachiev_buffer[] = '(' . $arr['userid'] . ',3, ' . $points . ')';
                $cache->increment('inbox_' . $arr['userid']);
                $cache->delete('user_achievement_points_' . $arr['userid']);
            }
            if ($stickyup >= 25 && $lvl == 3) {
                $msg = sqlesc('Congratulations, you have just earned the [b]Stick Em Up LVL4[/b] achievement. :) [img]' . $site_config['baseurl'] . '/images/achievements/sticky4.png[/img]');
                $msgs_buffer[] = "(0, {$arr['userid']} , $dt, $msg, $subject)";
                $achievements_buffer[] = '(' . $arr['userid'] . ', ' . $dt . ', \'Stick Em Up LVL4\', \'sticky4.png\' , \'Uploading at least 25 sticky torrents to the site.\')';
                $usersachiev_buffer[] = '(' . $arr['userid'] . ',4, ' . $points . ')';
                $cache->increment('inbox_' . $arr['userid']);
                $cache->delete('user_achievement_points_' . $arr['userid']);
            }
            if ($stickyup >= 50 && $lvl == 4) {
                $msg = sqlesc('Congratulations, you have just earned the [b]Stick Em Up LVL5[/b] achievement. :) [img]' . $site_config['baseurl'] . '/images/achievements/sticky5.png[/img]');
                $msgs_buffer[] = "(0, {$arr['userid']} , $dt, $msg, $subject)";
                $achievements_buffer[] = '(' . $arr['userid'] . ', ' . $dt . ', \'Stick Em Up LVL5\', \'sticky5.png\' , \'Uploading at least 50 sticky torrents to the site.\')';
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
            write_log("Achievements Cleanup: Stickied Completed using $queries queries. Stickied Achievements awarded to - " . $count . ' Member(s)');
        }
        unset($usersachiev_buffer, $achievements_buffer, $msgs_buffer, $count);
    }
}
