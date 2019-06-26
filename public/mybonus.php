<?php

declare(strict_types = 1);

use Delight\Auth\Auth;
use Envms\FluentPDO\Literal;
use Pu239\Bonuslog;
use Pu239\Database;
use Pu239\Message;
use Pu239\Session;
use Pu239\Snatched;
use Pu239\Torrent;
use Pu239\User;

require_once __DIR__ . '/../include/bittorrent.php';
require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_html.php';
require_once CLASS_DIR . 'class_user_options_2.php';
require_once INCL_DIR . 'function_event.php';
require_once INCL_DIR . 'function_bonus.php';
check_user_status();
$lang = array_merge(load_language('global'), load_language('mybonus'));
global $container, $site_config;

if (!$site_config['bonus']['on']) {
    stderr('Information', 'The Karma bonus system is currently offline for maintainance work');
}

$dt = TIME_NOW;
$max_donation = 100000;
$bonuses = [];
$fluent = $container->get(Database::class);
$torrent_ids = $fluent->from('torrents')
                      ->select(null)
                      ->select('MIN(id) AS min')
                      ->select('MAX(id) AS max')
                      ->fetch();

$options = $fluent->from('bonus')
                  ->where('enabled = "yes"')
                  ->orderBy('orderid')
                  ->fetchAll();
