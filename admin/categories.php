<?php
require_once INCL_DIR . 'user_functions.php';
require_once CLASS_DIR . 'class_check.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
$lang = array_merge($lang, load_language('ad_categories'));
$params = array_merge($_GET, $_POST);
$params['mode'] = isset($params['mode']) ? $params['mode'] : '';
switch ($params['mode']) {
    case 'takemove_cat':
        move_cat();
        break;

    case 'move_cat':
        move_cat_form();
        break;

    case 'takeadd_cat':
        add_cat();
        break;

    case 'takedel_cat':
        delete_cat();
        break;

    case 'del_cat':
        delete_cat_form();
        break;

    case 'takeedit_cat':
        edit_cat();
        break;

    case 'edit_cat':
        edit_cat_form();
        break;

    case 'cat_form':
        show_cat_form();
        break;

    default:
        show_categories();
        break;
}
function move_cat()
{
    global $site_config, $params, $mc1, $lang;
    if ((!isset($params['id']) or !is_valid_id($params['id'])) or (!isset($params['new_cat_id']) or !is_valid_id($params['new_cat_id']))) {
        stderr($lang['categories_error'], $lang['categories_no_id']);
    }
    if (!is_valid_id($params['new_cat_id']) or ($params['id'] == $params['new_cat_id'])) {
        stderr($lang['categories_error'], $lang['categories_move_error2']);
    }
    $old_cat_id = intval($params['id']);
    $new_cat_id = intval($params['new_cat_id']);
    // make sure both categories exist
    $q = sql_query("SELECT id FROM categories WHERE id IN($old_cat_id, $new_cat_id)");
    if (2 != mysqli_num_rows($q)) {
        stderr($lang['categories_error'], $lang['categories_exist_error']);
    }
    //all go
    sql_query('UPDATE torrents SET category = ' . sqlesc($new_cat_id) . ' WHERE category = ' . sqlesc($old_cat_id));
    $mc1->delete_value('genrelist');
    $mc1->delete_value('categories');
    if (-1 != mysqli_affected_rows($GLOBALS['___mysqli_ston'])) {
        header("Location: {$site_config['baseurl']}/staffpanel.php?tool=categories&action=categories");
    } else {
        stderr($lang['categories_error'], $lang['categories_move_error4']);
    }
}

function move_cat_form()
{
    global $params, $lang;
    if (!isset($params['id']) or !is_valid_id($params['id'])) {
        stderr($lang['categories_error'], $lang['categories_no_id']);
    }
    $q = sql_query('SELECT * FROM categories WHERE id = ' . intval($params['id']));
    if (false == mysqli_num_rows($q)) {
        stderr($lang['categories_error'], $lang['categories_exist_error']);
    }
    $r = mysqli_fetch_assoc($q);
    $check = '';
    $select = "<select name='new_cat_id'>\n<option value='0'>{$lang['categories_select']}</option>\n";
    $cats = genrelist();
    foreach ($cats as $c) {
        $select .= ($c['id'] != $r['id']) ? "<option value='{$c['id']}'>" . htmlsafechars($c['name'], ENT_QUOTES) . "</option>\n" : '';
    }
    $select .= "</select>\n";
    $check .= "<tr>
      <td width='50%'><span style='color:limegreen;font-weight:bold;'>{$lang['categories_select_new']}</span></td>
      <td>$select</td>
    </tr>";
    $htmlout = '';
    $htmlout .= "<form action='staffpanel.php?tool=categories&amp;action=categories' method='post'>
      <input type='hidden' name='mode' value='takemove_cat' />
      <input type='hidden' name='id' value='{$r['id']}' />
    
      <table class='torrenttable' width='80%' bgcolor='#555555' cellspacing='2' cellpadding='4px'>
      <tr>
        <td colspan='2' class='colhead'>" . $lang['categories_move_about'] . htmlsafechars($r['name'], ENT_QUOTES) . "</td>
      </tr>
      <tr>
        <td colspan='2'>{$lang['categories_move_note']}</td>
      </tr>
      <tr>
        <td width='50%'><span style='color:red;font-weight:bold;'>{$lang['categories_move_old']}</span></td>
        <td>" . htmlsafechars($r['name'], ENT_QUOTES) . "</td>
      </tr>
      {$check}
      <tr>
        <td colspan='2'>
         <input type='submit' class='btn' value='{$lang['categories_move']}' /><input type='button' class='btn' value={$lang['categories_cancel']}' onclick=\"history.go(-1)\" /></td>
      </tr>
      </table>
      </form>";
    echo stdhead($lang['categories_move_stdhead'] . $r['name']) . $htmlout . stdfoot();
}

