<?php

declare(strict_types = 1);
global $CURUSER, $site_config;

if ($CURUSER['gotgift'] === 'no') {
    $christmas_gift .= "
    <a id='gift-hash'></a>
    <div id='gift' class='box'>";
    $div = "
                <a href='{$site_config['paths']['baseurl']}/gift.php?open=1'>
                    <img src='{$site_config['paths']['images_baseurl']}gift.png' class='tooltipper image_48 padding20' alt='{$lang['index_christmas_gift']}' title='{$lang['index_christmas_gift']}'>
                </a>";
    $christmas_gift .= main_div($div, 'has-text-centered') . '
    </div>';
}
