<?php
/** latestuser index **/
if (($latestuser_cache = $mc1->get_value('latestuser')) === false) {
    $latestuser_cache = mysqli_fetch_assoc(sql_query('SELECT id, username, class, donor, warned, enabled, chatpost, leechwarn, pirate, king FROM users WHERE status="confirmed" ORDER BY id DESC LIMIT 1'));
    $latestuser_cache['id'] = (int)$latestuser_cache['id'];
    $latestuser_cache['class'] = (int)$latestuser_cache['class'];
    $latestuser_cache['warned'] = (int)$latestuser_cache['warned'];
    $latestuser_cache['chatpost'] = (int)$latestuser_cache['chatpost'];
    $latestuser_cache['leechwarn'] = (int)$latestuser_cache['leechwarn'];
    $latestuser_cache['pirate'] = (int)$latestuser_cache['pirate'];
    $latestuser_cache['king'] = (int)$latestuser_cache['king'];
    $mc1->cache_value('latestuser', $latestuser_cache, $INSTALLER09['expires']['latestuser']);
}
$latestuser = '
	<fieldset class="header">
	<legend>' . $lang['index_lmember'] . '</legend>
		<div class="container-fluid">
		' . $lang['index_wmember'] . '
		<b>' . format_username($latestuser_cache) . '!</b>
		</div>
	</fieldset>
	<hr />';
//==MemCached latest user
$HTMLOUT.= $latestuser;
//==
// End Class
// End File
