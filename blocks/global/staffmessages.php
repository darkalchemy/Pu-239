<?php

global $CURUSER, $site_config, $lang, $fluent, $cache;

if ($site_config['staffmsg_alert'] && $CURUSER['class'] >= UC_STAFF) {
    $answeredby = $cache->get('staff_mess_');
    if ($answeredby === false || is_null($answeredby)) {
        $res = $fluent->from('staffmessages')
            ->select(null)
            ->select('COUNT(id) AS count')
            ->where('answeredby = 0')
            ->fetch();

        $answeredby = $res['count'];
        $cache->set('staff_mess_', $answeredby, $site_config['expires']['alerts']);
    }
    if ($answeredby > 0) {
        $htmlout .= "
        <li>
            <a href='{$site_config['baseurl']}/staffbox.php'>
                <span class='button tag is-warning dt-tooltipper-small' data-tooltip-content='#staffmessage_tooltip'>" .
            ($answeredby > 1 ? "{$lang['gl_staff_messages']}{$lang['gl_staff_message_news']}" : "{$lang['gl_staff_message']}{$lang['gl_newmess']}") . "
                </span>
                <div class='tooltip_templates'>
                    <span id='staffmessage_tooltip'>
                        <div class='size_4 has-text-centered has-text-warning has-text-weight-bold bottom10'>" .
            ($answeredby > 1 ? "{$lang['gl_staff_messages']}{$lang['gl_staff_message_news']}" : "{$lang['gl_staff_message']}{$lang['gl_staff_message_news']}") . "
                        </div>
                            {$lang['gl_hey']} {$CURUSER['username']}!<br> " . sprintf($lang['gl_staff_message_alert'], $answeredby) . ($answeredby > 1 ? $lang['gl_staff_message_alerts'] : '') . "{$lang['gl_staff_message_for']}
                    </span>
                </div>
            </a>
        </li>";
    }
}
