<?php

global $site_config, $cache, $CURUSER, $message_stuffs, $mailbox, $pm_id;

$set = [
    'location' => $_POST['boxx'],
];
$result = $message_stuffs->update($set, $pm_id);
if (!$result) {
    stderr($lang['pm_error'], '' . $lang['pm_move_err'] . '<a class="altlink" href="' . $site_config['baseurl'] . '/messages.php?action=view_message&id=' . $pm_id . '>' . $lang['pm_move_back'] . '</a>' . $lang['pm_move_msg'] . '');
}
$cache->delete('inbox_' . $CURUSER['id']);
header('Location: ' . $site_config['baseurl'] . '/messages.php?action=view_mailbox&singlemove=1&box=' . $mailbox);
die();
