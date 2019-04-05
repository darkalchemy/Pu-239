<?php

require_once __DIR__ . '/../include/bittorrent.php';
require_once INCL_DIR . 'function_users.php';
check_user_status();
global $site_config, $cache;

$HTMLOUT = '';
$lang = load_language('global');
$abba = '
        <h1 class="has-text-centered">Top Moods</h1>
        <div class="has-text-centered bottom20">You may select your mood by clicking on the smiley in the left side menu or clicking <a href="javascript:;" onclick="PopUp(\'usermood.php\',\'Mood\',530,500,1,1);"><span class="has-text-lime">here</span></a>.</div>
         <table class="table table-bordered table-striped">
         <tr><td class="colhead">Count</td>
         <td class="colhead">Mood</td>
         <td class="colhead">Icon</td>
         </tr>';
$key = 'topmoods';
$topmoods = $cache->get($key);
if ($topmoods === false || is_null($topmoods)) {
    $res = sql_query('SELECT moods.*, users.mood, COUNT(users.mood) as moodcount ' . 'FROM users LEFT JOIN moods ON (users.mood = moods.id) GROUP BY users.mood ' . 'ORDER BY moodcount DESC, moods.id ASC') or sqlerr(__FILE__, __LINE__);
    while ($arr = mysqli_fetch_assoc($res)) {
        $topmoods .= '<tr><td>' . (int) $arr['moodcount'] . '</td>
                 <td>' . htmlsafechars($arr['name']) . ' ' . ($arr['bonus'] == 1 ? '<a href="/mybonus.php">(bonus)</a>' : '') . '</td>
                 <td><img src="' . $site_config['paths']['images_baseurl'] . 'smilies/' . htmlsafechars($arr['image']) . '" alt=""></td>
                 </tr>';
    }
    $cache->set($key, $topmoods, 0);
}
$HTMLOUT .= $abba . $topmoods . '</table>';
echo stdhead('Top Moods') . $HTMLOUT . stdfoot();
