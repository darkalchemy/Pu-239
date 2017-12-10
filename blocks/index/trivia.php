<?php
global $lang, $site_config;

$HTMLOUT .= "
    <a id='trivia-hash'></a>
    <fieldset id='trivia' class='header'>
        <legend class='flipper has-text-primary'><i class='fa fa-angle-up right10' aria-hidden='true'></i>{$lang['index_trivia']}</legend>
        <iframe src='{$site_config['baseurl']}/trivia.php' id='triviabox' name='triviabox' scrolling='no' onload='resizeIframe(this)' style='margin-bottom: -3px;' class='bg-none'></iframe>
    </fieldset>";
