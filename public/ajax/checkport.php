<?php

require_once __DIR__ . '/../../include/bittorrent.php';
dbconn();

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
