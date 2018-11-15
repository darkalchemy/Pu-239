<?php

require_once ROOT_DIR . 'tfreak.php';
global $lang;

$feed = rsstfreakinfo();
if (!empty($feed)) {
    $tfreak_feed .= "
    <a id='tfreak-hash'></a>
    <div id='tfreak' class='box'>
        <div>
            $feed
        </div>
    </div>";
}
