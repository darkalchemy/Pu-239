<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'user_functions.php';
check_user_status();
global $CURUSER, $site_config;

$lang = array_merge(load_language('global'), load_language('achievement_history'));
$HTMLOUT = '';
$id = (int)$_GET['id'];
if (!is_valid_id($id)) {
    stderr($lang['achievement_history_err'], $lang['achievement_history_err1']);
}
$res = sql_query('SELECT u.id, u.username, a.achpoints, a.spentpoints FROM users AS u LEFT JOIN usersachiev AS a ON u.id = a.userid WHERE u.id = ' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
$arr = mysqli_fetch_assoc($res);
if (!$arr) {
    stderr($lang['achievement_history_err'], $lang['achievement_history_err1']);
}
$achpoints = (int)$arr['achpoints'];
$spentpoints = (int)$arr['spentpoints'];
$res = sql_query('SELECT COUNT(*) FROM achievements WHERE userid =' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
$row = mysqli_fetch_row($res);
$count = $row[0];
$perpage = 15;
if (!$count) {
    stderr($lang['achievement_history_no'], "{$lang['achievement_history_err2']}<a class='altlink' href='userdetails.php?id=" . (int)$arr['id'] . "'>" . htmlsafechars($arr['username']) . "</a>{$lang['achievement_history_err3']}");
}
$pager = pager($perpage, $count, "?id=$id&amp;");
if ($id == $CURUSER['id']) {
    $HTMLOUT .= "
	<div class='row-fluid'>
		<span class='button is-small'>
		<a href='/achievementlist.php'>{$lang['achievement_history_al']}</a></span>
		<span class='button is-small'>
		<a href='/postcounter.php'>{$lang['achievement_history_fpc']}</a></span>
		<span class='button is-small'>
		<a href='/topiccounter.php'>{$lang['achievement_history_ftc']}</a></span>
		<span class='button is-small'>
		<a href='/invitecounter.php'>{$lang['achievement_history_ic']}</a></span>
	</div>
	";
}
$HTMLOUT .= "
	<div class='row-fluid'>
		<fieldset>
			<legend>
			{$lang['achievement_history_afu']}
			<a class='altlink' href='{$site_config['baseurl']}/userdetails.php?id=" . (int)$arr['id'] . "'>" . htmlsafechars($arr['username']) . "</a><br>
			{$lang['achievement_history_c']}" . htmlsafechars($row['0']) . "{$lang['achievement_history_a']}" . ($row[0] == 1 ? '' : 's') . '.';
if ($id == $CURUSER['id']) {
    $HTMLOUT .= "
			<a class='altlink' href='achievementbonus.php'>" . htmlsafechars($achpoints) . "{$lang['achievement_history_pa']}" . htmlsafechars($spentpoints) . "{$lang['achievement_history_ps']}</a>
			</legend>
		";
}
if ($count > $perpage) {
    $HTMLOUT .= $pager['pagertop'];
}
$HTMLOUT .= "
			<div class='row-fluid'>
				<table class='table table-bordered '>
					<thead>
						<tr>
							<th>{$lang['achievement_history_award']}</th>
							<th>{$lang['achievement_history_descr']}</th>
							<th>{$lang['achievement_history_date']}</th>
						</tr>
					</thead>
					";
$res = sql_query('SELECT * FROM achievements WHERE userid=' . sqlesc($id) . " ORDER BY date DESC {$pager['limit']}") or sqlerr(__FILE__, __LINE__);
while ($arr = mysqli_fetch_assoc($res)) {
    $HTMLOUT .= "
						<tr>
							<td><img src='./images/achievements/" . htmlsafechars($arr['icon']) . "' alt='" . htmlsafechars($arr['achievement']) . "' title='" . htmlsafechars($arr['achievement']) . "' /></td>
							<td>" . htmlsafechars($arr['description']) . '</td>
							<td>' . get_date($arr['date'], '') . '</td>
						</tr>
				';
}
$HTMLOUT .= '
				</table>
			</div>
		</fieldset>
	</div>
	';
if ($count > $perpage) {
    $HTMLOUT .= $pager['pagerbottom'];
}
echo stdhead($lang['achievement_history_stdhead']) . $HTMLOUT . stdfoot();
die;
