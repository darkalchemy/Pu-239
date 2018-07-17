<?php

function get_bluray_info()
{
    global $cache, $site_config;

    $bluray_data = $cache->get('bluray_');
    if ($bluray_data === false || is_null($bluray_data)) {
        $url = 'http://www.blu-ray.com/rss/newreleasesfeed.xml';
        $content = fetch($url);
        if (!$content) {
            return false;
        }
        $bluray_data = $content;
        if (!empty($bluray_content)) {
            $cache->set('bluray_', $bluray_data, 86400);
        }
    }

    if (empty($bluray_data)) {
        return false;
    }

    $pubs = $cache->get('bluray_pubs_');
    if ($pubs === false || is_null($pubs)) {
        $doc = new DOMDocument();
        $doc->loadXML($bluray_data);
        $items = $doc->getElementsByTagName('item');
        $pubs = [];
        $i = 10000;
        foreach ($items as $item) {
            ++$i;
            $movie = empty($item->getElementsByTagName('title')->item(0)->nodeValue) ? '' : $item->getElementsByTagName('title')->item(0)->nodeValue;
            $movie = trim(replace_unicode_strings(str_replace('(Blu-ray)', '', $movie)));
            $pubDate = empty($item->getElementsByTagName('pubDate')->item(0)->nodeValue) ? '' : $item->getElementsByTagName('pubDate')->item(0)->nodeValue;
            $description = empty($item->getElementsByTagName('description')->item(0)->nodeValue) ? '' : $item->getElementsByTagName('description')->item(0)->nodeValue;
            $description = explode(' | ', strip_tags(str_replace('<br><br>', ' | ', $description)));
            $imdb_info = search_omdb_by_title(str_replace(' 4K', '', $movie), replace_unicode_strings($description[1]));
            $poster = !empty($imdb_info['Poster']) && preg_match('/http/', $imdb_info['Poster']) ? url_proxy($imdb_info['Poster'], true, 150) : '';
            $placeholder = !empty($imdb_info['Poster']) && preg_match('/http/', $imdb_info['Poster']) ? url_proxy($imdb_info['Poster'], true, 150, null, 10) : '';
            $imdbid = !empty($imdb_info['imdbID']) ? $imdb_info['imdbID'] : '';
            $omdb_title = !empty($imdb_info['Title']) ? $imdb_info['Title'] : '';
            if (empty($poster) && !empty($imdbid)) {
                $poster = getMovieImagesByImdb($imdbid, 'movieposter');
                $poster = !empty($poster) ? url_proxy($poster, true, 150) : $site_config['pic_baseurl'] . 'noposter.png';
                $placeholder = !empty($poster) ? url_proxy($poster, true, 150, null, 10) : $site_config['pic_baseurl'] . 'noposter.png';
            }
            if (empty($poster)) {
                $poster = $site_config['pic_baseurl'] . 'noposter.png';
                $placeholder = $site_config['pic_baseurl'] . 'noposter.png';
            }
            $background = '';
            if (!empty($imdbid)) {
                $background = getMovieImagesByImdb($imdbid, 'moviebackground');
                $background = !empty($background) ? $background : '';
            }

            $imdbid = !empty($imdbid) ? $imdbid : $i;

            $pubs[] = [
                'title' => $movie,
                'omdb_title' => $omdb_title,
                'pubDate' => replace_unicode_strings($pubDate),
                'genre' => replace_unicode_strings($description[0]),
                'year' => replace_unicode_strings($description[1]),
                'runtime' => replace_unicode_strings($description[2]),
                'mpaa' => replace_unicode_strings($description[3]),
                'release_date' => replace_unicode_strings($description[4]),
                'description' => replace_unicode_strings($description[5]),
                'poster' => $poster,
                'placeholder' => $placeholder,
                'background' => url_proxy($background, true),
                'imdbid' => $imdbid,
            ];
        }

        if (!empty($pubs)) {
            $cache->set('bluray_pubs_', $pubs, 1800);
        }
    }

    if (empty($pubs)) {
        return false;
    }

    return $pubs;
}
