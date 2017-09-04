<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR.'user_functions.php';
check_user_status();
$lang = load_language('global');

global $INSTALLER09, $CURUSER;
if ($CURUSER['class'] < UC_USER) {
    stderr('Error!', 'Sorry, you must be this tall to play on this game... User and up can play in the arcade!');
}

$HTMLOUT = "
        <div class='container-fluid portlet top10'>
            <div class='text-center'>
                <h1>{$INSTALLER09['site_name']} Old School Arcade!</h1>
                <span>Top Scores Earn {$INSTALLER09['top_score_points']} Karma Points</span>
                <br><br>
                <span><a class='altlink' href='arcade_top_scores.php'>Top Scores</a></span>
               <br><br>
            </div>
            <div class='flex-grid'>";

$list = $INSTALLER09['arcade_games_names'];
sort($list);
foreach ($list as $gamename) {
    $id = $i + 1;
    $game_id = array_search($gamename, $INSTALLER09['arcade_games_names']);
    $game = $INSTALLER09['arcade_games'][$game_id];
    $HTMLOUT .= "
                <div class='flex_cell_3'>
                    <a class='altlink' href='flash.php?gameURI=" . $game . '.swf&amp;gamename=' . $game . '&amp;game_id=' . $id . "'>
                        <img style='width:100%;height:auto;max-height:100%;' src='" . $INSTALLER09['pic_base_url'] . 'games/' . $game . ".png' alt='" . $game . "' />
                    </a>
                </div>";


}
$HTMLOUT .= '
            </div>
        </div>';

echo stdhead('Arcade').$HTMLOUT.stdfoot();
