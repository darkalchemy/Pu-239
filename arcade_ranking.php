<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR.'user_functions.php';
check_user_status();

$lang = load_language('global');

$game_id = (isset($_GET['game_id']) ? intval($_GET['game_id']) : 0);
global $site_config;

$HTMLOUT = '';
$HTMLOUT .= '<h1>'.$site_config['site_name'].' Arcade Stats!</h1>
        <span style="align: center"><a class="altlink" href="arcade.php">Arcade</a> || <a class="altlink" href="arcade_top_scores.php">Top Scores</a> || <a class="altlink" href="arcade_ranking.php">More Stats</a></span><br><br>';
        $HTMLOUT .= '<h2>'.$site_config['arcade_games'][$game_id].'</h2>';

echo stdhead('Old School Arcade :: Top scores ').$HTMLOUT.stdfoot();
