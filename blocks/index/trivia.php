<?php
global $lang, $site_config;


$HTMLOUT .= "
    <a id='trivia-hash'></a>
    <fieldset id='trivia' class='header'>
        <legend class='flipper has-text-primary'><i class='fa icon-up-open size_3' aria-hidden='true'></i>{$lang['index_trivia']}</legend>
        <div class='bordered'>
            <div class='alt_bordered bg-00 has-text-centered'>
                <iframe src='{$site_config['baseurl']}/trivia.php' id='triviabox' name='triviabox' scrolling='no' onload='resizeIframe(this)'></iframe>
            </div>
        </div>
    </fieldset>";
