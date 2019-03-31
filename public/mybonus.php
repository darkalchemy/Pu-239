<?php

require_once __DIR__ . '/../include/bittorrent.php';
require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_html.php';
require_once CLASS_DIR . 'class_user_options_2.php';
require_once INCL_DIR . 'emoticons.php';
require_once INCL_DIR . 'function_event.php';
check_user_status();
global $User, $site_config, $fluent, $cache, $session, $smilies, $message_stuffs, $user_stuffs;

$bonuslog_stuffs = new Pu239\Bonuslog();

$lang = array_merge(load_language('global'), load_language('mybonus'));
if (!$site_config['seedbonus_on']) {
    stderr('Information', 'The Karma bonus system is currently offline for maintainance work');
}

$max_donation = 100000;
$ids = $fluent->from('torrents')
              ->select(null)
              ->select('MIN(id) AS min')
              ->select('MAX(id) AS max')
              ->fetch();

$free = get_event(false);
$HTMLOUT = '';

/**
 * @param $var
 *
 * @return int
 */
function I_smell_a_rat(int $var)
{
    if ($var === 1) {
        return $var;
    } else {
        stderr('Error', 'I smell a rat!');
    }
}

$User = $user_stuffs->getUserFromId($CURUSER['id']);

$ratio = 1;
if ($User['uploaded'] !== 0 && $User['downloaded'] !== 0) {
    $ratio = $User['uploaded'] / $User['downloaded'];
}

if (isset($_GET['freeleech_success']) && $_GET['freeleech_success']) {
    $freeleech_success = $_GET['freeleech_success'];
    if ($freeleech_success != 1 && $freeleech_success != 2) {
        stderr('Error', 'I smell a rat on freeleech!');
    }
    if ($freeleech_success == 1) {
        if ($_GET['norefund'] != 0) {
            $session->set('is-success', "[b]Congratulations! [/b]{$User['username']}, you have set the tracker [b]Free Leech![/b] :woot: [br][br]Remaining " . htmlsafechars($_GET['norefund']) . "' points have been contributed towards the next freeleech period automatically!");
        } else {
            $session->set('is-success', "[b]Congratulations! [/b]{$User['username']} you have set the tracker [b]Free Leech![/b] :woot:");
        }
    }
    if ($freeleech_success == 2) {
        $session->set('is-success', "[b]Congratulations! [/b]{$User['username']} you have contributed towards making the tracker Free Leech! :woot:");
    }
}
if (isset($_GET['doubleup_success']) && $_GET['doubleup_success']) {
    $doubleup_success = $_GET['doubleup_success'];
    if ($doubleup_success != 1 && $doubleup_success != 2) {
        $session->set('is-danger', 'I smell a rat on freeleech!');
    }
    if ($doubleup_success == 1) {
        if ($_GET['norefund'] != 0) {
            $session->set('is-success', "[b]Congratulations! [/b]{$User['username']} you have set the tracker [b]Double Up![/b] :woot: [br][br]Remaining " . htmlsafechars($_GET['norefund']) . ' points have been contributed towards the next doubleup period automatically!');
        } else {
            $session->set('is-success', "[b]Congratulations! [/b]{$User['username']} you have set the tracker [b]Double Up![/b] :woot:");
        }
    }
    if ($doubleup_success == 2) {
        $session->set('is-success', "[b]Congratulations! [/b]{$User['username']} you have contributed towards making the tracker Double Upload! :woot:");
    }
}
if (isset($_GET['halfdown_success']) && $_GET['halfdown_success']) {
    $halfdown_success = $_GET['halfdown_success'];
    if ($halfdown_success != 1 && $halfdown_success != 2) {
        stderr('Error', 'I smell a rat on halfdownload!');
    }
    if ($halfdown_success == 1) {
        if ($_GET['norefund'] != 0) {
            $session->set('is-success', "[b]Congratulations! [/b]{$User['username']} you have set the tracker [b]Half Download![/b] :woot: [br][br]Remaining " . htmlsafechars($_GET['norefund']) . ' points have been contributed towards the next Half download period automatically!');
        } else {
            $session->set('is-success', "[b]Congratulations! [/b]{$User['username']} you have set the tracker [b]Half Download![/b] :woot:");
        }
    }
    if ($halfdown_success == 2) {
        $session->set('is-success', "[b]Congratulations! [/b]{$User['username']} you have contributed towards making the tracker Half Download! :woot:");
    }
}

switch (true) {
    case isset($_GET['up_success']):
        I_smell_a_rat($_GET['up_success']);
        $amounts = $fluent->from('bonus')
                          ->select(null)
                          ->select('points')
                          ->select('bonusname')
                          ->where('bonusname LIKE ?', '%Uploaded%')
                          ->orderBy('points');
        $check_amt = $_GET['amt'];
        foreach ($amounts as $amount) {
            if ($amount['points'] === $check_amt) {
                $amt = str_replace(' Uploaded', '', $amount['bonusname']);
            }
        }
        $session->set('is-success', "[b]Congratulations![/b] {$User['username']}, you have just increased your upload amount by $amt! :woot:");
        break;

    case isset($_GET['anonymous_success']):
        I_smell_a_rat($_GET['anonymous_success']);

        $session->set('is-success', "[b]Congratulations![/b] {$User['username']}, you have just purchased Anonymous profile for 14 days! :woot:");
        break;

    case isset($_GET['parked_success']):
        I_smell_a_rat($_GET['parked_success']);

        $session->set('is-success', "[b]Congratulations![/b] {$User['username']}, you have just purchased parked option for your profile! :woot:");
        break;

    case isset($_GET['freeyear_success']):
        I_smell_a_rat($_GET['freeyear_success']);

        $session->set('is-success', "[b]Congratulations![/b] {$User['username']}, you have just purchased freeleech for 1 year! :woot:");
        break;

    case isset($_GET['freeslots_success']):
        I_smell_a_rat($_GET['freeslots_success']);

        $session->set('is-success', "[b]Congratulations![/b] {$User['username']}, you have just got yourself 3 freeleech slots! :woot:");
        break;

    case isset($_GET['itrade_success']):
        I_smell_a_rat($_GET['itrade_success']);

        $session->set('is-success', "[b]Congratulations![/b] {$User['username']}, you have just got yourself 200 points! :woot:");
        break;

    case isset($_GET['itrade2_success']):
        I_smell_a_rat($_GET['itrade2_success']);

        $session->set('is-success', "[b]Congratulations![/b] {$User['username']}, you have just got yourself 2 freeleech slots! :woot:");
        break;

    case isset($_GET['pirate_success']):
        I_smell_a_rat($_GET['pirate_success']);

        $session->set('is-success', "[b]Congratulations![/b] {$User['username']}, you have just got yourself Pirate Status and Freeleech for 2 weeks! :woot:");
        break;

    case isset($_GET['king_success']):
        I_smell_a_rat($_GET['king_success']);

        $session->set('is-success', "[b]Congratulations![/b] {$User['username']}, you have just got yourself King Status and Freeleech for 1 month! :woot:");
        break;

    case isset($_GET['dload_success']):
        I_smell_a_rat($_GET['dload_success']);
        $amt = $_GET['amt'];
        switch ($amt) {
            case $amt == 150:
                $amt = '1 GB';
                break;
            case $amt == 300:
                $amt = '2.5 GB';
                break;
            default:
                $amt = '5 GB';
        }

        $session->set('is-success', "[b]Congratulations![/b] {$User['username']}, you have just decreased your download amount by $amt! :woot:");
        break;

    case isset($_GET['class_success']):
        I_smell_a_rat($_GET['class_success']);

        $session->set('is-success', "[b]Congratulations![/b] {$User['username']}, you have just got yourself VIP Status for 1 month! :woot:");
        break;

    case isset($_GET['smile_success']):
        I_smell_a_rat($_GET['smile_success']);

        $session->set('is-success', "[b]Congratulations![/b] {$User['username']}, you have just got yourself a set of custom smilies for 1 month! :woot:");
        break;

    case isset($_GET['warning_success']):
        I_smell_a_rat($_GET['warning_success']);

        $session->set('is-success', "[b]Congratulations![/b] {$User['username']}, you have removed your warning for the low price of 1000 points! :woot:");
        break;

    case isset($_GET['invite_success']):
        I_smell_a_rat($_GET['invite_success']);

        $session->set('is-success', "[b]Congratulations![/b] {$User['username']}, you have got your self 3 new invites! :woot:");
        break;

    case isset($_GET['title_success']):
        I_smell_a_rat($_GET['title_success']);

        $session->set('is-success', "[b]Congratulations![/b] {$User['username']}, you are now known as [b]{$User['title']}[/b]! :woot:");
        break;

    case isset($_GET['ratio_success']):
        I_smell_a_rat($_GET['ratio_success']);

        $session->set('is-success', "[b]Congratulations![/b] {$User['username']}, you have gained a 1 to 1 ratio on the selected torrent, and the difference in MB has been added to your total upload! :woot:");
        break;

    case isset($_GET['gift_fail']):
        I_smell_a_rat($_GET['gift_fail']);

        $session->set('is-warning', "{$User['username']}, you can not spread the karma to yourself.[br]If you want to spread the love, pick another user! :-{");
        break;

    case isset($_GET['gift_fail_user']):
        I_smell_a_rat($_GET['gift_fail_user']);

        $session->set('is-warning', "{$User['username']}, no user with that username! :-{");
        break;

    case isset($_GET['bump_success']) && $_GET['bump_success'] == 1:
        $session->set('is-success', "[b]Congratulations![/b] {$User['username']}, you have Re-Animated the [url={$site_config['baseurl']}/details.php?id={$_GET['t_name']}][color=black]torrent![/color][/url] :woot:");
        break;

    case isset($_GET['gift_fail_points']):
        I_smell_a_rat($_GET['gift_fail_points']);

        $session->set('is-warning', "{$User['username']}, you dont have enough Karma points for that! :-{");
        break;

    case isset($_GET['gift_success']):
        I_smell_a_rat($_GET['gift_success']);

        $session->set('is-success', "[b]Congratulations![/b] {$User['username']}, you have spread the Karma well.[br]Member: " . htmlsafechars($_GET['usernamegift']) . ' will be pleased with your kindness![br][br]A message has been was sent! :woot:');
        break;

    case isset($_GET['bounty_success']):
        I_smell_a_rat($_GET['bounty_success']);

        $session->set('is-success', "[b]Congratulations![/b] {$User['username']}, you have got yourself bounty and robbed many users of there reputation points! :woot:");
        break;

    case isset($_GET['reputation_success']):
        I_smell_a_rat($_GET['reputation_success']);

        $session->set('is-success', "[b]Congratulations![/b] {$User['username']}, you have got your 100 rep points! :woot:");
        break;

    case isset($_GET['immunity_success']):
        I_smell_a_rat($_GET['immunity_success']);

        $session->set('is-success', "[b]Congratulations![/b] {$User['username']}, you have got yourself immuntiy from auto hit and run warnings and auto leech warnings! :woot:");
        break;

    case isset($_GET['userblocks_success']):
        I_smell_a_rat($_GET['userblocks_success']);

        $session->set('is-success', "[b]Congratulations![/b] {$User['username']}, you have got yourself access to control the site user blocks! :woot:");
        break;

    case isset($_GET['user_unlocks_success']):
        I_smell_a_rat($_GET['user_unlocks_success']);

        $session->set('is-success', "[b]Congratulations![/b] {$User['username']}, you have got yourself unlocked bonus moods for use on site! :woot:");
        break;
}

