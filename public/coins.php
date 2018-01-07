<?php
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'user_functions.php';
check_user_status();
global $CURUSER, $site_config, $cache;

$lang = array_merge(load_language('global'), load_language('coins'));

// / Mod by dokty - tbdev.net
$id = (int)$_GET['id'];
$points = (int)$_GET['points'];
if (!is_valid_id($id) || !is_valid_id($points)) {
    die();
}
$pointscangive = [
    '10',
    '20',
    '50',
    '100',
    '200',
    '500',
    '1000',
];
$returnto = "details.php?id=$id";

if (!in_array($points, $pointscangive)) {
    setSessionVar('is-warning', $lang['coins_you_cant_give_that_amount_of_points']);
    header("Location: $returnto");
    die();
}
$sdsa = sql_query('SELECT 1 FROM coins WHERE torrentid=' . sqlesc($id) . ' AND userid =' . sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);
$asdd = mysqli_fetch_assoc($sdsa);
if ($asdd) {
    setSessionVar('is-warning', $lang['coins_you_already_gave_points_to_this_torrent']);
    header("Location: $returnto");
    die();
}
$res = sql_query('SELECT owner,name,points FROM torrents WHERE id = ' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
$row = mysqli_fetch_assoc($res) or stderr($lang['gl_error'], $lang['coins_torrent_was_not_found']);
$userid = (int)$row['owner'];
if ($userid == $CURUSER['id']) {
    setSessionVar('is-warning', $lang['coins_you_cant_give_your_self_points']);
    header("Location: $returnto");
    die();
}
if ($CURUSER['seedbonus'] < $points) {
    setSessionVar('is-warning', $lang['coins_you_dont_have_enough_points']);
    header("Location: $returnto");
    die();
}
$sql = sql_query('SELECT seedbonus FROM users WHERE id = ' . sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
$User = mysqli_fetch_assoc($sql);
sql_query('INSERT INTO coins (userid, torrentid, points) VALUES (' . sqlesc($CURUSER['id']) . ', ' . sqlesc($id) . ', ' . sqlesc($points) . ')') or sqlerr(__FILE__, __LINE__);
sql_query('UPDATE users SET seedbonus=seedbonus+' . sqlesc($points) . ' WHERE id=' . sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
sql_query('UPDATE users SET seedbonus=seedbonus-' . sqlesc($points) . ' WHERE id=' . sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);
sql_query('UPDATE torrents SET points=points+' . sqlesc($points) . ' WHERE id=' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
$msg = sqlesc("{$lang['coins_you_have_been_given']} " . htmlspecialchars($points) . " {$lang['coins_points_by']} " . $CURUSER['username'] . " {$lang['coins_for_torrent']} [url=" . $site_config['baseurl'] . '/details.php?id=' . $id . ']' . htmlspecialchars($row['name']) . '[/url].');
$subject = sqlesc($lang['coins_you_have_been_given_a_gift']);
sql_query("INSERT INTO messages (sender, receiver, msg, added, subject) VALUES(0, " . sqlesc($userid) . ", $msg, " . TIME_NOW . ", $subject)") or sqlerr(__FILE__, __LINE__);
$update['points'] = ($row['points'] + $points);
$update['seedbonus_uploader'] = ($User['seedbonus'] + $points);
$update['seedbonus_donator'] = ($CURUSER['seedbonus'] - $points);
//==The torrent
$cache->update_row('torrent_details_' . $id, [
    'points' => $update['points'],
], $site_config['expires']['torrent_details']);
//==The uploader
$cache->update_row('userstats_' . $userid, [
    'seedbonus' => $update['seedbonus_uploader'],
], $site_config['expires']['u_stats']);
$cache->update_row('user_stats_' . $userid, [
    'seedbonus' => $update['seedbonus_uploader'],
], $site_config['expires']['user_stats']);
//==The donator
$cache->update_row('userstats_' . $CURUSER['id'], [
    'seedbonus' => $update['seedbonus_donator'],
], $site_config['expires']['u_stats']);
$cache->update_row('user_stats_' . $CURUSER['id'], [
    'seedbonus' => $update['seedbonus_donator'],
], $site_config['expires']['user_stats']);
//== delete the pm keys
$cache->increment('inbox_' . $userid);
$cache->delete('coin_points_' . $id);

setSessionVar('is-success', $lang['coins_successfully_gave_points_to_this_torrent']);
header("Location: $returnto");
die();
