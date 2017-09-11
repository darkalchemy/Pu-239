<?php
function achievement_karma_update($data)
{
    global $site_config, $queries, $mc1;
    set_time_limit(1200);
    ignore_user_abort(true);
    // *Updated* Bonus Point Achievements
    $res = sql_query("SELECT u.id, u.username, u.seedbonus, a.bonus FROM users AS u LEFT JOIN usersachiev AS a ON u.id = a.userid WHERE enabled = 'yes' AND u.seedbonus >= '1' AND a.bonus >= '0'") or sqlerr(__FILE__, __LINE__);
    $msg_buffer = $usersachiev_buffer = $achievements_buffer = [];
    if (mysqli_num_rows($res) > 0) {
        $dt = TIME_NOW;
        $points = random_int(1, 3);
        $subject = sqlesc('New Achievement Earned!');
        while ($arr = mysqli_fetch_assoc($res)) {
            $seedbonus = (float)$arr['seedbonus'];
            $lvl = (int)$arr['bonus'];
            if ($seedbonus >= 1 && $lvl == 0) {
                $msg = sqlesc('Congratulations, you have just earned the [b]Bonus Banker LVL1[/b] achievement. :) [img]' . $site_config['baseurl'] . '/images/achievements/bonus1.png[/img]');
                $msgs_buffer[] = '(0,' . $arr['id'] . ',' . TIME_NOW . ', ' . sqlesc($msg) . ', ' . sqlesc($subject) . ')';
                $achievements_buffer[] = '(' . $arr['id'] . ', ' . TIME_NOW . ', \'Bonus Banker LVL1\', \'bonus1.png\' , \'Earned at least 1 bonus point.\')';
                $usersachiev_buffer[] = '(' . $arr['id'] . ',1, ' . $points . ')';
                $mc1->delete_value('inbox_new_' . $arr['id']);
                $mc1->delete_value('inbox_new_sb_' . $arr['id']);
                $mc1->delete_value('user_achievement_points_' . $arr['id']);
                $var1 = 'bonus';
            }
            if ($seedbonus >= 100 && $lvl == 1) {
                $msg = sqlesc('Congratulations, you have just earned the [b]Bonus Banker LVL2[/b] achievement. :) [img]' . $site_config['baseurl'] . '/images/achievements/bonus2.png[/img]');
                $msgs_buffer[] = '(0,' . $arr['id'] . ',' . TIME_NOW . ', ' . sqlesc($msg) . ', ' . sqlesc($subject) . ')';
                $achievements_buffer[] = '(' . $arr['id'] . ', ' . TIME_NOW . ', \'Bonus Banker LVL2\', \'bonus2.png\' , \'Earned at least 100 bonus points.\')';
                $usersachiev_buffer[] = '(' . $arr['id'] . ',2, ' . $points . ')';
                $mc1->delete_value('inbox_new_' . $arr['id']);
                $mc1->delete_value('inbox_new_sb_' . $arr['id']);
                $mc1->delete_value('user_achievement_points_' . $arr['id']);
                $var1 = 'bonus';
            }
            if ($seedbonus >= 500 && $lvl == 2) {
                $msg = sqlesc('Congratulations, you have just earned the [b]Bonus Banker LVL3[/b] achievement. :) [img]' . $site_config['baseurl'] . '/images/achievements/bonus3.png[/img]');
                $msgs_buffer[] = '(0,' . $arr['id'] . ',' . TIME_NOW . ', ' . sqlesc($msg) . ', ' . sqlesc($subject) . ')';
                $achievements_buffer[] = '(' . $arr['id'] . ', ' . TIME_NOW . ', \'Bonus Banker LVL3\', \'bonus3.png\' , \'Earned at least 500 bonus points.\')';
                $usersachiev_buffer[] = '(' . $arr['id'] . ',3, ' . $points . ')';
                $mc1->delete_value('inbox_new_' . $arr['id']);
                $mc1->delete_value('inbox_new_sb_' . $arr['id']);
                $mc1->delete_value('user_achievement_points_' . $arr['id']);
                $var1 = 'bonus';
            }
            if ($seedbonus >= 1000 && $lvl == 3) {
                $msg = sqlesc('Congratulations, you have just earned the [b]Bonus Banker LVL4[/b] achievement. :) [img]' . $site_config['baseurl'] . '/images/achievements/bonus4.png[/img]');
                $msgs_buffer[] = '(0,' . $arr['id'] . ',' . TIME_NOW . ', ' . sqlesc($msg) . ', ' . sqlesc($subject) . ')';
                $achievements_buffer[] = '(' . $arr['id'] . ', ' . TIME_NOW . ', \'Bonus Banker LVL4\', \'bonus4.png\' , \'Earned at least 1000 bonus points.\')';
                $usersachiev_buffer[] = '(' . $arr['id'] . ',4, ' . $points . ')';
                $mc1->delete_value('inbox_new_' . $arr['id']);
                $mc1->delete_value('inbox_new_sb_' . $arr['id']);
                $mc1->delete_value('user_achievement_points_' . $arr['id']);
                $var1 = 'bonus';
            }
            if ($seedbonus >= 2000 && $lvl == 4) {
                $msg = sqlesc('Congratulations, you have just earned the [b]Bonus Banker LVL5[/b] achievement. :) [img]' . $site_config['baseurl'] . '/images/achievements/bonus5.png[/img]');
                $msgs_buffer[] = '(0,' . $arr['id'] . ',' . TIME_NOW . ', ' . sqlesc($msg) . ', ' . sqlesc($subject) . ')';
                $achievements_buffer[] = '(' . $arr['id'] . ', ' . TIME_NOW . ', \'Bonus Banker LVL5\', \'bonus5.png\' , \'Earned at least 2000 bonus points.\')';
                $usersachiev_buffer[] = '(' . $arr['id'] . ',5, ' . $points . ')';
                $mc1->delete_value('inbox_new_' . $arr['id']);
                $mc1->delete_value('inbox_new_sb_' . $arr['id']);
                $mc1->delete_value('user_achievement_points_' . $arr['id']);
                $var1 = 'bonus';
            }
            if ($seedbonus >= 5000 && $lvl == 5) {
                $msg = sqlesc('Congratulations, you have just earned the [b]Bonus Banker LVL6[/b] achievement. :) [img]' . $site_config['baseurl'] . '/images/achievements/bonus6.png[/img]');
                $msgs_buffer[] = '(0,' . $arr['id'] . ',' . TIME_NOW . ', ' . sqlesc($msg) . ', ' . sqlesc($subject) . ')';
                $achievements_buffer[] = '(' . $arr['id'] . ', ' . TIME_NOW . ', \'Bonus Banker LVL6\', \'bonus6.png\' , \'Earned at least 5000 bonus points.\')';
                $usersachiev_buffer[] = '(' . $arr['id'] . ',6, ' . $points . ')';
                $mc1->delete_value('inbox_new_' . $arr['id']);
                $mc1->delete_value('inbox_new_sb_' . $arr['id']);
                $var1 = 'bonus';
            }
            if ($seedbonus >= 10000 && $lvl == 6) {
                $msg = sqlesc('Congratulations, you have just earned the [b]Bonus Banker LVL7[/b] achievement. :) [img]' . $site_config['baseurl'] . '/images/achievements/bonus7.png[/img]');
                $msgs_buffer[] = '(0,' . $arr['id'] . ',' . TIME_NOW . ', ' . sqlesc($msg) . ', ' . sqlesc($subject) . ')';
                $achievements_buffer[] = '(' . $arr['id'] . ', ' . TIME_NOW . ', \'Bonus Banker LVL7\', \'bonus7.png\' , \'Earned at least 10000 bonus points.\')';
                $usersachiev_buffer[] = '(' . $arr['id'] . ',7, ' . $points . ')';
                $mc1->delete_value('inbox_new_' . $arr['id']);
                $mc1->delete_value('inbox_new_sb_' . $arr['id']);
                $mc1->delete_value('user_achievement_points_' . $arr['id']);
                $var1 = 'bonus';
            }
            if ($seedbonus >= 30000 && $lvl == 7) {
                $msg = sqlesc('Congratulations, you have just earned the [b]Bonus Banker LVL8[/b] achievement. :) [img]' . $site_config['baseurl'] . '/images/achievements/bonus8.png[/img]');
                $msgs_buffer[] = '(0,' . $arr['id'] . ',' . TIME_NOW . ', ' . sqlesc($msg) . ', ' . sqlesc($subject) . ')';
                $achievements_buffer[] = '(' . $arr['id'] . ', ' . TIME_NOW . ', \'Bonus Banker LVL8\', \'bonus8.png\' , \'Earned at least 30000 bonus points.\')';
                $usersachiev_buffer[] = '(' . $arr['id'] . ',8, ' . $points . ')';
                $mc1->delete_value('inbox_new_' . $arr['id']);
                $mc1->delete_value('inbox_new_sb_' . $arr['id']);
                $mc1->delete_value('user_achievement_points_' . $arr['id']);
                $var1 = 'bonus';
            }
            if ($seedbonus >= 70000 && $lvl == 8) {
                $msg = sqlesc('Congratulations, you have just earned the [b]Bonus Banker LVL9[/b] achievement. :) [img]' . $site_config['baseurl'] . '/images/achievements/bonus9.png[/img]');
                $msgs_buffer[] = '(0,' . $arr['id'] . ',' . TIME_NOW . ', ' . sqlesc($msg) . ', ' . sqlesc($subject) . ')';
                $achievements_buffer[] = '(' . $arr['id'] . ', ' . TIME_NOW . ', \'Bonus Banker LVL9\', \'bonus9.png\' , \'Earned at least 70000 bonus points.\')';
                $usersachiev_buffer[] = '(' . $arr['id'] . ',9, ' . $points . ')';
                $mc1->delete_value('inbox_new_' . $arr['id']);
                $mc1->delete_value('inbox_new_sb_' . $arr['id']);
                $mc1->delete_value('user_achievement_points_' . $arr['id']);
                $var1 = 'bonus';
            }
            if ($seedbonus >= 100000 && $lvl == 9) {
                $msg = sqlesc('Congratulations, you have just earned the [b]Bonus Banker LVL10[/b] achievement. :) [img]' . $site_config['baseurl'] . '/images/achievements/bonus10.png[/img]');
                $msgs_buffer[] = '(0,' . $arr['id'] . ',' . TIME_NOW . ', ' . sqlesc($msg) . ', ' . sqlesc($subject) . ')';
                $achievements_buffer[] = '(' . $arr['id'] . ', ' . TIME_NOW . ', \'Bonus Banker LVL10\', \'bonus10.png\' , \'Earned at least 100000 bonus points.\')';
                $usersachiev_buffer[] = '(' . $arr['id'] . ',10, ' . $points . ')';
                $mc1->delete_value('inbox_new_' . $arr['id']);
                $mc1->delete_value('inbox_new_sb_' . $arr['id']);
                $var1 = 'bonus';
            }
            if ($seedbonus >= 1000000 && $lvl == 10) {
                $msg = sqlesc('Congratulations, you have just earned the [b]Bonus Banker LVL11[/b] achievement. :) [img]' . $site_config['baseurl'] . '/images/achievements/bonus11.png[/img]');
                $msgs_buffer[] = '(0,' . $arr['id'] . ',' . TIME_NOW . ', ' . sqlesc($msg) . ', ' . sqlesc($subject) . ')';
                $achievements_buffer[] = '(' . $arr['id'] . ', ' . TIME_NOW . ', \'Bonus Banker LVL11\', \'bonus11.png\' , \'Earned at least 1000000 bonus points.\')';
                $usersachiev_buffer[] = '(' . $arr['id'] . ',11, ' . $points . ')';
                $mc1->delete_value('inbox_new_' . $arr['id']);
                $mc1->delete_value('inbox_new_sb_' . $arr['id']);
                $mc1->delete_value('user_achievement_points_' . $arr['id']);
                $var1 = 'bonus';
            }
        }
        $count = count($achievements_buffer);
        if ($count > 0) {
            sql_query('INSERT INTO messages (sender,receiver,added,msg,subject) VALUES ' . implode(', ', $msgs_buffer)) or sqlerr(__FILE__, __LINE__);
            sql_query('INSERT INTO achievements (userid, date, achievement, icon, description) VALUES ' . implode(', ', $achievements_buffer) . ' ON DUPLICATE key UPDATE date=values(date),achievement=values(achievement),icon=values(icon),description=values(description)') or sqlerr(__FILE__, __LINE__);
            sql_query("INSERT INTO usersachiev (userid, $var1, achpoints) VALUES " . implode(', ', $usersachiev_buffer) . " ON DUPLICATE key UPDATE $var1=values($var1), achpoints=achpoints+values(achpoints)") or sqlerr(__FILE__, __LINE__);
            if ($queries > 0) {
                write_log("Achievements Cleanup: Karma Completed using $queries queries. Karma Achievements awarded to - " . $count . ' Member(s)');
            }
        }
        unset($usersachiev_buffer, $achievements_buffer, $msgs_buffer, $count);
    }
    if (false !== mysqli_affected_rows($GLOBALS['___mysqli_ston'])) {
        $data['clean_desc'] = mysqli_affected_rows($GLOBALS['___mysqli_ston']) . ' items updated';
    }
    if ($data['clean_log']) {
        cleanup_log($data);
    }
}
