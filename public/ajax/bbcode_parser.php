<?php

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'bbcode_functions.php';
check_user_status();

$output = format_comment($_POST['data']);
//file_put_contents('/var/log/nginx/bbcode.log', $output . PHP_EOL, FILE_APPEND);
echo $output;
die();
