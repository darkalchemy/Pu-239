<?php

require_once INCL_DIR . 'user_functions.php';
require_once INCL_DIR . 'html_functions.php';
require_once INCL_DIR . 'pager_functions.php';
require_once CLASS_DIR . 'class_check.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
global $CURUSER, $site_config, $lang, $fluent, $cache;

$lang    = array_merge($lang, load_language('ad_acp'));
$stdfoot = [
    'js' => [
        get_file_name('acp_js'),
    ],
];
$HTMLOUT = '';
if (isset($_POST['ids'])) {
    $ids = $_POST['ids'];
    foreach ($ids as $id) {
        if (!is_valid_id($id)) {
            stderr($lang['std_error'], $lang['text_invalid']);
        }
    }
    $do = isset($_POST['do']) ? htmlsafechars(trim($_POST['do'])) : '';
    if ($do == 'enabled') {
        sql_query("UPDATE users SET enabled = 'yes' WHERE ID IN (" . join(', ', array_map('sqlesc', $ids)) . ") AND enabled = 'no'") or sqlerr(__FILE__, __LINE__);
    }
    $cache->update_row('user' . $id, [
        'enabled' => 'yes',
    ], $site_config['expires']['user_cache']);
    //else
    if ($do == 'confirm') {
        sql_query("UPDATE users SET status = 'confirmed' WHERE ID IN (" . join(', ', array_map('sqlesc', $ids)) . ") AND status = 'pending'") or sqlerr(__FILE__, __LINE__);
    }
    $cache->update_row('user' . $id, [
        'status' => 'confirmed',
    ], $site_config['expires']['user_cache']);
    //else
    if ($do == 'delete' && ($CURUSER['class'] >= UC_SYSOP)) {
        $res_del = sql_query('SELECT id, username, added, downloaded, uploaded, last_access, class, donor, warned, enabled, status FROM users WHERE ID IN(' . join(', ', array_map('sqlesc', $ids)) . ') AND class < 3 ORDER BY username DESC');
        if (mysqli_num_rows($res_del) != 0) {
            while ($arr_del = mysqli_fetch_assoc($res_del)) {
                $userid = $arr_del['id'];
                $res    = sql_query('DELETE FROM users WHERE id = ' . sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
                $cache->delete('user' . $userid);
                write_log("User: {$arr_del['username']} Was deleted by " . $CURUSER['username']);
            }
        } else {
            header('Location: staffpanel.php?tool=acpmanage&amp;action=acpmanage');
        }
    } else {
        header('Location: staffpanel.php?tool=acpmanage&amp;action=acpmanage');
        exit;
    }
}
$disabled = number_format(get_row_count('users', "WHERE enabled = 'no'"));
$pending  = number_format(get_row_count('users', "WHERE status = 'pending'"));
$count    = number_format(get_row_count('users', "WHERE enabled = 'no' OR status = 'pending' ORDER BY username DESC"));
$perpage  = 25;
$pager    = pager($perpage, $count, 'staffpanel.php?tool=acpmanage&amp;action=acpmanage&amp;');
$res      = sql_query("SELECT id, username, added, downloaded, uploaded, last_access, class, donor, warned, enabled, status FROM users WHERE enabled = 'no' OR status = 'pending' ORDER BY username DESC {$pager['limit']}");
$HTMLOUT .= begin_main_frame($lang['text_du'] . " [$disabled] | " . $lang['text_pu'] . "[$pending]");
if (mysqli_num_rows($res) != 0) {
    if ($count > $perpage) {
        $HTMLOUT .= $pager['pagertop'];
    }
    $HTMLOUT .= "<form action='{$site_config['baseurl']}/staffpanel.php?tool=acpmanage&amp;action=acpmanage' method='post'>";
    $HTMLOUT .= begin_table();
    $HTMLOUT .= "<tr><td class='colhead'>
      <input style='margin: 0;' type='checkbox' title='" . $lang['text_markall'] . "' value='" . $lang['text_markall'] . "' onclick=\"this.value=check(form);\" /></td>
      <td class='colhead'>{$lang['text_username']}</td>
      <td class='colhead' style='white-space: nowrap;'>{$lang['text_reg']}</td>
      <td class='colhead' style='white-space: nowrap;'>{$lang['text_la']}</td>
      <td class='colhead'>{$lang['text_class']}</td>
      <td class='colhead'>{$lang['text_dload']}</td>
      <td class='colhead'>{$lang['text_upload']}</td>
      <td class='colhead'>{$lang['text_ratio']}</td>
      <td class='colhead'>{$lang['text_status']}</td>
      <td class='colhead' style='white-space: nowrap;'>{$lang['text_enabled']}</td>
      </tr>";
    while ($arr = mysqli_fetch_assoc($res)) {
        $uploaded   = mksize($arr['uploaded']);
        $downloaded = mksize($arr['downloaded']);
        $ratio      = $arr['downloaded'] > 0 ? $arr['uploaded'] / $arr['downloaded'] : 0;
        $ratio      = number_format($ratio, 2);
        $color      = get_ratio_color($ratio);
        if ($color) {
            $ratio = "<span style='color: $color;'>$ratio</span>";
        }
        $added       = get_date($arr['added'], 'LONG', 0, 1);
        $last_access = get_date($arr['last_access'], 'LONG', 0, 1);
        $class       = get_user_class_name($arr['class']);
        $status      = htmlsafechars($arr['status']);
        $enabled     = htmlsafechars($arr['enabled']);
        $HTMLOUT .= '<tr><td><input type="checkbox" name="ids[]" value="' . (int) $arr['id'] . "\" /></td><td><a href='{$site_config['baseurl']}/userdetails.php?id=" . (int) $arr['id'] . "'><b>" . htmlsafechars($arr['username']) . '</b></a>' . ($arr['donor'] === 'yes' ? "<img src='{$site_config['pic_baseurl']}star.gif' alt='" . $lang['text_donor'] . "' />" : '') . ($arr['warned'] >= 1 ? "<img src='{$site_config['pic_baseurl']}warned.gif' alt='" . $lang['text_warned'] . "' />" : '') . "</td>
        <td style='white-space: nowrap;'>{$added}</td>
        <td style='white-space: nowrap;'>{$last_access}</td>
        <td>{$class}</td>
        <td>{$downloaded}</td>
        <td>{$uploaded}</td>
        <td>{$ratio}</td>
        <td>{$status}</td>
        <td>{$enabled}</td>
        </tr>\n";
    }
    if (($CURUSER['class'] >= UC_SYSOP)) {
        $HTMLOUT .= "<tr><td colspan='10'><select name='do'><option value='enabled' disabled selected>{$lang['text_wtd']}</option><option value='enabled'>{$lang['text_es']}</option><option value='confirm'>{$lang['text_cs']}</option><option value='delete'>{$lang['text_ds']}</option></select><input type='submit' value='" . $lang['text_submit'] . "' /></td></tr>";
    } else {
        $HTMLOUT .= "<tr><td colspan='10'><select name='do'><option value='enabled' disabled selected>{$lang['text_wtd']}</option><option value='enabled'>{$lang['text_es']}</option><option value='confirm'>{$lang['text_cs']}</option></select><input type='submit' value='" . $lang['text_submit'] . "' /></td></tr>";
    }

    $HTMLOUT .= end_table();
    $HTMLOUT .= '</form>';
    if ($count > $perpage) {
        $HTMLOUT .= $pager['pagerbottom'];
    }
} else {
    $HTMLOUT .= stdmsg($lang['std_sorry'], $lang['std_nf']);
}
$HTMLOUT .= end_main_frame();
echo stdhead($lang['text_stdhead']) . $HTMLOUT . stdfoot($stdfoot);
