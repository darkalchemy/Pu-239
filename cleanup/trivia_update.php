<?php

declare(strict_types = 1);

use DI\DependencyException;
use DI\NotFoundException;
use Pu239\Cache;
use Pu239\Database;

/**
 * @param $data
 *
 * @throws \Envms\FluentPDO\Exception
 * @throws DependencyException
 * @throws NotFoundException
 *
 * @return bool
 */
function trivia_update($data)
{
    global $container;

    $time_start = microtime(true);
    $cache = $container->get(Cache::class);
    $count = $cache->get('trivia_questions_count_');
    $fluent = $container->get(Database::class);
    if ($count === false || is_null($count)) {
        $count = $fluent->from('triviaq')
                        ->select(null)
                        ->select('COUNT(qid) AS count')
                        ->fetch('count');
        $cache->set('trivia_questions_count_', $count, 900);
    }
    if ($count > 0) {
        $gamenum = $fluent->from('triviasettings')
                          ->select(null)
                          ->select('gamenum')
                          ->where('gameon = 1')
                          ->fetch('gamenum');
        if ($gamenum >= 1) {
            $qids = get_qids();
            if (empty($qids) || count($qids) <= 100) {
                $set = [
                    'asked' => 0,
                    'current' => 0,
                ];
                $fluent->update('triviaq')
                       ->set($set)
                       ->where('asked = 1')
                       ->execute();
                $cache->delete('triviaquestions_');
                $qids = get_qids();
            }
            if (empty($qids)) {
                return false;
            }
            for ($x = 0; $x <= 100; ++$x) {
                shuffle($qids);
            }
            $qid = array_pop($qids);
            if (empty($qid)) {
                return false;
            }
            $cache->delete('triviaq_');

            $set = [
                'current' => 0,
            ];
            $fluent->update('triviaq')
                   ->set($set)
                   ->where('current = 1')
                   ->execute();
            $set = [
                'asked' => 1,
                'current' => 1,
            ];
            $fluent->update('triviaq')
                   ->set($set)
                   ->where('qid = ?', $qid)
                   ->execute();

            $values = $fluent->from('triviaq')
                             ->select('question')
                             ->select('answer1')
                             ->select('answer2')
                             ->select('answer3')
                             ->select('answer4')
                             ->select('answer5')
                             ->select('asked')
                             ->where('qid=?', $qid)
                             ->fetch();
            $cache->set('trivia_current_question_', $values, 360);
        }
    }

    $time_end = microtime(true);
    $run_time = $time_end - $time_start;
    $text = " Run time: $run_time seconds";
    echo $text . "\n";
    if ($data['clean_log']) {
        write_log('Trivia Questions Cleanup completed' . $text);
    }

    return true;
}

/**
 * @throws \Envms\FluentPDO\Exception
 * @throws DependencyException
 * @throws NotFoundException
 *
 * @return array|bool|mixed
 */
function get_qids()
{
    global $container;

    $cache = $container->get(Cache::class);
    $qids = $cache->get('triviaquestions_');
    if ($qids === false || is_null($qids)) {
        $fluent = $container->get(Database::class);
        $result = $fluent->from('triviaq')
                         ->select(null)
                         ->select('qid')
                         ->where('asked = 0')
                         ->where('current = 0')
                         ->fetchall('qid');
        foreach ($result as $qidarray) {
            $qids[] = $qidarray['qid'];
        }
        $cache->set('triviaquestions_', $qids, 0);
    }

    return $qids;
}
