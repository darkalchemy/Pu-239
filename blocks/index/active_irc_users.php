<?php
function calctime($val)
{
    global $lang;
    $days = intval($val / 86400);
    $val -= $days * 86400;
    $hours = intval($val / 3600);
    $val -= $hours * 3600;
    $mins = intval($val / 60);
    $secs = $val - ($mins * 60);

    return "<br>&#160;&#160;&#160;$days {$lang['gl_irc_days']}, $hours {$lang['gl_irc_hrs']}, $mins {$lang['gl_irc_min']}";
}

$keys['activeircusers'] = 'activeircusers';
if (($active_irc_users_cache = $mc1->get_value($keys['activeircusers'])) === false) {
    $dt = $_SERVER['REQUEST_TIME'] - 180;
    $activeircusers = '';
    $active_irc_users_cache = [];
    $res = sql_query('SELECT id, username, perms FROM users WHERE onirc = "yes" AND perms < ' . bt_options::PERMS_STEALTH . ' ORDER BY username ASC') or sqlerr(__FILE__, __LINE__);
    $actcount = mysqli_num_rows($res);
    while ($arr = mysqli_fetch_assoc($res)) {
        if ($activeircusers) {
            $activeircusers .= ", ";
        }
        $activeircusers .= format_username($arr['id']);
    }
    $active_irc_users_cache['activeircusers'] = $activeircusers;
    $active_irc_users_cache['actcount'] = $actcount;
    $mc1->cache_value($keys['activeircusers'], $active_irc_users_cache, $INSTALLER09['expires']['activeircusers']);
}
if (!$active_irc_users_cache['activeircusers']) {
    $active_irc_users_cache['activeircusers'] = $lang['index_irc_nousers'];
}
$active_irc_users = '
	<fieldset class="header">
		<legend>' . $lang['index_active_irc'] . '&#160;(' . $active_irc_users_cache['actcount'] . ')</legend>
			 <div class="container-fluid">
			 <!--<a href=\'javascript: klappe_news("a1")\'><img border=\'0\' src=\'pic/plus.gif\' id=\'pica1\' alt=\'' . $lang['index_hide_show'] . '\' /></a><div id=\'ka1\' style=\'display: none;\'>-->
			  ' . $active_irc_users_cache['activeircusers'] . '
			 </div><!--</div>-->
	</fieldset>';
$HTMLOUT .= $active_irc_users;
