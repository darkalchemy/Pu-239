<?php

require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
global $site_config, $fluent, $session, $user_stuffs, $cache;


header('content-type: application/json');
if (empty($_POST['csrf']) || !$session->validateToken($_POST['csrf'])) {
    echo json_encode(['show_in_navbar' => 'csrf']);
    die();
}

$current_user = $session->get('userID');
$class = $user_stuffs->get_item('class', $current_user);
if (empty($current_user) || $class < UC_STAFF) {
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
$result = $fluent->update('staffpanel')
    ->set($set)
    ->where('id = ?', $_POST['id'])
    ->execute();

if ($result) {
    $cache->delete('staff_panels_' . $class);
    $data['show_in_navbar'] = $show;
    echo json_encode($data);
    die();
} else {
    $data['show_in_navbar'] = 'fail';
    echo json_encode($data);
    die();
}
