<?php

declare(strict_types = 1);

use Pu239\Database;

require_once __DIR__ . '/../../include/bittorrent.php';
$user = check_user_status();
header('content-type: application/json');
global $container;

if (empty($user)) {
    echo json_encode(['vote' => 'invalid']);
    die();
}
$id = (int) $_POST['id'];
$voted = $_POST['voted'];
if (empty($id) || !isset($voted)) {
    echo json_encode(['voted' => 'invalid']);
    die();
}
$fluent = $container->get(Database::class);
if ($voted === 'yes') {
    $update = [
        'vote' => 'no',
    ];
    try {
        $fluent->update('request_votes')
               ->set($update)
               ->where('user_id = ?', $user['id'])
               ->where('request_id = ?', $id)
               ->execute();
        echo json_encode(['voted' => 'no']);
        die();
    } catch (Exception $e) {
        // TODO
    }
} elseif ($voted === 'no') {
    try {
        $fluent->deleteFrom('request_votes')
               ->where('user_id = ?', $user['id'])
               ->where('request_id = ?', $id)
               ->execute();
        echo json_encode(['voted' => 0]);
        die();
    } catch (Exception $e) {
        // TODO
    }
} else {
    $values = [
        'vote' => 'yes',
        'user_id' => $user['id'],
        'request_id' => $id,
    ];
    try {
        $fluent->insertInto('request_votes')
               ->values($values)
               ->execute();
        echo json_encode(['voted' => 'yes']);
        die();
    } catch (Exception $e) {
        // TODO
    }
}
echo json_encode(['voted' => 'invalid']);
die();
