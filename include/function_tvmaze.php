<?php

require_once INCL_DIR . 'html_functions.php';

/**
 * @param $tvmaze_data
 * @param $tvmaze_type
 *
 * @return string
 */
function tvmaze_format($tvmaze_data, $tvmaze_type)
{
    global $site_config, $BLOCKS;

    if (!$BLOCKS['tvmaze_api_on']) {
        return;
    }

    $cast = !empty($tvmaze_data['_embedded']['cast']) ? $tvmaze_data['_embedded']['cast'] : [];
    $tvmaze_display['show'] = [
        'name' => line_by_line('Title', '%s'),
        'url' => line_by_line('Link', "<a href='{$site_config['anonymizer_url']}%s'>TVMaze Lookup</a>"),
        'premiered' => line_by_line('Started', '%s'),
        'airtime' => line_by_line('Airs', '%s'),
        'origin' => line_by_line('Origin: Language', '%s'),
        'status' => line_by_line('Status', '%s'),
        'runtime' => line_by_line('Runtime', '%s min'),
        'genres2' => line_by_line('Genres', '%s'),
        'rated' => line_by_line('Rating', '%s'),
        'summary' => line_by_line('Summary', '%s'),
    ];

    foreach ($tvmaze_display[$tvmaze_type] as $key => $value) {
        if (isset($tvmaze_data[$key])) {
            $tvmaze_display[$tvmaze_type][$key] = sprintf($value, $tvmaze_data[$key]);
        } else {
            $tvmaze_display[$tvmaze_type][$key] = sprintf($value, 'None Found');
        }
    }

    $persons = $roles = [];
    foreach ($cast as $person) {
        $roles[] = [
            'name' => $person['person']['name'],
            'character' => $person['character']['name'],
            'thumb' => $person['character']['image']['medium'],
            'photo' => $person['character']['image']['medium'],
            'url' => $person['character']['url'],
            'id' => $person['character']['id'],
        ];
    }

    foreach ($roles as $role) {
        $persons[] = "
                            <span class='padding5'>
                                <a href='" . url_proxy($role['url']) . "' target='_blank'>
                                    <span class='dt-tooltipper-small' data-tooltip-content='#cast_{$role['id']}_tooltip'>
                                        <span class='cast'>
                                            <img src='" . url_proxy(strip_tags($role['thumb']), true) . "' alt='' class='round5'>
                                        </span>
                                        <span class='tooltip_templates'>
                                            <span id='cast_{$role['id']}_tooltip'>
                                                <span class='is-flex'>
                                                    <span class='has-text-centered'>
                                                        <img src='" . url_proxy(strip_tags($role['photo']), true, 250) . "' class='tooltip-poster'>
                                                        <p class='top10'>{$role['name']}</p>
                                                        <p>{$role['character']}</p>
                                                    </span>
                                                </span>
                                            </span>
                                        </span>
                                    </span>
                                </a>
                            </span>";
    }

    return implode('', $tvmaze_display[$tvmaze_type]) . line_by_line('Cast', implode('', $persons));
}

/**
 * @param $tvmaze_data
 * @param $tvmaze_type
 *
 * @return string
 */
function episode_format($tvmaze_data, $tvmaze_type)
{
    global $site_config, $BLOCKS;

    if (!$BLOCKS['tvmaze_api_on']) {
        return false;
    }
    $tvmaze_display['episode'] = [
        'name' => line_by_line('Episode Title', '%s'),
        'url' => line_by_line('Link', "<a href='{$site_config['anonymizer_url']}%s'>TVMaze Lookup</a>"),
        'timestamp' => line_by_line('Aired', '%s'),
        'runtime' => line_by_line('Runtime', '%s min'),
        'summary' => line_by_line('Summary', '%s'),
    ];

    foreach ($tvmaze_display[$tvmaze_type] as $key => $value) {
        if (isset($tvmaze_data[$key])) {
            if ($key === 'timestamp') {
                $tvmaze_data[$key] = get_date($tvmaze_data[$key], 'WITHOUT_SEC');
            }
            $tvmaze_display[$tvmaze_type][$key] = sprintf($value, $tvmaze_data[$key]);
        } else {
            $tvmaze_display[$tvmaze_type][$key] = sprintf($value, 'None Found');
        }
    }

    return implode('', $tvmaze_display[$tvmaze_type]);
}

