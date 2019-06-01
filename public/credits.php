<?php

declare(strict_types = 1);

require_once __DIR__ . '/../include/bittorrent.php';
require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_bbcode.php';
require_once INCL_DIR . 'function_comments.php';
check_user_status();
$lang = array_merge(load_language('global'), load_language('credits'));
global $CURUSER, $site_config;

$HTMLOUT = '';
$action = isset($_GET['action']) ? htmlsafechars(trim($_GET['action'])) : '';
$act_validation = [
    '',
    'add',
    'edit',
    'delete',
    'update',
];

$id = (isset($_GET['id']) ? (int) $_GET['id'] : '');

if (!in_array($action, $act_validation)) {
    stderr('Error', 'Unknown action.');
}

if (isset($_POST['action']) === 'add' && $CURUSER['class'] >= UC_SYSOP) {
    $name = ($_POST['name']);
    $description = ($_POST['description']);
    $category = ($_POST['category']);
    $link = ($_POST['link']);
    $status = ($_POST['status']);
    $credit = ($_POST['credit']);
    sql_query('INSERT INTO modscredits (name, description,  category,  pu239lnk,  status, credit) VALUES(' . sqlesc($name) . ', ' . sqlesc($description) . ', ' . sqlesc($category) . ', ' . sqlesc($link) . ', ' . sqlesc($status) . ', ' . sqlesc($credit) . ')') or sqlerr(__FILE__, __LINE__);
    header("Location: {$site_config['paths']['baseurl']}/credits.php");
    die();
}

if ($action === 'delete' && $CURUSER['class'] >= UC_SYSOP) {
    if (!$id) {
        stderr("{$lang['credits_error']}", "{$lang['credits_error2']}");
    }
    sql_query("DELETE FROM modscredits where id='$id'") or sqlerr(__FILE__, __LINE__);
    header("Location: {$site_config['paths']['baseurl']}/credits.php");
    die();
}

if ($action === 'edit' && $CURUSER['class'] >= UC_SYSOP) {
    $id = (int) $_GET['id'];
    $res = sql_query('SELECT name, description, category, pu239lnk, status, credit FROM modscredits WHERE id =' . $id . '') or sqlerr(__FILE__, __LINE__);
    if (mysqli_num_rows($res) == 0) {
        stderr("{$lang['credits_error']}", "{$lang['credits_nocr']}");
    }
    while ($mod = mysqli_fetch_assoc($res)) {
        $HTMLOUT .= "<form method='post' action='" . $_SERVER['PHP_SELF'] . '?action=update&amp;id=' . $id . "' accept-charset='utf-8'>
  <table width='50%'>
    <tr><td class='rowhead'>{$lang['credits_mod']}</td>" . "<td style='padding: 0'><input type='text' size='60' maxlength='120' name='name' " . "value='" . htmlsafechars($mod['name']) . "'></td></tr>\n" . "<tr>
    <td class='rowhead'>{$lang['credits_description']}</td>" . "<td style='padding: 0'>
    <input type='text' size='60' maxlength='120' name='description' value='" . htmlsafechars($mod['description']) . "'></td></tr>\n" . "<tr>
    <td class='rowhead'>{$lang['credits_category']}</td>
  <td style='padding: 0'>
  <select name='category'>";

        $result = sql_query('SHOW COLUMNS FROM modscredits WHERE field="category"');
        while ($row = mysqli_fetch_row($result)) {
            foreach (explode("','", substr($row[1], 6, -2)) as $v) {
                $HTMLOUT .= "<option value='$v" . ($mod['category'] == $v ? ' selected' : '') . "'>$v</option>";
            }
        }

        $HTMLOUT .= '</select></td></tr>';

        $HTMLOUT .= "<tr><td class='rowhead'>{$lang['credits_link']}</td>" . "<td style='padding: 0'><input type='text' size='60' maxlength='120' name='link' " . "value='" . htmlsafechars($mod['pu239lnk']) . "'></td></tr>\n" . "<tr>
  <td class='rowhead'>{$lang['credits_status']}</td>
  <td style='padding: 0'>
  <select name='modstatus'>";

        $result = sql_query('SHOW COLUMNS FROM modscredits WHERE field="status"');
        while ($row = mysqli_fetch_row($result)) {
            foreach (explode("','", substr($row[1], 6, -2)) as $y) {
                $HTMLOUT .= "<option value='$y" . ($mod['status'] == $y ? ' selected' : '') . "'>$y</option>";
            }
        }

        $HTMLOUT .= '</select></td></tr>';

        $HTMLOUT .= "<tr><td class='rowhead'>{$lang['credits_credits']}</td><td style='padding: 0'>
  <input type='text' size='60' maxlength='120' name='credits' value='" . htmlsafechars($mod['credit']) . "'></td></tr>\n";
        $HTMLOUT .= "<tr><td colspan='2'><input type='submit' value='Submit'></td></tr>\n";
        $HTMLOUT .= '</table></form>';
    }
    echo stdhead($lang['credits_editmod']) . $HTMLOUT . stdfoot();
    die();
} elseif ($action === 'update' && $CURUSER['class'] >= UC_SYSOP) {
    $id = (int) $_GET['id'];
    if (!is_valid_id($id)) {
        stderr('Error', 'Invalid ID!');
    }
    $res = sql_query('SELECT id FROM modscredits WHERE id=' . sqlesc($id));
    if (mysqli_num_rows($res) == 0) {
        stderr("{$lang['credits_error']}", "{$lang['credits_nocr']}");
    }

    $name = $_POST['name'];
    $description = $_POST['description'];
    $category = $_POST['category'];
    $link = $_POST['link'];
    $modstatus = $_POST['modstatus'];
    $credit = $_POST['credits'];

    if (empty($name)) {
        stderr("{$lang['credits_error']}", "{$lang['credits_error3']}");
    }

    if (empty($description)) {
        stderr("{$lang['credits_error']}", "{$lang['credits_error4']}");
    }

    if (empty($link)) {
        stderr("{$lang['credits_error']}", "{$lang['credits_error5']}");
    }

    if (empty($credit)) {
        stderr("{$lang['credits_error']}", "{$lang['credits_error6']}");
    }

    sql_query('UPDATE modscredits SET name = ' . sqlesc($name) . ', category = ' . sqlesc($category) . ', status = ' . sqlesc($modstatus) . ',  pu239lnk = ' . sqlesc($link) . ', credit = ' . sqlesc($credit) . ', description = ' . sqlesc($description) . ' WHERE id=' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
    header("Location: {$_SERVER['PHP_SELF']}");
    die();
}

$HTMLOUT .= "<script>
  <!--
  function confirm_delete(id)
  {
    if(confirm('Are you sure you want to delete this mod credit?'))
    {
    self.location.href='" . $_SERVER['PHP_SELF'] . "?action=delete&id='+id;
    }
  }
  //-->
  </script>";

$res = sql_query('SELECT * FROM modscredits') or sqlerr(__FILE__, __LINE__);

$credits = $fluent->from('modscredits')
                  ->orderBy('id')
                  ->fetchAll();
$heading = "
    <tr>
        <th>{$lang['credits_name']}</th>
        <th>{$lang['credits_category']}</th>
        <th>{$lang['credits_status']}</th>
        <th>{$lang['credits_credits']}</th>
    </tr>";

if (empty($credits)) {
    $body = "
    <tr>
        <td colspan='4' class='has-text-centered'>{$lang['credits_nosofar']}</td>
    </tr>";
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
        if ($CURUSER['class'] >= UC_ADMINISTRATOR) {
            $body .= "&#160;<a class='is-link_blue' href='?action=edit&amp;id=" . $id . "'>{$lang['credits_edit']}</a>&#160;<a class='is-link_blue' href=\"javascript:confirm_delete(" . $id . ");\">{$lang['credits_delete']}</a>";
        }

        $body .= "<br><font class='small'>" . htmlsafechars($descr) . '</font></td>
        <td><b>' . htmlsafechars($category) . '</b></td>
        <td><b>' . format_comment($status) . '</b></td>
        <td>' . htmlsafechars($credit) . '</td>
    </tr>';
    }
}
$HTMLOUT .= main_table($body, $heading);

