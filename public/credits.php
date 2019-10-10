<?php

declare(strict_types = 1);

use Pu239\Database;

require_once __DIR__ . '/../include/bittorrent.php';
require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_bbcode.php';
require_once INCL_DIR . 'function_comments.php';
$user = check_user_status();
global $container, $site_config;

$HTMLOUT = '';
$action = isset($_GET['action']) ? htmlsafechars($_GET['action']) : '';
$act_validation = [
    '',
    'add',
    'edit',
    'delete',
    'update',
];

$id = (isset($_GET['id']) ? (int) $_GET['id'] : '');

if (!in_array($action, $act_validation)) {
    stderr(_('Error'), 'Unknown action.');
}

if (isset($_POST['action']) === 'add' && has_access($user['class'], UC_SYSOP, 'coder')) {
    $name = ($_POST['name']);
    $description = ($_POST['description']);
    $category = ($_POST['category']);
    $link = ($_POST['link']);
    $status = ($_POST['status']);
    $credit = ($_POST['credit']);
    sql_query('INSERT INTO modscredits (name, description,  category,  pu239lnk,  status, credit) VALUES(' . sqlesc($name) . ', ' . sqlesc($description) . ', ' . sqlesc($category) . ', ' . sqlesc($link) . ', ' . sqlesc($status) . ', ' . sqlesc($credit) . ')') or sqlerr(__FILE__, __LINE__);
    header("Location: {$_SERVER['PHP_SELF']}");
    die();
}

if ($action === 'delete' && has_access($user['class'], UC_SYSOP, 'coder')) {
    if (!$id) {
        stderr(_('Error'), _('Fuck something went Pete Tong!'));
    }
    sql_query("DELETE FROM modscredits where id='$id'") or sqlerr(__FILE__, __LINE__);
    header("Location: {$_SERVER['PHP_SELF']}");
    die();
}

