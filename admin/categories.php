<?php

use MatthiasMullie\Scrapbook\Exception\UnbegunTransaction;

require_once INCL_DIR . 'function_users.php';
require_once CLASS_DIR . 'class_check.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
global $lang;

$lang = array_merge($lang, load_language('ad_categories'));
$params = array_merge($_GET, $_POST);
$params['mode'] = isset($params['mode']) ? $params['mode'] : '';
$params['parent_id'] = !empty($params['parent_id']) ? intval($params['parent_id']) : 0;
$params['id'] = !empty($params['id']) ? intval($params['id']) : 0;
$params['new_cat_id'] = !empty($params['new_cat_id']) ? intval($params['new_cat_id']) : 0;

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

    default:
        show_categories();
        break;
}

/**
 * @throws \Envms\FluentPDO\Exception
 * @throws Exception
 */
function move_cat()
{
    global $site_config, $params, $lang, $cache, $fluent;

    if ((!isset($params['id']) || !is_valid_id($params['id'])) || (!isset($params['new_cat_id']) || !is_valid_id($params['new_cat_id']))) {
        stderr($lang['categories_error'], $lang['categories_no_id']);
    }
    if (!is_valid_id($params['new_cat_id']) || ($params['id'] == $params['new_cat_id'])) {
        stderr($lang['categories_error'], $lang['categories_move_error2']);
    }

    $count = $fluent->from('categories')
                    ->select(null)
                    ->select('COUNT(*) AS count')
                    ->where('id', [
                        $params['id'],
                        $params['new_cat_id'],
                    ])
                    ->fetch('count');

    if ($count != 2) {
        stderr($lang['categories_error'], $lang['categories_exist_error']);
    }
    $set = [
        'category' => $params['new_cat_id'],
    ];

    $results = $fluent->update('torrents')
                      ->set($set)
                      ->where('category = ?', $params['id'])
                      ->execute();

    flush_torrents($params['id']);
    flush_torrents($params['new_cat_id']);
    $cache->delete('genrelist_grouped_');
    $cache->delete('genrelist_ordered_');
    $cache->delete('categories');
    if ($results) {
        header("Location: {$site_config['paths']['baseurl']}/staffpanel.php?tool=categories");
        die();
    } else {
        stderr($lang['categories_error'], $lang['categories_move_error4']);
    }
}

/**
 * @throws \Envms\FluentPDO\Exception
 * @throws Exception
 */
