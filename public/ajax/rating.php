<?php

require_once dirname(__FILE__, 3).DIRECTORY_SEPARATOR.'include'.DIRECTORY_SEPARATOR.'bittorrent.php';
require_once INCL_DIR.'user_functions.php';
check_user_status();
global $CURUSER, $site_config, $cache;

$lang = load_language('global');
if (empty($_POST)) {
    return null;
}

$id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
$rate = isset($_POST['rate']) ? (int) $_POST['rate'] : 0;
$uid = $CURUSER['id'];
$ajax = isset($_POST['ajax']) && 1 == $_POST['ajax'] ? true : false;
$what = isset($_POST['what']) && 'torrent' == $_POST['what'] ? 'torrent' : 'topic';
$ref = isset($_POST['ref']) ? $_POST['ref'] : ('torrent' == $what ? 'details.php' : 'forums/view_topic.php');
$completeres = sql_query('SELECT * FROM '.(XBT_TRACKER ? 'xbt_files_users' : 'snatched').' WHERE '.(XBT_TRACKER ? 'completedtime !=0' : 'complete_date !=0').' AND '.(XBT_TRACKER ? 'uid' : 'userid').' = '.$CURUSER['id'].' AND '.(XBT_TRACKER ? 'fid' : 'torrentid').' = '.$id) or sqlerr(__FILE__, __LINE__);
$completecount = mysqli_num_rows($completeres);
if ('torrent' == $what && 0 == $completecount) {
    return false;
}
if ($id > 0 && $rate >= 1 && $rate <= 5) {
    if (sql_query('INSERT INTO rating('.$what.',rating,user) VALUES ('.sqlesc($id).','.sqlesc($rate).','.sqlesc($uid).')')) {
        $table = ('torrent' == $what ? 'torrents' : 'topics');
        sql_query('UPDATE '.$table.' SET num_ratings = num_ratings + 1, rating_sum = rating_sum+'.sqlesc($rate).' WHERE id = '.sqlesc($id));
        $cache->delete('rating_'.$what.'_'.$id.'_'.$CURUSER['id']);
        if ('torrent' == $what) {
            $f_r = sql_query('SELECT num_ratings, rating_sum FROM torrents WHERE id = '.sqlesc($id)) or sqlerr(__FILE__, __LINE__);
            $r_f = mysqli_fetch_assoc($f_r);
            $update['num_ratings'] = ($r_f['num_ratings'] + 1);
            $update['rating_sum'] = ($r_f['rating_sum'] + $rate);
            $cache->update_row('torrent_details_'.$id, [
                'num_ratings' => $update['num_ratings'],
                'rating_sum' => $update['rating_sum'],
            ], $site_config['expires']['torrent_details']);
        }
        if (1 == $site_config['seedbonus_on']) {
            $amount = ('torrent' == $what ? $site_config['bonus_per_rating'] : $site_config['bonus_per_topic']);
            sql_query("UPDATE users SET seedbonus = seedbonus+$amount WHERE id = ".sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);
            $update['seedbonus'] = ($CURUSER['seedbonus'] + $amount);
            $cache->update_row('user'.$CURUSER['id'], [
                'seedbonus' => $update['seedbonus'],
            ], $site_config['expires']['user_cache']);
        }
        $keys['rating'] = 'rating_'.$what.'_'.$id.'_'.$CURUSER['id'];
        $qy1 = $fluent->from('rating')
            ->select(null)
            ->select('SUM(rating) AS sum')
            ->select('COUNT(*) AS count')
            ->where("$what = ?", $id)
            ->fetchAll();
        $qy2 = $fluent->from('rating')
            ->select(null)
            ->select('id AS rated')
            ->select('rating')
            ->where("$what = ?", $id)
            ->where('user = ?', $CURUSER['id'])
            ->fetchAll();

        $rating_cache = array_merge($qy1[0], $qy2[0]);
        $ratings = $cache->get('ratings_'.$id);
        if (!empty($ratings)) {
            foreach ($ratings as $rater) {
                $cache->delete('rating_'.$what.'_'.$id.'_'.$rater);
            }
            $cache->delete('ratings_'.$id);
        }
        $cache->set($keys['rating'], $rating_cache, 0);

        $rated = number_format($rating_cache['sum'] / $rating_cache['count'] / 5 * 100, 0).'%';
        echo "
                <div class='star-ratings-css-top tooltipper' title='Rating: $rated. You rated this $what {$rating_cache['rating']} star".plural($rating_cache['rating'])."' style='width: $rated;'>
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
                </div>";
    } else {
        return null;
    }
}
