<?php
if ($site_config['staffmsg_alert'] && $CURUSER['class'] >= UC_STAFF) {
    if (($answeredby = $mc1->get_value('staff_mess_')) === false) {
        $res1 = sql_query('SELECT count(id) FROM staffmessages WHERE answeredby = 0');
        list($answeredby) = mysqli_fetch_row($res1);
        $mc1->cache_value('staff_mess_', $answeredby, $site_config['expires']['alerts']);
    }
    if ($answeredby > 0) {
        $htmlout .= "
        <li>
            <a href='./staffbox.php'>
                <span class='button tag is-info dt-tooltipper-small' data-tooltip-content='#staffmessage_tooltip'>" .
                    ($answeredby > 1 ? "{$lang['gl_staff_messages']}{$lang['gl_staff_message_news']}" : "{$lang['gl_staff_message']}{$lang['gl_newmess']}") . "
                </span>
                <div class='tooltip_templates'>
                    <span id='staffmessage_tooltip'>
                        <div class='size_4 has-text-centered has-text-success .has-text-weight-bold bottom10'>" .
                            ($answeredby > 1 ? "{$lang['gl_staff_messages']}{$lang['gl_staff_message_news']}" : "{$lang['gl_staff_message']}{$lang['gl_staff_message_news']}") . "
                        </div>
                            {$lang['gl_hey']} {$CURUSER['username']}!<br> " . sprintf($lang['gl_staff_message_alert'], $answeredby) . ($answeredby > 1 ? $lang['gl_staff_message_alerts'] : '') . "{$lang['gl_staff_message_for']}
                    </span>
                </div>
            </a>
        </li>";
    }
}
