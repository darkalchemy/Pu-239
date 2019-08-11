<?php

declare(strict_types = 1);

use Pu239\Database;
use Pu239\Message;
use Pu239\Torrent;
use Pu239\User;

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
    if ($tid === 0) {
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
                  ->select('t.name')
                  ->select('t.owner')
                  ->select('t.size')
                  ->select('u.id AS uid')
                  ->select('u.username')
                  ->select('u.seedbonus')
                  ->select('s.downloaded AS sd')
                  ->select('u.downloaded AS ud')
                  ->select('s.uploaded as su')
                  ->select('u.uploaded as uu')
                  ->leftJoin('snatched as s ON s.torrentid = t.id')
                  ->leftJoin('users AS u ON u.id = s.userid')
                  ->where('t.id = ?', $tid);
    $users = $container->get(User::class);
    foreach ($row as $a) {
        $hash = $a['info_hash'];
        $newd = $a['ud'] > 0 && $a['ud'] > $a['sd'] ? $a['ud'] - $a['sd'] : 0;
        $tname = htmlsafechars($a['name']);
        if (!empty($a['uid'])) {
            $msg = $lang['datareset_hey'] . htmlsafechars($a['username']) . "\n";
            $msg .= $lang['datareset_looks'] . htmlsafechars($a['name']) . $lang['datareset_nuked'];
            $msg .= $lang['datareset_down'] . mksize($a['sd']) . $lang['datareset_downbe'] . mksize($newd) . "\n";
            if ($a['owner'] === $a['uid']) {
                $update = [
                    'seedbonus' => $a['seedbonus'] - $site_config['bonus']['per_delete'],
                    'uploaded' => $a['uu'] > 0 && $a['uu'] > $a['su'] ? $a['uu'] - $a['su'] : 0,
                ];
            } else {
                $update = [
                    'seedbonus' => $a['seedbonus'] + $site_config['bonus']['per_download'],
                    'downloaded' => $newd,
                ];
            }
            $user->update($update, $a['uid']);
            $msgs_buffer[] = [
                'receiver' => $a['uid'],
                'added' => TIME_NOW,
                'msg' => $msg,
                'subject' => 'Torrent Data has been Reset',
            ];
        }
    }
    $message_class = $container->get(Message::class);
    if (!empty($msgs_buffer)) {
        $messages_class->insert($msgs_buffer);
    }
    $torrents_class = $container->get(Torrent::class);
    $torrents_class->delete_by_id($tid);
    $torrents_class->remove_torrent($hash);

    write_log($lang['datareset_torr'] . $tname . $lang['datareset_wdel'] . htmlsafechars($CURUSER['username']) . $lang['datareset_allusr']);
    header('Refresh: 3; url=staffpanel.php?tool=datareset');
    stderr($lang['datareset_stderr'], $lang['datareset_pls']);
} else {
    $form = "
    <form action='{$_SERVER['PHP_SELF']}?tool=datareset&amp;action=datareset' method='post' enctype='multipart/form-data' accept-charset='utf-8'>
    <div class='has-text-centered'>
        <h1>{$lang['datareset_reset']}</h1>
        <label for='tid'>{$lang['datareset_tid']}</label>
        <input type='number' name='tid' id='tid' class='left10'>
        <div style='background:#990033;' class='has-text-left padding10 top20 bottom20 round5'>
            <ul>
                <li>{$lang['datareset_tid_info']}</li>
                <li>{$lang['datareset_info']}</li>
                <li>{$lang['datareset_info1']}</b></li>
            </ul>
        </div>
        <input type='submit' value='{$lang['datareset_repay']}' class='button is-small margin20'>
    </div>
    </form>";

    $HTMLOUT .= main_div($form);
    echo stdhead($lang['datareset_stdhead']) . wrapper($HTMLOUT) . stdfoot();
}
