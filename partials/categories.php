<?php

declare(strict_types = 1);

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
$cats = !empty($get['cats']) ? $get['cats'] : [];
$main_div = "
        <div class='padding20'>
            <div id='parents' class='level-wide'>";

$children = '';
foreach ($grouped as $cat) {
    $main_div .= format_row($cat, 'parent', $cat['name'], $grouped, $cats, $terms);
    $children .= "
            <div id='{$cat['name']}' class='top20 level-wide children padding20 bg-03 round10" . (!in_array($cat['id'], $cats) ? ' is_hidden' : '') . "'>";
    foreach ($cat['children'] as $child) {
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
 * @param array  $cat
 * @param string $parent
 * @param string $cat_name
 * @param array  $grouped
 * @param array  $cats
 * @param array  $terms
 *
 * @return string
 */
function format_row(array $cat, string $parent, string $cat_name, array $grouped, array $cats, array $terms)
{
    global $site_config, $CURUSER;

    $terms = !empty($terms) ? '&amp;' . implode('&amp;', $terms) : '';
    $checked = in_array($cat['id'], $cats) ? ' checked' : '';
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
        $js = ' onclick="return showMe(event);"';
    }
    $link = "{$_SERVER['PHP_SELF']}?cats[]={$cat['id']}&amp;" . implode('&amp;', $list) . $terms;
    $image = !empty($cat['image']) && $CURUSER['opt2'] & user_options_2::BROWSE_ICONS ? "
        <span class='left10'>
            <a href='{$site_config['paths']['baseurl']}/browse.php?c{$cat['id']}'>
                <img class='caticon' src='{$site_config['paths']['images_baseurl']}caticons/{$CURUSER['categorie_icon']}/" . htmlsafechars($cat['image']) . "'alt='" . htmlsafechars($cat['name']) . "'>
            </a>
        </span>" : "
        <span class='left10'>" . htmlsafechars($cat['name']) . '</span>';

    return "
        <a href='{$link}'>
            <span class='margin10 is-flex tooltipper' title='" . htmlsafechars($cat['name']) . "'>
                <span class='bordered level-center bg-02 cat-image'>
                    <input name='cats[]' id='cat_{$cat['id']}' value='{$cat['id']}' class='styled' data-parent='$cat_name' type='checkbox'{$checked}{$js}>$image
                </span>
            </span>
        </a>";
}
