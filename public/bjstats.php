<?php

declare(strict_types = 1);

use Pu239\Database;

require_once __DIR__ . '/../include/bittorrent.php';
require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_html.php';
$user = check_user_status();
global $container, $site_config;

if (!has_access($user['class'], $site_config['allowed']['play'], '')) {
    stderr(_('Error'), _fe('Sorry, you must be a {0} to play blackjack!', $site_config['class_names'][$site_config['allowed']['play']]), 'bottom20');
} elseif ($user['game_access'] !== 1 || $user['status'] !== 0) {
    stderr(_('Error'), _('Your gaming rights have been disabled.'), 'bottom20');
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
    $htmlout = "<h1 class='has-text-centered'>$frame_caption</h1>";
    $heading = '
        <tr>
            <th>' . _('Rank') . '</th>
            <th>' . _('User') . "</th>
            <th class='colhead has-text-right'>" . _('Wins') . "</th>
            <th class='colhead has-text-right'>" . _('Losses') . "</th>
            <th class='colhead has-text-right'>" . _('Games') . "</th>
            <th class='colhead has-text-right'>" . _('Percentage') . "</th>
            <th class='colhead has-text-right'>" . _('Win/Loss') . '</th>
        </tr>';
    $num = 0;
    $body = '';
    foreach ($res as $a) {
        $win_perc = number_format(($a['wins'] / $a['games']) * 100, 1);
        $plus_minus = $a['wins'] - $a['losses'];
        if ($plus_minus >= 0) {
            $plus_minus = mksize(($a['wins'] - $a['losses']) * 100 * 1024 * 1024);
        } else {
            $plus_minus = '-';
            $plus_minus .= mksize(($a['losses'] - $a['wins']) * 100 * 1024 * 1024);
        }
        $body .= '
            <tr>
                <td>' . ++$num . '</td>
                <td>' . format_username((int) $a['id']) . "</td>
                <td class='has-text-right'>" . number_format($a['wins'], 0) . "</td>
                <td class='has-text-right'>" . number_format($a['losses'], 0) . "</td>
                <td class='has-text-right'>" . number_format($a['games'], 0) . "</td>
                <td class='has-text-right'>$win_perc</td>
                <td class='has-text-right'>$plus_minus</td>
            </tr>";
    }
    if (empty($body)) {
        $body .= "
            <tr>
                <td colspan='7' class='has-text-centered'>" . _('No Game Stats') . '</td>
            </tr>';
    }
    $htmlout .= main_table($body, $heading);

    return $htmlout;
}

$mingames = 10;
$fluent = $container->get(Database::class);
$res = $fluent->from('users')
              ->select('id')
              ->select('username')
              ->select('bjwins AS wins')
              ->select('bjlosses AS losses')
              ->select('bjwins + bjlosses AS games')
              ->where('bjwins + bjlosses > ?', $mingames)
              ->orderBy('games')
              ->limit(10)
              ->fetchAll();
$HTMLOUT = bjtable($res, _('Most Games Played'));

$res = $fluent->from('users')
              ->select('id')
              ->select('username')
              ->select('bjwins AS wins')
              ->select('bjlosses AS losses')
              ->select('bjwins + bjlosses AS games')
              ->select('bjwins / (bjwins + bjlosses) AS winperc')
              ->where('bjwins + bjlosses > ?', $mingames)
              ->orderBy('winperc')
              ->limit(10)
              ->fetchAll();
$HTMLOUT .= bjtable($res, _('Highest Win Percentage'));

$res = $fluent->from('users')
              ->select('id')
              ->select('username')
              ->select('bjwins AS wins')
              ->select('bjlosses AS losses')
              ->select('bjwins - bjlosses AS winnings')
              ->where('bjwins + bjlosses > ?', $mingames)
              ->orderBy('winnings')
              ->limit(10)
              ->fetchAll();
$HTMLOUT .= bjtable($res, _('Most Credit Won'));

$res = $fluent->from('users')
              ->select('id')
              ->select('username')
              ->select('bjwins AS wins')
              ->select('bjlosses AS losses')
              ->select('bjlosses - bjwins AS losings')
              ->select('bjwins / (bjwins + bjlosses) AS winperc')
              ->where('bjwins + bjlosses > ?', $mingames)
              ->orderBy('losings')
              ->limit(10)
              ->fetchAll();
$HTMLOUT .= bjtable($res, _('Most Credit Lost'));
$title = _('Blackjack Stats');
$breadcrumbs = [
    "<a href='{$_SERVER['PHP_SELF']}'>$title</a>",
];
echo stdhead($title, [], 'page-wrapper', $breadcrumbs) . wrapper($HTMLOUT) . stdfoot();
