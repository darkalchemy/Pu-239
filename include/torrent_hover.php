<?php

/**
 * @param $text
 *
 * @return string
 */
function torrent_tooltip($text, $id, $block_id, $name, $poster, $uploader, $added, $size, $seeders, $leechers, $imdb_id, $rating, $year, $subtitles)
{
    global $site_config, $lang, $fluent, $subs, $cache;

    $released = $rating = $plot = $show_subs = '';
    if (!empty($imdb_id)) {
        $plot = $cache->get('plot_' . $imdb_id);
        if ($plot === false || is_null($plot)) {
            $plot = $fluent->from('imdb_info')
                ->select(null)
                ->select('plot')
                ->where('imdb_id = ?', str_replace('tt', '', $imdb_id))
                ->fetch('plot');

            $cache->set('plot_' . $imdb_id, $plot, 86400);
        }

        if (!empty($plot)) {
            $stripped = strip_tags($plot);
            $plot = strlen($stripped) > 500 ? substr($plot, 0, 500) . '...' : $stripped;
            $plot = "
                                                        <div class='column padding5 is-4'>
                                                            <span class='size_4 has-text-primary'>Plot:</span>
                                                        </div>
                                                        <div class='column padding5 is-8'>
                                                            <span class='size_4'>{$plot}</span>
                                                        </div>";
        }
    }
    if (!empty($rating)) {
        $percent = $rating * 10;
        $rating = "
                                                    <div class='column padding5 is-4'>
                                                        <span class='size_4 has-text-primary'>Rating:</span>
                                                    </div>
                                                    <div class='column padding5 is-8'>
                                                        <span class='size_4 is-flex'>
                                                            <div class='right10'>$rating</div>
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
                                                        <span class='size_4 has-text-primary'>Released:</span>
                                                    </div>
                                                    <div class='column padding5 is-8'>
                                                        <span class='size_4'>{$year}</span>
                                                    </div>";
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
                                                        <span class='size_4 has-text-primary'>Subtitles:</span>
                                                    </div>
                                                    <div class='column padding5 is-8'>
                                                        <span class='size_4'>" . implode(' ', $Subs) . "</span>
                                                    </div>";
        }
    }

    $content = "
                            <a href='{$site_config['baseurl']}/details.php?id={$id}&amp;hit=1'>
                                <span class='dt-tooltipper-large' data-tooltip-content='#{$block_id}_tooltip'>
                                    {$text}
                                    <div class='tooltip_templates'>
                                        <div id='{$block_id}_tooltip'>
                                            <div class='tooltip-torrent padding10'>
                                                <div class='columns is-marginless is-paddingless'>
                                                    <div class='column padding10 is-4'>
                                                        <span>
                                                            $poster
                                                        </span>
                                                    </div>
                                                    <div class='column padding10 is-8'>
                                                        <span>
                                                            <div class='columns is-multiline'>
                                                                <div class='column padding5 is-4'>
                                                                    <span class='size_4 has-text-primary'>{$lang['index_ltst_name']}</span>
                                                                </div>
                                                                <div class='column padding5 is-8'>" . htmlsafechars($name) . "</div>
                                                                <div class='column padding5 is-4'>
                                                                    <span class='size_4 has-text-primary'>{$lang['index_ltst_uploader']}</span>
                                                                </div>
                                                                <div class='column padding5 is-8'>
                                                                    $uploader
                                                                </div>
                                                                <div class='column padding5 is-4'>
                                                                    <span class='size_4 has-text-primary'>{$lang['index_ltst_added']}</span>
                                                                </div>
                                                                <div class='column padding5 is-8'>" . get_date($added, 'DATE', 0, 1) . "</div>
                                                                <div class='column padding5 is-4'>
                                                                    <span class='size_4 has-text-primary'>{$lang['index_ltst_size']}</span>
                                                                </div>
                                                                <div class='column padding5 is-8'>" . mksize($size) . "</div>$show_subs
                                                                <div class='column padding5 is-4'>
                                                                    <span class='size_4 has-text-primary'>{$lang['index_ltst_seeder']}</span>
                                                                </div>
                                                                <div class='column padding5 is-8'>
                                                                    {$seeders}
                                                                </div>
                                                                <div class='column padding5 is-4'>
                                                                    <span class='size_4 has-text-primary'>{$lang['index_ltst_leecher']}</span>
                                                                </div>
                                                                <div class='column padding5 is-8'>
                                                                    {$leechers}
                                                                </div>{$released}{$rating}{$plot}
                                                            </div>
                                                        </span>
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
 * @param $text
 *
 * @return string
 */
function torrent_tooltip_wrapper($text, $id, $block_id, $name, $poster, $uploader, $added, $size, $seeders, $leechers, $imdb_id, $rating, $year, $subtitles)
{
    global $site_config, $image, $cat, $times_completed;

    $content = "
                    <tr>
                        <td class='has-text-centered'>
                            <img src='{$site_config['pic_baseurl']}caticons/" . get_category_icons() . '/' . htmlsafechars($image) . "' class='tooltipper' alt='" . htmlsafechars($cat) . "' title='" . htmlsafechars($cat) . "' height='20px' width='auto'>
                        </td>
                        <td>" . torrent_tooltip($text, $id, $block_id, $name, $poster, $uploader, $added, $size, $seeders, $leechers, $imdb_id, $rating, $year, $subtitles) . "
                        <td class='has-text-centered'>{$times_completed}</td>
                        <td class='has-text-centered'>{$seeders}</td>
                        <td class='has-text-centered'>{$leechers}</td>
                    </tr>";

    return $content;
}
