<?php

declare(strict_types = 1);
/*
 * @package AJAX_Chat
 * @author Sebastian Tschan
 * @copyright (c) Sebastian Tschan
 * @license Modified MIT License
 * @link https://blueimp.net/ajax/
 */

// List containing the registered chat users:
$users = [];
$users = $this->_cache->get('chat_users_list');
if ($users === false || is_null($users)) {
    $all_users = $this->_fluent->from('users')
                           ->select(null)
                           ->select('id')
                           ->select('class');

    foreach ($all_users as $user) {
        $users[$user['id']]['userRole'] = $user['class'];
        if (has_access($user['class'], UC_ADMINISTRATOR, 'coder')) {
            $users[$user['id']]['channels'] = [
                0,
                1,
                2,
                3,
                4,
                5,
                6,
            ];
        } elseif (has_access($user['class'], UC_STAFF, 'coder')) {
            $users[$user['id']]['channels'] = [
                0,
                1,
                2,
                3,
                4,
                5,
            ];
        } else {
            $users[$user['id']]['channels'] = [
                0,
                1,
                2,
                3,
                4,
            ];
        }
    }
    $this->_cache->set('chat_users_list', $users, 86400);
}
