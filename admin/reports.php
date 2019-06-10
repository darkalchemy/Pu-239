<?php

declare(strict_types = 1);

use Pu239\Cache;
use Pu239\Session;

require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_pager.php';
require_once INCL_DIR . 'function_html.php';
require_once INCL_DIR . 'function_bbcode.php';
require_once CLASS_DIR . 'class_check.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
$lang = array_merge($lang, load_language('ad_report'));
global $container, $site_config, $CURUSER;

$stdhead = [
    'css' => [
        get_file_name('sceditor_css'),
    ],
];
$stdfoot = [
    'js' => [
        get_file_name('sceditor_js'),
    ],
];
$HTMLOUT = $delt_link = $type = $count2 = '';

/**
 * @param $ts
 *
 * @return string
 */
function round_time($ts)
{
    $mins = floor($ts / 60);
    $hours = floor($mins / 60);
    $mins -= $hours * 60;
    $days = floor($hours / 24);
    $hours -= $days * 24;
    $weeks = floor($days / 7);
    $days -= $weeks * 7;
    $t = '';
    if ($weeks > 0) {
        return "$weeks week" . ($weeks > 1 ? 's' : '');
    }
    if ($days > 0) {
        return "$days day" . ($days > 1 ? 's' : '');
    }
    if ($hours > 0) {
        return "$hours hour" . ($hours > 1 ? 's' : '');
    }
    if ($mins > 0) {
        return "$mins min" . ($mins > 1 ? 's' : '');
    }

    return '< 1 min';
}

if (isset($_GET['id'])) {
    $id = ($_GET['id'] ? (int) $_GET['id'] : (int) $_POST['id']);
    if (!is_valid_id($id)) {
        stderr("{$lang['reports_error']}", "{$lang['reports_error1']}");
    }
}
if (isset($_GET['type'])) {
    $type = ($_GET['type'] ? htmlsafechars((string) $_GET['type']) : htmlsafechars((string) $_POST['type']));
    $typesallowed = [
        'User',
        'Comment',
        'Request_Comment',
        'Offer_Comment',
        'Request',
        'Offer',
        'Torrent',
        'Hit_And_Run',
        'Post',
    ];
    if (!in_array($type, $typesallowed)) {
        stderr("{$lang['reports_error']}", "{$lang['reports_error2']}");
    }
}
$cache = $container->get(Cache::class);
if ((isset($_GET['deal_with_report'])) || (isset($_POST['deal_with_report']))) {
    if (!is_valid_id((int) $_POST['id'])) {
        stderr("{$lang['reports_error']}", "{$lang['reports_error3']}");
    }
    $how_delt_with = 'how_delt_with = ' . sqlesc($_POST['body']);
    $when_delt_with = 'when_delt_with = ' . sqlesc(TIME_NOW);
    sql_query("UPDATE reports SET delt_with = 1, $how_delt_with, $when_delt_with , who_delt_with_it =" . sqlesc($CURUSER['id']) . ' WHERE delt_with!=1 AND id =' . sqlesc($_POST['id'])) or sqlerr(__FILE__, __LINE__);
    $cache->delete('new_report_');
}

$HTMLOUT .= "<h1 class='has-text-centered'>{$lang['reports_active']}</h1>";

