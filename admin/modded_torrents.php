<?php
require_once INCL_DIR . 'user_functions.php';
require_once INCL_DIR . 'pager_functions.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
global $lang;

$lang = array_merge($lang, load_language('ad_modded_torrents'));
$modes = [
    'today',
    'yesterday',
    'unmodded',
];
$HTMLOUT = '';
/**
 * @param      $arr
 * @param bool $empty
 *
 * @return array|bool|string
 */
function do_sort($arr, $empty = false)
{
    global $lang;
    $count = $arr->num_rows;
    $ret_html = '';
    if ($empty) {
        if ($count < 1) {
            return false;
        }
        while ($res = mysqli_fetch_assoc($arr)) {
            $ret_html .= "
                <tr>
                    <td>
                        <a href='details.php?id=" . (int)$res['id'] . "'>" . htmlsafechars($res['name']) . "</a>
                    </td>
                    <td>" . get_date($res['added'], 'LONG') . "</td>
                    <td>
                        <a class='is-small button is-danger' href='edit.php?id=" . (int)$res['id'] . "' >" . $lang['mtor_edit'] . '</a>
                    </td>
                </tr>';
        }

        return $ret_html;
    }
    if ($count == 1) {
        $res = mysqli_fetch_assoc($arr);
        $users[ $res['checked_by'] ] = ((isset($users[ $res['checked_by'] ]) && $users[ $res['checked_by'] ] > 0) ? $users[ $res['checked_by'] ] + 1 : 1);
        $ret_html .= "
                <tr>
                    <td>
                        <a href='details.php?id=" . (int)$res['id'] . "'>" . htmlsafechars($res['name']) . "</a>
                    </td>
                    <td>" . format_username($res['checked_by']) . "</td>
                    <td>" . get_date($res['checked_when'], 'LONG') . '</td>
                </tr>';

        return [
            $users,
            $ret_html,
        ];
    } elseif ($count > 1) {
        while ($res = mysqli_fetch_assoc($arr)) {
            $users[ $res['checked_by'] ] = ((isset($users[ $res['checked_by'] ]) && $users[ $res['checked_by'] ] > 0) ? $users[ $res['checked_by'] ] + 1 : 1);
            $ret_html .= "
                <tr>
                    <td>
                        <a href='details.php?id=" . (int)$res['id'] . "'>" . htmlsafechars($res['name']) . "</a>
                    </td>
                    <td>" . format_username($res['checked_by']) . "</td>
                    <td>" . get_date($res['checked_when'], 'LONG') . '</td>
                </tr>';
        }

        return [
            $users,
            $ret_html,
        ];
    }
}

