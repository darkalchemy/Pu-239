<?php

declare(strict_types = 1);

use Pu239\Message;

global $CURUSER, $container, $lang, $site_config;

if ($site_config['alerts']['message'] && $CURUSER) {
    $message_stuffs = $container->get(Message::class);
    $unread = $message_stuffs->get_count($CURUSER['id']);

    if (!empty($unread)) {
        $htmlout .= "
        <li>
            <a href='{$site_config['paths']['baseurl']}/messages.php'>
                <span class='button tag is-info has-text-black dt-tooltipper-small' data-tooltip-content='#message_tooltip'>" . ($unread > 1 ? "{$lang['gl_newprivs']}{$lang['gl_newmesss']}" : "{$lang['gl_newpriv']}{$lang['gl_newmess']}") . "
                </span>
                <div class='tooltip_templates'>
                    <div id='message_tooltip' class='margin20'>
                        <div class='size_6 has-text-centered has-text-success has-text-weight-bold bottom10'>" . ($unread > 1 ? "
                            {$lang['gl_newprivs']}{$lang['gl_newmesss']}" : "
                            {$lang['gl_newpriv']}{$lang['gl_newmess']}") . "
                        </div>
                        <div class='has-text-centered'>" . sprintf($lang['gl_msg_alert'], $unread) . ($unread > 1 ? $lang['gl_msg_alerts'] : '') . '</div>
                    </div>
                </div>
            </a>
        </li>';
    }
}
