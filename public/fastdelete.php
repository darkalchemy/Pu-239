<?php

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'user_functions.php';
require_once INCL_DIR . 'html_functions.php';
check_user_status();
global $CURUSER, $site_config, $cache, $session, $fluent, $torrent_stuffs;

$lang = array_merge(load_language('global'), load_language('fastdelete'));
if ($CURUSER['class'] < UC_STAFF) {
    stderr($lang['fastdelete_error'], $lang['fastdelete_no_acc']);
}

if (!isset($_GET['id']) || !is_valid_id($_GET['id'])) {
    stderr("{$lang['fastdelete_error']}", "{$lang['fastdelete_error_id']}");
}

$id = (int) $_GET['id'];

$tid = $fluent->from('torrents AS t')
    ->select(null)
    ->select('t.id')
    ->select('t.info_hash')
    ->select('t.owner')
    ->select('t.name')
    ->select('t.added')
    ->select('u.seedbonus')
    ->leftJoin('users AS u ON u.id = t.owner')
    ->where('t.id = ?', $id)
    ->fetch();

if (!$tid) {
    stderr('Oops', 'Something went wrong - Contact admin!!');
}

$sure = isset($_GET['sure']) && (int) $_GET['sure'];
if (!$sure) {
    $returnto = !empty($_GET['returnto']) ? '&amp;returnto=' . urlencode($_GET['returnto']) : '';
    stderr("{$lang['fastdelete_sure']}", sprintf($lang['fastdelete_sure_msg'], $returnto));
}

$torrent_stuffs->delete_by_id($tid['id']);
$torrent_stuffs->remove_torrent($tid['info_hash']);

if ($CURUSER['id'] != $tid['owner']) {
    $msg = sqlesc("{$lang['fastdelete_msg_first']} [b]{$tid['name']}[/b] {$lang['fastdelete_msg_last']} {$CURUSER['username']}");
    sql_query('INSERT INTO messages (sender, receiver, added, msg) VALUES (0, ' . sqlesc($tid['owner']) . ', ' . TIME_NOW . ", {$msg})") or sqlerr(__FILE__, __LINE__);
}
write_log("{$lang['fastdelete_log_first']} {$tid['name']} {$lang['fastdelete_log_last']} {$CURUSER['username']}");
if ($site_config['seedbonus_on'] == 1) {
    $dt = sqlesc(TIME_NOW - (14 * 86400)); // lose karma if deleted within 2 weeks
    if ($tid['added'] > $dt) {
        $sb = $tid['seedbonus'] - $site_config['bonus_per_delete'];
        $set = [
            'seedbonus' => $sb,
        ];
        $fluent->update('users')
            ->set($set)
            ->where('id = ?', $tid['owner'])
            ->execute();

        $cache->update_row('user' . $tid['owner'], [
            'seedbonus' => $sb,
        ], $site_config['expires']['user_cache']);
    }
}

$session->set('is-success', '[h2]Torrent deleted[/h2][p]' . htmlsafechars($tid['name']) . '[/p]');
if (isset($_GET['returnto'])) {
    header("Location: {$site_config['baseurl']}{$_GET['returnto']}");
} else {
    header("Location: {$site_config['baseurl']}");
}
die();
