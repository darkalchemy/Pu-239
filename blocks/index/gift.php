<?php
$xmasday = mktime(0, 0, 0, 12, 25, date("Y"));
$today = mktime(date("G") , date("i") , date("s") , date("m") , date("d") , date("Y"));
if (($CURUSER["opt1"] & user_options::GOTGIFT) && $today <> $xmasday) {
//if ($CURUSER["gotgift"] == 'no' && $today <> $xmasday) {
    $HTMLOUT.= "
	<fieldset class='header'>
		<legend>{$lang['index_xmas_gift']}</legend>
			<div class='container-fluid  cite text-center'>
			<a href='{$INSTALLER09['baseurl']}/gift.php?open=1'><img src='{$INSTALLER09['pic_base_url']}gift.png' style='float: center;border-style: none;' alt='{$lang['index_xmas_gift']}' title='{$lang['index_xmas_gift']}' /></a>
			<br /><br /><br /><br />
			</div>
	</fieldset>
	<hr />";
}
//==
// End Class
// End File
