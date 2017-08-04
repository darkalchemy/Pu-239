<?php
if (($latestuser = $mc1->get_value('latestuser')) === false) {
    $latestuser = mysqli_fetch_assoc(sql_query('SELECT id FROM users WHERE status = "confirmed" ORDER BY id DESC LIMIT 1'));
    $mc1->cache_value('latestuser', $latestuser, $INSTALLER09['expires']['latestuser']);
}
$latestuser = "
	<fieldset class='header'>
	<legend>{$lang['index_lmember']}</legend>
		<div class='container-fluid'>
		{$lang['index_wmember']}
		" . format_username($latestuser['id']) . "!
		</div>
	</fieldset>
	<hr>";
$HTMLOUT .= $latestuser;

