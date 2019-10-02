<?php

declare(strict_types = 1);

use Pu239\Cache;

require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_html.php';
require_once CLASS_DIR . 'class_check.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
global $container, $site_config, $CURUSER;

$HTMLOUT = '';
$this_url = $_SERVER['SCRIPT_NAME'];
$do = isset($_GET['do']) && $_GET['do'] === 'disabled' ? 'disabled' : 'leechwarn';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
    $cache = $container->get(Cache::class);
    if ($act === 'delete' && has_access($CURUSER['class'], UC_SYSOP, 'coder')) {
        $res_del = sql_query('SELECT id, username, registered, downloaded, uploaded, last_access, class, donor, warned, status FROM users WHERE id IN (' . implode(', ', $_uids) . ') ORDER BY username DESC');
        if (mysqli_num_rows($res_del) != 0) {
            $count = mysqli_num_rows($res_del);
            while ($arr_del = mysqli_fetch_assoc($res_del)) {
                $userid = $arr_del['id'];
                $res = sql_query('DELETE FROM users WHERE id=' . sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
                $cache->delete('user_' . $userid);
                write_log("User: {$arr_del['username']} Was deleted by " . $CURUSER['username'] . ' Via Leech Warn Page');
            }
        } else {
            stderr(_('Error'), _('Something went wrong2!'));
        }
    }
    if ($act === 'disable') {
        if (sql_query('UPDATE users SET status = 2, modcomment = CONCAT(' . sqlesc(get_date((int) TIME_NOW, 'DATE', 1) . _(' - Disabled by ') . $CURUSER['username'] . "\n") . ',modcomment) WHERE id IN (' . implode(', ', $_uids) . ')')) {
            foreach ($_uids as $uid) {
                $cache->update_row('user_' . $uid, [
                    'status' => 2,
                ], $site_config['expires']['user_cache']);
            }
            $c = mysqli_affected_rows($mysqli);
            header('Refresh: 2; url=' . $r);
            stderr(_('Success'), $c . _(' user') . ($c > 1 ? _('s') : '') . _(' disabled!'));
        } else {
            stderr(_('Error'), _('Something went wrong3!'));
        }
    } elseif ($act === 'unwarn') {
        $sub = _('Leech Warn removed');
        $body = _('Hey, your Leech warning was removed by ') . $CURUSER['username'] . _('
Please keep in your best behaviour from now on.');
        $pms = [];
        foreach ($_uids as $uid) {
            $cache->update_row('user_' . $uid, [
                'leechwarn' => 0,
            ], $site_config['expires']['user_cache']);
            $pms[] = '(2,' . $uid . ', ' . sqlesc($sub) . ', ' . sqlesc($body) . ', ' . sqlesc(TIME_NOW) . ')';
        }
        if (!empty($pms) && count($pms)) {
            $g = sql_query('INSERT INTO messages(sender,receiver,subject,msg,added) VALUE ' . implode(', ', $pms)) or ($q_err = ((is_object($mysqli)) ? mysqli_error($mysqli) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
            $q1 = sql_query("UPDATE users SET leechwarn='0', modcomment=CONCAT(" . sqlesc(get_date((int) TIME_NOW, 'DATE', 1) . _(' - Leech Warning removed by ') . $CURUSER['username'] . "\n") . ',modcomment) WHERE id IN (' . implode(', ', $_uids) . ')') or ($q2_err = ((is_object($mysqli)) ? mysqli_error($mysqli) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
            if ($g && $q1) {
                header('Refresh: 2; url=' . $r);
                stderr(_('Success'), count($pms) . _(' user') . (count($pms) > 1 ? _('s') : '') . _(' Leech warning removed'));
            } else {
                stderr(_('Error'), _('Something went wrong! Q1 - ') . $q_err . '<br>' . _('Q2 - ') . '' . $q2_err);
            }
        }
    }
    exit;
}
switch ($do) {
    case 'disabled':
        $query = "SELECT id,username, class, downloaded, uploaded, IF(downloaded>0, round((uploaded/downloaded),2), '---') AS ratio, disable_reason, registered, last_access FROM users WHERE status = 2 ORDER BY last_access DESC ";
        $title = _('Disabled users');
        $link = "<a href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=leechwarn&amp;action=leechwarn&amp;?do=warned'>" . _('Leech warned users') . '</a>';
        break;

    case 'leechwarn':
        $query = "SELECT id, username, class, downloaded, uploaded, IF(downloaded>0, round((uploaded/downloaded),2), '---') AS ratio, warn_reason, leechwarn, registered, last_access FROM users WHERE leechwarn>='1' ORDER BY last_access DESC, leechwarn DESC ";
        $title = _('Leech Warned users');
        $link = "<a href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=leechwarn&amp;action=leechwarn&amp;do=disabled'>" . _('disabled users') . '</a>';
        break;
}
$g = sql_query($query) or print (is_object($mysqli)) ? mysqli_error($mysqli) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false);
$count = mysqli_num_rows($g);
$HTMLOUT .= "
    <ul class='level-center bg-06'>
        <li class='is-link margin10'>
            $link
        </li>
    </ul>";
$HTMLOUT .= "<h2 class='has-text-centered'>" . _('total - ') . " $count " . _(' user') . ' ' . ($count > 1 ? _('s') : '') . '</h2>';
if ($count == 0) {
    $HTMLOUT .= stdmsg(_('Hey'), _('There is no ') . strtolower($title));
} else {
    $HTMLOUT .= "
    <form action='{$_SERVER['PHP_SELF']}?tool=leechwarn&amp;action=leechwarn' method='post' enctype='multipart/form-data' accept-charset='utf-8'>";
    $heading = '
        <tr>
            <th>' . _('User') . '</th>
            <th>' . _('Ratio') . '</th>
            <th>' . _('Class') . '</th>
            <th>' . _('Last access') . '</th>
            <th>' . _('Joined') . "</th>
            <th><input type='checkbox' id='checkThemAll'></th>
        </tr>";
    $body = '';
    while ($a = mysqli_fetch_assoc($g)) {
        $tip = ($do === 'leechwarn' ? _('Leech Warned for: ') . htmlsafechars($a['warn_reason']) . '<br>' . _(' Warned till ') . get_date((int) $a['leechwarn'], 'DATE', 1) . ' - ' . mkprettytime($a['leechwarn'] - TIME_NOW) : _('Disabled for ') . htmlsafechars($a['disable_reason']));
        $body .= "
        <tr>
            <td><a href='userdetails.php?id=" . (int) $a['id'] . "' class='tooltipper' title='$tip'>" . htmlsafechars($a['username']) . '</a></td>
            <td>' . (float) $a['ratio'] . "<br><span class='small'><b>" . _('D: ') . '</b>' . mksize($a['downloaded']) . '&#160;<b>' . _('U: ') . '</b> ' . mksize($a['uploaded']) . '</span></td>
            <td>' . get_user_class_name((int) $a['class']) . '</td>
            <td>' . get_date((int) $a['last_access'], 'LONG', 0, 1) . '</td>
            <td>' . get_date((int) $a['registered'], 'DATE', 1) . "</td>
            <td><input type='checkbox' name='users[]' value='" . (int) $a['id'] . "'></td>
        </tr>";
    }
    $HTMLOUT .= main_table($body, $heading, null, null, 'table-striped', 'checkbox_container');
    $HTMLOUT .= "
        <div class='has-text-centered margin20'>
            <select name='action'>
                <option value='unwarn'>" . _('Unwarn') . "</option>
                <option value='disable'>" . _('Disable') . "</option>
                <option value='delete' " . (!has_access($CURUSER['class'], UC_SYSOP, 'coder') ? 'disabled' : '') . '>' . _('Delete') . "</option>
            </select>
                &raquo;
            <input type='submit' value='" . _('Apply') . "' class='button is-small'>
            <input type='hidden' value='" . htmlsafechars($_SERVER['REQUEST_URI']) . "' name='ref'>
        </div>
    </form>";
}
$breadcrumbs = [
    "<a href='{$site_config['paths']['baseurl']}/staffpanel.php'>" . _('Staff Panel') . '</a>',
    "<a href='{$_SERVER['PHP_SELF']}'>$title</a>",
];
echo stdhead($title, [], 'page-wrapper', $breadcrumbs) . wrapper($HTMLOUT) . stdfoot();
