<?php

declare(strict_types = 1);

use Pu239\Database;
use Pu239\Session;

require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_pager.php';
require_once CLASS_DIR . 'class_check.php';
require_once INCL_DIR . 'function_html.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
$lang = array_merge($lang, load_language('ad_bans'));
global $container, $CURUSER, $site_config;

$session = $container->get(Session::class);
$fluent = $container->get(Database::class);
$remove = isset($_GET['remove']) ? (int) $_GET['remove'] : 0;
if ($remove > 0) {
    $res = $fluent->from('bans')
                  ->select(null)
                  ->select('INET6_NTOA(first) AS first')
                  ->select('INET6_NTOA(last) AS last')
                  ->where('id = ?', $remove)
                  ->fetch();

    if (!$res) {
        stderr($lang['stderr_error'], $lang['stderr_error1']);
    }
    for ($i = $res['first']; $i <= $res['last']; ++$i) {
        $cache->delete('bans_' . $i);
    }
    if (is_valid_id($remove)) {
        $fluent->deleteFrom('bans')
               ->where('id = ?', $remove)
               ->execute();
        $removed = sprintf($lang['text_banremoved'], $remove);
        write_log("{$removed}" . $CURUSER['id'] . ' (' . $CURUSER['username'] . ')');
        $session->set('is-success', "IPS: {$res['first']} to {$res['last']} removed");
        unset($_GET);
    }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $CURUSER['class'] >= UC_MAX) {
    $first = trim($_POST['first']);
    $last = trim($_POST['last']);
    $comment = htmlsafechars(trim($_POST['comment']));
    if (!$first || !$last || !$comment) {
        stderr($lang['stderr_error'], $lang['text_missing']);
    }
    if (!validip($first) || !validip($last)) {
        stderr($lang['stderr_error'], $lang['text_badip']);
    }
    $added = TIME_NOW;
    for ($i = $first; $i <= $last; ++$i) {
        $cache->delete('bans_' . $i);
    }

    $values = [
        'added' => $added,
        'addedby' => $CURUSER['id'],
        'first' => inet_pton($first),
        'last' => inet_pton($last),
        'comment' => $comment,
    ];

    $fluent->insertInto('bans')
           ->values('values')
           ->execute();

    $session->set('is-success', "IPs: $first to $last added to Bans");
    unset($_POST);
}

$res = $fluent->from('bans')
              ->select('INET6_NTOA(first) AS first')
              ->select('INET6_NTOA(last) AS last')
              ->orderBy('added DESC');

foreach ($res as $arr) {
    $bans[] = $arr;
}
$count = !empty($bans) ? count($bans) : 0;
$perpage = 15;
$pager = pager($perpage, $count, 'staffpanel.php?tool=bans&amp;');

$HTMLOUT = "
        <h1 class='has-text-centered'>Bans</h1>
        <div class='top20 bg-00 round10'>
            <div class='padding20'>
                <h2>{$lang['text_current']}</h2>
            </div>";
if ($count == 0) {
    $HTMLOUT .= main_div("<div class='padding20'>{$lang['text_nothing']}</div>");
} else {
    if ($count > $perpage) {
        $HTMLOUT .= $pager['pagertop'];
    }
    $header = "
                <tr>
                    <th>{$lang['header_added']}</th>
                    <th>{$lang['header_firstip']}</th>
                    <th>{$lang['header_lastip']}</th>
                    <th>{$lang['header_by']}</th>
                    <th>{$lang['header_comment']}</th>
                    <th>{$lang['header_remove']}</th>
                </tr>";
    $body = '';
    foreach ($bans as $banned) {
        $body .= '
                <tr>
                    <td>' . get_date((int) $banned['added'], '') . '</td>
                    <td>' . htmlsafechars($banned['first']) . '</td>
                    <td>' . htmlsafechars($banned['last']) . '</td>
                    <td>' . format_username((int) $banned['addedby']) . '</td>
                    <td>' . htmlsafechars($banned['comment']) . "</td>
                    <td><a href='" . $site_config['paths']['baseurl'] . '/staffpanel.php?tool=bans&amp;remove=' . $banned['id'] . "'><i class='icon-trash-empty icon tooltipper has-text-danger' title='{$lang['text_remove']}'></i></a></td>
               </tr>";
    }
    $HTMLOUT .= main_table($body, $header);
    if ($count > $perpage) {
        $HTMLOUT .= $pager['pagerbottom'];
    }
}
$HTMLOUT .= '
        </div>';
if ($CURUSER['class'] >= UC_MAX) {
    $HTMLOUT .= "
        <div class='top20 bg-00 round10'>
            <div class='padding20'>
                <h2>{$lang['text_addban']}</h2>
            </div>
            <form method='post' action='staffpanel.php?tool=bans' enctype='multipart/form-data' accept-charset='utf-8'>";
    $HTMLOUT .= main_table("
                <tr>
                    <td class='rowhead'>{$lang['table_firstip']}</td>
                    <td><input type='text' name='first' class='w-100'></td>
                </tr>
                <tr>
                    <td class='rowhead'>{$lang['table_lastip']}</td>
                    <td><input type='text' name='last' class='w-100'></td>
                </tr>
                <tr>
                    <td class='rowhead'>{$lang['table_comment']}</td><td><input type='text' name='comment' class='w-100'></td>
                </tr>");
    $HTMLOUT .= "
                <div class='has-text-centered padding20'>
                    <input type='submit' name='okay' value='{$lang['btn_add']}' class='button is-small'>
                </div>
            </form>
        </div>";
}
echo stdhead($lang['stdhead_adduser']) . wrapper($HTMLOUT) . stdfoot();