if ((isset($_GET['delete'])) && ($CURUSER['class'] >= UC_MAX)) {
    $res = sql_query('DELETE FROM reports WHERE id =' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
    $cache->delete('new_report_');
    $session = $container->get(Session::class);
    $session->set('is-success', $lang['reports_deleted']);
}

$res = sql_query('SELECT count(id) FROM reports') or sqlerr(__FILE__, __LINE__);
$row = mysqli_fetch_array($res);
$count = (int) $row[0];
$perpage = 15;
$pager = pager($perpage, $count, "{$site_config['paths']['baseurl']}/staffpanel.php?tool=reports&amp;");
if ($count === 0) {
    $HTMLOUT .= stderr('', $lang['reports_nice'], 'bottom20');
} else {
    $HTMLOUT .= $count > $perpage ? $pager['pagertop'] : '';
    $HTMLOUT .= "
        <form method='post' action='{$_SERVER['PHP_SELF']}?tool=reports&amp;action=reports&amp;deal_with_report=1' accept-charset='utf-8'>";
    $header = "
        <tr>
            <th>{$lang['reports_added']}</th>
            <th>{$lang['reports_report']}</th>
            <th>{$lang['reports_report1']}</th>
            <th>{$lang['reports_type']}</th>
            <th>{$lang['reports_reason']}</th>
            <th>{$lang['reports_dealt']}</th>
            <th>{$lang['reports_deal']}</th>" . ($CURUSER['class'] >= UC_MAX ? "
            <th>{$lang['reports_delete']}</th>" : '') . '
        </tr>';

    $res_info = sql_query("SELECT reports.id, reports.reported_by, reports.reporting_what, reports.reporting_type, reports.reason, reports.who_delt_with_it, reports.delt_with, reports.added, reports.how_delt_with, reports.when_delt_with, reports.2nd_value, users.username FROM reports INNER JOIN users on reports.reported_by = users.id ORDER BY id DESC {$pager['limit']}");
    $body = '';
    while ($arr_info = mysqli_fetch_assoc($res_info)) {
        $added = (int) $arr_info['added'];
        $solved_date = (int) $arr_info['when_delt_with'];
        if ($solved_date == '0') {
            $solved_in = ' [N/A]';
            $solved_color = 'pink';
        } else {
            $solved_in_wtf = $arr_info['when_delt_with'] - $arr_info['added'];
            $solved_in = '&#160;[' . round_time($solved_in_wtf) . ']';
            $solved_color = 'purple';
            if ($solved_in_wtf > 4 * 3600) {
                $solved_color = 'red';
            } elseif ($solved_in_wtf > 2 * 3600) {
                $solved_color = 'yellow';
            } elseif ($solved_in_wtf <= 3600) {
                $solved_color = 'green';
            }
        }

        if ($arr_info['delt_with']) {
            $res_who = sql_query('SELECT username FROM users WHERE id = ' . sqlesc($arr_info['who_delt_with_it']));
            $arr_who = mysqli_fetch_assoc($res_who);
            $dealtwith = "<span style='color: {$solved_color};'><b>{$lang['reports_yes']}</b> </span> {$lang['reports_by']} " . format_username((int) $arr_info['who_delt_with_it']) . "<br>{$lang['reports_in']} <span style='color: {$solved_color};'>{$solved_in}</span>";
            $checkbox = "<input type='radio' name='id' value='" . (int) $arr_info['id'] . "' disabled>";
        } else {
            $dealtwith = "<span class='has-text-danger'><b>{$lang['reports_no']}</b></span>";
            $checkbox = "<input type='radio' name='id' value='" . (int) $arr_info['id'] . "'>";
        }

        if ($arr_info['reporting_type'] != '') {
            switch ($arr_info['reporting_type']) {
                case 'User':
                    $link_to_thing = format_username((int) $arr_info['reporting_what']);
                    break;

                case 'Comment':
                    $res_who2 = sql_query('SELECT comments.user, users.username, torrents.id FROM comments, users, torrents WHERE comments.user = users.id AND comments.id=' . sqlesc($arr_info['reporting_what']));
                    $arr_who2 = mysqli_fetch_assoc($res_who2);
                    $link_to_thing = "<a class='is-link' href='{$site_config['paths']['baseurl']}/details.php?id=" . (int) $arr_who2['id'] . '&amp;viewcomm=' . (int) $arr_info['reporting_what'] . '#comm' . (int) $arr_info['reporting_what'] . "'><b>" . htmlsafechars($arr_who2['username']) . '</b></a>';
                    break;

                case 'Request_Comment':
                    $res_who2 = sql_query('SELECT comments.request, comments.user, users.username FROM comments, users WHERE comments.user = users.id AND comments.id=' . sqlesc($arr_info['reporting_what']));
                    $arr_who2 = mysqli_fetch_assoc($res_who2);
                    $link_to_thing = "<a class='is-link' href='{$site_config['paths']['baseurl']}/requests.php?id=" . (int) $arr_who2['request'] . '&amp;req_details=1&amp;viewcomm=' . (int) $arr_info['reporting_what'] . '#comm' . (int) $arr_info['reporting_what'] . "'><b>" . htmlsafechars($arr_who2['username']) . '</b></a>';
                    break;

                case 'Offer_Comment':
                    $res_who2 = sql_query('SELECT comments.offer, comments.user, users.username FROM comments, users WHERE comments.user = users.id AND comments.id=' . sqlesc($arr_info['reporting_what']));
                    $arr_who2 = mysqli_fetch_assoc($res_who2);
                    $link_to_thing = "<a class='is-link' href='{$site_config['paths']['baseurl']}/offers.php?id=" . (int) $arr_who2['offer'] . '&amp;off_details=1&amp;viewcomm=' . (int) $arr_info['reporting_what'] . '#comm' . (int) $arr_info['reporting_what'] . "'><b>" . htmlsafechars($arr_who2['username']) . '</b></a>';
                    break;

                case 'Request':
                    $res_who2 = sql_query('SELECT request_name FROM requests WHERE id=' . sqlesc($arr_info['reporting_what']));
                    $arr_who2 = mysqli_fetch_assoc($res_who2);
                    $link_to_thing = "<a class='is-link' href='{$site_config['paths']['baseurl']}/requests.php?id=" . (int) $arr_info['reporting_what'] . "&amp;req_details=1'><b>" . htmlsafechars($arr_who2['request_name']) . '</b></a>';
                    break;

                case 'Offer':
                    $res_who2 = sql_query('SELECT offer_name FROM offers WHERE id=' . sqlesc($arr_info['reporting_what']));
                    $arr_who2 = mysqli_fetch_assoc($res_who2);
                    $link_to_thing = "<a class='is-link' href='{$site_config['paths']['baseurl']}/offers.php?id=" . (int) $arr_info['reporting_what'] . "&amp;off_details=1'><b>" . htmlsafechars($arr_who2['offer_name']) . '</b></a>';
                    break;

                case 'Torrent':
                    $res_who2 = sql_query('SELECT name FROM torrents WHERE id =' . sqlesc($arr_info['reporting_what']));
                    $arr_who2 = mysqli_fetch_assoc($res_who2);
                    $name = !empty($arr_who2['name']) ? htmlsafechars($arr_who2['name']) : 'Torrent Unknown';
                    $link_to_thing = "<a class='is-link' href='{$site_config['paths']['baseurl']}/details.php?id=" . (int) $arr_info['reporting_what'] . "'><b>" . $name . '</b></a>';
                    break;

                case 'Hit_And_Run':
                    $res_who2 = sql_query('SELECT users.username, torrents.name, r.2nd_value FROM users, torrents LEFT JOIN reports AS r ON r.2nd_value = torrents.id WHERE users.id=' . sqlesc($arr_info['reporting_what']));
                    $arr_who2 = mysqli_fetch_assoc($res_who2);
                    $name = !empty($arr_who2['name']) ? htmlsafechars($arr_who2['name']) : '';
                    $link_to_thing = "<b>{$lang['reports_user']}</b> " . format_username((int) $arr_info['reporting_what']) . "<br>{$lang['reports_hit']}<br> <a class='is-link' href='{$site_config['paths']['baseurl']}/details.php?id=" . (int) $arr_info['2nd_value'] . "&amp;page=0#snatched'><b>" . $name . '</b></a>';
                    break;

                case 'Post':
                    $res_who2 = sql_query('SELECT topic_name FROM topics WHERE id =' . sqlesc($arr_info['2nd_value']));
                    $arr_who2 = mysqli_fetch_assoc($res_who2);
                    $name = !empty($arr_who2['topic_name']) ? htmlsafechars($arr_who2['topic_name']) : '';
                    $link_to_thing = "<b>{$lang['reports_post']}</b> <a class='is-link' href='{$site_config['paths']['baseurl']}/forums.php?action=view_topic&amp;topic_id=" . (int) $arr_info['2nd_value'] . '&amp;page=last#' . (int) $arr_info['reporting_what'] . "'><b>" . $name . '</b></a>';
                    break;
            }
        }
        $body .= '
        <tr>
            <td>' . get_date((int) $arr_info['added'], 'DATE', 0, 1) . '</td>
            <td>' . format_username((int) $arr_info['reported_by']) . "</td>
            <td>{$link_to_thing}</td>
            <td><b>" . str_replace('_', ' ', $arr_info['reporting_type']) . '</b>' . '</td>
            <td>' . $arr_info['reason'] . "</td>
            <td>{$dealtwith} {$delt_link}</td>
            <td>{$checkbox}</td>" . ($CURUSER['class'] >= UC_MAX ? "
            <td><a class='is-link' href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=reports&amp;action=reports&amp;id=" . (int) $arr_info['id'] . "&amp;delete=1'>
                    <i class='icon-trash-empty tooltipper has-text-danger' title='{$lang['reports_delete']}' aria-hidden='true'></i>
                </a>
            </td>" : '') . '
        </tr>';
        if ($arr_info['how_delt_with']) {
            $body .= "
        <tr>
            <td colspan='" . ($CURUSER['class'] >= UC_MAX ? '8' : '7') . "'><b>{$lang['reports_with']} " . htmlsafechars($arr_who['username']) . ':</b> ' . get_date((int) $arr_info['when_delt_with'], 'LONG', 0, 1) . "</td>
        </tr>
        <tr>
            <td colspan='" . ($CURUSER['class'] >= UC_MAX ? '8' : '7') . "'>" . htmlsafechars($arr_info['how_delt_with']) . '<br><br></td>
        </tr>';
        }
    }
    $HTMLOUT .= main_table($body, $header);
    $HTMLOUT .= $count > $perpage ? $pager['pagerbottom'] : '';
}

if ($count > 0) {
    $HTMLOUT .= main_div("{$lang['reports_how']} {$CURUSER['username']} {$lang['reports_dealt1']}<br>{$lang['reports_please']}" . BBcode('', 'top20', 200) . "
    <input type='submit' class='button is-small margin20' value='{$lang['reports_confirm']}'>
    </form>", 'top20 has-text-centered', 'padding20');
}
echo stdhead($lang['reports_stdhead'], $stdhead) . wrapper($HTMLOUT) . stdfoot($stdfoot);
die();
