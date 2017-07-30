<?php
/*
 * @package AJAX_Chat
 * @author Sebastian Tschan
 * @copyright (c) Sebastian Tschan
 * @license Modified MIT License
 * @link https://blueimp.net/ajax/
 */

// List containing the registered chat users:
$users = [];

global $CURUSER;
if (!empty($CURUSER['id'])) {
    $users[$CURUSER['id']]['userRole'] = $CURUSER['class'];
    $users[$CURUSER['id']]['channels'] = [0];
    if ($CURUSER['class'] >= UC_ADMINISTRATOR) {
        $users[$CURUSER['id']]['channels'] = [0, 1, 2];
    } elseif ($CURUSER['class'] >= UC_MODERATOR) {
        $users[$CURUSER['id']]['channels'] = [0, 1];
    }
}
