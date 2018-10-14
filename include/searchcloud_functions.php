<?php

/**
 * @param int $limit
 *
 * @return array|string
 */
function searchcloud($limit = 50)
{
    global $cache;

    if (!($return = $cache->get('searchcloud'))) {
        $search_q = sql_query('SELECT searchedfor, howmuch
                                FROM searchcloud
                                ORDER BY id DESC' . ($limit > 0 ? ' LIMIT ' . $limit : '')) or sqlerr(__FILE__, __LINE__);
        if (mysqli_num_rows($search_q)) {
            $return = [];
            while ($search_a = mysqli_fetch_assoc($search_q)) {
                $return[$search_a['searchedfor']] = $search_a['howmuch'];
            }
            ksort($return);
            $cache->set('searchcloud', $return, 0);

            return $return;
        }

        return [];
    }
    ksort($return);

    return $return;
}

/**
 * @param $word
 *
 * @throws \MatthiasMullie\Scrapbook\Exception\UnbegunTransaction
 */
function searchcloud_insert($word)
{
    global $cache, $searchcloud_stuffs;

    $searchcloud = searchcloud();
    $ip = getip();
    $howmuch = isset($searchcloud[$word]) ? $searchcloud[$word] + 1 : 1;
    if (!count($searchcloud) || !isset($searchcloud[$word])) {
        $searchcloud[$word] = $howmuch;
        $cache->set('searchcloud', $searchcloud, 0);
    } else {
        $cache->update_row('searchcloud', [
            $word => $howmuch,
        ], 0);
    }
    $values = [
        'searchedfor' => $word,
        'howmuch' => 1,
        'ip' => inet_pton($ip),
    ];
    $update = [
        'howmuch' => new Envms\FluentPDO\Literal('howmuch + 1'),
    ];
    $searchcloud_stuffs->insert($values, $update);
}

/**
 * @return string
 */
function cloud()
{
    global $site_config;

    $small = 14;
    $big = 40;
    $tags = searchcloud();

    if (!empty($tags)) {
        $minimum_count = min(array_values($tags));
        $maximum_count = max(array_values($tags));
        $spread = $maximum_count - $minimum_count;
        if ($spread == 0) {
            $spread = 1;
        }
        $cloud_html = '';
        $cloud_tags = [];
        $tags = shuffle_assoc($tags, 3);
        foreach ($tags as $tag => $count) {
            $size = floor($small + round(($count - $minimum_count) * ($big - $small) / $spread, 0, PHP_ROUND_HALF_UP));
            $color = random_color(100, 200);
            $cloud_tags[] = "
                            <a class='tooltipper tag_cloud' style='color:{$color}; font-size: {$size}px' href='{$site_config['baseurl']}/browse.php?search=" . urlencode($tag) . "&amp;searchin=all&amp;incldead=1' title='<div class=\"size_5 has-text-primary has-text-centered\">\"" . htmlsafechars($tag) . "\"</div><br>has been searched for {$count} times.'>
                                <span class='padding10 has-no-wrap'>" . htmlsafechars(stripslashes($tag)) . '</span>
                            </a>';
        }
        $cloud_html = implode("\n", $cloud_tags) . "\n";

        return $cloud_html;
    }
}
