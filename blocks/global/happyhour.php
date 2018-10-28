<?php

global $CURUSER, $site_config, $lang;

if ($site_config['happy_hour'] === true) {
    if ($CURUSER) {
        require_once INCL_DIR . 'function_happyhour.php';
        if (happyHour('check')) {
            $htmlout .= "
    <li>
        <a href='{$site_config['baseurl']}/browse.php?cat=" . happyCheck('check') . "'>
            <span class='button tag is-success dt-tooltipper-small' data-tooltip-content='#happyhour_tooltip'>{$lang['gl_happyhour']}</span>
            <div class='tooltip_templates'>
                <div id='happyhour_tooltip' class='margin20'>
                    <div class='size_6 has-text-centered has-text-success has-text-weight-bold bottom10'>
                        {$lang['gl_happyhour']}
                    </div>
                    <div class='has-text-centered has-text-white'>
                        {$lang['gl_happyhour1']}<br>" . ((happyCheck('check') == 255) ? "
                        {$lang['gl_happyhour2']}" : "
                        {$lang['gl_happyhour3']}") . "<br>
                        <span class='has-text-red'><b>" . happyHour('time') . '</b></span>
                    </div>
                </div>
            </div>
        </a>
    </li>';
        }
    }
}
