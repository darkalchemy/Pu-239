<?php

/**
 * @param string $text
 * @param int    $id
 * @param string $block_id
 * @param string $name
 * @param string $poster
 * @param string $uploader
 * @param int    $added
 * @param int    $size
 * @param int    $seeders
 * @param int    $leechers
 * @param string $imdb_id
 * @param int    $rating
 * @param int    $year
 * @param string $subtitles
 * @param string $genre
 * @param bool   $icons
 *
 * @return string
 *
 * @throws \Envms\FluentPDO\Exception
 */
function torrent_tooltip(string $text, int $id, string $block_id, string $name, string $poster, string $uploader, int $added, int $size, int $seeders, int $leechers, string $imdb_id, int $rating, int $year, string $subtitles, string $genre, $icons = false)
{
    global $site_config, $lang, $fluent, $subs, $cache;

    $is_year = $released = $rated = $plot = $show_subs = '';
    if (!empty($imdb_id)) {
        $background = find_images($imdb_id, $type = 'background');
        $plot = $cache->get('imdb_plot_' . $imdb_id);
        if ($plot === false || is_null($plot)) {
            $plot = $fluent->from('imdb_info')
                           ->select(null)
                           ->select('plot')
                           ->where('imdb_id=?', str_replace('tt', '', $imdb_id))
                           ->fetch('plot');

            $cache->set('imdb_plot_' . $imdb_id, $plot, 86400);
        }

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
                                                        <span class='level-left'>
                                                            <div class='right5'>$rating</div>
                                                            <div class='star-ratings-css'>
                                                                <div class='star-ratings-css-top' style='width: {$percent}%'><span>★</span><span>★</span><span>★</span><span>★</span><span>★</span></div>
                                                                <div class='star-ratings-css-bottom'><span>★</span><span>★</span><span>★</span><span>★</span><span>★</span></div>
                                                            </div>
                                                        </span>
                                                    </div>";
    }
    if (!empty($year)) {
        $released = "
                                                    <div class='column padding5 is-4'>
                                                        <span class='size_4 has-text-primary has-text-weight-bold'>Released:</span>
                                                    </div>
                                                    <div class='column padding5 is-8'>
                                                        <span class='size_4'>{$year}</span>
                                                    </div>";
        $is_year = " ($year)";
    }
    if (!empty($subtitles)) {
        require_once CACHE_DIR . 'subs.php';
        $subs_array = explode(',', $subtitles);
        foreach ($subs_array as $k => $sid) {
            foreach ($subs as $sub) {
                if ($sub['id'] == $sid) {
                    $Subs[] = "<img src='{$sub['pic']}' class='tooltipper icon' width='16px' alt='{$sub['name']}' title='{$sub['name']}'>";
                }
            }
        }
        if (!empty($Subs)) {
            $show_subs = "
                                                    <div class='column padding5 is-4'>
                                                        <span class='size_4 has-text-primary has-text-weight-bold'>Subtitles:</span>
                                                    </div>
                                                    <div class='column padding5 is-8'>
                                                        <span class='size_4'>" . implode(' ', $Subs) . '</span>
                                                    </div>';
        }
    }
    if ($icons) {
        $icons = "
                                    <div class='level'>
                                        <div>$text</div>
                                        <div>$icons</div>
                                    </div>";
    }
    $background = !empty($background) ? " style='background-image: url({$background});'" : '';
    $content = "
                            <a class='altlink' href='{$site_config['paths']['baseurl']}/details.php?id={$id}&amp;hit=1'>
                                <span class='dt-tooltipper-large' data-tooltip-content='#{$block_id}_tooltip'>
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
                                                                <div class='column padding5 is-8'>" . get_date($added, 'DATE', 0, 1) . "</div>
                                                                <div class='column padding5 is-4'>
                                                                    <span class='size_4 has-text-primary has-text-weight-bold'>{$lang['index_ltst_size']}</span>
                                                                </div>
                                                                <div class='column padding5 is-8'>" . mksize($size) . "</div>{$genre}{$show_subs}
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
                                </span>
                            </a>";

    return $content;
}

/**
 * @param string $text
 * @param int    $id
 * @param string $block_id
 * @param string $name
 * @param string $poster
 * @param string $uploader
 * @param int    $added
 * @param int    $size
 * @param int    $seeders
 * @param int    $leechers
 * @param string $imdb_id
 * @param int    $rating
 * @param int    $year
 * @param string $subtitles
 * @param string $genre
 *
 * @return string
 * @throws \Envms\FluentPDO\Exception
 */
function torrent_tooltip_wrapper(string $text, int $id, string $block_id, string $name, string $poster, string $uploader, int $added, int $size, int $seeders, int $leechers, string $imdb_id, int $rating, int $year, string $subtitles, string $genre)
{
    global $site_config, $image, $cat, $times_completed;

    $caticon = !empty($image) ? "<img src='{$site_config['paths']['images_baseurl']}caticons/" . get_category_icons() . '/' . htmlsafechars($image) . "' class='tooltipper' alt='" . htmlsafechars($cat) . "' title='" . htmlsafechars($cat) . "' height='20px' width='auto'>" : htmlsafechars($cat);
    $content = "
                    <tr>
                        <td class='has-text-centered'>$caticon</td>
                        <td>" . torrent_tooltip($text, $id, $block_id, $name, $poster, $uploader, $added, $size, $seeders, $leechers, $imdb_id, $rating, $year, $subtitles, $genre) . "
                        <td class='has-text-centered'>{$times_completed}</td>
                        <td class='has-text-centered'>{$seeders}</td>
                        <td class='has-text-centered'>{$leechers}</td>
                    </tr>";

    return $content;
}
