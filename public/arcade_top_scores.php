<?php

declare(strict_types = 1);

use Pu239\Database;

require_once __DIR__ . '/../include/bittorrent.php';
require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_html.php';
$user = check_user_status();
global $container, $site_config;

$HTMLOUT = "
        <h1 class='has-text-centered'>{$site_config['site']['name']} " . _('Arcade Top Scores!') . "</h1>
        <div class='bottom10 has-text-centered'>
            <div>" . _('Top Scores Earn %s Karma Points', $site_config['arcade']['top_score_points']) . "</div>
            <div class='level-center top10'>
                <a class='is-link' href='{$site_config['paths']['baseurl']}/arcade.php'>" . _('Back to the Arcade') . '</a>
            </div>
        </div>';

$fluent = $container->get(Database::class);
$scores = $fluent->from('flashscores')
                 ->orderBy('game')
                 ->orderBy('level DESC')
                 ->orderBy('score DESC')
                 ->fetchAll();
$highscores = $fluent->from('highscores')
                     ->orderBy('game')
                     ->fetchAll();

/**
 * @param string $game
 * @param array  $scores
 *
 * @return array
 */
function get_scores(string $game, array $scores)
{
    $game_scores = [];
    foreach ($scores as $score) {
        if ($score['game'] === $game) {
            $game_scores[] = $score;
        }
    }

    return $game_scores;
}

/**
 * @param string $game
 * @param array  $highscore
 *
 * @return int|mixed
 */
function get_highscore(string $game, array $highscore)
{
    foreach ($highscore as $score) {
        if ($score['game'] === $game) {
            return $score;
        }
    }

    return 0;
}

$list = $site_config['arcade']['game_names'];
sort($list);
$heading = '
                    <tr>
                        <th>' . _('Rank') . '</th>
                        <th>' . _('Name') . '</th>
                        <th>' . _('Level') . '</th>
                        <th>' . _('Score') . '</th>
                    </tr>';
foreach ($list as $gname) {
    $game_id = array_search($gname, $site_config['arcade']['game_names']);
    $game = $site_config['arcade']['games'][$game_id];
    $game_scores = get_scores($game, $scores);
    $body = '';
    if (!empty($game_scores)) {
        $highscore = get_highscore($game, $highscores);
        $body .= '
                    <tr>
                        <td>0</td>
                        <td>' . format_username($highscore['user_id']) . '</td>
                        <td>' . $highscore['level'] . '</td>
                        <td>' . number_format((float) $highscore['score']) . '</td>
                    </tr>
                    <tr>
                        <td colspan="4"></td>
                    </tr>';
        $i = $user_high = $user_rank = 0;
        foreach ($game_scores as $gscores) {
            $body .= '
                    <tr>
                        <td>' . ++$i . '</td>
                        <td>' . format_username($gscores['user_id']) . '</td>
                        <td>' . $gscores['level'] . '</td>
                        <td>' . number_format((float) $gscores['score']) . '</td>
                    </tr>';
            if ($gscores['user_id'] === $user['id'] && $user_high === 0) {
                $user_high = $gscores['score'];
                $user_rank = $i;
            }
            if ($i >= 10) {
                break;
            }
        }
        if ($user_high != 0) {
            $body .= '
                    <tr>
                        <td colspan="4">
                            <div class="top10 bottom10 has-text-centered">' . _fe('Your high score was {0} and you ranked #{1}.', number_format((float) $user_high), number_format((float) $user_rank)) . '</div>
                        </td>
                    </tr>';
        }
        $table = main_table($body, $heading, 'top20');
        $HTMLOUT .= "
        <div class='bg-00 round10 has-text-centered top20'>
            <a id='{$game}'></a>
            <a href='{$site_config['paths']['baseurl']}/flash.php?gameURI={$game}.swf&amp;gamename={$game}&amp;game_id={$game_id}'>
                <img src='{$site_config['paths']['images_baseurl']}games/{$game}.png' alt='{$gname}' class='round10 top20 w-50 min-250'>
            </a>{$table}
        </div>";
    }
}

$title = _('Top Scores');
$breadcrumbs = [
    "<a href='{$site_config['paths']['baseurl']}/games.php'>" . _('Games') . '</a>',
    "<a href='{$_SERVER['PHP_SELF']}'>$title</a>",
];
echo stdhead($title, [], 'page-wrapper', $breadcrumbs) . wrapper($HTMLOUT) . stdfoot();