function add_cat()
{
    global $site_config, $params, $mc1, $lang;
    foreach ([
                 'new_cat_name',
                 'new_cat_desc',
                 'new_cat_image',
             ] as $x) {
        if (!isset($params[$x]) or empty($params[$x])) {
            stderr($lang['categories_error'], $lang['categories_add_error1']);
        }
    }
    if (!preg_match("/^cat_[A-Za-z0-9_]+\.(?:gif|jpg|jpeg|png)$/i", $params['new_cat_image'])) {
        stderr($lang['categories_error'], $lang['categories_add_error2']);
    }
    $cat_name = sqlesc($params['new_cat_name']);
    $cat_desc = sqlesc($params['new_cat_desc']);
    $cat_image = sqlesc($params['new_cat_image']);
    sql_query("INSERT INTO categories (name, cat_desc, image)
                  VALUES($cat_name, $cat_desc, $cat_image)");
    $mc1->delete_value('genrelist');
    $mc1->delete_value('categories');
    if (-1 == mysqli_affected_rows($GLOBALS['___mysqli_ston'])) {
        stderr($lang['categories_error'], $lang['categories_exist_error']);
    } else {
        header("Location: {$site_config['baseurl']}/staffpanel.php?tool=categories&action=categories");
    }
}

function delete_cat()
{
    global $site_config, $params, $mc1, $lang;
    if (!isset($params['id']) or !is_valid_id($params['id'])) {
        stderr($lang['categories_error'], $lang['categories_no_id']);
    }
    $q = sql_query('SELECT * FROM categories WHERE id = ' . intval($params['id']));
    if (false == mysqli_num_rows($q)) {
        stderr($lang['categories_error'], $lang['categories_exist_error']);
    }
    $r = mysqli_fetch_assoc($q);
    $old_cat_id = intval($r['id']);
    if (isset($params['new_cat_id'])) {
        if (!is_valid_id($params['new_cat_id']) or ($r['id'] == $params['new_cat_id'])) {
            stderr($lang['categories_error'], $lang['categories_exist_error']);
        }
        $new_cat_id = intval($params['new_cat_id']);
        //make sure category isn't out of range before moving torrents! else orphans!
        $q = sql_query('SELECT COUNT(*) FROM categories WHERE id = ' . sqlesc($new_cat_id));
        $count = mysqli_fetch_array($q, MYSQLI_NUM);
        if (!$count[0]) {
            stderr($lang['categories_error'], $lang['categories_exist_error']);
        }
        //all go
        sql_query('UPDATE torrents SET category = ' . sqlesc($new_cat_id) . ' WHERE category = ' . sqlesc($old_cat_id));
    }
    sql_query('DELETE FROM categories WHERE id = ' . sqlesc($old_cat_id));
    $mc1->delete_value('genrelist');
    $mc1->delete_value('categories');
    if (mysqli_affected_rows($GLOBALS['___mysqli_ston'])) {
        header("Location: {$site_config['baseurl']}/staffpanel.php?tool=categories&action=categories");
    } else {
        stderr($lang['categories_error'], $lang['categories_del_error1']);
    }
}

function delete_cat_form()
{
    global $params, $lang;
    if (!isset($params['id']) or !is_valid_id($params['id'])) {
        stderr($lang['categories_error'], $lang['categories_no_id']);
    }
    $q = sql_query('SELECT * FROM categories WHERE id = ' . intval($params['id']));
    if (false == mysqli_num_rows($q)) {
        stderr($lang['categories_error'], $lang['categories_exist_error']);
    }
    $r = mysqli_fetch_assoc($q);
    $q = sql_query('SELECT COUNT(*) FROM torrents WHERE category = ' . intval($r['id']));
    $count = mysqli_fetch_array($q, MYSQLI_NUM);
    $check = '';
    if ($count[0]) {
        $select = "<select name='new_cat_id'>\n<option value='0'>{$lang['categories_select']}</option>\n";
        $cats = genrelist2();
        foreach ($cats as $c) {
            $select .= ($c['id'] != $r['id']) ? "<option value='{$c['id']}'>" . htmlsafechars($c['name'], ENT_QUOTES) . "</option>\n" : '';
        }
        $select .= "</select>\n";
        $check .= "<tr>
        <td width='50%'>{$lang['categories_select_new']}<br><span style='color:red;font-weight:bold;'>{$lang['categories_del_warning']}</span></td>
        <td>$select</td>
      </tr>";
    }
    $htmlout = '';
    $htmlout .= "<form action='staffpanel.php?tool=categories&amp;action=categories' method='post'>
      <input type='hidden' name='mode' value='takedel_cat' />
      <input type='hidden' name='id' value='" . (int)$r['id'] . "' />
    
      <table class='torrenttable' width='80%' bgcolor='#555555' cellspacing='2' cellpadding='2'>
      <tr>
        <td colspan='2' class='colhead'>{$lang['categories_del_about']}" . htmlsafechars($r['name'], ENT_QUOTES) . "</td>
      </tr>
      <tr>
        <td width='50%'>{$lang['categories_del_name']}</td>
        <td>" . htmlsafechars($r['name'], ENT_QUOTES) . "</td>
      </tr>
      <tr>
        <td>{$lang['categories_del_description']}</td>
        <td>" . htmlsafechars($r['cat_desc'], ENT_QUOTES) . "</td>
      </tr>
      <tr>
        <td>{$lang['categories_del_image']}</td>
        <td>" . htmlsafechars($r['image'], ENT_QUOTES) . "</td>
      </tr>
      {$check}
      <tr>
        <td colspan='2'>
         <input type='submit' class='btn' value='{$lang['categories_del_delete']}' /><input type='button' class='btn' value='{$lang['categories_cancel']}' onclick=\"history.go(-1)\" /></td>
      </tr>
      </table>
      </form>";
    echo stdhead($lang['categories_del_stdhead'] . $r['name']) . $htmlout . stdfoot();
}

function edit_cat()
{
    global $site_config, $params, $mc1, $lang;
    if (!isset($params['id']) or !is_valid_id($params['id'])) {
        stderr($lang['categories_error'], $lang['categories_no_id']);
    }
    foreach ([
                 'cat_name',
                 'cat_desc',
                 'cat_image',
             ] as $x) {
        if (!isset($params[$x]) or empty($params[$x])) {
            stderr($lang['categories_error'], $lang['categories_edit_error1'] . $x . '');
        }
    }
    if (!preg_match("/^cat_[A-Za-z0-9_]+\.(?:gif|jpg|jpeg|png)$/i", $params['cat_image'])) {
        stderr($lang['categories_error'], $lang['categories_edit_error2']);
    }
    $cat_name = sqlesc($params['cat_name']);
    $cat_desc = sqlesc($params['cat_desc']);
    $cat_image = sqlesc($params['cat_image']);
    $cat_id = intval($params['id']);
    sql_query("UPDATE categories SET name = $cat_name, cat_desc = $cat_desc, image = $cat_image WHERE id = $cat_id");
    $mc1->delete_value('genrelist');
    $mc1->delete_value('categories');
    if (-1 == mysqli_affected_rows($GLOBALS['___mysqli_ston'])) {
        stderr($lang['categories_error'], $lang['categories_exist_error']);
    } else {
        header("Location: {$site_config['baseurl']}/staffpanel.php?tool=categories&action=categories");
    }
}

function edit_cat_form()
{
    global $site_config, $params, $lang;
    if (!isset($params['id']) or !is_valid_id($params['id'])) {
        stderr($lang['categories_error'], $lang['categories_no_id']);
    }
    $htmlout = '';
    $q = sql_query('SELECT * FROM categories WHERE id = ' . intval($params['id']));
    if (false == mysqli_num_rows($q)) {
        stderr($lang['categories_error'], $lang['categories_exist_error']);
    }
    $r = mysqli_fetch_assoc($q);
    $dh = opendir($site_config['pic_base_url'] . 'caticons/1');
    $files = [];
    while (false !== ($file = readdir($dh))) {
        if (($file != '.') && ($file != '..')) {
            if (preg_match("/^cat_[A-Za-z0-9_]+\.(?:gif|jpg|jpeg|png)$/i", $file)) {
                $files[] = $file;
            }
        }
    }
    closedir($dh);
    if (is_array($files) and count($files)) {
        $select = "<select name='cat_image'>\n<option value='0'>{$lang['categories_edit_select']}</option>\n";
        foreach ($files as $f) {
            $selected = ($f == $r['image']) ? " selected='selected'" : '';
            $select .= "<option value='" . htmlsafechars($f, ENT_QUOTES) . "'$selected>" . htmlsafechars($f, ENT_QUOTES) . "</option>\n";
        }
        $select .= "</select>\n";
        $check = "<tr>
        <td width='50%'>{$lang['categories_edit_select_new']}<br><span style='color:limegreen;font-weight:bold;'>{$lang['categories_edit_info']}</span></td>
        <td>$select</td>
      </tr>";
    } else {
        $check = "<tr>
        <td width='50%'>{$lang['categories_edit_select_new']}</td>
        <td><span style='color:red;font-weight:bold;'>{$lang['categories_edit_warning']}</span></td>
      </tr>";
    }
    $htmlout .= "<form action='staffpanel.php?tool=categories&amp;action=categories' method='post'>
      <input type='hidden' name='mode' value='takeedit_cat' />
      <input type='hidden' name='id' value='" . (int)$r['id'] . "' />
    
      <table class='torrenttable' width='80%' bgcolor='#555555' cellspacing='2' cellpadding='2'>
      <tr>
        <td>{$lang['categories_edit_name']}</td>
        <td><input type='text' name='cat_name' class='option' size='50' value='" . htmlsafechars($r['name'], ENT_QUOTES) . "' /></td>
      </tr>
      <tr>
        <td>{$lang['categories_del_description']}</td>
        <td><textarea cols='50' rows='5' name='cat_desc'>" . htmlsafechars($r['cat_desc'], ENT_QUOTES) . "</textarea></td>
      </tr>
      {$check}
      <tr>
        <td colspan='2'>
         <input type='submit' class='btn' value='{$lang['categories_edit_edit']}' /><input type='button' class='btn' value='{$lang['categories_cancel']}' onclick=\"history.go(-1)\" /></td>
      </tr>
      </table>
      </form>";
    echo stdhead($lang['categories_edit_stdhead'] . $r['name']) . $htmlout . stdfoot();
}

function show_categories()
{
    global $site_config, $lang;
    $htmlout = '';
    $dh = opendir($site_config['pic_base_url'] . 'caticons/1');
    $files = [];
    while (false !== ($file = readdir($dh))) {
        if (($file != '.') && ($file != '..')) {
            if (preg_match("/^cat_[A-Za-z0-9_]+\.(?:gif|jpg|jpeg|png)$/i", $file)) {
                $files[] = $file;
            }
        }
    }
    closedir($dh);
    if (is_array($files) and count($files)) {
        $select = "<select name='new_cat_image'>\n<option value='0'>{$lang['categories_edit_select']}</option>\n";
        foreach ($files as $f) {
            $i = 0;
            $select .= "<option value='" . htmlsafechars($f, ENT_QUOTES) . "'>" . htmlsafechars($f, ENT_QUOTES) . "</option>\n";
            ++$i;
        }
        $select .= "</select>\n";
        $check = "<tr>
        <td width='50%'>{$lang['categories_edit_select_new']}<br><span style='color:limegreen;font-weight:bold;'>{$lang['categories_edit_warning1']}</span></td>
        <td>$select</td>
      </tr>";
    } else {
        $check = "<tr>
        <td width='50%'>{$lang['categories_edit_select_new']}</td>
        <td><span style='color:red;font-weight:bold;'{$lang['categories_edit_select_warning']}</span></td>
      </tr>";
    }
    $htmlout .= "<form action='staffpanel.php?tool=categories&amp;action=categories' method='post'>
    <input type='hidden' name='mode' value='takeadd_cat' />
    
    <table class='torrenttable' border='1' width='80%' bgcolor='#555555' cellspacing='2' cellpadding='2'>
    <tr>
      <td class='colhead' colspan='2'>
        <b>{$lang['categories_show_make']}</b>
      </td>
    </tr>
    <tr>
      <td>{$lang['categories_edit_name']}</td>
      <td><input type='text' name='new_cat_name' size='50' maxlength='50' /></td>
    </tr>
    <tr>
      <td>{$lang['categories_del_description']}</td>
      <td><textarea cols='50' rows='5' name='new_cat_desc'></textarea></td>
    </tr>
    <!--<tr>
      <td>{$lang['categories_show_file']}</td>
      <td><input type='text' name='new_cat_image' class='option' size='50' /></td>
    </tr>-->
    {$check}
    <tr>
      <td colspan='2'>
        <input type='submit' value='{$lang['categories_show_add']}' class='btn' />
        <input type='reset' value='{$lang['categories_show_reset']}' class='btn' />
      </td>
    </tr>
    </table>
    </form>


    <h2>{$lang['categories_show_head']}</h2>
    <table class='torrenttable' border='1' width='80%' bgcolor='#333333' cellpadding='5px'>
    <tr>
      <td class='colhead' width='60'>{$lang['categories_show_id']}</td>
      <td class='colhead' width='60'>{$lang['categories_show_name']}</td>
      <td class='colhead' width='200'>{$lang['categories_show_descr']}</td>
      <td class='colhead' width='45'>{$lang['categories_show_image']}</td>
      <td class='colhead' width='40'>{$lang['categories_show_edit']}</td>
      <td class='colhead' width='40'>{$lang['categories_show_delete']}</td>
      <td class='colhead' width='40'>{$lang['categories_show_move']}</td>
    </tr>";
    $query = sql_query('SELECT * FROM categories ORDER BY id ASC');
    if (false == mysqli_num_rows($query)) {
        $htmlout = '<h1>' . $lang['categories_show_oops'] . '</h1>';
    } else {
        while ($row = mysqli_fetch_assoc($query)) {
            $cat_image = file_exists($site_config['pic_base_url'] . 'caticons/1/' . $row['image']) ? "<img border='0' src='{$site_config['pic_base_url']}caticons/1/" . htmlsafechars($row['image']) . "' alt='" . (int)$row['id'] . "' />" : "{$lang['categories_show_no_image']}";
            $htmlout .= "<tr>
          <td height='48' width='60'><b>{$lang['categories_show_id2']} (" . (int)$row['id'] . ")</b></td>	
          <td width='120'>" . htmlsafechars($row['name']) . "</td>
          <td width='250'>" . htmlsafechars($row['cat_desc']) . "</td>
          <td width='45'>$cat_image</td>
          <td width='18'><a href='staffpanel.php?tool=categories&amp;action=categories&amp;mode=edit_cat&amp;id=" . (int)$row['id'] . "'>
            <img src='{$site_config['pic_base_url']}aff_tick.gif' alt='{$lang['categories_show_edit2']}' title='{$lang['categories_show_edit']}' width='12' height='12' border='0' /></a></td>
          <td width='18'><a href='staffpanel.php?tool=categories&amp;action=categories&amp;mode=del_cat&amp;id=" . (int)$row['id'] . "'>
            <img src='{$site_config['pic_base_url']}aff_cross.gif' alt='{$lang['categories_show_delete2']}' title='{$lang['categories_show_delete']}' width='12' height='12' border='0' /></a></td>
          <td width='18'><a href='staffpanel.php?tool=categories&amp;action=categories&amp;mode=move_cat&amp;id=" . (int)$row['id'] . "'>
            <img src='{$site_config['pic_base_url']}plus.gif' alt='{$lang['categories_show_move2']}' title='{$lang['categories_show_move']}' width='12' height='12' border='0' /></a></td>
        </tr>";
        }
    } //endif
    $htmlout .= '</table>';
    echo stdhead($lang['categories_show_stdhead']) . $htmlout . stdfoot();
}
