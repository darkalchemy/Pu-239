<?php

declare(strict_types = 1);

use Delight\Auth\AuthError;
use Delight\Auth\NotLoggedInException;
use DI\DependencyException;
use DI\NotFoundException;
use MatthiasMullie\Scrapbook\Exception\UnbegunTransaction;
use Pu239\Bookmark;
use Pu239\Image;
use Pu239\Session;
use Spatie\Image\Exceptions\InvalidManipulation;

require_once INCL_DIR . 'function_categories.php';
require_once INCL_DIR . 'function_event.php';
$curuser = check_user_status();

/**
 * @param $num
 *
 * @return string
 * @return string
 */
function linkcolor($num)
{
    if (!$num) {
        return 'red';
    }

    return 'pink';
}

function torrenttable(array $torrents, array $curuser)
{
    global $container, $site_config, $curuser;

    $session = $container->get(Session::class);
    $scheme = $session->get('scheme') === 'http' ? '' : '&amp;ssl=1';
    $is_free = get_events_data();
    $sorts = get_sorts();
    $image = placeholder_image(100, 150);

    $heading = "
        <tr>
            <th>
                <div class='level-wide'>
                    $sorts
                </div>
            </th>
        </tr>";
    $body = '';
    foreach ($torrents as $torrent) {
        $lookup = get_lookup();
        $catinfo = get_cat_info($torrent, $lookup);
        $new = $torrent['added'] >= $curuser['last_browse'] ? "<span class='tag is-danger'>" . _('New') . "!</span>" : '';
        $sticky = $torrent['sticky'] === 'yes' ? "<img src='{$site_config['paths']['images_baseurl']}sticky.gif' class='tooltipper icon' alt='" . _('Sticky') . "' title='" . _('Sticky') . "'>" : '';
        $poster = get_poster($torrent, $image);
        $uploader = get_uploader($torrent);
        $uploaded = get_week_day($torrent['added']) . ', ' . get_date($torrent['added'], 'LONG', 1, 0);
        $title = "<a href='{$site_config['paths']['baseurl']}/details.php?id={$torrent['id']}'><span class='is-wrapped'>" . format_comment($torrent['name']) . "</span></a>";
        $ratio = $torrent['leechers'] >= 1 ? $torrent['seeders'] / $torrent['leechers'] : 1;
        $seeders = "<a href='{$site_config['paths']['baseurl']}/peerlist.php?id={$torrent['id']}#seeders' class='tooltipper' title='" . _('Seeders') . "'><span style='color: " . get_slr_color($ratio) . ";'>" . number_format($torrent['seeders']) . '</span></a>';
        $leechers = "<a href='{$site_config['paths']['baseurl']}/peerlist.php?id={$torrent['id']}#leechers' class='tooltipper' title='" . _('Leechers') . "'>" . number_format($torrent['leechers']) . '</a>';
        $snatched = "<a href='{$site_config['paths']['baseurl']}/snatches.php?id={$torrent['id']}' class='tooltipper' title='" . _('Times Completed') . "'>" . number_format($torrent['times_completed']) . '</a>';
        $numfiles = "<a href='{$site_config['paths']['baseurl']}/filelist.php?id={$torrent['id']}'>" . number_format($torrent['numfiles']) . '</a>';
        $comments = "<a href='{$site_config['paths']['baseurl']}/details.php?id={$torrent['id']}&amp;hit=1&amp;tocomm=1' class='tooltipper' title='" . _('Comments') . "'>" . number_format($torrent['comments']) . '</a>';
        $download = "<a href='{$site_config['paths']['baseurl']}/download.php?torrent={$torrent['id']}{$scheme}'><i class='icon-download icon tooltipper' aria-hidden='true' title='" . _('Download this Torrent!') . "'></i></a>";
        $bookmark = get_bookmark($torrent);
        $subtitles = get_subtitles($torrent, $lookup);
        $audios = get_audios($torrent, $lookup);
        $torrent['free'] = $torrent['free'] < $is_free['free'] ? $is_free['free'] : $torrent['free'];
        $torrent['doubletorrent'] = $torrent['doubletorrent'] < $is_free['double'] ? $is_free['double'] : $torrent['doubletorrent'];
        $torrent['silver'] = $torrent['silver'] < $is_free['silver'] ? $is_free['silver'] : $torrent['silver'];
        $icons = get_icons($torrent);
        $genres = get_genres($torrent, $lookup);
        $imdb_rating = get_imdb_rating($torrent);
        $user_rating = empty($torrent['rating_sum']) ? '' : ratingpic($torrent['rating_sum'] / $torrent['num_ratings']);
        $staff_pick = get_staff_picks($torrent);
        $staff_tools = get_tools($torrent);
        $to_go = get_togo($torrent);
        $subs = !empty($subtitles) || !empty($audios) ? "
                        <div class='column no-pad is-2'>
                            <div class='level-center'>
                                $subtitles
                            </div>
                            <div class='level-center'>
                                $audios
                            </div>
                        </div>" : '';
        $body .= "
        <tr class='no_hover'>
            <td>
                <div class='top10 bottom10'>
                    <div class='bg-06 round5 padding10 level-wide'>
                        <div class='level level-left w-75'>{$staff_pick}{$title}</div>
                        <div>{$new}{$sticky}</div>
                    </div>
                    <div class='columns is-marginless is-paddingless'>
                        <div class='column no-pad is-1 has-text-centered-mobile'>
                            <div class='padding10'>
                                $poster
                            </div>
                        </div>
                        <div class='column no-pad'>
                            <div class='padding10'>
                                <div>" . _fe('Uploaded {0} by {1}', $uploaded, $uploader) . "</div>
                                <span class='level-left is-wrapped'>
                                    <div class='level-left top5 bottom5 right10'>" . _('Seeders') . ":&nbsp;{$seeders}</div>
                                    <div class='level-left top5 bottom5 right10'>" . _('Leechers') . ":&nbsp;{$leechers}</div>
                                    <div class='level-left top5 bottom5 right10'>" . _('Files') . ":&nbsp;{$numfiles}</div>
                                    <div class='level-left top5 bottom5 right10'>" . _('Size') . ":&nbsp;" . mksize($torrent['size']) . "</div>
                                    <div class='level-left top5 bottom5 right10'>" . _('Completed') . ":&nbsp;{$snatched}</div>
                                    <div class='level-left top5 bottom5'>" . _('Comments') . ":&nbsp;{$comments}</div>
                                </span>
                                <div class='level-left top10'>
                                    $download
                                    $bookmark
                                    $icons
                                    $to_go
                                </div>
                            </div>
                        </div>
                        <div class='column no-pad is-2'>
                            <div class='level-wide'>
                                <div class='padding10'>
                                    $genres
                                    $imdb_rating
                                    $user_rating
                                </div>
                                <div class='flex-vertical'>
                                    <div class='padding10'>
                                        $staff_tools
                                    </div>
                                </div>
                            </div>
                        </div>
                        $subs
                        <div class='column no-pad is-1 level-center'>
                            <div class='padding10'>
                                $catinfo
                            </div>
                        </div>
                    </div>
                </div>
            </td>
        </tr>";
    }

    return main_table($body, $heading);
}

