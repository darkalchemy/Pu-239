<?php
function docleanup($data)
{
    global $INSTALLER09, $queries, $mc1;
    set_time_limit(0);
    ignore_user_abort(1);
    // *Updated* Daily AJAX Chat Achievements Mod by MelvinMeow
    $res = sql_query("SELECT id, dailyshouts, dailyshoutlvl FROM usersachiev WHERE dailyshouts >= '10'") or sqlerr(__FILE__, __LINE__);
    $msg_buffer = $usersachiev_buffer = $achievements_buffer = [];
    if (mysqli_num_rows($res) > 0) {
        $dt = TIME_NOW;
        $subject = sqlesc('New Achievement Earned!');
        $points = rand(1, 3);
        while ($arr = mysqli_fetch_assoc($res)) {
            $shouts = (int)$arr['dailyshouts'];
            $lvl = (int)$arr['dailyshoutlvl'];
            if ($shouts >= 1 && $lvl == 0) {
                $msg = sqlesc('Congratulations, you have just earned the [b]AJAX Chat Spammer Level 1[/b] achievement. :) [img]' . $INSTALLER09['baseurl'] . '/pic/achievements/spam1.png[/img]');
                $msgs_buffer[] = '(0,' . $arr['id'] . ',' . TIME_NOW . ', ' . sqlesc($msg) . ', ' . sqlesc($subject) . ')';
                $achievements_buffer[] = '(' . $arr['id'] . ', ' . TIME_NOW . ', \'AJAX Chat Spammer LVL1\', \'spam1.png\' , \'Made at least 10 posts to AJAX Chat today.\')';
                $usersachiev_buffer[] = '(' . $arr['id'] . ',1, ' . $points . ')';
                $mc1->delete_value('inbox_new_' . $arr['id']);
                $mc1->delete_value('inbox_new_sb_' . $arr['id']);
                $mc1->delete_value('user_achievement_points_' . $arr['id']);
                $var1 = 'dailyshoutlvl';
            }
            if ($shouts >= 25 && $lvl == 1) {
                $msg = sqlesc('Congratulations, you have just earned the [b]AJAX Chat Spammer Level 2[/b] achievement. :) [img]' . $INSTALLER09['baseurl'] . '/pic/achievements/spam2.png[/img]');
                $msgs_buffer[] = '(0,' . $arr['id'] . ',' . TIME_NOW . ', ' . sqlesc($msg) . ', ' . sqlesc($subject) . ')';
                $achievements_buffer[] = '(' . $arr['id'] . ', ' . TIME_NOW . ', \'AJAX Chat Spammer LVL2\', \'spam2.png\' , \'Made at least 25 posts to AJAX Chat today.\')';
                $usersachiev_buffer[] = '(' . $arr['id'] . ',2, ' . $points . ')';
                $mc1->delete_value('inbox_new_' . $arr['id']);
                $mc1->delete_value('inbox_new_sb_' . $arr['id']);
                $mc1->delete_value('user_achievement_points_' . $arr['id']);
                $var1 = 'dailyshoutlvl';
            }
            if ($shouts >= 50 && $lvl == 2) {
                $msg = sqlesc('Congratulations, you have just earned the [b]AJAX Chat Spammer Level 3[/b] achievement. :) [img]' . $INSTALLER09['baseurl'] . '/pic/achievements/spam3.png[/img]');
                $msgs_buffer[] = '(0,' . $arr['id'] . ',' . TIME_NOW . ', ' . sqlesc($msg) . ', ' . sqlesc($subject) . ')';
                $achievements_buffer[] = '(' . $arr['id'] . ', ' . TIME_NOW . ', \'AJAX Chat Spammer LVL3\', \'spam3.png\' , \'Made at least 50 posts to AJAX Chat today.\')';
                $usersachiev_buffer[] = '(' . $arr['id'] . ',3, ' . $points . ')';
                $mc1->delete_value('inbox_new_' . $arr['id']);
                $mc1->delete_value('inbox_new_sb_' . $arr['id']);
                $mc1->delete_value('user_achievement_points_' . $arr['id']);
                $var1 = 'dailyshoutlvl';
            }
            if ($shouts >= 75 && $lvl == 3) {
                $msg = sqlesc('Congratulations, you have just earned the [b]AJAX Chat Spammer Level 4[/b] achievement. :) [img]' . $INSTALLER09['baseurl'] . '/pic/achievements/spam4.png[/img]');
                $msgs_buffer[] = '(0,' . $arr['id'] . ',' . TIME_NOW . ', ' . sqlesc($msg) . ', ' . sqlesc($subject) . ')';
                $achievements_buffer[] = '(' . $arr['id'] . ', ' . TIME_NOW . ', \'AJAX Chat Spammer LVL4\', \'spam4.png\' , \'Made at least 75 posts to AJAX Chat today.\')';
                $usersachiev_buffer[] = '(' . $arr['id'] . ',4, ' . $points . ')';
                $mc1->delete_value('inbox_new_' . $arr['id']);
                $mc1->delete_value('inbox_new_sb_' . $arr['id']);
                $mc1->delete_value('user_achievement_points_' . $arr['id']);
                $var1 = 'dailyshoutlvl';
            }
            if ($shouts >= 100 && $lvl == 4) {
                $msg = sqlesc('Congratulations, you have just earned the [b]AJAX Chat Spammer Level 5[/b] achievement. :) [img]' . $INSTALLER09['baseurl'] . '/pic/achievements/spam5.png[/img]');
                $msgs_buffer[] = '(0,' . $arr['id'] . ',' . TIME_NOW . ', ' . sqlesc($msg) . ', ' . sqlesc($subject) . ')';
                $achievements_buffer[] = '(' . $arr['id'] . ', ' . TIME_NOW . ', \'AJAX Chat Spammer LVL5\', \'spam5.png\' , \'Made at least 100 posts to AJAX Chat today.\')';
                $usersachiev_buffer[] = '(' . $arr['id'] . ',5, ' . $points . ')';
                $mc1->delete_value('inbox_new_' . $arr['id']);
                $mc1->delete_value('inbox_new_sb_' . $arr['id']);
                $mc1->delete_value('user_achievement_points_' . $arr['id']);
                $var1 = 'dailyshoutlvl';
            }
        }
        $count = count($achievements_buffer);
        if ($count > 0) {
            sql_query('INSERT INTO messages (sender,receiver,added,msg,subject) VALUES ' . implode(', ', $msgs_buffer)) or sqlerr(__FILE__, __LINE__);
            sql_query('INSERT INTO achievements (userid, date, achievement, icon, description) VALUES ' . implode(', ', $achievements_buffer) . ' ON DUPLICATE key UPDATE date=values(date),achievement=values(achievement),icon=values(icon),description=values(description)') or sqlerr(__FILE__, __LINE__);
            sql_query("INSERT INTO usersachiev (id, $var1, achpoints) VALUES " . implode(', ', $usersachiev_buffer) . " ON DUPLICATE key UPDATE $var1=values($var1), achpoints=achpoints+values(achpoints)") or sqlerr(__FILE__, __LINE__);
            if ($queries > 0) {
                write_log("Achievements Cleanup: Achievements Shouter Completed using $queries queries. Shouter Achievements awarded to - " . $count . ' Member(s)');
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

function cleanup_log($data)
{
    $text = sqlesc($data['clean_title']);
    $added = TIME_NOW;
    $ip = sqlesc($_SERVER['REMOTE_ADDR']);
    $desc = sqlesc($data['clean_desc']);
    sql_query("INSERT INTO cleanup_log (clog_event, clog_time, clog_ip, clog_desc) VALUES ($text, $added, $ip, {$desc})") or sqlerr(__FILE__, __LINE__);
}
