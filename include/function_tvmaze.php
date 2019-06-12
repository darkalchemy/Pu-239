<?php

declare(strict_types = 1);

use DI\DependencyException;
use DI\NotFoundException;
use MatthiasMullie\Scrapbook\Exception\UnbegunTransaction;
use Pu239\Cache;
use Pu239\Image;
use Pu239\Torrent;
use Spatie\Image\Exceptions\InvalidManipulation;

require_once INCL_DIR . 'function_html.php';

/**
 * @param $tvmaze_data
 * @param $tvmaze_type
 *
 * @throws DependencyException
 * @throws NotFoundException
 * @throws \Envms\FluentPDO\Exception
 * @throws InvalidManipulation
 *
 * @return string|null
 */
function tvmaze_format($tvmaze_data, $tvmaze_type)
{
    global $site_config, $BLOCKS;
    if (!$BLOCKS['tvmaze_api_on']) {
        return null;
    }

    $cast = !empty($tvmaze_data['_embedded']['cast']) ? $tvmaze_data['_embedded']['cast'] : [];
    $tvmaze_display['show'] = [
        'name' => line_by_line('Series Title', '%s'),
        'url' => line_by_line('Series Link', "<a href='{$site_config['site']['anonymizer_url']}%s'>TVMaze Lookup</a>"),
        'premiered' => line_by_line('Serires Started', '%s'),
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
                        <ul class='right10'>
                            <li>
                                <a href='" . url_proxy($role['url']) . "' target='_blank'>
                                    <div class='dt-tooltipper-small' data-tooltip-content='#cast_{$role['id']}_tooltip'>
                                        <span class='cast'>
                                            <img src='" . url_proxy(strip_tags($role['thumb']), true) . "' alt='' class='round5'>
                                        </span>
                                        <div class='tooltip_templates'>
                                            <div id='cast_{$role['id']}_tooltip'>
                                                <div class='tooltip-torrent padding10'>
													<div class='columns is-marginless is-paddingless'>
														<div class='column padding10 is-4'>
                                                            <span>
                                                                <img src='" . url_proxy(strip_tags($role['thumb']), true, 250) . "' class='tooltip-poster' alt=''>
                                                            </span>
														</div>
														<div class='column paddin10 is-8'>
                                                            <div>
                                                                <div class='columns is-multiline'>
                                                                    <div class='column padding5 is-4'>
                                                                        <span class='size_4 has-text-primary'>Name:</span>
                                                                    </div>
                                                                    <div class='column padding5 is-8'>
                                                                        <span class='size_4'>{$role['name']}</span>
                                                                    </div>
                                                                    <div class='column padding5 is-4'>
                                                                        <span class='size_4 has-text-primary'>Role:</span>
                                                                    </div>
                                                                    <div class='column padding5 is-8'>
                                                                        <span class='size_4'>{$role['character']}</span>
                                                                    </div>
                                                                </div>
                                                            </div>
														</div>
													</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </li>
                        </ul>";
    }
    $cast = "<div class='level-left is-wrapped'>" . implode(' ', $persons) . '</div>';

    return implode('', $tvmaze_display[$tvmaze_type]) . line_by_line('Cast', $cast);
}

/**
 * @param $tvmaze_data
 * @param $tvmaze_type
 *
 * @throws NotFoundException
 * @throws DependencyException
 *
 * @return bool|string
 */
