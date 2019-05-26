<?php

declare(strict_types = 1);

require_once INCL_DIR . 'function_tfreak.php';
$feed = rsstfreakinfo();
if (!empty($feed)) {
    $tfreak_feed .= "
    <a id='tfreak-hash'></a>
    <div id='tfreak'>
        $feed
    </div>";
}
