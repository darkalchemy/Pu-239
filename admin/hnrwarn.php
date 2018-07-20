<?php

require_once INCL_DIR . 'user_functions.php';
require_once INCL_DIR . 'html_functions.php';
require_once CLASS_DIR . 'class_check.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
global $CURUSER, $site_config, $lang, $cache;

$lang = array_merge($lang, load_language('ad_hnrwarn'));
$HTMLOUT = '';
/**
 * @param $x
 *
 * @return int
 */
function mkint($x)
{
    return (int) $x;
}

$this_url = $_SERVER['SCRIPT_NAME'];
$do = isset($_GET['do']) && $_GET['do'] === 'disabled' ? 'disabled' : 'hnrwarn';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $r = isset($_POST['ref']) ? $_POST['ref'] : $this_url;
    $_uids = isset($_POST['users']) ? array_map('mkint', $_POST['users']) : 0;
    if ($_uids == 0 || count($_uids) == 0) {
        stderr($lang['hnrwarn_stderror'], $lang['hnrwarn_nouser']);
    }
    $valid = [
        'unwarn',
        'disable',
        'delete',
    ];
    $act = isset($_POST['action']) && in_array($_POST['action'], $valid) ? $_POST['action'] : false;
    if (!$act) {
        stderr($lang['hnrwarn_stderror'], $lang['hnrwarn_wrong']);
    }
    if ($act === 'delete' && ($CURUSER['class'] >= UC_SYSOP)) {
        $res_del = sql_query('SELECT id, username, added, downloaded, uploaded, last_access, class, donor, warned, enabled, status FROM users WHERE id IN (' . implode(', ', $_uids) . ') ORDER BY username DESC');
        if (mysqli_num_rows($res_del) != 0) {
            $count = mysqli_num_rows($res_del);
            while ($arr_del = mysqli_fetch_assoc($res_del)) {
                $userid = $arr_del['id'];
                $res = sql_query('DELETE FROM users WHERE id=' . sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
                $cache->delete('user' . $userid);
                write_log("User: {$arr_del['username']} Was deleted by " . $CURUSER['username'] . ' Via Hit And Run Page');
            }
        } else {
            stderr($lang['hnrwarn_stderror'], $lang['hnrwarn_wrong']);
        }
    }
    if ($act === 'disable') {
        if (sql_query("UPDATE users SET enabled='no', modcomment=CONCAT(" . sqlesc(get_date(TIME_NOW, 'DATE', 1) . $lang['hnrwarn_disabled'] . $CURUSER['username'] . "\n") . ',modcomment) WHERE id IN (' . implode(', ', $_uids) . ')')) {
            foreach ($_uids as $uid) {
                $cache->update_row('user' . $uid, [
                    'enabled' => 'no',
                ], $site_config['expires']['user_cache']);
            }
            $d = mysqli_affected_rows($GLOBALS['___mysqli_ston']);
            header('Refresh: 2; url=' . $r);
            stderr($lang['hnrwarn_success'], $d . $lang['hnrwarn_user'] . ($d > 1 ? $lang['hnrwarn_s'] : '') . ' disabled!');
        } else {
            stderr($lang['hnrwarn_stderror'], $lang['hnrwarn_wrong3']);
        }
    } elseif ($act === 'unwarn') {
        $sub = $lang['hnrwarn_removed'];
        $body = $lang['hnrwarn_msg1'] . $CURUSER['username'] . $lang['hnrwarn_msg2'];
        $pms = [];
        foreach ($_uids as $id) {
            $pms[] = '(0,' . $id . ',' . sqlesc($sub) . ',' . sqlesc($body) . ',' . sqlesc(TIME_NOW) . ')';
        }
        $cache->update_row('user' . $id, [
            'hnrwarn' => 'no',
        ], $site_config['expires']['user_cache']);
        if (!empty($pms) && count($pms)) {
            $g = sql_query('INSERT INTO messages(sender,receiver,subject,msg,added) VALUE ' . implode(', ', $pms)) or sqlerr(__FILE__, __LINE__);
            $q1 = sql_query("UPDATE users SET hnrwarn='no', modcomment=CONCAT(" . sqlesc(get_date(TIME_NOW, 'DATE', 1) . $lang['hnrwarn_rem_log'] . $CURUSER['username'] . "\n") . ',modcomment) WHERE id IN (' . implode(', ', $_uids) . ')') or sqlerr(__FILE__, __LINE__);
            if ($g && $q1) {
                header('Refresh: 2; url=' . $r);
                stderr($lang['hnrwarn_success'], count($pms) . $lang['hnrwarn_user'] . (count($pms) > 1 ? 's' : '') . $lang['hnrwarn_rem_suc']);
            } else {
                stderr($lang['hnrwarn_stderror'], $lang['hnrwarn_q1'] . $q_err . "<br>{$lang['hnrwarn_q2']}" . $q1_err);
            }
        }
    }
    exit;
}
switch ($do) {
    case 'disabled':
        $query = "SELECT id,username, class, downloaded, uploaded, IF(downloaded>0, round((uploaded/downloaded),2), '---') AS ratio, disable_reason, added, last_access FROM users WHERE enabled='no' ORDER BY last_access DESC ";
        $title = $lang['hnrwarn_disabled_title'];
        $link = "<a href=\"staffpanel.php?tool=hnrwarn&amp;action=hnrwarn&amp;?do=warned\">{$lang['hnrwarn_users']}</a>";
        break;

    case 'hnrwarn':
        $query = "SELECT id, username, class, downloaded, uploaded, IF(downloaded>0, round((uploaded/downloaded),2), '---') AS ratio, warn_reason, hnrwarn, added, last_access FROM users WHERE hnrwarn='yes' ORDER BY last_access DESC, hnrwarn DESC ";
        $title = $lang['hnrwarn_warned_title'];
        $link = "<a href=\"staffpanel.php?tool=hnrwarn&amp;action=hnrwarn&amp;do=disabled\">{$lang['hnrwarn_disabled_users']}</a>";
        break;
}
$g = sql_query($query) or sqlerr(__FILE__, __LINE__);
$count = mysqli_num_rows($g);
$HTMLOUT .= begin_main_frame();
$HTMLOUT .= begin_frame($title . '&#160;[<font class="small">total - ' . $count . ' user' . ($count > 1 ? 's' : '') . '</font>] - ' . $link);
if ($count == 0) {
    $HTMLOUT .= stdmsg($lang['hnrwarn_hey'], $lang['hnrwarn_none'] . strtolower($title));
} else {
    $HTMLOUT .= "<form action='staffpanel.php?tool=hnrwarn&amp;action=hnrwarn' method='post'>
        <table width='600' style='border-collapse:separate;'>
        <tr>        
            <td class='colhead' width='100%' >{$lang['hnrwarn_form_user']}</td>
            <td class='colhead' nowrap='nowrap'>{$lang['hnrwarn_form_ratio']}</td>
            <td class='colhead' nowrap='nowrap'>{$lang['hnrwarn_form_class']}</td>
            <td class='colhead' nowrap='nowrap'>{$lang['hnrwarn_form_access']}</td>
            <td class='colhead' nowrap='nowrap'>{$lang['hnrwarn_form_join']}</td>
            <td class='colhead' nowrap='nowrap'><input type='checkbox' name='checkall' /></td>
        </tr>";
    while ($a = mysqli_fetch_assoc($g)) {
        $tip = ($do === 'hnrwarn' ? $lang['hnrwarn_tip1'] . htmlsafechars($a['warn_reason']) . '<br>' : $lang['hnrwarn_tip2'] . htmlsafechars($a['disable_reason']));
        $HTMLOUT .= "<tr>
                  <td width='100%'><a href='userdetails.php?id=" . (int) $a['id'] . "' class='tooltipper' title='$tip'>" . htmlsafechars($a['username']) . "</a></td>
                  <td nowrap='nowrap'>" . (float) $a['ratio'] . "<br><font class='small'><b>{$lang['hnrwarn_d']}</b>" . mksize($a['downloaded']) . "&#160;<b>{$lang['hnrwarn_u']}</b> " . mksize($a['uploaded']) . "</font></td>
                  <td nowrap='nowrap'>" . get_user_class_name($a['class']) . "</td>
                  <td nowrap='nowrap'>" . get_date($a['last_access'], 'LONG', 0, 1) . "</td>
                  <td nowrap='nowrap'>" . get_date($a['added'], 'DATE', 1) . "</td>
                  <td nowrap='nowrap'><input type='checkbox' name='users[]' value='" . (int) $a['id'] . "' /></td>
                </tr>";
    }
    $HTMLOUT .= "<tr>
            <td colspan='6' class='colhead'>
                <select name='action'>
                    <option value='unwarn'>{$lang['hnrwarn_unwarn']}</option>
                    <option value='disable'>{$lang['hnrwarn_disable2']}</option>
                    ";
    $HTMLOUT .= "<option value='delete' " . ($CURUSER['class'] < UC_ADMINISTRATOR ? 'disabled' : '') . ">{$lang['hnrwarn_delete']}</option>";
    $HTMLOUT .= "
                    </select>
                &raquo;
                <input type='submit' value='{$lang['hnrwarn_apply']}' />
                <input type='hidden' value='" . htmlsafechars($_SERVER['REQUEST_URI']) . "' name='ref' />
            </td>
            </tr>
            </table>
            </form>";
}
$HTMLOUT .= end_frame();
$HTMLOUT .= end_main_frame();
echo stdhead($title) . $HTMLOUT . stdfoot();
