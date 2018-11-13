<?php

/**
 * @param string $no_data
 *
 * @return string
 */
function tables($no_data = '')
{
    $tables = $temp = [];
    $no_data = explode('|', $no_data);
    $r = sql_query('SHOW TABLES') or sqlerr(__FILE__, __LINE__);
    while ($a = mysqli_fetch_assoc($r)) {
        $temp[] = $a;
    }
    foreach ($temp as $k => $tname) {
        $tn = $tname["Tables_in_{$_ENV['DB_DATABASE']}"];
        if (in_array($tn, $no_data)) {
            continue;
        }
        $tables[] = $tn;
    }

    return implode(' ', $tables);
}

/**
 * @param $data
 */
function backupdb($data)
{
    $time_start = microtime(true);
    dbconn();
    global $site_config, $queries;

    set_time_limit(1200);
    ignore_user_abort(true);

    $host = $_ENV['DB_HOST'];
    $user = $_ENV['DB_USERNAME'];
    $pass = $_ENV['DB_PASSWORD'];
    $db = $_ENV['DB_DATABASE'];
    $dt = TIME_NOW;
    $bdir = BACKUPS_DIR;
    $filename = 'db_' . date('m_d_y_H', TIME_NOW) . '.sql';

    $c1 = "mysqldump -h $host -u{$user} -p" . quotemeta($pass) . " $db -d > $bdir/db_structure.sql";
    $c2 = "mysqldump -h $host -u{$user} -p" . quotemeta($pass) . " $db " . tables('peers') . " | bzip2 -9 > $bdir/{$filename}.bz2";

    system($c1);
    exec($c2);

    // table backup
    $tables = explode(' ', tables());
    foreach ($tables as $table) {
        $filename = "tbl_{$table}_" . date('m_d_y_H', TIME_NOW) . '.sql';
        $c2 = "mysqldump -h $host -u{$user} -p" . quotemeta($pass) . " $db $table | bzip2 -cq9 > $bdir/{$filename}.bz2";
        system($c2);
    }

    // delete db files older than 3 days
    $files = glob($bdir . '/db_*');
    foreach ($files as $file) {
        if (($dt - filemtime($file)) > 3 * 86400) {
            unlink($file);
        }
    }

    // delete table files older than 1 day
    $files = glob($bdir . '/tbl_*');
    foreach ($files as $file) {
        if ((TIME_NOW - filemtime($file)) > 1 * 86400) {
            unlink($file);
        }
    }

    $ext = 'db_' . date('m_d_y_H', $dt) . '.sql.bz2';
    sql_query('INSERT INTO dbbackup (name, added, userid) VALUES (' . sqlesc($ext) . ', ' . $dt . ', ' . $site_config['site']['owner'] . ')') or sqlerr(__FILE__, __LINE__);
    $time_end = microtime(true);
    $run_time = $time_end - $time_start;
    $text = " Run time: $run_time seconds";
    echo $text . "\n";
    if ($data['clean_log'] && $queries > 0) {
        write_log("Auto DB Backup Cleanup: Completed using $queries queries" . $text);
    }
}
