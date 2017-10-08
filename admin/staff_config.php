<?php
if (!defined('IN_site_config_ADMIN')) {
    setSessionVar('error', 'Access Not Allowed');
    header("Location: {$site_config['baseurl']}/index.php");
    exit();
}
require_once INCL_DIR . 'user_functions.php';
require_once CLASS_DIR . 'class_check.php';
class_check(UC_MAX);

write_staffs();
echo stdhead($lang['staffcfg_stdhead']) . stdfoot();
