<?php
require_once realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..') . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
check_user_status();
global $site_config;
$pm_what = isset($_POST['pm_what']) && $_POST['pm_what'] == 'last10' ? 'last10' : 'owner';
$reseedid = (int)$_POST['reseedid'];
$uploader = (int)$_POST['uploader'];
$use_subject = true;
$subject = 'Request reseed!';
$pm_msg = 'User ' . $CURUSER['username'] . ' asked for a reseed on torrent ' . $site_config['baseurl'] . '/details.php?id=' . $reseedid . " !\nThank You!";
$pms = [];
if ($pm_what == 'last10') {
    $res = sql_query('SELECT s.userid, s.torrentid FROM snatched AS s WHERE s.torrentid =' . sqlesc($reseedid) . " AND s.seeder = 'yes' LIMIT 10") or sqlerr(__FILE__, __LINE__);
    while ($row = mysqli_fetch_assoc($res)) {
        $pms[] = '(0,' . sqlesc($row['userid']) . ',' . TIME_NOW . ',' . sqlesc($pm_msg) . ($use_subject ? ',' . sqlesc($subject) : '') . ')';
    }
} elseif ($pm_what == 'owner') {
    $pms[] = "(0, $uploader, " . TIME_NOW . ', ' . sqlesc($pm_msg) . ($use_subject ? ', ' . sqlesc($subject) : '') . ')';
}
if (count($pms) > 0) {
    sql_query('INSERT INTO messages (sender, receiver, added, msg ' . ($use_subject ? ', subject' : '') . ' ) VALUES ' . join(',', $pms)) or sqlerr(__FILE__, __LINE__);
    setSessionVar('is-success', 'PM was sent! Now wait for a seeder!');
} else {
    setSessionVar('is-warning', 'There were no users to PM!');
}
sql_query('UPDATE torrents set last_reseed = ' . TIME_NOW . ' WHERE id = ' . sqlesc($reseedid)) or sqlerr(__FILE__, __LINE__);
$mc1->begin_transaction('torrent_details_' . $reseedid);
$mc1->update_row(false, [
    'last_reseed' => TIME_NOW,
]);
$mc1->commit_transaction($site_config['expires']['torrent_details']);
if ($site_config['seedbonus_on'] == 1) {
    sql_query('UPDATE users SET seedbonus = seedbonus-10.0 WHERE id = ' . sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);
    $update['seedbonus'] = ($CURUSER['seedbonus'] - 10);
    $mc1->begin_transaction('userstats_' . $CURUSER['id']);
    $mc1->update_row(false, [
        'seedbonus' => $update['seedbonus'],
    ]);
    $mc1->commit_transaction($site_config['expires']['u_stats']);
    $mc1->begin_transaction('user_stats_' . $CURUSER['id']);
    $mc1->update_row(false, [
        'seedbonus' => $update['seedbonus'],
    ]);
    $mc1->commit_transaction($site_config['expires']['user_stats']);
}

header("Refresh: 0; url=./details.php?id=$reseedid");
