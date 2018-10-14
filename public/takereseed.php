<?php

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
check_user_status();
global $CURUSER, $site_config, $cache, $session, $message_stuffs;

$pm_what = isset($_POST['pm_what']) && $_POST['pm_what'] === 'last10' ? 'last10' : 'owner';
$reseedid = (int) $_POST['reseedid'];
$uploader = (int) $_POST['uploader'];
$name = $_POST['name'];
if (!$session->validateToken($_POST['csrf'])) {
    $session->set('is-warning', 'CSRF Token Verification Failed.');
    header("Refresh: 0; url={$site_config['baseurl']}/details.php?id=$reseedid");
}
$dt = TIME_NOW;
$subject = 'Request reseed!';
$msg = "@{$CURUSER['username']} asked for a reseed on [url={$site_config['baseurl']}/details.php?id={$reseedid}][class=has-text-lime]{$name}[/class][/url]![br][br]Thank You!";
$msgs_buffer = [];
if ($pm_what === 'last10') {
    $res = sql_query('SELECT s.userid, s.torrentid FROM snatched AS s WHERE s.torrentid =' . sqlesc($reseedid) . " AND s.seeder = 'yes' LIMIT 10") or sqlerr(__FILE__, __LINE__);
    while ($row = mysqli_fetch_assoc($res)) {
        $msgs_buffer[] = [
            'sender' => 0,
            'receiver' => $row['userid'],
            'added' => $dt,
            'msg' => $msg,
            'subject' => $subject,
        ];
    }
} elseif ($pm_what === 'owner') {
    $msgs_buffer[] = [
        'sender' => 0,
        'receiver' => $uploader,
        'added' => $dt,
        'msg' => $msg,
        'subject' => $subject,
    ];
}

if (count($msgs_buffer) > 0) {
    $message_stuffs->insert($msgs_buffer);
    sql_query('INSERT INTO messages (sender, receiver, added, msg ' . ($use_subject ? ', subject' : '') . ' ) VALUES ' . implode(', ', $msgs_buffer)) or sqlerr(__FILE__, __LINE__);
    $session->set('is-success', 'PM was sent! Now wait for a seeder!');
} else {
    $session->set('is-warning', 'There were no users to PM!');
}
sql_query('UPDATE torrents SET last_reseed = ' . $dt . ' WHERE id = ' . sqlesc($reseedid)) or sqlerr(__FILE__, __LINE__);
$cache->update_row('torrent_details_' . $reseedid, [
    'last_reseed' => $dt,
], $site_config['expires']['torrent_details']);
if ($site_config['seedbonus_on'] == 1) {
    sql_query('UPDATE users SET seedbonus = seedbonus-10.0 WHERE id = ' . sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);
    $update['seedbonus'] = ($CURUSER['seedbonus'] - 10);
    $cache->update_row('user' . $CURUSER['id'], [
        'seedbonus' => $update['seedbonus'],
    ], $site_config['expires']['user_cache']);
}

header("Refresh: 0; url={$site_config['baseurl']}/details.php?id=$reseedid");
