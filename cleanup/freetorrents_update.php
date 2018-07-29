<?php

/**
 * @param $data
 *
 * @throws Exception
 * @throws \MatthiasMullie\Scrapbook\Exception\UnbegunTransaction
 */
function freetorrents_update($data)
{
    dbconn();
    global $site_config, $queries, $fluent, $cache;

    set_time_limit(1200);
    ignore_user_abort(true);

    $query = $fluent->from('torrents')
        ->select(null)
        ->select('id')
        ->select('free')
        ->where('free > 1')
        ->where('free < ?', TIME_NOW);

    $count = 0;
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

    if ($data['clean_log'] && $queries > 0) {
        write_log("Free Cleanup: Completed using $queries queries");
    }
}
