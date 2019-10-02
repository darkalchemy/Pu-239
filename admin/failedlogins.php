<?php

declare(strict_types = 1);

use Pu239\Session;

require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_html.php';
require_once INCL_DIR . 'function_pager.php';
require_once CLASS_DIR . 'class_check.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
$HTMLOUT = '';
global $container, $site_config;

$session = $container->get(Session::class);
$mode = isset($_GET['mode']) ? $_GET['mode'] : '';
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if (!is_valid_id($id)) {
    stderr(_('Error'), _('Invalid ID'));
}

if ($mode === 'ban') {
    sql_query("UPDATE failedlogins SET banned = 'yes' WHERE id = " . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
    $session->set('is-warning', _('Member banned'));
    unset($_POST);
}
if ($mode === 'removeban') {
    sql_query("UPDATE failedlogins SET banned = 'no' WHERE id = " . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
    $session->set('is-success', _('Ip ban Removed'));
    unset($_POST);
}
if ($mode === 'delete') {
    sql_query('DELETE FROM failedlogins WHERE id = ' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
    $session->set('is-success', _('Entry deleted'));
    unset($_POST);
}

$where = '';
$search = isset($_POST['search']) ? strip_tags($_POST['search']) : '';
if (isset($_GET['search'])) {
    $search = strip_tags($_GET['search']);
}
if (!$search) {
    $where = '';
} else {
    $where = 'WHERE INET6_NTOA(f.ip) = ' . sqlesc($search);
}

$sql = "SELECT COUNT(id) AS count FROM failedlogins AS f $where";
$res = sql_query($sql) or sqlerr(__FILE__, __LINE__);
$row = mysqli_fetch_assoc($res);
$count = (int) $row['count'];
$perpage = 15;
$pager = pager($perpage, $count, $site_config['paths']['baseurl'] . '/staffpanel.php?tool=failedlogins&amp;action=failedlogins&amp;' . (!empty($search) ? "search=$search&amp;" : '') . '');
if (!$where && $count === 0) {
    stderr(_('No Failed Logins'), _('It appears that there are currently no failed logins matching your search criteria.'));
}
$HTMLOUT = main_div("
    <h1 class='has-text-centered'>" . _('Search Failed Logins') . "</h1>
    <form method='post' action='staffpanel.php?tool=failedlogins&amp;action=failedlogins' class='has-text-centered' enctype='multipart/form-data' accept-charset='utf-8'>
        <input type='text' name='search' class='w-50' placeholder='Search By IP' value=''><br>
        <input type='submit' value='" . _('Search') . "' class='button is-small margin20'>
    </form>");
if ($count > $perpage) {
    $HTMLOUT .= $pager['pagertop'];
}
$sql = "SELECT f.*, INET6_NTOA(f.ip) AS ip, u.id as uid, u.username FROM failedlogins as f LEFT JOIN users as u ON u.ip = f.ip $where ORDER BY f.added DESC " . $pager['limit'];
$res = sql_query($sql) or sqlerr(__FILE__, __LINE__);
if (mysqli_num_rows($res) == 0) {
    $HTMLOUT .= stdmsg(_('Sorry'), _('Nothing found!'), 'top20');
} else {
    $heading = "
        <tr>
            <th class='has-text-centered'>" . _('ID') . "</th>
            <th class='has-text-centered'>" . _('Ip Address') . "</th>
            <th class='has-text-centered'>" . _('Added') . "</th>
            <th class='has-text-centered'>" . _('Attempts') . "</th>
            <th class='has-text-centered'>" . _('Status') . '</th>
        </tr>';
    $body = '';
    while ($arr = mysqli_fetch_assoc($res)) {
        $body .= "
        <tr>
            <td class='has-text-centered'>{$arr['id']}</td>
            <td>" . htmlsafechars($arr['ip']) . ' ' . ((int) $arr['uid'] ? format_username((int) $arr['uid']) : '') . "</td>
            <td class='has-text-centered'>" . get_date((int) $arr['added'], '', 1, 0) . "</td>
            <td class='has-text-centered'>" . (int) $arr['attempts'] . '</td>
            <td>' . ($arr['banned'] === 'yes' ? "
                <span class='has-text-danger'>" . _('Banned') . "</span> 
                <a href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=failedlogins&amp;action=failedlogins&amp;mode=removeban&amp;id=" . (int) $arr['id'] . "'> 
                    <span class='is-success'>[" . _('Remove ban') . ']</span>
                </a>' : "
                <span class='is-success'>" . _('Not banned') . "</span> 
                <a href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=failedlogins&amp;action=failedlogins&amp;mode=ban&amp;id=" . (int) $arr['id'] . "'>
                    <span class='has-text-danger'>[" . _('Ban') . ']</span>
                </a>') . "  
                <a onclick=\"return confirm('" . _('Are you wish to delete this attempt?') . "');\" href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=failedlogins&amp;action=failedlogins&amp;mode=delete&amp;id=" . (int) $arr['id'] . "'>
                    [" . _('Delete') . ']
                </a>
            </td>
        </tr>';
    }
    $HTMLOUT .= main_table($body, $heading, 'top20');
}

if ($count > $perpage) {
    $HTMLOUT .= $pager['pagerbottom'];
}
$title = _('Failed Logins');
$breadcrumbs = [
    "<a href='{$site_config['paths']['baseurl']}/staffpanel.php'>" . _('Staff Panel') . '</a>',
    "<a href='{$_SERVER['PHP_SELF']}'>$title</a>",
];
echo stdhead($title, [], 'page-wrapper', $breadcrumbs) . wrapper($HTMLOUT) . stdfoot();
