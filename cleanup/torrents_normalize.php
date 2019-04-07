<?php

use MatthiasMullie\Scrapbook\Exception\UnbegunTransaction;

/**
 * @param $data
 *
 * @throws \Envms\FluentPDO\Exception
 * @throws UnbegunTransaction
 */
function torrents_normalize($data)
{
    $time_start = microtime(true);
    global $site_config, $fluent, $torrent_stuffs;

    set_time_limit(1200);
    ignore_user_abort(true);

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
    $i = 0;
    foreach ($bad as $tid) {
        $torrent_stuffs->delete_by_id($tid);
        if (!empty($list[$tid]['info_hash'])) {
            $torrent_stuffs->remove_torrent($list[$tid]['info_hash'], $list[$tid]['id'], $list[$tid]['owner']);
        }
        ++$i;
    }

    $time_end = microtime(true);
    $run_time = $time_end - $time_start;
    $text = " Run time: $run_time seconds";
    echo $text . "\n";
    if ($data['clean_log']) {
        write_log("Normalize Cleanup: Completed, deleted $i torrents" . $text);
    }
}
