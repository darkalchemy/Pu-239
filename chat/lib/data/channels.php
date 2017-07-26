<?php
/*
 * @package AJAX_Chat
 * @author Sebastian Tschan
 * @copyright (c) Sebastian Tschan
 * @license Modified MIT License
 * @link https://blueimp.net/ajax/
 */

global $INSTALLER09, $mc1;

// List containing the custom channels:
$channels = [];
$sql = 'SELECT name FROM ajax_chat_channels ORDER BY id ASC';
$hashed = md5($sql);
if (($channels = $mc1->get_value('channels_'.$hashed)) === false) {
    $res = sql_query($sql) or sqlerr(__FILE__, __LINE__);
    $channels[] = $INSTALLER09['site_name'];
    while ($channel = mysqli_fetch_assoc($res)) {
        $channels[] = $channel['name'];
    }
    $mc1->cache_value('channels_'.$hashed, $channels, 0);
}
