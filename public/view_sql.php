<?php

require_once dirname(__FILE__, 2).DIRECTORY_SEPARATOR.'include'.DIRECTORY_SEPARATOR.'bittorrent.php';
require_once INCL_DIR.'user_functions.php';
require_once INCL_DIR.'html_functions.php';
check_user_status();
global $site_config, $CURUSER;
$lang = load_language('global');

$html = "<iframe src='{$site_config['baseurl']}/ajax/view_sql.php?username={$CURUSER['username']}&db={$_ENV['DB_DATABASE']}' id='iframe_adminer' name='iframe_adminer' scrolling='no' onload='resizeIframe(this)'></iframe>";
echo stdhead('Adminer', true).wrapper($html).stdfoot();
