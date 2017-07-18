<?php
//==Start birthdayusers pdq
$current_date = getdate();
$keys['birthdayusers'] = 'birthdayusers';
if (($birthday_users_cache = $mc1->get_value($keys['birthdayusers'])) === false) {
    $birthdayusers = '';
    $birthday_users_cache = array();
    $res = sql_query("SELECT id, username, class, donor, title, warned, enabled, chatpost, leechwarn, pirate, king, birthday, perms FROM users WHERE MONTH(birthday) = " . sqlesc($current_date['mon']) . " AND DAYOFMONTH(birthday) = " . sqlesc($current_date['mday']) . " AND perms < " . bt_options::PERMS_STEALTH . " ORDER BY username ASC") or sqlerr(__FILE__, __LINE__);
    $actcount = mysqli_num_rows($res);
    while ($arr = mysqli_fetch_assoc($res)) {
        if ($birthdayusers) $birthdayusers.= ",\n";
        $birthdayusers.= '<b>' . format_username($arr) . '</b>';
    }
    $birthday_users_cache['birthdayusers'] = $birthdayusers;
    $birthday_users_cache['actcount'] = $actcount;
    $mc1->cache_value($keys['birthdayusers'], $birthday_users_cache, $INSTALLER09['expires']['birthdayusers']);
}
if (!$birthday_users_cache['birthdayusers']) $birthday_users_cache['birthdayusers'] = $lang['index_birthday_no'];
$birthday_users = 
	'<fieldset class="header">
		<legend>' . $lang['index_birthday'] . '&nbsp;(' . $birthday_users_cache['actcount'] . ')</legend>
		 <div class="container-fluid">  
			 <!--<a href=\'javascript: klappe_news("a1")\'><img border=\'0\' src=\'pic/plus.gif\' id=\'pica1\' alt=\'' . $lang['index_hide_show'] . '\' /></a><div id=\'ka1\' style=\'display: none;\'>-->
			  ' . $birthday_users_cache['birthdayusers'] . '
		 </div><!--</div>-->
	</fieldset><hr />';
$HTMLOUT.= $birthday_users;
//== end birthdayusers
// End Class
// End File
