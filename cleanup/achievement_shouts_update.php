<?php

declare(strict_types = 1);

use Pu239\Cache;
use Pu239\Message;

/**
 * @param $data
 *
 * @throws Exception
 */
function achievement_shouts_update($data)
{
    global $container, $site_config;

    $time_start = microtime(true);
    $res = sql_query('SELECT userid, dailyshouts, dailyshoutlvl FROM usersachiev WHERE dailyshouts >= 10') or sqlerr(__FILE__, __LINE__);
    $msgs_buffer = $usersachiev_buffer = $achievements_buffer = [];
    if (mysqli_num_rows($res) > 0) {
        $dt = TIME_NOW;
        $subject = 'New Achievement Earned!';
        $points = random_int(1, 3);
        $var1 = 'dailyshoutlvl';
        $cache = $container->get(Cache::class);
        while ($arr = mysqli_fetch_assoc($res)) {
            $shouts = (int) $arr['dailyshouts'];
            $lvl = (int) $arr['dailyshoutlvl'];
            $msg = '';
            if ($shouts >= 1 && $lvl === 0) {
                $msg = 'Congratulations, you have just earned the [b]AJAX Chat Spammer Level 1[/b] achievement. :) [img]' . $site_config['paths']['images_baseurl'] . 'achievements/spam1.png[/img]';
                $achievements_buffer[] = '(' . $arr['userid'] . ', ' . $dt . ', \'AJAX Chat Spammer LVL1\', \'spam1.png\' , \'Made at least 10 posts to AJAX Chat today.\')';
                $usersachiev_buffer[] = '(' . $arr['userid'] . ',1, ' . $points . ')';
            } elseif ($shouts >= 25 && $lvl === 1) {
                $msg = 'Congratulations, you have just earned the [b]AJAX Chat Spammer Level 2[/b] achievement. :) [img]' . $site_config['paths']['images_baseurl'] . 'achievements/spam2.png[/img]';
                $achievements_buffer[] = '(' . $arr['userid'] . ', ' . $dt . ', \'AJAX Chat Spammer LVL2\', \'spam2.png\' , \'Made at least 25 posts to AJAX Chat today.\')';
                $usersachiev_buffer[] = '(' . $arr['userid'] . ',2, ' . $points . ')';
            } elseif ($shouts >= 50 && $lvl === 2) {
                $msg = 'Congratulations, you have just earned the [b]AJAX Chat Spammer Level 3[/b] achievement. :) [img]' . $site_config['paths']['images_baseurl'] . 'achievements/spam3.png[/img]';
                $achievements_buffer[] = '(' . $arr['userid'] . ', ' . $dt . ', \'AJAX Chat Spammer LVL3\', \'spam3.png\' , \'Made at least 50 posts to AJAX Chat today.\')';
                $usersachiev_buffer[] = '(' . $arr['userid'] . ',3, ' . $points . ')';
            } elseif ($shouts >= 75 && $lvl === 3) {
                $msg = 'Congratulations, you have just earned the [b]AJAX Chat Spammer Level 4[/b] achievement. :) [img]' . $site_config['paths']['images_baseurl'] . 'achievements/spam4.png[/img]';
                $achievements_buffer[] = '(' . $arr['userid'] . ', ' . $dt . ', \'AJAX Chat Spammer LVL4\', \'spam4.png\' , \'Made at least 75 posts to AJAX Chat today.\')';
                $usersachiev_buffer[] = '(' . $arr['userid'] . ',4, ' . $points . ')';
            } elseif ($shouts >= 100 && $lvl === 4) {
                $msg = 'Congratulations, you have just earned the [b]AJAX Chat Spammer Level 5[/b] achievement. :) [img]' . $site_config['paths']['images_baseurl'] . 'achievements/spam5.png[/img]';
                $achievements_buffer[] = '(' . $arr['userid'] . ', ' . $dt . ', \'AJAX Chat Spammer LVL5\', \'spam5.png\' , \'Made at least 100 posts to AJAX Chat today.\')';
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
            $message_stuffs = $container->get(Message::class);
            $message_stuffs->insert($msgs_buffer);
            sql_query('INSERT INTO achievements (userid, date, achievement, icon, description) VALUES ' . implode(', ', $achievements_buffer) . ' ON DUPLICATE KEY UPDATE date = VALUES(date),achievement = VALUES(achievement),icon = VALUES(icon),description = VALUES(description)') or sqlerr(__FILE__, __LINE__);
            sql_query("INSERT INTO usersachiev (userid, $var1, achpoints) VALUES " . implode(', ', $usersachiev_buffer) . " ON DUPLICATE KEY UPDATE $var1 = VALUES($var1), achpoints=achpoints + VALUES(achpoints)") or sqlerr(__FILE__, __LINE__);
        }
        $time_end = microtime(true);
        $run_time = $time_end - $time_start;
        $text = " Run time: $run_time seconds";
        echo $text . "\n";
        if ($data['clean_log']) {
            write_log('Achievements Cleanup: Shouter Completed. Shouter Achievements awarded to - ' . $count . ' Member(s).' . $text);
        }
        unset($usersachiev_buffer, $achievements_buffer, $msgs_buffer, $count);
    }
}
