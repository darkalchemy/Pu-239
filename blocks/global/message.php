<?php
if ($site_config['msg_alert'] && $CURUSER) {
    if (($unread = $mc1->get_value('inbox_new_' . $CURUSER['id'])) === false) {
        $res = sql_query('SELECT count(id) FROM messages WHERE receiver = ' . sqlesc($CURUSER['id']) . ' && unread = "yes" AND location = "1"') or sqlerr(__FILE__, __LINE__);
        $arr = mysqli_fetch_row($res);
        $unread = (int)$arr[0];
        $mc1->cache_value('inbox_new_' . $CURUSER['id'], $unread, $site_config['expires']['unread']);
    }

    if (!empty($unread)) {
        $htmlout .= "
        <li>
            <a href='./pm_system.php'>
                <b class='btn btn-warning btn-small dt-tooltipper-small' data-tooltip-content='#message_tooltip'>" .
                    ($unread > 1 ? "{$lang['gl_newprivs']}{$lang['gl_newmesss']}" : "{$lang['gl_newpriv']}{$lang['gl_newmess']}") . "
                </b>
                <div class='tooltip_templates'>
                    <span id='message_tooltip'>
                        <div class='size_4 text-center text-lime bottom10'>" . ($unread > 1 ? "{$lang['gl_newprivs']}{$lang['gl_newmesss']}" : "{$lang['gl_newpriv']}{$lang['gl_newmess']}") . "</div>" .
                        sprintf($lang['gl_msg_alert'], $unread) . ($unread > 1 ? $lang['gl_msg_alerts'] : '') . "
                    </span>
                </div>
            </a>
        </li>";
    }
}
