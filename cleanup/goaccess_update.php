<?php

/**
 * @param $data
 */
function goaccess_cleanup($data)
{
    $time_start = microtime(true);
    global $site_config;

    set_time_limit(1200);
    ignore_user_abort(true);

    if (file_exists('/usr/bin/goaccess')) {
        $path = '/dev/shm/goaccess/';
        make_dir($path);
        passthru("zcat /var/log/nginx/access.log.gz* > {$path}access.log");
        passthru("/usr/bin/goaccess '{$path}access.log' -p '" . BIN_DIR . "goaccess.conf' --real-os --geoip-database='" . ROOT_DIR . "GeoIP/GeoLiteCity.dat' -o '" . CACHE_DIR . "goaccess.html' \n");
    }

    $time_end = microtime(true);
    $run_time = $time_end - $time_start;
    $text = " Run time: $run_time seconds";
    echo $text . "\n";
    if ($data['clean_log']) {
        write_log("GO Access Cleanup: Completed" . $text);
    }
}

function make_dir($path)
{
    if (!file_exists($path)) {
        mkdir($path, 0777);
    }
}
