<?php

global $site_config, $lang;

$disclaimer .= "
    <a id='disclaimer-hash'></a>
    <div id='disclaimer' class='box'>";
$div = sprintf("
        <div class='padding20'>
            <p class='size_2'>{$lang['foot_disclaimer']}</p>
        </div>", $site_config['site_name']);
$disclaimer .= main_div($div) . '
    </div>';
