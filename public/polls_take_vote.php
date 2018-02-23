<?php

require_once dirname(__FILE__, 2).DIRECTORY_SEPARATOR.'include'.DIRECTORY_SEPARATOR.'bittorrent.php';
require_once INCL_DIR.'user_functions.php';
check_user_status();
global $CURUSER, $site_config, $fluent, $cache;

$lang = load_language('global');
$poll_id = isset($_GET['pollid']) ? intval($_GET['pollid']) : false;
if (!is_valid_id($poll_id)) {
    stderr('ERROR', 'No poll with that ID');
}
$vote_cast = [];
$_POST['choice'] = isset($_POST['choice']) ? $_POST['choice'] : [];

$sql = "SELECT * FROM polls
            LEFT JOIN poll_voters ON polls.pid = poll_voters.poll_id
            AND poll_voters.user_id = {$CURUSER['id']}
            WHERE pid = ".sqlesc($poll_id);
$query = sql_query($sql) or sqlerr(__FILE__, __LINE__);
if (1 == !mysqli_num_rows($query)) {
    stderr('ERROR', 'No poll with that ID');
}
$poll_data = mysqli_fetch_assoc($query);
if (!empty($poll_data['user_id'])) {
    stderr('ERROR', 'You have already voted!');
}
$_POST['nullvote'] = isset($_POST['nullvote']) ? $_POST['nullvote'] : 0;
if (!$_POST['nullvote']) {
    if (is_array($_POST['choice']) and count($_POST['choice'])) {
        foreach ($_POST['choice'] as $question_id => $choice_id) {
            if (!$question_id or !isset($choice_id)) {
                continue;
            }
            $vote_cast[$question_id][] = $choice_id;
        }
    }
    foreach ($_POST as $k => $v) {
        if (preg_match("#^choice_(\d+)_(\d+)$#", $k, $matches)) {
            if (1 == $_POST[$k]) {
                $vote_cast[$matches[1]][] = $matches[2];
            }
        }
    }
    $poll_answers = unserialize(stripslashes($poll_data['choices']));
    reset($poll_answers);
    if (!empty($vote_cast) && count($vote_cast) < count($poll_answers)) {
        stderr('ERROR', 'No vote');
    }
    $sql = "INSERT INTO poll_voters (user_id, ip, poll_id, vote_date) VALUES ({$CURUSER['id']}, ".ipToStorageFormat($CURUSER['ip']).", {$poll_data['pid']}, ".TIME_NOW.')';
    sql_query($sql) or sqlerr(__FILE__, __LINE__);
    $votes = $poll_data['votes'] + 1;
    $cache->update_row('poll_data_'.$CURUSER['id'], [
        'votes' => $votes,
        'ip' => $CURUSER['ip'],
        'user_id' => $CURUSER['id'],
        'vote_date' => TIME_NOW,
    ], $site_config['expires']['poll_data']);
    if (-1 == mysqli_affected_rows($GLOBALS['___mysqli_ston'])) {
        stderr('DBERROR', 'Could not update records');
    }
    foreach ($vote_cast as $question_id => $choice_array) {
        foreach ($choice_array as $choice_id) {
            ++$poll_answers[$question_id]['votes'][$choice_id];
            if ($poll_answers[$question_id]['votes'][$choice_id] < 1) {
                $poll_answers[$question_id]['votes'][$choice_id] = 1;
            }
        }
    }
    $poll_data['choices'] = addslashes(serialize($poll_answers));
    sql_query("UPDATE polls set votes = votes + 1, choices = '{$poll_data['choices']}' WHERE pid = {$poll_data['pid']}") or sqlerr(__FILE__, __LINE__);
    if (-1 == mysqli_affected_rows($GLOBALS['___mysqli_ston'])) {
        stderr('DBERROR', 'Could not update records');
    }
} else {
    sql_query("INSERT INTO poll_voters (user_id, ip, poll_id, vote_date)
                VALUES
                ({$CURUSER['id']}, ".ipToStorageFormat($CURUSER['ip']).", {$poll_data['pid']}, ".TIME_NOW.')') or sqlerr(__FILE__, __LINE__);
    $votes = $poll_data['votes'] + 1;
    $cache->update_row('poll_data_'.$CURUSER['id'], [
        'votes' => $votes,
        'ip' => $CURUSER['ip'],
        'user_id' => $CURUSER['id'],
        'vote_date' => TIME_NOW,
    ], $site_config['expires']['poll_data']);
    if (-1 == mysqli_affected_rows($GLOBALS['___mysqli_ston'])) {
        stderr('DBERROR', 'Could not update records');
    }
}
header("location: {$site_config['baseurl']}/index.php#poll");
