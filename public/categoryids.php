<?php

require_once __DIR__ . '/../include/bittorrent.php';
require_once INCL_DIR . 'function_html.php';
check_user_status();
global $site_config, $fluent;

$lang = array_merge(load_language('global'), load_language('index'));
$parents = genrelist(true);

$heading = "
        <tr>
            <th class='has-text-centered w-25'>Cat ID</th>
            <th class='has-text-centered'>Cat Name</th>
            <th class='has-text-centered w-25'>Torrents Uploaded</th>
        </td>";
$body = '';
foreach ($parents as $parent) {
    foreach ($parent['children'] as $child) {
        $count = $fluent->from('torrents')
                        ->select(null)
                        ->select('COUNT(*) AS count')
                        ->where('category = ?', $child['id'])
                        ->fetch('count');
        $body .= "
        <tr>
            <td class='has-text-centered'>{$child['id']}</td>
            <td><a href='{$site_config['paths']['baseurl']}/browse.php?cats[]={$child['id']}'>{$parent['name']}::{$child['name']}</a></td>
            <td class='has-text-centered'>$count</td>
        </tr>";
    }
}

$HTMLOUT = "
    <h1 class='has-text-centered'>Category ID's</h1>";
$HTMLOUT .= main_table($body, $heading, 'w-50 has-text-centered');

echo stdhead("Category ID's") . wrapper($HTMLOUT) . stdfoot();
