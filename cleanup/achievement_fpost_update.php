<?php

declare(strict_types = 1);

use Pu239\Cache;
use Pu239\Message;

/**
 * @param $data
 *
 * @throws Exception
 */
function achievement_fpost_update($data)
{
    global $container, $site_config;

    $time_start = microtime(true);
    $res = sql_query('SELECT userid, forumposts, postachiev FROM usersachiev WHERE forumposts >= 1') or sqlerr(__FILE__, __LINE__);
    $msgs_buffer = $usersachiev_buffer = $achievements_buffer = [];
    if (mysqli_num_rows($res) > 0) {
        $dt = TIME_NOW;
        $subject = 'New Achievement Earned!';
        $points = random_int(1, 3);
        $var1 = 'postachiev';
        $cache = $container->get(Cache::class);
        while ($arr = mysqli_fetch_assoc($res)) {
            $posts = (int) $arr['forumposts'];
            $lvl = (int) $arr['postachiev'];
            $msg = '';
            if ($posts >= 1 && $lvl === 0) {
                $msg = 'Congratulations, you have just earned the [b]Forum Poster Level 1[/b] achievement. :) [img]' . $site_config['paths']['images_baseurl'] . 'achievements/fpost1.png[/img]';
                $achievements_buffer[] = '(' . $arr['userid'] . ', ' . $dt . ', \'Forum Poster LVL1\', \'fpost1.png\' , \'Made at least 1 post in the forums.\')';
                $usersachiev_buffer[] = '(' . $arr['userid'] . ',1, ' . $points . ')';
            } elseif ($posts >= 25 && $lvl === 1) {
                $msg = 'Congratulations, you have just earned the [b]Forum Poster Level 2[/b] achievement. :) [img]' . $site_config['paths']['images_baseurl'] . 'achievements/fpost2.png[/img]';
                $achievements_buffer[] = '(' . $arr['userid'] . ', ' . $dt . ', \'Forum Poster LVL2\', \'fpost2.png\' , \'Made at least 25 posts in the forums.\')';
                $usersachiev_buffer[] = '(' . $arr['userid'] . ',2, ' . $points . ')';
            } elseif ($posts >= 50 && $lvl === 2) {
                $msg = 'Congratulations, you have just earned the [b]Forum Poster Level 3[/b] achievement. :) [img]' . $site_config['paths']['images_baseurl'] . 'achievements/fpost3.png[/img]';
                $achievements_buffer[] = '(' . $arr['userid'] . ', ' . $dt . ', \'Forum Poster LVL3\', \'fpost3.png\' , \'Made at least 50 posts in the forums.\')';
                $usersachiev_buffer[] = '(' . $arr['userid'] . ',3, ' . $points . ')';
            } elseif ($posts >= 100 && $lvl === 3) {
                $msg = 'Congratulations, you have just earned the [b]Forum Poster Level 4[/b] achievement. :) [img]' . $site_config['paths']['images_baseurl'] . 'achievements/fpost4.png[/img]';
                $achievements_buffer[] = '(' . $arr['userid'] . ', ' . $dt . ', \'Forum Poster LVL4\', \'fpost4.png\' , \'Made at least 100 posts in the forums.\')';
                $usersachiev_buffer[] = '(' . $arr['userid'] . ',4, ' . $points . ')';
            } elseif ($posts >= 250 && $lvl === 4) {
                $msg = 'Congratulations, you have just earned the [b]Forum Poster Level 5[/b] achievement. :) [img]' . $site_config['paths']['images_baseurl'] . 'achievements/fpost5.png[/img]';
                $achievements_buffer[] = '(' . $arr['userid'] . ', ' . $dt . ', \'Forum Poster LVL5\', \'fpost5.png\' , \'Made at least 250 posts in the forums.\')';
                $usersachiev_buffer[] = '(' . $arr['userid'] . ',5, ' . $points . ')';
            } elseif ($posts >= 500 && $lvl === 5) {
                $msg = 'Congratulations, you have just earned the [b]Forum Poster Level 6[/b] achievement. :) [img]' . $site_config['paths']['images_baseurl'] . 'achievements/fpost6.png[/img]';
                $achievements_buffer[] = '(' . $arr['userid'] . ', ' . $dt . ', \'Forum Poster LVL6\', \'fpost6.png\' , \'Made at least 500 posts in the forums.\')';
                $usersachiev_buffer[] = '(' . $arr['userid'] . ',6, ' . $points . ')';
            } elseif ($posts >= 750 && $lvl === 6) {
                $msg = 'Congratulations, you have just earned the [b]Forum Poster Level 7[/b] achievement. :) [img]' . $site_config['paths']['images_baseurl'] . 'achievements/fpost7.png[/img]';
                $achievements_buffer[] = '(' . $arr['userid'] . ', ' . $dt . ', \'Forum Poster LVL7\', \'fpost7.png\' , \'Made at least 750 posts in the forums.\')';
                $usersachiev_buffer[] = '(' . $arr['userid'] . ',7, ' . $points . ')';
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
            write_log('Achievements Cleanup: Forum Posts Completed. Forum Posts Achievements awarded to - ' . $count . ' Member(s).' . $text);
        }
        unset($usersachiev_buffer, $achievements_buffer, $msgs_buffer, $count);
    }
}
