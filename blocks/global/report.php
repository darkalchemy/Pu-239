<?php

declare(strict_types = 1);

use Pu239\Cache;
use Pu239\Database;

global $CURUSER, $container, $lang, $site_config;

$cache = $container->get(Cache::class);
if ($site_config['alerts']['report'] && $CURUSER['class'] >= UC_STAFF) {
    $delt_with = $cache->get('new_report_');
    if ($delt_with === false || is_null($delt_with)) {
        $fluent = $container->get(Database::class);
        $delt_with = $fluent->from('reports')
                            ->select(null)
                            ->select('COUNT(id) AS count')
                            ->where('delt_with = 0')
                            ->fetch('count');

        $cache->set('new_report_', $delt_with, $site_config['expires']['alerts']);
    }
    if ($delt_with > 0) {
        $htmlout .= "
    <li>
        <a href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=reports&amp;action=reports'>
            <span class='button tag has-text-danger dt-tooltipper-small' data-tooltip-content='#reportmessage_tooltip'>
                " . ($delt_with > 1 ? $lang['gl_reportss'] . $lang['gl_reports_news'] : $lang['gl_reports'] . $lang['gl_reports_new']) . "
            </span>
            <div class='tooltip_templates'>
                <div id='reportmessage_tooltip' class='margin20'>
                    <div class='size_6 has-text-centered has-text-danger has-text-weight-bold bottom10'>
                        " . ($delt_with > 1 ? $lang['gl_reportss'] . $lang['gl_reports_news'] : $lang['gl_reports'] . $lang['gl_reports_new']) . "
                    </div>
                    <div class='has-text-centered'>
                        {$lang['gl_hey']} {$CURUSER['username']}!<br> $delt_with " . ($delt_with > 1 ? $lang['gl_reportss'] . $lang['gl_reports_news'] : $lang['gl_reports'] . $lang['gl_reports_new']) . "{$lang['gl_reports_dealt']}
                    </div>
                </div>
            </div>
        </a>
    </li>";
    }
}
