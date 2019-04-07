<?php

require_once __DIR__ . '/../include/bittorrent.php';
global $site_config, $fluent, $session;

$lang = load_language('global');
$torrent_pass = $auth = $bot = $owner_id = '';
extract($_POST);
unset($_POST);

if (!empty($bot) && !empty($auth) && !empty($torrent_pass)) {
    $userid = $user_stuffs->get_bot_id($site_config['allowed']['upload'], $bot, $torrent_pass, $auth);
} else {
    $session->set('is-warning', 'The search page is a restricted page, bots only');
    header("Location: {$site_config['paths']['baseurl']}/browse.php");
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
