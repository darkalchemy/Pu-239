<?php

/**
 * @param $data
 *
 * @throws \Envms\FluentPDO\Exception
 * @throws \MatthiasMullie\Scrapbook\Exception\UnbegunTransaction
 */
function silvertorrents_update($data)
{
    $time_start = microtime(true);
    global $site_config, $fluent, $cache;

    set_time_limit(1200);
    ignore_user_abort(true);
    $dt = TIME_NOW;

    $torrents = $fluent->from('torrents')
        ->select(null)
        ->select('id')
        ->select('silver')
        ->where('silver>1')
        ->where('silver < ?', $dt)
        ->fetchAll();

    $count = count($torrents);
    if ($count > 0) {
        $set = [
            'silver' => 0,
        ];
        $fluent->update('torrents')
            ->set($set)
            ->where('silver>1')
            ->where('silver < ?', $dt)
            ->execute();
    }
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
