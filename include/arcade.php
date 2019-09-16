<?php

declare(strict_types = 1);

use Pu239\Database;
use Pu239\User;

$user = check_user_status();
global $container, $site_config;

if (isset($_POST['gname'])) {
    $gname = htmlsafechars($_POST['gname']);
    $all_our_games = $site_config['arcade']['games'];
    if (!in_array($gname, $all_our_games)) {
        return false;
    }
} elseif (isset($_POST['func']) && $_POST['func'] === 'storeScore') {
    $gname = 'ms-pac-man';
}
if (isset($_POST['levelName'])) {
    $levelName = htmlsafechars($_POST['levelName']);
    $all_levels = [
        'LEVEL: SLUG',
        'LEVEL: WORM',
        'LEVEL: PYTHON',
    ];
    if (!in_array($levelName, $all_levels)) {
        return false;
    }
}
$fluent = $container->get(Database::class);
$score = isset($_POST['score']) ? (int) $_POST['score'] : (isset($_POST['gscore']) ? (int) $_POST['gscore'] : 0);
$level = isset($_POST['level']) ? (int) $_POST['level'] : 1;
$values = [
    'game' => $gname,
    'user_id' => $user['id'],
    'level' => $level,
    'score' => $score,
];
$fluent->insertInto('flashscores')
       ->values($values)
       ->execute();

$game_id = array_search($gname, $site_config['arcade']['games']);
$game = $site_config['arcade']['game_names'][$game_id];
$link = '[url=' . $site_config['paths']['baseurl'] . '/flash.php?gameURI=' . $gname . '.swf&gamename=' . $gname . '&game_id=' . $game_id . ']' . $game . '[/url]';
$classColor = get_user_class_color($user['class']);
$scores = $fluent->from('flashscores')
                 ->select(null)
                 ->select('score')
                 ->where('game = ?', $gname)
                 ->where('score != ?', $score)
                 ->orderBy('level DESC')
                 ->orderBy('score DESC')
                 ->limit(1)
                 ->fetch('score');
$highScore = !empty($scores) ? $scores[0]['score'] : 0;
if ($highScore < $score) {
    $message = "[color=#$classColor][b]{$user['username']}[/b][/color] has just set a new high score of " . number_format($score) . " in $link and earned {$site_config['arcade']['top_score_points']} karma points.";
    $bonuscomment = get_date((int) TIME_NOW, 'DATE', 1) . " - {$site_config['arcade']['top_score_points']} Points for setting a new high score in $game.\n ";
    $set = [
        'bonuscomment' => $bonuscomment . $user['bonuscomment'],
        'seedbonus' => $site_config['arcade']['top_score_points'] + $user['seedbonus'],
    ];
    $users_class = $container->get(User::class);
    $users_class->update($set, $user['id']);
} elseif ($score >= .9 * $highScore) {
    $message = "[color=#$classColor][b]" . format_comment($user['username']) . "[/b][/color] has just played $link and scored a whopping " . number_format($score) . '. Excellent! The high score remains ' . number_format($highScore) . '.';
} else {
    $message = "[color=#$classColor][b]" . format_comment($user['username']) . "[/b][/color] has just played $link and scored a measly " . number_format($score) . '. Try again. The high score remains ' . number_format($highScore) . '.';
}

if ($site_config['site']['autoshout_chat']) {
    require_once INCL_DIR . 'function_users.php';
    autoshout($message);
}
$high = $fluent->from('highscores')
               ->select(null)
               ->select('score')
               ->where('game = ?', $gname)
               ->fetch('score');

$update = [
    'score' => $score,
    'level' => $level,
    'user_id' => $user['id'],
];
if (!empty($high) && $highScore > $high) {
    $fluent->update('highscores')
           ->set($update)
           ->where('game = ?', $gname)
           ->execute();
} elseif (empty($high)) {
    $set = [
        'game' => $gname,
        'score' => $score,
        'level' => $level,
        'user_id' => $user['id'],
    ];
    $fluent->insertInto('highscores')
           ->values($values)
           ->execute();
}
header('Location: ' . $site_config['paths']['baseurl'] . "/arcade_top_scores.php#{$gname}");
