<?php

declare(strict_types = 1);

use Pu239\Cache;
use Pu239\Message;

/**
 * @param $data
 *
 * @throws Exception
 */
function achievement_avatar_update($data)
{
    global $container, $site_config;

    $cache = $container->get(Cache::class);
    $time_start = microtime(true);
    $res = sql_query('SELECT userid, avatarset FROM usersachiev WHERE avatarset = 1 AND avatarach = 0') or sqlerr(__FILE__, __LINE__);
    $msgs_buffer = $usersachiev_buffer = $achievements_buffer = [];
    if (mysqli_num_rows($res) > 0) {
        $subject = 'New Achievement Earned!';
        $msg = 'Congratulations, you have just earned the [b]Avatar Setter[/b] achievement. :) [img]' . $site_config['paths']['images_baseurl'] . 'achievements/piratesheep.png[/img]';
        $dt = TIME_NOW;
        while ($arr = mysqli_fetch_assoc($res)) {
            $points = random_int(1, 3);
            $msgs_buffer[] = [
                'receiver' => $arr['userid'],
                'added' => $dt,
                'msg' => $msg,
                'subject' => $subject,
            ];
            $achievements_buffer[] = '(' . $arr['userid'] . ', ' . $dt . ', \'Avatar Setter\', \'piratesheep.png\' , \'User has successfully set an avatar on profile settings.\')';
            $usersachiev_buffer[] = '(' . $arr['userid'] . ',1, ' . $points . ')';
            $cache->delete('user_' . $arr['userid']);
        }
        $count = count($achievements_buffer);
        if ($count > 0) {
            $messages_class = $container->get(Message::class);
            $messages_class->insert($msgs_buffer);
            sql_query('INSERT INTO achievements (userid, date, achievement, icon, description) VALUES ' . implode(', ', $achievements_buffer) . ' ON DUPLICATE KEY UPDATE date = VALUES(date),achievement = VALUES(achievement),icon = VALUES(icon),description = VALUES(description)') or sqlerr(__FILE__, __LINE__);
            sql_query('INSERT INTO usersachiev (userid, avatarach, achpoints) VALUES ' . implode(', ', $usersachiev_buffer) . ' ON DUPLICATE KEY UPDATE avatarach = VALUES(avatarach), achpoints=achpoints + VALUES(achpoints)') or sqlerr(__FILE__, __LINE__);
        }
        $time_end = microtime(true);
        $run_time = $time_end - $time_start;
        $text = " Run time: $run_time seconds";
        echo $text . "\n";
        if ($data['clean_log']) {
            write_log('Achievements Cleanup: Avatar Setter Completed. Avatar Achievements awarded to - ' . $count . ' Member(s).' . $text);
        }
    }
}
