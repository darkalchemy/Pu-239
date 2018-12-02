<?php

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'user_functions.php';
require_once INCL_DIR . 'html_functions.php';
check_user_status();
global $site_config, $session;

$HTMLOUT = '';
$lang = array_merge(load_language('global'), load_language('needseed'));
$possible_actions = [
    'leechers',
    'seeders',
];
$needed = (isset($_GET['needed']) ? htmlsafechars($_GET['needed']) : 'seeders');
if (!in_array($needed, $possible_actions)) {
    stderr('Error', 'A ruffian that will swear, drink, dance, revel the night, rob, murder and commit the oldest of ins the newest kind of ways.');
}
$categorie = genrelist();
$change = [];
foreach ($categorie as $key => $value) {
    $change[$value['id']] = [
        'id' => $value['id'],
        'name' => $value['name'],
        'image' => $value['image'],
    ];
}
if ($needed === 'leechers') {
    $HTMLOUT .= "
        <div class='padding20'>
            <ul class='tabs'>
                <li>
                    <a href='#' class='active altlink'>{$lang['needseed_sin']}</a>
                </li>
                <li>
                    <a href='{$site_config['baseurl']}/needseed.php?needed=seeders' class='altlink'>{$lang['needseed_tns']}</a>
                </li>
            </ul>
        </div>";

    $Dur = TIME_NOW - 86400 * 7; //== 7 days
    $res = sql_query("
        SELECT p.id, p.userid, p.torrent, u.username, u.uploaded, u.downloaded, t.name, t.seeders, t.leechers, t.category
        FROM peers AS p
        LEFT JOIN users AS u ON u.id = p.userid
        LEFT JOIN torrents AS t ON t.id = p.torrent
        WHERE p.seeder = 'yes' AND u.downloaded > '1024' AND u.added < $Dur
        ORDER BY u.uploaded / u.downloaded ASC
        LIMIT 20") or sqlerr(__FILE__, __LINE__);
    if (mysqli_num_rows($res) > 0) {
        $header = "
                <tr>
                    <th>{$lang['needseed_user']}</th>
                    <th>{$lang['needseed_tor']}</th>
                    <th>{$lang['needseed_cat']}</th>
                    <th>{$lang['needseed_peer']}</th>
                </tr>";
        $body = '';
        while ($arr = mysqli_fetch_assoc($res)) {
            $What_ID = (XBT_TRACKER === true ? $arr['fid'] : $arr['torrent']);
            $What_User_ID = (XBT_TRACKER === true ? $arr['uid'] : $arr['userid']);
            $needseed['cat_name'] = htmlsafechars($change[$arr['category']]['name']);
            $needseed['cat_pic'] = htmlsafechars($change[$arr['category']]['image']);
            $cat = "<img src='{$site_config['pic_baseurl']}caticons/" . get_category_icons() . "/{$needseed['cat_pic']}' alt='{$needseed['cat_name']}' title='{$needseed['cat_name']}' class='tooltipper'>";
            $torrname = htmlsafechars(CutName($arr['name'], 80));
            $peers = (int) $arr['seeders'] . ' seeder' . ((int) $arr['seeders'] > 1 ? 's' : '') . ', ' . (int) $arr['leechers'] . ' leecher' . ((int) $arr['leechers'] > 1 ? 's' : '');
            $body .= '
                <tr>
                    <td>' . format_username($arr['id']) . ' (' . member_ratio($arr['uploaded'], $arr['downloaded']) . ")</td>
                    <td><a href='{$site_config['baseurl']}/details.php?id=" . (int) $What_ID . "' title='{$torrname}' class='tooltipper'>{$torrname}</a></td>
                    <td>{$cat}</td>
                    <td>{$peers}</td>
                </tr>";
        }
        $HTMLOUT .= main_table($body, $header);
    } else {
        $HTMLOUT .= main_div("{$lang['needseed_noleech']}");
    }
    echo stdhead("{$lang['needseed_lin']}") . wrapper($HTMLOUT) . stdfoot();
} else {
    $HTMLOUT .= "
        <div class='padding20'>
            <ul class='tabs'>
                <li>
                    <a href='{$site_config['baseurl']}/needseed.php?needed=leechers'  class='altlink'>{$lang['needseed_sin']}</a>
                </li>
                <li>
                    <a href='#' class='active altlink'>{$lang['needseed_tns']}</a>
                </li>
            </ul>
        </div>";
    $res = sql_query('SELECT id, name, seeders, leechers, added, category FROM torrents WHERE leechers >= 0 AND seeders = 0 ORDER BY leechers DESC LIMIT 20') or sqlerr(__FILE__, __LINE__);
    if (mysqli_num_rows($res) > 0) {
        $header = "
                <tr>
                    <th class='has-text-centered'>{$lang['needseed_cat']}</th>
                    <th>{$lang['needseed_tor']}</th>
                    <th class='has-text-centered'>{$lang['needseed_seed']}</th>
                    <th class='has-text-centered'>{$lang['needseed_leech']}</th>
                </tr>";
        $body = '';
        while ($arr = mysqli_fetch_assoc($res)) {
            $needseed['cat_name'] = htmlsafechars($change[$arr['category']]['name']);
            $needseed['cat_pic'] = htmlsafechars($change[$arr['category']]['image']);
            $cat = "<img src='{$site_config['pic_baseurl']}caticons/" . get_category_icons() . "/{$needseed['cat_pic']}' alt='{$needseed['cat_name']}' title='{$needseed['cat_name']}' class='tooltipper'>";
            $torrname = htmlsafechars(CutName($arr['name'], 80));
            $body .= "
                <tr>
                    <td class='has-text-centered'>{$cat}</td>
                    <td><a href='{$site_config['baseurl']}/details.php?id=" . (int) $arr['id'] . "&amp;hit=1' title='{$torrname}' class='tooltipper'>{$torrname}</a></td>
                    <td class='has-text-centered'><span>" . (int) $arr['seeders'] . "</span></td>
                    <td class='has-text-centered'>" . (int) $arr['leechers'] . '</td>
                </tr>';
        }
        $HTMLOUT .= main_table($body, $header);
    } else {
        $HTMLOUT .= main_div("{$lang['needseed_noseed']}");
    }
    echo stdhead("{$lang['needseed_sin']}") . wrapper($HTMLOUT) . stdfoot();
}
