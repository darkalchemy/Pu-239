<?php

declare(strict_types = 1);

use Pu239\Cache;
use Pu239\Message;

global $container, $site_config, $CURUSER;
$set = [
    'location' => $_POST['boxx'],
];
$message_stuffs = $container->get(Message::class);
$result = $message_stuffs->update($set, $pm_id);
if (!$result) {
    stderr($lang['pm_error'], '' . $lang['pm_move_err'] . '<a class="altlink" href="' . $site_config['paths']['baseurl'] . '/messages.php?action=view_message&id=' . $pm_id . '>' . $lang['pm_move_back'] . '</a>' . $lang['pm_move_msg'] . '');
}
$cache = $container->get(Cache::class);
$cache->delete('inbox_' . $CURUSER['id']);
header('Location: ' . $site_config['paths']['baseurl'] . '/messages.php?action=view_mailbox&singlemove=1&box=' . $mailbox);
die();
