<?php
global $mc1, $lang, $site_config;
$categorie = genrelist();
foreach ($categorie as $key => $value) {
    $change[$value['id']] = [
        'id'    => $value['id'],
        'name'  => $value['name'],
        'image' => $value['image'],
    ];
}
if (($motw_cached = $mc1->get_value('top_movie_2')) === false) {
    $motw = sql_query("SELECT t.added, t.checked_by, t.id, t.seeders, t.poster, t.leechers, t.name, t.size, t.category, c.name AS cat, c.image, t.free, t.silver, t.subs, t.times_completed, t.added, t.size
                        FROM torrents AS t
                        LEFT JOIN categories AS c ON t.category = c.id
                        INNER JOIN avps AS a ON t.id = a.value_u WHERE a.arg = 'bestfilmofweek'
                        LIMIT 1") or sqlerr(__FILE__, __LINE__);
    while ($motw_cache = mysqli_fetch_assoc($motw)) {
        $motw_cached[] = $motw_cache;
    }
    $mc1->cache_value('top_movie_2', $motw_cached, 0);
}

if (count($motw_cached) > 0) {
    $HTMLOUT .= "
    <a id='mow-hash'></a>
    <fieldset id='mow' class='header'>
        <legend class='flipper has-text-primary'><i class='fa fa-angle-up right10' aria-hidden='true'></i>{$lang['index_mow_title']}</legend>
        <div class='has-text-centered'>
            <div class='table-wrapper module'><div class='badge badge-hot'></div>
                <table class='table table-bordered table-striped'>
                    <thead>
                        <tr>
                            <th class='has-text-centered w-10'>{$lang['index_mow_type']}</th>
                            <th class='w-50'>{$lang['index_mow_name']}</th>
                            <th class='has-text-centered'>{$lang['index_mow_snatched']}</th>
                            <th class='has-text-centered'>{$lang['index_mow_seeder']}</th>
                            <th class='has-text-centered'>{$lang['index_mow_leecher']}</th>
                        </tr>
                    </thead>
                    <tbody>";
    if ($motw_cached) {
        foreach ($motw_cached as $m_w) {
            $torrname = htmlsafechars($m_w['name']);
            if (strlen($torrname) > 50) {
                $torrname = substr($torrname, 0, 50) . '...';
            }
            $poster = empty($m_w['poster']) ? "<img src='{$site_config['pic_base_url']}noposter.png' class='tooltip-poster' />" : "<img src='" . htmlsafechars($m_w['poster']) . "' class='tooltip-poster' />";
            $mw['cat_name'] = htmlsafechars($change[$m_w['category']]['name']);
            $mw['cat_pic'] = htmlsafechars($change[$m_w['category']]['image']);

            $HTMLOUT .= "
                        <tr>
                            <td class='has-text-centered'><img src='./images/caticons/" . get_categorie_icons() . "/" . $mw['cat_pic'] . "' class='tooltipper' alt='" . $mw['cat_name'] . "' title='" . $mw['cat_name'] . "' /></td>
                            <td>
                                <a href='{$site_config['baseurl']}/details.php?id=" . (int)$m_w['id'] . "&amp;hit=1'>
                                    <span class='dt-tooltipper-large' data-tooltip-content='#mow_id_{$m_w['id']}_tooltip'>
                                        {$torrname}
                                        <div class='tooltip_templates'>
                                            <span id='mow_id_{$m_w['id']}_tooltip'>
                                                <div class='is-flex tooltip-torrent'>
                                                    <span class='margin10'>
                                                        $poster
                                                    </span>
                                                    <span class='margin10'>
                                                        <b class='size_4 right10 has-text-primary'>{$lang['index_ltst_name']}</b>" . htmlsafechars($m_w['name']) . "<br>
                                                        <b class='size_4 right10 has-text-primary'>{$lang['index_ltst_added']}</b>" . get_date($m_w['added'], 'DATE', 0, 1) . "<br>
                                                        <b class='size_4 right10 has-text-primary'>{$lang['index_ltst_size']}</b>" . mksize(htmlsafechars($m_w['size'])) . "<br>
                                                        <b class='size_4 right10 has-text-primary'>{$lang['index_ltst_seeder']}</b>" . (int)$m_w['seeders'] . "<br>
                                                        <b class='size_4 right10 has-text-primary'>{$lang['index_ltst_leecher']}</b>" . (int)$m_w['leechers'] . "<br>
                                                    </span>
                                                </div>
                                            </span>
                                        </div>
                                    </span>
                                </a>
                            </td>
                            </td>
                            <td class='has-text-centered'>" . (int)$m_w['times_completed'] . "</td>
                            <td class='has-text-centered'>" . (int)$m_w['seeders'] . "</td>
                            <td class='has-text-centered'>" . (int)$m_w['leechers'] . "</td>
                        </tr>";
        }
        $HTMLOUT .= "
                    </tbody>
                </table>
            </div>
        </div>
    </fieldset>";
    } else {
        if (empty($motw_cached)) {
            $HTMLOUT .= "
                        <tr>
                            <td colspan='5'>{$lang['index_mow_no']}!</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </fieldset>";
        }
    }
}
