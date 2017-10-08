<?php
require_once realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..') . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'user_functions.php';
require_once INCL_DIR . 'password_functions.php';
check_user_status();
$HTMLOUT = $sure = '';
$lang = array_merge(load_language('global'), load_language('invite_code'));
$do = (isset($_GET['do']) ? htmlsafechars($_GET['do']) : (isset($_POST['do']) ? htmlsafechars($_POST['do']) : ''));
$valid_actions = [
    'create_invite',
    'delete_invite',
    'confirm_account',
    'view_page',
    'send_email',
];
$do = (($do && in_array($do, $valid_actions, true)) ? $do : '') or header('Location: ?do=view_page');
if ($CURUSER['suspended'] == 'yes') {
    stderr('Sorry', 'Your account is suspended');
}
/*
 * @action Main Page
 */
if ($do == 'view_page') {
    $query = sql_query('SELECT * FROM users WHERE invitedby = ' . sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);
    $rows = mysqli_num_rows($query);
    $HTMLOUT = '';
    $HTMLOUT .= "
<table class='table table-bordered table-striped'>
<tr class='table'>
<td colspan='7' class='colhead'><b>{$lang['invites_users']}</b></td></tr>";
    if (!$rows) {
        $HTMLOUT .= "<tr><td colspan='7' class='colhead'>{$lang['invites_nousers']}</td></tr>";
    } else {
        $HTMLOUT .= "<tr class='one'>
<td><b>{$lang['invites_username']}</b></td>
<td><b>{$lang['invites_uploaded']}</b></td>
" . ($site_config['ratio_free'] ? '' : "<td><b>{$lang['invites_downloaded']}</b></td>") . "
<td><b>{$lang['invites_ratio']}</b></td>
<td><b>{$lang['invites_status']}</b></td>
<td><b>{$lang['invites_confirm']}</b></td>
</tr>";
        for ($i = 0; $i < $rows; ++$i) {
            $arr = mysqli_fetch_assoc($query);
            if ($arr['status'] == 'pending') {
                $user = "<td>" . htmlsafechars($arr['username']) . '</td>';
            } else {
                $user = "<td><a href='{$site_config['baseurl']}/userdetails.php?id=" . (int)$arr['id'] . "'>" . format_username($arr) . '</a></td>';
            }
            $ratio = member_ratio($arr['uploaded'], $site_config['ratio_free'] ? '0' : $arr['downloaded']);
            if ($arr['status'] == 'confirmed') {
                $status = "<font color='#1f7309'>{$lang['invites_confirm1']}</font>";
            } else {
                $status = "<font color='#ca0226'>{$lang['invites_pend']}</font>";
            }
            $HTMLOUT .= "<tr class='one'>" . $user . "<td>" . mksize($arr['uploaded']) . '</td>' . ($site_config['ratio_free'] ? '' : "<td>" . mksize($arr['downloaded']) . '</td>') . "<td>" . $ratio . "</td><td>" . $status . '</td>';
            if ($arr['status'] == 'pending') {
                $HTMLOUT .= "<td><a href='?do=confirm_account&amp;userid=" . (int)$arr['id'] . '&amp;sender=' . (int)$CURUSER['id'] . "'><img src='{$site_config['pic_base_url']}confirm.png' alt='confirm' title='Confirm' border='0' /></a></td></tr>";
            } else {
                $HTMLOUT .= "<td>---</td></tr>";
            }
        }
    }
    $HTMLOUT .= '</table><br>';
    $select = sql_query('SELECT * FROM invite_codes WHERE sender = ' . sqlesc($CURUSER['id']) . " AND status = 'Pending'") or sqlerr(__FILE__, __LINE__);
    $num_row = mysqli_num_rows($select);
    $HTMLOUT .= "<table class='table table-bordered table-striped'>" . "<tr class='tabletitle'><td colspan='6' class='colhead'><b>{$lang['invites_codes']}</b></td></tr>";
    if (!$num_row) {
        $HTMLOUT .= "<tr class='one'><td colspan='1'>{$lang['invites_nocodes']}</td></tr>";
    } else {
        $HTMLOUT .= "<tr class='one'><td><b>{$lang['invites_send_code']}</b></td><td><b>{$lang['invites_date']}</b></td><td><b>{$lang['invites_delete']}</b></td><td><b>{$lang['invites_status']}</b></td></tr>";
        for ($i = 0; $i < $num_row; ++$i) {
            $fetch_assoc = mysqli_fetch_assoc($select);
            $HTMLOUT .= "<tr class='one'>
<td>" . htmlsafechars($fetch_assoc['code']) . " <a href='?do=send_email&amp;id=" . (int)$fetch_assoc['id'] . "'><img src='{$site_config['pic_base_url']}email.gif' border='0' alt='Email' title='Send Email' /></a></td>
<td>" . get_date($fetch_assoc['invite_added'], '', 0, 1) . '</td>';
            $HTMLOUT .= "<td><a href='?do=delete_invite&amp;id=" . (int)$fetch_assoc['id'] . '&amp;sender=' . (int)$CURUSER['id'] . "'><img src='{$site_config['pic_base_url']}del.png' border='0' alt='Delete'/></a></td>
<td>" . htmlsafechars($fetch_assoc['status']) . '</td></tr>';
        }
    }
    $HTMLOUT .= "<tr class='one'><td colspan='6'><form action='?do=create_invite' method='post'><input type='submit' value='{$lang['invites_create']}' /></form></td></tr>";
    $HTMLOUT .= '</table>';
    echo stdhead('Invites') . $HTMLOUT . stdfoot();
    die;
} /*
 * @action Create Invites
 */
