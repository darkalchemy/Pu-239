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
        stderr(_('Error'), _('A Ban with that ID could not be found'));
    }
    for ($i = $res['first']; $i <= $res['last']; ++$i) {
        $cache->delete('bans_' . $i);
    }
    if (is_valid_id($remove)) {
        $fluent->deleteFrom('bans')
               ->where('id = ?', $remove)
               ->execute();
        write_log(_fe('Ban {0} was removed by {1}', $remove, $CURUSER['username']));
        $session->set('is-success', _fe('IPS: {0} to {1} were removed', $res['first'], $res['last']));
        unset($_GET);
    }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $CURUSER['class'] >= UC_MAX) {
    $first = htmlsafechars($_POST['first']);
    $last = htmlsafechars($_POST['last']);
    $comment = htmlsafechars($_POST['comment']);
    if (!$first || !$last || !$comment) {
        stderr(_('Error'), _('Missing form data.'));
    }
    if (!validip($first) || !validip($last)) {
        stderr(_('Error'), _('Invalid IP address.'));
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
           ->values($values)
           ->execute();

    $key = 'bans_' . $ip;
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
                <h2>" . _('Current bans') . '</h2>
            </div>';
if ($count == 0) {
    $HTMLOUT .= main_div("<div class='padding20'>" . _('Nothing found.') . '</div>');
} else {
    if ($count > $perpage) {
        $HTMLOUT .= $pager['pagertop'];
    }
    $header = '
                <tr>
                    <th>' . _('Added') . '</th>
                    <th>' . _('First IP') . '</th>
                    <th>' . _('Last IP') . '</th>
                    <th>' . _('By') . '</th>
                    <th>' . _('Comment') . '</th>
                    <th>' . _('Remove') . '</th>
                </tr>';
    $body = '';
    foreach ($bans as $banned) {
        $body .= '
                <tr>
                    <td>' . get_date((int) $banned['added'], '') . '</td>
                    <td>' . htmlsafechars($banned['first']) . '</td>
                    <td>' . htmlsafechars($banned['last']) . '</td>
                    <td>' . format_username((int) $banned['addedby']) . '</td>
                    <td>' . htmlsafechars($banned['comment']) . "</td>
                    <td><a href='" . $site_config['paths']['baseurl'] . '/staffpanel.php?tool=bans&amp;remove=' . $banned['id'] . "'><i class='icon-trash-empty icon tooltipper has-text-danger' title='" . _('Remove') . "'></i></a></td>
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
                <h2>" . _('Add ban') . "</h2>
            </div>
            <form method='post' action='staffpanel.php?tool=bans' enctype='multipart/form-data' accept-charset='utf-8'>";
    $HTMLOUT .= main_table("
                <tr>
                    <td class='rowhead'>" . _('First IP') . "</td>
                    <td><input type='text' name='first' class='w-100'></td>
                </tr>
                <tr>
                    <td class='rowhead'>" . _('Last IP') . "</td>
                    <td><input type='text' name='last' class='w-100'></td>
                </tr>
                <tr>
                    <td class='rowhead'>" . _('Comment') . "</td><td><input type='text' name='comment' class='w-100'></td>
                </tr>");
    $HTMLOUT .= "
                <div class='has-text-centered padding20'>
                    <input type='submit' name='okay' value='" . _('Add') . "' class='button is-small'>
                </div>
            </form>
        </div>";
}
$title = _('Bans');
$breadcrumbs = [
    "<a href='{$site_config['paths']['baseurl']}/staffpanel.php'>" . _('Staff Panel') . '</a>',
    "<a href='{$_SERVER['PHP_SELF']}'>$title</a>",
];
echo stdhead($title, [], 'page-wrapper', $breadcrumbs) . wrapper($HTMLOUT) . stdfoot();
