<?php

declare(strict_types = 1);

global $CURUSER, $site_config;

if ($CURUSER && $CURUSER['override_class'] != 255) {
    $htmlout .= "
    <li>
        <a href='{$site_config['paths']['baseurl']}/restoreclass.php'>
            <span class='button tag is-warning dt-tooltipper-small' data-tooltip-content='#demotion_tooltip'>
                {$lang['gl_temp_demotion']}
            </span>
            <div class='tooltip_templates'>
                <div id='demotion_tooltip' class='margin20'>
                    <div class='size_6 has-text-centered has-text-warning has-text-weight-bold bottom10'>
                        {$lang['gl_temp_demotion1']}
                    </div>
                    <div class='has-text-centered'>
                        {$lang['gl_temp_demotion2']}
                    </div>
                </div>
            </div>
        </a>
    </li>";
}
