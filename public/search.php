<?php

declare(strict_types = 1);

use Pu239\Database;
use Pu239\Session;
use Pu239\User;

require_once __DIR__ . '/../include/bittorrent.php';
$lang = load_language('global');
global $container, $site_config;

$torrent_pass = $auth = $bot = $owner_id = '';
extract($_POST);
unset($_POST);

if (!empty($bot) && !empty($auth) && !empty($torrent_pass)) {
    $user_stuffs = $container->get(User::class);
    $userid = $user_stuffs->get_bot_id($site_config['allowed']['upload'], $bot, $torrent_pass, $auth);
} else {
    $session = $container->get(Session::class);
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
    $fluent = $container->get(Database::class);
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
