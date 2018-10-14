<?php

require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'user_functions.php';
global $session, $user_stuffs, $cache;

header('content-type: application/json');
if (!$session->validateToken($_POST['csrf'])) {
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
