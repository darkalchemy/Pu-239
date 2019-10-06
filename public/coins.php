<?php

declare(strict_types = 1);

use Pu239\Cache;
use Pu239\Message;
use Pu239\Session;

require_once __DIR__ . '/../include/bittorrent.php';
require_once INCL_DIR . 'function_users.php';
$user = check_user_status();
global $container, $site_config;

$id = (int) $_GET['id'];
$points = (int) $_GET['points'];
$dt = TIME_NOW;
if (!is_valid_id($id) || !is_valid_id($points)) {
    die();
}
$pointscangive = [
    10,
    20,
    50,
    100,
    200,
    500,
    1000,
];
$returnto = "details.php?id=$id";
$session = $container->get(Session::class);
if (!in_array($points, $pointscangive)) {
    $session->set('is-warning', _("You can't give that amount of points!"));
    header("Location: $returnto");
    die();
}
$sdsa = sql_query('SELECT 1 FROM coins WHERE torrentid = ' . sqlesc($id) . ' AND userid =' . sqlesc($user['id'])) or sqlerr(__FILE__, __LINE__);
$asdd = mysqli_fetch_assoc($sdsa);
if ($asdd) {
    $session->set('is-warning', _('You already gave points to this torrent.'));
    header("Location: $returnto");
    die();
}
$res = sql_query('SELECT owner,name,points FROM torrents WHERE id=' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
$row = mysqli_fetch_assoc($res) or stderr(_('Error'), _('Torrent was not found'));
$userid = (int) $row['owner'];
if ($userid == $user['id']) {
    $session->set('is-warning', _("You can't give your self points!"));
    header("Location: $returnto");
    die();
}
if ($user['seedbonus'] < $points) {
    $session->set('is-warning', _("You don't have enough points for that!"));
    header("Location: $returnto");
    die();
}
$sql = sql_query('SELECT seedbonus FROM users WHERE id=' . sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
$User = mysqli_fetch_assoc($sql);
sql_query('INSERT INTO coins (userid, torrentid, points) VALUES (' . sqlesc($user['id']) . ', ' . sqlesc($id) . ', ' . sqlesc($points) . ')') or sqlerr(__FILE__, __LINE__);
sql_query('UPDATE users SET seedbonus=seedbonus+' . sqlesc($points) . ' WHERE id=' . sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
sql_query('UPDATE users SET seedbonus=seedbonus-' . sqlesc($points) . ' WHERE id=' . sqlesc($user['id'])) or sqlerr(__FILE__, __LINE__);
sql_query('UPDATE torrents SET points=points+' . sqlesc($points) . ' WHERE id=' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
$msg = _('You have been given') . " $points " . _('points by') . ' ' . $user['username'] . ' ' . _('for torrent') . ' [url=' . $site_config['paths']['baseurl'] . '/details.php?id=' . $id . ']' . htmlsafechars($row['name']) . '[/url].';
$subject = _('You have been given.gift');
$msgs_buffer[] = [
    'receiver' => $userid,
    'added' => $dt,
    'msg' => $msg,
    'subject' => $subject,
];
$messages_class = $container->get(Message::class);
$messages_class->insert($msgs_buffer);
$update['points'] = ($row['points'] + $points);
$update['seedbonus_uploader'] = ($User['seedbonus'] + $points);
$update['seedbonus_donator'] = ($user['seedbonus'] - $points);
$cache = $container->get(Cache::class);
//==The torrent
$cache->update_row('torrent_details_' . $id, [
    'points' => $update['points'],
], $site_config['expires']['torrent_details']);
//==The uploader
$cache->update_row('user_' . $userid, [
    'seedbonus' => $update['seedbonus_uploader'],
], $site_config['expires']['user_cache']);
//==The donator
$cache->update_row('user_' . $user['id'], [
    'seedbonus' => $update['seedbonus_donator'],
], $site_config['expires']['user_cache']);
//== delete the pm keys
$cache->delete('coin_points_' . $id);

$session->set('is-success', _('Successfully gave points to this torrent.'));
header("Location: $returnto");
die();