$option = [
    'id' => 0,
];
foreach ($options as $option) {
    $bonuses[$option['id']] = $option;
}
$options = $bonuses;
$session = $container->get(Session::class);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $post = $_POST;
    unset($_POST);
    $option = isset($post['option']) ? (int) $post['option'] : 0;
    $art = isset($post['art']) ? $post['art'] : '';
    $donate = isset($post['donate']) ? (int) $post['donate'] : 0;
    $torrent_id = isset($post['torrent_id']) ? (int) $post['torrent_id'] : 0;
    $username = isset($post['username']) ? htmlsafechars(trim($post['username'])) : '';
    $bonusgift = isset($post['bonusgift']) ? (int) $post['bonusgift'] : 0;
    $title = isset($post['title']) ? htmlsafechars(trim($post['title'])) : '';
    $users_class = $container->get(User::class);
    $auth = $container->get(Auth::class);
    $user = $users_class->getUserFromId($auth->getUserId());
    $bonuslog = $container->get(Bonuslog::class);

    if (in_array($option, $traffic1) && $art === 'traffic') {
        if ($options[$option]['enabled'] === 'yes') {
            if ($user['seedbonus'] >= $options[$option]['points']) {
                $set = [
                    'uploaded' => $user['uploaded'] + $options[$option]['menge'],
                    'seedbonus' => $user['seedbonus'] - $options[$option]['points'],
                ];
                if ($users_class->update($set, $user['id'])) {
                    $session->set('is-success', ":woot: You bought [b]{$options[$option]['bonusname']}[/b] for " . number_format($options[$option]['points']) . ' Karma');
                } else {
                    $session->set('is-warning', 'Something went wrong');
                }
            }
        }
    } elseif (in_array($option, $traffic2) && $art === 'traffic2') {
        if ($options[$option]['enabled'] === 'yes') {
            if ($user['seedbonus'] >= $options[$option]['points'] && $user['downloaded'] > 0) {
                $set = [
                    'downloaded' => $user['downloaded'] - $options[$option]['menge'] > 0 ? $user['downloaded'] - $options[$option]['menge'] : 0,
                    'seedbonus' => $user['seedbonus'] - $options[$option]['points'],
                ];
                if ($users_class->update($set, $user['id'])) {
                    $session->set('is-success', ":woot: You bought [b]{$options[$option]['bonusname']}[/b] for " . number_format($options[$option]['points']) . ' Karma');
                } else {
                    $session->set('is-warning', 'Something went wrong');
                }
            }
        }
    } elseif (in_array($option, $donations)) {
        if ($options[$option]['enabled'] === 'yes') {
            if ($user['seedbonus'] >= $donate) {
                $set = [
                    'seedbonus' => $user['seedbonus'] - $donate,
                ];
                if ($users_class->update($set, $user['id'])) {
                    if (($options[$option]['pointspool'] + $donate) >= $options[$option]['points']) {
                        $end = 86400 * 3 + $dt;
                        $message = 'FreeLeech [ON]';
                        set_event(1, $dt, $end, $User['id'], $message);
                        $excess = ($donate + $options[$option]['pointspool']) % $options[$option]['pointspool'];
                        $values = [
                            'donation' => $excess,
                            'type' => $options[$option]['art'],
                            'added_at' => $dt,
                            'user_id' => $user['id'],
                        ];
                    } else {
                        $values = [
                            'donation' => $donate,
                            'type' => $options[$option]['art'],
                            'added_at' => $dt,
                            'user_id' => $user['id'],
                        ];
                    }
                    $bonuslog->insert($values);
                    $session->set('is-success', ':woot: You donated ' . number_format($donate) . " Karma to the [b]{$options[$option]['bonusname']}[/b] fund");
                } else {
                    $session->set('is-warning', 'Something went wrong');
                }
            }
        }
    } elseif ($art === 'freeyear') {
        if ($options[$option]['enabled'] === 'yes') {
            if ($user['seedbonus'] >= $options[$option]['points']) {
                $set = [
                    'free_switch' => $user['free_switch'] === 0 ? $dt + 365 * 86400 : $user['free_switch'] + 365 * 86400,
                    'seedbonus' => $user['seedbonus'] - $options[$option]['points'],
                    'bonuscomment' => get_date((int) $dt, 'DATE', 1) . ' - ' . $options[$option]['points'] . " Points for 1 year Freeleech Status.\n" . $user['bonuscomment'],
                ];
                if ($users_class->update($set, $user['id'])) {
                    $session->set('is-success', ":woot: You bought [b]{$options[$option]['bonusname']}[/b] for " . number_format($options[$option]['points']) . ' Karma');
                } else {
                    $session->set('is-warning', 'Something went wrong');
                }
            }
        }
    } elseif ($art === 'king') {
        if ($options[$option]['enabled'] === 'yes') {
            if ($user['seedbonus'] >= $options[$option]['points']) {
                $set = [
                    'king' => $user['king'] === 0 ? $dt + 30 * 86400 : $user['king'] + 30 * 86400,
                    'free_switch' => $user['free_switch'] === 0 ? $dt + 30 * 86400 : $user['free_switch'] + 30 * 86400,
                    'seedbonus' => $user['seedbonus'] - $options[$option]['points'],
                    'bonuscomment' => get_date((int) $dt, 'DATE', 1) . ' - ' . $options[$option]['points'] . " Points for 1 month King Status.\n" . $user['bonuscomment'],
                ];
                if ($users_class->update($set, $user['id'])) {
                    $session->set('is-success', ":woot: You bought [b]{$options[$option]['bonusname']}[/b] for " . number_format($options[$option]['points']) . ' Karma');
                } else {
                    $session->set('is-warning', 'Something went wrong');
                }
            }
        }
    } elseif ($art === 'pirate') {
        if ($options[$option]['enabled'] === 'yes') {
            if ($user['seedbonus'] >= $options[$option]['points']) {
                $set = [
                    'pirate' => $user['pirate'] === 0 ? $dt + 14 * 86400 : $user['king'] + 14 * 86400,
                    'free_switch' => $user['free_switch'] === 0 ? $dt + 14 * 86400 : $user['free_switch'] + 14 * 86400,
                    'seedbonus' => $user['seedbonus'] - $options[$option]['points'],
                    'bonuscomment' => get_date((int) $dt, 'DATE', 1) . ' - ' . $options[$option]['points'] . " Points for 2 weeks Pirate + freeleech Status.\n " . $user['bonuscomment'],
                ];
                if ($users_class->update($set, $user['id'])) {
                    $session->set('is-success', ":woot: You bought [b]{$options[$option]['bonusname']}[/b] for " . number_format($options[$option]['points']) . ' Karma');
                } else {
                    $session->set('is-warning', 'Something went wrong');
                }
            }
        }
    } elseif ($art === 'gift_1') {
        if ($options[$option]['enabled'] === 'yes') {
            if ($user['seedbonus'] >= $bonusgift) {
                $gift_user_id = $users_class->getUserIdFromName($username);
                if ($gift_user_id) {
                    $gift_user = $users_class->getUserFromId((int) $gift_user_id);
                    $set = [
                        'seedbonus' => $user['seedbonus'] - $bonusgift,
                        'bonuscomment' => get_date((int) $dt, 'DATE', 1) . ' - ' . $bonusgift . " Points as gift to $username .\n " . $user['bonuscomment'],
                    ];
                    if ($users_class->update($set, $user['id'])) {
                        $set = [
                            'seedbonus' => $gift_user['seedbonus'] + $bonusgift,
                            'bonuscomment' => get_date((int) $dt, 'DATE', 1) . ' - recieved ' . $bonusgift . " Points as gift from {$user['username']} .\n " . $gift_user['bonuscomment'],
                        ];
                        $users_class->update($set, $gift_user['id']);
                        $msgs_buffer[] = [
                            'receiver' => $gift_user_id,
                            'added' => $dt,
                            'msg' => "You have been given a gift of $bonusgift Karma points by " . $user['username'],
                            'subject' => 'Someone Loves you',
                        ];
                        $session->set('is-success', ':woot: You donated ' . number_format($bonusgift) . ' Karma to ' . $username);
                    } else {
                        $session->set('is-warning', 'Something went wrong');
                    }
                } else {
                    $session->set('is-warning', 'Invalid User Name');
                }
            }
        }
    } elseif ($art === 'bounty') {
        if ($options[$option]['enabled'] === 'yes') {
            if ($user['seedbonus'] >= $options[$option]['points']) {
                $rep_to_steal = $options[$option]['points'] / 1000;
                $new_bonus = $user['seedbonus'] - $options[$option]['points'];
                $pm = [];
                $pm['subject'] = 'You just got robbed by %s';
                $pm['subject_thief'] = 'Theft summary';
                $pm['message'] = "Hey\nWe are sorry to announce that you have been robbed by [url=" . $site_config['paths']['baseurl'] . "/userdetails.php?id=%d]%s[/url]\nNow your total reputation is [b]%d[/b]\n[color=#ff0000]This is normal and you should not worry, if you have enough bonus points you can rob other people[/color]";
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
                                ->where('id != ?', $user['id'])
                                ->where('reputation > ?', $rep_to_steal)
                                ->orderBy('RAND()')
                                ->limit($user_limit)
                                ->fetchAll();
                $update_users = $pms = $robbed_user = [];
                foreach ($query as $ar) {
                    $new_rep = $ar['reputation'] - $rep_to_steal;
                    $robbed_users[] = sprintf('[url=' . $site_config['paths']['baseurl'] . '/userdetails.php?id=%d]%s[/url]', $ar['id'], $ar['username']);
                    $set = [
                        'reputation' => $new_rep,
                    ];
                    $users_class->update($set, $ar['id']);
                    $msgs_buffer[] = [
                        'receiver' => $ar['id'],
                        'added' => $dt,
                        'subject' => sprintf($pm['subject'], $user['username']),
                        'msg' => sprintf($pm['message'], $user['id'], $user['username'], $new_rep),
                    ];
                }
                if (isset($robbed_users)) {
                    $new_rep = $user['reputation'] + ($user_limit * $rep_to_steal);
                    $msgs_buffer[] = [
                        'receiver' => $user['id'],
                        'added' => $dt,
                        'subject' => $pm['subject_thief'],
                        'msg' => sprintf($pm['message_thief'], $user['username'], implode("\n", $robbed_users), $new_rep, $options[$option]['points']),
                    ];
                    $set = [
                        'reputation' => $new_rep,
                        'seedbonus' => $user['seedbonus'] - $options[$option]['points'],
                    ];
                    if ($users_class->update($set, $user['id'])) {
                        $session->set('is-success', ":woot: You bought [b]{$options[$option]['bonusname']}[/b] for " . number_format($options[$option]['points']) . ' Karma');
                    } else {
                        $session->set('is-warning', 'Something went wrong');
                    }
                }
            }
            if (empty($msgs_buffer)) {
                $session->set('is-warning', 'No users to steal from.');
            }
        }
    } elseif ($art === 'class') {
        if ($options[$option]['enabled'] === 'yes') {
            if ($user['class'] > UC_VIP) {
                $session->set('is-warning', 'Now why would you want to lower yourself to VIP?');
            } elseif ($user['seedbonus'] >= $options[$option]['points']) {
                $set = [
                    'vip' => $user['vip'] === 0 ? $dt + 30 * 86400 : $user['vip'] + 30 * 86400,
                    'seedbonus' => $user['seedbonus'] - $options[$option]['points'],
                    'bonuscomment' => get_date((int) $dt, 'DATE', 1) . ' - ' . $options[$option]['points'] . " Points for 1 month VIP Status.\n" . $user['bonuscomment'],
                ];
                if ($users_class->update($set, $user['id'])) {
                    $session->set('is-success', ":woot: You bought [b]{$options[$option]['bonusname']}[/b] for " . number_format($options[$option]['points']) . ' Karma');
                } else {
                    $session->set('is-warning', 'Something went wrong');
                }
            }
        }
    } elseif ($art === 'warning') {
        if ($options[$option]['enabled'] === 'yes') {
            if ($user['warned'] === 0) {
                $session->set('is-warning', "How can we remove a warning that isn't there?");
            } elseif ($user['seedbonus'] >= $options[$option]['points']) {
                $set = [
                    'warned' => 0,
                    'seedbonus' => $user['seedbonus'] - $options[$option]['points'],
                    'bonuscomment' => get_date((int) $dt, 'DATE', 1) . ' - ' . $options[$option]['points'] . " Points for removing warning.\n " . $user['bonuscomment'],
                    'modcomment' => get_date((int) $dt, 'DATE', 1) . " - Warning removed by - Bribe with Karma.\n" . $user['modcomment'],
                ];
                if ($users_class->update($set, $user['id'])) {
                    $session->set('is-success', ":woot: You bought [b]{$options[$option]['bonusname']}[/b] for " . number_format($options[$option]['points']) . ' Karma. Please keep on your best behaviour from now on.');
                } else {
                    $session->set('is-warning', 'Something went wrong');
                }
            }
        }
    } elseif ($art === 'ratio') {
        if ($options[$option]['enabled'] === 'yes') {
            $snatched_class = $container->get(Snatched::class);
            $snatched = $snatched_class->get_snatched($user['id'], $torrent_id);
            if (!$snatched) {
                $session->set('is-warning', 'Invalid Torrent ID');
            } elseif ($snatched['size'] > 6442450944) {
                $session->set('is-warning', 'One to One ratio only works on torrents smaller then 6GB!');
            } elseif ($snatched['uploaded'] >= $snatched['downloaded']) {
                $session->set('is-warning', 'Your ratio on that torrent is fine, you must have selected the wrong torrent ID.');
            } elseif ($user['seedbonus'] >= $options[$option]['points']) {
                $difference = $snatched['downloaded'] - $snatched['uploaded'];
                $set = [
                    'uploaded' => $snatched['downloaded'],
                ];
                $snatched_class->update($set, $torrent_id, $user['id']);
                $set = [
                    'seedbonus' => $user['seedbonus'] - $options[$option]['points'],
                    'bonuscomment' => get_date((int) $dt, 'DATE', 1) . ' - ' . $options[$option]['points'] . ' Points for 1 to 1 ratio on torrent: ' . htmlsafechars((string) $snatched['name']) . ' ' . $torrent_id . ', ' . $difference . " bytes added.\n " . $user['bonuscomment'],
                ];
                if ($users_class->update($set, $user['id'])) {
                    $session->set('is-success', ":woot: You bought [b]{$options[$option]['bonusname']}[/b] for " . number_format($options[$option]['points']) . ' Karma. Please keep on your best behaviour from now on.');
                } else {
                    $session->set('is-warning', 'Something went wrong');
                }
            }
        }
    } elseif ($art === 'immunity') {
        if ($options[$option]['enabled'] === 'yes') {
            if ($user['seedbonus'] >= $options[$option]['points']) {
                $set = [
                    'immunity' => $user['immunity'] === 0 ? $dt + 30 * 86400 : $user['immunity'] + 30 * 86400,
                    'seedbonus' => $user['seedbonus'] - $options[$option]['points'],
                    'bonuscomment' => get_date((int) $dt, 'DATE', 1) . ' - ' . $options[$option]['points'] . " Points for 1 month Immunity Status.\n" . $user['bonuscomment'],
                ];
                if ($users_class->update($set, $user['id'])) {
                    $session->set('is-success', ":woot: You bought [b]{$options[$option]['bonusname']}[/b] for " . number_format($options[$option]['points']) . ' Karma');
                } else {
                    $session->set('is-warning', 'Something went wrong');
                }
            }
        }
    } elseif ($art === 'parked') {
        if ($user['parked'] === 'yes' && $user['parked_until'] > $dt) {
            $session->set('is-warning', 'Your profile is already parked.');
        } elseif ($options[$option]['enabled'] === 'yes') {
            if ($user['seedbonus'] >= $options[$option]['points']) {
                $set = [
                    'parked' => 'yes',
                    'parked_until' => $user['parked_until'] === 0 ? $dt + 365 * 86400 : $user['parked_until'] + 365 * 86400,
                    'seedbonus' => $user['seedbonus'] - $options[$option]['points'],
                    'bonuscomment' => get_date((int) $dt, 'DATE', 1) . ' - ' . $options[$option]['points'] . " Points for  1 Year Parked Profile.\n" . $user['bonuscomment'],
                ];
                if ($users_class->update($set, $user['id'])) {
                    $session->set('is-success', ":woot: You bought [b]{$options[$option]['bonusname']}[/b] for " . number_format($options[$option]['points']) . ' Karma');
                } else {
                    $session->set('is-warning', 'Something went wrong');
                }
            }
        }
    } elseif ($art === 'userunlock') {
        if ($user['class'] === UC_MIN || $user['reputation'] < 50) {
            $session->set('is-warning', "Sorry your not a Power User or you don't have enough rep points yet - Minimum 50 required :-P");
        } elseif ($options[$option]['enabled'] === 'yes') {
            if ($user['seedbonus'] >= $options[$option]['points']) {
                $setbits = $clrbits = 0;
                $setbits |= user_options_2::GOT_MOODS;
                $set = [
                    'opt2' => new Literal('((opt2 | ' . $setbits . ') & ~' . $clrbits . ')'),
                    'seedbonus' => $user['seedbonus'] - $options[$option]['points'],
                    'bonuscomment' => get_date((int) $dt, 'DATE', 1) . ' - ' . $options[$option]['points'] . " Points for user unlocks access.\n " . $user['bonuscomment'],
                ];
                if ($users_class->update($set, $user['id'], false)) {
                    $session->set('is-success', ":woot: You bought [b]{$options[$option]['bonusname']}[/b] for " . number_format($options[$option]['points']) . ' Karma');
                }
            } else {
                $session->set('is-warning', 'Something went wrong');
            }
        }
    } elseif ($art === 'userblocks') {
        if ($user['class'] === UC_MIN || $user['reputation'] < 50) {
            $session->set('is-warning', "Sorry your not a Power User or you don't have enough rep points yet - Minimum 50 required :-P");
        } elseif ($options[$option]['enabled'] === 'yes') {
            if ($user['seedbonus'] >= $options[$option]['points']) {
                $set = [
                    'got_blocks' => 'yes',
                    'seedbonus' => $user['seedbonus'] - $options[$option]['points'],
                    'bonuscomment' => get_date((int) $dt, 'DATE', 1) . ' - ' . $options[$option]['points'] . " Points for user blocks access.\n " . $user['bonuscomment'],
                ];
                if ($users_class->update($set, $user['id'])) {
                    $session->set('is-success', ":woot: You bought [b]{$options[$option]['bonusname']}[/b] for " . number_format($options[$option]['points']) . ' Karma');
                } else {
                    $session->set('is-warning', 'Something went wrong');
                }
            }
        }
    } elseif ($art === 'anonymous') {
        if ($options[$option]['enabled'] === 'yes') {
            if ($user['seedbonus'] >= $options[$option]['points']) {
                $set = [
                    'anonymous_until' => $user['anonymous_until'] === 0 ? $dt + 14 * 86400 : $user['anonymous_until'] + 14 * 86400,
                    'seedbonus' => $user['seedbonus'] - $options[$option]['points'],
                    'bonuscomment' => get_date((int) $dt, 'DATE', 1) . ' - ' . $options[$option]['points'] . " Points for 14 Days Anonymous Profile.\n" . $user['bonuscomment'],
                ];
                if ($users_class->update($set, $user['id'])) {
                    $session->set('is-success', ":woot: You bought [b]{$options[$option]['bonusname']}[/b] for " . number_format($options[$option]['points']) . ' Karma');
                } else {
                    $session->set('is-warning', 'Something went wrong');
                }
            }
        }
    } elseif ($art === 'smile') {
        if ($options[$option]['enabled'] === 'yes') {
            if ($user['seedbonus'] >= $options[$option]['points']) {
                $set = [
                    'smile_until' => $user['anonymous_until'] === 0 ? $dt + 30 * 86400 : $user['smile_until'] + 30 * 86400,
                    'seedbonus' => $user['seedbonus'] - $options[$option]['points'],
                    'bonuscomment' => get_date((int) $dt, 'DATE', 1) . ' - ' . $options[$option]['points'] . " Points for 1 month Custom Smilies.\n" . $user['bonuscomment'],
                ];
                if ($users_class->update($set, $user['id'])) {
                    $session->set('is-success', ":woot: You bought [b]{$options[$option]['bonusname']}[/b] for " . number_format($options[$option]['points']) . ' Karma');
                } else {
                    $session->set('is-warning', 'Something went wrong');
                }
            }
        }
    } elseif ($art === 'reputation') {
        if ($options[$option]['enabled'] === 'yes') {
            if ($user['seedbonus'] >= $options[$option]['points']) {
                $set = [
                    'reputation' => $user['reputation'] + 100,
                    'seedbonus' => $user['seedbonus'] - $options[$option]['points'],
                    'bonuscomment' => get_date((int) $dt, 'DATE', 1) . ' - ' . $options[$option]['points'] . " Points for 100 Reputation Points.\n" . $user['bonuscomment'],
                ];
                if ($users_class->update($set, $user['id'])) {
                    $session->set('is-success', ":woot: You bought [b]{$options[$option]['bonusname']}[/b] for " . number_format($options[$option]['points']) . ' Karma');
                } else {
                    $session->set('is-warning', 'Something went wrong');
                }
            }
        }
    } elseif ($art === 'invite') {
        if ($options[$option]['enabled'] === 'yes') {
            if ($user['seedbonus'] >= $options[$option]['points']) {
                $set = [
                    'invites' => $user['invites'] + $options[$option]['menge'],
                    'seedbonus' => $user['seedbonus'] - $options[$option]['points'],
                    'bonuscomment' => get_date((int) $dt, 'DATE', 1) . ' - ' . $options[$option]['menge'] . " Invites for {$options[$option]['points']} Karma.\n" . $user['bonuscomment'],
                ];
                if ($users_class->update($set, $user['id'])) {
                    $session->set('is-success', ":woot: You bought [b]{$options[$option]['menge']} Invite[/b] for {$options[$option]['points']} Karma");
                } else {
                    $session->set('is-warning', 'Something went wrong');
                }
            }
        }
    } elseif ($art === 'itrade') {
        if ($options[$option]['enabled'] === 'yes') {
            if ($user['invites'] >= $options[$option]['points']) {
                $set = [
                    'invites' => $user['invites'] - $options[$option]['points'],
                    'seedbonus' => $user['seedbonus'] + $options[$option]['menge'],
                    'bonuscomment' => get_date((int) $dt, 'DATE', 1) . ' - ' . $options[$option]['points'] . " Invite for {$options[$option]['menge']} Karma.\n" . $user['bonuscomment'],
                ];
                if ($users_class->update($set, $user['id'])) {
                    $session->set('is-success', ":woot: You sold [b]{$options[$option]['points']} Invite[/b] for {$options[$option]['menge']} Karma");
                } else {
                    $session->set('is-warning', 'Something went wrong');
                }
            }
        }
    } elseif ($art === 'itrade2') {
        if ($options[$option]['enabled'] === 'yes') {
            if ($user['invites'] >= $options[$option]['points']) {
                $set = [
                    'invites' => $user['invites'] - $options[$option]['points'],
                    'freeslots' => $user['freeslots'] + $options[$option]['menge'],
                    'bonuscomment' => get_date((int) $dt, 'DATE', 1) . ' - ' . $options[$option]['points'] . " Invite for {$options[$option]['menge']} Freeslots.\n" . $user['bonuscomment'],
                ];
                if ($users_class->update($set, $user['id'])) {
                    $session->set('is-success', ":woot: You traded [b]{$options[$option]['points']} Invites[/b] for {$options[$option]['menge']} Freeslots");
                } else {
                    $session->set('is-warning', 'Something went wrong');
                }
            }
        }
    } elseif ($art === 'freeslots') {
        if ($options[$option]['enabled'] === 'yes') {
            if ($user['seedbonus'] >= $options[$option]['points']) {
                $set = [
                    'seedbonus' => $user['seedbonus'] - $options[$option]['points'],
                    'freeslots' => $user['freeslots'] + $options[$option]['menge'],
                    'bonuscomment' => get_date((int) $dt, 'DATE', 1) . ' - ' . $options[$option]['points'] . " Points for freeslots.\n " . $user['bonuscomment'],
                ];
                if ($users_class->update($set, $user['id'])) {
                    $session->set('is-success', ":woot: You bought [b]{$options[$option]['menge']} Freeslots[/b] for {$options[$option]['points']} Karma");
                } else {
                    $session->set('is-warning', 'Something went wrong');
                }
            }
        }
    } elseif ($art === 'title') {
        if ($options[$option]['enabled'] === 'yes') {
            if ($user['seedbonus'] >= $options[$option]['points']) {
                $title = str_replace($site_config['site']['badwords'], '', $title);
                if (empty($title)) {
                    $title = 'I just wasted my karma';
                }
                $set = [
                    'title' => $title,
                    'seedbonus' => $user['seedbonus'] - $options[$option]['points'],
                    'bonuscomment' => get_date((int) $dt, 'DATE', 1) . ' - ' . $options[$option]['points'] . " Points for custom title. Old title was {$user['title']} new title is " . $title . ".\n " . $user['bonuscomment'],
                ];
                if ($users_class->update($set, $user['id'])) {
                    $session->set('is-success', ":woot: You bought [b]{$options[$option]['bonusname']}[/b] for " . number_format($options[$option]['points']) . ' Karma');
                } else {
                    $session->set('is-warning', 'Something went wrong');
                }
            }
        }
    } elseif ($art === 'bump') {
        if ($options[$option]['enabled'] === 'yes') {
            if ($user['seedbonus'] >= $options[$option]['points']) {
                $torrents_class = $container->get(Torrent::class);
                $torrent = $torrents_class->get((int) $torrent_id);
                if ($torrent) {
                    $set = [
                        'bump' => 'yes',
                        'free' => 7 * 86400 + $dt,
                        'added' => $dt,
                    ];
                    if ($torrents_class->update($set, (int) $torrent_id)) {
                        $set = [
                            'seedbonus' => $user['seedbonus'] - $options[$option]['points'],
                            'bonuscomment' => get_date((int) $dt, 'DATE', 1) . ' - ' . $options[$option]['points'] . ' Points to Reanimate torrent: ' . $torrent['name'] . ".\n " . $user['bonuscomment'],
                        ];
                        if ($users_class->update($set, $user['id'])) {
                            $session->set('is-success', ":woot: You reanimated for [b]{$torrent['name']}[/b] {$options[$option]['points']} Karma");
                        } else {
                            $session->set('is-warning', 'Something went wrong');
                        }
                    } else {
                        $session->set('is-warning', 'Something went wrong');
                    }
                } else {
                    $session->set('is-warning', 'Invalid Torrent ID');
                }
            }
        }
    }
    if (!empty($msgs_buffer)) {
        $messages_class = $container->get(Message::class);
        $messages_class->insert($msgs_buffer);
    }
    header("Location: {$_SERVER['PHP_SELF']}");
    die();
}

