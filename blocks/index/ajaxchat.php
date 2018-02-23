<?php

global $CURUSER, $lang;

$height = 600;
if (!empty($CURUSER['ajaxchat_height'])) {
    $height = $CURUSER['ajaxchat_height'];
}

$HTMLOUT .= "
    <a id='ajaxchat-hash'></a>
    <fieldset id='ajaxchat' class='header'>
        <legend class='flipper has-text-primary'><i class='fa icon-up-open size_3' aria-hidden='true'></i>{$lang['index_ajaxchat']}</legend>
        <div class='bordered'>
            <div class='alt_bordered iframe-container bg-none has-text-centered is-paddingless'>
                <iframe src='./ajaxchat.php' height='$height' id='iframe_ajaxchat' name='iframe_ajaxchat'></iframe>
            </div>
        </div>
    </fieldset>";