/**
 * @param $tvmaze_id
 * @param $season
 * @param $episode
 * @param $tid
 *
 * @return bool|null|string
 *
 * @throws \Envms\FluentPDO\Exception
 * @throws \MatthiasMullie\Scrapbook\Exception\UnbegunTransaction
 */
function get_episode($tvmaze_id, $season, $episode, $tid)
{
    global $cache, $BLOCKS, $torrent_stuffs;

    if (!$BLOCKS['tvmaze_api_on']) {
        return false;
    }

    $episode_info = $cache->get('tvshow_episode_info_' . $tvmaze_id . $season . $episode);
    if ($episode_info === false || is_null($episode_info)) {
        $tvmaze_link = "http://api.tvmaze.com/shows/{$tvmaze_id}/episodebynumber?season={$season}&number={$episode}";
        $content = fetch($tvmaze_link);
        if (empty($content)) {
            return false;
        }
        $episode_info = json_decode($content, true);
        if (!empty($episode_info['summary'])) {
            $episode_info['timestamp'] = strtotime($episode_info['airstamp']);
            $cache->set('tvshow_episode_info_' . $tvmaze_id . $season . $episode, $episode_info, 604800);
        } else {
            $cache->set('tvshow_episode_info_' . $tvmaze_id . $season . $episode, 'failed', 86400);
        }
    }
    preg_match('/(\d{4})/', $episode_info['airdate'], $match);
    if (!empty($match[1])) {
        $episode_info['year'] = $match[1];
        $set = [
            'year' => $episode_info['year'],
        ];
        $torrent_stuffs->update($set, $tid);
    }

    if (!empty($episode_info)) {
        return episode_format($episode_info, 'episode');
    }

    return null;
}

/**
 * @param int    $tvmaze_id
 * @param int    $tid
 * @param int    $season
 * @param int    $episode
 * @param string $poster
 *
 * @return bool|string
 *
 * @throws \Envms\FluentPDO\Exception
 * @throws \MatthiasMullie\Scrapbook\Exception\UnbegunTransaction
 * @throws \Spatie\Image\Exceptions\InvalidManipulation
 */