elseif ($do == 'create_invite') {
    if ($CURUSER['invites'] <= 0) {
        stderr($lang['invites_error'], $lang['invites_noinvite']);
    }
    if ($CURUSER['invite_rights'] == 'no' || $CURUSER['suspended'] == 'yes') {
        stderr($lang['invites_deny'], $lang['invites_disabled']);
    }
    $res = sql_query('SELECT COUNT(id) FROM users') or sqlerr(__FILE__, __LINE__);
    $arr = mysqli_fetch_row($res);
    if ($arr[0] >= $site_config['invites']) {
        stderr($lang['invites_error'], $lang['invites_limit']);
    }
    $invite = make_password(16);
    sql_query('INSERT INTO invite_codes (sender, invite_added, code) VALUES (' . sqlesc((int)$CURUSER['id']) . ', ' . TIME_NOW . ', ' . sqlesc($invite) . ')') or sqlerr(__FILE__, __LINE__);
    sql_query('UPDATE users SET invites = invites - 1 WHERE id = ' . sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);
    $update['invites'] = ($CURUSER['invites'] - 1);
    $mc1->begin_transaction('MyUser_' . $CURUSER['id']);
    $mc1->update_row(false, [
        'invites' => $update['invites'],
    ]);
    $mc1->commit_transaction($site_config['expires']['curuser']); // 15 mins
    $mc1->begin_transaction('user' . $CURUSER['id']);
    $mc1->update_row(false, [
        'invites' => $update['invites'],
    ]);
    $mc1->commit_transaction($site_config['expires']['user_cache']); // 15 mins
    header('Location: ?do=view_page');
} /*
 * @action Send e-mail
 */
