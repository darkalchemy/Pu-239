<?php

declare(strict_types = 1);

use DI\DependencyException;
use DI\NotFoundException;
use MatthiasMullie\Scrapbook\Exception\UnbegunTransaction;
use Pu239\Database;
use Pu239\Message;
use Pu239\User;

/**
 * @param $data
 *
 * @throws DependencyException
 * @throws NotFoundException
 * @throws \Envms\FluentPDO\Exception
 * @throws UnbegunTransaction
 */
function trivia_points_update($data)
{
    global $container;

    $time_start = microtime(true);
    $dt = TIME_NOW;
    $count = 0;
    $msgs = [];
    $i = 1;
    $fluent = $container->get(Database::class);
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
                      ->select('u.modcomment')
                      ->innerJoin('users AS  u ON t.user_id = u.id')
                      ->where('t.correct = 1')
                      ->where('gamenum = ?', $gamenum)
                      ->groupBy('t.user_id')
                      ->groupBy('u.seedbonus')
                      ->groupBy('u.modcomment')
                      ->orderBy('correct DESC')
                      ->limit(10)
                      ->fetchAll();

    if ($results) {
        $users_class = $container->get(User::class);
        $subject = 'Trivia Bonus Points Award.';
        foreach ($results as $winners) {
            $user_id = $winners['user_id'];
            $seedbonus = $winners['seedbonus'];
            $correct = $winners['correct'];
            $modcomment = $winners['modcomment'];
            $points = $winners['points'];
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
            $comment = get_date((int) TIME_NOW, 'DATE', 1) . " - Awarded Bonus Points for Trivia.\n";
            $msgs[] = [
                'receiver' => $user_id,
                'added' => $dt,
                'msg' => $msg,
                'subject' => $subject,
            ];
            $values = [
                'modcomment' => $comment . $modcomment,
                'seedbonus' => $seedbonus + $points,
            ];
            $users_class->update($values, $user_id);
            $count = $i++;
        }
    }

    if (!empty($msgs)) {
        $messages_class = $container->get(Message::class);
        $messages_class->insert($msgs);
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

    $time_end = microtime(true);
    $run_time = $time_end - $time_start;
    $text = " Run time: $run_time seconds";
    echo $text . "\n";
    if ($data['clean_log']) {
        write_log('Cleanup - Trivia Bonus Points awarded to - ' . $count . ' Member(s).' . $text);
    }
}
