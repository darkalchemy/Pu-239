<?php

require_once ROOT_DIR . 'radio.php';

$site_radio .= "
    <a id='radio-hash'></a>
    <div id='radio' class='box'>
        <div class='bordered'>
            <div class='alt_bordered bg-00 has-text-centered'>" . radioinfo($radio) . '
            </div>
        </div>
    </div>';
