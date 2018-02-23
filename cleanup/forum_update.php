<?php
/**
 * @param $data
 */
function forum_update($data)
{
    global $queries;
    set_time_limit(1200);
    ignore_user_abort(true);

    sql_query('DELETE FROM now_viewing WHERE added < '.(TIME_NOW - 900));
    $forums = sql_query('SELECT f.id, count( DISTINCT t.id ) AS topics, count(p.id) AS posts
                          FROM forums f
                          LEFT JOIN topics t ON f.id = t.forum_id
                          LEFT JOIN posts p ON t.id = p.topic_id
                          GROUP BY f.id');
    while ($forum = mysqli_fetch_assoc($forums)) {
        $forum['posts'] = $forum['topics'] > 0 ? $forum['posts'] : 0;
        sql_query('UPDATE forums SET post_count = '.sqlesc($forum['posts']).', topic_count = '.sqlesc($forum['topics']).' WHERE id = '.sqlesc($forum['id']));
    }
    if ($data['clean_log'] && $queries > 0) {
        write_log("Forum Cleanup: Completed using $queries queries");
    }
}
