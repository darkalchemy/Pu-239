<?php

declare(strict_types = 1);

use DI\DependencyException;
use DI\NotFoundException;
use Envms\FluentPDO\Literal;
use MatthiasMullie\Scrapbook\Exception\UnbegunTransaction;
use Pu239\Cache;
use Pu239\Database;
use Spatie\Image\Exceptions\InvalidManipulation;

require_once INCL_DIR . 'function_users.php';
require_once CLASS_DIR . 'class_check.php';
require_once INCL_DIR . 'function_categories.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
$params = array_merge($_GET, $_POST);
$params['mode'] = isset($params['mode']) ? $params['mode'] : '';
$params['parent_id'] = !empty($params['parent_id']) ? (int) $params['parent_id'] : 0;
$params['id'] = !empty($params['id']) ? (int) $params['id'] : 0;
$params['cat_hidden'] = !empty($params['cat_hidden']) ? (int) $params['cat_hidden'] : 0;
$params['new_cat_id'] = !empty($params['new_cat_id']) ? (int) $params['new_cat_id'] : 0;
switch ($params['mode']) {
    case 'takemove_cat':
        move_cat($params);
        break;

    case 'move_cat':
        move_cat_form($params);
        break;

    case 'takeadd_cat':
        add_cat($params);
        break;

    case 'takedel_cat':
        delete_cat($params);
        break;

    case 'del_cat':
        delete_cat_form($params);
        break;

    case 'takeedit_cat':
        edit_cat($params);
        break;

    case 'edit_cat':
        edit_cat_form($params);
        break;

    default:
        show_categories();
        break;
}

/**
 * @param $params
 *
 * @throws UnbegunTransaction
 * @throws \Envms\FluentPDO\Exception
 * @throws Exception
 */
