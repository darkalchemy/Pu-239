<?php

declare(strict_types = 1);

use Pu239\Phpzip;
use Pu239\Session;
use Pu239\Torrent;
use Pu239\User;

require_once __DIR__ . '/../include/bittorrent.php';
require_once INCL_DIR . 'function_users.php';
require_once CLASS_DIR . 'class.bencdec.php';
require_once INCL_DIR . 'function_common.php';
check_user_status();
$lang = array_merge(load_language('global'), load_language('index'));
global $container, $site_config, $CURUSER;

$userid = isset($_GET['userid']) ? (int) $_GET['userid'] : $CURUSER['id'];
$yes_no = [
    'yes',
    'no',
];
$users_class = $container->get(User::class);
$torrents_class = $container->get(Torrent::class);
if ($CURUSER['id'] === $userid || $CURUSER['class'] >= UC_ADMINISTRATOR) {
    $session = $container->get(Session::class);
    $usessl = $session->get('scheme') === 'http' ? 'http' : 'https';
    $user = $users_class->getUserFromId($userid);
    if (!empty($_GET['owner'])) {
        $torrents = $torrents_class->get_all_by_owner($userid);
        $zipfile = USER_TORRENTS_DIR . '[' . $site_config['site']['name'] . "]-{$user['username']}_uploaded_torrents.zip";
    } elseif (!empty($_GET['getall']) && in_array($_GET['getall'], $yes_no)) {
        $torrents = $torrents_class->get_all($_GET['getall']);
        $zipfile = USER_TORRENTS_DIR . '[' . $site_config['site']['name'] . "]-{$user['username']}_all_torrents.zip";
    } else {
        $torrents = $torrents_class->get_all_snatched($userid);
        $zipfile = USER_TORRENTS_DIR . '[' . $site_config['site']['name'] . "]-{$user['username']}_snatched_torrents.zip";
    }
    if (file_exists($zipfile)) {
        unlink($zipfile);
    }
    $zip = $container->get(Phpzip::class);
    $zip->open($zipfile, ZipArchive::CREATE);
    $announce_url = $site_config['announce_urls'][$usessl][0];
    foreach ($torrents as $t_file) {
        $fn = TORRENTS_DIR . $t_file['id'] . '.torrent';
        $dict = bencdec::decode_file($fn, $site_config['site']['max_torrent_size']);
        $dict['announce'] = "{$announce_url}?torrent_pass={$user['torrent_pass']}";
        $dict['uid'] = $userid;
        $tor = bencdec::encode($dict);
        if ($tor) {
            $filename = "[{$site_config['site']['name']}]{$t_file['filename']}";
            $zip->addFromString($filename, $tor);
        }
    }
    $zip->close();
    $zip->force_download($zipfile);
    unlink($zipfile);
} else {
    stderr('Wutt!', 'You do not have the authority to do that.');
}
