<?php

declare(strict_types = 1);

use Pu239\Database;
use Pu239\Image;

require_once __DIR__ . '/../include/bittorrent.php';
require_once INCL_DIR . 'function_pager.php';
require_once INCL_DIR . 'function_html.php';
$user = check_user_status();
$valid_search = [
    'sn',
    'sys',
    'sye',
    'srs',
    'sre',
];
global $container, $site_config;

$fluent = $container->get(Database::class);
$count = $fluent->from('torrents AS t')
                ->select(null)
                ->select('COUNT(t.id) AS count')
                ->where('t.category', $site_config['categories']['movie']);

$select = $fluent->from('torrents AS t')
                 ->select(null)
                 ->select('t.id')
                 ->select('t.name')
                 ->select('t.poster')
                 ->select('t.imdb_id')
                 ->select('t.seeders')
                 ->select('t.leechers')
                 ->select('t.year')
                 ->select('t.rating')
                 ->where('t.category', $site_config['categories']['movie'])
                 ->groupBy('t.imdb_id, t.id');
if ($user['hidden'] === 0) {
    $count->leftJoin('categories AS c ON t.category = c.id')
          ->where('c.hidden = 0');
    $select->leftJoin('categories AS c ON t.category = c.id')
           ->where('c.hidden = 0');
}

$addparam = [];
foreach ($valid_search as $search) {
    if (!empty($_GET[$search])) {
        $cleaned = searchfield($_GET[$search]);
        if ($search != 'srs' && $search != 'sre') {
            $addparam[] = "{$search}=" . urlencode($cleaned);
        }
    }
}
if (!empty($_GET['sn'])) {
    $count->where('MATCH (t.name) AGAINST (? IN NATURAL LANGUAGE MODE)', searchfield($_GET['sn']));
    $select->where('MATCH (t.name) AGAINST (? IN NATURAL LANGUAGE MODE)', searchfield($_GET['sn']));
}
if (!empty($_GET['sys'])) {
    $count->where('t.year >= ?', (int) $_GET['sys']);
    $select->where('t.year >= ?', (int) $_GET['sys'])
           ->orderBy('t.year DESC');
}
if (!empty($_GET['sye'])) {
    $count->where('t.year <= ?', (int) $_GET['sye']);
    $select->where('t.year <= ?', (int) $_GET['sye'])
           ->orderBy('t.year DESC');
}
if (!empty($_GET['srs'])) {
    $addparam[] = "{$search}=" . urlencode($_GET['srs']);
    $count->where('t.rating >= ?', (float) $_GET['srs']);
    $select->where('t.rating >= ?', (float) $_GET['srs'])
           ->orderBy('t.rating DESC');
}
if (!empty($_GET['sre'])) {
    $addparam[] = "{$search}=" . urlencode($_GET['sre']);
    $count->where('t.rating <= ?', (float) $_GET['sre']);
    $select->where('t.rating <= ?', (float) $_GET['sre'])
           ->orderBy('t.rating DESC');
}
$count = $count->fetch('count');
$perpage = 25;
$addparam = !empty($addparam) ? '?' . implode('&amp;', $addparam) . '&amp;' : '?';
$pager = pager($perpage, $count, "{$site_config['paths']['baseurl']}/tmovies.php{$addparam}");
$select->limit($pager['pdo']['limit'])
       ->offset($pager['pdo']['offset'])
       ->orderBy('t.added DESC');
$HTMLOUT = "
    <h1 class='has-text-centered top20'>" . _('Movies') . '</h1>';

$body = "
        <div class='masonry padding20'>";
