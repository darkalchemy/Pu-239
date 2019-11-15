<?php

declare(strict_types = 1);

use DI\DependencyException;
use DI\NotFoundException;
use Spatie\Image\Exceptions\InvalidManipulation;

$user = check_user_status();
require_once INCL_DIR . 'function_categories.php';
$grouped = genrelist(true);
$cats = genrelist(false);
$catids = $terms = [];
foreach ($cats as $cat) {
    $catids[] = $cat['id'];
}
$get = $_GET;
foreach ($get as $key => $value) {
    if ($key === 'cats') {
        continue;
    }
    if (isset($value) && strlen($value) >= 1) {
        $terms[] = "$key=$value";
    }
}

if (empty($get['cats']) && !empty($user['notifs'])) {
    $user_cats = explode('][', $user['notifs']);
    $temp = [];
    foreach ($user_cats as $user_cat) {
        preg_match('/\d+/', $user_cat, $match);
        if (!empty($match[0])) {
            $temp[] = (int) $match[0];
            $parent = find_parent((int) $match[0]);
            if (!in_array($parent, $temp)) {
                $temp[] = $parent;
            }
        }
    }
    $get['cats'] = $temp;
}
$cats = !empty($get['cats']) ? (!is_array($get['cats']) ? explode(',', $get['cats']) : $get['cats']) : [];
asort($cats);
$main_div = "
        <div class='padding20'>
            <div id='parents' class='level-wide'>";

$children = '';
foreach ($grouped as $cat) {
    if (!$user['hidden'] && $cat['hidden'] === 1) {
        continue;
    }
    $main_div .= format_row($cat, 'parent', $cat['name'], $grouped, $cats, $terms);
    $children .= "
            <div id='{$cat['name']}' class='top20 level-wide children padding20 bg-03 round10" . (!in_array($cat['id'], $cats) ? ' is_hidden' : '') . "'>";
    foreach ($cat['children'] as $child) {
        if (!$user['hidden'] && $child['hidden'] === 1) {
            continue;
        }
        if (is_array($child)) {
            $children .= format_row($child, 'child', $cat['name'], $grouped, $cats, $terms);
        }
    }
    $children .= '
            </div>';
}

$main_div .= "
            </div>$children
        </div>";
$HTMLOUT .= main_div($main_div, 'bottom20');

/**
 *
 * @param array  $cat
 * @param string $parent
 * @param string $cat_name
 * @param array  $grouped
 * @param array  $cats
 * @param array  $terms
 *
 * @throws DependencyException
 * @throws NotFoundException
 * @throws \Envms\FluentPDO\Exception
 * @throws InvalidManipulation
 *
 * @return string
 */
function format_row(array $cat, string $parent, string $cat_name, array $grouped, array $cats, array $terms)
{
    global $site_config, $CURUSER;

    $terms = !empty($terms) ? '&amp;' . implode('&amp;', $terms) : '';
    $checked = in_array($cat['id'], $cats) ? 'checked' : '';
    $list[] = 'cats[]=' . $cat['parent_id'];
    if ($parent === 'child') {
        $js = '';
    } else {
        foreach ($grouped as $group) {
            if ($cat['id'] === $group['id']) {
                foreach ($group['children'] as $children) {
                    $list[] = 'cats[]=' . $children['id'];
                }
            }
        }
        $js = 'onclick="return showMe(event);"';
    }
    $link = "{$_SERVER['PHP_SELF']}?cats[]={$cat['id']}&amp;" . implode('&amp;', $list) . $terms;
    $image = !empty($cat['image']) && $CURUSER['opt2'] & user_options_2::BROWSE_ICONS ? "
        <div class='left10 tooltipper' title='" . _fe('Search All {0}', $parent === 'child' ? format_comment("{$cat_name} :: {$cat['name']}") : format_comment($cat['name'])) . "'>
            <img class='caticon' src='{$site_config['paths']['images_baseurl']}caticons/{$CURUSER['categorie_icon']}/" . format_comment($cat['image']) . "' alt='" . format_comment($cat['name']) . "'>
        </div>" : "
        <div class='left10 tooltipper has-text-right tooltipper' title='" . _fe('Search All {0}', $parent === 'child' ? format_comment("{$cat_name} :: {$cat['name']}") : format_comment($cat['name'])) . "'>" . format_comment($cat['name']) . '</div>';

    return "
        <a href='{$link}'>
            <div class='margin10'>
                <div class='bordered bg-02 level-center-center cat-image'>
                    <div class='right10 tooltipper level-center'>
                        <input name='cats[]' id='cat_{$cat['id']}' value='{$cat['id']}' class='styled tooltipper' data-parent='$cat_name' type='checkbox' {$checked} {$js} title='" . _fe('Select {0}', $parent === 'child' ? format_comment("'{$cat_name} :: {$cat['name']}'") : format_comment($cat['name'])) . "'>
                    </div>$image
                </div>
            </div>
        </a>";
}

/**
 *
 * @param int $user_cat
 *
 * @throws DependencyException
 * @throws NotFoundException
 * @throws \Envms\FluentPDO\Exception
 *
 * @return bool
 */
function find_parent(int $user_cat)
{
    $cats = genrelist(false);
    foreach ($cats as $cat) {
        if ($cat['id'] === $user_cat && $cat['parent_id'] != 0) {
            return $cat['parent_id'];
        }
    }

    return false;
}
