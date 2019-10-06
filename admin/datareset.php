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
global $container, $CURUSER, $site_config;

$HTMLOUT = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tid = (isset($_POST['tid']) ? (int) $_POST['tid'] : 0);
    if ($tid === 0) {
        stderr(_('Error'), _('Invalid ID.'));
    }
    $fluent = $container->get(Database::class);
    $torrents = $fluent->from('torrents')
                       ->select(null)
                       ->select('COUNT(id) AS count')
                       ->where('id = ?', $tid)
                       ->fetch('count');

    if (empty($torrents)) {
        stderr(_('Error'), _('Invalid ID.'));
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
            $msg = _fe('Hey, {0}', htmlsafechars($a['username'])) . "\n";
            $msg .= _fe('Looks like torrent {0} has been nuked and we want to take back the data you downloaded!', htmlsafechars($a['name']));
            $msg .= _fe('So you downloaded {0} your new download will be {1}', mksize($a['sd']), mksize($newd)) . "\n";
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

    write_log(_fe('Torrent {0} was deleted by {1} and all users were Re-Paid Download credit.', $tname, htmlsafechars($CURUSER['username'])));
    header('Refresh: 3; url=staffpanel.php?tool=datareset');
    stderr(_('Success'), _fe('It worked! Long live {0} - Please wait while you are re-directed!', $site_config['site']['name']));
} else {
    $form = "
    <form action='{$_SERVER['PHP_SELF']}?tool=datareset&amp;action=datareset' method='post' enctype='multipart/form-data' accept-charset='utf-8'>
    <div class='has-text-centered'>
        <h1>" . _('Reset Ratio for nuked torrents') . "</h1>
        <label for='tid'>" . _('Torrent id') . "</label>
        <input type='number' name='tid' id='tid' class='left10'>
        <div style='background:#990033;' class='has-text-left padding10 top20 bottom20 round5'>
            <ul>
                <li>" . _('Torrent id must be a number and only a number!') . '</li>
                <li>' . _("If the torrent is not nuked or there is not problem with it , don't use this as it will delete the torrent and any other entries associated with it!") . '</li>
                <li>' . _("If you don't know what this will do, go play somewhere else") . "</b></li>
            </ul>
        </div>
        <input type='submit' value='" . _('Re-pay!') . "' class='button is-small margin20'>
    </div>
    </form>";

    $HTMLOUT .= main_div($form);
    $title = _('Data Reset Manager');
    $breadcrumbs = [
        "<a href='{$site_config['paths']['baseurl']}/staffpanel.php'>" . _('Staff Panel') . '</a>',
        "<a href='{$_SERVER['PHP_SELF']}'>$title</a>",
    ];
    echo stdhead($title, [], 'page-wrapper', $breadcrumbs) . wrapper($HTMLOUT) . stdfoot();
}
