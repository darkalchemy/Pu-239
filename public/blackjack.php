<?php

declare(strict_types = 1);

use DI\DependencyException;
use DI\NotFoundException;
use Pu239\Cache;
use Pu239\Message;
use Pu239\User;

require_once __DIR__ . '/../include/bittorrent.php';
require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_html.php';
$user = check_user_status();
global $container, $site_config;

$HTMLOUT = $debugout = '';
if ($user['class'] < $site_config['allowed']['play']) {
    stderr(_('Error'), _fe('Sorry, you must be a {0} to play blackjack!', $site_config['class_names'][$site_config['allowed']['play']]), 'bottom20');
} elseif ($user['game_access'] !== 1 || $user['status'] !== 0) {
    stderr(_('Error'), _('Your gaming rights have been disabled.'), 'bottom20');
}

$user_class = $container->get(User::class);
$blackjack['debug'] = false; // display debug info
$blackjack['decks'] = 2; // number of decks in shoe
$blackjack['dead_cards'] = 35; // number of cards remaining before shuffle
$blackjack['shuffle'] = random_int(1000, 20000); // number of time to shuffle the deck
$blackjack['allowed'] = [
    1,
    10,
    20,
    50,
    100,
    250,
    500,
    1000,
]; // games allowed
$blackjack['id'] = isset($_GET['id']) && in_array($_GET['id'], $blackjack['allowed']) ? (int) $_GET['id'] : 1;
$blackjack['gameid'] = array_search($blackjack['id'], $blackjack['allowed']) + 1;
$blackjack['version'] = "blackjack{$blackjack['gameid']}";
$blackjack['modifier'] = $blackjack['id'];  //default is 1 for 1 GB
$blackjack['min_uploaded'] = $blackjack['gameid']; // min to play * $blackjack['modifier']
$blackjack['title'] = _fe('Blackjack {0}GB', $blackjack['gameid']);
$blackjack['min'] = 5; // min upload credit that will required to play any game
$blackjack['max'] = 5120; // max upload credit that will required to play any game
$blackjack['gm'] = $blackjack['min_uploaded'] * $blackjack['modifier'];
$blackjack['required_ratio'] = 1; // min ratio that will required to play any game
$blackjack['mib'] = 1000; // MiB vs MB

// determine min upload credit required to play this game
if ($blackjack['gm'] < $blackjack['max'] && $blackjack['gm'] > $blackjack['min']) {
    $blackjack['quantity'] = $blackjack['gm'];
} elseif ($blackjack['gm'] > $blackjack['max']) {
    $blackjack['quantity'] = $blackjack['max'];
} else {
    $blackjack['quantity'] = $blackjack['min'];
}
$blackjack['min_text'] = mksize($blackjack['quantity'] * 1073741824);
$id = $blackjack['id'];

if ($user['uploaded'] < 1073741824 * $blackjack['quantity']) {
    stderr(_('Sorry'), _fe('You must have at least {0} upload credit to play.', $blackjack['min_text']));
}

$cardids2 = $dealer_cardids2 = $cards = $update = [];
$debugout .= '
            <tr class="no_hover">
                <td>_POST</td>
                <td>blackjack.php: ' . __LINE__ . '</td>
                <td>' . json_encode($_POST, JSON_PRETTY_PRINT) . '</td>
            </tr>
            <tr class="no_hover">
                <td>blackjack</td>
                <td>blackjack.php: ' . __LINE__ . '</td>
                <td>' . json_encode($blackjack, JSON_PRETTY_PRINT) . '</td>
            </tr>';

$ddown = false;
$update_ddown = "ddown = 'no'";
if (isset($_POST['ddown']) && $_POST['ddown'] === 'ddown') {
    $ddown = true;
    $update_ddown = "ddown = 'yes'";
}

$messages_class = $container->get(Message::class);
$cards_history = $dealer_cards_history = $deadcards = [];
$sql = 'SELECT b.*, u.username, u.class, u.id, u.gender FROM blackjack AS b INNER JOIN users AS u ON u.id=b.userid WHERE game_id = ' . sqlesc($blackjack['gameid']) . ' ORDER BY b.date LIMIT 1';
$res = sql_query($sql) or sqlerr(__FILE__, __LINE__);
$nick = mysqli_fetch_assoc($res);
$userName = empty($nick['username']) || $nick['username'] === $user['username'] ? "<span class='has-text-danger'><b>" . _('Dealer') . '</b></span>' : format_username((int) $nick['id']);
if ($nick['gender'] === 'Male') {
    $gender = 'he';
} elseif ($nick['gender'] === 'Female') {
    $gender = 'she';
} else {
    $gender = 'it';
}
$bjusers = [];
$cardsa = !empty($nick['cards']) ? $nick['cards'] : '';
$deadcards = explode(' ', $cardsa);
$doubleddown = !empty($nick['ddown']) && $nick['ddown'] === 'yes' ? true : false;

if ($user['id'] == $nick['userid'] && $nick['status'] === 'waiting') {
    stderr(_('Error'), _fe("Sorry {0}, you'll have to wait until another player plays your last game before you can play a new one.<br>You have {1}.<br>{2}Back{3}", format_username($user['id']), $nick['points'], "<a href='{$site_config['paths']['baseurl']}/games.php' class='button is-small margin20'>", '</a>'));
}
if ($user['id'] != $nick['userid'] && $nick['gameover'] === 'no') {
    stderr(_('Error'), _fe("Sorry {0}, you'll have to wait until {1} finishes {2} game before you can play a new one.<br>{3}Back{4}", format_username($user['id']), format_username((int) $nick['id']), $gender, "<a href='{$site_config['paths']['baseurl']}/games.php' class='button is-small margin20'>", '</a>'));
}
$opponent = isset($nick['username']) ? '<h3>' . _fe('Your Opponent is: {0}', format_username((int) $nick['id'])) . '</h3>' : '';
$required_ratio = 1.0;

$blackjack['mb'] = $blackjack['mib'] * $blackjack['mib'] * $blackjack['mib'] * $blackjack['modifier'];
$game_size = mksize($blackjack['mb']);
$link = '[url=' . $site_config['paths']['baseurl'] . '/blackjack.php?id=' . $id . ']Blackjack ' . $game_size . '[/url]';
$dt = TIME_NOW;
$game = isset($_POST['game']) ? htmlsafechars($_POST['game']) : '';
$start_ = isset($_POST['start_']) ? htmlsafechars($_POST['start_']) : '';

