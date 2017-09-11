<?php
require_once realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..') . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR.'user_functions.php';
check_user_status();
$lang = load_language('global');

global $site_config, $CURUSER;
if ($CURUSER['class'] < UC_USER) {
    stderr('Error!', 'Sorry, you must be this tall to play on this game... User and up can play in the arcade!');
}

$HTMLOUT = "
        <div class='container-fluid portlet top10'>
            <div class='text-center'>
                <h1>{$site_config['site_name']} Old School Arcade!</h1>
                <span>Top Scores Earn {$site_config['top_score_points']} Karma Points</span>
                <br><br>
                <span><a class='altlink' href='arcade_top_scores.php'>Top Scores</a></span>
               <br><br>
            </div>
            <div class='flex-grid'>";

$list = $site_config['arcade_games_names'];
sort($list);
foreach ($list as $gamename) {
    $id = $i + 1;
    $game_id = array_search($gamename, $site_config['arcade_games_names']);
    $game = $site_config['arcade_games'][$game_id];
    $HTMLOUT .= "
                <div class='flex_cell_3'>
                    <a class='altlink' href='flash.php?gameURI=" . $game . '.swf&amp;gamename=' . $game . '&amp;game_id=' . $id . "'>
                        <img style='width:100%;height:auto;max-height:100%;' src='" . $site_config['pic_base_url'] . 'games/' . $game . ".png' alt='" . $game . "' />
                    </a>
                </div>";


}
$HTMLOUT .= '
            </div>
        </div>';

echo stdhead('Arcade').$HTMLOUT.stdfoot();
