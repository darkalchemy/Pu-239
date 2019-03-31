<?php

require_once __DIR__ . '/../../include/bittorrent.php';
require_once INCL_DIR . 'function_html.php';
require_once INCL_DIR . 'function_trivia.php';
global $session;

$lang = array_merge(load_language('global'), load_language('trivia'));
extract($_POST);

header('content-type: application/json');
if (empty($csrf) || !$session->validateToken($csrf)) {
    echo json_encode(['fail' => 'csrf']);
    die();
}
$current_user = $session->get('userID');
if (empty($current_user)) {
    echo json_encode(['fail' => 'csrf']);
    die();
}

$table = trivia_table();
$qid = $table['qid'];
$gamenum = $table['gamenum'];
$table = $table['table'];
$data = $cache->get('trivia_current_question_');
if (empty($data)) {
    echo json_encode(['fail' => 'invalid']);
    die();
}
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
    echo json_encode([
        'content' => $table . $answered . trivia_clocks(),
        'round' => $cleanup['round'],
        'game' => $cleanup['game'],
    ]);
    die();
}

$question = $output = '';
$answers = [
    'answer1',
    'answer2',
    'answer3',
    'answer4',
    'answer5',
];
if (!empty($data['question'])) {
    $question = "
        <h2 class='bg-00 padding10 bottom10 round5'>" . htmlspecialchars_decode($data['question']) . '</h2>';
}
foreach ($answers as $answer) {
    if (!empty($data[$answer])) {
        $output .= "
        <span id='{$answer}' class='size_4 margin10 trivia-pointer bg-00 round5 padding10' data-csrf='" . $session->get('csrf_token') . "' data-answer='{$answer}'  data-qid='{$qid}' data-gamenum='{$gamenum}' onclick=\"process_trivia('$answer')\">" . htmlspecialchars_decode($data[$answer]) . '</span>';
    }
}
if (!empty($output)) {
    $output = "<div class='level-center'>$output</div>";
    echo json_encode([
        'content' => $question . $output . trivia_clocks(),
        'round' => $cleanup['round'],
        'game' => $cleanup['game'],
    ]);
    die();
}

echo json_encode(['fail' => 'invalid']);
die();
