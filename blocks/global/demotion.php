<?php

declare(strict_types = 1);

$user = check_user_status();
global $site_config;

if ($user && $user['override_class'] != 255) {
    $htmlout .= "
    <li>
        <a href='{$site_config['paths']['baseurl']}/restoreclass.php'>
            <span class='button tag is-warning dt-tooltipper-small' data-tooltip-content='#demotion_tooltip'>
                " . _('Temp. Demotion') . "
            </span>
            <div class='tooltip_templates'>
                <div id='demotion_tooltip' class='margin20'>
                    <div class='size_6 has-text-centered has-text-warning has-text-weight-bold bottom10'>
                        " . _('Temporary Demotion') . "
                    </div>
                    <div class='has-text-centered'>
                        " . _('To reset your class, simply click here.') . '
                    </div>
                </div>
            </div>
        </a>
    </li>';
}
