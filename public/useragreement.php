<?php
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'user_functions.php';
require_once INCL_DIR . 'html_functions.php';
if (!getSessionVar('LoggedIn')) {
    dbconn();
    get_template();
} else {
    check_user_status();
}
global $site_config;

$lang = array_merge(load_language('global'), load_language('useragreement'));

$HTMLOUT = "
    <div class='container is-fluid portlet padbottom20 has-text-centered'>
        <h1>{$site_config['site_name']} {$lang['frame_usragrmnt']}</h1>
        <div class='text-justify'>
            {$lang['text_usragrmnt']}
        </div>
    </div>";

echo stdhead($lang['stdhead_usragrmnt']) . $HTMLOUT . stdfoot();
