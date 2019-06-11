<?php

declare(strict_types = 1);

use Pu239\Cache;

require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_html.php';
require_once CLASS_DIR . 'class_check.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
$lang = array_merge($lang, load_language('ad_leechwarn'));
global $container, $site_config, $CURUSER;

$HTMLOUT = '';
$this_url = $_SERVER['SCRIPT_NAME'];
$do = isset($_GET['do']) && $_GET['do'] === 'disabled' ? 'disabled' : 'leechwarn';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $r = isset($_POST['ref']) ? $_POST['ref'] : $this_url;
    $_uids = isset($_POST['users']) ? array_map('intval', $_POST['users']) : 0;
    if ($_uids == 0 || count($_uids) == 0) {
        stderr($lang['leechwarn_stderror'], $lang['leechwarn_nouser']);
    }
    $valid = [
        'unwarn',
        'disable',
        'delete',
    ];
    $act = isset($_POST['action']) && in_array($_POST['action'], $valid) ? $_POST['action'] : false;
    if (!$act) {
        stderr($lang['leechwarn_stderror'], $lang['leechwarn_wrong']);
    }
    $cache = $container->get(Cache::class);
    if ($act === 'delete' && $CURUSER['class'] >= UC_SYSOP) {
        $res_del = sql_query('SELECT id, username, registered, downloaded, uploaded, last_access, class, donor, warned, enabled, status FROM users WHERE id IN (' . implode(', ', $_uids) . ') ORDER BY username DESC');
        if (mysqli_num_rows($res_del) != 0) {
            $count = mysqli_num_rows($res_del);
            while ($arr_del = mysqli_fetch_assoc($res_del)) {
                $userid = $arr_del['id'];
                $res = sql_query('DELETE FROM users WHERE id=' . sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
                $cache->delete('user_' . $userid);
                write_log("User: {$arr_del['username']} Was deleted by " . $CURUSER['username'] . ' Via Leech Warn Page');
            }
        } else {
            stderr($lang['leechwarn_stderror'], $lang['leechwarn_wrong2']);
        }
    }
    if ($act === 'disable') {
        if (sql_query("UPDATE users SET enabled = 'no', modcomment = CONCAT(" . sqlesc(get_date((int) TIME_NOW, 'DATE', 1) . $lang['leechwarn_disabled_by'] . $CURUSER['username'] . "\n") . ',modcomment) WHERE id IN (' . implode(', ', $_uids) . ')')) {
            foreach ($_uids as $uid) {
                $cache->update_row('user_' . $uid, [
                    'enabled' => 'no',
                ], $site_config['expires']['user_cache']);
            }
            $c = mysqli_affected_rows($mysqli);
            header('Refresh: 2; url=' . $r);
            stderr($lang['leechwarn_success'], $c . $lang['leechwarn_user'] . ($c > 1 ? $lang['leechwarn_s'] : '') . $lang['leechwarn_disabled']);
        } else {
            stderr($lang['leechwarn_stderror'], $lang['leechwarn_wrong3']);
        }
    } elseif ($act === 'unwarn') {
        $sub = $lang['leechwarn_removed'];
        $body = $lang['leechwarn_removed_msg1'] . $CURUSER['username'] . $lang['leechwarn_removed_msg2'];
        $pms = [];
        foreach ($_uids as $uid) {
            $cache->update_row('user_' . $uid, [
                'leechwarn' => 0,
            ], $site_config['expires']['user_cache']);
            $pms[] = '(0,' . $uid . ', ' . sqlesc($sub) . ', ' . sqlesc($body) . ', ' . sqlesc(TIME_NOW) . ')';
        }
        if (!empty($pms) && count($pms)) {
            $g = sql_query('INSERT INTO messages(sender,receiver,subject,msg,added) VALUE ' . implode(', ', $pms)) or ($q_err = ((is_object($mysqli)) ? mysqli_error($mysqli) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
            $q1 = sql_query("UPDATE users SET leechwarn='0', modcomment=CONCAT(" . sqlesc(get_date((int) TIME_NOW, 'DATE', 1) . $lang['leechwarn_mod'] . $CURUSER['username'] . "\n") . ',modcomment) WHERE id IN (' . implode(', ', $_uids) . ')') or ($q2_err = ((is_object($mysqli)) ? mysqli_error($mysqli) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
            if ($g && $q1) {
                header('Refresh: 2; url=' . $r);
                stderr($lang['leechwarn_success'], count($pms) . $lang['leechwarn_user'] . (count($pms) > 1 ? $lang['leechwarn_s'] : '') . $lang['leechwarn_removed_success']);
            } else {
                stderr($lang['leechwarn_stderror'], $lang['leechwarn_q1'] . $q_err . "<br>{$lang['leechwarn_q2']}" . $q2_err);
            }
        }
    }
    exit;
}
switch ($do) {
    case 'disabled':
        $query = "SELECT id,username, class, downloaded, uploaded, IF(downloaded>0, round((uploaded/downloaded),2), '---') AS ratio, disable_reason, registered, last_access FROM users WHERE enabled='no' ORDER BY last_access DESC ";
        $title = $lang['leechwarn_disabled_title'];
        $link = "<a href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=leechwarn&amp;action=leechwarn&amp;?do=warned'>{$lang['leechwarn_warned_link']}</a>";
        break;

    case 'leechwarn':
        $query = "SELECT id, username, class, downloaded, uploaded, IF(downloaded>0, round((uploaded/downloaded),2), '---') AS ratio, warn_reason, leechwarn, registered, last_access FROM users WHERE leechwarn>='1' ORDER BY last_access DESC, leechwarn DESC ";
        $title = $lang['leechwarn_leechwarn_title'];
        $link = "<a href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=leechwarn&amp;action=leechwarn&amp;do=disabled'>{$lang['leechwarn_disabled_link']}</a>";
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
$HTMLOUT .= "<h2 class='has-text-centered'>{$lang['leechwarn_total']} $count {$lang['leechwarn_user']} " . ($count > 1 ? $lang['leechwarn_s'] : '') . '</h2>';
if ($count == 0) {
    $HTMLOUT .= stdmsg($lang['leechwarn_hey'], $lang['leechwarn_none'] . strtolower($title));
} else {
    $HTMLOUT .= "
    <form action='{$_SERVER['PHP_SELF']}?tool=leechwarn&amp;action=leechwarn' method='post' accept-charset='utf-8'>";
    $heading = "
        <tr>
            <th>{$lang['leechwarn_user2']}</th>
            <th>{$lang['leechwarn_ratio']}</th>
            <th>{$lang['leechwarn_class']}</th>
            <th>{$lang['leechwarn_access']}</th>
            <th>{$lang['leechwarn_joined']}</th>
            <th><input type='checkbox' id='checkThemAll'></th>
        </tr>";
    $body = '';
    while ($a = mysqli_fetch_assoc($g)) {
        $tip = ($do === 'leechwarn' ? $lang['leechwarn_warned_for'] . htmlsafechars($a['warn_reason']) . '<br>' . $lang['leechwarn_warned_till'] . get_date((int) $a['leechwarn'], 'DATE', 1) . ' - ' . mkprettytime($a['leechwarn'] - TIME_NOW) : $lang['leechwarn_disabled_for'] . htmlsafechars($a['disable_reason']));
        $body .= "
        <tr>
            <td><a href='userdetails.php?id=" . (int) $a['id'] . "' class='tooltipper' title='$tip'>" . htmlsafechars($a['username']) . '</a></td>
            <td>' . (float) $a['ratio'] . "<br><font class='small'><b>{$lang['leechwarn_d']}</b>" . mksize($a['downloaded']) . "&#160;<b>{$lang['leechwarn_u']}</b> " . mksize($a['uploaded']) . '</font></td>
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
                <option value='unwarn'>{$lang['leechwarn_unwarn']}</option>
                <option value='disable'>{$lang['leechwarn_disable']}</option>
                <option value='delete' " . ($CURUSER['class'] < UC_SYSOP ? 'disabled' : '') . ">{$lang['leechwarn_delete']}</option>
            </select>
                &raquo;
            <input type='submit' value='{$lang['leechwarn_apply']}' class='button is-small'>
            <input type='hidden' value='" . htmlsafechars($_SERVER['REQUEST_URI']) . "' name='ref'>
        </div>
    </form>";
}
echo stdhead($title) . wrapper($HTMLOUT) . stdfoot();
