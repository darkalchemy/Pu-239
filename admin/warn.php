<?php

declare(strict_types = 1);

use Pu239\Cache;
use Pu239\Message;

require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_html.php';
require_once CLASS_DIR . 'class_check.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
$HTMLOUT = '';
$dt = TIME_NOW;
$this_url = $_SERVER['SCRIPT_NAME'];
$do = isset($_GET['do']) && $_GET['do'] === 'disabled' ? 'disabled' : 'warned';
global $container, $site_config;

$mysqli = $container->get(mysqli::class);
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
    global $CURUSER;

    if ($act === 'delete' && has_access($CURUSER['class'], UC_SYSOP, 'coder')) {
        $res_del = sql_query('SELECT id, username, registered, downloaded, uploaded, last_access, class, donor, warned, status FROM users WHERE id IN (' . implode(', ', $_uids) . ') ORDER BY username DESC');
        if (mysqli_num_rows($res_del) != 0) {
            $count = mysqli_num_rows($res_del);
            while ($arr_del = mysqli_fetch_assoc($res_del)) {
                $userid = $arr_del['id'];
                $res = sql_query('DELETE FROM users WHERE id=' . sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
                $cache->delete('user_' . $userid);
                write_log("User: {$arr_del['username']} Was deleted by " . $CURUSER['username'] . ' Via Warn Page');
            }
        } else {
            stderr(_('Error'), _('Something went wrong!'));
        }
    }
    if ($act === 'disable') {
        global $container;

        $cache = $container->get(Cache::class);
        if (sql_query('UPDATE users SET status = 2, modcomment=CONCAT(' . sqlesc(get_date((int) $dt, 'DATE', 1) . _('- Disabled by  ') . $CURUSER['username'] . "\n") . ',modcomment) WHERE id IN (' . implode(', ', $_uids) . ')')) {
            foreach ($_uids as $uid) {
                $cache->update_row('user_' . $uid, [
                    'status' => 2,
                ], $site_config['expires']['user_cache']);
            }
            $d = mysqli_affected_rows($mysqli);
            header('Refresh: 2; url=' . $r);
            stderr(_('Success'), _pfe('{0} user disabled!', '{0} users disabled!', $d));
        } else {
            stderr(_('Error'), _('Something went wrong!'));
        }
    } elseif ($act === 'unwarn') {
        $subject = _('Warn removed');
        $msg = _fe('Hey, your warning was removed by {0}. Please keep in your best behaviour from now on.', $CURUSER['username']);
        global $container;

        $cache = $container->get(Cache::class);
        foreach ($_uids as $id) {
            $cache->update_row('user_' . $id, [
                'warned' => 0,
            ], $site_config['expires']['user_cache']);
            $msgs_buffer[] = [
                'receiver' => $id,
                'registered' => $dt,
                'msg' => $msg,
                'subject' => $subject,
            ];
        }
        if (!empty($msgs_buffer)) {
            global $container;

            $messages_class = $container->get(Message::class);
            $messages_class->insert($msgs_buffer);
            $q1 = sql_query("UPDATE users SET warned='0', modcomment=CONCAT(" . sqlesc(get_date((int) $dt, 'DATE', 1) . _fe('Hey, your warning was removed by {0}', $CURUSER['username']) . "\n") . ',modcomment) WHERE id IN (' . implode(', ', $_uids) . ')') or ($q2_err = ((is_object($mysqli)) ? mysqli_error($mysqli) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
            if ($q1) {
                header('Refresh: 2; url=' . $r);
                stderr(_('Success'), _pfe('{0} user unwarned.', '{0} users unwarned.', count($msgs_buffer)));
            } else {
                stderr(_('Error'), _('Something went wrong!'));
            }
        }
    }
    die();
}
switch ($do) {
    case 'disabled':
        $query = "SELECT id,username, class, downloaded, uploaded, IF(downloaded>0, round((uploaded/downloaded),2), '---') AS ratio, disable_reason, registered, last_access FROM users WHERE status = 2 ORDER BY last_access DESC ";
        $title = _('Disabled users');
        $link = '<a href="staffpanel.php?tool=warn&amp;action=warn&amp;?do=warned">' . _('warned users') . '</a>';
        break;

    case 'warned':
        $query = "SELECT id, username, class, downloaded, uploaded, IF(downloaded>0, round((uploaded/downloaded),2), '---') AS ratio, warn_reason, warned, registered, last_access FROM users WHERE warned>='1' ORDER BY last_access DESC, warned DESC ";
        $title = _('Warned users');
        $link = '<a href="staffpanel.php?tool=warn&amp;action=warn&amp;do=disabled">' . _('disabled users') . '</a>';
        break;
}
$g = sql_query($query) or print (is_object($mysqli)) ? mysqli_error($mysqli) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false);
$count = mysqli_num_rows($g);
$HTMLOUT .= "
        <ul class='level-center bg-06'>
            <li class='is-link margin10'>
                $link
            </li>
        </ul>
        <h1 class='has-text-centered'>" . _pf('total - {0} user', 'total - {0} users', $count) . '</h1>';
if ($count == 0) {
    $HTMLOUT .= stdmsg(_('Error'), _fe('There is no {0}', strtolower($title)));
} else {
    global $site_config;

    $HTMLOUT .= "<form action='{$_SERVER['PHP_SELF']}?tool=warn&amp;action=warn' method='post' enctype='multipart/form-data' accept-charset='utf-8'>";
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
        $tip = ($do === 'warned' ? _fe('Warned for: {0}', $a['warn_reason']) . '<br>' . _fe('Warned utill {0} - {1}', get_date((int) $a['warned'], 'DATE', 1), mkprettytime($a['warned'] - $dt)) : _fe('Disabled for {0}', $a['disable_reason']));
        $body .= "
        <tr>
            <td><a href='userdetails.php?id=" . (int) $a['id'] . "' class='tooltipper' title='$tip'>" . htmlsafechars($a['username']) . '</a></td>
            <td>' . (float) $a['ratio'] . "<br><span class='small'><b>" . _('Downloaded') . '</b>' . mksize($a['downloaded']) . '&#160;<b>' . _('Uploaded') . '</b> ' . mksize($a['uploaded']) . '</span></td>
            <td>' . get_user_class_name((int) $a['class']) . '</td>
            <td>' . get_date((int) $a['last_access'], 'LONG', 0, 1) . '</td>
            <td>' . get_date((int) $a['registered'], 'DATE', 1) . "</td>
            <td><input type='checkbox' name='users[]' value='" . (int) $a['id'] . "'></td>
        </tr>";
    }
    $HTMLOUT .= main_table($body, $heading, null, null, 'table-striped', 'checkbox_container');
    global $CURUSER;
    $HTMLOUT .= "
        <div class='has-text-centered margin20'>
            <select name='action'>
                <option value='unwarn'>" . _('Unwarn') . "</option>
                <option value='disable'>" . _('Disable') . "</option>
                <option value='delete' " . (!has_access($CURUSER['class'], UC_SYSOP, 'coder') ? 'disabled' : '') . '>' . _('Delete') . "</option>
            </select>
            &raquo;
            <input type='submit' value='Apply' class='button is-small'>
            <input type='hidden' value='" . htmlsafechars($_SERVER['REQUEST_URI']) . "' name='ref'>
        </div>
        </form>";
}
$breadcrumbs = [
    "<a href='{$site_config['paths']['baseurl']}/staffpanel.php'>" . _('Staff Panel') . '</a>',
    "<a href='{$_SERVER['PHP_SELF']}'>$title</a>",
];
echo stdhead($title, [], 'page-wrapper', $breadcrumbs) . wrapper($HTMLOUT) . stdfoot();
