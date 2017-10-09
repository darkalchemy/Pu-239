<?php
require_once realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..') . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'user_functions.php';
require_once INCL_DIR . 'html_functions.php';
check_user_status();
$lang = array_merge(load_language('global'));
$lottery_root = ROOT_DIR . 'lottery' . DIRECTORY_SEPARATOR;
$valid = [
    'config'      => [
        'minclass' => UC_STAFF,
        'file'     => $lottery_root . 'config.php',
    ],
    'viewtickets' => [
        'minclass' => UC_STAFF,
        'file'     => $lottery_root . 'viewtickets.php',
    ],
    'tickets'     => [
        'minclass' => UC_USER,
        'file'     => $lottery_root . 'tickets.php',
    ],
];
$do = isset($_GET['do']) && in_array($_GET['do'], array_keys($valid)) ? $_GET['do'] : '';
if ($CURUSER['game_access'] == 0 || $CURUSER['game_access'] > 1 || $CURUSER['suspended'] == 'yes') {
    stderr('Error', 'Your gaming rights have been disabled.');
    exit();
}
switch (true) {
    case $do === 'config' && $CURUSER['class'] >= $valid['config']['minclass']:
        require_once $valid['config']['file'];
        break;

    case $do == 'viewtickets' && $CURUSER['class'] >= $valid['viewtickets']['minclass']:
        require_once $valid['viewtickets']['file'];
        break;

    case $do == 'tickets' && $CURUSER['class'] >= $valid['tickets']['minclass']:
        require_once $valid['tickets']['file'];
        break;

    default:
        $html = "
                <div class='container-fluid portlet'>
                    <h1 class='text-center'>{$site_config['site_name']} Lottery</h1>";

        $lconf = sql_query('SELECT * FROM lottery_config') or sqlerr(__FILE__, __LINE__);
        while ($ac = mysqli_fetch_assoc($lconf)) {
            $lottery_config[$ac['name']] = $ac['value'];
        }
        if (!$lottery_config['enable']) {
            $html .= stdmsg('Sorry', 'Lottery is closed at the moment');
        } elseif ($lottery_config['end_date'] > TIME_NOW) {
            $html .= stdmsg('Lottery in progress', 'Lottery started on <b>' . get_date($lottery_config['start_date'], 'LONG') . '</b> and ends on <b>' . get_date($lottery_config['end_date'], 'LONG') . "</b> remaining <span>" . mkprettytime($lottery_config['end_date'] - TIME_NOW) . "</span><br>
       <p>" . ($CURUSER['class'] >= $valid['viewtickets']['minclass'] ? "<a href='lottery.php?do=viewtickets'>[View bought tickets]</a>&#160;&#160;" : '') . "<a href='lottery.php?do=tickets'>[Buy tickets]</a></p>");
        }
        //get last lottery data
        if (!empty($lottery_config['lottery_winners'])) {
            $html .= stdmsg('Last lottery', '' . get_date($lottery_config['lottery_winners_time'], 'LONG') . '');
            $uids = (strpos($lottery_config['lottery_winners'], '|') ? explode('|', $lottery_config['lottery_winners']) : $lottery_config['lottery_winners']);
            $last_winners = [];
            $qus = sql_query('SELECT id,username FROM users WHERE id ' . (is_array($uids) ? 'IN (' . join(',', $uids) . ')' : '=' . $uids)) or sqlerr(__FILE__, __LINE__);
            while ($aus = mysqli_fetch_assoc($qus)) {
                $last_winners[] = "<a href='userdetails.php?id=" . (int)$aus['id'] . "'>" . htmlsafechars($aus['username']) . '</a>';
            }
            $html .= begin_main_frame();
            $html .= stdmsg('Lottery Winners Info', "<ul><li>Last winners: " . join(', ', $last_winners) . '</li><li>Amount won	(each): ' . $lottery_config['lottery_winners_amount'] . "</li></ul><br>
        <p>" . ($CURUSER['class'] >= $valid['config']['minclass'] ? "<a href='lottery.php?do=config'>[Lottery configuration]</a>&#160;&#160;" : 'Nothing Configured Atm Sorry') . '</p>');
            $html .= end_main_frame();
        } else {
            $html .= "
                    <div class='bordered padleft10 padright10 top20 bottom20'>
                        <div class='alt_bordered transparent'>
                            <ul>
                                <li>Nobody has won, because nobody has played yet :)</li>
                            </ul>" . ($CURUSER['class'] >= $valid['config']['minclass'] ? "
                            <a class='altlink' href='./lottery.php?do=config'>Lottery configuration</a>" : "
                            <span>Nothing Configured Atm Sorry.</span>") . "
                        </div>
                    </div>";
        }
        $html .= "
                </div>
            </div>";
        echo stdhead('Lottery') . $html . stdfoot();
}
