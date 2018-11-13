<?php

require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'user_functions.php';
require_once INCL_DIR . 'trivia_functions.php';
global $session;

$lang = array_merge(load_language('global'), load_language('trivia'));
extract($_POST);

header('content-type: application/json');
if (!$session->validateToken($csrf)) {
    echo json_encode(['fail' => 'csrf']);
    die();
}
if (empty($gamenum) || empty($qid) || empty($answer)) {
    echo json_encode(['fail' => 'invalid']);
    die();
}
$current_user = $session->get('userID');
if (empty($current_user)) {
    echo json_encode(['fail' => 'csrf']);
    die();
}

$correct_answer = $fluent->from('triviaq')
    ->select('canswer')
    ->where('qid = ?', $qid)
    ->fetch('canswer');

$user = $fluent->from('triviausers')
    ->where('user_id = ?', $current_user)
    ->where('qid = ?', $qid)
    ->where('gamenum = ?', $gamenum)
    ->fetch();

$cleanup = trivia_time();

if (!empty($user)) {
    if ($user['correct'] == 1) {
        $answered = "<h3 class='has-text-success top20'>{$lang['trivia_correct']}</h3>";
    } else {
        $answered = "<h3 class='has-text-danger top20'>{$lang['trivia_incorrect']}</h3>";
    }
} else {
    $values = [
        'user_id' => $current_user,
        'gamenum' => $gamenum,
        'qid' => $qid,
        'date' => date('Y-m-d H:i:s'),
    ];
    if ($correct_answer === $answer) {
        $answered = "<h3 class='has-text-success top20'>{$lang['trivia_correct']}</h3>";
        $values['correct'] = 1;
    } else {
        $answered = "<h3 class='has-text-danger top20'>{$lang['trivia_incorrect']}</h3>";
        $values['correct'] = 0;
    }
    $fluent->insertInto('triviausers')
        ->values($values)
        ->execute();
}

$table = trivia_table();
echo json_encode([
    'content' => $table['table'] . $answered . trivia_clocks(),
    'round' => $cleanup['round'],
    'game' => $cleanup['game'],
]);
die();
