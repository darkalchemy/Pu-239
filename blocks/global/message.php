<?php

global $CURUSER, $site_config, $lang, $message_stuffs;

if ($site_config['msg_alert'] && $CURUSER) {
    $unread = $message_stuffs->get_count($CURUSER['id']);

    if (!empty($unread)) {
        $htmlout .= "
        <li>
            <a href='{$site_config['baseurl']}/messages.php'>
                <span class='button tag is-info dt-tooltipper-small' data-tooltip-content='#message_tooltip'>" . ($unread > 1 ? "{$lang['gl_newprivs']}{$lang['gl_newmesss']}" : "{$lang['gl_newpriv']}{$lang['gl_newmess']}") . "
                </span>
                <div class='tooltip_templates'>
                    <div id='message_tooltip' class='margin20'>
                        <div class='size_4 has-text-centered has-text-info has-text-weight-bold bottom10'>" . ($unread > 1 ? "
                            {$lang['gl_newprivs']}{$lang['gl_newmesss']}" : "
                            {$lang['gl_newpriv']}{$lang['gl_newmess']}") . '
                        </div>' . sprintf($lang['gl_msg_alert'], $unread) . ($unread > 1 ? $lang['gl_msg_alerts'] : '') . '
                    </div>
                </div>
            </a>
        </li>';
    }
}