$HTMLOUT = "
    <div class='portlet'>
        <div class='has-text-centered size_6 top20 bottom20'>Karma Bonus Point's System</div>";
$HTMLOUT .= $fl_header . "
            <div class='has-text-centered top20'>
                <span class='size_5'>Exchange your <span class='has-text-primary'>" . number_format((float) $user['seedbonus']) . "</span> Karma Bonus Points for goodies!</span>
                <br>
                <span class='size_3'>
                    [ If no buttons appear, you have not earned enough bonus points to trade. ]
                </span>
            </div>";

$items = '';
foreach ($options as $gets) {
    $gets['points'] = floor($gets['points']);
    $gets['points_formatted'] = number_format($gets['points']);
    $gets['minpoints'] = floor($gets['minpoints']);
    $disabled = [
        'FreeLeech',
        'Doubleupload',
        'Halfdownload',
    ];
    if (!empty($free) && $free['expires'] > $dt && in_array($gets['bonusname'], $disabled)) {
        continue;
    }
    $button = "
                <div class='has-text-centered top20'>
                    <input type='submit' class='button is-small' value='Cost: " . number_format($gets['points']) . " Karma' " . ($user['seedbonus'] < $gets['points'] ? 'disabled' : '') . '>
                </div>';
    switch (true) {
        case $gets['id'] === 5:
            $additional_text = "
                    <div class='top20 has-text-centered'>
                        <label for='title'>Enter the <b>Special Title</b> you would like to have:</label>
                        <input type='text' id='title' name='title' class='w-100' maxlength='30' value='" . (!empty($user['title']) ? htmlsafechars($user['title']) : '') . "' required>
                    </div>";
            break;
        case $gets['id'] === 7:
            $max = $user['seedbonus'] < 100000 ? $user['seedbonus'] : 100000;
            $additional_text = "
                    <div class='top20 has-text-centered'>
                        <label for='username'>Enter the <b>username</b> you would like to send karma to:</label>
                        <input type='text' id='username' name='username' class='w-100' maxlength='64' required>
                    </div>
                    <div class='top20 has-text-centered'>
                        <label for='bonusgift'>Select how many points you want to send:</label>
                        <input type='number' id='bonusgift' name='bonusgift' class='w-100' min='100' max='$max' required>
                    </div>";
            $button = "
                    <div class='has-text-centered top20'>
                        <input type='submit' class='button is-small' value='Give Karma Gift'>
                    </div>";
            break;
        case $gets['id'] === 9:
            $additional_text = "
                    <div class='top20 has-text-centered'>Min: {$gets['points_formatted']}</div>";
            break;
        case $gets['id'] === 10:
            $additional_text = "
                    <div class='top20 has-text-centered'>
                        <label for='torrent_id'>Enter the <b>Torrent ID:</b></label>
                        <input type='number' class='w-100' id='torrent_id' name='torrent_id' min='{$torrent_ids['min']}' max='{$torrent_ids['max']}' required>
                    </div>";
            break;
        case $gets['id'] === 11:
            $max_donation = $user['seedbonus'] < $max_donation ? $user['seedbonus'] : $max_donation;
            $additional_text = "
                    <div class='top20 has-text-centered'>
                        $top_donator1
                    </div>
                    <div class='top20 has-text-centered'>
                        <label for='donate'>Enter the <b>amount to contribute</b></label>
                        <input type='number' id='donate' name='donate' class='w-100' min='{$gets['minpoints']}' max='$max_donation' required>
                    </div>";
            $button = "
                    <div class='has-text-centered top20'>
                        <input type='submit' class='button is-small' value='Donate!'>
                    </div>";
            break;
        case $gets['id'] === 12:
            $max_donation = $user['seedbonus'] < $max_donation ? $user['seedbonus'] : $max_donation;
            $additional_text = "
                    <div class='top20 has-text-centered'>
                        $top_donator2
                    </div>
                    <div class='top20 has-text-centered'>
                        <label for='donate'>Enter the <b>amount to contribute</b></label>
                        <input type='number' id='donate' name='donate' class='w-100' min='{$gets['minpoints']}' max='$max_donation' required>
                    </div>";
            $button = "
                    <div class='has-text-centered top20'>
                        <input type='submit' class='button is-small' value='Donate!'>
                    </div>";
            break;
        case $gets['id'] === 13:
            $max_donation = $user['seedbonus'] < $max_donation ? $user['seedbonus'] : $max_donation;
            $additional_text = "
                    <div class='top20 has-text-centered'>
                        $top_donator3
                    </div>
                    <div class='top20 has-text-centered'>
                        <label for='donate'>Enter the <b>amount to contribute</b></label>
                        <input type='number' id='donate' name='donate' class='w-100' min='{$gets['minpoints']}' max='$max_donation' required>
                    </div>";
            $button = "
                    <div class='has-text-centered top20'>
                        <input type='submit' class='button is-small' value='Donate!'>
                    </div>";
            break;
        case $gets['id'] === 20:
        case $gets['id'] === 21:
            $button = "
                    <div class='has-text-centered top20'>
                        <input type='submit' class='button is-small' value='Trade!'>
                    </div>";
            break;
        case $gets['id'] === 24:
            $additional_text = "<div class='top20 has-text-centered'>Min: {$gets['points_formatted']}</div>";
            break;
        case $gets['id'] === 34:
            $additional_text = "
                    <div class='top20 has-text-centered'>
                        <label for='torrent_id'>Enter the <b>Torrent ID:</b></label>
                        <input type='number' class='w-100' id='torrent_id' name='torrent_id' min='{$torrent_ids['min']}' max='{$torrent_ids['max']}' required>
                    </div>";
            break;
        default:
            $additional_text = '';
    }

    $body = "
                <div class='masonry-item-clean padding20 bg-04 round10'>
                    <div class='flex-vertical comments h-100'>
                        <div>
                            <h2 class='has-text-centered has-text-weight-bold'>" . htmlsafechars($gets['bonusname']) . '</h2>' . htmlsafechars($gets['description']) . "
                        </div>
                        <div>
                            <form action='{$site_config['paths']['baseurl']}/mybonus.php' method='post' accept-charset='utf-8'>$additional_text
                                <input type='hidden' name='option' value='" . $gets['id'] . "'>
                                <input type='hidden' name='art' value='" . htmlsafechars($gets['art']) . "'>
                                <input type='hidden' name='menge' value='" . $gets['menge'] . "'>
                                <input type='hidden' name='pointspool' value='" . $gets['pointspool'] . "'>
                                <input type='hidden' name='minpoints' value='" . $gets['minpoints'] . "'>
                                <input type='hidden' name='points' value='" . $gets['points'] . "'>
                                $button
                            </form>   
                        </div>
                    </div>
                </div>";

    $items .= $body;
}

