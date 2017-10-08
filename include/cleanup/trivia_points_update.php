<?php
function trivia_points_update($data)
{
    global $site_config, $queries, $mc1;
    set_time_limit(1200);
    ignore_user_abort(true);
    $count = 0;

    $msgs_buffer = $users_buffer = $users = [];
    $i = 1;
    $gamenum = get_one_row('triviasettings', 'gamenum', 'WHERE gameon = 1');
    $sql = 'SELECT t.user_id, COUNT(t.correct) AS correct, u.username, u.modcomment, (SELECT COUNT(correct) AS incorrect FROM triviausers WHERE gamenum = ' . sqlesc($gamenum) . ' AND correct = 0 AND user_id = t.user_id) AS incorrect
                FROM triviausers AS t
                INNER JOIN users AS u ON u.id = t.user_id
                WHERE t.correct = 1 AND gamenum = ' . sqlesc($gamenum) . '
                GROUP BY t.user_id
                ORDER BY correct DESC, incorrect ASC
                LIMIT 10';
    $res = sql_query($sql) or sqlerr(__FILE__, __LINE__);
    if (mysqli_num_rows($res) > 0) {
        $subject = 'Trivia Bonus Points Award.';
        while ($winners = mysqli_fetch_assoc($res)) {
            extract($winners);
            switch ($i) {
                case 1:
                    $points = 10 * $correct;
                    break;
                case 2:
                    $points = 9 * $correct;
                    break;
                case 3:
                    $points = 8 * $correct;
                    break;
                case 4:
                    $points = 7 * $correct;
                    break;
                case 5:
                    $points = 6 * $correct;
                    break;
                case 6:
                    $points = 5 * $correct;
                    break;
                case 7:
                    $points = 4 * $correct;
                    break;
                case 8:
                    $points = 3 * $correct;
                    break;
                case 9:
                    $points = 2 * $correct;
                    break;
                case 10:
                    $points = 1 * $correct;
                    break;
            }

            $msg = 'You answered ' . number_format($correct) . " trivia question correctly and were awarded $points Bonus Points!!\n";
            $modcomment = $modcomment;
            $modcomment = get_date(TIME_NOW, 'DATE', 1) . " - Awarded Bonus Points for Trivia.\n" . $modcomment;
            $msgs_buffer[] = '(0,' . sqlesc($user_id) . ',' . TIME_NOW . ', ' . sqlesc($msg) . ', ' . sqlesc($subject) . ')';
            $users[] = $user_id;
            $mc1->begin_transaction('user_stats' . $user_id);
            $mc1->update_row(false, [
                'modcomment' => $modcomment,
            ]);
            $mc1->commit_transaction($site_config['expires']['user_stats']);
            sql_query('UPDATE users SET modcomment = ' . sqlesc($modcomment) . ", seedbonus = seedbonus + $points WHERE id = " . sqlesc($user_id)) or sqlerr(__FILE__, __LINE__);
            $count = $i++;
        }
    }

    if (!empty($msgs_buffer)) {
        sql_query('INSERT INTO messages (sender,receiver,added,msg,subject) VALUES ' . implode(', ', $msgs_buffer)) or sqlerr(__FILE__, __LINE__);
    }
    write_log('Cleanup - Trivia Bonus Points awarded to - ' . $count . ' Member(s)');
    foreach ($users as $user_id) {
        $mc1->delete_value('inbox_new_' . $user_id);
        $mc1->delete_value('inbox_new_sb_' . $user_id);
        $mc1->delete_value('userstats_' . $user_id);
        $mc1->delete_value('user_stats_' . $user_id);
        $mc1->delete_value('MyUser_' . $user_id);
        $mc1->delete_value('user' . $user_id);
    }

    sql_query('UPDATE triviaq SET asked = 0, current = 0') or sqlerr(__FILE__, __LINE__);
    sql_query('UPDATE triviasettings SET gameon = 0, finished = NOW() WHERE gameon = 1') or sqlerr(__FILE__, __LINE__);
    sql_query('INSERT INTO triviasettings (gameon, started) VALUES (1, NOW())') or sqlerr(__FILE__, __LINE__);

    if ($data['clean_log'] && $queries > 0) {
        write_log("Trivia Points Cleanup: Completed using $queries queries");
    }
}
