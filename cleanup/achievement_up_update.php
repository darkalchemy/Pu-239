<?php

/**
 * @param $data
 *
 * @throws Exception
 */
function achievement_up_update($data)
{
    $time_start = microtime(true);
    dbconn();
    global $site_config, $queries, $cache, $message_stuffs;

    set_time_limit(1200);
    ignore_user_abort(true);

    $res = sql_query("SELECT u.id, u.numuploads, a.ul FROM users AS u LEFT JOIN usersachiev AS a ON u.id=a.userid WHERE u.enabled = 'yes' AND u.numuploads>= 1") or sqlerr(__FILE__, __LINE__);
    $msgs_buffer = $usersachiev_buffer = $achievements_buffer = [];
    if (mysqli_num_rows($res) > 0) {
        $dt = TIME_NOW;
        $subject = 'New Achievement Earned!';
        $points = random_int(1, 3);
        while ($arr = mysqli_fetch_assoc($res)) {
            $uploads = (int) $arr['numuploads'];
            $lvl = (int) $arr['ul'];
            $msg = '';
            if ($uploads >= 1 && $lvl === 0) {
                $msg = 'Congratulations, you have just earned the [b]Uploader LVL1[/b] achievement. :) [img]' . $site_config['paths']['images_baseurl'] . 'achievements/ul1.png[/img]';
                $achievements_buffer[] = '(' . $arr['id'] . ', ' . $dt . ', \'Uploader LVL1\', \'ul1.png\' , \'Uploaded at least 1 torrent to the site.\')';
                $usersachiev_buffer[] = '(' . $arr['id'] . ',1, ' . $points . ')';
            } elseif ($uploads >= 50 && $lvl === 1) {
                $msg = 'Congratulations, you have just earned the [b]Uploader LVL2[/b] achievement. :) [img]' . $site_config['paths']['images_baseurl'] . 'achievements/ul2.png[/img]';
                $achievements_buffer[] = '(' . $arr['id'] . ', ' . $dt . ', \'Uploader LVL2\', \'ul2.png\' , \'Uploaded at least 50 torrents to the site.\')';
                $usersachiev_buffer[] = '(' . $arr['id'] . ',2, ' . $points . ')';
            } elseif ($uploads >= 100 && $lvl === 2) {
                $msg = 'Congratulations, you have just earned the [b]Uploader LVL3[/b] achievement. :) [img]' . $site_config['paths']['images_baseurl'] . 'achievements/ul3.png[/img]';
                $achievements_buffer[] = '(' . $arr['id'] . ', ' . $dt . ', \'Uploader LVL3\', \'ul3.png\' , \'Uploaded at least 100 torrents to the site.\')';
                $usersachiev_buffer[] = '(' . $arr['id'] . ',3, ' . $points . ')';
            } elseif ($uploads >= 200 && $lvl === 3) {
                $msg = 'Congratulations, you have just earned the [b]Uploader LVL4[/b] achievement. :) [img]' . $site_config['paths']['images_baseurl'] . 'achievements/ul4.png[/img]';
                $achievements_buffer[] = '(' . $arr['id'] . ', ' . $dt . ', \'Uploader LVL4\', \'ul4.png\' , \'Uploaded at least 200 torrents to the site.\')';
                $usersachiev_buffer[] = '(' . $arr['id'] . ',4, ' . $points . ')';
            } elseif ($uploads >= 300 && $lvl === 4) {
                $msg = 'Congratulations, you have just earned the [b]Uploader LVL5[/b] achievement. :) [img]' . $site_config['paths']['images_baseurl'] . 'achievements/ul5.png[/img]';
                $achievements_buffer[] = '(' . $arr['id'] . ', ' . $dt . ', \'Uploader LVL5\', \'ul5.png\' , \'Uploaded at least 300 torrents to the site.\')';
                $usersachiev_buffer[] = '(' . $arr['id'] . ',5, ' . $points . ')';
            } elseif ($uploads >= 500 && $lvl === 5) {
                $msg = 'Congratulations, you have just earned the [b]Uploader LVL6[/b] achievement. :) [img]' . $site_config['paths']['images_baseurl'] . 'achievements/ul6.png[/img]';
                $achievements_buffer[] = '(' . $arr['id'] . ', ' . $dt . ', \'Uploader LVL6\', \'ul6.png\' , \'Uploaded at least 500 torrents to the site.\')';
                $usersachiev_buffer[] = '(' . $arr['id'] . ',6, ' . $points . ')';
            } elseif ($uploads >= 800 && $lvl === 6) {
                $msg = 'Congratulations, you have just earned the [b]Uploader LVL7[/b] achievement. :) [img]' . $site_config['paths']['images_baseurl'] . 'achievements/ul7.png[/img]';
                $achievements_buffer[] = '(' . $arr['id'] . ', ' . $dt . ', \'Uploader LVL7\', \'ul7.png\' , \'Uploaded at least 800 torrents to the site.\')';
                $usersachiev_buffer[] = '(' . $arr['id'] . ',7, ' . $points . ')';
            } elseif ($uploads >= 1000 && $lvl === 7) {
                $msg = 'Congratulations, you have just earned the [b]Uploader LVL8[/b] achievement. :) [img]' . $site_config['paths']['images_baseurl'] . 'achievements/ul8.png[/img]';
                $achievements_buffer[] = '(' . $arr['id'] . ', ' . $dt . ', \'Uploader LVL8\', \'ul8.png\' , \'Uploaded at least 1000 torrents to the site.\')';
                $usersachiev_buffer[] = '(' . $arr['id'] . ',8, ' . $points . ')';
            } elseif ($uploads >= 1500 && $lvl === 8) {
                $msg = 'Congratulations, you have just earned the [b]Uploader LVL9[/b] achievement. :) [img]' . $site_config['paths']['images_baseurl'] . 'achievements/ul9.png[/img]';
                $achievements_buffer[] = '(' . $arr['id'] . ', ' . $dt . ', \'Uploader LVL9\', \'ul9.png\' , \'Uploaded at least 1500 torrents to the site.\')';
                $usersachiev_buffer[] = '(' . $arr['id'] . ',9, ' . $points . ')';
            } elseif ($uploads >= 2000 && $lvl === 9) {
                $msg = 'Congratulations, you have just earned the [b]Uploader LVL10[/b] achievement. :) [img]' . $site_config['paths']['images_baseurl'] . 'achievements/ul10.png[/img]';
                $achievements_buffer[] = '(' . $arr['id'] . ', ' . $dt . ', \'Uploader LVL10\', \'ul10.png\' , \'Uploaded at least 2000 torrents to the site.\')';
                $usersachiev_buffer[] = '(' . $arr['id'] . ',10, ' . $points . ')';
            }
            if (!empty($msg)) {
                $msgs_buffer[] = [
                    'sender' => 0,
                    'receiver' => $arr['id'],
                    'added' => $dt,
                    'msg' => $msg,
                    'subject' => $subject,
                ];
                $cache->delete('user_achievement_points_' . $arr['id']);
            }
        }
        $count = count($achievements_buffer);
        if ($count > 0) {
            $message_stuffs->insert($msgs_buffer);
            sql_query('INSERT INTO achievements (userid, date, achievement, icon, description) VALUES ' . implode(', ', $achievements_buffer) . ' ON DUPLICATE KEY UPDATE date = VALUES(date),achievement = VALUES(achievement),icon = VALUES(icon),description = VALUES(description)') or sqlerr(__FILE__, __LINE__);
            sql_query('INSERT INTO usersachiev (userid, ul, achpoints) VALUES ' . implode(', ', $usersachiev_buffer) . ' ON DUPLICATE KEY UPDATE ul = VALUES(ul), achpoints=achpoints + VALUES(achpoints)') or sqlerr(__FILE__, __LINE__);
        }
        $time_end = microtime(true);
        $run_time = $time_end - $time_start;
        $text = " Run time: $run_time seconds";
        echo $text . "\n";
        if ($data['clean_log'] && $queries > 0) {
            write_log("Achievements Cleanup: Uploader Completed using $queries queries. Uploader Achievements awarded to - " . $count . ' Member(s).' . $text);
        }
        unset($usersachiev_buffer, $achievements_buffer, $msgs_buffer, $count);
    }
}