$images_class = $container->get(Image::class);
foreach ($select as $torrent) {
    $cast = $cache->get('cast_' . $torrent['imdb_id']);
    if ($cast === false || is_null($cast)) {
        $cast = $fluent->from('person AS p')
                       ->select(null)
                       ->select('p.name')
                       ->innerJoin('imdb_person AS i ON p.imdb_id = i.person_id')
                       ->where('i.imdb_id = ?', str_replace('tt', '', $torrent['imdb_id']))
                       ->where('i.type = "cast"')
                       ->orderBy('p.name')
                       ->limit(7)
                       ->fetchAll();
        $cache->set('cast_' . $torrent['imdb_id'], $cast, 604800);
    }

    $casts[] = $cast;
    $people = [];
    foreach ($cast as $person) {
        $people[] = "<div class='size_2'><a href='{$site_config['paths']['baseurl']}/browse.php?sp=" . urlencode(htmlsafechars($person['name'])) . "'>" . format_comment($person['name']) . '</a></div>';
    }

    $name = "<a href='{$site_config['paths']['baseurl']}/browse.php?si={$torrent['imdb_id']}'>" . format_comment($torrent['name']) . '</a>';
    if (empty($torrent['poster'])) {
        if (!empty($torrent['imdb_id'])) {
            $image = $images_class->find_images($torrent['imdb_id'], 'poster');
        }
        if (!empty($image)) {
            $image = url_proxy($image, true);
        } else {
            $image = $site_config['paths']['images_baseurl'] . 'noposter.png';
        }
    } else {
        $image = url_proxy($torrent['poster'], true);
    }
    $percent = $torrent['rating'] * 10;
    $rating = "
                <a href='{$site_config['paths']['baseurl']}/browse.php?srs={$torrent['rating']}&amp;sre={$torrent['rating']}'>
                    <div>
                        <div class='level-left size_3'>
                            <div class='right5'>{$percent}%</div>
                            <div class='star-ratings-css'>
                                <div class='star-ratings-css-top' style='width: {$percent}%'><span>★</span><span>★</span><span>★</span><span>★</span><span>★</span></div>
                                <div class='star-ratings-css-bottom'><span>★</span><span>★</span><span>★</span><span>★</span><span>★</span></div>
                            </div>
                        </div>
                    </div>
                </a>";

    $seeders = "<a href='{$site_config['paths']['baseurl']}/peerlist.php?id={$torrent['seeders']}#seeders'>{$torrent['seeders']}</a>";
    $leechers = "<a href='{$site_config['paths']['baseurl']}/peerlist.php?id={$torrent['leechers']}#leechers'>{$torrent['leechers']}</a>";
    $year = "<a href='{$site_config['paths']['baseurl']}/browse.php?sys={$torrent['year']}&amp;sye={$torrent['year']}'>{$torrent['year']}</a>";
    $body .= "
                <div class='masonry-item padding10 bg-04 round10'>
                    <div class='columns'>
                        <div class='column'>
                            <img src='{$image}' alt='" . htmlsafechars($torrent['name']) . "'>
                        </div>
                        <div class='column'>
                            <div class='has-text-left size_4'>$name <span class='size_2'>({$year})</span></div>
                            $rating
                            <div class='size_2'>
                                <span class='has-text-primary'>" . _('Peers') . ":</span>
                                <span class='has-text-primary'> {$seeders} / {$leechers}</span>
                            </div>" . implode("\n", $people) . '
                        </div>
                    </div>
                </div>';
}
$body .= '
        </div>';

$HTMLOUT .= main_div("
            <form id='test1' method='get' action='{$site_config['paths']['baseurl']}/tmovies.php' enctype='multipart/form-data' accept-charset='utf-8'>
                <div class='padding20'>
                    <div class='padding10 w-100'>
                        <div class='columns'>
                            <div class='column'>
                                <div class='has-text-centered bottom10'>" . _('Name') . "</div>
                                <input id='search' name='sn' type='text' placeholder='" . _('Search by Name') . "' class='search w-100' value='" . (!empty($_GET['sn']) ? $_GET['sn'] : '') . "' onkeyup='autosearch()'>
                            </div>
                            <div class='column'>
                                <div class='columns'>
                                    <div class='column'>
                                        <div class='has-text-centered bottom10'>" . _('Year') . "</div>
                                        <input name='sys' type='number' min='1900' max='" . (date('Y') + 1) . "' placeholder='" . _('From Year Released') . "' class='search w-100' value='" . (!empty($_GET['sys']) ? $_GET['sys'] : '') . "'>
                                    </div>
                                    <div class='column'>
                                        <div class='has-text-centered bottom10'>" . _('Year') . "</div>
                                        <input name='sye' type='number' min='1900' max='" . (date('Y') + 1) . "' placeholder='" . _('To Year Released') . "' class='search w-100' value='" . (!empty($_GET['sye']) ? $_GET['sye'] : '') . "'>
                                    </div>
                                </div>
                            </div>
                            <div class='column'>
                                <div class='columns'>
                                    <div class='column'>
                                        <div class='has-text-centered bottom10'>" . _('Rating') . "</div>
                                        <input name='srs' type='number' min='0' max='10' step='0.1' placeholder='" . _('From IMDb Rating') . "' class='search w-100' value='" . (!empty($_GET['srs']) ? $_GET['srs'] : '') . "'>
                                    </div>
                                    <div class='column'>
                                        <div class='has-text-centered bottom10'>" . _('Rating') . "</div>
                                        <input name='sre' type='number' min='0' max='10' step='0.1' placeholder='" . _('To IMDb Rating') . "' class='search w-100' value='" . (!empty($_GET['sre']) ? $_GET['sre'] : '') . "'>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class='margin10 has-text-centered'>
                        <input type='submit' value='" . _('Search!') . "' class='button is-small'>
                    </div>
                </div>
            </form>");

$HTMLOUT .= "<div class='top20'>" . ($count > $perpage ? $pager['pagertop'] : '') . main_div($body, 'top20') . ($count > $perpage ? $pager['pagertop'] : '') . '</div>';

$title = _('Search Movies');
$breadcrumbs = [
    "<a href='{$site_config['paths']['baseurl']}/browse.php'>" . _('Browse Torrents') . '</a>',
    "<a href='{$_SERVER['PHP_SELF']}'>$title</a>",
];
echo stdhead($title, [], 'page-wrapper', $breadcrumbs) . wrapper($HTMLOUT) . stdfoot();
