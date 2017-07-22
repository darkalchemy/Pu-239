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
function cleanup_log($data)
{
    $text = sqlesc($data['clean_title']);
    $added = TIME_NOW;
    $ip = sqlesc($_SERVER['REMOTE_ADDR']);
    $desc = sqlesc($data['clean_desc']);
    sql_query("INSERT INTO cleanup_log (clog_event, clog_time, clog_ip, clog_desc) VALUES ($text, $added, $ip, {$desc})") or sqlerr(__FILE__, __LINE__);
}
function docleanup($data)
{
    global $INSTALLER09, $queries, $mc1;
    set_time_limit(1200);
    ignore_user_abort(1);

    // update trivia to get next question
    $sql = "SELECT gameon FROM triviasettings WHERE gamenum = 1";
    $res = sql_query($sql) or sqlerr(__FILE__, __LINE__);
    $result = mysqli_fetch_assoc($res);
    $gameon = $result['gameon'];

    if ($gameon == 1) {
        $sql = "SELECT qid FROM triviaq WHERE asked = 0 AND asked = 0 ORDER BY RAND() LIMIT 0, 1";
        $res = sql_query($sql) or sqlerr(__FILE__, __LINE__);
        $qidarray = mysqli_fetch_assoc($res);
        $qid = $qidarray['qid'];
        // clear previous question
        $sql = "UPDATE triviaq SET current = 0 WHERE current = 1";
        sql_query($sql) or sqlerr(__FILE__, __LINE__);
        // set current question
        $sql = "UPDATE triviaq SET asked = 1, current = 1 WHERE qid = " . sqlesc($qid);
        sql_query($sql) or sqlerr(__FILE__, __LINE__);
    }

    if ($queries > 0) write_log("Updated Trivia Questions Clean -------------------- Trivia Questions cleanup Complete using $queries queries --------------------");
    if (false !== mysqli_affected_rows($GLOBALS["___mysqli_ston"])) {
        $data['clean_desc'] = mysqli_affected_rows($GLOBALS["___mysqli_ston"]) . " items deleted/updated";
    }
    if ($data['clean_log']) {
        cleanup_log($data);
    }
}
?>