function move_cat($params)
{
    global $container;

    if ((!isset($params['id']) || !is_valid_id((int) $params['id'])) || (!isset($params['new_cat_id']) || !is_valid_id((int) $params['new_cat_id']))) {
        stderr(_('Error'), _('No category ID selected'));
    }
    if (!is_valid_id((int) $params['new_cat_id']) || ((int) $params['id'] === (int) $params['new_cat_id'])) {
        stderr(_('Error'), _('You can not move torrents into the same category'));
    }
    $fluent = $container->get(Database::class);
    $count = $fluent->from('categories')
                    ->select(null)
                    ->select('COUNT(id) AS count')
                    ->where('id', [
                        $params['id'],
                        $params['new_cat_id'],
                    ])
                    ->fetch('count');

    if ($count != 2) {
        stderr(_('Error'), _('That category does not exist or has been deleted'));
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
    $cache = $container->get(Cache::class);
    $cache->delete('genrelist_grouped_');
    $cache->delete('genrelist_ordered_');
    $cache->delete('categories');
    if ($results) {
        header("Location: {$_SERVER['PHP_SELF']}?tool=categories");
        die();
    } else {
        stderr(_('Error'), _('There was an error deleting the category'));
    }
}

/**
 * @param $params
 *
 * @throws DependencyException
 * @throws NotFoundException
 * @throws \Envms\FluentPDO\Exception
 * @throws InvalidManipulation
 * @throws Exception
 */
function move_cat_form($params)
{
    global $site_config;

    if (!isset($params['id']) || !is_valid_id((int) $params['id'])) {
        stderr(_('Error'), _('No category ID selected'));
    }

    $current_cat = get_cat($params['id']);

    if (empty($current_cat)) {
        stderr(_('Error'), _('That category does not exist or has been deleted'));
    }

    $select = "
            <select name='new_cat_id'>
                <option value='0'>" . _('Select Category') . '</option>';
    $cats = genrelist(true);
    foreach ($cats as $cat) {
        foreach ($cat['children'] as $child) {
            $select .= ($child['id'] != $current_cat['id']) ? "
                <option value='{$child['id']}'>{$cat['name']}::" . format_comment($child['name']) . '</option>' : '';
        }
    }
    $select .= '
            </select>';
    $htmlout = "
        <form action='{$_SERVER['PHP_SELF']}?tool=categories' method='post' enctype='multipart/form-data' accept-charset='utf-8'>
            <input type='hidden' name='mode' value='takemove_cat'>
            <input type='hidden' name='id' value='{$current_cat['id']}'>
            <h2 class='has-text-centered'>" . _fe('You are about to move category: {0}', format_comment($current_cat['name'])) . "</h2>
            <h3 class='has-text-centered'>" . _('Note: This tool will move ALL torrents FROM one category to ANOTHER category only! It will NOT delete any categories or torrents.') . '</h3>';
    $body = "
            <div class='w-50 has-text-centered padding20'>
                <p class='has-text-danger level'>" . _('Old Category Name') . ": <span class='has-text-primary'>" . htmlsafechars($current_cat['parent_name']) . '::' . htmlsafechars($current_cat['name']) . "</span></p>
                <p class='is-success level'>" . _('Select a new category') . ": $select</p>
                <div class='has-text-centered'>
                    <input type='submit' class='button is-small right20' value='" . _('Move') . "'>
                    <input type='button' class='button is-small' value='" . _('Cancel') . "' onclick=\"history.go(-1)\">
                </div>
            </div>";
    $htmlout .= main_div($body) . '
        </form>';
    $title = _('Move Category');
    $breadcrumbs = [
        "<a href='{$site_config['paths']['baseurl']}/staffpanel.php'>" . _('Staff Panel') . '</a>',
        "<a href='{$_SERVER['PHP_SELF']}'>$title</a>",
    ];
    echo stdhead($title, [], 'page-wrapper', $breadcrumbs) . wrapper($htmlout) . stdfoot();
}

/**
 * @param $params
 *
 * @throws Exception
 */
function add_cat($params)
{
    global $container;

    foreach ([
        'new_cat_name',
        'new_cat_desc',
        'parent_id',
    ] as $x) {
        if (!isset($params[$x])) {
            stderr(_('Error'), _('Some fields were left blank') . ': ' . $x);
        }
    }
    if (!empty($params['cat_image']) && !preg_match("/^[A-Za-z0-9_\-]+\.(?:gif|jpg|jpeg|png)$/i", $params['cat_image'])) {
        stderr(_('Error'), _('File name is not allowed') . ': ' . $params['cat_image']);
    }
    $values = [
        'name' => $params['new_cat_name'],
        'cat_desc' => $params['new_cat_desc'],
        'image' => !empty($params['cat_image']) ? $params['cat_image'] : '',
        'parent_id' => $params['parent_id'],
        'hidden' => $params['cat_hidden'],
    ];
    $fluent = $container->get(Database::class);
    $insert = $fluent->insertInto('categories')
                     ->values($values)
                     ->execute();

    $cache = $container->get(Cache::class);
    $cache->delete('genrelist_grouped_');
    $cache->delete('genrelist_ordered_');
    $cache->delete('categories');
    if (!$insert) {
        stderr(_('Error'), _('That category does not exist or has been deleted'));
    } else {
        header("Location: {$_SERVER['PHP_SELF']}?tool=categories");
        die();
    }
}

/**
 * @param $params
 *
 * @throws DependencyException
 * @throws NotFoundException
 * @throws Exception
 */
function delete_cat($params)
{
    global $container;

    $cache = $container->get(Cache::class);
    if (!isset($params['id']) || !is_valid_id((int) $params['id'])) {
        stderr(_('Error'), _('No category ID selected'));
    }
    $fluent = $container->get(Database::class);
    $cat = $fluent->from('categories')
                  ->where('id = ?', $params['id'])
                  ->fetch();

    if (!$cat) {
        stderr(_('Error'), _('That category does not exist or has been deleted'));
    }
    $count = $fluent->from('torrents')
                    ->select(null)
                    ->select('COUNT(id) AS count')
                    ->where('category = ?', $params['id'])
                    ->fetch('count');

    if ($count) {
        stderr(_('Error'), _('There are still torrents assigned to this category'));
    }

    $results = $fluent->deleteFrom('categories')
                      ->where('id  = ?', $params['id'])
                      ->execute();

    $cache->delete('genrelist_grouped_');
    $cache->delete('genrelist_ordered_');
    $cache->delete('categories');
    if ($results) {
        header("Location: {$_SERVER['PHP_SELF']}?tool=categories");
        die();
    } else {
        stderr(_('Error'), _('There was an error deleting the category'));
    }
}

/**
 * @param mixed $params
 *
 * @throws \Envms\FluentPDO\Exception
 * @throws Exception
 */
function delete_cat_form($params)
{
    global $container, $site_config;

    if (!isset($params['id']) || !is_valid_id((int) $params['id'])) {
        stderr(_('Error'), _('No category ID selected'));
    }
    $cat = get_cat($params['id']);

    if (!$cat) {
        stderr(_('Error'), _('That category does not exist or has been deleted'));
    }
    $fluent = $container->get(Database::class);
    $count = $fluent->from('torrents')
                    ->select(null)
                    ->select('COUNT(id) AS count')
                    ->where('category = ?', $params['id'])
                    ->fetch('count');

    if ($count) {
        stderr(_('Error'), _('There are still torrents assigned to this category'));
    }

    $htmlout = "
        <form action='{$_SERVER['PHP_SELF']}?tool=categories' method='post' enctype='multipart/form-data' accept-charset='utf-8'>
            <input type='hidden' name='mode' value='takedel_cat'>
            <input type='hidden' name='id' value='{$cat['id']}'>";
    $htmlout .= main_div("
            <div class='w-50 has-text-centered padding20'>
                <h2 class='has-text-centered'>" . _('You are about to delete category') . ": {$cat['name']}</h2>
                <p class='has-text-danger level'>" . _('Cat Name') . ": <span class='has-text-primary'>{$cat['name']}</span></p>
                <p class='has-text-danger level'>" . _('Parent Name') . ": <span class='has-text-primary'>{$cat['parent_name']}</span></p>
                <p class='has-text-danger level'>" . _('Description') . ": <span class='has-text-primary'>{$cat['cat_desc']}</span></p>
                <p class='has-text-danger level'>" . _('Image') . ": <span class='has-text-primary'>{$cat['image']}</span></p>
                <input type='submit' class='button is-small right20' value='" . _('Delete') . "'>
                <input type='button' class='button is-small' value='" . _('Cancel') . "' onclick=\"history.go(-1)\">
            </div>");
    $htmlout .= '
        </form>';

    $title = _('Delete Category');
    $breadcrumbs = [
        "<a href='{$site_config['paths']['baseurl']}/staffpanel.php'>" . _('Staff Panel') . '</a>',
        "<a href='{$_SERVER['PHP_SELF']}'>$title</a>",
    ];
    echo stdhead($title, [], 'page-wrapper', $breadcrumbs) . wrapper($htmlout) . stdfoot();
}

/**
 * @param mixed $params
 *
 * @throws \Envms\FluentPDO\Exception
 * @throws Exception
 */
function edit_cat($params)
{
    global $container;

    $cache = $container->get(Cache::class);
    if (!isset($params['id']) || !is_valid_id((int) $params['id'])) {
        stderr(_('Error'), _('No category ID selected'));
    }
    foreach ([
        'cat_name',
        'cat_desc',
        'parent_id',
        'order_id',
    ] as $x) {
        if (!isset($params[$x])) {
            stderr(_('Error'), _('Some fields were left blank '));
        }
    }
    if (!empty($params['cat_image']) && !preg_match("/^[A-Za-z0-9_\-]+\.(?:gif|jpg|jpeg|png)$/i", $params['cat_image'])) {
        stderr(_('Error'), _('File name is not allowed'));
    }

    $set = [
        'name' => $params['cat_name'],
        'cat_desc' => $params['cat_desc'],
        'image' => !empty($params['cat_image']) ? $params['cat_image'] : '',
        'ordered' => $params['order_id'],
        'parent_id' => $params['parent_id'],
        'hidden' => $params['cat_hidden'],
    ];
    $fluent = $container->get(Database::class);
    $update = $fluent->update('categories')
                     ->set($set)
                     ->where('id = ?', $params['id'])
                     ->execute();

    if ($update) {
        set_ordered($params);
        reorder_cats(false);

        $cache->delete('genrelist_grouped_');
        $cache->delete('genrelist_ordered_');
        $cache->delete('categories');
        header("Location: {$_SERVER['PHP_SELF']}?tool=categories");
        die();
    } else {
        header("Location: {$_SERVER['PHP_SELF']}?tool=categories");
        die();
    }
}

/**
 * @param $params
 *
 * @throws DependencyException
 * @throws InvalidManipulation
 * @throws NotFoundException
 * @throws \Envms\FluentPDO\Exception
 * @throws Exception
 */
function edit_cat_form($params)
{
    global $site_config;

    if (!isset($params['id']) || !is_valid_id((int) $params['id'])) {
        stderr(_('Error'), _('No category ID selected'));
    }

    $cat = get_cat($params['id']);

    if (!$cat) {
        stderr(_('Error'), _('That category does not exist or has been deleted'));
    }

    $parents = get_parents($cat);
    $select = get_images($cat);
    $htmlout = "
        <form action='{$_SERVER['PHP_SELF']}?tool=categories' method='post' enctype='multipart/form-data' accept-charset='utf-8'>
            <input type='hidden' name='mode' value='takeedit_cat'>
            <input type='hidden' name='id' value='{$cat['id']}'>";
    $htmlout .= main_div("
            <div class='w-100 has-text-centered padding20'>
                <h2>" . _('Edit Category') . "</h2>
                <p class='is-success level'>" . _('New Cat Name') . ": <input type='text' name='cat_name' class='w-75' value='{$cat['name']}' required></p>
                <div class='is-success level-wide'>
                    " . _('Hidden') . "
                    <select name='cat_hidden' class='w-75' required>
                        <option value=''>Select</option>
                        <option value='1' " . ($cat['hidden'] === 1 ? 'selected' : '') . ">Hidden by Default</option>
                        <option value='0' " . ($cat['hidden'] === 0 ? 'selected' : '') . ">Shown by Default</option>
                    </select>
                </div>
                <div class='has-text-info has-text-centered top10 bottom20'>" . _('If a parent is hidden, then all of the children are also hidden') . "</div>
                $parents
                <p class='is-success level'>" . _('New Order ID') . ": <input type='number' min='0' max='1000' name='order_id' class='w-75' value='{$cat['ordered']}' required></p>
                <p class='is-success level'>" . _('Description') . ": <textarea class='w-75' rows='5' name='cat_desc'>{$cat['cat_desc']}</textarea></p>
                $select
                <input type='submit' class='button is-small right10' value='" . _('Edit') . "'>
                <input type='button' class='button is-small' value='" . _('Cancel') . "' onclick=\"history.go(-1)\">
            </div>");
    $htmlout .= '
        </form>';
    $title = _('Edit Category');
    $breadcrumbs = [
        "<a href='{$site_config['paths']['baseurl']}/staffpanel.php'>" . _('Staff Panel') . '</a>',
        "<a href='{$_SERVER['PHP_SELF']}'>$title</a>",
    ];
    echo stdhead($title, [], 'page-wrapper', $breadcrumbs) . wrapper($htmlout) . stdfoot();
}

/**
 * @throws \Envms\FluentPDO\Exception
 * @throws Exception
 */
function show_categories()
{
    global $site_config;

    $parents = get_parents([]);
    $select = get_images([]);
    $htmlout = "
        <form action='" . $site_config['paths']['baseurl'] . "/staffpanel.php?tool=categories' method='post' enctype='multipart/form-data' accept-charset='utf-8'>";
    $htmlout .= main_div("
            <input type='hidden' name='mode' value='takeadd_cat'>
            <div class='has-text-centered padding20'>
                <h2>" . _('Make a new category') . "</h2>
                <p class='is-success level'>
                    " . _('New Cat Name') . ":
                    <input type='text' name='new_cat_name' class='w-75' maxlength='50' placeholder='New Category Name' required>
                </p>
                <div class='is-success level-wide'>
                    " . _('Hidden') . "
                    <select name='cat_hidden' class='w-75' required>
                        <option value=''>Select</option>
                        <option value='1'>Hidden by Default</option>
                        <option value='0'>Shown by Default</option>
                    </select>
                </div>
                <div class='has-text-info has-text-centered top10 bottom20'>" . _('If a parent is hidden, then all of the children are also hidden') . "</div>
                $parents
                <p class='is-success level'>
                    " . _('Description') . ":
                    <textarea class='w-75' rows='5' name='new_cat_desc'></textarea>
                </p>
                $select
                <input type='submit' value='" . _('Add New') . "' class='button is-small right10'>
                <input type='reset' value='" . _('Reset') . "' class='button is-small'>
            </div>");
    $htmlout .= '
        </form>';

    $htmlout .= "
        <h2 class='has-text-centered top20'>" . _('Current Categories') . ':</h2>';
    $body = '';
    $heading = "
        <tr>
            <th class='has-text-centered w-1'>" . _('Cat ID') . "</th>
            <th class='has-text-centered w-10'>" . _('Order') . "</th>
            <th class='w-25'>" . _('Cat Name') . "</th>
            <th class='has-text-centered w-1'>" . _('Parent Category') . "</th>
            <th class='has-text-centered'>" . _('Hidden') . "</th>
            <th class='has-text-centered'>" . _('Cat Description') . "</th>
            <th class='has-text-centered w-10'>" . _('Image') . "</th>
            <th class='has-text-centered w-10'>" . _('Tools') . '</th>
        </tr>';
    $cats = genrelist(true);
    foreach ($cats as $cat) {
        $parent_name = '';
        $body .= build_table($cat, $parent_name);
        foreach ($cat['children'] as $child) {
            $parent_name = format_comment($cat['name']);
            $child['name'] = format_comment($cat['name']) . '::' . format_comment($child['name']);
            $body .= build_table($child, $parent_name);
        }
    }
    $htmlout .= main_table($body, $heading);
    $title = _('Admin Categories');
    $breadcrumbs = [
        "<a href='{$site_config['paths']['baseurl']}/staffpanel.php'>" . _('Staff Panel') . '</a>',
        "<a href='{$_SERVER['PHP_SELF']}'>$title</a>",
    ];
    echo stdhead($title, [], 'page-wrapper', $breadcrumbs) . wrapper($htmlout) . stdfoot();
}

/**
 * @param array  $data
 * @param string $parent_name
 *
 * @return string
 */
function build_table(array $data, string $parent_name)
{
    global $site_config;

    $cat_image = !empty($data['image']) && file_exists(IMAGES_DIR . 'caticons/1/' . $data['image']) ? "
            <img src='{$site_config['paths']['images_baseurl']}caticons/1/" . htmlsafechars($data['image']) . "' alt='{$data['id']}'>" : _('No Image');

    $row = "
        <tr>
            <td class='has-text-centered'>{$data['id']}</td>
            <td class='has-text-centered'>{$data['ordered']}</td>
            <td>" . htmlsafechars($data['name']) . "</td>
            <td class='has-text-centered'>{$parent_name}</td>
            <td class='has-text-centered'>" . ($data['hidden'] === 1 ? 'true' : 'false') . "</td>
            <td class='has-text-centered'>{$data['cat_desc']}</td>
            <td class='has-text-centered'>{$cat_image}</td>
            <td>
                <div class='level-center'>
                    <a href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=categories&amp;mode=edit_cat&amp;id={$data['id']}'>
                        <i class='icon-edit icon has-text-info tooltipper' title='" . _('Edit') . "'></i>
                    </a>
                    <a href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=categories&amp;mode=del_cat&amp;id={$data['id']}'>
                        <i class='icon-trash-empty icon has-text-danger tooltipper' aria-hidden='true' title='" . _('Delete') . "'></i>
                    </a>
                    <a href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=categories&amp;mode=move_cat&amp;id={$data['id']}'>
                        <i class='icon-plus icon has-text-success tooltipper' aria-hidden='true' title='" . _('Move') . "'></i>
                    </a>
                </div>
            </td>
        </tr>";

    return $row;
}

/**
 * @param array $cat
 *
 * @throws NotFoundException
 * @throws \Envms\FluentPDO\Exception
 * @throws DependencyException
 * @throws InvalidManipulation
 *
 * @return string
 */
function get_parents(array $cat)
{
    global $container;

    $fluent = $container->get(Database::class);
    $parents = $fluent->from('categories')
                      ->select('IF (cat_desc IS NULL, "", cat_desc) AS cat_desc')
                      ->where('parent_id = 0')
                      ->orderBy('ordered')
                      ->fetchAll();

    foreach ($parents as $parent) {
        $parent['name'] = format_comment($parent['name']);
        $parent['cat_desc'] = format_comment($parent['cat_desc']);
        $parent['image'] = format_comment($parent['image']);
    }

    $out = "
            <p class='is-success level'>" . _('Select Parent Category') . "
                <select class='w-75' name='parent_id'>
                    <option value=''>" . _('Select Parent Category') . '</option>';
    foreach ($parents as $parent) {
        $selected = !empty($cat) && $parent['id'] === $cat['parent_id'] ? 'selected' : '';
        $out .= "
                    <option value='{$parent['id']}' {$selected}>{$parent['name']}</option>";
    }
    $out .= '
                </select>
            </p>';

    return $out;
}

/**
 * @param bool $redirect
 *
 * @throws DependencyException
 * @throws NotFoundException
 * @throws UnbegunTransaction
 * @throws \Envms\FluentPDO\Exception
 */
function reorder_cats(bool $redirect = true)
{
    global $container;

    $fluent = $container->get(Database::class);

    $i = 0;
    $cats = $fluent->from('categories')
                   ->orderBy('ordered');

    foreach ($cats as $cat) {
        $set = [
            'ordered' => ++$i,
        ];

        $fluent->update('categories')
               ->set($set)
               ->where('id = ?', $cat['id'])
               ->execute();
    }

    flush_torrents(0);
    $cache = $container->get(Cache::class);
    $cache->delete('genrelist_grouped_');
    $cache->delete('genrelist_ordered_');
    $cache->delete('categories');

    if ($redirect) {
        header("Location: {$_SERVER['PHP_SELF']}?tool=categories");
        die();
    }
}

/**
 * @param array $params
 *
 * @throws DependencyException
 * @throws NotFoundException
 * @throws \Envms\FluentPDO\Exception
 */
function set_ordered(array $params)
{
    global $container;

    $fluent = $container->get(Database::class);
    $set = [
        'ordered' => new Literal('ordered + 1'),
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
 * @throws NotFoundException
 * @throws \Envms\FluentPDO\Exception
 * @throws DependencyException
 * @throws InvalidManipulation
 *
 * @return string
 */
function get_images(array $cat)
{
    global $site_config;

    $path = IMAGES_DIR . 'caticons/1/';
    $objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST);
    $files = [];

    foreach ($objects as $name => $object) {
        $basename = pathinfo($name, PATHINFO_BASENAME);
        $ext = pathinfo($name, PATHINFO_EXTENSION);
        if (in_array($ext, $site_config['images']['formats'])) {
            $files[] = $basename;
        }
    }
    if (is_array($files) && count($files)) {
        natsort($files);
        $select = "
            <p class='is-success level'>" . _('Select a new image') . ":
                <select class='w-75' name='cat_image'>
                    <option value='0'>" . _('Select Image') . '</option>';
        foreach ($files as $file) {
            $selected = !empty($cat) && $file == $cat['image'] ? 'selected' : '';
            $select .= "
                    <option value='" . htmlsafechars($file) . "' {$selected}>" . format_comment($file) . '</option>';
        }
        $select .= "
                </select>
            </p>
            <p class='has-text-danger has-text-centered'>" . _fe('Info: If you want a new image, you have to upload it to each of the {0} directories first.', realpath(IMAGES_DIR) . '/caticons/') . '</p>';
    } else {
        $select = "
            <p class='has-text-danger has-text-centered'>" . _fe('Warning: There are no images in the directory {0}, please upload one.', realpath(IMAGES_DIR) . '/caticons/1/') . '</p>';
    }

    return $select;
}

/**
 * @param int $id
 *
 * @throws NotFoundException
 * @throws \Envms\FluentPDO\Exception
 * @throws DependencyException
 * @throws InvalidManipulation
 *
 * @return mixed
 */
function get_cat(int $id)
{
    global $container;

    $fluent = $container->get(Database::class);
    $cat = $fluent->from('categories')
                  ->where('id = ?', $id)
                  ->fetch();

    $current_cat['parent_name'] = $fluent->from('categories')
                                         ->select(null)
                                         ->select('name')
                                         ->where('id = ?', $cat['parent_id'])
                                         ->fetch('name');

    $cat['name'] = format_comment($cat['name']);
    $cat['cat_desc'] = format_comment($cat['cat_desc']);
    $cat['image'] = format_comment($cat['image']);
    $cat['parent_name'] = !empty($cat['parent_name']) ? format_comment($cat['parent_name']) : '';

    return $cat;
}

/**
 * @param int $id
 *
 * @throws DependencyException
 * @throws NotFoundException
 * @throws \Envms\FluentPDO\Exception
 * @throws UnbegunTransaction
 */
function flush_torrents(int $id)
{
    global $container, $site_config;

    $fluent = $container->get(Database::class);
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

    $cache = $container->get(Cache::class);
    foreach ($torrents as $torrent) {
        $cache->update_row('torrent_details_' . $torrent['id'], $set, $site_config['expires']['torrent_details']);
    }
}
