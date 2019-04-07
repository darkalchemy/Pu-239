<?php

/**
 * @param $data
 */
function backup_update($data)
{
    $time_start = microtime(true);
    global $site_config, $fluent;

    set_time_limit(1200);
    ignore_user_abort(true);
    $dt = TIME_NOW;
    $days = 3;
    $hours = 6 * 3600;
    $files = $fluent->from('dbbackup')
                    ->where('added < ?', $dt - ($days * 86400))
                    ->fetchAll();

    foreach ($files as $arr) {
        $filename = BACKUPS_DIR . $arr['name'];
        if (is_file($filename)) {
            unlink($filename);
        }
    }

    $fluent->deleteFrom('dbbackup')
           ->where('added < ?', $dt - ($days * 86400))
           ->execute();

    $objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(BACKUPS_DIR, RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST);
    foreach ($objects as $name => $object) {
        if (preg_match('/^tbl_/', basename($name))) {
            $date = filemtime($name);
            if (($date + $hours) < $dt) {
                unlink($name);
            }
        }
    }

    $time_end = microtime(true);
    $run_time = $time_end - $time_start;
    $text = " Run time: $run_time seconds";
    echo $text . "\n";
    if ($data['clean_log']) {
        write_log('Backup Cleanup: Completed.' . $text);
    }
}