function get_links()
{
    foreach ($_GET as $key => $var) {
        if (in_array($key, [
            'sort',
            'type',
        ])
        ) {
            continue;
        }
        if (is_array($var)) {
            foreach ($var as $s_var) {
                $oldlink[] = sprintf('%s=%s', urlencode($key) . '%5B%5D', urlencode((string) $s_var));
            }
        } else {
            $oldlink[] = sprintf('%s=%s', urlencode($key), urlencode($var));
        }
    }
    $oldlink = !empty($oldlink) ? implode('&amp;', array_map('htmlsafechars', $oldlink)) . '&amp;' : '';
    $type = isset($_GET['type']) ? $_GET['type'] : 'desc';
    $link = [];
    for ($i = 1; $i <= 9; ++$i) {
        if (isset($_GET['sort']) && (int) $_GET['sort'] === $i) {
            $link[$i] = isset($type) && $type === 'desc' ? 'asc' : 'desc';
        } else {
            $link[$i] = 'desc';
        }
    }

    return [$oldlink, $link];
}

function get_sorts()
{
    $links = get_links();
    $link = $links[1];
    $oldlink = $links[0];
    $sorts = "
            <div class='tooltipper' title='" . _('Sort By') . ': ' . _('Name') . "'>
                <a href='{$_SERVER['PHP_SELF']}?{$oldlink}sort=1&amp;type={$link[1]}'>" . _('Name') . "</a>
            </div>
            <div class='tooltipper' title='" . _('Sort By') . ': ' . _('Added') . "'>
                <a href='{$_SERVER['PHP_SELF']}?{$oldlink}sort=2&amp;type={$link[2]}'>" . _('Added') . "</a>
            </div>
            <div class='tooltipper' title='" . _('Sort By') . ': ' . _('Files') . "'>
                <a href='{$_SERVER['PHP_SELF']}?{$oldlink}sort=3&amp;type={$link[3]}'>" . _('Files') . "</a>
            </div>
            <div class='tooltipper' title='" . _('Sort By') . ': ' . _('Comments') . "'>
                <a href='{$_SERVER['PHP_SELF']}?{$oldlink}sort=4&amp;type={$link[4]}'>" . _('Comments') . "</a>
            </div>
            <div class='tooltipper' title='" . _('Sort By') . ': ' . _('Size') . "'>
                <a href='{$_SERVER['PHP_SELF']}?{$oldlink}sort=5&amp;type={$link[5]}'>" . _('Size') . "</a>
            </div>
            <div class='tooltipper' title='" . _('Sort By') . ': ' . _('Times Completed') . "'>
                <a href='{$_SERVER['PHP_SELF']}?{$oldlink}sort=6&amp;type={$link[6]}'>" . _('Times Completed') . "</a>
            </div>
            <div class='tooltipper' title='" . _('Sort By') . ': ' . _('Seeders') . "'>
                <a href='{$_SERVER['PHP_SELF']}?{$oldlink}sort=7&amp;type={$link[7]}'>" . _('Seeders') . "</a>
            </div>
            <div class='tooltipper' title='" . _('Sort By') . ': ' . _('Leechers') . "'>
                <a href='{$_SERVER['PHP_SELF']}?{$oldlink}sort=8&amp;type={$link[8]}'>" . _('Leechers') . "</a>
            </div>
            <div class='tooltipper' title='" . _('Sort By') . ': ' . _('Uploader') . "'>
                <a href='{$_SERVER['PHP_SELF']}?{$oldlink}sort=9&amp;type={$link[9]}'>" . _('Uploader') . "</a>
            </div>";

    return $sorts;
}

