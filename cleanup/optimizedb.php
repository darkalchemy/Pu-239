<?php

/**
 * @param $data
 */
function optimizedb($data)
{
    $time_start = microtime(true);
    dbconn();
    global $queries;

    set_time_limit(1200);
    ignore_user_abort(true);
    $minwaste = 1024 * 1024 * 10; // 10 MB
    $sql = sql_query("SHOW TABLE STATUS FROM {$_ENV['DB_DATABASE']} WHERE Data_free > " . sqlesc($minwaste)) or sqlerr(__FILE__, __LINE__);
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
    $time_end = microtime(true);
    $run_time = $time_end - $time_start;
    $text = " Run time: $run_time seconds";
    echo $text . "\n";
    if ($data['clean_log'] && $oht != '') {
        write_log('MySQL Optimized ' . count($tables) . ' table' . plural(count($tables)) . ": {$oht}" . $text);
    }
}
