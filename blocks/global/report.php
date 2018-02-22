<?php
global $site_config, $CURUSER, $lang, $fluent;

$cache = new DarkAlchemy\Pu239\Cache();

if ($site_config['report_alert'] && $CURUSER['class'] >= UC_STAFF) {
    $delt_with = $cache->get('new_report_');
    if ($delt_with === false || is_null($delt_with)) {
        $res_reports = $fluent->from('reports')
            ->select(null)
            ->select('COUNT(id) AS count')
            ->where('delt_with = 0')
            ->fetch();

        $delt_with = $res_reports['count'];
        $cache->set('new_report_', $delt_with, $site_config['expires']['alerts']);
    }
    if ($delt_with > 0) {
        $htmlout .= "
    <li>
        <a href='{$site_config['baseurl']}/staffpanel.php?tool=reports&amp;action=reports'>
            <span class='button tag is-danger dt-tooltipper-small' data-tooltip-content='#reportmessage_tooltip'>
                " . ($delt_with > 1 ? $lang['gl_reportss'] . $lang['gl_reports_news'] : $lang['gl_reports'] . $lang['gl_reports_new']) . "
            </span>
            <div class='tooltip_templates'>
                <span id='reportmessage_tooltip'>
                    <div class='size_4 has-text-centered has-text-danger has-text-weight-bold bottom10'>
                        " . ($delt_with > 1 ? $lang['gl_reportss'] . $lang['gl_reports_news'] : $lang['gl_reports'] . $lang['gl_reports_new']) . "
                    </div>
                    {$lang['gl_hey']} {$CURUSER['username']}!<br> $delt_with " . ($delt_with > 1 ? $lang['gl_reportss'] . $lang['gl_reports_news'] : $lang['gl_reports'] . $lang['gl_reports_new']) . "{$lang['gl_reports_dealt']}
                </span>
            </div>
        </a>
    </li>";
    }
}
