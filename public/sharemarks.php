<?php

declare(strict_types = 1);

use DI\DependencyException;
use DI\NotFoundException;
use Pu239\Database;
use Pu239\User;

require_once __DIR__ . '/../include/bittorrent.php';
require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_torrenttable.php';
require_once INCL_DIR . 'function_pager.php';
require_once INCL_DIR . 'function_html.php';
require_once INCL_DIR . 'function_categories.php';
$user = check_user_status();
$stdfoot = [
    'js' => [
        get_file_name('bookmarks_js'),
    ],
];

$HTMLOUT = '';

/**
 * @param        $res
 * @param        $userid
 * @param        $user
 * @param string $variant
 *
 * @throws DependencyException
 * @throws NotFoundException
 * @throws \Envms\FluentPDO\Exception
 *
 * @return string
 */
function sharetable($res, $userid, $user, $variant = 'index')
{
    global $container, $site_config;
    $HTMLOUT = "
        <div class='has-text-centered bottom20'>
            " . _('Icon Legend :') . "
            <i class='icon-bookmark-empty icon has-text-danger'></i>" . _(' = Delete Bookmark |') . "
            <i class='icon-download icon'></i>" . _(' = Download Torrent |') . "
            <i class='icon-bookmark-empty icon has-text-success'></i>" . _('Add Bookmark') . '
        </div>';

    $heading = '
        <tr>
            <th>Type</th>
            <th>Name</th>';
    //$userid=(int) $_GET['id'];
    if ($user['id'] === $userid) {
        $heading .= ($variant === 'index' ? '
            <th>Download</th>' : '') . '
            <th>Delete</th>';
    } else {
        $heading .= ($variant === 'index' ? '
            <th>Download</th>' : '') . '
            <th>Bookmark</th>';
    }
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
    $categories = genrelist(false);
    $change = [];
    foreach ($categories as $key => $value) {
        $change[$value['id']] = [
            'id' => $value['id'],
            'name' => $value['name'],
            'image' => $value['image'],
        ];
    }
    $body = '';
    foreach ($res as $row) {
        $row['cat_name'] = htmlsafechars($change[$row['category']]['name']);
        $row['cat_pic'] = htmlsafechars($change[$row['category']]['image']);
        $id = (int) $row['id'];
        $body .= '
        <tr>
            <td>';
        if (isset($row['cat_name'])) {
            $body .= "<a href='browse.php?cat=" . (int) $row['category'] . "'>";
            if (isset($row['cat_pic']) && $row['cat_pic'] != '') {
                $body .= "<img src='{$site_config['paths']['images_baseurl']}caticons/" . get_category_icons() . "/{$row['cat_pic']}' alt='{$row['cat_name']}'>";
            } else {
                $body .= $row['cat_name'];
            }
            $body .= '</a>';
        } else {
            $body .= '-';
        }
        $body .= '
            </td>';
        $dispname = htmlsafechars($row['name']);
        $body .= "
            <td><a href='details.php?";
        if ($variant === 'mytorrents') {
            $body .= 'returnto=' . urlencode($_SERVER['REQUEST_URI']) . '&amp;';
        }
        $body .= "id=$id";
        if ($variant === 'index') {
            $body .= '&amp;hit=1';
        }
        $body .= "'><b>$dispname</b></a>&#160;</td>";
        $body .= ($variant === 'index' ? "
                        <td>
                            <a href='{$site_config['paths']['baseurl']}/download.php?torrent={$id}' class='tooltipper' title='" . _('Download Bookmark!') . "'>
                                <i class='icon-download icon'></i>
                            </a>
                        </td>" : '');
        $fluent = $container->get(Database::class);
        $bms = $fluent->from('bookmarks')
                      ->where('torrentid = ?', $id)
                      ->where('userid = ?', $userid)
                      ->fetch();

        $bookmarked = (empty($bms) ? "
                            <span data-tid='{$id}' data-remove='false' data-private='false' class='bookmarks tooltipper' title='" . _('Add Bookmark') . "'>
                                <i class='icon-ok icon'></i>
                            </span>" : "
                            <span data-tid='{$id}' data-remove='true' data-private='false' class='bookmarks tooltipper' title='" . _('Delete Bookmark') . "'>
                                <i class='icon-bookmark-empty icon has-text-danger'></i>
                            </span>");
        $body .= ($variant === 'index' ? "<td>{$bookmarked}</td>" : '');
        if ($variant === 'mytorrents') {
            $body .= "</td><td><a href='edit.php?returnto=" . urlencode($_SERVER['REQUEST_URI']) . '&amp;id=' . (int) $row['id'] . "'>" . _('Edit') . "</a>\n";
        }
        if ($variant === 'mytorrents') {
            $body .= '<td>';
            if ($row['visible'] === 'no') {
                $body .= '<b>' . _('No') . '</b>';
            } else {
                $body .= _('Yes');
            }
            $body .= "</td>\n";
        }
        if ($variant === 'index') {
            $body .= "<td><b><a href='filelist.php?id=$id'>" . (int) $row['numfiles'] . "</a></b></td>\n";
        } else {
            $body .= "<td><b><a href='filelist.php?id=$id'>" . (int) $row['numfiles'] . "</a></b></td>\n";
        }
        if (!$row['comments']) {
            $body .= '<td>' . (int) $row['comments'] . "</td>\n";
        } else {
            if ($variant === 'index') {
                $body .= "<td><b><a href='details.php?id=$id&amp;hit=1&amp;tocomm=1'>" . (int) $row['comments'] . "</a></b></td>\n";
            } else {
                $body .= "<td><b><a href='details.php?id=$id&amp;page=0#startcomments'>" . (int) $row['comments'] . "</a></b></td>\n";
            }
        }
        $body .= '<td><span>' . str_replace(',', '<br>', get_date((int) $row['added'], '')) . "</span></td>\n";
        $body .= '
    <td>' . str_replace(' ', '<br>', mksize($row['size'])) . "</td>\n";
        if ($row['times_completed'] != 1) {
            $_s = _('times');
        } else {
            $_s = _('time');
        }
        $body .= "<td><a href='snatches.php?id=$id'>" . number_format($row['times_completed']) . "<br>$_s</a></td>\n";
        if ($row['seeders']) {
            if ($variant === 'index') {
                if ($row['leechers']) {
                    $ratio = $row['seeders'] / $row['leechers'];
                } else {
                    $ratio = 1;
                }
                $body .= "<td><b><a href='peerlist.php?id=$id#seeders'>
               <span style='color: " . get_slr_color($ratio) . ";'>" . (int) $row['seeders'] . "</span></a></b></td>\n";
            } else {
                $body .= "<td><b><a class='" . linkcolor($row['seeders']) . "' href='peerlist.php?id=$id#seeders'>" . (int) $row['seeders'] . "</a></b></td>\n";
            }
        } else {
            $body .= "<td><span class='" . linkcolor($row['seeders']) . "'>" . (int) $row['seeders'] . "</span></td>\n";
        }
        if ($row['leechers']) {
            if ($variant == 'index') {
                $body .= "<td><b><a href='peerlist.php?id=$id#leechers'>" . number_format($row['leechers']) . "</a></b></td>\n";
            } else {
                $body .= "<td><b><a class='" . linkcolor($row['leechers']) . "' href='peerlist.php?id=$id#leechers'>" . (int) $row['leechers'] . "</a></b></td>\n";
            }
        } else {
            $body .= "<td>0</td>\n";
        }
        if ($variant === 'index') {
            $body .= '<td>' . (isset($row['username']) ? format_username((int) $row['owner']) : '<i>(' . get_anonymous_name() . ')</i>') . "</td>\n";
        }
        $body .= "</tr>\n";
    }
    $HTMLOUT .= main_table($body, $heading);

    return $HTMLOUT;
}

global $container, $site_config;

$userid = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if (!is_valid_id($userid)) {
    stderr(_('Error'), _('Invalid ID'));
}
$HTMLOUT .= '
    <div class="has-text-centered bottom20">
        <h1>' . _('Sharemarks for %s', format_username((int) $userid)) . '</h1>
        <div class="tabs is-centered">
            <ul>
                <li><a href="' . $site_config['paths']['baseurl'] . '/bookmarks.php" class="is-link">My Bookmarks</a></li>
            </ul>
        </div>
    </div>';

$fluent = $container->get(Database::class);
$count = $fluent->from('bookmarks')
                ->select(null)
                ->select('COUNT(id) AS count')
                ->where('private = "no"')
                ->where('userid = ?', $userid)
                ->fetch('count');

$torrentsperpage = $user['torrentsperpage'];
if (empty($torrentsperpage)) {
    $torrentsperpage = 25;
}
if ($count) {
    $pager = pager($torrentsperpage, $count, 'sharemarks.php?&amp;');
    $sharemarks = $fluent->from('bookmarks AS b')
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
                         ->select('u.username')
                         ->innerJoin('torrents AS t ON b.torrentid=t.id')
                         ->leftJoin('users AS u on b.userid=u.id')
                         ->where('private = "no"')
                         ->where('b.userid = ?', $userid)
                         ->orderBy('t.id DESC')
                         ->limit($pager['pdo']['limit'])
                         ->offset($pager['pdo']['offset'])
                         ->fetchAll();

    $HTMLOUT .= $count > $torrentsperpage ? $pager['pagertop'] : '';
    $HTMLOUT .= sharetable($sharemarks, $userid, $user, 'index');
    $HTMLOUT .= $count > $torrentsperpage ? $pager['pagerbottom'] : '';
}

$title = _('Sharemarks');
$breadcrumbs = [
    "<a href='{$_SERVER['PHP_SELF']}'>$title</a>",
];
echo stdhead($title, [], 'page-wrapper', $breadcrumbs) . wrapper($HTMLOUT) . stdfoot($stdfoot);
