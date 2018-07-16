<?php

function get_bluray_info()
{
    global $cache;

    $bluray_data = $cache->get('bluray_');
    if ($bluray_data === false || is_null($bluray_data)) {
        $url = "http://www.blu-ray.com/rss/newreleasesfeed.xml";
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

    return $bluray_data;
}
