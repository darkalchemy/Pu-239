<?php

declare(strict_types = 1);

use Pu239\Database;
use Pu239\Session;
use Pu239\User;

require_once __DIR__ . '/../include/bittorrent.php';
global $container, $site_config;

$data = array_merge($_GET, $_POST);
$torrent_pass = $data['torrent_pass'];  // user torrent_pass
$auth = $data['auth'];                  // users 'auth' key
$bot = $data['bot'];                    // users useranem
$search = $data['search'];              // search term
if (!empty($bot) && !empty($auth) && !empty($torrent_pass)) {
    $users_class = $container->get(User::class);
    $userid = $users_class->get_bot_id($bot, $torrent_pass, $auth);
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
$status = $users_class->get_item('status', $userid);
if ($status === 5) {
    echo json_encode(['msg' => "Permission denied, you're account is suspended"]);
    die();
} elseif ($status === 2) {
    echo json_encode(['msg' => "Permission denied, you're account is disabled"]);
    die();
} elseif ($status === 1) {
    echo json_encode(['msg' => "Permission denied, you're account is parked"]);
    die();
}
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