function move_cat_form()
{
    global $params, $lang, $site_config;

    if (!isset($params['id']) || !is_valid_id($params['id'])) {
        stderr($lang['categories_error'], $lang['categories_no_id']);
    }

    $current_cat = get_cat($params['id']);

    if (empty($current_cat)) {
        stderr($lang['categories_error'], $lang['categories_exist_error']);
    }

    $select = "
            <select name='new_cat_id'>
                <option value='0'>{$lang['categories_select']}</option>";
    $cats = genrelist(true);
    foreach ($cats as $cat) {
        foreach ($cat['children'] as $child) {
            $select .= ($child['id'] != $current_cat['id']) ? "
                <option value='{$child['id']}'>{$cat['name']}::" . htmlsafechars(htmlspecialchars($child['name'], ENT_QUOTES, 'UTF-8')) . '</option>' : '';
        }
    }
    $select .= '
            </select>';
    $htmlout = "
        <form action='{$site_config['paths']['baseurl']}/staffpanel.php?tool=categories' method='post' accept-charset='utf-8'>
            <input type='hidden' name='mode' value='takemove_cat'>
            <input type='hidden' name='id' value='{$current_cat['id']}'>
            <h2 class='has-text-centered'>{$lang['categories_move_about']} " . htmlsafechars(htmlspecialchars($current_cat['name'], ENT_QUOTES, 'UTF-8')) . "</h2>
            <h3 class='has-text-centered'>{$lang['categories_move_note']}</h3>";
    $htmlout .= main_div("
            <div class='w-50 has-text-centered padding20'>
                <p class='has-text-danger level'>{$lang['categories_move_old']} <span class='has-text-white'>" . htmlsafechars(htmlspecialchars($current_cat['parent_name'], ENT_QUOTES, 'UTF-8') . '::' . htmlsafechars(htmlspecialchars($current_cat['name'], ENT_QUOTES, 'UTF-8'))) . "</span></p>
                <p class='has-text-green level'>{$lang['categories_select_new']} $select</p>
                <div class='has-text-centered'>
                    <input type='submit' class='button is-small right20' value='{$lang['categories_move']}'>
                    <input type='button' class='button is-small' value='{$lang['categories_cancel']}' onclick=\"history.go(-1)\">
                </div>
            </div>");
    $htmlout .= '
        </form>';

    echo stdhead($lang['categories_move_stdhead'] . $current_cat['name']) . wrapper($htmlout) . stdfoot();
}

/**
 * @throws \Envms\FluentPDO\Exception
 * @throws Exception
 */
function add_cat()
{
    global $site_config, $params, $lang, $cache, $fluent;

    foreach ([
                 'new_cat_name',
                 'new_cat_desc',
                 'cat_image',
                 'parent_id',
             ] as $x) {
        if (!isset($params[$x])) {
            stderr($lang['categories_error'], $lang['categories_add_error1'] . ': ' . $x);
        }
    }
    if (!empty($params['cat_image']) && !preg_match("/^[A-Za-z0-9_\-]+\.(?:gif|jpg|jpeg|png)$/i", $params['cat_image'])) {
        stderr($lang['categories_error'], $lang['categories_add_error2'] . ': ' . $params['cat_image']);
    }
    $values = [
        'name' => $params['new_cat_name'],
        'cat_desc' => $params['new_cat_desc'],
        'image' => $params['cat_image'],
        'parent_id' => $params['parent_id'],
    ];
    $insert = $fluent->insertInto('categories')
                     ->values($values)
                     ->execute();

    $cache->delete('genrelist_grouped_');
    $cache->delete('genrelist_ordered_');
    $cache->delete('categories');
    if (!$insert) {
        stderr($lang['categories_error'], $lang['categories_exist_error']);
    } else {
        header("Location: {$site_config['paths']['baseurl']}/staffpanel.php?tool=categories");
        die();
    }
}

/**
 * @throws \Envms\FluentPDO\Exception
 * @throws Exception
 */
function delete_cat()
{
    global $site_config, $params, $lang, $cache, $fluent;

    if (!isset($params['id']) || !is_valid_id($params['id'])) {
        stderr($lang['categories_error'], $lang['categories_no_id']);
    }
    $cat = $fluent->from('categories')
                  ->where('id=?', $params['id'])
                  ->fetch();

    if (!$cat) {
        stderr($lang['categories_error'], $lang['categories_exist_error']);
    }
    $count = $fluent->from('torrents')
                    ->select(null)
                    ->select('COUNT(*) AS count')
                    ->where('category = ?', $params['id'])
                    ->fetch('count');

    if ($count) {
        stderr($lang['categories_error'], $lang['categories_not_empty']);
    }

    $results = $fluent->deleteFrom('categories')
                      ->where('id =?', $params['id'])
                      ->execute();

    $cache->delete('genrelist_grouped_');
    $cache->delete('genrelist_ordered_');
    $cache->delete('categories');
    if ($results) {
        header("Location: {$site_config['paths']['baseurl']}/staffpanel.php?tool=categories");
        die();
    } else {
        stderr($lang['categories_error'], $lang['categories_del_error1']);
    }
}

/**
 * @throws \Envms\FluentPDO\Exception
 * @throws Exception
 */
function delete_cat_form()
{
    global $params, $lang, $site_config, $fluent;

    if (!isset($params['id']) || !is_valid_id($params['id'])) {
        stderr($lang['categories_error'], $lang['categories_no_id']);
    }
    $cat = get_cat($params['id']);

    if (!$cat) {
        stderr($lang['categories_error'], $lang['categories_exist_error']);
    }

    $count = $fluent->from('torrents')
                    ->select(null)
                    ->select('COUNT(*) AS count')
                    ->where('category = ?', $params['id'])
                    ->fetch('count');

    if ($count) {
        stderr($lang['categories_error'], $lang['categories_not_empty']);
    }

    $htmlout = "
        <form action='{$site_config['paths']['baseurl']}/staffpanel.php?tool=categories' method='post' accept-charset='utf-8'>
            <input type='hidden' name='mode' value='takedel_cat'>
            <input type='hidden' name='id' value='{$cat['id']}'>";
    $htmlout .= main_div("
            <div class='w-50 has-text-centered padding20'>
                <h2 class='has-text-centered'>{$lang['categories_del_about']} {$cat['name']}</h2>
                <p class='has-text-danger level'>{$lang['categories_del_name']} <span class='has-text-white'>{$cat['name']}</span></p>
                <p class='has-text-danger level'>{$lang['categories_del_parent_name']} <span class='has-text-white'>{$cat['parent_name']}</span></p>
                <p class='has-text-danger level'>{$lang['categories_del_description']} <span class='has-text-white'>{$cat['cat_desc']}</span></p>
                <p class='has-text-danger level'>{$lang['categories_del_image']} <span class='has-text-white'>{$cat['image']}</span></p>
                <input type='submit' class='button is-small right20' value='{$lang['categories_del_delete']}'>
                <input type='button' class='button is-small' value='{$lang['categories_cancel']}' onclick=\"history.go(-1)\">
            </div>");
    $htmlout .= '
        </form>';

    echo stdhead($lang['categories_del_stdhead'] . $cat['name']) . wrapper($htmlout) . stdfoot();
}

/**
 * @throws \Envms\FluentPDO\Exception
 * @throws Exception
 */
function edit_cat()
{
    global $site_config, $params, $lang, $cache, $fluent;

    if (!isset($params['id']) || !is_valid_id($params['id'])) {
        stderr($lang['categories_error'], $lang['categories_no_id']);
    }
    foreach ([
                 'cat_name',
                 'cat_desc',
                 'cat_image',
                 'parent_id',
                 'order_id',
             ] as $x) {
        if (!isset($params[$x])) {
            stderr($lang['categories_error'], $lang['categories_edit_error1'] . $x . '');
        }
    }
    if (!empty($params['cat_image']) && !preg_match("/^[A-Za-z0-9_\-]+\.(?:gif|jpg|jpeg|png)$/i", $params['cat_image'])) {
        stderr($lang['categories_error'], $lang['categories_edit_error2']);
    }
    $set = [
        'name' => $params['cat_name'],
        'cat_desc' => $params['cat_desc'],
        'image' => $params['cat_image'],
        'ordered' => $params['order_id'],
        'parent_id' => $params['parent_id'],
    ];
    $update = $fluent->update('categories')
                     ->set($set)
                     ->where('id=?', $params['id'])
                     ->execute();

    if ($update) {
        set_ordered($params);
        reorder_cats(false);

        $cache->delete('genrelist_grouped_');
        $cache->delete('genrelist_ordered_');
        $cache->delete('categories');
        header("Location: {$site_config['paths']['baseurl']}/staffpanel.php?tool=categories");
        die();
    } else {
        header("Location: {$site_config['paths']['baseurl']}/staffpanel.php?tool=categories");
        die();
    }
}

/**
 * @throws Exception
 */
function edit_cat_form()
{
    global $site_config, $params, $lang;

    if (!isset($params['id']) || !is_valid_id($params['id'])) {
        stderr($lang['categories_error'], $lang['categories_no_id']);
    }

    $cat = get_cat($params['id']);

    if (!$cat) {
        stderr($lang['categories_error'], $lang['categories_exist_error']);
    }

    $parents = get_parents($cat);
    $select = get_images($cat);
    $htmlout = "
        <form action='{$site_config['paths']['baseurl']}/staffpanel.php?tool=categories' method='post' accept-charset='utf-8'>
            <input type='hidden' name='mode' value='takeedit_cat'>
            <input type='hidden' name='id' value='{$cat['id']}'>";
    $htmlout .= main_div("
            <div class='w-100 has-text-centered padding20'>
                <h2>{$lang['categories_show_edit2']}</h2>
                <p class='has-text-green level'>{$lang['categories_edit_name']}<input type='text' name='cat_name' class='w-75' value='{$cat['name']}'></p>
                $parents
                <p class='has-text-green level'>{$lang['categories_edit_order_id']}<input type='number' min='0' max='1000' name='order_id' class='w-75' value='{$cat['ordered']}'></p>
                <p class='has-text-green level'>{$lang['categories_del_description']}<textarea class='w-75' rows='5' name='cat_desc'>{$cat['cat_desc']}</textarea></p>
                $select
                <input type='submit' class='button is-small right10' value='{$lang['categories_edit_edit']}'>
                <input type='button' class='button is-small' value='{$lang['categories_cancel']}' onclick=\"history.go(-1)\">
            </div>");
    $htmlout .= '
        </form>';
    echo stdhead($lang['categories_edit_stdhead'] . $cat['name']) . wrapper($htmlout) . stdfoot();
}

/**
 * @throws \Envms\FluentPDO\Exception
 * @throws Exception
 */
function show_categories()
{
    global $site_config, $lang;

    $parents = get_parents([]);
    $select = get_images([]);
    $htmlout = "
        <form action='" . $site_config['paths']['baseurl'] . "/staffpanel.php?tool=categories' method='post' accept-charset='utf-8'>";
    $htmlout .= main_div("
            <input type='hidden' name='mode' value='takeadd_cat'>
            <div class='has-text-centered padding20'>
                <h2>{$lang['categories_show_make']}</h2>
                <p class='has-text-green level'>
                    {$lang['categories_edit_name']}
                    <input type='text' name='new_cat_name' class='w-75' maxlength='50'>
                </p>
                $parents
                <p class='has-text-green level'>
                    {$lang['categories_del_description']}
                    <textarea class='w-75' rows='5' name='new_cat_desc'></textarea>
                </p>
                $select
                <input type='submit' value='{$lang['categories_show_add']}' class='button is-small right10'>
                <input type='reset' value='{$lang['categories_show_reset']}' class='button is-small'>
            </div>");
    $htmlout .= '
        </form>';

    $htmlout .= "
        <h2 class='has-text-centered top20'>{$lang['categories_show_head']}</h2>";
    $body = '';
    $heading = "
        <tr>
            <th class='has-text-centered w-1'>{$lang['categories_show_id']}</th>
            <th class='has-text-centered w-10'>{$lang['categories_show_order_id']}</th>
            <th class='w-25'>{$lang['categories_show_name']}</th>
            <th class='has-text-centered w-1'>{$lang['categories_show_parent']}</th>
            <th class='has-text-centered'>{$lang['categories_show_descr']}</th>
            <th class='has-text-centered w-10'>{$lang['categories_show_image']}</th>
            <th class='has-text-centered w-10'>{$lang['categories_show_tools']}</th>
        </tr>";
    $cats = genrelist(true);
    foreach ($cats as $cat) {
        $parent_name = '';
        $body .= build_table($cat, $parent_name);
        foreach ($cat['children'] as $child) {
            $parent_name = htmlsafechars($cat['name']);
            $child['name'] = htmlsafechars($cat['name']) . '::' . htmlsafechars($child['name']);
            $body .= build_table($child, $parent_name);
        }
    }
    $htmlout .= main_table($body, $heading);
    echo stdhead($lang['categories_show_stdhead']) . wrapper($htmlout) . stdfoot();
}

/**
 * @param array  $data
 * @param string $parent_name
 *
 * @return string
 */
function build_table(array $data, string $parent_name)
{
    global $site_config, $lang;

    $cat_image = !empty($data['image']) && file_exists(IMAGES_DIR . 'caticons/1/' . $data['image']) ? "
            <img src='{$site_config['paths']['images_baseurl']}caticons/1/" . htmlsafechars($data['image']) . "' alt='{$data['id']}'>" : $lang['categories_show_no_image'];

    $row = "
        <tr>
            <td class='has-text-centered'>{$data['id']}</td>
            <td class='has-text-centered'>{$data['ordered']}</td>
            <td>" . htmlsafechars($data['name']) . "</td>
            <td class='has-text-centered'>{$parent_name}</td>
            <td class='has-text-centered'>{$data['cat_desc']}</td>
            <td class='has-text-centered'>{$cat_image}</td>
            <td>
                <div class='level-center'>
                    <a href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=categories&amp;mode=edit_cat&amp;id={$data['id']}'>
                        <i class='icon-edit icon tooltipper' title='{$lang['categories_show_edit']}'></i>
                    </a>
                    <a href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=categories&amp;mode=del_cat&amp;id={$data['id']}'>
                        <i class='icon-trash-empty icon has-text-danger tooltipper' title='{$lang['categories_show_delete']}'></i>
                    </a>
                    <a href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=categories&amp;mode=move_cat&amp;id={$data['id']}'>
                        <i class='icon-plus icon has-text-success tooltipper' title='{$lang['categories_show_move']}'></i>
                    </a>
                </div>
            </td>
        </tr>";

    return $row;
}

/**
 * @param array $cat
 *
 * @return string
 *
 * @throws \Envms\FluentPDO\Exception
 */
function get_parents(array $cat)
{
    global $fluent, $lang;

    $parents = $fluent->from('categories')
                      ->where('parent_id=0')
                      ->orderBy('ordered')
                      ->fetchAll();

    foreach ($parents as $parent) {
        $parent['name'] = htmlsafechars(htmlspecialchars($parent['name'], ENT_QUOTES, 'UTF-8'));
        $parent['cat_desc'] = htmlsafechars(htmlspecialchars($parent['cat_desc'], ENT_QUOTES, 'UTF-8'));
        $parent['image'] = htmlsafechars(htmlspecialchars($parent['image'], ENT_QUOTES, 'UTF-8'));
    }

    $out = "
            <p class='has-text-green level'>{$lang['categories_select_parent']}
                <select class='w-75' name='parent_id'>
                    <option value='0'>{$lang['categories_select_parent']}</option>";
    foreach ($parents as $parent) {
        $selected = !empty($cat) && $parent['id'] === $cat['parent_id'] ? ' selected' : '';
        $out .= "
                    <option value='{$parent['id']}'{$selected}>{$parent['name']}</option>";
    }
    $out .= '
                </select>
            </p>';

    return $out;
}

/**
 * @param bool $redirect
 *
 * @throws \Envms\FluentPDO\Exception
 * @throws UnbegunTransaction
 */
function reorder_cats(bool $redirect = true)
{
    global $site_config, $fluent, $cache;

    $i = 0;
    $cats = $fluent->from('categories')
                   ->orderBy('ordered');

    foreach ($cats as $cat) {
        $set = [
            'ordered' => ++$i,
        ];

        $fluent->update('categories')
               ->set($set)
               ->where('id=?', $cat['id'])
               ->execute();
    }

    flush_torrents(0);
    $cache->delete('genrelist_grouped_');
    $cache->delete('genrelist_ordered_');
    $cache->delete('categories');

    if ($redirect) {
        header("Location: {$site_config['paths']['baseurl']}/staffpanel.php?tool=categories");
        die();
    }
}

/**
 * @param array $params
 *
 * @throws \Envms\FluentPDO\Exception
 */
function set_ordered(array $params)
{
    global $fluent;

    $set = [
        'ordered' => new Envms\FluentPDO\Literal('ordered + 1'),
    ];
    $fluent->update('categories')
           ->set($set)
           ->where('ordered>= ?', $params['order_id'])
           ->where('id != ?', $params['id'])
           ->execute();
}

/**
 * @param array $cat
 *
 * @return string
 */
function get_images(array $cat)
{
    global $lang;

    $path = IMAGES_DIR . 'caticons/1/';
    $objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST);
    $files = [];
    foreach ($objects as $name => $object) {
        $basename = pathinfo($name, PATHINFO_BASENAME);
        $files[] = $basename;
    }

    if (is_array($files) && count($files)) {
        natsort($files);
        $select = "
            <p class='has-text-green level'>{$lang['categories_edit_select_new']}
                <select class='w-75' name='cat_image'>
                    <option value='0'>{$lang['categories_edit_select']}</option>";
        foreach ($files as $file) {
            $selected = !empty($cat) && $file == $cat['image'] ? ' selected' : '';
            $select .= "
                    <option value='" . htmlsafechars(htmlspecialchars($file, ENT_QUOTES) . "'{$selected}>" . htmlsafechars(htmlspecialchars($file, ENT_QUOTES, 'UTF-8'))) . '</option>';
        }
        $select .= "
                </select>
            </p>
            <p class='has-text-danger has-text-centered'>{$lang['categories_edit_info']}</p>";
    } else {
        $select = "
            <p class='has-text-danger'>{$lang['categories_edit_warning']}</p>";
    }

    return $select;
}

/**
 * @param int $id
 *
 * @return mixed
 *
 * @throws \Envms\FluentPDO\Exception
 */
function get_cat(int $id)
{
    global $fluent;

    $cat = $fluent->from('categories')
                  ->where('id=?', $id)
                  ->fetch();

    $current_cat['parent_name'] = $fluent->from('categories')
                                         ->select(null)
                                         ->select('name')
                                         ->where('id=?', $cat['parent_id'])
                                         ->fetch('name');

    $cat['name'] = htmlsafechars(htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8'));
    $cat['cat_desc'] = htmlsafechars(htmlspecialchars($cat['cat_desc'], ENT_QUOTES, 'UTF-8'));
    $cat['image'] = htmlsafechars(htmlspecialchars($cat['image'], ENT_QUOTES, 'UTF-8'));
    $cat['parent_name'] = !empty($cat['parent_name']) ? htmlsafechars(htmlspecialchars($cat['parent_name'], ENT_QUOTES, 'UTF-8')) : '';

    return $cat;
}

/**
 * @param int $id
 *
 * @throws \Envms\FluentPDO\Exception
 * @throws UnbegunTransaction
 */
function flush_torrents(int $id)
{
    global $fluent, $site_config, $cache;

    $torrents = $fluent->from('torrents')
                       ->select(null)
                       ->select('id');
    if (!empty($id)) {
        $torrents->where('category = ?', $id);
    } else {
        $torrents->where('category != 0');
    }

    $set = [
        'category' => $id,
    ];

    foreach ($torrents as $torrent) {
        $cache->update_row('torrent_details_' . $torrent['id'], $set, $site_config['expires']['torrent_details']);
    }
}
