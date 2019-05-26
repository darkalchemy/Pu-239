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
 * @throws DependencyException
 * @throws NotFoundException
 * @throws \Envms\FluentPDO\Exception
 * @throws UnbegunTransaction
 */
function torrents_update($data)
{
    global $container;

    $time_start = microtime(true);
    $fluent = $container->get(Database::class);
    $torrents = $fluent->from('torrents')
                       ->select(null)
                       ->select('id')
                       ->select('seeders')
                       ->select('leechers')
                       ->select('comments')
                       ->orderBy('id')
                       ->fetchAll();

    $peers = $fluent->from('peers')
                    ->select(null)
                    ->select('seeder')
                    ->select('torrent')
                    ->fetchAll();

    $comments = $fluent->from('comments')
                       ->select(null)
                       ->select('torrent')
                       ->fetchAll();

    $torrent_stuffs = $container->get(Torrent::class);
    foreach ($torrents as $torrent) {
        $torrent['seeders_num'] = $torrent['leechers_num'] = $torrent['comments_num'] = 0;

        foreach ($peers as $peer) {
            if ($peer['torrent'] === $torrent['id']) {
                if ($peer['seeder'] === 'yes') {
                    ++$torrent['seeders_num'];
                } else {
                    ++$torrent['leechers_num'];
                }
            }
        }

        foreach ($comments as $comment) {
            if ($comment['torrent'] === $torrent['id']) {
                ++$torrent['comments_num'];
            }
        }

        if ($torrent['seeders'] != $torrent['seeders_num'] || $torrent['leechers'] != $torrent['leechers_num'] || $torrent['comments'] != $torrent['comments_num']) {
            $set = [
                'seeders' => $torrent['seeders_num'],
                'leechers' => $torrent['leechers_num'],
                'comments' => $torrent['comments_num'],
            ];
            $torrent_stuffs->update($set, $torrent['id'], true);
        }
    }

    $time_end = microtime(true);
    $run_time = $time_end - $time_start;
    $text = " Run time: $run_time seconds";
    echo $text . "\n";
    if ($data['clean_log']) {
        write_log('Torrent Cleanup completed' . $text);
    }
}
