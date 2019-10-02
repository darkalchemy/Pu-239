<?php

declare(strict_types = 1);

use Pu239\Cache;
use Pu239\Session;

require_once __DIR__ . '/../include/bittorrent.php';
require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_bbcode.php';
$user = check_user_status();
global $container, $site_config;

$id = $user['id'];
$min = 1;
$max = 38;
$rand = (int) random_int((int) $min, (int) $max);
$res = sql_query('SELECT achpoints FROM usersachiev WHERE userid=' . sqlesc($id) . ' AND achpoints>= 1') or sqlerr(__FILE__, __LINE__);
$row = mysqli_fetch_row($res);
$count = $row['0'];
$session = $container->get(Session::class);
$cache = $container->get(Cache::class);
if (!$count) {
    $session->set('is-warning', _('It appears that you currently have no Achievement Bonus Points available to spend.'));
    header("Refresh: 3; url=achievementhistory.php?id=$id");
    stderr(_('No Achievement Bonus Points'), _('It appears that you currently have no Achievement Bonus Points available to spend.'));
    die();
}
$HTMLOUT = '';
$get_bonus = sql_query('SELECT * FROM ach_bonus WHERE bonus_id=' . sqlesc($rand)) or sqlerr(__FILE__, __LINE__);
$bonus = mysqli_fetch_assoc($get_bonus);
$bonus_desc = htmlsafechars($bonus['bonus_desc']);
$bonus_type = (int) $bonus['bonus_type'];
$bonus_do = htmlsafechars($bonus['bonus_do']);
$get_d = sql_query('SELECT * FROM users WHERE id=' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
$dn = mysqli_fetch_assoc($get_d);
$down = (float) $dn['downloaded'];
$up = (float) $dn['uploaded'];
$invite = (int) $dn['invites'];
$karma = (float) $dn['seedbonus'];
if ($bonus_type === 1) {
    if ($down >= $bonus_do) {
        $msg = '' . _('Congratulations') . ', ' . _('you have just won') . " $bonus_desc";
        sql_query('UPDATE usersachiev SET achpoints = achpoints - 1, spentpoints = spentpoints + 1 WHERE userid=' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
        $sql = 'UPDATE users SET downloaded = downloaded - ' . sqlesc($bonus_do) . ' WHERE id=' . sqlesc($id);
        sql_query($sql) or sqlerr(__FILE__, __LINE__);
    } elseif ($down < $bonus_do) {
        $msg = '' . _('Congratulations') . ', ' . _('your downloaded total has been reset from a negative value back to 0') . '';
        sql_query('UPDATE usersachiev SET achpoints = achpoints - 1, spentpoints = spentpoints + 1 WHERE userid=' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
        $sql = "UPDATE users SET downloaded = '0' WHERE id =" . sqlesc($id);
        sql_query($sql) or sqlerr(__FILE__, __LINE__);
    }
} elseif ($bonus_type == 2) {
    $msg = '' . _('Congratulations') . ', ' . _('you have just won') . " $bonus_desc";
    sql_query('UPDATE usersachiev SET achpoints = achpoints - 1, spentpoints = spentpoints + 1 WHERE userid=' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
    $sql = 'UPDATE users SET uploaded = uploaded + ' . sqlesc($bonus_do) . ' WHERE id=' . sqlesc($id);
    sql_query($sql) or sqlerr(__FILE__, __LINE__);
} elseif ($bonus_type == 3) {
    $msg = '' . _('Congratulations') . ', ' . _('you have just won') . " $bonus_desc";
    sql_query('UPDATE usersachiev SET achpoints = achpoints - 1, spentpoints = spentpoints + 1 WHERE userid=' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
    $sql = 'UPDATE users SET invites = invites + ' . sqlesc($bonus_do) . ' WHERE id=' . sqlesc($id);
    sql_query($sql) or sqlerr(__FILE__, __LINE__);
} elseif ($bonus_type == 4) {
    $msg = '' . _('Congratulations') . ', ' . _('you have just won') . " $bonus_desc";
    sql_query('UPDATE usersachiev SET achpoints = achpoints - 1, spentpoints = spentpoints + 1 WHERE userid=' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
    $sql = 'UPDATE users SET seedbonus = seedbonus + ' . sqlesc($bonus_do) . ' WHERE id=' . sqlesc($id);
    sql_query($sql) or sqlerr(__FILE__, __LINE__);
} elseif ($bonus_type == 5) {
    $rand_fail = random_int(1, 5);
    if ($rand_fail == 1) {
        $msg = '' . _('Sorry') . ', ' . _('Dunk64 has just run over you with his ultra-powered wheelchair. Better luck next time.') . '';
        sql_query('UPDATE usersachiev SET achpoints = achpoints - 1, spentpoints = spentpoints + 1 WHERE userid=' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
    } elseif ($rand_fail == 2) {
        $msg = '' . _('Sorry') . ', ' . _('We put your achievement bonus point into the collection plate in an attempt to get Ducktape a prostitute.') . '';
        sql_query('UPDATE usersachiev SET achpoints = achpoints - 1, spentpoints = spentpoints + 1 WHERE userid=' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
    } elseif ($rand_fail == 3) {
        $msg = '' . _('Sorry') . ', ' . _('The evil villian Adam has stolen your bonus point.') . '';
        sql_query('UPDATE usersachiev SET achpoints = achpoints - 1, spentpoints = spentpoints + 1 WHERE userid=' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
    } elseif ($rand_fail == 4) {
        $msg = '' . _('Sorry') . ', ' . _('Somehelp has used your achievement bonus point in attempt to buy puppy chow to lure doggies into his dinner plate.') . '';
        sql_query('UPDATE usersachiev SET achpoints = achpoints - 1, spentpoints = spentpoints + 1 WHERE userid=' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
    } elseif ($rand_fail == 5) {
        $msg = '' . _('Sorry') . ', ' . _('Hoodini has magically made your achievement bonus point dissapear, better luck next time.') . '';
        sql_query('UPDATE usersachiev SET achpoints = achpoints - 1, spentpoints = spentpoints + 1 WHERE userid=' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
    }
}
if (isset($bonus_type)) {
    $cache->delete('user_' . $id);
}
header("Refresh: 3; url=achievementhistory.php?id=$id");
stderr(_('Random Achievement Bonus'), "$msg");
$title = _('Random Bonus');
$breadcrumbs = [
    "<a href='{$_SERVER['PHP_SELF']}'>$title</a>",
];
echo stdhead($title, [], 'page-wrapper', $breadcrumbs) . wrapper($HTMLOUT) . stdfoot();
