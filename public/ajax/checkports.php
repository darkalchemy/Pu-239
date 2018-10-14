<?php

require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
dbconn();

if (empty($_POST['uid'])) {
    return false;
}

$uid = intval($_POST['uid']);
$sql = 'SELECT INET6_NTOA(ip) AS ip, port, agent FROM peers WHERE userid = ' . sqlesc($uid) . ' GROUP BY ip, port';
$res = sql_query($sql) or sqlerr(__FILE__, __LINE__);

$out = '';
while ($curip = mysqli_fetch_assoc($res)) {
    $uip = $curip['ip'];
    $uport = $curip['port'];
    $uagent = $curip['agent'];
    $connection = @fsockopen($uip, $uport, $errno, $errstr, 10);
    if (is_resource($connection)) {
        $msg = "<span class='has-text-lime'> OPEN</span>";
        fclose($connection);
    } else {
        $msg = "<span class='has-text-red'> CLOSED => $errstr </span>";
    }
    $out .= "
<div class='top20 bottom20 has-text-centered'>
    <div class='columns is-multiline bg-00 round10'>
        <span class='column is-2 padding5'>{$uip}</span>
        <span class='column is-1 padding5'>{$uport}</span>
        <span class='column is-2 padding5'>{$uagent}</span>
        <span class='column padding5 has-text-left'>$msg</span>
    </div>
<div>";
}
$status = ['data' => $out];
header('content-type: application/json');
echo json_encode($status);
