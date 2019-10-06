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
$users = $this->_cache->get('chat_users_list_');
if ($users === false || is_null($users)) {
    $all_users = $this->_fluent->from('users')
                               ->select(null)
                               ->select('id')
                               ->select('chatpost')
                               ->select('status')
                               ->select('class');

    foreach ($all_users as $user) {
        $users[$user['id']]['userRole'] = $user['class'];
        if (has_access((int) $user['class'], UC_ADMINISTRATOR, 'coder')) {
            $users[$user['id']]['channels'] = $this->_siteConfig['ajaxchat']['admin_access'];
        } elseif (has_access((int) $user['class'], UC_STAFF, '')) {
            $users[$user['id']]['channels'] = $this->_siteConfig['ajaxchat']['staff_access'];
        } else {
            $users[$user['id']]['channels'] = $this->_siteConfig['ajaxchat']['user_access'];
        }
    }
    $this->_cache->set('chat_users_list_', $users, 900);
}
