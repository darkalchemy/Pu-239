<?php

/**
 * @param $data
 *
 * @throws Exception
 */
function tvmaze_update($data)
{
    global $fluent;

    $max = $fluent->from('tvmaze')
        ->select(null)
        ->select('MAX(tvmaze_id) AS id')
        ->fetch('id');

    $pages[0] = floor($max / 250);
    $pages[1] = ceil($max / 250);
    $i = 1;

    $values = [];
    foreach ($pages as $page) {
        $url = "http://api.tvmaze.com/shows?page=$page";
        $json = @file_get_contents($url);
        $shows = @json_decode($json, true);
        if ($shows) {
            foreach ($shows as $show) {
                $values[] = [
                    'name'       => htmlsafechars($show['name']),
                    'tvmaze_id'  => $show['id'],
                    'tvrage_id'  => $show['externals']['tvrage'],
                    'thetvdb_id' => $show['externals']['thetvdb'],
                    'imdb_id'    => $show['externals']['imdb'],
                ];
            }
        }
    }
    if (!empty($values)) {
        $fluent->insertInto('tvmaze')
            ->values($values)
            ->ignore()
            ->execute();
        ++$i;
    }

    if ($data['clean_log'] && $i > 0) {
        write_log("TVMaze ID's Cleanup: Completed using $i queries");
    }
}
