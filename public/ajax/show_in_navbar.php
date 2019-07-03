<?php

declare(strict_types = 1);

use Pu239\Cache;
use Pu239\Database;

require_once __DIR__ . '/../../include/bittorrent.php';
$user = check_user_status();

header('content-type: application/json');
global $container;

if (empty($user) || $user['class'] < UC_STAFF) {
    echo json_encode(['show_in_navbar' => 'class']);
    die();
}

if (!isset($_POST['show']) || empty($_POST['id'])) {
    echo json_encode(['show_in_navbar' => 'invalid']);
    die();
}

$show = $_POST['show'] == 0 ? 1 : 0;
$set = [
    'navbar' => $show,
];
$fluent = $container->get(Database::class);
$result = $fluent->update('staffpanel')
                 ->set($set)
                 ->where('id = ?', $_POST['id'])
                 ->execute();

if ($result) {
    $cache = $container->get(Cache::class);
    $cache->delete('staff_panels_' . $class);
    $data['show_in_navbar'] = $show;
    echo json_encode($data);
    die();
} else {
    $data['show_in_navbar'] = 'fail';
    echo json_encode($data);
    die();
}
