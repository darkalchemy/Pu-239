<?php

require_once dirname(__FILE__).DIRECTORY_SEPARATOR.'include'.DIRECTORY_SEPARATOR.'bittorrent.php';
require_once INCL_DIR.'user_functions.php';
require_once INCL_DIR.'bbcode_functions.php';
check_user_status();
global $CURUSER, $site_config, $session, $cache;

$lang = array_merge(load_language('global'), load_language('achievementbonus'));
$id = (int) $CURUSER['id'];
$min = 1;
$max = 32;
$rand = (int) random_int((int) $min, (int) $max);
$res = sql_query('SELECT achpoints FROM usersachiev WHERE userid = '.sqlesc($id).' AND achpoints >= 1') or sqlerr(__FILE__, __LINE__);
$row = mysqli_fetch_row($res);
$count = $row['0'];
if (!$count) {
    $session->set('is-warning', $lang['achbon_no_ach_bon_pnts_msg']);
    header("Refresh: 3; url=achievementhistory.php?id=$id");
    stderr($lang['achbon_no_ach_bon_pnts'], $lang['achbon_no_ach_bon_pnts_msg']);
    die();
}
$HTMLOUT = '';
$get_bonus = sql_query('SELECT * FROM ach_bonus WHERE bonus_id = '.sqlesc($rand)) or sqlerr(__FILE__, __LINE__);
$bonus = mysqli_fetch_assoc($get_bonus);
$bonus_desc = htmlsafechars($bonus['bonus_desc']);
$bonus_type = htmlsafechars($bonus['bonus_type']);
$bonus_do = htmlsafechars($bonus['bonus_do']);
$get_d = sql_query('SELECT * FROM users WHERE id = '.sqlesc($id)) or sqlerr(__FILE__, __LINE__);
$dn = mysqli_fetch_assoc($get_d);
$down = (float) $dn['downloaded'];
$up = (float) $dn['uploaded'];
$invite = (int) $dn['invites'];
$karma = (float) $dn['seedbonus'];
if (1 == $bonus_type) {
    if ($down >= $bonus_do) {
        $msg = "{$lang['achbon_congratulations']}, {$lang['achbon_you_hv_just_won']} $bonus_desc";
        sql_query('UPDATE usersachiev SET achpoints = achpoints-1, spentpoints = spentpoints+1 WHERE userid = '.sqlesc($id)) or sqlerr(__FILE__, __LINE__);
        $cache->delete('user_achievement_points_'.$id);
        $sql = 'UPDATE users SET downloaded = downloaded - '.sqlesc($bonus_do).' WHERE id = '.sqlesc($id);
        sql_query($sql) or sqlerr(__FILE__, __LINE__);
        $cache->update_row('user'.$id, [
            'downloaded' => $down - $bonus_do,
        ], $site_config['expires']['user_cache']);
    }
    if ($down < $bonus_do) {
        $msg = "{$lang['achbon_congratulations']}, {$lang['achbon_your_dl_been_reset_0']}";
        sql_query('UPDATE usersachiev SET achpoints = achpoints-1, spentpoints = spentpoints+1 WHERE userid = '.sqlesc($id)) or sqlerr(__FILE__, __LINE__);
        $cache->delete('user_achievement_points_'.$id);
        $sql = "UPDATE users SET downloaded = '0' WHERE id =".sqlesc($id);
        sql_query($sql) or sqlerr(__FILE__, __LINE__);
        $cache->update_row('user'.$id, [
            'downloaded' => 0,
        ], $site_config['expires']['user_cache']);
    }
}
if (2 == $bonus_type) {
    $msg = "{$lang['achbon_congratulations']}, {$lang['achbon_you_hv_just_won']} $bonus_desc";
    sql_query('UPDATE usersachiev SET achpoints = achpoints-1, spentpoints = spentpoints+1 WHERE userid = '.sqlesc($id)) or sqlerr(__FILE__, __LINE__);
    $cache->delete('user_achievement_points_'.$id);
    $sql = 'UPDATE users SET uploaded = uploaded + '.sqlesc($bonus_do).' WHERE id = '.sqlesc($id);
    sql_query($sql) or sqlerr(__FILE__, __LINE__);
    $cache->update_row('user'.$id, [
        'uploaded' => $up + $bonus_do,
    ], $site_config['expires']['user_cache']);
}
if (3 == $bonus_type) {
    $msg = "{$lang['achbon_congratulations']}, {$lang['achbon_you_hv_just_won']} $bonus_desc";
    sql_query('UPDATE usersachiev SET achpoints = achpoints-1, spentpoints = spentpoints+1 WHERE userid = '.sqlesc($id)) or sqlerr(__FILE__, __LINE__);
    $cache->delete('user_achievement_points_'.$id);
    $sql = 'UPDATE users SET invites = invites + '.sqlesc($bonus_do).' WHERE id = '.sqlesc($id);
    sql_query($sql) or sqlerr(__FILE__, __LINE__);
    $cache->update_row('user'.$id, [
        'invites' => $invite + $bonus_do,
    ], $site_config['expires']['user_cache']);
}
if (4 == $bonus_type) {
    $msg = "{$lang['achbon_congratulations']}, {$lang['achbon_you_hv_just_won']} $bonus_desc";
    sql_query('UPDATE usersachiev SET achpoints = achpoints-1, spentpoints = spentpoints+1 WHERE userid = '.sqlesc($id)) or sqlerr(__FILE__, __LINE__);
    $cache->delete('user_achievement_points_'.$id);
    $sql = 'UPDATE users SET seedbonus = seedbonus + '.sqlesc($bonus_do).' WHERE id = '.sqlesc($id);
    sql_query($sql) or sqlerr(__FILE__, __LINE__);
    $cache->update_row('user'.$id, [
        'seedbonus' => $karma + $bonus_do,
    ], $site_config['expires']['user_cache']);
}
if (5 == $bonus_type) {
    $rand_fail = random_int(1, 5);
    if (1 == $rand_fail) {
        $msg = "{$lang['gl_sorry']}, {$lang['achbon_failed_msg1']}";
        sql_query('UPDATE usersachiev SET achpoints = achpoints-1, spentpoints = spentpoints+1 WHERE userid = '.sqlesc($id)) or sqlerr(__FILE__, __LINE__);
        $cache->delete('user_achievement_points_'.$id);
    }
    if (2 == $rand_fail) {
        $msg = "{$lang['gl_sorry']}, {$lang['achbon_failed_msg2']}";
        sql_query('UPDATE usersachiev SET achpoints = achpoints-1, spentpoints = spentpoints+1 WHERE userid = '.sqlesc($id)) or sqlerr(__FILE__, __LINE__);
        $cache->delete('user_achievement_points_'.$id);
    }
    if (3 == $rand_fail) {
        $msg = "{$lang['gl_sorry']}, {$lang['achbon_failed_msg3']}";
        sql_query('UPDATE usersachiev SET achpoints = achpoints-1, spentpoints = spentpoints+1 WHERE userid = '.sqlesc($id)) or sqlerr(__FILE__, __LINE__);
        $cache->delete('user_achievement_points_'.$id);
    }
    if (4 == $rand_fail) {
        $msg = "{$lang['gl_sorry']}, {$lang['achbon_failed_msg4']}";
        sql_query('UPDATE usersachiev SET achpoints = achpoints-1, spentpoints = spentpoints+1 WHERE userid = '.sqlesc($id)) or sqlerr(__FILE__, __LINE__);
        $cache->delete('user_achievement_points_'.$id);
    }
    if (5 == $rand_fail) {
        $msg = "{$lang['gl_sorry']}, {$lang['achbon_failed_msg5']}";
        sql_query('UPDATE usersachiev SET achpoints = achpoints-1, spentpoints = spentpoints+1 WHERE userid = '.sqlesc($id)) or sqlerr(__FILE__, __LINE__);
        $cache->delete('user_achievement_points_'.$id);
    }
}
header("Refresh: 3; url=achievementhistory.php?id=$id");
stderr($lang['achbon_random_achievement_bonus'], "$msg");
echo stdhead($lang['achbon_std_head']).$HTMLOUT.stdfoot();
