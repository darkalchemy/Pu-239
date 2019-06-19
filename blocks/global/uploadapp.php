<?php

declare(strict_types = 1);

use Pu239\Cache;
use Pu239\Database;

global $CURUSER, $container, $lang, $site_config;

if ($site_config['alerts']['uploadapp'] && $CURUSER['class'] >= UC_STAFF) {
    $cache = $container->get(Cache::class);
    $newapp = $cache->get('new_uploadapp_');
    if ($newapp === false || is_null($newapp)) {
        $fluent = $container->get(Database::class);
        $newapp = $fluent->from('uploadapp')
                         ->select(null)
                         ->select('COUNT(id) AS count')
                         ->where('status = ?', 'pending')
                         ->fetch('count');

        $cache->set('new_uploadapp_', $newapp, $site_config['expires']['alerts']);
    }
    if ($newapp > 0) {
        $htmlout .= "
    <li>
        <a href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=uploadapps&amp;action=app'>
            <span class='button tag is-info dt-tooltipper-small' data-tooltip-content='#uploadapp_tooltip'>
                {$lang['gl_uploadapp_new']}
            </span>
            <div class='tooltip_templates'>
                <div id='uploadapp_tooltip' class='margin20'>
                    <div class='size_6 has-text-centered has-text-danger has-text-weight-bold bottom10 has-text-black'>
                        {$lang['gl_hey']} {$CURUSER['username']}!
                    </div>
                    <div class='has-text-centered'>
                        $newapp {$lang['gl_uploadapp_ua']}" . ($newapp > 1 ? 's' : '') . " {$lang['gl_uploadapp_dealt']}
                    </div>
                </div>
            </div>
        </a>
    </li>";
    }
}
