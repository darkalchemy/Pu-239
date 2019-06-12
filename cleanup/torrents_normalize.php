<?php

declare(strict_types = 1);

use DI\DependencyException;
use DI\NotFoundException;
use MatthiasMullie\Scrapbook\Exception\UnbegunTransaction;
use Pu239\Database;
use Pu239\Torrent;

/**
 * @param $data
 *
 * @throws UnbegunTransaction
 * @throws DependencyException
 * @throws NotFoundException
 * @throws \Envms\FluentPDO\Exception
 */
function torrents_normalize($data)
{
    global $container;

    $time_start = microtime(true);
    $fluent = $container->get(Database::class);
    $torrents = $fluent->from('torrents')
                       ->select(null)
                       ->select('id')
                       ->select('info_hash')
                       ->select('owner')
                       ->orderBy('id');

    $tids = $ids = $bad1 = $bad2 = [];
    foreach ($torrents as $torrent) {
        $tids[] = $torrent['id'];
        $list[$torrent['id']] = $torrent;
    }
    $path = TORRENTS_DIR;

    $objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST);
    foreach ($objects as $name => $object) {
        $ext = pathinfo($name, PATHINFO_EXTENSION);
        if ($ext === 'torrent') {
            $ids[] = basename($name, '.torrent');
        }
    }
    sort($ids);
    $bad1 = array_diff($ids, $tids);
    $bad2 = array_diff($tids, $ids);
    $bad = array_merge($bad1, $bad2);
    $torrents_class = $container->get(Torrent::class);
    $i = 0;
    foreach ($bad as $tid) {
        ++$i;
        $torrents_class->delete_by_id((int) $tid);
        if (!empty($list[$tid]['info_hash'])) {
            $torrents_class->remove_torrent($list[$tid]['info_hash'], $list[$tid]['id'], $list[$tid]['owner']);
        }
    }

    $time_end = microtime(true);
    $run_time = $time_end - $time_start;
    $text = " Run time: $run_time seconds";
    echo $text . "\n";
    if ($data['clean_log']) {
        write_log("Normalize Cleanup: Completed, deleted $i torrents" . $text);
    }
}