$player_showcards = $player_showcards_end = '';
$sql = 'SELECT cards FROM blackjack WHERE game_id = ' . sqlesc($blackjack['gameid']) . ' AND userid != ' . sqlesc($user['id']);
$res = sql_query($sql) or sqlerr(__FILE__, __LINE__);
$list = mysqli_fetch_assoc($res);
if (!empty($list) && count($list) > 0) {
    $player_cards = explode(' ', $list['cards']);
    foreach ($player_cards as $card) {
        $arr = getCardData($card);
        if ($card != $player_cards[0]) {
            $player_showcards .= "<div class='card {$arr['pic']}'></div>";
        } else {
            $player_showcards .= "<img src='{$site_config['paths']['images_baseurl']}back.png' width='71' height='97' alt='' alt='" . _('Cards') . "' title='" . _('Cards') . "' class='tooltipper tooltipper_img'>";
        }
        $player_showcards_end .= "<div class='card {$arr['pic']}'></div>";
    }
    $dealer = true;
    $user_warning = _('You are the dealer, you must take a card below 17.');
} else {
    $sql = 'SELECT dealer_cards FROM blackjack WHERE game_id = ' . sqlesc($blackjack['gameid']) . ' AND userid = ' . sqlesc($user['id']);
    $res = sql_query($sql) or sqlerr(__FILE__, __LINE__);
    $list = mysqli_fetch_assoc($res);
    if (!empty($list) && count($list) > 0) {
        $dealer_cards = explode(' ', $list['dealer_cards']);
        foreach ($dealer_cards as $card) {
            $arr = getCardData($card);
            if ($card != $dealer_cards[0]) {
                $player_showcards .= "<div class='card {$arr['pic']}'></div>";
            } else {
                $player_showcards .= "<img src='{$site_config['paths']['images_baseurl']}back.png' width='71' height='97' alt='' alt='" . _('Cards') . "' title='" . _('Cards') . "' class='tooltipper tooltipper_img'>";
            }
        }
        $player_showcards_end = $player_showcards;
    }
    $dealer = false;
    $user_warning = _('You are the player, you can double down with any opening hand worth 9, 10, 11.');
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
            die();
        }
    }

    $cardcount = 52;
    $points = $showcards = $aces = '';
    $User = $user_class->getUserFromId($user['id']);
    if ($start_ != 'yes') {
        $sql = 'SELECT * FROM blackjack WHERE game_id = ' . sqlesc($blackjack['gameid']) . ' AND userid = ' . sqlesc($user['id']);
        $playeres = sql_query($sql) or sqlerr(__FILE__, __LINE__);
        $playerarr = mysqli_fetch_assoc($playeres);
        if ($game === 'hit') {
            $points = $aces = 0;
        }
        $points = 0;
        $gameover = ($playerarr['gameover'] === 'yes' ? true : false);
        cheater_check($gameover && ($game === 'hit' ^ $game === 'stop'));
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

    if ($_POST['game'] === 'hit') {
        if ($start_ === 'yes') {
            if ($user['uploaded'] < $blackjack['mb']) {
                stderr(_('Error') . _fe("Sorry {0}, you haven't uploaded {1} yet.", format_username($user['id']), mksize($blackjack['mb'])), 'bottom20');
            }
            if ($user['downloaded'] > 0) {
                $ratio = number_format($user['uploaded'] / $user['downloaded'], 3);
            } elseif ($user['uploaded'] > 0) {
                $ratio = 999;
            } else {
                $ratio = 0;
            }
            if (!$site_config['site']['ratio_free'] && $ratio < $required_ratio) {
                stderr(_('Error') . _fe('Sorry {0}, your ratio is lower than the requirement of {1}%', format_username($user['id']), $required_ratio), 'bottom20');
            }
            $sql = 'SELECT * FROM blackjack WHERE userid = ' . sqlesc($user['id']) . ' AND game_id = ' . sqlesc($blackjack['gameid']);
            $res = sql_query($sql) or sqlerr(__FILE__, __LINE__);
            $arr = mysqli_fetch_assoc($res);
            if ($arr['status'] === 'waiting') {
                stderr(_('Sorry'), _("You'll have to wait until your last game completes before you play a new one."), 'bottom20');
            } elseif ($arr['status'] === 'playing') {
                stderr(_('Sorry'), _('You must finish your old game first.') . "
                    <form method='post' action='" . $_SERVER['PHP_SELF'] . "?id=$id' enctype='multipart/form-data' accept-charset='utf-8'>
                        <input type='hidden' name='game' value='hit' readonly='readonly'>
                        <input type='hidden' name='continue' value='yes' readonly='readonly'>
                        <div class='has-text-centered top20'>
                            <input class='button is-small' type='submit' value='" . _('Continue old game') . "'>
                        </div>
                    </form>");
            }
            cheater_check($arr['gameover'] === 'yes');
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
                    <img src='{$site_config['paths']['images_baseurl']}back.png' width='71' height='97' alt='' alt='" . _('Cards') . "' title='" . _('Cards') . "' class='tooltipper tooltipper_img'>";
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
                $sql = 'SELECT cards, dealer_cards FROM blackjack WHERE game_id = ' . sqlesc($blackjack['gameid']) . ' AND userid != ' . sqlesc($user['id']);
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

            $sql = 'INSERT INTO blackjack (userid, points, cards, date, dealer_cards, game_id) VALUES (' . sqlesc($user['id']) . ', ' . sqlesc($points) . ', ' . sqlesc(implode(' ', $cardids2)) . ', ' . sqlesc($dt) . ', ' . sqlesc(implode(' ', $dealer_cardids2)) . ', ' . sqlesc($blackjack['gameid']) . ')';
            sql_query($sql) or sqlerr(__FILE__, __LINE__);
            if ($points < 21) {
                $HTMLOUT .= "
                <a id='blackjack-hash'></a>
                <h3>" . _('Welcome') . ', ' . format_username($user['id']) . "</h3>
                    <table class='table table-bordered table-striped top20 bottom20'>
                        <tr class='no_hover'>
                            <td class='card-background ww-50'>
                                <div class='has-text-centered'>
                                    " . trim($player_showcards) . "
                                </div>
                            </td>
                            <td class='card-background ww-50'>
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
                            <td>" . format_username($user['id']) . '<br>' . _('Points') . " = {$points}<br>{$user_warning}</td>
                        </tr>";
                if (!$ddown) {
                    $HTMLOUT .= "
                        <tr class='no_hover'>
                            <td colspan='2'>
                                <form method='post' action='" . $_SERVER['PHP_SELF'] . "?id=$id' enctype='multipart/form-data' accept-charset='utf-8'>
                                    <input type='hidden' name='game' value='hit' readonly='readonly'>
                                    <div class='has-text-centered'>
                                        <input class='button is-small' type='submit' value='" . _('Hit Me') . "'>
                                    </div>
                                </form>
                            </td>
                        </tr>";
                }
                if ($points >= 17 && $dealer || $points >= 10 && !$dealer) {
                    $HTMLOUT .= "
                        <tr class='no_hover'>
                            <td colspan='2'>
                                <form method='post' action='" . $_SERVER['PHP_SELF'] . "?id=$id' enctype='multipart/form-data' accept-charset='utf-8'>
                                    <input type='hidden' name='game' value='stop' readonly='readonly'>
                                    <div class='has-text-centered'>
                                        <input class='button is-small' type='submit' value='" . _('Stay') . "'>
                                    </div>
                                </form>
                            </td>
                        </tr>";
                }
                if ($points >= 9 && $points <= 11 && $numCards === 2 && !$dealer) {
                    $HTMLOUT .= "
                        <tr class='no_hover'>
                            <td colspan='2'>
                                <form method='post' action='" . $_SERVER['PHP_SELF'] . "?id=$id' enctype='multipart/form-data' accept-charset='utf-8'>
                                    <input type='hidden' name='ddown' value='ddown' readonly='readonly'>
                                    <input type='hidden' name='game' value='hit' readonly='readonly'>
                                    <div class='has-text-centered'>
                                        <input class='button is-small' type='submit' value='Double Down'>
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
            //  $cardid=getCard($cardcount, $blackjack['gameid']);
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
            $sql = "UPDATE blackjack SET $update_ddown, points = " . sqlesc($points) . ", cards = '" . $cards . ' ' . $cardid . "' WHERE game_id=" . sqlesc($blackjack['gameid']) . ' AND userid=' . sqlesc($user['id']);
            sql_query($sql) or sqlerr(__FILE__, __LINE__);
        }
        if ($points == 21 || $points > 21) {
            $sql = 'SELECT COUNT(userid) AS c FROM blackjack WHERE game_id=' . sqlesc($blackjack['gameid']) . " AND status = 'waiting' AND userid != " . sqlesc($user['id']);
            $res = sql_query($sql) or sqlerr(__FILE__, __LINE__);
            $waitarr = mysqli_fetch_assoc($res);
            $HTMLOUT .= "
                <a id='blackjack-hash'></a>
                <h3>" . _('Game over') . "</h3>
                <table class='table table-bordered table-striped top20 bottom20'>
                    <tr class='no_hover'>
                        <td class='card-background ww-50'>
                            <div class='has-text-centered'>
                                {$player_showcards_end}
                            </div>
                        </td>
                        <td class='card-background ww-50'>
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
                                " . format_username($user['id']) . '<br>' . _('Points') . " = {$points}<br>{$user_warning}
                            </div>
                        </td>
                    </tr>
                    <tr class='no_hover'>
                        <td colspan='2'>";
        }
        if ($points == 21) {
            if ($waitarr['c'] > 0) {
                $sql = 'SELECT b.*, u.username, u.class, u.id, u.uploaded, u.downloaded, u.bjwins, u.bjlosses FROM blackjack AS b LEFT JOIN users AS u ON u.id=b.userid WHERE game_id=' . sqlesc($blackjack['gameid']) . " AND b.status = 'waiting' AND b.userid != " . sqlesc($user['id']) . ' ORDER BY b.date LIMIT 1';
                $res = sql_query($sql) or sqlerr(__FILE__, __LINE__);
                $a = mysqli_fetch_assoc($res);
                $points_text = htmlsafechars($a['points']) . ' ' . _('points') . '';
                $card_count = count(explode(' ', $a['cards']));
                $dbl_text = '';
                if ($a['ddown'] === 'yes') {
                    $blackjack['mb'] = $blackjack['mb'] * 2;
                    $blackjack['modifier'] = $blackjack['modifier'] * 2;
                    $dbl_text = '[' . _('Doubled Down') . ']';
                    $doubleddown = true;
                } elseif ($card_count === 2 && $a['points'] == 21 && $playerarr['points'] != 21) {
                    $blackjack['mb'] = $blackjack['mb'] * 1.5;
                    $blackjack['modifier'] = $blackjack['modifier'] * 1.5;
                    $points_text = 'Blackjack';
                }
                if ($a['points'] != 21) {
                    $winorlose = _('you won') . ' ' . mksize($blackjack['mb']);
                    $sql = "UPDATE users SET uploaded = uploaded + {$blackjack['mb']}, bjwins = bjwins + {$blackjack['modifier']} WHERE id=" . sqlesc($user['id']);
                    sql_query($sql) or sqlerr(__FILE__, __LINE__);

                    $sql = "UPDATE users SET uploaded = uploaded - {$blackjack['mb']}, bjlosses = bjlosses + {$blackjack['modifier']} WHERE id=" . sqlesc($a['userid']);
                    sql_query($sql) or sqlerr(__FILE__, __LINE__);

                    $update['uploaded'] = $User['uploaded'] + $blackjack['mb'];
                    $update['uploaded_loser'] = $a['uploaded'] - $blackjack['mb'];
                    $update['bjwins'] = $User['bjwins'] + $blackjack['modifier'];
                    $update['bjlosses'] = $a['bjlosses'] + $blackjack['modifier'];

                    //==stats
                    // winner $user
                    $cache->update_row('user_' . $user['id'], [
                        'uploaded' => $update['uploaded'],
                        'bjwins' => $update['bjwins'],
                    ], $site_config['expires']['user_cache']);
                    // loser $a
                    $cache->update_row('user_' . $a['userid'], [
                        'uploaded' => $update['uploaded_loser'],
                        'bjlosses' => $update['bjlosses'],
                    ], $site_config['expires']['user_cache']);

                    $lost_str = str_replace('10GB', mksize($blackjack['mb']), _('You lost 10GB to'));
                    $msg = _fe("Blackjack {0}: {1} {2} (You had {3} point, {4} had {6} points\n", $game_size, $lost_str, $user['username'], $a['points'], $user['username'], 21);
                    $subject = _('Blackjack Results');
                    $outcome = _fe('{0}and won', $dbl_text);
                } else {
                    $subject = _('Blackjack Results');
                    $winorlose = _('nobody won');
                    $msg = _fe("Blackjack {0}: You tied with {1} You both had {2} points\n", $game_size, $user['username'], $a['points']);
                    $outcome = _fe('{0}and tied', $dbl_text);
                }
                $msgs_buffer[] = [
                    'receiver' => $a['userid'],
                    'added' => $dt,
                    'msg' => $msg,
                    'subject' => $subject,
                ];
                $messages_class->insert($msgs_buffer);

                if ($site_config['site']['autoshout_chat'] || $site_config['site']['autoshout_irc']) {
                    $classColor = get_user_class_color($user['class']);
                    $opponent = get_user_class_color((int) $a['class']);
                    $msg = "[color=#$classColor]" . format_comment($user['username']) . "[/color] has just played [color=#$opponent]" . format_comment($a['username']) . "[/color] $outcome ($points to {$a['points']}) $link.";
                    for ($i = 0; $i < $aces; ++$i) {
                        $points += ($points < 11 && $aces - $i == 1 ? 11 : 1);
                    }
                    $sql = 'INSERT INTO blackjack_history (`date`, game, player1_userid, player1_points, player1_cards, player2_points, player2_userid, player2_cards) VALUES (UNIX_TIMESTAMP(NOW()), ' . sqlesc($blackjack['gameid']) . ', ' . sqlesc($a['userid']) . ', ' . sqlesc($a['points']) . ', ' . sqlesc($a['cards']) . ', ' . sqlesc($points) . ', ' . sqlesc($user['id']) . ', ' . sqlesc(implode(' ', $cards_history)) . ')';
                    sql_query($sql) or sqlerr(__FILE__, __LINE__);
                    autoshout($msg);
                }
                $sql = 'DELETE FROM blackjack WHERE game_id=' . sqlesc($blackjack['gameid']) . ' AND userid IN (' . sqlesc($user['id']) . ', ' . sqlesc($a['userid']) . ')';
                sql_query($sql) or sqlerr(__FILE__, __LINE__);
                $HTMLOUT .= "
                        <div class='has-text-centered'>
                            " . _('Your opponent was') . ' ' . format_username((int) $a['id']) . ", $gender had $points_text, $winorlose.
                        </div>
                        <p class='has-text-centered top20'>
                            <a href='{$site_config['paths']['baseurl']}/blackjack.php?id=$id' class='button is-small right10'>" . _('Play again') . "</a>
                            <a href='{$site_config['paths']['baseurl']}/games.php' class='button is-small'>Games</a>
                        </p>";
            } else {
                $sql = "UPDATE blackjack SET $update_ddown, status = 'waiting', date = " . $dt . ", gameover = 'yes' WHERE game_id=" . sqlesc($blackjack['gameid']) . ' AND userid=' . sqlesc($user['id']);
                sql_query($sql) or sqlerr(__FILE__, __LINE__);
                if ($site_config['site']['autoshout_chat'] || $site_config['site']['autoshout_irc']) {
                    $classColor = get_user_class_color($user['class']);
                    $msg = "[color=#$classColor]" . format_comment($user['username']) . "[/color] has just played $link.";
                    autoshout($msg);
                }
                $HTMLOUT .= "
                        <div class='has-text-centered top20'>
                            " . _("There are no other players, so you'll have to wait until someone plays against you.<br>You will receive a PM with the game results.") . "<br>
                            <a href='{$site_config['paths']['baseurl']}/games.php' class='button is-small top20'>" . _('Back') . '</a>
                        </div>';
            }
            $HTMLOUT .= '
                        </td>
                    </tr>
                </table>
            </div>';
            output($blackjack, $HTMLOUT, $debugout);
        } elseif ($points > 21) {
            if ($waitarr['c'] > 0) {
                $sql = 'SELECT b.*, u.username, u.class, u.id, u.uploaded, u.downloaded, u.bjwins, u.bjlosses FROM blackjack AS b LEFT JOIN users AS u ON u.id=b.userid WHERE b.game_id=' . sqlesc($blackjack['gameid']) . " AND b.status = 'waiting' AND b.userid != " . sqlesc($user['id']) . ' ORDER BY b.date LIMIT 1';
                $res = sql_query($sql) or sqlerr(__FILE__, __LINE__);
                $a = mysqli_fetch_assoc($res);
                $points_text = htmlsafechars($a['points']) . ' ' . _('points') . '';
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
                    $subject = _('Blackjack Results');
                    $winorlose = _('nobody won');
                    $msg = "Blackjack $game_size: " . _('You tied with') . ' ' . $user['username'] . ' (' . _('You both had') . ' ' . $a['points'] . " points).\n\n";
                    $outcome = "{$dbl_text}and busted";
                } else {
                    $subject = _('Blackjack Results');
                    $winorlose = _('you lost') . ' ' . mksize($blackjack['mb']);

                    $sql = "UPDATE users SET uploaded = uploaded + {$blackjack['mb']}, bjwins = bjwins + {$blackjack['modifier']} WHERE id=" . sqlesc($a['userid']);
                    sql_query($sql) or sqlerr(__FILE__, __LINE__);

                    $sql = "UPDATE users SET uploaded = uploaded - {$blackjack['mb']}, bjlosses = bjlosses + {$blackjack['modifier']} WHERE id=" . sqlesc($user['id']);
                    sql_query($sql) or sqlerr(__FILE__, __LINE__);

                    $update['uploaded'] = ($a['uploaded'] + $blackjack['mb']);
                    $update['uploaded_loser'] = ($User['uploaded'] - $blackjack['mb']);
                    $update['bjwins'] = ($a['bjwins'] + $blackjack['modifier']);
                    $update['bjlosses'] = ($User['bjlosses'] + $blackjack['modifier']);

                    //==stats
                    // winner $a
                    $cache->update_row('user_' . $a['userid'], [
                        'uploaded' => $update['uploaded'],
                        'bjwins' => $update['bjwins'],
                    ], $site_config['expires']['user_cache']);

                    // loser $user
                    $cache->update_row('user_' . $user['id'], [
                        'uploaded' => $update['uploaded_loser'],
                        'bjlosses' => $update['bjlosses'],
                    ], $site_config['expires']['user_cache']);

                    $won_str = str_replace('10GB', mksize($blackjack['mb']), _('You won 10GB and beat'));
                    $msg = "Blackjack $game_size: $won_str " . $user['username'] . ' (' . _('You had') . ' ' . $a['points'] . ' ' . _('points') . ', ' . $user['username'] . " had $points points).\n\n";
                    $outcome = "{$dbl_text}and lost";
                }
                $msgs_buffer[] = [
                    'receiver' => $a['userid'],
                    'added' => $dt,
                    'msg' => $msg,
                    'subject' => $subject,
                ];
                $messages_class->insert($msgs_buffer);

                if ($site_config['site']['autoshout_chat'] || $site_config['site']['autoshout_irc']) {
                    $classColor = get_user_class_color($user['class']);
                    $opponent = get_user_class_color((int) $a['class']);
                    $msg = "[color=#$classColor]" . format_comment($user['username']) . "[/color] has just played [color=#$opponent]" . format_comment($a['username']) . "[/color] $outcome ($points to {$a['points']}) $link.";
                    for ($i = 0; $i < $aces; ++$i) {
                        $points += ($points < 11 && $aces - $i == 1 ? 11 : 1);
                    }
                    $sql = 'INSERT INTO blackjack_history (`date`, game, player1_userid, player1_points, player1_cards, player2_points, player2_userid, player2_cards) VALUES (UNIX_TIMESTAMP(NOW()), ' . sqlesc($blackjack['gameid']) . ', ' . sqlesc($a['userid']) . ', ' . sqlesc($a['points']) . ', ' . sqlesc($a['cards']) . ', ' . sqlesc($points) . ', ' . sqlesc($user['id']) . ', ' . sqlesc(implode(' ', $cards_history)) . ')';
                    sql_query($sql) or sqlerr(__FILE__, __LINE__);
                    autoshout($msg);
                }
                $sql = 'DELETE FROM blackjack WHERE game_id=' . sqlesc($blackjack['gameid']) . ' AND userid IN (' . sqlesc($user['id']) . ', ' . sqlesc($a['userid']) . ')';
                sql_query($sql) or sqlerr(__FILE__, __LINE__);

                $HTMLOUT .= "
                        <div class='has-text-centered'>
                            " . _('Your opponent was') . ' ' . format_username((int) $a['id']) . ", $gender had $points_text, $winorlose.
                        </div>
                        <p class='has-text-centered top20'>
                            <a href='{$site_config['paths']['baseurl']}/blackjack.php?id=$id' class='button is-small right10'>" . _('Play again') . "</a>
                            <a href='{$site_config['paths']['baseurl']}/games.php' class='button is-small'>Games</a>
                        </p>";
            } else {
                $sql = "UPDATE blackjack SET $update_ddown, status = 'waiting', date = " . $dt . ", gameover = 'yes' WHERE game_id=" . sqlesc($blackjack['gameid']) . ' AND userid=' . sqlesc($user['id']);
                sql_query($sql) or sqlerr(__FILE__, __LINE__);

                if ($site_config['site']['autoshout_chat'] || $site_config['site']['autoshout_irc']) {
                    $classColor = get_user_class_color($user['class']);
                    $msg = "[color=#$classColor]" . format_comment($user['username']) . "[/color] has just played $link.";
                    autoshout($msg);
                }
                $HTMLOUT .= "
                        <div class='has-text-centered top20'>
                            " . _("There are no other players, so you'll have to wait until someone plays against you.<br>You will receive a PM with the game results.") . "<br>
                            <a href='{$site_config['paths']['baseurl']}/games.php' class='button is-small top20'>" . _('Back') . '</a>
                        </div>';
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
                <h3>" . _('Welcome') . ', ' . format_username($user['id']) . "</h3>
                <table class='table table-bordered table-striped top20 bottom20'>
                    <tr class='no_hover'>
                        <td class='card-background ww-50'>
                            <div class='has-text-centered'>
                                {$player_showcards}
                            </div>
                        </td>
                        <td class='card-background ww-50'>
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
                                " . format_username($user['id']) . '<br>' . _('Points') . " = {$points}<br>{$user_warning}
                            </div>
                        </td>
                    </tr>";
            if (!$ddown) {
                $HTMLOUT .= "
                    <tr class='no_hover'>
                        <td colspan='2'>
                            <form method='post' action='" . $_SERVER['PHP_SELF'] . "?id=$id' enctype='multipart/form-data' accept-charset='utf-8'>
                                <input type='hidden' name='game' value='hit' readonly='readonly'>
                                <div class='has-text-centered'>
                                    <input class='button is-small' type='submit' value='" . _('Hit Me') . "'>
                                </div>
                            </form>
                        </td>
                    </tr>";
            }
            if ($points >= 17 && $dealer || $points >= 10 && !$dealer) {
                $HTMLOUT .= "
                    <tr class='no_hover'>
                        <td colspan='2'>
                            <form method='post' action='" . $_SERVER['PHP_SELF'] . "?id=$id' enctype='multipart/form-data' accept-charset='utf-8'>
                                <input type='hidden' name='game' value='stop' readonly='readonly'>
                                <div class='has-text-centered'>
                                    <input class='button is-small' type='submit' value='" . _('Stay') . "'>
                                </div>
                            </form>
                        </td>
                    </tr>";
            }
            if ($points >= 9 && $points <= 11 && $numCards === 2 && !$dealer) {
                $HTMLOUT .= "
                    <tr class='no_hover'>
                        <td colspan='2'>
                            <form method='post' action='" . $_SERVER['PHP_SELF'] . "?id=$id' enctype='multipart/form-data' accept-charset='utf-8'>
                                <input type='hidden' name='ddown' value='ddown' readonly='readonly'>
                                <input type='hidden' name='game' value='hit' readonly='readonly'>
                                <div class='has-text-centered'>
                                    <input class='button is-small' type='submit' value='Double Down'>
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
        $sql = 'SELECT COUNT(userid) AS c FROM blackjack WHERE game_id = ' . sqlesc($blackjack['gameid']) . " AND status = 'waiting' AND userid != " . sqlesc($user['id']);
        $res = sql_query($sql) or sqlerr(__FILE__, __LINE__);
        $waitarr = mysqli_fetch_assoc($res);
        $HTMLOUT .= "
                <a id='blackjack-hash'></a>
                <h3>" . _('Game over') . "</h3>
                <table class='table table-bordered table-striped top20 bottom20'>
                    <tr class='no_hover'>
                        <td class='card-background ww-50'>
                            <div class='has-text-centered'>
                                {$player_showcards_end}
                            </div>
                        </td>
                        <td class='card-background ww-50'>
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
                                " . format_username($user['id']) . '<br>' . _('Points') . " = {$playerarr['points']}
                            </div>
                        </td>
                    </tr>
                    <tr class='no_hover'>
                        <td colspan='2'>";
        //{$user_warning}";
        if ($waitarr['c'] > 0) {
            $sql = 'SELECT b.*, u.username, u.class, u.id, u.uploaded, u.downloaded, u.bjwins, u.bjlosses FROM blackjack AS b LEFT JOIN users AS u ON u.id=b.userid WHERE b.game_id=' . sqlesc($blackjack['gameid']) . " AND b.status = 'waiting' AND b.userid != " . sqlesc($user['id']) . ' ORDER BY b.date LIMIT 1';
            $res = sql_query($sql) or sqlerr(__FILE__, __LINE__);
            $a = mysqli_fetch_assoc($res);
            $points_text = htmlsafechars($a['points']) . ' ' . _('points') . '';
            $card_count = count(explode(' ', $a['cards']));
            $dbl_text = '';
            if ($a['ddown'] === 'yes') {
                $blackjack['mb'] = $blackjack['mb'] * 2;
                $blackjack['modifier'] = $blackjack['modifier'] * 2;
                $dbl_text = _('[Doubled Down]');
                $doubleddown = true;
            } elseif ($card_count === 2 && $a['points'] == 21 && $playerarr['points'] != 21) {
                $blackjack['mb'] = $blackjack['mb'] * 1.5;
                $blackjack['modifier'] = $blackjack['modifier'] * 1.5;
                $points_text = _('Blackjack');
            }
            if ($a['points'] == $playerarr['points']) {
                $subject = _('Blackjack Results');
                $winorlose = _('nobody won');
                $msg = _fe('Blackjack {0}: Your opponent was {1}. You both had {2} - it was a tie.', $game_size, $user['username'], $points_text) . "\n\n";
                $outcome = _fe('{0} and tied', $dbl_text);
            } else {
                // winner $user
                if (($a['points'] < $playerarr['points'] && $a['points'] < 21) || ($a['points'] > $playerarr['points'] && $a['points'] > 21)) {
                    $subject = _('Blackjack Results');
                    $lost_str = str_replace('10GB', mksize($blackjack['mb']), _('You lost 10GB to'));
                    $msg = _fe('Blackjack {0}: {1} {2} (You had {3} points, {4} had {5} points.)', $game_size, $lost_str, $user['username'], (int) $a['points'], $user['username'], (int) $playerarr['points']) . "\n\n";
                    $winorlose = _('you won') . ' ' . mksize($blackjack['mb']);
                    $st_query = '+ ' . $blackjack['mb'] . ', bjwins = bjwins +';
                    $nd_query = '- ' . $blackjack['mb'] . ', bjlosses = bjlosses +';
                    $update['uploaded'] = ($User['uploaded'] + $blackjack['mb']);
                    $update['uploaded_loser'] = ($a['uploaded'] - $blackjack['mb']);
                    $update['bjwins'] = ($User['bjwins'] + $blackjack['modifier']);
                    $update['bjlosses'] = ($a['bjlosses'] + $blackjack['modifier']);
                    $update['winnerid'] = $playerarr['userid'];
                    $update['loserid'] = $a['userid'];
                    $outcome = _fe('{0} and won', $dbl_text);
                // loser $user
                } elseif (($a['points'] > $playerarr['points'] && $a['points'] < 21) || $a['points'] == 21 || ($a['points'] < $playerarr['points'] && $a['points'] > 21)) {
                    $subject = _('Blackjack Results');
                    $won_str = str_replace('10GB', mksize($blackjack['mb']), _('You won 10GB and beat'));
                    $msg = _fe('Blackjack {0}: {1} {2} (You had {3} points, {4} had {5} points.)', $game_size, $won_str, $user['username'], (int) $a['points'], $user['username'], (int) $playerarr['points']) . "\n\n";
                    $winorlose = _('you lost') . ' ' . mksize($blackjack['mb']);
                    $st_query = '- ' . $blackjack['mb'] . ', bjlosses = bjlosses +';
                    $nd_query = '+ ' . $blackjack['mb'] . ', bjwins = bjwins +';
                    $update['uploaded'] = ($a['uploaded'] + $blackjack['mb']);
                    $update['uploaded_loser'] = ($User['uploaded'] - $blackjack['mb']);
                    $update['bjwins'] = ($a['bjwins'] + $blackjack['modifier']);
                    $update['bjlosses'] = ($User['bjlosses'] + $blackjack['modifier']);
                    $update['winnerid'] = $a['userid'];
                    $update['loserid'] = $playerarr['userid'];
                    $outcome = _fe('{0} and lost', $dbl_text);
                }

                $sql = 'UPDATE users SET uploaded = uploaded ' . $st_query . " {$blackjack['modifier']} WHERE id=" . sqlesc($user['id']);
                sql_query($sql) or sqlerr(__FILE__, __LINE__);

                $sql = 'UPDATE users SET uploaded = uploaded ' . $nd_query . " {$blackjack['modifier']} WHERE id=" . sqlesc($a['userid']);
                sql_query($sql) or sqlerr(__FILE__, __LINE__);

                //==stats
                $cache->update_row('user_' . $update['winnerid'], [
                    'uploaded' => $update['uploaded'],
                    'bjwins' => $update['bjwins'],
                ], $site_config['expires']['user_cache']);

                $cache->update_row('user_' . $update['loserid'], [
                    'uploaded' => $update['uploaded_loser'],
                    'bjlosses' => $update['bjlosses'],
                ], $site_config['expires']['user_cache']);
            }

            $msgs_buffer[] = [
                'receiver' => $a['userid'],
                'added' => $dt,
                'msg' => $msg,
                'subject' => $subject,
            ];
            $messages_class->insert($msgs_buffer);

            if ($site_config['site']['autoshout_chat'] || $site_config['site']['autoshout_irc']) {
                $classColor = get_user_class_color($user['class']);
                $opponent = get_user_class_color((int) $a['class']);
                $msg = "[color=#$classColor]" . format_comment($user['username']) . "[/color] has just played [color=#$opponent]" . format_comment($a['username']) . "[/color] $outcome ({$playerarr['points']} to {$a['points']}) $link.";
                autoshout($msg);
            }

            for ($i = 0; $i < $aces; ++$i) {
                $points += ($points < 11 && $aces - $i == 1 ? 11 : 1);
            }
            $sql = 'INSERT INTO blackjack_history (`date`, game, player1_userid, player1_points, player1_cards, player2_points, player2_userid, player2_cards) VALUES (UNIX_TIMESTAMP(NOW()), ' . sqlesc($blackjack['gameid']) . ', ' . sqlesc($a['userid']) . ', ' . sqlesc($a['points']) . ', ' . sqlesc($a['cards']) . ', ' . sqlesc($points) . ', ' . sqlesc($user['id']) . ', ' . sqlesc(implode(' ', $cards_history)) . ')';
            sql_query($sql) or sqlerr(__FILE__, __LINE__);

            $sql = 'DELETE FROM blackjack WHERE game_id = ' . sqlesc($blackjack['gameid']) . ' AND userid IN (' . sqlesc($user['id']) . ', ' . sqlesc($a['userid']) . ')';
            sql_query($sql) or sqlerr(__FILE__, __LINE__);

            $HTMLOUT .= "
                        <div class='has-text-centered'>
                            " . _('Your opponent was') . ' ' . format_username((int) $a['id']) . ", $gender had $points_text, $winorlose.
                        </div>
                        <p class='has-text-centered top20'>
                            <a href='{$site_config['paths']['baseurl']}/blackjack.php?id=$id' class='button is-small right10'>" . _('Play again') . "</a>
                            <a href='{$site_config['paths']['baseurl']}/games.php' class='button is-small'>Games</a>
                        </p>";
        } else {
            $sql = "UPDATE blackjack SET $update_ddown, status = 'waiting', date = " . $dt . ", gameover = 'yes' WHERE game_id=" . sqlesc($blackjack['gameid']) . ' AND userid=' . sqlesc($user['id']);
            sql_query($sql) or sqlerr(__FILE__, __LINE__);

            if ($site_config['site']['autoshout_chat'] || $site_config['site']['autoshout_irc']) {
                $classColor = get_user_class_color($user['class']);
                $msg = "[color=#$classColor]" . format_comment($user['username']) . "[/color] has just played $link.";
                autoshout($msg);
            }
            $HTMLOUT .= "
                        <div class='has-text-centered'>
                            " . _("There are no other players, so you'll have to wait until someone plays against you.<br>You will receive a PM with the game results.") . "<br>
                            <a href='{$site_config['paths']['baseurl']}/games.php' class='button is-small top20'>" . _('Back') . '</a>
                        </div>';
        }
        $HTMLOUT .= '
                        </td>
                    </tr>
                </table>
            </div>';
        output($blackjack, $HTMLOUT, $debugout);
    }
} else {
    $sql = 'SELECT bjwins, bjlosses FROM users WHERE id = ' . sqlesc($user['id']);
    $res = sql_query($sql) or sqlerr(__FILE__, __LINE__);
    $User = mysqli_fetch_assoc($res);
    $User['bjwins'] = (int) $User['bjwins'] * $blackjack['mib'] * $blackjack['mib'] * $blackjack['mib'];
    $User['bjlosses'] = (int) $User['bjlosses'] * $blackjack['mib'] * $blackjack['mib'] * $blackjack['mib'];
    $tot_wins = (int) $User['bjwins'];
    $tot_losses = (int) $User['bjlosses'];
    $tot_games = $tot_wins + $tot_losses;
    $win_perc = ($tot_losses == 0 ? ($tot_wins == 0 ? '---' : '100%') : ($tot_wins == 0 ? 0 : number_format(($tot_wins / $tot_games) * 100, 1)) . '%');
    $plus_minus = $tot_wins - abs($tot_losses);
    $sql = 'SELECT * FROM blackjack WHERE game_id=' . sqlesc($blackjack['gameid']) . ' ORDER BY date LIMIT 1';
    $result = sql_query($sql) or sqlerr(__FILE__, __LINE__);
    $res = mysqli_fetch_assoc($result);
    $doubled = '';
    if ($res['ddown'] === 'yes') {
        $blackjack['mb'] = $blackjack['mb'] * 2;
        $doubled = "
            <tr class='no_hover'>
                <td>
                    <div class='has-text-centered'>" . _fe('{0} has Doubled Down, thereby doubling the bet to {1}.', format_username((int) $nick['id']), mksize($blackjack['mb'])) . '</div>
                </td>
            </tr>';
    }
    $game_str = str_replace('10GB', mksize($blackjack['mb']), _('By playing blackjack, you are betting 10GB of upload credit!'));

    $HTMLOUT .= "
                <a id='blackjack-hash'></a>
                <h2><a href='{$site_config['paths']['baseurl']}/games.php' title='" . _('Return to the Games') . "' class='tooltipper'>{$blackjack['title']}</a></h2>
                $opponent
                <table class='table table-bordered table-striped top20 bottom20'>
                    <tr class='no_hover'>
                        <td class='card-background ww-50'>
                            <div class='has-text-centered'>
                                <div class='card ace_spades'></div>
                                <div class='card jack_spades'></div>
                            </div>
                        </td>
                    </tr>
                    $doubled
                    <tr class='no_hover'>
                        <td>
                            <p class='has-text-centered'>" . _('You must collect 21 points without going over.') . "</p>
                            <p class='has-text-centered'>" . _('NOTE') . ": {$game_str}</p>
                        </td>
                    </tr>
                    <tr class='no_hover'>
                        <td>
                            <form method='post' action='" . $_SERVER['PHP_SELF'] . "?id=$id' enctype='multipart/form-data' accept-charset='utf-8'>
                                <input type='hidden' name='game' value='hit' readonly='readonly'>
                                <input type='hidden' name='start_' value='yes' readonly='readonly'>
                                <div class='has-text-centered'>
                                    <input class='button is-small' type='submit' value='Start!'>
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
                                    <h3>" . _('Personal Statistics') . "</h3>
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class='no_hover'>
                            <td>" . _('Wins') . '</td>
                            <td>' . mksize($tot_wins) . "</td>
                        </tr>
                        <tr class='no_hover'>
                            <td>" . _('Losses') . '</td>
                            <td>' . mksize($tot_losses) . "</td>
                        </tr>
                        <tr class='no_hover'>
                            <td>" . _('Win') . ' ' . _('Percentage') . '</td>
                            <td>' . htmlsafechars($win_perc) . "</td>
                        </tr>
                        <tr class='no_hover'>
                            <td>+/-</td>
                            <td>" . mksize($plus_minus) . '</td>
                        </tr>
                </table>';
    // site stats
    $gig = (int) $blackjack['mib'] * $blackjack['mib'] * $blackjack['mib'];
    $sql = "SELECT id, bjwins * $gig AS wins, bjlosses * $gig AS losses, (bjwins - bjlosses) * $gig AS sum
                FROM users
                WHERE status = 0 AND (bjwins>0 OR bjlosses>0) ORDER BY sum DESC LIMIT 20";
    $res = sql_query($sql) or sqlerr(__FILE__, __LINE__);
    while ($row = mysqli_fetch_assoc($res)) {
        $bjusers[] = $row;
    }

    $HTMLOUT .= '
            <h3>Site Statistics</h3>';
    $header = "
                        <tr class='no_hover'>
                            <th>" . _('Username') . '</th>
                            <th>' . _('Total') . '</th>
                            <th>' . _('Wins') . '</th>
                            <th>' . _('Losses') . '</th>
                        </tr>';

    $body = '';
    foreach ($bjusers as $bjuser) {
        $body .= "
                        <tr class='no_hover'>
                            <td>
                                " . format_username((int) $bjuser['id']) . '
                            </td>
                            <td>
                                ' . mksize($bjuser['sum']) . '
                            </td>
                            <td>
                                ' . mksize($bjuser['wins']) . '
                            </td>
                            <td>
                                ' . mksize($bjuser['losses']) . '
                            </td>
                        </tr>';
    }
    $HTMLOUT .= main_table($body, $header);

    $sql = 'SELECT * FROM blackjack_history WHERE game = ' . sqlesc($blackjack['gameid']) . ' ORDER BY id DESC LIMIT 10';
    $res = sql_query($sql) or sqlerr(__FILE__, __LINE__);
    $bjgames = [];
    while ($row = mysqli_fetch_assoc($res)) {
        $bjgames[] = $row;
    }

    if (!empty($bjgames) && count($bjgames) > 0) {
        $HTMLOUT .= "
                <table class='table table-bordered table-striped top20 bottom20'>
                    <thead>
                        <tr class='no_hover'>
                            <th colspan='2'>
                                <div class='has-text-centered'>
                                    <h3>" . _fe('Last {0} Games of {1}', count($bjgames), $blackjack['title']) . '</h3>
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody>';
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
            $HTMLOUT .= '
                            </td>
                            <td>';
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
                                    " . format_username((int) $bjgame['player1_userid']) . ': ' . $bjgame['player1_points'] . "
                                </div>div>
                            </td>
                            <td>
                                <div class='has-text-centered'>
                                    " . format_username((int) $bjgame['player2_userid']) . ': ' . ${'points_' . $g} . '
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
 * @throws NotFoundException
 * @throws \Envms\FluentPDO\Exception
 * @throws DependencyException
 *
 * @return array|bool|mixed|null
 */
function getCardData($cardid)
{
    global $container;

    $cache = $container->get(Cache::class);
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
 * @throws Exception
 *
 * @return mixed
 */
function getCard($cardcount, $gameid, $deal = false)
{
    global $debugout, $blackjack;

    $debugout .= "
            <tr class='no_hover'>
                <td>" . _('deal') . '</td>
                <td>blackjack.php:' . __LINE__ . "</td>
                <td>$deal</td>
            </tr>";
    $cards = [];
    $sql = 'SELECT cards FROM decks WHERE gameid = ' . sqlesc($gameid);

    $debugout .= "
            <tr class='no_hover'>
                <td>" . _('sql') . '</td>
                <td>blackjack.php:' . __LINE__ . "</td>
                <td>$sql</td>
            </tr>";
    $res = sql_query($sql) or sqlerr(__FILE__, __LINE__);
    $card_str = mysqli_fetch_assoc($res);
    $card_str = $card_str['cards'];
    $debugout .= "
            <tr class='no_hover'>
                <td>" . _('cards string') . '</td>
                <td>blackjack.php:' . __LINE__ . "</td>
                <td>$card_str</td>
            </tr>";
    if (!empty($card_str)) {
        $cards = explode(', ', $card_str);
    }
    $cardcount = count($cards);
    $debugout .= "
            <tr class='no_hover'>
                <td>" . _('card count') . '</td>
                <td>blackjack.php:' . __LINE__ . "</td>
                <td>$cardcount</td>
            </tr>";
    if (empty($cards) || ($cardcount <= $blackjack['dead_cards'] && $deal)) {
        $cards = shuffle_decks();
        $sql = 'UPDATE decks SET shuffled = shuffled + 1 WHERE gameid=' . sqlesc($gameid);
        sql_query($sql) or sqlerr(__FILE__, __LINE__);
    }
    $debugout .= '
            <tr class="no_hover">
                <td>' . _('cards - ready') . '</td>
                <td>blackjack.php: ' . __LINE__ . '</td>
                <td>' . json_encode($cards, JSON_PRETTY_PRINT) . '</td>
            </tr>';
    $cardid = $cards[0];
    array_splice($cards, 0, 1);
    $debugout .= '
            <tr class="no_hover">
                <td>' . _('cards - given') . '</td>
                <td>blackjack.php: ' . __LINE__ . '</td>
                <td>' . $cardid . '</td>
            </tr>
            <tr class="no_hover">
                <td>' . _('cards - card removed') . '</td>
                <td>blackjack.php: ' . __LINE__ . '</td>
                <td>' . json_encode($cards, JSON_PRETTY_PRINT) . '</td>
            </tr>';
    $card_str = implode(', ', $cards);
    $debugout .= '
            <tr class="no_hover">
                <td>' . _('cards string') . '</td>
                <td>blackjack.php: ' . __LINE__ . '</td>
                <td>' . $card_str . '</td>
            </tr>';
    $sql = 'INSERT INTO decks (gameid, cards) VALUES (' . sqlesc($gameid) . ', ' . sqlesc($card_str) . ') ON DUPLICATE KEY UPDATE cards = VALUES(cards)';
    $debugout .= '
            <tr class="no_hover">
                <td>' . _('sql') . '</td>
                <td>blackjack.php: ' . __LINE__ . '</td>
                <td>' . $sql . '</td>
            </tr>';
    sql_query($sql) or sqlerr(__FILE__, __LINE__);

    return $cardid;
}

/**
 * @param $blackjack
 * @param $HTMLOUT
 * @param $debugout
 *
 * @throws Exception
 */
function output($blackjack, $HTMLOUT, $debugout)
{
    global $site_config, $user;

    $debugout = "
                <table id='last10List' class='table table-bordered table-striped top20 bottom20'>
                    <thead>
                        <tr class='no_hover'>
                            <th colspan='3'>
                                <div class='has-text-centered'>
                                    <h3>" . _('Debug Info') . "</h3>
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        {$debugout}
                    </tbody>
                </table>";

    if (has_access((int) $user['class'], UC_SYSOP, 'coder') && $blackjack['debug']) {
        $HTMLOUT = $HTMLOUT . $debugout;
    }

    $title = $blackjack['title'];
    $breadcrumbs = [
        "<a href='{$site_config['paths']['baseurl']}/games.php'>" . _('Games') . '</a>',
        "<a href='{$_SERVER['PHP_SELF']}'>$title</a>",
    ];
    echo stdhead($title, [], 'page-wrapper', $breadcrumbs) . wrapper($HTMLOUT) . stdfoot();
}

/**
 * @throws Exception
 *
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
                <td>blackjack.php: ' . __LINE__ . '</td>
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
                <td>' . _('deck - created') . '</td>
                <td>blackjack.php: ' . __LINE__ . '</td>
                <td>' . json_encode($cards, JSON_PRETTY_PRINT) . '</td>
            </tr>';
    // shuffle the decks x number of times
    for ($x = 0; $x <= $blackjack['shuffle']; ++$x) {
        shuffle($cards);
    }
    $debugout .= '
            <tr class="no_hover">
                <td>' . _('deck - shuffled') . '</td>
                <td>blackjack.php: ' . __LINE__ . '</td>
                <td>' . json_encode($cards, JSON_PRETTY_PRINT) . '</td>
            </tr>';
    // cut the decks
    $split = random_int(20, 84);
    $split_deck = array_chunk($cards, $split);
    $temp_deck = [];
    // recombine the decks in reverse order of cut
    for ($x = (count($split_deck) - 1); $x >= 0; --$x) {
        $temp_deck = array_merge($temp_deck, $split_deck[$x]);
        $debugout .= '
            <tr class="no_hover">
                <td>' . _('deck - recombining') . '</td>
                <td>blackjack.php: ' . __LINE__ . '</td>
                <td>' . json_encode($temp_deck, JSON_PRETTY_PRINT) . '</td>
            </tr>';
    }
    $cards = $temp_deck;
    $debugout .= '
            <tr class="no_hover">
                <td>' . _('deck - cut') . '</td>
                <td>blackjack.php: ' . __LINE__ . '</td>
                <td>' . json_encode($cards, JSON_PRETTY_PRINT) . '</td>
            </tr>';
    // burn the first card
    array_splice($cards, 0, 1);
    $debugout .= '
            <tr class="no_hover">
                <td>' . _('deck - first card burned') . '</td>
                <td>blackjack.php: ' . __LINE__ . '</td>
                <td>' . json_encode($cards, JSON_PRETTY_PRINT) . '</td>
            </tr>';

    return $cards;
}