if ($CURUSER['class'] >= UC_MAX) {
    $HTMLOUT .= "
    <form method='post' action='{$_SERVER['PHP_SELF']}' accept-charset='utf-8'>
    <h2 class='has-text-centered top20'>{$lang['credits_add']}</h2>
        <input type='hidden' name='action' value='add'>";
    $body = "
    <tr>
        <td>{$lang['credits_name1']}</td>
        <td><input name='name' type='text' class='w-100'></td>
    </tr>
    <tr>
        <td>{$lang['credits_description1']}</td>
        <td><input name='description' type='text' class='w-100' maxlength='120'></td>
    </tr>
    <tr>
        <td>{$lang['credits_category1']}</td>
        <td>
            <select name='category'>
                <option value='Addon'>{$lang['credits_addon']}</option>
                <option value='Forum'>{$lang['credits_forum']}</option>
                <option value='Message/Email'>{$lang['credits_mes']}</option>
                <option value='Display/Style'>{$lang['credits_disp']}</option>
                <option value='Staff/Tools'>{$lang['credits_staff']}</option>
                <option value='Browse/Torrent/Details'>{$lang['credits_btd']}</option>
                <option value='Misc'>{$lang['credits_misc']}</option>
            </select>
        </td>
    </tr>
    <tr>
        <td>{$lang['credits_link1']}</td>
        <td><input name='link' type='text' class='w-100'></td>
    </tr>
    <tr>
        <td>{$lang['credits_status1']}</td>
        <td>
            <select name='status'>
                <option value='In-Progress'>{$lang['credits_progress']}</option>
                <option value='Complete'>{$lang['credits_complete']}</option>
            </select>
        </td>
    </tr>
    <tr>
        <td>{$lang['credits_credits1']}</td>
        <td><input name='credit' type='text' class='w-100' maxlength='120'><br><font class='small'>{$lang['credits_val']}</font></td>
    </tr>
    <tr>
        <td colspan='2' class='has-text-centered'>
            <input type='submit' value='{$lang['credits_addc']}' class='button is-small'>
        </td>
    </tr>";
    $HTMLOUT .= main_table($body) . '
    </form>';
}
echo stdhead($lang['credits_headers']) . wrapper($HTMLOUT) . stdfoot();
