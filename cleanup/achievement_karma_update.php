<?php

/**
 * @param $data
 *
 * @throws Exception
 */
function achievement_karma_update($data)
{
    global $site_config, $queries, $cache;

    $lang = load_language('ad_cleanup_manager');

    set_time_limit(1200);
    ignore_user_abort(true);

    $res = sql_query("SELECT u.id, u.seedbonus, a.bonus FROM users AS u LEFT JOIN usersachiev AS a ON u.id = a.userid WHERE enabled = 'yes' AND u.seedbonus >= 1 AND a.bonus >= 0") or sqlerr(__FILE__, __LINE__);
    $msgs_buffer = $usersachiev_buffer = $achievements_buffer = [];
    if (mysqli_num_rows($res) > 0) {
        $dt = TIME_NOW;
        $points = random_int(1, 3);
        $subject = sqlesc($lang['doc_achiev_earned']);
        $var1 = 'bonus';
        while ($arr = mysqli_fetch_assoc($res)) {
            $seedbonus = (float) $arr['seedbonus'];
            $lvl = (int) $arr['bonus'];
            if ($seedbonus >= 1 && 0 == $lvl) {
                $msg = sqlesc('Congratulations, you have just earned the [b]Bonus Banker LVL1[/b] achievement. :) [img]'.$site_config['pic_baseurl'].'achievements/bonus1.png[/img]');
                $msgs_buffer[] = "(0, {$arr['id']}, $dt, $msg, $subject)";
                $achievements_buffer[] = '('.$arr['id'].', '.$dt.', \'Bonus Banker LVL1\', \'bonus1.png\' , \'Earned at least 1 bonus point.\')';
                $usersachiev_buffer[] = '('.$arr['id'].',1, '.$points.')';
                $cache->increment('inbox_'.$arr['id']);
                $cache->delete('user_achievement_points_'.$arr['id']);
            }
            if ($seedbonus >= 100 && 1 == $lvl) {
                $msg = sqlesc('Congratulations, you have just earned the [b]Bonus Banker LVL2[/b] achievement. :) [img]'.$site_config['pic_baseurl'].'achievements/bonus2.png[/img]');
                $msgs_buffer[] = "(0, {$arr['id']}, $dt, $msg, $subject)";
                $achievements_buffer[] = '('.$arr['id'].', '.$dt.', \'Bonus Banker LVL2\', \'bonus2.png\' , \'Earned at least 100 bonus points.\')';
                $usersachiev_buffer[] = '('.$arr['id'].',2, '.$points.')';
                $cache->increment('inbox_'.$arr['id']);
                $cache->delete('user_achievement_points_'.$arr['id']);
            }
            if ($seedbonus >= 500 && 2 == $lvl) {
                $msg = sqlesc('Congratulations, you have just earned the [b]Bonus Banker LVL3[/b] achievement. :) [img]'.$site_config['pic_baseurl'].'achievements/bonus3.png[/img]');
                $msgs_buffer[] = "(0, {$arr['id']}, $dt, $msg, $subject)";
                $achievements_buffer[] = '('.$arr['id'].', '.$dt.', \'Bonus Banker LVL3\', \'bonus3.png\' , \'Earned at least 500 bonus points.\')';
                $usersachiev_buffer[] = '('.$arr['id'].',3, '.$points.')';
                $cache->increment('inbox_'.$arr['id']);
                $cache->delete('user_achievement_points_'.$arr['id']);
            }
            if ($seedbonus >= 1000 && 3 == $lvl) {
                $msg = sqlesc('Congratulations, you have just earned the [b]Bonus Banker LVL4[/b] achievement. :) [img]'.$site_config['pic_baseurl'].'achievements/bonus4.png[/img]');
                $msgs_buffer[] = "(0, {$arr['id']}, $dt, $msg, $subject)";
                $achievements_buffer[] = '('.$arr['id'].', '.$dt.', \'Bonus Banker LVL4\', \'bonus4.png\' , \'Earned at least 1000 bonus points.\')';
                $usersachiev_buffer[] = '('.$arr['id'].',4, '.$points.')';
                $cache->increment('inbox_'.$arr['id']);
                $cache->delete('user_achievement_points_'.$arr['id']);
            }
            if ($seedbonus >= 2000 && 4 == $lvl) {
                $msg = sqlesc('Congratulations, you have just earned the [b]Bonus Banker LVL5[/b] achievement. :) [img]'.$site_config['pic_baseurl'].'achievements/bonus5.png[/img]');
                $msgs_buffer[] = "(0, {$arr['id']}, $dt, $msg, $subject)";
                $achievements_buffer[] = '('.$arr['id'].', '.$dt.', \'Bonus Banker LVL5\', \'bonus5.png\' , \'Earned at least 2000 bonus points.\')';
                $usersachiev_buffer[] = '('.$arr['id'].',5, '.$points.')';
                $cache->increment('inbox_'.$arr['id']);
                $cache->delete('user_achievement_points_'.$arr['id']);
            }
            if ($seedbonus >= 5000 && 5 == $lvl) {
                $msg = sqlesc('Congratulations, you have just earned the [b]Bonus Banker LVL6[/b] achievement. :) [img]'.$site_config['pic_baseurl'].'achievements/bonus6.png[/img]');
                $msgs_buffer[] = "(0, {$arr['id']}, $dt, $msg, $subject)";
                $achievements_buffer[] = '('.$arr['id'].', '.$dt.', \'Bonus Banker LVL6\', \'bonus6.png\' , \'Earned at least 5000 bonus points.\')';
                $usersachiev_buffer[] = '('.$arr['id'].',6, '.$points.')';
                $cache->increment('inbox_'.$arr['id']);
            }
            if ($seedbonus >= 10000 && 6 == $lvl) {
                $msg = sqlesc('Congratulations, you have just earned the [b]Bonus Banker LVL7[/b] achievement. :) [img]'.$site_config['pic_baseurl'].'achievements/bonus7.png[/img]');
                $msgs_buffer[] = "(0, {$arr['id']}, $dt, $msg, $subject)";
                $achievements_buffer[] = '('.$arr['id'].', '.$dt.', \'Bonus Banker LVL7\', \'bonus7.png\' , \'Earned at least 10000 bonus points.\')';
                $usersachiev_buffer[] = '('.$arr['id'].',7, '.$points.')';
                $cache->increment('inbox_'.$arr['id']);
                $cache->delete('user_achievement_points_'.$arr['id']);
            }
            if ($seedbonus >= 30000 && 7 == $lvl) {
                $msg = sqlesc('Congratulations, you have just earned the [b]Bonus Banker LVL8[/b] achievement. :) [img]'.$site_config['pic_baseurl'].'achievements/bonus8.png[/img]');
                $msgs_buffer[] = "(0, {$arr['id']}, $dt, $msg, $subject)";
                $achievements_buffer[] = '('.$arr['id'].', '.$dt.', \'Bonus Banker LVL8\', \'bonus8.png\' , \'Earned at least 30000 bonus points.\')';
                $usersachiev_buffer[] = '('.$arr['id'].',8, '.$points.')';
                $cache->increment('inbox_'.$arr['id']);
                $cache->delete('user_achievement_points_'.$arr['id']);
            }
            if ($seedbonus >= 70000 && 8 == $lvl) {
                $msg = sqlesc('Congratulations, you have just earned the [b]Bonus Banker LVL9[/b] achievement. :) [img]'.$site_config['pic_baseurl'].'achievements/bonus9.png[/img]');
                $msgs_buffer[] = "(0, {$arr['id']}, $dt, $msg, $subject)";
                $achievements_buffer[] = '('.$arr['id'].', '.$dt.', \'Bonus Banker LVL9\', \'bonus9.png\' , \'Earned at least 70000 bonus points.\')';
                $usersachiev_buffer[] = '('.$arr['id'].',9, '.$points.')';
                $cache->increment('inbox_'.$arr['id']);
                $cache->delete('user_achievement_points_'.$arr['id']);
            }
            if ($seedbonus >= 100000 && 9 == $lvl) {
                $msg = sqlesc('Congratulations, you have just earned the [b]Bonus Banker LVL10[/b] achievement. :) [img]'.$site_config['pic_baseurl'].'achievements/bonus10.png[/img]');
                $msgs_buffer[] = "(0, {$arr['id']}, $dt, $msg, $subject)";
                $achievements_buffer[] = '('.$arr['id'].', '.$dt.', \'Bonus Banker LVL10\', \'bonus10.png\' , \'Earned at least 100000 bonus points.\')';
                $usersachiev_buffer[] = '('.$arr['id'].',10, '.$points.')';
                $cache->increment('inbox_'.$arr['id']);
            }
            if ($seedbonus >= 1000000 && 10 == $lvl) {
                $msg = sqlesc('Congratulations, you have just earned the [b]Bonus Banker LVL11[/b] achievement. :) [img]'.$site_config['pic_baseurl'].'achievements/bonus11.png[/img]');
                $msgs_buffer[] = "(0, {$arr['id']}, $dt, $msg, $subject)";
                $achievements_buffer[] = '('.$arr['id'].', '.$dt.', \'Bonus Banker LVL11\', \'bonus11.png\' , \'Earned at least 1000000 bonus points.\')';
                $usersachiev_buffer[] = '('.$arr['id'].',11, '.$points.')';
                $cache->increment('inbox_'.$arr['id']);
                $cache->delete('user_achievement_points_'.$arr['id']);
            }
        }
        $count = count($achievements_buffer);
        if ($count > 0) {
            sql_query('INSERT INTO messages (sender,receiver,added,msg,subject) VALUES '.implode(', ', $msgs_buffer)) or sqlerr(__FILE__, __LINE__);
            sql_query('INSERT INTO achievements (userid, date, achievement, icon, description) VALUES '.implode(', ', $achievements_buffer).' ON DUPLICATE KEY UPDATE date = VALUES(date),achievement = VALUES(achievement),icon = VALUES(icon),description = VALUES(description)') or sqlerr(__FILE__, __LINE__);
            sql_query("INSERT INTO usersachiev (userid, $var1, achpoints) VALUES ".implode(', ', $usersachiev_buffer)." ON DUPLICATE KEY UPDATE $var1 = VALUES($var1), achpoints=achpoints + VALUES(achpoints)") or sqlerr(__FILE__, __LINE__);
        }
        if ($data['clean_log'] && $queries > 0) {
            write_log("Achievements Cleanup: Karma Completed using $queries queries. Karma Achievements awarded to - ".$count.' Member(s)');
        }
        unset($usersachiev_buffer, $achievements_buffer, $msgs_buffer, $count);
    }
}
