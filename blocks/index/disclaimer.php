<?php
$HTMLOUT .= "
    <a id='disclaimer-hash'></a>
    <fieldset id='disclaimer' class='header'>
        <legend class='flipper'><i class='fa fa-angle-up right10' aria-hidden='true'></i>{$lang['index_disclaimer']}</legend>
        <div class='bordered padleft10 padright10'>
            <div class='alt_bordered transparent'>" .
                sprintf("<div><font class='small'>{$lang['foot_disclaimer']}</font></div>", $site_config['site_name']) . "
            </div>
        </div>
    </fieldset>";
