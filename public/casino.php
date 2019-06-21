<?php

declare(strict_types = 1);

use Delight\Auth\Auth;
use Envms\FluentPDO\Literal;
use Pu239\Cache;
use Pu239\Casino;
use Pu239\CasinoBets;
use Pu239\Database;
use Pu239\Message;
use Pu239\Session;
use Pu239\User;

require_once __DIR__ . '/../include/bittorrent.php';
require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_html.php';
check_user_status();
$lang = array_merge(load_language('global'), load_language('casino'));
global $container, $site_config;

//== Config
$amnt = $nobits = $abcdefgh = 0;
$maxbetGB = 50;
$maxbet = $maxbetGB * 1024 * 1024 * 1024;
$mb_basic = 1024 * 1024;
$max_download_user = $mb_basic * 1024 * 1024 * 1024; //= 255 Gb
$max_download_global = $mb_basic * $mb_basic * 1024; //== 10.0 Tb
$required_ratio = 1.0; //== Min ratio
$user_everytimewin_mb = $mb_basic * 20; //== Means users that wins under 70 mb get a cheat_value of 0 -> win every time
$cheat_value = 8; //== Higher value -> less winner
$cheat_breakpoint = 10; //== Very important value -> if (win MB>max_download_global/cheat_breakpoint)
$cheat_value_max = 2; //== Then cheat_value = cheat_value_max -->> i hope you know what i mean. ps: must be higher as cheat_value.
$cheat_ratio_user = .4; //== If casino_ratio_user>cheat_ratio_user -> $cheat_value = random_int($cheat_value,$cheat_value_max)
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
//== End of Config

$fluent = $container->get(Database::class);
$users_class = $container->get(User::class);
$auth = $container->get(Auth::class);
$user = $users_class->getUserFromId($auth->getUserId());
$casino = $container->get(Casino::class);
$casino_bets = $container->get(CasinoBets::class);
$session = $container->get(Session::class);
if (empty($user)) {
    stderr($lang['gl_error'], 'Invalid User Data', 'bottom20');
    die();
}
if ($user['class'] < $site_config['allowed']['play']) {
    stderr('Error!', 'Sorry, you must be a ' . [$site_config['allowed']['play']] . ' to play in the casino!', 'bottom20');
}
if ($user['game_access'] == 0 || $user['game_access'] > 1 || $user['suspended'] === 'yes') {
    stderr($lang['gl_error'], $lang['casino_your_gaming_rights_have_been_disabled'], 'bottom20');
    die();
}

$min_text = mksize(100 * 1073741824);
if ($user['uploaded'] < 1073741824 * 100) {
    stderr('Sorry,', "You must have at least {$min_text} upload credit to play.", 'bottom20');
}

$hours = 2;
$dt = TIME_NOW - $hours * 3600;
$casino->reset_trys($user['id']);
$row = $casino->get_user($user['id']);
if (empty($row)) {
    $row = $casino->add_user($user['id']);
}
$user_win = $row['win'];
$user_lost = $row['lost'];
$user_trys = $row['trys'];
$user_date = $row['date'];
$user_deposit = $row['deposit'];
$user_enableplay = $row['enableplay'];
unset($row);
if ($user_enableplay === 'no') {
    stderr($lang['gl_sorry'], htmlsafechars($user['username']) . " {$lang['casino_your_banned_from_casino']}", 'bottom20');
}
if (($user_win - $user_lost) > $max_download_user) {
    stderr($lang['gl_sorry'], '' . htmlsafechars($user['username']) . " {$lang['casino_you_have_reached_the_max_dl_for_a_single_user']}", 'bottom20');
}
if ($user['downloaded'] > 0) {
    $ratio = $user['uploaded'] / $user['downloaded'];
} elseif ($user['uploaded'] > 0) {
    $ratio = 999;
} else {
    $ratio = 0;
}
if (!$site_config['site']['ratio_free'] && $ratio < $required_ratio) {
    stderr($lang['gl_sorry'], '' . htmlsafechars($user['username']) . " {$lang['casino_your_ratio_is_under']} {$required_ratio}", 'bottom20');
}
$row = $casino->get_totals();
$global_down = $row['globaldown'];
$global_win = $row['win'];
$global_lost = $row['lost'];
$global_deposit = $row['globaldeposit'];
unset($row);
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
        $cheat_value = random_int(min($cheat_value, $cheat_value_max), max($cheat_value, $cheat_value_max));
    }
    if (($user_win - $user_lost) > ($max_download_user / $cheat_breakpoint)) {
        $cheat_value = $cheat_value_max;
    }
    if ($casino_ratio_user < $cheat_ratio_user) {
        $cheat_value = random_int(min($cheat_value, $cheat_value_max), max($cheat_value, $cheat_value_max));
    }
}
if ($global_down > $max_download_global) {
    stderr($lang['gl_sorry'], '' . htmlsafechars($user['username']) . " {$lang['casino_but_global_max_win_is_above']} " . htmlsafechars(mksize($max_download_global)), 'bottom20');
}