elseif ($do == 'send_email') {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $email = (isset($_POST['email']) ? htmlsafechars($_POST['email']) : '');
        $invite = (isset($_POST['code']) ? htmlsafechars($_POST['code']) : '');
        if (!$email) {
            stderr($lang['invites_error'], $lang['invites_noemail']);
        }
        $check = (mysqli_fetch_row(sql_query('SELECT COUNT(id) FROM users WHERE email = ' . sqlesc($email)))) or sqlerr(__FILE__, __LINE__);
        if ($check[0] != 0) {
            stderr('Error', 'This email address is already in use!');
        }
        if (!validemail($email)) {
            stderr($lang['invites_error'], $lang['invites_invalidemail']);
        }
        $inviter = htmlsafechars($CURUSER['username']);
        $body = <<<EOD
You have been invited to {$site_config['site_name']} by $inviter. They have
specified this address ($email) as your email. If you do not know this person, please ignore this email. Please do not reply.

This is a private site and you must agree to the rules before you can enter:

{$site_config['baseurl']}/useragreement.php

{$site_config['baseurl']}/rules.php

{$site_config['baseurl']}/faq.php

------------------------------------------------------------

To confirm your invitation, you have to follow this link and type the invite code:

{$site_config['baseurl']}/invite_signup.php

Invite Code: $invite

------------------------------------------------------------

After you do this, your inviter need's to confirm your account. 
We urge you to read the RULES and FAQ before you start using {$site_config['site_name']}.
EOD;
        $sendit = mail($email, "You have been invited to {$site_config['site_name']}", $body, "From: {$site_config['site_email']}", "-f{$site_config['site_email']}");
        if (!$sendit) {
            stderr($lang['invites_error'], $lang['invites_unable']);
        } else {
            stderr('', $lang['invites_confirmation']);
        }
    }
    $id = (isset($_GET['id']) ? (int)$_GET['id'] : (isset($_POST['id']) ? (int)$_POST['id'] : ''));
    if (!is_valid_id($id)) {
        stderr($lang['invites_error'], $lang['invites_invalid']);
    }
    $query = sql_query('SELECT * FROM invite_codes WHERE id = ' . sqlesc($id) . ' AND sender = ' . sqlesc($CURUSER['id']) . ' AND status = "Pending"') or sqlerr(__FILE__, __LINE__);
    $fetch = mysqli_fetch_assoc($query) or stderr($lang['invites_error'], $lang['invites_noexsist']);
    $HTMLOUT .= "<form method='post' action='?do=send_email'><table class='table table-bordered table-striped'>
<tr><td class='rowhead'>E-Mail</td><td><input type='text' size='40' name='email' /></td></tr><tr><td colspan='2'><input type='hidden' name='code' value='" . htmlsafechars($fetch['code']) . "' /></td></tr><tr><td colspan='2'><input type='submit' value='Send e-mail' class='btn' /></td></tr></table></form>";
    echo stdhead('Invites') . $HTMLOUT . stdfoot();
} /*
 * @action Delete Invites
 */
elseif ($do == 'delete_invite') {
    $id = (isset($_GET['id']) ? (int)$_GET['id'] : (isset($_POST['id']) ? (int)$_POST['id'] : ''));
    $query = sql_query('SELECT * FROM invite_codes WHERE id = ' . sqlesc($id) . ' AND sender = ' . sqlesc($CURUSER['id']) . ' AND status = "Pending"') or sqlerr(__FILE__, __LINE__);
    $assoc = mysqli_fetch_assoc($query);
    if (!$assoc) {
        stderr($lang['invites_error'], $lang['invites_noexsist']);
    }
    isset($_GET['sure']) && $sure = htmlsafechars($_GET['sure']);
    if (!$sure) {
        stderr($lang['invites_delete1'], $lang['invites_sure'] . ' Click <a href="' . $_SERVER['PHP_SELF'] . '?do=delete_invite&amp;id=' . $id . '&amp;sender=' . $CURUSER['id'] . '&amp;sure=yes">here</a> to delete it or <a href="?do=view_page">here</a> to go back.');
    }
    sql_query('DELETE FROM invite_codes WHERE id = ' . sqlesc($id) . ' AND sender = ' . sqlesc($CURUSER['id']) . ' AND status = "Pending"') or sqlerr(__FILE__, __LINE__);
    sql_query('UPDATE users SET invites = invites + 1 WHERE id = ' . sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);
    $update['invites'] = ($CURUSER['invites'] + 1);
    $mc1->begin_transaction('MyUser_' . $CURUSER['id']);
    $mc1->update_row(false, [
        'invites' => $update['invites'],
    ]);
    $mc1->commit_transaction($site_config['expires']['curuser']); // 15 mins
    $mc1->begin_transaction('user' . $CURUSER['id']);
    $mc1->update_row(false, [
        'invites' => $update['invites'],
    ]);
    $mc1->commit_transaction($site_config['expires']['user_cache']); // 15 mins
    header('Location: ?do=view_page');
} /*
 * @action Confirm Accounts
 */
