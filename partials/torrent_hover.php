<?php

$HTMLOUT .= "
                            <a href='{$site_config['baseurl']}/details.php?id={$id}&amp;hit=1'>
                                <span class='dt-tooltipper-large' data-tooltip-content='#{$block_id}_tooltip'>
                                    {$torrname}
                                    <div class='tooltip_templates'>
                                        <div id='{$block_id}_tooltip'>
                                            <div class='is-flex tooltip-torrent'>
                                                <span class='margin10'>
                                                    $poster
                                                </span>
                                                <span class='margin10'>
                                                    <b class='size_4 right10 has-text-primary'>{$lang['index_ltst_name']}</b>" . htmlsafechars($name) . "<br>
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
