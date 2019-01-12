<?php

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'html_functions.php';
check_user_status();
global $site_config;

$lang = array_merge(load_language('global'), load_language('index'));
$parents = genrelist(true);

$heading = "
        <tr>
            <th>Cat ID</th>
            <th>Cat Name</th>
        </td>";
$body = '';
foreach ($parents as $parent) {
    foreach ($parent['children'] as $child) {
        $body .= "
        <tr>
            <td>{$child['id']}</td>
            <td><a href='{$site_config['baseurl']}/browse.php?cats[]={$child['id']}'>{$parent['name']}::{$child['name']}</a></td>
        </tr>";
    }
}

$HTMLOUT = "
    <h1 class='has-text-centered'>Category ID's</h1>";
$HTMLOUT .= main_table($body, $heading, 'w-50 has-text-centered');

echo stdhead("Category ID's") . wrapper($HTMLOUT) . stdfoot();
