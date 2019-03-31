<?php

require_once __DIR__ . '/../include/bittorrent.php';
require_once CLASS_DIR . 'class_check.php';
require_once INCL_DIR . 'function_html.php';
require_once INCL_DIR . 'function_users.php';
class_check(UC_STAFF);
global $site_config, $cache, $mysqli;

$lconf = sql_query('SELECT * FROM lottery_config') or sqlerr(__FILE__, __LINE__);
while ($ac = mysqli_fetch_assoc($lconf)) {
    $lottery_config[$ac['name']] = $ac['value'];
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    foreach ([
                 'ticket_amount' => 0,
                 'class_allowed' => 1,
                 'user_tickets' => 0,
                 'end_date' => 0,
             ] as $key => $type) {
        if (isset($_POST[$key]) && ($type == 0 && $_POST[$key] == 0 || $type == 1 && count($_POST[$key]) == 0)) {
            $session->set('is-warning', 'You forgot to fill some data');
        }
    }
    if (!empty($lottery_config)) {
        foreach ($lottery_config as $c_name => $c_value) {
            if (isset($_POST[$c_name]) && $_POST[$c_name] != $c_value) {
                $update[] = '(' . sqlesc($c_name) . ',' . sqlesc(is_array($_POST[$c_name]) ? implode('|', $_POST[$c_name]) : $_POST[$c_name]) . ')';
                if ($_POST['c_name'] === 'prize_fund') {
                    $fund = number_format($_POST['c_value']);
                } elseif ($_POST['c_name'] === 'ticket_amount') {
                    $cost = number_format($_POST['c_value']);
                } elseif ($_POST['c_name'] === 'ticket_amount_type') {
                    $type = ucfirst($_POST['c_value']);
                }
            }
        }
    }
    if (sql_query('INSERT INTO lottery_config(name,value) VALUES ' . implode(', ', $update) . ' ON DUPLICATE KEY UPDATE value = VALUES(value)')) {
        $cache->delete('lottery_info_');
        if ($site_config['autoshout_on'] || $site_config['irc_autoshout_on'] == 1) {
            $link = "[url={$site_config['baseurl']}/lottery.php]Lottery[/url]";
            $msg = "The $link has begun!! Get your tickets now. The pot is $fund and each ticket is only $cost $type.";
            autoshout($msg);
        }
        $session->set('is-success', 'Lottery configuration was saved!');
        header("Location: {$site_config['baseurl']}/lottery.php");
        die();
    } else {
        $session->set('is-warning', 'There was an error while executing the update query. Mysql error: ' . ((is_object($mysqli)) ? mysqli_error($mysqli) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
    }
}
if (!empty($lottery_config)) {
    if ($lottery_config['enable']) {
        $classes = implode(', ', array_map('get_user_class_name', explode('|', $lottery_config['class_allowed'])));
        $html .= stdmsg('Lottery configuration closed', 'Classes playing in this lottery are : <b>' . $classes . '</b>');
    } else {
        $html .= "
        <form action='{$site_config['baseurl']}/lottery.php?action=config' method='post' accept-charset='utf-8'>
            <div class='portlet'>";
        $table = "
                    <tr>
                        <td class='rowhead'>Enable The Lottery</td>
                        <td>
                            <input type='radio' name='enable' value='1' " . ($lottery_config['enable'] ? 'checked' : '') . "> Yes
                            <input type='radio' name='enable' value='0' " . (!$lottery_config['enable'] ? 'checked' : '') . "> No
                        </td>
                    </tr>
                    <tr>
                        <td>Use Prize Fund (No, uses default pot of all users)</td>
                        <td>
                            <input type='radio' name='use_prize_fund' value='1' " . ($lottery_config['use_prize_fund'] ? 'checked' : '') . "> Yes
                            <input type='radio' name='use_prize_fund' value='0' " . (!$lottery_config['use_prize_fund'] ? 'checked' : '') . "> No
                        </td>
                    </tr>
                    <tr>
                        <td>Prize Fund</td>
                        <td><input type='text' name='prize_fund' value='{$lottery_config['prize_fund']}' class='w-100' required></td>
                    </tr>
                    <tr>
                        <td>Ticket Amount</td>
                        <td><input type='text' name='ticket_amount' value='{$lottery_config['ticket_amount']}' class='w-100' required></td>
                    </tr>
                    <tr>
                        <td>Ticket Amount Type</td>
                        <td>
                            <select name='ticket_amount_type' class='w-100'>
                                <option value='seedbonus' selected>Karma Bonus Points</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>Amount Of Tickets Allowed</td>
                        <td><input type='text' name='user_tickets' value='{$lottery_config['user_tickets']}' class='w-100' required></td>
                    </tr>
                    <tr>
                        <td>Classes Allowed</td>
                        <td>";
        for ($i = UC_MIN; $i <= UC_MAX; ++$i) {
            $table .= "
                        <label for='c{$i}'>
                            <input type='checkbox' value='{$i}' id='c{$i}' name='class_allowed[]'> " . get_user_class_name($i) . '
                        </label>';
        }
        $table .= "
                        </td>
                    </tr>
                    <tr>
                        <td>Total Winners</td>
                        <td><input type='text' name='total_winners' value='{$lottery_config['total_winners']}' class='w-100' required></td>
                    </tr>
                    <tr>
                        <td>Start Date</td>
                        <td>
                            <select name='start_date' class='w-100'>
                                <option value='" . TIME_NOW . "'>Now</option>";
        for ($i = 2; $i <= 24; $i += 2) {
            $table .= "
                                <option value='" . (TIME_NOW + (3600 * $i)) . "' >" . $i . ' hours</option>';
        }
        $table .= "
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>Run Length</td>
                        <td>
                            <select name='end_date' class='w-100'>
                                <option value='0'>------</option>";
        for ($i = 1; $i <= 7; ++$i) {
            $table .= "
                                <option value='" . (TIME_NOW + (84600 * $i)) . "'>{$i} days</option>";
        }
        $table .= "
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td colspan='2'>
                            <div class='has-text-centered'>
                                <input type='submit' class='button is-small' value='Apply changes'>
                            </div>
                        </td>
                    </tr>";
        $html .= main_table($table) . '
            </div>
        </form>';
    }
}
echo stdhead('Lottery configuration') . wrapper($html) . stdfoot();
