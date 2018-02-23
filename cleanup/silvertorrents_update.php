<?php

/**
 * @param $data
 *
 * @throws Exception
 * @throws \MatthiasMullie\Scrapbook\Exception\UnbegunTransaction
 */
function silvertorrents_update($data)
{
    global $site_config, $queries, $fluent, $cache;

    set_time_limit(1200);
    ignore_user_abort(true);

    $query = $fluent->from('torrents')
        ->select(null)
        ->select('id')
        ->select('silver')
        ->where('silver > 1')
        ->where('silver < ?', TIME_NOW);

    $count = 0;
    foreach ($query as $arr) {
        $set = [
            'silver' => 0,
        ];

        $fluent->update('torrents')
            ->set($set)
            ->where('id = ?', $arr['id'])
            ->execute();

        $cache->update_row('torrent_details_'.$arr['id'], [
            'silver' => 0,
        ], $site_config['expires']['torrent_details']);
        ++$count;
    }

    if ($data['clean_log']) {
        write_log('Cleanup - Removed Silver from '.$count.' torrents');
    }
    unset($set, $count);

    if ($data['clean_log'] && $queries > 0) {
        write_log("Silver Torrents Cleanup: Completed using $queries queries");
    }
}
