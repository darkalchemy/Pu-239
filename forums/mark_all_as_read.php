<?php
/**
 |--------------------------------------------------------------------------|
 |   https://github.com/Bigjoos/                			    |
 |--------------------------------------------------------------------------|
 |   Licence Info: GPL			                                    |
 |--------------------------------------------------------------------------|
 |   Copyright (C) 2010 U-232 V4					    |
 |--------------------------------------------------------------------------|
 |   A bittorrent tracker source based on TBDev.net/tbsource/bytemonsoon.   |
 |--------------------------------------------------------------------------|
 |   Project Leaders: Mindless,putyn.					    |
 |--------------------------------------------------------------------------|
  _   _   _   _   _     _   _   _   _   _   _     _   _   _   _
 / \ / \ / \ / \ / \   / \ / \ / \ / \ / \ / \   / \ / \ / \ / \
( U | - | 2 | 3 | 2 )-( S | o | u | r | c | e )-( C | o | d | e )
 \_/ \_/ \_/ \_/ \_/   \_/ \_/ \_/ \_/ \_/ \_/   \_/ \_/ \_/ \_/
 */
/**********************************************************
New 2010 forums that don't suck for TB based sites....
pretty much coded page by page, but coming from a 
history ot TBsourse and TBDev and the many many 
coders who helped develop them over time.
proper credits to follow :)

beta monday aug 2nd 2010 v0.1

taken from the old code and using Retros 
READPOST mod and updated a bit to work with new forums :D
**********************************************************/
if (!defined('BUNNY_FORUMS')) {
    $HTMLOUT = '';
    $HTMLOUT.= '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
        <html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
        <head>
        <meta http-equiv="content-type" content="text/html; charset=iso-8859-1" />
        <title>ERROR</title>
        </head><body>
        <h1 style="text-align:center;">ERROR</h1>
        <p style="text-align:center;">How did you get here? silly rabbit Trix are for kids!.</p>
        </body></html>';
    echo $HTMLOUT;
    exit();
}
global $lang;
$dt = (TIME_NOW - $readpost_expiry);
$last_posts_read_res = sql_query('SELECT t.id, t.last_post FROM topics AS t LEFT JOIN posts AS p ON p.id = t.last_post AND p.added > ' . $dt);
while ($last_posts_read_arr = mysqli_fetch_assoc($last_posts_read_res)) {
    $members_last_posts_read_res = sql_query('SELECT id, last_post_read FROM read_posts WHERE user_id=' . sqlesc($CURUSER['id']) . ' and topic_id=' . sqlesc($last_posts_read_arr['id']));
    if (mysqli_num_rows($members_last_posts_read_res) === 0) {
        sql_query('INSERT INTO read_posts (user_id, topic_id, last_post_read) VALUES (' . sqlesc($CURUSER['id']) . ', ' . sqlesc($last_posts_read_arr['id']) . ', ' . sqlesc($last_posts_read_arr['last_post']) . ')');
        $mc1->delete_value('last_read_post_' . $last_posts_read_arr['id'] . '_' . $CURUSER['id']);
        $mc1->delete_value('sv_last_read_post_' . $last_posts_read_arr['id'] . '_' . $CURUSER['id']);
    } else {
        $members_last_posts_read_arr = mysqli_fetch_assoc($members_last_posts_read_res);
        if ($members_last_posts_read_arr['last_post_read'] < $last_posts_read_arr['last_post']) {
            sql_query('UPDATE read_posts SET last_post_read=' . sqlesc($last_posts_read_arr['last_post']) . ' WHERE id=' . sqlesc($members_last_posts_read_arr['id']));
            $mc1->delete_value('last_read_post_' . $last_posts_read_arr['id'] . '_' . $CURUSER['id']);
            $mc1->delete_value('sv_last_read_post_' . $last_posts_read_arr['id'] . '_' . $CURUSER['id']);
        }
    }
}
//=== ok, all done here, send them back! \o/
header('Location: forums.php?m=1');
die();
?>