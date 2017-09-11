<?php
require_once realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..') . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'user_functions.php';
check_user_status();
sql_query("UPDATE users SET override_class='255' WHERE id = " . sqlesc($CURUSER['id']));
$mc1->begin_transaction('MyUser_' . $CURUSER['id']);
$mc1->update_row(false, [
    'override_class' => 255,
]);
$mc1->commit_transaction($site_config['expires']['curuser']);
$mc1->begin_transaction('user' . $CURUSER['id']);
$mc1->update_row(false, [
    'override_class' => 255,
]);
$mc1->commit_transaction($site_config['expires']['user_cache']);
header("Location: {$site_config['baseurl']}/index.php");
exit();
