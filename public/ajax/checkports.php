<?php

declare(strict_types = 1);

use Pu239\Database;

require_once __DIR__ . '/../../include/bittorrent.php';
$user = check_user_status();
if (empty($_POST['uid'])) {
    return false;
}
global $container;

$uid = has_access($user['class'], UC_STAFF, '') ? (int) $_POST['uid'] : $user['id'];
$fluent = $container->get(Database::class);
$ips = $fluent->from('peers')
    ->select(null)
    ->select('INET6_NTOA(ip) AS ip')
    ->select('port')
    ->select('agent')
    ->where('userid = ?', $uid)
    ->fetchAll();
$out = '';
$used_ips = [];
foreach ($ips as $curip) {
    $uip = $curip['ip'];
    $uport = $curip['port'];
    $uagent = $curip['agent'];
    if (in_array($uip . $uport, $used_ips)) {
        continue;
    }
    $used_ips[] = $uip . $uport;
    $connection = fsockopen($uip, $uport, $errno, $errstr, 10);
    if (is_resource($connection)) {
        $msg = "<span class='has-text-success'>" . _('OPEN') . '</span>';
        fclose($connection);
    } else {
        $msg = "<span class='has-text-danger'>" . _fe('CLOSED => {0}', $errstr) . '</span>';
    }
    $out .= "
    <div class='columns is-multiline is-gapless padding10'>
        <span class='column is-2 padding5'>{$uip}</span>
        <span class='column is-1 padding5'>{$uport}</span>
        <span class='column is-2 padding5'>{$uagent}</span>
        <span class='column padding5 has-text-left'>$msg</span>
    </div>";
}
$status = ['data' => $out];
header('content-type: application/json');
echo json_encode($status);
