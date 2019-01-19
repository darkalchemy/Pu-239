<?php

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_html.php';
require_once INCL_DIR . 'function_torrenttable.php';
require_once INCL_DIR . 'function_pager.php';
check_user_status();
global $CURUSER;

$lang = array_merge(load_language('global'), load_language('torrenttable_functions'), load_language('bookmark'));
$stdfoot = [
    'js' => [
        get_file_name('bookmarks_js'),
    ],
];

$htmlout = '';
/**
 * @param        $res
 * @param string $variant
 *
 * @return string
 */
function bookmarktable($res, $variant = 'index')
{
    global $site_config, $CURUSER, $lang, $session;

    $htmlout = "
    <div class='has-text-centered bottom20'>
        {$lang['bookmarks_icon']}
        <i class='icon-trash-empty icon has-text-danger'></i>{$lang['bookmarks_del1']}
        <i class='icon-download icon'></i>{$lang['bookmarks_down1']}
        <i class='icon-key icon'></i>{$lang['bookmarks_private1']}
        <i class='icon-users icon'></i>{$lang['bookmarks_public1']}
    </div>
    <div class='table-wrapper'>
        <div class='portlet'>
            <table class='table table-bordered table-striped top20 bottom20''>
                <thead>
                    <tr>
                        <th class='has-text-centered'>{$lang['torrenttable_type']}</th>
                        <th class='has-text-left'>{$lang['torrenttable_name']}</th>";
    $htmlout .= ($variant === 'index' ? '
                        <th class="has-text-centered">' . $lang['bookmarks_del2'] . '</th>
                        <th class="has-text-right">' : '') . '' . $lang['bookmarks_down2'] . '</th>
                        <th class="has-text-right">' . $lang['bookmarks_share'] . '</th>';
    if ($variant === 'mytorrents') {
        $htmlout .= "
                        <th class='has-text-centered'>{$lang['torrenttable_edit']}</th>
                        <th class='has-text-centered'>{$lang['torrenttable_visible']}</th>";
    }
    $htmlout .= "
                        <th class='has-text-right'>{$lang['torrenttable_files']}</th>
                        <th class='has-text-right'>{$lang['torrenttable_comments']}</th>
                        <th class='has-text-centered'>{$lang['torrenttable_added']}</th>
                        <th class='has-text-centered'>{$lang['torrenttable_size']}</th>
                        <th class='has-text-centered'>{$lang['torrenttable_snatched']}</th>
                        <th class='has-text-right'>{$lang['torrenttable_seeders']}</th>
                        <th class='has-text-right'>{$lang['torrenttable_leechers']}</th>";
    if ($variant === 'index') {
        $htmlout .= "
                        <th class='has-text-centered'>{$lang['torrenttable_uppedby']}</th>";
    }
    $htmlout .= '
                    </tr>
                </thead>
                <tbody>';
    $categories = genrelist(false);
    $change = [];
    foreach ($categories as $key => $value) {
        $change[$value['id']] = [
            'id' => $value['id'],
            'name' => $value['name'],
            'image' => $value['image'],
        ];
    }
    while ($row = mysqli_fetch_assoc($res)) {
        $row['cat_name'] = htmlsafechars($change[$row['category']]['name']);
        $row['cat_pic'] = htmlsafechars($change[$row['category']]['image']);
        $id = (int) $row['id'];
        $htmlout .= "
                    <tr>
                        <td class='has-text-centered'>";
        if (isset($row['cat_name'])) {
            $htmlout .= '<a href="' . $site_config['baseurl'] . '/browse.php?cat=' . (int) $row['category'] . '">';
            if (isset($row['cat_pic']) && $row['cat_pic'] != '') {
                $htmlout .= "<img src='{$site_config['pic_baseurl']}caticons/" . get_category_icons() . '/' . htmlsafechars($row['cat_pic']) . "' alt='" . htmlsafechars($row['cat_name']) . "' class='tooltipper' title='" . htmlsafechars($row['cat_name']) . "'>";
            } else {
                $htmlout .= htmlsafechars($row['cat_name']);
            }
            $htmlout .= '</a>';
        } else {
            $htmlout .= '-';
        }
        $htmlout .= '
                        </td>';
        $dispname = htmlsafechars($row['name']);
        $htmlout .= "
                        <td class='has-text-left'>
                            <a href='{$site_config['baseurl']}/details.php?";
        if ($variant === 'mytorrents') {
            $htmlout .= 'returnto=' . urlencode($_SERVER['REQUEST_URI']) . '&amp;';
        }
        $htmlout .= "id=$id";
        if ($variant === 'index') {
            $htmlout .= '&amp;hit=1';
        }
        $htmlout .= "'><b>$dispname</b></a>&#160;
                        </td>";
        $htmlout .= ($variant === 'index' ? "
                        <td class='has-text-centered'>
                            <span data-tid='{$id}' data-csrf='" . $session->get('csrf_token') . "' data-remove='true' data-private='false' class='bookmarks tooltipper' title='{$lang['bookmarks_del3']}'>
                                <i class='icon-trash-empty icon has-text-danger'></i>
                            </span>
                        </td>" : '');
        $htmlout .= ($variant === 'index' ? "
                        <td class='has-text-centered'>
                            <a href='{$site_config['baseurl']}/download.php?torrent={$id}' class='tooltipper' title='{$lang['bookmarks_down3']}'>
                                <i class='icon-download icon'></i>
                            </a>
                        </td>" : '');
        $bm = sql_query('SELECT * FROM bookmarks WHERE torrentid=' . sqlesc($id) . ' && userid=' . sqlesc($CURUSER['id']));
        $bms = mysqli_fetch_assoc($bm);
        if ($bms['private'] === 'yes' && $bms['userid'] == $CURUSER['id']) {
            $htmlout .= ($variant === 'index' ? "
                        <td class='has-text-centered'>
                            <span data-tid='{$id}' data-csrf='" . $session->get('csrf_token') . "' data-remove='false' data-private='true' class='bookmarks tooltipper' title='{$lang['bookmarks_public2']}'>
                                <i class='icon-key icon'></i>
                            </span>
                        </td>" : '');
        } elseif ($bms['private'] === 'no' && $bms['userid'] == $CURUSER['id']) {
            $htmlout .= ($variant === 'index' ? "
                        <td class='has-text-centered'>
                            <span data-tid='{$id}' data-csrf='" . $session->get('csrf_token') . "' data-remove='false' data-private='true' class='bookmarks tooltipper' title='{$lang['bookmarks_private2']}'>
                                <i class='icon-users icon'></i>
                            </span>
                        </td>" : '');
        }
        if ($variant === 'mytorrents') {
            $htmlout .= "
                        </td>
                        <td class='has-text-centered'>
                            <a href='{$site_config['baseurl']}/edit.php?returnto=" . urlencode($_SERVER['REQUEST_URI']) . '&amp;id=' . (int) $row['id'] . "'>{$lang['torrenttable_edit']}</a>";
        }
        if ($variant === 'mytorrents') {
            $htmlout .= "
                        <td class='has-text-right'>";
            if ($row['visible'] === 'no') {
                $htmlout .= '<b>' . $lang['torrenttable_not_visible'] . '</b>';
            } else {
                $htmlout .= '' . $lang['torrenttable_visible'] . '';
            }
            $htmlout .= '
                        </td>';
        }
        if ($variant === 'index') {
            $htmlout .= "
                        <td class='has-text-right'><b><a href='{$site_config['baseurl']}/filelist.php?id=$id'>" . (int) $row['numfiles'] . '</a></b></td>';
        } else {
            $htmlout .= "
                        <td class='has-text-right'><b><a href='{$site_config['baseurl']}/filelist.php?id=$id'>" . (int) $row['numfiles'] . '</a></b></td>';
        }
        if (!$row['comments']) {
            $htmlout .= "
                        <td class='has-text-right'>" . (int) $row['comments'] . '</td>';
        } else {
            if ($variant === 'index') {
                $htmlout .= "
                        <td class='has-text-right'><b><a href='{$site_config['baseurl']}/details.php?id=$id&amp;hit=1&amp;tocomm=1'>" . (int) $row['comments'] . '</a></b></td>';
            } else {
                $htmlout .= "
                        <td class='has-text-right'><b><a href='{$site_config['baseurl']}/details.php?id=$id&amp;page=0#startcomments'>" . (int) $row['comments'] . '</a></b></td>';
            }
        }
        $htmlout .= "
                        <td class='has-text-centered'><span>" . str_replace(',', '<br>', get_date($row['added'], '')) . "</span></td>
                        <td class='has-text-centered'>" . str_replace(' ', '<br>', mksize($row['size'])) . '</td>';
        if ($row['times_completed'] != 1) {
            $_s = '' . $lang['torrenttable_time_plural'] . '';
        } else {
            $_s = '' . $lang['torrenttable_time_singular'] . '';
        }
        $htmlout .= "
                        <td class='has-text-centered'><a href='{$site_config['baseurl']}/snatches.php?id=$id'>" . number_format($row['times_completed']) . "<br>$_s</a></td>";
        if ((int) $row['seeders']) {
            if ($variant === 'index') {
                if ($row['leechers']) {
                    $ratio = (int) $row['seeders'] / (int) $row['leechers'];
                } else {
                    $ratio = 1;
                }
                $htmlout .= "
                        <td class='has-text-right'><b><a href='{$site_config['baseurl']}/peerlist.php?id=$id#seeders'><span style='color: " . get_slr_color($ratio) . ";'>" . (int) $row['seeders'] . '</span></a></b></td>';
            } else {
                $htmlout .= "
                        <td class='has-text-right'><b><a class='" . linkcolor($row['seeders']) . "' href='{$site_config['baseurl']}/peerlist.php?id=$id#seeders'>" . (int) $row['seeders'] . '</a></b></td>';
            }
        } else {
            $htmlout .= "
                        <td class='has-text-right'><span class='" . linkcolor($row['seeders']) . "'>" . (int) $row['seeders'] . '</span></td>';
        }
        if ((int) $row['leechers']) {
            if ($variant === 'index') {
                $htmlout .= "
                        <td class='has-text-right'><b><a href='{$site_config['baseurl']}/peerlist.php?id=$id#leechers'>" . number_format($row['leechers']) . '</a></b></td>';
            } else {
                $htmlout .= "
                        <td class='has-text-right'><b><a class='" . linkcolor($row['leechers']) . "' href='{$site_config['baseurl']}/peerlist.php?id=$id#leechers'>" . (int) $row['leechers'] . '</a></b></td>';
            }
        } else {
            $htmlout .= "
                        <td class='has-text-right'>0</td>";
        }
        if ($variant === 'index') {
            $htmlout .= "
                        <td class='has-text-centered'>" . (isset($row['owner']) ? format_username($row['owner']) : '<i>(' . $lang['torrenttable_unknown_uploader'] . ')</i>') . '</td>';
        }
        $htmlout .= '
                    </tr>';
    }
    $htmlout .= '
                </tbody>
            </table>
        </div>
    </div>';

    return $htmlout;
}

$userid = isset($_GET['id']) ? (int) $_GET['id'] : $CURUSER['id'];
if (!is_valid_id($userid)) {
    stderr($lang['bookmarks_err'], $lang['bookmark_invalidid']);
}
if ($userid != $CURUSER['id']) {
    stderr($lang['bookmarks_err'], "{$lang['bookmarks_denied']}<a href='{$site_config['baseurl']}/sharemarks.php?id={$userid}'>{$lang['bookmarks_here']}</a>");
}
$res = sql_query('SELECT id, username FROM users WHERE id = ' . sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
$arr = mysqli_fetch_array($res);
$htmlout .= '
    <div class="has-text-centered bottom20">
        <h1>' . $lang['bookmarks_my'] . '</h1>
        <div class="tabs is-centered">
            <ul>
                <li><a href="' . $site_config['baseurl'] . '/sharemarks.php?id=' . $CURUSER['id'] . '" class="altlink">' . $lang['bookmarks_my_share'] . '</a></li>
            </ul>
        </div>
    </div>';

$res = sql_query('SELECT COUNT(id) FROM bookmarks WHERE userid = ' . sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
$row = mysqli_fetch_array($res);
$count = $row[0];
$torrentsperpage = $CURUSER['torrentsperpage'];
if (!$torrentsperpage) {
    $torrentsperpage = 25;
}
if ($count) {
    $pager = pager($torrentsperpage, $count, 'bookmarks.php?&amp;');
    $query1 = 'SELECT b.id as bookmarkid, t.owner, t.id, t.name, t.comments, t.leechers, t.seeders, t.save_as, t.numfiles, t.added, t.filename, t.size, t.views, t.visible, t.hits, t.times_completed, t.category
                FROM bookmarks AS b
                LEFT JOIN torrents AS t ON b.torrentid = t.id
                WHERE b.userid =' . sqlesc($userid) . "
                ORDER BY t.id DESC {$pager['limit']}" or sqlerr(__FILE__, __LINE__);
    $res = sql_query($query1) or sqlerr(__FILE__, __LINE__);
}
if ($count) {
    $htmlout .= $count > $torrentsperpage ? $pager['pagertop'] : '';
    $htmlout .= bookmarktable($res, 'index');
    $htmlout .= $count > $torrentsperpage ? $pager['pagerbottom'] : '';
}
echo stdhead($lang['bookmarks_stdhead']) . wrapper($htmlout) . stdfoot($stdfoot);
