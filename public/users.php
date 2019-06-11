<?php

declare(strict_types = 1);

require_once __DIR__ . '/../include/bittorrent.php';
require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_html.php';
require_once INCL_DIR . 'function_pager.php';
check_user_status();
$lang = array_merge(load_language('global'), load_language('users'));
global $site_config;

$search = isset($_GET['search']) ? strip_tags(trim($_GET['search'])) : '';
$class = isset($_GET['class']) ? $_GET['class'] : '-';
$letter = '';
$q1 = '';
if ($class === '-' || !ctype_digit($class)) {
    $class = '';
}
if ($search != '' || $class) {
    $query1 = 'username LIKE ' . sqlesc("%$search%") . " AND status = 'confirmed' AND anonymous_until = 0";
    if ($search) {
        $q1 = 'search=' . htmlsafechars($search);
    }
} else {
    $letter = isset($_GET['letter']) ? trim((string) $_GET['letter']) : '';
    if (strlen($letter) > 1) {
        die();
    }
    if ($letter == '' || strpos('abcdefghijklmnopqrstuvwxyz0123456789', $letter) === false) {
        $letter = '';
    }
    $query1 = "username LIKE '$letter%' AND status = 'confirmed' AND anonymous_until = 0";
    $q1 = "letter=$letter";
}
if (ctype_digit($class)) {
    $query1 .= " AND class=$class";
    $q1 .= ($q1 ? '&amp;' : '') . "class=$class";
}
$HTMLOUT = '';
$HTMLOUT .= "
    <h1 class='has-text-centered'>Search {$lang['head_users']}</h1>";
$div = "
    <form method='get' action='users.php?' accept-charset='utf-8'>
        <div class='level-center-center'>
            <span class='right10 top20'>{$lang['form_search']}</span>
            <input type='text' name='search' class='w-25 top20'>
            <select name='class' class='left10 top20'>";
$div .= "
                <option value='-'>(any class)</option>";
for ($i = 0;; ++$i) {
    if ($c = get_user_class_name((int) $i)) {
        $div .= "
                <option value='$i'" . (ctype_digit($class) && $class == $i ? ' selected' : '') . ">$c</option>";
    } else {
        break;
    }
}
$div .= "
            </select>
            <input type='submit' value='{$lang['form_btn']}' class='button is-small left10 top20'>
        </div>
    </form>";

$aa = range('0', '9');
$bb = range('a', 'z');
$cc = [
    $aa,
    $bb,
];
foreach ($cc as $aa) {
    $div .= "
    <div class='tabs is-small is-centered top20'>
        <ul>";
    foreach ($aa as $L) {
        if (!strcmp((string) $L, $letter)) {
            $div .= "
            <li class='is-active'><a>" . strtoupper((string) $L) . '</a></li>';
        } else {
            $div .= "
            <li><a href='users.php?letter=$L'>" . strtoupper((string) $L) . '</a></li>';
        }
    }
    $div .= '
        </ul>
    </div>';
}

$HTMLOUT .= main_div($div, 'bottom20');

$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$perpage = 25;
$browsemenu = '';
$pagemenu = '';
$res = sql_query('SELECT COUNT(id) FROM users WHERE ' . $query1) or sqlerr(__FILE__, __LINE__);
$arr = mysqli_fetch_row($res);
$pager = pager($perpage, (int) $arr[0], "{$site_config['paths']['baseurl']}/users.php?$q1&amp;");
if ($arr[0] > 0) {
    if ($arr[0] > $perpage) {
        $HTMLOUT .= $pager['pagertop'];
    }
    $res = sql_query("SELECT users.*, countries.name, countries.flagpic FROM users FORCE INDEX ( username ) LEFT JOIN countries ON country = countries.id WHERE $query1 ORDER BY username {$pager['limit']}") or sqlerr(__FILE__, __LINE__);
    $heading = "
                <tr>
                    <th class='has-text-centered'>{$lang['users_username']}</th>
                    <th class='has-text-centered'>{$lang['users_regd']}</th>
                    <th class='has-text-centered'>{$lang['users_la']}</th>
                    <th class='has-text-centered'>{$lang['users_class']}</th>
                    <th class='has-text-centered'>{$lang['users_country']}</th>
                </tr>";
    $body = '';
    while ($row = mysqli_fetch_assoc($res)) {
        $country = ($row['name'] != null) ? "<img src='{$site_config['paths']['images_baseurl']}flag/" . htmlsafechars($row['flagpic']) . "' alt='" . htmlsafechars($row['name']) . "'>" : '---';
        $body .= '
                <tr>
                    <td>' . format_username((int) $row['id']) . '</td>
                    <td class="has-text-centered">' . get_date((int) $row['registered'], 'LONG') . '</td>
                    <td class="has-text-centered">' . get_date((int) $row['last_access'], 'LONG') . '</td>
                    <td class="has-text-centered">' . get_user_class_name((int) $row['class']) . "</td>
                    <td class='has-text-centered'>$country</td>
                </tr>";
    }
    $HTMLOUT .= main_table($body, $heading);
    if ($arr[0] > $perpage) {
        $HTMLOUT .= $pager['pagerbottom'];
    }
}
echo stdhead($lang['head_users']) . wrapper($HTMLOUT) . stdfoot();
die();
