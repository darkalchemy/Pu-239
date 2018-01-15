<?php
/**
 * @param $data
 *
 * @throws Exception
 */
function achievement_ftopics_update($data)
{
    global $site_config, $queries, $cache;
    $lang = load_language('ad_cleanup_manager');

    set_time_limit(1200);
    ignore_user_abort(true);

    $res = sql_query("SELECT userid, forumtopics, topicachiev FROM usersachiev WHERE forumtopics >= 1") or sqlerr(__FILE__, __LINE__);
    $msgs_buffer = $usersachiev_buffer = $achievements_buffer = [];
    if (mysqli_num_rows($res) > 0) {
        $dt = TIME_NOW;
        $subject = sqlesc($lang['doc_achiev_earned']);
        $points = random_int(1, 3);
        $var1 = 'topicachiev';
        while ($arr = mysqli_fetch_assoc($res)) {
            $topics = (int)$arr['forumtopics'];
            $lvl = (int)$arr['topicachiev'];
            if ($topics >= 1 && $lvl == 0) {
                $msg = sqlesc('Congratulations, you have just earned the [b]Forum Topic Starter Level 1[/b] achievement. :) [img]' . $site_config['pic_baseurl'] . 'achievements/ftopic1.png[/img]');
                $msgs_buffer[] = "(0, {$arr['userid']} , $dt, $msg, $subject)";
                $achievements_buffer[] = '(' . $arr['userid'] . ', ' . $dt . ', \'Forum Topic Starter LVL1\', \'ftopic1.png\' , \'Started at least 1 topic in the forums.\')';
                $usersachiev_buffer[] = '(' . $arr['userid'] . ',1, ' . $points . ')';
                $cache->increment('inbox_' . $arr['userid']);
                $cache->delete('user_achievement_points_' . $arr['userid']);
            }
            if ($topics >= 10 && $lvl == 1) {
                $msg = sqlesc('Congratulations, you have just earned the [b]Forum Topic Starter Level 2[/b] achievement. :) [img]' . $site_config['pic_baseurl'] . 'achievements/ftopic2.png[/img]');
                $msgs_buffer[] = "(0, {$arr['userid']} , $dt, $msg, $subject)";
                $achievements_buffer[] = '(' . $arr['userid'] . ', ' . $dt . ', \'Forum Topic Starter LVL2\', \'ftopic2.png\' , \'Started at least 10 topics in the forums.\')';
                $usersachiev_buffer[] = '(' . $arr['userid'] . ',2, ' . $points . ')';
                $cache->increment('inbox_' . $arr['userid']);
                $cache->delete('user_achievement_points_' . $arr['userid']);
            }
            if ($topics >= 25 && $lvl == 2) {
                $msg = sqlesc('Congratulations, you have just earned the [b]Forum Topic Starter Level 3[/b] achievement. :) [img]' . $site_config['pic_baseurl'] . 'achievements/ftopic3.png[/img]');
                $msgs_buffer[] = "(0, {$arr['userid']} , $dt, $msg, $subject)";
                $achievements_buffer[] = '(' . $arr['userid'] . ', ' . $dt . ', \'Forum Topic Starter LVL3\', \'ftopic3.png\' , \'Started at least 25 topics in the forums.\')';
                $usersachiev_buffer[] = '(' . $arr['userid'] . ',3, ' . $points . ')';
                $cache->increment('inbox_' . $arr['userid']);
                $cache->delete('user_achievement_points_' . $arr['userid']);
            }
            if ($topics >= 50 && $lvl == 3) {
                $msg = sqlesc('Congratulations, you have just earned the [b]Forum Topic Starter Level 4[/b] achievement. :) [img]' . $site_config['pic_baseurl'] . 'achievements/ftopic4.png[/img]');
                $msgs_buffer[] = "(0, {$arr['userid']} , $dt, $msg, $subject)";
                $achievements_buffer[] = '(' . $arr['userid'] . ', ' . $dt . ', \'Forum Topic Starter LVL4\', \'ftopic4.png\' , \'Started at least 50 topics in the forums.\')';
                $usersachiev_buffer[] = '(' . $arr['userid'] . ',4, ' . $points . ')';
                $cache->increment('inbox_' . $arr['userid']);
            }
            if ($topics >= 75 && $lvl == 4) {
                $msg = sqlesc('Congratulations, you have just earned the [b]Forum Topic Starter Level 5[/b] achievement. :) [img]' . $site_config['pic_baseurl'] . 'achievements/ftopic5.png[/img]');
                $msgs_buffer[] = "(0, {$arr['userid']} , $dt, $msg, $subject)";
                $achievements_buffer[] = '(' . $arr['userid'] . ', ' . $dt . ', \'Forum Topic Starter LVL5\', \'ftopic5.png\' , \'Started at least 75 topics in the forums.\')';
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
            write_log("Achievements Cleanup: Forum Topics Completed using $queries queries. Forum Topics Achievements awarded to - " . $count . ' Member(s)');
        }
        unset($usersachiev_buffer, $achievements_buffer, $msgs_buffer, $count);
    }
}
