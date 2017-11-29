<?php
require_once realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..') . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'user_functions.php';
require_once INCL_DIR . 'html_functions.php';
check_user_status();
$lang = array_merge(load_language('global'), load_language('blackjack'));
global $CURUSER, $site_config, $cache;

$HTMLOUT = $debugout = '';

if ($CURUSER['game_access'] == 0 || $CURUSER['game_access'] > 1 || $CURUSER['suspended'] == 'yes') {
    stderr($lang['bj_error'], $lang['bj_gaming_rights_disabled']);
}

$blackjack['debug'] = false; // display debug info
$blackjack['decks'] = 2; // number of decks in shoe
$blackjack['dead_cards'] = 35; // number of cards remaining before shuffle
$blackjack['shuffle'] = random_int(1000, 20000); // number of time to shuffle the deck
$blackjack['allowed'] = [1, 10, 20, 50, 100, 250, 500, 1024]; // games allowed
$blackjack['id'] = isset($_GET['id']) && in_array($_GET['id'], $blackjack['allowed']) ? (int)$_GET['id'] : 1;
$blackjack['gameid'] = array_search($blackjack['id'], $blackjack['allowed']) + 1;
$blackjack['version'] = "blackjack{$blackjack['gameid']}";
$blackjack['modifier'] = $blackjack['id'];  //default is 1 for 1 GB
$blackjack['min_uploaded'] = $blackjack['gameid']; // min to play * $blackjack['modifier']
$blackjack['title'] = $lang["bj_title{$blackjack['gameid']}"];
$blackjack['min'] = 5; // min upload credit that will required to play any game
$blackjack['max'] = 5120; // max upload credit that will required to play any game
$blackjack['gm'] = $blackjack['min_uploaded'] * $blackjack['modifier'];
$blackjack['required_ratio'] = 1; // min ratio that will required to play any game

// determine min upload credit required to play this game
if ($blackjack['gm'] < $blackjack['max'] && $blackjack['gm'] > $blackjack['min']) {
    $blackjack['quan'] = $blackjack['gm'];
} elseif ($blackjack['gm'] > $blackjack['max']) {
    $blackjack['quan'] = $blackjack['max'];
} else {
    $blackjack['quan'] = $blackjack['min'];
}
$blackjack['min_text'] = mksize($blackjack['quan'] * 1073741824, 1);
$id = $blackjack['id'];

if ($CURUSER['uploaded'] < 1073741824 * $blackjack['quan']) {
    stderr($lang['bj_sorry'], "You must have at least {$blackjack['min_text']} upload credit to play.");
}

$debugout .= '
            <tr class="no_hover">
                <td>_POST</td>
                <td>blackjack.php:' . __LINE__ . '</td>
                <td>' . json_encode($_POST, JSON_PRETTY_PRINT) . '</td>
            </tr>';
$debugout .= '
            <tr class="no_hover">
                <td>blackjack</td>
                <td>blackjack.php:' . __LINE__ . '</td>
                <td>' . json_encode($blackjack, JSON_PRETTY_PRINT) . '</td>
            </tr>';

$ddown = false;
$update_ddown = "ddown = 'no'";
if (isset($_POST['ddown']) && $_POST['ddown'] === 'ddown') {
    $ddown = true;
    $update_ddown = "ddown = 'yes'";
}

$cards_history = $dealer_cards_history = $deadcards = [];
$sql = "SELECT b.*, u.id, u.gender FROM blackjack AS b INNER JOIN users AS u ON u.id = b.userid WHERE game_id = " . sqlesc($blackjack['gameid']) . " ORDER BY b.date ASC LIMIT 1";
$res = sql_query($sql) or sqlerr(__FILE__, __LINE__);
$nick = mysqli_fetch_assoc($res);
$userName = empty($nick['username']) || $nick['username'] === $CURUSER['username'] ? "<span class='text-red'><b>Dealer</b></span>" : format_username($nick['id']);
if ($nick['gender'] == 'Male') {
    $gender = 'he';
} elseif ($nick['gender'] == 'Female') {
    $gender = 'she';
} else {
    $gender = 'it';
}
$bjusers = [];
$cardsa = $nick['cards'];
$deadcards = explode(' ', $cardsa);
$doubleddown = $nick['ddown'] === 'yes' ? true : false;

