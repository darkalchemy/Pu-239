<?php

require_once __DIR__ . '/../../include/bittorrent.php';
require_once INCL_DIR . 'function_users.php';
global $session, $user_stuffs, $cache;

header('content-type: application/json');
if (empty($_POST['csrf']) || !$session->validateToken($_POST['csrf'])) {
    $status = ['data' => 'Invalid CSRF Token'];
    echo json_encode($status);
    die();
}

$term = htmlsafechars(strtolower(strip_tags($_POST['keyword'])));
if (!empty($term)) {
    $users = $user_stuffs->search_by_username($term);
    if (!empty($users)) {
        echo json_encode($users);
        die();
    }
}
$status = ['data' => 'Invalid Request'];
echo json_encode($status);
die();
