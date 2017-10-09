<?php
require_once 'config.php';
if (!@($GLOBALS['___mysqli_ston'] = mysqli_connect($site_config['mysql_host'], $site_config['mysql_user'], $site_config['mysql_pass']))) {
    sqlerr(__FILE__, __LINE__);
}
((bool)mysqli_query($GLOBALS['___mysqli_ston'], "USE {$site_config['mysql_db']}")) or sqlerr(__FILE__, 'dbconn: mysql_select_db: ' . ((is_object($GLOBALS['___mysqli_ston'])) ? mysqli_error($GLOBALS['___mysqli_ston']) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
$now = TIME_NOW;
$sql = sql_query("SELECT * FROM cleanup WHERE clean_cron_key = '{$argv[1]}' LIMIT 0,1");
$row = mysqli_fetch_assoc($sql);
if ($row['clean_id']) {
    $next_clean = intval($now + ($row['clean_increment'] ? $row['clean_increment'] : 15 * 60));
    // don't really need to update if its cron. no point as yet.
    sql_query("UPDATE cleanup SET clean_time = $next_clean WHERE clean_id = {$row['clean_id']}");
    if (file_exists(CLEAN_DIR . '' . $row['clean_file'])) {
        require_once CLEAN_DIR . '' . $row['clean_file'];
        if (function_exists('docleanup')) {
            register_shutdown_function('docleanup', $row);
        }
    }
}
function sqlesc($x)
{
    return "'" . ((isset($GLOBALS['___mysqli_ston']) && is_object($GLOBALS['___mysqli_ston'])) ? mysqli_real_escape_string($GLOBALS['___mysqli_ston'], $x) : ((trigger_error('Err', E_USER_ERROR)) ? '' : '')) . "'";
}
