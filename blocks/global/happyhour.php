<?php
global $CURUSER, $site_config, $lang;

if (!XBT_TRACKER or $site_config['happy_hour'] == true) {
    if ($CURUSER) {
        require_once INCL_DIR . 'function_happyhour.php';
        if (happyHour('check')) {
            $htmlout .= "
        <li>
        <a class='tooltip' href='browse.php?cat=" . happyCheck('check') . "'><b class='button is-success is-small'>{$lang['gl_happyhour']}</b>
        <span class='custom info alert alert-success'><em>{$lang['gl_happyhour']}</em>
        {$lang['gl_happyhour1']}<br> " . ((happyCheck('check') == 255) ? "{$lang['gl_happyhour2']}" : "{$lang['gl_happyhour3']}") . "<br><span class='has-text-danger'><b> " . happyHour('time') . " </b></span> {$lang['gl_happyhour4']}</span></a></li>";
        }
    }
}
