<?php

declare(strict_types = 1);

use DI\DependencyException;
use DI\NotFoundException;
use Pu239\Database;

/**
 * @param string $no_data
 *
 * @throws NotFoundException
 * @throws DependencyException
 *
 * @return string
 */
function tables($no_data = '')
{
    global $container;

    $tables = $temp = [];
    $no_data = explode('|', $no_data);
    $fluent = $container->get(Database::class);
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
 *
 * @throws DependencyException
 * @throws NotFoundException
 * @throws \Envms\FluentPDO\Exception
 */
function backupdb($data)
{
    global $container, $site_config;

    $time_start = microtime(true);
    $host = $site_config['db']['host'];
    $user = $site_config['db']['username'];
    $pass = quotemeta($site_config['db']['password']);
    $db = $site_config['db']['database'];
    $dt = TIME_NOW;
    $bdir = BACKUPS_DIR;
    make_dir($bdir, 0774);
    $filename = $db . '_' . date('Y.m.d-H.i.s', $dt) . '.sql';

    exec(MYSQLDUMP . " -h $host -u'{$user}' -p'{$pass}' $db -d | sed 's/ AUTO_INCREMENT=[0-9]*//g'>{$bdir}{$db}_structure.sql");

    $bdir = BACKUPS_DIR . 'db' . DIRECTORY_SEPARATOR . date('Y.m.d', $dt) . DIRECTORY_SEPARATOR;
    make_dir($bdir, 0774);
    if ($site_config['backup']['use_gzip']) {
        exec(MYSQLDUMP . " -h $host -u'{$user}' -p'{$pass}' $db " . tables() . ' | ' . GZIP . " -q9>{$bdir}{$filename}.gz");
    } else {
        exec(MYSQLDUMP . " -h $host -u'{$user}' -p'{$pass}' $db " . tables() . ">{$bdir}{$filename}");
    }

    $bdir = BACKUPS_DIR . 'table' . DIRECTORY_SEPARATOR . date('Y.m.d', $dt) . DIRECTORY_SEPARATOR;
    make_dir($bdir, 0774);
    $tables = explode(' ', tables());
    foreach ($tables as $table) {
        $filename = "tbl_{$table}_" . date('Y.m.d-H.i.s', $dt) . '.sql';
        if ($site_config['backup']['use_gzip']) {
            exec(MYSQLDUMP . " -h $host -u'{$user}' -p'{$pass}' $db $table | " . GZIP . " -q9>{$bdir}{$filename}.gz");
        } else {
            exec(MYSQLDUMP . " -h $host -u'{$user}' -p'{$pass}' $db $table>{$bdir}{$filename}");
        }
    }

    $filename = $db . '_' . date('Y.m.d-H.i.s', $dt) . '.sql';
    if ($site_config['backup']['use_gzip']) {
        $filename = $filename . '.gz';
    }
    $fluent = $container->get(Database::class);
    $set = [
        'last_access' => $dt,
    ];
    $fluent->update('users')
           ->set($set)
           ->where('id = ?', $site_config['chatbot']['id'])
           ->execute();
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
