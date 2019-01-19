<?php

require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_pager.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
global $lang, $fluent;

$lang = array_merge($lang, load_language('ad_modded_torrents'));
$modes = [
    'today',
    'yesterday',
    'unmodded',
];
$HTMLOUT = '';
$links = "
    <ul class='level-center bg-06'>
        <li class='altlink margin10'>
            <a href='{$_SERVER['PHP_SELF']}?tool={$_GET['tool']}&amp;type=today' data-toggle='tooltip' data-placement='top' title='Tooltip on top'>" . $lang['mtor_modded_today'] . "</a>
        </li>
        <li class='altlink margin10'>
            <a href='{$_SERVER['PHP_SELF']}?tool={$_GET['tool']}&amp;type=yesterday' >" . $lang['mtor_modded_yesterday'] . "</a>
        </li>
        <li class='altlink margin10'>
            <a href='{$_SERVER['PHP_SELF']}?tool={$_GET['tool']}&amp;type=unmodded' >" . $lang['mtor_all_unmodded_torrents'] . '</a>
        </li>
    </ul>';

/**
 * @param      $arr
 * @param bool $empty
 *
 * @return string
 *
 * @throws \Envms\FluentPDO\Exception
 */
function do_sort($arr, $empty = false)
{
    global $lang, $site_config;

    $returnto = !empty($_SERVER['REQUEST_URI']) ? '&amp;returnto=' . urlencode($_SERVER['REQUEST_URI']) : '';
    $ret_html = '';

    foreach ($arr as $res) {
        if (isset($res['checked_when'])) {
            $ret_html .= "
                <tr>
                    <td>
                        <a href='{$site_config['baseurl']}/details.php?id=" . (int) $res['id'] . "'>" . htmlsafechars($res['name']) . '</a>
                    </td>
                    <td>' . format_username($res['checked_by']) . '</td>
                    <td>' . get_date($res['checked_when'], 'LONG') . '</td>
                </tr>';
        } else {
            $ret_html .= "
                <tr>
                    <td>
                        <a href='{$site_config['baseurl']}/details.php?id={$res['id']}{$returnto}'>" . htmlsafechars($res['name']) . '</a>
                    </td>
                    <td>' . get_date($res['added'], 'LONG') . "</td>
                    <td>
                        <a href='{$site_config['baseurl']}/edit.php?id={$res['id']}{$returnto}' class='tooltipper' title='{$lang['mtor_edit']}'>
                            <i class='icon-edit icon'></i>
                        </a>
                    </td>
                </tr>";
        }
    }

    return $ret_html;
}

