<?php

declare(strict_types = 1);

use Pu239\Session;

require_once INCL_DIR . 'function_pager.php';
require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_html.php';
require_once CLASS_DIR . 'class_check.php';
require_once INCL_DIR . 'function_account_delete.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
global $container, $CURUSER, $site_config;

$HTMLOUT = '';
$record_mail = true;
$days = 30;
$session = $container->get(Session::class);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? htmlsafechars($_POST['action']) : '';
    if (empty($_POST['userid']) && (($action === 'deluser') || ($action === 'mail'))) {
        $session->set('is-warning', _('For this to work you must select at least a user!'));
    }

    if ($action === 'deluser' && (!empty($_POST['userid']))) {
        $res = sql_query('SELECT id, email, modcomment, username, registered, last_access FROM users WHERE id IN (' . implode(', ', array_map('sqlesc', $_POST['userid'])) . ') ORDER BY last_access DESC ');
        $count = mysqli_num_rows($res);
        while ($arr = mysqli_fetch_array($res)) {
            $userid = (int) $arr['id'];
            $username = htmlsafechars($arr['username']);
            if (account_delete($userid)) {
                write_log("User: $username Was deleted by {$CURUSER['username']}");
            }
        }
        $session->set('is-success', _('You have successfully deleted the selected accounts!'));
    }

    if ($action === 'disable' && (!empty($_POST['userid']))) {
        sql_query('UPDATE users SET status = 2 WHERE id IN (' . implode(', ', array_map('sqlesc', $_POST['userid'])) . ') ');
        $session->set('is-success', _('You have successfully disabled the selected accounts!'));
    }

    if ($action === 'mail' && (!empty($_POST['userid']))) {
        $res = sql_query('SELECT id, email, modcomment, username, registered, last_access FROM users WHERE id IN (' . implode(', ', array_map('sqlesc', $_POST['userid'])) . ') ORDER BY last_access DESC ');
        $count = mysqli_num_rows($res);
        while ($arr = mysqli_fetch_array($res)) {
            $id = (int) $arr['id'];
            $username = htmlsafechars($arr['username']);
            $added = get_date((int) $arr['registered'], 'DATE');
            $last_access = get_date((int) $arr['last_access'], 'DATE');
            $body = doc_head(_('Your account at ')) . '
</head>
<body>
<p>' . _('Hey') . " $username,</p>
<p>" . _('Your account at ') . " {$site_config['site']['name']} " . _(' has been marked as inactive and will be deleted. If you wish to remain a member at') . " {$site_config['site']['name']}" . _(', please login.') . '<br>
' . _('Your username is: ') . " $username<br>
" . _('And was created: ') . " $added<br>
" . _('Last accessed: ') . " $last_access<br>
" . _('Login at: ') . " {$site_config['paths']['baseurl']}/login.php<br>
" . _('If you have forgotten your password you can retrieve it at') . " {$site_config['paths']['baseurl']}/resetpw.php<br>
" . _('Welcome back!') . " {$site_config['site']['name']}</p>
</body>
</html>";
            $mail = send_mail($arr['email'], _('Your account at ') . "{$site_config['site']['name']}!", $body, strip_tags($body));
        }

        if ($record_mail) {
            $date = TIME_NOW;
            $userid = (int) $CURUSER['id'];
            if ($count > 0 && $mail) {
                sql_query('UPDATE avps SET value_i=' . sqlesc($date) . ', value_u=' . sqlesc($count) . ', value_s=' . sqlesc($userid) . " WHERE arg='inactivemail'") or sqlerr(__FILE__, __LINE__);
            }
        }

        if ($mail) {
            $session->set('is-success', _('Messages sent.'));
        } else {
            $session->set('is-error', _('Try again'));
        }
    }
}
$dt = TIME_NOW - ($days * 86400);
$res = sql_query('SELECT COUNT(id) FROM users WHERE last_access<' . sqlesc($dt) . ' AND verified = 1 AND status = 0 ORDER BY last_access DESC');
$row = mysqli_fetch_array($res);
$count = (int) $row[0];
$perpage = 15;
$pager = pager($perpage, $count, 'staffpanel.php?tool=inactive&amp;');
$res = sql_query('SELECT id,username,class,email,uploaded,downloaded,last_access FROM users WHERE last_access < ' . sqlesc($dt) . " AND verified = 1 AND status = 0 ORDER BY last_access DESC {$pager['limit']}") or sqlerr(__FILE__, __LINE__);
$count_inactive = mysqli_num_rows($res);
if ($count_inactive > 0) {
    if ($count > $perpage) {
        $HTMLOUT .= $pager['pagertop'];
    }
    $HTMLOUT .= "<script>
    /*<![CDATA[*/
    var checkflag = 'false';
    function check(field) {
    if (checkflag == 'false') {
    for (i = 0; i < field.length; i++) {
    field[i].checked = true;}
    checkflag = 'true';
    return 'Uncheck All'; }
    else {
    for (i = 0; i < field.length; i++) {
    field[i].checked = false; }
    checkflag = 'false';
    return 'Check All'; }
    }
    /*]]>*/
    </script>";
    $HTMLOUT .= "
    <div class='row'><div class='col-md-12'>
    <h1 class='has-text-centered'>" . _fe('{0} accounts inactive for longer than {1} days.', $count, $days) . "</h1>
    <form method='post' action='staffpanel.php?tool=inactive&amp;action=inactive' enctype='multipart/form-data' accept-charset='utf-8'>
    <table class='table table-bordered'>
    <tr>
    <td class='colhead'>" . _('Username') . "</td>
    <td class='colhead'>" . _('Class') . "</td>
    <td class='colhead'>" . _('Email') . "</td>
    <td class='colhead'>" . _('Ratio') . "</td>
    <td class='colhead'>" . _('Last seen') . "</td>
    <td class='colhead'>" . _('X') . '</td></tr>';
    while ($arr = mysqli_fetch_assoc($res)) {
        $ratio = member_ratio((float) $arr['uploaded'], (float) $arr['downloaded']);
        $last_seen = (($arr['last_access'] == '0') ? 'never' : get_date((int) $arr['last_access'], 'DATE'));
        $class = get_user_class_name((int) $arr['class']);
        $HTMLOUT .= '<tr>
        <td>' . format_username((int) $arr['id']) . '</td>
        <td>' . $class . "</td>
        <td style='max-width:130px;overflow: hidden;text-overflow: ellipsis;white-space: nowrap;'><a href='mailto:" . htmlsafechars($arr['email']) . "'>" . htmlsafechars($arr['email']) . '</a></td>
        <td>' . $ratio . '</td>
        <td>' . $last_seen . "</td>
        <td><input type='checkbox' name='userid[]' value='" . (int) $arr['id'] . "'></td></tr>
        ";
    }
    $HTMLOUT .= "<tr>
    <td colspan='6' class='colhead'>
    <select name='action'>
    <option value='mail'>" . _('Send email') . "</option>
    <option value='deluser' " . (!has_access($CURUSER['class'], UC_ADMINISTRATOR, 'coder') ? 'disabled' : '') . '>' . _('Delete users') . "</option>
    <option value='disable'>" . _('Disable accounts') . "</option>
    </select>&#160;&#160;<input type='submit' name='submit' value='" . _('Apply Changes') . "' class='button is-small'>&#160;&#160;<input type='button' value='Check all' onclick='this.value=check(form)' class='button is-small'></td></tr>";
    if ($record_mail) {
        $ress = sql_query("SELECT avps.value_s AS userid, avps.value_i AS last_mail, avps.value_u AS mails, users.username FROM avps LEFT JOIN users ON avps.value_s=users.id WHERE avps.arg='inactivemail' LIMIT 1");
        $date = mysqli_fetch_assoc($ress);
        if ($date['last_mail'] > 0) {
            $HTMLOUT .= "<tr><td colspan='6' class='colhead has-text-danger'>" . _pfe('Last Email sent by {1} on {2} - {0} email sent', 'Last Email sent by {1} on {2} - {0} emails sent', $date['mails'], format_username((int) $date['userid']), get_date((int) $date['last_mail'], 'DATE')) . '</td></tr>';
        }
    }
    $HTMLOUT .= '</table></form>';
    $HTMLOUT .= '</div></div>';
} else {
    $HTMLOUT .= stdmsg(_('Awesome'), _fe('No account inactive for longer than {0} days.', $days));
}
if ($count > $perpage) {
    $HTMLOUT .= $pager['pagerbottom'];
}
$title = _('Inactive Users');
$breadcrumbs = [
    "<a href='{$site_config['paths']['baseurl']}/staffpanel.php'>" . _('Staff Panel') . '</a>',
    "<a href='{$_SERVER['PHP_SELF']}'>$title</a>",
];
echo stdhead($title, [], 'page-wrapper', $breadcrumbs) . wrapper($HTMLOUT) . stdfoot();
