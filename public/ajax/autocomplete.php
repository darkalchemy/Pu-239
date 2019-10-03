<?php

declare(strict_types = 1);

use Pu239\Cache;
use Pu239\Database;

require_once __DIR__ . '/../../include/bittorrent.php';
require_once INCL_DIR . 'function_users.php';
check_user_status();
global $container;

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
    $fluent = $container->get(Database::class);
    $results = $fluent->from('torrents')
                      ->select(null)
                      ->select('id')
                      ->select('name')
                      ->select('seeders')
                      ->select('leechers')
                      ->select('visible')
                      ->where('name LIKE ?', "%$keyword%")
                      ->fetchAll();
    $cache = $container->get(Cache::class);
    $cache->set($hash, $results, 0);
    $hashes = $cache->get('suggest_torrents_hashes_');
    if (empty($hashes)) {
        $hashes = [];
    }
    if (!in_array($hash, $hashes)) {
        $hashes[] = $hash;
        $cache->set('suggest_torrents_hashes_', $hashes, 300);
    }
}

$temp = "
        <ul>
            <li class='has-text-centered'>" . _fe("No results. Try refining your search for '{0}.'", $keyword) . '</li>
        </ul>';

if (!empty($results)) {
    $temp = "
        <ul class='columns has-text-wight-bold'>
            <li class='column is-three-fifth'>
                <span class='size_5 is-bold'>" . _('Name') . "</span>
            </li>
            <li class='column is-one-fifth has-text-centered'>
                <span class='size_5 is-bold'>" . _('Seeders') . "</span>
            </li>
            <li class='column is-one-fifth has-text-centered'>
                <span class='size_5 is-bold'>" . _('Leechers') . '</span>
            </li>
        </ul>';
    $i = 1;
    foreach ($results as $result) {
        $color = $result['visible'] === 'yes' ? 'is-success' : 'has-text-danger';
        $background = $i++ % 2 === 0 ? 'bg-04' : 'bg-03';
        $temp .= "
        <ul class='columns $background round10'>
            <li class='column is-three-fifth'>
                <a href='{$site_config['paths']['baseurl']}/details.php?id={$result['id']}&amp;hit=1'>
                    <span class='$color'>{$result['name']}</span>
                </a>
            </li>
            <li class='column is-one-fifth has-text-centered'>
                <span class='$color'>{$result['seeders']}</span>
            </li>
            <li class='column is-one-fifth has-text-centered'>
                <span class='$color'>{$result['leechers']}</span>
            </li>
        </ul>";
    }
}

echo $temp;
