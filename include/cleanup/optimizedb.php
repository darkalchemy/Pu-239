<?php
function optimizedb($data)
{
    global $INSTALLER09, $queries;
    set_time_limit(1200);
    ignore_user_abort(1);
    $sql = sql_query("SHOW TABLE STATUS FROM {$INSTALLER09['mysql_db']}");
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
    if ($queries > 0) {
        write_log("Auto Optimize DB Cleanup: Commpleted using $queries queries");
    }
    if ($oht != '') {
        $data['clean_desc'] = "MySQLCleanup optimized {$oht} table(s)";
    }
    if ($data['clean_log']) {
        cleanup_log($data);
    }
}
