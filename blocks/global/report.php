<?php
if ($INSTALLER09['report_alert'] && $CURUSER['class'] >= UC_STAFF) {
    if (($delt_with = $mc1->get_value('new_report_')) === false) {
        $res_reports = sql_query("SELECT COUNT(id) FROM reports WHERE delt_with = '0'");
        list($delt_with) = mysqli_fetch_row($res_reports);
        $mc1->cache_value('new_report_', $delt_with, $INSTALLER09['expires']['alerts']);
    }
    if ($delt_with > 0) {
        $htmlout.= "
    <li>
    <a class='tooltip' href='staffpanel.php?tool=reports&amp;action=reports'><b class='btn btn-warning btn-small'>" . ($delt_with > 1 ? $lang['gl_reportss'] . $lang['gl_reports_news'] : $lang['gl_reports'] . $lang['gl_reports_new']) . "</b>
	<span class='custom info alert alert-warning'><em>" . ($delt_with > 1 ? $lang['gl_reportss'] . $lang['gl_reports_news'] : $lang['gl_reports'] . $lang['gl_reports_new']) . "</em>
    " . $lang['gl_hey'] . " {$CURUSER['username']}!<br /> $delt_with " . ($delt_with > 1 ? $lang['gl_reportss'] . $lang['gl_reports_news'] : $lang['gl_reports'] . $lang['gl_reports_new']) . "" . $lang['gl_reports_dealt'] . "<br />
    " . $lang['gl_reports_click'] . "
    </span></a></li>";
    }
}
//==End
// End Class
// End File