function episode_format($tvmaze_data, $tvmaze_type)
{
    global $site_config, $BLOCKS;

    if (!$BLOCKS['tvmaze_api_on']) {
        return false;
    }
    $tvmaze_display['episode'] = [
        'name' => line_by_line('Episode Title', '%s'),
        'season_episode' => line_by_line('Episode', '%s'),
        'url' => line_by_line('Episode Link', "<a href='{$site_config['site']['anonymizer_url']}%s'>TVMaze Lookup</a>"),
        'showtime' => line_by_line('Aired', '%s'),
        'runtime' => line_by_line('Runtime', '%s min'),
        'summary' => line_by_line('Summary', '%s'),
    ];

    foreach ($tvmaze_display[$tvmaze_type] as $key => $value) {
        if (isset($tvmaze_data[$key])) {
            if ($key === 'timestamp') {
                $tvmaze_data[$key] = get_date((int) $tvmaze_data[$key], 'WITHOUT_SEC');
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
 * @throws DependencyException
 * @throws NotFoundException
 * @throws \Envms\FluentPDO\Exception
 * @throws UnbegunTransaction
 *
 * @return bool|string|null
 */
function get_episode($tvmaze_id, $season, $episode, $tid)
{
    global $container, $BLOCKS;

    if (!$BLOCKS['tvmaze_api_on']) {
        return false;
    }
    $cache = $container->get(Cache::class);
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
    $episode_info['showtime'] = get_date(strtotime($episode_info['airtime'] . ' ' . $episode_info['airdate']), 'LONG', 1, 0);
    $episode_info['season_episode'] = 'S' . sprintf('%02d', $episode_info['season']) . 'E' . sprintf('%02d', $episode_info['number']);
    preg_match('/(\d{4})/', $episode_info['airdate'], $match);
    if (!empty($match[1])) {
        $episode_info['year'] = $match[1];
        $set = [
            'year' => $episode_info['year'],
        ];
        $torrents_class = $container->get(Torrent::class);
        $torrents_class->update($set, $tid);
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
 * @throws DependencyException
 * @throws InvalidManipulation
 * @throws NotFoundException
 * @throws UnbegunTransaction
 * @throws \Envms\FluentPDO\Exception
 *
 * @return bool|string
 */
function tvmaze(int $tvmaze_id, int $tid, int $season = 0, int $episode = 0, string $poster = '')
{
    global $container, $site_config, $BLOCKS;

    if (!$BLOCKS['tvmaze_api_on'] || empty($tvmaze_id)) {
        return false;
    }
    $cache = $container->get(Cache::class);
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
    $use_12_hour = !empty($CURUSER['use_12_hour']) ? $CURUSER['use_12_hour'] : $site_config['site']['use_12_hour'];
    $tvmaze_show_data['airtime'] = $days . ' at ' . ($use_12_hour ? time24to12($airtime) : get_date((int) $airtime, 'WITHOUT_SEC', 0, 1)) . " on {$tvmaze_show_data['network']['name']}. <span class='has-text-primary'>(Time zone: {$tvmaze_show_data['network']['country']['timezone']})</span>";
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
            $images_class = $container->get(Image::class);
            $images_class->insert($values);
        }
    }
    $torrents_class = $container->get(Torrent::class);
    $torrents_class->update($set, $tid);
    $episode = get_episode($tvmaze_id, $season, $episode, $tid);
    if (!empty($tvmaze_show_data)) {
        if (!empty($poster)) {
            $tvmaze_data = "
            <div class='padding20'>
                <div class='columns bottom20'>
                    <div class='column is-one-third is-paddingless'>
                        <img src='" . url_proxy($poster, true, 450) . "' alt='' class='round10 img-polaroid'>
                    </div>
                    <div class='column'>
                        <div class='left20'>" . $episode . tvmaze_format($tvmaze_show_data, 'show') . '
                        </div>
                    </div>
                </div>
            </div>';
        } else {
            $tvmaze_data = "<div class='column'>" . $episode . tvmaze_format($tvmaze_show_data, 'show') . '</div>';
        }
        $cache->set('tvmaze_fullset_' . $tvmaze_id, $tvmaze_data, 604800);

        return $tvmaze_data;
    }

    return false;
}

/**
 * @param bool $use_cache
 *
 * @throws NotFoundException
 * @throws \Envms\FluentPDO\Exception
 * @throws DependencyException
 *
 * @return bool|mixed
 */
function get_schedule($use_cache = true)
{
    global $container, $BLOCKS;
    if (!$BLOCKS['tvmaze_api_on']) {
        return false;
    }

    $url = 'https://api.tvmaze.com/schedule/full';
    $cache = $container->get(Cache::class);
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
    $heading = str_replace('Origin: ', '', $heading);

    return "
                    <div class='columns'>
                        <div class='has-text-primary column is-2 size_5 padding5'>$heading: </div>
                        <span class='column padding5'>$body</span>
                    </div>";
}

/**
 * @param $a
 * @param $b
 *
 * @return int
 */
function timeSort($a, $b)
{
    return strcmp($a['airstamp'], $b['airstamp']);
}
