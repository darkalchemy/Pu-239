<?php

global $site_config;

$ajaxchat .= "
    <a id='ajaxchat-hash'></a>
    <div id='ajaxchat' class='box'>
        <div class='bordered'>
            <div class='alt_bordered iframe-container bg-none has-text-centered is-paddingless'>
                <iframe src='{$site_config['paths']['baseurl']}/ajaxchat.php' id='iframe_ajaxchat' name='iframe_ajaxchat' allow='autoplay' class='iframe' style='visibility:hidden;' onload=\"this.style.visibility = 'visible';\"></iframe>
            </div>
        </div>
    </div>";
