<?php
/**
 \_/ \_/ \_/ \_/ \_/   \_/ \_/ \_/ \_/ \_/ \_/   \_/ \_/ \_/ \_/
 */
require_once dirname(__FILE__).DIRECTORY_SEPARATOR.'include'.DIRECTORY_SEPARATOR.'bittorrent.php';
require_once INCL_DIR.'user_functions.php';
require_once INCL_DIR.'emoticons.php';
require_once INCL_DIR.'html_functions.php';
dbconn(false);
loggedinorreturn();
$lang = load_language('global');
$HTMLOUT = stdhead();
$HTMLOUT .= begin_main_frame();
$HTMLOUT .= insert_smilies_frame();
$HTMLOUT .= end_main_frame();
$HTMLOUT .= stdfoot();
echo $HTMLOUT;
