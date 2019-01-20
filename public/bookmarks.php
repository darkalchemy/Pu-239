<?php

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_html.php';
require_once INCL_DIR . 'function_torrenttable.php';
require_once INCL_DIR . 'function_pager.php';
check_user_status();
global $CURUSER, $fluent;

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
function bookmarktable($res, $userid, $variant = 'index')
{
    global $site_config, $lang, $session, $fluent;

    $htmlout = "
    <div class='has-text-centered bottom20'>
        {$lang['bookmarks_icon']}
        <i class='icon-bookmark-empty icon has-text-danger'></i>{$lang['bookmarks_del1']}
        <i class='icon-download icon'></i>{$lang['bookmarks_down1']}
        <i class='icon-key icon has-text-success'></i>{$lang['bookmarks_private1']}
        <i class='icon-users icon has-text-danger'></i>{$lang['bookmarks_public1']}
    </div>";

    $heading = "
                    <tr>
                        <th>{$lang['torrenttable_type']}</th>
                        <th class='has-text-left'>{$lang['torrenttable_name']}</th>";
    $heading .= ($variant === 'index' ? '
                        <th>' . $lang['bookmarks_del2'] . '</th>
                        <th>' : '') . '' . $lang['bookmarks_down2'] . '</th>
                        <th>' . $lang['bookmarks_share'] . '</th>';
    if ($variant === 'mytorrents') {
        $heading .= "
                        <th>{$lang['torrenttable_edit']}</th>
                        <th>{$lang['torrenttable_visible']}</th>";
    }
    $heading .= "
                        <th>{$lang['torrenttable_files']}</th>
                        <th>{$lang['torrenttable_comments']}</th>
                        <th>{$lang['torrenttable_added']}</th>
                        <th>{$lang['torrenttable_size']}</th>
                        <th>{$lang['torrenttable_snatched']}</th>
                        <th>{$lang['torrenttable_seeders']}</th>
                        <th>{$lang['torrenttable_leechers']}</th>";
    if ($variant === 'index') {
        $heading .= "
                        <th>{$lang['torrenttable_uppedby']}</th>";
    }
    $heading .= '
                    </tr>';
    $body = '';
    $categories = genrelist(false);
    $change = [];
    foreach ($categories as $key => $value) {
        $change[$value['id']] = [
            'id' => $value['id'],
            'name' => $value['name'],
            'image' => $value['image'],
        ];
    }
    foreach ($res as $row) {
        $row['cat_name'] = htmlsafechars($change[$row['category']]['name']);
        $row['cat_pic'] = htmlsafechars($change[$row['category']]['image']);
        $id = (int) $row['id'];
        $body .= "
                    <tr>
                        <td class='has-text-centered'>";
        if (isset($row['cat_name'])) {
            $body .= '<a href="' . $site_config['baseurl'] . '/browse.php?cat=' . (int) $row['category'] . '">';
            if (isset($row['cat_pic']) && $row['cat_pic'] != '') {
                $body .= "<img src='{$site_config['pic_baseurl']}caticons/" . get_category_icons() . '/' . htmlsafechars($row['cat_pic']) . "' alt='" . htmlsafechars($row['cat_name']) . "' class='tooltipper' title='" . htmlsafechars($row['cat_name']) . "'>";
            } else {
                $body .= htmlsafechars($row['cat_name']);
            }
            $body .= '</a>';
        } else {
            $body .= '-';
        }
        $body .= '
                        </td>';
        $dispname = htmlsafechars($row['name']);
        $body .= "
                        <td class='has-text-left'>
                            <a href='{$site_config['baseurl']}/details.php?";
        if ($variant === 'mytorrents') {
            $body .= 'returnto=' . urlencode($_SERVER['REQUEST_URI']) . '&amp;';
        }
        $body .= "id=$id";
        if ($variant === 'index') {
            $body .= '&amp;hit=1';
        }
        $body .= "'><b>$dispname</b></a>&#160;
                        </td>";
        $body .= ($variant === 'index' ? "
                        <td class='has-text-centered'>
                            <span data-tid='{$id}' data-csrf='" . $session->get('csrf_token') . "' data-remove='true' data-private='false' class='bookmarks tooltipper' title='{$lang['bookmarks_del3']}'>
                                <i class='icon-bookmark-empty icon has-text-danger'></i>
                            </span>
                        </td>" : '');
        $body .= ($variant === 'index' ? "
                        <td class='has-text-centered'>
                            <a href='{$site_config['baseurl']}/download.php?torrent={$id}' class='tooltipper' title='{$lang['bookmarks_down3']}'>
                                <i class='icon-download icon'></i>
                            </a>
                        </td>" : '');
        $bms = $fluent->from('bookmarks')
            ->where('torrentid = ?', $id)
            ->where('userid = ?', $userid)
            ->fetch();
        if ($bms['private'] === 'yes') {
            $body .= ($variant === 'index' ? "
                        <td class='has-text-centered'>
                            <span data-tid='{$id}' data-csrf='" . $session->get('csrf_token') . "' data-remove='false' data-private='true' class='bookmarks tooltipper' title='{$lang['bookmarks_public2']}'>
                                <i class='icon-key icon has-text-success'></i>
                            </span>
                        </td>" : '');
        } elseif ($bms['private'] === 'no') {
            $body .= ($variant === 'index' ? "
                        <td class='has-text-centered'>
                            <span data-tid='{$id}' data-csrf='" . $session->get('csrf_token') . "' data-remove='false' data-private='true' class='bookmarks tooltipper' title='{$lang['bookmarks_private2']}'>
                                <i class='icon-users icon has-text-danger'></i>
                            </span>
                        </td>" : '');
        }
        if ($variant === 'mytorrents') {
            $body .= "
                        </td>
                        <td class='has-text-centered'>
                            <a href='{$site_config['baseurl']}/edit.php?returnto=" . urlencode($_SERVER['REQUEST_URI']) . '&amp;id=' . (int) $row['id'] . "'>{$lang['torrenttable_edit']}</a>";
        }
        if ($variant === 'mytorrents') {
            $body .= "
                        <td class='has-text-right'>";
            if ($row['visible'] === 'no') {
                $body .= '<b>' . $lang['torrenttable_not_visible'] . '</b>';
            } else {
                $body .= '' . $lang['torrenttable_visible'] . '';
            }
            $body .= '
                        </td>';
        }
        if ($variant === 'index') {
            $body .= "
                        <td class='has-text-right'><b><a href='{$site_config['baseurl']}/filelist.php?id=$id'>" . (int) $row['numfiles'] . '</a></b></td>';
        } else {
            $body .= "
                        <td class='has-text-right'><b><a href='{$site_config['baseurl']}/filelist.php?id=$id'>" . (int) $row['numfiles'] . '</a></b></td>';
        }
        if (!$row['comments']) {
            $body .= "
                        <td class='has-text-right'>" . (int) $row['comments'] . '</td>';
        } else {
            if ($variant === 'index') {
                $body .= "
                        <td class='has-text-right'><b><a href='{$site_config['baseurl']}/details.php?id=$id&amp;hit=1&amp;tocomm=1'>" . (int) $row['comments'] . '</a></b></td>';
            } else {
                $body .= "
                        <td class='has-text-right'><b><a href='{$site_config['baseurl']}/details.php?id=$id&amp;page=0#startcomments'>" . (int) $row['comments'] . '</a></b></td>';
            }
        }
        $body .= "
                        <td class='has-text-centered'><span>" . str_replace(',', '<br>', get_date($row['added'], '')) . "</span></td>
                        <td class='has-text-centered'>" . str_replace(' ', '<br>', mksize($row['size'])) . '</td>';
        if ($row['times_completed'] != 1) {
            $_s = '' . $lang['torrenttable_time_plural'] . '';
        } else {
            $_s = '' . $lang['torrenttable_time_singular'] . '';
        }
        $body .= "
                        <td class='has-text-centered'><a href='{$site_config['baseurl']}/snatches.php?id=$id'>" . number_format($row['times_completed']) . "<br>$_s</a></td>";
        if ((int) $row['seeders']) {
            if ($variant === 'index') {
                if ($row['leechers']) {
                    $ratio = (int) $row['seeders'] / (int) $row['leechers'];
                } else {
                    $ratio = 1;
                }
                $body .= "
                        <td class='has-text-right'><b><a href='{$site_config['baseurl']}/peerlist.php?id=$id#seeders'><span style='color: " . get_slr_color($ratio) . ";'>" . (int) $row['seeders'] . '</span></a></b></td>';
            } else {
                $body .= "
                        <td class='has-text-right'><b><a class='" . linkcolor($row['seeders']) . "' href='{$site_config['baseurl']}/peerlist.php?id=$id#seeders'>" . (int) $row['seeders'] . '</a></b></td>';
            }
        } else {
            $body .= "
                        <td class='has-text-right'><span class='" . linkcolor($row['seeders']) . "'>" . (int) $row['seeders'] . '</span></td>';
        }
        if ((int) $row['leechers']) {
            if ($variant === 'index') {
                $body .= "
                        <td class='has-text-right'><b><a href='{$site_config['baseurl']}/peerlist.php?id=$id#leechers'>" . number_format($row['leechers']) . '</a></b></td>';
            } else {
                $body .= "
                        <td class='has-text-right'><b><a class='" . linkcolor($row['leechers']) . "' href='{$site_config['baseurl']}/peerlist.php?id=$id#leechers'>" . (int) $row['leechers'] . '</a></b></td>';
            }
        } else {
            $body .= "
                        <td class='has-text-right'>0</td>";
        }
        if ($variant === 'index') {
            $body .= "
                        <td class='has-text-centered'>" . (isset($row['owner']) ? format_username($row['owner']) : '<i>(' . $lang['torrenttable_unknown_uploader'] . ')</i>') . '</td>';
        }
        $body .= '
                    </tr>';
    }
    $htmlout .= main_table($body, $heading);

    return $htmlout;
}

$userid = isset($_GET['id']) ? (int) $_GET['id'] : $CURUSER['id'];
if (!is_valid_id($userid)) {
    stderr($lang['bookmarks_err'], $lang['bookmark_invalidid']);
}
if ($userid != $CURUSER['id']) {
    stderr($lang['bookmarks_err'], "{$lang['bookmarks_denied']}<a href='{$site_config['baseurl']}/sharemarks.php?id={$userid}'>{$lang['bookmarks_here']}</a>");
}
$htmlout .= '
    <div class="has-text-centered bottom20">
        <h1>' . $lang['bookmarks_my'] . '</h1>
        <div class="tabs is-centered">
            <ul>
                <li><a href="' . $site_config['baseurl'] . '/sharemarks.php?id=' . $userid . '" class="altlink">' . $lang['bookmarks_my_share'] . '</a></li>
            </ul>
        </div>
    </div>';

$count = $fluent->from('bookmarks')
    ->select(null)
    ->select('COUNT(*) AS count')
    ->where('userid = ?', $userid)
    ->fetch('count');
$torrentsperpage = $CURUSER['torrentsperpage'];
if (empty($torrentsperpage)) {
    $torrentsperpage = 25;
}

if ($count) {
    $pager = pager($torrentsperpage, $count, 'bookmarks.php?&amp;');
    $bookmarks = $fluent->from('bookmarks AS b')
        ->select(null)
        ->select('b.id as bookmarkid')
        ->select('t.owner')
        ->select('t.id')
        ->select('t.name')
        ->select('t.comments')
        ->select('t.leechers')
        ->select('t.seeders')
        ->select('t.save_as')
        ->select('t.numfiles')
        ->select('t.added')
        ->select('t.filename')
        ->select('t.size')
        ->select('t.views')
        ->select('t.visible')
        ->select('t.hits')
        ->select('t.times_completed')
        ->select('t.category')
        ->innerJoin('torrents AS t ON b.torrentid = t.id')
        ->where('b.userid = ?', $userid)
        ->orderBy('t.id DESC')
        ->limit($pager['pdo'])
        ->fetchAll();

    $htmlout .= $count > $torrentsperpage ? $pager['pagertop'] : '';
    $htmlout .= bookmarktable($bookmarks, $userid, 'index');
    $htmlout .= $count > $torrentsperpage ? $pager['pagerbottom'] : '';
}
echo stdhead($lang['bookmarks_stdhead']) . wrapper($htmlout) . stdfoot($stdfoot);
