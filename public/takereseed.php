<?php

require_once dirname(__FILE__, 2).DIRECTORY_SEPARATOR.'include'.DIRECTORY_SEPARATOR.'bittorrent.php';
check_user_status();
global $CURUSER, $site_config, $cache, $session;

$pm_what = isset($_POST['pm_what']) && 'last10' == $_POST['pm_what'] ? 'last10' : 'owner';
$reseedid = (int) $_POST['reseedid'];
$uploader = (int) $_POST['uploader'];
$use_subject = true;
$subject = 'Request reseed!';
$pm_msg = 'User '.$CURUSER['username'].' asked for a reseed on torrent '.$site_config['baseurl'].'/details.php?id='.$reseedid." !\nThank You!";
$pms = [];
if ('last10' == $pm_what) {
    $res = sql_query('SELECT s.userid, s.torrentid FROM snatched AS s WHERE s.torrentid ='.sqlesc($reseedid)." AND s.seeder = 'yes' LIMIT 10") or sqlerr(__FILE__, __LINE__);
    while ($row = mysqli_fetch_assoc($res)) {
        $pms[] = '(0,'.sqlesc($row['userid']).','.TIME_NOW.','.sqlesc($pm_msg).($use_subject ? ','.sqlesc($subject) : '').')';
    }
} elseif ('owner' == $pm_what) {
    $pms[] = "(0, $uploader, ".TIME_NOW.', '.sqlesc($pm_msg).($use_subject ? ', '.sqlesc($subject) : '').')';
}
if (count($pms) > 0) {
    sql_query('INSERT INTO messages (sender, receiver, added, msg '.($use_subject ? ', subject' : '').' ) VALUES '.join(',', $pms)) or sqlerr(__FILE__, __LINE__);
    $session->set('is-success', 'PM was sent! Now wait for a seeder!');
} else {
    $session->set('is-warning', 'There were no users to PM!');
}
sql_query('UPDATE torrents SET last_reseed = '.TIME_NOW.' WHERE id = '.sqlesc($reseedid)) or sqlerr(__FILE__, __LINE__);
$cache->update_row('torrent_details_'.$reseedid, [
    'last_reseed' => TIME_NOW,
], $site_config['expires']['torrent_details']);
if (1 == $site_config['seedbonus_on']) {
    sql_query('UPDATE users SET seedbonus = seedbonus-10.0 WHERE id = '.sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);
    $update['seedbonus'] = ($CURUSER['seedbonus'] - 10);
    $cache->update_row('user'.$CURUSER['id'], [
        'seedbonus' => $update['seedbonus'],
    ], $site_config['expires']['user_cache']);
}

header("Refresh: 0; url=./details.php?id=$reseedid");
