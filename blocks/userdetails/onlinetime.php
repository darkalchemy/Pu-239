<?php
//== Online time
if ($user['onlinetime'] > 0) {
    $onlinetime = time_return($user['onlinetime']);
    $HTMLOUT .= "<tr><td class='rowhead'>{$lang['userdetails_time_online']}</td><td width='99%'>{$onlinetime}</td></tr>";
} else {
    $onlinetime = $lang['userdetails_notime_online'];
    $HTMLOUT .= "<tr><td class='rowhead'>{$lang['userdetails_time_online']}</td><td width='99%'>{$onlinetime}</td></tr>";
}
// end
// End Class
// End File