elseif ($do = 'confirm_account') {
    $userid = (isset($_GET['userid']) ? (int)$_GET['userid'] : (isset($_POST['userid']) ? (int)$_POST['userid'] : ''));
    if (!is_valid_id($userid)) {
        stderr($lang['invites_error'], $lang['invites_invalid']);
    }
    $select = sql_query('SELECT id, username FROM users WHERE id = ' . sqlesc($userid) . ' AND invitedby = ' . sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);
    $assoc = mysqli_fetch_assoc($select);
    if (!$assoc) {
        stderr($lang['invites_error'], $lang['invites_errorid']);
    }
    isset($_GET['sure']) && $sure = htmlsafechars($_GET['sure']);
    if (!$sure) {
        stderr($lang['invites_confirm1'], $lang['invites_sure1'] . ' ' . htmlsafechars($assoc['username']) . '\'s account? Click <a href="?do=confirm_account&amp;userid=' . $userid . '&amp;sender=' . (int)$CURUSER['id'] . '&amp;sure=yes">here</a> to confirm it or <a href="?do=view_page">here</a> to go back.');
    }
    sql_query('UPDATE users SET status = "confirmed" WHERE id = ' . sqlesc($userid) . ' AND invitedby = ' . sqlesc($CURUSER['id']) . ' AND status="pending"') or sqlerr(__FILE__, __LINE__);

    $mc1->begin_transaction('MyUser_' . $userid);
    $mc1->update_row(false, [
        'status' => 'confirmed',
    ]);
    $mc1->commit_transaction($site_config['expires']['curuser']); // 15 mins
    $mc1->begin_transaction('user' . $userid);
    $mc1->update_row(false, [
        'status' => 'confirmed',
    ]);
    $mc1->commit_transaction($site_config['expires']['user_cache']); // 15 mins

    //==pm to new invitee/////
    $msg = sqlesc("Hey there :wave:
Welcome to {$site_config['site_name']}!\n
We have made many changes to the site, and we hope you enjoy them!\n 
We have been working hard to make {$site_config['site_name']} somethin' special!\n
{$site_config['site_name']} has a strong community (just check out forums), and is a feature rich site. We hope you'll join in on all the fun!\n
Be sure to read the [url={$site_config['baseurl']}/rules.php]Rules[/url] and [url={$site_config['baseurl']}/faq.php]FAQ[/url] before you start using the site.\n
We are a strong friendly community here :D {$site_config['site_name']} is so much more then just torrents.\n
Just for kicks, we've started you out with 200.0 Karma Bonus  Points, and a couple of bonus GB to get ya started!\n 
so, enjoy\n  
cheers,\n 
{$site_config['site_name']} Staff.\n");
    $id = (int)$assoc['id'];
    $subject = sqlesc("Welcome to {$site_config['site_name']} !");
    $added = TIME_NOW;
    sql_query("INSERT INTO messages (sender, subject, receiver, msg, added) VALUES (0, $subject, " . sqlesc($id) . ", $msg, $added)") or sqlerr(__FILE__, __LINE__);
    ///////////////////end////////////
    header('Location: ?do=view_page');
}
