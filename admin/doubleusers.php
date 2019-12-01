<?php

declare(strict_types = 1);

use Pu239\Cache;
use Pu239\Message;

require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_pager.php';
require_once CLASS_DIR . 'class_check.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
global $container, $site_config, $CURUSER;

$dt = TIME_NOW;
$HTMLOUT = '';
$remove = (isset($_GET['remove']) ? (int) $_GET['remove'] : 0);
if ($remove) {
    if (empty($remove)) {
        stderr(_('Error'), _('Invalid data'));
    }
    $res = sql_query('SELECT id, username, class FROM users WHERE personal_doubleseed > NOW() AND id = ' . sqlesc($remove)) or sqlerr(__FILE__, __LINE__);
    $msgs_buffer = [];
    if (mysqli_num_rows($res) > 0) {
        $msg = _fe('DoubleSeed On All Torrents have been removed by {0}', $CURUSER['username']);
        $messages_class = $container->get(Message::class);
        $cache = $container->get(Cache::class);
        while ($arr = mysqli_fetch_assoc($res)) {
            $modcomment = sqlesc(get_date((int) $dt, 'DATE', 1) . ' - ' . _fe('DoubleSeed On All Torrents removed by {0}', $CURUSER['username']) . " \n");
            $msgs_buffer[] = [
                'receiver' => $arr['id'],
                'added' => $dt,
                'msg' => $msg,
                'subject' => _('DoubleSeed Notice!'),
            ];
            $messages_class->insert($msgs_buffer);
            sql_query('UPDATE users SET personal_doubleseed = NOW(), modcomment = ' . $modcomment . ' WHERE id = ' . sqlesc($arr['id'])) or sqlerr(__FILE__, __LINE__);
            write_log(_fe('User account {0} ){1}) DoubleSeed On All Torrents have been removed by {2}', $remove, $arr['username'], $CURUSER['username']));
            $cache->delete('user_' . $arr['id']);
        }
    } else {
        stderr(_('Error'), _('That User has No DoubleSeed Status!'));
    }
}
$res2 = sql_query('SELECT id, username, class, personal_doubleseed FROM users WHERE personal_doubleseed > NOW() ORDER BY username') or sqlerr(__FILE__, __LINE__);
$count = mysqli_num_rows($res2);
$perpage = 25;
$pager = pager($perpage, $count, "{$site_config['paths']['baseurl']}/staffpanel.php?tool=doubleusers&amp;");
$res2 = sql_query('SELECT id, username, class, personal_doubleseed FROM users WHERE personal_doubleseed > NOW() ORDER BY username ' . $pager['limit']) or sqlerr(__FILE__, __LINE__);

$HTMLOUT .= "<h1 class='has-text-centered'>" . _fe('DoubleSeed Users ({0})', $count) . '</h1>';
if ($count == 0) {
    $HTMLOUT .= main_div(_('Nothing here'), null, 'padding20 has-text-centered');
} else {
    $heading = '
        <tr>
            <th>' . _('UserName') . '</th>
            <th>' . _('Class') . '</th>
            <th>' . _('Expires') . '</th>
            <th>' . _('Remove DoubleSeed') . '</th>
        </tr>';
    $body = '';
    while ($arr2 = mysqli_fetch_assoc($res2)) {
        $personal_doubleseed = strtotime($arr2['personal_doubleseed']);
        $body .= '
        <tr>
            <td>' . format_username((int) $arr2['id']) . '</td>
            <td>' . get_user_class_name((int) $arr2['class']);
        if (!has_access((int) $arr2['class'], UC_ADMINISTRATOR, 'coder') && $arr2['id'] != $CURUSER['id']) {
            $body .= '</td>
            <td>' . _fe('Until {0} ({1}) to go.', get_date($personal_doubleseed, 'DATE'), mkprettytime($personal_doubleseed - $dt)) . "</td>
            <td><span class='has-text-danger'>" . _('Not Allowed') . '</span></td>
        </tr>';
        } else {
            $body .= '</td>
            <td>' . _fe('Until {0} ({1}) to go.', get_date($personal_doubleseed, 'DATE'), mkprettytime($personal_doubleseed - $dt)) . "</td>
            <td><a href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=doubleusers&amp;action=doubleusers&amp;remove=" . (int) $arr2['id'] . "' onclick=\"return confirm('" . _('Are you sure you want to remove this users DoubleSeed Status?') . "')\">" . _('Remove') . '</a></td>
        </tr>';
        }
    }
    $HTMLOUT .= ($count > $perpage ? $pager['pagertop'] : '') . main_table($body, $heading) . ($count > $perpage ? $pager['pagerbottom'] : '');
}
$title = _('DoubleSeed Manager');
$breadcrumbs = [
    "<a href='{$site_config['paths']['baseurl']}/staffpanel.php'>" . _('Staff Panel') . '</a>',
    "<a href='{$_SERVER['PHP_SELF']}'>$title</a>",
];
echo stdhead($title, [], 'page-wrapper', $breadcrumbs) . wrapper($HTMLOUT) . stdfoot();
