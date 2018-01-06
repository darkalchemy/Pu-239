<?php
global $CURUSER, $site_config, $lang;

if ($site_config['msg_alert'] && $CURUSER) {
    $unread = getPmCount($CURUSER['id']);

    if (!empty($unread)) {
        $htmlout .= "
        <li>
            <a href='{$site_config['baseurl']}/pm_system.php'>
                <span class='button tag is-info dt-tooltipper-small' data-tooltip-content='#message_tooltip'>" .
            ($unread > 1 ? "{$lang['gl_newprivs']}{$lang['gl_newmesss']}" : "{$lang['gl_newpriv']}{$lang['gl_newmess']}") . "
                </span>
                <div class='tooltip_templates'>
                    <span id='message_tooltip'>
                        <div class='size_4 has-text-centered has-text-info has-text-weight-bold bottom10'>" . ($unread > 1 ? "{$lang['gl_newprivs']}{$lang['gl_newmesss']}" : "{$lang['gl_newpriv']}{$lang['gl_newmess']}") . "</div>" .
            sprintf($lang['gl_msg_alert'], $unread) . ($unread > 1 ? $lang['gl_msg_alerts'] : '') . "
                    </span>
                </div>
            </a>
        </li>";
    }
}
