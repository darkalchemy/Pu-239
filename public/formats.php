<?php

declare(strict_types = 1);

require_once __DIR__ . '/../include/bittorrent.php';
require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_html.php';
require_once INCL_DIR . 'function_bbcode.php';
check_user_status();
$lang = array_merge(load_language('global'), load_language('formats'));

$HTMLOUT = "
<h1 class='has-text-centered'>{$lang['formats_download_title']}</h1>";

$HTMLOUT .= main_div("
<h2>{$lang['formats_guide_heading']}</h2>
{$lang['formats_guide_body']}", 'top20', 'padding20');

$HTMLOUT .= main_div("
<h2>{$lang['formats_compression_title']}</h2>
{$lang['formats_compression_body']}", 'top20', 'padding20');

$HTMLOUT .= main_div("
<h2>{$lang['formats_multimedia_title']}</h2>
{$lang['formats_multimedia_body']}", 'top20', 'padding20');

$HTMLOUT .= main_div("
<h2>{$lang['formats_image_title']}</h2>
{$lang['formats_image_body']}", 'top20', 'padding20');

$HTMLOUT .= main_div("
<h2>{$lang['formats_other_title']}</h2>
{$lang['formats_other_body']}", 'top20', 'padding20');

$HTMLOUT .= main_div("
{$lang['formats_questions']}", 'top20', 'padding20');

echo stdhead($lang['formats_download_title']) . wrapper($HTMLOUT) . stdfoot();