function get_togo($row)
{
    if ($row['to_go'] == -1) {
        $to_go = "
            <div class='tooltipper' title='" . _('You have never Snatched this Torrent') . "'>
                <i class='icon-ok icon has-text-danger' aria-hidden='true'></i>
            </div>";
    } elseif ($row['to_go'] == 1) {
        $to_go = "
            <div class='tooltipper' title='" . _('You have completed this Torrent') . "'>
                <i class='icon-ok icon has-text-danger' aria-hidden='true'></i>
            </div>";
    } else {
        $to_go = "
            <div class='has-text-warning tooltipper' title='" . _('You have snatched this torrent but you have not completed downloading') . "'>" . number_format((int) $row['to_go'], 1) . '%</div>';
    }

    return $to_go;
}

function get_tools($row)
{
    global $site_config, $curuser;

    $links = '';
    $staff_tools = has_access($curuser['class'], $site_config['allowed']['fast_edit'], 'torrent_mod') || has_access($curuser['class'], $site_config['allowed']['fast_delete'], '') || has_access($curuser['class'], $site_config['allowed']['staff_picks'], '');
    if ($staff_tools) {
        $returnto = !empty($_SERVER['REQUEST_URI']) ? '&amp;returnto=' . urlencode($_SERVER['REQUEST_URI']) : '';
        $edit_link = (has_access($curuser['class'], $site_config['allowed']['fast_edit'], 'torrent_mod') ? "
                <div>
                    <a href='{$site_config['paths']['baseurl']}/edit.php?id=" . $row['id'] . "{$returnto}' class='tooltipper padding5' title='Fast Edit'>
                        <i class='icon-edit icon has-text-info' aria-hidden='true'></i>
                    </a>
                </div>" : '');
        $del_link = (has_access($curuser['class'], $site_config['allowed']['fast_delete'], '') ? "
                <div>
                    <a href='{$site_config['paths']['baseurl']}/fastdelete.php?id=" . $row['id'] . "{$returnto}' class='tooltipper padding5' title='Fast Delete'>
                        <i class='icon-trash-empty icon has-text-danger' aria-hidden='true'></i>
                    </a>
                </div>" : '');
        $staff_pick = '';
        if (has_access($curuser['class'], $site_config['allowed']['staff_picks'], '') && $row['staff_picks'] > 0) {
            $staff_pick = "
                <div data-id='{$row['id']}' data-pick='{$row['staff_picks']}' class='staff_pick tooltipper padding5' title='Remove from Staff Picks'>
                    <i class='icon-star-empty icon has-text-danger' aria-hidden='true'></i>
                </div>";
        } elseif (has_access($curuser['class'], $site_config['allowed']['staff_picks'], '')) {
            $staff_pick = "
                <div data-id='{$row['id']}' data-pick='{$row['staff_picks']}' class='staff_pick tooltipper padding5' title='Add to Staff Picks'>
                    <i class='icon-star-empty icon has-text-success' aria-hidden='true'></i>
                </div>";
        }
        $links = "
                <div class='flex-vertical'>
                    {$edit_link}
                    {$del_link}
                    {$staff_pick}
                </div>";
    }

    return $links;
}

function get_staff_picks($row)
{
    global $site_config;
    $staff_pick = $row['staff_picks'] > 0 ? "
                    <div id='staff_pick_{$row['id']}'>
                        <img src='{$site_config['paths']['images_baseurl']}staff_pick.png' class='tooltipper emoticon' alt='" . _('Staff Pick!') . "' title='" . _('Staff Pick!') . "'>
                    </div>" : "
                    <div id='staff_pick_{$row['id']}'></div>";

    return $staff_pick;
}

function get_imdb_rating($row)
{
    global $site_config;
    $imdb_info = '';
    if ($row['rating'] > 0 && (in_array($row['category'], $site_config['categories']['movie']) || in_array($row['category'], $site_config['categories']['tv']))) {
        $percent = !empty($row['rating']) ? $row['rating'] * 10 : 0;
        $imdb_info = "
                    <div class='star-ratings-css tooltipper' title='{$percent}% " . _('of IMDb voters liked this!') . "'>
                        <div class='star-ratings-css-top' style='width: {$percent}%'><span>★</span><span>★</span><span>★</span><span>★</span><span>★</span></div>
                        <div class='star-ratings-css-bottom'><span>★</span><span>★</span><span>★</span><span>★</span><span>★</span></div>
                    </div>";
    }

    return $imdb_info;
}

function get_icons($row)
{
    global $site_config;

    $icons = [];
    $title = "
        <div class=\"size_5 has-text-centered has-text-success\">" . _('VIP') . "</div>" . _('This torrent is for VIP users only!');
    $icons[] = $row['vip'] == 1 ? "
        <img src='{$site_config['paths']['images_baseurl']}star.png' class='tooltipper icon' alt='" . _('VIP') . "' title='$title'>" : '';
    $icons[] = !empty($row['youtube']) ? "
        <a href=\"" . htmlsafechars($row['youtube']) . "\" target=\"_blank\">
            <i class=\"icon-youtube icon\" aria-hidden=\"true\"></i>
        </a>" : '';
    $icons[] = $row['release_group'] === 'scene' ? "
        <img src='{$site_config['paths']['images_baseurl']}scene.gif' class='tooltipper icon' title='" . _('Scene') . "' alt='" . _('Scene') . "'>" : ($row['release_group'] === 'p2p' ? " <img src='{$site_config['paths']['images_baseurl']}p2p.gif' class='tooltipper icon' title='" . _('P2P') . "' alt='" . _('P2P') . "'>" : '');
    $title = "
        <div class=\"size_5 has-text-primary has-text-centered\">" . _('CHECKED') . "</div>
        <div class=\"right10\">" . _('By') . ": " . format_comment($row['checked_by_username']) . "</div>
        <div class=\"right10\">" . _('On') . ": " . get_date((int) $row['checked_when'], 'DATE') . "</div>";
    $icons[] = !empty($row['checked_by_username']) ? "
        <i class='icon-thumbs-up icon has-text-success tooltipper' aria-hidden='true' title='$title'></i>" : '';
    $title = "
        <div class=\"has-text-centered size_5 has-text-success\">" . _('Free Torrent') . "!</div>
        <div class=\"has-text-centered\">" . ($row['free'] > 1 ? _('Expires') . ': ' . get_date((int) $row['free'], 'DATE') . '<br>(' . mkprettytime($row['free'] - TIME_NOW) . ' ' . _('to go') . ')</div>' : "
        <div class=\"has-text-centered\">" . _('Unlimited') . "</div>");
    $icons[] = $row['free'] != 0 ? "
        <img src='{$site_config['paths']['images_baseurl']}gold.png' class='tooltipper icon' alt='" . _('Free Torrent!') . "' title='$title'>" : '';
    $title = "
        <div class=\"has-text-centered size_5 has-text-success\">" . _('Silver Torrent') . "!</div>
        <div class=\"has-text-centered\">" . ($row['silver'] > 1 ? _('Expires') . ': ' . get_date((int) $row['silver'], 'DATE') . '<br>(' . mkprettytime($row['silver'] - TIME_NOW) . ' ' . _('to go') . ")</div>" : "
        <div class=\"has-text-centered\">" . _('Unlimited') . "</div>");
    $icons[] = $row['silver'] != 0 ? "
        <img src='{$site_config['paths']['images_baseurl']}silver.png' class='tooltipper icon' alt='" . _('Silver Torrent!') . "' title='$title'>" : '';
    $icons[] = $row['freetorrent'] != 0 ? '
        <img src="' . $site_config['paths']['images_baseurl'] . 'freedownload.gif" class="tooltipper icon" alt="' . _('Free Slot') . '" title="' . _('Free Slot in Use') . '">' : '';
    $icons[] = $row['doubletorrent'] != 0 ? '
        <img src="' . $site_config['paths']['images_baseurl'] . 'doubleseed.gif" class="tooltipper icon" alt="' . _('Double Upload Slot') . '" title="' . _('Double Upload Slot in Use') . '">' : '';
    $title = "
        <div class=\"size_5 has-text-centered has-text-danger\">" . _('Nuked') . "</div>
        <div class=\"right10\">" . _('Reason') . ": " . format_comment($row['nukereason']) . "</div>";
    $icons[] = $row['nuked'] === 'yes' ? "
        <img src='{$site_config['paths']['images_baseurl']}nuked.gif' class='tooltipper icon' alt='" . _('Nuked') . "'  class='has-text-centered' title='$title'>" : '';
    $title = "
        <div class=\"size_5 has-text-centered has-text-success\">" . _('Bumped') . "</div>
        <div class=\"has-text-centered\">" . _('This torrent was Re-Animated!') . "</div>";
    $icons[] = $row['bump'] === 'yes' ? "
        <img src='{$site_config['paths']['images_baseurl']}forums/up.gif' class='tooltipper icon' alt='" . _('Re-Animated Torrent') . "' title='$title'>" : '';

    $icons = array_filter($icons);
    $icon_string = implode('&nbsp;', $icons);

    return $icon_string;
}

function get_genres($row, $lookup)
{
    global $site_config;

    $genres = '';
    $genre_icons = '';
    $icons = [];
    if (!empty($row['newgenre'])) {
        $newgenre = [];
        $row['newgenre'] = explode(',', $row['newgenre']);
        foreach ($row['newgenre'] as $foo) {
            $newgenre[] = "<a href='{$site_config['paths']['baseurl']}/browse.php?{$lookup}sg=" . strtolower(trim($foo)) . "'>" . ucfirst(strtolower(trim($foo))) . '</a>';
        }
        if (!empty($newgenre)) {
            $genre_icons = implode(',&nbsp;', $newgenre);
        }
    }

    return "<span class='is-wrapped'>{$genre_icons}</span>";
}

/**
 * @param $row
 * @param $lookup
 *
 * @throws DependencyException
 * @throws NotFoundException
 *
 * @return string
 */
function get_audios($row, $lookup)
{
    global $container, $site_config;

    $subs = $container->get('subtitles');
    $subs_array = explode('|', $row['audios']);
    $Subs = [];
    foreach ($subs_array as $k => $subname) {
        foreach ($subs as $sub) {
            if (strtolower($sub['name']) === strtolower($subname)) {
                $Subs[] = "
                    <a href='{$site_config['paths']['baseurl']}/browse.php?{$lookup}st=" . htmlsafechars($sub['name']) . "' class='left5'>
                        <img src='{$site_config['paths']['images_baseurl']}/{$sub['pic']}' class='tooltipper icon is-marginless' width='16' alt='" . htmlsafechars($sub['name']) . "' title='" . htmlsafechars($sub['name']) . "'>
                    </a>";
            }
        }
    }
    $subtitles = '';
    if (!empty($Subs)) {
        $subtitles = "
            <div class='bg-02 round5 top10 bottom10 padding5'><h4 class='has-text-centered top10'>" . _('Audios') . "</h4>" . implode(' ', $Subs) . '</div>';
    }

    return $subtitles;
}

/**
 * @param $row
 * @param $lookup
 *
 * @throws DependencyException
 * @throws NotFoundException
 *
 * @return string
 */
function get_subtitles($row, $lookup)
{
    global $container, $site_config;

    $subs = $container->get('subtitles');
    $subs_array = explode('|', $row['subs']);
    $Subs = [];
    foreach ($subs_array as $k => $subname) {
        foreach ($subs as $sub) {
            if (strtolower($sub['name']) === strtolower($subname)) {
                $Subs[] = "
                    <a href='{$site_config['paths']['baseurl']}/browse.php?{$lookup}st=" . htmlsafechars($sub['name']) . "' class='left5'>
                        <img src='{$site_config['paths']['images_baseurl']}/{$sub['pic']}' class='tooltipper icon is-marginless' width='16' alt='" . htmlsafechars($sub['name']) . "' title='" . htmlsafechars($sub['name']) . "'>
                    </a>";
            }
        }
    }
    $subtitles = '';
    if (!empty($Subs)) {
        $subtitles = "
            <div class='bg-02 round5 top10 bottom10 padding5'><h4 class='has-text-centered top10'>" . _('Subtitles') . "</h4>" . implode(' ', $Subs) . '</div>';
    }

    return $subtitles;
}

/**
 * @param $row
 *
 * @throws DependencyException
 * @throws NotFoundException
 * @throws \Envms\FluentPDO\Exception
 *
 * @return string
 */
function get_uploader($row)
{
    global $curuser;

    if (!empty($row['username'])) {
        if ((int) $row['anonymous'] === 1) {
            $title = _('Anonymous');
            if (has_access($curuser['class'], UC_STAFF, '')) {
                $title = $row['username'];
            }
            $uploader = "<i class='tooltipper' title='$title'>" . get_anonymous_name() . "</i>";
        } else {
            $uploader = format_username((int) $row['owner']);
        }
    } else {
        $uploader = "<i>" . _('Unknown') . "</i>";
    }

    return $uploader;
}

/**
 * @param $row
 *
 * @throws DependencyException
 * @throws NotFoundException
 * @throws \Envms\FluentPDO\Exception
 *
 * @return string
 */
function get_bookmark($row)
{
    global $container, $curuser;

    $bookmark_class = $container->get(Bookmark::class);
    $bookmark = "
                <span data-tid='{$row['id']}' data-remove='false' data-private='false' class='bookmarks tooltipper' title='" . _('Add Bookmark') . "'>
                    <i class='icon-bookmark-empty icon has-text-success' aria-hidden='true'></i>
                </span>";

    $book = $bookmark_class->get($curuser['id']);
    if (!empty($book)) {
        foreach ($book as $bk) {
            if ($bk['torrentid'] == $row['id']) {
                $bookmark = "
                    <span data-tid='{$row['id']}' data-remove='false' data-private='false' class='bookmarks tooltipper' title='" . _('Delete Bookmark') . "'>
                        <i class='icon-bookmark-empty icon has-text-danger' aria-hidden='true'></i>
                    </span>";
            }
        }
    }

    return $bookmark;
}

/**
 * @param $row
 * @param mixed $image
 *
 * @throws DependencyException
 * @throws InvalidManipulation
 * @throws NotFoundException
 * @throws \Envms\FluentPDO\Exception
 *
 * @return string
 */
function get_poster($row, $image)
{
    global $container, $site_config;

    if (empty($row['poster']) && !empty($row['imdb_id'])) {
        $image_class = $container->get(Image::class);
        $row['poster'] = $image_class->find_images($row['imdb_id'], 'poster');
    }
    $poster = empty($row['poster']) ? "<img src='$image' data-src='{$site_config['paths']['images_baseurl']}noposter.png' class='img-torrent' alt='" . _('Poster') . "'>" : "<img src='" . url_proxy($row['poster'], true, 100) . "' class='img-torrent' alt='" . _('Poster') . "'>";

    return $poster;
}

function get_lookup()
{
    $lookup = $oldlink = [];
    $query_strings = explode('&', $_SERVER['QUERY_STRING']);
    foreach ($query_strings as $query_string) {
        $term = explode('=', $query_string);
        $ignore = [
            'sa',
            'st',
        ];
        if (!in_array($term[0], $ignore) && !empty($term[1])) {
            $lookup[] = "{$term[0]}={$term[1]}";
        }
    }
    $lookup = !empty($lookup) ? implode('&amp;', $lookup) . '&amp;' : '';

    return $lookup;
}

function get_cat_info($row, $lookup)
{
    global $site_config;

    $categories = genrelist(false);
    $change = [];
    foreach ($categories as $key => $value) {
        $change[$value['id']] = [
            'id' => $value['id'],
            'name' => $value['name'],
            'image' => $value['image'],
            'parent_id' => $value['parent_id'],
        ];
    }
    $row['cat_name'] = format_comment($change[$row['category']]['name']);
    $row['cat_pic'] = format_comment($change[$row['category']]['image']);
    $row['parent_id'] = $change[$row['category']]['parent_id'];
    $id = $row['id'];

    $catinfo = '';
    if (isset($row['cat_name'])) {
        $catinfo .= "<a href='{$site_config['paths']['baseurl']}/browse.php?{$lookup}" . (!empty($row['parent_id']) ? "cats[]={$row['parent_id']}&amp;" : '') . 'cats[]=' . $row['category'] . "'>";
        if (isset($row['cat_pic']) && $row['cat_pic'] != '') {
            $catinfo .= "<img src='{$site_config['paths']['images_baseurl']}caticons/" . get_category_icons() . "/{$row['cat_pic']}' class='tooltipper' alt='{$row['cat_name']}' title='{$row['cat_name']}'>";
        } else {
            $catinfo .= format_comment($row['cat_name']);
        }
        $catinfo .= '</a>';
    } else {
        $catinfo .= '-';
    }

    return $catinfo;
}
