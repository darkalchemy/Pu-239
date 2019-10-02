<?php

declare(strict_types = 1);

use Pu239\Database;

require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_html.php';
require_once INCL_DIR . 'function_pager.php';
require_once CLASS_DIR . 'class_check.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
global $ontainer, $CURUSER, $site_config;

$HTMLOUT = '';
$remove = isset($_GET['remove']) ? (int) $_GET['remove'] : 0;
if (is_valid_id($remove)) {
    sql_query('DELETE FROM bannedemails WHERE id=' . sqlesc($remove)) or sqlerr(__FILE__, __LINE__);
    write_log('' . _('Email ban') . " $remove " . _('was removed by') . " {$CURUSER['username']}");
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = htmlsafechars(trim($_POST['email']));
    $comment = htmlsafechars(trim($_POST['comment']));
    if (!$email || !$comment) {
        stderr(_('Error'), _('Missing Form Data.'));
    }
    sql_query('INSERT INTO bannedemails (added, addedby, comment, email) VALUES(' . TIME_NOW . ', ' . sqlesc($CURUSER['id']) . ', ' . sqlesc($comment) . ', ' . sqlesc($email) . ')') or sqlerr(__FILE__, __LINE__);
    header('Location: ' . $_SERVER['PHP_SELF'] . '?tool=bannedemails');
    die();
}
$HTMLOUT .= "
    <h1 class='has-text-centered'>" . _('Add Ban') . "</h1>
    <form method='post' action='staffpanel.php?tool=bannedemails' enctype='multipart/form-data' accept-charset='utf-8'>";
$body = "
        <tr>
            <td class='rowhead'>" . _('Email') . "</td>
            <td><input type='text' name='email' size='40'></td></tr>
            <tr><td class='rowhead has-text-left'>" . _('Comment') . "</td>
            <td><input type='text' name='comment' size='40'></td>
        </tr>
        <tr>
            <td colspan='2'>" . _('Use *@email.com as wildcard for domain.') . "</td>
        </tr>
        <tr>
            <td colspan='2' class='has-text-centered'>
                <input type='submit' value='" . _('Ok') . "' class='button is-small'>
            </td>
        </tr>";
$HTMLOUT .= main_table($body) . '
    </form>';
$fluent = $container->get(Database::class);
$count1 = $fluent->from('bannedemails')
                 ->select(null)
                 ->select('COUNT(id) AS count')
                 ->fetch('count');
$perpage = 15;
$pager = pager($perpage, $count1, 'staffpanel.php?tool=bannedemails&amp;');
$res = sql_query('SELECT b.id, b.added, b.addedby, b.comment, b.email, u.username FROM bannedemails AS b LEFT JOIN users AS u ON b.addedby=u.id ORDER BY added DESC ' . $pager['limit']) or sqlerr(__FILE__, __LINE__);
$HTMLOUT .= "<h1 class='has-text-centered'>" . _('Current Banned Emails') . '</h1>';
if ($count1 > $perpage) {
    $HTMLOUT .= $pager['pagertop'];
}
if (mysqli_num_rows($res) == 0) {
    $HTMLOUT .= stdmsg('Sorry', '<p><b>' . _('Nothing Found!') . '</b></p>');
} else {
    $heading = '
        <tr>
            <th>' . _('Added') . '</th>
            <th>' . _('Email') . '</th>
            <th>' . _('By') . '</th>
            <th>' . _('Comment') . '</th>
            <th>' . _('Remove?') . '</th>
        </tr>';
    $body = '';
    while ($arr = mysqli_fetch_assoc($res)) {
        $body .= '
        <tr>
            <td>' . get_date((int) $arr['added'], '') . '</td>
            <td>' . htmlsafechars($arr['email']) . '</td>
            <td>' . format_username((int) $arr['addedby']) . '</td>
            <td>' . htmlsafechars($arr['comment']) . "</td>
            <td><a href='staffpanel.php?tool=bannedemails&amp;remove=" . (int) $arr['id'] . "'>" . _('Remove it') . '</a></td>
        </tr>';
    }
    $HTMLOUT .= main_table($body, $heading);
}
if ($count1 > $perpage) {
    $HTMLOUT .= $pager['pagerbottom'];
}
$title = _('Banned Emails');
$breadcrumbs = [
    "<a href='{$site_config['paths']['baseurl']}/staffpanel.php'>" . _('Staff Panel') . '</a>',
    "<a href='{$_SERVER['PHP_SELF']}'>$title</a>",
];
echo stdhead($title, [], 'page-wrapper', $breadcrumbs) . wrapper($HTMLOUT) . stdfoot();
