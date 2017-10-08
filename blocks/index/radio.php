<?php
require_once ROOT_DIR . 'radio.php';
$HTMLOUT .= "
    <a id='radio-hash'></a>
    <fieldset id='radio' class='header'>
        <legend class='flipper'><i class='fa fa-angle-up right10' aria-hidden='true'></i>{$lang['site_name']} Radio</legend>
        <div class='bordered padleft10 padright10'>
            <div class='alt_bordered transparent text-center'>" .
                radioinfo($radio) . "
            </div>
        </div>
    </fieldset>";
