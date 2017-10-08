<?php
function searchcloud($limit = 50)
{
    global $mc1, $site_config;
    if (!($return = $mc1->get_value('searchcloud'))) {
        $search_q = sql_query('SELECT searchedfor, howmuch
                                FROM searchcloud
                                ORDER BY id DESC ' . ($limit > 0 ? 'LIMIT ' . $limit : '')) or sqlerr(__FILE__, __LINE__);
        if (mysqli_num_rows($search_q)) {
            $return = [];
            while ($search_a = mysqli_fetch_assoc($search_q)) {
                $return[$search_a['searchedfor']] = $search_a['howmuch'];
            }
            ksort($return);
            $mc1->cache_value('searchcloud', $return, 0);

            return $return;
        }

        return [];
    }
    ksort($return);
    return $return;
}

function searchcloud_insert($word)
{
    global $mc1, $site_config;
    $searchcloud = searchcloud();
    $ip = getip();
    $howmuch = isset($searchcloud[$word]) ? $searchcloud[$word] + 1 : 1;
    if (!count($searchcloud) || !isset($searchcloud[$word])) {
        $searchcloud[$word] = $howmuch;
        $mc1->cache_value('searchcloud', $searchcloud, 0);
    } else {
        $mc1->begin_transaction('searchcloud');
        $mc1->update_row(false, [
            $word => $howmuch,
        ]);
        $mc1->commit_transaction(0);
    }
    sql_query('INSERT INTO searchcloud(searchedfor,howmuch,ip) VALUES (' . sqlesc($word) . ',1,' . sqlesc($ip) . ') ON DUPLICATE KEY UPDATE howmuch=howmuch+1') or sqlerr(__FILE__, __LINE__);
}

function cloud()
{
    $small = 14;
    $big = 40;
    $tags = searchcloud();

    if (isset($tags)) {
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
                            <a class='tooltipper tag_cloud' style='color:{$color}; font-size: {$size}px' href='./browse.php?search=" . urlencode($tag) . "&amp;searchin=all&amp;incldead=1' title='<span class=\"size_5 text-main\">\"" . htmlsafechars($tag) . "\"</span><br>has been searched for {$count} times.'>
                                <span class='padding10'>" . htmlsafechars(stripslashes($tag)) . "</span>
                            </a>";
        }
        $cloud_html = join("\n", $cloud_tags) . "\n";

        return $cloud_html;
    }
}