if (isset($_GET['type']) && in_array($_GET['type'], $modes)) {
    $mode = (isset($_GET['type']) && in_array($_GET['type'], $modes)) ? $_GET['type'] : stderr($lang['mtor_error'], '' . $lang['mtor_please_try_that_previous_request_again'] . '.');
    if ($mode === 'yesterday') {
        $count = $fluent->from('torrents')
            ->select(null)
            ->select('COUNT(*) AS count')
            ->where('checked_when < UNIX_TIMESTAMP(CURDATE())')
            ->where('checked_when >= UNIX_TIMESTAMP(CURDATE() - INTERVAL 1 DAY)')
            ->fetch('count');

        if (!$count) {
            $HTMLOUT = $links . stdmsg($lang['mtor_sorry'], $lang['mtor_no_torrents_have_been_modded'], 'top20');
            $title = $lang['mtor_modded_today'];
        } else {
            $perpage = 15;
            $pager = pager($perpage, $count, "{$_SERVER['PHP_SELF']}?tool=modded_torrents&type={$mode}&");
            $data = $fluent->from('torrents')
                ->select(null)
                ->select('id')
                ->select('name')
                ->select('checked_when')
                ->select('checked_by')
                ->where('checked_when < UNIX_TIMESTAMP(CURDATE())')
                ->where('checked_when >= UNIX_TIMESTAMP(CURDATE() - INTERVAL 1 DAY)')
                ->orderBy('checked_when DESC')
                ->limit("{$pager['pdo']}")
                ->fetchAll();

            if ($data) {
                $data = do_sort($data);
                $HTMLOUT .= $links . "
                <div class='has-text-centered'>
                    <h2>" . $lang['mtor_summary'] . '</h2>
                </div>' . ($count > $perpage ? $pager['pagertop'] : '');
                $heading = '
                    <tr>
                       <th>' . $lang['mtor_torrent'] . '</th>
                       <th>' . $lang['mtor_modded_by'] . '</th>
                       <th>' . $lang['mtor_time'] . '</th>
                    </tr>';
                $HTMLOUT .= main_table($data, $heading);
                $HTMLOUT .= $count > $perpage ? $pager['pagerbottom'] : '';
            }
            $title = "$count " . $lang['mtor_modded_torrents'] . " $mode";
        }
    } elseif ($mode === 'today') {
        $count = $fluent->from('torrents')
            ->select(null)
            ->select('COUNT(*) AS count')
            ->where('checked_when >= UNIX_TIMESTAMP(CURDATE())')
            ->where('checked_when < UNIX_TIMESTAMP(CURDATE() + INTERVAL 1 DAY)')
            ->fetch('count');

        if (!$count) {
            $HTMLOUT = $links . stdmsg($lang['mtor_sorry'], $lang['mtor_no_torrents_have_been_modded'], 'top20');
            $title = $lang['mtor_modded_yesterday'];
        } else {
            $perpage = 15;
            $pager = pager($perpage, $count, "{$_SERVER['PHP_SELF']}?tool=modded_torrents&type={$mode}&");
            $data = $fluent->from('torrents')
                ->select(null)
                ->select('id')
                ->select('name')
                ->select('checked_when')
                ->select('checked_by')
                ->where('checked_when >= UNIX_TIMESTAMP(CURDATE())')
                ->where('checked_when < UNIX_TIMESTAMP(CURDATE() + INTERVAL 1 DAY)')
                ->orderBy('checked_when DESC')
                ->limit("{$pager['pdo']}")
                ->fetchAll();

            if ($data) {
                $data = do_sort($data);
                $HTMLOUT .= $links . "
                <div class='has-text-centered'>
                    <h2>" . $lang['mtor_summary'] . '</h2>
                </div>' . ($count > $perpage ? $pager['pagertop'] : '');
                $heading = '
                    <tr>
                       <th>' . $lang['mtor_torrent'] . '</th>
                       <th>' . $lang['mtor_modded_by'] . '</th>
                       <th>' . $lang['mtor_time'] . '</th>
                    </tr>';
                $HTMLOUT .= main_table($data, $heading);
                $HTMLOUT .= $count > $perpage ? $pager['pagerbottom'] : '';
            }
            $title = "$count " . $lang['mtor_modded_torrents'] . " $mode";
        }
    } elseif ($mode === 'unmodded') {
        $count = $fluent->from('torrents')
            ->select(null)
            ->select('COUNT(*) AS count')
            ->where('checked_when = 0')
            ->fetch('count');

        if (!$count) {
            $HTMLOUT = $links . stdmsg($lang['mtor_sorry'], $lang['mtor_no_un-modded_torrents_detected'], 'top20');
            $title = $lang['mtor_add_done'];
        } else {
            $put = $lang['mtor_unmodded_torrent'] . plural($count);
            $perpage = 15;
            $pager = pager($perpage, $count, "{$_SERVER['PHP_SELF']}?tool=modded_torrents&type={$mode}&");
            $HTMLOUT .= $links;
            $HTMLOUT .= "
                <div class='has-text-centered'>
                    <h1>{$lang['mtor_summary']}</h1>
                    <p class='has-text-centered bottom10'>$put</p>" . ($count > $perpage ? $pager['pagertop'] : '') . '
                </div>';
            $heading = '
                <tr>
                   <th>' . $lang['mtor_torrent'] . '</th>
                   <th>' . $lang['mtor_added'] . '</th>
                   <th>' . $lang['mtor_edit'] . ' ' . $lang['mtor_torrent'] . '</th>
                </tr>';
            $data = $fluent->from('torrents')
                ->select(null)
                ->select('id')
                ->select('name')
                ->select('added')
                ->where('checked_when = 0')
                ->limit("{$pager['pdo']}")
                ->fetchAll();

            $HTMLOUT .= main_table(do_sort($data), $heading);
            $HTMLOUT .= $count > $perpage ? $pager['pagerbottom'] : '';
            $title = $put;
        }
    } else {
        $HTMLOUT .= $links . main_div('<h3>' . $lang['mtor_no_torrents_have_been_modded'] . ' ' . $mode . '.</h3>', 'top20');
        $title = $lang['mtor_no_torrents_modded'] . " $mode";
    }
    echo stdhead($title) . wrapper($HTMLOUT) . stdfoot();
    die();
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $where = false;
    $ts = strtotime(date('F', time()) . ' ' . date('Y', time()));
    $last_day = date('t', $ts);
    $whom = !empty($_POST['username']) ? $_POST['username'] : false;
    $when = !empty($_POST['time']) && $_POST['time'] > 0 && $_POST['time'] < $last_day ? (int) $_POST['time'] : false;
    $date = isset($_POST['date']) ? $_POST['date'] : false;
    $perpage = 15;

    if ($when && $when > 0) {
        $when = TIME_NOW - ($when * 24 * 60 * 60);
    }
    if ($whom || $when || $date) {
        if ($date && $whom) {
            $beginOfDay = strtotime('midnight', strtotime($date));
            $endOfDay = strtotime('midnight', strtotime($date) + 86400);
            $data = $fluent->from('torrents AS t')
                ->select(null)
                ->select('t.id')
                ->select('t.name')
                ->select('t.checked_by')
                ->select('t.checked_when')
                ->where('LOWER(u.username) = ?', $whom)
                ->where('t.checked_when >= ?', $beginOfDay)
                ->where('t.checked_when < ?', $endOfDay)
                ->innerJoin('users AS u ON t.checked_by = u.username = ?', $whom)
                ->orderBy('checked_when DESC')
                ->fetchAll();

            $text = "by <u>$_POST[username]</u> on $date";
            $title = "$_POST[username] : " . $lang['mtor_modded_torrents'] . " on $date";
        } elseif ($date) {
            $beginOfDay = strtotime('midnight', strtotime($date));
            $endOfDay = strtotime('midnight', strtotime($date) + 86400);
            $data = $fluent->from('torrents')
                ->select(null)
                ->select('id')
                ->select('name')
                ->select('checked_by')
                ->select('checked_when')
                ->where('checked_when >= ?', $beginOfDay)
                ->where('checked_when < ?', $endOfDay)
                ->orderBy('checked_when DESC')
                ->fetchAll();

            $text = "on $date";
            $title = $lang['mtor_modded_torrents'] . " on $date";
        } elseif ($whom && $when) {
            $data = $fluent->from('torrents AS t')
                ->select(null)
                ->select('t.id')
                ->select('t.name')
                ->select('t.checked_by')
                ->select('t.checked_when')
                ->where('LOWER(u.username) = ?', $whom)
                ->where('t.checked_when >= ?', $when)
                ->innerJoin('users AS u ON t.checked_by = u.username = ?', $whom)
                ->orderBy('checked_when DESC')
                ->fetchAll();

            $text = "by <u>$_POST[username]</u> within the last " . ($_POST['time'] == 1 ? '<u>1 day.</u>' : '<u>' . $_POST['time'] . ' days.</u>');
            $title = "$_POST[username] : " . $lang['mtor_modded_torrents'] . ' ' . $lang['mtor_from'] . " $_POST[time] days ago";
        } elseif ($when) {
            $data = $fluent->from('torrents')
                ->select(null)
                ->select('id')
                ->select('name')
                ->select('checked_by')
                ->select('checked_when')
                ->where('checked_when >= ?', $when)
                ->orderBy('checked_when DESC')
                ->fetchAll();

            $text = 'from the past ' . ($_POST['time'] == 1 ? '<u>1 day.</u>' : '<u>' . $_POST['time'] . ' days.</u>');
            $title = "$_POST[username] : " . $lang['mtor_modded_torrents'] . ' ' . $lang['mtor_from'] . " $_POST[time] days ago";
        } elseif ($whom) {
            $data = $fluent->from('torrents AS t')
                ->select(null)
                ->select('t.id')
                ->select('t.name')
                ->select('t.checked_by')
                ->select('t.checked_when')
                ->where('LOWER(u.username) = ?', $whom)
                ->innerJoin('users AS u ON t.checked_by = u.username = ?', $whom)
                ->orderBy('checked_when DESC')
                ->fetchAll();

            $text = "by <u>$_POST[username]</u>";
            $title = "$_POST[username] : " . $lang['mtor_modded_torrents'] . '';
        }
        $count = count($data);
        if (!$count) {
            $HTMLOUT .= stdmsg($lang['mtor_sorry'], $lang['mtor_no_torrents_have_been_modded'], 'top20');
        } else {
            $HTMLOUT = $trim = '';
            if (isset($data)) {
                $data = do_sort($data);
                $HTMLOUT .= $links;
                $HTMLOUT .= "
                <div class='has-text-centered'>
                    <h2>" . $lang['mtor_summary'] . '</h2>
                </div>';
                $heading = '
                    <tr>
                        <th>' . $lang['mtor_torrent'] . '</th>
                        <th>' . $lang['mtor_modded_by'] . '</th>
                        <th>' . $lang['mtor_time'] . '</th>
                    </tr>';
                $HTMLOUT .= main_table($data, $heading);
            }
        }
    } else {
        stderr($lang['mtor_error'], '' . $lang['mtor_empty_data_supplied'] . ' ! ' . $lang['mtor_please_try_again'] . '');
    }
    echo stdhead($title) . wrapper($HTMLOUT) . stdfoot();
    die();
}
$HTMLOUT = '';
$HTMLOUT .= $links . "
    <h1 class='has-text-centered'>" . $lang['mtor_modded_torrents_complete_panel'] . '</h1>';

