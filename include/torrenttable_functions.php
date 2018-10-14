<?php

/**
 * @param $num
 *
 * @return string
 */
function linkcolor($num)
{
    if (!$num) {
        return 'red';
    }

    return 'pink';
}

/**
 * @param $text
 * @param $char
 * @param $link
 *
 * @return mixed|string
 */
function readMore($text, $char, $link)
{
    return strlen($text) > $char ? '<p>' . substr(htmlsafechars($text), 0, $char - 1) . "...</p><br><p><a href='$link' class='has-text-primary'>Read more...</a></p>" : htmlsafechars($text);
}

/**
 * @param        $res
 * @param string $variant
 *
 * @return string
 */
function torrenttable($res, $variant = 'index')
{
    $htmlout = $prevdate = $nuked = $free_slot = $free_color = $slots_check = $double_slot = $private = '';
    $Subs = $subs = '';
    $link1 = $link2 = $link3 = $link4 = $link5 = $link6 = $link7 = $link8 = $link9 = '';
    $oldlink = [];

    global $site_config, $CURUSER, $lang, $free, $session;
    include_once INCL_DIR . 'bbcode_functions.php';
    include_once CLASS_DIR . 'class_user_options_2.php';
    include_once CACHE_DIR . 'subs.php';

    foreach ($free as $fl) {
        switch ($fl['modifier']) {
            case 1:
                $free_display = '[Free]';
                break;

            case 2:
                $free_display = '[Double]';
                break;

            case 3:
                $free_display = '[Free and Double]';
                break;

            case 4:
                $free_display = '[Silver]';
                break;
        }
        $slot = make_freeslots($CURUSER['id'], 'fllslot_');
        $book = make_bookmarks($CURUSER['id'], 'bookmm_');
        $all_free_tag = ($fl['modifier'] != 0 && ($fl['expires'] > TIME_NOW || $fl['expires'] == 1) ? ' <a class="info" href="#">
            <b>' . $free_display . '</b>
            <span>' . ($fl['expires'] != 1 ? '
            Expires: ' . get_date($fl['expires'], 'DATE') . '<br>
            (' . mkprettytime($fl['expires'] - TIME_NOW) . ' to go)</span></a><br>' : 'Unlimited</span></a><br>') : '');
    }
    foreach ($_GET as $key => $var) {
        if (in_array($key, [
            'sort',
            'type',
        ])) {
            continue;
        }
        if (is_array($var)) {
            foreach ($var as $s_var) {
                $oldlink[] = sprintf('%s=%s', urlencode($key) . '%5B%5D', urlencode($s_var));
            }
        } else {
            $oldlink[] = sprintf('%s=%s', urlencode($key), urlencode($var));
        }
    }
    $oldlink = !empty($oldlink) ? implode('&amp;', array_map('htmlsafechars', $oldlink)) . '&amp;' : '';
    $links = [
        'link1',
        'link2',
        'link3',
        'link4',
        'link5',
        'link6',
        'link7',
        'link8',
        'link9',
    ];
    $i = 1;
    foreach ($links as $link) {
        if (isset($_GET['sort']) && $_GET['sort'] == $i) {
            $$link = (isset($_GET['type']) && $_GET['type'] === 'desc') ? 'asc' : 'desc';
        } else {
            $$link = 'asc';
        }
        ++$i;
    }
    $htmlout .= "
    <div class='table-wrapper'>
        <table class='table table-bordered table-striped'>
            <thead>
                <tr>
                    <th class='has-text-centered w-1 tooltipper' title='{$lang['torrenttable_type']}'>{$lang['torrenttable_type']}</th>
                    <th class='has-text-centered w-50 tooltipper' title='{$lang['torrenttable_name']}'><a href='{$_SERVER['PHP_SELF']}?{$oldlink}sort=1&amp;type={$link1}'>{$lang['torrenttable_name']}</a></th>
                    <th class='has-text-centered w-1 tooltipper' title='Download'><img src='{$site_config['pic_baseurl']}zip.gif' alt='Download'></th>";
    $htmlout .= ($variant === 'index' ? "
                    <th class='has-text-centered tooltipper' title='Go To My Bookmarks'>
                        <a href='{$site_config['baseurl']}/bookmarks.php'>
                            <i class='icon-ok icon'></i>
                        </a>
                    </th>" : '');
    if ($variant === 'mytorrents') {
        $htmlout .= "
                    <th class='has-text-centered w-1 tooltipper' title='{$lang['torrenttable_edit']}'>{$lang['torrenttable_edit']}</th>
                    <th class='has-text-centered w-1 tooltipper' title='{$lang['torrenttable_visible']}'>{$lang['torrenttable_visible']}</th>";
    }
    $htmlout .= "
                    <th class='has-text-centered w-1 tooltipper' title='{$lang['torrenttable_files']}'><a href='{$_SERVER['PHP_SELF']}?{$oldlink}sort=2&amp;type={$link2}'>{$lang['torrenttable_files']}</a></th>
                    <th class='has-text-centered w-1 tooltipper' title='{$lang['torrenttable_comments']}'><a href='{$_SERVER['PHP_SELF']}?{$oldlink}sort=3&amp;type={$link3}'>C</a></th>
                    <th class='has-text-centered w-1 tooltipper' title='{$lang['torrenttable_added']}'><a href='{$_SERVER['PHP_SELF']}?{$oldlink}sort=4&amp;type={$link4}'>{$lang['torrenttable_added']}</a></th>
                    <th class='has-text-centered w-1 tooltipper' title='{$lang['torrenttable_size']}'><a href='{$_SERVER['PHP_SELF']}?{$oldlink}sort=5&amp;type={$link5}'>{$lang['torrenttable_size']}</a></th>
                    <th class='has-text-centered w-1 tooltipper' title='{$lang['torrenttable_snatched']}'><a href='{$_SERVER['PHP_SELF']}?{$oldlink}sort=6&amp;type={$link6}'>{$lang['torrenttable_snatched']}</a></th>
                    <th class='has-text-centered w-1 tooltipper' title='{$lang['torrenttable_seeders']}'><a href='{$_SERVER['PHP_SELF']}?{$oldlink}sort=7&amp;type={$link7}'>S</a></th>
                    <th class='has-text-centered w-1 tooltipper' title='{$lang['torrenttable_leechers']}'><a href='{$_SERVER['PHP_SELF']}?{$oldlink}sort=8&amp;type={$link8}'>L</a></th>";
    if ($variant === 'index') {
        $htmlout .= "
                    <th class='has-text-centered w-1 tooltipper' title='{$lang['torrenttable_uppedby']}'><a href='{$_SERVER['PHP_SELF']}?{$oldlink}sort=9&amp;type={$link9}'>{$lang['torrenttable_uppedby']}</a></th>";
    }
    if ($CURUSER['class'] >= UC_STAFF) {
        $htmlout .= "
                    <th class='has-text-success w-1 tooltipper' title='Tools'>Tools</th>";
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
        if ($CURUSER['opt2'] & user_options_2::SPLIT) {
            if (get_date($row['added'], 'DATE') == $prevdate) {
                $cleandate = '';
            } else {
                $htmlout .= "
            <tr>
                <td colspan='12' class='colhead has-text-left'><b>{$lang['torrenttable_upped']} " . get_date($row['added'], 'DATE') . '</b></td>
            </tr>';
            }
            $prevdate = get_date($row['added'], 'DATE');
        }
        $row['cat_name'] = htmlsafechars($change[$row['category']]['name']);
        $row['cat_pic'] = htmlsafechars($change[$row['category']]['image']);
        /** Freeslot/doubleslot in Use **/
        $id = (int) $row['id'];
        if (!empty($slot)) {
            foreach ($slot as $sl) {
                $slots_check = ($sl['torrentid'] == $id && $sl['free'] === 'yes' || $sl['doubleup'] === 'yes');
            }
        }
        $htmlout .= "
                    <tr>
                    <td class='has-text-centered'>";
        if (isset($row['cat_name'])) {
            $htmlout .= "<a href='{$site_config['baseurl']}/browse.php?cat=" . (int) $row['category'] . "'>";
            if (isset($row['cat_pic']) && $row['cat_pic'] != '') {
                $htmlout .= "<img src='{$site_config['pic_baseurl']}caticons/" . get_category_icons() . "/{$row['cat_pic']}' class='tooltipper' alt='{$row['cat_name']}' title='{$row['cat_name']}'>";
            } else {
                $htmlout .= htmlsafechars($row['cat_name']);
            }
            $htmlout .= '</a>';
        } else {
            $htmlout .= '-';
        }
        $htmlout .= '</td>';
        $dispname = htmlsafechars($row['name']);
        $smalldescr = (!empty($row['description']) ? '<i>[' . htmlsafechars($row['description']) . ']</i>' : '');
        $poster = empty($row['poster']) ? "<img src='{$site_config['pic_baseurl']}noposter.png' class='tooltip-poster' alt='Poster'>" : "<img src='" . url_proxy($row['poster'], true, 150, null) . "' class='tooltip-poster' alt='Poster'>";
        //$rating = empty($row["rating"]) ? "No votes yet":"".ratingpic($row["rating"])."";
        if (!empty($row['descr'])) {
            $descr = str_replace('"', '&quot;', readMore($row['descr'], 500, $site_config['baseurl'] . '/details.php?id=' . (int) $row['id'] . '&amp;hit=1'));
            $descr = preg_replace('/\[img\].*?\[\/img\]\s+/', '', $descr);
            $descr = preg_replace('/\[img=.*?\]\s+/', '', $descr);
        }

        $htmlout .= "<td class='has-text-left'><a href='{$site_config['baseurl']}/details.php?";
        if ($variant === 'mytorrents') {
            $htmlout .= 'returnto=' . urlencode($_SERVER['REQUEST_URI']) . '&amp;';
        }
        $htmlout .= "id=$id";
        if ($variant === 'index') {
            $htmlout .= '&amp;hit=1';
        }

        $icons = [];
        $icons[] = $row['added'] >= $CURUSER['last_browse'] ? "<img src='{$site_config['pic_baseurl']}newb.png' class='tooltipper icon' alt='New!' title='New!'>" : '';
        $icons[] = ($row['sticky'] === 'yes' ? "<img src='{$site_config['pic_baseurl']}sticky.gif' class='tooltipper icon' alt='Sticky' title='Sticky!'>" : '');
        $icons[] = ($row['vip'] == 1 ? "<img src='{$site_config['pic_baseurl']}star.png' class='tooltipper icon' alt='VIP torrent' title='<div class=\"size_5 has-text-centered has-text-success\">VIP</div>This torrent is for VIP user only!'>" : '');
        $icons[] = (!empty($row['youtube']) ? "<a href='" . htmlsafechars($row['youtube']) . "' target='_blank'><img src='{$site_config['pic_baseurl']}youtube.png' class='tooltipper icon' alt='Youtube Trailer' title='Youtube Trailer'></a>" : '');
        $icons[] = ($row['release_group'] === 'scene' ? "<img src='{$site_config['pic_baseurl']}scene.gif' class='tooltipper icon' title='Scene' alt='Scene'>" : ($row['release_group'] === 'p2p' ? " <img src='{$site_config['pic_baseurl']}p2p.gif' class='tooltipper icon' title='P2P' alt='P2P'>" : ''));
        $icons[] = (!empty($row['checked_by_username']) && $CURUSER['class'] >= UC_MIN) ? "<img src='{$site_config['pic_baseurl']}mod.gif' class='tooltipper icon' alt='Checked by " . htmlsafechars($row['checked_by_username']) . "' title='<div class=\"size_5 has-text-primary has-text-centered\">CHECKED</div><span class=\"right10\">By: </span><span>" . htmlsafechars($row['checked_by_username']) . '</span><br><span class="right10">On: </span><span>' . get_date($row['checked_when'], 'DATE') . "</span>'>" : '';
        $icons[] = (XBT_TRACKER && $row['freetorrent'] >= 1 ? "<img src='{$site_config['pic_baseurl']}freedownload.gif' class='tooltipper icon' alt='Free Torrent' title='Free Torrent'>" : '');
        $icons[] = ($row['free'] != 0 ? "<img src='{$site_config['pic_baseurl']}gold.png' class='tooltipper icon' alt='Free Torrent' title='<div class=\"has-text-centered size_5 has-text-success\">FREE Torrent</div><div class=\"has-text-centered\">" . ($row['free'] > 1 ? 'Expires: ' . get_date($row['free'], 'DATE') . '<br>(' . mkprettytime($row['free'] - TIME_NOW) . ' to go)</div>' : '<div class="has-text-centered">Unlimited</div>') . "'>" : '');
        $icons[] = ($row['silver'] != 0 ? "<img src='{$site_config['pic_baseurl']}silver.png' class='tooltipper icon' alt='Silver Torrent' title='<div class=\"has-text-centered size_5 has-text-success\">Silver Torrent</div><div class=\"has-text-centered\">" . ($row['silver'] > 1 ? 'Expires: ' . get_date($row['silver'], 'DATE') . '<br>(' . mkprettytime($row['silver'] - TIME_NOW) . ' to go)</div>' : '<div class="has-text-centered">Unlimited</div>') . "'>" : '');
        $title = "
            <span class='dt-tooltipper-large' data-tooltip-content='#desc_{$row['id']}_tooltip'>
                <img src='{$site_config['pic_baseurl']}forums/mg.gif' class='icon' alt='Preview'>
                <div class='tooltip_templates'>
                    <span id='desc_{$row['id']}_tooltip'>
                        " . format_comment($descr, false, true, false) . '
                    </span>
                </div>
            </span>';

        $icons[] = !empty($row['descr']) ? $title : '';

        if (!empty($slot)) {
            foreach ($slot as $sl) {
                if ($sl['torrentid'] == $id && $sl['free'] === 'yes') {
                    $free_slot = 1;
                }
                if ($sl['torrentid'] == $id && $sl['doubleup'] === 'yes') {
                    $double_slot = 1;
                }
                if ($free_slot && $double_slot) {
                    break;
                }
            }
        }
        $icons[] = ($free_slot == 1 ? '<img src="' . $site_config['pic_baseurl'] . 'freedownload.gif" class="tooltipper icon" alt="Free Slot" title="Free Slot in Use">' : '');
        $icons[] = ($double_slot == 1 ? '<img src="' . $site_config['pic_baseurl'] . 'doubleseed.gif" class="tooltipper icon" alt="Double Upload Slot" title="Double Upload Slot in Use">' : '');
        $icons[] = ($row['nuked'] === 'yes' ? "<img src='{$site_config['pic_baseurl']}nuked.gif' class='tooltipper icon' alt='Nuked'  class='has-text-centered' title='<div class=\"size_5 has-text-centered has-text-danger\">Nuked</div><span class=\"right10\">Reason: </span>" . htmlsafechars($row['nukereason']) . "'>" : '');
        $icons[] = ($row['bump'] === 'yes' ? "<img src='{$site_config['pic_baseurl']}forums/up.gif' class='tooltipper icon' alt='Re-Animated torrent' title='<div class=\"size_5 has-text-centered has-text-success\">Bumped</div><span class=\"has-text-centered\">This torrent was ReAnimated!</span>'>" : '');

        if (!empty($row['newgenre'])) {
            $newgenre = [];
            $row['newgenre'] = explode(',', $row['newgenre']);
            foreach ($row['newgenre'] as $foo) {
                $newgenre[] = "<a href='{$site_config['baseurl']}/browse.php?search=" . strtolower(trim($foo)) . "&amp;searchin=genre'>" . ucfirst(strtolower(trim($foo))) . '</a>';
            }
            if (!empty($newgenre)) {
                $icons[] = '<br>' . implode(', ', $newgenre);
            }
        }

        if (in_array($row['category'], $site_config['movie_cats']) && !empty($row['subs'])) {
            $subs_array = explode(',', $row['subs']);
            require_once CACHE_DIR . 'subs.php';
            foreach ($subs_array as $k => $sid) {
                foreach ($subs as $sub) {
                    if ($sub['id'] == $sid) {
                        $Subs = "<img src='{$sub['pic']}' class='tooltipper icon' width='16px' alt='{$sub['name']}' title='{$sub['name']}'>";
                    }
                }
            }
        } else {
            $Subs = '---';
        }

        $icon_string = implode(' ', array_diff($icons, ['']));
        $htmlout .= "'><span class='dt-tooltipper-large' data-tooltip-content='#torrent_{$row['id']}_tooltip'>
                            <span class='torrent-name'>{$dispname}</span>
                            <div class='tooltip_templates'>
                                <span id='torrent_{$row['id']}_tooltip'>
                                    <div class='is-flex tooltip-torrent'>
                                        <span class='margin10'>
                                            $poster
                                        </span>
                                        <span class='margin10'>
                                            <b class='size_4 right10 has-text-primary'>Name:</b>" . htmlsafechars($dispname) . "<br>
                                            <b class='size_4 right10 has-text-primary'>Added:</b>" . get_date($row['added'], 'DATE', 0, 1) . "<br>
                                            <b class='size_4 right10 has-text-primary'>Size</b>" . mksize(htmlsafechars($row['size'])) . "<br>
                                            <b class='size_4 right10 has-text-primary'>Subtitle:</b> {$Subs}<br>
                                            <b class='size_4 right10 has-text-primary'>Seeders</b>" . (int) $row['seeders'] . "<br>
                                            <b class='size_4 right10 has-text-primary'>Leechers</b>" . (int) $row['leechers'] . "<br>
                                        </span>
                                    </div>
                                </span>
                            </div>
                        </span>
                        </a>
                        <span class='left10'>$icon_string</span>
                        <br>$smalldescr
                </td>";
        if ($variant === 'mytorrents') {
            $htmlout .= "
                <td>
                    <div class='level-center'>
                        <div class='flex-inrow'>
                            <a href='{$site_config['baseurl']}/download.php?torrent={$id}" . (get_scheme() === 'https' ? '&amp;ssl=1' : '') . "' class='flex-item'>
                                <i class='icon-download icon tooltipper' title='Download This Torrent!'></i>
                            </a>
                        </div>
                    </div>
                </td>
                <td>
                    <div class='level-center'>
                        <div class='flex-inrow'>
                            <a href='{$site_config['baseurl']}/edit.php?id=" . (int) $row['id'] . 'amp;returnto=' . urlencode($_SERVER['REQUEST_URI']) . "' class='flex-item'>
                                {$lang['torrenttable_edit']}
                            </a>
                        </div>
                    </div>
                </td>";
        }
        $htmlout .= ($variant === 'index' ? "
                <td class='has-text-centered'>
                    <div class='level-center'>
                        <div class='flex-inrow'>
                            <a href='{$site_config['baseurl']}/download.php?torrent={$id}" . (get_scheme() === 'https' ? '&amp;ssl=1' : '') . "'  class='flex-item'>
                                <i class='icon-download icon tooltipper' title='Download This Torrent!'></i>
                            </a>
                        </div>
                    </div>
                </td>" : '');
        if ($variant === 'mytorrents') {
            $htmlout .= "<td class='has-text-centered'>";
            if ($row['visible'] === 'no') {
                $htmlout .= "<b>{$lang['torrenttable_not_visible']}</b>";
            } else {
                $htmlout .= "{$lang['torrenttable_visible']}";
            }
            $htmlout .= '</td>';
        }
        $booked = '';
        if (!empty($book)) {
            foreach ($book as $bk) {
                if ($bk['torrentid'] == $id) {
                    $booked = 1;
                }
            }
        }
        $rm_status = (!$booked ? ' style="display:none;"' : ' style="display:inline;"');
        $bm_status = ($booked ? ' style="display:none;"' : ' style="display:inline;"');
        $bookmark = "
                    <span id='bookmark{$id}' {$bm_status}>
                        <div class='level-center'>
                            <div class='flex-inrow'>
                                <a href='{$site_config['baseurl']}/bookmark.php?torrent={$id}&amp;action=add' class='flex-item bookmark' name='{$id}'>
                                    <i class='icon-ok icon has-text-success tooltipper' title='Bookmark it!'></i>
                                </a>
                            </div>
                        </div>
                    </span>
                    <span id='remove{$id}' {$rm_status}>
                        <div class='level-center'>
                            <div class='flex-inrow'>
                                <a href='{$site_config['baseurl']}/bookmark.php?torrent={$id}&amp;action=delete' class='flex-item remove' name='{$id}'>
                                    <i class='icon-ok icon has-text-red tooltipper' title='Delete Bookmark!'></i>
                                </a>
                            </div>
                        </div>
                    </span>";
        if ($variant === 'index') {
            $htmlout .= "<td class='has-text-centered'>{$bookmark}</td>";
        }
        if ($variant === 'index') {
            $htmlout .= "<td class='has-text-centered'><b><a href='{$site_config['baseurl']}/filelist.php?id=$id'>" . (int) $row['numfiles'] . '</a></b></td>';
        } else {
            $htmlout .= "<td class='has-text-centered'><b><a href='{$site_config['baseurl']}/filelist.php?id=$id'>" . (int) $row['numfiles'] . '</a></b></td>';
        }
        if (!$row['comments']) {
            $htmlout .= "<td class='has-text-centered'>" . (int) $row['comments'] . '</td>';
        } else {
            if ($variant === 'index') {
                $htmlout .= "<td class='has-text-centered'><b><a href='{$site_config['baseurl']}/details.php?id=$id&amp;hit=1&amp;tocomm=1'>" . (int) $row['comments'] . '</a></b></td>';
            } else {
                $htmlout .= "<td class='has-text-centered'><b><a href='{$site_config['baseurl']}/details.php?id=$id&amp;page=0#startcomments'>" . (int) $row['comments'] . '</a></b></td>';
            }
        }
        $htmlout .= "<td class='has-text-centered'><span style='white-space: nowrap;'>" . str_replace(',', '<br>', get_date($row['added'], '')) . '</span></td>';
        $htmlout .= "<td class='has-text-centered'>" . str_replace(' ', '<br>', mksize($row['size'])) . '</td>';
        if ($row['times_completed'] != 1) {
            $_s = '' . $lang['torrenttable_time_plural'] . '';
        } else {
            $_s = '' . $lang['torrenttable_time_singular'] . '';
        }
        $What_Script_S = (XBT_TRACKER ? "{$site_config['baseurl']}/snatches_xbt.php?id=" : "{$site_config['baseurl']}/snatches.php?id=");
        $htmlout .= "<td class='has-text-centered'><a href='$What_Script_S" . "$id'>" . number_format($row['times_completed']) . "<br>$_s</a></td>";
        if ($row['seeders']) {
            if ($variant === 'index') {
                if ($row['leechers']) {
                    $ratio = $row['seeders'] / $row['leechers'];
                } else {
                    $ratio = 1;
                }
                $What_Script_P = (XBT_TRACKER ? "{$site_config['baseurl']}/peerlist_xbt.php?id=" : "{$site_config['baseurl']}/peerlist.php?id=");
                $htmlout .= "<td class='has-text-centered'><b><a href='$What_Script_P" . "$id#seeders'><span style='color: " . get_slr_color($ratio) . ";'>" . (int) $row['seeders'] . '</span></a></b></td>';
            } else {
                $What_Script_P = (XBT_TRACKER ? "{$site_config['baseurl']}/peerlist_xbt.php?id=" : "{$site_config['baseurl']}/peerlist.php?id=");
                $htmlout .= "<td class='has-text-centered'><b><a class='" . linkcolor($row['seeders']) . "' href='$What_Script_P" . "$id#seeders'>" . (int) $row['seeders'] . '</a></b></td>';
            }
        } else {
            $htmlout .= "<td class='has-text-centered'><span class='" . linkcolor($row['seeders']) . "'>" . (int) $row['seeders'] . '</span></td>';
        }
        if ($row['leechers']) {
            $What_Script_P = (XBT_TRACKER ? "{$site_config['baseurl']}/peerlist_xbt.php?id=" : "{$site_config['baseurl']}/peerlist.php?id=");
            if ($variant === 'index') {
                $htmlout .= "<td class='has-text-centered'><b><a href='$What_Script_P" . "$id#leechers'>" . number_format($row['leechers']) . '</a></b></td>';
            } else {
                $htmlout .= "<td class='has-text-centered'><b><a class='" . linkcolor($row['leechers']) . "' href='$What_Script_P" . "$id#leechers'>" . (int) $row['leechers'] . '</a></b></td>';
            }
        } else {
            $htmlout .= "<td class='has-text-centered'>0</td>";
        }
        if ($variant === 'index') {
            $htmlout .= "<td class='has-text-centered'>" . (isset($row['owner']) ? (($row['anonymous'] === 'yes' && $CURUSER['class'] < UC_STAFF && $row['owner'] != $CURUSER['id']) ? '<i>' . get_anonymous_name() . '</i>' : format_username($row['owner'])) : '<i>(' . $lang['torrenttable_unknown_uploader'] . ')</i>') . '</td>';
        }
        if ($CURUSER['class'] >= UC_STAFF) {
            $returnto = !empty($_SERVER['REQUEST_URI']) ? '&amp;returnto=' . urlencode($_SERVER['REQUEST_URI']) : '';

            $edit_link = "
                <span>
                    <a href='{$site_config['baseurl']}/edit.php?id=" . (int) $row['id'] . "{$returnto}' class='tooltipper' title='Fast Edit'>
                        <i class='icon-edit icon'></i>
                    </a>
                </span>";
            $del_link = ($CURUSER['class'] === UC_MAX ? "
                <span>
                    <a href='{$site_config['baseurl']}/fastdelete.php?id=" . (int) $row['id'] . "{$returnto}' class='tooltipper' title='Fast Delete'>
                        <i class='icon-cancel icon has-text-red'></i>
                    </a>
                </span>" : '');
            $staff_pick = '';
            if ($CURUSER['class'] === UC_MAX && (int) $row['staff_picks'] > 0) {
                $staff_pick = "
                <span data-id='{$row['id']}' data-pick='{$row['staff_picks']}' . data-csrf='" . $session->get('csrf_token') . "' class='staff_pick tooltipper' title='Remove from Staff Picks'>
                    <i class='icon-minus icon has-text-red'></i>
                </span>";
            } elseif ($CURUSER['class'] === UC_MAX) {
                $staff_pick = "
                <span data-id='{$row['id']}' data-pick='{$row['staff_picks']}' . data-csrf='" . $session->get('csrf_token') . "' class='staff_pick tooltipper' title='Add to Staff Picks'>
                    <i class='icon-plus icon has-text-lime'></i>
                </span>";
            }

            $htmlout .= "
                        <td>
                            <div class='level-center'>
                                {$edit_link}
                                {$del_link}
                                {$staff_pick}
                            </div>
                        </td>";
        }
        $htmlout .= '</tr>';
    }
    $htmlout .= '</tbody>
            </table>
        </div>';

    return $htmlout;
}
