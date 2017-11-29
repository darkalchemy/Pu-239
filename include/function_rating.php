<?php
/**
 * @param $id
 * @param $what
 *
 * @return string|void
 */
function getRate($id, $what)
{
    global $CURUSER, $cache;
    if ($id == 0 || !in_array($what, [
            'topic',
            'torrent',
        ])) {
        return;
    }
    //== lets memcache $what
    $keys['rating'] = 'rating_' . $what . '_' . $id . '_' . $CURUSER['id'];
    $rating_cache = $cache->get($keys['rating']);
    if ($rating_cache === false || is_null($rating_cache)) {
        $qy = sql_query('SELECT sum(r.rating) AS sum, count(r.rating) AS count, r2.id AS rated, r2.rating  FROM rating AS r LEFT JOIN rating AS r2 ON (r2.' . $what . ' = ' . sqlesc($id) . ' AND r2.user = ' . sqlesc($CURUSER['id']) . ') WHERE r.' . $what . ' = ' . sqlesc($id) . ' GROUP BY r.' . $what) or sqlerr(__FILE__, __LINE__);
        $rating_cache = mysqli_fetch_assoc($qy);
        $cache->set($keys['rating'], $rating_cache, 0);
    }
    $completeres = sql_query('SELECT * FROM ' . (XBT_TRACKER == true ? 'xbt_files_users' : 'snatched') . ' WHERE ' . (XBT_TRACKER == true ? 'completedtime !=0' : 'complete_date !=0') . ' AND ' . (XBT_TRACKER == true ? 'uid' : 'userid') . ' = ' . $CURUSER['id'] . ' AND ' . (XBT_TRACKER == true ? 'fid' : 'torrentid') . ' = ' . $id);
    $completecount = mysqli_num_rows($completeres);
    // outputs
    if ($rating_cache['rated']) {
        $rate = '<ul class="star-rating tooltipper" title="You rated this ' . $what . ' ' . htmlsafechars($rating_cache['rating']) . ' star' . (htmlsafechars($rating_cache['rating']) > 1 ? 's' : '') . '"><li class="current-rating">.</li></ul>';
    } elseif ($what == 'torrent' && $completecount == 0) {
        $rate = '<ul class="star-rating tooltipper" title="You must download this ' . $what . ' in order to rate it."><li class="current-rating">.</li></ul>';
    } else {
        $i = 1;
        $rate = '<ul class="star-rating"><li class="current-rating">.</li>';
        foreach ([
                     'one-star',
                     'two-stars',
                     'three-stars',
                     'four-stars',
                     'five-stars',
                 ] as $star) {
            $rate .= '<li><a href="./ajax/rating.php?id=' . (int)$id . '&amp;rate=' . $i . '&amp;ref=' . urlencode($_SERVER['REQUEST_URI']) . '&amp;what=' . $what . '" class="' . $star . ' tooltipper" onclick="do_rate(' . $i . ',' . $id . ",'" . $what . "'); return false\" title=\"" . $i . ' star' . ($i > 1 ? 's' : '') . " out of 5\" >$i</a></li>";
            ++$i;
        }
        $rate .= '</ul>';
    }
    switch ($what) {
        case 'torrent':
            $return = '<div id="rate_' . $id . '">' . $rate . '</div>';
            break;

        case 'topic':
            $return = '<div id="rate_' . $id . '">' . $rate . '</div>';
            break;
    }

    return $return;
}

/**
 * @param $rate_sum
 * @param $rate_count
 *
 * @return string
 */
function showRate($rate_sum, $rate_count)
{
    return '<ul class="star-rating"><li class="current-rating" >.</li></ul>';
}
