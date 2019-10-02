<?php

declare(strict_types = 1);

require_once __DIR__ . '/../include/bittorrent.php';
require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_html.php';
$user = check_user_status();
global $site_config;

if ($user['class'] < $site_config['allowed']['play']) {
    stderr(_('Sorry...'), 'Sorry, you must be a ' . $site_config['class_names'][$site_config['allowed']['play']] . ' to play blackjack!');
    exit;
}
/**
 * @param $res
 * @param $frame_caption
 *
 * @throws \Envms\FluentPDO\Exception
 *
 * @return string
 */
function bjtable($res, $frame_caption)
{
    $htmlout = '';
    $htmlout .= begin_frame($frame_caption, true);
    $htmlout .= begin_table();
    $htmlout .= "<tr>
    <td class='colhead'>" . _('Rank') . "</td>
    <td class='colhead'>" . _('User') . "</td>
    <td class='colhead has-text-right'>" . _('Wins') . "</td>
    <td class='colhead has-text-right'>" . _('Losses') . "</td>
    <td class='colhead has-text-right'>" . _('Games') . "</td>
    <td class='colhead has-text-right'>" . _('Percentage') . "</td>
    <td class='colhead has-text-right'>" . _('Win/Loss') . '</td>
    </tr>';
    $num = 0;
    while ($a = mysqli_fetch_assoc($res)) {
        ++$num;
        //==Calculate Win %
        $win_perc = number_format(($a['wins'] / $a['games']) * 100, 1);
        //==Add a user's +/- statistic
        $plus_minus = $a['wins'] - $a['losses'];
        if ($plus_minus >= 0) {
            $plus_minus = mksize(($a['wins'] - $a['losses']) * 100 * 1024 * 1024);
        } else {
            $plus_minus = '-';
            $plus_minus .= mksize(($a['losses'] - $a['wins']) * 100 * 1024 * 1024);
        }
        $htmlout .= "<tr><td>$num</td><td>" . format_username((int) $a['id']) . '</td>' . "<td class='has-text-right'>" . number_format($a['wins'], 0) . '</td>' . "<td class='has-text-right'>" . number_format($a['losses'], 0) . '</td>' . "<td class='has-text-right'>" . number_format($a['games'], 0) . '</td>' . "<td class='has-text-right'>$win_perc</td>" . "<td class='has-text-right'>$plus_minus</td>" . "</tr>\n";
    }
    $htmlout .= end_table();
    $htmlout .= end_frame();

    return $htmlout;
}

$HTMLOUT = '';
$mingames = 10;
$HTMLOUT .= '<br>';
$res = sql_query('SELECT id, username, bjwins AS wins, bjlosses AS losses, bjwins + bjlosses AS games FROM users WHERE bjwins + bjlosses>' . sqlesc($mingames) . ' ORDER BY games DESC LIMIT 10') or sqlerr(__FILE__, __LINE__);
$HTMLOUT .= bjtable($res, '' . _('Most') . ' ' . _('Games Played') . '');
$HTMLOUT .= '<br><br>';
//==Highest Win %
$res = sql_query('SELECT id, username, bjwins AS wins, bjlosses AS losses, bjwins + bjlosses AS games, bjwins / (bjwins + bjlosses) AS winperc FROM users WHERE bjwins + bjlosses>' . sqlesc($mingames) . ' ORDER BY winperc DESC LIMIT 10') or sqlerr(__FILE__, __LINE__);
$HTMLOUT .= bjtable($res, _('Highest Win Percentage'));
$HTMLOUT .= '<br><br>';
//==Highest Win %
$res = sql_query('SELECT id, username, bjwins AS wins, bjlosses AS losses, bjwins + bjlosses AS games, bjwins - bjlosses AS winnings FROM users WHERE bjwins + bjlosses>' . sqlesc($mingames) . ' ORDER BY winnings DESC LIMIT 10') or sqlerr(__FILE__, __LINE__);
$HTMLOUT .= bjtable($res, _('Most Credit Won'));
$HTMLOUT .= '<br><br>';
$res = sql_query('SELECT id, username, bjwins AS wins, bjlosses AS losses, bjwins + bjlosses AS games, bjlosses - bjwins AS losings FROM users WHERE bjwins + bjlosses>' . sqlesc($mingames) . ' ORDER BY losings DESC LIMIT 10') or sqlerr(__FILE__, __LINE__);
$HTMLOUT .= bjtable($res, _('Most Credit Lost'));
$HTMLOUT .= '<br><br>';
$title = _('Blackjack Stats');
$breadcrumbs = [
    "<a href='{$_SERVER['PHP_SELF']}'>$title</a>",
];
echo stdhead($title, [], 'page-wrapper', $breadcrumbs) . wrapper($HTMLOUT) . stdfoot();
