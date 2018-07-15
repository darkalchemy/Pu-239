<?php
/**
 * @param $tvmaze_data
 * @param $tvmaze_type
 *
 * @return string
 */
function tvmaze_format($tvmaze_data, $tvmaze_type)
{
    global $site_config;

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
                                                        <img src='" . url_proxy(strip_tags($role['photo']), true, 150, null) . "' class='tooltip-poster' />
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

    return join('', $tvmaze_display[$tvmaze_type]) . line_by_line('Cast', join('', $persons));
}

/**
 * @param $tvmaze_data
 * @param $tvmaze_type
 *
 * @return string
 */
function episode_format($tvmaze_data, $tvmaze_type)
{
    global $site_config;

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

    return join('', $tvmaze_display[$tvmaze_type]);
}

/**
 * @param $tvmaze_id
 * @param $season
 * @param $episode
 *
 * @return null|string
 */
function get_episode($tvmaze_id, $season, $episode)
{
    global $cache;

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
        }
    }
    if (!empty($episode_info)) {
        return "<div class='padding10'><div class='has-text-centered size_6 bottom20'>TVMaze Episode</div>" . episode_format($episode_info, 'episode') . '</div>';
    }

    return null;
}

/**
 * @param $torrents
 *
 * @return string
 *
 * @throws Exception
 * @throws \MatthiasMullie\Scrapbook\Exception\Exception
 * @throws \MatthiasMullie\Scrapbook\Exception\ServerUnhealthy
 * @throws \MatthiasMullie\Scrapbook\Exception\UnbegunTransaction
 */
function tvmaze($tvmaze_id, $id)
{
    global $fluent, $cache, $site_config, $CURUSER, $torrents;

    $set = [];
    if (empty($tvmaze_id)) {
        return null;
    }

    $force_update = false;
    if (empty($torrents['newgenre']) || empty($torrents['poster'])) {
        $force_update = true;
    }

    $tvmaze_show_data = $cache->get('tvmaze_' . $tvmaze_id);
    if ($force_update || $tvmaze_show_data === false || is_null($tvmaze_show_data)) {
        $tvmaze_link = "http://api.tvmaze.com/shows/{$tvmaze_id}?embed=cast";
        $content = fetch($tvmaze_link);
        if (empty($content)) {
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
    $use_12_hour = !empty($CURUSER['12_hour']) ? $CURUSER['12_hour'] === 'yes' ? 1 : 0 : $site_config['12_hour'];
    $tvmaze_show_data['airtime'] = $days . ' at ' . ($use_12_hour ? time24to12($airtime) : get_date($airtime, 'WITHOUT_SEC', 1, 1)) . " on {$tvmaze_show_data['network']['name']}. <span class='has-text-primary'>(Time zone: {$tvmaze_show_data['network']['country']['timezone']})</span>";
    $tvmaze_show_data['origin'] = "{$tvmaze_show_data['network']['country']['name']}: {$tvmaze_show_data['language']}";
    if (count($tvmaze_show_data['genres']) > 0) {
        $temp = implode(', ', array_map('strtolower', $tvmaze_show_data['genres']));
        $temp = explode(', ', $temp);
        $tvmaze_show_data['genres2'] = implode(', ', array_map('ucwords', $temp));
    }

    if (empty($torrents['newgenre'])) {
        $torrents['newgenre'] = $tvmaze_show_data['genres2'];
        $set['newgenre'] = ucwords($tvmaze_show_data['genres2']);
        $cache->update_row('torrent_details_' . $id, [
            'newgenre' => ucwords($tvmaze_show_data['genres2']),
        ], 0);
    }
    if (empty($torrents['poster'])) {
        $poster = '';
        if (!empty($tvmaze_show_data['image']['medium'])) {
            $poster = $tvmaze_show_data['image']['medium'];
        } elseif (!empty($tvmaze_show_data['_embedded']['show']) && !empty($tvmaze_show_data['_embedded']['show']['image']['medium'])) {
            $poster = $tvmaze_show_data['_embedded']['show']['image']['medium'];
        }
        if (!empty($poster)) {
            $torrents['poster'] = $poster;
            $set['poster'] = $tposter;
            $cache->update_row('torrent_details_' . $id, [
                'poster' => $poster,
            ], 0);
            $insert = $cache->get('insert_tvmaze_tvmazeid_' . $tvmaze_id);
            if ($insert === false || is_null($insert)) {
                $sql = "INSERT IGNORE INTO images (tvmaze_id, url, type) VALUES ($tvmaze_id, '$poster', 'poster')";
                sql_query($sql) or sqlerr(__FILE__, __LINE__);
                $cache->set('insert_tvmaze_tvmazeid_' . $tvmaze_id, 0, 604800);
            }
        }
    }
    if (!empty($set)) {
        $fluent->update('torrents')
            ->set($set)
            ->where('id = ?', $id)
            ->execute();
    }
    if (!empty($tvmaze_show_data)) {
        return "<div class='padding10'><div class='has-text-centered size_6 bottom20'>TVMaze</div>" . tvmaze_format($tvmaze_show_data, 'show') . '</div>';
    }

    return null;
}

function get_schedule($use_cache = true)
{
    global $cache;

    $url = 'https://api.tvmaze.com/schedule/full';
    $tvmaze_data = $cache->get('tvmaze_schedule_');

    if (!$use_cache || $tvmaze_data === false || is_null($tvmaze_data)) {
        $content = fetch($url);
        if (!$content) {
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

function insert_images_from_schedule($schedule, $date)
{
    global $cache;

    foreach ($schedule as $listing) {
        $poster = !empty($listing['image']['medium']) ? $listing['image']['medium'] : !empty($listing['_embedded']['show']['image']['medium']) ? $listing['_embedded']['show']['image']['medium'] : '';
        if ($listing['airdate'] === $date && $listing['_embedded']['show']['language'] === 'English' && !empty($poster)) {
            $insert = $cache->get('insert_tvmaze_tvmazeid_' . $listing['id']);
            if ($insert === false || is_null($insert)) {
                $sql = "INSERT IGNORE INTO images (tvmaze_id, url, type) VALUES ({$listing['id']}, '$poster', 'poster')";
                sql_query($sql) or sqlerr(__FILE__, __LINE__);
                $cache->set('insert_tvmaze_tvmazeid_' . $listing['id'], 0, 604800);
            }
        }
    }
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
                        <div class='has-text-red column is-2 size_5 padding5'>$heading: </div>
                        <span class='column padding5'>$body</span>
                    </div>";
}

function timeSort($a, $b)
{
    return strcmp($a['airstamp'], $b['airstamp']);
}
