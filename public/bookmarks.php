<?php

declare(strict_types = 1);

use DI\DependencyException;
use DI\NotFoundException;
use Pu239\Database;

require_once __DIR__ . '/../include/bittorrent.php';
require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_html.php';
require_once INCL_DIR . 'function_torrenttable.php';
require_once INCL_DIR . 'function_pager.php';
require_once INCL_DIR . 'function_categories.php';
$user = check_user_status();
global $container, $site_config;

$stdfoot = [
    'js' => [
        get_file_name('bookmarks_js'),
    ],
];

$HTMLOUT = '';

/**
 * @param        $res
 * @param        $userid
 * @param string $variant
 *
 * @throws DependencyException
 * @throws NotFoundException
 * @throws \Envms\FluentPDO\Exception
 *
 * @return string
 */
function bookmarktable($res, $userid, $variant = 'index')
{
    global $container, $site_config;

    $HTMLOUT = "
    <div class='has-text-centered bottom20'>
        " . _('Icon Legend :') . "
        <i class='icon-bookmark-empty icon has-text-danger'></i> = " . _('Delete Bookmark') . " | 
        <i class='icon-download icon'></i> = " . _('Download Torrent') . " | 
        <i class='icon-key icon has-text-success'></i> = " . _('Bookmark is Private') . " | 
        <i class='icon-users icon has-text-danger'></i> = " . _('Bookmark is Public') . '
    </div>';

    $heading = '
                    <tr>
                        <th>' . _('Type') . "</th>
                        <th class='has-text-left'>" . _('Name') . '</th>';
    $heading .= ($variant === 'index' ? '
                        <th>' . _('Delete') . '</th>
                        <th>' : '') . _('Download') . '</th>
                        <th>' . _('Share') . '</th>';
    if ($variant === 'mytorrents') {
        $heading .= '
                        <th>' . _('Edit') . '</th>
                        <th>' . _('Yes') . '</th>';
    }
    $heading .= '
                        <th>' . _('Files') . '</th>
                        <th>' . _('Comments') . '</th>
                        <th>' . _('Added') . '</th>
                        <th>' . _('Torrent Size') . '</th>
                        <th>' . _('Times Completed') . '</th>
                        <th>' . _('Seeders') . '</th>
                        <th>' . _('Leechers') . '</th>';
    if ($variant === 'index') {
        $heading .= '
                        <th>' . _('Upped by') . '</th>';
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
    $fluent = $container->get(Database::class);
    foreach ($res as $row) {
        $row['cat_name'] = htmlsafechars($change[$row['category']]['name']);
        $row['cat_pic'] = htmlsafechars($change[$row['category']]['image']);
        $id = (int) $row['id'];
        $body .= "
                    <tr>
                        <td class='has-text-centered'>";
        if (isset($row['cat_name'])) {
            $body .= '<a href="' . $site_config['paths']['baseurl'] . '/browse.php?cat=' . (int) $row['category'] . '">';
            if (isset($row['cat_pic']) && $row['cat_pic'] != '') {
                $body .= "<img src='{$site_config['paths']['images_baseurl']}caticons/" . get_category_icons() . '/' . htmlsafechars($row['cat_pic']) . "' alt='" . htmlsafechars($row['cat_name']) . "' class='tooltipper' title='" . htmlsafechars($row['cat_name']) . "'>";
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
                            <a href='{$site_config['paths']['baseurl']}/details.php?";
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
                            <span data-tid='{$id}' data-remove='true' data-private='false' class='bookmarks tooltipper' title='" . _('Delete Bookmark!') . "'>
                                <i class='icon-bookmark-empty icon has-text-danger'></i>
                            </span>
                        </td>" : '');
        $body .= ($variant === 'index' ? "
                        <td class='has-text-centered'>
                            <a href='{$site_config['paths']['baseurl']}/download.php?torrent={$id}' class='tooltipper' title='" . _('Download Bookmark!') . "'>
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
                            <span data-tid='{$id}' data-remove='false' data-private='true' class='bookmarks tooltipper' title='" . _('Mark Bookmark Public!') . "'>
                                <i class='icon-key icon has-text-success'></i>
                            </span>
                        </td>" : '');
        } elseif ($bms['private'] === 'no') {
            $body .= ($variant === 'index' ? "
                        <td class='has-text-centered'>
                            <span data-tid='{$id}' data-remove='false' data-private='true' class='bookmarks tooltipper' title='" . _('Mark Bookmark Private!') . "'>
                                <i class='icon-users icon has-text-danger'></i>
                            </span>
                        </td>" : '');
        }
        if ($variant === 'mytorrents') {
            $body .= "
                        </td>
                        <td class='has-text-centered'>
                            <a href='{$site_config['paths']['baseurl']}/edit.php?returnto=" . urlencode($_SERVER['REQUEST_URI']) . '&amp;id=' . (int) $row['id'] . "'>" . _('Edit') . '</a>';
        }
        if ($variant === 'mytorrents') {
            $body .= "
                        <td class='has-text-right'>";
            if ($row['visible'] === 'no') {
                $body .= _('No');
            } else {
                $body .= _('Yes');
            }
            $body .= '
                        </td>';
        }
        if ($variant === 'index') {
            $body .= "
                        <td class='has-text-right'><b><a href='{$site_config['paths']['baseurl']}/filelist.php?id=$id'>" . (int) $row['numfiles'] . '</a></b></td>';
        } else {
            $body .= "
                        <td class='has-text-right'><b><a href='{$site_config['paths']['baseurl']}/filelist.php?id=$id'>" . (int) $row['numfiles'] . '</a></b></td>';
        }
        if (!$row['comments']) {
            $body .= "
                        <td class='has-text-right'>" . (int) $row['comments'] . '</td>';
        } elseif ($variant === 'index') {
            $body .= "
                    <td class='has-text-right'><b><a href='{$site_config['paths']['baseurl']}/details.php?id=$id&amp;hit=1&amp;tocomm=1'>" . (int) $row['comments'] . '</a></b></td>';
        } else {
            $body .= "
                    <td class='has-text-right'><b><a href='{$site_config['paths']['baseurl']}/details.php?id=$id&amp;page=0#startcomments'>" . (int) $row['comments'] . '</a></b></td>';
        }
        $body .= "
                        <td class='has-text-centered'><span>" . str_replace(',', '<br>', get_date((int) $row['added'], '')) . "</span></td>
                        <td class='has-text-centered'>" . str_replace(' ', '<br>', mksize($row['size'])) . '</td>';
        $body .= "
                        <td class='has-text-centered'><a href='{$site_config['paths']['baseurl']}/snatches.php?id=$id'>" . _pfe('{0} time', '{0} times', $row['times_completed']) . '</a></td>';
        if ((int) $row['seeders']) {
            if ($variant === 'index') {
                if ($row['leechers']) {
                    $ratio = (int) $row['seeders'] / (int) $row['leechers'];
                } else {
                    $ratio = 1;
                }
                $body .= "
                        <td class='has-text-right'><b><a href='{$site_config['paths']['baseurl']}/peerlist.php?id=$id#seeders'><span style='color: " . get_slr_color($ratio) . ";'>" . (int) $row['seeders'] . '</span></a></b></td>';
            } else {
                $body .= "
                        <td class='has-text-right'><b><a class='" . linkcolor($row['seeders']) . "' href='{$site_config['paths']['baseurl']}/peerlist.php?id=$id#seeders'>" . (int) $row['seeders'] . '</a></b></td>';
            }
        } else {
            $body .= "
                        <td class='has-text-right'><span class='" . linkcolor($row['seeders']) . "'>" . (int) $row['seeders'] . '</span></td>';
        }
        if ((int) $row['leechers']) {
            if ($variant === 'index') {
                $body .= "
                        <td class='has-text-right'><b><a href='{$site_config['paths']['baseurl']}/peerlist.php?id=$id#leechers'>" . number_format($row['leechers']) . '</a></b></td>';
            } else {
                $body .= "
                        <td class='has-text-right'><b><a class='" . linkcolor($row['leechers']) . "' href='{$site_config['paths']['baseurl']}/peerlist.php?id=$id#leechers'>" . (int) $row['leechers'] . '</a></b></td>';
            }
        } else {
            $body .= "
                        <td class='has-text-right'>0</td>";
        }
        if ($variant === 'index') {
            $body .= "
                        <td class='has-text-centered'>" . (isset($row['owner']) ? format_username((int) $row['owner']) : '<i>(' . _('Unknown') . ')</i>') . '</td>';
        }
        $body .= '
                    </tr>';
    }
    $HTMLOUT .= main_table($body, $heading);

    return $HTMLOUT;
}

$userid = isset($_GET['id']) ? (int) $_GET['id'] : $user['id'];
if (!is_valid_id($userid)) {
    stderr(_('Error'), _('Invalid ID'));
}
if ($userid != $user['id']) {
    stderr(_('Error'), _('Access denied. Try ') . "<a href='{$site_config['paths']['baseurl']}/sharemarks.php?id={$userid}'>" . _('Here') . '</a>');
}
$HTMLOUT .= '
    <div class="has-text-centered bottom20">
        <h1>' . _('My Bookmarks') . '</h1>
        <div class="tabs is-centered">
            <ul>
                <li><a href="' . $site_config['paths']['baseurl'] . '/sharemarks.php?id=' . $userid . '" class="is-link">' . _('My Sharemarks') . '</a></li>
            </ul>
        </div>
    </div>';

$fluent = $container->get(Database::class);
$count = $fluent->from('bookmarks')
                ->select(null)
                ->select('COUNT(id) AS count')
                ->where('userid = ?', $userid)
                ->fetch('count');
$torrentsperpage = $user['torrentsperpage'];
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
                        ->innerJoin('torrents AS t ON b.torrentid=t.id')
                        ->where('b.userid = ?', $userid)
                        ->orderBy('t.id DESC')
                        ->limit($pager['pdo']['limit'])
                        ->offset($pager['pdo']['offset'])
                        ->fetchAll();

    $HTMLOUT .= $count > $torrentsperpage ? $pager['pagertop'] : '';
    $HTMLOUT .= bookmarktable($bookmarks, $userid, 'index');
    $HTMLOUT .= $count > $torrentsperpage ? $pager['pagerbottom'] : '';
}
$title = _('Bookmarks');
$breadcrumbs = [
    "<a href='{$_SERVER['PHP_SELF']}'>$title</a>",
];
echo stdhead($title, [], 'page-wrapper', $breadcrumbs) . wrapper($HTMLOUT) . stdfoot($stdfoot);
