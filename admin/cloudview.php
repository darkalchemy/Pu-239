<?php

declare(strict_types = 1);

use Pu239\Cache;
use Pu239\Searchcloud;

require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_bbcode.php';
require_once INCL_DIR . 'function_pager.php';
require_once INCL_DIR . 'function_html.php';
require_once CLASS_DIR . 'class_check.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
global $container, $site_config;

$HTMLOUT = '';
$seachcloud_class = $container->get(Searchcloud::class);
$cache = $container->get(Cache::class);
if (isset($_POST['delcloud'])) {
    $seachcloud_class->delete($_POST['delcloud']);
    $cache->delete('searchcloud_');
    header('Refresh: 3; url=staffpanel.php?tool=cloudview&action=cloudview');
    stderr(_('Success'), _('The obscene terms where successfully deleted!'));
}
$count = $seachcloud_class->get_count();
$perpage = 15;
$pager = pager($perpage, $count, $site_config['paths']['baseurl'] . '/staffpanel.php?tool=cloudview&amp;action=cloudview&amp;');
if ($count > $perpage) {
    $HTMLOUT .= $pager['pagertop'];
}
$searches = $seachcloud_class->get($pager['pdo']);
$HTMLOUT .= "
<form id='checkbox_container' method='post' action='{$_SERVER['PHP_SELF']}?tool=cloudview&amp;action=cloudview' enctype='multipart/form-data' accept-charset='utf-8'>";
$heading = '
    <tr>
        <th>' . _('Searched phrase') . '</th>
        <th>' . _('Hits') . "</th>
        <th><input type='checkbox' id='checkThemAll' class='tooltipper' title='" . _('Delete') . "'></th>
    </tr>";
$body = '';
foreach ($searches as $arr) {
    $search_phrase = htmlsafechars($arr['searchedfor']);
    $body .= "
    <tr>
        <td>$search_phrase</td>
        <td>{$arr['howmuch']}</td>
     
        <td><input type='checkbox' name='delcloud[]' title='" . _('Mark') . "' value='" . (int) $arr['id'] . "'></td>
    </tr>";
}
if (!empty($body)) {
    $body .= "
    <tr>
        <td colspan='4' class='has-text-centered'>
            <input type='submit' value='" . _('Delete selected terms!') . "' class='button is-small margin10'>
        </td>
    </tr>";

    $HTMLOUT .= main_table($body, $heading);
} else {
    $HTMLOUT .= main_div('No cloud search terms to preview.', null, 'has-text-centered padding20');
}
if ($count > $perpage) {
    $HTMLOUT .= $pager['pagerbottom'];
}
$HTMLOUT = '<h1 class="has-text-centered">Cloud Search Terms</h1>' . $HTMLOUT;
$title = _('Cloud View');
$breadcrumbs = [
    "<a href='{$site_config['paths']['baseurl']}/staffpanel.php'>" . _('Staff Panel') . '</a>',
    "<a href='{$_SERVER['PHP_SELF']}'>$title</a>",
];
echo stdhead($title, [], 'page-wrapper', $breadcrumbs) . wrapper($HTMLOUT) . stdfoot();
