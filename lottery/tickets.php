<?php

declare(strict_types = 1);

use Pu239\Cache;
use Pu239\Database;
use Pu239\Session;

require_once __DIR__ . '/../include/bittorrent.php';
require_once INCL_DIR . 'function_html.php';
$lconf = sql_query('SELECT * FROM lottery_config') or sqlerr(__FILE__, __LINE__);
while ($ac = mysqli_fetch_assoc($lconf)) {
    $lottery_config[$ac['name']] = $ac['value'];
}
global $container, $site_config, $CURUSER;

$session = $container->get(Session::class);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fail = false;
    $tickets = isset($_POST['tickets']) ? (int) $_POST['tickets'] : '';
    if (!$tickets) {
        $session->set('is-warning', _fe('How many tickets you wanna buy? [{0}]', $_POST['tickets']));
        $fail = true;
    } elseif ($tickets <= 0) {
        $session->set('is-warning', _fe("You can't buy a negative quantity? [{0}]", $_POST['tickets']));
        $fail = true;
    }
    $fluent = $container->get(Database::class);
    $user_tickets = $fluent->from('tickets')
                           ->select(null)
                           ->select('COUNT(id) AS count')
                           ->where('user = ?', $CURUSER['id'])
                           ->fetch('count');

    if ($user_tickets + $tickets > $lottery_config['user_tickets']) {
        $session->set('is-warning', _pfe('You reached your limit. The max is {0} ticket.', 'You reached your limit. The max is {0} tickets.', $lottery_config['user_tickets']));
        $fail = true;
    } elseif ($CURUSER['seedbonus'] < $tickets * $lottery_config['ticket_amount']) {
        $session->set('is-warning', _('You need more points to buy the amount of tickets you want'));
        $fail = true;
    }
    $t = [];
    for ($i = 1; $i <= $tickets; ++$i) {
        $t[] = '(' . $CURUSER['id'] . ')';
    }
    if (!$fail) {
        if (sql_query('INSERT INTO tickets(user) VALUES ' . implode(', ', $t))) {
            sql_query('UPDATE users SET seedbonus = seedbonus - ' . ($tickets * $lottery_config['ticket_amount']) . ' WHERE id=' . $CURUSER['id']);
            $seedbonus_new = $CURUSER['seedbonus'] - ($tickets * $lottery_config['ticket_amount']);
            $cache = $container->get(Cache::class);
            $cache->update_row('user_' . $CURUSER['id'], [
                'seedbonus' => $seedbonus_new,
            ], $site_config['expires']['user_cache']);
            $session->set('is-success', _pfe('You bought {0} ticket.', 'You bought {0} tickets.', number_format($tickets)) . ' ' . _pfe('You now have {0} ticket!', 'You now have {0} tickets!', number_format($tickets + $user_tickets)));
            if ($site_config['site']['autoshout_chat'] || $site_config['site']['autoshout_irc']) {
                $classColor = get_user_class_color($CURUSER['class']);
                $msg = _pfe('{1} has just bought {2}{0} Lottery Ticket!{3} GOOD LUCK!', '{1} has just bought {2}{0} Lottery Tickets!{3} GOOD LUCK!', $tickets, "[color=#$classColor]" . format_comment($CURUSER['username']) . '[/color]', "[url={$site_config['paths']['baseurl']}/lottery.php]", '[/url]');
                autoshout($msg);
            }
        } else {
            $session->set('is-warning', _('There was an error with the update query.'));
        }
    }
}
$classes_allowed = (strpos($lottery_config['class_allowed'], '|') ? explode('|', $lottery_config['class_allowed']) : $lottery_config['class_allowed']);
if (!(is_array($classes_allowed) ? in_array($CURUSER['class'], $classes_allowed) : $CURUSER['class'] == $classes_allowed)) {
    $session->set('is-danger', _('Your class is not allowed to play in this lottery'));
    header('Location: ' . $site_config['paths']['baseurl']);
    die();
}
//some default values
$lottery['total_pot'] = 0;
$lottery['current_user'] = [];
$lottery['current_user']['tickets'] = [];
$lottery['total_tickets'] = 0;
//select the total amount of tickets
$qt = sql_query('SELECT id,user FROM tickets ORDER BY id ') or sqlerr(__FILE__, __LINE__);
while ($at = mysqli_fetch_assoc($qt)) {
    ++$lottery['total_tickets'];
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
    for ($lottery['current_user']['can_buy']; $CURUSER['seedbonus'] < ($lottery_config['ticket_amount'] * $lottery['current_user']['can_buy']); --$lottery['current_user']['can_buy']) {
    }
}
//check if the lottery ended if the lottery ended don't allow the user to buy more tickets or if he has already bought the max tickets
if (time() > $lottery_config['end_date'] || $lottery_config['user_tickets'] <= $lottery['current_user']['total_tickets']) {
    $lottery['current_user']['can_buy'] = 0;
}
$html = "
        <h1 class='has-text-centered'>" . _fe('{0} Lottery', $site_config['site']['name']) . '</h1>';
