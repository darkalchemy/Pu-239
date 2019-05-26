<?php

declare(strict_types = 1);

use MatthiasMullie\Scrapbook\Exception\UnbegunTransaction;
use Pu239\Cache;
use Pu239\Database;

/**
 * @param $data
 *
 * @throws Exception
 * @throws UnbegunTransaction
 */
function freetorrents_update($data)
{
    global $container, $site_config;

    $time_start = microtime(true);
    $fluent = $container->get(Database::class);
    $query = $fluent->from('torrents')
                    ->select(null)
                    ->select('id')
                    ->select('free')
                    ->where('free > 1')
                    ->where('free < ?', TIME_NOW);

    $count = 0;
    $cache = $container->get(Cache::class);
    foreach ($query as $arr) {
        $set = [
            'free' => 0,
        ];

        $fluent->update('torrents')
               ->set($set)
               ->where('id = ?', $arr['id'])
               ->execute();

        $cache->update_row('torrent_details_' . $arr['id'], [
            'free' => 0,
        ], $site_config['expires']['torrent_details']);
        ++$count;
    }
    if ($data['clean_log']) {
        write_log('Cleanup - Removed Free from ' . $count . ' torrents');
    }
    unset($set, $count);

    $time_end = microtime(true);
    $run_time = $time_end - $time_start;
    $text = " Run time: $run_time seconds";
    echo $text . "\n";
    if ($data['clean_log']) {
        write_log('Free Cleanup: Completed' . $text);
    }
}
