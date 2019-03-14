<?php

$grouped = genrelist(true);
$main_div = "
        <div class='padding20'>
            <div id='parents' class='level-center'>";

$children = '';
foreach ($grouped as $cat) {
    $main_div .= format_row($cat, 'parent', $cat['name']);
    $children .= "
            <div id='{$cat['name']}' class='top20 level-center children padding20 bg-02 round10" . (!in_array($cat['id'], $cats) ? ' is_hidden' : '') . "'>";
    foreach ($cat['children'] as $child) {
        $children .= format_row($child, 'child', $cat['name']);
    }
    $children .= '
            </div>';
}

$main_div .= "
            </div>$children
        </div>";
$HTMLOUT .= main_div($main_div, 'bottom20');

function format_row(array $cat, string $parent, string $cat_name)
{
    global $site_config, $CURUSER, $cats;

    if ($parent === 'child') {
        $js = '';
    } else {
        $js = 'onclick="return showMe(event);"';
    }

    $image = !empty($cat['image']) && $CURUSER['opt2'] & user_options_2::BROWSE_ICONS ? "
        <span class='left10'>
            <a href='{$site_config['baseurl']}/browse.php?c{$cat['id']}'>
                <img class='caticon' src='{$site_config['pic_baseurl']}caticons/{$CURUSER['categorie_icon']}/" . htmlsafechars($cat['image']) . "'alt='" . htmlsafechars($cat['name']) . "'>
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
