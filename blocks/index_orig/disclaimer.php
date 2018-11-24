<?php

global $site_config, $lang;

$disclaimer .= "
    <a id='disclaimer-hash'></a>
    <fieldset id='disclaimer' class='header'>
        <legend class='flipper has-text-primary has-text-primary'><i class='icon-down-open size_2' aria-hidden='true'></i>{$lang['index_disclaimer']}</legend>
        <div class='bordered'>
            <div class='alt_bordered bg-00'>" . sprintf("<div><p class='size_2'>{$lang['foot_disclaimer']}</p></div>", $site_config['site_name']) . '
            </div>
        </div>
    </fieldset>';
