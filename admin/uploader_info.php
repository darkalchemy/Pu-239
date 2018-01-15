<?php
require_once INCL_DIR . 'user_functions.php';
require_once INCL_DIR . 'pager_functions.php';
require_once CLASS_DIR . 'class_check.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
global $site_config, $lang;

$lang = array_merge($lang, load_language('ad_upinfo'));
$HTMLOUT = $count = '';
$count1 = get_row_count('torrents');
$perpage = 15;
$pager = pager($perpage, $count1, 'staffpanel.php?tool=uploader_info&amp;');
//=== main query
$res = sql_query('SELECT COUNT(t.id) AS how_many_torrents, t.owner, t.added, u.username, u.uploaded, u.downloaded, u.id, u.donor, u.suspended, u.class, u.warned, u.enabled, u.chatpost, u.leechwarn, u.pirate, u.king
            FROM torrents AS t LEFT JOIN users AS u ON u.id = t.owner GROUP BY t.owner ORDER BY how_many_torrents DESC ' . $pager['limit']);
if ($count1 > $perpage) {
    $HTMLOUT .= $pager['pagertop'];
}
$HTMLOUT .= '<table border="0" cellspacing="0" cellpadding="5">
   <tr><td class="colhead">' . $lang['upinfo_rank'] . '</td><td class="colhead">' . $lang['upinfo_torrent'] . '</td><td class="colhead">' . $lang['upinfo_member'] . '</td><td class="colhead">' . $lang['upinfo_class'] . '</td><td class="colhead">' . $lang['upinfo_ratio'] . '</td><td class="colhead">' . $lang['upinfo_ltupload'] . '</td><td class="colhead">' . $lang['upinfo_sendpm'] . '</td></tr>';
$i = 0;
while ($arr = mysqli_fetch_assoc($res)) {
    ++$i;
    $ratio = member_ratio($arr['uploaded'], $site_config['ratio_free'] ? '0' : $arr['downloaded']);
    $HTMLOUT .= '<tr>
<td>' . $i . '</td>
<td>' . (int)$arr['how_many_torrents'] . '</td>
<td>' . format_username($arr) . '</td>
<td>' . get_user_class_name($arr['class']) . '</td>
<td>' . $ratio . '</td>
<td>' . get_date($arr['added'], 'DATE', 0, 1) . '</td>
<td><a href="pm_system.php?action=send_message&amp;receiver=' . (int)$arr['id'] . '"><img src="' . $site_config['pic_baseurl'] . 'button_pm.gif" alt="' . $lang['upinfo_pm'] . '" title="' . $lang['upinfo_pm'] . '" border="0" /></a></td>
</tr>';
}
$HTMLOUT .= '</table>';
if ($count1 > $perpage) {
    $HTMLOUT .= $pager['pagerbottom'];
}
echo stdhead($lang['upinfo_stdhead']) . $HTMLOUT . stdfoot();
