<?php
// happy hour
if(XBT_TRACKER == false OR $INSTALLER09['happy_hour'] == true) {
if ($CURUSER) {
    require_once (INCL_DIR . 'function_happyhour.php');
    if (happyHour("check")) {
        $htmlout.= "
        <li>
        <a class='tooltip' href='browse.php?cat=" . happyCheck("check") . "'><b class='btn btn-success btn-small'>{$lang['gl_happyhour']}</b>
		<span class='custom info alert alert-success'><em>{$lang['gl_happyhour']}</em>
        {$lang['gl_happyhour1']}<br /> " . ((happyCheck("check") == 255) ? "{$lang['gl_happyhour2']}" : "{$lang['gl_happyhour3']}") . "<br /><font color='red'><b> " . happyHour("time") . " </b></font> {$lang['gl_happyhour4']}</span></a></li>";
    }
}
}
//==
// End Class
// End File
