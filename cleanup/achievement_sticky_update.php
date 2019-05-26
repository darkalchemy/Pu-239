<?php

declare(strict_types = 1);

use Pu239\Cache;
use Pu239\Message;

/**
 * @param $data
 *
 * @throws Exception
 */
function achievement_sticky_update($data)
{
    global $container, $site_config;

    $time_start = microtime(true);
    $res = sql_query('SELECT userid, stickyup, stickyachiev FROM usersachiev WHERE stickyup >= 1') or sqlerr(__FILE__, __LINE__);
    $msgs_buffer = $usersachiev_buffer = $achievements_buffer = [];
    if (mysqli_num_rows($res) > 0) {
        $dt = TIME_NOW;
        $subject = 'New Achievement Earned!';
        $points = random_int(1, 3);
        $var1 = 'stickyachiev';
        $cache = $container->get(Cache::class);
        while ($arr = mysqli_fetch_assoc($res)) {
            $stickyup = (int) $arr['stickyup'];
            $lvl = (int) $arr['stickyachiev'];
            $msg = '';
            if ($stickyup >= 1 && $lvl === 0) {
                $msg = 'Congratulations, you have just earned the [b]Stick Em Up LVL1[/b] achievement. :) [img]' . $site_config['paths']['images_baseurl'] . 'achievements/sticky1.png[/img]';
                $achievements_buffer[] = '(' . $arr['userid'] . ', ' . $dt . ', \'Stick Em Up LVL1\', \'sticky1.png\' , \'Uploading at least 1 sticky torrent to the site.\')';
                $usersachiev_buffer[] = '(' . $arr['userid'] . ',1, ' . $points . ')';
            } elseif ($stickyup >= 5 && $lvl === 1) {
                $msg = 'Congratulations, you have just earned the [b]Stick Em Up LVL2[/b] achievement. :) [img]' . $site_config['paths']['images_baseurl'] . 'achievements/sticky2.png[/img]';
                $achievements_buffer[] = '(' . $arr['userid'] . ', ' . $dt . ', \'Stick Em Up LVL2\', \'sticky2.png\' , \'Uploading at least 5 sticky torrents to the site.\')';
                $usersachiev_buffer[] = '(' . $arr['userid'] . ',2, ' . $points . ')';
            } elseif ($stickyup >= 10 && $lvl === 2) {
                $msg = 'Congratulations, you have just earned the [b]Stick Em Up LVL3[/b] achievement. :) [img]' . $site_config['paths']['images_baseurl'] . 'achievements/sticky3.png[/img]';
                $achievements_buffer[] = '(' . $arr['userid'] . ', ' . $dt . ', \'Stick Em Up LVL3\', \'sticky3.png\' , \'Uploading at least 10 sticky torrents to the site.\')';
                $usersachiev_buffer[] = '(' . $arr['userid'] . ',3, ' . $points . ')';
            } elseif ($stickyup >= 25 && $lvl === 3) {
                $msg = 'Congratulations, you have just earned the [b]Stick Em Up LVL4[/b] achievement. :) [img]' . $site_config['paths']['images_baseurl'] . 'achievements/sticky4.png[/img]';
                $achievements_buffer[] = '(' . $arr['userid'] . ', ' . $dt . ', \'Stick Em Up LVL4\', \'sticky4.png\' , \'Uploading at least 25 sticky torrents to the site.\')';
                $usersachiev_buffer[] = '(' . $arr['userid'] . ',4, ' . $points . ')';
            } elseif ($stickyup >= 50 && $lvl === 4) {
                $msg = 'Congratulations, you have just earned the [b]Stick Em Up LVL5[/b] achievement. :) [img]' . $site_config['paths']['images_baseurl'] . 'achievements/sticky5.png[/img]';
                $achievements_buffer[] = '(' . $arr['userid'] . ', ' . $dt . ', \'Stick Em Up LVL5\', \'sticky5.png\' , \'Uploading at least 50 sticky torrents to the site.\')';
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
            }
            $cache->delete('user_achievement_points_' . $arr['userid']);
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
            write_log('Achievements Cleanup: Stickied Completed. Stickied Achievements awarded to - ' . $count . ' Member(s).' . $text);
        }
        unset($usersachiev_buffer, $achievements_buffer, $msgs_buffer, $count);
    }
}
