<?php

require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_html.php';
require_once INCL_DIR . 'function_pager.php';
require_once CLASS_DIR . 'class_check.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
global $CURUSER, $site_config, $lang, $fluent;

$lang = array_merge($lang, load_language('ad_banemail'));

$HTMLOUT = '';
$remove = isset($_GET['remove']) ? (int) $_GET['remove'] : 0;
if (is_valid_id($remove)) {
    sql_query('DELETE FROM bannedemails WHERE id=' . sqlesc($remove)) or sqlerr(__FILE__, __LINE__);
    write_log("{$lang['ad_banemail_log1']} $remove {$lang['ad_banemail_log2']} {$CURUSER['username']}");
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = htmlsafechars(trim($_POST['email']));
    $comment = htmlsafechars(trim($_POST['comment']));
    if (!$email || !$comment) {
        stderr("{$lang['ad_banemail_error']}", "{$lang['ad_banemail_missing']}");
    }
    sql_query('INSERT INTO bannedemails (added, addedby, comment, email) VALUES(' . TIME_NOW . ', ' . sqlesc($CURUSER['id']) . ', ' . sqlesc($comment) . ', ' . sqlesc($email) . ')') or sqlerr(__FILE__, __LINE__);
    header('Location: staffpanel.php?tool=bannedemails');
    die();
}
$HTMLOUT .= "
    <h1 class='has-text-centered'>{$lang['ad_banemail_add']}</h1>
    <form method='post' action='staffpanel.php?tool=bannedemails' accept-charset='utf-8'>";
$body = "
        <tr>
            <td class='rowhead'>{$lang['ad_banemail_email']}</td>
            <td><input type='text' name='email' size='40'></td></tr>
            <tr><td class='rowhead'align='left'>{$lang['ad_banemail_comment']}</td>
            <td><input type='text' name='comment' size='40'></td>
        </tr>
        <tr>
            <td colspan='2'>{$lang['ad_banemail_info']}</td>
        </tr>
        <tr>
            <td colspan='2' class='has-text-centered'>
                <input type='submit' value='{$lang['ad_banemail_ok']}' class='button is-small'>
            </td>
        </tr>";
$HTMLOUT .= main_table($body) . '
    </form>';
$count1 = $fluent->from('bannedemails')
                 ->select(null)
                 ->select('COUNT(*) AS count')
                 ->fetch('count');
$perpage = 15;
$pager = pager($perpage, $count1, 'staffpanel.php?tool=bannedemails&amp;');
$res = sql_query('SELECT b.id, b.added, b.addedby, b.comment, b.email, u.username FROM bannedemails AS b LEFT JOIN users AS u ON b.addedby=u.id ORDER BY added DESC ' . $pager['limit']) or sqlerr(__FILE__, __LINE__);
$HTMLOUT .= "<h1 class='has-text-centered'>{$lang['ad_banemail_current']}</h1>";
if ($count1 > $perpage) {
    $HTMLOUT .= $pager['pagertop'];
}
if (mysqli_num_rows($res) == 0) {
    $HTMLOUT .= stdmsg('Sorry', "<p><b>{$lang['ad_banemail_nothing']}</b></p>");
} else {
    $heading = "
        <tr>
            <th>{$lang['ad_banemail_add1']}</th>
            <th>{$lang['ad_banemail_email']}</th>
            <th>{$lang['ad_banemail_by']}</th>
            <th>{$lang['ad_banemail_comment']}</th>
            <th>{$lang['ad_banemail_remove']}</th>
        </tr>";
    $body = '';
    while ($arr = mysqli_fetch_assoc($res)) {
        $body .= '
        <tr>
            <td>' . get_date($arr['added'], '') . '</td>
            <td>' . htmlsafechars($arr['email']) . '</td>
            <td>' . format_username($arr['addedby']) . '</td>
            <td>' . htmlsafechars($arr['comment']) . "</td>
            <td><a href='staffpanel.php?tool=bannedemails&amp;remove=" . (int) $arr['id'] . "'>{$lang['ad_banemail_remove1']}</a></td>
        </tr>";
    }
    $HTMLOUT .= main_table($body, $heading);
}
if ($count1 > $perpage) {
    $HTMLOUT .= $pager['pagerbottom'];
}
echo stdhead("{$lang['ad_banemail_head']}") . wrapper($HTMLOUT) . stdfoot();
