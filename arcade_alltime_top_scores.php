<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR.'user_functions.php';
check_user_status();

$lang = load_language('global');
global $INSTALLER09;

$HTMLOUT = '';
$HTMLOUT .= '<h1>'.$INSTALLER09['site_name'].' Arcade Top Scores!</h1>
        <span>Top Scores Earn 2000 Karma Points</span>
        <br><br>
        <a class="altlink" href="arcade.php">Arcade</a>
        <br><br>';

$list = $INSTALLER09['arcade_games_names'];
sort($list);
foreach ($list as $gname) {
    $game_id = array_search($gname, $INSTALLER09['arcade_games_names']);
    $game = $INSTALLER09['arcade_games'][$game_id];
    //=== get high score (5)
    $score_res = sql_query('SELECT * FROM highscores WHERE game = '.sqlesc($game).' ORDER BY score DESC LIMIT 10') or sqlerr(__FILE__, __LINE__);
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
        while ($score_arr = mysqli_fetch_assoc($score_res)) {
            $username = format_username_test($score_arr['user'], true, false, 'tooltipper');
            $ranking = sql_query('SELECT COUNT(id) FROM highscores WHERE game = '.sqlesc($gname).' AND score > '.sqlesc($score_arr['score'])) or sqlerr(__FILE__, __LINE__);
            $rankrow = mysqli_fetch_row($ranking);

        //=======change colors
        $count2 = (++$count2) % 2;
            $class = ($count2 == 0 ? 'one' : 'two');

            $HTMLOUT .= '
    <tr'.($score_arr['user'] == $CURUSER['id'] ? ' style="background-color:green"' : '').'>
        <td class="'.$class.'" align="center">'.number_format($rankrow[0] + 1).'</td>
        <td class="'.$class.'" align="left">'.$username.'</td>
        <td align="center" class="'.$class.'">'.(int) $score_arr['level'].'</td>
        <td class="'.$class.'" align="center">'.number_format($score_arr['score']).'</td>
    </tr>';
        }
    //=== get members high score if any
    $member_score_res = sql_query('SELECT score FROM highscores WHERE game = '.sqlesc($gname).' AND user = '.sqlesc($CURUSER['id']).' ORDER BY score DESC LIMIT 1') or sqlerr(__FILE__, __LINE__);

        if (mysqli_num_rows($member_score_res) != 0) {
            $score_arr = mysqli_fetch_row($member_score_res);
            $member_rank_res = sql_query('SELECT COUNT(id) FROM highscores WHERE game = '.sqlesc($gname).' AND score > '.sqlesc($score_arr[0])) or sqlerr(__FILE__, __LINE__);
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
echo stdhead('Old School Arcade :: Top scores ').$HTMLOUT.stdfoot();
