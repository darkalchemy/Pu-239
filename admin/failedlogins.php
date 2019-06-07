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
$lang = array_merge($lang, load_language('failedlogins'));
global $container, $site_config;

$session = $container->get(Session::class);
$mode = (isset($_GET['mode']) ? $_GET['mode'] : '');
$id = isset($_GET['id']) ? (int) $_GET['id'] : '';

/**
 * @param $id
 *
 * @throws Exception
 *
 * @return bool
 */
function validate($id)
{
    global $lang;

    if (!is_valid_id($id)) {
        stderr($lang['failed_sorry'], "{$lang['failed_bad_id']}");
    }

    return true;
}

if ($mode === 'ban') {
    validate($id);
    sql_query("UPDATE failedlogins SET banned = 'yes' WHERE id=" . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
    $session->set('is-warning', $lang['failed_message_ban']);
    unset($_POST);
}
if ($mode === 'removeban') {
    validate($id);
    sql_query("UPDATE failedlogins SET banned = 'no' WHERE id=" . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
    $session->set('is-success', $lang['failed_message_unban']);
    unset($_POST);
}
if ($mode === 'delete') {
    validate($id);
    sql_query('DELETE FROM failedlogins WHERE id=' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
    $session->set('is-success', $lang['failed_message_deleted']);
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
$count = $row['count'];
$perpage = 15;
$pager = pager($perpage, $count, $site_config['paths']['baseurl'] . '/staffpanel.php?tool=failedlogins&amp;action=failedlogins&amp;' . (!empty($search) ? "search=$search&amp;" : '') . '');
if (!$where && $count === 0) {
    stderr($lang['failed_main_nofail'], $lang['failed_main_nofail_msg']);
}
$HTMLOUT = main_div("
    <h1 class='has-text-centered'>{$lang['failed_main_search']}</h1>
    <form method='post' action='staffpanel.php?tool=failedlogins&amp;action=failedlogins' class='has-text-centered' accept-charset='utf-8'>
        <input type='text' name='search' class='w-50' placeholder='Search By IP' value=''><br>
        <input type='submit' value='{$lang['failed_main_search_btn']}' class='button is-small margin20'>
    </form>");
if ($count > $perpage) {
    $HTMLOUT .= $pager['pagertop'];
}
$sql = "SELECT f.*, INET6_NTOA(f.ip) AS ip, u.id as uid, u.username FROM failedlogins as f LEFT JOIN users as u ON u.ip = f.ip $where ORDER BY f.added DESC " . $pager['limit'];
$res = sql_query($sql) or sqlerr(__FILE__, __LINE__);
if (mysqli_num_rows($res) == 0) {
    $HTMLOUT .= stdmsg($lang['failed_sorry'], $lang['failed_message_nothing'], 'top20');
} else {
    $heading = "
        <tr>
            <th class='has-text-centered'>{$lang['failed_main_id']}</th>
            <th class='has-text-centered'>{$lang['failed_main_ip']}</th>
            <th class='has-text-centered'>{$lang['failed_main_added']}</th>
            <th class='has-text-centered'>{$lang['failed_main_attempts']}</th>
            <th class='has-text-centered'>{$lang['failed_main_status']}</th>
        </tr>";
    $body = '';
    while ($arr = mysqli_fetch_assoc($res)) {
        $body .= "
        <tr>
            <td class='has-text-centered'>{$arr['id']}</td>
            <td>" . htmlsafechars($arr['ip']) . ' ' . ((int) $arr['uid'] ? format_username((int) $arr['uid']) : '') . "</td>
            <td class='has-text-centered'>" . get_date((int) $arr['added'], '', 1, 0) . "</td>
            <td class='has-text-centered'>" . (int) $arr['attempts'] . '</td>
            <td>' . ($arr['banned'] === 'yes' ? "
                <span class='has-text-danger'>{$lang['failed_main_banned']}</span> 
                <a href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=failedlogins&amp;action=failedlogins&amp;mode=removeban&amp;id=" . (int) $arr['id'] . "'> 
                    <span class='is-success'>[{$lang['failed_main_remban']}]</span>
                </a>" : "
                <span class='is-success'>{$lang['failed_main_noban']}</span> 
                <a href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=failedlogins&amp;action=failedlogins&amp;mode=ban&amp;id=" . (int) $arr['id'] . "'>
                    <span class='has-text-danger'>[{$lang['failed_main_ban']}]</span>
                </a>") . "  
                <a onclick=\"return confirm('{$lang['failed_main_delmessage']}');\" href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=failedlogins&amp;action=failedlogins&amp;mode=delete&amp;id=" . (int) $arr['id'] . "'>
                    [{$lang['failed_main_delete']}]
                </a>
            </td>
        </tr>";
    }
    $HTMLOUT .= main_table($body, $heading, 'top20');
}

if ($count > $perpage) {
    $HTMLOUT .= $pager['pagerbottom'];
}
echo stdhead($lang['failed_main_logins']) . wrapper($HTMLOUT) . stdfoot();