$HTMLOUT .= main_div($items, 'top20', 'masonry padding20');

$bpt = $site_config['bonus']['per_duration'];
$bmt = $site_config['bonus']['max_torrents'];
$at = $fluent->from('peers')
             ->select(null)
             ->select('COUNT(*) AS count')
             ->where('seeder = ?', 'yes');
if ($site_config['tracker']['connectable_check']) {
    $at = $at->where('connectable = "yes"');
}
$at = $at->where('connectable = ?', 'yes')
         ->where('userid=?', $user['id'])
         ->fetch('count');

$at = $at >= $bmt ? $bmt : $at;

$atform = number_format($at);
$activet = number_format($at * $bpt * 2, 2);

$HTMLOUT .= "
    <div class='portlet'>
        <h1 class='top20 has-text-centered'>What the hell are these Karma Bonus points, and how do I get them?</h1>
        <div class='bordered bottom20'>
            <div class='alt_bordered bg-04 padding20'>
                <h2>
                    For every hour that you seed a torrent, you are awarded with " . number_format($bpt * 2, 2) . " Karma Bonus Point...
                </h2>
                <p>
                    If you save up enough of them, you can trade them in for goodies like bonus GB(s) to increase your upload stats, also to get more invites, or doing the real Karma booster... give them to another user!<br>
                    This is awarded on a per torrent basis (max of $bmt) even if there are no leechers on the Torrent you are seeding! <br>
                    Seeding" . ($site_config['tracker']['connectable_check'] ? ' Torrents Based on Connectable Status' : '') . " = <span>
                        <span class='tooltipper' title='Seeding $atform torrents'> $atform </span>*
                        <span class='tooltipper' title='$bpt per announce period'> $bpt </span>*
                        <span class='tooltipper' title='2 announce periods per hour'> 2 </span>= $activet
                    </span>
                    karma per hour
                </p>
            </div>
        </div>
        <div class='bordered bottom20'>
            <div class='alt_bordered bg-04 padding20'>
                <h2>Other things that will get you karma points:</h2>
                <p>
                    Uploading a new torrent = {$site_config['bonus']['per_upload']} points<br>
                    Filling a request = {$site_config['bonus']['per_request']} points<br>
                    Comment on torrent = {$site_config['bonus']['per_comment']} point<br>
                    Saying thanks = {$site_config['bonus']['per_thanks']} points<br>
                    Rating a torrent = {$site_config['bonus']['per_rating']} points<br>
                    Making a post = {$site_config['bonus']['per_post']} point<br>
                    Starting a topic = {$site_config['bonus']['per_topic']} points
                </p>
            </div>
        </div>

        <div class='bordered'>
            <div class='alt_bordered bg-04 padding20'>
                <h2>Some things that will cost you karma points:</h2>
                <p>
                    Deleting a torrent = -{$site_config['bonus']['per_delete']} points<br>
                    Downloading a torrent = -{$site_config['bonus']['per_download']} points<br>
                    Upload credit<br>
                    Custom title<br>
                    One month VIP status<br>
                    A 1:1 ratio on a torrent<br>
                    Buying off your warning<br>
                    One month custom smilies for the forums and comments<br>
                    Getting extra invites<br>
                    Getting extra freeslots<br>
                    Giving.gift of karma points to another user<br>
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

echo stdhead($user['username'] . "'s Karma Bonus Points Page") . $HTMLOUT . stdfoot();
