<?php

declare(strict_types = 1);
global $site_config;

$disclaimer .= "
    <a id='disclaimer-hash'></a>
    <div id='disclaimer' class='box'>";
$div = sprintf("
        <div class='padding20'>
            <p class='size_2'>
                " . _("Disclaimer: None of the files shown here are actually hosted on this server. The links are provided solely by this site's users. The administrator of this site (%s) cannot be held responsible for what its users post, or any other actions of its users. You may not use this site to distribute or download any material when you do not have the legal rights to do so. It is your own responsibility to adhere to these terms.") . '
            </p>
        </div>', $site_config['site']['name']);
$disclaimer .= main_div($div) . '
    </div>';
