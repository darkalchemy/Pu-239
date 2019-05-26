<?php

declare(strict_types = 1);

use DI\DependencyException;
use DI\NotFoundException;
use MatthiasMullie\Scrapbook\Exception\UnbegunTransaction;
use Pu239\Cache;
use Pu239\Database;
use Pu239\Message;
use Pu239\Torrent;

/**
 * @param $data
 *
 * @throws DependencyException
 * @throws NotFoundException
 * @throws \Envms\FluentPDO\Exception
 * @throws UnbegunTransaction
 */
function delete_torrents_update($data)
{
    global $container;

    $time_start = microtime(true);
    $hours = 2;
    $dt = TIME_NOW - ($hours * 3600);
    $fluent = $container->get(Database::class);
    $never_seeded = $fluent->from('torrents')
                           ->select(null)
                           ->select('id')
                           ->select('owner')
                           ->select('name')
                           ->select('info_hash')
                           ->where('last_action = added')
                           ->where('last_action < ?', $dt)
                           ->where('seeders = 0')
                           ->where('leechers = 0');

    $days = 45;
    $dt = TIME_NOW - ($days * 86400);
    $dead = $fluent->from('torrents')
                   ->select(null)
                   ->select('id')
                   ->select('owner')
                   ->select('name')
                   ->select('info_hash')
                   ->where('last_action < ?', $dt)
                   ->where('seeders = 0')
                   ->where('leechers = 0');

    $values = [];
    $dt = TIME_NOW;
    $torrent_stuffs = $container->get(Torrent::class);
    foreach ($never_seeded as $torrent) {
        $torrent_stuffs->delete_by_id((int) $torrent['id']);
        $torrent_stuffs->remove_torrent($torrent['info_hash']);
        $msg = 'Torrent ' . (int) $torrent['id'] . ' (' . htmlsafechars($torrent['name']) . ") was deleted by system (older than $days days and no seeders)";
        $values[] = [
            'sender' => 0,
            'receiver' => $torrent['owner'],
            'added' => $dt,
            'msg' => $msg,
            'subject' => 'Torrent Deleted [Dead]',
        ];
        if ($data['clean_log']) {
            write_log($msg);
        }
    }

    foreach ($dead as $torrent) {
        $torrent_stuffs->delete_by_id((int) $torrent['id']);
        $torrent_stuffs->remove_torrent($torrent['info_hash']);
        $msg = 'Torrent ' . (int) $torrent['id'] . ' (' . htmlsafechars($torrent['name']) . ") was deleted by system (older than $days days and no seeders)";
        $values[] = [
            'sender' => 0,
            'receiver' => $torrent['owner'],
            'added' => $dt,
            'msg' => $msg,
            'subject' => 'Torrent Deleted [Dead]',
        ];
        if ($data['clean_log']) {
            write_log($msg);
        }
    }

    $count = count($values);
    if ($count > 0) {
        $cache = $container->get(Cache::class);
        $cache->delete('torrent_poster_count_');
        $message_stuffs = $container->get(Message::class);
        $message_stuffs->insert($values);
    }

    $time_end = microtime(true);
    $run_time = $time_end - $time_start;
    $text = " Run time: $run_time seconds";
    echo $text . "\n";
    if ($data['clean_log']) {
        write_log('Delete Old Torrents Cleanup: Completed' . $text);
    }
}
