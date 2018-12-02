<?php

/**
 * @param $id
 * @param $what
 *
 * @return bool|string
 *
 * @throws \Envms\FluentPDO\Exception
 */
function getRate($id, $what)
{
    global $CURUSER, $site_config, $fluent, $cache;

    $return = false;
    if ($id == 0 || !in_array($what, [
            'topic',
            'torrent',
        ])) {
        return $return;
    }
    $keys['rating'] = 'rating_' . $what . '_' . $id . '_' . $CURUSER['id'];
    $rating_cache = $cache->get($keys['rating']);
    if ($rating_cache === false || is_null($rating_cache)) {
        $qy1 = $fluent->from('rating')
            ->select(null)
            ->select('IFNULL(SUM(rating), 0) AS sum')
            ->select('IFNULL(COUNT(*), 0) AS count')
            ->where("$what = ?", $id)
            ->fetch();

        $qy2 = $fluent->from('rating')
            ->select(null)
            ->select('id AS rated')
            ->select('rating')
            ->where("$what = ?", $id)
            ->where('user = ?', $CURUSER['id'])
            ->fetch();

        if (!empty($qy2)) {
            $rating_cache = array_merge($qy1, $qy2);
        } else {
            $rating_cache = $qy1;
            $rating_cache['rated'] = 0;
            $rating_cache['rating'] = 0;
        }
        $cache->set($keys['rating'], $rating_cache, 0);
        $ratings = $cache->get('ratings_' . $id);
        if (!empty($ratings) && !in_array($CURUSER['id'], $ratings)) {
            $ratings[] = $CURUSER['id'];
            $cache->set('ratings_' . $id, $ratings, 0);
        }
    }

    $completeres = sql_query('SELECT * FROM ' . (XBT_TRACKER ? 'xbt_files_users' : 'snatched') . ' WHERE ' . (XBT_TRACKER ? 'completedtime !=0' : 'complete_date !=0') . ' AND ' . (XBT_TRACKER ? 'uid' : 'userid') . ' = ' . $CURUSER['id'] . ' AND ' . (XBT_TRACKER ? 'fid' : 'torrentid') . ' = ' . $id);
    $completecount = mysqli_num_rows($completeres);
    if ($rating_cache['rated']) {
        $rated = number_format($rating_cache['sum'] / $rating_cache['count'] / 5 * 100, 0) . '%';
        $rate = "
            <div class='star-ratings-css tooltipper' title='Rating: $rated.<br>You rated this $what {$rating_cache['rating']} star" . plural($rating_cache['rating']) . "'>
                <div class='star-ratings-css-top' style='width: $rated;'>
                    <span>&#9733;</span>
                    <span>&#9733;</span>
                    <span>&#9733;</span>
                    <span>&#9733;</span>
                    <span>&#9733;</span>
                </div>
                <div class='star-ratings-css-bottom'>
                    <span>&#9734;</span>
                    <span>&#9734;</span>
                    <span>&#9734;</span>
                    <span>&#9734;</span>
                    <span>&#9734;</span>
                </div>
            </div>";
    } elseif ($what === 'torrent' && $completecount == 0) {
        $rated = 0;
        $title = 'Unrated';
        if (!empty($rating_cache['count'])) {
            $rated = number_format($rating_cache['sum'] / $rating_cache['count'] / 5 * 100, 0) . '%';
            $title = "Rating: $rated.";
        }
        $rate = "
            <div class='star-ratings-css tooltipper' title='{$title}<br>You must download this torrent in order to rate it.'>
                <div class='star-ratings-css-top' style='width: $rated;'>
                    <span>&#9733;</span>
                    <span>&#9733;</span>
                    <span>&#9733;</span>
                    <span>&#9733;</span>
                    <span>&#9733;</span>
                </div>
                <div class='star-ratings-css-bottom'>
                    <span>&#9734;</span>
                    <span>&#9734;</span>
                    <span>&#9734;</span>
                    <span>&#9734;</span>
                    <span>&#9734;</span>
                </div>
            </div>";
    } else {
        $i = 5;
        $rate = '
                    <div id="rated" class="rating">';
        foreach ([
                     'five stars',
                     'four stars',
                     'three stars',
                     'two stars',
                     'one star',
                 ] as $star) {
            $rate .= "
                        <span title='$star out of 5' class='tooltipper' onclick=\"do_rate($i,$id,'$what'); return false\">☆</span>";
            --$i;
        }
        $rate .= '</div>';
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
