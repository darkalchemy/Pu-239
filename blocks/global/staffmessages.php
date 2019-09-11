<?php

declare(strict_types = 1);

use Pu239\Cache;
use Pu239\Database;

$user = check_user_status();
global $container, $lang, $site_config;

$cache = $container->get(Cache::class);
if ($site_config['alerts']['staffmsg'] && has_access($user['class'], UC_STAFF, 'coder')) {
    $answeredby = $cache->get('staff_mess_');
    if ($answeredby === false || is_null($answeredby)) {
        $fluent = $container->get(Database::class);
        $answeredby = $fluent->from('staffmessages')
                             ->select(null)
                             ->select('COUNT(id) AS count')
                             ->where('answeredby = 0')
                             ->fetch('count');

        $cache->set('staff_mess_', $answeredby, $site_config['expires']['alerts']);
    }
    if ($answeredby > 0) {
        $htmlout .= "
        <li>
            <a href='{$site_config['paths']['baseurl']}/staffbox.php'>
                <span class='button tag is-warning dt-tooltipper-small' data-tooltip-content='#staffmessage_tooltip'>" . ($answeredby > 1 ? "{$lang['gl_staff_messages']}{$lang['gl_staff_message_news']}" : "{$lang['gl_staff_message']}{$lang['gl_newmess']}") . "
                </span>
                <div class='tooltip_templates'>
                    <div id='staffmessage_tooltip' class='margin20'>
                        <div class='size_6 has-text-centered has-text-warning has-text-weight-bold bottom10'>" . ($answeredby > 1 ? "
                            {$lang['gl_staff_messages']}{$lang['gl_staff_message_news']}" : "
                            {$lang['gl_staff_message']}{$lang['gl_staff_message_news']}") . "
                        </div>
                        <div class='has-text-centered'>
                            {$lang['gl_hey']} {$user['username']}!<br> " . sprintf($lang['gl_staff_message_alert'], $answeredby) . ($answeredby > 1 ? $lang['gl_staff_message_alerts'] : '') . "{$lang['gl_staff_message_for']}
                        </div>
                    </div>
                </div>
            </a>
        </li>";
    }
}
