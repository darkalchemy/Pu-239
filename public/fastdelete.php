<?php
require_once realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..') . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'user_functions.php';
require_once INCL_DIR . 'function_memcache.php';
check_user_status();
global $CURUSER, $site_config, $cache;

$lang = array_merge(load_language('global'), load_language('fastdelete'));
if (!in_array($CURUSER['id'], $site_config['is_staff']['allowed'])) {
    stderr($lang['fastdelete_error'], $lang['fastdelete_no_acc']);
}

if (!isset($_GET['id']) || !is_valid_id($_GET['id'])) {
    stderr("{$lang['fastdelete_error']}", "{$lang['fastdelete_error_id']}");
}

$id = (int)$_GET['id'];

/**
 * @param $id
 */
function deletetorrent($id)
{
    global $site_config, $cache, $CURUSER;
    sql_query('DELETE torrents.* FROM torrents WHERE torrents.id = ' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
    unlink("{$site_config['torrent_dir']}/$id.torrent");
    $cache->delete('MyPeers_' . $CURUSER['id']);
}

/**
 * @param $id
 */
function deletetorrent_xbt($id)
{
    global $site_config, $cache, $CURUSER, $lang;
    sql_query('UPDATE torrents SET flags = 1 WHERE id = ' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
    sql_query('DELETE files.*, comments.*, thankyou.*, thanks.*, thumbsup.*, bookmarks.*, coins.*, rating.*, xbt_files_users.* FROM xbt_files_users
                                     LEFT JOIN files ON files.torrent = xbt_files_users.fid
                                     LEFT JOIN comments ON comments.torrent = xbt_files_users.fid
                                     LEFT JOIN thankyou ON thankyou.torid = xbt_files_users.fid
                                     LEFT JOIN thanks ON thanks.torrentid = xbt_files_users.fid
                                     LEFT JOIN bookmarks ON bookmarks.torrentid = xbt_files_users.fid
                                     LEFT JOIN coins ON coins.torrentid = xbt_files_users.fid
                                     LEFT JOIN rating ON rating.torrent = xbt_files_users.fid
                                     LEFT JOIN thumbsup ON thumbsup.torrentid = xbt_files_users.fid
                                     WHERE xbt_files_users.fid =' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
    unlink("{$site_config['torrent_dir']}/$id.torrent");
    $cache->delete('MyPeers_XBT_' . $CURUSER['id']);
}

$q = mysqli_fetch_assoc(sql_query('SELECT name, owner FROM torrents WHERE id = ' . sqlesc($id))) or sqlerr(__FILE__, __LINE__);
if (!$q) {
    stderr('Oopps', 'Something went wrong - Contact admin!!');
}

$sure = (isset($_GET['sure']) && (int)$_GET['sure']);
if (!$sure) {
    $returnto = !empty($_GET['returnto']) ? '&amp;returnto=' . urlencode($_GET['returnto']) : '';
    stderr("{$lang['fastdelete_sure']}", sprintf($lang['fastdelete_sure_msg'], $returnto));
}

if (XBT_TRACKER == true) {
    deletetorrent_xbt($id);
} else {
    deletetorrent($id);
    remove_torrent_peers($id);
}
$cache->delete('top5_tor_');
$cache->delete('last5_tor_');
$cache->delete('scroll_tor_');
$cache->delete('torrent_details_' . $id);
$cache->delete('torrent_details_text' . $id);
if ($CURUSER['id'] != $q['owner']) {
    $msg = sqlesc("{$lang['fastdelete_msg_first']} [b]{$q['name']}[/b] {$lang['fastdelete_msg_last']} {$CURUSER['username']}");
    sql_query('INSERT INTO messages (sender, receiver, added, msg) VALUES (0, ' . sqlesc($q['owner']) . ', ' . TIME_NOW . ", {$msg})") or sqlerr(__FILE__, __LINE__);
}
write_log("{$lang['fastdelete_log_first']} {$q['name']} {$lang['fastdelete_log_last']} {$CURUSER['username']}");
if ($site_config['seedbonus_on'] == 1) {
    //===remove karma
    sql_query('UPDATE users SET seedbonus = seedbonus-' . sqlesc($site_config['bonus_per_delete']) . ' WHERE id = ' . sqlesc($q['owner'])) or sqlerr(__FILE__, __LINE__);
    $update['seedbonus'] = ($CURUSER['seedbonus'] - $site_config['bonus_per_delete']);
    $cache->update_row('userstats_' . $q['owner'], [
        'seedbonus' => $update['seedbonus'],
    ], $site_config['expires']['u_stats']);
    $cache->update_row('user_stats_' . $q['owner'], [
        'seedbonus' => $update['seedbonus'],
    ], $site_config['expires']['user_stats']);
    //===end
}

if (isset($_GET['returnto'])) {
    $ret = "<a href='" . htmlsafechars($_GET['returnto']) . "'>{$lang['fastdelete_returnto']}</a>";
} else {
    $ret = "<a href='{$site_config['baseurl']}/index.php'>{$lang['fastdelete_index']}</a>";
}

$HTMLOUT = '';
$HTMLOUT .= "<h2>{$lang['fastdelete_deleted']}</h2>
    <p>{$ret}</p>";

echo stdhead("{$lang['fastdelete_head']}") . $HTMLOUT . stdfoot();
