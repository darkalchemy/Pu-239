<?php
require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'user_functions.php';
check_user_status();
global $CURUSER, $site_config, $cache;

$lang = load_language('global');
if (empty($_GET)) {
    setSessionVar('is-danger', 'Access Not Allowed');
    header("Location: {$site_config['baseurl']}/index.php");
    die();
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$rate = isset($_GET['rate']) ? (int)$_GET['rate'] : 0;
$uid = $CURUSER['id'];
$ajax = isset($_GET['ajax']) && $_GET['ajax'] == 1 ? true : false;
$what = isset($_GET['what']) && $_GET['what'] == 'torrent' ? 'torrent' : 'topic';
$ref = isset($_GET['ref']) ? $_GET['ref'] : ($what == 'torrent' ? 'details.php' : 'forums/view_topic.php');
$completeres = sql_query('SELECT * FROM ' . (XBT_TRACKER ? 'xbt_files_users' : 'snatched') . ' WHERE ' . (XBT_TRACKER ? 'completedtime !=0' : 'complete_date !=0') . ' AND ' . (XBT_TRACKER ? 'uid' : 'userid') . ' = ' . $CURUSER['id'] . ' AND ' . (XBT_TRACKER ? 'fid' : 'torrentid') . ' = ' . $id) or sqlerr(__FILE__, __LINE__);
$completecount = mysqli_num_rows($completeres);
if ($what == 'torrent' && $completecount == 0) {
    setSessionVar('is-warning', 'You must have downloaded this torrent in order to rate it.');
}
if ($id > 0 && $rate >= 1 && $rate <= 5) {
    if (sql_query('INSERT INTO rating(' . $what . ',rating,user) VALUES (' . sqlesc($id) . ',' . sqlesc($rate) . ',' . sqlesc($uid) . ')')) {
        $table = ($what == 'torrent' ? 'torrents' : 'topics');
        sql_query('UPDATE ' . $table . ' SET num_ratings = num_ratings + 1, rating_sum = rating_sum+' . sqlesc($rate) . ' WHERE id = ' . sqlesc($id));
        $cache->delete('rating_' . $what . '_' . $id . '_' . $CURUSER['id']);
        if ($what == 'torrent') {
            $f_r = sql_query('SELECT num_ratings, rating_sum FROM torrents WHERE id = ' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
            $r_f = mysqli_fetch_assoc($f_r);
            $update['num_ratings'] = ($r_f['num_ratings'] + 1);
            $update['rating_sum'] = ($r_f['rating_sum'] + $rate);
            $cache->update_row('torrent_details_' . $id, [
                'num_ratings' => $update['num_ratings'],
                'rating_sum'  => $update['rating_sum'],
            ], $site_config['expires']['torrent_details']);
        }
        if ($site_config['seedbonus_on'] == 1) {
            //===add karma
            $amount = ($what == 'torrent' ? $site_config['bonus_per_rating'] : $site_config['bonus_per_topic']);
            sql_query("UPDATE users SET seedbonus = seedbonus+$amount WHERE id = " . sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);
            $update['seedbonus'] = ($CURUSER['seedbonus'] + $amount);
            $cache->update_row('userstats_' . $CURUSER['id'], [
                'seedbonus' => $update['seedbonus'],
            ], $site_config['expires']['u_stats']);
            $cache->update_row('user_stats_' . $CURUSER['id'], [
                'seedbonus' => $update['seedbonus'],
            ], $site_config['expires']['user_stats']);
            //===end
        }
        if ($ajax) {
            $qy = sql_query('SELECT sum(r.rating) AS sum, count(r.rating) AS count, r2.rating AS rate FROM rating AS r LEFT JOIN rating AS r2 ON (r2.' . $what . ' = ' . sqlesc($id) . ' AND r2.user = ' . sqlesc($uid) . ') WHERE r.' . $what . ' = ' . sqlesc($id) . ' GROUP BY r.' . sqlesc($what)) or sqlerr(__FILE__, __LINE__);
            $a = mysqli_fetch_assoc($qy);
            echo '<ul class="star-rating tooltipper" title="Your rated this ' . $what . ' ' . htmlsafechars($a['rate']) . ' star' . (htmlsafechars($a['rate']) > 1 ? 's' : '') . '"  ><li class="current-rating" />.</ul>';
        } else {
            header('Refresh: 2; url=' . $ref);
            setSessionVar('is-success', 'Your rating has been added!');
        }
    } else {
        if (((is_object($GLOBALS['___mysqli_ston'])) ? mysqli_errno($GLOBALS['___mysqli_ston']) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)) == 1062 && $ajax) {
            echo 'You already rated this ' . $what . '';
        } elseif (((is_object($GLOBALS['___mysqli_ston'])) ? mysqli_error($GLOBALS['___mysqli_ston']) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)) && $ajax) {
            echo "You can't rate twice, Err - " . ((is_object($GLOBALS['___mysqli_ston'])) ? mysqli_error($GLOBALS['___mysqli_ston']) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false));
        } else {
            setSessionVar('is-warning', "You can't rate twice, Err - " . ((is_object($GLOBALS['___mysqli_ston'])) ? mysqli_error($GLOBALS['___mysqli_ston']) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
        }
    }
}
