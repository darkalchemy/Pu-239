<?php

require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'user_functions.php';
global $site_config, $cache;

if (!$session->validateToken($_POST['csrf'])) {
    return false;
    die();
}

if (isset($_POST['id'])) {
    $id = $_POST['id'];
}

if (!isset($_POST['keyword']) || strlen($_POST['keyword']) < 2) {
    return false;
}
$keyword = htmlsafechars(strtolower(strip_tags($_POST['keyword'])));
$hash = 'suggest_torrents_' . hash('sha256', $keyword);

$results = $cache->get($hash);
if ($results === false || is_null($results)) {
    $results = $fluent->from('torrents')
        ->select(null)
        ->select('id')
        ->select('name')
        ->select('seeders')
        ->select('leechers')
        ->select('visible')
        ->where('name LIKE ?', "%$keyword%")
        ->fetchAll();
    $cache->set($hash, $results, 0);
    $hashes = $cache->get('suggest_torrents_hashes_');
    if (empty($hashes)) {
        $hashes = [];
    }
    if (!in_array($hash, $hashes)) {
        $hashes[] = $hash;
        $cache->set('suggest_torrents_hashes_', $hashes, 0);
    }
}

$temp = "
        <ul>
            <li class='has-text-centered'>No results. Try refining your search for '$keyword'.</li>
        </ul>";

if (!empty($results)) {
    $temp = "
        <ul class='columns level w-100 is-paddingless'>
            <li class='column is-three-fifth is-paddingless'>
                <span class='size_6 is-bold'>Name</span>
            </li>
            <li class='column is-one-fifth is-paddingless has-text-centered'>
                <span class='size_6 is-bold'>Seeders</span>
            </li>
            <li class='column is-one-fifth is-paddingless has-text-centered'>
                <span class='size_6 is-bold'>Leechers</span>
            </li>
        </ul>
        <hr class='top5 bottom20'>";
    $i = 1;
    foreach ($results as $result) {
        $color = $result['visible'] === 'yes' ? 'has-text-green' : 'has-text-red';
        $background = $i++ % 2 === 0 ? 'bg-04' : 'bg-03';
        $temp .= "
        <ul class='columns level w-100 padding10 round5 $background'>
            <li class='column is-three-fifth is-paddingless'>
                <a href='{$site_config['baseurl']}/details.php?id={$result['id']}&amp;hit=1'>
                    <span class='$color'>{$result['name']}</span>
                </a>
            </li>
            <li class='column is-one-fifth is-paddingless has-text-centered'>
                <span class='$color'>{$result['seeders']}</span>
            </li>
            <li class='column is-one-fifth is-paddingless has-text-centered'>
                <span class='$color'>{$result['leechers']}</span>
            </li>
        </ul>";
    }
}

echo $temp;
