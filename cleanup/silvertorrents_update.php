<?php

declare(strict_types = 1);

use DI\DependencyException;
use DI\NotFoundException;
use MatthiasMullie\Scrapbook\Exception\UnbegunTransaction;
use Pu239\Cache;
use Pu239\Database;

/**
 * @param $data
 *
 * @throws UnbegunTransaction
 * @throws DependencyException
 * @throws NotFoundException
 * @throws \Envms\FluentPDO\Exception
 */
function silvertorrents_update($data)
{
    global $container, $site_config;

    $time_start = microtime(true);
    $dt = TIME_NOW;
    $fluent = $container->get(Database::class);
    $torrents = $fluent->from('torrents')
                       ->select(null)
                       ->select('id')
                       ->select('silver')
                       ->where('silver > 1')
                       ->where('silver < ?', $dt)
                       ->fetchAll();

    $count = count($torrents);
    if ($count > 0) {
        $set = [
            'silver' => 0,
        ];
        $fluent->update('torrents')
               ->set($set)
               ->where('silver > 1')
               ->where('silver < ?', $dt)
               ->execute();
    }
    $cache = $container->get(Cache::class);
    foreach ($torrents as $torrent) {
        $details = $cache->get('torrent_details_' . $torrent['id']);
        if (!empty($details)) {
            $cache->update_row('torrent_details_' . $torrent['id'], [
                'silver' => 0,
            ], $site_config['expires']['torrent_details']);
        }
    }

    $time_end = microtime(true);
    $run_time = $time_end - $time_start;
    $text = " Run time: $run_time seconds";
    echo $text . "\n";
    if ($data['clean_log']) {
        write_log('Cleanup - Removed Silver from ' . $count . ' torrents' . $text);
    }
}
