<?php

require_once INCL_DIR . 'user_functions.php';
require_once INCL_DIR . 'pager_functions.php';
require_once INCL_DIR . 'html_functions.php';
require_once CLASS_DIR . 'class_check.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
global $site_config, $lang;

$lang = array_merge($lang, load_language('ad_donations'));
$HTMLOUT = '';
if (isset($_GET['total_donors'])) {
    $total_donors = (int) $_GET['total_donors'];
    if ($total_donors != '1') {
        stderr($lang['donate_err'], $lang['donate_err1']);
    }
    $res = sql_query("SELECT COUNT(*) FROM users WHERE total_donated != '0.00' AND enabled='yes'") or sqlerr(__FILE__, __LINE__);
    $row = mysqli_fetch_array($res);
    $count = $row[0];
    $perpage = 15;
    $pager = pager($perpage, $count, 'staffpanel.php?tool=donations&amp;action=donations&amp;');
    if (mysqli_num_rows($res) == 0) {
        stderr($lang['donate_sorry'], $lang['donate_nofound']);
    }
    $users = number_format(get_row_count('users', "WHERE total_donated != '0.00'"));
    $res = sql_query("SELECT id, username, email, added, donated, donoruntil, total_donated FROM users WHERE total_donated != '0.00' ORDER BY id DESC " . $pager['limit'] . '') or sqlerr(__FILE__, __LINE__);
} else {
    $res = sql_query("SELECT COUNT(id) FROM users WHERE donor='yes'") or sqlerr(__FILE__, __LINE__);
    $row = mysqli_fetch_array($res);
    $count = $row[0];
    $perpage = 15;
    $pager = pager($perpage, $count, 'staffpanel.php?tool=donations&amp;action=donations&amp;');
    if (mysqli_num_rows($res) == 0) {
        stderr($lang['donate_sorry'], $lang['donate_nofound']);
    }
    $users = number_format(get_row_count('users', "WHERE donor='yes'"));
    $res = sql_query("SELECT id, username, email, added, donated, total_donated, donoruntil FROM users WHERE donor='yes' ORDER BY id DESC " . $pager['limit'] . '') or sqlerr(__FILE__, __LINE__);
}
if ($count > $perpage) {
    $HTMLOUT .= $pager['pagertop'];
}

$HTMLOUT .= "
    <ul class='level-center bg-06'>
        <li class='altlink margin10'>
            <a href='{$site_config['baseurl']}/staffpanel.php?tool=donations&amp;action=donations'>{$lang['donate_curr_don']}</a>
        </li>
        <li class='altlink margin10'>
            <a href='{$site_config['baseurl']}/staffpanel.php?tool=donations&amp;action=donations&amp;total_donors=1'>{$lang['donate_all_don']}</a>
        </li>
    </ul>
    <h1 class='has-text-centered'>Site Donations</h1>";
$heading = "
    <tr>
        <th>{$lang['donate_id']}</th>
        <th>{$lang['donate_username']}</th>
        <th>{$lang['donate_email']}</th>
        <th>{$lang['donate_joined']}</th>
        <th>{$lang['donate_until']}</th>
        <th>{$lang['donate_current']}</th>
        <th>{$lang['donate_total']}</th>
        <th>{$lang['donate_pm']}</th>
    </tr>";
$body = '';
while ($arr = mysqli_fetch_assoc($res)) {
    $body .= "
    <tr>
        <td>{$arr['id']}</td>
        <td>" . format_username($arr['id']) . "</td>
        <td><a class='altlink' href='mailto:" . htmlsafechars($arr['email']) . "'>" . htmlsafechars($arr['email']) . "</a></td>
        <td><span class='size_3'>" . get_date($arr['added'], 'DATE') . '</span></td>
        <td>';
    $donoruntil = (int) $arr['donoruntil'];
    if ($donoruntil == 0) {
        $body .= 'n/a';
    } else {
        $body .= '<span class="size_3">' . get_date($arr['donoruntil'], 'DATE') . ' [ ' . mkprettytime($donoruntil - TIME_NOW) . " ]{$lang['donate_togo']}</span>";
    }
    $body .= '
        </td>
        <td><b>&#36;' . htmlsafechars($arr['donated']) . '</td>
        <td><b>&#36;' . htmlsafechars($arr['total_donated']) . "</td>
        <td>
            <a class='altlink' href='{$site_config['baseurl']}/messages.php?action=send_message&amp;receiver=" . (int) $arr['id'] . "'>{$lang['donate_sendpm']}</a>
        </td>
    </tr>";
}
$HTMLOUT .= main_table($body, $heading);
if ($count > $perpage) {
    $HTMLOUT .= $pager['pagerbottom'];
}

echo stdhead($lang['donate_stdhead']) . wrapper($HTMLOUT) . stdfoot();
