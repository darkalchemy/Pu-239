<?php

declare(strict_types = 1);

use Pu239\Database;

require_once __DIR__ . '/../include/bittorrent.php';
require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_html.php';
require_once INCL_DIR . 'function_categories.php';
$user = check_user_status();
$HTMLOUT = '';
$lang = array_merge(load_language('global'), load_language('needseed'));
global $container, $site_config;

$possible_actions = [
    'leechers',
    'seeders',
];

$fluent = $container->get(Database::class);
$needed = isset($_GET['needed']) && !is_array($_GET['needed']) ? htmlsafechars($_GET['needed']) : 'seeders';
if (!in_array($needed, $possible_actions)) {
    stderr('Error', 'A ruffian that will swear, drink, dance, revel the night, rob, murder and commit the oldest of ins the newest kind of ways.');
}
$categorie = genrelist(false);
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
                    <a href='#' class='active is-link'>{$lang['needseed_sin']}</a>
                </li>
                <li>
                    <a href='{$site_config['paths']['baseurl']}/needseed.php?needed=seeders' class='is-link'>{$lang['needseed_tns']}</a>
                </li>
            </ul>
        </div>";

    $Dur = TIME_NOW - (86400 * 7);
    $res = $fluent->from('peers AS p')
                  ->select('p.id')
                  ->select('p.userid')
                  ->select('p.torrent')
                  ->select('u.username')
                  ->select('u.uploaded')
                  ->select('u.downloaded')
                  ->select('t.name')
                  ->select('t.seeders')
                  ->select('t.leechers')
                  ->select('t.category')
                  ->leftJoin('users AS u ON p.userid = u.id')
                  ->leftJoin('torrents AS t ON p.torrent = t.id')
                  ->leftJoin('categories AS c ON t.category = c.id')
                  ->where("p.seeder = 'yes'")
                  ->where('u.downloaded > 1024')
                  ->where('u.registered < ?', $Dur)
                  ->orderBy('u.uploaded / u.downloaded');
    if ($user['hidden'] === 0) {
        $res->where('c.hidden = 0');
    }
    $res = $res->fetchAll();
    if (!empty($res)) {
        $header = "
                <tr>
                    <th>{$lang['needseed_user']}</th>
                    <th>{$lang['needseed_tor']}</th>
                    <th>{$lang['needseed_cat']}</th>
                    <th>{$lang['needseed_peer']}</th>
                </tr>";
        $body = '';
        foreach ($res as $arr) {
            $What_ID = $arr['torrent'];
            $What_User_ID = $arr['userid'];
            $needseed['cat_name'] = htmlsafechars($change[$arr['category']]['name']);
            $needseed['cat_pic'] = htmlsafechars($change[$arr['category']]['image']);
            if (!empty($needseed['cat_pic'])) {
                $cat = "<img src='{$site_config['paths']['images_baseurl']}caticons/" . get_category_icons() . "/{$needseed['cat_pic']}' alt='{$needseed['cat_name']}' title='{$needseed['cat_name']}' class='tooltipper'>";
            } else {
                $cat = $needseed['cat_name'];
            }
            $torrname = format_comment(CutName($arr['name'], 80));
            $peers = (int) $arr['seeders'] . ' seeder' . ((int) $arr['seeders'] > 1 ? 's' : '') . ', ' . (int) $arr['leechers'] . ' leecher' . ((int) $arr['leechers'] > 1 ? 's' : '');
            $body .= '
                <tr>
                    <td>' . format_username((int) $arr['id']) . ' (' . member_ratio($arr['uploaded'], $arr['downloaded']) . ")</td>
                    <td><a href='{$site_config['paths']['baseurl']}/details.php?id=" . (int) $What_ID . "' title='{$torrname}' class='tooltipper'>{$torrname}</a></td>
                    <td>{$cat}</td>
                    <td>{$peers}</td>
                </tr>";
        }
        $HTMLOUT .= main_table($body, $header);
    } else {
        $HTMLOUT .= main_div("<div class='padding20'>{$lang['needseed_noleech']}</div>");
    }
    echo stdhead($lang['needseed_lin']) . wrapper($HTMLOUT) . stdfoot();
} else {
    $HTMLOUT .= "
        <div class='padding20'>
            <ul class='tabs'>
                <li>
                    <a href='{$site_config['paths']['baseurl']}/needseed.php?needed=leechers'  class='is-link'>{$lang['needseed_sin']}</a>
                </li>
                <li>
                    <a href='#' class='active is-link'>{$lang['needseed_tns']}</a>
                </li>
            </ul>
        </div>";
    $res = $fluent->from('torrents AS t')
                  ->select(null)
                  ->select('t.id')
                  ->select('t.name')
                  ->select('t.seeders')
                  ->select('t.leechers')
                  ->select('t.added')
                  ->select('t.category')
                  ->where('t.leechers >= 0')
                  ->where('t.seeders = 0')
                  ->orderBy('t.leechers DESC')
                  ->limit(20);
    if ($user['hidden'] === 0) {
        $res->leftJoin('categories AS c ON t.category = c.id')
            ->where('c.hidden = 0');
    }
    $res = $res->fetchAll();
    if (!empty($res)) {
        $header = "
                <tr>
                    <th class='has-text-centered'>{$lang['needseed_cat']}</th>
                    <th>{$lang['needseed_tor']}</th>
                    <th class='has-text-centered'>{$lang['needseed_seed']}</th>
                    <th class='has-text-centered'>{$lang['needseed_leech']}</th>
                </tr>";
        $body = '';
        foreach ($res as $arr) {
            $needseed['cat_name'] = htmlsafechars($change[$arr['category']]['name']);
            $needseed['cat_pic'] = htmlsafechars($change[$arr['category']]['image']);
            if (!empty($needseed['cat_pic'])) {
                $cat = "<img src='{$site_config['paths']['images_baseurl']}caticons/" . get_category_icons() . "/{$needseed['cat_pic']}' alt='{$needseed['cat_name']}' title='{$needseed['cat_name']}' class='tooltipper'>";
            } else {
                $cat = $needseed['cat_name'];
            }
            $torrname = format_comment(CutName($arr['name'], 80));
            $body .= "
                <tr>
                    <td class='has-text-centered'>{$cat}</td>
                    <td><a href='{$site_config['paths']['baseurl']}/details.php?id=" . (int) $arr['id'] . "&amp;hit=1' title='{$torrname}' class='tooltipper'>{$torrname}</a></td>
                    <td class='has-text-centered'><span>" . (int) $arr['seeders'] . "</span></td>
                    <td class='has-text-centered'>" . (int) $arr['leechers'] . '</td>
                </tr>';
        }
        $HTMLOUT .= main_table($body, $header);
    } else {
        $HTMLOUT .= main_div("<div class='padding20'>{$lang['needseed_noseed']}</div>");
    }
    echo stdhead($lang['needseed_sin']) . wrapper($HTMLOUT) . stdfoot();
}
