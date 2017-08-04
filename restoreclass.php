<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'user_functions.php';
dbconn(false);
loggedinorreturn();
sql_query("UPDATE users SET override_class='255' WHERE id = " . sqlesc($CURUSER['id']));
$mc1->begin_transaction('MyUser_' . $CURUSER['id']);
$mc1->update_row(false, [
    'override_class' => 255,
]);
$mc1->commit_transaction($INSTALLER09['expires']['curuser']);
$mc1->begin_transaction('user' . $CURUSER['id']);
$mc1->update_row(false, [
    'override_class' => 255,
]);
$mc1->commit_transaction($INSTALLER09['expires']['user_cache']);
header("Location: {$INSTALLER09['baseurl']}/index.php");
die();
