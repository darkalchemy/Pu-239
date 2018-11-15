<?php

global $CURUSER, $lang, $site_config;

$height = 600;
if (!empty($CURUSER['ajaxchat_height'])) {
    $height = $CURUSER['ajaxchat_height'];
}

$ajaxchat .= "
    <a id='ajaxchat-hash'></a>
    <div id='ajaxchat' class='box'>
        <div class='bordered'>
            <div class='alt_bordered iframe-container bg-none has-text-centered is-paddingless'>
                <iframe src='{$site_config['baseurl']}/ajaxchat.php' height='$height' id='iframe_ajaxchat' name='iframe_ajaxchat' allow='autoplay' class='iframe'></iframe>
            </div>
        </div>
    </div>";

