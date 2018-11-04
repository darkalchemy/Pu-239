<?php

/**
 * @param     $title
 * @param int $year
 *
 * @return bool|mixed
 *
 * @throws Exception
 */
function search_omdb_by_title($title, int $year)
{
    global $cache, $BLOCKS, $fluent;

    if (!$BLOCKS['omdb_api_on']) {
        return false;
    }

    $apikey = $_ENV['OMDB_API_KEY'];

    if (empty($apikey)) {
        return false;
    }

    $hash = hash('sha256', $title . $year);
    $omdb_data = $cache->get('omdb_' . $hash);
    if ($omdb_data === false || is_null($omdb_data)) {
        $url = "http://www.omdbapi.com/?apikey=$apikey&t=" . urlencode(strtolower($title)) . '&y=' . urlencode($year) . '&plot=full';
        $content = fetch($url);
        if (!$content) {
            $cache->set('omdb_' . $hash, 'failed', 86400);

            return false;
        }
        $omdb_data = json_decode($content, true);
        if (!empty($omdb_data['Title']) && strtolower($omdb_data['Title']) === strtolower($title)) {
            if (!empty($omdb_data['Poster'])) {
                $imdbid = $omdb_data['imdbID'];
                $insert = $cache->get('insert_imdb_imdbid_' . $imdbid);
                if ($insert === false || is_null($insert)) {
                    $values = [
                        'imdb_id' => $imdbid,
                        'url' => $omdb_data['Poster'],
                        'type' => 'poster',
                    ];
                    $fluent->insertInto('images')
                        ->values($values)
                        ->ignore()
                        ->execute();
                    $cache->set('insert_imdb_imdbid_' . $imdbid, 'failed', 604800);
                }
            }
            $cache->set('omdb_' . $hash, $omdb_data, 604800);
        }
    }

    return $omdb_data;
}

/**
 * @param      $imdbid
 * @param bool $title
 *
 * @return bool|string
 *
 * @throws Exception
 */
function get_omdb_info($imdbid, $title = true, $data_only = false)
{
    global $cache, $BLOCKS, $fluent;

    if (!$BLOCKS['omdb_api_on']) {
        return false;
    }

    $apikey = $_ENV['OMDB_API_KEY'];

    if (empty($apikey)) {
        return false;
    }

    $omdb_data = $cache->get('omdb_' . $imdbid);
    if ($omdb_data === false || is_null($omdb_data)) {
        $url = "https://www.omdbapi.com/?apikey=$apikey&i=$imdbid&plot=full";
        $content = fetch($url);
        if (!$content) {
            $cache->set('omdb_' . $imdbid, 'failed', 86400);

            return false;
        }
        $omdb_data = json_decode($content, true);
        if (!empty($omdb_data)) {
            $cache->set('omdb_' . $imdbid, $omdb_data, 604800);
        }
    }

    if (empty($omdb_data)) {
        return false;
    }
    if ($data_only) {
        return $omdb_data;
    }
    $body = '';
    $exclude = [
        'Type',
        'imdbID',
        'Response',
        'Ratings',
    ];

    foreach ($omdb_data as $key => $value) {
        if ($key === 'Poster') {
            $poster = $value;
            if (!empty($poster)) {
                $insert = $cache->get('insert_imdb_imdbid_' . $imdbid);
                if ($insert === false || is_null($insert)) {
                    $values = [
                        'imdb_id' => $imdbid,
                        'url' => $poster,
                        'type' => 'poster',
                    ];
                    $fluent->insertInto('images')
                        ->values($values)
                        ->ignore()
                        ->execute();
                    $cache->set('insert_imdb_imdbid_' . $imdbid, 'failed', 604800);
                }
            }
        } elseif (!in_array($key, $exclude)) {
            $body .= "
            <div class='columns'>
                <div class='has-text-red column is-2 size_5 padding5'>{$key}: </div>
                <div class='column padding5'>{$value}</div>
            </div>";
        }
    }

    if ($title) {
        $body = "<div class='padding10'><div class='has-text-centered size_6 bottom20'>OMDb</div>$body</div>";
    } else {
        $body = "<div class='padding10'>$body</div>";
    }

    return $body;
}
