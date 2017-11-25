<?php
global $site_config, $CURUSER, $cache, $lang;

if ($site_config['report_alert'] && $CURUSER['class'] >= UC_STAFF) {
    if (($delt_with = $cache->get('new_report_')) === false) {
        $res_reports = sql_query("SELECT COUNT(id) FROM reports WHERE delt_with = '0'");
        list($delt_with) = mysqli_fetch_row($res_reports);
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
