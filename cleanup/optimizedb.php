<?php

declare(strict_types = 1);

use DI\DependencyException;
use DI\NotFoundException;

/**
 * @param $data
 *
 * @throws DependencyException
 * @throws NotFoundException
 * @throws \Envms\FluentPDO\Exception
 */
function optimizedb($data)
{
    global $site_config;
    $time_start = microtime(true);
    $minwaste = 1024 * 1024 * 10; // 10 MB
    $sql = sql_query("SHOW TABLE STATUS FROM {$site_config['db']['database']} WHERE Data_free > " . sqlesc($minwaste)) or sqlerr(__FILE__, __LINE__);
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
    if ($data['clean_log']) {
        write_log('Auto Optimize DB Cleanup: Completed');
    }
    $time_end = microtime(true);
    $run_time = $time_end - $time_start;
    $text = " Run time: $run_time seconds";
    echo $text . "\n";
    if ($data['clean_log'] && $oht != '') {
        write_log('MySQL Optimized ' . count($tables) . ' table' . plural(count($tables)) . ": {$oht}" . $text);
    }
}
