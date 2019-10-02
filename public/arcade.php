<?php

declare(strict_types = 1);

require_once __DIR__ . '/../include/bittorrent.php';
require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_html.php';
$user = check_user_status();
global $site_config;

if ($user['class'] < $site_config['allowed']['play']) {
    stderr('Error', _f('Sorry, you must be a %s to play in the arcade!', $site_config['class_names'][$site_config['allowed']['play']]), 'bottom20');
} elseif ($user['game_access'] !== 1 || $user['status'] !== 0) {
    stderr(_('Error'), _('Your gaming rights have been disabled.'), 'bottom20', 'bottom20');
    die();
}

$HTMLOUT = "
            <div class='has-text-centered'>
                <h1>{$site_config['site']['name']} " . _('Old School Arcade!') . '</h1>
                <span>' . _f('Top Scores Earn %s Karma Points', $site_config['arcade']['top_score_points']) . "</span>
                <div class='level-center top10'>
                    <a class='is-link' href='{$site_config['paths']['baseurl']}/arcade_top_scores.php'>" . _('Top Scores') . '</a>
                </div>
            </div>';

$body = "
            <div class='level-center'>";

$list = $site_config['arcade']['game_names'];
sort($list);
$i = 0;
foreach ($list as $gamename) {
    $id = $i++;
    $game_id = array_search($gamename, $site_config['arcade']['game_names']);
    $game = $site_config['arcade']['games'][$game_id];
    $fullgamename = $site_config['arcade']['game_names'][$game_id];
    $body .= "
                <div class='margin10 w-20'>
                    <a href='{$site_config['paths']['baseurl']}/flash.php?gameURI={$game}.swf&amp;gamename={$game}&amp;game_id={$id}' class='tooltipper' title='" . urlencode($fullgamename) . "'>
                        <img src='{$site_config['paths']['images_baseurl']}games/{$game}.png' alt='{$game}' class='round10'>
                    </a>
                </div>";
}
$body .= '
            </div>';
$HTMLOUT .= main_div($body, 'top20');

$title = _('Arcade');
$breadcrumbs = [
    "<a href='{$site_config['paths']['baseurl']}/games.php'>" . _('Games') . '</a>',
    "<a href='{$_SERVER['PHP_SELF']}'>$title</a>",
];
echo stdhead($title, [], 'page-wrapper', $breadcrumbs) . wrapper($HTMLOUT) . stdfoot();