if (isset($_GET['exchange'])) {
    I_smell_a_rat($_GET['exchange']);
    $userid = $User['id'];
    if (!is_valid_id($userid)) {
        stderr('Error', 'That is not your user ID!');
    }
    $option = $_POST['option'];

    $res_points = $cache->get('bonus_points_' . $option);
    if ($res_points === false || is_null($res_points)) {
        $res_points = $fluent->from('bonus')
                             ->where('id = ?', $option)
                             ->fetch();
        $cache->set('bonus_points_' . $option, $res_points, 0);
    }

    $art = htmlsafechars($res_points['art']);
    $points = $res_points['points'];
    $minpoints = $res_points['minpoints'];

    if ($User['seedbonus'] <= 0) {
        stderr('Error', 'I smell a rat!');
    }

    if ($points <= 0) {
        stderr('Error', 'I smell a rat!');
    }

    $bonus = $User['seedbonus'];
    $seedbonus = ($bonus - $points);
    $upload = $User['uploaded'];
    $download = $User['downloaded'];
    $bonuscomment = htmlsafechars($User['bonuscomment']);
    $free_switch = $User['free_switch'];
    $warned = $User['warned'];
    $reputation = $User['reputation'];

    if ($bonus < $minpoints) {
        stderr('Sorry', 'you do not have enough Karma points!');
    }

    switch ($art) {
        case 'traffic':
            $up = $upload + $res_points['menge'];
            $bonuscomment = get_date(TIME_NOW, 'DATE', 1) . ' - ' . $points . " Points for upload bonus.\n " . $bonuscomment;
            $set = [
                'uploaded' => $upload + $res_points['menge'],
                'seedbonus' => $seedbonus,
                'bonuscomment' => $bonuscomment,
            ];
            $user_stuffs->update($set, $userid);
            header("Refresh: 0; url={$site_config['baseurl']}/mybonus.php?up_success=1&amt=$points");
            die();
            break;

        case 'reputation':
            if ($User['class'] === UC_MIN || $User['reputation'] >= 5000) {
                stderr('Error', "Time shall unfold what plighted cunning hides\n\nWho cover faults, at last shame them derides...Sorry your not a Power User or you already have to many rep points :-P<br>go back to your <a class='altlink' href='{$site_config['baseurl']}/mybonus.php'>Karma Bonus Point</a> page and think that one over.");
            }
            $rep = $reputation + $res_points['menge'];
            $bonuscomment = get_date(TIME_NOW, 'DATE', 1) . ' - ' . $points . " Points for 100 rep points.\n " . $bonuscomment;
            $set = [
                'reputation' => $rep,
                'seedbonus' => $seedbonus,
                'bonuscomment' => $bonuscomment,
            ];
            $user_stuffs->update($set, $userid);
            header("Refresh: 0; url={$site_config['baseurl']}/mybonus.php?reputation_success=1");
            die();
            break;

        case 'immunity':
            if ($User['class'] === UC_MIN || $User['reputation'] < 3000) {
                stderr('Error', "Time shall unfold what plighted cunning hides\n\nWho cover faults, at last shame them derides...Sorry your not a Power User or you dont have enough rep :-P<br>go back to your <a class='altlink' href='{$site_config['baseurl']}/mybonus.php'>Karma Bonus Point</a> page and think that one over.");
            }
            $bonuscomment = get_date(TIME_NOW, 'DATE', 1) . ' - ' . $points . " Points for 1 years immunity status.\n " . $bonuscomment;
            $immunity = (86400 * 30 + TIME_NOW);
            $set = [
                'immunity' => $immunity,
                'seedbonus' => $seedbonus,
                'bonuscomment' => $bonuscomment,
            ];
            $user_stuffs->update($set, $userid);
            header("Refresh: 0; url={$site_config['baseurl']}/mybonus.php?immunity_success=1");
            die();
            break;

        case 'userblocks':
            $reputation = $User['reputation'];
            if ($User['class'] === UC_MIN || $User['reputation'] < 50) {
                stderr('Error', "Time shall unfold what plighted cunning hides\n\nWho cover faults, at last shame them derides...Sorry your not a Power User or you dont have enough rep points yet - Minimum 50 required :-P<br>go back to your <a class='altlink' href='{$site_config['baseurl']}/mybonus.php'>Karma Bonus Point</a> page and think that one over.");
            }
            $bonuscomment = get_date(TIME_NOW, 'DATE', 1) . ' - ' . $points . " Points for user blocks access.\n " . $bonuscomment;
            $set = [
                'got_blocks' => 'yes',
                'seedbonus' => $seedbonus,
                'bonuscomment' => $bonuscomment,
            ];
            $user_stuffs->update($set, $userid);
            header("Refresh: 0; url={$site_config['baseurl']}/mybonus.php?userblocks_success=1");
            die();
            break;

        case 'userunlock':
            $reputation = $User['reputation'];
            if ($User['class'] === UC_MIN || $User['reputation'] < 50) {
                stderr('Error', "Time shall unfold what plighted cunning hides\n\nWho cover faults, at last shame them derides...Sorry your not a Power User or you dont have enough rep points yet - Minimum 50 required :-P<br>go back to your <a class='altlink' href='{$site_config['baseurl']}/mybonus.php'>Karma Bonus Point</a> page and think that one over.");
            }
            $bonuscomment = get_date(TIME_NOW, 'DATE', 1) . ' - ' . $points . " Points for user unlocks access.\n " . $bonuscomment;
            $setbits = $clrbits = 0;
            $setbits |= user_options_2::GOT_MOODS;
            $sql = 'UPDATE users SET opt2 = ((opt2 | ' . $setbits . ') & ~' . $clrbits . ') WHERE id = ' . sqlesc($User['id']);
            sql_query($sql) or sqlerr(__FILE__, __LINE__);
            $opt2 = $fluent->from('users')
                           ->select(null)
                           ->select('opt2')
                           ->where('id = ?', $User['id'])
                           ->fetch('opt2');

            $cache->update_row('user_' . $User['id'], [
                'opt2' => $opt2,
            ], $site_config['expires']['user_cache']);

            $set = [
                'seedbonus' => $seedbonus,
                'bonuscomment' => $bonuscomment,
            ];
            $user_stuffs->update($set, $userid);

            header("Refresh: 0; url={$site_config['baseurl']}/mybonus.php?user_unlocks_success=1");
            die();
            break;

        case 'anonymous':
            if ($User['anonymous_until'] >= 1) {
                stderr('Error', "Time shall unfold what plighted cunning hides\n\nWho cover faults, at last shame them derides.");
            }
            $bonuscomment = get_date(TIME_NOW, 'DATE', 1) . ' - ' . $points . " Points for 14 days Anonymous profile.\n " . $bonuscomment;
            $anonymous_until = (86400 * 14 + TIME_NOW);
            $set = [
                'anonymous_until' => $anonymous_until,
                'seedbonus' => $seedbonus,
                'bonuscomment' => $bonuscomment,
            ];
            $user_stuffs->update($set, $userid);
            header("Refresh: 0; url={$site_config['baseurl']}/mybonus.php?anonymous_success=1");
            die();
            break;

        case 'parked':
            if ($User['parked_until'] == 1) {
                stderr('Error', "Time shall unfold what plighted cunning hides\n\nWho cover faults, at last shame them derides.");
            }
            $bonuscomment = get_date(TIME_NOW, 'DATE', 1) . ' - ' . $points . " Points for 14 days Anonymous profile.\n " . $bonuscomment;
            $parked_until = 1;
            $set = [
                'parked_until' => $parked_until,
                'seedbonus' => $seedbonus,
                'bonuscomment' => $bonuscomment,
            ];
            $user_stuffs->update($set, $userid);
            header("Refresh: 0; url={$site_config['baseurl']}/mybonus.php?parked_success=1");
            die();
            break;

        case 'traffic2':
            if ($User['downloaded'] == 0) {
                stderr('Error', "Time shall unfold what plighted cunning hides\n\nWho cover faults, at last shame them derides.");
            }
            $bonuscomment = get_date(TIME_NOW, 'DATE', 1) . ' - ' . $points . " Points for download credit removal.\n " . $bonuscomment;
            $down = $download - $res_points['menge'];
            $set = [
                'downloaded' => $down,
                'seedbonus' => $seedbonus,
                'bonuscomment' => $bonuscomment,
            ];
            $user_stuffs->update($set, $userid);
            header("Refresh: 0; url={$site_config['baseurl']}/mybonus.php?dload_success=1&amt=$points");
            die();
            break;

        case 'freeyear':
            if ($User['free_switch'] != 0) {
                stderr('Error', "Time shall unfold what plighted cunning hides\n\nWho cover faults, at last shame them derides.");
            }
            $bonuscomment = get_date(TIME_NOW, 'DATE', 1) . ' - ' . $points . " Points for One year of freeleech.\n " . $bonuscomment;
            $free_switch = (365 * 86400 + TIME_NOW);
            $set = [
                'free_switch' => $free_switch,
                'seedbonus' => $seedbonus,
                'bonuscomment' => $bonuscomment,
            ];
            $user_stuffs->update($set, $userid);
            header("Refresh: 0; url={$site_config['baseurl']}/mybonus.php?freeyear_success=1");
            die();
            break;

        case 'freeslots':
            $bonuscomment = get_date(TIME_NOW, 'DATE', 1) . ' - ' . $points . " Points for freeslots.\n " . $bonuscomment;
            $slots = $User['freeslots'] + $res_points['menge'];
            $set = [
                'freeslots' => $slots,
                'seedbonus' => $seedbonus,
                'bonuscomment' => $bonuscomment,
            ];
            $user_stuffs->update($set, $userid);
            header("Refresh: 0; url={$site_config['baseurl']}/mybonus.php?freeslots_success=1");
            die();
            break;

        case 'itrade':
            if ($User['invites'] < 1) {
                stderr('Error', "Time shall unfold what plighted cunning hides\n\nWho cover faults, at last shame them derides.");
            }
            $bonuscomment = get_date(TIME_NOW, 'DATE', 1) . ' - ' . $points . " invites for bonus points.\n" . $bonuscomment;
            $seedbonus = $User['seedbonus'] + 200;
            $inv = $User['invites'] - 1;
            $set = [
                'invites' => $inv,
                'seedbonus' => $seedbonus,
                'bonuscomment' => $bonuscomment,
            ];
            $user_stuffs->update($set, $userid);
            header("Refresh: 0; url={$site_config['baseurl']}/mybonus.php?itrade_success=1");
            die();
            break;

        case 'itrade2':
            if ($User['invites'] < 1) {
                stderr('Error', "Time shall unfold what plighted cunning hides\n\nWho cover faults, at last shame them derides.");
            }
            $bonuscomment = get_date(TIME_NOW, 'DATE', 1) . ' - ' . $points . " invites for bonus points.\n" . $bonuscomment;
            $inv = $User['invites'] - 1;
            $slots = $User['freeslots'] + 2;
            $set = [
                'invites' => $inv,
                'freeslots' => $slots,
                'bonuscomment' => $bonuscomment,
            ];
            $user_stuffs->update($set, $userid);
            header("Refresh: 0; url={$site_config['baseurl']}/mybonus.php?itrade2_success=1");
            die();
            break;

        case 'pirate':
            if ($User['pirate'] != 0 or $User['king'] != 0) {
                stderr('Error', "Now why would you want to add what you already have?<br>go back to your <a class='altlink' href='{$site_config['baseurl']}/mybonus.php'>Karma Bonus Point</a> page and think that one over.");
            }
            $bonuscomment = get_date(TIME_NOW, 'DATE', 1) . ' - ' . $points . " Points for 2 weeks Pirate + freeleech Status.\n " . $bonuscomment;
            $pirate = (86400 * 14 + TIME_NOW);
            $set = [
                'pirate' => $pirate,
                'free_switch' => $pirate,
                'seedbonus' => $seedbonus,
                'bonuscomment' => $bonuscomment,
            ];
            $user_stuffs->update($set, $userid);
            header("Refresh: 0; url={$site_config['baseurl']}/mybonus.php?pirate_success=1");
            die();
            break;

        case 'bounty':
            $thief_id = $User['id'];
            $thief_name = $User['username'];
            $thief_rep = $User['reputation'];
            $thief_bonus = $User['seedbonus'];
            $rep_to_steal = $points / 1000;
            $new_bonus = $thief_bonus - $points;
            $pm = [];
            $pm['subject'] = 'You just got robbed by %s';
            $pm['subject_thief'] = 'Theft summary';
            $pm['message'] = "Hey\nWe are sorry to announce that you have been robbed by [url=" . $site_config['baseurl'] . "/userdetails.php?id=%d]%s[/url]\nNow your total reputation is [b]%d[/b]\n[color=#ff0000]This is normal and you should not worry, if you have enough bonus points you can rob other people[/color]";
            $pm['message_thief'] = "Hey %s:\nYou robbed:\n%s\nYour total reputation is now [b]%d[/b] but you lost [b]%d[/b] karma points ";
            $foo = [
                50 => 3,
                75 => 3,
                100 => 3,
                150 => 4,
                200 => 5,
                250 => 5,
                300 => 6,
            ];
            $user_limit = isset($foo[$rep_to_steal]) ? $foo[$rep_to_steal] : 3;

            $query = $fluent->from('users')
                            ->select(null)
                            ->select('id')
                            ->select('username')
                            ->select('reputation')
                            ->where('id != ?', $User['id'])
                            ->where('reputation > ?', $rep_to_steal)
                            ->orderBy('RAND()')
                            ->limit($user_limit)
                            ->fetchAll();
            $update_users = $pms = $robbed_user = [];

            foreach ($query as $ar) {
                $new_rep = $ar['reputation'] - $rep_to_steal;
                $robbed_users[] = sprintf('[url=' . $site_config['baseurl'] . '/userdetails.php?id=%d]%s[/url]', $ar['id'], $ar['username']);
                $set = [
                    'reputation' => $new_rep,
                ];
                $user_stuffs->update($set, $ar['id']);
                $msgs_buffer[] = [
                    'sender' => $site_config['chatBotID'],
                    'receiver' => $ar['id'],
                    'added' => TIME_NOW,
                    'subject' => sprintf($pm['subject'], $thief_name),
                    'msg' => sprintf($pm['message'], $thief_id, $thief_name, $new_rep),
                ];
            }
            if (isset($robbed_users)) {
                $new_bonus = $thief_bonus - $points;
                $new_rep = $thief_rep + ($user_limit * $rep_to_steal);
                $msgs_buffer[] = [
                    'sender' => $site_config['chatBotID'],
                    'receiver' => $thief_id,
                    'added' => TIME_NOW,
                    'subject' => $pm['subject_thief'],
                    'msg' => sprintf($pm['message_thief'], $thief_name, implode("\n", $robbed_users), $new_rep, $points),
                ];
                $set = [
                    'reputation' => $new_rep,
                    'seedbonus' => $new_bonus,
                ];
                $user_stuffs->update($set, $thief_id);
            }
            if (!empty($msgs_buffer)) {
                $message_stuffs->insert($msgs_buffer);
            }
            header("Refresh: 0; url={$site_config['baseurl']}/mybonus.php?bounty_success=1");
            die();
            break;

        case 'king':
            if ($User['king'] != 0 or $User['pirate'] != 0) {
                stderr('Error', "Now why would you want to add what you already have?<br>go back to your <a class='altlink' href='{$site_config['baseurl']}/mybonus.php'>Karma Bonus Point</a> page and think that one over.");
            }
            $bonuscomment = get_date(TIME_NOW, 'DATE', 1) . ' - ' . $points . " Points for 1 month King + freeleech Status.\n " . $bonuscomment;
            $king = (86400 * 30 + TIME_NOW);
            $set = [
                'king' => $king,
                'free_switch' => $king,
                'seedbonus' => $seedbonus,
                'bonuscomment' => $bonuscomment,
            ];
            $user_stuffs->update($set, $userid);
            header("Refresh: 0; url={$site_config['baseurl']}/mybonus.php?king_success=1");
            die();
            break;

        case 'freeleech':
            $pointspool = $res_points['pointspool'];
            $points2 = $points - $pointspool;
            $donation = (int) $_POST['donate'];
            $seedbonus = ($bonus - $donation);
            if ($bonus < $donation || $donation <= 0) {
                stderr('Error', ' <br>Points: ' . $donation . ' <br> Bonus: ' . $bonus . ' <br> Donation: ' . $donation . " <br>Time shall unfold what plighted cunning hides\n\nWho cover faults, at last shame them derides.<br> Click to go back to your <a class='altlink' href='{$site_config['baseurl']}/mybonus.php'>Karma Bonus Point</a> page.<br>");
                die();
            }
            if (($pointspool + $donation) >= $res_points['points']) {
                $end = 86400 * 3 + TIME_NOW;
                $message = 'FreeLeech [ON]';
                set_event(1, TIME_NOW, $end, $User['id'], $message);
                $norefund = ($donation + $pointspool) % $points;
                $bonuscomment = get_date(TIME_NOW, 'DATE', 1) . ' - ' . $donation . " Points contributed for freeleech.\n " . $bonuscomment;
                sql_query('UPDATE users
                            SET seedbonus = ' . sqlesc($seedbonus) . ',  bonuscomment = ' . sqlesc($bonuscomment) . '
                            WHERE id = ' . sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
                sql_query('UPDATE bonus
                            SET pointspool = ' . sqlesc($norefund) . "
                            WHERE id = '11' LIMIT 1") or sqlerr(__FILE__, __LINE__);
                $cache->update_row('user_' . $userid, [
                    'seedbonus' => $seedbonus,
                    'bonuscomment' => $bonuscomment,
                ], $site_config['expires']['user_cache']);
                $cache->delete('top_donators3_');
                $cache->delete('top_donators_');
                $cache->delete('freeleech_alerts_');
                $cache->delete('bonus_points_');
                $values = [
                    'userid' => $User['id'],
                    'donation' => $donation,
                    'type' => 'freeleech',
                ];
                $bonuslog_stuffs->insert($values);
                $msg = $User['username'] . ' Donated ' . $donation . ' karma point' . ($donation > 1 ? 's' : '') . ' into the freeleech contribution pot and has activated freeleech for 3 days ' . $donation . '/' . $points . '';
                autoshout($msg);
                header("Refresh: 0; url={$site_config['baseurl']}/mybonus.php?freeleech_success=1&norefund=$norefund");
                die();
            } else {
                sql_query('UPDATE bonus
                            SET pointspool = pointspool + ' . sqlesc($donation) . "
                            WHERE id = '11' LIMIT 1") or sqlerr(__FILE__, __LINE__);
                $bonuscomment = get_date(TIME_NOW, 'DATE', 1) . ' - ' . $donation . " Points contributed for freeleech.\n " . $bonuscomment;
                sql_query('UPDATE users SET
                            seedbonus = ' . sqlesc($seedbonus) . ', bonuscomment = ' . sqlesc($bonuscomment) . '
                            WHERE id = ' . sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
                $cache->update_row('user_' . $userid, [
                    'seedbonus' => $seedbonus,
                    'bonuscomment' => $bonuscomment,
                ], $site_config['expires']['user_cache']);
                $cache->delete('top_donators3_');
                $cache->delete('top_donators_');
                $cache->delete('freeleech_alerts_');
                $cache->delete('bonus_points_');
                $values = [
                    'userid' => $User['id'],
                    'donation' => $donation,
                    'type' => 'freeleech',
                ];
                $bonuslog_stuffs->insert($values);
                $Remaining = ($res_points['points'] - $res_points['pointspool'] - $donation);
                $msg = $User['username'] . ' Donated ' . $donation . ' karma point' . ($donation > 1 ? 's' : '') . ' into the freeleech contribution pot! * Only [b]' . htmlsafechars($Remaining) . '[/b] more karma point' . ($Remaining > 1 ? 's' : '') . " to go! * [color=green][b]Freeleech contribution:[/b][/color] [url={$site_config['baseurl']}/mybonus.php]" . $donation . '/' . $points . '[/url]';
                autoshout($msg);
                header("Refresh: 0; url={$site_config['baseurl']}/mybonus.php?freeleech_success=2");
                die();
            }
            break;

        case 'doubleup':
            $pointspool = $res_points['pointspool'];
            $points2 = $points - $pointspool;
            $donation = (int) $_POST['donate'];
            $seedbonus = ($bonus - $donation);
            if ($bonus < $donation || $donation <= 0) {
                stderr('Error', ' <br>Points: ' . $donation . ' <br> Bonus: ' . $bonus . ' <br> Donation: ' . $donation . " <br>Time shall unfold what plighted cunning hides\n\nWho cover faults, at last shame them derides.<br> Click to go back to your <a class='altlink' href='{$site_config['baseurl']}/mybonus.php'>Karma Bonus Point</a> page.<br>");
                die();
            }
            if (($pointspool + $donation) >= $res_points['points']) {
                $end = 86400 * 3 + TIME_NOW;
                $message = 'DoubleUpload [ON]';
                set_event(2, TIME_NOW, $end, $User['id'], $message);
                $norefund = ($donation + $pointspool) % $points;
                $bonuscomment = get_date(TIME_NOW, 'DATE', 1) . ' - ' . $donation . " Points contributed for doubleupload.\n " . $bonuscomment;
                sql_query('UPDATE users
                            SET seedbonus = ' . sqlesc($seedbonus) . ',  bonuscomment = ' . sqlesc($bonuscomment) . '
                            WHERE id = ' . sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
                sql_query('UPDATE bonus
                            SET pointspool = ' . sqlesc($norefund) . "
                            WHERE id = '12' LIMIT 1") or sqlerr(__FILE__, __LINE__);
                $cache->update_row('user_' . $userid, [
                    'seedbonus' => $seedbonus,
                    'bonuscomment' => $bonuscomment,
                ], $site_config['expires']['user_cache']);
                $cache->delete('top_donators3_');
                $cache->delete('top_donators2_');
                $cache->delete('doubleupload_alerts_');
                $cache->delete('bonus_points_');
                $values = [
                    'userid' => $User['id'],
                    'donation' => $donation,
                    'type' => 'doubleupload',
                ];
                $bonuslog_stuffs->insert($values);
                $msg = $User['username'] . ' Donated ' . $donation . ' karma point' . ($donation > 1 ? 's' : '') . ' into the double upload contribution pot and has activated Double Upload for 3 days ' . $donation . '/' . $points . '';
                autoshout($msg);
                header("Refresh: 0; url={$site_config['baseurl']}/mybonus.php?doubleup_success=1&norefund=$norefund");
                die();
            } else {
                sql_query('UPDATE bonus
                            SET pointspool = pointspool + ' . sqlesc($donation) . "
                            WHERE id = '12' LIMIT 1") or sqlerr(__FILE__, __LINE__);
                $bonuscomment = get_date(TIME_NOW, 'DATE', 1) . ' - ' . $donation . " Points contributed for doubleupload.\n " . $bonuscomment;
                sql_query('UPDATE users
                            SET seedbonus = ' . sqlesc($seedbonus) . ', bonuscomment = ' . sqlesc($bonuscomment) . '
                            WHERE id = ' . sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
                $cache->update_row('user_' . $userid, [
                    'seedbonus' => $seedbonus,
                    'bonuscomment' => $bonuscomment,
                ], $site_config['expires']['user_cache']);
                $cache->delete('top_donators3_');
                $cache->delete('top_donators2_');
                $cache->delete('doubleupload_alerts_');
                $cache->delete('bonus_points_');
                $values = [
                    'userid' => $User['id'],
                    'donation' => $donation,
                    'type' => 'doubleupload',
                ];
                $bonuslog_stuffs->insert($values);
                $Remaining = ($res_points['points'] - $res_points['pointspool'] - $donation);
                $msg = $User['username'] . ' Donated ' . $donation . ' karma point' . ($donation > 1 ? 's' : '') . ' into the double upload contribution pot! * Only [b]' . htmlsafechars($Remaining) . '[/b] more karma point' . ($Remaining > 1 ? 's' : '') . " to go! * [color=green][b]Double upload contribution:[/b][/color] [url={$site_config['baseurl']}/mybonus.php]" . $donation . '/' . $points . '[/url]';
                autoshout($msg);
                header("Refresh: 0; url={$site_config['baseurl']}/mybonus.php?doubleup_success=2");
                die();
            }
            break;

        case 'halfdown':
            $pointspool = $res_points['pointspool'];
            $points2 = $points - $pointspool;
            $donation = (int) $_POST['donate'];
            $seedbonus = ($bonus - $donation);
            if ($bonus < $donation || $donation <= 0) {
                stderr('Error', ' <br>Points: ' . $donation . ' <br> Bonus: ' . $bonus . ' <br> Donation: ' . $donation . " <br>Time shall unfold what plighted cunning hides\n\nWho cover faults, at last shame them derides.<br> Click to go back to your <a class='altlink' href='{$site_config['baseurl']}/mybonus.php'>Karma Bonus Point</a> page.<br>");
                die();
            }
            if (($pointspool + $donation) >= $res_points['points']) {
                $end = 86400 * 3 + TIME_NOW;
                $message = 'HalfDownload [ON]';
                set_event(4, TIME_NOW, $end, $User['id'], $message);
                $norefund = ($donation + $pointspool) % $points;
                $bonuscomment = get_date(TIME_NOW, 'DATE', 1) . ' - ' . $donation . " Points contributed for Halfdownload.\n " . $bonuscomment;
                sql_query('UPDATE users
                            SET seedbonus = ' . sqlesc($seedbonus) . ',  bonuscomment = ' . sqlesc($bonuscomment) . '
                            WHERE id = ' . sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
                sql_query('UPDATE bonus
                            SET pointspool = ' . sqlesc($norefund) . "
                            WHERE id = '13' LIMIT 1") or sqlerr(__FILE__, __LINE__);
                $cache->update_row('user_' . $userid, [
                    'seedbonus' => $seedbonus,
                    'bonuscomment' => $bonuscomment,
                ], $site_config['expires']['user_cache']);
                $cache->delete('top_donators3_');
                $cache->delete('halfdownload_alerts_');
                $cache->delete('bonus_points_');
                $values = [
                    'userid' => $User['id'],
                    'donation' => $donation,
                    'type' => 'halfdownload',
                ];
                $bonuslog_stuffs->insert($values);
                $msg = $User['username'] . ' Donated ' . $donation . ' karma point' . ($donation > 1 ? 's' : '') . ' into the half download contribution pot and has activated half download for 3 days ' . $donation . '/' . $points . '';
                autoshout($msg);
                header("Refresh: 0; url={$site_config['baseurl']}/mybonus.php?halfdown_success=1&norefund=$norefund");
                die();
            } else {
                sql_query('UPDATE bonus
                            SET pointspool = pointspool + ' . sqlesc($donation) . "
                            WHERE id = '13' LIMIT 1") or sqlerr(__FILE__, __LINE__);
                $bonuscomment = get_date(TIME_NOW, 'DATE', 1) . ' - ' . $points . " Points contributed for halfdownload.\n " . $bonuscomment;
                sql_query('UPDATE users
                            SET seedbonus = ' . sqlesc($seedbonus) . ', bonuscomment = ' . sqlesc($bonuscomment) . '
                            WHERE id = ' . sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
                $cache->update_row('user_' . $userid, [
                    'seedbonus' => $seedbonus,
                    'bonuscomment' => $bonuscomment,
                ], $site_config['expires']['user_cache']);
                $cache->delete('top_donators3_');
                $cache->delete('halfdownload_alerts_');
                $cache->delete('bonus_points_');
                $values = [
                    'userid' => $User['id'],
                    'donation' => $donation,
                    'type' => 'halfdownload',
                ];
                $bonuslog_stuffs->insert($values);
                $Remaining = ($res_points['points'] - $res_points['pointspool'] - $donation);
                $msg = $User['username'] . ' Donated ' . $donation . ' karma point' . ($donation > 1 ? 's' : '') . ' into the half download contribution pot! * Only [b]' . htmlsafechars($Remaining) . '[/b] more karma point' . ($Remaining > 1 ? 's' : '') . " to go! * [color=green][b]Half download contribution:[/b][/color] [url={$site_config['baseurl']}/mybonus.php]" . $donation . '/' . $points . '[/url]';
                autoshout($msg);
                header("Refresh: 0; url={$site_config['baseurl']}/mybonus.php?halfdown_success=2");
                die();
            }
            break;

        case 'ratio':
            $torrent_number = $_POST['torrent_id'];
            $res_snatched = sql_query('SELECT s.uploaded, s.downloaded, t.name
                                        FROM snatched AS s
                                        LEFT JOIN torrents AS t ON t.id = s.torrentid
                                        WHERE s.userid = ' . sqlesc($userid) . ' AND torrentid = ' . sqlesc($torrent_number) . '
                                        LIMIT 1') or sqlerr(__FILE__, __LINE__);
            $arr_snatched = mysqli_fetch_assoc($res_snatched);
            if ($arr_snatched['size'] > 6442450944) {
                stderr('Error', "One to One ratio only works on torrents smaller then 6GB!<br><br>Back to your <a class='altlink' href='{$site_config['baseurl']}/mybonus.php'>Karma Bonus Points</a> page.");
            }
            if ($arr_snatched['name'] == '') {
                stderr('Error', "No torrent with that ID!<br>Back to your <a class='altlink' href='{$site_config['baseurl']}/mybonus.php'>Karma Bonus Points</a> page.");
            }
            if ($arr_snatched['uploaded'] >= $arr_snatched['downloaded']) {
                stderr('Error', "Your ratio on that torrent is fine, you must have selected the wrong torrent ID.<br>Back to your <a class='altlink' href='{$site_config['baseurl']}/mybonus.php'>Karma Bonus Points</a> page.");
            }
            sql_query('UPDATE snatched
                        SET uploaded = ' . sqlesc($arr_snatched['downloaded']) . '
                        WHERE userid = ' . sqlesc($userid) . ' AND torrentid = ' . sqlesc($torrent_number)) or sqlerr(__FILE__, __LINE__);
            $difference = $arr_snatched['downloaded'] - $arr_snatched['uploaded'];
            $bonuscomment = get_date(TIME_NOW, 'DATE', 1) . ' - ' . $points . ' Points for 1 to 1 ratio on torrent: ' . htmlsafechars($arr_snatched['name']) . ' ' . $torrent_number . ', ' . $difference . " added .\n " . $bonuscomment;
            sql_query('UPDATE users
                        SET uploaded = ' . sqlesc($upload + $difference) . ', bonuscomment = ' . sqlesc($bonuscomment) . ', seedbonus = ' . sqlesc($seedbonus) . '
                        WHERE id = ' . sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
            $cache->update_row('user_' . $userid, [
                'uploaded' => $upload + $difference,
                'seedbonus' => $seedbonus,
                'bonuscomment' => $bonuscomment,
            ], $site_config['expires']['user_cache']);
            header("Refresh: 0; url={$site_config['baseurl']}/mybonus.php?ratio_success=1");
            die();
            break;

        case 'bump':
            $torrent_number = isset($_POST['torrent_id']) ? intval($_POST['torrent_id']) : 0;
            $res_free = sql_query('SELECT name
                                    FROM torrents
                                    WHERE id = ' . sqlesc($torrent_number)) or sqlerr(__FILE__, __LINE__);
            $arr_free = mysqli_fetch_assoc($res_free);
            if ($arr_free['name'] == '') {
                stderr('Error', 'No torrent with that ID!<br><br>Back to your <a class="altlink" href="karma_bonus.php">Karma Points</a> page.');
            }
            $free_time = (7 * 86400 + TIME_NOW);
            $bonuscomment = get_date(TIME_NOW, 'DATE', 1) . ' - ' . $points . ' Points to Reanimate torrent: ' . $arr_free['name'] . ".\n " . $bonuscomment;
            sql_query('UPDATE users
                        SET bonuscomment = ' . sqlesc($bonuscomment) . ', seedbonus = ' . sqlesc($seedbonus) . '
                        WHERE id = ' . sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
            sql_query('UPDATE torrents
                        SET bump = "yes", free = ' . sqlesc($free_time) . ', added = ' . TIME_NOW . '
                        WHERE id = ' . sqlesc($torrent_number)) or sqlerr(__FILE__, __LINE__);
            $cache->update_row('user_' . $userid, [
                'seedbonus' => $seedbonus,
                'bonuscomment' => $bonuscomment,
            ], $site_config['expires']['user_cache']);
            $cache->update_row('torrent_details_' . $torrent_number, [
                'added' => TIME_NOW,
                'bump' => 'yes',
                'free' => $free_time,
            ], 0);
            header("Refresh: 0; url={$site_config['baseurl']}/mybonus.php?bump_success=1&t_name={$torrent_number}");
            die();
            break;

        case 'class':
            if ($User['class'] > UC_VIP) {
                stderr('Error', "Now why would you want to lower yourself to VIP?<br>go back to your <a class='altlink' href='{$site_config['baseurl']}/mybonus.php'>Karma Bonus Point</a> page and think that one over.");
            }
            $vip_until = (86400 * 28 + TIME_NOW);
            $bonuscomment = get_date(TIME_NOW, 'DATE', 1) . ' - ' . $points . " Points for 1 month VIP Status.\n " . $bonuscomment;
            sql_query('UPDATE users
                        SET class = ' . sqlesc(UC_VIP) . ", vip_added = 'yes', vip_until = " . sqlesc($vip_until) . ', seedbonus = ' . sqlesc($seedbonus) . ', bonuscomment = ' . sqlesc($bonuscomment) . '
                        WHERE id = ' . sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
            $cache->update_row('user_' . $userid, [
                'class' => 2,
                'vip_added' => 'yes',
                'vip_until' => $vip_until,
                'seedbonus' => $seedbonus,
                'bonuscomment' => $bonuscomment,
            ], $site_config['expires']['user_cache']);
            header("Refresh: 0; url={$site_config['baseurl']}/mybonus.php?class_success=1");
            die();
            break;

        case 'warning':
            if ($User['warned'] == 0) {
                stderr('Error', "How can we remove a warning that isn't there?<br>go back to your <a class='altlink' href='{$site_config['baseurl']}/mybonus.php'>Karma Bonus Point</a> page and think that one over.");
            }
            $bonuscomment = get_date(TIME_NOW, 'DATE', 1) . ' - ' . $points . " Points for removing warning.\n " . $bonuscomment;
            $res_warning = sql_query('SELECT modcomment
                                        FROM users
                                        WHERE id = ' . sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
            $arr = mysqli_fetch_assoc($res_warning);
            $modcomment = htmlsafechars($arr['modcomment']);
            $modcomment = get_date(TIME_NOW, 'DATE', 1) . " - Warning removed by - Bribe with Karma.\n" . $modcomment;
            $modcom = sqlesc($modcomment);
            sql_query('UPDATE users
                        SET warned = 0, seedbonus = ' . sqlesc($seedbonus) . ', bonuscomment = ' . sqlesc($bonuscomment) . ', modcomment = ' . sqlesc($modcom) . '
                        WHERE id = ' . sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
            $dt = TIME_NOW;
            $subject = 'Warning removed by Karma.';
            $msg = "Your warning has been removed by the big Karma payoff... Please keep on your best behaviour from now on.\n";
            $msgs_buffer[] = [
                'sender' => 0,
                'receiver' => $userid,
                'added' => $dt,
                'msg' => $msg,
                'subject' => $subject,
            ];
            $message_stuffs->insert($msgs_buffer);
            $cache->update_row('user_' . $userid, [
                'warned' => 0,
                'seedbonus' => $seedbonus,
                'bonuscomment' => $bonuscomment,
                'modcomment' => $modcomment,
            ], $site_config['expires']['user_cache']);
            header("Refresh: 0; url={$site_config['baseurl']}/mybonus.php?warning_success=1");
            die();
            break;

        case 'smile':
            $smile_until = (86400 * 28 + TIME_NOW);
            $bonuscomment = get_date(TIME_NOW, 'DATE', 1) . ' - ' . $points . " Points for 1 month of custom smilies.\n " . $bonuscomment;
            sql_query('UPDATE users
                        SET smile_until = ' . sqlesc($smile_until) . ', seedbonus = ' . sqlesc($seedbonus) . ', bonuscomment = ' . sqlesc($bonuscomment) . '
                        WHERE id = ' . sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
            $cache->update_row('user_' . $userid, [
                'smile_until' => $smile_until,
                'seedbonus' => $seedbonus,
                'bonuscomment' => $bonuscomment,
            ], $site_config['expires']['user_cache']);
            header("Refresh: 0; url={$site_config['baseurl']}/mybonus.php?smile_success=1");
            die();
            break;

        case 'invite':
            $invites = $User['invites'];
            $inv = $invites + 3;
            $bonuscomment = get_date(TIME_NOW, 'DATE', 1) . ' - ' . $points . " Points for invites.\n " . $bonuscomment;
            sql_query('UPDATE users
                        SET invites = ' . sqlesc($inv) . ', seedbonus = ' . sqlesc($seedbonus) . ', bonuscomment = ' . sqlesc($bonuscomment) . '
                        WHERE id = ' . sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
            $cache->update_row('user_' . $userid, [
                'invites' => $inv,
                'seedbonus' => $seedbonus,
                'bonuscomment' => $bonuscomment,
            ], $site_config['expires']['user_cache']);
            header("Refresh: 0; url={$site_config['baseurl']}/mybonus.php?invite_success=1");
            die();
            break;

        case 'title':
            //=== trade for special title
            /**** the $words array are words that you DO NOT want the user to have... use to filter "bad words" & user class...
             * the user class is just for show, but what the hell :p Add more or edit to your liking.
             *note if they try to use a restricted word, they will recieve the special title "I just wasted my karma" *****/
            $title = strip_tags(htmlsafechars($_POST['title']));
            $title = str_replace($site_config['bad_words'], 'I just wasted my karma', $title);
            $bonuscomment = get_date(TIME_NOW, 'DATE', 1) . ' - ' . $points . " Points for custom title. Old title was {$User['title']} new title is " . $title . ".\n " . $bonuscomment;
            sql_query('UPDATE users
                        SET title = ' . sqlesc($title) . ', seedbonus = ' . sqlesc($seedbonus) . ', bonuscomment = ' . sqlesc($bonuscomment) . '
                        WHERE id = ' . sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
            $cache->update_row('user_' . $userid, [
                'title' => $title,
                'seedbonus' => $seedbonus,
                'bonuscomment' => $bonuscomment,
            ], $site_config['expires']['user_cache']);
            header("Refresh: 0; url={$site_config['baseurl']}/mybonus.php?title_success=1");
            die();
            break;

        case 'gift_1':
            $points = $_POST['bonusgift'];
            $usernamegift = htmlsafechars($_POST['username']);
            $res = sql_query('SELECT id, seedbonus, bonuscomment, username
                                FROM users
                                WHERE username = ' . sqlesc($usernamegift)) or sqlerr(__FILE__, __LINE__);
            $arr = mysqli_fetch_assoc($res);
            $useridgift = $arr['id'];
            $userseedbonus = $arr['seedbonus'];
            $bonuscomment_gift = htmlsafechars($arr['bonuscomment']);
            $usernamegift = htmlsafechars($arr['username']);

            if ($bonus >= $points) {
                $bonuscomment = get_date(TIME_NOW, 'DATE', 1) . ' - ' . $points . " Points as gift to $usernamegift .\n " . $bonuscomment;
                $bonuscomment_gift = get_date(TIME_NOW, 'DATE', 1) . ' - recieved ' . $points . " Points as gift from {$User['username']} .\n " . $bonuscomment_gift;
                $seedbonus = $bonus - $points;
                $giftbonus1 = $userseedbonus + $points;
                if ($userid == $useridgift) {
                    header("Refresh: 0; url={$site_config['baseurl']}/mybonus.php?gift_fail=1");
                    die();
                }
                if (!$useridgift) {
                    header("Refresh: 0; url={$site_config['baseurl']}/mybonus.php?gift_fail_user=1");
                    die();
                }
                sql_query('SELECT bonuscomment, id
                            FROM users
                            WHERE id = ' . sqlesc($useridgift)) or sqlerr(__FILE__, __LINE__);
                sql_query('UPDATE users
                            SET seedbonus = ' . sqlesc($seedbonus) . ', bonuscomment = ' . sqlesc($bonuscomment) . '
                            WHERE id = ' . sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
                sql_query('UPDATE users
                            SET seedbonus = ' . sqlesc($giftbonus1) . ', bonuscomment = ' . sqlesc($bonuscomment_gift) . '
                            WHERE id = ' . sqlesc($useridgift)) or sqlerr(__FILE__, __LINE__);
                $cache->update_row('user_' . $userid, [
                    'seedbonus' => $seedbonus,
                    'bonuscomment' => $bonuscomment,
                ], $site_config['expires']['user_cache']);
                $cache->update_row('user_' . $useridgift, [
                    'seedbonus' => $giftbonus1,
                    'bonuscomment' => $bonuscomment_gift,
                ], $site_config['expires']['user_cache']);
                $subject = 'Someone Loves you';
                $dt = TIME_NOW;
                $msg = "You have been given a gift of $points Karma points by " . $User['username'];
                $msgs_buffer[] = [
                    'sender' => 0,
                    'receiver' => $useridgift,
                    'added' => $dt,
                    'msg' => $msg,
                    'subject' => $subject,
                ];
                $message_stuffs->insert($msgs_buffer);
                header("Refresh: 0; url={$site_config['baseurl']}/mybonus.php?gift_success=1&gift_amount_points=$points&usernamegift=$usernamegift&gift_id=$useridgift");
                die();
            } else {
                header("Refresh: 0; url={$site_config['baseurl']}/mybonus.php?gift_fail_points=1");
                die();
            }
            break;
    }
}

$HTMLOUT .= "
    <div class='portlet'>
        <div class='has-text-centered size_6 top20 bottom20'>Karma Bonus Point's System</div>";

$freeleech_enabled = $double_upload_enabled = $half_down_enabled = false;
if (!empty($free) && $free['modifier'] != 0) {
    $begin = $free['begin'];
    $expires = $free['expires'];
    if ($free['modifier'] === 1) {
        $freeleech_start_time = $free['begin'];
        $freeleech_end_time = $free['expires'];
        $freeleech_enabled = true;
    } elseif ($free['modifier'] === 2) {
        $double_upload_start_time = $free['begin'];
        $double_upload_end_time = $free['expires'];
        $double_upload_enabled = true;
    } elseif ($free['modifier'] === 3) {
        $freeleech_start_time = $free['begin'];
        $freeleech_end_time = $free['expires'];
        $freeleech_enabled = true;
        $double_upload_start_time = $free['begin'];
        $double_upload_end_time = $free['expires'];
        $double_upload_enabled = true;
    } elseif ($free['modifier'] === 4) {
        $half_down_start_time = $free['begin'];
        $half_down_end_time = $free['expires'];
        $half_down_enabled = true;
    }
}

$total_fl = $fluent->from('bonus')
                   ->select(null)
                   ->select('SUM(pointspool) AS pointspool')
                   ->select('points')
                   ->select('enabled')
                   ->where('id = 11')
                   ->fetch();
$font_color_fl = $font_color_du = $font_color_hd = '';
$percent_fl = number_format($total_fl['pointspool'] / $total_fl['points'] * 100, 2);
if ($total_fl['enabled'] === 'yes') {
    switch ($percent_fl) {
        case $percent_fl >= 90:
            $font_color_fl = '<span style="color: green">' . number_format($percent_fl) . ' %</span>';
            break;
        case $percent_fl >= 80:
            $font_color_fl = '<span style="color: lightgreen">' . number_format($percent_fl) . ' %</span>';
            break;
        case $percent_fl >= 70:
            $font_color_fl = '<span style="color: jade">' . number_format($percent_fl) . ' %</span>';
            break;
        case $percent_fl >= 50:
            $font_color_fl = '<span style="color: turquoise">' . number_format($percent_fl) . ' %</span>';
            break;
        case $percent_fl >= 40:
            $font_color_fl = '<span style="color: lightblue">' . number_format($percent_fl) . ' %</span>';
            break;
        case $percent_fl >= 30:
            $font_color_fl = '<span style="color: yellow">' . number_format($percent_fl) . ' %</span>';
            break;
        case $percent_fl >= 20:
            $font_color_fl = '<span style="color: orange">' . number_format($percent_fl) . ' %</span>';
            break;
        case $percent_fl < 20:
            $font_color_fl = '<span style="color: red">' . number_format($percent_fl) . ' %</span>';
            break;
    }
}
$total_du = $fluent->from('bonus')
                   ->select(null)
                   ->select('SUM(pointspool) AS pointspool')
                   ->select('points')
                   ->select('enabled')
                   ->where('id = 12')
                   ->fetch();
$percent_du = number_format($total_du['pointspool'] / $total_du['points'] * 100, 2);
if ($total_du['enabled'] === 'yes') {
    switch ($percent_du) {
        case $percent_du >= 90:
            $font_color_du = '<span style="color: green">' . number_format($percent_du) . ' %</span>';
            break;
        case $percent_du >= 80:
            $font_color_du = '<span style="color: lightgreen">' . number_format($percent_du) . ' %</span>';
            break;
        case $percent_du >= 70:
            $font_color_du = '<span style="color: jade">' . number_format($percent_du) . ' %</span>';
            break;
        case $percent_du >= 50:
            $font_color_du = '<span style="color: turquoise">' . number_format($percent_du) . ' %</span>';
            break;
        case $percent_du >= 40:
            $font_color_du = '<span style="color: lightblue">' . number_format($percent_du) . ' %</span>';
            break;
        case $percent_du >= 30:
            $font_color_du = '<span style="color: yellow">' . number_format($percent_du) . ' %</span>';
            break;
        case $percent_du >= 20:
            $font_color_du = '<span style="color: orange">' . number_format($percent_du) . ' %</span>';
            break;
        case $percent_du < 20:
            $font_color_du = '<span style="color: red">' . number_format($percent_du) . ' %</span>';
            break;
    }
}

$total_hd = $fluent->from('bonus')
                   ->select(null)
                   ->select('SUM(pointspool) AS pointspool')
                   ->select('points')
                   ->select('enabled')
                   ->where('id = 13')
                   ->fetch();
$percent_hd = number_format($total_hd['pointspool'] / $total_hd['points'] * 100, 2);
if ($total_hd['enabled'] === 'yes') {
    switch ($percent_hd) {
        case $percent_hd >= 90:
            $font_color_hd = '<span style="color: green">' . number_format($percent_hd) . ' %</span>';
            break;
        case $percent_hd >= 80:
            $font_color_hd = '<span style="color: lightgreen">' . number_format($percent_hd) . ' %</span>';
            break;
        case $percent_hd >= 70:
            $font_color_hd = '<span style="color: jade">' . number_format($percent_hd) . ' %</span>';
            break;
        case $percent_hd >= 50:
            $font_color_hd = '<span style="color: turquoise">' . number_format($percent_hd) . ' %</span>';
            break;
        case $percent_hd >= 40:
            $font_color_hd = '<span style="color: lightblue">' . number_format($percent_hd) . ' %</span>';
            break;
        case $percent_hd >= 30:
            $font_color_hd = '<span style="color: yellow">' . number_format($percent_hd) . ' %</span>';
            break;
        case $percent_hd >= 20:
            $font_color_hd = '<span style="color: orange">' . number_format($percent_hd) . ' %</span>';
            break;
        case $percent_hd < 20:
            $font_color_hd = '<span style="color: red">' . number_format($percent_hd) . ' %</span>';
            break;
    }
}

if ($freeleech_enabled) {
    $fstatus = "<span style='color: green;'> ON </span>";
} else {
    $fstatus = $font_color_fl . '';
}
if ($double_upload_enabled) {
    $dstatus = "<span style='color: green;'> ON </span>";
} else {
    $dstatus = $font_color_du . '';
}
if ($half_down_enabled) {
    $hstatus = "<span style='color: green;'> ON </span>";
} else {
    $hstatus = $font_color_hd . '';
}

$top_donators = $cache->get('top_donators_');
if ($top_donators === false || is_null($top_donators)) {
    $a = sql_query("SELECT b.id, SUM(b.donation) AS total
                        FROM bonuslog AS b
                        WHERE b.type = 'freeleech'
                        GROUP BY b.id
                        ORDER BY total DESC LIMIT 10") or sqlerr(__FILE__, __LINE__);
    while ($top_donator = mysqli_fetch_assoc($a)) {
        $top_donators[] = $top_donator;
    }
    $cache->set('top_donators_', $top_donators, 0);
}
if (!empty($top_donators) && count($top_donators) > 0) {
    $top_donator = "<h4>Top 10 Contributors </h4>\n";
    if ($top_donators) {
        foreach ($top_donators as $a) {
            $top_donator .= format_username($a['id']) . '  [' . number_format($a['total']) . ' ]<br>';
        }
    } else {
        if (empty($top_donators)) {
            $top_donator .= 'Nobodys contibuted yet!!';
        }
    }
}

$top_donators2 = $cache->get('top_donators2_');
if ($top_donators2 === false || is_null($top_donators2)) {
    $b = sql_query("SELECT b.id, SUM(b.donation) AS total
                        FROM bonuslog AS b
                        WHERE b.type = 'doubleupload'
                        GROUP BY b.id
                        ORDER BY total DESC LIMIT 10") or sqlerr(__FILE__, __LINE__);
    while ($top_donator2 = mysqli_fetch_assoc($b)) {
        $top_donators2[] = $top_donator2;
    }
    $cache->set('top_donators2_', $top_donators2, 0);
}
if (!empty($top_donators2) && count($top_donators2) > 0) {
    $top_donator2 = "<h4>Top 10 Contributors </h4>\n";
    if ($top_donators2) {
        foreach ($top_donators2 as $b) {
            $top_donator2 .= format_username($b['id']) . ' [' . number_format($b['total']) . ']<br>';
        }
    } else {
        if (empty($top_donators2)) {
            $top_donator2 .= 'Nobodys contibuted yet!!';
        }
    }
}
$top_donators3 = $cache->get('top_donators3_');
if ($top_donators3 === false || is_null($top_donators3)) {
    $c = sql_query("SELECT b.id, SUM(b.donation) AS total
                        FROM bonuslog AS b
                        WHERE b.type = 'halfdownload'
                        GROUP BY b.id
                        ORDER BY total DESC LIMIT 10") or sqlerr(__FILE__, __LINE__);
    while ($top_donator3 = mysqli_fetch_assoc($c)) {
        $top_donators3[] = $top_donator3;
    }
    $cache->set('top_donators3_', $top_donators3, 0);
}
if (!empty($top_donators3) && count($top_donators3) > 0) {
    $top_donator3 = "<h4>Top 10 Contributors </h4>\n";
    if ($top_donators3) {
        foreach ($top_donators3 as $c) {
            $top_donator3 .= format_username($c['id']) . ' [' . number_format($c['total']) . ']<br>';
        }
    } else {
        if (empty($top_donators3)) {
            $top_donator3 .= 'Nobodys contibuted yet!';
        }
    }
}
$HTMLOUT .= "<div class='has-text-centered size_5'> FreeLeech [ ";
if ($freeleech_enabled) {
    $HTMLOUT .= '<span style="color: green;"> ON</span>';
} else {
    $HTMLOUT .= "{$fstatus}";
}
$HTMLOUT .= ' ]';

$HTMLOUT .= ' DoubleUpload [ ';
if ($double_upload_enabled) {
    $HTMLOUT .= '<span style="color: green"> ON</span>';
} else {
    $HTMLOUT .= "{$dstatus}";
}
$HTMLOUT .= ' ]';

$HTMLOUT .= ' Half Download [ ';
if ($half_down_enabled) {
    $HTMLOUT .= '<span style="color: green"> ON</span>';
} else {
    $HTMLOUT .= "{$hstatus}";
}
$HTMLOUT .= ' ]</div>';

$bonus = $User['seedbonus'];
$HTMLOUT .= "
            <div class='bordered has-text-centered top20'>
                <span class='size_5'>Exchange your <span class='has-text-primary'>" . number_format($bonus) . "</span> Karma Bonus Points for goodies!</span>
                <br>
                <span class='size_3'>
                    [ If no buttons appear, you have not earned enough bonus points to trade. ]
                </span>
            </div>
            <table class='table table-bordered table-striped top20'>
                <thead>
                    <tr>
                        <th>Description</th>
                        <th class='has-text-centered'>Points</th>
                        <th class='has-text-centered'>Trade</th>
                    </tr>
                </thead>";

$options = $fluent->from('bonus')
                  ->where('enabled = "yes"')
                  ->orderBy('orderid ASC');

foreach ($options as $gets) {
    $gets['points'] = floor($gets['points']);
    $gets['points_formatted'] = number_format($gets['points']);
    $gets['minpoints'] = floor($gets['minpoints']);
    $disabled = [
        'FreeLeech',
        'Doubleupload',
        'Halfdownload',
    ];
    if (!empty($free) && $free['expires'] > TIME_NOW && in_array($gets['bonusname'], $disabled)) {
        continue;
    }
    $otheroption = "
            <div>
                <b>Username:</b>
                <input type='text' name='username' class='left10 bottom10' size='40' maxlength='64'>
            </div>
            <div>
                <b>to be given: </b><input type='number' name='bonusgift' class='left10 right10' min='100' max='100000'>Karma points!
            </div>";

    switch (true) {
        case $gets['id'] == 5:
            $HTMLOUT .= "
            <tr>
                <td>
                    <form action='{$site_config['baseurl']}/mybonus.php?exchange=1' method='post' accept-charset='utf-8'>
                        <input type='hidden' name='option' value='" . $gets['id'] . "'>
                        <input type='hidden' name='art' value='" . htmlsafechars($gets['art']) . "'>
                        <h1>" . htmlsafechars($gets['bonusname']) . '</h1>' . htmlsafechars($gets['description']) . "<br><br>
                        <p>Enter the <b>Special Title</b> you would like to have <input type='text' name='title' size='30' maxlength='30'> and click Exchange!</p>
                </td>
                <td class='has-text-centered'>" . $gets['points_formatted'] . '</td>';
            break;
        case $gets['id'] == 7:
            $HTMLOUT .= "
            <tr>
                <td>
                    <form action='{$site_config['baseurl']}/mybonus.php?exchange=1' method='post' accept-charset='utf-8'>
                        <input type='hidden' name='option' value='" . $gets['id'] . "'>
                        <input type='hidden' name='art' value='" . htmlsafechars($gets['art']) . "'>
                        <h1>" . htmlsafechars($gets['bonusname']) . '</h1>' . htmlsafechars($gets['description']) . '<br><br>
                        <p>Enter the <b>username</b> of the person you would like to send karma to, and select how many points you want to send and click Exchange!<br>' . $otheroption . '</p>
                </td>
                <td class="has-text-centered">min.<br>' . $gets['points_formatted'] . '<br>max.<br>100,000</td>';
            break;
        case $gets['id'] == 9:
            $HTMLOUT .= "<tr><td><form action='{$site_config['baseurl']}/mybonus.php?exchange=1' method='post' accept-charset='utf-8'><input type='hidden' name='option' value='" . $gets['id'] . "'> <input type='hidden' name='art' value='" . htmlsafechars($gets['art']) . "'><h1>" . htmlsafechars($gets['bonusname']) . '</h1>' . htmlsafechars($gets['description']) . '</td><td class="has-text-centered">min.<br>' . $gets['points_formatted'] . '</td>';
            break;
        case $gets['id'] == 10:
            $HTMLOUT .= "<tr><td><form action='{$site_config['baseurl']}/mybonus.php?exchange=1' method='post' accept-charset='utf-8'><input type='hidden' name='option' value='" . $gets['id'] . "'> <input type='hidden' name='art' value='" . htmlsafechars($gets['art']) . "'><h1>" . htmlsafechars($gets['bonusname']) . '</h1>' . htmlsafechars($gets['description']) . "<br><br>Enter the <b>ID number of the Torrent:</b> <input type='number' class='left10' name='torrent_id' size='4' min='{$ids['min']}' max='{$ids['max']}'> you would like to buy a 1 to 1 ratio on.</td><td class='has-text-centered'>min.<br>" . $gets['points_formatted'] . '</td>';
            break;
        case $gets['id'] == 11:
            $HTMLOUT .= "<tr><td><form action='{$site_config['baseurl']}/mybonus.php?exchange=1' method='post' accept-charset='utf-8'><input type='hidden' name='option' value='" . $gets['id'] . "'> <input type='hidden' name='art' value='" . htmlsafechars($gets['art']) . "'><h1>" . htmlsafechars($gets['bonusname']) . '</h1>' . htmlsafechars($gets['description']) . '<br>' . $top_donator . "<br>Enter the <b>amount to contribute</b><input type='number' name='donate' class='left10' size='10' min='100' max='$max_donation'></td><td class='has-text-centered'>" . $gets['minpoints'] . '</td>';
            break;
        case $gets['id'] == 12:
            $HTMLOUT .= "<tr><td><form action='{$site_config['baseurl']}/mybonus.php?exchange=1' method='post' accept-charset='utf-8'><input type='hidden' name='option' value='" . $gets['id'] . "'> <input type='hidden' name='art' value='" . htmlsafechars($gets['art']) . "'><h1>" . htmlsafechars($gets['bonusname']) . '</h1>' . htmlsafechars($gets['description']) . '<br>' . $top_donator2 . "<br>Enter the <b>amount to contribute</b><input type='number' name='donate' class='left10' size='10' min='100' max='$max_donation'></td><td class='has-text-centered'>" . $gets['minpoints'] . '</td>';
            break;
        case $gets['id'] == 13:
            $HTMLOUT .= "<tr><td><form action='{$site_config['baseurl']}/mybonus.php?exchange=1' method='post' accept-charset='utf-8'><input type='hidden' name='option' value='" . $gets['id'] . "'><input type='hidden' name='art' value='" . htmlsafechars($gets['art']) . "'><h1>" . htmlsafechars($gets['bonusname']) . '</h1>' . htmlsafechars($gets['description']) . '<br>' . $top_donator3 . "<br>Enter the <b>amount to contribute</b><input type='number' name='donate' class='left10' size='10' min='100' max='$max_donation'></td><td class='has-text-centered'>" . $gets['minpoints'] . '</td>';
            break;
        case $gets['id'] == 34:
            $HTMLOUT .= "<tr><td><form action='{$site_config['baseurl']}/mybonus.php?exchange=1' method='post' accept-charset='utf-8'><input type='hidden' name='option' value='" . $gets['id'] . "'><input type='hidden' name='art' value='" . htmlsafechars($gets['art']) . "'><h1>" . htmlsafechars($gets['bonusname']) . '</h1>' . htmlsafechars($gets['description']) . "<br><br>Enter the <b>ID number of the Torrent:</b> <input type='number' name='torrent_id' size='4' min='{$ids['min']}' max='{$ids['max']}'> you would like to bump.</td><td class='has-text-centered'>min.<br>" . $gets['points_formatted'] . '</td>';
            break;
        default:
            $HTMLOUT .= "<tr><td><form action='{$site_config['baseurl']}/mybonus.php?exchange=1' method='post' accept-charset='utf-8'><input type='hidden' name='option' value='" . $gets['id'] . "'><input type='hidden' name='art' value='" . htmlsafechars($gets['art']) . "'><h1>" . htmlsafechars($gets['bonusname']) . '</h1>' . htmlsafechars($gets['description']) . '</td><td class="has-text-centered">' . $gets['points_formatted'] . '</td>';
    }

    if ($bonus >= $gets['points'] || $bonus >= $gets['minpoints']) {
        switch (true) {
            case $gets['id'] == 7:
                $HTMLOUT .= "<td class='has-text-centered'><input class='button is-small' type='submit' name='submit' value='Karma Gift!'></td></form>";
                break;
            case $gets['id'] == 11:
                $HTMLOUT .= '<td class="has-text-centered">' . number_format($gets['points'] - $gets['pointspool']) . " <br>Points needed! <br><input class='button is-small' type='submit' name='submit' value='Contribute!'></td></form>";
                break;
            case $gets['id'] == 12:
                $HTMLOUT .= '<td class="has-text-centered">' . number_format($gets['points'] - $gets['pointspool']) . " <br>Points needed! <br><input class='button is-small' type='submit' name='submit' value='Contribute!'></td></form>";
                break;
            case $gets['id'] == 13:
                $HTMLOUT .= '<td class="has-text-centered">' . number_format($gets['points'] - $gets['pointspool']) . " <br>Points needed! <br><input class='button is-small' type='submit' name='submit' value='Contribute!'></td></form>";
                break;
            default:
                $HTMLOUT .= "<td class='has-text-centered'><input class='button is-small' type='submit' name='submit' value='Exchange!'></td></form>";
        }
    } else {
        $HTMLOUT .= '<td><b>Not Enough Karma</b></td>';
    }
}

$bpt = $site_config['bonus_per_duration'];
$bmt = $site_config['bonus_max_torrents'];
$bonus_per_comment = $site_config['bonus_per_comment'];
$bonus_per_rating = $site_config['bonus_per_rating'];
$bonus_per_post = $site_config['bonus_per_post'];
$bonus_per_topic = $site_config['bonus_per_topic'];

$at = $fluent->from('peers')
             ->select(null)
             ->select('COUNT(*) AS count')
             ->where('seeder = ?', 'yes')
             ->where('connectable = ?', 'yes')
             ->where('userid = ?', $User['id'])
             ->fetch('count');

$at = $at >= $bmt ? $bmt : $at;

$atform = number_format($at);
$activet = number_format($at * $bpt * 2, 2);

$HTMLOUT .= "</tr></table></div>
    <div class='portlet'>
        <h1 class='top20 has-text-centered'>What the hell are these Karma Bonus points, and how do I get them?</h1>
        <div class='bordered bottom20'>
            <div class='alt_bordered bg-00 padding20'>
                <h2>
                    For every hour that you seed a torrent, you are awarded with " . number_format($bpt * 2, 2) . " Karma Bonus Point...
                </h2>
                <p>
                    If you save up enough of them, you can trade them in for goodies like bonus GB(s) to increase your upload stats, also to get more invites, or doing the real Karma booster... give them to another user!<br>
                    This is awarded on a per torrent basis (max of $bmt) even if there are no leechers on the Torrent you are seeding! <br>
                    Seeding Torrents Based on Connectable Status = <span>
                        <span class='tooltipper' title='Seeding $atform torrents'> $atform </span>*
                        <span class='tooltipper' title='$bpt per announce period'> $bpt </span>*
                        <span class='tooltipper' title='2 announce periods per hour'> 2 </span>= $activet
                    </span>
                    karma per hour
                </p>
            </div>
        </div>

        <div class='bordered bottom20'>
            <div class='alt_bordered bg-00 padding20'>
                <h2>Other things that will get you karma points:</h2>
                <p>
                    Uploading a new torrent = 15 points<br>
                    Filling a request = 10 points<br>
                    Comment on torrent = 3 points<br>
                    Saying thanks = 2 points<br>
                    Rating a torrent = 2 points<br>
                    Making a post = 1 point<br>
                    Starting a topic = 2 points
                </p>
            </div>
        </div>

        <div class='bordered'>
            <div class='alt_bordered bg-00 padding20'>
                <h2>Some things that will cost you karma points:</h2>
                <p>
                    Upload credit<br>
                    Custom title<br>
                    One month VIP status<br>
                    A 1:1 ratio on a torrent<br>
                    Buying off your warning<br>
                    One month custom smilies for the forums and comments<br>
                    Getting extra invites<br>
                    Getting extra freeslots<br>
                    Giving a gift of karma points to another user<br>
                    Asking for a re-seed<br>
                    Making a request<br>
                    Freeleech, Doubleupload, Halfdownload contribution<br>
                    Anonymous profile<br>
                    Download reduction<br>
                    Freeleech for a year<br>
                    Pirate or King status<br>
                    Unlocking parked option<br>
                    Pirates bounty<br>
                    Reputation points<br>
                    Userblocks<br>
                    Bump a torrent<br>
                    User immuntiy<br>
                    User unlocks<br>
                </p>
                <p>
                    But keep in mind that everything that can get you karma can also be lost...<br>
                </p>
                <p>
                    ie: If you up a torrent then delete it, you will gain and then lose 15 points, making a post and having it deleted will do the same... and there are other hidden bonus karma points all over the site which is another way to help out your ratio!
                </p>
                <span>
                    *Please note, staff can give or take away points for breaking the rules, or doing good for the community.
                </span>
            </div>
        </div>
    </div>";

echo stdhead($User['username'] . "'s Karma Bonus Points Page") . $HTMLOUT . stdfoot();
