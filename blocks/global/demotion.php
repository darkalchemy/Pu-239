<?php

global $CURUSER, $lang;

if ($CURUSER && 255 != $CURUSER['override_class']) {
    $htmlout .= "
    <li>
        <a href='{$site_config['baseurl']}/restoreclass.php'>
            <span class='button tag is-warning dt-tooltipper-large' data-tooltip-content='#demotion_tooltip'>
                {$lang['gl_temp_demotion']}
            </span>
            <div class='tooltip_templates'>
                <span id='demotion_tooltip'>
                    <div class='size_4 has-text-centered has-text-warning has-text-weight-bold bottom10'>{$lang['gl_temp_demotion1']}</div>
                    {$lang['gl_temp_demotion2']}
                </span>
            </div>
        </a>
    </li>";
}
