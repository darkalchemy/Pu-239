<?php
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'user_functions.php';
require_once INCL_DIR . 'html_functions.php';
check_user_status();
global $CURUSER, $site_config;

$lang = array_merge(load_language('global'), load_language('blackjack'));
$HTMLOUT = '';
if ($CURUSER['game_access'] == 0 || $CURUSER['game_access'] > 1 || $CURUSER['suspended'] == 'yes') {
    stderr($lang['bj_error'], $lang['bj_gaming_rights_disabled']);
}

$width = 100 / 3;
$color1 = $color2 = $color3 = $color4 = $color5 = $color6 = $color7 = $color8 = $color9 = 'text-red';

$sql = "SELECT game_id FROM blackjack WHERE status = 'waiting' ORDER BY game_id";
$res = sql_query($sql) or sqlerr(__FILE__, __LINE__);
while ($count = mysqli_fetch_array($res)) {
    extract($count);
    ${'color' . $game_id} = 'has-text-success';
}

// Casino
$casino_count = get_row_count('casino', 'WHERE deposit > 0 AND userid != ' . sqlesc($CURUSER['id']));
if ($casino_count > 0) {
    $color9 = 'green';
}

$HTMLOUT = '';
$HTMLOUT .= "
            <div class='has-text-centered'>
                <h1>{$site_config['site_name']} Games!</h1>
                <h3>Welcome To The Casino, Please Select A Game Below To Play.</h3>
            </div>
            <div class='columns'>
                <div class='column'>
                    <a href='{$site_config['baseurl']}/blackjack.php?id=1'><div class='has-text-centered $color1'>BlackJack 1GB</div>
                        <img src='{$site_config['pic_base_url']}blackjack.jpg' alt='blackjack' class='tooltipper round10' title='BlackJack 1GB' />
                    </a>
                </div>
                <div class='column'>
                    <a href='{$site_config['baseurl']}/blackjack.php?id=10'><div class='has-text-centered $color2'>BlackJack 10GB</div>
                        <img src='{$site_config['pic_base_url']}blackjack.jpg' alt='blackjack' class='tooltipper round10' title='BlackJack 10GB' />
                    </a>
                </div>
                <div class='column'>
                    <a href='{$site_config['baseurl']}/blackjack.php?id=20'><div class='has-text-centered $color3'>BlackJack 20GB</div>
                        <img src='{$site_config['pic_base_url']}blackjack.jpg' alt='blackjack' class='tooltipper round10' title='BlackJack 20GB' />
                    </a>
                </div>
            </div>
            <div class='columns'>
                <div class='column'>
                    <a href='{$site_config['baseurl']}/blackjack.php?id=50'><div class='has-text-centered $color4'>BlackJack 50GB</div>
                        <img src='{$site_config['pic_base_url']}blackjack.jpg' alt='blackjack' class='tooltipper round10' title='BlackJack 50GB' />
                    </a>
                </div>
                <div class='column'>
                    <a href='{$site_config['baseurl']}/casino.php'><div class='has-text-centered $color9'>Casino</div>
                        <img src='{$site_config['pic_base_url']}casino.jpg' alt='casino' class='tooltipper round10' title='Casino' />
                    </a>
                </div>
                <div class='column'>
                    <a href='{$site_config['baseurl']}/blackjack.php?id=100'><div class='has-text-centered $color5'>BlackJack 100GB</div>
                        <img src='{$site_config['pic_base_url']}blackjack.jpg' alt='blackjack' class='tooltipper round10' title='BlackJack 100GB' />
                    </a>
                </div>
            </div>
            <div class='columns'>
                <div class='column'>
                    <a href='{$site_config['baseurl']}/blackjack.php?id=250'><div class='has-text-centered $color6'>BlackJack 250GB</div>
                        <img src='{$site_config['pic_base_url']}blackjack.jpg' alt='blackjack' class='tooltipper round10' title='BlackJack 250GB' />
                    </a>
                </div>
                <div class='column'>
                    <a href='{$site_config['baseurl']}/blackjack.php?id=500'><div class='has-text-centered $color7'>BlackJack 500GB</div>
                        <img src='{$site_config['pic_base_url']}blackjack.jpg' alt='blackjack' class='tooltipper round10' title='BlackJack 500GB' />
                    </a>
                </div>
                <div class='column'>
                    <a href='{$site_config['baseurl']}/blackjack.php?id=1024'><div class='has-text-centered $color8'>BlackJack 1TB</div>
                        <img src='{$site_config['pic_base_url']}blackjack.jpg' alt='blackjack' class='tooltipper round10' title='BlackJack 1TB' />
                    </a>
                </div>
            </div>";

echo stdhead('Games') . wrapper($HTMLOUT) . stdfoot();