$goback = "<a href='{$_SERVER['PHP_SELF']}'>{$lang['casino_go_back']}</a>";
$color_options = [
    'red' => 1,
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
$post_color = isset($_POST['color']) ? $_POST['color'] : '';
$post_number = isset($_POST['number']) ? $_POST['number'] : '';
$post_betmb = isset($_POST['betmb']) ? $_POST['betmb'] : '';
$cache = $container->get(Cache::class);
$messages_class = $container->get(Message::class);
if (isset($color_options[$post_color], $number_options[$post_number]) || isset($betmb_options[$post_betmb])) {
    $betmb = $_POST['betmb'];
    if (isset($_POST['number'])) {
        $win_amount = $win_amount_on_number;
        $cheat_value = $cheat_value + 5;
        $winner_was = (int) $_POST['number'];
    } else {
        $winner_was = htmlsafechars($_POST['color']);
    }
    $win = $win_amount * $betmb;
    if ($user['uploaded'] < $betmb) {
        stderr($lang['gl_sorry'], '' . htmlsafechars($user['username']) . " {$lang['casino_but_you_have_not_uploaded']} " . htmlsafechars(mksize($betmb)), 'bottom20');
    }
    if (random_int(0, $cheat_value) == $cheat_value) {
        $update = [
            'uploaded' => $user['uploaded'] + $win,
        ];
        $users_class->update($update, $user['id']);
        $update = [
            'date' => TIME_NOW,
            'trys' => new Literal('trys + 1'),
            'win' => new Literal('win + ' . $win),
        ];
        $casino->update_user($update, $user['id']);
        stderr($lang['casino_yes'], '' . htmlsafechars($winner_was) . " {$lang['casino_is_the_result']} " . htmlsafechars($user['username']) . " {$lang['casino_you_got_it_and_win']} " . htmlsafechars(mksize($win)) . "&#160;&#160;&#160;$goback", 'bottom20');
    } else {
        if (isset($_POST['number'])) {
            do {
                $fake_winner = random_int(1, 6);
            } while ($_POST['number'] == $fake_winner);
        } else {
            if ($_POST['color'] === 'black') {
                $fake_winner = 'red';
            } else {
                $fake_winner = 'black';
            }
        }
        $update = [
            'uploaded' => $user['uploaded'] - $betmb,
        ];
        $users_class->update($update, $user['id']);
        $update = [
            'date' => TIME_NOW,
            'trys' => new Literal('trys + 1'),
            'lost' => new Literal('lost + ' . $betmb),
        ];
        $casino->update_user($update, $user['id']);
        stderr($lang['gl_sorry'], "$fake_winner {$lang['casino_is_the_winner_and_not']} $winner_was, " . htmlsafechars($user['username']) . " {$lang['casino_you_lost']} " . mksize($betmb) . "&#160;&#160;&#160;$goback", 'bottom20');
    }
} else {
    $openbet = $casino_bets->get_open_bets($user['username']);
    if (isset($_POST['unit'])) {
        if ((int) $_POST['unit'] === 1) {
            $nobits = $amnt * $mb_basic;
        } else {
            $nobits = $amnt * $mb_basic * 1024;
        }
    }
    if ($user['uploaded'] == 0 || $user['downloaded'] == 0) {
        $ratio = '0';
    } else {
        $ratio = number_format(($user['uploaded'] - $nobits) / $user['downloaded'], 2);
    }
    $dt = TIME_NOW;
    if (isset($_GET['takebet'])) {
        $betid = (int) $_GET['takebet'];
        $rand = 0;
        for ($x = 1; $x <= 100000; ++$x) {
            $random = (random_int(1, 10000));
            if ($random > 5000) {
                ++$rand;
            }
        }
        $tbet = $casino_bets->get_bet($betid);
        $nogb = mksize($tbet['amount']);
        if ($user['id'] == $tbet['userid']) {
            stderr($lang['gl_sorry'], "{$lang['casino_you_want_to_bet_against_yourself_lol']} ?&#160;&#160;&#160;$goback", 'bottom20');
        } elseif ($tbet['challenged'] != 'empty') {
            stderr($lang['gl_sorry'], "{$lang['casino_someone_has_already_taken_that_bet']}!&#160;&#160;&#160;$goback", 'bottom20');
        }
        if ($user['uploaded'] < $tbet['amount']) {
            $debt = $tbet['amount'] - $user['uploaded'];
            $newup = $user['uploaded'] - $debt;
        }
        if (isset($debt) && $alwdebt != 1) {
            stderr($lang['gl_sorry'], "<h2>{$lang['casino_you_are']} " . htmlsafechars(mksize(($nobits - $user['uploaded']))) . " {$lang['casino_short_of_making_that_bet']}!</h2>&#160;&#160;&#160;$goback", 'bottom20');
        }
        if ($rand > 50000) {
            $update = [
                'uploaded' => $user['uploaded'] + $tbet['amount'],
            ];
            $users_class->update($update, $user['id']);
            $update = [
                'deposit' => new Literal('deposit - ' . $tbet['amount']),
                'trys' => new Literal('trys + 1'),
                'lost' => new Literal('lost + ' . $tbet['amount']),
            ];
            $casino->update_user($update, $tbet['userid']);
            $update = [
                'win' => new Literal('win - ' . $tbet['amount']),
                'trys' => new Literal('trys + 1'),
            ];
            $casino->update_user($update, $user['id']);
            $update = [
                'challenged' => $user['username'],
                'winner' => $user['username'],
            ];
            $casino_bets->update($update, $betid);
            $subject = $lang['casino_casino_results'];
            $msg = 'You lost a bet! ' . htmlsafechars($user['username']) . ' just won ' . htmlsafechars($nogb) . ' of your upload credit!';
            $msgs_buffer[] = [
                'receiver' => $tbet['userid'],
                'added' => $dt,
                'msg' => $msg,
                'subject' => $subject,
            ];
            $messages_class->insert($msgs_buffer);
            if ($writelog == 1) {
                write_log($user['username'] . " won $nogb {$lang['casino_of_upload_credit_off']} " . htmlsafechars($tbet['proposed']));
            }
            if ($delold === 1) {
                $casino_bets->delete_bet($tbet['id']);
            }
            stderr($lang['casino_you_got_it'], "<h2>{$lang['casino_you_won_the_bet']}, " . htmlsafechars($nogb) . " {$lang['casino_has_been_credited_to_your_account']}, at " . format_username($tbet['userid']) . "</a> {$lang['casino_expense']}!</h2>&#160;&#160;&#160;$goback", 'bottom20');
            die();
        } else {
            if (empty($newup)) {
                $newup = $user['uploaded'] - $tbet['amount'];
            }
            $newup2 = $tbet['amount'] * 2;
            $update = [
                'uploaded' => $newup,
            ];
            $users_class->update($update, $user['id']);
            $update = [
                'uploaded' => $user['uploaded'] + $newup2,
            ];
            $users_class->update($update, $tbet['id']);

            $update = [
                'lost' => new Literal('lost + ' . $tbet['amount']),
                'trys' => new Literal('trys + 1'),
            ];
            $casino->update_user($update, $user['id']);
            $update = [
                'deposit' => new Literal('deposit - ' . $tbet['amount']),
                'win' => new Literal('win + ' . $tbet['amount']),
                'trys' => new Literal('trys + 1'),
            ];
            $casino->update_user($update, $tbet['userid']);
            $user2 = $users_class->getUserFromId($tbet['userid']);
            $update = [
                'uploaded' => $user2['uploaded'] + $newup2,
            ];
            $users_class->update($update, $user2['id']);
            $update['uploaded_2'] = $user2['uploaded'] + $newup2;
            $update = [
                'challenged' => $user['username'],
                'winner' => $tbet['proposed'],
            ];
            $casino_bets->update($update, $betid);
            $subject = $lang['casino_casino_results'];
            $msg = "{$lang['casino_you_just_won']} " . htmlsafechars($nogb) . " {$lang['casino_of_upload_credit_from']} " . $user['username'] . '!';

            $msgs_buffer[] = [
                'receiver' => $tbet['userid'],
                'added' => $dt,
                'msg' => $msg,
                'subject' => $subject,
            ];
            $messages_class->insert($msgs_buffer);

            if ($writelog == 1) {
                write_log('' . htmlsafechars($tbet['proposed']) . " won $nogb {$lang['casino_of_upload_credit_off']} " . $user['username']);
            }
            if ($delold === 1) {
                $casino_bets->delete_bet($tbet['id']);
            }
            stderr($lang['casino_damn_it'], "<h2>{$lang['casino_you_lost_the_bet']} " . format_username($tbet['userid']) . " {$lang['casino_has_won']} " . htmlsafechars($nogb) . " {$lang['casino_of_your_hard_earnt_upload_credit']}!</h2> &#160;&#160;&#160;$goback", 'bottom20');
        }
        die();
    }
    $empty_bets = $casino_bets->get_empty_bets();
    $totbets = !empty($empty_bets) ? count($empty_bets) : 0;
    if (isset($_POST['unit'])) {
        if ((int) $_POST['unit'] === 1) {
            $nobits = (int) $_POST['amnt'] * $mb_basic;
        } else {
            $nobits = (int) $_POST['amnt'] * $mb_basic * 1024;
        }
    }
    if (isset($_POST['unit'])) {
        if ($openbet >= $maxusrbet) {
            stderr($lang['gl_sorry'], "{$lang['casino_there_are_already']} $openbet {$lang['casino_bets_open_take_an_open_bet']}!&#160;&#160;&#160;$goback", 'bottom20');
        }
        if ($nobits <= 0) {
            stderr($lang['gl_sorry'], " {$lang['casino_this_wont_work_enter_a_pos_val']}?&#160;&#160;&#160;$goback", 'bottom20');
        }
        if ($nobits === '.') {
            stderr($lang['gl_sorry'], " {$lang['casino_this_wont_work_enter_without_a_dec']}?&#160;&#160;&#160;$goback", 'bottom20');
        }
        if ($maxbet < $nobits) {
            stderr($lang['gl_sorry'], '' . htmlsafechars($user['username']) . " The Max allowed bet is $maxbetGB GB!&#160;&#160;&#160;$goback", 'bottom20');
        }
        if ($nobits <= 104857599) {
            stderr($lang['gl_sorry'], '' . htmlsafechars($user['username']) . " The Min allowed bet is 100 MB!&#160;&#160;&#160;$goback", 'bottom20');
        }

        $newups = $user['uploaded'] - $nobits;
        $debt = $nobits - $user['uploaded'];
        if ($user['uploaded'] < $nobits) {
            if ($alwdebt != 1) {
                stderr($lang['gl_sorry'], "<h2>{$lang['casino_thats']} " . htmlsafechars(mksize($debt)) . " {$lang['casino_more_than_you_got']}!</h2>$goback", 'bottom20');
            }
        }
        $betsp = $casino_bets->get_bets($user['id']);
        $session->set('is-success', $lang['casino_bet_added_you_will_receive_a_pm_notify']);
        $bet = mksize($nobits);
        $classColor = get_user_class_color($user['class']);
        $message = "[color=#$classColor][b]{$user['username']}[/b][/color] {$lang['casino_has_just_placed_a']} [color=red][b]{$bet}[/b][/color] {$lang['casino_bet_in_the_casino']}";
        $messages = "{$user['username']} {$lang['casino_has_just_placed_a']} {$bet} {$lang['casino_bet_in_the_casino']}";
        $values = [
            'userid' => $user['id'],
            'proposed' => $user['username'],
            'challenged' => 'empty',
            'amount' => $nobits,
            'time' => $dt,
        ];
        $casino_bets->insert($values);
        $update = [
            'uploaded' => $newups,
        ];
        $users_class->update($update, $user['id']);
        $user_deposit = $user_deposit + $nobits;
        $update = [
            'deposit' => $user_deposit,
        ];
        $casino->update_user($update, $user['id']);
        if ($site_config['site']['autoshout_chat'] || $site_config['site']['autoshout_irc']) {
            autoshout($message);
        }
    }
    $empty_bets = $casino_bets->get_empty_bets();
    $totbets = !empty($empty_bets) ? count($empty_bets) : 0;
    $details = '';
    $blocks = [];
    $HTMLOUT = "
            <h1 class='has-text-centered'>{$site_config['site']['name']} Casino</h1>";
    if ($openbet < $maxusrbet) {
        if ($totbets >= $maxtotbet) {
            $HTMLOUT .= "{$lang['casino_there_are_already']} $maxtotbet {$lang['casino_bets_open_take_an_open_bet']}!";
        } else {
            $blocks[] = "
            <div class='has-text-centered w-40 bg-03 margin20 padding20 round10'>
                <form name='p2p' method='post' action='{$_SERVER['PHP_SELF']}' accept-charset='utf-8'>
                    <h1 class='has-text-centered'>{$site_config['site']['name']} {$lang['casino_stdhead']} - {$lang['casino_bet_p2p_with_other_users']}:</h1>
                    <div>{$lang['casino_place_bet']}</div>
                    <div>{$lang['casino_amount_to_bet']}</div>
                    <input type='text' name='amnt' size='5' value='1'>
                    <select name='unit'>
                        <option value='1'>MB</option>
                        <option value='2'>GB</option>
                    </select>
                    <input type='submit' class='button is-small' value='{$lang['casino_gamble']}!'>
                </form>
            </div>";
        }
    } else {
        $HTMLOUT .= "{$lang['casino_you_already_have']} $maxusrbet {$lang['casino_open_bets_wait_until_they_are_comp']}.";
    }
    $maxbetShow = mksize($maxbet);
    $open_bets = "
                <div class='has-text-centered w-40 bg-03 margin20 padding20 round10'>
                    <h2 class='has-text-centered'>{$lang['casino_open_bets']} - Max Bet {$maxbetShow} - Limit {$maxusrbet} Active Bets</h2>";
    if (!empty($empty_bets)) {
        $heading = "    
                        <tr>
                            <th class='has-text-centered'>{$lang['casino_name']}</th>
                            <th class='has-text-centered'>{$lang['casino_amount']}</th>
                            <th class='has-text-centered'>{$lang['casino_time']}</th>
                            <th class='has-text-centered'>{$lang['casino_take_bet']}</th>
                        </tr>";
        $body = '';

        foreach ($empty_bets as $res) {
            $body .= "
                        <tr>
                            <td class='has-text-centered'>" . format_username($res['userid']) . "</td>
                            <td class='has-text-centered'>" . htmlsafechars(mksize($res['amount'])) . "</td>
                            <td class='has-text-centered'>" . get_date($res['time'], 'LONG', 0, 1) . "</td>
                            <td class='has-text-centered'>
                                <a href='{$_SERVER['PHP_SELF']}?takebet=" . $res['id'] . "'>{$lang['casino_take_bet']}</a>
                            </td>
                        </tr>";
        }
        $blocks[] = $open_bets . main_table($body, $heading) . '
                </div>';
    } else {
        $blocks[] = $open_bets . main_div($lang['casino_sorry_no_bets_currently'], '', 'has-text-centered') . '
                </div>';
    }

    $real_chance = 2;
    if ($show_real_chance) {
        $real_chance = $cheat_value + 1;
    }
    $table = "
            <div class='has-text-centered w-40 bg-03 margin20 padding20 round10'>
            <form name='casino' method='post' action='{$_SERVER['PHP_SELF']}' accept-charset='utf-8'>
                <h2 class='has-text-centered'>{$lang['casino_bet_on_a_colour']}</h2>";

    $body = "
                    <tr>
                        <td>{$lang['casino_black']}</td>
                        <td><input name='color' type='radio' checked value='black'></td>
                    </tr>
                    <tr>
                        <td>{$lang['casino_red']}</td>
                        <td><input name='color' type='radio' checked value='red'></td>
                    </tr>
                    <tr>
                        <td>{$lang['casino_how_much']}</td>
                        <td><select name='betmb'>
                                <option value='{$bet_value1}'>" . mksize($bet_value1) . "</option>
                                <option value='{$bet_value2}'>" . mksize($bet_value2) . "</option>
                                <option value='{$bet_value3}'>" . mksize($bet_value3) . "</option>
                                <option value='{$bet_value4}'>" . mksize($bet_value4) . "</option>
                                <option value='{$bet_value5}'>" . mksize($bet_value5) . "</option>
                                <option value='{$bet_value6}'>" . mksize($bet_value6) . "</option>
                                <option value='{$bet_value7}'>" . mksize($bet_value7) . "</option>
                                <option value='{$bet_value8}'>" . mksize($bet_value8) . "</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>{$lang['casino_your_chance']}</td>
                        <td>1 : {$real_chance}</td>
                    </tr>
                    <tr>
                        <td>{$lang['casino_you_can_win']}</td>
                        <td>{$win_amount} * stake</td>
                    </tr>
                    <tr>
                        <td>{$lang['casino_bet_on_color']}</td>
                        <td><input type='submit' class='button is-small' value='{$lang['casino_do_it']}!'></td>
                    </tr>";
    $blocks[] = $table . main_table($body) . '
            </form>
            </div>';

    $real_chance = 6;
    if ($show_real_chance) {
        $real_chance = $cheat_value + 5;
    }
    $table = "
            <div class='has-text-centered w-40 bg-03 margin20 padding20 round10'>
            <form name='casino' method='post' action='{$_SERVER['PHP_SELF']}' accept-charset='utf-8'>
                <h2 class='has-text-centered'>{$lang['casino_bet_on_a_number']}</h2>";

    $body = "
                    <tr>
                        <td>{$lang['casino_number']}</td>
                        <td>
                            <div class='level-left'>";
    for ($i = 1; $i <= 6; ++$i) {
        $body .= "
                                <label>$i</label>
                                <input name='number' type='radio' value='$i' class='left5 right10'>";
    }
    $body .= "
                            </div>        
                        </td>
                    </tr>
                    <tr>
                        <td>{$lang['casino_how_much']}</td>
                        <td><select name='betmb'>
                                <option value='{$bet_value1}'>" . mksize($bet_value1) . "</option>
                                <option value='{$bet_value2}'>" . mksize($bet_value2) . "</option>
                                <option value='{$bet_value3}'>" . mksize($bet_value3) . "</option>
                                <option value='{$bet_value4}'>" . mksize($bet_value4) . "</option>
                                <option value='{$bet_value5}'>" . mksize($bet_value5) . "</option>
                                <option value='{$bet_value6}'>" . mksize($bet_value6) . "</option>
                                <option value='{$bet_value7}'>" . mksize($bet_value7) . "</option>
                                <option value='{$bet_value8}'>" . mksize($bet_value8) . "</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>{$lang['casino_your_chance']}</td>
                        <td>1 : {$real_chance}</td>
                    </tr>
                    <tr>
                        <td>{$lang['casino_you_can_win']}</td>
                        <td>{$win_amount} * stake</td>
                    </tr>
                    <tr>
                        <td>{$lang['casino_bet_on_a_number']}</td>
                        <td><input type='submit' class='button is-small' value='{$lang['casino_do_it']}!'></td>
                    </tr>";
    $blocks[] = $table . main_table($body) . '
            </form>
            </div>';

    $table = "
            <div class='w-100'>
                <div class='level-center flex-top'>
                    <div class='has-text-centered w-30 bg-03 margin20 padding20 round10'>
                        <h2>User @ {$site_config['site']['name']} {$lang['casino_stdhead']}</h2>";
    $body = "
                        <tr>
                            <td>{$lang['casino_you_can_win']}</td>
                            <td>" . mksize($max_download_user) . "</td>
                        </tr>
                        <tr>
                            <td>{$lang['casino_won']}</td>
                            <td>" . mksize($user_win) . "</td>
                        </tr>
                        <tr>
                            <td>{$lang['casino_lost']}</td>
                            <td>" . mksize($user_lost) . "</td>
                        </tr>
                        <tr>
                            <td>{$lang['casino_ratio']}</td>
                            <td>{$casino_ratio_user}</td>
                        </tr>
                        <tr>
                            <td>{$lang['casino_deposit_on_p2p']}</td>
                            <td>" . mksize($user_deposit + $nobits) . '</td>
                        </tr>';
    $details = $table . main_table($body) . "
                    </div>
                    <div class='has-text-centered w-30 bg-03 margin20 padding20 round10'>
                        <h2>{$lang['casino_global_stats']}</h2>";
    $body = "
                        <tr>
                            <td>{$lang['casino_users_can_win']}</td>
                            <td>" . mksize($max_download_global) . "</td>
                        </tr>
                        <tr>
                            <td>{$lang['casino_won']}</td>
                            <td>" . mksize($global_win) . "</td>
                        </tr>
                        <tr>
                            <td>{$lang['casino_lost']}</td>
                            <td>" . mksize($global_lost) . "</td>
                        </tr>
                        <tr>
                            <td>{$lang['casino_ratio']}</td>
                            <td>{$casino_ratio_global}</td>
                        </tr>
                        <tr>
                            <td>{$lang['casino_deposit']}</td>
                            <td>" . mksize($global_deposit) . '</td>
                        </tr>';

    $details .= main_table($body) . "
                    </div>
                    <div class='has-text-centered w-30 bg-03 margin20 padding20 round10'>
                        <h2>{$lang['casino_user_stats']}</h2>";
    $body = "
                        <tr>
                            <td>{$lang['casino_uploaded']}</td>
                            <td>" . mksize($user['uploaded'] - $nobits) . "</td>
                        </tr>
                        <tr>
                            <td>{$lang['casino_downloaded']}</td>
                            <td>" . mksize($user['downloaded']) . "</td>
                        </tr>
                        <tr>
                            <td>{$lang['casino_ratio']}</td>
                            <td>{$ratio}</td>
                        </tr>";
    $details .= main_table($body) . '
                    </div>
                </div>
            </div>';
}

$HTMLOUT = main_div($HTMLOUT . "<div class='level-center'>" . implode('', $blocks) . '</div>' . $details);
echo stdhead("{$lang['casino_stdhead']}") . wrapper($HTMLOUT) . stdfoot();
