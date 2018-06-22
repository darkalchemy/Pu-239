<?php

require_once INCL_DIR . 'pager_functions.php';
require_once INCL_DIR . 'user_functions.php';
require_once INCL_DIR . 'html_functions.php';
require_once CLASS_DIR . 'class_check.php';
require_once INCL_DIR . 'function_account_delete.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
global $CURUSER, $site_config, $lang, $cache;

$lang = array_merge($lang, load_language('inactive'));

use Nette\Mail\Message;
use Nette\Mail\SendmailMailer;

$HTMLOUT = '';
$record_mail = true; // set this true or false . If you set this true every time whene you send a mail the time , userid , and the number of mail sent will be recorded
$days = 30; //number of days of inactivity
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? htmlsafechars(trim($_POST['action'])) : '';
    if (empty($_POST['userid']) && (($action === 'deluser') || ($action === 'mail'))) {
        stderr($lang['inactive_error'], "{$lang['inactive_selectuser']}");
    }

    if ($action === 'deluser' && (!empty($_POST['userid']))) {
        $res = sql_query('SELECT id, email, modcomment, username, added, last_access FROM users WHERE id IN (' . implode(', ', array_map('sqlesc', $_POST['userid'])) . ') ORDER BY last_access DESC ');
        $count = mysqli_num_rows($res);
        while ($arr = mysqli_fetch_array($res)) {
            $userid = (int) $arr['id'];
            $username = htmlsafechars($arr['username']);
            $res_del = sql_query(account_delete($userid)) or sqlerr(__FILE__, __LINE__);
            if (mysqli_affected_rows($GLOBALS['___mysqli_ston']) !== false) {
                $cache->delete('user' . $userid);
                write_log("User: $username Was deleted by {$CURUSER['username']}");
            }
        }
        stderr($lang['inactive_success'], "{$lang['inactive_deleted']} <a href='" . $site_config['baseurl'] . "/staffpanel.php?tool=inactive>{$lang['inactive_back']}</a>");
    }

    if ($action === 'disable' && (!empty($_POST['userid']))) {
        sql_query("UPDATE users SET enabled='no' WHERE id IN (" . implode(', ', array_map('sqlesc', $_POST['userid'])) . ') ');
        stderr($lang['inactive_success'], "{$lang['inactive_disabled']} <a href='" . $site_config['baseurl'] . "/staffpanel.php?tool=inactive>{$lang['inactive_back']}</a>");
    }

    if ($action === 'mail' && (!empty($_POST['userid']))) {
        $res = sql_query('SELECT id, email, modcomment, username, added, last_access FROM users WHERE id IN (' . implode(', ', array_map('sqlesc', $_POST['userid'])) . ') ORDER BY last_access DESC ');
        $count = mysqli_num_rows($res);
        while ($arr = mysqli_fetch_array($res)) {
            $id = (int) $arr['id'];
            $username = htmlsafechars($arr['username']);
            $added = get_date($arr['added'], 'DATE');
            $last_access = get_date($arr['last_access'], 'DATE');
            $body = "<html>
<head>
    <meta http-equiv='Content-Type' content='text/html; charset=utf-8'>
    <title>{$lang['inactive_youracc']}</title>
</head>
<body>
<p>{$lang['inactive_hey']} $username,</p>
<p>{$lang['inactive_youracc']} {$site_config['site_name']} {$lang['inactive_marked']} {$site_config['site_name']}{$lang['inactive_plogin']}<br>
{$lang['inactive_yourusername']} $username<br>
{$lang['inactive_created']} $added<br>
{$lang['inactive_lastaccess']} $last_access<br>
{$lang['inactive_loginat']} {$site_config['baseurl']}/login.php<br>
{$lang['inactive_forgotten']} {$site_config['baseurl']}/resetpw.php<br>
{$lang['inactive_welcomeback']} {$site_config['site_name']}</p>
</body>
</html>";

            $mail = new Message();
            $mail->setFrom("{$site_config['site_email']}", "{$site_config['chatBotName']}")
                ->addTo($arr['email'])
                ->setReturnPath($site_config['site_email'])
                ->setSubject("{$lang['inactive_youracc']}{$site_config['site_name']}!")
                ->setHtmlBody($body);

            $mailer = new SendmailMailer();
            $mailer->commandArgs = "-f{$site_config['site_email']}";
            $mailer->send($mail);
        }

        if ($record_mail) {
            $date = TIME_NOW;
            $userid = (int) $CURUSER['id'];
            if ($count > 0 && $mail) {
                sql_query('UPDATE avps SET value_i=' . sqlesc($date) . ', value_u=' . sqlesc($count) . ', value_s=' . sqlesc($userid) . " WHERE arg='inactivemail'") or sqlerr(__FILE__, __LINE__);
            }
        }

        if ($mail) {
            stderr($lang['inactive_success'], "{$lang['inactive_msgsent']} <a href='" . $site_config['baseurl'] . "/staffpanel.php?tool=inactive'>{$lang['inactive_back']}</a>");
        } else {
            stderr($lang['inactive_error'], "{$lang['inactive_tryagain']}");
        }
    }
}
$dt = TIME_NOW - ($days * 86400);
$res = sql_query('SELECT COUNT(id) FROM users WHERE last_access<' . sqlesc($dt) . " AND status = 'confirmed' AND enabled = 'yes' ORDER BY last_access DESC");
$row = mysqli_fetch_array($res);
$count = $row[0];
$perpage = 15;
$pager = pager($perpage, $count, 'staffpanel.php?tool=inactive&amp;');
$res = sql_query('SELECT id,username,class,email,uploaded,downloaded,last_access FROM users WHERE last_access < ' . sqlesc($dt) . " AND status='confirmed' AND enabled='yes' ORDER BY last_access DESC {$pager['limit']}") or sqlerr(__FILE__, __LINE__);
$count_inactive = mysqli_num_rows($res);
if ($count_inactive > 0) {
    //if ($count > $perpage)
    $HTMLOUT .= $pager['pagertop'];
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
    $HTMLOUT .= "<div class='row'><div class='col-md-12'>";
    $HTMLOUT .= '<h2>' . htmlsafechars($count) . "{$lang['inactive_accounts']} " . htmlsafechars($days) . " {$lang['inactive_days']}</h2>
    <form method='post' action='staffpanel.php?tool=inactive&amp;action=inactive'>
    <table class='table table-bordered'>
    <tr>
    <td class='colhead'>{$lang['inactive_username']}</td>
    <td class='colhead'>{$lang['inactive_class']}</td>
    <td class='colhead'>{$lang['inactive_mail']}</td>
    <td class='colhead'>{$lang['inactive_ratio']}</td>
    <td class='colhead'>{$lang['inactive_lastseen']}</td>
    <td class='colhead'>{$lang['inactive_x']}</td></tr>";
    while ($arr = mysqli_fetch_assoc($res)) {
        $ratio = (member_ratio($arr['uploaded'], $site_config['ratio_free'] ? '0' : $arr['downloaded']));
        $last_seen = (($arr['last_access'] == '0') ? 'never' : '' . get_date($arr['last_access'], 'DATE') . '&#160;');
        $class = get_user_class_name($arr['class']);
        $HTMLOUT .= '<tr>
        <td>' . format_username($arr['id']) . '</td>
        <td>' . $class . "</td>
        <td style='max-width:130px;overflow: hidden;text-overflow: ellipsis;white-space: nowrap;'><a href='mailto:" . htmlsafechars($arr['email']) . "'>" . htmlsafechars($arr['email']) . '</a></td>
        <td>' . $ratio . '</td>
        <td>' . $last_seen . "</td>
        <td bgcolor='#FF0000'><input type='checkbox' name='userid[]' value='" . (int) $arr['id'] . "' /></td></tr>
        ";
    }
    $HTMLOUT .= "<tr>
    <td colspan='6' class='colhead'>
    <select name='action'>
    <option value='mail'>{$lang['inactive_sendmail']}</option>
    <option value='deluser' " . ($CURUSER['class'] < UC_ADMINISTRATOR ? 'disabled' : '') . ">{$lang['inactive_deleteusers']}</option>
    <option value='disable'>{$lang['inactive_disaccounts']}</option>
    </select>&#160;&#160;<input type='submit' name='submit' value='{$lang['inactive_apchanges']}' />&#160;&#160;<input type='button' value='Check all' onclick='this.value=check(form)' /></td></tr>";
    if ($record_mail) {
        $ress = sql_query("SELECT avps.value_s AS userid, avps.value_i AS last_mail, avps.value_u AS mails, users.username FROM avps LEFT JOIN users ON avps.value_s=users.id WHERE avps.arg='inactivemail' LIMIT 1");
        $date = mysqli_fetch_assoc($ress);
        if ($date['last_mail'] > 0) {
            $HTMLOUT .= "<tr><td colspan='6' class='colhead' style='color:red;'>{$lang['inactive_lastmail']} " . format_username($date['userid']) . " {$lang['inactive_on']} <b>" . get_date($date['last_mail'], 'DATE') . ' -  ' . $date['mails'] . "</b>{$lang['inactive_email']} " . ($date['mails'] > 1 ? 's' : '') . "  {$lang['inactive_sent']}</td></tr>";
        }
    }
    $HTMLOUT .= '</table></form>';
    $HTMLOUT .= '</div></div>';
} else {
    $HTMLOUT .= "<h2 class='has-text-centered margin20'>{$lang['inactive_noaccounts']} " . $days . " {$lang['inactive_days']}</h2>";
}
//if ($count > $perpage)
$HTMLOUT .= $pager['pagerbottom'];
echo stdhead($lang['inactive_users']) . wrapper($HTMLOUT) . stdfoot();