$body = "
                <ul class='padding20 disc left20'>
                    <li>" . _('Tickets are non-refundable') . '</li>
                    <li>' . _fe('Each ticket costs {0} Karma Bonus Points, which is taken from your seedbonus amount.', number_format((int) $lottery_config['ticket_amount'])) . '</li>
                    <li>' . _('Purchasable shows how many tickets you can afford to purchase.') . '</li>
                    <li>' . _('You can only buy up to your purchaseable amount.') . '</li>
                    <li>' . _fe('The competition will end: {0}', get_date((int) $lottery_config['end_date'], 'LONG')) . '</li>
                    <li>' . _pfe('There will be {0} winner, picked at random.', 'There will be {0} winners, picked at random.', $lottery_config['total_winners']) . '</li>
                    <li>' . _pfe('The {0} winner will get {1} added to their seedbonus amount.', 'The {0} winners will get {1} added to their seedbonus amount.', $lottery_config['total_winners'], number_format($lottery['per_user'])) . '</li>
                    <li>' . _('The Winners will be announced once the lottery has closed and posted on the home page.') . '</li>';
if (!$lottery_config['use_prize_fund']) {
    $body .= '
                    <li>' . _('The more tickets that are sold the bigger the pot will be!') . '</li>';
}
if (!empty($lottery['current_user']['tickets']) && count($lottery['current_user']['tickets'])) {
    $body .= '
                    <li>' . _fe('You own ticket numbers: {0}', implode(', ', $lottery['current_user']['tickets'])) . '</li>';
}
$body .= '
                </ul>';
$table = '
            <tr>
                <td>' . _('Total Pot') . '</td>
                <td>' . number_format((int) $lottery['total_pot']) . '</td>
            </tr>
            <tr>
                <td>' . _('Total Tickets Purchased') . '</td>
                <td>' . _pfe('{0} Ticket', '{0} Tickets', number_format((int) $lottery['total_tickets'])) . '</td>
            </tr>
            <tr>
                <td>' . _('Tickets Purchased by You') . '</td>
                <td>' . _pfe('{0} Ticket', '{0} Tickets', number_format((int) $lottery['current_user']['total_tickets'])) . '</td>
            </tr>
            <tr>
                <td>' . _('Purchaseable') . '</td>
                <td>' . ($lottery['current_user']['could_buy'] > $lottery['current_user']['can_buy'] ? _pfe('You have enough points for {0} ticket.', 'You have enough points for {0} tickets.', number_format((int) $lottery['current_user']['can_buy'])) . ' ' . _pfe('You can buy another {0} ticket if you get get more bonus points.', 'You can buy another {0} tickets if you get get more bonus points.', $lottery['current_user']['could_buy'] - $lottery['current_user']['can_buy']) : number_format($lottery['current_user']['can_buy'])) . '</td>
            </tr>';

$html = main_div($body) . main_table($table, '', 'top20');
if ($lottery['current_user']['can_buy'] > 0) {
    $available = $lottery['current_user']['could_buy'] < $lottery['current_user']['can_buy'] ? $lottery['current_user']['could_buy'] : $lottery['current_user']['can_buy'];
    $html .= "
        <form action='lottery.php?action=tickets' method='post' enctype='multipart/form-data' accept-charset='utf-8'>
            <div class='has-text-centered margin20'>
                <input type='number' min='0' max='$available' name='tickets' value='1' required>
                <input type='submit' value='Buy tickets' class='button is-small left10'>
            </div>
        </form>";
}

$title = _('Buy tickets for lottery');
$breadcrumbs = [
    "<a href='{$site_config['paths']['baseurl']}/games.php'>" . _('Games') . '</a>',
    "<a href='{$site_config['paths']['baseurl']}/lottery.php'>" . _('Lottery') . '</a>',
    "<a href='{$_SERVER['PHP_SELF']}'>$title</a>",
];
echo stdhead($title, [], 'page-wrapper', $breadcrumbs) . wrapper($html) . stdfoot();
