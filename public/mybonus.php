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
$user = check_user_status();
global $container, $site_config;

$auth = $container->get(Auth::class);
$auth->isSuspended();

if (!$site_config['bonus']['on']) {
    stderr(_('Error'), _('The Karma bonus system is currently offline for maintainance work'));
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
    $username = isset($post['username']) ? htmlsafechars($post['username']) : '';
    $bonusgift = isset($post['bonusgift']) ? (int) $post['bonusgift'] : 0;
    $title = isset($post['title']) ? htmlsafechars($post['title']) : '';
    $users_class = $container->get(User::class);
    $bonuslog = $container->get(Bonuslog::class);
    $auth = $container->get(Auth::class);

    if (in_array($option, $traffic1) && $art === 'traffic') {
        if ($options[$option]['enabled'] === 'yes') {
            if ($user['seedbonus'] >= $options[$option]['points']) {
                $set = [
                    'uploaded' => $user['uploaded'] + $options[$option]['menge'],
                    'seedbonus' => $user['seedbonus'] - $options[$option]['points'],
                ];
                if ($users_class->update($set, $user['id'])) {
                    $session->set('is-success', _fe('{0} You bought {1} for {2} Karma.', ':woot:', "[b]{$options[$option]['bonusname']}[/b]", number_format($options[$option]['points'])));
                } else {
                    $session->set('is-warning', _('Something went wrong'));
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
                    $session->set('is-success', _fe('{0} You bought {1} for {2} Karma.', ':woot:', "[b]{$options[$option]['bonusname']}[/b]", number_format($options[$option]['points'])));
                } else {
                    $session->set('is-warning', _('Something went wrong'));
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
                        $message = _('FreeLeech [ON]');
                        set_event(1, $dt, $end, $user['id'], $message);
                        $excess = ($donate + $options[$option]['pointspool']) % $options[$option]['pointspool'];
                        $values = [
                            'donation' => $donate,
                            'type' => $options[$option]['art'],
                            'added_at' => $dt,
                            'user_id' => $user['id'],
                        ];
                        $update = [
                            'pointspool' => $excess,
                        ];
                    } else {
                        $values = [
                            'donation' => $donate,
                            'type' => $options[$option]['art'],
                            'added_at' => $dt,
                            'user_id' => $user['id'],
                        ];
                        $update = [
                            'pointspool' => (int) $options[$option]['pointspool'] + $donate,
                        ];
                    }
                    $fluent->update('bonus')
                           ->set($update)
                           ->where('id = ?', $post['option'])
                           ->execute();
                    $bonuslog->insert($values);
                    $session->set('is-success', _fe('{0} You donated {1} Karma {2} to the {3} fund.', ':woot:', "[b]{$options[$option]['bonusname']}[/b]", number_format($donate), number_format($options[$option]['points'])));
                } else {
                    $session->set('is-warning', _('Something went wrong'));
                }
            }
        }
    } elseif ($art === 'freeyear') {
        if ($options[$option]['enabled'] === 'yes') {
            if ($user['seedbonus'] >= $options[$option]['points']) {
                $personal_freeleech = strtotime($user['personal_freeleech']);
                $set = [
                    'personal_freeleech' => $personal_freeleech > TIME_NOW ? get_date($personal_freeleech + 365 * 86400, 'MYSQL') : get_date($dt + 365 * 86400, 'MYSQL'),
                    'seedbonus' => $user['seedbonus'] - $options[$option]['points'],
                    'bonuscomment' => get_date((int) $dt, 'DATE', 1) . ' - ' . $options[$option]['points'] . ' ' . _('Points for 1 year Freeleech Status.') . "\n" . $user['bonuscomment'],
                ];
                if ($users_class->update($set, $user['id'])) {
                    $session->set('is-success', _fe('{0} You bought {1} for {2} Karma.', ':woot:', "[b]{$options[$option]['bonusname']}[/b]", number_format($options[$option]['points'])));
                } else {
                    $session->set('is-warning', _('Something went wrong'));
                }
            }
        }
    } elseif ($art === 'king') {
        if ($options[$option]['enabled'] === 'yes') {
            if ($user['seedbonus'] >= $options[$option]['points']) {
                $personal_freeleech = strtotime($user['personal_freeleech']);
                $set = [
                    'king' => $user['king'] === 0 ? $dt + 30 * 86400 : $user['king'] + 30 * 86400,
                    'personal_freeleech' => $personal_freeleech > TIME_NOW ? get_date($personal_freeleech + 30 * 86400, 'MYSQL') : get_date($dt + 30 * 86400, 'MYSQL'),
                    'seedbonus' => $user['seedbonus'] - $options[$option]['points'],
                    'bonuscomment' => get_date((int) $dt, 'DATE', 1) . ' - ' . $options[$option]['points'] . ' ' . _('Points for 1 month King Status.') . "\n" . $user['bonuscomment'],
                ];
                if ($users_class->update($set, $user['id'])) {
                    $session->set('is-success', _fe('{0} You bought {1} for {2} Karma.', ':woot:', "[b]{$options[$option]['bonusname']}[/b]", number_format($options[$option]['points'])));
                } else {
                    $session->set('is-warning', _('Something went wrong'));
                }
            }
        }
    } elseif ($art === 'pirate') {
        if ($options[$option]['enabled'] === 'yes') {
            if ($user['seedbonus'] >= $options[$option]['points']) {
                $personal_freeleech = strtotime($user['personal_freeleech']);
                $set = [
                    'pirate' => $user['pirate'] === 0 ? $dt + 14 * 86400 : $user['king'] + 14 * 86400,
                    'personal_freeleech' => $personal_freeleech > TIME_NOW ? get_date($personal_freeleech + 14 * 86400, 'MYSQL') : get_date($dt + 14 * 86400, 'MYSQL'),
                    'seedbonus' => $user['seedbonus'] - $options[$option]['points'],
                    'bonuscomment' => get_date((int) $dt, 'DATE', 1) . ' - ' . $options[$option]['points'] . ' ' . _('Points for 2 weeks Pirate + freeleech Status.') . "\n " . $user['bonuscomment'],
                ];
                if ($users_class->update($set, $user['id'])) {
                    $session->set('is-success', _fe('{0} You bought {1} for {2} Karma.', ':woot:', "[b]{$options[$option]['bonusname']}[/b]", number_format($options[$option]['points'])));
                } else {
                    $session->set('is-warning', _('Something went wrong'));
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
                        'bonuscomment' => get_date((int) $dt, 'DATE', 1) . ' - ' . $bonusgift . ' ' . _fe('Points as gift to {0}.', $username) . "\n " . $user['bonuscomment'],
                    ];
                    if ($users_class->update($set, $user['id'])) {
                        $set = [
                            'seedbonus' => $gift_user['seedbonus'] + $bonusgift,
                            'bonuscomment' => get_date((int) $dt, 'DATE', 1) . ' - recieved ' . $bonusgift . ' ' . _fe('Points as gift from {0}.', $user['username']) . "\n " . $gift_user['bonuscomment'],
                        ];
                        $users_class->update($set, $gift_user['id']);
                        $msgs_buffer[] = [
                            'receiver' => $gift_user_id,
                            'added' => $dt,
                            'msg' => _fe('You have been given a gift of {0} Karma points by {1}', $bonusgift, $user['username']),
                            'subject' => _('Someone Loves you'),
                        ];
                        $session->set('is-success', _fe('{0} You donated {1} Karma to {2}.', ':woot:', number_format($bonusgift), $username));
                    } else {
                        $session->set('is-warning', _('Something went wrong'));
                    }
                } else {
                    $session->set('is-warning', _('Invalid User Name'));
                }
            }
        }
    } elseif ($art === 'bounty') {
        if ($options[$option]['enabled'] === 'yes') {
            if ($user['seedbonus'] >= $options[$option]['points']) {
                $rep_to_steal = $options[$option]['points'] / 1000;
                $new_bonus = $user['seedbonus'] - $options[$option]['points'];
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
                        'subject' => _fe('You just got robbed by {0}', $user['username']),
                        'msg' => _fe("Hey\nWe are sorry to announce that you have been robbed by {0}{1}{2}.\nNow your total reputation is [b]{3}[/b]\n[color=#ff0000]This is normal and you should not worry, if you have enough bonus points you can rob other people[/color]", "[url={$site_config['paths']['baseurl']}/userdetails.php?id={$user['id']}]", $user['username'], '[/url]', $new_rep),
                    ];
                }
                if (isset($robbed_users)) {
                    $new_rep = $user['reputation'] + ($user_limit * $rep_to_steal);
                    $msgs_buffer[] = [
                        'receiver' => $user['id'],
                        'added' => $dt,
                        'subject' => _('Theft summary'),
                        'msg' => _fe("Hey {0}:\nYou robbed:\n{1}\nYour total reputation is now [b]{2}[/b] but you lost [b]{3}[/b] karma points ", $user['username'], implode("\n", $robbed_users), $new_rep, $options[$option]['points']),
                    ];
                    $set = [
                        'reputation' => $new_rep,
                        'seedbonus' => $user['seedbonus'] - $options[$option]['points'],
                    ];
                    if ($users_class->update($set, $user['id'])) {
                        $session->set('is-success', _fe('{0} You bought {1} for {2} Karma.', ':woot:', "[b]{$options[$option]['bonusname']}[/b]", number_format($options[$option]['points'])));
                    } else {
                        $session->set('is-warning', _('Something went wrong'));
                    }
                }
            }
            if (empty($msgs_buffer)) {
                $session->set('is-warning', _('No users to steal from.'));
            }
        }
    } elseif ($art === 'class') {
        if ($options[$option]['enabled'] === 'yes') {
            if ($user['class'] > UC_VIP) {
                $session->set('is-warning', _('Now why would you want to lower yourself to VIP?'));
            } elseif ($user['seedbonus'] >= $options[$option]['points']) {
                $set = [
                    'vip' => $user['vip'] === 0 ? $dt + 30 * 86400 : $user['vip'] + 30 * 86400,
                    'seedbonus' => $user['seedbonus'] - $options[$option]['points'],
                    'bonuscomment' => get_date((int) $dt, 'DATE', 1) . ' - ' . $options[$option]['points'] . ' ' . _('Points for 1 month VIP Status.') . "\n" . $user['bonuscomment'],
                ];
                if ($users_class->update($set, $user['id'])) {
                    $session->set('is-success', _fe('{0} You bought {1} for {2} Karma.', ':woot:', "[b]{$options[$option]['bonusname']}[/b]", number_format($options[$option]['points'])));
                } else {
                    $session->set('is-warning', _('Something went wrong'));
                }
            }
        }
    } elseif ($art === 'warning') {
        if ($options[$option]['enabled'] === 'yes') {
            if ($user['warned'] === 0) {
                $session->set('is-warning', _("How can we remove a warning that isn't there?"));
            } elseif ($user['seedbonus'] >= $options[$option]['points']) {
                $set = [
                    'warned' => 0,
                    'seedbonus' => $user['seedbonus'] - $options[$option]['points'],
                    'bonuscomment' => get_date((int) $dt, 'DATE', 1) . ' - ' . $options[$option]['points'] . ' ' . _('Points for removing warning.') . "\n " . $user['bonuscomment'],
                    'modcomment' => get_date((int) $dt, 'DATE', 1) . ' - ' . _('Warning removed by - Bribe with Karma.') . "\n" . $user['modcomment'],
                ];
                if ($users_class->update($set, $user['id'])) {
                    $session->set('is-success', _fe('{0} You bought {1} for {2} Karma. Please keep on your best behaviour from now on.', ':woot:', "[b]{$options[$option]['bonusname']}[/b]", number_format($options[$option]['points'])));
                } else {
                    $session->set('is-warning', _('Something went wrong'));
                }
            }
        }
    } elseif ($art === 'ratio') {
        if ($options[$option]['enabled'] === 'yes') {
            $snatched_class = $container->get(Snatched::class);
            $snatched = $snatched_class->get_snatched($user['id'], $torrent_id);
            if (!$snatched) {
                $session->set('is-warning', _('Invalid Torrent ID'));
            } elseif ($snatched['size'] > 6442450944) {
                $session->set('is-warning', _('One to One ratio only works on torrents smaller then 6GB!'));
            } elseif ($snatched['uploaded'] >= $snatched['downloaded']) {
                $session->set('is-warning', _('Your ratio on that torrent is fine, you must have selected the wrong torrent ID.'));
            } elseif ($user['seedbonus'] >= $options[$option]['points']) {
                $difference = $snatched['downloaded'] - $snatched['uploaded'];
                $set = [
                    'uploaded' => $snatched['downloaded'],
                ];
                $snatched_class->update($set, $torrent_id, $user['id']);
                $set = [
                    'seedbonus' => $user['seedbonus'] - $options[$option]['points'],
                    'bonuscomment' => get_date((int) $dt, 'DATE', 1) . ' - ' . $options[$option]['points'] . ' ' . _('Points for 1 to 1 ratio on torrent') . ': ' . format_comment((string) $snatched['name']) . ' ' . $torrent_id . ', ' . _fe('{0} bytes added.', $difference) . "\n " . $user['bonuscomment'],
                ];
                if ($users_class->update($set, $user['id'])) {
                    $session->set('is-success', _fe('{0} You bought {1} for {2} Karma. Please keep on your best behaviour from now on.', ':woot:', "[b]{$options[$option]['bonusname']}[/b]", number_format($options[$option]['points'])));
                } else {
                    $session->set('is-warning', _('Something went wrong'));
                }
            }
        }
    } elseif ($art === 'immunity') {
        if ($options[$option]['enabled'] === 'yes') {
            if ($user['seedbonus'] >= $options[$option]['points']) {
                $set = [
                    'immunity' => $user['immunity'] === 0 ? $dt + 30 * 86400 : $user['immunity'] + 30 * 86400,
                    'seedbonus' => $user['seedbonus'] - $options[$option]['points'],
                    'bonuscomment' => get_date((int) $dt, 'DATE', 1) . ' - ' . $options[$option]['points'] . ' ' . _('Points for 1 month Immunity Status.') . "\n" . $user['bonuscomment'],
                ];
                if ($users_class->update($set, $user['id'])) {
                    $session->set('is-success', _fe('{0} You bought {1} for {2} Karma.', ':woot:', "[b]{$options[$option]['bonusname']}[/b]", number_format($options[$option]['points'])));
                } else {
                    $session->set('is-warning', _('Something went wrong'));
                }
            }
        }
    } elseif ($art === 'parked') {
        if ($options[$option]['enabled'] === 'yes') {
            if ($user['seedbonus'] >= $options[$option]['points']) {
                $set = [
                    'status' => 1,
                    'parked_until' => $user['parked_until'] === 0 ? $dt + 365 * 86400 : $user['parked_until'] + 365 * 86400,
                    'seedbonus' => $user['seedbonus'] - $options[$option]['points'],
                    'bonuscomment' => get_date((int) $dt, 'DATE', 1) . ' - ' . $options[$option]['points'] . ' ' . _('Points for  1 Year Parked Profile.') . "\n" . $user['bonuscomment'],
                ];
                if ($users_class->update($set, $user['id'])) {
                    $session->set('is-success', _fe('{0} You bought {1} for {2} Karma.', ':woot:', "[b]{$options[$option]['bonusname']}[/b]", number_format($options[$option]['points'])));
                } else {
                    $session->set('is-warning', _('Something went wrong'));
                }
            }
        }
    } elseif ($art === 'userunlock') {
        if ($user['class'] === UC_MIN || $user['reputation'] < 50) {
            $session->set('is-warning', _fe("Sorry you are not a Power User or you don't have enough rep points yet - Minimum 50 required {0}", ':-P'));
        } elseif ($options[$option]['enabled'] === 'yes') {
            if ($user['seedbonus'] >= $options[$option]['points']) {
                $setbits = $clrbits = 0;
                $setbits |= user_options_2::GOT_MOODS;
                $set = [
                    'opt2' => new Literal('((opt2 | ' . $setbits . ') & ~' . $clrbits . ')'),
                    'seedbonus' => $user['seedbonus'] - $options[$option]['points'],
                    'bonuscomment' => get_date((int) $dt, 'DATE', 1) . ' - ' . $options[$option]['points'] . ' ' . _('Points for user unlocks access.') . "\n " . $user['bonuscomment'],
                ];
                if ($users_class->update($set, $user['id'], false)) {
                    $session->set('is-success', _fe('{0} You bought {1} for {2} Karma.', ':woot:', "[b]{$options[$option]['bonusname']}[/b]", number_format($options[$option]['points'])));
                }
            } else {
                $session->set('is-warning', _('Something went wrong'));
            }
        }
    } elseif ($art === 'userblocks') {
        if ($user['class'] === UC_MIN || $user['reputation'] < 50) {
            $session->set('is-warning', _fe("Sorry your not a Power User or you don't have enough rep points yet - Minimum 50 required {0}", ':-P'));
        } elseif ($options[$option]['enabled'] === 'yes') {
            if ($user['seedbonus'] >= $options[$option]['points']) {
                $set = [
                    'got_blocks' => 'yes',
                    'seedbonus' => $user['seedbonus'] - $options[$option]['points'],
                    'bonuscomment' => get_date((int) $dt, 'DATE', 1) . ' - ' . $options[$option]['points'] . ' ' . _('Points for user blocks access.') . "\n " . $user['bonuscomment'],
                ];
                if ($users_class->update($set, $user['id'])) {
                    $session->set('is-success', _fe('{0} You bought {1} for {2} Karma.', ':woot:', "[b]{$options[$option]['bonusname']}[/b]", number_format($options[$option]['points'])));
                } else {
                    $session->set('is-warning', _('Something went wrong'));
                }
            }
        }
    } elseif ($art === 'anonymous') {
        if ($options[$option]['enabled'] === 'yes') {
            if ($user['seedbonus'] >= $options[$option]['points']) {
                $set = [
                    'anonymous_until' => $user['anonymous_until'] === 0 ? $dt + 28 * 86400 : $user['anonymous_until'] + 28 * 86400,
                    'seedbonus' => $user['seedbonus'] - $options[$option]['points'],
                    'bonuscomment' => get_date((int) $dt, 'DATE', 1) . ' - ' . $options[$option]['points'] . ' ' . _('Points for 14 Days Anonymous Profile.') . "\n" . $user['bonuscomment'],
                ];
                if ($users_class->update($set, $user['id'])) {
                    $session->set('is-success', _fe('{0} You bought {1} for {2} Karma.', ':woot:', "[b]{$options[$option]['bonusname']}[/b]", number_format($options[$option]['points'])));
                    $cache->deleteMulti([
                        'last24_users_',
                        'birthdayusers_',
                        'ircusers_',
                        'activeusers_',
                        'site_stats_',
                    ]);
                } else {
                    $session->set('is-warning', _('Something went wrong'));
                }
            }
        }
    } elseif ($art === 'smile') {
        if ($options[$option]['enabled'] === 'yes') {
            if ($user['seedbonus'] >= $options[$option]['points']) {
                $set = [
                    'smile_until' => $user['smile_until'] === 0 ? $dt + 30 * 86400 : $user['smile_until'] + 30 * 86400,
                    'seedbonus' => $user['seedbonus'] - $options[$option]['points'],
                    'bonuscomment' => get_date((int) $dt, 'DATE', 1) . ' - ' . $options[$option]['points'] . ' ' . _('Points for 1 month Custom Smilies.') . "\n" . $user['bonuscomment'],
                ];
                if ($users_class->update($set, $user['id'])) {
                    $session->set('is-success', _fe('{0} You bought {1} for {2} Karma.', ':woot:', "[b]{$options[$option]['bonusname']}[/b]", number_format($options[$option]['points'])));
                } else {
                    $session->set('is-warning', _('Something went wrong'));
                }
            }
        }
    } elseif ($art === 'reputation') {
        if ($options[$option]['enabled'] === 'yes') {
            if ($user['seedbonus'] >= $options[$option]['points']) {
                $set = [
                    'reputation' => $user['reputation'] + 100,
                    'seedbonus' => $user['seedbonus'] - $options[$option]['points'],
                    'bonuscomment' => get_date((int) $dt, 'DATE', 1) . ' - ' . $options[$option]['points'] . ' ' . _('Points for 100 Reputation Points.') . "\n" . $user['bonuscomment'],
                ];
                if ($users_class->update($set, $user['id'])) {
                    $session->set('is-success', _fe('{0} You bought {1} for {2} Karma.', ':woot:', "[b]{$options[$option]['bonusname']}[/b]", number_format($options[$option]['points'])));
                } else {
                    $session->set('is-warning', _('Something went wrong'));
                }
            }
        }
    } elseif ($art === 'invite') {
        if ($options[$option]['enabled'] === 'yes') {
            if ($user['seedbonus'] >= $options[$option]['points']) {
                $set = [
                    'invites' => $user['invites'] + $options[$option]['menge'],
                    'seedbonus' => $user['seedbonus'] - $options[$option]['points'],
                    'bonuscomment' => get_date((int) $dt, 'DATE', 1) . ' - ' . _fe('{0} Invites for {1} Karma.', $options[$option]['menge'], $options[$option]['points']) . ".\n" . $user['bonuscomment'],
                ];
                if ($users_class->update($set, $user['id'])) {
                    $session->set('is-success', _fe('{0} You bought {1} Invite for {2} Karma.', ':woot:', "[b]{$options[$option]['menge']}[/b]", number_format($options[$option]['points'])));
                } else {
                    $session->set('is-warning', _('Something went wrong'));
                }
            }
        }
    } elseif ($art === 'itrade') {
        if ($options[$option]['enabled'] === 'yes') {
            if ($user['invites'] >= $options[$option]['points']) {
                $set = [
                    'invites' => $user['invites'] - $options[$option]['points'],
                    'seedbonus' => $user['seedbonus'] + $options[$option]['menge'],
                    'bonuscomment' => get_date((int) $dt, 'DATE', 1) . ' - ' . _fe('{0} Invite for {1} Karma.', $options[$option]['points'], $options[$option]['menge']) . ".\n" . $user['bonuscomment'],
                ];
                if ($users_class->update($set, $user['id'])) {
                    $session->set('is-success', _fe('{0} You sold {1} Invite {2} Karma.', ':woot:', "[b]{$options[$option]['bonusname']}[/b]", number_format($options[$option]['menge'])));
                    $session->set('is-success', ":woot: You sold [b]{$options[$option]['points']} Invite[/b] for {$options[$option]['menge']} Karma");
                } else {
                    $session->set('is-warning', _('Something went wrong'));
                }
            }
        }
    } elseif ($art === 'itrade2') {
        if ($options[$option]['enabled'] === 'yes') {
            if ($user['invites'] >= $options[$option]['points']) {
                $set = [
                    'invites' => $user['invites'] - $options[$option]['points'],
                    'freeslots' => $user['freeslots'] + $options[$option]['menge'],
                    'bonuscomment' => get_date((int) $dt, 'DATE', 1) . ' - ' . _fe('{0} Invite for {1} Freeslots.', $options[$option]['points'], $options[$option]['menge']) . ".\n" . $user['bonuscomment'],
                ];
                if ($users_class->update($set, $user['id'])) {
                    $session->set('is-success', _fe('{0} You traded {1} Invites for {2} Freeslots.', ':woot:', "[b]{$options[$option]['points']}[/b]", number_format($options[$option]['menge'])));
                } else {
                    $session->set('is-warning', _('Something went wrong'));
                }
            }
        }
    } elseif ($art === 'freeslots') {
        if ($options[$option]['enabled'] === 'yes') {
            if ($user['seedbonus'] >= $options[$option]['points']) {
                $set = [
                    'seedbonus' => $user['seedbonus'] - $options[$option]['points'],
                    'freeslots' => $user['freeslots'] + $options[$option]['menge'],
                    'bonuscomment' => get_date((int) $dt, 'DATE', 1) . ' - ' . _fe('{0} Points for Freeslots.', $options[$option]['points']) . "\n " . $user['bonuscomment'],
                ];
                if ($users_class->update($set, $user['id'])) {
                    $session->set('is-success', _fe('{0} You bought {1} Freeslots for {2} Karma.', ':woot:', "[b]{$options[$option]['menge']}[/b]", number_format($options[$option]['points'])));
                } else {
                    $session->set('is-warning', _('Something went wrong'));
                }
            }
        }
    } elseif ($art === 'title') {
        if ($options[$option]['enabled'] === 'yes') {
            if ($user['seedbonus'] >= $options[$option]['points']) {
                foreach ($site_config['site']['badwords'] as $badword) {
                    $title = str_replace($badword, '', $title);
                }
                if (empty($title)) {
                    $title = 'I just wasted my karma';
                }
                $set = [
                    'title' => $title,
                    'seedbonus' => $user['seedbonus'] - $options[$option]['points'],
                    'bonuscomment' => get_date((int) $dt, 'DATE', 1) . ' - ' . _fe("{0} Points for custom title. Old title was ''{1}'' new title is ''{2}''.", $options[$option]['points'], $user['title'], $title) . "\n " . $user['bonuscomment'],
                ];
                if ($users_class->update($set, $user['id'])) {
                    $session->set('is-success', _fe('{0} You bought {1} for {2} Karma.', ':woot:', "[b]{$options[$option]['bonusname']}[/b]", number_format($options[$option]['points'])));
                } else {
                    $session->set('is-warning', _('Something went wrong'));
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
                            'bonuscomment' => get_date((int) $dt, 'DATE', 1) . ' - ' . _fe('{0} Points to Reanimate torrent: {1}.', $options[$option]['points'], $torrent['name']) . "\n " . $user['bonuscomment'],
                        ];
                        if ($users_class->update($set, $user['id'])) {
                            $session->set('is-success', _fe('{0} You reanimated {1} for {2} Karma.', ':woot:', "[b]{$torrent['name']}[/b]", number_format($options[$option]['points'])));
                        } else {
                            $session->set('is-warning', 'Something went wrong');
                        }
                    } else {
                        $session->set('is-warning', _('Something went wrong'));
                    }
                } else {
                    $session->set('is-warning', _('Invalid Torrent ID'));
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
        <div class='has-text-centered size_6 top20 bottom20'>" . _("Karma Bonus Point's System") . '</div>';
$HTMLOUT .= $fl_header . "
            <div class='has-text-centered top20'>
                <span class='size_5'>" . _fe('Exchange your {0}{1}{2} Karma Bonus Points for goodies!', "<span class='has-text-primary'>", number_format((float) $user['seedbonus']), '</span>') . "</span>
                <br>
                <span class='size_3'>
                    [ " . _('If no buttons appear, you have not earned enough bonus points to trade.') . ' ]
                </span>
            </div>';

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
                    <input type='submit' class='button is-small' value='" . _fe('Cost: {0} Karma', number_format($gets['points'])) . "' " . ($user['seedbonus'] < $gets['points'] ? 'disabled' : '') . '>
                </div>';
    switch (true) {
        case $gets['id'] === 5:
            $additional_text = "
                    <div class='top20 has-text-centered'>
                        <label for='title'>" . _('Enter the <b>Special Title</b> you would like to have') . ":</label>
                        <input type='text' id='title' name='title' class='w-100' maxlength='30' value='" . (!empty($user['title']) ? format_comment($user['title']) : '') . "' required>
                    </div>";
            break;
        case $gets['id'] === 7:
            $max = $user['seedbonus'] < 100000 ? $user['seedbonus'] : 100000;
            $additional_text = "
                    <div class='top20 has-text-centered'>
                        <label for='username'>" . _('Enter the <b>username</b> you would like to send karma to') . ":</label>
                        <input type='text' id='username' name='username' class='w-100' maxlength='64' required>
                    </div>
                    <div class='top20 has-text-centered'>
                        <label for='bonusgift'>" . _('Select how many points you want to send') . ":</label>
                        <input type='number' id='bonusgift' name='bonusgift' class='w-100' min='100' max='$max' required>
                    </div>";
            $button = "
                    <div class='has-text-centered top20'>
                        <input type='submit' class='button is-small' value='" . _('Give Karma Gift') . "'>
                    </div>";
            break;
        case $gets['id'] === 9:
            $additional_text = "
                    <div class='top20 has-text-centered'>" . _('Min') . ": {$gets['points_formatted']}</div>";
            break;
        case $gets['id'] === 34:
        case $gets['id'] === 10:
            $additional_text = "
                    <div class='top20 has-text-centered'>
                        <label for='torrent_id'>" . _('Enter the <b>Torrent ID:</b>') . "</label>
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
                        <label for='donate'>" . _('Enter the <b>amount to contribute</b>') . "</label>
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
                        <label for='donate'>" . _('Enter the <b>amount to contribute</b>') . "</label>
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
                        <label for='donate'>" . _('Enter the <b>amount to contribute</b>') . "</label>
                        <input type='number' id='donate' name='donate' class='w-100' min='{$gets['minpoints']}' max='$max_donation' required>
                    </div>";
            $button = "
                    <div class='has-text-centered top20'>
                        <input type='submit' class='button is-small' value='" . _('Donate') . "!'>
                    </div>";
            break;
        case $gets['id'] === 20:
        case $gets['id'] === 21:
            $button = "
                    <div class='has-text-centered top20'>
                        <input type='submit' class='button is-small' value='" . _('Trade') . "!'>
                    </div>";
            break;
        case $gets['id'] === 24:
            $additional_text = "<div class='top20 has-text-centered'>" . _('Min') . ": {$gets['points_formatted']}</div>";
            break;
        default:
            $additional_text = '';
    }

    $body = "
                <div class='masonry-item-clean padding20 bg-04 round10'>
                    <div class='flex-vertical comments h-100'>
                        <div>
                            <h2 class='has-text-centered has-text-weight-bold'>" . format_comment($gets['bonusname']) . '</h2>' . format_comment($gets['description']) . "
                        </div>
                        <div>
                            <form action='{$site_config['paths']['baseurl']}/mybonus.php' method='post' enctype='multipart/form-data' accept-charset='utf-8'>$additional_text
                                <input type='hidden' name='option' value='" . $gets['id'] . "'>
                                <input type='hidden' name='art' value='" . format_comment($gets['art']) . "'>
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
        <h1 class='top20 has-text-centered'>" . _('What the hell are these Karma Bonus points, and how do I get them?') . "</h1>
        <div class='bordered bottom20'>
            <div class='alt_bordered bg-04 padding20'>
                <h2>
                    " . _fe('For every hour that you seed a torrent, you are awarded with {0} Karma Bonus Points', number_format($bpt * 2, 2)) . '
                </h2>
                <p>
                    ' . _('If you save up enough of them, you can trade them in for goodies like bonus GB(s) to increase your upload stats, also to get more invites, or doing the real Karma booster... give them to another user!') . '<br>
                    ' . _fe('This is awarded on a per torrent basis (max of {0} even if there are no leechers on the Torrent you are seeding!', $bmt) . '<br>
                    ' . _('Seeding') . ($site_config['tracker']['connectable_check'] ? ' ' . _('Torrents Based on Connectable Status') : '') . " = <span>
                        <span class='tooltipper' title='" . _fe('Seeding {0} torrents', $atform) . "'> $atform </span>*
                        <span class='tooltipper' title='" . _fe('{0} per announce period', $bpt) . "'> $bpt </span>*
                        <span class='tooltipper' title='" . _('Two announce periods per hour') . "'> 2 </span>= $activet
                    </span>
                    " . _('karma per hour') . "
                </p>
            </div>
        </div>
        <div class='bordered bottom20'>
            <div class='alt_bordered bg-04 padding20'>
                <h2>" . _('Other things that will get you karma points') . ':</h2>
                <p>
                    ' . _fe('Uploading a new torrent = {0} points', $site_config['bonus']['per_upload']) . '<br>
                    ' . _fe('Filling a request = {0} points', $site_config['bonus']['per_request']) . '<br>
                    ' . _fe('Comment on torrent = {0} points', $site_config['bonus']['per_comment']) . '<br>
                    ' . _fe('Saying thanks = {0} points', $site_config['bonus']['per_thanks']) . '<br>
                    ' . _fe('Rating a torrent = {0} points', $site_config['bonus']['per_rating']) . '<br>
                    ' . _fe('Making a post = {0} points', $site_config['bonus']['per_post']) . '<br>
                    ' . _fe('Starting a topic = {0} points', $site_config['bonus']['per_topic']) . "
                </p>
            </div>
        </div>

        <div class='bordered'>
            <div class='alt_bordered bg-04 padding20'>
                <h2>" . _('Some things that will cost you karma points') . ':</h2>
                <p>
                    ' . _fe('Deleting a torrent = -{0} points', $site_config['bonus']['per_delete']) . '<br>
                    ' . _fe('Downloading a torrent = -{0} points', $site_config['bonus']['per_download']) . '<br>
                    ' . _('Upload credit') . '<br>
                    ' . _('Custom title') . '<br>
                    ' . _('One month VIP status') . '<br>
                    ' . _('A 1:1 ratio on a torrent') . '<br>
                    ' . _('Buying off your warning') . '<br>
                    ' . _('One month custom smilies for the forums and comments') . '<br>
                    ' . _('Getting extra invites') . '<br>
                    ' . _('Getting extra freeslots') . '<br>
                    ' . _('Giving a gift of karma points to another user') . '<br>
                    ' . _('Asking for a re-seed') . '<br>
                    ' . _('Making a request') . '<br>
                    ' . _('Freeleech, Doubleupload, Halfdownload contribution') . '<br>
                    ' . _('Anonymous profile') . '<br>
                    ' . _('Download reduction') . '<br>
                    ' . _('Freeleech for a year') . '<br>
                    ' . _('Pirate or King status') . '<br>
                    ' . _('Unlocking parked option') . '<br>
                    ' . _('Pirates bounty') . '<br>
                    ' . _('Reputation points') . '<br>
                    ' . _('Userblocks') . '<br>
                    ' . _('Bump a torrent') . '<br>
                    ' . _('User immuntiy') . '<br>
                    ' . _('User unlocks') . '<br>
                </p>
                <p>
                    ' . _('But keep in mind that everything that can get you karma can also be lost...') . '<br>
                </p>
                <p>
                    ' . _fe('ie: If you up a torrent then delete it, you will gain and then lose {0} points, making a post and having it deleted will do the same... and there are other hidden bonus karma points all over the site which is another way to help out your ratio!', $site_config['bonus']['per_delete']) . '
                </p>
                <span>
                    *' . _('Please note, the staff can give or take away points for breaking the rules, or doing good for the community.') . '
                </span>
            </div>
        </div>
    </div>';

$title = _('Karma Store');
$breadcrumbs = [
    "<a href='{$_SERVER['PHP_SELF']}'>$title</a>",
];
echo stdhead($title, [], 'page-wrapper', $breadcrumbs) . wrapper($HTMLOUT) . stdfoot();
