<?php

declare(strict_types = 1);

use Pu239\Database;
use Pu239\User;

global $container, $CURUSER, $site_config;

if (isset($_POST['gname'])) {
    $gname = htmlsafechars($_POST['gname']);
    $all_our_games = $site_config['arcade']['games'];
    if (!in_array($gname, $all_our_games)) {
        stderr('Error', 'I smell a fat rat!');
    }
}

if (isset($_POST['levelName'])) {
    $levelName = htmlsafechars($_POST['levelName']);
    $all_levels = [
        'LEVEL: SLUG',
        'LEVEL: WORM',
        'LEVEL: PYTHON',
    ];
    if (!in_array($levelName, $all_levels)) {
        stderr('Error', 'I smell a very fat rat!');
    }
}

$score = isset($_POST['score']) ? (int) $_POST['score'] : (isset($_POST['gscore']) ? (int) $_POST['gscore'] : 0);
$level = isset($_POST['level']) ? (int) $_POST['level'] : 1;

$highScore = 0;
$fluent = $container->get(Database::class);
$highScore = $fluent->from('flashscores')
                    ->select(null)
                    ->select('score')
                    ->where('game = ?', $gname)
                    ->orderBy('score DESC')
                    ->limit(1)
                    ->fetch('score');

$values = [
    'game' => $gname,
    'user_id' => $CURUSER['id'],
    'level' => $level,
    'score' => $score,
];
$fluent->insertInto('flashscores')
       ->values($values)
       ->execute();

$game_id = array_search($gname, $site_config['arcade']['games']);
$game = $site_config['arcade']['game_names'][$game_id];
$link = '[url=' . $site_config['paths']['baseurl'] . '/flash.php?gameURI=' . $gname . '.swf&gamename=' . $gname . '&game_id=' . $game_id . ']' . $game . '[/url]';
$classColor = get_user_class_color($CURUSER['class']);
if ($highScore < $score) {
    $message = "[color=#$classColor][b]{$CURUSER['username']}[/b][/color] has just set a new high score of " . number_format($score) . " in $link and earned {$site_config['arcade']['top_score_points']} karma points.";
    $bonuscomment = get_date((int) TIME_NOW, 'DATE', 1) . " - {$site_config['arcade']['top_score_points']} Points for setting a new high score in $game.\n ";
    $set = [
        'bonuscomment' => $bonuscomment . $CURUSER['bonuscomment'],
        'seedbonus' => $site_config['arcade']['top_score_points'] + $CURUSER['seedbonus'],
    ];
    $users_class = $container->get(User::class);
    $users_class->update($set, $CURUSER['id']);
} elseif ($score >= .9 * $highScore) {
    $message = "[color=#$classColor][b]" . format_comment($CURUSER['username']) . "[/b][/color] has just played $link and scored a whopping " . number_format($score) . '. Excellent! The high score remains ' . number_format($highScore) . '.';
} else {
    $message = "[color=#$classColor][b]" . format_comment($CURUSER['username']) . "[/b][/color] has just played $link and scored a measly " . number_format($score) . '. Try again. The high score remains ' . number_format($highScore) . '.';
}

require_once INCL_DIR . 'function_users.php';
if ($site_config['site']['autoshout_chat'] || $site_config['site']['autoshout_irc']) {
    autoshout($message);
}
$max = $fluent->from('flashscores')
              ->select(null)
              ->select('MAX(score) AS score')
              ->select('game')
              ->groupBy('game');

foreach ($max as $row) {
    $next = $fluent->from('flashscores')
                   ->where('game = ?', $row['game'])
                   ->where('score = ?', $row['score']);
    foreach ($next as $score) {
        $high = $fluent->from('highscores')
                       ->where('game = ?', $row['game'])
                       ->where('score = ?', $row['score'])
                       ->fetchAll();

        if (!empty($high)) {
            foreach ($high as $check) {
                if ($score['game'] === $check['game'] && $score['score'] > $check['score']) {
                    $set = [
                        'score' => $score['score'],
                        'level' => $score['level'],
                        'user_id' => $score['user_id'],
                    ];
                    $fluent->update('highscores')
                           ->set($set)
                           ->where('game = ?', $score['game'])
                           ->execute();
                }
            }
        } else {
            $values = [
                'score' => $score['score'],
                'level' => $score['level'],
                'user_id' => $score['user_id'],
                'game' => $score['game'],
            ];
            $fluent->insertInto('highscores')
                   ->values($values)
                   ->execute();
        }
    }
}

header('Location: ' . $site_config['paths']['baseurl'] . "/arcade_top_scores.php#{$gname}");
