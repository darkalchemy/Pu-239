<?php
global $site_config, $CURUSER;

$cache = new DarkAlchemy\Pu239\Cache();

//====  make sure name is what you expect or error... add or remove to match your site
if (isset($_POST['gname'])) {
    $gname = htmlspecialchars($_POST['gname']);
    $all_our_games = $site_config['arcade_games'];
    if (!in_array($gname, $all_our_games)) {
        stderr('Error', 'I smell a fat rat!');
    }
}
//====  make sure level name is what you expect or error... add or remove to match your site
if (isset($_POST['levelName'])) {
    $levelName = htmlspecialchars($_POST['levelName']);
    $all_levels = [
        'LEVEL: SLUG',
        'LEVEL: WORM',
        'LEVEL: PYTHON'
    ];
    if (!in_array($levelName, $all_levels)) {
        stderr('Error', 'I smell a very fat rat!');
    }
}

//=== get score or "gscore"
$score = (isset($_POST['score']) ? intval($_POST['score']) : (isset($_POST['gscore']) ? intval($_POST['gscore']) : 0));
$level = (isset($_POST['level']) ? intval($_POST['level']) : 1);

$highScore = 0;
$highScore = get_one_row('flashscores', 'score', 'WHERE game = ' . sqlesc($gname) . ' ORDER BY score DESC limit 1');

sql_query('INSERT INTO flashscores (game, user_id, level, score) VALUES (' . sqlesc($gname) . ', ' . sqlesc($CURUSER['id']) . ', ' . sqlesc($level) . ', ' . sqlesc($score) . ')') or sqlerr(__FILE__, __LINE__);
$game_id = array_search($gname, $site_config['arcade_games']);
$game = $site_config['arcade_games_names'][$game_id];
$link = '[url=' . $site_config['baseurl'] . '/flash.php?gameURI=' . $gname . '.swf&gamename=' . $gname . '&game_id=' . $game_id . ']' . $game . '[/url]';
//$link = '[url=' . $site_config['baseurl'] . '/arcade.php]' . $game . '[/url]';
$classColor = get_user_class_color($CURUSER['class']);
if ($highScore < $score) {
    $message = "[color=#$classColor][b]{$CURUSER['username']}[/b][/color] has just set a new high score of " . number_format($score) . " in $link and earned {$site_config['top_score_points']} karma points.";
    $bonuscomment = get_one_row('users', 'bonuscomment', 'WHERE id = ' . $CURUSER['id']);
    $bonuscomment = get_date(TIME_NOW, 'DATE', 1) . " - {$site_config['top_score_points']} Points for setting a new high score in $game.\n " . $bonuscomment;
    sql_query('UPDATE users SET seedbonus = seedbonus + ' . sqlesc($site_config['top_score_points']) . ', bonuscomment = ' . sqlesc($bonuscomment) . ' WHERE id = ' . sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);
    $seedbonus = get_one_row('users', 'seedbonus', 'WHERE id = ' . $CURUSER['id']);
    $cache->update_row('user' . $CURUSER['id'], [
        'seedbonus' => $seedbonus,
        'bonuscomment' => $bonuscomment,
    ], $site_config['expires']['user_cache']);
} elseif ($score >= .9 * $highScore) {
    $message = "[color=#$classColor][b]{$CURUSER['username']}[/b][/color] has just played $link and scored a whopping " . number_format($score) . '. Excellent! The high score remains ' . number_format($highScore) . '.';
} else {
    $message = "[color=#$classColor][b]{$CURUSER['username']}[/b][/color] has just played $link and scored a measly " . number_format($score) . '. Try again. The high score remains ' . number_format($highScore) . '.';
}

require_once INCL_DIR . 'user_functions.php';
if ($site_config['autoshout_on'] == 1) {
    autoshout($message);
}
// update alltime high scores
$res = sql_query('SELECT MAX(score) AS score, game FROM flashscores GROUP BY game') or sqlerr(__FILE__, __LINE__);
while ($row = $res->fetch_assoc()) {
    $next = sql_query("SELECT score, game, level, user_id FROM flashscores WHERE game = '" . $row['game'] . "' AND score = " . $row['score']) or sqlerr(__FILE__, __LINE__);
    while ($score = $next->fetch_assoc()) {
        $high = sql_query("SELECT score, game, level, user_id FROM highscores WHERE game = '" . $row['game'] . "'") or sqlerr(__FILE__, __LINE__);
        if (mysqli_num_rows($high) > 0) {
            while ($check = $high->fetch_assoc()) {
                if ($score['game'] === $check['game'] && $score['score'] > $check['score']) {
                    sql_query('UPDATE highscores SET score = ' . $score['score'] . ', level = ' . $score['level'] . ', user_id = ' . $score['user_id'] . " WHERE game = '" . $score['game'] . "'") or sqlerr(__FILE__, __LINE__);
                }
            }
        } else {
            sql_query('INSERT INTO highscores (score, level, user_id, game) VALUES (' . $score['score'] . ', ' . $score['level'] . ', ' . $score['user_id'] . ", '" . $score['game'] . "')") or sqlerr(__FILE__, __LINE__);
        }
    }
}

header('Location: ' . $site_config['baseurl'] . "/arcade_top_scores.php#{$gname}");
