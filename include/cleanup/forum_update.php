<?php
function forum_update($data)
{
    global $site_config, $queries, $mc1;
    set_time_limit(1200);
    ignore_user_abort(true);
    //=== delete from now viewing after 15 minutes
    sql_query('DELETE FROM now_viewing WHERE added < ' . (TIME_NOW - 900));
    //=== fix any messed up counts
    $forums = sql_query('SELECT f.id, count( DISTINCT t.id ) AS topics, count(p.id) AS posts
                          FROM forums f
                          LEFT JOIN topics t ON f.id = t.forum_id
                          LEFT JOIN posts p ON t.id = p.topic_id
                          GROUP BY f.id');
    while ($forum = mysqli_fetch_assoc($forums)) {
        $forum['posts'] = $forum['topics'] > 0 ? $forum['posts'] : 0;
        sql_query('update forums set post_count = ' . sqlesc($forum['posts']) . ', topic_count = ' . sqlesc($forum['topics']) . ' where id=' . sqlesc($forum['id']));
    }
    if ($queries > 0) {
        write_log("Forum Cleanup: Completed using $queries queries");
    }
    if (false !== mysqli_affected_rows($GLOBALS['___mysqli_ston'])) {
        $data['clean_desc'] = mysqli_affected_rows($GLOBALS['___mysqli_ston']) . ' items updated';
    }
    if ($data['clean_log']) {
        cleanup_log($data);
    }
}
