<?php

require_once ROOT_DIR . 'radio.php';
$HTMLOUT .= "
    <a id='radio-hash'></a>
    <fieldset id='radio' class='header'>
        <legend class='flipper has-text-primary'><i class='icon-down-open size_2' aria-hidden='true'></i>Radio</legend>
        <div class='bordered'>
            <div class='alt_bordered bg-00 has-text-centered'>" . radioinfo($radio) . '
            </div>
        </div>
    </fieldset>';
