<?php

require_once __DIR__ . '/../include/bittorrent.php';
require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_pager.php';
require_once INCL_DIR . 'function_torrenttable.php';
require_once INCL_DIR . 'function_html.php';
check_user_status();
global $CURUSER, $site_config, $fluent;

$lang = array_merge(load_language('global'), load_language('mytorrents'), load_language('torrenttable_functions'));
$HTMLOUT = '';
$count = $fluent->from('torrents AS t')
    ->select(null)
    ->select('COUNT(*) AS count');

$select = $fluent->from('torrents AS t')
    ->select("IF(t.num_ratings < {$site_config['minvotes']}, NULL, ROUND(t.rating_sum / t.num_ratings, 1)) AS rating")
    ->select('IF(s.to_go IS NOT NULL, (t.size - s.to_go) / t.size, -1) AS to_go')
    ->select('u.class')
    ->select('u.username')
    ->leftJoin('snatched AS s on s.torrentid = t.id AND s.userid = ?', $CURUSER['id'])
    ->leftJoin('users AS u ON t.owner = u.id');

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
    $select = $select->orderBy("t.{$column} $ascdecs");
    $pagerlink = 'sort=' . intval($_GET['sort']) . '&amp;type=' . $linkascdesc . '&amp;';
} else {
    $select = $select->orderBy('t.staff_picks DESC')->orderBy('t.sticky')->orderBy('t.added DESC');
    $pagerlink = '';
}
$count = $count->where('owner = ?', $CURUSER['id'])
    ->where('banned != "yes"')
    ->fetch('count');

$select = $select->where('owner = ?', $CURUSER['id'])
    ->where('banned != "yes"');

if (!$count) {
    $HTMLOUT .= "
        <h1 class='has-text-centered'>{$lang['mytorrents_no_torrents']}</h1>" . main_div("
        <div class='has-text-centered'>{$lang['mytorrents_no_uploads']}</div>", null, 'padding20');
} else {
    $pager = pager(20, $count, "{$site_config['baseurl']}/mytorrents.php?{$pagerlink}");
    $select = $select->limit($pager['pdo'])->fetchAll();
    $HTMLOUT .= $pager['pagertop'];
    $HTMLOUT .= torrenttable($select, 'mytorrents');
    $HTMLOUT .= $pager['pagerbottom'];
}
echo stdhead($CURUSER['username'] . "'s torrents") . wrapper($HTMLOUT) . stdfoot();
