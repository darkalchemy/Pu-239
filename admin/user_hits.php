<?php

declare(strict_types = 1);

use Pu239\User;

require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_pager.php';
require_once CLASS_DIR . 'class_check.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
global $container, $site_config, $CURUSER;
stderr(_('Error'), 'This page is not in use atm');
$HTMLOUT = '';
$id = (int) $_GET['id'];
if (!is_valid_id($id) || $CURUSER['id'] != $id && $CURUSER['class'] < UC_STAFF) {
    $id = $CURUSER['id'];
}
$res = sql_query('SELECT COUNT(id) FROM userhits WHERE hitid = ' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
$row = mysqli_fetch_row($res);
$count = $row[0];
$perpage = 15;
$pager = pager($perpage, $count, "staffpanel.php?tool=user_hits&amp;id=$id&amp;");
if (!$count) {
    stderr(_('No views'), _('This user has had no profile views yet.'));
}
$users_class = $container->get(User::class);
$user = $users_class->getUserFromId($id);
$res = sql_query('SELECT username FROM users WHERE id=' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
$user = mysqli_fetch_assoc($res);
$HTMLOUT .= '<h1>' . _('Profile views of ') . '' . format_username((int) $id) . '</h1>
<h2>' . _('In total ') . '' . htmlsafechars($count) . '' . _(' views') . '</h2>';
if ($count > $perpage) {
    $HTMLOUT .= $pager['pagertop'];
}
$HTMLOUT .= "
<table>
<tr>
<td class='colhead'>" . _('Nr.') . "</td>
<td class='colhead'>" . _('Username') . "</td>
<td class='colhead'>" . _('Viewed at') . "</td>
</tr>\n";
$res = sql_query('SELECT uh.*, username, users.id AS uid FROM userhits uh LEFT JOIN users ON uh.userid=users.id WHERE hitid =' . sqlesc($id) . ' ORDER BY uh.id DESC ' . $pager['limit']) or sqlerr(__FILE__, __LINE__);
while ($arr = mysqli_fetch_assoc($res)) {
    $HTMLOUT .= '
<tr><td>' . number_format($arr['number']) . '</td>
<td>' . format_username((int) $arr['uid']) . '</td>
<td>' . get_date((int) $arr['added'], 'DATE', 0, 1) . "</td>
</tr>\n";
}
$HTMLOUT .= '</table>';
if ($count > $perpage) {
    $HTMLOUT .= $pager['pagerbottom'];
}
$title = _('Profile Views');
$breadcrumbs = [
    "<a href='{$site_config['paths']['baseurl']}/staffpanel.php'>" . _('Staff Panel') . '</a>',
    "<a href='{$_SERVER['PHP_SELF']}'>$title</a>",
];
echo stdhead($title, [], 'page-wrapper', $breadcrumbs) . wrapper($HTMLOUT) . stdfoot();
