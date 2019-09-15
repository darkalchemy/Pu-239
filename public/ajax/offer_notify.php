<?php

declare(strict_types = 1);

use Pu239\Database;

require_once __DIR__ . '/../../include/bittorrent.php';
$user = check_user_status();
header('content-type: application/json');
global $container;

if (empty($user)) {
    echo json_encode(['notify' => 'invalid']);
    die();
}
$id = (int) $_POST['id'];
$notified = (bool) $_POST['notified'];
if (empty($id) || !isset($notified)) {
    echo json_encode(['notify' => 'invalid']);
    die();
}
$fluent = $container->get(Database::class);
if ($notified) {
    try {
        $fluent->deleteFrom('offer_notify')
               ->where('userid = ?', $user['id'])
               ->where('offerid = ?', $id)
               ->execute();
        echo json_encode(['notify' => 0]);
        die();
    } catch (Exception $e) {
        // TODO
    }
} else {
    $values = [
        'userid' => $user['id'],
        'offerid' => $id,
    ];
    try {
        $notify_id = $fluent->insertInto('offer_notify')
                            ->values($values)
                            ->execute();

        echo json_encode(['notify' => $notify_id]);
        die();
    } catch (Exception $e) {
        // TODO
    }
}
echo json_encode(['notify' => 'invalid']);
die();
