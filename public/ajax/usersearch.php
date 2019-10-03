<?php

declare(strict_types = 1);

use Pu239\User;

require_once __DIR__ . '/../../include/bittorrent.php';
require_once INCL_DIR . 'function_users.php';
check_user_status();
header('content-type: application/json');
global $container;

$term = htmlsafechars(strtolower(strip_tags($_POST['keyword'])));
if (!empty($term)) {
    $users_class = $container->get(User::class);
    $users = $users_class->search_by_username($term);
    if (!empty($users)) {
        echo json_encode($users);
        die();
    }
}
$status = ['data' => _('Invalid Request')];
echo json_encode($status);
die();
