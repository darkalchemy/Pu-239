<?php

declare(strict_types = 1);

use Pu239\Cache;
use Pu239\Database;
use Pu239\Message;

/**
 * @param $data
 *
 * @throws Exception
 */
function achievement_seedtime_update($data)
{
    global $container, $site_config;

    $time_start = microtime(true);
    $seedtime = 604800; // 7days
    $seedtime2 = 1209600; // 14days
    $seedtime3 = 1814400; // 21days
    $seedtime4 = 2419200; // 28days
    $seedtime5 = 3888000; // 45days
    $seedtime6 = 5184000; // 60days
    $seedtime7 = 7776000; // 90days
    $seedtime8 = 10368000; // 120days
    $seedtime9 = 12960000; // 200days
    $seedtime10 = 31536000; //1year
    $fluent = $container->get(Database::class);
    $query = $fluent->from('snatched AS s')
                    ->select(null)
                    ->select('DISTINCT s.userid')
                    ->select('s.seedtime')
                    ->select('a.dayseed')
                    ->leftJoin('usersachiev AS a ON s.userid = a.userid')
                    ->where('seedtime >= ?', $seedtime)
                    ->groupBy('s.userid')
                    ->groupBy('s.seedtime')
                    ->groupBy('a.dayseed')
                    ->orderBy('a.dayseed')
                    ->fetchAll();

    $msgs_buffer = $usersachiev_buffer = $achievements_buffer = $userids = [];
    if (!empty($query)) {
        $dt = TIME_NOW;
        $subject = 'New Achievement Earned!';
        $points = random_int(1, 3);
        $var1 = 'dayseed';
        $cache = $container->get(Cache::class);
        foreach ($query as $arr) {
            $timeseeded = $arr['seedtime'];
            $dayseed = $arr['dayseed'];
            $msg = '';
            if ($dayseed === 0 && $timeseeded >= $seedtime && !in_array($arr['user_id'], $userids)) {
                $msg = 'Congratulations, you have just earned the [b]7 Day Seeder[/b] achievement. :) [img]' . $site_config['paths']['images_baseurl'] . 'achievements/7dayseed.png[/img]';
                $achievements_buffer[] = '(' . $arr['userid'] . ', ' . $dt . ', \'7 Day Seeder\', \'7dayseed.png\' , \'Seeded a snatched torrent for a total of at least 7 days.\')';
                $usersachiev_buffer[] = '(' . $arr['userid'] . ',7, ' . $points . ')';
                $userids[] = $arr['user_id'];
            } elseif ($dayseed === 7 && $timeseeded >= $seedtime2) {
                $msg = 'Congratulations, you have just earned the [b]14 Day Seeder[/b] achievement. :) [img]' . $site_config['paths']['images_baseurl'] . 'achievements/14dayseed.png[/img]';
                $achievements_buffer[] = '(' . $arr['userid'] . ', ' . $dt . ', \'14 Day Seeder\', \'14dayseed.png\' , \'Seeded a snatched torrent for a total of at least 14 days.\')';
                $usersachiev_buffer[] = '(' . $arr['userid'] . ',14, ' . $points . ')';
                $userids[] = $arr['user_id'];
            } elseif ($dayseed === 14 && $timeseeded >= $seedtime3) {
                $msg = 'Congratulations, you have just earned the [b]21 Day Seeder[/b] achievement. :) [img]' . $site_config['paths']['images_baseurl'] . 'achievements/21dayseed.png[/img]';
                $achievements_buffer[] = '(' . $arr['userid'] . ', ' . $dt . ', \'21 Day Seeder\', \'21dayseed.png\' , \'Seeded a snatched torrent for a total of at least 21 days.\')';
                $usersachiev_buffer[] = '(' . $arr['userid'] . ',21, ' . $points . ')';
                $userids[] = $arr['user_id'];
            } elseif ($dayseed === 21 && $timeseeded >= $seedtime4) {
                $msg = 'Congratulations, you have just earned the [b]28 Day Seeder[/b] achievement. :) [img]' . $site_config['paths']['images_baseurl'] . 'achievements/28dayseed.png[/img]';
                $achievements_buffer[] = '(' . $arr['userid'] . ', ' . $dt . ', \'28 Day Seeder\', \'28dayseed.png\' , \'Seeded a snatched torrent for a total of at least 28 days.\')';
                $usersachiev_buffer[] = '(' . $arr['userid'] . ',28, ' . $points . ')';
                $userids[] = $arr['user_id'];
            } elseif ($dayseed === 28 && $timeseeded >= $seedtime5) {
                $msg = 'Congratulations, you have just earned the [b]45 Day Seeder[/b] achievement. :) [img]' . $site_config['paths']['images_baseurl'] . 'achievements/45dayseed.png[/img]';
                $achievements_buffer[] = '(' . $arr['userid'] . ', ' . $dt . ', \'45 Day Seeder\', \'45dayseed.png\' , \'Seeded a snatched torrent for a total of at least 45 days.\')';
                $usersachiev_buffer[] = '(' . $arr['userid'] . ',45, ' . $points . ')';
                $userids[] = $arr['user_id'];
            } elseif ($dayseed === 45 && $timeseeded >= $seedtime6) {
                $msg = 'Congratulations, you have just earned the [b]60 Day Seeder[/b] achievement. :) [img]' . $site_config['paths']['images_baseurl'] . 'achievements/60dayseed.png[/img]';
                $achievements_buffer[] = '(' . $arr['userid'] . ', ' . $dt . ', \'60 Day Seeder\', \'60dayseed.png\' , \'Seeded a snatched torrent for a total of at least 60 days.\')';
                $usersachiev_buffer[] = '(' . $arr['userid'] . ',60, ' . $points . ')';
                $userids[] = $arr['user_id'];
            } elseif ($dayseed === 60 && $timeseeded >= $seedtime7) {
                $msg = 'Congratulations, you have just earned the [b]90 Day Seeder[/b] achievement. :) [img]' . $site_config['paths']['images_baseurl'] . 'achievements/90dayseed.png[/img]';
                $achievements_buffer[] = '(' . $arr['userid'] . ', ' . $dt . ', \'90 Day Seeder\', \'90dayseed.png\' , \'Seeded a snatched torrent for a total of at least 90 days.\')';
                $usersachiev_buffer[] = '(' . $arr['userid'] . ',90, ' . $points . ')';
                $userids[] = $arr['user_id'];
            } elseif ($dayseed === 90 && $timeseeded >= $seedtime8) {
                $msg = 'Congratulations, you have just earned the [b]120 Day Seeder[/b] achievement. :) [img]' . $site_config['paths']['images_baseurl'] . 'achievements/120dayseed.png[/img]';
                $achievements_buffer[] = '(' . $arr['userid'] . ', ' . $dt . ', \'120 Day Seeder\', \'120dayseed.png\' , \'Seeded a snatched torrent for a total of at least 120 days.\')';
                $usersachiev_buffer[] = '(' . $arr['userid'] . ',120, ' . $points . ')';
                $userids[] = $arr['user_id'];
            } elseif ($dayseed === 120 && $timeseeded >= $seedtime9) {
                $msg = 'Congratulations, you have just earned the [b]200 Day Seeder[/b] achievement. :) [img]' . $site_config['paths']['images_baseurl'] . 'achievements/200dayseed.png[/img]';
                $achievements_buffer[] = '(' . $arr['userid'] . ', ' . $dt . ', \'200 Day Seeder\', \'200dayseed.png\' , \'Seeded a snatched torrent for a total of at least 200 days.\')';
                $usersachiev_buffer[] = '(' . $arr['userid'] . ',200, ' . $points . ')';
                $userids[] = $arr['user_id'];
            } elseif ($dayseed === 200 && $timeseeded >= $seedtime10) {
                $msg = 'Congratulations, you have just earned the [b]1 Year Seeder[/b] achievement. :) [img]' . $site_config['paths']['images_baseurl'] . 'achievements/365dayseed.png[/img]';
                $achievements_buffer[] = '(' . $arr['userid'] . ', ' . $dt . ', \'365 Day Seeder\', \'365dayseed.png\' , \'Seeded a snatched torrent for a total of at least 1 Year.\')';
                $usersachiev_buffer[] = '(' . $arr['userid'] . ',365, ' . $points . ')';
                $userids[] = $arr['user_id'];
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
            write_log('Achievements Cleanup: Seedtime Completed. Seedtime Achievements awarded to - ' . $count . ' Member(s).' . $text);
        }
        unset($usersachiev_buffer, $achievements_buffer, $msgs_buffer, $count);
    }
}
