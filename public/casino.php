<?php
require_once realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..') . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'user_functions.php';
require_once INCL_DIR . 'html_functions.php';
global $CURUSER, $site_config, $cache;

//require_once INCL_DIR . 'function_ircbot.php';
//== Updated casino.php by Bigjoos
check_user_status();

$lang = array_merge(load_language('global'), load_language('casino'));
//== Config
$amnt = $nobits = $abcdefgh = 0;
$dummy = '';
$maxbetGB = 50;
$maxbet = $maxbetGB * 1024 * 1024 * 1024;
$mb_basic = 1024 * 1024;
$max_download_user = $mb_basic * 1024 * 1024 * 1024; //= 255 Gb
$max_download_global = $mb_basic * $mb_basic * 1024; //== 10.0 Tb
$required_ratio = 1.0; //== Min ratio
$user_everytimewin_mb = $mb_basic * 20; //== Means users that wins under 70 mb get a cheat_value of 0 -> win every time
$cheat_value = 8; //== Higher value -> less winner
$cheat_breakpoint = 10; //== Very important value -> if (win MB > max_download_global/cheat_breakpoint)
$cheat_value_max = 2; //== Then cheat_value = cheat_value_max -->> i hope you know what i mean. ps: must be higher as cheat_value.
$cheat_ratio_user = .4; //== If casino_ratio_user > cheat_ratio_user -> $cheat_value = random_int($cheat_value,$cheat_value_max)
$cheat_ratio_global = .4; //== Same as user just global
$win_amount = 3; //== How much do the player win in the first game eg. bet 300, win_amount=3 ---->>> 300*3= 900 win
$win_amount_on_number = 6; //== Same as win_amount for the number game
$show_real_chance = false; //== Shows the user the real chance true or false
$bet_value1 = 1024 * 1024 * 200; //== This is in MB but you can also choose gb or tb
$bet_value2 = 1024 * 1024 * 500;
$bet_value3 = 1024 * 1024 * 1020;
$bet_value4 = 1024 * 1024 * 2560;
$bet_value5 = 1024 * 1024 * 5120;
$bet_value6 = 1024 * 1024 * 10240;
$bet_value7 = 1024 * 1024 * 20480;
$bet_value8 = 1024 * 1024 * 51200;
$maxusrbet = 5; //==Amount of bets to allow per person
$maxtotbet = 30; //== Amount of total open bets allowed
$alwdebt = 0; //== Allow users to get into debt
$writelog = 1; //== Writes results to log
$delold = 1; //== Clear bets once finished
$sendfrom = 2; //== The id of the user which notification PM's are noted as sent from
$casino = 'casino.php'; //== Name of file
//== End of Config
if ($CURUSER['game_access'] == 0 || $CURUSER['game_access'] > 1 || $CURUSER['suspended'] == 'yes') {
    stderr($lang['gl_error'], $lang['casino_your_gaming_rights_have_been_disabled']);
    die();
}

$min_text = mksize(100 * 1073741824);
if ($CURUSER['uploaded'] < 1073741824 * 100) {
    stderr('Sorry,', "You must have at least {$min_text} upload credit to play.");
}

$sql = sql_query('SELECT uploaded, downloaded ' . 'FROM users ' . 'WHERE id = ' . sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);
$User = mysqli_fetch_assoc($sql);
$User['uploaded'] = (int)$User['uploaded'];
$User['downloaded'] = (int)$User['downloaded'];

//== Reset user gamble stats!
$hours = 2; //== Hours to wait after using all tries, until they will be restarted
$dt = TIME_NOW - $hours * 3600;
$res = sql_query("SELECT userid, trys, date, enableplay FROM casino WHERE date < $dt AND trys >= '51' AND enableplay = 'yes'") or sqlerr(__FILE__, __LINE__);
while ($arr = mysqli_fetch_assoc($res)) {
    sql_query("UPDATE casino SET trys='0' WHERE userid=" . sqlesc($arr['userid'])) or sqlerr(__FILE__, __LINE__);
}
$query = 'SELECT * FROM casino WHERE userid = ' . sqlesc($CURUSER['id']);
$result = sql_query($query) or sqlerr(__FILE__, __LINE__);
if (mysqli_affected_rows($GLOBALS['___mysqli_ston']) != 1) {
    sql_query('INSERT INTO casino (userid, win, lost, trys, date, started) VALUES(' . sqlesc($CURUSER['id']) . ', 0, 0, 0,' . TIME_NOW . ',1)') or ((is_object($GLOBALS['___mysqli_ston'])) ? mysqli_error($GLOBALS['___mysqli_ston']) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false));
    $result = sql_query($query) or sqlerr(__FILE__, __LINE__);
}
// Make sure Current User is in the table
sql_query('INSERT IGNORE INTO casino (userid, date) VALUES (' . sqlesc($CURUSER['id']) . ', UNIX_TIMESTAMP(NOW()))') or sqlerr(__FILE__, __LINE__);

