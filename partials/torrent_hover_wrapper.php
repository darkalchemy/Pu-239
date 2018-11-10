<?php

$HTMLOUT .= "
                    <tr>
                        <td class='has-text-centered'>
                            <img src='{$site_config['pic_baseurl']}caticons/" . get_category_icons() . '/' . htmlsafechars($image) . "' class='tooltipper' alt='" . htmlsafechars($cat) . "' title='" . htmlsafechars($cat) . "'>
                        </td>
                        <td>";

include PARTIALS_DIR . 'torrent_hover.php';
$HTMLOUT .= "
                        <td class='has-text-centered'>{$times_completed}</td>
                        <td class='has-text-centered'>{$seeders}</td>
                        <td class='has-text-centered'>{$leechers}</td>
                    </tr>";
