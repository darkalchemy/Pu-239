<?php
/**
 * @param $data
 */
function optimizedb($data)
{
    global $site_config, $queries;
    set_time_limit(1200);
    ignore_user_abort(true);
    $sql = sql_query("SHOW TABLE STATUS FROM {$site_config['mysql_db']}");
    $oht = '';
    while ($row = mysqli_fetch_assoc($sql)) {
        if ($row['Data_free'] > 100) {
            $oht .= $row['Data_free'] . ',';
        }
    }
    $oht = rtrim($oht, ',');
    if ($oht != '') {
        $sql = sql_query("OPTIMIZE TABLE {$oht}");
    }
    if ($data['clean_log'] && $queries > 0) {
        write_log("Auto Optimize DB Cleanup: Commpleted using $queries queries");
    }
    if ($data['clean_log'] && $oht != '') {
        $data['clean_desc'] = "MySQLCleanup optimized {$oht} table(s)";
    }
}