$row = mysqli_fetch_assoc($result);
$user_win = $row['win'];
$user_lost = $row['lost'];
$user_trys = (int)$row['trys'];
$user_date = (int)$row['date'];
$user_deposit = $row['deposit'];
$user_enableplay = htmlsafechars($row['enableplay']);
if ($user_enableplay == 'no') {
    stderr($lang['gl_sorry'], '' . htmlsafechars($CURUSER['username']) . " {$lang['casino_your_banned_from_casino']}");
}
if (($user_win - $user_lost) > $max_download_user) {
    stderr($lang['gl_sorry'], '' . htmlsafechars($CURUSER['username']) . " {$lang['casino_you_have_reached_the_max_dl_for_a_single_user']}");
}
if ($CURUSER['downloaded'] > 0) {
    $ratio = number_format($CURUSER['uploaded'] / $CURUSER['downloaded'], 2);
} elseif ($CURUSER['uploaded'] > 0) {
    $ratio = 999;
} else {
    $ratio = 0;
}
if ($site_config['ratio_free'] === false && $ratio < $required_ratio) {
    stderr($lang['gl_sorry'], '' . htmlsafechars($CURUSER['username']) . " {$lang['casino_your_ratio_is_under']} {$required_ratio}");
}
$global_down2 = sql_query('SELECT (sum(win)-sum(lost)) AS globaldown,(sum(deposit)) AS globaldeposit, sum(win) AS win, sum(lost) AS lost FROM casino') or sqlerr(__FILE__, __LINE__);
$row = mysqli_fetch_assoc($global_down2);
$global_down = $row['globaldown'];
$global_win = $row['win'];
$global_lost = $row['lost'];
$global_deposit = $row['globaldeposit'];
if ($user_win > 0) {
    $casino_ratio_user = number_format($user_lost / $user_win, 2);
} elseif ($user_lost > 0) {
    $casino_ratio_user = 999;
} else {
    $casino_ratio_user = 0.00;
}
if ($global_win > 0) {
    $casino_ratio_global = number_format($global_lost / $global_win, 2);
} elseif ($global_lost > 0) {
    $casino_ratio_global = 999;
} else {
    $casino_ratio_global = 0.00;
}
if ($user_win < $user_everytimewin_mb) {
    $cheat_value = 8;
} else {
    if ($global_down > ($max_download_global / $cheat_breakpoint)) {
        $cheat_value = $cheat_value_max;
    }
    if ($casino_ratio_global < $cheat_ratio_global) {
        $cheat_value = random_int($cheat_value, $cheat_value_max);
    }
    if (($user_win - $user_lost) > ($max_download_user / $cheat_breakpoint)) {
        $cheat_value = $cheat_value_max;
    }
    if ($casino_ratio_user < $cheat_ratio_user) {
        $cheat_value = random_int($cheat_value, $cheat_value_max);
    }
}
if ($global_down > $max_download_global) {
    stderr($lang['gl_sorry'], '' . htmlsafechars($CURUSER['username']) . " {$lang['casino_but_global_max_win_is_above']} " . htmlsafechars(mksize($max_download_global)));
}
//== Updated post color/number by pdq
$goback = "<a href='$casino'>{$lang['casino_go_back']}</a>";
$color_options = [
    'red'   => 1,
    'black' => 2,
];
$number_options = [
    1 => 1,
    2 => 1,
    3 => 1,
    4 => 1,
    5 => 1,
    6 => 1,
];
$betmb_options = [
    $bet_value1 => 1,
    $bet_value2 => 1,
    $bet_value3 => 1,
    $bet_value4 => 1,
    $bet_value5 => 1,
    $bet_value6 => 1,
    $bet_value7 => 1,
    $bet_value8 => 1,
];
$post_color = (isset($_POST['color']) ? $_POST['color'] : '');
$post_number = (isset($_POST['number']) ? $_POST['number'] : '');
$post_betmb = (isset($_POST['betmb']) ? $_POST['betmb'] : '');
if (isset($color_options[ $post_color ]) && isset($number_options[ $post_number ]) || isset($betmb_options[ $post_betmb ])) {
    $betmb = $_POST['betmb'];
    if (isset($_POST['number'])) {
        $win_amount = $win_amount_on_number;
        $cheat_value = $cheat_value + 5;
        $winner_was = (int)$_POST['number'];
    } else {
        $winner_was = htmlsafechars($_POST['color']);
    }
    $win = $win_amount * $betmb;
    if ($CURUSER['uploaded'] < $betmb) {
        stderr($lang['gl_sorry'], '' . htmlsafechars($CURUSER['username']) . " {$lang['casino_but_you_have_not_uploaded']} " . htmlsafechars(mksize($betmb)));
    }
    if (random_int(0, $cheat_value) == $cheat_value) {
        sql_query('UPDATE users SET uploaded = uploaded + ' . sqlesc($win) . ' WHERE id=' . sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);
        sql_query("UPDATE casino SET date = '" . TIME_NOW . "', trys = trys + 1, win = win + " . sqlesc($win) . '  WHERE userid=' . sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);
        $update['uploaded'] = ($User['uploaded'] + $win);
        //==stats
        $cache->update_row('userstats_' . $CURUSER['id'], [
            'uploaded' => $update['uploaded'],
        ], $site_config['expires']['u_stats']);
        $cache->update_row('user_stats_' . $CURUSER['id'], [
            'uploaded' => $update['uploaded'],
        ], $site_config['expires']['user_stats']);
        stderr($lang['casino_yes'], '' . htmlsafechars($winner_was) . " {$lang['casino_is_the_result']} " . htmlsafechars($CURUSER['username']) . " {$lang['casino_you_got_it_and_win']} " . htmlsafechars(mksize($win)) . "&#160;&#160;&#160;$goback");
    } else {
        if (isset($_POST['number'])) {
            do {
                $fake_winner = random_int(1, 6);
            } while ($_POST['number'] == $fake_winner);
        } else {
            if ($_POST['color'] == 'black') {
                $fake_winner = 'red';
            } else {
                $fake_winner = 'black';
            }
        }
        sql_query('UPDATE users SET uploaded = uploaded - ' . sqlesc($betmb) . ' WHERE id=' . sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);
        sql_query('UPDATE casino SET date = ' . TIME_NOW . ', trys = trys + 1 ,lost = lost + ' . sqlesc($betmb) . ' WHERE userid=' . sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);
        $update['uploaded_loser'] = ($User['uploaded'] - $betmb);
        //==stats
        $cache->update_row('userstats_' . $CURUSER['id'], [
            'uploaded' => $update['uploaded_loser'],
        ], $site_config['expires']['u_stats']);
        $cache->update_row('user_stats_' . $CURUSER['id'], [
            'uploaded' => $update['uploaded_loser'],
        ], $site_config['expires']['user_stats']);
        stderr($lang['gl_sorry'], '' . htmlsafechars($fake_winner) . " {$lang['casino_is_the_winner_and_not']} " . htmlsafechars($winner_was) . ', ' . htmlsafechars($CURUSER['username']) . " {$lang['casino_you_lost']} " . htmlsafechars(mksize($betmb)) . "&#160;&#160;&#160;$goback");
    }
} else {
    //== Get user stats
    $betsp = sql_query('SELECT challenged FROM casino_bets WHERE proposed =' . sqlesc($CURUSER['username'])) or sqlerr(__FILE__, __LINE__);
    $openbet = 0;
    while ($tbet2 = mysqli_fetch_assoc($betsp)) {
        if ($tbet2['challenged'] == 'empty') {
            ++$openbet;
        }
    }
    //== Convert bet amount into bits
    if (isset($_POST['unit'])) {
        if ((int)$_POST['unit'] == 1) {
            $nobits = $amnt * $mb_basic;
        } else {
            $nobits = $amnt * $mb_basic * 1024;
        }
    }
    if ($CURUSER['uploaded'] == 0 || $CURUSER['downloaded'] == 0) {
        $ratio = '0';
    } else {
        $ratio = number_format(($CURUSER['uploaded'] - $nobits) / $CURUSER['downloaded'], 2);
    }
    $time = TIME_NOW;
    //== Take Bet
    if (isset($_GET['takebet'])) {
        $betid = (int)$_GET['takebet'];
        $rand = 0;
        for ($x = 1; $x <= 100000; ++$x) {
            $random = (random_int(1, 10000));
            if ($random > 5000) {
                ++$rand;
            }
        }
        $loc = sql_query('SELECT * FROM casino_bets WHERE id = ' . sqlesc($betid)) or sqlerr(__FILE__, __LINE__);
        $tbet = mysqli_fetch_assoc($loc);
        $nogb = mksize($tbet['amount']);
        if ($CURUSER['id'] == $tbet['userid']) {
            stderr($lang['gl_sorry'], "{$lang['casino_you_want_to_bet_against_yourself_lol']} ?&#160;&#160;&#160;$goback");
        } elseif ($tbet['challenged'] != 'empty') {
            stderr($lang['gl_sorry'], "{$lang['casino_someone_has_already_taken_that_bet']}!&#160;&#160;&#160;$goback");
        }
        if ($CURUSER['uploaded'] < $tbet['amount']) {
            $debt = $tbet['amount'] - $CURUSER['uploaded'];
            $newup = $CURUSER['uploaded'] - $debt;
        }
        if (isset($debt) && $alwdebt != 1) {
            stderr($lang['gl_sorry'], "<h2>{$lang['casino_you_are']} " . htmlsafechars(mksize(($nobits - $CURUSER['uploaded']))) . " {$lang['casino_short_of_making_that_bet']}!</h2>&#160;&#160;&#160;$goback");
        }
        if ($rand > 50000) {
            // Current user WINS
            sql_query('UPDATE users SET uploaded = uploaded + ' . sqlesc($tbet['amount']) . ' WHERE id = ' . sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);
            sql_query('UPDATE casino SET deposit = deposit - ' . sqlesc($tbet['amount']) . ', trys = trys + 1, lost = lost + ' . sqlesc($tbet['amount']) . ' WHERE userid = ' . sqlesc($tbet['userid'])) or sqlerr(__FILE__, __LINE__);
            sql_query('UPDATE casino SET win = win + ' . sqlesc($tbet['amount']) . ', trys = trys + 1 WHERE userid = ' . sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);
            $update['uploaded'] = ($User['uploaded'] + $tbet['amount']);
            //==stats
            $cache->update_row('userstats_' . $CURUSER['id'], [
                'uploaded' => $update['uploaded'],
            ], $site_config['expires']['u_stats']);
            $cache->update_row('user_stats_' . $CURUSER['id'], [
                'uploaded' => $update['uploaded'],
            ], $site_config['expires']['user_stats']);
            if (mysqli_affected_rows($GLOBALS['___mysqli_ston']) == 0) {
                sql_query('INSERT INTO casino (userid, date, deposit) VALUES (' . sqlesc($tbet['userid']) . ", $time, -" . sqlesc($tbet['amount']) . ')') or sqlerr(__FILE__, __LINE__);
            }
            sql_query('UPDATE casino_bets SET challenged = ' . sqlesc($CURUSER['username']) . ', winner = ' . sqlesc($CURUSER['username']) . ' WHERE id =' . sqlesc($betid)) or sqlerr(__FILE__, __LINE__);
            $subject = sqlesc($lang['casino_casino_results']);
            $msg = sqlesc('You lost a bet! ' . htmlsafechars($CURUSER['username']) . ' just won ' . htmlsafechars($nogb) . ' of your upload credit!');
            sql_query("INSERT INTO messages (subject, sender, receiver, added, msg, unread, poster) VALUES ($subject, $sendfrom, " . sqlesc($tbet['userid']) . ", $time, $msg, 'yes', $sendfrom)") or sqlerr(__FILE__, __LINE__);
            $cache->increment('inbox_' . $tbet['userid']);
            if ($writelog == 1) {
                write_log($CURUSER['username'] . " won $nogb {$lang['casino_of_upload_credit_off']} " . htmlsafechars($tbet['proposed']));
            }
            if ($delold == 1) {
                sql_query('DELETE FROM casino_bets WHERE id =' . sqlesc($tbet['id'])) or sqlerr(__FILE__, __LINE__);
            }
            stderr($lang['casino_you_got_it'], "<h2>{$lang['casino_you_won_the_bet']}, " . htmlsafechars($nogb) . " {$lang['casino_has_been_credited_to_your_account']}, at <a href='userdetails.php?id=" . (int)$tbet['userid'] . "'>" . htmlsafechars($tbet['proposed']) . "'s</a> {$lang['casino_expense']}!</h2>&#160;&#160;&#160;$goback");
            die();
        } else {
            if (empty($newup)) {
                $newup = $User['uploaded'] - $tbet['amount'];
            }
            $newup2 = $tbet['amount'] * 2;
            // Current User Loses
            sql_query('UPDATE users SET uploaded = ' . sqlesc($newup) . ' WHERE id =' . sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);
            sql_query('UPDATE users SET uploaded = uploaded + ' . sqlesc($newup2) . ' WHERE id = ' . sqlesc($tbet['userid'])) or sqlerr(__FILE__, __LINE__);
            sql_query('UPDATE casino SET lost = lost + ' . sqlesc($tbet['amount']) . ', trys = trys + 1 WHERE userid = ' . sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);
            sql_query('UPDATE casino SET deposit = deposit - ' . sqlesc($tbet['amount']) . ', trys = trys + 1, win = win + ' . sqlesc($tbet['amount']) . ' WHERE userid = ' . sqlesc($tbet['userid'])) or sqlerr(__FILE__, __LINE__);
            $update['uploaded'] = ($newup);
            //==stats
            $cache->update_row('userstats_' . $CURUSER['id'], [
                'uploaded' => $update['uploaded'],
            ], $site_config['expires']['u_stats']);

            $cache->update_row('user_stats_' . $CURUSER['id'], [
                'uploaded' => $update['uploaded'],
            ], $site_config['expires']['user_stats']);

            // get bet owner uploaded
            $sql = sql_query('SELECT uploaded FROM users WHERE id = ' . sqlesc($tbet['userid'])) or sqlerr(__FILE__, __LINE__);
            $User2 = mysqli_fetch_assoc($sql);
            $User2['uploaded'] = (int)$User2['uploaded'];
            $update['uploaded_2'] = (int)($User2['uploaded'] + $newup2);
            //$update['uploaded_2'] = (int)($User['uploaded'] + $newup2);
            //==stats
            $cache->update_row('userstats_' . $tbet['userid'], [
                'uploaded' => $update['uploaded_2'],
            ], $site_config['expires']['u_stats']);
            $cache->update_row('user_stats_' . $tbet['userid'], [
                'uploaded' => $update['uploaded_2'],
            ], $site_config['expires']['user_stats']);

            if (mysqli_affected_rows($GLOBALS['___mysqli_ston']) == 0) {
                sql_query('INSERT INTO casino (userid, date, deposit) VALUES (' . sqlesc($tbet['userid']) . ", $time, -" . sqlesc($tbet['amount']) . ')') or sqlerr(__FILE__, __LINE__);
            }
            sql_query('UPDATE casino_bets SET challenged = ' . sqlesc($CURUSER['username']) . ', winner = ' . sqlesc($tbet['proposed']) . ' WHERE id = ' . sqlesc($betid)) or sqlerr(__FILE__, __LINE__);
            $subject = sqlesc($lang['casino_casino_results']);
            $msg = sqlesc("{$lang['casino_you_just_won']} " . htmlsafechars($nogb) . " {$lang['casino_of_upload_credit_from']} " . $CURUSER['username'] . '!');
            sql_query("INSERT INTO messages (subject, sender, receiver, added, msg, unread, poster) VALUES ($subject, $sendfrom, " . sqlesc($tbet['userid']) . ", $time, $msg, 'yes', $sendfrom)") or sqlerr(__FILE__, __LINE__);
            $cache->increment('inbox_' . $tbet['userid']);
            if ($writelog == 1) {
                write_log('' . htmlsafechars($tbet['proposed']) . " won $nogb {$lang['casino_of_upload_credit_off']} " . $CURUSER['username']);
            }
            if ($delold == 1) {
                sql_query('DELETE FROM casino_bets WHERE id =' . sqlesc($tbet['id'])) or sqlerr(__FILE__, __LINE__);
            }
            stderr($lang['casino_damn_it'], "<h2>{$lang['casino_you_lost_the_bet']} <a href='userdetails.php?id=" . (int)$tbet['userid'] . "'>" . htmlsafechars($tbet['proposed']) . "</a> {$lang['casino_has_won']} " . htmlsafechars($nogb) . " {$lang['casino_of_your_hard_earnt_upload_credit']}!</h2> &#160;&#160;&#160;$goback");
        }
        die();
    }
    //== Add a new bet
    $loca = sql_query("SELECT * FROM casino_bets WHERE challenged ='empty'") or sqlerr(__FILE__, __LINE__);
    $totbets = mysqli_num_rows($loca);
    if (isset($_POST['unit'])) {
        if ((int)$_POST['unit'] == '1') {
            $nobits = (int)$_POST['amnt'] * $mb_basic;
        } else {
            $nobits = (int)$_POST['amnt'] * $mb_basic * 1024;
        }
    }
    if (isset($_POST['unit'])) {
        if ($openbet >= $maxusrbet) {
            stderr($lang['gl_sorry'], "{$lang['casino_there_are_already']} " . htmlsafechars($openbet) . " {$lang['casino_bets_open_take_an_open_bet']}!&#160;&#160;&#160;$goback");
        }
        if ($nobits <= 0) {
            stderr($lang['gl_sorry'], " {$lang['casino_this_wont_work_enter_a_pos_val']}?&#160;&#160;&#160;$goback");
        }
        if ($nobits == '.') {
            stderr($lang['gl_sorry'], " {$lang['casino_this_wont_work_enter_without_a_dec']}?&#160;&#160;&#160;$goback");
        }
        if ($maxbet < $nobits) {
            stderr($lang['gl_sorry'], '' . htmlsafechars($CURUSER['username']) . " The Max allowed bet is $maxbetGB GB!&#160;&#160;&#160;$goback");
        }
        if ($nobits <= 104857599) {
            stderr($lang['gl_sorry'], '' . htmlsafechars($CURUSER['username']) . " The Min allowed bet is 100 MB!&#160;&#160;&#160;$goback");
        }

        $newups = $CURUSER['uploaded'] - $nobits;
        $debt = $nobits - $CURUSER['uploaded'];
        if ($CURUSER['uploaded'] < $nobits) {
            if ($alwdebt != 1) {
                stderr($lang['gl_sorry'], "<h2>{$lang['casino_thats']} " . htmlsafechars(mksize($debt)) . " {$lang['casino_more_than_you_got']}!</h2>$goback");
            }
        }
        $betsp = sql_query('SELECT id, amount FROM casino_bets WHERE userid = ' . sqlesc($CURUSER['id']) . ' ORDER BY time ASC') or sqlerr(__FILE__, __LINE__);
        $tbet2 = mysqli_fetch_row($betsp);
        $dummy = "<h2 class='has-text-centered'>{$lang['casino_bet_added_you_will_receive_a_pm_notify']}</h2>";
        $user = $CURUSER['username'];
        $bet = mksize($nobits);
        $classColor = get_user_class_color($CURUSER['class']);
        $message = "[color=#$classColor][b]{$user}[/b][/color] {$lang['casino_has_just_placed_a']} [color=red][b]{$bet}[/b][/color] {$lang['casino_bet_in_the_casino']}";
        $messages = "{$user} {$lang['casino_has_just_placed_a']} {$bet} {$lang['casino_bet_in_the_casino']}";
        sql_query('INSERT INTO casino_bets ( userid, proposed, challenged, amount, time) VALUES (' . sqlesc($CURUSER['id']) . ',' . sqlesc($CURUSER['username']) . ", 'empty', $nobits, $time)") or sqlerr(__FILE__, __LINE__);
        sql_query('UPDATE users SET uploaded = ' . sqlesc($newups) . ' WHERE id = ' . sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);
        sql_query('UPDATE casino SET deposit = deposit + ' . sqlesc($nobits) . ' WHERE userid = ' . sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);

        $update['uploaded'] = ($newups);
        //==stats
        $cache->update_row('userstats_' . $CURUSER['id'], [
            'uploaded' => $update['uploaded'],
        ], $site_config['expires']['u_stats']);
        $cache->update_row('user_stats_' . $CURUSER['id'], [
            'uploaded' => $update['uploaded'],
        ], $site_config['expires']['user_stats']);
        if ($site_config['autoshout_on'] == 1 || $site_config['irc_autoshout_on'] == 1) {
            autoshout($message);
            //ircbot($messages);
        }
        if (mysqli_affected_rows($GLOBALS['___mysqli_ston']) == 0) {
            sql_query('INSERT INTO casino (userid, date, deposit) VALUES (' . sqlesc($CURUSER['id']) . ", $time, " . sqlesc($nobits) . ')') or sqlerr(__FILE__, __LINE__);
        }
    }
    $loca = sql_query("SELECT * FROM casino_bets WHERE challenged ='empty'") or sqlerr(__FILE__, __LINE__);
    $totbets = mysqli_num_rows($loca);
    $HTMLOUT .= $dummy;
    //== Place bet table
    if ($openbet < $maxusrbet) {
        if ($totbets >= $maxtotbet) {
            $HTMLOUT .= "{$lang['casino_there_are_already']} " . htmlsafechars($maxtotbet) . " {$lang['casino_bets_open_take_an_open_bet']}!";
        } else {
            $HTMLOUT .= "
            <form name='p2p' method='post' action='{$casino}'>
                <h1 class='has-text-centered'>{$site_config['site_name']} {$lang['casino_stdhead']} - {$lang['casino_bet_p2p_with_other_users']}:</h1>
                <table class='table table-bordered table-striped top20 bottom20'>
                    <thead>
                        <tr>
                            <td class='has-text-centered' colspan='2'>{$lang['casino_place_bet']}</td>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class='has-text-centered'><b>{$lang['casino_amount_to_bet']}</b>
                                <input type='text' name='amnt' size='5' value='1' />
                                <select name='unit'>
                                    <option value='1'>MB</option>
                                    <option value='2'>GB</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td class='has-text-centered' colspan='2'>
                                <input type='submit' class='button is-small' value='{$lang['casino_gamble']}!' />
                            </td>
                        </tr>
                    </tbody>
                </table>
            </form>";
        }
    } else {
        $HTMLOUT .= "<b>{$lang['casino_you_already_have']} " . htmlsafechars($maxusrbet) . " {$lang['casino_open_bets_wait_until_they_are_comp']}.</b><br><br>";
    }
    //== Open Bets table
    $maxbetShow = mksize($maxbet);
    $HTMLOUT .= "
                <table class='table table-bordered table-striped top20 bottom20'>
                    <thead>
                        <tr>
                            <th class='has-text-centered' colspan='4'>{$lang['casino_open_bets']} - Max Bet {$maxbetShow} - Limit {$maxusrbet} Active Bets</th>
                        </tr>
                        <tr>
                            <td class='has-text-centered'><b>{$lang['casino_name']}</b></td>
                            <td class='has-text-centered'><b>{$lang['casino_amount']}</b></td>
                            <td class='has-text-centered'><b>{$lang['casino_time']}</b></td>
                            <td class='has-text-centered'><b>{$lang['casino_take_bet']}</b></td>
                        </tr>
                    </thead>
                    <tbody>";
    while ($res = mysqli_fetch_assoc($loca)) {
        $HTMLOUT .= "
                        <tr>
                            <td class='has-text-centered'>" . format_username($res['userid']) . "</td>
                            <td class='has-text-centered'>" . htmlsafechars(mksize($res['amount'])) . "</td>
                            <td class='has-text-centered'>" . get_date($res['time'], 'LONG', 0, 1) . "</td>
                            <td class='has-text-centered'>
                                <b><a href='{$casino}?takebet=" . (int)$res['id'] . "'>{$lang['casino_take_bet']}</a></b>
                            </td>
                        </tr>";
        $abcdefgh = 1;
    }
    if ($abcdefgh == false) {
        $HTMLOUT .= "
                        <tr>
                            <td class='has-text-centered' colspan='4'>{$lang['casino_sorry_no_bets_currently']}.</td>
                        </tr>";
    }
    $HTMLOUT .= "
                    </tbody>
                </table>";
    //== Bet on color table
    $HTMLOUT .= "
            <form name='casino' method='post' action='{$casino}'>
                <table class='table table-bordered table-striped top20 bottom20'>
                    <thead>
                        <tr>
                            <td class='has-text-centered' colspan='2'>{$lang['casino_bet_on_a_colour']}</td>
                        </tr>
                    </thead>
                    <tbody>";
    $HTMLOUT .= tr($lang['casino_black'], '<input name="color" type="radio" checked value="black" />', 1);
    $HTMLOUT .= tr($lang['casino_red'], '<input name="color" type="radio" checked value="red" />', 1);
    $HTMLOUT .= tr($lang['casino_how_much'], "
                            <select name='betmb'>
                                <option value='{$bet_value1}'>" . mksize($bet_value1) . "</option>
                                <option value='{$bet_value2}'>" . mksize($bet_value2) . "</option>
                                <option value='{$bet_value3}'>" . mksize($bet_value3) . "</option>
                                <option value='{$bet_value4}'>" . mksize($bet_value4) . "</option>
                                <option value='{$bet_value5}'>" . mksize($bet_value5) . "</option>
                                <option value='{$bet_value6}'>" . mksize($bet_value6) . "</option>
                                <option value='{$bet_value7}'>" . mksize($bet_value7) . "</option>
                                <option value='{$bet_value8}'>" . mksize($bet_value8) . '</option>
                            </select>', 1);
    $real_chance = 2;
    if ($show_real_chance) {
        $real_chance = $cheat_value + 1;
    }
    $HTMLOUT .= tr($lang['casino_your_chance'], '1 : ' . $real_chance, 1);
    $HTMLOUT .= tr($lang['casino_you_can_win'], $win_amount . ' * stake', 1);
    $HTMLOUT .= tr($lang['casino_bet_on_color'], "<input type='submit' class='button is-small' value='{$lang['casino_do_it']}!' />", 1);
    $HTMLOUT .= "
                    </tbody>
                </table>
            </form>";
    //== Bet on number table
    $HTMLOUT .= "
            <form name='casino' method='post' action='{$casino}'>
                <table class='table table-bordered table-striped top20 bottom20'>
                    <thead>
                        <tr>
                            <td class='has-text-centered' class='colhead' colspan='2'>{$lang['casino_bet_on_a_number']}</td>
                        </tr>
                    </thead>
                    <tbody>";
    $HTMLOUT .= tr($lang['casino_number'], '<input name="number" type="radio" checked value="1" />1&#160;&#160;<input name="number" type="radio" value="2" />2&#160;&#160;<input name="number" type="radio" value="3" />3', 1);
    $HTMLOUT .= tr('', '<input name="number" type="radio" value="4" />4&#160;&#160;<input name="number" type="radio" value="5" />5&#160;&#160;<input name="number" type="radio" value="6" />6', 1);
    $HTMLOUT .= tr($lang['casino_how_much'], "
                                <select name='betmb'>
                                    <option value='{$bet_value1}'>" . mksize($bet_value1) . "</option>
                                    <option value='{$bet_value2}'>" . mksize($bet_value2) . "</option>
                                    <option value='{$bet_value3}'>" . mksize($bet_value3) . "</option>
                                    <option value='{$bet_value4}'>" . mksize($bet_value4) . "</option>
                                    <option value='{$bet_value5}'>" . mksize($bet_value5) . "</option>
                                    <option value='{$bet_value6}'>" . mksize($bet_value6) . "</option>
                                    <option value='{$bet_value7}'>" . mksize($bet_value7) . "</option>
                                    <option value='{$bet_value8}'>" . mksize($bet_value8) . '</option>
                                </select>', 1);
    $real_chance = 6;
    if ($show_real_chance) {
        $real_chance = $cheat_value + 5;
    }
    $HTMLOUT .= tr($lang['casino_your_chance'], '1 : ' . $real_chance, 1);
    $HTMLOUT .= tr($lang['casino_you_can_win'], $win_amount_on_number . ' * stake', 1);
    $HTMLOUT .= tr($lang['casino_bet_on_a_number'], "<input type='submit' class='button is-small' value='{$lang['casino_do_it']}!' />", 1);
    $HTMLOUT .= "
                    </tbody>
                </table>
            </form>";
    //== User stats table
    $HTMLOUT .= "
            <div class='bordered top20 bottom20'>
                <div class='has-text-centered top20'>
                    <span class='size_7'>{$CURUSER['username']}'s {$lang['casino_details']}</span>
                </div>
                <div class='level-center flex-top'>
                    <div class='has-text-centered w-25 top20'>
                        <span class='size_6'>User @ {$site_config['site_name']} {$lang['casino_stdhead']}</span>
                        <table class='table table-bordered table-striped'>";
    $HTMLOUT .= tr($lang['casino_you_can_win'], mksize($max_download_user), 1);
    $HTMLOUT .= tr($lang['casino_won'], mksize($user_win), 1);
    $HTMLOUT .= tr($lang['casino_lost'], mksize($user_lost), 1);
    $HTMLOUT .= tr($lang['casino_ratio'], $casino_ratio_user, 1);
    $HTMLOUT .= tr($lang['casino_deposit_on_p2p'], mksize($user_deposit + $nobits));
    $HTMLOUT .= "
                        </table>
                    </div>
                    <div class='has-text-centered w-25 top20'>
                        <span class='size_6'>{$lang['casino_global_stats']}</span>
                        <table class='table table-bordered table-striped'>";
    $HTMLOUT .= tr($lang['casino_users_can_win'], mksize($max_download_global), 1);
    $HTMLOUT .= tr($lang['casino_won'], mksize($global_win), 1);
    $HTMLOUT .= tr($lang['casino_lost'], mksize($global_lost), 1);
    $HTMLOUT .= tr($lang['casino_ratio'], $casino_ratio_global, 1);
    $HTMLOUT .= tr($lang['casino_deposit'], mksize($global_deposit));
    $HTMLOUT .= "
                        </table>
                    </div>
                    <div class='has-text-centered w-25 top20'>
                        <span class='size_6'>{$lang['casino_user_stats']}</span>
                        <table class='table table-bordered table-striped'>";
    $HTMLOUT .= tr($lang['casino_uploaded'], mksize($User['uploaded'] - $nobits));
    $HTMLOUT .= tr($lang['casino_downloaded'], mksize($User['downloaded']));
    $HTMLOUT .= tr($lang['casino_ratio'], $ratio);
    $HTMLOUT .= "
                        </table>
                    </div>
                </div>
            </div>";
}
echo stdhead("{$lang['casino_stdhead']}") . wrapper($HTMLOUT) . stdfoot();
