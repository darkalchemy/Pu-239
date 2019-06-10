<?php

declare(strict_types = 1);

use DI\DependencyException;
use DI\NotFoundException;
use Pu239\Image;
use Pu239\Torrent;

/**
 * @param      $text
 * @param      $id
 * @param      $block_id
 * @param      $name
 * @param      $poster
 * @param      $uploader
 * @param      $added
 * @param      $size
 * @param      $seeders
 * @param      $leechers
 * @param      $imdb_id
 * @param      $rating
 * @param      $year
 * @param      $subtitles
 * @param      $genre
 * @param bool $icons
 * @param null $is_comment
 *
 * @throws DependencyException
 * @throws NotFoundException
 * @throws \Envms\FluentPDO\Exception
 *
 * @return string
 */
function torrent_tooltip($text, $id, $block_id, $name, $poster, $uploader, $added, $size, $seeders, $leechers, $imdb_id, $rating, $year, $subtitles, $genre, $icons = false, $is_comment = null)
{
    global $container, $site_config, $lang;

    $is_year = $released = $rated = $plot = $show_subs = $show_icons = '';
    if (!empty($imdb_id)) {
        $is_comment = !empty($is_comment) ? '#comm' . $is_comment : '';
        $image_stuffs = $container->get(Image::class);
        $background = $image_stuffs->find_images($imdb_id, $type = 'background');
        $torrent = $container->get(Torrent::class);
        $plot = $torrent->get_plot($imdb_id);
        if (!empty($plot)) {
            $stripped = strip_tags($plot);
            $plot = strlen($stripped) > 500 ? substr($plot, 0, 500) . '...' : $stripped;
            $plot = "
                                                        <div class='column padding5 is-4'>
                                                            <span class='size_4 has-text-primary has-text-weight-bold'>Plot:</span>
                                                        </div>
                                                        <div class='column padding5 is-8'>
                                                            <span class='size_4'>{$plot}</span>
                                                        </div>";
        }
    }
    if (!empty($genre)) {
        $genre = "
                                                    <span class='column padding5 is-4'>
                                                        <span class='size_4 has-text-primary has-text-weight-bold'>Genre:</span>
                                                    </span>
                                                    <span class='column padding5 is-8'>
                                                        <span class='size_4'>{$genre}</span>
                                                    </span>";
    }
    if (!empty($rating) && $rating > 0) {
        $percent = $rating * 10;
        $rated = "
                                                    <div class='column padding5 is-4'>
                                                        <span class='size_4 has-text-primary has-text-weight-bold'>Rating:</span>
                                                    </div>
                                                    <div class='column padding5 is-8'>
                                                        <div class='level-left'>
                                                            <div class='right5'>$rating</div>
                                                            <div class='star-ratings-css'>
                                                                <div class='star-ratings-css-top' style='width: {$percent}%'><span>★</span><span>★</span><span>★</span><span>★</span><span>★</span></div>
                                                                <div class='star-ratings-css-bottom'><span>★</span><span>★</span><span>★</span><span>★</span><span>★</span></div>
                                                            </div>
                                                        </div>
                                                    </div>";
    }
    if (!empty($year)) {
        $is_year = " ($year)";
    }
    if (!empty($subtitles)) {
        $subs = $container->get('subtitles');
        $subtitles = explode('|', $subtitles);
        $Subs = [];
        foreach ($subtitles as $k => $subname) {
            foreach ($subs as $sub) {
                if (strtolower($sub['name']) === strtolower($subname)) {
                    $Subs[] = "<img src='{$site_config['paths']['images_baseurl']}/{$sub['pic']}' class='sub_flag tooltipper' alt='" . htmlsafechars($sub['name']) . "' title='" . htmlsafechars($sub['name']) . "'>";
                }
            }
        }

        if (!empty($Subs)) {
            $show_subs = "
                                                    <div class='column padding5 is-4'>
                                                        <span class='size_4 has-text-primary has-text-weight-bold'>Subtitles:</span>
                                                    </div>
                                                    <div class='column padding5 is-8'>
                                                        <span class='size_4 right10'>" . implode(' ', $Subs) . '</span>
                                                    </div>';
        }
    }
    if ($icons) {
        $show_icons = "
                                    <div class='level'>
                                        <div>$text</div>
                                        <div>$icons</div>
                                    </div>";
    }
    $background = !empty($background) ? " style='background-image: url({$background});'" : '';
    $content = "
                            <a class='is-link' href='{$site_config['paths']['baseurl']}/details.php?id={$id}&amp;hit=1{$is_comment}'>
                                <div class='dt-tooltipper-large' data-tooltip-content='#{$block_id}_tooltip'>
                                    $text
                                    <div class='tooltip_templates'>
                                        <div id='{$block_id}_tooltip' class='round10 tooltip-background'{$background}>
                                            <div class='tooltip-torrent padding10'>
                                                <div class='columns is-marginless is-paddingless'>
                                                    <div class='column padding10 is-4'>
                                                        <span>
                                                            $poster
                                                        </span>
                                                    </div>
                                                    <div class='column padding10 is-8'>
                                                        <div class='padding20 is-8 bg-09 round10'>
                                                            <div class='columns is-multiline'>
                                                                <div class='column padding5 is-4'>
                                                                    <span class='size_4 has-text-primary has-text-weight-bold'>{$lang['index_ltst_name']}</span>
                                                                </div>
                                                                <div class='column padding5 is-8'>" . htmlsafechars($name) . "{$is_year}</div>
                                                                <div class='column padding5 is-4'>
                                                                    <span class='size_4 has-text-primary has-text-weight-bold'>{$lang['index_ltst_uploader']}</span>
                                                                </div>
                                                                <div class='column padding5 is-8'>
                                                                    $uploader
                                                                </div>
                                                                <div class='column padding5 is-4'>
                                                                    <span class='size_4 has-text-primary has-text-weight-bold'>{$lang['index_ltst_added']}</span>
                                                                </div>
                                                                <div class='column padding5 is-8'>" . get_date((int) $added, 'DATE', 0, 1) . "</div>
                                                                <div class='column padding5 is-4'>
                                                                    <span class='size_4 has-text-primary has-text-weight-bold'>{$lang['index_ltst_size']}</span>
                                                                </div>
                                                                <div class='column padding5 is-8'>" . mksize($size) . "</div>{$genre}{$show_subs}{$show_icons}
                                                                <div class='column padding5 is-4'>
                                                                    <span class='size_4 has-text-primary has-text-weight-bold'>{$lang['index_ltst_seeder']}</span>
                                                                </div>
                                                                <div class='column padding5 is-8'>
                                                                    {$seeders}
                                                                </div>
                                                                <div class='column padding5 is-4'>
                                                                    <span class='size_4 has-text-primary has-text-weight-bold'>{$lang['index_ltst_leecher']}</span>
                                                                </div>
                                                                <div class='column padding5 is-8'>
                                                                    {$leechers}
                                                                </div>{$rated}{$plot}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </a>";

    return $content;
}

