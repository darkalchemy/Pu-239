<?php

declare(strict_types = 1);

require_once __DIR__ . '/../include/bittorrent.php';
require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_html.php';
$user = check_user_status();
global $site_config;

$scores = '';
$player = $user['id'];

$all_our_games = $site_config['arcade']['games'];

if (isset($_GET['gamename'])) {
    $gamename = strip_tags($_GET['gamename']);
    if (!in_array($gamename, $all_our_games)) {
        stderr(_('Error'), _f('No game with that name! (%s)', $gamename));
    }
}

$game_name = str_replace('_', ' ', $gamename);
if (isset($_GET['gameURI'])) {
    $gameURI = strip_tags($_GET['gameURI']);
    $gameURIclean = str_replace('.swf', '', $gameURI);
    if (!in_array($gameURIclean, $all_our_games)) {
        stderr(_('Error'), _('Could not find game!'));
    }
}
if (!isset($user['gameheight']) || $user['gameheight'] === 0) {
    $game_height = 800;
} else {
    $game_height = $user['gameheight'];
}
$game_width = $game_height;

$HTMLOUT = '';
$HTMLOUT .= "
        <div class='bottom20'>
            <ul class='level-center bg-06'>
                <li class='is-link margin10'>
                    <a href='{$site_config['paths']['baseurl']}/arcade.php'>" . _('Arcade') . "</a>
                </li>
                <li class='is-link margin10'>
                    <a href='{$site_config['paths']['baseurl']}/arcade_top_scores.php'>" . _('Top Scores') . "</a>
                </li>
            </ul>
        </div>
        <h1 class='has-text-centered'>{$site_config['site']['name']} " . _('Old School Arcade!') . "</h1>
        <div class='has-text-centered'>" . _f('Top Scores Earn %d Karma Points', $site_config['arcade']['top_score_points']) . '</div>';

$HTMLOUT .= "
        <div class='bordered top20'>
            <div class='alt_bordered bg-00 has-text-centered'>
                <object style='width: {$game_width}px; height: {$game_height}px;' width='{$game_width}' height='{$game_height}'>
                    <param name='movie' value='./media/flash_games/{$gameURI}'>
                    <param name='quality' value='high'>
                    <embed src='{$site_config['paths']['baseurl']}/media/flash_games/{$gameURI}' quality='high' type='application/x-shockwave-flash' style='width: {$game_width}px;' height: {$game_height}px; width='{$game_width}' height='{$game_height}'>
                </object>
            </div>
        </div>";

$res = sql_query('SELECT * FROM flashscores WHERE game = ' . sqlesc($gamename) . ' ORDER BY score DESC LIMIT 15') or sqlerr(__FILE__, __LINE__);

if (mysqli_num_rows($res) > 0) {
    $id = array_search($gamename, $site_config['arcade']['games']);
    $fullgamename = $site_config['arcade']['game_names'][$id];
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
                    <th>" . _('Rank') . '</th>
                    <th>' . _('Name') . '</th>
                    <th>' . _('Level') . '</th>
                    <th>' . _('Score') . '</th>
                </tr>
            </thead>
            <tbody>';
    $at_score_res = sql_query('SELECT * FROM highscores WHERE game = ' . sqlesc($gamename) . ' ORDER BY score DESC LIMIT 15') or sqlerr(__FILE__, __LINE__);
    while ($at_score_arr = mysqli_fetch_assoc($at_score_res)) {
        $at_username = format_username((int) $at_score_arr['user_id']);
        $at_ranking = sql_query('SELECT COUNT(id) FROM highscores WHERE game = ' . sqlesc($gamename) . ' AND score > ' . sqlesc($at_score_arr['score'])) or sqlerr(__FILE__, __LINE__);
        $at_rankrow = mysqli_fetch_row($at_ranking);
        $HTMLOUT .= '
                <tr' . ($at_score_arr['user_id'] == $user['id'] ? ' class="has-text-primary text-shadow"' : '') . '>
                    <td>0</td>
                    <td>' . $at_username . '</td>
                    <td>' . (int) $at_score_arr['level'] . '</td>
                    <td>' . number_format((float) $at_score_arr['score']) . '</td>
                </tr>';
    }

    while ($row = mysqli_fetch_assoc($res)) {
        $username = format_username((int) $row['user_id']);
        $ranking = sql_query('SELECT COUNT(id) FROM flashscores WHERE game = ' . sqlesc($gamename) . ' AND score>' . sqlesc($row['score'])) or sqlerr(__FILE__, __LINE__);
        $rankrow = mysqli_fetch_row($ranking);

        $HTMLOUT .= '
                <tr' . ($row['user_id'] == $player ? ' class="has-text-primary text-shadow"' : '') . '>
                    <td>' . number_format($rankrow[0] + 1) . '</td>
                    <td>' . $username . '</td>
                    <td>' . (int) $row['level'] . '</td>
                    <td>' . number_format((float) $row['score']) . '</td>
                </tr>';
    }
    $member_score_res = sql_query('SELECT * FROM flashscores WHERE game = ' . sqlesc($gamename) . ' AND user_id=' . sqlesc($user['id']) . ' ORDER BY score DESC LIMIT 1') or sqlerr(__FILE__, __LINE__);

    if (mysqli_num_rows($member_score_res) > 0) {
        $member_score_arr = mysqli_fetch_assoc($member_score_res);
        $member_ranking_res = sql_query('SELECT COUNT(id) FROM flashscores WHERE game = ' . sqlesc($gamename) . ' AND score>' . sqlesc($member_score_arr['score'])) or sqlerr(__FILE__, __LINE__);
        $member_ranking_arr = mysqli_fetch_row($member_ranking_res);

        $member_rank = number_format((float) $member_ranking_arr[0]);

        if ($member_rank > 10) {
            $HTMLOUT .= '
                <tr>
                    <td>' . $member_rank . '</td>
                    <td>' . format_username($user['id']) . '</td>
                    <td>' . (int) $row['level'] . '</td>
                    <td>' . number_format((int) $member_score_arr['score']) . '</td>
                </tr>';
        }
    }

    $HTMLOUT .= '
            </tbody>
        </table>';
} //}
else {
    $id = array_search($gamename, $site_config['arcade']['games']);
    $fullgamename = $site_config['arcade']['game_names'][$id];
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
                            " . _('Sorry, we cannot save scores of this game or there are no scores saved, yet.') . '
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>';
}
$title = _('Old School Arcade');
$breadcrumbs = [
    "<a href='{$site_config['paths']['baseurl']}/games.php'>" . _('Games') . '</a>',
    "<a href='{$_SERVER['PHP_SELF']}'>$title</a>",
];
echo stdhead($title, [], 'page-wrapper', $breadcrumbs) . wrapper($HTMLOUT) . stdfoot();
