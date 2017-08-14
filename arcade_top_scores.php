<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR.'user_functions.php';
check_user_status();

$lang = load_language('global');
global $INSTALLER09, $CURUSER;

$stdfoot = array(
    'js' => array(
    ),
);

$HTMLOUT = "<div class='container-fluid text-center' align='center'>
        <h1>{$INSTALLER09['site_name']} Arcade Top Scores!</h1>
        <span>Top Scores Earn {$INSTALLER09['top_score_points']} Karma Points</span>
        <br><br>
        <span><a class='altlink' href='arcade.php'>Arcade</a></span>
        <br><br>";

$list = $INSTALLER09['arcade_games_names'];
sort($list);
foreach ($list as $gname) {
    //echo "$gname ";
    $game_id = array_search($gname, $INSTALLER09['arcade_games_names']);
    $game = $INSTALLER09['arcade_games'][$game_id];
    //=== get high score (5)
    $sql = 'SELECT * FROM flashscores WHERE game = '.sqlesc($game).' ORDER BY score DESC LIMIT 25';
    $score_res = sql_query($sql) or sqlerr(__FILE__, __LINE__);
    if (mysqli_num_rows($score_res) !== 0) {
        $HTMLOUT .= "<a name='".$game."'><br><br></a><a href='flash.php?gameURI=".$game.'.swf&amp;gamename='.$game.'&amp;game_id='.$game_id."'><img style='width:33%;height:auto;max-height:33%;' src='".$INSTALLER09['pic_base_url'].'games/'.$game.".png' alt='".$gname."' /></span></a>";
//                  <h2><a href='flash.php?gameURI=" . $game . ".swf&amp;gamename=" . $game . "&amp;game_id=" . $game_id . "'>$gname</a></h2>";
//      $HTMLOUT .= '<h2>' . $gname . '</h2>';
        $HTMLOUT .= '<table border="0" cellspacing="5" cellpadding="5" align="center" width="500px">
            <tr>
                <td class="colhead" align="center">Rank</td>
                <td class="colhead" width="75%">Name</td>
                <td class="colhead" align="center">Level</td>
                <td class="colhead" align="center">Score</td>
            </tr>';

    //=== do the top 10 for each game
    $count2 = '';
        $sql = 'SELECT * FROM highscores WHERE game = '.sqlesc($game).' ORDER BY score DESC LIMIT 1';
        $at_score_res = sql_query($sql) or sqlerr(__FILE__, __LINE__);
        while ($at_score_arr = mysqli_fetch_assoc($at_score_res)) {
            $at_username = format_username($at_score_arr['user_id']);
            $count2 = (++$count2) % 2;
            $class = ($count2 == 0 ? 'one' : 'two');
            $HTMLOUT .= '
        <tr'.($at_score_arr['user_id'] == $CURUSER['id'] ? ' style="background-color:green"' : '').'>
            <td class="'.$class.'" align="center">0</td>
            <td class="'.$class.'" align="left">'.$at_username.'</td>
            <td align="center" class="'.$class.'">'.(int) $at_score_arr['level'].'</td>
            <td class="'.$class.'" align="center">'.number_format($at_score_arr['score']).'</td>
        </tr><tr style="border: none;"><td style="border: none;" colspan="4"></td></tr>';
        }

        while ($score_arr = mysqli_fetch_assoc($score_res)) {
            $username = format_username($score_arr['user_id']);
            $sql = 'SELECT COUNT(id) FROM flashscores WHERE game = '.sqlesc($game).' AND score > '.sqlesc($score_arr['score']);
            $ranking = sql_query($sql) or sqlerr(__FILE__, __LINE__);
            $rankrow = mysqli_fetch_row($ranking);

    //=======change colors
    $count2 = (++$count2) % 2;
            $class = ($count2 == 0 ? 'one' : 'two');

            $HTMLOUT .= '
    <tr'.($score_arr['user_id'] == $CURUSER['id'] ? ' style="background-color:green"' : '').'>
        <td class="'.$class.'" align="center">'.number_format($rankrow[0] + 1).'</td>
        <td class="'.$class.'" align="left">'.$username.'</td>
        <td align="center" class="'.$class.'">'.(int) $score_arr['level'].'</td>
        <td class="'.$class.'" align="center">'.number_format($score_arr['score']).'</td>
    </tr>';
        }
    //=== get members high score if any
    $sql = 'SELECT score FROM flashscores WHERE game = '.sqlesc($game).' AND user_id = '.sqlesc($CURUSER['id']).' ORDER BY score DESC LIMIT 1';
    $member_score_res = sql_query($sql) or sqlerr(__FILE__, __LINE__);

        if (mysqli_num_rows($member_score_res) != 0) {
            $score_arr = mysqli_fetch_row($member_score_res);
            $member_rank_res = sql_query('SELECT COUNT(id) FROM flashscores WHERE game = '.sqlesc($game).' AND score > '.sqlesc($score_arr[0])) or sqlerr(__FILE__, __LINE__);
            $member_rank_arr = mysqli_fetch_row($member_rank_res);

            $HTMLOUT .= '
            <tr>
                <td class="three" colspan="4"><br>
                Your high score was '.number_format($score_arr[0]).' and you ranked '.number_format($member_rank_arr[0] + 1).'.<br></td>
            </tr>';
        }
        $HTMLOUT .= '</table>';
    }
}

//=== total games played:
$sql = 'SELECT COUNT(id) AS count, SUM(score) AS score FROM flashscores WHERE user_id = ' . $CURUSER['id'];
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
    $HTMLOUT .= '<br><br>
        <table border="0" cellspacing="10" cellpadding="5" align="center" width="500px">
            <tr>
                <td class="colhead" colspan="2"><h2>Stats</h2></td>
            </tr>
            <tr>
                <td class="two" colspan="2">So far, you have played a total of '.number_format($member_totals['count']).' games,<br>
                scoring '.number_format($member_totals['score']).' points in total!<br></td>
            </tr>
            <tr>
                <td class="colhead" align="center">Most Bored Award</td>
                <td class="colhead" align="center">Highest Score Award</td>
            </tr>
            <tr>
                <td class="two" align="center">
                The most Bored award goes to: ' . format_username($member_played_most_games['user_id']) . '
                With ' . number_format($member_played_most_games['count']) . ' games played!<br><span style="font-weight: bold;">Congratulations!</span></td>
                <td class="two" align="center">
                The highest score award goes to: ' . format_username($member_high_score['user_id']) . '
                With a total score of ' . number_format($member_high_score['score']) . ' playing ' . number_format($member_high_score['count']).' games!<br><span style="font-weight: bold;">Congratulations!</span></td>
            </tr>
        </table></div>';
}

echo stdhead('Top Scores').$HTMLOUT.stdfoot($stdfoot);
