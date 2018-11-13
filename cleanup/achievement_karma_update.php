<?php

/**
 * @param $data
 *
 * @throws Exception
 */
function achievement_karma_update($data)
{
    $time_start = microtime(true);
    dbconn();
    global $site_config, $queries, $cache, $message_stuffs, $usersachiev_stuffs, $achievement_stuffs;

    set_time_limit(1200);
    ignore_user_abort(true);

    $res = sql_query("SELECT u.id, u.seedbonus, a.bonus FROM users AS u LEFT JOIN usersachiev AS a ON u.id = a.userid WHERE enabled = 'yes' AND u.seedbonus >= 1 AND a.bonus >= 0") or sqlerr(__FILE__, __LINE__);
    $msgs_buffer = $usersachiev_buffer = $achievements_buffer = [];
    if (mysqli_num_rows($res) > 0) {
        $dt = TIME_NOW;
        $points = random_int(1, 3);
        $subject = 'New Achievement Earned!';
        $var1 = 'bonus';
        while ($arr = mysqli_fetch_assoc($res)) {
            $seedbonus = (float) $arr['seedbonus'];
            $lvl = (int) $arr['bonus'];
            if ($seedbonus >= 250 && $lvl === 0) {
                $msg = 'Congratulations, you have just earned the [b]Bonus Banker LVL1[/b] achievement. :) [img]' . $site_config['pic_baseurl'] . 'achievements/bonus1.png[/img]';
                $achievements_buffer[] = [
                    'userid' => $arr['id'],
                    'date' => $dt,
                    'achievement' => 'Bonus Banker LVL1',
                    'icon' => 'bonus1.png',
                    'description' => 'Earned at least 1 bonus point.',
                ];
            } elseif ($seedbonus >= 500 && $lvl === 1) {
                $msg = 'Congratulations, you have just earned the [b]Bonus Banker LVL2[/b] achievement. :) [img]' . $site_config['pic_baseurl'] . 'achievements/bonus2.png[/img]';
                $achievements_buffer[] = [
                    'userid' => $arr['id'],
                    'date' => $dt,
                    'achievement' => 'Bonus Banker LVL2',
                    'icon' => 'bonus2.png',
                    'description' => 'Earned at least 100 bonus points.',
                ];
            } elseif ($seedbonus >= 1000 && $lvl === 2) {
                $msg = 'Congratulations, you have just earned the [b]Bonus Banker LVL3[/b] achievement. :) [img]' . $site_config['pic_baseurl'] . 'achievements/bonus3.png[/img]';
                $achievements_buffer[] = [
                    'userid' => $arr['id'],
                    'date' => $dt,
                    'achievement' => 'Bonus Banker LVL3',
                    'icon' => 'bonus3.png',
                    'description' => 'Earned at least 500 bonus points.',
                ];
            } elseif ($seedbonus >= 2000 && $lvl === 3) {
                $msg = 'Congratulations, you have just earned the [b]Bonus Banker LVL4[/b] achievement. :) [img]' . $site_config['pic_baseurl'] . 'achievements/bonus4.png[/img]';
                $achievements_buffer[] = [
                    'userid' => $arr['id'],
                    'date' => $dt,
                    'achievement' => 'Bonus Banker LVL4',
                    'icon' => 'bonus4.png',
                    'description' => 'Earned at least 1000 bonus points.',
                ];
            } elseif ($seedbonus >= 2500 && $lvl === 4) {
                $msg = 'Congratulations, you have just earned the [b]Bonus Banker LVL5[/b] achievement. :) [img]' . $site_config['pic_baseurl'] . 'achievements/bonus5.png[/img]';
                $achievements_buffer[] = [
                    'userid' => $arr['id'],
                    'date' => $dt,
                    'achievement' => 'Bonus Banker LVL5',
                    'icon' => 'bonus5.png',
                    'description' => 'Earned at least 2000 bonus points.',
                ];
            } elseif ($seedbonus >= 5000 && $lvl === 5) {
                $msg = 'Congratulations, you have just earned the [b]Bonus Banker LVL6[/b] achievement. :) [img]' . $site_config['pic_baseurl'] . 'achievements/bonus6.png[/img]';
                $achievements_buffer[] = [
                    'userid' => $arr['id'],
                    'date' => $dt,
                    'achievement' => 'Bonus Banker LVL6',
                    'icon' => 'bonus6.png',
                    'description' => 'Earned at least 5000 bonus points.',
                ];
            } elseif ($seedbonus >= 10000 && $lvl === 6) {
                $msg = 'Congratulations, you have just earned the [b]Bonus Banker LVL7[/b] achievement. :) [img]' . $site_config['pic_baseurl'] . 'achievements/bonus7.png[/img]';
                $achievements_buffer[] = [
                    'userid' => $arr['id'],
                    'date' => $dt,
                    'achievement' => 'Bonus Banker LVL7',
                    'icon' => 'bonus7.png',
                    'description' => 'Earned at least 10000 bonus points.',
                ];
            } elseif ($seedbonus >= 30000 && $lvl === 7) {
                $msg = 'Congratulations, you have just earned the [b]Bonus Banker LVL8[/b] achievement. :) [img]' . $site_config['pic_baseurl'] . 'achievements/bonus8.png[/img]';
                $achievements_buffer[] = [
                    'userid' => $arr['id'],
                    'date' => $dt,
                    'achievement' => 'Bonus Banker LVL8',
                    'icon' => 'bonus8.png',
                    'description' => 'Earned at least 30000 bonus points.',
                ];
            } elseif ($seedbonus >= 70000 && $lvl === 8) {
                $msg = 'Congratulations, you have just earned the [b]Bonus Banker LVL9[/b] achievement. :) [img]' . $site_config['pic_baseurl'] . 'achievements/bonus9.png[/img]';
                $achievements_buffer[] = [
                    'userid' => $arr['id'],
                    'date' => $dt,
                    'achievement' => 'Bonus Banker LVL9',
                    'icon' => 'bonus9.png',
                    'description' => 'Earned at least 70000 bonus points.',
                ];
            } elseif ($seedbonus >= 100000 && $lvl === 9) {
                $msg = 'Congratulations, you have just earned the [b]Bonus Banker LVL10[/b] achievement. :) [img]' . $site_config['pic_baseurl'] . 'achievements/bonus10.png[/img]';
                $achievements_buffer[] = [
                    'userid' => $arr['id'],
                    'date' => $dt,
                    'achievement' => 'Bonus Banker LVL10',
                    'icon' => 'bonus10.png',
                    'description' => 'Earned at least 100000 bonus points.',
                ];
            } elseif ($seedbonus >= 1000000 && $lvl === 10) {
                $msg = 'Congratulations, you have just earned the [b]Bonus Banker LVL11[/b] achievement. :) [img]' . $site_config['pic_baseurl'] . 'achievements/bonus11.png[/img]';
                $achievements_buffer[] = [
                    'userid' => $arr['id'],
                    'date' => $dt,
                    'achievement' => 'Bonus Banker LVL11',
                    'icon' => 'bonus11.png',
                    'description' => 'Earned at least 1000000 bonus points.',
                ];
            }
            if (!empty($msg)) {
                $msgs_buffer[] = [
                    'sender' => 0,
                    'receiver' => $arr['id'],
                    'added' => $dt,
                    'msg' => $msg,
                    'subject' => $subject,
                ];
                $usersachiev_buffer[] = [
                    'userid' => $arr['id'],
                    'bonus' => $lvl + 1,
                    'achpoints' => $points,
                ];
                $cache->delete('user_achievement_points_' . $arr['id']);
            }
        }
        $count = count($achievements_buffer);
        if ($count > 0) {
            $message_stuffs->insert($msgs_buffer);

            $update = [
                'date' => new Envms\FluentPDO\Literal('VALUES(date)'),
                'achievement' => new Envms\FluentPDO\Literal('VALUES(achievement)'),
                'icon' => new Envms\FluentPDO\Literal('VALUES(icon)'),
                'description' => new Envms\FluentPDO\Literal('VALUES(description)'),
            ];

            $achievement_stuffs->insert($achievements_buffer, $update);

            $update = [
                'bonus' => new Envms\FluentPDO\Literal('VALUES(bonus)'),
                'achpoints' => new Envms\FluentPDO\Literal('VALUES(achpoints)'),
            ];
            $usersachiev_stuffs->insert($usersachiev_buffer, $update);
        }
        $time_end = microtime(true);
        $run_time = $time_end - $time_start;
        $text = " Run time: $run_time seconds";
        echo $text . "\n";
        if ($data['clean_log'] && $queries > 0) {
            write_log("Achievements Cleanup: Karma Completed using $queries queries. Karma Achievements awarded to - " . $count . ' Member(s).' . $text);
        }
        unset($usersachiev_buffer, $achievements_buffer, $msgs_buffer, $count);
    }
}
