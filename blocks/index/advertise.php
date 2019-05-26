<?php

declare(strict_types = 1);

global $site_config;

$advertise .= "
    <a id='advertise-hash'></a>
    <div id='advertise' class='box'>
        <div class='bordered'>
            <div class='alt_bordered bg-00 has-text-centered'>
                <a href='" . url_proxy('https://github.com/darkalchemy/Pu-239') . "'>
                    <img src='{$site_config['paths']['images_baseurl']}logo.png' alt='Pu-239' class='tooltipper mw-100' title='Pu-239'>
                </a>
            </div>
        </div>
    </div>";
