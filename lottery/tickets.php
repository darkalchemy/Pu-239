<?php
require_once realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..') . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'html_functions.php';

$lconf = sql_query('SELECT * FROM lottery_config') or sqlerr(__FILE__, __LINE__);
while ($ac = mysqli_fetch_assoc($lconf)) {
    $lottery_config[ $ac['name'] ] = $ac['value'];
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fail = false;
    $tickets = isset($_POST['tickets']) ? (int)$_POST['tickets'] : '';
    if (!$tickets) {
        setSessionVar('is-warning', "How many tickets you wanna buy? [{$_POST['tickets']}]");
        $fail = true;
    } elseif ($tickets <= 0) {
        setSessionVar('is-warning', "You can't buy a negative quantity? [{$_POST['tickets']}]");
        $fail = true;
    }
    $user_tickets = get_row_count('tickets', 'WHERE user = ' . $CURUSER['id']);
    if ($user_tickets + $tickets > $lottery_config['user_tickets']) {
        setSessionVar('is-warning', 'You reached your limit max is ' . $lottery_config['user_tickets'] . ' ticket(s)');
        $fail = true;
    } elseif ($CURUSER['seedbonus'] < $tickets * $lottery_config['ticket_amount']) {
        setSessionVar('is-warning', 'You need more points to buy the amount of tickets you want');
        $fail = true;
    }
    for ($i = 1; $i <= $tickets; ++$i) {
        $t[] = '(' . $CURUSER['id'] . ')';
    }
    if (!$fail) {
        if (sql_query('INSERT INTO tickets(user) VALUES ' . join(', ', $t))) {
            sql_query('UPDATE users SET seedbonus = seedbonus - ' . ($tickets * $lottery_config['ticket_amount']) . ' WHERE id = ' . $CURUSER['id']);
            $seedbonus_new = $CURUSER['seedbonus'] - ($tickets * $lottery_config['ticket_amount']);
            $What_Cache = (XBT_TRACKER == true ? 'userstats_xbt_' : 'userstats_');
            $What_Expire = (XBT_TRACKER == true ? $site_config['expires']['u_stats_xbt'] : $site_config['expires']['u_stats']);
            $cache->update_row($What_Cache . $CURUSER['id'], [
                'seedbonus' => $seedbonus_new,
            ], $What_Expire);
            $What_Cache = (XBT_TRACKER == true ? 'user_stats_xbt_' : 'user_stats_');
            $What_Expire = (XBT_TRACKER == true ? $site_config['expires']['user_stats_xbt'] : $site_config['expires']['user_stats']);
            $cache->update_row($What_Cache . $CURUSER['id'], [
                'seedbonus' => $seedbonus_new,
            ], $What_Expire);
            setSessionVar('is-success', 'You bought <b class="has-text-primary">' . number_format($tickets) . '</b>. You now have <b class="has-text-primary">' . number_format($tickets + $user_tickets) . '</b> tickets!
');
        } else {
            setSessionVar('is-warning', 'There was an error with the update query, mysql error: ' . ((is_object($GLOBALS['___mysqli_ston'])) ? mysqli_error($GLOBALS['___mysqli_ston']) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
        }
    }
}
$classes_allowed = (strpos($lottery_config['class_allowed'], '|') ? explode('|', $lottery_config['class_allowed']) : $lottery_config['class_allowed']);
if (!(is_array($classes_allowed) ? in_array($CURUSER['class'], $classes_allowed) : $CURUSER['class'] == $classes_allowed)) {
    setSessionVar('is-danger', 'Your class is not allowed to play in this lottery');
    header('Location: index.php');
    die();
}
//some default values
$lottery['total_pot'] = 0;
$lottery['current_user'] = [];
$lottery['current_user']['tickets'] = [];
$lottery['total_tickets'] = 0;
//select the total amount of tickets
$qt = sql_query('SELECT id,user FROM tickets ORDER BY id ASC ') or sqlerr(__FILE__, __LINE__);
while ($at = mysqli_fetch_assoc($qt)) {
    $lottery['total_tickets'] += 1;
    if ($at['user'] == $CURUSER['id']) {
        $lottery['current_user']['tickets'][] = $at['id'];
    }
}
//set the current user total tickets amount
$lottery['current_user']['total_tickets'] = count($lottery['current_user']['tickets']);
//check if the prize setting is set to calculate the totat pot
if ($lottery_config['use_prize_fund']) {
    $lottery['total_pot'] = $lottery_config['prize_fund'];
} else {
    $lottery['total_pot'] = $lottery_config['ticket_amount'] * $lottery['total_tickets'];
}
//how much the winner gets
$lottery['per_user'] = round($lottery['total_pot'] / $lottery_config['total_winners'], 2);
//how many tickets could the user buy
$lottery['current_user']['could_buy'] = $lottery['current_user']['can_buy'] = $lottery_config['user_tickets'] - $lottery['current_user']['total_tickets'];
//if he has less bonus points calculate how many tickets can he buy with what he has
if ($CURUSER['seedbonus'] < ($lottery['current_user']['could_buy'] * $lottery_config['ticket_amount'])) {
    for ($lottery['current_user']['can_buy']; $CURUSER['seedbonus'] < ($lottery_config['ticket_amount'] * $lottery['current_user']['can_buy']); --$lottery['current_user']['can_buy']) ;
}
//check if the lottery ended if the lottery ended don't allow the user to buy more tickets or if he has already bought the max tickets
if (time() > $lottery_config['end_date'] || $lottery_config['user_tickets'] <= $lottery['current_user']['total_tickets']) {
    $lottery['current_user']['can_buy'] = 0;
}
$html .= "
        <h1 class='has-text-centered'>{$site_config['site_name']} Lottery</h1>
        <div class='bordered bottom10 top20'>
            <div class='alt_bordered bg-00 has-text-centered'>
                <ul style='text-align:left;'>
                    <li>Tickets are non-refundable</li>
                    <li>Each ticket costs <b>" . number_format($lottery_config['ticket_amount']) . '</b> Karma Bonus Points, which is taken from your seedbonus amount</li>
                    <li>Purchaseable shows how many tickets you can afford to purchase.</li>
                    <li>You can only buy up to your purchaseable amount.</li>
                    <li>The competiton will end: <b>' . get_date($lottery_config['end_date'], 'LONG') . '</b></li>
                    <li>There will be <b>' . $lottery_config['total_winners'] . '</b> winner(s) who will be picked at random.</li>
                    <li>Winner(s) will get <b>' . number_format($lottery['per_user']) . '</b> added to their seedbonus amount</li>
                    <li>The Winners will be announced once the lottery has closed and posted on the home page.</li>';
if (!$lottery_config['use_prize_fund']) {
    $html .= '
                    <li>The more tickets that are sold the bigger the pot will be !</li>';
}
if (count($lottery['current_user']['tickets'])) {
    $html .= '
                    <li>You own ticket numbers : <b>' . join('</b>, <b>', $lottery['current_user']['tickets']) . '</b></li>';
}
$html .= "
                </ul>
            </div>
        </div>
        <table class='table table-bordered table-striped top20 bottom20'>
            <tr>
                <td>Total Pot</td>
                <td>" . number_format($lottery['total_pot']) . "</td>
            </tr>
            <tr>
                <td>Total Tickets Purchased</td>
                <td>" . number_format($lottery['total_tickets']) . " Tickets</td>
            </tr>
            <tr>
                <td>Tickets Purchased by You</td>
                <td>" . number_format($lottery['current_user']['total_tickets']) . " Tickets</td>
            </tr>
            <tr>
                <td>Purchaseable</td>
                <td>" . ($lottery['current_user']['could_buy'] > $lottery['current_user']['can_buy'] ? 'you have points for <b>' . number_format($lottery['current_user']['can_buy']) . '</b> ticket(s) but you can buy another <b>' . ($lottery['current_user']['could_buy'] - $lottery['current_user']['can_buy']) . '</b> ticket(s) if you get more bonus points' : number_format($lottery['current_user']['can_buy'])) . '</td>
            </tr>
        </table>';
if ($lottery['current_user']['can_buy'] > 0) {
    $html .= "
        <form action='lottery.php?action=tickets' method='post'>
            <div class='has-text-centered bottom20'>
                <input type='text' size='10' name='tickets' />
                <input type='submit' value='Buy tickets' class='button is-small' />
            </div>
        </form>";
}

echo stdhead('Buy tickets for lottery') . wrapper($html) . stdfoot();
