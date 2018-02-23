<?php

global $site_config, $lang;

$HTMLOUT .= "
    <a id='disclaimer-hash'></a>
    <fieldset id='disclaimer' class='header'>
        <legend class='flipper has-text-primary has-text-primary'><i class='fa icon-up-open size_3' aria-hidden='true'></i>{$lang['index_disclaimer']}</legend>
        <div class='bordered'>
            <div class='alt_bordered bg-00'>" .
    sprintf("<div><p class='size_3'>{$lang['foot_disclaimer']}</p></div>", $site_config['site_name']) . '
            </div>
        </div>
    </fieldset>';
