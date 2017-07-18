<?php
if ($INSTALLER09['bug_alert'] && $CURUSER['class'] >= UC_STAFF) {
    if (($bugs = $mc1->get_value('bug_mess_')) === false) {
        $res1 = sql_query("SELECT COUNT(id) FROM bugs WHERE status = 'na'");
        list($bugs) = mysqli_fetch_row($res1);
        $mc1->cache_value('bug_mess_', $bugs, $INSTALLER09['expires']['alerts']);
    }
    if ($bugs > 0) {
        $htmlout.= "<li>
    <a class='tooltip' href='bugs.php?action=bugs'><b class='btn btn-danger btn-small'>{$lang['gl_bug_alert']}</b>
	<span class='custom info custom info alert alert-danger'><em>{$lang['gl_bug_alert1']}</em>
   <b>{$lang['gl_bug_alert2']} {$CURUSER['username']}!<br /> " . sprintf($lang['gl_bugs'], $bugs[0]) . ($bugs[0] > 1 ? "{$lang['gl_bugss']}" : "") . "!</b>
   {$lang['gl_bug_alert3']}
   </span></a></li>";
    }
}
//==End
// End Class
// End File
