<?php

declare(strict_types = 1);

require_once __DIR__ . '/../include/bittorrent.php';
require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_html.php';
check_user_status();
$lang = load_language('global');

global $CURUSER, $site_config;

if ($CURUSER['class'] < $site_config['allowed']['play']) {
    stderr('Error!', 'Sorry, you must be a ' . $site_config['class_names'][$site_config['allowed']['play']] . ' to play in the arcade!');
}

$HTMLOUT = "
            <div class='has-text-centered'>
                <h1>{$site_config['site']['name']} Old School Arcade!</h1>
                <span>Top Scores Earn {$site_config['arcade']['top_score_points']} Karma Points</span>
                <div class='level-center top10'>
                    <a class='altlink' href='{$site_config['paths']['baseurl']}/arcade_top_scores.php'>Top Scores</a>
                </div>
            </div>";

$body = "
            <div class='level-center'>";

$list = $site_config['arcade']['game_names'];
sort($list);
$i = 0;
foreach ($list as $gamename) {
    $id = $i++;
    $game_id = array_search($gamename, $site_config['arcade']['game_names']);
    $game = $site_config['arcade']['games'][$game_id];
    $fullgamename = $site_config['arcade']['game_names'][$game_id];
    $body .= "
                <div class='margin10 w-20'>
                    <a href='{$site_config['paths']['baseurl']}/flash.php?gameURI={$game}.swf&amp;gamename={$game}&amp;game_id={$id}' class='tooltipper' title='" . urlencode($fullgamename) . "'>
                        <img src='{$site_config['paths']['images_baseurl']}games/{$game}.png' alt='{$game}' class='round10'>
                    </a>
                </div>";
}
$body .= '
            </div>';
$HTMLOUT .= main_div($body, 'top20');

echo stdhead('Arcade') . wrapper($HTMLOUT) . stdfoot();
