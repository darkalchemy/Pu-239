<?php

declare(strict_types = 1);

use Pu239\Cache;
use Pu239\Database;
use Pu239\Torrent;

require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_html.php';
require_once CLASS_DIR . 'class_check.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
$lang = array_merge($lang, load_language('ad_datareset'));
global $container, $CURUSER, $site_config;

$HTMLOUT = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tid = (isset($_POST['tid']) ? (int) $_POST['tid'] : 0);
    if ($tid == 0) {
        stderr($lang['datareset_stderr'], $lang['datareset_stderr1']);
    }
    $fluent = $container->get(Database::class);
    $torrents = $fluent->from('torrents')
                       ->select(null)
                       ->select('COUNT(id) AS count')
                       ->where('id = ?', $tid)
                       ->fetch('count');

    if (empty($torrents)) {
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
                  ->leftJoin('users AS u ON u.id=s.userid')
                  ->leftJoin('snatched as s ON s.torrentid=t.id')
                  ->where('t.id = ?', $id);

    $pms = $new_download = [];
    foreach ($row as $a) {
        $newd = ($a['ud'] > 0 ? $a['ud'] - $a['sd'] : 0);
        $new_download[] = '(' . $a['uid'] . ',' . $newd . ')';
        $tname = htmlsafechars($a['name']);
        $msg = $lang['datareset_hey'] . htmlsafechars($a['username']) . "\n";
        $msg .= $lang['datareset_looks'] . htmlsafechars($a['name']) . $lang['datareset_nuked'];
        $msg .= $lang['datareset_down'] . mksize($a['sd']) . $lang['datareset_downbe'] . mksize($newd) . "\n";
        $pms[] = '(0,' . sqlesc($a['uid']) . ',' . TIME_NOW . ',' . sqlesc($msg) . ')';
        $cache = $container->get(Cache::class);
        $cache->update_row('user_' . $a['uid'], [
            'downloaded' => $new_download,
        ], $site_config['expires']['curuser']);
    }
    sql_query('INSERT INTO messages (sender, receiver, added, msg) VALUES ' . implode(', ', array_map('sqlesc', $pms))) or sqlerr(__FILE__, __LINE__);
    sql_query('INSERT INTO users (id,downloaded) VALUES ' . implode(', ', array_map('sqlesc', $new_download)) . ' ON DUPLICATE KEY UPDATE downloaded = VALUES(downloaded)') or sqlerr(__FILE__, __LINE__);
    $torrent_stuffs = $container->get(Torrent::class);
    $torrent_stuffs->delete_by_id((int) $a['id']);
    $torrent_stuffs->remove_torrent($a['info_hash']);

    write_log($lang['datareset_torr'] . $tname . $lang['datareset_wdel'] . htmlsafechars($CURUSER['username']) . $lang['datareset_allusr']);
    header('Refresh: 3; url=staffpanel.php?tool=datareset');
    stderr($lang['datareset_stderr'], $lang['datareset_pls']);
} else {
    $form = "
    <form action='{$site_config['paths']['baseurl']}/staffpanel.php?tool=datareset&amp;action=datareset' method='post' accept-charset='utf-8'>
    <div class='has-text-centered'>
        <h1>{$lang['datareset_reset']}</h1>
        <label for='tid'>{$lang['datareset_tid']}</label>
        <input type='text' name='tid' id='tid' required pattern='\d+'>
        <div style='background:#990033; color:#CCCCCC;' class='has-text-left padding10 top20 bottom20 round5'>
            <ul>
                <li>{$lang['datareset_tid_info']}</li>
                <li>{$lang['datareset_info']}</li>
                <li>{$lang['datareset_info1']}</b></li>
            </ul>
        </div>
        <input type='submit' value='{$lang['datareset_repay']}' class='button is-small'>
    </div>
    </form>";

    $HTMLOUT .= main_div($form);
    echo stdhead($lang['datareset_stdhead']) . wrapper($HTMLOUT) . stdfoot();
}
