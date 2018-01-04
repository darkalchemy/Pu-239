<?php
require_once INCL_DIR . 'user_functions.php';
require_once CLASS_DIR . 'class_check.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
global $site_config, $cache, $lang;

$lang = array_merge($lang, load_language('ad_namechanger'));
$HTMLOUT = '';
$mode = (isset($_GET['mode']) && htmlsafechars($_GET['mode']));
if (isset($mode) && $mode == 'change') {
    $uid = (int)$_POST['uid'];
    $uname = htmlsafechars($_POST['uname']);
    if ($_POST['uname'] == '' || $_POST['uid'] == '') {
        stderr($lang['namechanger_err'], $lang['namechanger_missing']);
    }
    $nc_sql = sql_query('SELECT class FROM users WHERE id = ' . sqlesc($uid)) or sqlerr(__FILE__, __LINE__);
    if (mysqli_num_rows($nc_sql)) {
        $classuser = mysqli_fetch_assoc($nc_sql);
        if ($classuser['class'] >= UC_STAFF) {
            stderr($lang['namechanger_err'], $lang['namechanger_cannot']);
        }
        $change = sql_query('UPDATE users SET username=' . sqlesc($uname) . ' WHERE id = ' . sqlesc($uid)) or sqlerr(__FILE__, __LINE__);
        $cache->update_row('user' . $uid, [
            'username' => $uname,
        ], $site_config['expires']['user_cache']);
        $added = TIME_NOW;
        $changed = sqlesc("{$lang['namechanger_changed_to']} $uname");
        $subject = sqlesc($lang['namechanger_changed']);
        if (!$change) {
            if (((is_object($GLOBALS['___mysqli_ston'])) ? mysqli_errno($GLOBALS['___mysqli_ston']) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)) == 1062) {
                stderr($lang['namechanger_borked'], $lang['namechanger_already_exist']);
            }
        }
        sql_query("INSERT INTO messages (sender, receiver, msg, subject, added) VALUES(0, $uid, $changed, $subject, $added)") or sqlerr(__FILE__, __LINE__);
        header('Refresh: 2; url=staffpanel.php?tool=namechanger');
        stderr($lang['namechanger_success'], $lang['namechanger_u_changed'] . htmlsafechars($uname) . $lang['namechanger_please']);
    }
}
$HTMLOUT .= "
    <h1>{$lang['namechanger_change_u']}</h1>
    <form method='post' action='staffpanel.php?tool=namechanger&amp;mode=change'>
    <table >
    <tr><td class='rowhead'>{$lang['namechanger_id']}</td><td><input type='text' name='uid' size='10' /></td></tr>
    <tr><td class='rowhead'>{$lang['namechanger_new_user']}</td><td><input type='text' name='uname' size='20' /></td></tr>
    <tr><td colspan='2'>{$lang['namechanger_if']}<input type='submit' value='{$lang['namechanger_change_name']}' class='button is-small' /></td></tr>
    </table>
    </form>";
echo stdhead($lang['namechanger_stdhead']) . $HTMLOUT . stdfoot();