function tvmaze(int $tvmaze_id, int $tid, $season = 0, $episode = 0, $poster = '')
{
    global $cache, $site_config, $CURUSER, $BLOCKS, $torrent_stuffs, $image_stuffs;

    if (!$BLOCKS['tvmaze_api_on'] || empty($tvmaze_id)) {
        return false;
    }

    $tvmaze_show_data = $cache->get('tvmaze_' . $tvmaze_id);
    if ($tvmaze_show_data === false || is_null($tvmaze_show_data)) {
        $tvmaze_link = "http://api.tvmaze.com/shows/{$tvmaze_id}?embed=cast";
        $content = fetch($tvmaze_link);
        if (empty($content)) {
            $cache->set('tvmaze_' . $tvmaze_id, 'failed', 86400);

            return false;
        }
        $tvmaze_show_data = json_decode($content, true);
        $cache->set('tvmaze_' . $tvmaze_id, $tvmaze_show_data, 604800);
    }

    $tvmaze_show_data['rated'] = $tvmaze_show_data['rating']['average'];
    $airtime = explode(':', $tvmaze_show_data['schedule']['time']);
    if (!empty($airtime)) {
        $timestamp = strtotime('today midnight');
        $airtime = $timestamp + $airtime[0] * 3600 + $airtime[1] * 60;
    }

    $days = implode(', ', $tvmaze_show_data['schedule']['days']);
    $use_12_hour = !empty($CURUSER['use_12_hour']) ? $CURUSER['use_12_hour'] : $site_config['use_12_hour'];
    $tvmaze_show_data['airtime'] = $days . ' at ' . ($use_12_hour ? time24to12($airtime) : get_date($airtime, 'WITHOUT_SEC', 1, 1)) . " on {$tvmaze_show_data['network']['name']}. <span class='has-text-primary'>(Time zone: {$tvmaze_show_data['network']['country']['timezone']})</span>";
    $tvmaze_show_data['origin'] = "{$tvmaze_show_data['network']['country']['name']}: {$tvmaze_show_data['language']}";
    if (count($tvmaze_show_data['genres']) > 0) {
        $temp = implode(', ', array_map('strtolower', $tvmaze_show_data['genres']));
        $temp = explode(', ', $temp);
        $tvmaze_show_data['genres2'] = implode(', ', array_map('ucwords', $temp));
    }

    $set = [
        'newgenre' => $tvmaze_show_data['genres2'],
        'rating' => $tvmaze_show_data['rating']['average'],
    ];

    $episode = get_episode($tvmaze_id, $season, $episode, $tid);

    if (empty($poster)) {
        if (!empty($tvmaze_show_data['image']['medium'])) {
            $poster = $tvmaze_show_data['image']['medium'];
        } elseif (!empty($tvmaze_show_data['_embedded']['show']) && !empty($tvmaze_show_data['_embedded']['show']['image']['medium'])) {
            $poster = $tvmaze_show_data['_embedded']['show']['image']['medium'];
        }
        if (!empty($poster)) {
            $set['poster'] = $poster;
            $values = [
                'tvmaze_id' => $tvmaze_id,
                'url' => $poster,
                'type' => 'poster',
            ];
            $image_stuffs->insert($values);
        }
    }
    $torrent_stuffs->update($set, $tid);

    $episode = get_episode($tvmaze_id, $season, $episode, $tid);

    if (!empty($tvmaze_show_data)) {
        if (!empty($poster)) {
            $tvmaze_data = "
            <div class='padding10'>
                <div class='columns'>
                    <div class='column is-3'>
                        <img src='" . placeholder_image('250') . "' data-src='" . url_proxy($poster, true, 250) . "' class='lazy round10 img-polaroid'>
                    </div>
                    <div class='column'>" . tvmaze_format($tvmaze_show_data, 'show') . $episode . '
                    </div>
                </div>
            </div>';
        } else {
            $tvmaze_data = "<div class='column'>" . tvmaze_format($tvmaze_show_data, 'show') . $episode . '</div>';
        }
        $cache->set('tvmaze_fullset_' . $tvmaze_id, $tvmaze_data, 604800);

        return $tvmaze_data;
    }

    return false;
}

/**
 * @param bool $use_cache
 *
 * @return bool|mixed
 */
function get_schedule($use_cache = true)
{
    global $cache, $BLOCKS;

    if (!$BLOCKS['tvmaze_api_on']) {
        return false;
    }

    $url = 'https://api.tvmaze.com/schedule/full';
    $tvmaze_data = $cache->get('tvmaze_schedule_');

    if (!$use_cache || $tvmaze_data === false || is_null($tvmaze_data)) {
        $content = fetch($url);
        if (!$content) {
            $cache->set('tvmaze_schedule_', 'failed', 3600);

            return false;
        }
        $tvmaze_data = bzcompress($content, 9);
        $cache->set('tvmaze_schedule_', $tvmaze_data, 0);
    }

    if (!empty($tvmaze_data)) {
        $data = bzdecompress($tvmaze_data);

        return json_decode($data, true);
    }

    return false;
}

/**
 * @param $heading
 * @param $body
 *
 * @return string
 */
function line_by_line($heading, $body)
{
    return "
                    <div class='columns'>
                        <div class='has-text-danger column is-2 size_5 padding5'>$heading: </div>
                        <span class='column padding5'>$body</span>
                    </div>";
}

/**
 * @param $a
 * @param $b
 *
 * @return int|lt
 */
function timeSort($a, $b)
{
    return strcmp($a['airstamp'], $b['airstamp']);
}
