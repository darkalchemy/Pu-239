<?php

function trivia_update($data)
{
    $time_start = microtime(true);
    global $fluent, $cache;

    set_time_limit(1200);
    ignore_user_abort(true);

    $count = $cache->get('trivia_questions_count_');
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
            $qids = $cache->get('triviaquestions_');
            if ($qids === false || is_null($qids)) {
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
            for ($x = 0; $x <= 10; ++$x) {
                shuffle($qids);
            }
            $qid = array_pop($qids);
            $cache->replace('triviaquestions_', $qids, 0);
            if (count($qids) <= 100) {
                $set = [
                    'asked' => 0,
                    'current' => 0,
                ];
                $fluent->update('triviaq')
                    ->set($set)
                    ->execute();
                $cache->delete('triviaquestions_');
            }

            $cache->set('trivia_current_qid_', (int) $qid, 360);
            $cache->deleteMulti([
                'trivia_gamenum_',
                'trivia_remaining_',
                'trivia_current_question_',
                'trivia_correct_answer_',
            ]);

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
        }
    }

    $time_end = microtime(true);
    $run_time = $time_end - $time_start;
    $text = " Run time: $run_time seconds";
    echo $text . "\n";
    if ($data['clean_log']) {
        write_log('Trivia Questions Cleanup completed' . $text);
    }
}
