<?php
require_once realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..') . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'user_functions.php';
require_once INCL_DIR . 'html_functions.php';
dbconn();
$lang = array_merge(load_language('global'), load_language('useragreement'));
$HTMLOUT = '';
$HTMLOUT .= "<br><div class='main_section'>";
$HTMLOUT .= begin_frame($site_config['site_name'] . " {$lang['frame_usragrmnt']}");
$HTMLOUT .= "<p></p> {$lang['text_usragrmnt']}";
$HTMLOUT .= end_frame();
$HTMLOUT .= '</div>';
echo stdhead("{$lang['stdhead_usragrmnt']}") . $HTMLOUT . stdfoot();
