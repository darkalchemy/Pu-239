<?php

declare(strict_types = 1);

use Pu239\Torrent;

global $container, $lang, $site_config, $CURUSER;

$torrent = $container->get(Torrent::class);
$sliding_torrents = $torrent->get_latest_slider();

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
                    <div class="glide__arrows" data-glide-el="controls">
                        <button class="glide__arrow glide__arrow--left" data-glide-dir="<">prev</button>
                        <button class="glide__arrow glide__arrow--right" data-glide-dir=">">next</button>
                    </div>
                    <div class="glide__bullets" data-glide-el="controls[nav]">';
    $i = 0;
    foreach ($sliding_torrents as $slider_torrent) {
        $glide .= "    
                        <button class='glide__bullet' data-glide-dir='=$i'></button>";
        ++$i;
    }
    $glide .= '
                    </div>
                </div>
            </div>
        </div>
    </div>';
}
