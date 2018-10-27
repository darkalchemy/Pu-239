<?php

require_once INCL_DIR . 'user_functions.php';
require_once CLASS_DIR . 'class_check.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
global $lang;

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

    //    case 'cat_form':
    //        show_cat_form();
    //        break;

    default:
        show_categories();
        break;
}
function move_cat()
{
    global $site_config, $params, $lang, $cache, $mysqli;

    if ((!isset($params['id']) || !is_valid_id($params['id'])) || (!isset($params['new_cat_id']) || !is_valid_id($params['new_cat_id']))) {
        stderr($lang['categories_error'], $lang['categories_no_id']);
    }
    if (!is_valid_id($params['new_cat_id']) || ($params['id'] == $params['new_cat_id'])) {
        stderr($lang['categories_error'], $lang['categories_move_error2']);
    }
    $old_cat_id = intval($params['id']);
    $new_cat_id = intval($params['new_cat_id']);

    $q = sql_query("SELECT id FROM categories WHERE id IN($old_cat_id, $new_cat_id)");
    if (mysqli_num_rows($q) != 2) {
        stderr($lang['categories_error'], $lang['categories_exist_error']);
    }
    sql_query('UPDATE torrents SET category = ' . sqlesc($new_cat_id) . ' WHERE category = ' . sqlesc($old_cat_id));
    $cache->delete('genrelist');
    $cache->delete('categories');
    if (-1 != mysqli_affected_rows($mysqli)) {
        header("Location: {$site_config['baseurl']}/staffpanel.php?tool=categories&action=categories");
    } else {
        stderr($lang['categories_error'], $lang['categories_move_error4']);
    }
}

