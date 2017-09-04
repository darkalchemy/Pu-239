<?php
$HTMLOUT .= "
    <a id='disclaimer-hash'></a>
    <fieldset id='disclaimer' class='header'>
        <legend class='flipper'><i class='fa fa-angle-up' aria-hidden='true'></i>{$lang['index_disclaimer']}</legend>
            <div class='text-center'>" .
                sprintf("<p><font class='small'>{$lang['foot_disclaimer']}</font></p>", $INSTALLER09['site_name']) . "
            </div>
    </fieldset>";
