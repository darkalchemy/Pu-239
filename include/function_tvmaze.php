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

    $cast                   = !empty($tvmaze_data['_embedded']['cast']) ? $tvmaze_data['_embedded']['cast'] : [];
    $tvmaze_display['show'] = [
        'name'      => line_by_line('Title', '%s'),
        'url'       => line_by_line('Link', "<a href='{$site_config['anonymizer_url']}%s'>TVMaze Lookup</a>"),
        'premiered' => line_by_line('Started', '%s'),
        'airtime'   => line_by_line('Airs', '%s'),
        'origin'    => line_by_line('Origin: Language', '%s'),
        'status'    => line_by_line('Status', '%s'),
        'runtime'   => line_by_line('Runtime', '%s min'),
        'genres2'   => line_by_line('Genres', '%s'),
        'rated'     => line_by_line('Rating', '%s'),
        'summary'   => line_by_line('Summary', '%s'),
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
            'name'      => $person['person']['name'],
            'character' => $person['character']['name'],
            'thumb'     => $person['character']['image']['medium'],
            'photo'     => $person['character']['image']['original'],
            'url'       => $person['character']['url'],
            'id'        => $person['character']['id'],
        ];
    }

    foreach ($roles as $role) {
        $persons[] = "
                            <span class='padding5'>
                                <a href='{$site_config['anonymizer_url']}{$role['url']}' target='_blank'>
                                    <span class='dt-tooltipper-small' data-tooltip-content='#cast_{$role['id']}_tooltip'>
                                        <span class='cast'>
                                            <img src='" . htmlspecialchars(image_proxy($role['thumb'])) . "' alt='' class='round5'>
                                        </span>
                                        <span class='tooltip_templates'>
                                            <span id='cast_{$role['id']}_tooltip'>
                                                <span class='is-flex'>
                                                    <span class='has-text-centered'>
                                                        <img src='" . htmlspecialchars(image_proxy($role['photo'])) . "' class='tooltip-poster' />
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
        'name'      => line_by_line('Episode Title', '%s'),
        'url'       => line_by_line('Link', "<a href='{$site_config['anonymizer_url']}%s'>TVMaze Lookup</a>"),
        'timestamp' => line_by_line('Aired', '%s'),
        'runtime'   => line_by_line('Runtime', '%s min'),
        'summary'   => line_by_line('Summary', '%s'),
    ];

    foreach ($tvmaze_display[$tvmaze_type] as $key => $value) {
        if (isset($tvmaze_data[$key])) {
            if ('timestamp' === $key) {
                $tvmaze_data[$key] = get_date($tvmaze_data[$key], 'LONG');
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
        $tvmaze_link  = "http://api.tvmaze.com/shows/{$tvmaze_id}/episodebynumber?season={$season}&number={$episode}";
        $episode_info = json_decode(file_get_contents($tvmaze_link), true);
        if (!empty($episode_info['summary'])) {
            $episode_info['timestamp'] = strtotime($episode_info['airstamp']);
            $cache->set('tvshow_episode_info_' . $tvmaze_id . $season . $episode, $episode_info, 604800);
        }
    }
    if (!empty($episode_info)) {
        return "<div class='padding10'>" . episode_format($episode_info, 'episode') . '</div>';
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
function tvmaze(&$torrents)
{
    global $fluent, $cache;

    $set            = [];
    $tvmaze['name'] = get_show_name($torrents['name']);
    $tvmaze_id      = get_show_id($torrents['name'], 'tvmaze_id');

    $force_update = false;
    if (empty($torrents['newgenre']) || empty($torrents['poster'])) {
        $force_update = true;
    }

    $tvmaze_show_data = $cache->get('tvmaze_' . $tvmaze_id);
    if ($force_update || false === $tvmaze_show_data || is_null($tvmaze_show_data)) {
        $tvmaze_link                 = "http://api.tvmaze.com/shows/{$tvmaze_id}?embed=cast";
        $tvmaze_show_data            = json_decode(file_get_contents($tvmaze_link), true);
        $tvmaze_show_data['rated']   = $tvmaze_show_data['rating']['average'];
        $airedtime                   = explode(':', $tvmaze_show_data['schedule']['time']);
        $days                        = implode(', ', $tvmaze_show_data['schedule']['days']);
        $tvmaze_show_data['airtime'] = $days . ' at ' . time24to12($airedtime[0], $airedtime[1]) . " on {$tvmaze_show_data['network']['name']}. <span class='has-text-primary'>(Time zone: {$tvmaze_show_data['network']['country']['timezone']})</span>";
        $tvmaze_show_data['origin']  = "{$tvmaze_show_data['network']['country']['name']}: {$tvmaze_show_data['language']}";
        if (count($tvmaze_show_data['genres']) > 0) {
            $temp                        = implode(', ', array_map('strtolower', $tvmaze_show_data['genres']));
            $temp                        = explode(', ', $temp);
            $tvmaze_show_data['genres2'] = implode(', ', array_map('ucwords', $temp));
        }
        $cache->set('tvmaze_' . $tvmaze_id, $tvmaze_show_data, 604800);
    }
    if (empty($torrents['newgenre'])) {
        $torrents['newgenre'] = $tvmaze_show_data['genres2'];
        $set['newgenre']      = ucwords($tvmaze_show_data['genres2']);
        $cache->update_row('torrent_details_' . $torrents['id'], [
            'newgenre' => ucwords($tvmaze_show_data['genres2']),
        ], 0);
    }
    if (empty($torrents['poster'])) {
        $torrents['poster'] = $tvmaze_show_data['image']['original'];
        $set['poster']      = $tvmaze_show_data['image']['original'];
        $cache->update_row('torrent_details_' . $torrents['id'], [
            'poster' => $tvmaze_show_data['image']['original'],
        ], 0);
    }
    if (!empty($set)) {
        $fluent->update('torrents')
            ->set($set)
            ->where('id = ?', $torrents['id'])
            ->execute();
    }
    if (!empty($tvmaze_show_data)) {
        return "<div class='padding10'>" . tvmaze_format($tvmaze_show_data, 'show') . '</div>';
    }

    return null;
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
