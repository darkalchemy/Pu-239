<?php

declare(strict_types = 1);

use Pu239\Database;

require_once __DIR__ . '/../include/bittorrent.php';
require_once INCL_DIR . 'function_html.php';
require_once INCL_DIR . 'function_categories.php';
check_user_status();
$lang = array_merge(load_language('global'), load_language('index'));
$parents = genrelist(true);

$heading = "
        <tr>
            <th class='has-text-centered w-25'>Cat ID</th>
            <th class='has-text-centered'>Cat Name</th>
            <th class='has-text-centered w-25'>Torrents Uploaded</th>
        </tr>";
$body = '';
global $container, $site_config;

$fluent = $container->get(Database::class);
$counts = $fluent->from('torrents')
                 ->select(null)
                 ->select('category')
                 ->select('COUNT(id) AS count')
                 ->groupBy('category')
                 ->fetchPairs('category', 'COUNT(id)');

$child = [
    'id' => '',
    'name' => '',
];
foreach ($parents as $parent) {
    foreach ($parent['children'] as $child) {
        $count = !empty($counts) && !empty($counts[$child['id']]) ? $counts[$child['id']] : 0;
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
