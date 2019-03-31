<?php

/**
 * @return array|bool|mixed
 *
 * @throws \Spatie\Image\Exceptions\InvalidManipulation
 */
function get_bluray_info()
{
    global $cache, $site_config, $BLOCKS;

    if (!$BLOCKS['bluray_com_api_on']) {
        return false;
    }

    $bluray_data = $cache->get('bluray_xml_');
    if ($bluray_data === false || is_null($bluray_data)) {
        $url = 'http://www.blu-ray.com/rss/newreleasesfeed.xml';
        $bluray_data = fetch($url);
        if (!empty($bluray_data)) {
            $cache->set('bluray_xml_', $bluray_data, 86400);
        } else {
            $cache->set('bluray_xml_', 'failed', 3600);
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
        $i = 1000;
        foreach ($items as $item) {
            ++$i;
            $movie = empty($item->getElementsByTagName('title')
                                ->item(0)->nodeValue) ? '' : $item->getElementsByTagName('title')
                                                                  ->item(0)->nodeValue;
            $movie = trim(replace_unicode_strings(str_replace('(Blu-rdelete', '', $movie)));
            $movie = trim(replace_unicode_strings(str_replace('(Blu-ray)', '', $movie)));
            $pubDate = empty($item->getElementsByTagName('pubDate')
                                  ->item(0)->nodeValue) ? '' : $item->getElementsByTagName('pubDate')
                                                                    ->item(0)->nodeValue;
            $description = empty($item->getElementsByTagName('description')
                                      ->item(0)->nodeValue) ? '' : $item->getElementsByTagName('description')
                                                                        ->item(0)->nodeValue;
            $description = explode(' | ', strip_tags(str_replace('<br><br>', ' | ', $description)));
            $link = empty($item->getElementsByTagName('link')
                               ->item(0)->nodeValue) ? '' : $item->getElementsByTagName('link')
                                                                 ->item(0)->nodeValue;
            $poster_link = '';
            if ($link) {
                preg_match('#https?://www.blu-ray.com/movies/(.*)/(.*)/#', $link, $match);
                if (!empty($match[1])) {
                    $poster_link = "https://images.static-bluray.com/movies/covers/{$match[2]}_large.jpg";
                }
            }

            $poster = $placeholder = $site_config['pic_baseurl'] . 'noposter.png';

            if (!empty($poster_link)) {
                $image = url_proxy($poster_link, true, 250);
                if ($image) {
                    $poster = $image;
                    $placeholder = url_proxy($poster_link, true, 250, null, 20);
                }
            }

            $pubs[] = [
                'title' => $movie,
                'pubDate' => replace_unicode_strings($pubDate),
                'genre' => replace_unicode_strings($description[0]),
                'year' => replace_unicode_strings($description[1]),
                'runtime' => replace_unicode_strings($description[2]),
                'mpaa' => replace_unicode_strings($description[3]),
                'release_date' => replace_unicode_strings($description[4]),
                'description' => replace_unicode_strings($description[5]),
                'poster' => $poster,
                'placeholder' => $placeholder,
                'imdbid' => $i,
            ];
        }

        if (!empty($pubs)) {
            $cache->set('bluray_pubs_', $pubs, 1800);
        } else {
            $cache->set('bluray_pubs_', 'failed', 900);
        }
    }

    if (empty($pubs)) {
        return false;
    }

    return $pubs;
}
