<?php

require_once INCL_DIR . 'function_tfreak.php';
global $lang;

$feed = rsstfreakinfo();
if (!empty($feed)) {
    $tfreak_feed .= "
    <a id='tfreak-hash'></a>
    <div id='tfreak'>
        $feed
    </div>";
}
