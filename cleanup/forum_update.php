<?php

/**
 * @param $data
 */
function forum_update($data)
{
    global $fluent;

    set_time_limit(1200);
    ignore_user_abort(true);

    $fluent->deleteFrom('now_viewing')
        ->where('added < ?', TIME_NOW - 900)
        ->execute();

    $forums = $fluent->from('forums')
        ->select(null)
        ->select('forums.id')
        ->select('COUNT(DISTINCT topics.id) AS topics')
        ->select('COUNT(posts.id) AS posts')
        ->leftJoin('topics ON forums.id = topics.forum_id')
        ->leftJoin('posts ON topics.id = posts.topic_id')
        ->groupBy('forums.id');

    $i = 1;
    foreach ($forums as $forum) {
        $forum['posts'] = $forum['topics'] > 0 ? $forum['posts'] : 0;
        $set = [
            'post_count' => $forum['posts'],
            'topic_count' => $forum['topics'],
        ];
        $fluent->update('forums')
            ->set($set)
            ->where('id = ?', $forum['id'])
            ->execute();
        $i++;
    }
    if ($data['clean_log']) {
        write_log("Forum Cleanup: Completed using $i queries");
    }
}
