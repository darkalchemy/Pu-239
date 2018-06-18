<?php
/**
 * @param $data
 */
function optimizedb($data)
{
    global $queries;
    set_time_limit(1200);
    ignore_user_abort(true);

    $sql = sql_query("SHOW TABLE STATUS FROM {$_ENV['DB_DATABASE']} WHERE Data_free > 1000") or sqlerr(__FILE__, __LINE__);
    $oht = '';
    $tables = [];

    while ($row = mysqli_fetch_assoc($sql)) {
        $oht .= $row['Name'] . ',';
        $tables[] = $row['Name'];
    }
    $oht = rtrim($oht, ',');
    foreach ($tables as $table) {
        sql_query("OPTIMIZE TABLE {$table}") or sqlerr(__FILE__, __LINE__);
    }
    if ($data['clean_log'] && $queries > 0) {
        write_log("Auto Optimize DB Cleanup: Completed using $queries queries");
    }
    if ($data['clean_log'] && $oht != '') {
        write_log('MySQL Optimized ' . count($tables) . ' table' . plural(count($tables)) . ": {$oht}");
    }
}
