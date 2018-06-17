<?php

global $lang;

$dt                  = (TIME_NOW - $readpost_expiry);
$last_posts_read_res = sql_query('SELECT t.id, t.last_post FROM topics AS t LEFT JOIN posts AS p ON p.id = t.last_post AND p.added > ' . $dt) or sqlerr(__FILE__, __LINE__);
while ($last_posts_read_arr = mysqli_fetch_assoc($last_posts_read_res)) {
    $members_last_posts_read_res = sql_query('SELECT id, last_post_read FROM read_posts WHERE user_id = ' . sqlesc($CURUSER['id']) . ' AND topic_id = ' . sqlesc($last_posts_read_arr['id'])) or sqlerr(__FILE__, __LINE__);
    if (0 === mysqli_num_rows($members_last_posts_read_res)) {
        sql_query('INSERT INTO read_posts (user_id, topic_id, last_post_read) VALUES (' . sqlesc($CURUSER['id']) . ', ' . sqlesc($last_posts_read_arr['id']) . ', ' . sqlesc($last_posts_read_arr['last_post']) . ')') or sqlerr(__FILE__, __LINE__);
        $cache->delete('last_read_post_' . $last_posts_read_arr['id'] . '_' . $CURUSER['id']);
        $cache->delete('sv_last_read_post_' . $last_posts_read_arr['id'] . '_' . $CURUSER['id']);
    } else {
        $members_last_posts_read_arr = mysqli_fetch_assoc($members_last_posts_read_res);
        if ($members_last_posts_read_arr['last_post_read'] < $last_posts_read_arr['last_post']) {
            sql_query('UPDATE read_posts SET last_post_read = ' . sqlesc($last_posts_read_arr['last_post']) . ' WHERE id = ' . sqlesc($members_last_posts_read_arr['id'])) or sqlerr(__FILE__, __LINE__);
            $cache->delete('last_read_post_' . $last_posts_read_arr['id'] . '_' . $CURUSER['id']);
            $cache->delete('sv_last_read_post_' . $last_posts_read_arr['id'] . '_' . $CURUSER['id']);
        }
    }
}
//=== ok, all done here, send them back! \o/
header('Location: forums.php?m=1');
die();