$HTMLOUT .= main_div("
    <div class='has-text-centered padding20'>
        <form method='post' action='{$_SERVER['PHP_SELF']}?tool=modded_torrents&amp;type=search_modded'>
            <div class='columns is-gapless level'>
                <div class='column has-text-right'>
                    <label for='username' class='right10'>" . $lang['mtor_username'] . "</label>
                </div>
                <div class='column has-text-left'>
                    <input type='text' placeholder='" . $lang['mtor_username'] . "' name='username' id='username'>
                </div>
            </div>
            <div class='columns is-gapless level'>
                <div class='column has-text-right'>
                    <label for='time' class='right10'>" . $lang['mtor_from'] . ' ' . $lang['mtor_numbers_of_days_ago'] . "</label>
                </div>
                <div class='column has-text-left'>
                    <input type='text' placeholder='" . $lang['mtor_day'] . "' name='time' id='time'>
                </div>
            </div>
            <div class='columns is-gapless level'>
                <div class='column has-text-right'>
                    <label for='date' class='right10'>" . $lang['mtor_on_which_day'] . "</label>
                </div>
                <div class='column has-text-left'>
                    <input type='date' name='date'>
                </div>
            </div>
            <button type='submit' class='button is-small'>" . $lang['mtor_search'] . '</button>
        </form>
  </div>');

echo stdhead($lang['mtor_modded_torrents_panel']) . wrapper($HTMLOUT) . stdfoot();
