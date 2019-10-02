<?php

declare(strict_types = 1);

use Pu239\Cache;
use Pu239\Database;

$user = check_user_status();
global $container, $site_config;

$cache = $container->get(Cache::class);
if ($site_config['alerts']['bug'] && has_access($user['class'], UC_STAFF, 'coder')) {
    $bug_count = $cache->get('bug_mess_');
    if ($bug_count === false || is_null($bug_count)) {
        $fluent = $container->get(Database::class);
        $bug_count = $fluent->from('bugs')
                            ->select(null)
                            ->select('COUNT(id) AS count')
                            ->where('status = ?', 'na')
                            ->fetch('count');

        $cache->set('bug_mess_', $bug_count, $site_config['expires']['alerts']);
    }
    if ($bug_count > 0) {
        $htmlout .= "
    <li>
        <a href='{$site_config['paths']['baseurl']}/bugs.php?action=bugs'>
            <span class='button tag is-warning dt-tooltipper-small' data-tooltip-content='#bugmessage_tooltip'>
                " . _('Bug Alert Message') . "
            </span>
            <div class='tooltip_templates'>
                <div id='bugmessage_tooltip' class='margin20'>
                    <div class='size_6 has-text-centered has-text-danger has-text-weight-bold bottom10'>
                        " . _('New Bug Message') . "
                    </div>
                    <div class='has-text-centered'>" . _('New Bug Message') . " {$user['username']}!<br> " . _pf('There is %s new bug!', 'There are %s new bugs!', $bug_count) . '</div>
                 </div>
            </div>
        </a>
    </li>';
    }
}
