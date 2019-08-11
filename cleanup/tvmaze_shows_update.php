<?php

declare(strict_types = 1);

use DI\DependencyException;
use DI\NotFoundException;
use Pu239\Cache;
use Pu239\Database;
use Pu239\Image;

/**
 * @param $data
 *
 * @throws DependencyException
 * @throws NotFoundException
 * @throws \Envms\FluentPDO\Exception
 *
 * @return bool|void
 */
function tvmaze_shows_update($data)
{
    global $container, $BLOCKS;

    $time_start = microtime(true);
    require_once INCL_DIR . 'function_tvmaze.php';
    if (!$BLOCKS['tvmaze_api_on']) {
        return;
    }

    $cache = $container->get(Cache::class);
    $updates = $cache->get('tvmaze_shows_data_');
    if ($updates === false || is_null($updates)) {
        $url = 'http://api.tvmaze.com/updates/shows';
        $json = fetch($url);
        if (empty($json)) {
            return false;
        }
        $updates = json_decode($json, true);
        if (!empty($shows)) {
            $cache->set('tvmaze_shows_data_', $updates, 43200);
        }
    }
    $images_class = $container->get(Image::class);
    $fluent = $container->get(Database::class);
    $shows = $fluent->from('tvmaze')
                    ->select(null)
                    ->select('tvmaze_id')
                    ->select('updated')
                    ->fetchPairs('tvmaze_id', 'updated');
    $limit = 0;
    foreach ($shows as $tvmaze_id => $updated) {
        if (isset($updates[$tvmaze_id]) && $updates[1] > $updated) {
            $start_time = microtime(true);
            $url = 'http://api.tvmaze.com/shows/' . $tvmaze_id;
            $json = fetch($url);
            if (empty($json)) {
                return false;
            }
            $update = json_decode($json, true);
            if (!empty($update['id'])) {
                $values = [
                    'name' => get_or_empty($update['name'], false),
                    'tvrage_id' => get_or_empty($update['externals']['tvrage'], true),
                    'thetvdb_id' => get_or_empty($update['externals']['thetvdb'], true),
                    'updated' => $updates[1],
                ];
                if (!empty($update['externals']['imdb']) && preg_match('/tt\d{7,8}$/', $update['externals']['imdb'])) {
                    $values['imdb_id'] = get_or_empty($update['externals']['imdb'], false);
                }
            }
            if (!empty($values)) {
                $fluent->update('tvmaze')
                       ->set($values)
                       ->where('tvmaze_id = ?', $tvmaze_id)
                       ->execute();
            }
            echo "TVMaze ID #{$tvmaze_id} updated.\n";
            if (++$limit >= 50) {
                break;
            }
            $end_time = microtime(true);
            if ($end_time - $start_time < 1) {
                usleep(intval(1000000 - ($end_time - $start_time) * 1000000));
            }
        }
    }

    $time_end = microtime(true);
    $run_time = $time_end - $time_start;
    $text = " Run time: $run_time seconds";
    echo $text . "\n";
    if ($data['clean_log']) {
        write_log('TVMaze Data Update completed' . $text);
    }
}

/**
 * @param      $param
 * @param bool $int
 *
 * @return mixed|string
 */
function get_or_empty($param, bool $int)
{
    if (!empty($param)) {
        if (is_int($param)) {
            return $param;
        }

        return htmlsafechars($param);
    }

    if ($int) {
        return 0;
    }

    return '';
}
