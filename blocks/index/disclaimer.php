<?php
$HTMLOUT .= "
    <a id='disclaimer-hash'></a>
    <fieldset id='disclaimer' class='header'>
        <legend class='flipper'><i class='fa fa-angle-up right10' aria-hidden='true'></i>{$lang['index_disclaimer']}</legend>
            <div class='text-center'>" .
                sprintf("<p><font class='small'>{$lang['foot_disclaimer']}</font></p>", $site_config['site_name']) . "
            </div>
    </fieldset>";
