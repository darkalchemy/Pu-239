<?php

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
global $site_config, $fluent, $session;

$lang = load_language('global');
$torrent_pass = $auth = $bot = $owner_id = '';
extract($_POST);
unset($_POST);

if (!empty($bot) && !empty($auth)) {
    $userid = $fluent->from('users')
        ->select(null)
        ->select('id')
        ->where('class > ? AND username = ? AND auth = ? AND torrent_pass = ? AND uploadpos = 1 AND suspended = "no"', UC_UPLOADER, $bot, $auth, $torrent_pass)
        ->fetch('id');
} else {
    $session->set('is-warning', 'The search page is a restricted page, bots only');
    header("Location: {$site_config['baseurl']}/browse.php");
    die();
}

header('content-type: application/json');
if (empty($userid)) {
    echo json_encode(['msg' => 'invalid user credentials']);
    die();
}

$table = $body = $heading = '';
if (!empty($search)) {
    $results = $fluent->from('torrents')
        ->select(null)
        ->select('id')
        ->select('name')
        ->select('hex(info_hash) AS info_hash')
        ->where('name LIKE ?', "%$search%")
        ->fetchAll();

    if ($results) {
        echo json_encode($results);
        die();
    } else {
        echo json_encode(['msg' => 'no results for: ' . $search]);
        die();
    }
}
