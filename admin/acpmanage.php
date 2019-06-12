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
$lang = array_merge($lang, load_language('ad_acp'), load_language('ad_delacct'));
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
            stderr($lang['std_error'], $lang['text_invalid']);
        }
    }
    $do = isset($_POST['do']) ? htmlsafechars(trim($_POST['do'])) : '';
    if ($do == 'enabled') {
        sql_query("UPDATE users SET enabled = 'yes' WHERE id IN (" . implode(', ', array_map('sqlesc', $ids)) . ") AND enabled = 'no'") or sqlerr(__FILE__, __LINE__);
        $cache->update_row('user_' . $id, [
            'enabled' => 'yes',
        ], $site_config['expires']['user_cache']);
    } elseif ($do == 'confirm') {
        sql_query("UPDATE users SET status = 'confirmed' WHERE ID IN (" . implode(', ', array_map('sqlesc', $ids)) . ") AND status = 'pending'") or sqlerr(__FILE__, __LINE__);
        $cache->update_row('user_' . $id, [
            'status' => 'confirmed',
        ], $site_config['expires']['user_cache']);
    } elseif ($do == 'delete' && ($CURUSER['class'] >= UC_MAX)) {
        foreach ($ids as $id) {
            $username = account_delete($id);
            if ($username) {
                write_log("User: $username Was deleted by {$CURUSER['username']}");
                $session->set('is-success', $lang['text_success']);
            } else {
                stderr($lang['text_error'], $lang['text_unable']);
            }
        }
        $session->set('is-success', $lang['text_success']);
    }
    header('Location: ' . $_SERVER['PHP_SELF'] . '?tool=acpmanage&amp;action=acpmanage');
    exit;
}
$disabled = $fluent->from('users')
                   ->select(null)
                   ->select('COUNT(id) AS count')
                   ->where('enabled = "no"')
                   ->fetch('count');
$pending = $fluent->from('users')
                  ->select(null)
                  ->select('COUNT(id) AS count')
                  ->where('status = "pending"')
                  ->fetch('count');
$count = $fluent->from('users')
                ->select(null)
                ->select('COUNT(id) AS count')
                ->where('enabled = "no" OR status = "pending"')
                ->fetch('count');
$disabled = number_format($disabled);
$pending = number_format($pending);
$count = $count;
$perpage = 25;
$pager = pager($perpage, $count, 'staffpanel.php?tool=acpmanage&amp;action=acpmanage&amp;');
$res = sql_query("SELECT id, username, registered, downloaded, uploaded, last_access, class, donor, warned, enabled, status FROM users WHERE enabled = 'no' OR status = 'pending' ORDER BY username DESC {$pager['limit']}");
if (mysqli_num_rows($res) != 0) {
    if ($count > $perpage) {
        $HTMLOUT .= $pager['pagertop'];
    }
    $HTMLOUT .= "<form action='{$_SERVER['PHP_SELF']}?tool=acpmanage&amp;action=acpmanage' method='post' accept-charset='utf-8'>";
    $HTMLOUT .= begin_table();
    $HTMLOUT .= "<tr><td class='colhead'>
      <input style='margin: 0;' type='checkbox' title='" . $lang['text_markall'] . "' value='" . $lang['text_markall'] . "' onclick=\"this.value=check(form);\"></td>
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
        $uploaded = mksize($arr['uploaded']);
        $downloaded = mksize($arr['downloaded']);
        $ratio = $arr['downloaded'] > 0 ? $arr['uploaded'] / $arr['downloaded'] : 0;
        $ratio = $ratio;
        $color = get_ratio_color($ratio);
        if ($color) {
            $ratio = "<span style='color: $color;'>" . number_format($ratio, 2) . '</span>';
        }
        $added = get_date((int) $arr['registered'], 'LONG', 0, 1);
        $last_access = get_date((int) $arr['last_access'], 'LONG', 0, 1);
        $class = get_user_class_name((int) $arr['class']);
        $status = htmlsafechars($arr['status']);
        $enabled = htmlsafechars($arr['enabled']);
        $HTMLOUT .= "
        <tr>
            <td>
                <input type='checkbox' name='ids[]' value='{$arr['id']}'>
            </td>
            <td>" . format_username((int) $arr['id']) . "</td>
            <td style='white-space: nowrap;'>{$added}</td>
            <td style='white-space: nowrap;'>{$last_access}</td>
            <td>{$class}</td>
            <td>{$downloaded}</td>
            <td>{$uploaded}</td>
            <td>{$ratio}</td>
            <td>{$status}</td>
            <td>{$enabled}</td>
        </tr>";
    }
    if (($CURUSER['class'] >= UC_MAX)) {
        $HTMLOUT .= "<tr><td colspan='10' class='has-text-centered'><select name='do'><option value='enabled' disabled selected>{$lang['text_wtd']}</option><option value='enabled'>{$lang['text_es']}</option><option value='confirm'>{$lang['text_cs']}</option><option value='delete'>{$lang['text_ds']}</option></select><br><input type='submit' class='margin20 button is-small' value='" . $lang['text_submit'] . "'></td></tr>";
    } else {
        $HTMLOUT .= "<tr><td colspan='10' class='has-text-centered'><select name='do'><option value='enabled' disabled selected>{$lang['text_wtd']}</option><option value='enabled'>{$lang['text_es']}</option><option value='confirm'>{$lang['text_cs']}</option></select><br><input type='submit' class='margin20 button is-small' value='" . $lang['text_submit'] . "'></td></tr>";
    }

    $HTMLOUT .= end_table();
    $HTMLOUT .= '</form>';
    if ($count > $perpage) {
        $HTMLOUT .= $pager['pagerbottom'];
    }
} else {
    $HTMLOUT = stdmsg("<h2>{$lang['std_sorry']}</h2>", "<p>{$lang['std_nf']}</p>");
}

echo stdhead($lang['text_stdhead']) . wrapper($HTMLOUT) . stdfoot($stdfoot);
