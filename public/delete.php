<?php

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'user_functions.php';
require_once INCL_DIR . 'function_memcache.php';
check_user_status();
global $CURUSER, $site_config, $cache, $session;

$lang = array_merge(load_language('global'), load_language('delete'));
if (!mkglobal('id')) {
    stderr("{$lang['delete_failed']}", "{$lang['delete_missing_data']}");
}
$id = (int) $id;
if (!is_valid_id($id)) {
    stderr("{$lang['delete_failed']}", "{$lang['delete_missing_data']}");
}

/**
 * @param $id
 */
function deletetorrent($id)
{
    global $site_config, $CURUSER, $cache;

    sql_query('DELETE peers.*, files.*, comments.*, snatched.*, thanks.*, bookmarks.*, coins.*, rating.*, torrents.* FROM torrents 
                 LEFT JOIN peers ON peers.torrent = torrents.id
                 LEFT JOIN files ON files.torrent = torrents.id
                 LEFT JOIN comments ON comments.torrent = torrents.id
                 LEFT JOIN thanks ON thanks.torrentid = torrents.id
                 LEFT JOIN bookmarks ON bookmarks.torrentid = torrents.id
                 LEFT JOIN coins ON coins.torrentid = torrents.id
                 LEFT JOIN rating ON rating.torrent = torrents.id
                 LEFT JOIN snatched ON snatched.torrentid = torrents.id
                 WHERE torrents.id =' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
    unlink("{$site_config['torrent_dir']}/$id.torrent");
    $cache->delete('MyPeers_' . $CURUSER['id']);
}

/**
 * @param $id
 */
function deletetorrent_xbt($id)
{
    global $site_config, $CURUSER, $lang, $cache;

    sql_query('UPDATE torrents SET flags = 1 WHERE id = ' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
    sql_query('DELETE files.*, comments.*, thankyou.*, thanks.*, bookmarks.*, coins.*, rating.*, xbt_files_users.* FROM xbt_files_users
                                 LEFT JOIN files ON files.torrent = xbt_files_users.fid
                                 LEFT JOIN comments ON comments.torrent = xbt_files_users.fid
                                 LEFT JOIN thankyou ON thankyou.torid = xbt_files_users.fid
                                 LEFT JOIN thanks ON thanks.torrentid = xbt_files_users.fid
                                 LEFT JOIN bookmarks ON bookmarks.torrentid = xbt_files_users.fid
                                 LEFT JOIN coins ON coins.torrentid = xbt_files_users.fid
                                 LEFT JOIN rating ON rating.torrent = xbt_files_users.fid
                                 WHERE xbt_files_users.fid =' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
    unlink("{$site_config['torrent_dir']}/$id.torrent");
    $cache->delete('MyPeers_XBT_' . $CURUSER['id']);
}

$res = sql_query('SELECT name, owner, seeders FROM torrents WHERE id =' . sqlesc($id));
$row = mysqli_fetch_assoc($res);
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
if (XBT_TRACKER) {
    deletetorrent_xbt($id);
} else {
    deletetorrent($id);
    remove_torrent_peers($id);
}
$cache->deleteMulti([
    'lastest_tor_',
    'top5_tor_',
    'last5_tor_',
    'scroll_tor_',
    'torrent_details_' . $id,
    'torrent_details_text' . $id,
]);
write_log("{$lang['delete_torrent']} $id ({$row['name']}){$lang['delete_deleted_by']}{$CURUSER['username']} ($reasonstr)\n");
if ($site_config['seedbonus_on'] == 1) {
    sql_query('UPDATE users SET seedbonus = seedbonus-' . sqlesc($site_config['bonus_per_delete']) . ' WHERE id = ' . sqlesc($row['owner'])) or sqlerr(__FILE__, __LINE__);
    $update['seedbonus'] = ($CURUSER['seedbonus'] - $site_config['bonus_per_delete']);
    $cache->update_row('user' . $row['owner'], [
        'seedbonus' => $update['seedbonus'],
    ], $site_config['expires']['user_cache']);
}
$message = "Torrent $id (" . htmlsafechars($row['name']) . ") has been deleted.\n  Reason: $reasonstr";
if ($CURUSER['id'] != $row['owner'] && $CURUSER['pm_on_delete'] === 'yes') {
    $added = TIME_NOW;
    $pm_on = (int) $row['owner'];
    $subject = 'Torrent Deleted';
    sql_query('INSERT INTO messages (subject, sender, receiver, msg, added) VALUES(' . sqlesc($subject) . ', 0, ' . sqlesc($pm_on) . ',' . sqlesc($message) . ", $added)") or sqlerr(__FILE__, __LINE__);
    $cache->increment('inbox_' . $pm_on);
}

$session->set('is-success', $message);
if (!empty($_POST['returnto'])) {
    header('Location: ' . htmlsafechars($_POST['returnto']));
} else {
    header("Location: {$site_config['baseurl']}/browse.php");
}
