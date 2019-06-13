<?php

declare(strict_types = 1);

use Pu239\Bookmark;
use Pu239\Cache;
use Pu239\Image;
use Spatie\Image\Exceptions\InvalidManipulation;

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
 * @throws \Envms\FluentPDO\Exception
 * @throws InvalidManipulation
 * @throws Exception
 *
 * @return string
 */
function torrenttable($res, $variant = 'index')
{
    global $container, $site_config, $CURUSER, $lang;

    $htmlout = $prevdate = $nuked = $free_slot = $free_color = $slots_check = $double_slot = $private = '';
    $link1 = $link2 = $link3 = $link4 = $link5 = $link6 = $link7 = $link8 = $link9 = '';
    $oldlink = [];

    require_once INCL_DIR . 'function_bbcode.php';
    require_once CLASS_DIR . 'class_user_options_2.php';
    require_once INCL_DIR . 'function_torrent_hover.php';
    $lang = array_merge($lang, load_language('index'));
    $cache = $container->get(Cache::class);
    $free = $cache->get('site_events_');
    $free_display = '';
    if (!empty($free)) {
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
            $all_free_tag = ($fl['modifier'] != 0 && ($fl['expires'] > TIME_NOW || $fl['expires'] == 1) ? ' <a class="info" href="#">
            <b>' . $free_display . '</b>
            <span>' . ($fl['expires'] != 1 ? '
            Expires: ' . get_date((int) $fl['expires'], 'DATE') . '<br>
            (' . mkprettytime($fl['expires'] - TIME_NOW) . ' to go)</span></a><br>' : 'Unlimited</span></a><br>') : '');
        }
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
            ${$link} = (isset($_GET['type']) && $_GET['type'] === 'desc') ? 'asc' : 'desc';
        } else {
            ${$link} = 'asc';
        }
        ++$i;
    }
    $htmlout .= "
    <div class='table-wrapper'>
        <table class='table table-bordered table-striped'>
            <thead>
                <tr>
                    <th class='has-text-centered tooltipper' title='{$lang['torrenttable_type']}'>{$lang['torrenttable_type']}</th>
                    <th class='has-text-centered min-350 tooltipper' title='{$lang['torrenttable_name']}'><a href='{$_SERVER['PHP_SELF']}?{$oldlink}sort=1&amp;type={$link1}'>{$lang['torrenttable_name']}</a></th>
                    <th class='has-text-centered tooltipper' title='Download'><i class='icon-download icon' aria-hidden='true'></i></th>";
    $htmlout .= ($variant === 'index' ? "
                    <th class='has-text-centered tooltipper' title='{$lang['bookmark_goto']}'>
                        <a href='{$site_config['paths']['baseurl']}/bookmarks.php'>
                            <i class='icon-bookmark-empty icon' aria-hidden='true'></i>
                        </a>
                    </th>" : '');
    if ($variant === 'mytorrents') {
        $htmlout .= "
                    <th class='has-text-centered tooltipper' title='{$lang['torrenttable_edit']}'>{$lang['torrenttable_edit']}</th>
                    <th class='has-text-centered tooltipper' title='{$lang['torrenttable_visible']}'>{$lang['torrenttable_visible']}</th>";
    }
    $htmlout .= "
                    <th class='has-text-centered tooltipper' title='{$lang['torrenttable_files']}'><a href='{$_SERVER['PHP_SELF']}?{$oldlink}sort=2&amp;type={$link2}'>{$lang['torrenttable_files']}</a></th>
                    <th class='has-text-centered tooltipper' title='{$lang['torrenttable_comments']}'><a href='{$_SERVER['PHP_SELF']}?{$oldlink}sort=3&amp;type={$link3}'>C</a></th>
                    <th class='has-text-centered tooltipper' title='{$lang['torrenttable_added']}'><a href='{$_SERVER['PHP_SELF']}?{$oldlink}sort=4&amp;type={$link4}'>{$lang['torrenttable_added']}</a></th>
                    <th class='has-text-centered tooltipper' title='{$lang['torrenttable_size']}'><a href='{$_SERVER['PHP_SELF']}?{$oldlink}sort=5&amp;type={$link5}'>{$lang['torrenttable_size']}</a></th>
                    <th class='has-text-centered tooltipper' title='{$lang['torrenttable_snatched']}'><a href='{$_SERVER['PHP_SELF']}?{$oldlink}sort=6&amp;type={$link6}'>{$lang['torrenttable_snatched']}</a></th>
                    <th class='has-text-centered tooltipper' title='{$lang['torrenttable_to_go']}'>{$lang['torrenttable_to_go']}</th>
                    <th class='has-text-centered tooltipper' title='{$lang['torrenttable_seeders']}'><a href='{$_SERVER['PHP_SELF']}?{$oldlink}sort=7&amp;type={$link7}'>S</a></th>
                    <th class='has-text-centered tooltipper' title='{$lang['torrenttable_leechers']}'><a href='{$_SERVER['PHP_SELF']}?{$oldlink}sort=8&amp;type={$link8}'>L</a></th>";
    if ($variant === 'index') {
        $htmlout .= "
                    <th class='has-text-centered tooltipper' title='{$lang['torrenttable_uppedby']}'><a href='{$_SERVER['PHP_SELF']}?{$oldlink}sort=9&amp;type={$link9}'>{$lang['torrenttable_uppedby']}</a></th>";
    }
    if ($CURUSER['class'] >= UC_STAFF) {
        $htmlout .= "
                    <th class='has-text-centered has-text-success w-5 tooltipper' title='Tools'>Tools</th>";
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
    $images_class = $container->get(Image::class);
    foreach ($res as $row) {
        if ($CURUSER['opt2'] & user_options_2::SPLIT) {
            if (get_date((int) $row['added'], 'DATE') == $prevdate) {
                $cleandate = '';
            } else {
                $htmlout .= "
            <tr>
                <td colspan='12' class='colhead has-text-left'><b>{$lang['torrenttable_upped']} " . get_date((int) $row['added'], 'DATE') . '</b></td>
            </tr>';
            }
            $prevdate = get_date((int) $row['added'], 'DATE');
        }
        if ($row['to_go'] == -1) {
            $to_go = '<div class="has-text-danger tooltipper" title="Never Snatched">--</div>';
        } elseif ($row['to_go'] == 1) {
            $to_go = "<div class='has-text-success tooltipper' title='Download Complete'>100%</div>";
        } else {
            $to_go = "<div class='is-warning tooltipper' title='Download In Progress'>" . number_format((int) $row['to_go'], 1) . '%</div>';
        }
        $row['cat_name'] = htmlsafechars($change[$row['category']]['name']);
        $row['cat_pic'] = htmlsafechars($change[$row['category']]['image']);
        $id = $row['id'];
        if (!empty($slot)) {
            foreach ($slot as $sl) {
                $slots_check = ($sl['torrentid'] == $id && $sl['free'] === 'yes' || $sl['doubleup'] === 'yes');
            }
        }
        $htmlout .= "
                    <tr>
                    <td class='has-text-centered'>";
        if (isset($row['cat_name'])) {
            $htmlout .= "<a href='{$site_config['paths']['baseurl']}/browse.php?cat=" . $row['category'] . "'>";
            if (isset($row['cat_pic']) && $row['cat_pic'] != '') {
                $htmlout .= "<img src='{$site_config['paths']['images_baseurl']}caticons/" . get_category_icons() . "/{$row['cat_pic']}' class='tooltipper' alt='{$row['cat_name']}' title='{$row['cat_name']}'>";
            } else {
                $htmlout .= htmlsafechars($row['cat_name']);
            }
            $htmlout .= '</a>';
        } else {
            $htmlout .= '-';
        }
        $htmlout .= '</td>';
        $year = !empty($row['year']) ? " ({$row['year']})" : '';
        $dispname = htmlsafechars($row['name']) . $year;
        $staff_pick = $row['staff_picks'] > 0 ? "
            <span id='staff_pick_{$row['id']}'>
                <img src='{$site_config['paths']['images_baseurl']}staff_pick.png' class='tooltipper emoticon is-2x' alt='Staff Pick!' title='Staff Pick!'>
            </span>" : "
            <span id='staff_pick_{$row['id']}'>
            </span>";

        $imdb_info = '';
        if (in_array($row['category'], $site_config['categories']['movie']) || in_array($row['category'], $site_config['categories']['tv'])) {
            $percent = !empty($row['rating']) ? $row['rating'] * 10 : 0;
            $imdb_info = "
                    <div class='star-ratings-css tooltipper' title='{$percent}% of IMDb voters liked this!'>
                        <div class='star-ratings-css-top' style='width: {$percent}%'><span>★</span><span>★</span><span>★</span><span>★</span><span>★</span></div>
                        <div class='star-ratings-css-bottom'><span>★</span><span>★</span><span>★</span><span>★</span><span>★</span></div>
                    </div>";
        }
        $smalldescr = (!empty($row['description']) ? '<div><i>[' . htmlsafechars($row['description']) . ']</i></div>' : '');
        if (empty($row['poster']) && !empty($row['imdb_id'])) {
            $row['poster'] = $images_class->find_images($row['imdb_id']);
        }
        $poster = empty($row['poster']) ? "<img src='{$site_config['paths']['images_baseurl']}noposter.png' class='tooltip-poster' alt='Poster'>" : "<img src='" . url_proxy($row['poster'], true, 250) . "' class='tooltip-poster' alt='Poster'>";
        $user_rating = empty($row['rating_sum']) ? '' : ratingpic($row['rating_sum'] / $row['num_ratings']);
        $descr = '';
        if (!empty($row['descr'])) {
            $descr = str_replace('"', '&quot;', readMore($row['descr'], 500, $site_config['paths']['baseurl'] . '/details.php?id=' . $row['id'] . '&amp;hit=1'));
            $descr = preg_replace('/\[img\].*?\[\/img\]\s+/', '', $descr);
            $descr = preg_replace('/\[img=.*?\]\s+/', '', $descr);
        }

        $htmlout .= "
            <td>
                <div class='level-wide'>
                    <div>
                        <a class='crap is-link' href='{$site_config['paths']['baseurl']}/details.php?";
        if ($variant === 'mytorrents') {
            $htmlout .= 'returnto=' . urlencode($_SERVER['REQUEST_URI']) . '&amp;';
        }
        $htmlout .= "id=$id";
        if ($variant === 'index') {
            $htmlout .= '&amp;hit=1';
        }
        $htmlout .= "'>";
        $icons = $top_icons = [];
        $top_icons[] = $row['added'] >= $CURUSER['last_browse'] ? "<span class='tag is-danger'>New!</span>" : '';
        $icons[] = $row['sticky'] === 'yes' ? "<img src='{$site_config['paths']['images_baseurl']}sticky.gif' class='tooltipper icon' alt='Sticky' title='Sticky!'>" : '';
        $icons[] = $row['vip'] == 1 ? "<img src='{$site_config['paths']['images_baseurl']}star.png' class='tooltipper icon' alt='VIP torrent' title='<div class=\"size_5 has-text-centered has-text-success\">VIP</div>This torrent is for VIP user only!'>" : '';
        $icons[] = !empty($row['youtube']) ? "<a href='" . htmlsafechars($row['youtube']) . "' target='_blank'><i class='icon-youtube icon' aria-hidden='true'></i></a>" : '';
        $icons[] = $row['release_group'] === 'scene' ? "<img src='{$site_config['paths']['images_baseurl']}scene.gif' class='tooltipper icon' title='Scene' alt='Scene'>" : ($row['release_group'] === 'p2p' ? " <img src='{$site_config['paths']['images_baseurl']}p2p.gif' class='tooltipper icon' title='P2P' alt='P2P'>" : '');
        $icons[] = !empty($row['checked_by_username']) && $CURUSER['class'] >= UC_MIN ? "<img src='{$site_config['paths']['images_baseurl']}mod.gif' class='tooltipper icon' alt='Checked by " . htmlsafechars($row['checked_by_username']) . "' title='<div class=\"size_5 has-text-primary has-text-centered\">CHECKED</div><span class=\"right10\">By: </span><span>" . htmlsafechars($row['checked_by_username']) . '</span><br><span class="right10">On: </span><span>' . get_date((int) $row['checked_when'], 'DATE') . "</span>'>" : '';
        $icons[] = $row['free'] != 0 ? "<img src='{$site_config['paths']['images_baseurl']}gold.png' class='tooltipper icon' alt='Free Torrent' title='<div class=\"has-text-centered size_5 has-text-success\">FREE Torrent</div><div class=\"has-text-centered\">" . ($row['free'] > 1 ? 'Expires: ' . get_date((int) $row['free'], 'DATE') . '<br>(' . mkprettytime($row['free'] - TIME_NOW) . ' to go)</div>' : '<div class="has-text-centered">Unlimited</div>') . "'>" : '';
        $icons[] = $row['silver'] != 0 ? "<img src='{$site_config['paths']['images_baseurl']}silver.png' class='tooltipper icon' alt='Silver Torrent' title='<div class=\"has-text-centered size_5 has-text-success\">Silver Torrent</div><div class=\"has-text-centered\">" . ($row['silver'] > 1 ? 'Expires: ' . get_date((int) $row['silver'], 'DATE') . '<br>(' . mkprettytime($row['silver'] - TIME_NOW) . ' to go)</div>' : '<div class="has-text-centered">Unlimited</div>') . "'>" : '';
        $title = "
            <div class='dt-tooltipper-large' data-tooltip-content='#desc_{$row['id']}_tooltip'>
                <i class='icon-search icon' aria-hidden='true'></i>
                <div class='tooltip_templates'>
                    <div id='desc_{$row['id']}_tooltip'>
                        " . format_comment($descr, false, true, false) . '
                    </div>
                </div>
            </div>';

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
        $icons[] = $free_slot == 1 ? '<img src="' . $site_config['paths']['images_baseurl'] . 'freedownload.gif" class="tooltipper icon" alt="Free Slot" title="Free Slot in Use">' : '';
        $icons[] = $double_slot == 1 ? '<img src="' . $site_config['paths']['images_baseurl'] . 'doubleseed.gif" class="tooltipper icon" alt="Double Upload Slot" title="Double Upload Slot in Use">' : '';
        $icons[] = $row['nuked'] === 'yes' ? "<img src='{$site_config['paths']['images_baseurl']}nuked.gif' class='tooltipper icon' alt='Nuked'  class='has-text-centered' title='<div class=\"size_5 has-text-centered has-text-danger\">Nuked</div><span class=\"right10\">Reason: </span>" . htmlsafechars($row['nukereason']) . "'>" : '';
        $icons[] = $row['bump'] === 'yes' ? "<img src='{$site_config['paths']['images_baseurl']}forums/up.gif' class='tooltipper icon' alt='Re-Animated torrent' title='<div class=\"size_5 has-text-centered has-text-success\">Bumped</div><span class=\"has-text-centered\">This torrent was ReAnimated!</span>'>" : '';

        $genres = '';
        if (!empty($row['newgenre'])) {
            $genres = $row['newgenre'];
            $newgenre = [];
            $row['newgenre'] = explode(',', $row['newgenre']);
            foreach ($row['newgenre'] as $foo) {
                $newgenre[] = "<a href='{$site_config['paths']['baseurl']}/browse.php?sg=" . strtolower(trim($foo)) . "'>" . ucfirst(strtolower(trim($foo))) . '</a>';
            }
            if (!empty($newgenre)) {
                $icons[] = implode(', ', $newgenre);
            }
        }
        $icon_string = implode(' ', array_diff($icons, ['']));
        $icon_string = !empty($icon_string) ? "<div class='level-left'>{$icon_string}</div>" : '';
        $top_icons = implode(' ', array_diff($top_icons, ['']));
        $top_icons = !empty($top_icons) ? "<div class='left10'>{$top_icons}</div>" : '';
        $name = $row['name'];
        if (!empty($row['username'])) {
            if ($row['anonymous'] && $CURUSER['class'] < UC_STAFF && $row['owner'] != $CURUSER['id']) {
                $uploader = '<span>' . get_anonymous_name() . '</span>';
                $formatted = "<i>({$uploader})</i>";
            } else {
                $uploader = "<span class='" . get_user_class_name((int) $row['class'], true) . "'>" . htmlsafechars($row['username']) . '</span>';
                $formatted = format_username((int) $row['owner']);
            }
        } else {
            $uploader = $lang['torrenttable_unknown_uploader'];
            $formatted = "<i>({$uploader})</i>";
        }
        $block_id = "torrent_{$id}";
        $tooltip = torrent_tooltip(htmlsafechars($dispname), $id, $block_id, $name, $poster, $uploader, $row['added'], $row['size'], $row['seeders'], $row['leechers'], $row['imdb_id'], $row['rating'], $row['year'], $row['subs'], $genres);
        $subs = $container->get('subtitles');
        $subs_array = explode('|', $row['subs']);
        $Subs = [];
        foreach ($subs_array as $k => $subname) {
            foreach ($subs as $sub) {
                if (strtolower($sub['name']) === strtolower($subname)) {
                    $Subs[] = "<a href='{$site_config['paths']['baseurl']}/browse.php?st=" . htmlsafechars($sub['name']) . "'>
                                <img src='{$site_config['paths']['images_baseurl']}/{$sub['pic']}' class='tooltipper icon' width='16' alt='" . htmlsafechars($sub['name']) . "' title='" . htmlsafechars($sub['name']) . "'>
                               </a>";
                }
            }
        }
        $subtitles = '';
        if (!empty($Subs)) {
            $subtitles = "<span class='left10'>" . implode(' ', $Subs) . '</span>';
        }
        $htmlout .= $tooltip . "
                        </a>{$icon_string}{$imdb_info}{$user_rating}{$smalldescr}
                    </div>
                    <div class='level left10'>
                        {$top_icons}{$staff_pick}{$subtitles}
                    </div>
                </div>
            </td>";
        if ($variant === 'mytorrents') {
            $htmlout .= "
                <td>
                    <div class='level-center'>
                        <div class='flex-inrow'>
                            <a href='{$site_config['paths']['baseurl']}/download.php?torrent={$id}" . (get_scheme() === 'https' ? '&amp;ssl=1' : '') . "' class='flex-item'>
                                <i class='icon-download icon tooltipper' aria-hidden='true' title='Download This Torrent!'></i>
                            </a>
                        </div>
                    </div>
                </td>
                <td>
                    <div class='level-center'>
                        <div class='flex-inrow'>
                            <a href='{$site_config['paths']['baseurl']}/edit.php?id=" . $row['id'] . 'amp;returnto=' . urlencode($_SERVER['REQUEST_URI']) . "' class='flex-item'>
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
                            <a href='{$site_config['paths']['baseurl']}/download.php?torrent={$id}" . (get_scheme() === 'https' ? '&amp;ssl=1' : '') . "'  class='flex-item'>
                                <i class='icon-download icon tooltipper' aria-hidden='true' title='Download This Torrent!'></i>
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
        $bookmark = "
                <span data-tid='{$id}' data-remove='false' data-private='false' class='bookmarks tooltipper' title='{$lang['bookmark_add']}'>
                    <i class='icon-bookmark-empty icon has-text-success' aria-hidden='true'></i>
                </span>";

        $bookmark_class = $container->get(Bookmark::class);
        $book = $bookmark_class->get($CURUSER['id']);
        if (!empty($book)) {
            foreach ($book as $bk) {
                if ($bk['torrentid'] == $id) {
                    $bookmark = "
                    <span data-tid='{$id}' data-remove='false' data-private='false' class='bookmarks tooltipper' title='{$lang['bookmark_delete']}'>
                        <i class='icon-bookmark-empty icon has-text-danger' aria-hidden='true'></i>
                    </span>";
                }
            }
        }
        if ($variant === 'index') {
            $htmlout .= "<td class='has-text-centered'>{$bookmark}</td>";
        }
        if ($variant === 'index') {
            $htmlout .= "<td class='has-text-centered'><b><a href='{$site_config['paths']['baseurl']}/filelist.php?id=$id'>" . $row['numfiles'] . '</a></b></td>';
        } else {
            $htmlout .= "<td class='has-text-centered'><b><a href='{$site_config['paths']['baseurl']}/filelist.php?id=$id'>" . $row['numfiles'] . '</a></b></td>';
        }
        if (!$row['comments']) {
            $htmlout .= "<td class='has-text-centered'>" . $row['comments'] . '</td>';
        } else {
            if ($variant === 'index') {
                $htmlout .= "<td class='has-text-centered'><b><a href='{$site_config['paths']['baseurl']}/details.php?id=$id&amp;hit=1&amp;tocomm=1'>" . $row['comments'] . '</a></b></td>';
            } else {
                $htmlout .= "<td class='has-text-centered'><b><a href='{$site_config['paths']['baseurl']}/details.php?id=$id&amp;page=0#startcomments'>" . $row['comments'] . '</a></b></td>';
            }
        }
        $htmlout .= "<td class='has-text-centered'><span style='white-space: nowrap;'>" . str_replace(',', '<br>', get_date((int) $row['added'], '')) . '</span></td>';
        $htmlout .= "<td class='has-text-centered'>" . str_replace(' ', '<br>', mksize($row['size'])) . '</td>';
        if ($row['times_completed'] != 1) {
            $_s = '' . $lang['torrenttable_time_plural'] . '';
        } else {
            $_s = '' . $lang['torrenttable_time_singular'] . '';
        }
        $What_Script_S = "{$site_config['paths']['baseurl']}/snatches.php?id=";
        $htmlout .= "<td class='has-text-centered'><a href='$What_Script_S" . "$id'>" . number_format($row['times_completed']) . "<br>$_s</a></td>";
        $htmlout .= "<td class='has-text-centered'>$to_go</td>";
        if ($row['seeders']) {
            if ($variant === 'index') {
                if ($row['leechers']) {
                    $ratio = $row['seeders'] / $row['leechers'];
                } else {
                    $ratio = 1;
                }
                $What_Script_P = "{$site_config['paths']['baseurl']}/peerlist.php?id=";
                $htmlout .= "<td class='has-text-centered'><b><a href='$What_Script_P" . "$id#seeders'><span style='color: " . get_slr_color($ratio) . ";'>" . $row['seeders'] . '</span></a></b></td>';
            } else {
                $What_Script_P = "{$site_config['paths']['baseurl']}/peerlist.php?id=";
                $htmlout .= "<td class='has-text-centered'><b><a class='" . linkcolor($row['seeders']) . "' href='$What_Script_P" . "$id#seeders'>" . $row['seeders'] . '</a></b></td>';
            }
        } else {
            $htmlout .= "<td class='has-text-centered'><span class='" . linkcolor($row['seeders']) . "'>" . $row['seeders'] . '</span></td>';
        }
        if ($row['leechers']) {
            $What_Script_P = "{$site_config['paths']['baseurl']}/peerlist.php?id=";
            if ($variant === 'index') {
                $htmlout .= "<td class='has-text-centered'><b><a href='$What_Script_P" . "$id#leechers'>" . number_format($row['leechers']) . '</a></b></td>';
            } else {
                $htmlout .= "<td class='has-text-centered'><b><a class='" . linkcolor($row['leechers']) . "' href='$What_Script_P" . "$id#leechers'>" . $row['leechers'] . '</a></b></td>';
            }
        } else {
            $htmlout .= "<td class='has-text-centered'>0</td>";
        }
        if ($variant === 'index') {
            $htmlout .= "<td class='has-text-centered'>{$formatted}</td>";
        }
        if ($CURUSER['class'] >= UC_STAFF) {
            $returnto = !empty($_SERVER['REQUEST_URI']) ? '&amp;returnto=' . urlencode($_SERVER['REQUEST_URI']) : '';

            $edit_link = ($CURUSER['class'] >= $site_config['allowed']['fast_edit'] ? "
                <span>
                    <a href='{$site_config['paths']['baseurl']}/edit.php?id=" . $row['id'] . "{$returnto}' class='tooltipper' title='Fast Edit'>
                        <i class='icon-edit icon' aria-hidden='true'></i>
                    </a>
                </span>" : '');
            $del_link = ($CURUSER['class'] >= $site_config['allowed']['fast_delete'] ? "
                <span>
                    <a href='{$site_config['paths']['baseurl']}/fastdelete.php?id=" . $row['id'] . "{$returnto}' class='tooltipper' title='Fast Delete'>
                        <i class='icon-trash-empty icon has-text-danger' aria-hidden='true'></i>
                    </a>
                </span>" : '');
            $staff_pick = '';
            if ($CURUSER['class'] >= $site_config['allowed']['staff_picks'] && $row['staff_picks'] > 0) {
                $staff_pick = "
                <span data-id='{$row['id']}' data-pick='{$row['staff_picks']}' class='staff_pick tooltipper' title='Remove from Staff Picks'>
                    <i class='icon-star-empty icon has-text-danger' aria-hidden='true'></i>
                </span>";
            } elseif ($CURUSER['class'] >= $site_config['allowed']['staff_picks']) {
                $staff_pick = "
                <span data-id='{$row['id']}' data-pick='{$row['staff_picks']}' class='staff_pick tooltipper' title='Add to Staff Picks'>
                    <i class='icon-star-empty icon has-text-success' aria-hidden='true'></i>
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
