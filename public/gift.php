<?php

declare(strict_types = 1);

use Pu239\Cache;

require_once __DIR__ . '/../include/bittorrent.php';
require_once INCL_DIR . 'function_users.php';
$user = check_user_status();
global $container, $site_config;

$Christmasday = mktime(0, 0, 0, 12, 25, (int) date('Y'));
$dayafter = mktime(0, 0, 0, 12, 26, (int) date('Y'));
$today = mktime((int) date('G'), (int) date('i'), (int) date('s'), (int) date('m'), (int) date('d'), (int) date('Y'));
$gifts = [
    'upload',
    'bonus',
    'invites',
    'bonus2',
];
$randgift = array_rand($gifts);
$gift = $gifts[$randgift];
$userid = $user['id'];
if (!is_valid_id($userid)) {
    stderr(_('Error'), _('Invalid ID'), 'bottom20');
}
$open = isset($_GET['open']) ? (int) $_GET['open'] : 0;
if ($open != 1) {
    stderr(_('Error'), 'Invalid url', 'bottom20');
}
$sql = sql_query('SELECT seedbonus, invites, freeslots, uploaded FROM users WHERE id=' . sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
$User = mysqli_fetch_assoc($sql);
if (isset($open) && $open == 1) {
    if ($today >= $Christmasday && $today <= $dayafter) {
        $cache = $container->get(Cache::class);
        if ($user['gotgift'] === 'no') {
            if ($gift === 'upload') {
                sql_query("UPDATE users SET invites=invites+1, uploaded=uploaded+1024*1024*1024*10, freeslots=freeslots+1, gotgift='yes' WHERE id=" . sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
                $update['invites'] = ($User['invites'] + 1);
                $update['uploaded'] = ($User['uploaded'] + 1024 * 1024 * 1024 * 10);
                $update['freeslots'] = ($User['freeslots'] + 1);
                $cache->update_row('user_' . $userid, [
                    'invites' => $update['invites'],
                    'freeslots' => $update['freeslots'],
                    'gotgift' => 'yes',
                    'uploaded' => $update['uploaded'],
                ], $site_config['expires']['user_cache']);
                header('Refresh: 5; url=' . $site_config['paths']['baseurl'] . '/index.php');
                stderr(_('Congratulations!'), "<img src='{$site_config['paths']['images_baseurl']}gift.png' alt='" . _('Christmas Gift') . "' title='" . _('Christmas Gift') . "'><h2>" . _('You just got 1 invite , 10 GB upload and 1 bonus freeslot!') . '</h2>' . _fe('Thanks for your support and sharing through year {0}!<br>Merry Christmas and a Happy New Year from the {1} staff.<br>Redirecting in 5..4..3..2..1', date('Y'), $site_config['site']['name']), 'bottom20');
            }
            if ($gift === 'bonus') {
                sql_query("UPDATE users SET invites=invites+3,  seedbonus = seedbonus + 1750, gotgift='yes' WHERE id=" . sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
                $update['invites'] = ($User['invites'] + 3);
                $update['seedbonus'] = ($User['seedbonus'] + 1750);
                $cache->update_row('user_' . $userid, [
                    'invites' => $update['invites'],
                    'gotgift' => 'yes',
                    'seedbonus' => $update['seedbonus'],
                ], $site_config['expires']['user_cache']);
                header('Refresh: 5; url=' . $site_config['paths']['baseurl'] . '/index.php');
                stderr(_('Congratulations!'), "<img src='{$site_config['paths']['images_baseurl']}gift.png' alt='" . _('Christmas Gift') . "' title='" . _('Christmas Gift') . "'><h2>" . _('You just got 3 invites , and 1750 Karma Bonus Points!') . '</h2>' . _fe('Thanks for your support and sharing through year {0}!<br>Merry Christmas and a Happy New Year from the {1} staff.<br>Redirecting in 5..4..3..2..1', date('Y'), $site_config['site']['name']), 'bottom20');
            }
            if ($gift === 'invites') {
                sql_query("UPDATE users SET invites=invites+2, seedbonus = seedbonus + 2000, freeslots=freeslots+3, gotgift='yes' WHERE id=" . sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
                $update['invites'] = ($User['invites'] + 2);
                $update['seedbonus'] = ($User['seedbonus'] + 2000);
                $update['freeslots'] = ($User['freeslots'] + 3);
                $cache->update_row('user_' . $userid, [
                    'invites' => $update['invites'],
                    'freeslots' => $update['freeslots'],
                    'gotgift' => 'yes',
                    'seedbonus' => $update['seedbonus'],
                ], $site_config['expires']['user_cache']);
                header('Refresh: 5; url=' . $site_config['paths']['baseurl'] . '/index.php');
                stderr(_('Congratulations!'), "<img src='{$site_config['paths']['images_baseurl']}gift.png' alt='" . _('Christmas Gift') . "' title='" . _('Christmas Gift') . "'><h2>" . _('You just got 2 invites, 2000 Karma Bonus Points and 3 bonus Freeslots!') . '</h2>' . _fe('Thanks for your support and sharing through year {0}!<br>Merry Christmas and a Happy New Year from the {1} staff.<br>Redirecting in 5..4..3..2..1', date('Y'), $site_config['site']['name']), 'bottom20');
            }
            if ($gift === 'bonus2') {
                sql_query("UPDATE users SET invites=invites+3, uploaded=uploaded+1024*1024*1024*20, seedbonus = seedbonus + 2500, freeslots=freeslots+5, gotgift='yes' WHERE id=" . sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
                $update['invites'] = ($User['invites'] + 3);
                $update['seedbonus'] = ($User['seedbonus'] + 2500);
                $update['freeslots'] = ($User['freeslots'] + 5);
                $update['uploaded'] = ($User['uploaded'] + 1024 * 1024 * 1024 * 20);
                $cache->update_row('user_' . $userid, [
                    'invites' => $update['invites'],
                    'freeslots' => $update['freeslots'],
                    'gotgift' => 'yes',
                    'seedbonus' => $update['seedbonus'],
                    'uploaded' => $update['uploaded'],
                ], $site_config['expires']['user_cache']);
                header('Refresh: 5; url=' . $site_config['paths']['baseurl'] . '/index.php');
                stderr(_('Congratulations!'), "<img src='{$site_config['paths']['images_baseurl']}gift.png' alt='" . _('Christmas Gift') . "' title='" . _('Christmas Gift') . "'><h2>" . _('You just got 3 invites and 1750 Karma Bonus Points!') . '</h2>' . _fe('Thanks for your support and sharing through year {0}!<br>Merry Christmas and a Happy New Year from the {1} staff.<br>Redirecting in 5..4..3..2..1', date('Y'), $site_config['site']['name']), 'bottom20');
            }
        } else {
            stderr(_('Error'), _('You already received your gift!'), 'bottom20');
        }
    } elseif ($today <= $Christmasday) {
        $timezone_name = timezone_name_from_abbr('', $user['time_offset'] * 60 * 60, 0);
        $days = calc_time_difference((int) $Christmasday - $today, true);
        stderr(_('Be patient!'), _fe("You can't open your present until Christmas Day! {0} to go.<br>Today: {1}<br>Christmas Day: {2} [{3}]", $days, get_date((int) TIME_NOW, 'LONG', 1, 0), get_date((int) $Christmasday, 'LONG', 1, 0), $timezone_name), 'bottom20');
    } else {
        stderr(_('Too late!'), _("You missed it, you'll have to wait until Christmas comes again!"), 'bottom20');
    }
}
