<?php

require_once __DIR__ . '/include/bittorrent.php';
require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'emoticons.php';
require_once INCL_DIR . 'function_html.php';
check_user_status();
$lang = load_language('global');
$HTMLOUT = stdhead();
$HTMLOUT .= begin_main_frame();
$HTMLOUT .= insert_smilies_frame();
$HTMLOUT .= end_main_frame();
$HTMLOUT .= stdfoot();
echo $HTMLOUT;
