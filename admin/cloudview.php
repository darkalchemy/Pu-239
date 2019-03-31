<?php

require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_bbcode.php';
require_once INCL_DIR . 'function_pager.php';
require_once INCL_DIR . 'function_html.php';
require_once CLASS_DIR . 'class_check.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
global $lang, $site_config, $cache, $searchcloud_stuffs;

$lang = array_merge($lang, load_language('ad_cloudview'));
$HTMLOUT = '';
if (isset($_POST['delcloud'])) {
    $searchcloud_stuffs->delete($_POST['delcloud']);
    $cache->delete('searchcloud_');
    header('Refresh: 3; url=staffpanel.php?tool=cloudview&action=cloudview');
    stderr("{$lang['cloudview_success']}", "{$lang['cloudview_success_del']}");
}
$count = $searchcloud_stuffs->get_count();
$perpage = 15;
$pager = pager($perpage, $count, $site_config['baseurl'] . '/staffpanel.php?tool=cloudview&amp;action=cloudview&amp;');
if ($count > $perpage) {
    $HTMLOUT .= $pager['pagertop'];
}
$searches = $searchcloud_stuffs->get($pager['pdo']);
$HTMLOUT .= "
<form id='checkbox_container' method='post' action='staffpanel.php?tool=cloudview&amp;action=cloudview' accept-charset='utf-8'>";
$heading = "
    <tr>
        <th>{$lang['cloudview_phrase']}</th>
        <th>{$lang['cloudview_hits']}</th>
        <th>{$lang['cloudview_ip']}</th>
        <th><input type='checkbox' id='checkThemAll' class='tooltipper' title='{$lang['cloudview_del']}'></th>
    </tr>";
$body = '';
foreach ($searches as $arr) {
    $search_phrase = htmlsafechars($arr['searchedfor']);
    $body .= "
    <tr>
        <td>$search_phrase</td>
        <td>{$arr['howmuch']}</td>
        <td>{$arr['ip']}</td>
        <td><input type='checkbox' name='delcloud[]' title='{$lang['cloudview_mark']}' value='" . (int) $arr['id'] . "'></td>
    </tr>";
}
if (!empty($body)) {
    $body .= "
    <tr>
        <td colspan='4' class='has-text-centered'>
            <input type='submit' value='{$lang['cloudview_del_terms']}' class='button is-small margin10'>
        </td>
    </tr>";

    $HTMLOUT .= main_table($body, $heading);
} else {
    $HTMLOUT .= main_div('No cloud search terms to preview.');
}
if ($count > $perpage) {
    $HTMLOUT .= $pager['pagerbottom'];
}
$HTMLOUT = '<h1 class="has-text-centered">Cloud Search Terms</h1>' . $HTMLOUT;
echo stdhead($lang['cloudview_stdhead']) . wrapper($HTMLOUT) . stdfoot();
