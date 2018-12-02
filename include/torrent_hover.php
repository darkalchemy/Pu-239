<?php

/**
 * @param $text
 *
 * @return string
 */
function torrent_tooltip($text)
{
    global $site_config, $id, $block_id, $name, $poster, $lang, $uploader, $added, $size, $seeders, $leechers;

    $content = "
                            <a href='{$site_config['baseurl']}/details.php?id={$id}&amp;hit=1'>
                                <span class='dt-tooltipper-large' data-tooltip-content='#{$block_id}_tooltip'>
                                    <span class='torrent-name narrow'>{$text}</span>
                                    <div class='tooltip_templates'>
                                        <div id='{$block_id}_tooltip'>
                                            <div class='is-flex tooltip-torrent'>
                                                <span class='margin10'>
                                                    $poster
                                                </span>
                                                <span class='margin10'>
                                                    <b class='size_4 right10 has-text-primary torrent_name narrow'>{$lang['index_ltst_name']}</b>" . htmlsafechars($name) . "<br>
                                                    <b class='size_4 right10 has-text-primary'>{$lang['index_ltst_uploader']}</b>$uploader<br>
                                                    <b class='size_4 right10 has-text-primary'>{$lang['index_ltst_added']}</b>" . get_date($added, 'DATE', 0, 1) . "<br>
                                                    <b class='size_4 right10 has-text-primary'>{$lang['index_ltst_size']}</b>" . mksize(htmlsafechars($size)) . "<br>
                                                    <b class='size_4 right10 has-text-primary'>{$lang['index_ltst_seeder']}</b>{$seeders}<br>
                                                    <b class='size_4 right10 has-text-primary'>{$lang['index_ltst_leecher']}</b>{$leechers}<br>
                                                </span>
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
function torrent_tooltip_wrapper($text)
{
    global $site_config, $image, $cat, $times_completed, $seeders, $leechers;

    $content = "
                    <tr>
                        <td class='has-text-centered'>
                            <img src='{$site_config['pic_baseurl']}caticons/" . get_category_icons() . '/' . htmlsafechars($image) . "' class='tooltipper' alt='" . htmlsafechars($cat) . "' title='" . htmlsafechars($cat) . "' height='20px' width='auto'>
                        </td>
                        <td>" . torrent_tooltip($text) . "
                        <td class='has-text-centered'>{$times_completed}</td>
                        <td class='has-text-centered'>{$seeders}</td>
                        <td class='has-text-centered'>{$leechers}</td>
                    </tr>";

    return $content;
}
