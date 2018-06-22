<?php

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'user_functions.php';
require_once INCL_DIR . 'html_functions.php';
check_user_status();

$lang = load_language('global');
global $site_config, $CURUSER;

$scores = '';
$player = $CURUSER['id'];

$all_our_games = $site_config['arcade_games'];

//=== make sure that the gamename is what it is supposed to be... add or subtract games you have...
if (isset($_GET['gamename'])) {
    $gamename = strip_tags($_GET['gamename']);
    if (!in_array($gamename, $all_our_games)) {
        stderr('Error', 'No game with that name! (' . $gamename . ')');
    }
}

$game_name = str_replace('_', ' ', $gamename);
//=== make sure that the gameuri is what it is supposed to be... add or subtract games you have... be sure to add the .swf extention
if (isset($_GET['gameURI'])) {
    $gameURI = strip_tags($_GET['gameURI']);
    $gameURIclean = str_replace('.swf', '', $gameURI);
    if (!in_array($gameURIclean, $all_our_games)) {
        stderr('Error', 'Could not find game!');
    }
}
if (!isset($CURUSER['gameheight']) || $CURUSER['gameheight'] === 0) {
    $game_height = 800;
} else {
    $game_height = $CURUSER['gameheight'];
}
$game_width = $game_height;

$HTMLOUT = '';
$HTMLOUT .= "
        <div class='bottom20'>
            <ul class='level-center bg-06'>
                <li class='altlink margin20'>
                    <a href='{$site_config['baseurl']}/arcade.php'>Arcade</a>
                </li>
                <li class='altlink margin20>
                    <a href='{$site_config['baseurl']}/arcade_top_scores.php'>Top Scores</a>
                </li>
            </ul>
        </div>
        <h1>{$site_config['site_name']} Old School Arcade!</h1>
        <span>Top Scores Earn {$site_config['top_score_points']} Karma Points</span>";

$HTMLOUT .= "
        <div class='bordered top20'>
            <div class='alt_bordered bg-00 has-text-centered'>
                <object classid='clsid:D27CDB6E-AE6D-11cf-96B8-444553540000' codebase='http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=5,0,0,0' width='{$game_width}' height='{$game_height}'>
                    <param name='movie' value='./media/flash_games/{$gameURI}' />
                    <param name='quality' value='high' />
                    <embed src='{$site_config['baseurl']}/media/flash_games/{$gameURI}' quality='high' pluginspage='http://www.macromedia.com/shockwave/download/index.cgi?P1_Prod_Version=ShockwaveFlash type=application/x-shockwave-flash' width='{$game_width}' height='{$game_height}'></embed>
                </object>
            </div>
        </div>";

$res = sql_query('SELECT * FROM flashscores WHERE game = ' . sqlesc($gamename) . ' ORDER BY score DESC LIMIT 15') or sqlerr(__FILE__, __LINE__);

if (mysqli_num_rows($res) > 0) {
    $id = array_search($gamename, $site_config['arcade_games']);
    $fullgamename = $site_config['arcade_games_names'][$id];
    $HTMLOUT .= "
        <table class='table table-bordered table-striped top20 bottom20'>
            <thead>
                <tr>
                    <th colspan='4'>
                        <div class='size_4 has-text-centered'>
                            $fullgamename
                        </div>
                    </th>
                </tr>
                <tr>
                    <th>Rank</th>
                    <th>Name</th>
                    <th>Level</th>
                    <th>Score</th>
                </tr>
            </thead>
            <tbody>";
    $at_score_res = sql_query('SELECT * FROM highscores WHERE game = ' . sqlesc($gamename) . ' ORDER BY score DESC LIMIT 15') or sqlerr(__FILE__, __LINE__);
    while ($at_score_arr = mysqli_fetch_assoc($at_score_res)) {
        $at_username = format_username($at_score_arr['user_id']);
        $at_ranking = sql_query('SELECT COUNT(id) FROM highscores WHERE game = ' . sqlesc($gamename) . ' AND score > ' . sqlesc($at_score_arr['score'])) or sqlerr(__FILE__, __LINE__);
        $at_rankrow = mysqli_fetch_row($at_ranking);
        $HTMLOUT .= '
                <tr' . ($at_score_arr['user_id'] == $CURUSER['id'] ? ' class="has-text-primary text-shadow"' : '') . '>
                    <td>0</td>
                    <td>' . $at_username . '</td>
                    <td>' . (int) $at_score_arr['level'] . '</td>
                    <td>' . number_format($at_score_arr['score']) . '</td>
                </tr>';
    }

    while ($row = mysqli_fetch_assoc($res)) {
        $username = format_username($row['user_id']);
        $ranking = sql_query('SELECT COUNT(id) FROM flashscores WHERE game = ' . sqlesc($gamename) . ' AND score > ' . sqlesc($row['score'])) or sqlerr(__FILE__, __LINE__);
        $rankrow = mysqli_fetch_row($ranking);

        $HTMLOUT .= '
                <tr' . ($row['user_id'] == $player ? ' class="has-text-primary text-shadow"' : '') . '>
                    <td>' . number_format($rankrow[0] + 1) . '</td>
                    <td>' . $username . '</td>
                    <td>' . (int) $row['level'] . '</td>
                    <td>' . number_format($row['score']) . '</td>
                </tr>';
    }
    $member_score_res = sql_query('SELECT * FROM flashscores WHERE game = ' . sqlesc($gamename) . ' AND user_id = ' . sqlesc($CURUSER['id']) . ' ORDER BY score DESC LIMIT 1') or sqlerr(__FILE__, __LINE__);

    if (mysqli_num_rows($member_score_res) > 0) {
        $member_score_arr = mysqli_fetch_assoc($member_score_res);
        $member_ranking_res = sql_query('SELECT COUNT(id) FROM flashscores WHERE game = ' . sqlesc($gamename) . ' AND score > ' . sqlesc($member_score_arr['score'])) or sqlerr(__FILE__, __LINE__);
        $member_ranking_arr = mysqli_fetch_row($member_ranking_res);

        $member_rank = number_format($member_ranking_arr[0]);

        if ($member_rank > 10) {
            $HTMLOUT .= '
                <tr>
                    <td>' . $member_rank . '</td>
                    <td>' . format_username($CURUSER['id']) . '</td>
                    <td>' . (int) $row['level'] . '</td>
                    <td>' . number_format($member_score_arr['score']) . '</td>
                </tr>';
        }
    }

    $HTMLOUT .= '
            </tbody>
        </table>';
} //}
else {
    $id = array_search($gamename, $site_config['arcade_games']);
    $fullgamename = $site_config['arcade_games_names'][$id];
    $HTMLOUT .= "
        <table class='table table-bordered table-striped top20 bottom20'>
            <thead>
                <tr>
                    <th colspan='4'>
                        <div class='size_4 has-text-centered'>
                            $fullgamename
                        </div>
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <div class='has-text-centered'>
                            Sorry, we cannot save scores of this game or there are no scores saved, yet.
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>";
}

echo stdhead('Old School Arcade', true) . wrapper($HTMLOUT, 'has-text-centered') . stdfoot();
