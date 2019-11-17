<?php

declare(strict_types = 1);

use Pu239\Cache;
use Pu239\Torrent;

global $container, $site_config, $CURUSER;

$cache = $container->get(Cache::class);
$torrents = $cache->get('torrent_slider_block_');
if ($torrents === false || is_null($torrents)) {
    $torrent = $container->get(Torrent::class);
    $sliding_torrents = $torrent->get_latest_slider();
    $cache->set('torrent_slider_block_', $torrents, 300);
}

if (!empty($sliding_torrents)) {
    shuffle($sliding_torrents);
    $glide .= "
    <a id='glide-hash'></a>
    <div id='glide' class='box'>
        <div class='bordered'>
            <div class='alt_bordered bg-00'>
                <div class='glide'>
                    <div data-glide-el='track' class='glide__track round10'>
                        <ul class='glide__slides'>";

    foreach ($sliding_torrents as $slider_torrent) {
        $glide .= "
                            <li class='glide__slide slides'>
                                <img src='" . url_proxy($slider_torrent['banner'], true, 1000, 185) . "' alt='' class='round10 w-100'>
                            </li>";
    }

    $glide .= '
                        </ul>      
                    </div>
                </div>
            </div>
        </div>
    </div>';
}
