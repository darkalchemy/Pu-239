<?php
global $lang;

$HTMLOUT .= "
    <a id='trivia-hash'></a>
    <fieldset id='trivia' class='header'>
        <legend class='flipper has-text-primary'><i class='fa fa-angle-up right10' aria-hidden='true'></i>{$lang['index_trivia']}</legend>
        <div class='bordered'>
            <div class='alt_bordered iframe-container bg-none has-text-centered'>
                <iframe src='./trivia.php' id='triviabox' name='triviabox' scrolling='no' onload='resizeIframe(this)'></iframe>
            </div>
        </div>
    </fieldset>";