/**
 * @param      $text
 * @param      $id
 * @param      $block_id
 * @param      $name
 * @param      $poster
 * @param      $uploader
 * @param      $added
 * @param      $size
 * @param      $seeders
 * @param      $leechers
 * @param      $imdb_id
 * @param      $rating
 * @param      $year
 * @param      $subtitles
 * @param      $genre
 * @param bool $icons
 *
 * @throws DependencyException
 * @throws NotFoundException
 * @throws \Envms\FluentPDO\Exception
 *
 * @return string
 */
function torrent_tooltip_wrapper($text, $id, $block_id, $name, $poster, $uploader, $added, $size, $seeders, $leechers, $imdb_id, $rating, $year, $subtitles, $genre, $icons = false)
{
    global $site_config, $times_completed, $cat;

    $caticon = !empty($image) ? "<img src='{$site_config['paths']['images_baseurl']}caticons/" . get_category_icons() . '/' . htmlsafechars($image) . "' class='tooltipper' alt='" . htmlsafechars($cat) . "' title='" . htmlsafechars($cat) . "' height='20px' width='auto'>" : htmlsafechars($cat);
    $content = "
                    <tr>
                        <td class='has-text-centered'>$caticon</td>
                        <td>
                            <a href='{$site_config['paths']['baseurl']}/details.php?id={$id}'>
                                " . torrent_tooltip($text, $id, $block_id, $name, $poster, $uploader, $added, $size, $seeders, $leechers, $imdb_id, $rating, $year, $subtitles, $genre, $icons) . "
                            </a>
                        <td class='has-text-centered'>{$times_completed}</td>
                        <td class='has-text-centered'>{$seeders}</td>
                        <td class='has-text-centered'>{$leechers}</td>
                    </tr>";

    return $content;
}
