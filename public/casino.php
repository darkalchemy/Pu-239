<?php

declare(strict_types = 1);

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
$user = check_user_status();
global $container, $site_config;

//== Config
$amnt = $nobits = $abcdefgh = 0;
$maxbetGB = 50;
$mib = 1000; // MiB vs MB
$maxbet = $maxbetGB * $mib * $mib * $mib;
$mb_basic = $mib * $mib;
$max_download_user = $mb_basic * $mib * $mib * $mib; //= 255 Gb
$max_download_global = $mb_basic * $mb_basic * $mib; //== 10.0 Tb
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
$bet_value1 = $mib * $mib * 200; //== This is in MB but you can also choose gb or tb
$bet_value2 = $mib * $mib * 500;
$bet_value3 = $mib * $mib * 1000;
$bet_value4 = $mib * $mib * 2500;
$bet_value5 = $mib * $mib * 5000;
$bet_value6 = $mib * $mib * 10000;
$bet_value7 = $mib * $mib * 20000;
$bet_value8 = $mib * $mib * 50000;
$maxusrbet = 5; //==Amount of bets to allow per person
$maxtotbet = 30; //== Amount of total open bets allowed
$alwdebt = 0; //== Allow users to get into debt
$writelog = 1; //== Writes results to log
$delold = 1; //== Clear bets once finished
//== End of Config

$min_text = mksize(100 * 1073741824);
if ($user['class'] < $site_config['allowed']['play']) {
    stderr(_('Error'), _fe('Sorry, you must be a {0} to play in the casino!', $site_config['class_names'][$site_config['allowed']['play']]), 'bottom20');
} elseif ($user['game_access'] !== 1 || $user['status'] !== 0) {
    stderr(_('Error'), _('Your gaming rights have been disabled.'), 'bottom20');
    die();
} elseif ($user['uploaded'] < 1073741824 * 100) {
    stderr('Sorry,', "You must have at least {$min_text} upload credit to play.", 'bottom20');
}

$fluent = $container->get(Database::class);
$users_class = $container->get(User::class);
$casino = $container->get(Casino::class);
$casino_bets = $container->get(CasinoBets::class);
$session = $container->get(Session::class);
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
    stderr(_('Sorry'), htmlsafechars($user['username']) . ' ' . _('you are banned from casino'), 'bottom20');
}
if (($user_win - $user_lost) > $max_download_user) {
    stderr(_('Sorry'), htmlsafechars($user['username']) . ' ' . _('you have reached the max download for a single user'), 'bottom20');
}
if ($user['downloaded'] > 0) {
    $ratio = $user['uploaded'] / $user['downloaded'];
} elseif ($user['uploaded'] > 0) {
    $ratio = 999;
} else {
    $ratio = 0;
}
if (!$site_config['site']['ratio_free'] && $ratio < $required_ratio) {
    stderr(_('Sorry'), htmlsafechars($user['username']) . ' ' . _('your ratio is under') . " {$required_ratio}", 'bottom20');
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
    stderr(_('Sorry'), htmlsafechars($user['username']) . ' ' . _('but global max win is above') . ' ' . htmlsafechars(mksize($max_download_global)), 'bottom20');
}

