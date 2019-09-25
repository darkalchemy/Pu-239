<?php

declare(strict_types = 1);

require_once __DIR__ . '/../include/bittorrent.php';
require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_html.php';
$user = check_user_status();
$lang = load_language('global');
global $site_config;

if ($user['game_access'] !== 1 || $user['status'] !== 0) {
    stderr('Error', 'Your gaming rights have been disabled.', 'bottom20');
    die();
}

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
        'minclass' => $site_config['allowed']['play'],
        'file' => $lottery_root . 'tickets.php',
    ],
];
$do = isset($_GET['action']) && in_array($_GET['action'], array_keys($valid)) ? $_GET['action'] : '';

switch (true) {
    case $do === 'config' && $user['class'] >= $valid['config']['minclass']:
        require_once $valid['config']['file'];
        break;

    case $do === 'viewtickets' && $user['class'] >= $valid['viewtickets']['minclass']:
        require_once $valid['viewtickets']['file'];
        break;

    case $do === 'tickets' && $user['class'] >= $valid['tickets']['minclass']:
        require_once $valid['tickets']['file'];
        break;

    default:
        $html = "
                    <h1 class='has-text-centered'>{$site_config['site']['name']} Lottery</h1>";

        $lconf = sql_query('SELECT * FROM lottery_config') or sqlerr(__FILE__, __LINE__);
        while ($ac = mysqli_fetch_assoc($lconf)) {
            $lottery_config[$ac['name']] = $ac['value'];
        }
        if (!$lottery_config['enable']) {
            $html .= stdmsg('Sorry', 'Lottery is closed at the moment', 'bottom20');
        } elseif ($lottery_config['end_date'] > TIME_NOW) {
            $html .= stdmsg('Lottery in progress', '<div>Lottery started on <b>' . get_date((int) $lottery_config['start_date'], 'LONG') . '</b> and ends on <b>' . get_date((int) $lottery_config['end_date'], 'LONG') . '</b> remaining <span>' . mkprettytime($lottery_config['end_date'] - TIME_NOW) . "</span></div>
       <div class='top10'>" . ($user['class'] >= $valid['viewtickets']['minclass'] ? "<a href='{$site_config['paths']['baseurl']}/lottery.php?action=viewtickets' class='button is-small margin10'>View bought tickets</a>" : '') . "<a href='{$site_config['paths']['baseurl']}/lottery.php?action=tickets' class='button is-small margin10'>Buy tickets</a></div>", 'bottom20 has-text-centered');
        }
        //get last lottery data
        if (!empty($lottery_config['lottery_winners'])) {
            $html .= stdmsg('Last lottery', get_date((int) $lottery_config['lottery_winners_time'], 'LONG'), 'top20');
            $uids = (strpos($lottery_config['lottery_winners'], '|') ? explode('|', $lottery_config['lottery_winners']) : $lottery_config['lottery_winners']);
            $last_winners = [];
            $qus = sql_query('SELECT id, username FROM users WHERE ' . (is_array($uids) ? 'id IN (' . implode(', ', $uids) . ')' : 'id=' . $uids)) or sqlerr(__FILE__, __LINE__);
            while ($aus = mysqli_fetch_assoc($qus)) {
                $last_winners[] = format_username((int) $aus['id']);
            }
            $html .= stdmsg('Lottery Winners Info', '<ul><li>Last winners: ' . implode(', ', $last_winners) . '</li><li>Amount won    (each): ' . $lottery_config['lottery_winners_amount'] . '</li></ul><br>
        <p>' . ($user['class'] >= $valid['config']['minclass'] ? "<a href='{$site_config['paths']['baseurl']}/lottery.php?action=config' class='button is-small margin10'>Lottery configuration</a>" : 'Nothing Configured Atm Sorry') . '</p>', 'top20');
        } else {
            $html .= main_div("
                        <div class='padding20 has-text-centered'>
                            <div class='bottom20'>
                                Nobody has won, because nobody has played yet :)
                            </div>" . ($user['class'] >= $valid['config']['minclass'] ? "
                            <a href='{$site_config['paths']['baseurl']}/lottery.php?action=config' class='button is-small'>Lottery configuration</a>" : '
                            <span>Nothing Configured ATM Sorry.</span>') . '
                        </div>');
        }

        echo stdhead('Lottery') . wrapper($html) . stdfoot();
}
