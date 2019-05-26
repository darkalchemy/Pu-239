<?php

declare(strict_types = 1);

use Delight\Auth\Auth;
use Pu239\Cache;
use Pu239\Database;

require_once __DIR__ . '/../../include/bittorrent.php';
require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_trivia.php';
$lang = array_merge(load_language('global'), load_language('trivia'));
global $container;

$gamenum = $qid = 0;

extract($_POST);
header('content-type: application/json');
$auth = $container->get(Auth::class);
$current_user = $auth->getUserId();
$fluent = $container->get(Database::class);
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
$cache = $container->get(Cache::class);
$cache->delete('triviaq_');
$table = trivia_table();
echo json_encode([
    'content' => $table['table'] . $answered . trivia_clocks(),
    'round' => $cleanup['round'],
    'game' => $cleanup['game'],
]);
die();
