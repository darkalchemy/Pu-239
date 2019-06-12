<?php

declare(strict_types = 1);

use Pu239\Cache;
use Pu239\Message;

/**
 * @param $data
 *
 * @throws Exception
 */
function achievement_request_update($data)
{
    global $container, $site_config;

    $time_start = microtime(true);
    $res = sql_query('SELECT userid, reqfilled, reqlvl FROM usersachiev WHERE reqfilled >= 1') or sqlerr(__FILE__, __LINE__);
    $msgs_buffer = $usersachiev_buffer = $achievements_buffer = [];
    $cache = $container->get(Cache::class);
    if (mysqli_num_rows($res) > 0) {
        $dt = TIME_NOW;
        $subject = 'New Achievement Earned!';
        $points = random_int(1, 3);
        $var1 = 'reqlvl';
        while ($arr = mysqli_fetch_assoc($res)) {
            $reqfilled = (int) $arr['reqfilled'];
            $lvl = (int) $arr['reqlvl'];
            $msg = '';
            if ($reqfilled >= 1 && $lvl == 0) {
                $msg = 'Congratulations, you have just earned the [b]Request Filler LVL1[/b] achievement. :) [img]' . $site_config['paths']['images_baseurl'] . 'achievements/reqfiller1.png[/img]';
                $achievements_buffer[] = '(' . $arr['userid'] . ', ' . $dt . ', \'Request Filler LVL1\', \'reqfiller1.png\' , \'Filled at least 1 request from the request page.\')';
                $usersachiev_buffer[] = '(' . $arr['userid'] . ',1, ' . $points . ')';
            } elseif ($reqfilled >= 5 && $lvl == 1) {
                $msg = 'Congratulations, you have just earned the [b]Request Filler LVL2[/b] achievement. :) [img]' . $site_config['paths']['images_baseurl'] . 'achievements/reqfiller2.png[/img]';
                $achievements_buffer[] = '(' . $arr['userid'] . ', ' . $dt . ', \'Request Filler LVL2\', \'reqfiller2.png\' , \'Filled at least 5 requests from the request page.\')';
                $usersachiev_buffer[] = '(' . $arr['userid'] . ',2, ' . $points . ')';
            } elseif ($reqfilled >= 10 && $lvl == 2) {
                $msg = 'Congratulations, you have just earned the [b]Request Filler LVL3[/b] achievement. :) [img]' . $site_config['paths']['images_baseurl'] . 'achievements/reqfiller3.png[/img]';
                $achievements_buffer[] = '(' . $arr['userid'] . ', ' . $dt . ', \'Request Filler LVL3\', \'reqfiller3.png\' , \'Filled at least 10 requests from the request page.\')';
                $usersachiev_buffer[] = '(' . $arr['userid'] . ',3, ' . $points . ')';
            } elseif ($reqfilled >= 25 && $lvl == 3) {
                $msg = 'Congratulations, you have just earned the [b]Request Filler LVL4[/b] achievement. :) [img]' . $site_config['paths']['images_baseurl'] . 'achievements/reqfiller4.png[/img]';
                $achievements_buffer[] = '(' . $arr['userid'] . ', ' . $dt . ', \'Request Filler LVL4\', \'reqfiller4.png\' , \'Filled at least 25 requests from the request page.\')';
                $usersachiev_buffer[] = '(' . $arr['userid'] . ',4, ' . $points . ')';
            } elseif ($reqfilled >= 50 && $lvl == 4) {
                $msg = 'Congratulations, you have just earned the [b]Request Filler LVL5[/b] achievement. :) [img]' . $site_config['paths']['images_baseurl'] . 'achievements/reqfiller5.png[/img]';
                $achievements_buffer[] = '(' . $arr['userid'] . ', ' . $dt . ', \'Request Filler LVL5\', \'reqfiller5.png\' , \'Filled at least 50 requests from the request page.\')';
                $usersachiev_buffer[] = '(' . $arr['userid'] . ',5, ' . $points . ')';
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
            $messages_class = $container->get(Message::class);
            $messages_class->insert($msgs_buffer);
            sql_query('INSERT INTO achievements (userid, date, achievement, icon, description) VALUES ' . implode(', ', $achievements_buffer) . ' ON DUPLICATE KEY UPDATE date = VALUES(date),achievement = VALUES(achievement),icon = VALUES(icon),description = VALUES(description)') or sqlerr(__FILE__, __LINE__);
            sql_query("INSERT INTO usersachiev (userid, $var1, achpoints) VALUES " . implode(', ', $usersachiev_buffer) . " ON DUPLICATE KEY UPDATE $var1 = VALUES($var1), achpoints=achpoints + VALUES(achpoints)") or sqlerr(__FILE__, __LINE__);
        }
        $time_end = microtime(true);
        $run_time = $time_end - $time_start;
        $text = " Run time: $run_time seconds";
        echo $text . "\n";
        if ($data['clean_log']) {
            write_log('Achievements Cleanup: Request Filler Completed. Request Filler Achievements awarded to - ' . $count . ' Member(s).' . $text);
        }
        unset($usersachiev_buffer, $achievements_buffer, $msgs_buffer, $count);
    }
}
