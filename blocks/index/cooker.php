<?php

declare(strict_types = 1);

use Pu239\Upcoming;

require_once INCL_DIR . 'function_torrent_hover.php';
$user = check_user_status();
global $container, $site_config, $lang;

$lang = array_merge($lang, load_language('upcoming'));
$cooker_class = $container->get(Upcoming::class);
$recipes = $cooker_class->get_all($site_config['latest']['recipes_limit'], 0, 'expected', false, false, true, (bool) $user['hidden']);
$cooker .= "
    <a id='cooker-hash'></a>
    <div id='cooker' class='box'>
        <div class='grid-wrapper'>
        <div class='table-wrapper has-text-centered'>
            <table class='table table-bordered table-striped'>
                <thead>
                    <tr>
                        <th class='has-text-centered has-no-border-right'>{$lang['upcoming_type']}</th>
                        <th class='min-250 has-no-border-right has-no-border-left'>{$lang['upcoming_name']}</th>
                        <th class='has-text-centered has-no-border-right has-no-border-left'>{$lang['upcoming_status']}</th>
                        <th class='has-text-centered has-no-border-right has-no-border-left'><i class='icon-hourglass-3 icon' aria-hidden='true'></i></th>
                        <th class='has-text-centered has-no-border-left'><i class='icon-user-plus icon' aria-hidden='true'></i></th>
                    </tr>
                </thead>
                <tbody>";
if (!empty($recipes) && is_array($recipes)) {
    foreach ($recipes as $recipe) {
        $class_color = get_user_class_name($recipe['class'], true);
        $caticon = !empty($recipe['image']) ? "<img src='{$site_config['paths']['images_baseurl']}caticons/" . get_category_icons() . '/' . format_comment($recipe['image']) . "' class='tooltipper' alt='" . format_comment($recipe['cat']) . "' title='" . format_comment($recipe['cat']) . "' height='20px' width='auto'>" : format_comment($recipe['cat']);
        $poster = !empty($recipe['poster']) ? "<div class='has-text-centered'><img src='" . url_proxy($recipe['poster'], true, 250) . "' alt='image' class='img-polaroid'></div>" : '';
        $background = $imdb_id = '';
        preg_match('#(tt\d{7,8})#', $recipe['url'], $match);
        if (!empty($match[1])) {
            $imdb_id = $match[1];
            $background = $images_class->find_images($imdb_id, $type = 'background');
            $background = !empty($background) ? "style='background-image: url({$background});'" : '';
            $poster = !empty($recipe['poster']) ? $recipe['poster'] : $images_class->find_images($imdb_id, $type = 'poster');
            $poster = empty($poster) ? "<img src='{$site_config['paths']['images_baseurl']}noposter.png' alt='Poster for {$recipe['name']}' class='tooltip-poster'>" : "<img src='" . url_proxy($poster, true, 250) . "' alt='Poster for {$recipe['name']}' class='tooltip-poster'>";
        }
        $chef = "<span class='" . get_user_class_name($recipe['class'], true) . "'>" . $recipe['username'] . '</span>';
        $plot = $torrent->get_plot($imdb_id);
        if (!empty($plot)) {
            $stripped = strip_tags($plot);
            $plot = strlen($stripped) > 500 ? substr($plot, 0, 500) . '...' : $stripped;
            $plot = "
                                                        <div class='column padding5 is-4'>
                                                            <span class='size_4 has-text-primary has-text-weight-bold'>{$lang['upcoming_plot']}:</span>
                                                        </div>
                                                        <div class='column padding5 is-8'>
                                                            <span class='size_4'>{$plot}</span>
                                                        </div>";
        }
        $hover = upcoming_hover($recipe['url'], 'upcoming_' . $recipe['id'], $recipe['name'], $background, $poster, $recipe['added'], $recipe['expected'], $chef, $plot, $lang);
        $cooker .= "
                    <tr>
                        <td class='has-text-centered has-no-border-right'>{$caticon}</td>
                        <td class='has-no-border-right has-no-border-left'>{$hover}</td>
                        <td class='has-text-centered has-no-border-right has-no-border-left'>" . ucfirst($recipe['status']) . "</td>
                        <td class='has-text-centered has-no-border-right has-no-border-left'><span class='tooltipper' title='" . calc_time_difference(strtotime($recipe['expected']) - TIME_NOW, true) . "'>" . calc_time_difference(strtotime($recipe['expected']) - TIME_NOW, false) . "</span></td>
                        <td class='has-text-centered has-no-border-left'>
                            <div data-id='{$recipe['id']}' data-notified='{$recipe['notify']}' class='cooker_notify tooltipper' title='" . ($recipe['notify'] === 1 ? $lang['upcoming_notified'] : $lang['upcoming_not_notified']) . "'>
                                <span id='notify_{$recipe['id']}'>" . ($recipe['notify'] === 1 ? "<i class='icon-mail icon has-text-success is-marginless' aria-hidden='true'></i>" : "<i class='icon-envelope-open-o icon has-text-info is-marginless' aria-hidden='true'></i>") . '</span>
                            </div>
                        </td>
                    </tr>';
    }
    $cooker .= '
                </tbody>
            </table>
        </div>
        </div>
    </div>';
} else {
    $cooker .= "
                    <tr>
                        <td colspan='5'>{$lang['upcoming_none']}</td>
                    </tr>
                </tbody>
            </table>
        </div>
        </div>
    </div>";
}
