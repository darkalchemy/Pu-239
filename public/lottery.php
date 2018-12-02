<?php

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'user_functions.php';
require_once INCL_DIR . 'html_functions.php';
check_user_status();
global $CURUSER, $site_config;

$lang = load_language('global');
$html = '';
$lottery_config = [];
$lottery_root = ROOT_DIR . 'lottery' . DIRECTORY_SEPARATOR;
$valid = [
    'config' => [
        'minclass' => UC_STAFF,
        'file' => $lottery_root . 'config.php',
    ],
    'viewtickets' => [
        'minclass' => UC_STAFF,
        'file' => $lottery_root . 'viewtickets.php',
    ],
    'tickets' => [
        'minclass' => MIN_TO_PLAY,
        'file' => $lottery_root . 'tickets.php',
    ],
];
$do = isset($_GET['action']) && in_array($_GET['action'], array_keys($valid)) ? $_GET['action'] : '';
if ($CURUSER['game_access'] == 0 || $CURUSER['game_access'] > 1 || $CURUSER['suspended'] === 'yes') {
    stderr('Error', 'Your gaming rights have been disabled.');
    die();
}
switch (true) {
    case $do === 'config' && $CURUSER['class'] >= $valid['config']['minclass']:
        require_once $valid['config']['file'];
        break;

    case $do === 'viewtickets' && $CURUSER['class'] >= $valid['viewtickets']['minclass']:
        require_once $valid['viewtickets']['file'];
        break;

    case $do === 'tickets' && $CURUSER['class'] >= $valid['tickets']['minclass']:
        require_once $valid['tickets']['file'];
        break;

    default:
        $html = "
                    <h1 class='has-text-centered'>{$site_config['site_name']} Lottery</h1>";

        $lconf = sql_query('SELECT * FROM lottery_config') or sqlerr(__FILE__, __LINE__);
        while ($ac = mysqli_fetch_assoc($lconf)) {
            $lottery_config[$ac['name']] = $ac['value'];
        }
        if (!$lottery_config['enable']) {
            $html .= stdmsg('Sorry', 'Lottery is closed at the moment');
        } elseif ($lottery_config['end_date'] > TIME_NOW) {
            $html .= stdmsg('Lottery in progress', 'Lottery started on <b>' . get_date($lottery_config['start_date'], 'LONG') . '</b> and ends on <b>' . get_date($lottery_config['end_date'], 'LONG') . '</b> remaining <span>' . mkprettytime($lottery_config['end_date'] - TIME_NOW) . "</span><br>
       <p class='top10'>" . ($CURUSER['class'] >= $valid['viewtickets']['minclass'] ? "<a href='{$site_config['baseurl']}/lottery.php?action=viewtickets' class='button is-small margin10'>View bought tickets</a>" : '') . "<a href='{$site_config['baseurl']}/lottery.php?action=tickets' class='button is-small margin10'>Buy tickets</a></p>");
        }
        //get last lottery data
        if (!empty($lottery_config['lottery_winners'])) {
            $html .= stdmsg('Last lottery', '' . get_date($lottery_config['lottery_winners_time'], 'LONG') . '');
            $uids = (strpos($lottery_config['lottery_winners'], '|') ? explode('|', $lottery_config['lottery_winners']) : $lottery_config['lottery_winners']);
            $last_winners = [];
            $qus = sql_query('SELECT id, username FROM users WHERE ' . (is_array($uids) ? 'id IN (' . implode(', ', $uids) . ')' : 'id = ' . $uids)) or sqlerr(__FILE__, __LINE__);
            while ($aus = mysqli_fetch_assoc($qus)) {
                $last_winners[] = format_username($aus['id']);
            }
            $html .= stdmsg('Lottery Winners Info', '<ul><li>Last winners: ' . implode(', ', $last_winners) . '</li><li>Amount won    (each): ' . $lottery_config['lottery_winners_amount'] . '</li></ul><br>
        <p>' . ($CURUSER['class'] >= $valid['config']['minclass'] ? "<a href='{$site_config['baseurl']}/lottery.php?action=config' class='button is-small margin10'>Lottery configuration</a>" : 'Nothing Configured Atm Sorry') . '</p>');
        } else {
            $html .= "
                    <div class='bordered top20'>
                        <div class='alt_bordered bg-00'>
                            <ul>
                                <li>Nobody has won, because nobody has played yet :)</li>
                            </ul>" . ($CURUSER['class'] >= $valid['config']['minclass'] ? "
                            <a href='{$site_config['baseurl']}/lottery.php?action=config' class='button is-small margin10'>Lottery configuration</a>" : '
                            <span>Nothing Configured Atm Sorry.</span>') . '
                        </div>
                    </div>';
        }

        echo stdhead('Lottery') . wrapper($html) . stdfoot();
}
