<?php

declare(strict_types = 1);

use DI\DependencyException;
use DI\NotFoundException;
use Pu239\Cache;
use Pu239\Image;
use Spatie\Image\Exceptions\InvalidManipulation;

/**
 * @param $data
 *
 * @throws InvalidManipulation
 * @throws DependencyException
 * @throws NotFoundException
 * @throws \Envms\FluentPDO\Exception
 * @throws Exception
 */
function tvmaze_schedule_update($data)
{
    global $container, $BLOCKS;

    $time_start = microtime(true);
    require_once INCL_DIR . 'function_tvmaze.php';
    set_time_limit(1200);
    if (!$BLOCKS['tvmaze_api_on']) {
        return;
    }

    $tvmaze_data = get_schedule(false);
    $start = date('Y-m-d');
    $date = new DateTime($start);
    $end = $date->modify('+1 day')
                ->format('Y-m-d');

    $cache = $container->get(Cache::class);
    $images_class = $container->get(Image::class);
    if (!empty($tvmaze_data)) {
        foreach ($tvmaze_data as $tv) {
            if ($tv['airdate'] >= $start && $tv['airdate'] <= $end && $tv['_embedded']['show']['language'] === 'English') {
                $poster = !empty($tv['image']['original']) ? $tv['image']['original'] : (!empty($tv['_embedded']['show']['image']['original']) ? $tv['_embedded']['show']['image']['original'] : '');
                if (!empty($poster)) {
                    $insert = $cache->get('insert_tvmaze_tvmazeid_' . $tv['id']);
                    if ($insert === false || is_null($insert)) {
                        $values = [
                            'tvmaze_id' => $tv['id'],
                            'url' => $poster,
                            'type' => 'poster',
                        ];
                        if (!empty($tv['_embedded']['show']['externals']['imdb'])) {
                            $values['imdb_id'] = $tv['_embedded']['show']['externals']['imdb'];
                        }
                        $images_class->insert($values);
                        $cache->set('insert_tvmaze_tvmazeid_' . $tv['id'], 0, 604800);

                        url_proxy($poster, true, 250);
                        url_proxy($poster, true, 250, null, 20);
                    }
                }
            }
        }
    }

    $time_end = microtime(true);
    $run_time = $time_end - $time_start;
    $text = " Run time: $run_time seconds";
    echo $text . "\n";
    if ($data['clean_log']) {
        write_log('TVMaze Schedule Cleanup: Completed using 0 queries, processed $i images' . $text);
    }
}
