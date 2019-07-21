<?php

declare(strict_types = 1);

use Pu239\Nfo2Png;
use Pu239\Torrent;

require_once __DIR__ . '/../include/bittorrent.php';
require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_html.php';
require_once INCL_DIR . 'function_bbcode.php';
$user = check_user_status();
$lang = array_merge(load_language('global'), load_language('viewnfo'));

global $container, $site_config;

$id = (int) $_GET['id'];
if ($user['class'] === UC_MIN) {
    stderr('error', 'Need to rank up');
} elseif (!is_valid_id($id)) {
    stderr('error', 'Invalid ID');
}
$torrent = $container->get(Torrent::class);
$nfo = $torrent->get_items([
    'name',
    'nfo',
    'id',
], $id);
if (empty($nfo) || empty($nfo['nfo'])) {
    die($lang['text_puke']);
}

$HTMLOUT = "
        <h1 class='has-text-centered'>{$lang['text_nfofor']}<a href='{$site_config['paths']['baseurl']}/details.php?id=$id'>" . htmlsafechars($nfo['name']) . '</a></h1>';

if ($site_config['nfo']['as_image']) {
    $nfo2png = $container->get(Nfo2Png::class);
    $image = $nfo2png->nfo2png_ttf($nfo['nfo'], $nfo['id'], '000', '0f0');
    if (!empty($image)) {
        $HTMLOUT .= main_div("
        <div class='has-text-centered w-50 padding20'>
            <img src='{$site_config['paths']['nfos_baseurl']}$image' alt='{$nfo['name']}' class=' round10 w-100'>
        </div>");
    }
}
if (empty($image)) {
    $div = "
        <div class='size_5 has-text-centered w-50 padding20'>
            <div class='bottom20'>
                {$lang['text_forbest']}<a href='" . url_proxy('https://www.fontpalace.com/font-download/MS+LineDraw/') . "' target='_blank'>{$lang['text_linedraw']}</a>{$lang['text_font']}
            </div>
            <pre class='pre round10 noselect has-text-white has-text-left bg-dark h-100 w-100 has-text-green'>" . format_urls(strip_tags($nfo['nfo'])) . '</pre>
        </div';

    $HTMLOUT .= main_div($div);
}
echo stdhead($lang['text_stdhead']) . wrapper($HTMLOUT) . stdfoot();
