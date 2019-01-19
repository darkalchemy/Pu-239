<?php

/**
 * @param $data
 */
function happyhour_update($data)
{
    $time_start = microtime(true);
    dbconn();
    global $site_config, $queries;

    set_time_limit(1200);
    ignore_user_abort(true);

    require_once INCL_DIR . 'function_happyhour.php';
    $f = $site_config['happyhour'];
    $happy = unserialize(file_get_contents($f));
    $happyHour = strtotime($happy['time']);
    $curDate = TIME_NOW;
    $happyEnd = $happyHour + 3600;
    if ($happy['status'] == 0 && $site_config['happy_hour']) {
        if ($data['clean_log']) {
            write_log('Happy hour was @ ' . get_date($happyHour, 'LONG', 1, 0) . ' and Catid ' . $happy['catid']['id'] . ' ');
        }
        happyFile('set');
    } elseif (($curDate > $happyEnd) && $happy['status'] == 1) {
        happyFile('reset');
    }
    $time_end = microtime(true);
    $run_time = $time_end - $time_start;
    $text = " Run time: $run_time seconds";
    echo $text . "\n";
    if ($data['clean_log'] && $queries > 0) {
        write_log("Happyhour Cleanup: Completed using $queries queries" . $text);
    }
}
