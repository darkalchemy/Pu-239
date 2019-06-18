<?php

declare(strict_types = 1);

use Pu239\Cache;
use Pu239\Message;

/**
 * @param $data
 *
 * @throws Exception
 */
function achievement_sheep_update($data)
{
    global $container, $site_config;

    $time_start = microtime(true);
    $res = sql_query('SELECT userid, sheepyset FROM usersachiev WHERE sheepyset = 1 AND sheepyach = 0') or sqlerr(__FILE__, __LINE__);
    $msgs_buffer = $usersachiev_buffer = $achievements_buffer = [];
    if (mysqli_num_rows($res) > 0) {
        $subject = 'New Achievement Earned!';
        $msg = 'Congratulations, you have just earned the [b]Sheep Fondler[/b] achievement. :) [img]' . $site_config['paths']['images_baseurl'] . 'achievements/sheepfondler.png[/img]';
        $dt = TIME_NOW;
        $cache = $container->get(Cache::class);
        while ($arr = mysqli_fetch_assoc($res)) {
            $points = random_int(1, 3);
            $msgs_buffer[] = [
                'receiver' => $arr['userid'],
                'added' => $dt,
                'msg' => $msg,
                'subject' => $subject,
            ];
            $achievements_buffer[] = '(' . $arr['userid'] . ', ' . $dt . ', \'Sheep Fondler\', \'sheepfondler.png\' , \'User has been caught touching the sheep at least 1 time.\')';
            $usersachiev_buffer[] = '(' . $arr['userid'] . ',1, ' . $points . ')';
            $cache->delete('user_achievement_points_' . $arr['userid']);
        }
        $count = count($achievements_buffer);
        if ($count > 0) {
            $messages_class = $container->get(Message::class);
            $messages_class->insert($msgs_buffer);
            sql_query('INSERT INTO achievements (userid, date, achievement, icon, description) VALUES ' . implode(', ', $achievements_buffer) . ' ON DUPLICATE KEY UPDATE date = VALUES(date),achievement = VALUES(achievement),icon = VALUES(icon),description = VALUES(description)') or sqlerr(__FILE__, __LINE__);
            sql_query('INSERT INTO usersachiev (userid, sheepyach, achpoints) VALUES ' . implode(', ', $usersachiev_buffer) . ' ON DUPLICATE KEY UPDATE sheepyach = VALUES(sheepyach), achpoints=achpoints + VALUES(achpoints)') or sqlerr(__FILE__, __LINE__);
        }
        $time_end = microtime(true);
        $run_time = $time_end - $time_start;
        $text = " Run time: $run_time seconds";
        echo $text . "\n";
        if ($data['clean_log']) {
            write_log('Achievements Cleanup: Sheep Fondler Completed. Sheep Fondling Achievements awarded to - ' . $count . ' Member(s).' . $text);
        }
        unset($usersachiev_buffer, $achievement_buffer, $msgs_buffer, $count);
    }
}
