<?php
/**
 * @param $data
 */
function optimizedb($data)
{
    global $site_config, $queries;
    set_time_limit(1200);
    ignore_user_abort(true);

    $sql = sql_query("SHOW TABLE STATUS FROM {$site_config['mysql_db']} WHERE Data_free > 1000");
    $oht = '';
    $tables = [];

    while ($row = mysqli_fetch_assoc($sql)) {
        $oht .= $row['Name'] . ',';
        $tables[] = $row['Name'];
    }
    $oht = rtrim($oht, ',');
    foreach ($tables as $table) {
        sql_query("OPTIMIZE TABLE {$table}");
    }
    if ($data['clean_log'] && $queries > 0) {
        write_log("Auto Optimize DB Cleanup: Completed using $queries queries");
    }
    if ($data['clean_log'] && $oht != '') {
        write_log("MySQL Optimized " . count($tables) . " table" . plural(count($tables)) . ": {$oht}");
    }
}
