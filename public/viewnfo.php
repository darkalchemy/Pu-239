<?php

declare(strict_types = 1);

use Pu239\Torrent;

require_once __DIR__ . '/../include/bittorrent.php';
require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_html.php';
require_once INCL_DIR . 'function_bbcode.php';
check_user_status();
$lang = array_merge(load_language('global'), load_language('viewnfo'));

global $container, $CURUSER, $site_config;

$id = (int) $_GET['id'];
if ($CURUSER['class'] === UC_MIN) {
    stderr('error', 'Need to rank up');
} elseif (!is_valid_id($id)) {
    stderr('error', 'Invalid ID');
}
$torrent = $container->get(Torrent::class);
$nfo = $torrent->get_items([
    'name',
    'nfo',
], $id);
if (empty($nfo) || empty($nfo['nfo'])) {
    die($lang['text_puke']);
}

$HTMLOUT = "
        <h1 class='has-text-centered'>{$lang['text_nfofor']}<a href='{$site_config['paths']['baseurl']}/details.php?id=$id'>" . htmlsafechars($nfo['name']) . "</a></h1>
        <div class='size_5 has-text-centered bottom10'>{$lang['text_forbest']}<a href='" . url_proxy('https://www.fontpalace.com/font-download/MS+LineDraw/') . "' target='_blank'>{$lang['text_linedraw']}</a>{$lang['text_font']}</div>";

$HTMLOUT .= main_div("<pre class='pre round10 noselect has-text-white bg-dark h-100'>" . format_urls(strip_tags($nfo['nfo'])) . '</pre>');

echo stdhead($lang['text_stdhead']) . wrapper($HTMLOUT) . stdfoot();
