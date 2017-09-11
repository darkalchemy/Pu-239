<?php
/*
 * @package AJAX_Chat
 * @author Sebastian Tschan
 * @copyright (c) Sebastian Tschan
 * @license Modified MIT License
 * @link https://blueimp.net/ajax/
 */

global $site_config, $mc1;

// List containing the custom channels:
$channels = [];
$sql = 'SELECT name FROM ajax_chat_channels ORDER BY id ASC';
$hashed = md5($sql);
if (($channels = $mc1->get_value('channels_' . $hashed)) === false) {
    $res = sql_query($sql) or sqlerr(__FILE__, __LINE__);
    $channels[] = $site_config['site_name'];
    while ($channel = mysqli_fetch_assoc($res)) {
        $channels[] = $channel['name'];
    }
    $mc1->cache_value('channels_' . $hashed, $channels, 0);
}
