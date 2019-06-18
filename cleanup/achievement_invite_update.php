<?php

declare(strict_types = 1);

use Pu239\Cache;
use Pu239\Message;

/**
 * @param $data
 *
 * @throws Exception
 */
function achievement_invite_update($data)
{
    global $container, $site_config;

    $time_start = microtime(true);
    $res = sql_query('SELECT userid, invited, inviterach FROM usersachiev WHERE invited >= 1') or sqlerr(__FILE__, __LINE__);
    $msgs_buffer = $usersachiev_buffer = $achievements_buffer = [];
    if (mysqli_num_rows($res) > 0) {
        $dt = TIME_NOW;
        $subject = 'New Achievement Earned!';
        $points = random_int(1, 3);
        $var1 = 'inviterach';
        $cache = $container->get(Cache::class);
        while ($arr = mysqli_fetch_assoc($res)) {
            $invited = (int) $arr['invited'];
            $lvl = (int) $arr['inviterach'];
            $msg = '';
            if ($invited >= 1 && $lvl === 0) {
                $msg = 'Congratulations, you have just earned the [b]Inviter Level 1[/b] achievement. :) [img]' . $site_config['paths']['images_baseurl'] . 'achievements/invite1.png[/img]';
                $achievements_buffer[] = '(' . $arr['userid'] . ', ' . $dt . ', \'Inviter LVL1\', \'invite1.png\' , \'Invited at least 1 new user to the site.\')';
                $usersachiev_buffer[] = '(' . $arr['userid'] . ',1, ' . $points . ')';
            } elseif ($invited >= 2 && $lvl === 1) {
                $msg = 'Congratulations, you have just earned the [b]Inviter Level 2[/b] achievement. :) [img]' . $site_config['paths']['images_baseurl'] . 'achievements/invite2.png[/img]';
                $achievements_buffer[] = '(' . $arr['userid'] . ', ' . $dt . ', \'Inviter LVL2\', \'invite2.png\' , \'Invited at least 2 new users to the site.\')';
                $usersachiev_buffer[] = '(' . $arr['userid'] . ',2, ' . $points . ')';
            } elseif ($invited >= 3 && $lvl === 2) {
                $msg = 'Congratulations, you have just earned the [b]Inviter Level 3[/b] achievement. :) [img]' . $site_config['paths']['images_baseurl'] . 'achievements/invite3.png[/img]';
                $achievements_buffer[] = '(' . $arr['userid'] . ', ' . $dt . ', \'Inviter LVL3\', \'invite3.png\' , \'Invited at least 3 new users to the site.\')';
                $usersachiev_buffer[] = '(' . $arr['userid'] . ',3, ' . $points . ')';
            } elseif ($invited >= 5 && $lvl === 3) {
                $msg = 'Congratulations, you have just earned the [b]Inviter Level 4[/b] achievement. :) [img]' . $site_config['paths']['images_baseurl'] . 'achievements/invite4.png[/img]';
                $achievements_buffer[] = '(' . $arr['userid'] . ', ' . $dt . ', \'Inviter LVL4\', \'invite4.png\' , \'Invited at least 5 new users to the site.\')';
                $usersachiev_buffer[] = '(' . $arr['userid'] . ',4, ' . $points . ')';
            } elseif ($invited >= 10 && $lvl === 4) {
                $msg = 'Congratulations, you have just earned the [b]Inviter Level 5[/b] achievement. :) [img]' . $site_config['paths']['images_baseurl'] . 'achievements/invite5.png[/img]';
                $achievements_buffer[] = '(' . $arr['userid'] . ', ' . $dt . ', \'Inviter LVL5\', \'invite5.png\' , \'Invited at least 10 new users to the site.\')';
                $usersachiev_buffer[] = '(' . $arr['userid'] . ',5, ' . $points . ')';
            }
            if (!empty($msg)) {
                $msgs_buffer[] = [
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
            write_log('Achievements Cleanup: Inviter Completed. Inviter Achievements awarded to - ' . $count . ' Member(s).' . $text);
        }
        unset($usersachiev_buffer, $achievements_buffer, $msgs_buffer, $count);
    }
}
