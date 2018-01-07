<?php
require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
dbconn(false);

if (empty($_POST['uid'])) {
    return false;
}

$uid = intval($_POST['uid']);
$sql = 'SELECT ip, port, agent FROM peers WHERE userid = ' . sqlesc($uid) . ' GROUP BY ip, port';
$res = sql_query($sql) or sqlerr(__FILE__, __LINE__);

$out = '';
while ($curip = mysqli_fetch_assoc($res)) {
    $uip = $curip['ip'];
    $uport = $curip['port'];
    $uagent = $curip['agent'];
    $connection = @fsockopen($uip, $uport, $errno, $errstr, 10);
    if (is_resource($connection)) {
        $msg = "<span class='text-lime'> OPEN</span>";
        fclose($connection);
    } else {
        $msg = "<span class='text-red'> CLOSED => $errstr </span>";
    }
    $out .= "
								<section>
									<input class='text-center' type='text' size='12' value='{$uip}' readonly />
									<input class='text-center' type='text' size='5' value='{$uport}' readonly />
									<input class='text-center' type='text' size='20' value='{$uagent}' readonly />
									<span>$msg</span>
								</section>";
}
$status = ['data' => $out];
header('content-type: application/json');
echo json_encode($status);