$goback = "<a href='{$_SERVER['PHP_SELF']}'>" . _('Go back') . '</a>';
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
        stderr(_('Sorry'), htmlsafechars($user['username']) . ' ' . _('but you have not uploaded') . ' ' . htmlsafechars(mksize($betmb)), 'bottom20');
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
        stderr(_('Yes'), htmlsafechars($winner_was) . ' ' . _('is the result') . ' ' . htmlsafechars($user['username']) . ' ' . _('you got it and win') . ' ' . htmlsafechars(mksize($win)) . "&#160;&#160;&#160;$goback", 'bottom20');
    } else {
        if (isset($_POST['number'])) {
            do {
                $fake_winner = random_int(1, 6);
            } while ($_POST['number'] == $fake_winner);
        } elseif ($_POST['color'] === 'black') {
            $fake_winner = 'red';
        } else {
            $fake_winner = 'black';
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
        stderr(_('Sorry'), "$fake_winner " . _('is the winner and not') . " $winner_was, " . htmlsafechars($user['username']) . ' ' . _('you lost') . ' ' . mksize($betmb) . "&#160;&#160;&#160;$goback", 'bottom20');
    }
} else {
    $openbet = $casino_bets->get_open_bets($user['username']);
    if (isset($_POST['unit'])) {
        if ((int) $_POST['unit'] === 1) {
            $nobits = $amnt * $mb_basic;
        } else {
            $nobits = $amnt * $mb_basic * $mib;
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
            stderr(_('Sorry'), _('You want to bet against yourself lol') . " ?&#160;&#160;&#160;$goback", 'bottom20');
        } elseif ($tbet['challenged'] != 'empty') {
            stderr(_('Sorry'), _('Someone has already taken that bet') . "!&#160;&#160;&#160;$goback", 'bottom20');
        }
        if ($user['uploaded'] < $tbet['amount']) {
            $debt = $tbet['amount'] - $user['uploaded'];
            $newup = $user['uploaded'] - $debt;
        }
        if (isset($debt) && $alwdebt != 1) {
            stderr(_('Sorry'), '<h2>' . _('You are') . ' ' . htmlsafechars(mksize(($nobits - $user['uploaded']))) . ' ' . _('short of making that bet') . "!</h2>&#160;&#160;&#160;$goback", 'bottom20');
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
            $subject = _('Casino Results');
            $msg = 'You lost a bet! ' . htmlsafechars($user['username']) . ' just won ' . htmlsafechars($nogb) . ' of your upload credit!';
            $msgs_buffer[] = [
                'receiver' => $tbet['userid'],
                'added' => $dt,
                'msg' => $msg,
                'subject' => $subject,
            ];
            $messages_class->insert($msgs_buffer);
            if ($writelog == 1) {
                write_log($user['username'] . " won $nogb " . _('of upload credit off') . ' ' . htmlsafechars($tbet['proposed']));
            }
            if ($delold === 1) {
                $casino_bets->delete_bet($tbet['id']);
            }
            stderr(_('You got it'), '<h2>' . _('You won the bet') . ', ' . htmlsafechars($nogb) . ' ' . _('has been credited to your account') . ', at ' . format_username($tbet['userid']) . '</a> ' . _('expense') . "!</h2>&#160;&#160;&#160;$goback", 'bottom20');
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
            $subject = _('Casino Results');
            $msg = _fe('You just won {0} of upload credit from {1}!', format_comment($nogb), $user['username']);

            $msgs_buffer[] = [
                'receiver' => $tbet['userid'],
                'added' => $dt,
                'msg' => $msg,
                'subject' => $subject,
            ];
            $messages_class->insert($msgs_buffer);

            if ($writelog == 1) {
                write_log(htmlsafechars($tbet['proposed']) . " won $nogb " . _('of upload credit off') . ' ' . $user['username']);
            }
            if ($delold === 1) {
                $casino_bets->delete_bet($tbet['id']);
            }
            stderr(_('Damn it'), '<h2>' . _('You lost the bet') . ' ' . format_username($tbet['userid']) . ' ' . _('has won') . ' ' . htmlsafechars($nogb) . ' ' . _('of your hard earnt upload credit') . "!</h2> &#160;&#160;&#160;$goback", 'bottom20');
        }
        die();
    }
    $empty_bets = $casino_bets->get_empty_bets();
    $totbets = !empty($empty_bets) ? count($empty_bets) : 0;
    if (isset($_POST['unit'])) {
        if ((int) $_POST['unit'] === 1) {
            $nobits = (int) $_POST['amnt'] * $mb_basic;
        } else {
            $nobits = (int) $_POST['amnt'] * $mb_basic * $mib;
        }
    }
    if (isset($_POST['unit'])) {
        if ($openbet >= $maxusrbet) {
            stderr(_('Sorry'), _('There are already') . " $openbet " . _('bets open, take an open bet or wait till someone plays') . "!&#160;&#160;&#160;$goback", 'bottom20');
        }
        if ($nobits <= 0) {
            stderr(_('Sorry'), _("This won't work, are you trying to cheat? Enter a positive value.") . "&#160;&#160;&#160;$goback", 'bottom20');
        }
        if ($nobits === '.') {
            stderr(_('Sorry'), _("This won't work enter without a decimal point") . "?&#160;&#160;&#160;$goback", 'bottom20');
        }
        if ($maxbet < $nobits) {
            stderr(_('Sorry'), _fe('{0} The Max allowed bet is {1} GB!', htmlsafechars($user['username']), $maxbetGB) . "&#160;&#160;&#160;$goback", 'bottom20');
        }
        if ($nobits <= 104857599) {
            stderr(_('Sorry'), _fe('{0} The Min allowed bet is 100 MB!', htmlsafechars($user['username'])) . "&#160;&#160;&#160;$goback", 'bottom20');
        }

        $newups = $user['uploaded'] - $nobits;
        $debt = $nobits - $user['uploaded'];
        if (($user['uploaded'] < $nobits) && $alwdebt != 1) {
            stderr(_('Sorry'), '<h2>' . _fe("That's {0} more than you have!", mksize($debt)) . "</h2>$goback", 'bottom20');
        }
        $betsp = $casino_bets->get_bets($user['id']);
        $session->set('is-success', _('Bet added, you will receive a PM notifying you of the results when someone has taken it'));
        $bet = mksize($nobits);
        $classColor = get_user_class_color($user['class']);
        $message = "[color=#$classColor][b]" . format_comment($user['username']) . '[/b][/color] ' . _('has just placed a') . " [color=red][b]{$bet}[/b][/color] " . _('bet in the Casino') . '';
        $messages = "{$user['username']} " . _('has just placed a') . " {$bet} " . _('bet in the Casino') . '';
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
            $HTMLOUT .= _('There are already') . " $maxtotbet " . _('bets open, take an open bet or wait till someone plays') . '!';
        } else {
            $blocks[] = "
            <div class='has-text-centered w-40 bg-03 margin20 padding20 round10'>
                <form name='p2p' method='post' action='{$_SERVER['PHP_SELF']}' enctype='multipart/form-data' accept-charset='utf-8'>
                    <h1 class='has-text-centered'>{$site_config['site']['name']} " . _('Casino') . ' - ' . _('Bet P2P with other users') . ':</h1>
                    <div>' . _('Place Bet') . '</div>
                    <div>' . _('Amount to bet') . "</div>
                    <input type='text' name='amnt' size='5' value='1'>
                    <select name='unit'>
                        <option value='1'>MB</option>
                        <option value='2'>GB</option>
                    </select>
                    <input type='submit' class='button is-small' value='" . _('Gamble') . "!'>
                </form>
            </div>";
        }
    } else {
        $HTMLOUT .= _pfe('You already have {0} open bet. Wait until it is completed before you start another.', 'You already have {0} open bets. Wait until they are completed before you start another.', $maxusrbet);
    }
    $maxbetShow = mksize($maxbet);
    $open_bets = "
                <div class='has-text-centered w-40 bg-03 margin20 padding20 round10'>
                    <h2 class='has-text-centered'>" . _('Open Bets') . " - Max Bet {$maxbetShow} - Limit {$maxusrbet} Active Bets</h2>";
    if (!empty($empty_bets)) {
        $heading = "    
                        <tr>
                            <th class='has-text-centered'>" . _('Name') . "</th>
                            <th class='has-text-centered'>" . _('Amount') . "</th>
                            <th class='has-text-centered'>" . _('Time') . "</th>
                            <th class='has-text-centered'>" . _('Take Bet') . '</th>
                        </tr>';
        $body = '';

        foreach ($empty_bets as $res) {
            $body .= "
                        <tr>
                            <td class='has-text-centered'>" . format_username((int) $res['userid']) . "</td>
                            <td class='has-text-centered'>" . htmlsafechars(mksize((int) $res['amount'])) . "</td>
                            <td class='has-text-centered'>" . get_date((int) $res['time'], 'LONG', 0, 1) . "</td>
                            <td class='has-text-centered'>
                                <a href='{$_SERVER['PHP_SELF']}?takebet=" . $res['id'] . "'>" . _('Take Bet') . '</a>
                            </td>
                        </tr>';
        }
        $blocks[] = $open_bets . main_table($body, $heading) . '
                </div>';
    } else {
        $blocks[] = $open_bets . main_div(_('Sorry no bets currently'), '', 'has-text-centered') . '
                </div>';
    }

    $real_chance = 2;
    if ($show_real_chance) {
        $real_chance = $cheat_value + 1;
    }
    $table = "
            <div class='has-text-centered w-40 bg-03 margin20 padding20 round10'>
            <form name='casino' method='post' action='{$_SERVER['PHP_SELF']}' enctype='multipart/form-data' accept-charset='utf-8'>
                <h2 class='has-text-centered'>" . _('Bet on a colour') . '</h2>';

    $body = '
                    <tr>
                        <td>' . _('Black') . "</td>
                        <td><input name='color' type='radio' checked value='black'></td>
                    </tr>
                    <tr>
                        <td>" . _('Red') . "</td>
                        <td><input name='color' type='radio' checked value='red'></td>
                    </tr>
                    <tr>
                        <td>" . _('How much') . "</td>
                        <td><select name='betmb'>
                                <option value='{$bet_value1}'>" . mksize($bet_value1) . "</option>
                                <option value='{$bet_value2}'>" . mksize($bet_value2) . "</option>
                                <option value='{$bet_value3}'>" . mksize($bet_value3) . "</option>
                                <option value='{$bet_value4}'>" . mksize($bet_value4) . "</option>
                                <option value='{$bet_value5}'>" . mksize($bet_value5) . "</option>
                                <option value='{$bet_value6}'>" . mksize($bet_value6) . "</option>
                                <option value='{$bet_value7}'>" . mksize($bet_value7) . "</option>
                                <option value='{$bet_value8}'>" . mksize($bet_value8) . '</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>' . _('Your chance') . "</td>
                        <td>1 : {$real_chance}</td>
                    </tr>
                    <tr>
                        <td>" . _('You can win') . "</td>
                        <td>{$win_amount} * stake</td>
                    </tr>
                    <tr>
                        <td>" . _('Bet on color') . "</td>
                        <td><input type='submit' class='button is-small' value='" . _('Do it') . "!'></td>
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
            <form name='casino' method='post' action='{$_SERVER['PHP_SELF']}' enctype='multipart/form-data' accept-charset='utf-8'>
                <h2 class='has-text-centered'>" . _('Bet on a number') . '</h2>';

    $body = '
                    <tr>
                        <td>' . _('Number') . "</td>
                        <td>
                            <div class='level-left'>";
    for ($i = 1; $i <= 6; ++$i) {
        $body .= "
                                <label>$i</label>
                                <input name='number' type='radio' value='$i' class='left5 right10'>";
    }
    $body .= '
                            </div>        
                        </td>
                    </tr>
                    <tr>
                        <td>' . _('How much') . "</td>
                        <td><select name='betmb'>
                                <option value='{$bet_value1}'>" . mksize($bet_value1) . "</option>
                                <option value='{$bet_value2}'>" . mksize($bet_value2) . "</option>
                                <option value='{$bet_value3}'>" . mksize($bet_value3) . "</option>
                                <option value='{$bet_value4}'>" . mksize($bet_value4) . "</option>
                                <option value='{$bet_value5}'>" . mksize($bet_value5) . "</option>
                                <option value='{$bet_value6}'>" . mksize($bet_value6) . "</option>
                                <option value='{$bet_value7}'>" . mksize($bet_value7) . "</option>
                                <option value='{$bet_value8}'>" . mksize($bet_value8) . '</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>' . _('Your chance') . "</td>
                        <td>1 : {$real_chance}</td>
                    </tr>
                    <tr>
                        <td>" . _('You can win') . "</td>
                        <td>{$win_amount} * stake</td>
                    </tr>
                    <tr>
                        <td>" . _('Bet on a number') . "</td>
                        <td><input type='submit' class='button is-small' value='" . _('Do it') . "!'></td>
                    </tr>";
    $blocks[] = $table . main_table($body) . '
            </form>
            </div>';

    $table = "
            <div class='w-100'>
                <div class='level-center flex-top'>
                    <div class='has-text-centered w-30 bg-03 margin20 padding20 round10'>
                        <h2>User @ {$site_config['site']['name']} " . _('Casino') . '</h2>';
    $body = '
                        <tr>
                            <td>' . _('You can win') . '</td>
                            <td>' . mksize($max_download_user) . '</td>
                        </tr>
                        <tr>
                            <td>' . _('Won') . '</td>
                            <td>' . mksize($user_win) . '</td>
                        </tr>
                        <tr>
                            <td>' . _('Lost') . '</td>
                            <td>' . mksize($user_lost) . '</td>
                        </tr>
                        <tr>
                            <td>' . _('Ratio') . "</td>
                            <td>{$casino_ratio_user}</td>
                        </tr>
                        <tr>
                            <td>" . _('Deposit on P2P') . '</td>
                            <td>' . mksize($user_deposit + $nobits) . '</td>
                        </tr>';
    $details = $table . main_table($body) . "
                    </div>
                    <div class='has-text-centered w-30 bg-03 margin20 padding20 round10'>
                        <h2>" . _('Global stats') . '</h2>';
    $body = '
                        <tr>
                            <td>' . _('Users can win') . '</td>
                            <td>' . mksize($max_download_global) . '</td>
                        </tr>
                        <tr>
                            <td>' . _('Won') . '</td>
                            <td>' . mksize($global_win) . '</td>
                        </tr>
                        <tr>
                            <td>' . _('Lost') . '</td>
                            <td>' . mksize($global_lost) . '</td>
                        </tr>
                        <tr>
                            <td>' . _('Ratio') . "</td>
                            <td>{$casino_ratio_global}</td>
                        </tr>
                        <tr>
                            <td>" . _('Deposit') . '</td>
                            <td>' . mksize($global_deposit) . '</td>
                        </tr>';

    $details .= main_table($body) . "
                    </div>
                    <div class='has-text-centered w-30 bg-03 margin20 padding20 round10'>
                        <h2>" . _('User stats') . '</h2>';
    $body = '
                        <tr>
                            <td>' . _('Uploaded') . '</td>
                            <td>' . mksize($user['uploaded'] - $nobits) . '</td>
                        </tr>
                        <tr>
                            <td>' . _('Downloaded') . '</td>
                            <td>' . mksize($user['downloaded']) . '</td>
                        </tr>
                        <tr>
                            <td>' . _('Ratio') . "</td>
                            <td>{$ratio}</td>
                        </tr>";
    $details .= main_table($body) . '
                    </div>
                </div>
            </div>';
}

$HTMLOUT = main_div($HTMLOUT . "<div class='level-center'>" . implode('', $blocks) . '</div>' . $details);
$title = _('Casino');
$breadcrumbs = [
    "<a href='{$_SERVER['PHP_SELF']}'>$title</a>",
];
echo stdhead($title, [], 'page-wrapper', $breadcrumbs) . wrapper($HTMLOUT) . stdfoot();
