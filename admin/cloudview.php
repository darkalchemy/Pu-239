<?php
require_once INCL_DIR . 'user_functions.php';
require_once INCL_DIR . 'bbcode_functions.php';
require_once INCL_DIR . 'pager_functions.php';
require_once INCL_DIR . 'html_functions.php';
require_once CLASS_DIR . 'class_check.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
global $cache, $lang;

$lang = array_merge($lang, load_language('ad_cloudview'));
$HTMLOUT = '';
if (isset($_POST['delcloud'])) {
    $do = 'DELETE FROM searchcloud WHERE id IN (' . implode(', ', array_map('sqlesc', $_POST['delcloud'])) . ')';
    $res = sql_query($do);
    $cache->delete('searchcloud');
    header('Refresh: 3; url=staffpanel.php?tool=cloudview&action=cloudview');
    stderr("{$lang['cloudview_success']}", "{$lang['cloudview_success_del']}");
}
$HTMLOUT .= '<script>
/*<![CDATA[*/
var checkflag = "false";
var marked_row = new Array;
function check(field) {
if (checkflag == "false") {
for (i = 0; i < field.length; i++) {
field[i].checked = true;}
checkflag = "true";
}else {
for (i = 0; i < field.length; i++) {
field[i].checked = false; }
checkflag = "false";
}
}
/*]]>*/
</script>';
$search_count = sql_query('SELECT COUNT(id) FROM searchcloud');
$row = mysqli_fetch_array($search_count);
$count = $row[0];
$perpage = 15;
$pager = pager($perpage, $count, 'staffpanel.php?tool=cloudview&amp;action=cloudview&amp;');
if ($count > $perpage) {
    $HTMLOUT .= $pager['pagertop'];
}
$search_q = sql_query('SELECT id, searchedfor, ip, howmuch FROM searchcloud ORDER BY howmuch DESC ' . $pager['limit']) or sqlerr(__FILE__, __LINE__);
$HTMLOUT .= begin_main_frame($lang['cloudview_overview']);
$HTMLOUT .= "
<form method='post' action='staffpanel.php?tool=cloudview&amp;action=cloudview'>
<table >\n
<tr>
<td class='colhead' width='1%'>{$lang['cloudview_phrase']}</td>
<td class='colhead' width='1%'>{$lang['cloudview_hits']}</td>
<td class='colhead' width='1%'>{$lang['cloudview_ip']}</td>
<td class='colhead' width='1%'>{$lang['cloudview_del']}</td></tr>\n";
while ($arr = mysqli_fetch_assoc($search_q)) {
    $search_phrase = htmlsafechars($arr['searchedfor']);
    $hits = (int)$arr['howmuch'];
    $ip = htmlsafechars(ipToStorageFormat($arr['ip']));
    $HTMLOUT .= "<tr>
<td class='one'>$search_phrase</td>
<td class='two'>$hits</td>
<td class='two'>$ip</td>
<td class='one'><input type='checkbox' name='delcloud[]' title='{$lang['cloudview_mark']}' value='" . (int)$arr['id'] . "' /></td></tr>\n";
}
$HTMLOUT .= "<tr>
<td colspan='4' class='colhead'>{$lang['cloudview_markall_search']}<input type='checkbox' title='{$lang['cloudview_markall']}' value='{$lang['cloudview_markall']}' onclick=\"this.value=check(form.elements);\" /></td></tr>
<tr><td colspan='4' class='colhead'><input type='submit' value='{$lang['cloudview_del_terms']}' /></td></tr>";
$HTMLOUT .= '</table></form>';
$HTMLOUT .= end_main_frame();
if ($count > $perpage) {
    $HTMLOUT .= $pager['pagerbottom'];
}
echo stdhead($lang['cloudview_stdhead']) . $HTMLOUT . stdfoot();
