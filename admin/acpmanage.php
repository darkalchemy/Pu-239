<?php

declare(strict_types = 1);

use Pu239\Database;

require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_html.php';
require_once INCL_DIR . 'function_pager.php';
require_once CLASS_DIR . 'class_check.php';
require_once INCL_DIR . 'function_account_delete.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
$stdfoot = [
    'js' => [
        get_file_name('acp_js'),
    ],
];
$HTMLOUT = '';
global $container, $CURUSER, $site_config;

$fluent = $container->get(Database::class);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ids'])) {
    $ids = $_POST['ids'];
    foreach ($ids as $id) {
        $id = (int) $id;
        if (!is_valid_id($id)) {
            stderr(_('Error'), _('Invalid Credentials.'));
        }
    }
    $do = isset($_POST['do']) ? htmlsafechars(trim($_POST['do'])) : '';
    if ($do == 'enabled') {
        sql_query('UPDATE users SET status = 0 WHERE id IN (' . implode(', ', array_map('sqlesc', $ids)) . ') AND status = 2') or sqlerr(__FILE__, __LINE__);
        $cache->update_row('user_' . $id, [
            'status' => 0,
        ], $site_config['expires']['user_cache']);
    } elseif ($do == 'confirm') {
        sql_query('UPDATE users SET verified = 1 WHERE id IN (' . implode(', ', array_map('sqlesc', $ids)) . ') AND verified = 0') or sqlerr(__FILE__, __LINE__);
        $cache->update_row('user_' . $id, [
            'status' => 'confirmed',
        ], $site_config['expires']['user_cache']);
    } elseif ($do == 'delete' && ($CURUSER['class'] >= UC_MAX)) {
        foreach ($ids as $id) {
            $username = account_delete((int) $id);
            if ($username) {
                write_log(_fe('User: {0} was deleted by {1}', $username, $CURUSER['username']));
                $session->set('is-success', _('The account was deleted.'));
            } else {
                stderr(_('Error'), _('Unable to delete the account.'));
            }
        }
        $session->set('is-success', _('The account was deleted.'));
    }
    header('Location: ' . $_SERVER['PHP_SELF'] . '?tool=acpmanage&amp;action=acpmanage');
    exit;
}
$disabled = $fluent->from('users')
                   ->select(null)
                   ->select('COUNT(id) AS count')
                   ->where('status = 2')
                   ->fetch('count');
$pending = $fluent->from('users')
                  ->select(null)
                  ->select('COUNT(id) AS count')
                  ->where('verified = 0')
                  ->fetch('count');
$count = $fluent->from('users')
                ->select(null)
                ->select('COUNT(id) AS count')
                ->where('status = 2 OR verified = 0')
                ->fetch('count');
$disabled = number_format($disabled);
$pending = number_format($pending);
$perpage = 25;
$pager = pager($perpage, $count, 'staffpanel.php?tool=acpmanage&amp;action=acpmanage&amp;');
$res = sql_query("SELECT id, username, registered, downloaded, uploaded, last_access, class, donor, warned, status, verified FROM users WHERE status = 2 OR verified = 0 ORDER BY username DESC {$pager['limit']}");
if (mysqli_num_rows($res) != 0) {
    if ($count > $perpage) {
        $HTMLOUT .= $pager['pagertop'];
    }
    $HTMLOUT .= "<form action='{$_SERVER['PHP_SELF']}?tool=acpmanage&amp;action=acpmanage' method='post' enctype='multipart/form-data' accept-charset='utf-8'>";
    $HTMLOUT .= begin_table();
    $HTMLOUT .= "<tr><td class='colhead'>
      <input class='is-marginless' type='checkbox' title='" . _('Mark All') . "' value='" . _('Mark All') . "' onclick=\"this.value=check(form);\"></td>
      <td class='colhead'>" . _('Username') . "</td>
      <td class='colhead has-no-wrap'>" . _('Registered') . "</td>
      <td class='colhead has-no-wrap'>" . _('Last access') . "</td>
      <td class='colhead'>" . _('Class') . "</td>
      <td class='colhead'>" . _('Downloaded') . "</td>
      <td class='colhead'>" . _('Uploaded') . "</td>
      <td class='colhead'>" . _('Ratio') . "</td>
      <td class='colhead'>" . _('Status') . "</td>
      <td class='colhead has-no-wrap'>" . _('Enabled') . '</td>
      </tr>';
    while ($arr = mysqli_fetch_assoc($res)) {
        $uploaded = mksize($arr['uploaded']);
        $downloaded = mksize($arr['downloaded']);
        $ratio = $arr['downloaded'] > 0 ? $arr['uploaded'] / $arr['downloaded'] : 0;
        $color = get_ratio_color($ratio);
        if ($color) {
            $ratio = "<span style='color: $color;'>" . number_format($ratio, 2) . '</span>';
        }
        $added = get_date((int) $arr['registered'], 'LONG', 0, 1);
        $last_access = get_date((int) $arr['last_access'], 'LONG', 0, 1);
        $class = get_user_class_name((int) $arr['class']);
        $status = $arr['status'] == 0 ? 'Enabled' : 'Disabled';
        $enabled = $arr['status'] == 0 ? 'Enabled' : 'Disabled';
        $HTMLOUT .= "
        <tr>
            <td>
                <input type='checkbox' name='ids[]' value='{$arr['id']}'>
            </td>
            <td>" . format_username((int) $arr['id']) . "</td>
            <td class='has-no-wrap'>{$added}</td>
            <td class='has-no-wrap'>{$last_access}</td>
            <td>{$class}</td>
            <td>{$downloaded}</td>
            <td>{$uploaded}</td>
            <td>{$ratio}</td>
            <td>{$status}</td>
            <td>{$enabled}</td>
        </tr>";
    }
    if (($CURUSER['class'] >= UC_MAX)) {
        $HTMLOUT .= "<tr><td colspan='10' class='has-text-centered'><select name='do'><option value='enabled' disabled selected>" . _('What to do?') . "</option><option value='enabled'>" . _('Enabled selected') . "</option><option value='confirm'>" . _('Confirm selected') . "</option><option value='delete'>" . _('Delete selected') . "</option></select><br><input type='submit' class='margin20 button is-small' value='" . _('Submit') . "'></td></tr>";
    } else {
        $HTMLOUT .= "<tr><td colspan='10' class='has-text-centered'><select name='do'><option value='enabled' disabled selected>" . _('What to do?') . "</option><option value='enabled'>" . _('Enabled selected') . "</option><option value='confirm'>" . _('Confirm selected') . "</option></select><br><input type='submit' class='margin20 button is-small' value='" . _('Submit') . "'></td></tr>";
    }

    $HTMLOUT .= end_table();
    $HTMLOUT .= '</form>';
    if ($count > $perpage) {
        $HTMLOUT .= $pager['pagerbottom'];
    }
} else {
    $HTMLOUT = stdmsg('<h2>' . _('Sorry') . '</h2>', '<p>' . _('Nothing found!') . '</p>');
}
$title = _('Account Manager');
$breadcrumbs = [
    "<a href='{$site_config['paths']['baseurl']}/staffpanel.php'>" . _('Staff Panel') . '</a>',
    "<a href='{$_SERVER['PHP_SELF']}'>$title</a>",
];
echo stdhead($title, [], 'page-wrapper', $breadcrumbs) . wrapper($HTMLOUT) . stdfoot();
