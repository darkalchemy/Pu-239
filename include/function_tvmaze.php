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

    $tvmaze_display['show'] = [
        'name'           => line_by_line('Title', '%s'),
        'url'            => line_by_line('Link', "<a href='{$site_config['anonymizer_url']}'>TVMaze Lookup%s</a>"),
        'premiered'      => line_by_line('Started', '%s'),
        'origin_country' => line_by_line('Country', '%s'),
        'status'         => line_by_line('Status', '%s'),
        'type'           => line_by_line('Classification', '%s'),
        'summary'        => line_by_line('Summary', '%s'),
        'runtime'        => line_by_line('Runtime', '%s min'),
        'genres2'        => line_by_line('Genres', '%s'),
    ];
    foreach ($tvmaze_display[$tvmaze_type] as $key => $value) {
        if (isset($tvmaze_data[$key])) {
            $tvmaze_display[$tvmaze_type][$key] = sprintf($value, $tvmaze_data[$key]);
        } else {
            $tvmaze_display[$tvmaze_type][$key] = sprintf($value, 'None Found');
        }
    }

    return join('', $tvmaze_display[$tvmaze_type]);
}

/**
 * @param $torrents
 *
 * @return string
 *
 * @throws \MatthiasMullie\Scrapbook\Exception\UnbegunTransaction
 */
function tvmaze(&$torrents)
{
    global $cache;

    $tvmaze_data = '';
    $row_update = [];
    if (preg_match("/^(.*)S\d+(E\d+)?/i", $torrents['name'], $tmp)) {
        $tvmaze = [
            'name' => str_replace(['.', '_'], ' ', $tmp[1]),
        ];
    } else {
        $tvmaze = [
            'name' => str_replace(['.', '_'], ' ', $torrents['name']),
        ];
    }
    $memkey = 'tvmaze_' . strtolower($tvmaze['name']);
    $tvmaze_id = $cache->get($memkey);
    if ($tvmaze_id === false || is_null($tvmaze_id)) {
        $tvmaze_link = sprintf('http://api.tvmaze.com/singlesearch/shows?q=%s', urlencode($tvmaze['name']));
        $tvmaze_array = json_decode(file_get_contents($tvmaze_link), true);
        if ($tvmaze_array) {
            $tvmaze_id = $tvmaze_array['id'];
            $cache->set($memkey, $tvmaze_id, 0);
        } else {
            return false;
        }
    }
    $force_update = false;
    if (empty($torrents['newgenre']) || empty($torrents['poster'])) {
        $force_update = true;
    }
    $memkey = 'tvrage_' . $tvmaze_id;
    $tvmaze_showinfo = $cache->get($memkey);
    if ($force_update || $tvmaze_showinfo === false || is_null($tvmaze_showinfo)) {
        $tvmaze['name'] = preg_replace('/\d{4}.$/', '', $tvmaze['name']);
        $tvmaze_link = "http://api.tvmaze.com/shows/$tvmaze_id";
        $tvmaze_array = json_decode(file_get_contents($tvmaze_link), true);
        $tvmaze_array['origin_country'] = $tvmaze_array['network']['country']['name'];
        if (count($tvmaze_array['genres']) > 0) {
            $temp = implode(', ', array_map('strtolower', $tvmaze_array['genres']));
            $temp = explode(', ', $temp);
            $tvmaze_array['genres2'] = implode(', ', array_map('ucwords', $temp));
        }
        if (empty($torrents['newgenre'])) {
            $row_update[] = 'newgenre = ' . sqlesc(ucwords($tvmaze_showinfo['genres2']));
        }
        $cache->update_row('torrent_details_' . $torrents['id'], [
            'newgenre' => ucwords($tvmaze_array['genres2']),
        ], 0);
        if (empty($torrents['poster'])) {
            $row_update[] = 'poster = ' . sqlesc($tvmaze_array['image']['original']);
        }
        $cache->update_row('torrent_details_' . $torrents['id'], [
            'poster' => $tvmaze_array['image']['original'],
        ], 0);
        if (!empty($row_update) && count($row_update)) {
            sql_query('UPDATE torrents SET ' . join(', ', $row_update) . ' WHERE id = ' . $torrents['id']) or sqlerr(__FILE__, __LINE__);
        }
        $tvmaze_showinfo = tvmaze_format($tvmaze_array, 'show');
        $cache->set($memkey, $tvmaze_showinfo, 604800);
        $tvmaze_data .= $tvmaze_showinfo;
        $torrents['poster'] = $tvmaze_array['image']['original'];
    } else {
        $tvmaze_data .= $tvmaze_showinfo;
    }

    return "<div class='padding10'>$tvmaze_data</div>";
}

function line_by_line($heading, $body)
{
    return "
                    <div class='columns'>
                        <div class='has-text-red column is-2 size_5 padding5'>$heading: </div>
                        <span class='column padding5'>$body</span>
                    </div>";
}
