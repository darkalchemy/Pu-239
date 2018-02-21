<?php

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'user_functions.php';
require_once INCL_DIR . 'bbcode_functions.php';
require_once INCL_DIR . 'html_functions.php';
check_user_status();
global $site_config, $CURUSER, $lang, $fluent;

$cache = new Cache();

$table = $body = $heading = '';
if (!empty($_GET)) {
    $results = $fluent->from('torrents')
        ->select(null)
        ->select('id')
        ->select('name')
        ->select('hex(info_hash) AS info_hash')
        ->where('name = ?', $_GET['search']);
    foreach ($results as $result) {
        $body .= "
        <tr>
            <td>{$result['id']}</td>
            <td><a href='{$site_config['baseurl'    ]}/details.php?id={$result['id']}'>{$result['name']}</a></td>
            <td>{$result['info_hash']}</td>
        </tr>";
    }
    if (!empty($body)) {
        $heading = "
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Info Hash</th>
        </tr>";
        $table = main_table($body, $heading, 'top10');
    } else {
        $table = main_div('Nothing Found');
    }
}

$HTMLOUT = "
    <h1 class='has-text-centered'>BOT Search</h1>
    <form id='search' method='get' action='{$site_config['baseurl']}/search.php'>
        <div class='padding10' class='w-100'>
            <input type='text' name='search' placeholder='BOT search' class='search w-100' value='" . (!empty($_GET['search']) ? $_GET['search'] : '') . "' />
        </div>
        <div class='margin10 has-text-centered'>
            <input type='submit' value='Search' class='button is-small' />
        </div>
    </form>$table";

echo stdhead('Bot Search Torrents', true) . wrapper($HTMLOUT) . stdfoot();
