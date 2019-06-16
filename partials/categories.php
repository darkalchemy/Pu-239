<?php

declare(strict_types = 1);

$grouped = genrelist(true);
$cats = [];
$main_div = "
        <div class='padding20'>
            <div id='parents' class='level-wide'>";

$children = '';
foreach ($grouped as $cat) {
    $main_div .= format_row($cat, 'parent', $cat['name']);
    $children .= "
            <div id='{$cat['name']}' class='top20 level-wide children padding20 bg-03 round10" . (!in_array($cat['id'], $cats) ? ' is_hidden' : '') . "'>";
    foreach ($cat['children'] as $child) {
        if (is_array($child)) {
            $children .= format_row($child, 'child', $cat['name']);
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
 *
 * @return string
 */
function format_row(array $cat, string $parent, string $cat_name)
{
    global $site_config, $CURUSER;

    if ($parent === 'child') {
        $js = '';
    } else {
        $js = 'onclick="return showMe(event);"';
    }

    $image = !empty($cat['image']) && $CURUSER['opt2'] & user_options_2::BROWSE_ICONS ? "
        <span class='left10'>
            <a href='{$site_config['paths']['baseurl']}/browse.php?c{$cat['id']}'>
                <img class='caticon' src='{$site_config['paths']['images_baseurl']}caticons/{$CURUSER['categorie_icon']}/" . htmlsafechars($cat['image']) . "'alt='" . htmlsafechars($cat['name']) . "'>
            </a>
        </span>" : "
        <span class='left10'>" . htmlsafechars($cat['name']) . '</span>';

    return "
        <span class='margin10 is-flex tooltipper' title='" . htmlsafechars($cat['name']) . "'>
            <span class='bordered level-center bg-02 cat-image'>
                <input name='cats[]' id='cat_{$cat['id']}' value='{$cat['id']}' class='styled' data-parent='$cat_name' type='checkbox' " . (!empty($cats) && in_array($cat['id'], $cats) ? ' checked' : '') . " {$js}>$image
            </span>
        </span>";
}
