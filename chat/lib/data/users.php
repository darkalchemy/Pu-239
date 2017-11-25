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

global $cache;
$sql = "SELECT id, class FROM users";
if (($users = $cache->get('chat_users_list')) === false) {
    $res = sql_query($sql) or sqlerr(__FILE__, __LINE__);
    while ($user = mysqli_fetch_assoc($res)) {
        $id = $class = '';
        extract($user);
        $users[ $id ]['userRole'] = $class;
        $users[ $id ]['channels'] = [0, 1, 2, 3, 4];
        if ($class >= UC_ADMINISTRATOR) {
            $users[ $id ]['channels'] = [0, 1, 2, 3, 4, 5, 6];
        } elseif ($class >= UC_MODERATOR) {
            $users[ $id ]['channels'] = [0, 1, 2, 3, 4, 5];
        }
    }
    $cache->set('chat_users_list', $users, 86400);
}
