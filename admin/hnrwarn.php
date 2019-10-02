<?php

declare(strict_types = 1);

use Pu239\Cache;

require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_html.php';
require_once CLASS_DIR . 'class_check.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
global $site_config;

$HTMLOUT = '';
$this_url = $_SERVER['SCRIPT_NAME'];
$do = isset($_GET['do']) && $_GET['do'] === 'disabled' ? 'disabled' : 'hnrwarn';
global $container, $CURUSER;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cache = $container->get(Cache::class);
    $r = isset($_POST['ref']) ? $_POST['ref'] : $this_url;
    $_uids = isset($_POST['users']) ? array_map('intval', $_POST['users']) : 0;
    if ($_uids == 0 || count($_uids) == 0) {
        stderr(_('Error'), _("Looks like you didn't select any user!"));
    }
    $valid = [
        'unwarn',
        'disable',
        'delete',
    ];
    $act = isset($_POST['action']) && in_array($_POST['action'], $valid) ? $_POST['action'] : false;
    if (!$act) {
        stderr(_('Error'), _('Something went wrong!'));
    }
    if ($act === 'delete' && has_access($CURUSER['class'], UC_SYSOP, 'coder')) {
        $res_del = sql_query('SELECT id, username, registered, downloaded, uploaded, last_access, class, donor, warned, status FROM users WHERE id IN (' . implode(', ', $_uids) . ') ORDER BY username DESC');
        if (mysqli_num_rows($res_del) != 0) {
            $count = mysqli_num_rows($res_del);
            while ($arr_del = mysqli_fetch_assoc($res_del)) {
                $userid = $arr_del['id'];
                $res = sql_query('DELETE FROM users WHERE id=' . sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
                $cache->delete('user_' . $userid);
                write_log("User: {$arr_del['username']} Was deleted by " . $CURUSER['username'] . ' Via Hit And Run Page');
            }
        } else {
            stderr(_('Error'), _('Something went wrong!'));
        }
    }
    if ($act === 'disable') {
        if (sql_query('UPDATE users SET status = 2, modcomment=CONCAT(' . sqlesc(get_date((int) TIME_NOW, 'DATE', 1) . _(' - Disabled by ') . $CURUSER['username'] . "\n") . ',modcomment) WHERE id IN (' . implode(', ', $_uids) . ')')) {
            foreach ($_uids as $uid) {
                $cache->update_row('user_' . $uid, [
                    'status' => 2,
                ], $site_config['expires']['user_cache']);
            }
            $d = mysqli_affected_rows($mysqli);
            header('Refresh: 2; url=' . $r);
            stderr(_('Success'), _pf("%1$s user disabled", "%1$d users disable", $d));
        } else {
            stderr(_('Error'), _('Something went wrong!'));
        }
    } elseif ($act === 'unwarn') {
        $sub = _('Hit and Run Warn removed');
        $body = _('Hey, your Hit and Run warning was removed by ') . $CURUSER['username'] . _('
Please keep in your best behaviour from now on.');
        $pms = [];
        foreach ($_uids as $id) {
            $pms[] = '(2,' . $id . ',' . sqlesc($sub) . ',' . sqlesc($body) . ',' . sqlesc(TIME_NOW) . ')';
        }
        $cache->update_row('user_' . $id, [
            'hnrwarn' => 'no',
        ], $site_config['expires']['user_cache']);
        if (!empty($pms) && count($pms)) {
            $g = sql_query('INSERT INTO messages(sender,receiver,subject,msg,added) VALUE ' . implode(', ', $pms)) or sqlerr(__FILE__, __LINE__);
            $q1 = sql_query("UPDATE users SET hnrwarn='no', modcomment=CONCAT(" . sqlesc(get_date((int) TIME_NOW, 'DATE', 1) . _(' - Hit and Run Warning removed by ') . $CURUSER['username'] . "\n") . ',modcomment) WHERE id IN (' . implode(', ', $_uids) . ')') or sqlerr(__FILE__, __LINE__);
            if ($g && $q1) {
                header('Refresh: 2; url=' . $r);
                stderr(_('Success'), _pf("%1$s user HnR's warning removed", "%1$s users HnR's warning removed", count($pms)));
            } else {
                stderr(_('Error'), _('Something went wrong!'));
            }
        }
    }
    exit;
}
switch ($do) {
    case 'disabled':
        $query = "SELECT id,username, class, downloaded, uploaded, IF(downloaded>0, round((uploaded/downloaded),2), '---') AS ratio, disable_reason, registered, last_access FROM users WHERE status = 2 ORDER BY last_access DESC ";
        $title = _('Disabled users');
        $link = '<a href="staffpanel.php?tool=hnrwarn&amp;action=hnrwarn&amp;?do=warned">' . _('Hit and Run warned users') . '</a>';
        break;

    case 'hnrwarn':
        $query = "SELECT id, username, class, downloaded, uploaded, IF(downloaded>0, round((uploaded/downloaded),2), '---') AS ratio, warn_reason, hnrwarn, registered, last_access FROM users WHERE hnrwarn='yes' ORDER BY last_access DESC, hnrwarn DESC ";
        $title = _('Hit and Run Warned users');
        $link = '<a href="staffpanel.php?tool=hnrwarn&amp;action=hnrwarn&amp;do=disabled">' . _('disabled users') . '</a>';
        break;
}
$g = sql_query($query) or sqlerr(__FILE__, __LINE__);
$count = mysqli_num_rows($g);
if ($count == 0) {
    $HTMLOUT .= stdmsg(_('Hey'), _('There are no ') . strtolower($title));
} else {
    $HTMLOUT .= "<form action='staffpanel.php?tool=hnrwarn&amp;action=hnrwarn' method='post' enctype='multipart/form-data' accept-charset='utf-8'>
        <table id='checkbox_container' style='border-collapse:separate;'>
        <tr>
            <td class='colhead'>" . _('User') . "</td>
            <td class='colhead' nowrap='nowrap'>" . _('Ratio') . "</td>
            <td class='colhead' nowrap='nowrap'>" . _('Class') . "</td>
            <td class='colhead' nowrap='nowrap'>" . _('Last access') . "</td>
            <td class='colhead' nowrap='nowrap'>" . _('Joined') . "</td>
            <td class='colhead' nowrap='nowrap'><input type='checkbox' id='checkThemAll'></td>
        </tr>";
    while ($a = mysqli_fetch_assoc($g)) {
        $tip = ($do === 'hnrwarn' ? _('Hit and run Warned for: ') . htmlsafechars($a['warn_reason']) . '<br>' : _('Disabled for ') . htmlsafechars($a['disable_reason']));
        $HTMLOUT .= "<tr>
                  <td><a href='userdetails.php?id=" . (int) $a['id'] . "' class='tooltipper' title='$tip'>" . htmlsafechars($a['username']) . "</a></td>
                  <td nowrap='nowrap'>" . (float) $a['ratio'] . "<br><span class='small'><b>" . _('D:') . '</b>' . mksize($a['downloaded']) . '&#160;<b>' . _('U:') . '</b> ' . mksize($a['uploaded']) . "</span></td>
                  <td nowrap='nowrap'>" . get_user_class_name((int) $a['class']) . "</td>
                  <td nowrap='nowrap'>" . get_date((int) $a['last_access'], 'LONG', 0, 1) . "</td>
                  <td nowrap='nowrap'>" . get_date((int) $a['registered'], 'DATE', 1) . "</td>
                  <td nowrap='nowrap'><input type='checkbox' name='users[]' value='" . (int) $a['id'] . "'></td>
                </tr>";
    }
    $HTMLOUT .= "<tr>
            <td colspan='6' class='colhead'>
                <select name='action'>
                    <option value='unwarn'>" . _('Unwarn') . "</option>
                    <option value='disable'>" . _('Disable') . '</option>
                    ';
    $HTMLOUT .= "<option value='delete' " . (!has_access($CURUSER['class'], UC_ADMINISTRATOR, 'coder') ? 'disabled' : '') . '>' . _('Delete') . '</option>';
    $HTMLOUT .= "
                    </select>
                &raquo;
                <input type='submit' value='" . _('Apply') . "'>
                <input type='hidden' value='" . htmlsafechars($_SERVER['REQUEST_URI']) . "' name='ref'>
            </td>
            </tr>
            </table>
            </form>";
}
$breadcrumbs = [
    "<a href='{$site_config['paths']['baseurl']}/staffpanel.php'>" . _('Staff Panel') . '</a>',
    "<a href='{$_SERVER['PHP_SELF']}'>$title</a>",
];
echo stdhead($title, [], 'page-wrapper', $breadcrumbs) . wrapper($HTMLOUT) . stdfoot();
