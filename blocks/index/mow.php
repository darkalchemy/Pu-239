<?php
global $cache, $lang, $site_config, $fpdo;

$categorie = genrelist();
foreach ($categorie as $key => $value) {
    $change[ $value['id'] ] = [
        'id'    => $value['id'],
        'name'  => $value['name'],
        'image' => $value['image'],
    ];
}
$motw = $cache->get('motw_');
if ($motw === false || is_null($motw)) {
    $query = $fpdo->from('torrents')
        ->leftJoin('users ON torrents.owner = users.id')
        ->select('users.username')
        ->select('users.class')
        ->leftJoin('categories ON torrents.category = categories.id')
        ->select('categories.name AS cat')
        ->select('categories.image')
        ->leftJoin('avps ON torrents.id = avps.value_u')
        ->where('avps.arg', 'bestfilmofweek');
    foreach ($query as $row) {
        $motw[] = $row;
    }
    $cache->set('motw_', $motw, 0);
}

if (count($motw) > 0) {
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
    if ($motw) {
        foreach ($motw as $m_w) {
            $torrname = htmlsafechars($m_w['name']);
            if (strlen($torrname) > 50) {
                $torrname = substr($torrname, 0, 50) . '...';
            }
            $poster = empty($m_w['poster']) ? "<img src='{$site_config['pic_base_url']}noposter.png' class='tooltip-poster' />" : "<img src='" . htmlsafechars($m_w['poster']) . "' class='tooltip-poster' />";
            $mw['cat_name'] = htmlsafechars($change[ $m_w['category'] ]['name']);
            $mw['cat_pic'] = htmlsafechars($change[ $m_w['category'] ]['image']);

            $HTMLOUT .= "
                        <tr>
                            <td class='has-text-centered'><img src='{$site_config['pic_base_url']}caticons/" . get_categorie_icons() . "/" . $mw['cat_pic'] . "' class='tooltipper' alt='" . $mw['cat_name'] . "' title='" . $mw['cat_name'] . "' /></td>
                            <td>
                                <a href='{$site_config['baseurl']}/details.php?id=" . (int)$m_w['id'] . "&amp;hit=1'>
                                    <span class='dt-tooltipper-large' data-tooltip-content='#mow_id_{$m_w['id']}_tooltip'>
                                        {$torrname}
                                        <div class='tooltip_templates'>
                                            <div id='mow_id_{$m_w['id']}_tooltip'>
                                                <div class='is-flex tooltip-torrent'>
                                                    <span class='margin10'>
                                                        $poster
                                                    </span>
                                                    <span class='margin10'>
                                                        <b class='size_4 right10 has-text-primary'>{$lang['index_ltst_name']}</b>" . htmlsafechars($m_w['name']) . "<br>
                                                        <b class='size_4 right10 has-text-primary'>{$lang['index_ltst_uploader']}</b><span class='" . get_user_class_name($m_w['class'], true) . "'>" . htmlsafechars($m_w['username']) . "</span><br>
                                                        <b class='size_4 right10 has-text-primary'>{$lang['index_ltst_added']}</b>" . get_date($m_w['added'], 'DATE', 0, 1) . "<br>
                                                        <b class='size_4 right10 has-text-primary'>{$lang['index_ltst_size']}</b>" . mksize(htmlsafechars($m_w['size'])) . "<br>
                                                        <b class='size_4 right10 has-text-primary'>{$lang['index_ltst_seeder']}</b>" . (int)$m_w['seeders'] . "<br>
                                                        <b class='size_4 right10 has-text-primary'>{$lang['index_ltst_leecher']}</b>" . (int)$m_w['leechers'] . "<br>
                                                    </span>
                                                </div>
                                            </div>
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
        if (empty($motw)) {
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
