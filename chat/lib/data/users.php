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
$this->_cache->delete('chat_users_list_');
$users = $this->_cache->get('chat_users_list_');
if ($users === false || is_null($users)) {
    $all_users = $this->_fluent->from('users')
                               ->select(null)
                               ->select('id')
                               ->select('chatpost')
                               ->select('status')
                               ->select('class')
                               ->select('override_class');

    foreach ($all_users as $user) {
        $user_class = $user['override_class'] != 255 ? (int) $user['override_class'] : (int) $user['class'];
        $users[$user['id']]['userRole'] = $user_class;
        if (has_access($user_class, UC_ADMINISTRATOR, '')) {
            $users[$user['id']]['channels'] = $this->_siteConfig['ajaxchat']['admin_access'];
        } elseif (has_access($user_class, UC_STAFF, 'coder')) {
            $users[$user['id']]['channels'] = $this->_siteConfig['ajaxchat']['staff_access'];
        } else {
            $users[$user['id']]['channels'] = $this->_siteConfig['ajaxchat']['user_access'];
        }
    }
    $this->_cache->set('chat_users_list_', $users, 900);
}
