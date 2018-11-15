<?php

global $site_config, $lang;

$disclaimer .= "
    <a id='disclaimer-hash'></a>
    <div id='disclaimer' class='box'>
        <div class='bordered'>
            <div class='alt_bordered bg-00'>" . sprintf("<div><p class='size_2'>{$lang['foot_disclaimer']}</p></div>", $site_config['site_name']) . '
            </div>
        </div>
    </div>';
