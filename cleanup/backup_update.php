<?php

/**
 * @param $data
 */
function backup_update($data)
{
    $time_start = microtime(true);
    dbconn();
    global $site_config, $queries;

    set_time_limit(1200);
    ignore_user_abort(true);

    $days = 3;
    $hours = 6 * 3600;
    $res = sql_query('SELECT id, name FROM dbbackup WHERE added < ' . sqlesc(TIME_NOW - ($days * 86400))) or sqlerr(__FILE__, __LINE__);
    if (mysqli_num_rows($res) > 0) {
        $ids = [];
        while ($arr = mysqli_fetch_assoc($res)) {
            $ids[] = (int) $arr['id'];
            $filename = BACKUPS_DIR . $arr['name'];
            if (is_file($filename)) {
                unlink($filename);
            }
        }
        sql_query('DELETE FROM dbbackup WHERE id IN (' . implode(', ', $ids) . ')') or sqlerr(__FILE__, __LINE__);
    }

    $objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(BACKUPS_DIR, RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST);
    foreach ($objects as $name => $object) {
        if (preg_match('/^tbl_/', basename($name))) {
            $date = filemtime($name);
            if (($date + $hours) < TIME_NOW) {
                unlink($name);
            }
        }
    }

    $time_end = microtime(true);
    $run_time = $time_end - $time_start;
    $text = " Run time: $run_time seconds";
    echo $text . "\n";
    if ($data['clean_log'] && $queries > 0) {
        write_log("Backup Cleanup: Completed using $queries queries" . $text);
    }
}
