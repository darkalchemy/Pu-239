<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
check_user_status();
$query1 = sprintf('UPDATE users SET curr_ann_id = 0, curr_ann_last_check = \'0\' ' . 'WHERE id = %s AND curr_ann_id != 0', sqlesc($CURUSER['id']));
sql_query($query1);
$mc1->begin_transaction('user' . $CURUSER['id']);
$mc1->update_row(false, [
    'curr_ann_id'         => 0,
    'curr_ann_last_check' => 0,
]);
$mc1->commit_transaction($site_config['expires']['user_cache']);
$mc1->begin_transaction('MyUser_' . $CURUSER['id']);
$mc1->update_row(false, [
    'curr_ann_id'         => 0,
    'curr_ann_last_check' => 0,
]);
$mc1->commit_transaction($site_config['expires']['curuser']);
header("Location: {$site_config['baseurl']}/index.php");
