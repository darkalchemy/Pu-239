<?php
require_once ROOT_DIR . 'tfreak.php';
$HTMLOUT .= "
    <a id='tfreak-hash'></a>
    <fieldset id='tfreak' class='header'>
        <legend class='flipper'><i class='fa fa-angle-up' aria-hidden='true'></i>{$lang['index_torr_freak']}</legend>
            <div class='text-center'>" .
                rsstfreakinfo() . "
            </div>
    </fieldset>";
