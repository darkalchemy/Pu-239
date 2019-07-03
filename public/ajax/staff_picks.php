<?php

declare(strict_types = 1);

use Pu239\Cache;
use Pu239\Database;

require_once __DIR__ . '/../../include/bittorrent.php';
$user = check_user_status();
header('content-type: application/json');
global $container;

if (empty($user) || $user['class'] < UC_STAFF) {
    echo json_encode(['pick' => 'csrf']);
    die();
}
$pick = (int) $_POST['pick'];
$id = (int) $_POST['id'];
if (!isset($pick) || empty($id)) {
    echo json_encode(['pick' => 'invalid']);
    die();
}

$staff_picks = $pick === 0 ? TIME_NOW : 0;
$set = [
    'staff_picks' => $staff_picks,
];
$fluent = $container->get(Database::class);
$result = $fluent->update('torrents')
                 ->set($set)
                 ->where('id = ?', $id)
                 ->execute();

if ($result) {
    $cache = $container->get(Cache::class);
    $cache->delete('staff_picks_');
    $data['staff_pick'] = $staff_picks;
    echo json_encode($data);
    die();
} else {
    $data['staff_pick'] = 'fail';
    echo json_encode($data);
    die();
}
