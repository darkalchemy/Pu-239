<?php

declare(strict_types = 1);

use Pu239\Cache;
use Pu239\Database;
use Pu239\Message;
use Pu239\Session;
use Pu239\Torrent;
use Pu239\User;

require_once __DIR__ . '/../include/bittorrent.php';
require_once INCL_DIR . 'function_users.php';
require_once CLASS_DIR . 'class_user_options_2.php';
$user = check_user_status();
$lang = array_merge(load_language('global'), load_language('delete'));
global $container, $site_config;

$data = array_merge($_GET, $_POST);
if (empty($data['id'])) {
    stderr($lang['delete_failed'], $lang['delete_missing_data']);
}
$id = !empty($data['id']) ? (int) $data['id'] : 0;
if (!is_valid_id($id)) {
    stderr($lang['delete_failed'], $lang['delete_missing_data']);
}
$dt = TIME_NOW;
$fluent = $container->get(Database::class);
$row = $fluent->from('torrents AS t')
              ->select(null)
              ->select('t.id')
              ->select('t.info_hash')
              ->select('t.owner')
              ->select('t.name')
              ->select('t.seeders')
              ->select('t.added')
              ->select('u.seedbonus')
              ->leftJoin('users AS u ON u.id=t.owner')
              ->where('t.id = ?', $id)
              ->fetch();

if (!$row) {
    stderr($lang['delete_failed'], $lang['delete_not_exist']);
}
if ($user['id'] != $row['owner'] && $user['class'] < UC_STAFF) {
    stderr($lang['delete_failed'], $lang['delete_not_owner']);
}
$rt = (int) $data['reasontype'];
if (!is_int($rt) || $rt < 1 || $rt > 5) {
    stderr($lang['delete_failed'], $lang['delete_invalid']);
}
$reason = $data['reason'];
if ($rt === 1) {
    $reasonstr = $lang['delete_dead'];
} elseif ($rt === 2) {
    $reasonstr = $lang['delete_dupe'] . ($reason[0] ? (': ' . trim($reason[0])) : '!');
} elseif ($rt === 3) {
    $reasonstr = $lang['delete_nuked'] . ($reason[1] ? (': ' . trim($reason[1])) : '!');
} elseif ($rt === 4) {
    if (!$reason[2]) {
        stderr($lang['delete_failed'], $lang['delete_violated']);
    }
    $reasonstr = $site_config['site']['name'] . $lang['delete_rules'] . trim($reason[2]);
} else {
    if (!$reason[3]) {
        stderr($lang['delete_failed'], $lang['delete_reason']);
    }
    $reasonstr = trim($reason[3]);
}
$torrents_class = $container->get(Torrent::class);
$torrents_class->delete_by_id($row['id']);
$torrents_class->remove_torrent($row['info_hash']);

write_log("{$lang['delete_torrent']} $id ({$row['name']}){$lang['delete_deleted_by']}{$user['username']} ($reasonstr)\n");
if ($site_config['bonus']['on']) {
    $user_class = $container->get(User::class);
    $dt = sqlesc($dt - (14 * 86400));
    if ($row['added'] > $dt) {
        $owner = $user_class->getUserFromId($row['owner']);
        if (!empty($owner)) {
            $update = [
                'seedbonus' => $owner['seedbonus'] - $site_config['bonus']['per_delete'],
            ];
            $user_class->update($update, $owner['id']);
        }
    }
}
$msg = "Torrent $id (" . htmlsafechars($row['name']) . ") has been deleted.\n\nReason: $reasonstr";
if ($user['id'] != $row['owner'] && ($user['opt2'] & user_options_2::PM_ON_DELETE) === user_options_2::PM_ON_DELETE) {
    $subject = 'Torrent Deleted';
    $msgs_buffer[] = [
        'receiver' => $row['owner'],
        'added' => $dt,
        'msg' => $msg,
        'subject' => $subject,
    ];
    $messages_class = $container->get(Message::class);
    $messages_class->insert($msgs_buffer);
}
$session = $container->get(Session::class);
$session->set('is-success', $msg);
if (!empty($data['returnto'])) {
    header('Location: ' . htmlsafechars($data['returnto']));
} else {
    header("Location: {$site_config['paths']['baseurl']}/browse.php");
}
