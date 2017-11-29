<?php
/**
 * @param $data
 */
function achievement_up_update($data)
{
    global $site_config, $queries, $cache;
    set_time_limit(1200);
    ignore_user_abort(true);
    // *Updated* Upload Achievements Mod by MelvinMeow
    $res = sql_query("SELECT u.id, u.numuploads, a.ul FROM users AS u LEFT JOIN usersachiev AS a ON u.id = a.userid WHERE u.enabled = 'yes' AND u.numuploads >= 1") or sqlerr(__FILE__, __LINE__);
    $msg_buffer = $usersachiev_buffer = $achievements_buffer = [];
    if (mysqli_num_rows($res) > 0) {
        $dt = TIME_NOW;
        $subject = sqlesc('New Achievement Earned!');
        $points = random_int(1, 3);
        while ($arr = mysqli_fetch_assoc($res)) {
            $uploads = (int)$arr['numuploads'];
            $ul = (int)$arr['ul'];
            if ($uploads >= 1 && $ul == 0) {
                $msg = sqlesc('Congratulations, you have just earned the [b]Uploader LVL1[/b] achievement. :) [img]' . $site_config['baseurl'] . '/images/achievements/ul1.png[/img]');
                $msgs_buffer[] = '(0,' . $arr['id'] . ',' . TIME_NOW . ', ' . sqlesc($msg) . ', ' . sqlesc($subject) . ')';
                $achievements_buffer[] = '(' . $arr['id'] . ', ' . TIME_NOW . ', \'Uploader LVL1\', \'ul1.png\' , \'Uploaded at least 1 torrent to the site.\')';
                $usersachiev_buffer[] = '(' . $arr['id'] . ',1, ' . $points . ')';
                $cache->increment('inbox_' . $arr['id']);
                $cache->delete('user_achievement_points_' . $arr['id']);
                $var1 = 'ul';
            }
            if ($uploads >= 50 && $ul == 1) {
                $msg = sqlesc('Congratulations, you have just earned the [b]Uploader LVL2[/b] achievement. :) [img]' . $site_config['baseurl'] . '/images/achievements/ul2.png[/img]');
                $msgs_buffer[] = '(0,' . $arr['id'] . ',' . TIME_NOW . ', ' . sqlesc($msg) . ', ' . sqlesc($subject) . ')';
                $achievements_buffer[] = '(' . $arr['id'] . ', ' . TIME_NOW . ', \'Uploader LVL2\', \'ul2.png\' , \'Uploaded at least 50 torrents to the site.\')';
                $usersachiev_buffer[] = '(' . $arr['id'] . ',2, ' . $points . ')';
                $cache->increment('inbox_' . $arr['id']);
                $cache->delete('user_achievement_points_' . $arr['id']);
                $var1 = 'ul';
            }
            if ($uploads >= 100 && $ul == 2) {
                $msg = sqlesc('Congratulations, you have just earned the [b]Uploader LVL3[/b] achievement. :) [img]' . $site_config['baseurl'] . '/images/achievements/ul3.png[/img]');
                $msgs_buffer[] = '(0,' . $arr['id'] . ',' . TIME_NOW . ', ' . sqlesc($msg) . ', ' . sqlesc($subject) . ')';
                $achievements_buffer[] = '(' . $arr['id'] . ', ' . TIME_NOW . ', \'Uploader LVL3\', \'ul3.png\' , \'Uploaded at least 100 torrents to the site.\')';
                $usersachiev_buffer[] = '(' . $arr['id'] . ',3, ' . $points . ')';
                $cache->increment('inbox_' . $arr['id']);
                $cache->delete('user_achievement_points_' . $arr['id']);
                $var1 = 'ul';
            }
            if ($uploads >= 200 && $ul == 3) {
                $msg = sqlesc('Congratulations, you have just earned the [b]Uploader LVL4[/b] achievement. :) [img]' . $site_config['baseurl'] . '/images/achievements/ul4.png[/img]');
                $msgs_buffer[] = '(0,' . $arr['id'] . ',' . TIME_NOW . ', ' . sqlesc($msg) . ', ' . sqlesc($subject) . ')';
                $achievements_buffer[] = '(' . $arr['id'] . ', ' . TIME_NOW . ', \'Uploader LVL4\', \'ul4.png\' , \'Uploaded at least 200 torrents to the site.\')';
                $usersachiev_buffer[] = '(' . $arr['id'] . ',4, ' . $points . ')';
                $cache->increment('inbox_' . $arr['id']);
                $cache->delete('user_achievement_points_' . $arr['id']);
                $var1 = 'ul';
            }
            if ($uploads >= 300 && $ul == 4) {
                $msg = sqlesc('Congratulations, you have just earned the [b]Uploader LVL5[/b] achievement. :) [img]' . $site_config['baseurl'] . '/images/achievements/ul5.png[/img]');
                $msgs_buffer[] = '(0,' . $arr['id'] . ',' . TIME_NOW . ', ' . sqlesc($msg) . ', ' . sqlesc($subject) . ')';
                $achievements_buffer[] = '(' . $arr['id'] . ', ' . TIME_NOW . ', \'Uploader LVL5\', \'ul5.png\' , \'Uploaded at least 300 torrents to the site.\')';
                $usersachiev_buffer[] = '(' . $arr['id'] . ',5, ' . $points . ')';
                $cache->increment('inbox_' . $arr['id']);
                $cache->delete('user_achievement_points_' . $arr['id']);
                $var1 = 'ul';
            }
            if ($uploads >= 500 && $ul == 5) {
                $msg = sqlesc('Congratulations, you have just earned the [b]Uploader LVL6[/b] achievement. :) [img]' . $site_config['baseurl'] . '/images/achievements/ul6.png[/img]');
                $msgs_buffer[] = '(0,' . $arr['id'] . ',' . TIME_NOW . ', ' . sqlesc($msg) . ', ' . sqlesc($subject) . ')';
                $achievements_buffer[] = '(' . $arr['id'] . ', ' . TIME_NOW . ', \'Uploader LVL6\', \'ul6.png\' , \'Uploaded at least 500 torrents to the site.\')';
                $usersachiev_buffer[] = '(' . $arr['id'] . ',6, ' . $points . ')';
                $cache->increment('inbox_' . $arr['id']);
                $cache->delete('user_achievement_points_' . $arr['id']);
                $var1 = 'ul';
            }
            if ($uploads >= 800 && $ul == 6) {
                $msg = sqlesc('Congratulations, you have just earned the [b]Uploader LVL7[/b] achievement. :) [img]' . $site_config['baseurl'] . '/images/achievements/ul7.png[/img]');
                $msgs_buffer[] = '(0,' . $arr['id'] . ',' . TIME_NOW . ', ' . sqlesc($msg) . ', ' . sqlesc($subject) . ')';
                $achievements_buffer[] = '(' . $arr['id'] . ', ' . TIME_NOW . ', \'Uploader LVL7\', \'ul7.png\' , \'Uploaded at least 800 torrents to the site.\')';
                $usersachiev_buffer[] = '(' . $arr['id'] . ',7, ' . $points . ')';
                $cache->increment('inbox_' . $arr['id']);
                $cache->delete('user_achievement_points_' . $arr['id']);
                $var1 = 'ul';
            }
            if ($uploads >= 1000 && $ul == 7) {
                $msg = sqlesc('Congratulations, you have just earned the [b]Uploader LVL8[/b] achievement. :) [img]' . $site_config['baseurl'] . '/images/achievements/ul8.png[/img]');
                $msgs_buffer[] = '(0,' . $arr['id'] . ',' . TIME_NOW . ', ' . sqlesc($msg) . ', ' . sqlesc($subject) . ')';
                $achievements_buffer[] = '(' . $arr['id'] . ', ' . TIME_NOW . ', \'Uploader LVL8\', \'ul8.png\' , \'Uploaded at least 1000 torrents to the site.\')';
                $usersachiev_buffer[] = '(' . $arr['id'] . ',8, ' . $points . ')';
                $cache->increment('inbox_' . $arr['id']);
                $cache->delete('user_achievement_points_' . $arr['id']);
                $var1 = 'ul';
            }
            if ($uploads >= 1500 && $ul == 8) {
                $msg = sqlesc('Congratulations, you have just earned the [b]Uploader LVL9[/b] achievement. :) [img]' . $site_config['baseurl'] . '/images/achievements/ul9.png[/img]');
                $msgs_buffer[] = '(0,' . $arr['id'] . ',' . TIME_NOW . ', ' . sqlesc($msg) . ', ' . sqlesc($subject) . ')';
                $achievements_buffer[] = '(' . $arr['id'] . ', ' . TIME_NOW . ', \'Uploader LVL9\', \'ul9.png\' , \'Uploaded at least 1500 torrents to the site.\')';
                $usersachiev_buffer[] = '(' . $arr['id'] . ',9, ' . $points . ')';
                $cache->increment('inbox_' . $arr['id']);
                $cache->delete('user_achievement_points_' . $arr['id']);
                $var1 = 'ul';
            }
            if ($uploads >= 2000 && $ul == 9) {
                $msg = sqlesc('Congratulations, you have just earned the [b]Uploader LVL10[/b] achievement. :) [img]' . $site_config['baseurl'] . '/images/achievements/ul10.png[/img]');
                $msgs_buffer[] = '(0,' . $arr['id'] . ',' . TIME_NOW . ', ' . sqlesc($msg) . ', ' . sqlesc($subject) . ')';
                $achievements_buffer[] = '(' . $arr['id'] . ', ' . TIME_NOW . ', \'Uploader LVL10\', \'ul10.png\' , \'Uploaded at least 2000 torrents to the site.\')';
                $usersachiev_buffer[] = '(' . $arr['id'] . ',10, ' . $points . ')';
                $cache->increment('inbox_' . $arr['id']);
                $cache->delete('user_achievement_points_' . $arr['id']);
                $var1 = 'ul';
            }
        }
        $count = count($achievements_buffer);
        if ($count > 0) {
            sql_query('INSERT INTO messages (sender,receiver,added,msg,subject) VALUES ' . implode(', ', $msgs_buffer)) or sqlerr(__FILE__, __LINE__);
            sql_query('INSERT INTO achievements (userid, date, achievement, icon, description) VALUES ' . implode(', ', $achievements_buffer) . ' ON DUPLICATE KEY UPDATE date = VALUES(date),achievement = VALUES(achievement),icon = VALUES(icon),description = VALUES(description)') or sqlerr(__FILE__, __LINE__);
            sql_query("INSERT INTO usersachiev (userid, $var1, achpoints) VALUES " . implode(', ', $usersachiev_buffer) . " ON DUPLICATE KEY UPDATE $var1 = VALUES($var1), achpoints=achpoints + VALUES(achpoints)") or sqlerr(__FILE__, __LINE__);
        }
        if ($data['clean_log'] && $queries > 0) {
            write_log("Achievements Cleanup: Uploader Completed using $queries queries. Uploader Achievements awarded to - " . $count . ' Member(s)');
        }
        unset($usersachiev_buffer, $achievements_buffer, $msgs_buffer, $count);
    }
}
