<?php

require_once __DIR__ . '/../include/bittorrent.php';
require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_pager.php';
require_once INCL_DIR . 'function_torrenttable.php';
require_once INCL_DIR . 'function_html.php';
check_user_status();
global $CURUSER, $site_config;

$lang = array_merge(load_language('global'), load_language('mytorrents'), load_language('torrenttable_functions'));
$HTMLOUT = '';
if (isset($_GET['sort'], $_GET['type'])) {
    $column = '';
    $ascdesc = '';
    $_valid_sort = [
        'id',
        'name',
        'numfiles',
        'comments',
        'added',
        'size',
        'times_completed',
        'seeders',
        'leechers',
        'owner',
    ];
    $column = isset($_GET['sort'], $_valid_sort[(int) $_GET['sort']]) ? $_valid_sort[(int) $_GET['sort']] : $_valid_sort[0];
    switch (htmlsafechars($_GET['type'])) {
        case 'asc':
            $ascdesc = 'ASC';
            $linkascdesc = 'asc';
            break;

        case 'desc':
            $ascdesc = 'DESC';
            $linkascdesc = 'desc';
            break;

        default:
            $ascdesc = 'DESC';
            $linkascdesc = 'desc';
            break;
    }
    $orderby = 'ORDER BY torrents.' . $column . ' ' . $ascdesc;
    $pagerlink = 'sort=' . intval($_GET['sort']) . '&amp;type=' . $linkascdesc . '&amp;';
} else {
    $orderby = 'ORDER BY torrents.sticky ASC, torrents.id DESC';
    $pagerlink = '';
}
$where = 'WHERE owner = ' . sqlesc($CURUSER['id']) . " AND banned != 'yes'";
$res = sql_query("SELECT COUNT(id) FROM torrents $where") or sqlerr(__FILE__, __LINE__);
$row = mysqli_fetch_array($res, MYSQLI_NUM);
$count = $row[0];
if (!$count) {
    $HTMLOUT .= "{$lang['mytorrents_no_torrents']}";
    $HTMLOUT .= "{$lang['mytorrents_no_uploads']}";
} else {
    $pager = pager(20, $count, "mytorrents.php?{$pagerlink}");
    $res = sql_query("SELECT staff_picks, sticky, vip, descr, nuked, bump, nukereason, release_group, free, silver, comments, leechers, seeders, owner, IF(num_ratings < {$site_config['minvotes']}, NULL, ROUND(rating_sum / num_ratings, 1)) AS rating, id, name, save_as, numfiles, added, size, views, visible, hits, times_completed, category, description FROM torrents $where $orderby " . $pager['limit']) or sqlerr(__FILE__, __LINE__);
    $HTMLOUT .= $pager['pagertop'];
    $HTMLOUT .= '<br>';
    $HTMLOUT .= torrenttable($res, 'mytorrents');
    $HTMLOUT .= $pager['pagerbottom'];
}
echo stdhead($CURUSER['username'] . "'s torrents") . $HTMLOUT . stdfoot();
