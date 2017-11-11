<?php
/**
 * @param $data
 */
function achievement_fpost_update($data)
{
    global $site_config, $queries, $mc1;
    set_time_limit(1200);
    ignore_user_abort(true);
    // *Updated* Forum Post Achievements Mod by MelvinMeow
    $res = sql_query("SELECT userid, forumposts, postachiev FROM usersachiev WHERE forumposts >= 1") or sqlerr(__FILE__, __LINE__);
    $msg_buffer = $usersachiev_buffer = $achievements_buffer = [];
    if (mysqli_num_rows($res) > 0) {
        $dt = TIME_NOW;
        $subject = sqlesc('New Achievement Earned!');
        $points = random_int(1, 3);
        while ($arr = mysqli_fetch_assoc($res)) {
            $posts = (int)$arr['forumposts'];
            $lvl = (int)$arr['postachiev'];
            if ($posts >= 1 && $lvl == 0) {
                $msg = sqlesc('Congratulations, you have just earned the [b]Forum Poster Level 1[/b] achievement. :) [img]' . $site_config['baseurl'] . '/images/achievements/fpost1.png[/img]');
                $msgs_buffer[] = '(0,' . $arr['userid'] . ',' . TIME_NOW . ', ' . sqlesc($msg) . ', ' . sqlesc($subject) . ')';
                $achievements_buffer[] = '(' . $arr['userid'] . ', ' . TIME_NOW . ', \'Forum Poster LVL1\', \'fpost1.png\' , \'Made at least 1 post in the forums.\')';
                $usersachiev_buffer[] = '(' . $arr['userid'] . ',1, ' . $points . ')';
                $mc1->delete_value('inbox_new_' . $arr['userid']);
                $mc1->delete_value('inbox_new_sb_' . $arr['userid']);
                $mc1->delete_value('user_achievement_points_' . $arr['userid']);
                $var1 = 'postachiev';
            }
            if ($posts >= 25 && $lvl == 1) {
                $msg = sqlesc('Congratulations, you have just earned the [b]Forum Poster Level 2[/b] achievement. :) [img]' . $site_config['baseurl'] . '/images/achievements/fpost2.png[/img]');
                $msgs_buffer[] = '(0,' . $arr['userid'] . ',' . TIME_NOW . ', ' . sqlesc($msg) . ', ' . sqlesc($subject) . ')';
                $achievements_buffer[] = '(' . $arr['userid'] . ', ' . TIME_NOW . ', \'Forum Poster LVL2\', \'fpost2.png\' , \'Made at least 25 posts in the forums.\')';
                $usersachiev_buffer[] = '(' . $arr['userid'] . ',2, ' . $points . ')';
                $mc1->delete_value('inbox_new_' . $arr['userid']);
                $mc1->delete_value('inbox_new_sb_' . $arr['userid']);
                $mc1->delete_value('user_achievement_points_' . $arr['userid']);
                $var1 = 'postachiev';
            }
            if ($posts >= 50 && $lvl == 2) {
                $msg = sqlesc('Congratulations, you have just earned the [b]Forum Poster Level 3[/b] achievement. :) [img]' . $site_config['baseurl'] . '/images/achievements/fpost3.png[/img]');
                $msgs_buffer[] = '(0,' . $arr['userid'] . ',' . TIME_NOW . ', ' . sqlesc($msg) . ', ' . sqlesc($subject) . ')';
                $achievements_buffer[] = '(' . $arr['userid'] . ', ' . TIME_NOW . ', \'Forum Poster LVL3\', \'fpost3.png\' , \'Made at least 50 posts in the forums.\')';
                $usersachiev_buffer[] = '(' . $arr['userid'] . ',3, ' . $points . ')';
                $mc1->delete_value('inbox_new_' . $arr['userid']);
                $mc1->delete_value('inbox_new_sb_' . $arr['userid']);
                $mc1->delete_value('user_achievement_points_' . $arr['userid']);
                $var1 = 'postachiev';
            }
            if ($posts >= 100 && $lvl == 3) {
                $msg = sqlesc('Congratulations, you have just earned the [b]Forum Poster Level 4[/b] achievement. :) [img]' . $site_config['baseurl'] . '/images/achievements/fpost4.png[/img]');
                $msgs_buffer[] = '(0,' . $arr['userid'] . ',' . TIME_NOW . ', ' . sqlesc($msg) . ', ' . sqlesc($subject) . ')';
                $achievements_buffer[] = '(' . $arr['userid'] . ', ' . TIME_NOW . ', \'Forum Poster LVL4\', \'fpost4.png\' , \'Made at least 100 posts in the forums.\')';
                $usersachiev_buffer[] = '(' . $arr['userid'] . ',4, ' . $points . ')';
                $mc1->delete_value('inbox_new_' . $arr['userid']);
                $mc1->delete_value('inbox_new_sb_' . $arr['userid']);
                $var1 = 'postachiev';
            }
            if ($posts >= 250 && $lvl == 4) {
                $msg = sqlesc('Congratulations, you have just earned the [b]Forum Poster Level 5[/b] achievement. :) [img]' . $site_config['baseurl'] . '/images/achievements/fpost5.png[/img]');
                $msgs_buffer[] = '(0,' . $arr['userid'] . ',' . TIME_NOW . ', ' . sqlesc($msg) . ', ' . sqlesc($subject) . ')';
                $achievements_buffer[] = '(' . $arr['userid'] . ', ' . TIME_NOW . ', \'Forum Poster LVL5\', \'fpost5.png\' , \'Made at least 250 posts in the forums.\')';
                $usersachiev_buffer[] = '(' . $arr['userid'] . ',5, ' . $points . ')';
                $mc1->delete_value('inbox_new_' . $arr['userid']);
                $mc1->delete_value('inbox_new_sb_' . $arr['userid']);
                $mc1->delete_value('user_achievement_points_' . $arr['userid']);
                $var1 = 'postachiev';
            }
            if ($posts >= 500 && $lvl == 5) {
                $msg = sqlesc('Congratulations, you have just earned the [b]Forum Poster Level 6[/b] achievement. :) [img]' . $site_config['baseurl'] . '/images/achievements/fpost6.png[/img]');
                $msgs_buffer[] = '(0,' . $arr['userid'] . ',' . TIME_NOW . ', ' . sqlesc($msg) . ', ' . sqlesc($subject) . ')';
                $achievements_buffer[] = '(' . $arr['userid'] . ', ' . TIME_NOW . ', \'Forum Poster LVL6\', \'fpost6.png\' , \'Made at least 500 posts in the forums.\')';
                $usersachiev_buffer[] = '(' . $arr['userid'] . ',6, ' . $points . ')';
                $mc1->delete_value('inbox_new_' . $arr['userid']);
                $mc1->delete_value('inbox_new_sb_' . $arr['userid']);
                $var1 = 'postachiev';
            }
            if ($posts >= 750 && $lvl == 6) {
                $msg = sqlesc('Congratulations, you have just earned the [b]Forum Poster Level 7[/b] achievement. :) [img]' . $site_config['baseurl'] . '/images/achievements/fpost7.png[/img]');
                $msgs_buffer[] = '(0,' . $arr['userid'] . ',' . TIME_NOW . ', ' . sqlesc($msg) . ', ' . sqlesc($subject) . ')';
                $achievements_buffer[] = '(' . $arr['userid'] . ', ' . TIME_NOW . ', \'Forum Poster LVL7\', \'fpost7.png\' , \'Made at least 750 posts in the forums.\')';
                $usersachiev_buffer[] = '(' . $arr['userid'] . ',7, ' . $points . ')';
                $mc1->delete_value('inbox_new_' . $arr['userid']);
                $mc1->delete_value('inbox_new_sb_' . $arr['userid']);
                $mc1->delete_value('user_achievement_points_' . $arr['userid']);
                $var1 = 'postachiev';
            }
        }
        $count = count($achievements_buffer);
        if ($count > 0) {
            sql_query('INSERT INTO messages (sender,receiver,added,msg,subject) VALUES ' . implode(', ', $msgs_buffer)) or sqlerr(__FILE__, __LINE__);
            sql_query('INSERT INTO achievements (userid, date, achievement, icon, description) VALUES ' . implode(', ', $achievements_buffer) . ' ON DUPLICATE key UPDATE date=values(date),achievement=values(achievement),icon=values(icon),description=values(description)') or sqlerr(__FILE__, __LINE__);
            sql_query("INSERT INTO usersachiev (userid, $var1, achpoints) VALUES " . implode(', ', $usersachiev_buffer) . " ON DUPLICATE key UPDATE $var1=values($var1), achpoints=achpoints+values(achpoints)") or sqlerr(__FILE__, __LINE__);
        }
        if ($data['clean_log'] && $queries > 0) {
            write_log("Achievements Cleanup: Forum Posts Completed using $queries queries. Forum Posts Achievements awarded to - " . $count . ' Member(s)');
        }
        unset($usersachiev_buffer, $achievements_buffer, $msgs_buffer, $count);
    }
}
