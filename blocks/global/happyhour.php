<?php

declare(strict_types = 1);

$user = check_user_status();
global $site_config;

if ($site_config['bonus']['happy_hour'] && !empty($user)) {
    require_once INCL_DIR . 'function_happyhour.php';
    if (happyHour('check')) {
        $htmlout .= "
    <li>
        <a href='{$site_config['paths']['baseurl']}/browse.php?cat=" . happyCheck('check') . "'>
            <span class='button tag is-success dt-tooltipper-small' data-tooltip-content='#happyhour_tooltip'>" . _('HappyHour') . "</span>
            <div class='tooltip_templates'>
                <div id='happyhour_tooltip' class='margin20'>
                    <div class='size_6 has-text-centered has-text-success has-text-weight-bold bottom10'>
                        " . _('HappyHour') . "
                    </div>
                    <div class='has-text-centered is-primary'>
                        " . _('Hey its now happy hour!') . '<br>' . ((happyCheck('check') == 255) ? '
                        ' . _('Every torrent downloaded in the happy hour is free') : '
                        ' . _('Only in the selected Category, click on HappyHour above here to go to it')) . "<br>
                        <span class='has-text-danger'><b>" . happyHour('time') . '</b></span>
                    </div>
                </div>
            </div>
        </a>
    </li>';
    }
}
