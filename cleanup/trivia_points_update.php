<?php

function trivia_points_update($data)
{
    global $site_config, $cache, $message_stuffs, $fluent;

    set_time_limit(1200);
    ignore_user_abort(true);
    $dt = TIME_NOW;
    $count = 0;
    $msgs = [];
    $i = 1;

    $gamenum = $fluent->from('triviasettings')
        ->select(null)
        ->select('gamenum')
        ->where('gameon = 1')
        ->fetch('gamenum');

    $results = $fluent->from('triviausers AS t')
            ->select(null)
            ->select('t.user_id')
            ->select('COUNT(t.correct) AS correct')
            ->select('u.seedbonus')
            ->select('u.username')
            ->select('u.modcomment')
            ->innerJoin('users AS  u ON t.user_id = u.id')
            ->where('t.correct = 1')
            ->where('gamenum = ?', $gamenum)
            ->groupBy('t.user_id')
            ->orderBy('correct DESC')
            ->limit(10)
            ->fetchAll();

    if ($results) {
        $subject = 'Trivia Bonus Points Award.';
        foreach ($results as $winners) {
            $correct = $modcomment = $user_id = '';
            extract($winners);
            $points = 0;
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

            $msg = 'You answered ' . number_format($correct) . ' trivia question' . plural($correct) . " correctly and were awarded $points Bonus Points!!\n";
            $comment = get_date(TIME_NOW, 'DATE', 1) . " - Awarded Bonus Points for Trivia.\n";
            $modcomment = $comment . $modcomment;
            $msgs[] = [
                'sender' => 0,
                'receiver' => $user_id,
                'added' => $dt,
                'msg' => $msg,
                'subject' => $subject,
            ];

            $points = $seedbonus + $points;
            $user = $cache->get('user' . $user_id);
            if (!empty($user)) {
                $cache->update_row('user' . $user_id, [
                    'modcomment' => $modcomment,
                    'seedbonus' => $points,
                ], $site_config['expires']['user_cache']);
            }
            $set = [
                'modcomment' => new Envms\FluentPDO\Literal("CONCAT(\"$comment\", modcomment)"),
                'seedbonus' => $points,
            ];
            $fluent->update('users')
                ->set($set)
                ->where('id = ?', $user_id)
                ->execute();
            $count = $i++;
        }
    }

    if (!empty($msgs)) {
        $message_stuffs->insert($msgs);
    }

    $set = [
        'gameon' => 0,
        'finished' => date('Y-m-d H:i:s', $dt),
    ];
    $fluent->update('triviasettings')
        ->set($set)
        ->where('gameon = 1')
        ->execute();

    $values = [
        'gameon' => 1,
        'started' => date('Y-m-d H:i:s', $dt),
    ];
    $fluent->insertInto('triviasettings')
        ->values($values)
        ->execute();

    if ($data['clean_log']) {
        write_log('Cleanup - Trivia Bonus Points awarded to - ' . $count . ' Member(s)');
    }
}
