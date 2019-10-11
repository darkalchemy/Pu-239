<?php

declare(strict_types = 1);

use Pu239\Database;
use Pu239\User;

require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_bbcode.php';
require_once INCL_DIR . 'function_pager.php';
require_once CLASS_DIR . 'class_check.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
global $container, $site_config;

$HTMLOUT = '';
$id = isset($_GET['id']) ? (int) $_GET['id'] : (isset($_POST['id']) ? (int) $_POST['id'] : 0);
$users_class = $container->get(User::class);
$fluent = $container->get(Database::class);
if ($id !== 0) {
    $arr_user = $users_class->getUserFromId($id);
    $HTMLOUT .= '
    <div class="bottom20">
        <ul class="level-center bg-06">' . ($arr_user['invitedby'] == 0 ? '
            <li class="margin10"><a title="' . htmlsafechars($arr_user['username']) . ' ' . _('was registered during open doors') . '" class="is-link tooltipper">' . _('go up one level') . '</a></li>' : '
            <li class="margin10"><a href="' . $site_config['paths']['baseurl'] . '/staffpanel.php?tool=invite_tree&amp;really_deep=1&amp;id=' . (int) $arr_user['invitedby'] . '" title="go up one level" class="is-link tooltipper">' . _('go up one level') . '</a></li>') . '
            <li class="margin10"><a href="' . $site_config['paths']['baseurl'] . '/staffpanel.php?tool=invite_tree&amp;' . (isset($_GET['deeper']) ? '' : '&amp;deeper=1') . '&amp;id=' . $id . '" title=" ' . _('click to') . ' ' . (isset($_GET['deeper']) ? _('shrink') : _('expand')) . ' ' . _('this tree') . ' " class="is-link tooltipper">' . _('expand tree') . '</a></li>
            <li class="margin10"><a href="' . $site_config['paths']['baseurl'] . '/staffpanel.php?tool=invite_tree&amp;really_deep=1&amp;id=' . $id . '" title="' . _('click to expand even more') . '" class="is-link tooltipper">' . _('expand even more') . '</a></li>
        </ul>
    </div>
    <h1 class="has-text-centered">' . htmlsafechars($arr_user['username']) . (substr($arr_user['username'], -1) === 's' ? '\'' : '\'s') . ' ' . _('Invite Tree') . '</h1>
    <table>
        <tr>
            <td>';
    $query = $fluent->from('users as u')
                    ->select(null)
                    ->select('u.id')
                    ->select('u.username')
                    ->select('u.uploaded')
                    ->select('u.downloaded')
                    ->select('u.email')
                    ->select('i.status')
                    ->leftJoin('invite_codes AS i ON u.id = i.receiver')
                    ->where('u.invitedby = ?', $id)
                    ->where("u.join_type = 'invite'")
                    ->orderBy('u.registered')
                    ->fetchAll();
    if (empty($query)) {
        $HTMLOUT .= stdmsg(_('Error'), _('No invitees yet.'));
    } else {
        $HTMLOUT .= '
                <table class="table table-bordered table-striped">
                    <tr>
                        <td><span class="has-text-weight-bold">' . _('Username') . '</span></td>
                        <td><span class="has-text-weight-bold">' . _('Email') . '</span></td>
                        <td><span class="has-text-weight-bold">' . _('Uploaded') . '</span></td>
                        <td><span class="has-text-weight-bold">' . _('Downloaded') . '</span></td>
                        <td><span class="has-text-weight-bold">' . _('Ratio') . '</span></td>
                        <td><span class="has-text-weight-bold">' . _('Status') . '</span></td>
                    </tr>';
        foreach ($query as $arr_invited) {
            $deeper = '';
            if (isset($_GET['deeper']) || isset($_GET['really_deep'])) {
                $query2 = $fluent->from('users as u')
                                 ->select(null)
                                 ->select('u.id')
                                 ->select('u.username')
                                 ->select('u.uploaded')
                                 ->select('u.downloaded')
                                 ->select('u.email')
                                 ->select('i.status')
                                 ->leftJoin('invite_codes AS i ON u.id = i.receiver')
                                 ->where('u.invitedby = ?', $arr_invited['id'])
                                 ->where("u.join_type = 'invite'")
                                 ->orderBy('u.registered')
                                 ->fetchAll();

                if (!empty($query2)) {
                    $deeper .= '
                    <tr>
                        <td colspan="6"><span class="has-text-weight-bold">' . htmlsafechars($arr_invited['username']) . (substr($arr_invited['username'], -1) === 's' ? '\'' : '\'s') . _('Invites') . ': </span>
                            <div>
                                <table class="table table-bordered table-striped">
                                    <tr>
                                        <td><span class="has-text-weight-bold">' . _('Username') . '</span></td>
                                        <td><span class="has-text-weight-bold">' . _('Email') . '</span></td>
                                        <td><span class="has-text-weight-bold">' . _('Uploaded') . '</span></td>
                                        <td><span class="has-text-weight-bold">' . _('Downloaded') . '</span></td>
                                        <td><span class="has-text-weight-bold">' . _('Ratio') . '</span></td>
                                        <td><span class="has-text-weight-bold">' . _('Status') . '</span></td>
                                    </tr>';
                    foreach ($query2 as $arr_invited_deeper) {
                        if (isset($_GET['really_deep'])) {
                            $query3 = $fluent->from('users as u')
                                             ->select(null)
                                             ->select('u.id')
                                             ->select('u.username')
                                             ->select('u.uploaded')
                                             ->select('u.downloaded')
                                             ->select('u.email')
                                             ->select('i.status')
                                             ->leftJoin('invite_codes AS i ON u.id = i.receiver')
                                             ->where('u.invitedby = ?', $arr_invited_deeper['id'])
                                             ->where("u.join_type = 'invite'")
                                             ->orderBy('u.registered')
                                             ->fetchAll();

                            if (!empty($query3)) {
                                $deeper .= '
                                    <tr>
                                        <td colspan="6"><span class="has-text-weight-bold">' . htmlsafechars($arr_invited_deeper['username']) . (substr($arr_invited_deeper['username'], -1) === 's' ? '\'' : '\'s') . ' Invites:</span>
                                            <div>
                                                <table class="table table-bordered table-striped">
                                                    <tr>
                                                        <td><span class="has-text-weight-bold">' . _('Username') . '</span></td>
                                                        <td><span class="has-text-weight-bold">' . _('Email') . '</span></td>
                                                        <td><span class="has-text-weight-bold">' . _('Uploaded') . '</span></td>
                                                        <td><span class="has-text-weight-bold">' . _('Downloaded') . '</span></td>
                                                        <td><span class="has-text-weight-bold">' . _('Ratio') . '</span></td>
                                                        <td><span class="has-text-weight-bold">' . _('Status') . '</span></td>
                                                    </tr>';
                                foreach ($query3 as $arr_invited_really_deep) {
                                    $deeper .= '
                                                    <tr>
                                                        <td>' . ($arr_invited_really_deep['status'] === 'Pending' ? htmlsafechars($arr_invited_really_deep['username']) : format_username($arr_invited_really_deep['id'])) . '</td>
                                                        <td>' . htmlsafechars($arr_invited_really_deep['email']) . '</td>
                                                        <td>' . mksize($arr_invited_really_deep['uploaded']) . '</td>
                                                        <td>' . mksize($arr_invited_really_deep['downloaded']) . '</td>
                                                        <td>' . member_ratio($arr_invited_really_deep['uploaded'], $arr_invited_really_deep['downloaded']) . '</td>
                                                        <td>' . ($arr_invited_really_deep['status'] === 'Confirmed' ? '<span class="has-color-lime">' . _('Confirmed') . '</span></td></tr>' : '<span class="has-color-danger">' . _('Pending') . '</span></td>
                                                    </tr>');
                                }
                                $deeper .= '
                                                </table>
                                            </div>
                                        </td>
                                    </tr>';
                            }
                        }
                        $deeper .= '
                                    <tr>
                                        <td>' . ($arr_invited_deeper['status'] === 'Pending' ? htmlsafechars($arr_invited_deeper['username']) : format_username($arr_invited_deeper['id'])) . '</td>
                                        <td>' . htmlsafechars($arr_invited_deeper['email']) . '</td>
                                        <td>' . mksize($arr_invited_deeper['uploaded']) . '</td>
                                        <td>' . mksize($arr_invited_deeper['downloaded']) . '</td>
                                        <td>' . member_ratio($arr_invited_deeper['uploaded'], $arr_invited_deeper['downloaded']) . '</td>
                                        <td>' . ($arr_invited_deeper['status'] === 'Confirmed' ? '<span class="has-color-lime">' . _('Confirmed') . '</span></td></tr>' : '<span class="has-color-danger">' . _('Pending') . '</span></td>
                                    </tr>');
                    }
                    $deeper .= '
                                </table>
                            </div>
                        </td>
                    </tr>';
                }
            }
            $HTMLOUT .= '
                    <tr>
                        <td>' . ($arr_invited['status'] === 'Pending' ? htmlsafechars($arr_invited['username']) : format_username($arr_invited['id'])) . '</td>
                        <td>' . htmlsafechars($arr_invited['email']) . '</td>
                        <td>' . mksize($arr_invited['uploaded']) . '</td>
                        <td>' . mksize($arr_invited['downloaded']) . '</td>
                        <td>' . member_ratio($arr_invited['uploaded'], $arr_invited['downloaded']) . '</td>
                        <td>' . ($arr_invited['status'] === 'Confirmed' ? '
                            <span class="has-color-lime">' . _('Confirmed') . '</span>
                        </td>
                    </tr>' : '
                            <span class="has-color-danger">' . _('Pending') . '</span>
                        </td>
                    </tr>');
            $HTMLOUT .= $deeper;
        }
        $HTMLOUT .= '
                </table>';
    }
    $HTMLOUT .= '
            </td>
        </tr>
    </table>';
} else {
    $id = '';
    $search = isset($_GET['search']) ? strip_tags(trim($_GET['search'])) : '';
    $class = isset($_GET['class']) ? $_GET['class'] : '-';
    $letter = '';
    $q = '';
    if ($class == '-' || !ctype_digit($class)) {
        $class = '';
    }
    if ($search != '' || $class) {
        $query = 'username LIKE ' . sqlesc("%$search%") . ' AND status=\'confirmed\'';
        if ($search) {
            $q = 'search=' . htmlsafechars($search);
        }
    } else {
        $letter = isset($_GET['letter']) ? trim((string) $_GET['letter']) : '';
        if (strlen($letter) > 1) {
            die();
        }
        if ($letter == '' || strpos('abcdefghijklmnopqrstuvwxyz0123456789', $letter) === false) {
            $letter = '';
        }
        $query = 'username LIKE ' . sqlesc("$letter%") . ' AND status=\'confirmed\'';
        $q = 'letter=' . $letter;
    }
    if (ctype_digit($class)) {
        $query .= ' AND class=' . sqlesc($class);
        $q .= ($q ? '&amp;' : '') . 'class=' . $class;
    }
    $HTMLOUT .= '
        <h1 class="has-text-centered">' . _('Search Users To View Invite Tree') . '</h1>
        <form method="get" action="staffpanel.php?tool=invite_tree&amp;search=1&amp;" accept-charset="utf-8">
            <div class="has-text-centered margin20">
                <input type="hidden" name="action" value="invite_tree"/>
                <input type="text" size="30" name="search" value="' . $search . '"/>
                <select name="class">
                    <option value=" - ">' . _('(any class)') . '</option>';
    for ($i = 0;; ++$i) {
        if ($c = get_user_class_name((int) $i)) {
            $HTMLOUT .= '
                    <option value="' . $i . '" ' . (ctype_digit($class) && $class == $i ? 'selected' : '') . '>' . $c . '</option>';
        } else {
            break;
        }
    }
    $HTMLOUT .= '
                </select>
                <input type="submit" value="' . _('Search') . '" class="button is-small">
            </div>
        </form>';
    $aa = range('0', '9');
    $bb = range('a', 'z');
    $cc = array_merge($aa, $bb);
    unset($aa, $bb);
    $HTMLOUT .= '
            <nav class="pagination is-centered is-marginless is-small" role="navigation" aria-label="pagination">
                <ul class="pagination-list bottom20">
                    <li>';
    $count = 0;
    foreach ($cc as $L) {
        $HTMLOUT .= ($count === 10) ? '<br><br>' : '';
        $LL = strtoupper((string) $L);
        if (!strcmp((string) $L, $letter)) {
            $HTMLOUT .= '
                        <a class="pagination-link is-current" aria-label="' . $LL . '">' . $LL . '</a>';
        } else {
            $HTMLOUT .= '
                        <a href="' . $site_config['paths']['baseurl'] . '/staffpanel.php?tool=invite_tree&amp;letter=' . $L . '" class="pagination-link button">' . $LL . '</a>';
        }
        ++$count;
    }
    $HTMLOUT .= '
                    </li>
                </ul>
            </nav>';
    $page = isset($_GET['page']) ? (int) $_GET['page'] : 0;
    $perpage = isset($_GET['perpage']) ? (int) $_GET['perpage'] : 20;
    $res_count = sql_query('SELECT COUNT(id) FROM users WHERE ' . $query);
    $arr_count = mysqli_fetch_row($res_count);
    $count = $arr_count[0] > 0 ? (int) $arr_count[0] : 0;
    $link = $site_config['paths']['baseurl'] . '/staffpanel.php?tool=invite_tree';
    $pager = pager($perpage, $count, $link);
    $menu_top = $pager['pagertop'];
    $menu_bottom = $pager['pagerbottom'];
    $LIMIT = $pager['limit'];

    $HTMLOUT .= $count > $perpage ? $menu_top : '';
    if ($count > 0) {
        $res = sql_query('SELECT users.*, countries.name, countries.flagpic FROM users FORCE INDEX ( username ) LEFT JOIN countries ON country = countries.id WHERE ' . $query . ' ORDER BY username ' . $LIMIT);
        $heading = '
            <tr>
                <th>' . _('User name') . '</th>
                <th>' . _('Registered') . '</th>
                <th>' . _('Last access') . '</th>
                <th>' . _('Class') . '</th>
                <th>' . _('Country') . '</th>
                <th>' . _('Edit') . '</th>
            </tr>';
        $body = '';
        while ($row = mysqli_fetch_assoc($res)) {
            $country = ($row['name'] != null) ? '
                <td>
                    <img src="' . $site_config['paths']['images_baseurl'] . 'flag/' . $row['flagpic'] . '" alt="' . htmlsafechars($row['name']) . '" title="' . htmlsafechars($row['name']) . '" class="tooltipper">
                </td>' : '
                <td>---</td>';
            $body .= '
            <tr>
                <td>' . format_username((int) $row['id']) . '</td>
                <td>' . get_date((int) $row['registered'], '') . '</td><td>' . get_date((int) $row['last_access'], '') . '</td>
                <td>' . get_user_class_name((int) $row['class']) . '</td>' . $country . '
                <td>
                    <a href="' . $site_config['paths']['baseurl'] . '/staffpanel.php?tool=invite_tree&amp;id=' . (int) $row['id'] . '" title="' . _('Look at this members invite tree') . '" class="tooltipper">
                        <span class="button is-small">' . _('VIEW') . '</span>
                    </a>
                </td>
            </tr>';
        }
        $HTMLOUT .= main_table($body, $heading);
    } else {
        $HTMLOUT .= stdmsg(_('Error'), _('Sorry, no member was found'));
    }
    $HTMLOUT .= $count > $perpage ? $menu_bottom : '';
}
$title = _('Invite Tree');
$breadcrumbs = [
    "<a href='{$site_config['paths']['baseurl']}/staffpanel.php'>" . _('Staff Panel') . '</a>',
    "<a href='{$_SERVER['PHP_SELF']}'>$title</a>",
];
echo stdhead($title, [], 'page-wrapper', $breadcrumbs) . wrapper($HTMLOUT) . stdfoot();
