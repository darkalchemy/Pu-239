<?php

declare(strict_types = 1);

require_once __DIR__ . '/include/bittorrent.php';
require_once INCL_DIR . 'function_users.php';
check_user_status();
$lang = array_merge(load_language('global'), load_language('links'));
/**
 * @param        $url
 * @param        $title
 * @param string $description
 *
 * @return string
 */
function add_link($url, $title, $description = '')
{
    $text = "<a class='is-link' href=$url>$title</a>";
    if ($description) {
        $text = "$text - $description";
    }

    return "<li>$text</li>\n";
}

$HTMLOUT = '';
global $CURUSER;

if ($CURUSER) {
    $HTMLOUT .= "{$lang['links_dead']}";
}
$HTMLOUT .= "<table><tr><td class='embedded'>";
$HTMLOUT .= "{$lang['links_other_pages_header']}
    <table><tr><td class='text'><ul>
    {$lang['links_other_pages_body']}
    </ul></td></tr></table>";
$HTMLOUT .= "{$lang['links_bt_header']}
    <table><tr><td class='text'><ul>
    {$lang['links_bt_body']}
    </ul></td></tr></table>";
$HTMLOUT .= "{$lang['links_software_header']}
    <table><tr><td class='text'><ul>
    {$lang['links_software_body']}
    </ul></td></tr></table>";
$HTMLOUT .= "{$lang['links_download_header']}
    <table><tr><td class='text'><ul>
    {$lang['links_download_body']}
    </ul></td></tr></table>";
$HTMLOUT .= "{$lang['links_forums_header']}
    <table><tr><td class='text'><ul>
   {$lang['links_forums_body']}
    </ul></td></tr></table>";
$HTMLOUT .= "{$lang['links_other_header']}
    <table><tr><td class='text'><ul>
    {$lang['links_other_body']}
    </ul></td></tr></table>";
$HTMLOUT .= "{$lang['links_pu239_header']}>
    <table><tr><td class='text'>
    {$lang['links_pu239_body']}
    </td></tr></table>";
$HTMLOUT .= '</td></tr></table>';
echo stdhead('Links') . $HTMLOUT . stdfoot();
