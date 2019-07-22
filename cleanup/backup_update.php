<?php

declare(strict_types = 1);

use DI\DependencyException;
use DI\NotFoundException;
use Pu239\Database;

/**
 * @param $data
 *
 * @throws DependencyException
 * @throws NotFoundException
 * @throws \Envms\FluentPDO\Exception
 */
function backup_update($data)
{
    global $container;

    $time_start = microtime(true);
    $dt = TIME_NOW;
    $days = $dt - (3 * 86400);
    $hours = $dt - (6 * 3600);
    $fluent = $container->get(Database::class);
    $fluent->deleteFrom('dbbackup')
           ->where('added < ?', $days)
           ->execute();

    $paths = [
        BACKUPS_DIR . 'db' . DIRECTORY_SEPARATOR => $days,
        BACKUPS_DIR . 'table' . DIRECTORY_SEPARATOR => $hours,
    ];
    foreach ($paths as $path => $dt) {
        $objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST);
        foreach ($objects as $name => $object) {
            if (!is_dir($name)) {
                $date = filemtime($name);
                if ($date < $dt) {
                    unlink($name);
                }
            }
        }
    }
    $remove = [];
    foreach ($paths as $path => $dt) {
        $objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST);
        foreach ($objects as $name => $object) {
            if (is_dir($name) && !(new FilesystemIterator($name))->valid()) {
                $remove[] = $name;
            }
        }
    }
    foreach ($remove as $path) {
        rmdir($path);
    }
    $time_end = microtime(true);
    $run_time = $time_end - $time_start;
    $text = " Run time: $run_time seconds";
    echo $text . "\n";
    if ($data['clean_log']) {
        write_log('Backup Cleanup: Completed.' . $text);
    }
}