if (isset($_GET['type']) && in_array($_GET['type'], $modes)) {
    $mode = (isset($_GET['type']) && in_array($_GET['type'], $modes)) ? $_GET['type'] : stderr($lang['mtor_error'], '' . $lang['mtor_please_try_that_previous_request_again'] . '.');
    if ($mode == 'unmodded') {
        // TO GET ALL UNMODDED TORRENTS
        $res = sql_query("SELECT id, name, added FROM torrents WHERE checked_when = 0");
        $data = do_sort($res, true);
        if (!$data) {
            $HTMLOUT = '<h3>' . $lang['mtor_no_un-modded_torrents_detected'] . ' :D!</h3>';
            $title = $lang['mtor_add_done'];
        } else {
            $put = ($res->num_rows == 1 ? '1 ' . $lang['mtor_unmodded_torrent'] . '' : $res->num_rows . ' ' . $lang['mtor_all_unmodded_torrents'] . '');
            $perpage = 15;
            $pager = pager($perpage, $res->num_rows, "{$_SERVER['PHP_SELF']}?tool=modded_torrents&type={$mode}&");
            $HTMLOUT .= $pager['pagertop'];
            $HTMLOUT .= "
                <div>
                    <h4>" . $lang['mtor_summary'] . "</h4>$put
                </div>
                <table class='table table-bordered table-striped'>
                    <thead>
                        <tr>
                            <th>" . $lang['mtor_torrent'] . '</th>
                            <th>' . $lang['mtor_added'] . '</th>
                            <th>' . $lang['mtor_edit'] . ' ' . $lang['mtor_torrent'] . '</th>
                        </tr>' . $data . '
                    </thead>
                </table>';
            $HTMLOUT .= $pager['pagertop'];
            $title = $put;
        }
        //echo stdhead("".$lang['mtor_no_torrents_modded']."") . $HTMLOUT . stdfoot();
        //exit();
        // ENDS ALL UNMODDED TORRENTS
    } else {
        // IF ITS THE OTHER 2 CASES AS CHECKED BEFORE , NO NEED TO DO IT AGAIN
        $beginOfDay = strtotime('midnight', TIME_NOW);
        $endOfDay = strtotime('tomorrow', $beginOfDay) - 1;
        $_time = (($mode == 'yesterday') ? $endOfDay : $beginOfDay);
        $res = mysqli_fetch_row(sql_query("SELECT COUNT(*) FROM torrents WHERE checked_when >= $_time AND checked_by > 0"));
        $count = $res[0];
        if ($count < 1) {
            $HTMLOUT .= '<h3>' . $lang['mtor_no_torrents_have_been_modded'] . ' ' . $mode . '.</h3>';
            //echo stdhead("".$lang['mtor_no_torrents_modded']."") . $HTMLOUT . stdfoot();
            $title = '' . $lang['mtor_no_torrents_modded'] . " $mode";
            //exit();
        } else {
            $perpage = 15;
            $pager = pager($perpage, $count, "{$_SERVER['PHP_SELF']}?tool=modded_torrents&type={$mode}&");
            $HTMLOUT = $trim = '';
            $query = "SELECT tor.*, user.id as uid FROM torrents as tor INNER JOIN users as user ON user.id = tor.checked_by AND tor.checked_when >= $_time ORDER BY tor.checked_when DESC {$pager['limit']}";
            $data = do_sort(sql_query($query));
            if (isset($data[1])) {
                $HTMLOUT .= $pager['pagertop'];
                foreach ($data[0] as $k => $v) {
                    $trim .= "$k : $v ,";
                }
                $trim = trim($trim, ',');
                $HTMLOUT .= "
                <div class='has-text-centered'>
                    <h4>" . $lang['mtor_summary'] . "</h4>$trim
                </div>
                <table class='table table-bordered table-striped'>
                    <thead>
                        <tr>
                            <th>" . $lang['mtor_torrent'] . '</th>
                            <th>' . $lang['mtor_modded_by'] . '</th>
                            <th>' . $lang['mtor_time'] . '</th>
                        </tr>
                    </thead>
                    <tbody>
                        ' . $data[1] . '
                    </tbody>
                </table>';
                $HTMLOUT .= $pager['pagertop'];
            }
            $title = "$count " . $lang['mtor_modded_torrents'] . " $mode";
        }
    }
    echo stdhead($title) . $HTMLOUT . stdfoot();
    exit();
} elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $where = false;
    $ts = strtotime(date('F', time()) . ' ' . date('Y', time()));
    $last_day = date('t', $ts);
    $whom = (isset($_POST['username']) && !empty($_POST['username']) ? sqlesc($_POST['username']) : false);
    $when = (isset($_POST['time']) && $_POST['time'] > 0 && $_POST['time'] < $last_day ? (int)$_POST['time'] : false);
    $day = (isset($_POST['day']) && $_POST['day'] > 0 && $_POST['day'] < $last_day ? (int)$_POST['day'] : false);
    $month = (isset($_POST['month']) && $_POST['month'] > 0 && $_POST['month'] < 13 ? (int)$_POST['month'] : false);
    $year = (isset($_POST['year']) && $_POST['year'] <= date('Y', time()) ? (int)$_POST['year'] : false);
    if ($whom) {
        $whom = 'AND LOWER(tor.checked_by) = ' . strtolower($whom);
    }
    if ($when && $when > 0) {
        $when = 'AND tor.checked_when >= ' . (TIME_NOW - $when * 24 * 60 * 60);
    }
    if ($whom || $when || ($day && $month && $year)) {
        if ($day && $month && $year && $whom) {
            $beginOfDay = strtotime('midnight', strtotime("$day-$month-$year"));
            $endOfDay = strtotime('tomorrow', $beginOfDay) - 1;
            $query = "SELECT tor.*,user.id as uid FROM torrents as tor INNER JOIN users as user ON user.id = tor.checked_by $whom AND tor.checked_when > $beginOfDay AND tor.checked_when < $endOfDay ORDER BY tor.checked_when DESC";
            $text = "by <u>$_POST[username]</u> on $day / $month / $year";
            $title = "$_POST[username] : " . $lang['mtor_modded_torrents'] . " on $day / $month / $year";
        } elseif ($whom && $when) {
            $query = "SELECT tor.*,user.id as uid FROM torrents as tor INNER JOIN users as user ON user.id = tor.checked_by $whom $when ORDER BY tor.checked_when DESC";
            $text = "by <u>$_POST[username]</u> within the last " . ($_POST['time'] == 1 ? '<u>1 day.</u>' : '<u>' . $_POST['time'] . ' days.</u>');
            $title = "$_POST[username] : " . $lang['mtor_modded_torrents'] . ' ' . $lang['mtor_from'] . " $_POST[time] days ago";
        } elseif ($when) {
            $query = "SELECT tor.*,user.id as uid FROM torrents as tor INNER JOIN users as user ON user.id = tor.checked_by $when ORDER BY tor.checked_when DESC";
            $text = 'from the past ' . ($_POST['time'] == 1 ? '<u>1 day.</u>' : '<u>' . $_POST['time'] . ' days.</u>');
            $title = "$_POST[username] : " . $lang['mtor_modded_torrents'] . ' ' . $lang['mtor_from'] . " $_POST[time] days ago";
        } elseif ($whom) {
            $query = "SELECT tor.*,user.id as uid FROM torrents as tor INNER JOIN users as user ON user.id = tor.checked_by $whom ORDER BY tor.checked_when DESC";
            $text = "by <u>$_POST[username]</u>";
            $title = "$_POST[username] : " . $lang['mtor_modded_torrents'] . '';
        }
        $res = sql_query($query);
        $count = $res->num_rows;
        if ($count < 1) {
            $HTMLOUT .= "<h3>" . $lang['mtor_no_torrents_have_been_modded'] . " $text</h3>";
            $title = "$_POST[username] : " . $lang['mtor_no_modded_torrents'] . '';
        } else {
            $perpage = 15;
            $pager = pager($perpage, $count, "{$_SERVER['PHP_SELF']}?tool=modded_torrents&type={$mode}&");
            $HTMLOUT = $trim = '';
            $data = do_sort($res);
            if (isset($data[1])) {
                $HTMLOUT .= $pager['pagertop'];
                $trim = "$_POST[username] : $count";
                $HTMLOUT .= "
                <div class='has-text-centered'>
                    <h4>" . $lang['mtor_summary'] . "</h4>$trim
                </div>
                <table class='table table-bordered table-striped'>
                    <thead>
                        <tr>
                            <th>" . $lang['mtor_torrent'] . '</th>
                            <th>' . $lang['mtor_modded_by'] . '</th>
                            <th>' . $lang['mtor_time'] . '</th>
                        </tr>
                    <thead>
                    <tbody>
                        ' . $data[1] . '
                    </tbody>
                </table>';
                $HTMLOUT .= $pager['pagertop'];
            }
        }
    } else {
        stderr($lang['mtor_error'], '' . $lang['mtor_empty_data_supplied'] . ' ! ' . $lang['mtor_please_try_again'] . '');
    }
    echo stdhead($title) . $HTMLOUT . stdfoot();
    exit();
}
$HTMLOUT = '';
$HTMLOUT .= "
    <div class='container is-fluid portlet'>
        <div class='row-fluid bottom20'>
            <h1 class='has-text-centered'>" . $lang['mtor_modded_torrents_complete_panel'] . "</h1>
            <h4 class='has-text-centered'>" . $lang['mtor_quick_links'] . "</h4>
            <div class='flex flex-center flex-wrap'>
                <a class='button is-danger right10' href='{$_SERVER['PHP_SELF']}?tool={$_GET['tool']}&amp;type=today' data-toggle='tooltip' data-placement='top' title='Tooltip on top'>" . $lang['mtor_modded_today'] . "</a>
                <a class='button is-danger right10' href='{$_SERVER['PHP_SELF']}?tool={$_GET['tool']}&amp;type=yesterday' >" . $lang['mtor_modded_yesterday'] . "</a>
                <a class='button is-danger' href='{$_SERVER['PHP_SELF']}?tool={$_GET['tool']}&amp;type=unmodded' >" . $lang['mtor_all_unmodded_torrents'] . "</a>
            </div>

   <div class='has-text-centered'>

   <form method='post' action='{$_SERVER['PHP_SELF']}?tool=modded_torrents'>
    <div class='control-group'>
    <label class='control-label' >" . $lang['mtor_username'] . "</label>
    <div class='controls'>
      <input type='text' placeholder='" . $lang['mtor_username'] . "' name='username' />
    </div>
  </div>

  <div class='control-group'>
    <label class='control-label' >" . $lang['mtor_from'] . ' ' . $lang['mtor_numbers_of_days_ago'] . "</label>
    <div class='controls'>
      <input type='text' placeholder='" . $lang['mtor_day'] . "' name='time' />
    </div>
  </div>
<div class='control-group'>
    <label class='control-label' >" . $lang['mtor_on_which_day'] . "</label>
    <div class='controls'>
      <input type='text' placeholder='" . $lang['mtor_day'] . "' class='input-small' name='day' />
      <input type='text' class='input-small' placeholder='" . $lang['mtor_month'] . "' name='month' />
      <input type='text' class='input-small' placeholder='" . $lang['mtor_year'] . "' name='year' value='" . date('Y', time()) . "'/>
        <button type='submit' class='button'>" . $lang['mtor_search'] . '</button>

    </div>
  </div>

   </form>
  </div>

  </div>
  </div>';
echo stdhead($lang['mtor_modded_torrents_panel']) . $HTMLOUT . stdfoot();
