<?php

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'function_users.php';
require_once CLASS_DIR . 'class.bencdec.php';
require_once INCL_DIR . 'function_common.php';
check_user_status();
global $CURUSER, $site_config, $cache, $torrent_stuffs, $user_stuffs;

$lang = array_merge(load_language('global'), load_language('index'));
$userid = isset($_GET['userid']) ? $_GET['userid'] : $CURUSER['id'];
$yes_no = [
    'yes',
    'no',
];
if ($CURUSER['id'] === $userid || $CURUSER['class'] >= UC_ADMINISTRATOR) {
    $user = $user_stuffs->getUserFromId($userid);
    if (!empty($_GET['owner'])) {
        $torrents = $torrent_stuffs->get_all_by_owner($userid);
        $zipfile = USER_TORRENTS_DIR . '[' . $site_config['site_name'] . "]-{$user['username']}_uploaded_torrents.zip";
    } elseif (!empty($_GET['getall']) && in_array($_GET['getall'], $yes_no)) {
        $torrents = $torrent_stuffs->get_all($_GET['getall']);
        $zipfile = USER_TORRENTS_DIR . '[' . $site_config['site_name'] . "]-{$user['username']}_all_torrents.zip";
    } else {
        $torrents = $torrent_stuffs->get_all_snatched($userid);
        $zipfile = USER_TORRENTS_DIR . '[' . $site_config['site_name'] . "]-{$user['username']}_snatched_torrents.zip";
    }
    if (file_exists($zipfile)) {
        unlink($zipfile);
    }
    $zip = new ZipArchive();
    $zip->open($zipfile, ZipArchive::CREATE);

    $announce_url = $site_config['announce_urls'][0];
    if (get_scheme() === 'https') {
        $announce_url = $site_config['announce_urls'][1];
    }

    foreach ($torrents as $t_file) {
        $fn = TORRENTS_DIR . $t_file['id'] . '.torrent';
        $dict = bencdec::decode_file($fn, $site_config['max_torrent_size']);
        $dict['announce'] = "{$announce_url}?torrent_pass={$user['torrent_pass']}";
        $dict['uid'] = $userid;
        $tor = bencdec::encode($dict);
        if ($tor) {
            $filename = "[{$site_config['site_name']}]{$t_file['filename']}";
            $zip->addFromString($filename, $tor);
        }
    }
    $zip->close();

    if (file_exists($zipfile)) {
        header('Content-type: application/zip');
        header('Content-Disposition: attachment; filename="' . basename($zipfile) . '"');
        header('Content-Transfer-Encoding: Binary');
        header('Content-length: ' . filesize($zipfile));
        header('Pragma: no-cache');
        header('Expires: 0');

        ob_clean();
        flush();
        readfile($zipfile);
        unlink($zipfile);
        exit;
    }
} else {
    stderr('Wutt!', 'You do not have the authority to do that.');
}
