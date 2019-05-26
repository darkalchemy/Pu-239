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
    $days = 3;
    $hours = 6 * 3600;
    $fluent = $container->get(Database::class);
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
