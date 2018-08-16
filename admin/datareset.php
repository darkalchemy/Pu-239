<?php

require_once INCL_DIR . 'user_functions.php';
require_once INCL_DIR . 'html_functions.php';
require_once INCL_DIR . 'function_memcache.php';
require_once CLASS_DIR . 'class_check.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
global $CURUSER, $site_config, $lang, $cache, $fluent, $torrent_stuffs;

$lang = array_merge($lang, load_language('ad_datareset'));
$HTMLOUT = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tid = (isset($_POST['tid']) ? (int) $_POST['tid'] : 0);
    if ($tid == 0) {
        stderr($lang['datareset_stderr'], $lang['datareset_stderr1']);
    }
    if (get_row_count('torrents', 'where id = ' . sqlesc($tid)) != 1) {
        stderr($lang['datareset_stderr'], $lang['datareset_stderr2']);
    }
    $row = $fluent->from('torrents AS t')
        ->select(null)
        ->select('t.id AS tid')
        ->select('t.info_hash')
        ->select('t.owner AS uid')
        ->select('t.name')
        ->select('t.size')
        ->select('t.seeders')
        ->select('u.seedbonus')
        ->select('u.username')
        ->select('s.downloaded AS sd')
        ->select('u.downloaded AS ud')
        ->leftJoin('users AS u ON u.id = s.userid')
        ->leftJoin('snatched as s ON s.torrentid = t.id')
        ->where('t.id = ?', $id);

    foreach ($row as $a) {
        $newd = ($a['ud'] > 0 ? $a['ud'] - $a['sd'] : 0);
        $new_download[] = '(' . $a['uid'] . ',' . $newd . ')';
        $tname = htmlsafechars($a['name']);
        $msg = $lang['datareset_hey'] . htmlsafechars($a['username']) . "\n";
        $msg .= $lang['datareset_looks'] . htmlsafechars($a['name']) . $lang['datareset_nuked'];
        $msg .= $lang['datareset_down'] . mksize($a['sd']) . $lang['datareset_downbe'] . mksize($newd) . "\n";
        $pms[] = '(0,' . sqlesc($a['uid']) . ',' . TIME_NOW . ',' . sqlesc($msg) . ')';
        $cache->update_row('user' . $a['uid'], [
            'downloaded' => $new_download,
        ], $site_config['expires']['curuser']);
    }
    sql_query('INSERT INTO messages (sender, receiver, added, msg) VALUES ' . implode(', ', array_map('sqlesc', $pms))) or sqlerr(__FILE__, __LINE__);
    sql_query('INSERT INTO users (id,downloaded) VALUES ' . implode(', ', array_map('sqlesc', $new_download)) . ' ON DUPLICATE KEY UPDATE downloaded = VALUES(downloaded)') or sqlerr(__FILE__, __LINE__);
    $torrent_stuffs->delete_by_id($a['id']);
    remove_torrent($a['info_hash']);

    write_log($lang['datareset_torr'] . $tname . $lang['datareset_wdel'] . htmlsafechars($CURUSER['username']) . $lang['datareset_allusr']);
    header('Refresh: 3; url=staffpanel.php?tool=datareset');
    stderr($lang['datareset_stderr'], $lang['datareset_pls']);
} else {
    $HTMLOUT .= begin_frame();
    $HTMLOUT .= "<form action='staffpanel.php?tool=datareset&amp;action=datareset' method='post'>
    <fieldset>
    <legend>{$lang['datareset_reset']}</legend>
 <table width='500' style='border-collapse:collapse'>
        <tr><td nowrap='nowrap'>{$lang['datareset_tid']}</td><td width='100%'><input type='text' name='tid' size='20' /></td></tr>
        <tr><td style='background:#990033; color:#CCCCCC;' colspan='2'>
            <ul>
                    <li>{$lang['datareset_tid_info']}</li>
                    <li>{$lang['datareset_info']}</li>
                    <li>{$lang['datareset_info1']}</b></li>
                </ul>
            </td></tr>
            <tr><td colspan='2'><input type='submit' value='{$lang['datareset_repay']}' /></td></tr>
        </table>
    </fieldset>
    </form>";
    $HTMLOUT .= end_frame();
    echo stdhead($lang['datareset_stdhead']) . wrapper($HTMLOUT) . stdfoot();
}
