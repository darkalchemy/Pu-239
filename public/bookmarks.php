<?php

require_once dirname(__FILE__, 2).DIRECTORY_SEPARATOR.'include'.DIRECTORY_SEPARATOR.'bittorrent.php';
require_once INCL_DIR.'user_functions.php';
require_once INCL_DIR.'html_functions.php';
require_once INCL_DIR.'torrenttable_functions.php';
require_once INCL_DIR.'pager_functions.php';
check_user_status();
global $CURUSER;

$lang = array_merge(load_language('global'), load_language('torrenttable_functions'), load_language('bookmark'));
$htmlout = '';
/**
 * @param        $res
 * @param string $variant
 *
 * @return string
 */
function bookmarktable($res, $variant = 'index')
{
    global $site_config, $CURUSER, $lang;
    $htmlout = "
    <span>
        {$lang['bookmarks_icon']}
        <img src='{$site_config['pic_baseurl']}aff_cross.gif' alt='{$lang['bookmarks_del']}' border='none' />{$lang['bookmarks_del1']}
        <img src='{$site_config['pic_baseurl']}zip.gif' alt='{$lang['bookmarks_down']}' border='none' />{$lang['bookmarks_down1']}
        <img alt='{$lang['bookmarks_private']}' src='{$site_config['pic_baseurl']}key.gif' border='none'  /> {$lang['bookmarks_private1']}
        <img src='{$site_config['pic_baseurl']}public.gif' alt='{$lang['bookmarks_public']}' border='none'  />{$lang['bookmarks_public1']}
    </span>
    <div class='table-wrapper'>
        <div class='container is-fluid portlet'>
            <table class='table table-bordered table-striped top20 bottom20''>
                <thead>
                    <tr>
                        <th class='has-text-centered'>{$lang['torrenttable_type']}</th>
                        <th class='has-text-left'>{$lang['torrenttable_name']}</th>";
    $htmlout .= ('index' == $variant ? '
                        <th class="has-text-centered">'.$lang['bookmarks_del2'].'</th>
                        <th class="has-text-right">' : '').''.$lang['bookmarks_down2'].'</th>
                        <th class="has-text-right">'.$lang['bookmarks_share'].'</th>';
    if ('mytorrents' == $variant) {
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
    if ('index' == $variant) {
        $htmlout .= "
                        <th class='has-text-centered'>{$lang['torrenttable_uppedby']}</th>";
    }
    $htmlout .= '
                    </tr>
                </thead>
                <tbody>';
    $categories = genrelist();
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
            $htmlout .= '<a href="'.$site_config['baseurl'].'/browse.php?cat='.(int) $row['category'].'">';
            if (isset($row['cat_pic']) && '' != $row['cat_pic']) {
                $htmlout .= "<img src='{$site_config['pic_baseurl']}caticons/".get_category_icons().'/'.htmlsafechars($row['cat_pic'])."' alt='".htmlsafechars($row['cat_name'])."' class='tooltipper' title='".htmlsafechars($row['cat_name'])."' />";
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
        if ('mytorrents' == $variant) {
            $htmlout .= 'returnto='.urlencode($_SERVER['REQUEST_URI']).'&amp;';
        }
        $htmlout .= "id=$id";
        if ('index' == $variant) {
            $htmlout .= '&amp;hit=1';
        }
        $htmlout .= "'><b>$dispname</b></a>&#160;
                        </td>";
        $htmlout .= ('index' == $variant ? "
                        <td class='has-text-centered'>
                            <a href='{$site_config['baseurl']}/bookmark.php?torrent={$id}&amp;action=delete'>
                                <img src='{$site_config['pic_baseurl']}aff_cross.gif' alt='{$lang['bookmarks_del3']}' class='tooltipper' title='{$lang['bookmarks_del3']}' />
                            </a>
                        </td>" : '');
        $htmlout .= ('index' == $variant ? "
                        <td class='has-text-centered'>
                            <a href='{$site_config['baseurl']}/download.php?torrent={$id}'>
                                <img src='{$site_config['pic_baseurl']}zip.gif' alt='{$lang['bookmarks_down3']}' class='tooltipper' title='{$lang['bookmarks_down3']}' />
                            </a>
                        </td>" : '');
        $bm = sql_query('SELECT * FROM bookmarks WHERE torrentid='.sqlesc($id).' && userid='.sqlesc($CURUSER['id']));
        $bms = mysqli_fetch_assoc($bm);
        if ('yes' == $bms['private'] && $bms['userid'] == $CURUSER['id']) {
            $makepriv = "<a href='{$site_config['baseurl']}/bookmark.php?torrent={$id}&amp;action=public'>
                                <img src='{$site_config['pic_baseurl']}key.gif' alt='{$lang['bookmarks_public2']}' class='tooltipper' title='{$lang['bookmarks_public2']}' />
                            </a>";
            $htmlout .= ''.('index' == $variant ? "
                        <td class='has-text-centered'>
                            {$makepriv}
                        </td>" : '');
        } elseif ('no' == $bms['private'] && $bms['userid'] == $CURUSER['id']) {
            $makepriv = "<a href='{$site_config['baseurl']}/bookmark.php?torrent=".$id."&amp;action=private'>
                                <img src='{$site_config['pic_baseurl']}public.gif' alt='{$lang['bookmarks_private2']}' class='tooltipper' title='{$lang['bookmarks_private2']}' />
                            </a>";
            $htmlout .= ''.('index' == $variant ? "
                        <td class='has-text-centered'>
                            {$makepriv}
                        </td>" : '');
        }
        if ('mytorrents' == $variant) {
            $htmlout .= "
                        </td>
                        <td class='has-text-centered'>
                            <a href='{$site_config['baseurl']}/edit.php?returnto=".urlencode($_SERVER['REQUEST_URI']).'&amp;id='.(int) $row['id']."'>{$lang['torrenttable_edit']}</a>";
        }
        if ('mytorrents' == $variant) {
            $htmlout .= "
                        <td class='has-text-right'>";
            if ('no' == $row['visible']) {
                $htmlout .= '<b>'.$lang['torrenttable_not_visible'].'</b>';
            } else {
                $htmlout .= ''.$lang['torrenttable_visible'].'';
            }
            $htmlout .= '
                        </td>';
        }
        if ('index' == $variant) {
            $htmlout .= "
                        <td class='has-text-right'><b><a href='{$site_config['baseurl']}/filelist.php?id=$id'>".(int) $row['numfiles'].'</a></b></td>';
        } else {
            $htmlout .= "
                        <td class='has-text-right'><b><a href='{$site_config['baseurl']}/filelist.php?id=$id'>".(int) $row['numfiles'].'</a></b></td>';
        }
        if (!$row['comments']) {
            $htmlout .= "
                        <td class='has-text-right'>".(int) $row['comments'].'</td>';
        } else {
            if ('index' == $variant) {
                $htmlout .= "
                        <td class='has-text-right'><b><a href='{$site_config['baseurl']}/details.php?id=$id&amp;hit=1&amp;tocomm=1'>".(int) $row['comments'].'</a></b></td>';
            } else {
                $htmlout .= "
                        <td class='has-text-right'><b><a href='{$site_config['baseurl']}/details.php?id=$id&amp;page=0#startcomments'>".(int) $row['comments'].'</a></b></td>';
            }
        }
        $htmlout .= "
                        <td class='has-text-centered'><span>".str_replace(',', '<br>', get_date($row['added'], ''))."</span></td>
                        <td class='has-text-centered'>".str_replace(' ', '<br>', mksize($row['size'])).'</td>';
        if (1 != $row['times_completed']) {
            $_s = ''.$lang['torrenttable_time_plural'].'';
        } else {
            $_s = ''.$lang['torrenttable_time_singular'].'';
        }
        $htmlout .= "
                        <td class='has-text-centered'><a href='{$site_config['baseurl']}/snatches.php?id=$id'>".number_format($row['times_completed'])."<br>$_s</a></td>";
        if ((int) $row['seeders']) {
            if ('index' == $variant) {
                if ($row['leechers']) {
                    $ratio = (int) $row['seeders'] / (int) $row['leechers'];
                } else {
                    $ratio = 1;
                }
                $htmlout .= "
                        <td class='has-text-right'><b><a href='{$site_config['baseurl']}/peerlist.php?id=$id#seeders'><span style='color: ".get_slr_color($ratio).";'>".(int) $row['seeders'].'</span></a></b></td>';
            } else {
                $htmlout .= "
                        <td class='has-text-right'><b><a class='".linkcolor($row['seeders'])."' href='{$site_config['baseurl']}/peerlist.php?id=$id#seeders'>".(int) $row['seeders'].'</a></b></td>';
            }
        } else {
            $htmlout .= "
                        <td class='has-text-right'><span class='".linkcolor($row['seeders'])."'>".(int) $row['seeders'].'</span></td>';
        }
        if ((int) $row['leechers']) {
            if ('index' == $variant) {
                $htmlout .= "
                        <td class='has-text-right'><b><a href='{$site_config['baseurl']}/peerlist.php?id=$id#leechers'>".number_format($row['leechers']).'</a></b></td>';
            } else {
                $htmlout .= "
                        <td class='has-text-right'><b><a class='".linkcolor($row['leechers'])."' href='{$site_config['baseurl']}/peerlist.php?id=$id#leechers'>".(int) $row['leechers'].'</a></b></td>';
            }
        } else {
            $htmlout .= "
                        <td class='has-text-right'>0</td>";
        }
        if ('index' == $variant) {
            $htmlout .= "
                        <td class='has-text-centered'>".(isset($row['owner']) ? format_username($row['owner']) : '<i>('.$lang['torrenttable_unknown_uploader'].')</i>').'</td>';
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

//==Bookmarks
$userid = isset($_GET['id']) ? (int) $_GET['id'] : $CURUSER['id'];
if (!is_valid_id($userid)) {
    stderr($lang['bookmarks_err'], $lang['bookmark_invalidid']);
}
if ($userid != $CURUSER['id']) {
    stderr($lang['bookmarks_err'], "{$lang['bookmarks_denied']}<a href='{$site_config['baseurl']}/sharemarks.php?id={$userid}'>{$lang['bookmarks_here']}</a>");
}
$res = sql_query('SELECT id, username FROM users WHERE id = '.sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
$arr = mysqli_fetch_array($res);
$htmlout .= '
    <div class="has-text-centered bottom20">
        <h1>'.$lang['bookmarks_my'].'</h1>
        <div class="tabs is-centered">
            <ul>
                <li><a href="'.$site_config['baseurl'].'/sharemarks.php?id='.$CURUSER['id'].'" class="altlink">'.$lang['bookmarks_my_share'].'</a></li>
            </ul>
        </div>
    </div>';

$res = sql_query('SELECT COUNT(id) FROM bookmarks WHERE userid = '.sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
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
                WHERE b.userid ='.sqlesc($userid)."
                ORDER BY t.id DESC {$pager['limit']}" or sqlerr(__FILE__, __LINE__);
    $res = sql_query($query1) or sqlerr(__FILE__, __LINE__);
}
if ($count) {
    $htmlout .= $pager['pagertop'];
    $htmlout .= bookmarktable($res, 'index');
    $htmlout .= $pager['pagerbottom'];
}
echo stdhead($lang['bookmarks_stdhead']).wrapper($htmlout).stdfoot();