if ($CURUSER['id'] == $nick['userid'] && $nick['status'] == 'waiting') {
    stderr('Sorry ' . format_username($CURUSER['id']) . ',', "You'll have to wait until another player plays your last game before you can play a new one.<br>
    You have {$nick['points']}.<br><br>
    <a href='./games.php' class='button'>{$lang['bj_back']}</a><br><br>");
}
if ($CURUSER['id'] != $nick['userid'] && $nick['gameover'] == 'no') {
    stderr('Sorry ' . format_username($CURUSER['id']) . ',', "You'll have to wait until " . format_username($nick['id']) . " finishes $gender game before you can play a new one.<br><br>
    <a href='./games.php' class='button'>{$lang['bj_back']}</a><br><br>");
}
$opponent = isset($nick['username']) ? '<h3>Your Opponent is: ' . format_username($nick['id']) . '</h3>' : '';
$required_ratio = 1.0;

$blackjack['mb'] = 1024 * 1024 * 1024 * $blackjack['modifier'];
$game_size = mksize($blackjack['mb'], 0);
$link = '[url=' . $site_config['baseurl'] . '/blackjack.php?id=' . $id . ']BlackJack ' . $game_size . '[/url]';
$now = TIME_NOW;
$game = isset($_POST['game']) ? htmlsafechars($_POST['game']) : '';
$start_ = isset($_POST['start_']) ? htmlsafechars($_POST['start_']) : '';

$player_showcards = $player_showcards_end = '';
$sql = "SELECT cards FROM blackjack WHERE game_id = " . sqlesc($blackjack['gameid']) . " AND userid != " . sqlesc($CURUSER['id']);
$res = sql_query($sql) or sqlerr(__FILE__, __LINE__);
$list = mysqli_fetch_assoc($res);
if (count($list) > 0) {
    $player_cards = explode(' ', $list['cards']);
    foreach ($player_cards as $card) {
        $arr = getCardData($card);
        if ($card != $player_cards[0]) {
            $player_showcards .= "<div class='card {$arr['pic']}'></div>";
        } else {
            $player_showcards .= "<img src='{$site_config['pic_base_url']}back.png' width='71' height='97' alt='' alt='{$lang['bj_cards']}' title='{$lang['bj_cards']}' class='tooltipper tooltipper_img' />";
        }
        $player_showcards_end .= "<div class='card {$arr['pic']}'></div>";;
    }
    $dealer = true;
    $user_warning = 'You are the dealer, you must take a card below 17.';
} else {
    $sql = "SELECT dealer_cards FROM blackjack WHERE game_id = " . sqlesc($blackjack['gameid']) . " AND userid = " . sqlesc($CURUSER['id']);
    $res = sql_query($sql) or sqlerr(__FILE__, __LINE__);
    $list = mysqli_fetch_assoc($res);
    if (count($list) > 0) {
        $dealer_cards = explode(' ', $list['dealer_cards']);
        foreach ($dealer_cards as $card) {
            $arr = getCardData($card);
            if ($card != $dealer_cards[0]) {
                $player_showcards .= "<div class='card {$arr['pic']}'></div>";
            } else {
                $player_showcards .= "<img src='{$site_config['pic_base_url']}back.png' width='71' height='97' alt='' alt='{$lang['bj_cards']}' title='{$lang['bj_cards']}' class='tooltipper tooltipper_img' />";
            }
        }
        $player_showcards_end = $player_showcards;
    }
    $dealer = false;
    $user_warning = 'You are the player, you can double down with an opening hand worth 11, 10 or 9.';
}

$HTMLOUT .= "
            <div class='top10 has-text-centered'>";

if ($game) {
    /**
     * @param $arg
     */
    function cheater_check($arg)
    {
        if ($arg) {
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit();
        }
    }

    $cardcount = 52;
    $points = $showcards = $aces = '';
    $sql = sql_query("SELECT uploaded, downloaded, bjwins, bjlosses FROM users WHERE id = " . sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);
    $User = mysqli_fetch_assoc($sql);
    $User['uploaded'] = $User['uploaded'];
    $User['downloaded'] = $User['downloaded'];
    $User['bjwins'] = (int)$User['bjwins'];
    $User['bjlosses'] = (int)$User['bjlosses'];
    if ($start_ != 'yes') {
        $playeres = sql_query("SELECT * FROM blackjack WHERE game_id = " . sqlesc($blackjack['gameid']) . " AND userid = " . sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);
        $playerarr = mysqli_fetch_assoc($playeres);
        if ($game == 'hit') {
            $points = $aces = 0;
        }
        $points = 0;
        $gameover = ($playerarr['gameover'] == 'yes' ? true : false);
        cheater_check($gameover && ($game == 'hit' ^ $game == 'stop'));
        $cards = $playerarr['cards'];
        $usedcards = explode(' ', $cards);
        $cards_history = $usedcards;
        $arr = [];
        $numCards = 0;
        foreach ($usedcards as $array_list) {
            $arr[] = $array_list;
        }
        foreach ($arr as $card_id) {
            $used_cards = getCardData($card_id);
            $showcards .= "<div class='card {$used_cards['pic']}'></div>";
            ++$numCards;
            if ($used_cards['points'] > 1) {
                $points += $used_cards['points'];
            } else {
                ++$aces;
            }
        }
    }

    if ($_POST['game'] == 'hit') {
        if ($start_ == 'yes') {
            if ($CURUSER['uploaded'] < $blackjack['mb']) {
                stderr("{$lang['bj_sorry2']} " . $CURUSER['username'], "{$lang['bj_you_have_not_uploaded']} " . mksize($blackjack['mb'], 0) . ' yet.');
            }
            if ($CURUSER['downloaded'] > 0) {
                $ratio = number_format($CURUSER['uploaded'] / $CURUSER['downloaded'], 3);
            } elseif ($CURUSER['uploaded'] > 0) {
                $ratio = 999;
            } else {
                $ratio = 0;
            }
            if ($site_config['ratio_free'] === false && $ratio < $required_ratio) {
                stderr("{$lang['bj_sorry2']} " . $CURUSER['username'], "{$lang['bj_your_ratio_is_lower_req']} " . $required_ratio . '%.');
            }
            $res = sql_query("SELECT * FROM blackjack WHERE userid = " . sqlesc($CURUSER['id']) . " AND game_id = " . sqlesc($blackjack['gameid'])) or sqlerr(__FILE__, __LINE__);
            $arr = mysqli_fetch_assoc($res);
            if ($arr['status'] == 'waiting') {
                stderr($lang['bj_sorry'], $lang['bj_you_will_have_to_wait_til_complete']);
            } elseif ($arr['status'] == 'playing') {
                stderr($lang['bj_sorry'], "{$lang['bj_you_most_finish_current']}
                    <form method='post' action='" . $_SERVER['PHP_SELF'] . "?id=$id'>
                        <input type='hidden' name='game' value='hit' readonly='readonly' />
                        <input type='hidden' name='continue' value='yes' readonly='readonly' />
                        <div class='has-text-centered'>
                            <input class='button' type='submit' value='{$lang['bj_continue_old_game']}' />
                        </div>
                    </form>");
            }
            cheater_check($arr['gameover'] == 'yes');
            $cardids = $dealer_cardids = [];
            // get 2 cards for each the dealer and player
            if (!$dealer) {
                // initial game set up
                //player card 1
                $card = getCard($cardcount, $blackjack['gameid'], true);
                $cardids[] = $card;
                // delaer card 1
                $dealer_card = getCard($cardcount, $blackjack['gameid'], false);
                $dealer_cardids[] = $dealer_card;
                $player_showcards .= "
                    <img src='{$site_config['pic_base_url']}back.png' width='71' height='97' alt='' alt='{$lang['bj_cards']}' title='{$lang['bj_cards']}' class='tooltipper tooltipper_img' />";
                // player card 2
                $card = getCard($cardcount, $blackjack['gameid'], false);
                $cardids[] = $card;
                // dealer card 2
                $dealer_card = getCard($cardcount, $blackjack['gameid'], false);
                $dealer_cardids[] = $dealer_card;
                $card_details = getCardData($dealer_card);
                $player_showcards .= "
                    <div class='card {$card_details['pic']}'></div>";
                $player_showcards_end = $player_showcards;
            } else {
                $sql = "SELECT cards, dealer_cards FROM blackjack WHERE game_id = " . sqlesc($blackjack['gameid']) . " AND userid != " . sqlesc($CURUSER['id']);
                $res = sql_query($sql) or sqlerr(__FILE__, __LINE__);
                $list = mysqli_fetch_assoc($res);
                // get players(dealers) cards
                $cardids = explode(' ', $list['dealer_cards']); //dealers cards
                // get non-players(players) cards
                $dealer_cardids = explode(' ', $list['cards']); // players cards
            }
            $numCards = $dealer_numCards = 0;
            // get player card data
            $points = 0;
            foreach ($cardids as $cardid) {
                $cardarr = getCardData($cardid);
                if ($cardarr['points'] > 1) {
                    $points += $cardarr['points'];
                } else {
                    ++$aces;
                }
                $showcards .= "
                    <div class='card {$cardarr['pic']}'></div>";
                ++$numCards;
                $cardids2[] = $cardid;
                $cards_history[] = $cardid;
            }
            for ($i = 0; $i < $aces; ++$i) {
                $points += ($points < 11 && $aces - $i == 1 ? 11 : 1);
            }

            // get dealer card data
            foreach ($dealer_cardids as $dealer_cardid) {
                $dealer_cardarr = getCardData($dealer_cardid);
                $dealer_cardids2[] = $dealer_cardid;
            }

            $sql = "INSERT INTO blackjack (userid, points, cards, date, dealer_cards, game_id) VALUES (" .
                sqlesc($CURUSER['id']) . ', ' .
                sqlesc($points) . ', ' .
                sqlesc(implode(' ', $cardids2)) . ', ' .
                sqlesc($now) . ', ' .
                sqlesc(implode(' ', $dealer_cardids2)) . ', ' .
                sqlesc($blackjack['gameid']) . ')';

            sql_query($sql) or sqlerr(__FILE__, __LINE__);
            if ($points < 21) {
                $HTMLOUT .= "
                <a id='blackjack-hash'></a>
                <h3>{$lang['bj_welcome']}, " . format_username($CURUSER['id']) . "</h3>
                    <table class='table table-bordered table-striped top20 bottom20'>
                        <tr class='no_hover'>
                            <td class='card-background w-50'>
                                <div class='has-text-centered'>
                                    " . trim($player_showcards) . "
                                </div>
                            </td>
                            <td class='card-background w-50'>
                                <div class='has-text-centered'>
                                    " . trim($showcards) . "
                                </div>
                            </td>
                        </tr>
                        <tr class='no_hover'>
                            <td>
                                <div class='has-text-centered'>
                                    {$userName}
                                </div>
                            </td>
                            <td>" . format_username($CURUSER['id']) . "<br>{$lang['bj_points']} = {$points}<br>{$user_warning}</td>
                        </tr>";
                if (!$ddown) {
                    $HTMLOUT .= "
                        <tr class='no_hover'>
                            <td colspan='2'>
                                <form method='post' action='" . $_SERVER['PHP_SELF'] . "?id=$id'>
                                    <input type='hidden' name='game' value='hit' readonly='readonly' />
                                    <div class='has-text-centered'>
                                        <input class='button' type='submit' value='{$lang['bj_hitme']}' />
                                    </div>
                                </form>
                            </td>
                        </tr>";
                }
                if ($points >= 17 && $dealer || $points >= 10 && !$dealer) {
                    $HTMLOUT .= "
                        <tr class='no_hover'>
                            <td colspan='2'>
                                <form method='post' action='" . $_SERVER['PHP_SELF'] . "?id=$id'>
                                    <input type='hidden' name='game' value='stop' readonly='readonly' />
                                    <div class='has-text-centered'>
                                        <input class='button' type='submit' value='{$lang['bj_stay']}' />
                                    </div>
                                </form>
                            </td>
                        </tr>";
                }
                if ($points >= 9 && $points <= 11 && $numCards === 2 && !$dealer) {
                    $HTMLOUT .= "
                        <tr class='no_hover'>
                            <td colspan='2'>
                                <form method='post' action='" . $_SERVER['PHP_SELF'] . "?id=$id'>
                                    <input type='hidden' name='ddown' value='ddown' readonly='readonly' />
                                    <input type='hidden' name='game' value='hit' readonly='readonly' />
                                    <div class='has-text-centered'>
                                        <input class='button' type='submit' value='Double Down' />
                                    </div>
                                </form>
                            </td>
                        </tr>";
                }

                $HTMLOUT .= '
                    </table>
                </div>';
                output($blackjack, $HTMLOUT, $debugout);
            }
        } elseif (($start_ != 'yes' && isset($_POST['continue']) != 'yes') && !$gameover) {
            // draw 1 card
            cheater_check(empty($playerarr));
            $cardid = getCard($cardcount, $blackjack['gameid']);
            //while (in_array($cardid, $arr) || in_array($cardid, $deadcards)) {
            //  $cardid = getCard($cardcount, $blackjack['gameid']);
            //}
            //if (!in_array($cardid, $cards_history)) {
            $cards_history[] = $cardid;
            //}
            $cardarr = getCardData($cardid);
            $showcards .= "<div class='card {$cardarr['pic']}'></div>";
            ++$numCards;
            if ($cardarr['points'] > 1) {
                $points += $cardarr['points'];
            } else {
                ++$aces;
            }
            for ($i = 0; $i < $aces; ++$i) {
                $points += ($points < 11 && $aces - $i == 1 ? 11 : 1);
            }
            sql_query("UPDATE blackjack SET $update_ddown, points = " . sqlesc($points) . ", cards = '" . $cards . ' ' . $cardid . "' WHERE game_id = " . sqlesc($blackjack['gameid']) . " AND userid = " . sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);
        }
        if ($points == 21 || $points > 21) {
            $waitres = sql_query("SELECT COUNT(userid) AS c FROM blackjack WHERE game_id = " . sqlesc($blackjack['gameid']) . " AND status = 'waiting' AND userid != " . sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);
            $waitarr = mysqli_fetch_assoc($waitres);
            $HTMLOUT .= "
                <a id='blackjack-hash'></a>
                <h3>{$lang['bj_game_over']}</h3>
                <table class='table table-bordered table-striped top20 bottom20'>
                    <tr class='no_hover'>
                        <td class='card-background w-50'>
                            <div class='has-text-centered'>
                                {$player_showcards_end}
                            </div>
                        </td>
                        <td class='card-background w-50'>
                            <div class='has-text-centered'>
                                {$showcards}
                            </div>
                        </td>
                    </tr>
                    <tr class='no_hover'>
                        <td>
                            <div class='has-text-centered'>
                                {$userName}
                            </div>
                        </td>
                        <td>
                            <div class='has-text-centered'>
                                " . format_username($CURUSER['id']) . "<br>{$lang['bj_points']} = {$points}<br>{$user_warning}
                            </div>
                        </td>
                    </tr>
                    <tr class='no_hover'>
                        <td colspan='2'>";
        }
        if ($points == 21) {
            if ($waitarr['c'] > 0) {
                $r = sql_query("SELECT b.*, u.id, u.uploaded, u.downloaded, u.bjwins, u.bjlosses FROM blackjack AS b LEFT JOIN users AS u ON u.id = b.userid WHERE game_id = " . sqlesc($blackjack['gameid']) . " AND b.status = 'waiting' AND b.userid != " . sqlesc($CURUSER['id']) . ' ORDER BY b.date ASC LIMIT 1') or sqlerr(__FILE__, __LINE__);
                $a = mysqli_fetch_assoc($r);
                $points_text = htmlsafechars($a['points']) . " {$lang['bj_points2']}";
                $card_count = count(explode(' ', $a['cards']));
                $dbl_text = '';
                if ($a['ddown'] === 'yes') {
                    $blackjack['mb'] = $blackjack['mb'] * 2;
                    $blackjack['modifier'] = $blackjack['modifier'] * 2;
                    $dbl_text = '[Doubled Down] ';
                    $doubleddown = true;
                } elseif ($card_count === 2 && $a['points'] == 21 && $playerarr['points'] != 21) {
                    $blackjack['mb'] = $blackjack['mb'] * 1.5;
                    $blackjack['modifier'] = $blackjack['modifier'] * 1.5;
                    $points_text = 'Blackjack';
                }
                if ($a['points'] != 21) {
                    $winorlose = "{$lang['bj_you_won']} " . mksize($blackjack['mb'], 0);
                    $sql = "UPDATE users SET uploaded = uploaded + {$blackjack['mb']}, bjwins = bjwins + {$blackjack['modifier']} WHERE id = " . sqlesc($CURUSER['id']);
                    sql_query($sql) or sqlerr(__FILE__, __LINE__);

                    $sql = "UPDATE users SET uploaded = uploaded - {$blackjack['mb']}, bjlosses = bjlosses + {$blackjack['modifier']} WHERE id = " . sqlesc($a['userid']);
                    sql_query($sql) or sqlerr(__FILE__, __LINE__);

                    $update['uploaded'] = ($User['uploaded'] + $blackjack['mb']);
                    $update['uploaded_loser'] = ($a['uploaded'] - $blackjack['mb']);
                    $update['bjwins'] = ($User['bjwins'] + $blackjack['modifier']);
                    $update['bjlosses'] = ($a['bjlosses'] + $blackjack['modifier']);

                    //==stats
                    // winner $CURUSER
                    $cache->update_row('userstats_' . $CURUSER['id'], [
                        'uploaded' => $update['uploaded'],
                    ], $site_config['expires']['u_stats']);
                    // winner $CURUSER
                    $cache->update_row('user_stats_' . $CURUSER['id'], [
                        'uploaded' => $update['uploaded'],
                    ], $site_config['expires']['user_stats']);
                    // loser $a
                    $cache->update_row('userstats_' . $a['userid'], [
                        'uploaded' => $update['uploaded_loser'],
                    ], $site_config['expires']['u_stats']);
                    // loser $a
                    $cache->update_row('user_stats_' . $a['userid'], [
                        'uploaded' => $update['uploaded_loser'],
                    ], $site_config['expires']['user_stats']);

                    //== curuser values
                    // winner $CURUSER
                    $cache->update_row('MyUser_' . $CURUSER['id'], [
                        'bjwins' => $update['bjwins'],
                    ], $site_config['expires']['curuser']);
                    // winner $CURUSER
                    $cache->update_row('user' . $CURUSER['id'], [
                        'bjwins' => $update['bjwins'],
                    ], $site_config['expires']['user_cache']);
                    // loser $a
                    $cache->update_row('MyUser_' . $a['userid'], [
                        'bjlosses' => $update['bjlosses'],
                    ], $site_config['expires']['curuser']);
                    // loser $a
                    $cache->update_row('user' . $a['userid'], [
                        'bjlosses' => $update['bjlosses'],
                    ], $site_config['expires']['user_cache']);

                    $lost_str = str_replace('10GB', mksize($blackjack['mb'], 0), $lang['bj_you_loss_to_10']);
                    $msg = sqlesc("BlackJack $game_size: $lost_str " . $CURUSER['username'] . " ({$lang['bj_you_had']} " . $a['points'] . " {$lang['bj_points2']}, " . $CURUSER['username'] . " {$lang['bj_had_21_points']}).\n\n");
                    $subject = sqlesc($lang['bj_blackjack_results']);
                    $outcome = "{$dbl_text}and won";
                } else {
                    $subject = sqlesc($lang['bj_blackjack_results']);
                    $winorlose = $lang['bj_nobody_won'];
                    $msg = sqlesc("BlackJack $game_size: {$lang['bj_you_tied_with']} " . $CURUSER['username'] . " ({$lang['bj_you_both_had']} " . $a['points'] . " points).\n\n");
                    $outcome = "{$dbl_text}and tied";
                }

                $sql = 'INSERT INTO messages (sender, receiver, added, msg, subject) VALUES(0, ' . sqlesc($a['userid']) . ", $now, $msg, $subject)";
                sql_query($sql) or sqlerr(__FILE__, __LINE__);
                if ($site_config['autoshout_on'] == 1 || $site_config['irc_autoshout_on'] == 1) {
                    $classColor = get_user_class_color($CURUSER['class']);
                    $opponent = get_user_class_color($a['class']);
                    $msg = "[color=#$classColor]{$CURUSER['username']}[/color] has just played [color=#$opponent]{$a['username']}[/color] $outcome ($points to {$a['points']}) $link.";
                    for ($i = 0; $i < $aces; ++$i) {
                        $points += ($points < 11 && $aces - $i == 1 ? 11 : 1);
                    }
                    $list = 'INSERT INTO blackjack_history (`date`, game, player1_userid, player1_points, player1_cards, player2_points, player2_userid, player2_cards) VALUES (UNIX_TIMESTAMP(NOW()), ' . sqlesc($blackjack['gameid']) . ', ' . sqlesc($a['userid']) . ', ' . sqlesc($a['points']) . ', ' . sqlesc($a['cards']) . ', ' . sqlesc($points) . ', ' . sqlesc($CURUSER['id']) . ', ' . sqlesc(implode(' ', $cards_history)) . ')';
                    sql_query($list) or sqlerr(__FILE__, __LINE__);
                    autoshout($msg);
                }
                $cache->increment('inbox_' . $a['userid']);
                sql_query("DELETE FROM blackjack WHERE game_id = " . sqlesc($blackjack['gameid']) . " AND userid IN (" . sqlesc($CURUSER['id']) . ', ' . sqlesc($a['userid']) . ')') or sqlerr(__FILE__, __LINE__);
                $HTMLOUT .= "
                        <p class='has-text-centered'>
                            {$lang['bj_your_opp_was']} " . format_username($a['id']) . ", $gender had $points_text, $winorlose.
                        </p><br>
                        <p class='has-text-centered'>
                            <a href='./blackjack.php?id=$id' class='button'>{$lang['bj_play_again']}</a>
                            <a href='./games.php' class='button'>Games</a>
                        </p>";
            } else {
                sql_query("UPDATE blackjack SET $update_ddown, status = 'waiting', date = " . $now . ", gameover = 'yes' WHERE game_id = " . sqlesc($blackjack['gameid']) . " AND userid = " . sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);
                if ($site_config['autoshout_on'] == 1 || $site_config['irc_autoshout_on'] == 1) {
                    $classColor = get_user_class_color($CURUSER['class']);
                    $msg = "[color=#$classColor]{$CURUSER['username']}[/color] has just played $link.";
                    autoshout($msg);
                }
                $HTMLOUT .= "
                        <div class='has-text-centered'>
                            {$lang['bj_there_are_no_other_players']}<br>
                            <a href='./games.php' class='button'>{$lang['bj_back']}</a>
                        </div>";
            }
            $HTMLOUT .= '
                        </td>
                    </tr>
                </table>
            </div>';
            output($blackjack, $HTMLOUT, $debugout);
        } elseif ($points > 21) {
            if ($waitarr['c'] > 0) {
                $r = sql_query("SELECT b.*, u.id, u.uploaded, u.downloaded, u.bjwins, u.bjlosses FROM blackjack AS b LEFT JOIN users AS u ON u.id = b.userid WHERE b.game_id = " . sqlesc($blackjack['gameid']) . " AND b.status = 'waiting' AND b.userid != " . sqlesc($CURUSER['id']) . ' ORDER BY b.date ASC LIMIT 1') or sqlerr(__FILE__, __LINE__);
                $a = mysqli_fetch_assoc($r);
                $points_text = htmlsafechars($a['points']) . " {$lang['bj_points2']}";
                $card_count = count(explode(' ', $a['cards']));
                $dbl_text = '';
                if ($a['ddown'] === 'yes') {
                    $blackjack['mb'] = $blackjack['mb'] * 2;
                    $blackjack['modifier'] = $blackjack['modifier'] * 2;
                    $dbl_text = '[Doubled Down] ';
                    $doubleddown = true;
                } elseif ($card_count === 2 && $a['points'] == 21 && $playerarr['points'] != 21) {
                    $blackjack['mb'] = $blackjack['mb'] * 1.5;
                    $blackjack['modifier'] = $blackjack['modifier'] * 1.5;
                    $points_text = 'Blackjack';
                }
                if ($a['points'] > 21) {
                    $subject = sqlesc($lang['bj_blackjack_results']);
                    $winorlose = $lang['bj_nobody_won'];
                    $msg = sqlesc("BlackJack $game_size: {$lang['bj_you_tied_with']} " . $CURUSER['username'] . " ({$lang['bj_you_both_had']} " . $a['points'] . " points).\n\n");
                    $outcome = "{$dbl_text}and busted";
                } else {
                    $subject = sqlesc($lang['bj_blackjack_results']);
                    $winorlose = "{$lang['bj_you_lost']} " . mksize($blackjack['mb'], 0);

                    $sql = "UPDATE users SET uploaded = uploaded + {$blackjack['mb']}, bjwins = bjwins + {$blackjack['modifier']} WHERE id = " . sqlesc($a['userid']);
                    sql_query($sql) or sqlerr(__FILE__, __LINE__);

                    $sql = "UPDATE users SET uploaded = uploaded - {$blackjack['mb']}, bjlosses = bjlosses + {$blackjack['modifier']} WHERE id = " . sqlesc($CURUSER['id']);
                    sql_query($sql) or sqlerr(__FILE__, __LINE__);

                    $update['uploaded'] = ($a['uploaded'] + $blackjack['mb']);
                    $update['uploaded_loser'] = ($User['uploaded'] - $blackjack['mb']);
                    $update['bjwins'] = ($a['bjwins'] + $blackjack['modifier']);
                    $update['bjlosses'] = ($User['bjlosses'] + $blackjack['modifier']);

                    //==stats
                    // winner $a
                    $cache->update_row('userstats_' . $a['userid'], [
                        'uploaded' => $update['uploaded'],
                    ], $site_config['expires']['u_stats']);
                    // winner $a
                    $cache->update_row('user_stats_' . $a['userid'], [
                        'uploaded' => $update['uploaded'],
                    ], $site_config['expires']['user_stats']);
                    // loser $CURUSER
                    $cache->update_row('userstats_' . $CURUSER['id'], [
                        'uploaded' => $update['uploaded_loser'],
                    ], $site_config['expires']['u_stats']);
                    // loser $CURUSER
                    $cache->update_row('user_stats_' . $CURUSER['id'], [
                        'uploaded' => $update['uploaded_loser'],
                    ], $site_config['expires']['user_stats']);
                    //== curuser values
                    // winner $a
                    $cache->update_row('MyUser_' . $a['userid'], [
                        'bjwins' => $update['bjwins'],
                    ], $site_config['expires']['curuser']);
                    // winner $a
                    $cache->update_row('user' . $a['userid'], [
                        'bjwins' => $update['bjwins'],
                    ], $site_config['expires']['user_cache']);
                    // loser $CURUSER
                    $cache->update_row('MyUser_' . $CURUSER['id'], [
                        'bjlosses' => $update['bjlosses'],
                    ], $site_config['expires']['curuser']);
                    // loser $CURUSER
                    $cache->update_row('user' . $CURUSER['id'], [
                        'bjlosses' => $update['bjlosses'],
                    ], $site_config['expires']['user_cache']);

                    $won_str = str_replace('10GB', mksize($blackjack['mb'], 0), $lang['bj_you_beat_10']);
                    $msg = sqlesc("BlackJack $game_size: $won_str " . $CURUSER['username'] . " ({$lang['bj_you_had']} " . $a['points'] . " {$lang['bj_points2']}, " . $CURUSER['username'] . " had $points points).\n\n");
                    $outcome = "{$dbl_text}and lost";
                }
                $sql = 'INSERT INTO messages (sender, receiver, added, msg, subject) VALUES(0, ' . sqlesc($a['userid']) . ", $now, $msg, $subject)";
                sql_query($sql) or sqlerr(__FILE__, __LINE__);

                if ($site_config['autoshout_on'] == 1 || $site_config['irc_autoshout_on'] == 1) {
                    $classColor = get_user_class_color($CURUSER['class']);
                    $opponent = get_user_class_color($a['class']);
                    $msg = "[color=#$classColor]{$CURUSER['username']}[/color] has just played [color=#$opponent]{$a['username']}[/color] $outcome ($points to {$a['points']}) $link.";
                    for ($i = 0; $i < $aces; ++$i) {
                        $points += ($points < 11 && $aces - $i == 1 ? 11 : 1);
                    }
                    $list = 'INSERT INTO blackjack_history (`date`, game, player1_userid, player1_points, player1_cards, player2_points, player2_userid, player2_cards) VALUES (UNIX_TIMESTAMP(NOW()), ' . sqlesc($blackjack['gameid']) . ', ' . sqlesc($a['userid']) . ', ' . sqlesc($a['points']) . ', ' . sqlesc($a['cards']) . ', ' . sqlesc($points) . ', ' . sqlesc($CURUSER['id']) . ', ' . sqlesc(implode(' ', $cards_history)) . ')';
                    sql_query($list) or sqlerr(__FILE__, __LINE__);
                    autoshout($msg);
                }
                $cache->increment('inbox_' . $a['userid']);
                sql_query("DELETE FROM blackjack WHERE gameid = " . sqlesc($blackjack['gameid']) . " AND userid IN (" . sqlesc($CURUSER['id']) . ', ' . sqlesc($a['userid']) . ')') or sqlerr(__FILE__, __LINE__);
                $HTMLOUT .= "
                        <p class='has-text-centered'>
                            {$lang['bj_your_opp_was']} " . format_username($a['id']) . ", $gender had $points_text, $winorlose.
                        </p><br>
                        <p class='has-text-centered'>
                            <a href='./blackjack.php?id=$id' class='button'>{$lang['bj_play_again']}</a>
                            <a href='./games.php' class='button'>Games</a>
                        </p>";
            } else {
                sql_query("UPDATE blackjack SET $update_ddown, status = 'waiting', date = " . $now . ", gameover = 'yes' WHERE game_id = " . sqlesc($blackjack['gameid']) . " AND userid = " . sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);
                if ($site_config['autoshout_on'] == 1 || $site_config['irc_autoshout_on'] == 1) {
                    $classColor = get_user_class_color($CURUSER['class']);
                    $msg = "[color=#$classColor]{$CURUSER['username']}[/color] has just played $link.";
                    autoshout($msg);
                }
                $HTMLOUT .= "
                        <div class='has-text-centered'>
                            {$lang['bj_there_are_no_other_players']}<br>
                            <a href='./games.php' class='button'>{$lang['bj_back']}</a>
                        </div>";
            }
            $HTMLOUT .= '
                    </td>
                </tr>
            </table>
        </div>';
            output($blackjack, $HTMLOUT, $debugout);
        } else {
            cheater_check(empty($playerarr));
            $HTMLOUT .= "
                <a id='blackjack-hash'></a>
                <h3>{$lang['bj_welcome']}, " . format_username($CURUSER['id']) . "</h3>
                <table class='table table-bordered table-striped top20 bottom20'>
                    <tr class='no_hover'>
                        <td class='card-background w-50'>
                            <div class='has-text-centered'>
                                {$player_showcards}
                            </div>
                        </td>
                        <td class='card-background w-50'>
                            <div class='has-text-centered'>
                                {$showcards}
                            </div>
                        </td>
                    </tr>
                    <tr class='no_hover'>
                        <td>
                            <div class='has-text-centered'>
                                {$userName}
                            </div>
                        </td>
                        <td>
                            <div class='has-text-centered'>
                                " . format_username($CURUSER['id']) . "<br>{$lang['bj_points']} = {$points}<br>{$user_warning}
                            </div>
                        </td>
                    </tr>";
            if (!$ddown) {
                $HTMLOUT .= "
                    <tr class='no_hover'>
                        <td colspan='2'>
                            <form method='post' action='" . $_SERVER['PHP_SELF'] . "?id=$id'>
                                <input type='hidden' name='game' value='hit' readonly='readonly' />
                                <div class='has-text-centered'>
                                    <input class='button' type='submit' value='{$lang['bj_hitme']}' />
                                </div>
                            </form>
                        </td>
                    </tr>";
            }
            if ($points >= 17 && $dealer || $points >= 10 && !$dealer) {
                $HTMLOUT .= "
                    <tr class='no_hover'>
                        <td colspan='2'>
                            <form method='post' action='" . $_SERVER['PHP_SELF'] . "?id=$id'>
                                <input type='hidden' name='game' value='stop' readonly='readonly' />
                                <div class='has-text-centered'>
                                    <input class='button' type='submit' value='{$lang['bj_stay']}' />
                                </div>
                            </form>
                        </td>
                    </tr>";
            }
            if ($points >= 9 && $points <= 11 && $numCards === 2 && !$dealer) {
                $HTMLOUT .= "
                    <tr class='no_hover'>
                        <td colspan='2'>
                            <form method='post' action='" . $_SERVER['PHP_SELF'] . "?id=$id'>
                                <input type='hidden' name='ddown' value='ddown' readonly='readonly' />
                                <input type='hidden' name='game' value='hit' readonly='readonly' />
                                <div class='has-text-centered'>
                                    <input class='button' type='submit' value='Double Down' />
                                </div>
                            </form>
                        </td>
                    </tr>";
            }
            $HTMLOUT .= '
                </table>
            </div>';
            output($blackjack, $HTMLOUT, $debugout);
        }
    } elseif ($_POST['game'] == 'stop') {
        cheater_check(empty($playerarr));
        $waitres = sql_query("SELECT COUNT(userid) AS c FROM blackjack WHERE game_id = " . sqlesc($blackjack['gameid']) . " AND status = 'waiting' AND userid != " . sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);
        $waitarr = mysqli_fetch_assoc($waitres);
        $HTMLOUT .= "
                <a id='blackjack-hash'></a>
                <h3>{$lang['bj_game_over']}</h3>
                <table class='table table-bordered table-striped top20 bottom20'>
                    <tr class='no_hover'>
                        <td class='card-background w-50'>
                            <div class='has-text-centered'>
                                {$player_showcards_end}
                            </div>
                        </td>
                        <td class='card-background w-50'>
                            <div class='has-text-centered'>
                                {$showcards}
                            </div>
                        </td>
                    </tr>
                    <tr class='no_hover'>
                        <td>
                            <div class='has-text-centered'>
                                {$userName}
                            </div>
                        </td>
                        <td>
                            <div class='has-text-centered'>
                                " . format_username($CURUSER['id']) . "<br>{$lang['bj_points']} = {$playerarr['points']}
                            </div>
                        </td>
                    </tr>
                    <tr class='no_hover'>
                        <td colspan='2'>";
        //{$user_warning}";
        if ($waitarr['c'] > 0) {
            $r = sql_query("SELECT b.*, u.id, u.uploaded, u.downloaded, u.bjwins, u.bjlosses FROM blackjack AS b LEFT JOIN users AS u ON u.id = b.userid WHERE b.game_id = " . sqlesc($blackjack['gameid']) . " AND b.status = 'waiting' AND b.userid != " . sqlesc($CURUSER['id']) . ' ORDER BY b.date ASC LIMIT 1') or sqlerr(__FILE__, __LINE__);
            $a = mysqli_fetch_assoc($r);
            $points_text = htmlsafechars($a['points']) . " {$lang['bj_points2']}";
            $card_count = count(explode(' ', $a['cards']));
            $dbl_text = '';
            if ($a['ddown'] === 'yes') {
                $blackjack['mb'] = $blackjack['mb'] * 2;
                $blackjack['modifier'] = $blackjack['modifier'] * 2;
                $dbl_text = '[Doubled Down] ';
                $doubleddown = true;
            } elseif ($card_count === 2 && $a['points'] == 21 && $playerarr['points'] != 21) {
                $blackjack['mb'] = $blackjack['mb'] * 1.5;
                $blackjack['modifier'] = $blackjack['modifier'] * 1.5;
                $points_text = 'Blackjack';
            }
            if ($a['points'] == $playerarr['points']) {
                $subject = sqlesc($lang['bj_blackjack_results']);
                $winorlose = $lang['bj_nobody_won'];
                $msg = sqlesc("BlackJack $game_size: {$lang['bj_your_opp_was']} " . $CURUSER['username'] . ", you both had $points_text - it was a tie.\n\n");
                $outcome = "{$dbl_text}and tied";
            } else {
                // winner $CURUSER
                if (($a['points'] < $playerarr['points'] && $a['points'] < 21) || ($a['points'] > $playerarr['points'] && $a['points'] > 21)) {
                    $subject = sqlesc($lang['bj_blackjack_results']);
                    $lost_str = str_replace('10GB', mksize($blackjack['mb'], 0), $lang['bj_you_loss_to_10']);
                    $msg = sqlesc("BlackJack $game_size: $lost_str " . $CURUSER['username'] . " ({$lang['bj_you_had']} " . htmlsafechars($a['points']) . " {$lang['bj_points2']}, " . $CURUSER['username'] . ' had ' . htmlsafechars($playerarr['points']) . " points).\n\n");
                    $winorlose = "{$lang['bj_you_won']} " . mksize($blackjack['mb'], 0);
                    $st_query = '+ ' . $blackjack['mb'] . ', bjwins = bjwins +';
                    $nd_query = '- ' . $blackjack['mb'] . ', bjlosses = bjlosses +';
                    $update['uploaded'] = ($User['uploaded'] + $blackjack['mb']);
                    $update['uploaded_loser'] = ($a['uploaded'] - $blackjack['mb']);
                    $update['bjwins'] = ($User['bjwins'] + $blackjack['modifier']);
                    $update['bjlosses'] = ($a['bjlosses'] + $blackjack['modifier']);
                    $update['winnerid'] = $playerarr['userid'];
                    $update['loserid'] = $a['userid'];
                    $outcome = "{$dbl_text}and won";
                    // loser $CURUSER
                } elseif (($a['points'] > $playerarr['points'] && $a['points'] < 21) || $a['points'] == 21 || ($a['points'] < $playerarr['points'] && $a['points'] > 21)) {
                    $subject = sqlesc($lang['bj_blackjack_results']);
                    $won_str = str_replace('10GB', mksize($blackjack['mb'], 0), $lang['bj_you_beat_10']);
                    $msg = sqlesc("BlackJack $game_size: $won_str " . $CURUSER['username'] . " ({$lang['bj_you_had']} " . htmlsafechars($a['points']) . " {$lang['bj_points2']}, " . $CURUSER['username'] . ' had ' . htmlsafechars($playerarr['points']) . " points).\n\n");
                    $winorlose = "{$lang['bj_you_lost']} " . mksize($blackjack['mb'], 0);
                    $st_query = '- ' . $blackjack['mb'] . ', bjlosses = bjlosses +';
                    $nd_query = '+ ' . $blackjack['mb'] . ', bjwins = bjwins +';
                    $update['uploaded'] = ($a['uploaded'] + $blackjack['mb']);
                    $update['uploaded_loser'] = ($User['uploaded'] - $blackjack['mb']);
                    $update['bjwins'] = ($a['bjwins'] + $blackjack['modifier']);
                    $update['bjlosses'] = ($User['bjlosses'] + $blackjack['modifier']);
                    $update['winnerid'] = $a['userid'];
                    $update['loserid'] = $playerarr['userid'];
                    $outcome = "{$dbl_text}and lost";
                }

                $sql = 'UPDATE users SET uploaded = uploaded ' . $st_query . " {$blackjack['modifier']} WHERE id = " . sqlesc($CURUSER['id']);
                sql_query($sql) or sqlerr(__FILE__, __LINE__);

                $sql = 'UPDATE users SET uploaded = uploaded ' . $nd_query . " {$blackjack['modifier']} WHERE id = " . sqlesc($a['userid']);
                sql_query($sql) or sqlerr(__FILE__, __LINE__);

                //==stats
                $cache->update_row('userstats_' . $update['winnerid'], [
                    'uploaded' => $update['uploaded'],
                ], $site_config['expires']['u_stats']);
                $cache->update_row('user_stats_' . $update['winnerid'], [
                    'uploaded' => $update['uploaded'],
                ], $site_config['expires']['user_stats']);
                $cache->update_row('userstats_' . $update['loserid'], [
                    'uploaded' => $update['uploaded_loser'],
                ], $site_config['expires']['u_stats']);
                $cache->update_row('user_stats_' . $update['loserid'], [
                    'uploaded' => $update['uploaded_loser'],
                ], $site_config['expires']['user_stats']);
                //== curuser values
                $cache->update_row('MyUser_' . $update['winnerid'], [
                    'bjwins' => $update['bjwins'],
                ], $site_config['expires']['curuser']);
                $cache->update_row('user' . $update['winnerid'], [
                    'bjwins' => $update['bjwins'],
                ], $site_config['expires']['user_cache']);
                // loser $CURUSER
                $cache->update_row('MyUser_' . $update['loserid'], [
                    'bjlosses' => $update['bjlosses'],
                ], $site_config['expires']['curuser']);
                // loser $CURUSER
                $cache->update_row('user' . $update['loserid'], [
                    'bjlosses' => $update['bjlosses'],
                ], $site_config['expires']['user_cache']);
            }

            $sql = 'INSERT INTO messages (sender, receiver, added, msg, subject) VALUES(0, ' . sqlesc($a['userid']) . ", $now, $msg, $subject)";
            sql_query($sql) or sqlerr(__FILE__, __LINE__);

            if ($site_config['autoshout_on'] == 1 || $site_config['irc_autoshout_on'] == 1) {
                $classColor = get_user_class_color($CURUSER['class']);
                $opponent = get_user_class_color($a['class']);
                $msg = "[color=#$classColor]{$CURUSER['username']}[/color] has just played [color=#$opponent]{$a['username']}[/color] $outcome ({$playerarr['points']} to {$a['points']}) $link.";
                autoshout($msg);
            }

            for ($i = 0; $i < $aces; ++$i) {
                $points += ($points < 11 && $aces - $i == 1 ? 11 : 1);
            }
            $list = 'INSERT INTO blackjack_history (`date`, game, player1_userid, player1_points, player1_cards, player2_points, player2_userid, player2_cards) VALUES (UNIX_TIMESTAMP(NOW()), ' . sqlesc($blackjack['gameid']) . ', ' . sqlesc($a['userid']) . ', ' . sqlesc($a['points']) . ', ' . sqlesc($a['cards']) . ', ' . sqlesc($points) . ', ' . sqlesc($CURUSER['id']) . ', ' . sqlesc(implode(' ', $cards_history)) . ')';
            sql_query($list) or sqlerr(__FILE__, __LINE__);
            $cache->increment('inbox_' . $a['userid']);
            sql_query("DELETE FROM blackjack WHERE gameid = " . sqlesc($blackjack['gameid']) . " AND userid IN (" . sqlesc($CURUSER['id']) . ', ' . sqlesc($a['userid']) . ')') or sqlerr(__FILE__, __LINE__);
            $HTMLOUT .= "
                        <p class='has-text-centered'>
                            {$lang['bj_your_opp_was']} " . format_username($a['id']) . ", $gender had $points_text, $winorlose.
                        </p><br>
                        <p class='has-text-centered'>
                            <a href='./blackjack.php?id=$id' class='button'>{$lang['bj_play_again']}</a>
                            <a href='./games.php' class='button'>Games</a>
                        </p>";
        } else {
            sql_query("UPDATE blackjack SET $update_ddown, status = 'waiting', date = " . $now . ", gameover = 'yes' WHERE game_id = " . sqlesc($blackjack['gameid']) . " AND userid = " . sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);
            if ($site_config['autoshout_on'] == 1 || $site_config['irc_autoshout_on'] == 1) {
                $classColor = get_user_class_color($CURUSER['class']);
                $msg = "[color=#$classColor]{$CURUSER['username']}[/color] has just played $link.";
                autoshout($msg);
            }
            $HTMLOUT .= "
                        <div class='has-text-centered'>
                            {$lang['bj_there_are_no_other_players']}<br>
                            <a href='./games.php' class='button'>{$lang['bj_back']}</a>
                        </div>";
        }
        $HTMLOUT .= '
                        </td>
                    </tr>
                </table>
            </div>';
        output($blackjack, $HTMLOUT, $debugout);
    }
} else {
    $sql = sql_query('SELECT bjwins, bjlosses FROM users WHERE id = ' . sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);
    $User = mysqli_fetch_assoc($sql);
    $User['bjwins'] = (int)$User['bjwins'] * 1024 * 1024 * 1024;
    $User['bjlosses'] = (int)$User['bjlosses'] * 1024 * 1024 * 1024;
    $tot_wins = (int)$User['bjwins'];
    $tot_losses = (int)$User['bjlosses'];
    $tot_games = $tot_wins + $tot_losses;
    $win_perc = ($tot_losses == 0 ? ($tot_wins == 0 ? '---' : '100%') : ($tot_wins == 0 ? '0' : number_format(($tot_wins / $tot_games) * 100, 1)) . '%');
    $plus_minus = $tot_wins - abs($tot_losses);
    $sql = sql_query("SELECT * FROM blackjack WHERE game_id = " . sqlesc($blackjack['gameid']) . " ORDER BY date ASC LIMIT 1") or sqlerr(__FILE__, __LINE__);
    $res = mysqli_fetch_assoc($sql);
    $doubled = '';
    if ($res['ddown'] === 'yes') {
        $blackjack['mb'] = $blackjack['mb'] * 2;
        $doubled = "
            <tr class='no_hover'>
                <td>
                    <div class='has-text-centered'>" . format_username($nick['id']) . ' has Doubled Down, thereby doubling the bet to ' . mksize($blackjack['mb'], 0) . '.</div>
                </td>
            </tr>';
    }
    $game_str = str_replace('10GB', mksize($blackjack['mb'], 0), $lang['bj_bj_note_cost_10']);

    $HTMLOUT .= "
                <a id='blackjack-hash'></a>
                <h2><a href='./games.php' title='Return to the Games' class='tooltipper'>{$blackjack['title']}</a></h2>
                $opponent
                <table class='table table-bordered table-striped top20 bottom20'>
                    <tr class='no_hover'>
                        <td class='card-background w-50'>
                            <div class='has-text-centered'>
                                <div class='card ace_spades'></div>
                                <div class='card jack_spades'></div>
                            </div>
                        </td>
                    </tr>
                    $doubled
                    <tr class='no_hover'>
                        <td>
                            <p class='has-text-centered'>{$lang['bj_you_most_collect_21']}</p>
                            <p class='has-text-centered'>{$lang['bj_note']} {$game_str}</p>
                            <p class='has-text-centered'>You can lose 1.5 times your bet if you lose to a Natural Blackjack and a winning Natural Blackjack pays out at 1.5 times the bet.<br>The first player is considered the player and the second is considered the dealer. Only the player can double down on 9, 10, 11 and receive 1 card only. The dealer can see the upcards of the player but must draw a card for anything less than 17. No one wins a tie.</p>
                        </td>
                    </tr>
                    <tr class='no_hover'>
                        <td>
                            <form method='post' action='" . $_SERVER['PHP_SELF'] . "?id=$id'>
                                <input type='hidden' name='game' value='hit' readonly='readonly' />
                                <input type='hidden' name='start_' value='yes' readonly='readonly' />
                                <div class='has-text-centered'>
                                    <input class='button' type='submit' value='Start!' />
                                </div>
                            </form>
                        </td>
                    </tr>
                </table>

                <table class='table table-bordered table-striped top20 bottom20'>
                    <thead>
                        <tr class='no_hover'>
                            <th colspan='2'>
                                <div class='has-text-centered'>
                                    <h3>{$lang['bj_personal_stats']}</h3>
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class='no_hover'>
                            <td>{$lang['bj_wins']}</td>
                            <td>" . human_filesize($tot_wins) . "</td>
                        </tr>
                        <tr class='no_hover'>
                            <td>{$lang['bj_losses']}</td>
                            <td>" . human_filesize($tot_losses) . "</td>
                        </tr>
                        <tr class='no_hover'>
                            <td>{$lang['bj_win']} {$lang['bj_percentage']}</td>
                            <td>" . htmlsafechars($win_perc) . "</td>
                        </tr>
                        <tr class='no_hover'>
                            <td>+/-</td>
                            <td>" . human_filesize($plus_minus) . '</td>
                        </tr>
                      
                </table>';
    // site stats
    $query = "SELECT id, bjwins * 1024 * 1024 * 1024 AS wins, bjlosses * 1024 * 1024 * 1024 AS losses, (bjwins - bjlosses) * 1024 * 1024 * 1024 AS sum
                FROM users
                WHERE enabled = 'yes' AND (bjwins > 0 OR bjlosses > 0) ORDER BY sum DESC LIMIT 20";
    $sql = sql_query($query) or sqlerr(__FILE__, __LINE__);
    while ($row = mysqli_fetch_assoc($sql)) {
        $bjusers[] = $row;
    }

    $HTMLOUT .= "
            <h3>Site Statistics</h3>";
    $header = "
                        <tr class='no_hover'>
                            <th>Username</th>
                            <th>Total</th>
                            <th>Wins</th>
                            <th>Losses</th>
                        </tr>";

    $body = '';
    foreach ($bjusers as $bjuser) {
        $body .= "
                        <tr class='no_hover'>
                            <td>
                                " . format_username($bjuser['id']) . "
                            </td>
                            <td>
                                " . human_filesize($bjuser['sum']) . "
                            </td>
                            <td>
                                " . human_filesize($bjuser['wins']) . "
                            </td>
                            <td>
                                " . human_filesize($bjuser['losses']) . "
                            </td>
                        </tr>";
    }
    $HTMLOUT .= main_table($body, $header);

    $last10 = 'SELECT * FROM blackjack_history WHERE game = ' . sqlesc($blackjack['gameid']) . ' ORDER BY id DESC LIMIT 10';
    $sql = sql_query($last10) or sqlerr(__FILE__, __LINE__);
    $bjgames = [];
    while ($row = mysqli_fetch_assoc($sql)) {
        $bjgames[] = $row;
    }

    if (count($bjgames) > 0) {
        $HTMLOUT .= "
                <table class='table table-bordered table-striped top20 bottom20'>
                    <thead>
                        <tr class='no_hover'>
                            <th colspan='2'>
                                <div class='has-text-centered'>
                                    <h3>Last " . count($bjgames) . " Games of {$blackjack['title']}</h3>
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody>";
        $g = 0;
        foreach ($bjgames as $bjgame) {
            $aces_0 = $aces_1 = $aces_2 = $aces_3 = $aces_4 = $aces_5 = $aces_6 = $aces_7 = $aces_8 = $aces_9 = $points_0 = $points_1 = $points_2 = $points_3 = $points_4 = $points_5 = $points_6 = $points_7 = $points_8 = $points_9 = 0;
            $HTMLOUT .= "
                        <tr class='no_hover'>
                            <td>";
            $cards_a = explode(' ', $bjgame['player1_cards']);
            foreach ($cards_a as $card_a) {
                $cardarr = getCardData($card_a);
                $HTMLOUT .= "
                                <div class='card {$cardarr['pic']}'></div>";
            }
            $HTMLOUT .= "
                            </td>
                            <td>";
            $cards_b = explode(' ', $bjgame['player2_cards']);
            foreach ($cards_b as $card_b) {
                $cardarr = getCardData($card_b);
                if ($cardarr['points'] > 1) {
                    ${'points_' . $g} += $cardarr['points'];
                } else {
                    ++${'aces_' . $g};
                }

                $HTMLOUT .= "
                                <div class='card {$cardarr['pic']}'></div>";
            }
            for ($h = 0; $h < $g + 1; ++$h) {
                for ($i = 0; $i < ${'aces_' . $h}; ++$i) {
                    ${'points_' . $h} += (${'points_' . $h} < 11 && ${'aces_' . $h} - $i == 1 ? 11 : 1);
                }
            }

            $HTMLOUT .= "
                            </td>
                        </tr>
                        <tr class='no_hover'>
                            <td>
                                <div class='has-text-centered'>
                                    " . format_username($bjgame['player1_userid']) . ': ' . $bjgame['player1_points'] . "
                                </div>div>
                            </td>
                            <td>
                                <div class='has-text-centered'>
                                    " . format_username($bjgame['player2_userid']) . ': ' . ${'points_' . $g} . '
                                </div>
                            </td>
                        </tr>';
            ++$g;
        }
        $HTMLOUT .= '
                    </tbody>
                </table>';
    }
    $HTMLOUT .= '
        </div>';
    output($blackjack, $HTMLOUT, $debugout);
}

/**
 * @param $cardid
 *
 * @return array|null|string
 */
function getCardData($cardid)
{
    global $cache;
    $card = $cache->get('card_data_' . $cardid);
    if ($card === false || is_null($card)) {
        $sql = 'SELECT * FROM cards WHERE id = ' . sqlesc($cardid);
        $res = sql_query($sql) or sqlerr(__FILE__, __LINE__);
        $card = mysqli_fetch_assoc($res);
        $cache->set('card_data_' . $cardid, $card, 0);
    }
    return $card;
}

/**
 * @param      $cardcount
 * @param      $gameid
 * @param bool $deal
 *
 * @return mixed
 */
function getCard($cardcount, $gameid, $deal = false)
{
    global $debugout, $blackjack;
    $debugout .= "
            <tr class='no_hover'>
                <td>deal</td>
                <td>blackjack.php:" . __LINE__ . "</td>
                <td>$deal</td>
            </tr>";
    $cards = [];
    $sql = 'SELECT cards FROM decks WHERE gameid = ' . sqlesc($gameid);
    $debugout .= "
            <tr class='no_hover'>
                <td>sql</td>
                <td>blackjack.php:" . __LINE__ . "</td>
                <td>$sql</td>
            </tr>";
    $res = sql_query($sql) or sqlerr(__FILE__, __LINE__);
    $card_str = mysqli_fetch_assoc($res);
    $card_str = $card_str['cards'];
    $debugout .= "
            <tr class='no_hover'>
                <td>cards string</td>
                <td>blackjack.php:" . __LINE__ . "</td>
                <td>$card_str</td>
            </tr>";
    if (!empty($card_str)) {
        $cards = explode(', ', $card_str);
    }
    $cardcount = count($cards);
    $debugout .= "
            <tr class='no_hover'>
                <td>card count</td>
                <td>blackjack.php:" . __LINE__ . "</td>
                <td>$cardcount</td>
            </tr>";
    if (empty($cards) || ($cardcount <= $blackjack['dead_cards'] && $deal)) {
        $cards = shuffle_decks();
        sql_query('UPDATE decks SET shuffled = shuffled + 1 WHERE gameid = ' . sqlesc($gameid)) or sqlerr(__FILE__, __LINE__);
    }
    $debugout .= '
            <tr class="no_hover">
                <td>cards - ready</td>
                <td>blackjack.php:' . __LINE__ . '</td>
                <td>' . json_encode($cards, JSON_PRETTY_PRINT) . '</td>
            </tr>';
    $cardid = $cards[0];
    array_splice($cards, 0, 1);
    $debugout .= '
            <tr class="no_hover">
                <td>cards - given</td>
                <td>blackjack.php:' . __LINE__ . '</td>
                <td>' . $cardid . '</td>
            </tr>
            <tr class="no_hover">
                <td>cards - card removed</td>
                <td>blackjack.php:' . __LINE__ . '</td>
                <td>' . json_encode($cards, JSON_PRETTY_PRINT) . '</td>
            </tr>';
    $card_str = implode(', ', $cards);
    $debugout .= '
            <tr class="no_hover">
                <td>cards string</td>
                <td>blackjack.php:' . __LINE__ . '</td>
                <td>' . $card_str . '</td>
            </tr>';
    $sql = 'INSERT INTO decks (gameid, cards) VALUES (' . sqlesc($gameid) . ', ' . sqlesc($card_str) . ') ON DUPLICATE KEY UPDATE cards = VALUES(cards)';
    $debugout .= '
            <tr class="no_hover">
                <td>sql</td>
                <td>blackjack.php:' . __LINE__ . '</td>
                <td>' . $sql . '</td>
            </tr>';
    sql_query($sql) or sqlerr(__FILE__, __LINE__);

    return $cardid;
}

/**
 * @param $blackjack
 * @param $HTMLOUT
 * @param $debugout
 */
function output($blackjack, $HTMLOUT, $debugout)
{
    global $CURUSER;
    $stdhead = [
        'css' => [
            get_file('bj_css'),
        ],
    ];

    $debugout = "
                <table id='last10List' class='table table-bordered table-striped top20 bottom20'>
                    <thead>
                        <tr class='no_hover'>
                            <th colspan='3'>
                                <div class='has-text-centered'>
                                    <h3>Debug Info</h3>
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody>                    
                        {$debugout}
                    </tbody>
                </table>";

    if (($CURUSER['class'] >= UC_SYSOP) && $blackjack['debug']) {
        $HTMLOUT = $HTMLOUT . $debugout;
    }

    echo stdhead($blackjack['title'], true, $stdhead) . wrapper($HTMLOUT) . stdfoot();
    exit();
}

/**
 * @return array
 */
function shuffle_decks()
{
    global $debugout, $blackjack;
    $cards = [];
    // build the shoe with x number of decks
    $sql = 'SELECT id FROM cards';
    $debugout .= "
            <tr class='no_hover'>
                <td>sql</td>
                <td>blackjack.php:' . __LINE__ . '</td>
                <td>$sql</td>
            </tr>";
    $res = sql_query($sql) or sqlerr(__FILE__, __LINE__);
    while ($getcards = mysqli_fetch_assoc($res)) {
        for ($x = 1; $x <= $blackjack['decks']; ++$x) {
            $cards[] = $getcards['id'];
        }
    }
    $debugout .= '
            <tr class="no_hover">
                <td>deck - created</td>
                <td>blackjack.php:' . __LINE__ . '</td>
                <td>' . json_encode($cards, JSON_PRETTY_PRINT) . '</td>
            </tr>';
    // shuffle the decks x number of times
    for ($x = 0; $x <= $blackjack['shuffle']; ++$x) {
        shuffle($cards);
    }
    $debugout .= '
            <tr class="no_hover">
                <td>deck - shuffled</td>
                <td>blackjack.php:' . __LINE__ . '</td>
                <td>' . json_encode($cards, JSON_PRETTY_PRINT) . '</td>
            </tr>';
    // cut the decks
    $split = random_int(20, 84);
    $split_deck = array_chunk($cards, $split);
    $temp_deck = [];
    // recombine the decks in reverse order of cut
    for ($x = (count($split_deck) - 1); $x >= 0; --$x) {
        $temp_deck = array_merge($temp_deck, $split_deck[ $x ]);
        $debugout .= '
            <tr class="no_hover">
                <td>deck - recombining</td>
                <td>blackjack.php:' . __LINE__ . '</td>
                <td>' . json_encode($temp_deck, JSON_PRETTY_PRINT) . '</td>
            </tr>';
    }
    $cards = $temp_deck;
    $debugout .= '
            <tr class="no_hover">
                <td>deck - cut</td>
                <td>blackjack.php:' . __LINE__ . '</td>
                <td>' . json_encode($cards, JSON_PRETTY_PRINT) . '</td>
            </tr>';
    // burn the first card
    array_splice($cards, 0, 1);
    $debugout .= '
            <tr class="no_hover">
                <td>deck - first card burned</td>
                <td>blackjack.php:' . __LINE__ . '</td>
                <td>' . json_encode($cards, JSON_PRETTY_PRINT) . '</td>
            </tr>';

    return $cards;
}
