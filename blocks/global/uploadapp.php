<?php

declare(strict_types = 1);

use Pu239\Cache;
use Pu239\Database;

$user = check_user_status();
global $container, $site_config;

if ($site_config['alerts']['uploadapp'] && has_access($user['class'], UC_STAFF, 'coder')) {
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
            <span class='button tag is-info dt-tooltipper-small has-text-black' data-tooltip-content='#uploadapp_tooltip'>
                " . _p('New Uploader App', 'New Uploader Apps', $newapp) . "
            </span>
            <div class='tooltip_templates'>
                <div id='uploadapp_tooltip' class='margin20'>
                    <div class='size_6 has-text-centered has-text-danger has-text-weight-bold bottom10'>
                        " . _('Hey %s!', $user['username']) . "
                    </div>
                    <div class='has-text-centered'>
                        " . _pf('%2$d uploader application to be dealt with.', '%2$d uploader applications to be dealt with.', $newapp) . '
                    </div>
                </div>
            </div>
        </a>
    </li>';
    }
}
