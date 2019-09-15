<?php

declare(strict_types = 1);

use Pu239\Database;

require_once __DIR__ . '/../../include/bittorrent.php';
$user = check_user_status();
header('content-type: application/json');
global $container;

if (empty($user) || !has_access($user['class'], UC_STAFF, '')) {
    echo json_encode(['status' => 'invalid']);
    die();
}
$id = (int) $_POST['id'];
$status = $_POST['status'];
if (empty($id) || !isset($status)) {
    echo json_encode(['status' => 'invalid']);
    die();
}
$fluent = $container->get(Database::class);
$to_status = 'pending';
if ($status === 'pending') {
    $to_status = 'approved';
} elseif ($status === 'approved') {
    $to_status = 'denied';
}
$update = [
    'status' => $to_status,
];
try {
    $fluent->update('offers')
           ->set($update)
           ->where('id = ?', $id)
           ->execute();
    echo json_encode(['status' => $to_status]);
    die();
} catch (Exception $e) {
    //TODO
}

echo json_encode(['voted' => 'invalid']);
die();
