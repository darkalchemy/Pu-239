<?php
/**
 |--------------------------------------------------------------------------|
 |   https://github.com/Bigjoos/                			    |
 |--------------------------------------------------------------------------|
 |   Licence Info: GPL			                                    |
 |--------------------------------------------------------------------------|
 |   Copyright (C) 2010 U-232 V4					    |
 |--------------------------------------------------------------------------|
 |   A bittorrent tracker source based on TBDev.net/tbsource/bytemonsoon.   |
 |--------------------------------------------------------------------------|
 |   Project Leaders: Mindless,putyn.					    |
 |--------------------------------------------------------------------------|
  _   _   _   _   _     _   _   _   _   _   _     _   _   _   _
 / \ / \ / \ / \ / \   / \ / \ / \ / \ / \ / \   / \ / \ / \ / \
( U | - | 2 | 3 | 2 )-( S | o | u | r | c | e )-( C | o | d | e )
 \_/ \_/ \_/ \_/ \_/   \_/ \_/ \_/ \_/ \_/ \_/   \_/ \_/ \_/ \_/
 */
if (!defined('IN_LOTTERY')) die('You can\'t access this file directly!');
if ($CURUSER['class'] < UC_MODERATOR) stderr('Err', 'What you doing here dude?');
//get the config from db
$lconf = sql_query('SELECT * FROM lottery_config') or sqlerr(__FILE__, __LINE__);
while ($ac = mysqli_fetch_assoc($lconf)) $lottery_config[$ac['name']] = $ac['value'];
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    //can't be 0
    foreach (array(
        'ticket_amount' => 0,
        'class_allowed' => 1,
        'user_tickets' => 0,
        'end_date' => 0
    ) as $key => $type) {
        if (isset($_POST[$key]) && ($type == 0 && $_POST[$key] == 0 || $type == 1 && count($_POST[$key]) == 0)) stderr('Err', 'You forgot to fill some data');
    }
    foreach ($lottery_config as $c_name => $c_value) if (isset($_POST[$c_name]) && $_POST[$c_name] != $c_value) $update[] = '(' . sqlesc($c_name) . ',' . sqlesc(is_array($_POST[$c_name]) ? join('|', $_POST[$c_name]) : $_POST[$c_name]) . ')';
    if (sql_query('INSERT INTO lottery_config(name,value) VALUES ' . join(',', $update) . ' ON DUPLICATE KEY update value=values(value)')) stderr('Success', 'Lottery configuration was saved! Click <a href=\'lottery.php?do=config\'>here to get back</a>');
    else stderr('Error', 'There was an error while executing the update query. Mysql error: ' . ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
    exit;
}
$html = begin_main_frame();
if ($lottery_config['enable']) {
    $classes = join(', ', array_map('get_user_class_name', explode('|', $lottery_config['class_allowed'])));
    $html.= stdmsg('Lottery configuration closed', 'Classes playing in this lottery are : <b>' . $classes . '</b>');
} else {
    $html.= begin_frame('Lottery configuration');
    $html.= "<form action='lottery.php?do=config' method='post'>
  <table width='100%' border='1' cellpadding='5' cellspacing='0' >
	<tr>
    <td width='50%' class='table' align='left'>Enable The Lottery</td>
    <td class='table' align='left'>Yes <input class='table' type='radio' name='enable' value='1' " . ($lottery_config['enable'] ? 'checked=\'checked\'' : '') . " /> No <input class='table' type='radio' name='enable' value='0' " . (!$lottery_config['enable'] ? 'checked=\'checked\'' : '') . " />
    </td>
  </tr>
	<tr>
    <td width='50%' class='table' align='left'>Use Prize Fund (No, uses default pot of all users)</td><td class='table' align='left'>Yes <input class='table' type='radio' name='use_prize_fund' value='1' " . ($lottery_config['use_prize_fund'] ? 'checked=\'checked\'' : '') . " /> No <input class='table' type='radio' name='use_prize_fund' value='0' " . (!$lottery_config['use_prize_fund'] ? 'checked=\'checked\'' : '') . " /></td>
  </tr>
	<tr>
   <td width='50%' class='table' align='left'>Prize Fund</td>
   <td class='table' align='left'><input type='text' name='prize_fund' value='{$lottery_config['prize_fund']}' /></td>
  </tr>
	<tr>
   <td width='50%' class='table' align='left'>Ticket Amount</td>
   <td class='table' align='left'><input type='text' name='ticket_amount' value='{$lottery_config['ticket_amount']}' /></td>
  </tr>
	<tr>
    <td width='50%' class='table' align='left'>Ticket Amount Type</td>
    <td class='table' align='left'><select name='ticket_amount_type'><option value='seedbonus' selected='selected'>seedbonus</option></select></td>
  </tr>
	<tr><td width='50%' class='table' align='left'>Amount Of Tickets Allowed</td><td class='table' align='left'><input type='text' name='user_tickets' value='{$lottery_config['user_tickets']}' /></td>
  </tr>
	<tr><td width='50%' class='table' align='left' valign='top'>Classes Allowed</td><td class='table' align='left'>";
    for ($i = UC_USER; $i <= UC_SYSOP; $i++) $html.= "<label for='c{$i}'><input type='checkbox' value='{$i}' id='c{$i}' name='class_allowed[]'/> " . get_user_class_name($i) . "</label><br/>";
    $html.= "</td></tr>";
    $html.= "
   <tr>
    <td width='50%' class='table' align='left'>Total Winners</td>
    <td class='table' align='left'><input type='text' name='total_winners' value='{$lottery_config['total_winners']}' /></td>
  </tr>
	<tr>
    <td width='50%' class='table' align='left'>Start Date</td>
    <td class='table' align='left'>
      <select name='start_date'><option value='" . TIME_NOW . "'>Now</option>";
    for ($i = 2; $i <= 24; $i+= 2) $html.= "<option value='" . (TIME_NOW + (3600 * $i)) . "' >" . $i . " hours</option>";
    $html.= "</select></td>
    </tr>
    <tr>
      <td width='50%' class='table' align='left'>End Date</td><td class='table' align='left'><select name='end_date'>
        <option value='0'>------</option>";
    for ($i = 1; $i <= 7; $i++) $html.= "<option value='" . (TIME_NOW + (84600 * $i)) . "'>{$i} days</option>";
    $html.= "</select></td>
    </tr>
    <tr>
      <td colspan='2' class='table' align='center'><input type='submit' value='Apply changes' /></td>
    </tr>";
    $html.= "</table></form>";
    $html.= end_frame();
}
$html.= end_main_frame();
echo (stdhead('Lottery configuration') . $html . stdfoot());
?>
