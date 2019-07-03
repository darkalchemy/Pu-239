<?php

declare(strict_types = 1);

use Delight\Auth\Auth;
use Pu239\Cache;
use Pu239\Database;
use Pu239\User;

require_once __DIR__ . '/../../include/bittorrent.php';
header('content-type: application/json');
global $container;

$auth = $container->get(Auth::class);
$current_user = (int) $auth->getUserId();
if (!empty($current_user)) {
    $users_class = $container->get(User::class);
    $class = $users_class->get_item('class', $current_user);
}
if (empty($current_user) || $class < UC_STAFF) {
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
