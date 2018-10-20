<?php

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'user_functions.php';
require_once INCL_DIR . 'html_functions.php';
require_once INCL_DIR . 'pager_functions.php';
check_user_status();
global $CURUSER, $site_config, $cache, $session, $message_stuffs;

$HTMLOUT = '';
$lang = array_merge(load_language('global'), load_language('bugs'));
$possible_actions = [
    'viewbug',
    'bugs',
    'add',
];
$action = (isset($_GET['action']) ? htmlsafechars($_GET['action']) : (isset($_POST['action']) ? htmlsafechars($_POST['action']) : ''));
if (!in_array($action, $possible_actions)) {
    stderr('Error', 'A ruffian that will swear, drink, dance, revel the night, rob, murder and commit the oldest of ins the newest kind of ways.');
}
$dt = TIME_NOW;
if ($action === 'viewbug') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if ($CURUSER['class'] < UC_MAX) {
            stderr("{$lang['stderr_error']}", "{$lang['stderr_only_coder']}");
        }
        $id = isset($_POST['id']) ? (int) $_POST['id'] : '';
        $status = isset($_POST['status']) ? htmlsafechars($_POST['status']) : '';
        if ($status === 'na') {
            stderr("{$lang['stderr_error']}", "{$lang['stderr_no_na']}");
        }
        if (!$id || !is_valid_id($id)) {
            stderr("{$lang['stderr_error']}", "{$lang['stderr_invalid_id']}");
        }
        $query1 = sql_query('SELECT b.*, u.username, u.uploaded FROM bugs AS b LEFT JOIN users AS u ON b.sender = u.id WHERE b.id = ' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
        while ($q1 = mysqli_fetch_assoc($query1)) {
            switch ($status) {
                case 'fixed':
                    $msg = 'Hello ' . htmlsafechars($q1['username']) . ".\nYour bug: [b]" . htmlsafechars($q1['title']) . "[/b] has been treated by one of our coder, and is done.\n\nWe would to thank you and therefore we have added [b]2 GB[/b] to your upload total :].\n\nBest regards, {$site_config['site_name']}'s coders.\n";
                    $uq = 'UPDATE users SET uploaded = uploaded +' . 1024 * 1024 * 1024 * 2 . ' WHERE id = ' . sqlesc($q1['sender']);
                    $update['uploaded'] = ($q1['uploaded'] + 1024 * 1024 * 1024 * 2);
                    $cache->update_row('user' . $q1['sender'], [
                        'uploaded' => $update['uploaded'],
                    ], $site_config['expires']['user_cache']);
                    break;

                case 'ignored':
                    $msg = 'Hello ' . htmlsafechars($q1['username']) . ".\nYour bug: [b]" . htmlsafechars($q1['title']) . "[/b] has been ignored by one of our coder.\n\nPossibly it was not a bug.\n\nBest regards, {$site_config['site_name']}'s coders.\n";
                    $uq = '';
                    break;
            }
            sql_query($uq) or sqlerr(__FILE__, __LINE__);
            $msgs_buffer[] = [
                'sender' => 0,
                'receiver' => $q1['sender'],
                'added' => $dt,
                'msg' => $msg,
                'subject' => 'Bug Report',
            ];
            $message_stuffs->insert($msgs_buffer);
            sql_query('INSERT INTO messages (sender, receiver, added, msg) VALUES (0, ' . sqlesc($q1['sender']) . ', ' . $dt . ", {$msg})") or sqlerr(__FILE__, __LINE__);
            sql_query('UPDATE bugs SET status=' . sqlesc($status) . ', staff=' . sqlesc($CURUSER['id']) . ' WHERE id = ' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
            $cache->delete('bug_mess_');
        }
        header("location: bugs.php?action=viewbug&id={$id}");
    }
    $id = isset($_GET['id']) ? (int) $_GET['id'] : '';
    if (!$id || !is_valid_id($id)) {
        stderr("{$lang['stderr_error']}", "{$lang['stderr_invalid_id']}");
    }
    if ($CURUSER['class'] < UC_STAFF) {
        stderr("{$lang['stderr_error']}", 'Only staff can view bugs.');
    }
    $as = sql_query('SELECT b.*, u.username, u.class, staff.username AS st, staff.class AS stclass FROM bugs AS b LEFT JOIN users AS u ON b.sender = u.id LEFT JOIN users AS staff ON b.staff = staff.id WHERE b.id =' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
    while ($a = mysqli_fetch_assoc($as)) {
        $title = htmlsafechars($a['title']);
        $added = get_date($a['added'], '', 0, 1);
        $addedby = format_username($a['sender']) . '<i>(' . get_user_class_name($a['class']) . ')</i>';
        switch ($a['priority']) {
            case 'low':
                $priority = "<span style='color: green;'>{$lang['low']}</span>";
                break;

            case 'high':
                $priority = "<span class='has-text-danger'>{$lang['high']}</span>";
                break;

            case 'veryhigh':
                $priority = "<span class='has-text-danger'><b><u>{$lang['veryhigh']}</u></b></span>";
                break;
        }
        $problem = htmlsafechars($a['problem']);
        switch ($a['status']) {
            case 'fixed':
                $status = "<span style='color: green;'><b>{$lang['fixed']}</b></span>";
                break;

            case 'ignored':
                $status = "<span style='color: #FF8C00;'><b>{$lang['ignored']}</b></span>";
                break;

            default:
                $status = "<select name='status'>
          <option value='na'>{$lang['select_one']}</option>
          <option value='fixed'>{$lang['fix_problem']}</option>
          <option value='ignored'>{$lang['ignore_problem']}</option>
        </select>";
        }
        switch ($a['staff']) {
            case 0:
                $by = '';
                break;

            default:
                $by = format_username($a['staff']) . ' <i>(' . get_user_class_name($a['stclass']) . ')</i>';
                break;
        }
        $HTMLOUT .= "<form method='post' action='{$_SERVER['PHP_SELF']}?action=viewbug'>
      <input type='hidden' name='id' value='" . (int) $a['id'] . "'/>
      <table class='table table-bordered table-striped'>
      <tr><td class='rowhead'>{$lang['title']}:</td><td>{$title}</td></tr>
      <tr><td class='rowhead'>{$lang['added']} / {$lang['by']}</td><td>{$added} / {$addedby}</td></tr>
      <tr><td class='rowhead'>{$lang['priority']}</td><td>" . $priority . "</td></tr>
      <tr><td class='rowhead'>{$lang['problem_bug']}</td><td><textarea cols='60' rows='10' readonly='readonly'>{$problem}</textarea></td></tr>
      <tr><td class='rowhead'>{$lang['status']} / {$lang['by']}</td><td>{$status} - {$by}</td></tr>";
        if ($a['status'] === 'na') {
            $HTMLOUT .= "<tr><td colspan='2'><input type='submit' value='{$lang['submit_btn_fix']}' class='button is-small'/></td></tr>\n";
        }
    }
    $HTMLOUT .= "</table></form><a href='bugs.php?action=bugs'>{$lang['go_back']}</a>\n";
} elseif ($action === 'bugs') {
    if ($CURUSER['class'] < UC_STAFF) {
        stderr("{$lang['stderr_error']}", "{$lang['stderr_only_staff_can_view']}");
    }
    $search_count = sql_query('SELECT COUNT(id) FROM bugs');
    $row = mysqli_fetch_array($search_count);
    $count = $row[0];
    $perpage = 10;
    $pager = pager($perpage, $count, 'bugs.php?action=bugs&amp;');
    $res = sql_query("SELECT b.*, u.username, staff.username AS staffusername FROM bugs AS b LEFT JOIN users AS u ON b.sender = u.id LEFT JOIN users AS staff ON b.staff = staff.id ORDER BY b.id DESC {$pager['limit']}") or sqlerr(__FILE__, __LINE__);
    $r = sql_query("SELECT * FROM bugs WHERE status = 'na'") or sqlerr(__FILE__, __LINE__);
    if (mysqli_num_rows($res) > 0) {
        $count = mysqli_num_rows($r);
        $HTMLOUT .= $count > $perpage ? $pager['pagertop'] : '';
        $HTMLOUT .= "
      <h1 class='has-text-centered'>" . sprintf($lang['h1_count_bugs'], $count, ($count > 1 ? 's' : '')) . "</h1>
      <div class='has-text-centered size_3'>{$lang['delete_when']}</div>
      <table class='table table-bordered table-striped'><tr>
      <td class='colhead'>{$lang['title']}</td>
      <td class='colhead'>{$lang['added']} / {$lang['by']}</td>
      <td class='colhead'>{$lang['priority']}</td>
      <td class='colhead'>{$lang['status']}</td>
      <td class='colhead'>{$lang['coder']}</td>
      </tr>";
        while ($q1 = mysqli_fetch_assoc($res)) {
            switch ($q1['priority']) {
                case 'low':
                    $priority = "<span style='color: green;'>{$lang['low']}</span>";
                    break;

                case 'high':
                    $priority = "<span class='has-text-danger'>{$lang['high']}</span>";
                    break;

                case 'veryhigh':
                    $priority = "<span class='has-text-danger'><b><u>{$lang['veryhigh']}</u></b></span>";
                    break;
            }
            switch ($q1['status']) {
                case 'fixed':
                    $status = "<span style='color: green;'><b>{$lang['fixed']}</b></span>";
                    break;

                case 'ignored':
                    $status = "<span style='color: #FF8C00;'><b>{$lang['ignored']}</b></span>";
                    break;

                default:
                    $status = "<span style='color: black;'><b>N/A</b></span>";
                    break;
            }
            $HTMLOUT .= "<tr>
          <td><a href='?action=viewbug&amp;id=" . (int) $q1['id'] . "'>" . htmlsafechars($q1['title']) . "</a></td>
          <td nowrap='nowrap'>" . get_date($q1['added'], 'TINY') . ' / ' . format_username($q1['sender']) . "</td>
          <td>{$priority}</td>
          <td>{$status}</td>
      <td>" . ($q1['status'] != 'na' ? format_username($q1['staff']) : '---') . '</td>
      </tr>';
        }
        $HTMLOUT .= '</table>';
        $HTMLOUT .= $count > $perpage ? $pager['pagerbottom'] : '';
    } else {
        $session->set('is-warning', $lang['no_bugs']);
        header('Location: index.php');
        die();
    }
} elseif ($action === 'add') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $title = htmlsafechars($_POST['title']);
        $priority = htmlsafechars($_POST['priority']);
        $problem = htmlsafechars($_POST['problem']);
        if (empty($title) || empty($priority) || empty($problem)) {
            stderr("{$lang['stderr_error']}", "{$lang['stderr_missing']}");
        }
        if (strlen($problem) < 20) {
            stderr("{$lang['stderr_error']}", "{$lang['stderr_problem_20']}");
        }
        if (strlen($title) < 10) {
            stderr("{$lang['stderr_error']}", "{$lang['stderr_title_10']}");
        }
        $q1 = sql_query('INSERT INTO bugs (title, priority, problem, sender, added) VALUES (' . sqlesc($title) . ', ' . sqlesc($priority) . ', ' . sqlesc($problem) . ', ' . sqlesc($CURUSER['id']) . ', ' . $dt . ')') or sqlerr(__FILE__, __LINE__);
        $cache->delete('bug_mess_');
        if ($q1) {
            stderr("{$lang['stderr_sucess']}", sprintf($lang['stderr_sucess_2'], $priority));
        } else {
            stderr("{$lang['stderr_error']}", "{$lang['stderr_something_is_wrong']}");
        }
    }
    $HTMLOUT .= "<form method='post' action='bugs.php?action=add'>
                  <table class='table table-bordered table-striped'>
                  <tr><td class='rowhead'>{$lang['title']}:</td><td><input type='text' name='title' size='60'/><br>{$lang['proper_title']}</td></tr>
                  <tr><td class='rowhead'>{$lang['problem_bug']}:</td><td><textarea cols='60' rows='10' name='problem'></textarea><br>{$lang['describe_problem']}</td></tr>
                  <tr><td class='rowhead'>{$lang['priority']}:</td><td><select name='priority'>
                  <option value='0'>{$lang['select_one']}</option>
                  <option value='low'>{$lang['low']}</option>
                  <option value='high'>{$lang['high']}</option>
                  <option value='veryhigh'>{$lang['veryhigh']}</option>
                  </select>
                  <br>{$lang['only_veryhigh_when']}</td></tr>
                  <tr><td colspan='2'><input type='submit' value='{$lang['submit_btn_send']}' class='button is-small'/></td></tr>
                  </table></form>";
}
echo stdhead("{$lang['header']}") . wrapper($HTMLOUT) . stdfoot();
