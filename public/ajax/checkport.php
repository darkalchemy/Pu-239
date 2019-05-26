<?php

declare(strict_types = 1);

require_once __DIR__ . '/../../include/bittorrent.php';

if (empty($_POST['ip']) || empty($_POST['port'])) {
    return false;
}
$ip = $_POST['ip'];
$port = $_POST['port'];

$connection = @fsockopen($ip, $port, $errno, $errstr, 10);
if (is_resource($connection)) {
    $msg = [
        'class' => 'has-text-success',
        'text' => 'OPEN',
    ];
    fclose($connection);
} else {
    $msg = [
        'class' => 'has-text-danger',
        'text' => "CLOSED => $errstr",
    ];
}
$status = ['data' => $msg];
header('content-type: application/json');
echo json_encode($status);
