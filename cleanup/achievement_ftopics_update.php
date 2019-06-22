<?php

declare(strict_types = 1);

use Pu239\Cache;
use Pu239\Message;

/**
 * @param $data
 *
 * @throws Exception
 */
function achievement_ftopics_update($data)
{
    global $container, $site_config;

    $time_start = microtime(true);
    $res = sql_query('SELECT userid, forumtopics, topicachiev FROM usersachiev WHERE forumtopics >= 1') or sqlerr(__FILE__, __LINE__);
    $msgs_buffer = $usersachiev_buffer = $achievements_buffer = [];
    if (mysqli_num_rows($res) > 0) {
        $dt = TIME_NOW;
        $subject = 'New Achievement Earned!';
        $points = random_int(1, 3);
        $var1 = 'topicachiev';
        $cache = $container->get(Cache::class);
        while ($arr = mysqli_fetch_assoc($res)) {
            $topics = (int) $arr['forumtopics'];
            $lvl = (int) $arr['topicachiev'];
            $msg = '';
            if ($topics >= 1 && $lvl === 0) {
                $msg = 'Congratulations, you have just earned the [b]Forum Topic Starter Level 1[/b] achievement. :) [img]' . $site_config['paths']['images_baseurl'] . 'achievements/ftopic1.png[/img]';
                $achievements_buffer[] = '(' . $arr['userid'] . ', ' . $dt . ', \'Forum Topic Starter LVL1\', \'ftopic1.png\' , \'Started at least 1 topic in the forums.\')';
                $usersachiev_buffer[] = '(' . $arr['userid'] . ',1, ' . $points . ')';
            } elseif ($topics >= 10 && $lvl === 1) {
                $msg = 'Congratulations, you have just earned the [b]Forum Topic Starter Level 2[/b] achievement. :) [img]' . $site_config['paths']['images_baseurl'] . 'achievements/ftopic2.png[/img]';
                $achievements_buffer[] = '(' . $arr['userid'] . ', ' . $dt . ', \'Forum Topic Starter LVL2\', \'ftopic2.png\' , \'Started at least 10 topics in the forums.\')';
                $usersachiev_buffer[] = '(' . $arr['userid'] . ',2, ' . $points . ')';
            } elseif ($topics >= 25 && $lvl === 2) {
                $msg = 'Congratulations, you have just earned the [b]Forum Topic Starter Level 3[/b] achievement. :) [img]' . $site_config['paths']['images_baseurl'] . 'achievements/ftopic3.png[/img]';
                $achievements_buffer[] = '(' . $arr['userid'] . ', ' . $dt . ', \'Forum Topic Starter LVL3\', \'ftopic3.png\' , \'Started at least 25 topics in the forums.\')';
                $usersachiev_buffer[] = '(' . $arr['userid'] . ',3, ' . $points . ')';
            } elseif ($topics >= 50 && $lvl === 3) {
                $msg = 'Congratulations, you have just earned the [b]Forum Topic Starter Level 4[/b] achievement. :) [img]' . $site_config['paths']['images_baseurl'] . 'achievements/ftopic4.png[/img]';
                $achievements_buffer[] = '(' . $arr['userid'] . ', ' . $dt . ', \'Forum Topic Starter LVL4\', \'ftopic4.png\' , \'Started at least 50 topics in the forums.\')';
                $usersachiev_buffer[] = '(' . $arr['userid'] . ',4, ' . $points . ')';
            }
            if ($topics >= 75 && $lvl === 4) {
                $msg = 'Congratulations, you have just earned the [b]Forum Topic Starter Level 5[/b] achievement. :) [img]' . $site_config['paths']['images_baseurl'] . 'achievements/ftopic5.png[/img]';
                $achievements_buffer[] = '(' . $arr['userid'] . ', ' . $dt . ', \'Forum Topic Starter LVL5\', \'ftopic5.png\' , \'Started at least 75 topics in the forums.\')';
                $usersachiev_buffer[] = '(' . $arr['userid'] . ',5, ' . $points . ')';
            }
            if (!empty($msg)) {
                $msgs_buffer[] = [
                    'receiver' => $arr['userid'],
                    'added' => $dt,
                    'msg' => $msg,
                    'subject' => $subject,
                ];
                $cache->delete('user_' . $arr['userid']);
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
            write_log('Achievements Cleanup: Forum Topics Completed. Forum Topics Achievements awarded to - ' . $count . ' Member(s).' . $text);
        }
        unset($usersachiev_buffer, $achievements_buffer, $msgs_buffer, $count);
    }
}
