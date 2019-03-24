<?php

require_once __DIR__ . '/../include/bittorrent.php';
require_once INCL_DIR . 'function_users.php';
require_once CLASS_DIR . 'class_user_options_2.php';
check_user_status();
global $CURUSER, $site_config, $cache, $session, $fluent, $torrent_stuffs, $message_stuffs;

$lang = array_merge(load_language('global'), load_language('delete'));
if (!mkglobal('id')) {
    stderr("{$lang['delete_failed']}", "{$lang['delete_missing_data']}");
}
$id = (int) $id;
if (!is_valid_id($id)) {
    stderr("{$lang['delete_failed']}", "{$lang['delete_missing_data']}");
}
$dt = TIME_NOW;
$row = $fluent->from('torrents AS t')
    ->select(null)
    ->select('t.id')
    ->select('t.info_hash')
    ->select('t.owner')
    ->select('t.name')
    ->select('t.seeders')
    ->select('t.added')
    ->select('u.seedbonus')
    ->leftJoin('users AS u ON u.id = t.owner')
    ->where('t.id = ?', $id)
    ->fetch();

if (!$row) {
    stderr("{$lang['delete_failed']}", "{$lang['delete_not_exist']}");
}
if ($CURUSER['id'] != $row['owner'] && $CURUSER['class'] < UC_STAFF) {
    stderr("{$lang['delete_failed']}", "{$lang['delete_not_owner']}\n");
}
$rt = (int) $_POST['reasontype'];
if (!is_int($rt) || $rt < 1 || $rt > 5) {
    stderr("{$lang['delete_failed']}", "{$lang['delete_invalid']}");
}
$reason = $_POST['reason'];
if ($rt == 1) {
    $reasonstr = "{$lang['delete_dead']}";
} elseif ($rt == 2) {
    $reasonstr = "{$lang['delete_dupe']}" . ($reason[0] ? (': ' . trim($reason[0])) : '!');
} elseif ($rt == 3) {
    $reasonstr = "{$lang['delete_nuked']}" . ($reason[1] ? (': ' . trim($reason[1])) : '!');
} elseif ($rt == 4) {
    if (!$reason[2]) {
        stderr("{$lang['delete_failed']}", "{$lang['delete_violated']}");
    }
    $reasonstr = $site_config['site_name'] . "{$lang['delete_rules']}" . trim($reason[2]);
} else {
    if (!$reason[3]) {
        stderr("{$lang['delete_failed']}", "{$lang['delete_reason']}");
    }
    $reasonstr = trim($reason[3]);
}

$torrent_stuffs->delete_by_id($row['id']);
$torrent_stuffs->remove_torrent($row['info_hash']);

write_log("{$lang['delete_torrent']} $id ({$row['name']}){$lang['delete_deleted_by']}{$CURUSER['username']} ($reasonstr)\n");
if ($site_config['seedbonus_on']) {
    $dt = sqlesc($dt - (14 * 86400));
    if ($row['added'] > $dt) {
        sql_query('UPDATE users SET seedbonus = seedbonus - ' . sqlesc($site_config['bonus_per_delete']) . ' WHERE id = ' . sqlesc($row['owner'])) or sqlerr(__FILE__, __LINE__);
        $update['seedbonus'] = ($row['seedbonus'] - $site_config['bonus_per_delete']);
        $cache->update_row('user_' . $row['owner'], [
            'seedbonus' => $update['seedbonus'],
        ], $site_config['expires']['user_cache']);
    }
}
$msg = "Torrent $id (" . htmlsafechars($row['name']) . ") has been deleted.\n  Reason: $reasonstr";
if ($CURUSER['id'] != $row['owner'] && ($CURUSER['opt2'] & user_options_2::PM_ON_DELETE) === user_options_2::PM_ON_DELETE) {
    $subject = 'Torrent Deleted';
    $msgs_buffer[] = [
        'sender' => 0,
        'receiver' => $row['owner'],
        'added' => $dt,
        'msg' => $msg,
        'subject' => $subject,
    ];
    $message_stuffs->insert($msgs_buffer);
}

$session->set('is-success', $msg);
if (!empty($_POST['returnto'])) {
    header('Location: ' . htmlsafechars($_POST['returnto']));
} else {
    header("Location: {$site_config['baseurl']}/browse.php");
}
