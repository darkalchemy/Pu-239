<?php
//==Temp demotion
if ($CURUSER['override_class'] != 255 && $CURUSER) // Second condition needed so that this box isn't displayed for non members/logged out members.
{
    $htmlout.= "<li>
<a class='tooltip' href='./restoreclass.php'><b class='btn btn-warning btn-small'>{$lang['gl_temp_demotion']}</b>
<span class='custom info alert alert-warning'><em>{$lang['gl_temp_demotion1']}</em>   
{$lang['gl_temp_demotion2']}</span></a></li>";
}
//==End
// End Class
// End File
