<?php

require_once __DIR__ . '/../include/bittorrent.php';
require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_html.php';
check_user_status();
$lang = load_language('global');

global $site_config, $CURUSER;

if ($CURUSER['class'] < $site_config['min_to_play']) {
    stderr('Error!', 'Sorry, you must be a ' . $site_config['class_names'][$site_config['min_to_play']] . ' to play in the arcade!');
}

$HTMLOUT = "
            <div class='has-text-centered'>
                <h1>{$site_config['site_name']} Old School Arcade!</h1>
                <span>Top Scores Earn {$site_config['top_score_points']} Karma Points</span>
                <div class='level-center top10'>
                    <a class='altlink' href='{$site_config['baseurl']}/arcade_top_scores.php'>Top Scores</a>
                </div>
            </div>
            <div class='level-center'>";

$list = $site_config['arcade_games_names'];
sort($list);
$i = 0;
foreach ($list as $gamename) {
    $id = $i++;
    $game_id = array_search($gamename, $site_config['arcade_games_names']);
    $game = $site_config['arcade_games'][$game_id];
    $fullgamename = $site_config['arcade_games_names'][$game_id];
    $HTMLOUT .= "
                <div class='margin10 w-20'>
                    <a href='{$site_config['baseurl']}/flash.php?gameURI={$game}.swf&amp;gamename={$game}&amp;game_id={$id}' class='tooltipper' title='{$fullgamename}'>
                        <img src='{$site_config['pic_baseurl']}games/{$game}.png' alt='{$game}' class='round10'>
                    </a>
                </div>";
}
$HTMLOUT .= '
            </div>';

echo stdhead('Arcade') . wrapper($HTMLOUT) . stdfoot();
