<?php

declare(strict_types = 1);

use Pu239\Cache;
use Pu239\Message;

global $container, $CURUSER, $site_config;

$message_stuffs = $container->get(Message::class);
$message = $message_stuffs->get_by_id($pm_id);
$cache = $container->get(Cache::class);
if ($message['receiver'] == $CURUSER['id'] && $message['urgent'] === 'yes' && $message['unread'] === 'yes') {
    stderr($lang['pm_error'], '' . $lang['pm_delete_err'] . '<a class="is-link" href="' . $site_config['paths']['baseurl'] . '/messages.php?action=view_message&id=' . $pm_id . '">' . $lang['pm_delete_msg'] . '</a> to message.');
}
if (($message['receiver'] == $CURUSER['id'] || $message['sender'] == $CURUSER['id']) && $message['location'] == $site_config['pm']['deleted']) {
    $message_stuffs->delete($pm_id, $CURUSER['id']);
} elseif ($message['receiver'] == $CURUSER['id']) {
    $set = [
        'location' => 0,
        'unread' => 'no',
    ];
    $message_stuffs->update($set, $pm_id);
    $cache->decrement('inbox_' . $CURUSER['id']);
} elseif ($message['sender'] == $CURUSER['id'] && $message['location'] != $site_config['pm']['deleted']) {
    $set = [
        'saved' => 'no',
    ];
    $message_stuffs->update($set, $pm_id);
}

header("Location: {$_SERVER['PHP_SELF']}?action=view_mailbox&deleted=1");
die();
