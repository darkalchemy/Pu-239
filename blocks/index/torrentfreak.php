<?php

require_once ROOT_DIR . 'tfreak.php';
global $lang;

$feed = rsstfreakinfo();
if (!empty($feed)) {
    $HTMLOUT .= "
    <a id='tfreak-hash'></a>
    <fieldset id='tfreak' class='header'>
        <legend class='flipper has-text-primary'><i class='icon-down-open size_3' aria-hidden='true'></i>{$lang['index_torr_freak']}</legend>
        <div>
            $feed
        </div>
    </fieldset>";
}