function move_cat_form()
{
    global $params, $lang, $site_config;
    if (!isset($params['id']) || !is_valid_id($params['id'])) {
        stderr($lang['categories_error'], $lang['categories_no_id']);
    }
    $q = sql_query('SELECT * FROM categories WHERE id = ' . intval($params['id']));
    if (false == mysqli_num_rows($q)) {
        stderr($lang['categories_error'], $lang['categories_exist_error']);
    }
    $r = mysqli_fetch_assoc($q);
    $select = "
            <select name='new_cat_id'>
                <option value='0'>{$lang['categories_select']}</option>";
    $cats = genrelist();
    foreach ($cats as $c) {
        $select .= ($c['id'] != $r['id']) ? "
                <option value='{$c['id']}'>" . htmlsafechars($c['name'], ENT_QUOTES) . '</option>' : '';
    }
    $select .= '
            </select>';
    $htmlout = "
        <form action='{$site_config['baseurl']}/staffpanel.php?tool=categories&amp;action=categories' method='post'>
            <input type='hidden' name='mode' value='takemove_cat' />
            <input type='hidden' name='id' value='{$r['id']}' />
            <h2 class='has-text-centered'>{$lang['categories_move_about']} " . htmlsafechars($r['name'], ENT_QUOTES) . "</h2>
            <h3 class='has-text-centered'>{$lang['categories_move_note']}</h3>";
    $htmlout .= main_div("
            <div class='w-50 has-text-centered'>
                <p class='has-text-danger level'>{$lang['categories_move_old']} <span class='has-text-white'>" . htmlsafechars($r['name'], ENT_QUOTES) . "</span></p>
                <p class='has-text-green level'>{$lang['categories_select_new']} $select</p>
                <input type='submit' class='button is-small right20' value='{$lang['categories_move']}' />
                <input type='button' class='button is-small' value='{$lang['categories_cancel']}' onclick=\"history.go(-1)\" />
            </div>");
    $htmlout .= '
        </form>';

    echo stdhead($lang['categories_move_stdhead'] . $r['name']) . wrapper($htmlout) . stdfoot();
}

function add_cat()
{
    global $site_config, $params, $lang, $cache, $mysqli;

    foreach ([
                 'new_cat_name',
                 'new_cat_desc',
                 'new_cat_image',
             ] as $x) {
        if (!isset($params[$x]) || empty($params[$x])) {
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
    $cache->delete('genrelist');
    $cache->delete('categories');
    if (-1 == mysqli_affected_rows($mysqli)) {
        stderr($lang['categories_error'], $lang['categories_exist_error']);
    } else {
        header("Location: {$site_config['baseurl']}/staffpanel.php?tool=categories&action=categories");
    }
}

function delete_cat()
{
    global $site_config, $params, $lang, $cache, $mysqli;

    if (!isset($params['id']) || !is_valid_id($params['id'])) {
        stderr($lang['categories_error'], $lang['categories_no_id']);
    }
    $q = sql_query('SELECT * FROM categories WHERE id = ' . intval($params['id']));
    if (false == mysqli_num_rows($q)) {
        stderr($lang['categories_error'], $lang['categories_exist_error']);
    }
    $r = mysqli_fetch_assoc($q);
    $old_cat_id = intval($r['id']);
    if (isset($params['new_cat_id'])) {
        if (!is_valid_id($params['new_cat_id']) || ($r['id'] == $params['new_cat_id'])) {
            stderr($lang['categories_error'], $lang['categories_exist_error']);
        }
        $new_cat_id = intval($params['new_cat_id']);

        $q = sql_query('SELECT COUNT(*) FROM categories WHERE id = ' . sqlesc($new_cat_id));
        $count = mysqli_fetch_array($q, MYSQLI_NUM);
        if (!$count[0]) {
            stderr($lang['categories_error'], $lang['categories_exist_error']);
        }
        sql_query('UPDATE torrents SET category = ' . sqlesc($new_cat_id) . ' WHERE category = ' . sqlesc($old_cat_id));
    }
    sql_query('DELETE FROM categories WHERE id = ' . sqlesc($old_cat_id));
    $cache->delete('genrelist');
    $cache->delete('categories');
    if (mysqli_affected_rows($mysqli)) {
        header("Location: {$site_config['baseurl']}/staffpanel.php?tool=categories&action=categories");
    } else {
        stderr($lang['categories_error'], $lang['categories_del_error1']);
    }
}

function delete_cat_form()
{
    global $params, $lang, $site_config;

    if (!isset($params['id']) || !is_valid_id($params['id'])) {
        stderr($lang['categories_error'], $lang['categories_no_id']);
    }
    $q = sql_query('SELECT * FROM categories WHERE id = ' . intval($params['id']));
    if (false == mysqli_num_rows($q)) {
        stderr($lang['categories_error'], $lang['categories_exist_error']);
    }
    $r = mysqli_fetch_assoc($q);
    $q = sql_query('SELECT COUNT(*) AS count FROM torrents WHERE category = ' . intval($r['id']));
    $count = mysqli_fetch_array($q, MYSQLI_NUM);
    $select = '';
    if ($count['count'] > 0) {
        $select = "
            <p class='has-text-danger level'>{$lang['categories_select_new']}
                <select name='new_cat_id'>
                    <option value='0'>{$lang['categories_select']}</option>";
        $cats = genrelist();
        foreach ($cats as $c) {
            $select .= ($c['id'] != $r['id']) ? "
                    <option value='{$c['id']}'>" . htmlsafechars($c['name'], ENT_QUOTES) . '</option>' : '';
        }
        $select .= '
                </select>
            </p>';
    }

    $htmlout = "
        <form action='{$site_config['baseurl']}/staffpanel.php?tool=categories&amp;action=categories' method='post'>
            <input type='hidden' name='mode' value='takedel_cat' />
            <input type='hidden' name='id' value='" . (int) $r['id'] . "' />
            <h2 class='has-text-centered'>{$lang['categories_del_about']} " . htmlsafechars($r['name'], ENT_QUOTES) . '</h2>';
    $htmlout .= main_div("
            <div class='w-50 has-text-centered'>
                <p class='has-text-danger level'>{$lang['categories_del_name']} <span class='has-text-white'>" . htmlsafechars($r['name'], ENT_QUOTES) . "</span></p>
                <p class='has-text-danger level'>{$lang['categories_del_description']} <span class='has-text-white'>" . htmlsafechars($r['cat_desc'], ENT_QUOTES) . "</span></p>
                <p class='has-text-danger level'>{$lang['categories_del_image']} <span class='has-text-white'>" . htmlsafechars($r['image'], ENT_QUOTES) . "</span></p>
                $select
                <input type='submit' class='button is-small right20' value='{$lang['categories_del_delete']}' />
                <input type='button' class='button is-small' value='{$lang['categories_cancel']}' onclick=\"history.go(-1)\" />
            </div>");
    $htmlout .= '
        </form>';

    echo stdhead($lang['categories_del_stdhead'] . $r['name']) . wrapper($htmlout) . stdfoot();
}

function edit_cat()
{
    global $site_config, $params, $lang, $cache, $mysqli;

    if (!isset($params['id']) || !is_valid_id($params['id'])) {
        stderr($lang['categories_error'], $lang['categories_no_id']);
    }
    foreach ([
                 'cat_name',
                 'cat_desc',
                 'cat_image',
             ] as $x) {
        if (!isset($params[$x]) || empty($params[$x])) {
            stderr($lang['categories_error'], $lang['categories_edit_error1'] . $x . '');
        }
    }
    if (!preg_match("/^cat_[A-Za-z0-9_]+\.(?:gif|jpg|jpeg|png)$/i", $params['cat_image'])) {
        stderr($lang['categories_error'], $lang['categories_edit_error2']);
    }
    $cat_name = sqlesc($params['cat_name']);
    $cat_desc = sqlesc($params['cat_desc']);
    $cat_image = sqlesc($params['cat_image']);
    $order_id = intval($params['order_id']);
    $cat_id = intval($params['id']);
    sql_query("UPDATE categories SET ordered = $order_id, name = $cat_name, cat_desc = $cat_desc, image = $cat_image WHERE id = $cat_id");
    sql_query("UPDATE categories SET ordered = ordered + 1 WHERE ordered >= $order_id AND id != $cat_id") or sqlerr(__FILE__, __LINE__);

    $query = sql_query('SELECT id FROM categories ORDER BY ordered, name') or sqlerr(__FILE__, __LINE__);
    $iter = 0;
    while ($arr = mysqli_fetch_assoc($query)) {
        sql_query('UPDATE categories SET ordered = ' . ++$iter . ' WHERE id = ' . $arr['id']) or sqlerr(__FILE__, __LINE__);
    }

    $cache->delete('genrelist');
    $cache->delete('categories');
    if (-1 == mysqli_affected_rows($mysqli)) {
        stderr($lang['categories_error'], $lang['categories_exist_error']);
    } else {
        header("Location: {$site_config['baseurl']}/staffpanel.php?tool=categories&action=categories");
    }
}

function edit_cat_form()
{
    global $site_config, $params, $lang;
    if (!isset($params['id']) || !is_valid_id($params['id'])) {
        stderr($lang['categories_error'], $lang['categories_no_id']);
    }
    $htmlout = '';
    $q = sql_query('SELECT * FROM categories WHERE id = ' . intval($params['id']));
    if (false == mysqli_num_rows($q)) {
        stderr($lang['categories_error'], $lang['categories_exist_error']);
    }
    $r = mysqli_fetch_assoc($q);
    $path = IMAGES_DIR . 'caticons/1/';
    $objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST);
    $files = [];
    foreach ($objects as $name => $object) {
        $basename = pathinfo($name, PATHINFO_BASENAME);
        $files[] = $basename;
    }

    if (is_array($files) && count($files)) {
        $select = "
            <p class='has-text-green level'>{$lang['categories_edit_select_new']}
                <select class='w-75' name='cat_image'>
                    <option value='0'>{$lang['categories_edit_select']}</option>";
        foreach ($files as $f) {
            $selected = ($f == $r['image']) ? 'selected' : '';
            $select .= "
                    <option value='" . htmlsafechars($f, ENT_QUOTES) . "' $selected>" . htmlsafechars($f, ENT_QUOTES) . '</option>';
        }
        $select .= "
                </select>
            </p>
            <p class='has-text-lime'>{$lang['categories_edit_info']}</p>";
    } else {
        $select = "
            <p class='has-text-red'>{$lang['categories_edit_warning']}</p>";
    }
    $htmlout .= "
        <form action='{$site_config['baseurl']}/staffpanel.php?tool=categories&amp;action=categories' method='post'>
            <input type='hidden' name='mode' value='takeedit_cat' />
            <input type='hidden' name='id' value='" . (int) $r['id'] . "' />";
    $htmlout .= main_div("
            <div class='w-75 has-text-centered'>
                <p class='has-text-green level'>{$lang['categories_edit_name']}<input type='text' name='cat_name' class='w-75' value='" . htmlsafechars($r['name'], ENT_QUOTES) . "' /></p>
                <p class='has-text-green level'>{$lang['categories_edit_order_id']}<input type='text' name='order_id' class='w-75' value='" . htmlsafechars($r['ordered'], ENT_QUOTES) . "' /></p>
                <p class='has-text-green level'>{$lang['categories_del_description']}<textarea class='w-75' rows='5' name='cat_desc'>" . htmlsafechars($r['cat_desc'], ENT_QUOTES) . "</textarea></p>
                $select
                <input type='submit' class='button is-small right10' value='{$lang['categories_edit_edit']}' />
                <input type='button' class='button is-small' value='{$lang['categories_cancel']}' onclick=\"history.go(-1)\" />
            </div>");
    $htmlout .= '
        </form>';
    echo stdhead($lang['categories_edit_stdhead'] . $r['name']) . wrapper($htmlout) . stdfoot();
}

function show_categories()
{
    global $site_config, $lang;

    $path = IMAGES_DIR . 'caticons/1/';
    $objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST);
    $files = [];
    foreach ($objects as $name => $object) {
        $basename = pathinfo($name, PATHINFO_BASENAME);
        $files[] = $basename;
    }

    if (is_array($files) && count($files)) {
        $select = "
            <p class='has-text-green level'>
                {$lang['categories_edit_select_new']}
                <select class='w-75' name='new_cat_image'>
                    <option value='0'>{$lang['categories_edit_select']}</option>";
        foreach ($files as $file) {
            $select .= "
                    <option value='" . htmlsafechars($file, ENT_QUOTES) . "'>" . htmlsafechars($file, ENT_QUOTES) . '</option>';
        }
        $select .= "
                </select>
            </p>
            <p class='has-text-red'>{$lang['categories_edit_warning1']}</p>";
    } else {
        $select = "
        <p class='has-text-red'>{$lang['categories_edit_warning']}</p>";
    }
    $htmlout = "
        <form action='" . $site_config['baseurl'] . "/staffpanel.php?tool=categories&amp;action=categories' method='post'>";
    $htmlout .= main_div("
            <input type='hidden' name='mode' value='takeadd_cat' />
            <div class='w-75 has-text-centered'>
                <h2>{$lang['categories_show_make']}</h2>
                <p class='has-text-green level'>            
                    {$lang['categories_edit_name']}
                    <input type='text' name='new_cat_name' class='w-75' maxlength='50' />
                </p>
                <p class='has-text-green level'>
                    {$lang['categories_del_description']}
                    <textarea class='w-75' rows='5' name='new_cat_desc'></textarea>
                </p>
                $select
                <input type='submit' value='{$lang['categories_show_add']}' class='button is-small right10' />
                <input type='reset' value='{$lang['categories_show_reset']}' class='button is-small' />
            </div>");
    $htmlout .= '
        </form>';

    $htmlout .= "
        <h2 class='has-text-centered top20'>{$lang['categories_show_head']}</h2>";
    $body = '';
    $heading = "
        <tr>
            <th>{$lang['categories_show_id']}</th>
            <th>{$lang['categories_show_order_id']}</th>
            <th>{$lang['categories_show_name']}</th>
            <th>{$lang['categories_show_descr']}</th>
            <th>{$lang['categories_show_image']}</th>
            <th>{$lang['categories_show_edit']}</th>
            <th>{$lang['categories_show_delete']}</th>
            <th>{$lang['categories_show_move']}</th>
        </tr>";
    $query = sql_query('SELECT * FROM categories ORDER BY ordered ASC');
    if (false == mysqli_num_rows($query)) {
        $htmlout = '<h1>' . $lang['categories_show_oops'] . '</h1>';
    } else {
        $cats = genrelist();
        while ($row = mysqli_fetch_assoc($query)) {
            $cat_image = file_exists(IMAGES_DIR . 'caticons/1/' . $row['image']) ? "<img src='{$site_config['pic_baseurl']}caticons/1/" . htmlsafechars($row['image']) . "' alt='" . (int) $row['id'] . "' />" : "{$lang['categories_show_no_image']}";
            $body .= "
        <tr>
            <td><b>{$lang['categories_show_id2']} (" . (int) $row['id'] . ')</b></td>
            <td>' . htmlsafechars($row['ordered']) . '</td>    
            <td>' . htmlsafechars($row['name']) . '</td>
            <td>' . htmlsafechars($row['cat_desc']) . "</td>
            <td>$cat_image</td>
            <td><a href='staffpanel.php?tool=categories&amp;action=categories&amp;mode=edit_cat&amp;id=" . (int) $row['id'] . "'>
                <img src='{$site_config['pic_baseurl']}aff_tick.gif' alt='{$lang['categories_show_edit2']}' title='{$lang['categories_show_edit']}' width='12' height='12' /></a>
            </td>
            <td width='18'><a href='staffpanel.php?tool=categories&amp;action=categories&amp;mode=del_cat&amp;id=" . (int) $row['id'] . "'>
                <img src='{$site_config['pic_baseurl']}aff_cross.gif' alt='{$lang['categories_show_delete2']}' title='{$lang['categories_show_delete']}' width='12' height='12' /></a>
            </td>
            <td width='18'><a href='staffpanel.php?tool=categories&amp;action=categories&amp;mode=move_cat&amp;id=" . (int) $row['id'] . "'>
                <img src='{$site_config['pic_baseurl']}plus.gif' alt='{$lang['categories_show_move2']}' title='{$lang['categories_show_move']}' width='12' height='12' /></a>
            </td>
        </tr>";
        }
    }
    $htmlout .= main_table($body, $heading);
    echo stdhead($lang['categories_show_stdhead']) . wrapper($htmlout) . stdfoot();
}
