<?php

/**
 * @param string $no_data
 *
 * @return string
 */
function tables($no_data = '')
{
    global $fluent;

    $tables = $temp = [];
    $no_data = explode('|', $no_data);
    $query = $fluent->getPdo()
        ->prepare('SHOW TABLES');
    $query->execute();
    $all_tables = $query->fetchAll();

    foreach ($all_tables as $values) {
        foreach ($values as $key => $value) {
            if (in_array($value, $no_data)) {
                continue;
            }
            $tables[] = $value;
        }
    }

    return implode(' ', $tables);
}

/**
 * @param $data
 */
function backupdb($data)
{
    $time_start = microtime(true);
    global $site_config, $fluent;

    set_time_limit(1200);
    ignore_user_abort(true);

    $host = $site_config['database']['host'];
    $user = $site_config['database']['username'];
    $pass = quotemeta($site_config['database']['password']);
    $db = $site_config['database']['database'];
    $dt = TIME_NOW;
    $bdir = BACKUPS_DIR;
    $filename = $db . '_' . date('Y.m.d-H.i.s', $dt) . '.sql';

    exec("mysqldump -h $host -u'{$user}' -p'{$pass}' $db -d | sed 's/ AUTO_INCREMENT=[0-9]*//g'>{$bdir}{$db}_structure.sql");
    if ($site_config['backup']['use_gzip']) {
        exec("{$site_config['backup']['mysqldump_path']} -h $host -u'{$user}' -p'{$pass}' $db " . tables('peers') . ' | ' . $site_config['backup']['gzip_path'] . " -q9>{$bdir}{$filename}.gz");
    } else {
        exec("{$site_config['backup']['mysqldump_path']} -h $host -u'{$user}' -p'{$pass}' $db " . tables('peers') . ">{$bdir}{$filename}");
    }

    // table backup
    $tables = explode(' ', tables());
    foreach ($tables as $table) {
        if ($table !== 'peers') {
            $filename = "tbl_{$table}_" . date('Y.m.d-H.i.s', $dt) . '.sql';
            if ($site_config['backup']['use_gzip']) {
                exec("{$site_config['backup']['mysqldump_path']} -h $host -u'{$user}' -p'{$pass}' $db $table | " . $site_config['backup']['gzip_path'] . " -q9>{$bdir}{$filename}.gz");
            } else {
                exec("{$site_config['backup']['mysqldump_path']} -h $host -u'{$user}' -p'{$pass}' $db $table>{$bdir}{$filename}");
            }
        }
    }

    $filename = $db . '_' . date('Y.m.d-H.i.s', $dt) . '.sql';
    if ($site_config['backup']['use_gzip']) {
        $filename = $filename . '.gz';
    }
    $values = [
        'name' => $filename,
        'added' => $dt,
        'userid' => $site_config['site']['owner'],
    ];
    $fluent->insertInto('dbbackup')
        ->values($values)
        ->execute();

    $time_end = microtime(true);
    $run_time = $time_end - $time_start;
    $text = " Run time: $run_time seconds";
    echo $text . "\n";
    if ($data['clean_log']) {
        write_log('Auto DB Backup Cleanup: Completed.' . $text);
    }
}
