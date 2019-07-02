<?php

declare(strict_types = 1);

require_once __DIR__ . '/../include/bittorrent.php';
require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_html.php';
$user = check_user_status();
global $site_config;

$lang = load_language('global');
$HTMLOUT = "
        <h1 class='has-text-centered'>{$site_config['site']['name']} Arcade Top Scores!</h1>
        <div class='bottom10 has-text-centered'>
            <div>Top Scores Earn {$site_config['arcade']['top_score_points']} Karma Points</div>
            <div class='level-center top10'>
                <a class='is-link' href='{$site_config['paths']['baseurl']}/arcade.php'>Back to the Arcade</a>
            </div>
        </div>";

$list = $site_config['arcade']['game_names'];
sort($list);
foreach ($list as $gname) {
    $game_id = array_search($gname, $site_config['arcade']['game_names']);
    $game = $site_config['arcade']['games'][$game_id];
    //=== get high score (5)
    $sql = 'SELECT * FROM flashscores WHERE game = ' . sqlesc($game) . ' ORDER BY score DESC LIMIT 25';
    $score_res = sql_query($sql) or sqlerr(__FILE__, __LINE__);
    if (mysqli_num_rows($score_res) !== 0) {
        $HTMLOUT .= "
        <div class='bg-02 has-text-centered padtop10 round5'>
            <a id='{$game}'></a>
            <a href='{$site_config['paths']['baseurl']}/flash.php?gameURI={$game}.swf&amp;gamename={$game}&amp;game_id={$game_id}'>
                <img src='{$site_config['paths']['images_baseurl']}games/{$game}.png' alt='{$gname}' class='round5'>
            </a>";
        $HTMLOUT .= '
            <table class="table table-bordered table-striped top10 bottom20">
                <thead>
                    <tr>
                        <th>Rank</th>
                        <th>Name</th>
                        <th>Level</th>
                        <th>Score</th>
                    </tr>
                </thead>
                <tbody>';
        $sql = 'SELECT * FROM highscores WHERE game = ' . sqlesc($game) . ' ORDER BY score DESC LIMIT 1';
        $at_score_res = sql_query($sql) or sqlerr(__FILE__, __LINE__);
        while ($at_score_arr = mysqli_fetch_assoc($at_score_res)) {
            $at_username = format_username((int) $at_score_arr['user_id']);
            $HTMLOUT .= '
                    <tr' . ($at_score_arr['user_id'] == $user['id'] ? ' class="has-text-primary text-shadow"' : '') . '>
                        <td>0</td>
                        <td>' . $at_username . '</td>
                        <td>' . (int) $at_score_arr['level'] . '</td>
                        <td>' . number_format((float) $at_score_arr['score']) . '</td>
                    </tr>
                    <tr>
                        <td colspan="4"></td>
                    </tr>';
        }

        while ($score_arr = mysqli_fetch_assoc($score_res)) {
            $username = format_username((int) $score_arr['user_id']);
            $sql = 'SELECT COUNT(id) FROM flashscores WHERE game = ' . sqlesc($game) . ' AND score>' . sqlesc($score_arr['score']);
            $ranking = sql_query($sql) or sqlerr(__FILE__, __LINE__);
            $rankrow = mysqli_fetch_row($ranking);

            $HTMLOUT .= '
                    <tr' . ($score_arr['user_id'] == $user['id'] ? ' class="has-text-primary text-shadow"' : '') . '>
                        <td>' . number_format($rankrow[0] + 1) . '</td>
                        <td>' . $username . '</td>
                        <td>' . (int) $score_arr['level'] . '</td>
                        <td>' . number_format((float) $score_arr['score']) . '</td>
                    </tr>';
        }
        //=== get members high score if any
        $sql = 'SELECT score FROM flashscores WHERE game = ' . sqlesc($game) . ' AND user_id=' . sqlesc($user['id']) . ' ORDER BY score DESC LIMIT 1';
        $member_score_res = sql_query($sql) or sqlerr(__FILE__, __LINE__);

        if (mysqli_num_rows($member_score_res) != 0) {
            $score_arr = mysqli_fetch_row($member_score_res);
            $member_rank_res = sql_query('SELECT COUNT(id) FROM flashscores WHERE game = ' . sqlesc($game) . ' AND score>' . sqlesc($score_arr[0])) or sqlerr(__FILE__, __LINE__);
            $member_rank_arr = mysqli_fetch_row($member_rank_res);

            $HTMLOUT .= '
                    <tr>
                        <td colspan="4">
                            <div class="top10 bottom10 has-text-centered">Your high score was ' . number_format((float) $score_arr[0]) . ' and you ranked ' . number_format((float) $member_rank_arr[0] + 1) . '.</div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>';
        }
    }
}

//=== total games played:
$sql = 'SELECT COUNT(id) AS count, SUM(score) AS score FROM flashscores WHERE user_id=' . $user['id'];
$result = sql_query($sql) or sqlerr(__FILE__, __LINE__);
$member_totals = mysqli_fetch_assoc($result);

$sql = 'SELECT COUNT(id) AS count, user_id
            FROM flashscores
            GROUP BY user_id
            ORDER BY COUNT(id) DESC
            LIMIT 1';
$result = sql_query($sql) or sqlerr(__FILE__, __LINE__);
$member_played_most_games = mysqli_fetch_assoc($result);

$sql = 'SELECT SUM(score) AS score, COUNT(id) AS count, user_id
            FROM flashscores
            GROUP BY user_id
            ORDER BY score DESC
            LIMIT 1';
$result = sql_query($sql) or sqlerr(__FILE__, __LINE__);
$member_high_score = mysqli_fetch_assoc($result);
if (!empty($member_played_most_games) && !empty($member_high_score)) {
    $HTMLOUT .= '
        <div class="bg-02 has-text-centered padtop10 round5">
            <h2>Stats</h2>
            <table class="table table-bordered table-striped top20 bottom20">
                <thead>
                    <tr>
                        <th colspan="2">
                            <div class="size_4 has-text-centered">So far, you have played a total of ' . number_format((float) $member_totals['count']) . ' games, scoring ' . number_format((float) $member_totals['score']) . ' points in total!</div>
                        </th>
                    </tr>
                    <tr>
                        <th class="w-50">
                            <div class="size_3 has-text-centered">Most Bored Award</div>
                        </th>
                        <th>
                            <div class="size_3 has-text-centered">Highest Score Award</div>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <div class="has-text-centered">
                                Congratulations!<br>The Most Bored award goes to: ' . format_username((int) $member_played_most_games['user_id']) . ' with, ' . number_format((float) $member_played_most_games['count']) . ' games played!
                            </div>
                        </td>
                        <td>
                            <div class="has-text-centered">
                                Congratulations!<br>The Highest Score Award goes to: ' . format_username((int) $member_high_score['user_id']) . ', with a total score of ' . number_format((float) $member_high_score['score']) . ' playing ' . number_format((float) $member_high_score['count']) . ' games!
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>';
}

echo stdhead('Top Scores') . wrapper($HTMLOUT) . stdfoot();
