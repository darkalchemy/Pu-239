<?php

declare(strict_types = 1);

require_once INCL_DIR . 'function_users.php';
require_once CLASS_DIR . 'class_check.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
global $site_config;

$HTMLOUT = '';
$mode = (isset($_GET['mode']) && htmlsafechars($_GET['mode']));
if (isset($mode) && $mode == 'change') {
    $uid = (int) $_POST['uid'];
    $uname = htmlsafechars($_POST['uname']);
    if ($_POST['uname'] == '' || $_POST['uid'] == '') {
        stderr(_('Error'), _('UserName or ID missing'));
    }

    if (strlen($_POST['uname']) < 3 || !valid_username($_POST['uname'])) {
        stderr(_('Error'), "<b>'{$_POST['uname']}'</b> " . _('is invalid') . '');
    }

    $nc_sql = sql_query('SELECT class FROM users WHERE id=' . sqlesc($uid)) or sqlerr(__FILE__, __LINE__);
    if (mysqli_num_rows($nc_sql)) {
        $classuser = mysqli_fetch_assoc($nc_sql);
        if ($classuser['class'] >= UC_STAFF) {
            stderr(_('Error'), _('Cannot rename staff accounts, contact coder.'));
        }
        $change = sql_query('UPDATE users SET username =' . sqlesc($uname) . ' WHERE id=' . sqlesc($uid)) or sqlerr(__FILE__, __LINE__);
        $cache->update_row('user_' . $uid, [
            'username' => $uname,
        ], $site_config['expires']['user_cache']);
        $added = TIME_NOW;
        $changed = sqlesc(_('Your Username Has Been Changed To') . " $uname");
        $subject = sqlesc(_('Username changed'));
        if (!$change) {
            if (((is_object($mysqli)) ? mysqli_errno($mysqli) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)) == 1062) {
                stderr(_('Borked'), _('Username already exists!'));
            }
        }
        sql_query("INSERT INTO messages (sender, receiver, msg, subject, added) VALUES(2, $uid, $changed, $subject, $added)") or sqlerr(__FILE__, __LINE__);
        header("Refresh: 2; url={$site_config['paths']['baseurl']}/staffpanel.php?tool=namechanger");
        stderr(_('Success'), _('Username Has Been Changed To ') . htmlsafechars($uname) . _(' please wait while you are redirected'));
    }
}
$HTMLOUT .= "
    <h1 class='has-text-centered'>" . _('Change UserName') . "</h1>
    <form method='post' action='{$_SERVER['PHP_SELF']}?tool=namechanger&amp;mode=change' enctype='multipart/form-data' accept-charset='utf-8'>";
$body = '
    <tr>
        <td>' . _('ID: ') . "</td>
        <td><input type='text' name='uid' class='w-100'></td>
    </tr>
    <tr>
        <td>" . _('New Username: ') . "</td>
        <td><input type='text' name='uname' class='w-100'></td>
    </tr>";
$HTMLOUT .= main_table($body) . "
    <div class='has-text-centered'>
        <input type='submit' value='" . _('Change Name!') . "' class='button is-small margin20'>
    </div>
    </form>";
$title = _('Change Username');
$breadcrumbs = [
    "<a href='{$site_config['paths']['baseurl']}/staffpanel.php'>" . _('Staff Panel') . '</a>',
    "<a href='{$_SERVER['PHP_SELF']}'>$title</a>",
];
echo stdhead($title, [], 'page-wrapper', $breadcrumbs) . wrapper($HTMLOUT) . stdfoot();
