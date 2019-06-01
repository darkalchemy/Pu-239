<?php

declare(strict_types = 1);

use Pu239\Database;

require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_pager.php';
require_once INCL_DIR . 'function_html.php';
require_once CLASS_DIR . 'class_check.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
$lang = array_merge($lang, load_language('ad_donations'));
$HTMLOUT = '';
global $container, $site_config;

$fluent = $container->get(Database::class);
if (isset($_GET['total_donors'])) {
    $total_donors = (int) $_GET['total_donors'];
    if ($total_donors != '1') {
        stderr($lang['donate_err'], $lang['donate_err1']);
    }
    $res = sql_query("SELECT COUNT(id) FROM users WHERE total_donated != '0.00' AND enabled='yes'") or sqlerr(__FILE__, __LINE__);
    $row = mysqli_fetch_array($res);
    $count = $row[0];
    $perpage = 15;
    $pager = pager($perpage, $count, 'staffpanel.php?tool=donations&amp;action=donations&amp;');
    if (mysqli_num_rows($res) == 0) {
        stderr($lang['donate_sorry'], $lang['donate_nofound']);
    }
    $users = $fluent->from('users')
                    ->select(null)
                    ->select('COUNT(id) AS count')
                    ->where('total_donated >= 0')
                    ->fetch('count');
    $users = number_format($users);
    $res = sql_query("SELECT id, username, email, added, donated, donoruntil, total_donated FROM users WHERE total_donated != '0.00' ORDER BY id DESC " . $pager['limit'] . '') or sqlerr(__FILE__, __LINE__);
} else {
    $res = sql_query("SELECT COUNT(id) FROM users WHERE donor='yes'") or sqlerr(__FILE__, __LINE__);
    $row = mysqli_fetch_array($res);
    $count = (int) $row[0];
    $perpage = 15;
    $pager = pager($perpage, $count, 'staffpanel.php?tool=donations&amp;action=donations&amp;');
    if (mysqli_num_rows($res) == 0) {
        stderr($lang['donate_sorry'], $lang['donate_nofound']);
    }
    $users = $fluent->from('users')
                    ->select(null)
                    ->select('COUNT(id) AS count')
                    ->where('donor = "yes"')
                    ->fetch('count');
    $users = number_format($users);
    $res = sql_query("SELECT id, username, email, added, donated, total_donated, donoruntil FROM users WHERE donor='yes' ORDER BY id DESC " . $pager['limit'] . '') or sqlerr(__FILE__, __LINE__);
}
if ($count > $perpage) {
    $HTMLOUT .= $pager['pagertop'];
}

$HTMLOUT .= "
    <ul class='level-center bg-06'>
        <li class='is-link margin10'>
            <a href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=donations&amp;action=donations'>{$lang['donate_curr_don']}</a>
        </li>
        <li class='is-link margin10'>
            <a href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=donations&amp;action=donations&amp;total_donors=1'>{$lang['donate_all_don']}</a>
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
        <td>" . format_username((int) $arr['id']) . "</td>
        <td><a class='is-link' href='mailto:" . htmlsafechars($arr['email']) . "'>" . htmlsafechars($arr['email']) . "</a></td>
        <td><span class='size_3'>" . get_date((int) $arr['added'], 'DATE') . '</span></td>
        <td>';
    $donoruntil = (int) $arr['donoruntil'];
    if ($donoruntil == 0) {
        $body .= 'n/a';
    } else {
        $body .= '<span class="size_3">' . get_date((int) $arr['donoruntil'], 'DATE') . ' [ ' . mkprettytime($donoruntil - TIME_NOW) . " ]{$lang['donate_togo']}</span>";
    }
    $body .= '
        </td>
        <td><b>&#36;' . htmlsafechars($arr['donated']) . '</td>
        <td><b>&#36;' . htmlsafechars($arr['total_donated']) . "</td>
        <td>
            <a class='is-link' href='{$site_config['paths']['baseurl']}/messages.php?action=send_message&amp;receiver=" . (int) $arr['id'] . "'>{$lang['donate_sendpm']}</a>
        </td>
    </tr>";
}
$HTMLOUT .= main_table($body, $heading);
if ($count > $perpage) {
    $HTMLOUT .= $pager['pagerbottom'];
}

echo stdhead($lang['donate_stdhead']) . wrapper($HTMLOUT) . stdfoot();
