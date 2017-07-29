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

// Default guest user (don't delete this one):
$users[0] = [];
$users[0]['userRole'] = AJAX_CHAT_GUEST;
$users[0]['userName'] = null;
$users[0]['password'] = null;
$users[0]['channels'] = [0];

global $CURUSER;
if (!empty($CURUSER['id'])) {
    $users[$CURUSER['id']]['userRole'] = AJAX_CHAT_USER;
    $users[$CURUSER['id']]['channels'] = [0];
    if ($CURUSER['class'] >= UC_ADMINISTRATOR) {
        $users[$CURUSER['id']]['userRole'] = AJAX_CHAT_ADMIN;
        $users[$CURUSER['id']]['channels'] = [0, 1, 2];
    } elseif ($CURUSER['class'] >= UC_MODERATOR) {
        $users[$CURUSER['id']]['userRole'] = AJAX_CHAT_MODERATOR;
        $users[$CURUSER['id']]['channels'] = [0, 1];
    }
}
