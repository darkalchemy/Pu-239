<?php

declare(strict_types = 1);

use Pu239\Database;

require_once __DIR__ . '/../include/bittorrent.php';
require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_html.php';
require_once INCL_DIR . 'function_categories.php';
$user = check_user_status();
$HTMLOUT = '';
global $container, $site_config;

$possible_actions = [
    'leechers',
    'seeders',
];

$fluent = $container->get(Database::class);
$needed = isset($_GET['needed']) && !is_array($_GET['needed']) ? htmlsafechars($_GET['needed']) : 'seeders';
if (!in_array($needed, $possible_actions)) {
    stderr(_('Error'), _('Invalid action'));
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
                    <a href='#' class='active is-link'>" . _('Seeders in need') . "</a>
                </li>
                <li>
                    <a href='{$site_config['paths']['baseurl']}/needseed.php?needed=seeders' class='is-link'>" . _('Torrents Needing Seeds') . '</a>
                </li>
            </ul>
        </div>';

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
        $header = '
                <tr>
                    <th>' . _('User') . '</th>
                    <th>' . _('Torrent') . '</th>
                    <th>' . _('Category') . '</th>
                    <th>' . _('Peers') . '</th>
                </tr>';
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
                    <td>' . format_username((int) $arr['id']) . ' (' . member_ratio((float) $arr['uploaded'], (float) $arr['downloaded']) . ")</td>
                    <td><a href='{$site_config['paths']['baseurl']}/details.php?id=" . (int) $What_ID . "' title='{$torrname}' class='tooltipper'>{$torrname}</a></td>
                    <td>{$cat}</td>
                    <td>{$peers}</td>
                </tr>";
        }
        $HTMLOUT .= main_table($body, $header);
    } else {
        $HTMLOUT .= main_div("<div class='padding20'>" . _('There are no torrents needing leechers right now.') . '</div>');
    }

    $title = _('Leechers in Need');
    $breadcrumbs = [
        "<a href='{$site_config['paths']['baseurl']}/browse.php'>" . _('Browse Torrents') . '</a>',
        "<a href='{$_SERVER['PHP_SELF']}'>$title</a>",
    ];
    echo stdhead($title, [], 'page-wrapper', $breadcrumbs) . wrapper($HTMLOUT) . stdfoot();
} else {
    $HTMLOUT .= "
        <div class='padding20'>
            <ul class='tabs'>
                <li>
                    <a href='{$site_config['paths']['baseurl']}/needseed.php?needed=leechers'  class='is-link'>" . _('Seeders in need') . "</a>
                </li>
                <li>
                    <a href='#' class='active is-link'>" . _('Torrents Needing Seeds') . '</a>
                </li>
            </ul>
        </div>';
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
                    <th class='has-text-centered'>" . _('Category') . '</th>
                    <th>' . _('Name') . "</th>
                    <th class='has-text-centered'>" . _('Seeders') . "</th>
                    <th class='has-text-centered'>" . _('Leechers') . '</th>
                </tr>';
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
        $HTMLOUT .= main_div("<div class='padding20'>" . _('There are no torrents needing seeds right now.') . '</div>');
    }
    $title = _('Seeders in Need');
    $breadcrumbs = [
        "<a href='{$site_config['paths']['baseurl']}/browse.php'>" . _('Browse Torrents') . '</a>',
        "<a href='{$_SERVER['PHP_SELF']}'>$title</a>",
    ];
    echo stdhead($title, [], 'page-wrapper', $breadcrumbs) . wrapper($HTMLOUT) . stdfoot();
}