if ($action === 'edit' && has_access($user['class'], UC_SYSOP, 'coder')) {
    $id = (int) $_GET['id'];
    $res = sql_query('SELECT name, description, category, pu239lnk, status, credit FROM modscredits WHERE id =' . $id . '') or sqlerr(__FILE__, __LINE__);
    if (mysqli_num_rows($res) == 0) {
        stderr(_('Error'), _('No credit mod found with that ID!'));
    }
    while ($mod = mysqli_fetch_assoc($res)) {
        $HTMLOUT .= "
        <form method='post' action='" . $_SERVER['PHP_SELF'] . '?action=update&amp;id=' . $id . "' enctype='multipart/form-data' accept-charset='utf-8'>
            <table>
                <tr>
                    <td class='rowhead'>" . _('Mod name') . "</td>
                    <td style='padding: 0'><input type='text' size='60' maxlength='120' name='name' " . "value='" . htmlsafechars($mod['name']) . "'></td>
                </tr>
                <tr>
                    <td class='rowhead'>" . _('Description') . "</td>
                    <td style='padding: 0'>
                        <input type='text' size='60' maxlength='120' name='description' value='" . htmlsafechars($mod['description']) . "'>
                    </td>
                </tr>
                <tr>
                    <td class='rowhead'>" . _('Category') . "</td>
                    <td style='padding: 0'>
                        <select name='category'>";
        $result = sql_query('SHOW COLUMNS FROM modscredits WHERE field = "category"');
        while ($row = mysqli_fetch_row($result)) {
            foreach (explode("','", substr($row[1], 6, -2)) as $v) {
                $HTMLOUT .= "
                            <option value='$v' " . ($mod['category'] == $v ? 'selected' : '') . ">$v</option>";
            }
        }
        $HTMLOUT .= "
                        </select>
                    </td>
                </tr>
                <tr><td class='rowhead'>" . _('Link') . "</td>
                    <td style='padding: 0'><input type='text' size='60' maxlength='120' name='link' " . "value='" . htmlsafechars($mod['pu239lnk']) . "'></td>
                </tr>
                <tr>
                    <td class='rowhead'>" . _('Status') . "</td>
                    <td class='is-paddingless'>
                        <select name='modstatus'>";
        $result = sql_query('SHOW COLUMNS FROM modscredits WHERE field="status"');
        while ($row = mysqli_fetch_row($result)) {
            foreach (explode("','", substr($row[1], 6, -2)) as $y) {
                $HTMLOUT .= "
                            <option value='$y' " . ($mod['status'] == $y ? 'selected' : '') . ">$y</option>";
            }
        }
        $HTMLOUT .= "
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class='rowhead'>" . _('Credits') . "</td>
                    <td style='padding: 0'>
                        <input type='text' size='60' maxlength='120' name='credits' value='" . htmlsafechars($mod['credit']) . "'>
                    </td>
                </tr>
                <tr>
                    <td colspan='2'><input type='submit' value='" . _('Submit') . "'></td>
                </tr>
            </table>
        </form>";
    }
    $title = _('Mod Credits');
    $breadcrumbs = [
        "<a href='{$_SERVER['PHP_SELF']}'>$title</a>",
    ];
    echo stdhead($title, [], 'page-wrapper', $breadcrumbs) . wrapper($HTMLOUT) . stdfoot();
} elseif ($action === 'update' && has_access($user['class'], UC_SYSOP, 'coder')) {
    $id = (int) $_GET['id'];
    if (!is_valid_id($id)) {
        stderr(_('Error'), _('Invalid ID'));
    }
    $res = sql_query('SELECT id FROM modscredits WHERE id=' . sqlesc($id));
    if (mysqli_num_rows($res) == 0) {
        stderr(_('Error'), _('Invalid ID'));
    }

    $name = $_POST['name'];
    $description = $_POST['description'];
    $category = $_POST['category'];
    $link = $_POST['link'];
    $modstatus = $_POST['modstatus'];
    $credit = $_POST['credits'];

    if (empty($name)) {
        stderr(_('Error'), _('You must specify a name for this credit.'));
    }

    if (empty($description)) {
        stderr(_('Error'), _('You must provide a description for this credit.'));
    }

    if (empty($link)) {
        stderr(_('Error'), _('You must provide a link for this credit.'));
    }

    if (empty($credit)) {
        stderr(_('Error'), _('You must provide a credit for the author(s) of this credit.'));
    }

    sql_query('UPDATE modscredits SET name = ' . sqlesc($name) . ', category = ' . sqlesc($category) . ', status = ' . sqlesc($modstatus) . ',  pu239lnk = ' . sqlesc($link) . ', credit = ' . sqlesc($credit) . ', description = ' . sqlesc($description) . ' WHERE id=' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
    header("Location: {$_SERVER['PHP_SELF']}");
    die();
}

$res = sql_query('SELECT * FROM modscredits') or sqlerr(__FILE__, __LINE__);
$fluent = $container->get(Database::class);
$credits = $fluent->from('modscredits')
                  ->orderBy('id')
                  ->fetchAll();
$heading = '
    <tr>
        <th>' . _('Name') . '</th>
        <th>' . _('Category') . '</th>
        <th>' . _('Status') . '</th>
        <th>' . _('Credits') . '</th>
    </tr>';

if (empty($credits)) {
    $body = "
    <tr>
        <td colspan='4' class='has-text-centered'>" . _('There are no credits so far!!') . '</td>
    </tr>';
} else {
    $body = '';
    foreach ($credits as $row) {
        $id = $row['id'];
        $name = $row['name'];
        $category = $row['category'];
        if ($row['status'] === 'In-Progress') {
            $status = '[b][color=#ff0000]' . $row['status'] . '[/color][/b]';
        } else {
            $status = '[b][color=#018316]' . $row['status'] . '[/color][/b]';
        }
        $link = $row['pu239lnk'];
        $credit = $row['credit'];
        $descr = $row['description'];

        $body .= "
    <tr>
        <td><a target='_blank' class='is-link' href='" . $link . "'>" . htmlsafechars(CutName($name, 60)) . '</a>';
        if (has_access($user['class'], UC_ADMINISTRATOR, 'coder')) {
            $body .= "&#160;<a class='is-link_blue' href='?action=edit&amp;id=" . $id . "'>[" . _('Edit') . "]</a>&#160;<a class='is-link_blue' href=\"javascript:confirm_delete(" . $id . ');">[' . _('Delete') . ']</a>';
        }

        $body .= "<br><span class='small'>" . htmlsafechars($descr) . '</span></td>
        <td><b>' . htmlsafechars($category) . '</b></td>
        <td><b>' . format_comment($status) . '</b></td>
        <td>' . htmlsafechars($credit) . '</td>
    </tr>';
    }
}
$HTMLOUT .= main_table($body, $heading);

if ($user['class'] >= UC_MAX) {
    $HTMLOUT .= "
    <form method='post' action='{$_SERVER['PHP_SELF']}' enctype='multipart/form-data' accept-charset='utf-8'>
    <h2 class='has-text-centered top20'>" . _('Add Mods & Credits') . "</h2>
        <input type='hidden' name='action' value='add'>";
    $body = '
    <tr>
        <td>' . _('Name:') . "</td>
        <td><input name='name' type='text' class='w-100' required></td>
    </tr>
    <tr>
        <td>" . _('Description:') . "</td>
        <td><input name='description' type='text' class='w-100' maxlength='120' required></td>
    </tr>
    <tr>
        <td>" . _('Category:') . "</td>
        <td>
            <select name='category' required>
                <option value=''>" . _('Select One') . "</option>
                <option value='Addon'>" . _('Addon') . "</option>
                <option value='Forum'>" . _('Forum') . "</option>
                <option value='Message/Email'>" . _('Message/E-mail') . "</option>
                <option value='Display/Style'>" . _('Display/Style') . "</option>
                <option value='Staff/Tools'>" . _('Staff Tools') . "</option>
                <option value='Browse/Torrent/Details'>" . _('Browse/Torrents/Details') . "</option>
                <option value='Misc'>" . _('Misc') . '</option>
            </select>
        </td>
    </tr>
    <tr>
        <td>' . _('Link:') . "</td>
        <td><input name='link' type='text' class='w-100' required></td>
    </tr>
    <tr>
        <td>" . _('Status:') . "</td>
        <td>
            <select name='status' required>
                <option value=''>" . _('Select One') . "</option>
                <option value='In-Progress'>" . _('In-Progress') . "</option>
                <option value='Complete'>" . _('Complete') . '</option>
            </select>
        </td>
    </tr>
    <tr>
        <td>' . _('Credits:') . "</td>
        <td><input name='credit' type='text' class='w-100' maxlength='120' required><br><span class='small'>" . _('Values separated by commas') . "</span></td>
    </tr>
    <tr>
        <td colspan='2' class='has-text-centered'>
            <input type='submit' value='" . _('Add Credits') . "' class='button is-small'>
        </td>
    </tr>";
    $HTMLOUT .= main_table($body) . '
    </form>';
}
$title = _('Mod Credits');
$breadcrumbs = [
    "<a href='{$_SERVER['PHP_SELF']}'>$title</a>",
];
echo stdhead($title, [], 'page-wrapper', $breadcrumbs) . wrapper($HTMLOUT) . stdfoot();
