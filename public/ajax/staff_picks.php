<?php

require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
global $site_config, $fluent, $cache, $session, $user_stuffs;

extract($_POST);

header('content-type: application/json');
if (empty($csrf) || !$session->validateToken($csrf)) {
    echo json_encode(['pick' => 'csrf']);
    die();
}

$current_user = $session->get('userID');
$class = $user_stuffs->get_item('class', $current_user);
if (empty($current_user) || $class < UC_STAFF) {
    echo json_encode(['pick' => 'csrf']);
    die();
}

if (!isset($pick) || empty($id)) {
    echo json_encode(['pick' => 'invalid']);
    die();
}

$staff_picks = $pick == 0 ? TIME_NOW : 0;
$set = [
    'staff_picks' => $staff_picks,
];
$result = $fluent->update('torrents')
    ->set($set)
    ->where('id = ?', $id)
    ->execute();

if ($result) {
    $cache->delete('staff_picks_');
    $data['staff_pick'] = $staff_picks;
    echo json_encode($data);
    die();
} else {
    $data['staff_pick'] = 'fail';
    echo json_encode($data);
    die();
}
