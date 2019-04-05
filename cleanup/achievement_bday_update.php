<?php

/**
 * @param $data
 *
 * @throws Exception
 */
function achievement_bday_update($data)
{
    $time_start = microtime(true);
    dbconn();
    global $site_config, $queries, $cache, $message_stuffs;

    set_time_limit(1200);
    ignore_user_abort(true);

    $dt = TIME_NOW;
    $maxdt = ($dt - 86400 * 365); // 1year
    $maxdt2 = ($dt - 86400 * 730); // 2 years
    $maxdt3 = ($dt - 86400 * 1095); // 3 years
    $maxdt4 = ($dt - 86400 * 1460); // 4 years
    $maxdt5 = ($dt - 86400 * 1825); // 5 years
    $maxdt6 = ($dt - 86400 * 2190); // 6 years
    $res = sql_query("SELECT u.id, u.added, a.bday FROM users AS u LEFT JOIN usersachiev AS a ON u.id=a.userid WHERE enabled = 'yes' AND added < $maxdt") or sqlerr(__FILE__, __LINE__);
    $msgs_buffer = $usersachiev_buffer = $achievements_buffer = [];
    if (mysqli_num_rows($res) > 0) {
        $subject = 'New Achievement Earned!';
        $points = random_int(1, 3);
        $var1 = 'bday';
        while ($arr = mysqli_fetch_assoc($res)) {
            $bday = (int) $arr['bday'];
            $added = (int) $arr['added'];
            $msg = '';
            if ($bday === 0 && $added < $maxdt) {
                $msg = 'Congratulations, you have just earned the [b]First Birthday[/b] achievement. :) [img]' . $site_config['paths']['images_baseurl'] . 'achievements/birthday1.png[/img]';
                $achievements_buffer[] = '(' . $arr['id'] . ', ' . $dt . ', \'First Birthday\', \'birthday1.png\' , \'Been a member for at least 1 year.\')';
                $usersachiev_buffer[] = '(' . $arr['id'] . ',1, ' . $points . ')';
            } elseif ($bday === 1 && $added < $maxdt2) {
                $msg = 'Congratulations, you have just earned the [b]Second Birthday[/b] achievement. :) [img]' . $site_config['paths']['images_baseurl'] . 'achievements/birthday2.png[/img]';
                $achievements_buffer[] = '(' . $arr['id'] . ', ' . $dt . ', \'Second Birthday\', \'birthday2.png\' , \'Been a member for a period of at least 2 years.\')';
                $usersachiev_buffer[] = '(' . $arr['id'] . ',2, ' . $points . ')';
            } elseif ($bday === 2 && $added < $maxdt3) {
                $msg = 'Congratulations, you have just earned the [b]Third Birthday[/b] achievement. :) [img]' . $site_config['paths']['images_baseurl'] . 'achievements/birthday3.png[/img]';
                $achievements_buffer[] = '(' . $arr['id'] . ', ' . $dt . ', \'Third Birthday\', \'birthday3.png\' , \'Been a member for a period of at least 3 years.\')';
                $usersachiev_buffer[] = '(' . $arr['id'] . ',3, ' . $points . ')';
            } elseif ($bday === 3 && $added < $maxdt4) {
                $msg = 'Congratulations, you have just earned the [b]Fourth Birthday[/b] achievement. :) [img]' . $site_config['paths']['images_baseurl'] . 'achievements/birthday4.png[/img]';
                $achievements_buffer[] = '(' . $arr['id'] . ', ' . $dt . ', \'Fourth Birthday\', \'birthday4.png\' , \'Been a member for a period of at least 4 years.\')';
                $usersachiev_buffer[] = '(' . $arr['id'] . ',4, ' . $points . ')';
            } elseif ($bday === 4 && $added < $maxdt5) {
                $msg = 'Congratulations, you have just earned the [b]Fifth Birthday[/b] achievement. :) [img]' . $site_config['paths']['images_baseurl'] . 'achievements/birthday5.png[/img]';
                $achievements_buffer[] = '(' . $arr['id'] . ', ' . $dt . ', \'Fifth Birthday\', \'birthday5.png\' , \'Been a member for a period of at least 5 years.\')';
                $usersachiev_buffer[] = '(' . $arr['id'] . ',5, ' . $points . ')';
            } elseif ($bday === 5 && $added < $maxdt6) {
                $msg = 'Congratulations, you have just earned the [b]Sixth Birthday[/b] achievement. :) [img]' . $site_config['paths']['images_baseurl'] . 'achievements/birthday6.png[/img]';
                $achievements_buffer[] = '(' . $arr['id'] . ', ' . $dt . ', \'Sixth Birthday\', \'birthday6.png\' , \'Been a member for a period of at least 6 years.\')';
                $usersachiev_buffer[] = '(' . $arr['id'] . ',6, ' . $points . ')';
            }
            if (!empty($msg)) {
                $msgs_buffer[] = [
                    'sender' => 0,
                    'receiver' => $arr['userid'],
                    'added' => $dt,
                    'msg' => $msg,
                    'subject' => $subject,
                ];
                $cache->delete('user_achievement_points_' . $arr['userid']);
            }
        }
        $count = count($achievements_buffer);
        if ($count > 0) {
            $message_stuffs->insert($msgs_buffer);
            sql_query('INSERT INTO achievements (userid, date, achievement, icon, description) VALUES ' . implode(', ', $achievements_buffer) . ' ON DUPLICATE KEY UPDATE date = VALUES(date),achievement = VALUES(achievement),icon = VALUES(icon),description = VALUES(description)') or sqlerr(__FILE__, __LINE__);
            sql_query("INSERT INTO usersachiev (userid, $var1, achpoints) VALUES " . implode(', ', $usersachiev_buffer) . " ON DUPLICATE KEY UPDATE $var1 = VALUES($var1), achpoints=achpoints + VALUES(achpoints)") or sqlerr(__FILE__, __LINE__);
        }
        $time_end = microtime(true);
        $run_time = $time_end - $time_start;
        $text = " Run time: $run_time seconds";
        echo $text . "\n";
        if ($data['clean_log'] && $queries > 0) {
            write_log("Achievements Cleanup: Birthdays Completed using $queries queries. Birthday Achievements awarded to - " . $count . ' Member(s).' . $text);
        }
        unset($usersachiev_buffer, $achievement_buffer, $msgs_buffer, $count);
    }
}
