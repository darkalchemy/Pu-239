<?php

require_once INCL_DIR . 'function_users.php';
require_once CLASS_DIR . 'class_check.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
global $site_config, $lang, $cache, $mysqli;

$lang = array_merge($lang, load_language('ad_namechanger'));
$HTMLOUT = '';
$mode = (isset($_GET['mode']) && htmlsafechars($_GET['mode']));
if (isset($mode) && $mode == 'change') {
    $uid = (int) $_POST['uid'];
    $uname = htmlsafechars($_POST['uname']);
    if ($_POST['uname'] == '' || $_POST['uid'] == '') {
        stderr($lang['namechanger_err'], $lang['namechanger_missing']);
    }

    if (strlen($_POST['uname']) < 3 || !valid_username($_POST['uname'])) {
        stderr($lang['namechanger_err'], "<b>'{$_POST['uname']}'</b> {$lang['namechanger_invalid']}");
    }

    $nc_sql = sql_query('SELECT class FROM users WHERE id = ' . sqlesc($uid)) or sqlerr(__FILE__, __LINE__);
    if (mysqli_num_rows($nc_sql)) {
        $classuser = mysqli_fetch_assoc($nc_sql);
        if ($classuser['class'] >= UC_STAFF) {
            stderr($lang['namechanger_err'], $lang['namechanger_cannot']);
        }
        $change = sql_query('UPDATE users SET username =' . sqlesc($uname) . ' WHERE id = ' . sqlesc($uid)) or sqlerr(__FILE__, __LINE__);
        $cache->update_row('user' . $uid, [
            'username' => $uname,
        ], $site_config['expires']['user_cache']);
        $added = TIME_NOW;
        $changed = sqlesc("{$lang['namechanger_changed_to']} $uname");
        $subject = sqlesc($lang['namechanger_changed']);
        if (!$change) {
            if (((is_object($mysqli)) ? mysqli_errno($mysqli) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)) == 1062) {
                stderr($lang['namechanger_borked'], $lang['namechanger_already_exist']);
            }
        }
        sql_query("INSERT INTO messages (sender, receiver, msg, subject, added) VALUES(0, $uid, $changed, $subject, $added)") or sqlerr(__FILE__, __LINE__);
        header("Refresh: 2; url={$site_config['baseurl']}/staffpanel.php?tool=namechanger");
        stderr($lang['namechanger_success'], $lang['namechanger_u_changed'] . htmlsafechars($uname) . $lang['namechanger_please']);
    }
}
$HTMLOUT .= "
    <h1 class='has-text-centered'>{$lang['namechanger_change_u']}</h1>
    <form method='post' action='{$site_config['baseurl']}/staffpanel.php?tool=namechanger&amp;mode=change'>";
$body = "
    <tr>
        <td>{$lang['namechanger_id']}</td>
        <td><input type='text' name='uid' class='w-100'></td>
    </tr>
    <tr>
        <td>{$lang['namechanger_new_user']}</td>
        <td><input type='text' name='uname' class='w-100'></td>
    </tr>
    <tr>";
$HTMLOUT .= main_table($body) . "
    <div class='has-text-centered'>
        <input type='submit' value='{$lang['namechanger_change_name']}' class='button is-small margin20'>
    </div>
    </form>";
echo stdhead($lang['namechanger_stdhead']) . wrapper($HTMLOUT) . stdfoot();
