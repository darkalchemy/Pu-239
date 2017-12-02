<?php
require_once realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..') . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'user_functions.php';
require_once INCL_DIR . 'password_functions.php';
check_user_status();
global $CURUSER, $site_config, $cache;

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

if ($do == 'view_page') {
    $query = sql_query('SELECT * FROM users WHERE invitedby = ' . sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);
    $rows = mysqli_num_rows($query);
    $HTMLOUT = '';
    $HTMLOUT .= "
        <div class='container is-fluid portlet'>
            <table class='table table-bordered table-striped top20 bottom20'>
                <thead>    
                    <tr>
                        <th colspan='7'>{$lang['invites_users']}</th>
                    </tr>
                </thead>
                <tbody";
    if (!$rows) {
        $HTMLOUT .= "
                    <tr>
                        <td colspan='7'>{$lang['invites_nousers']}</td>
                    </tr>";
    } else {
        $HTMLOUT .= "
                    <tr>
                        <td>{$lang['invites_username']}</td>
                        <td>{$lang['invites_uploaded']}</td>
                        " . ($site_config['ratio_free'] ? '' : "
                        <td>{$lang['invites_downloaded']}</td>") . "
                        <td>{$lang['invites_ratio']}</td>
                        <td>{$lang['invites_status']}</td>
                        <td>{$lang['invites_confirm']}</td>
                    </tr>";
        for ($i = 0; $i < $rows; ++$i) {
            $arr = mysqli_fetch_assoc($query);
            $ratio = member_ratio($arr['uploaded'], $site_config['ratio_free'] ? '0' : $arr['downloaded']);
            if ($arr['status'] == 'confirmed') {
                $status = "<span class='has-text-success'>{$lang['invites_confirm1']}</span>";
            } else {
                $status = "<span class='text-red'>{$lang['invites_pend']}</span>";
            }
            $HTMLOUT .= "
                    <tr>
                        <td>" . format_username($arr['id']) . "</td>
                        <td>" . mksize($arr['uploaded']) . "</td>" . ($site_config['ratio_free'] ? '' : "
                        <td>" . mksize($arr['downloaded']) . "</td>") . "
                        <td>{$ratio}</td>
                        <td>{$status}</td>";
            if ($arr['status'] == 'pending') {
                $HTMLOUT .= "
                        <td>
                            <a href='?do=confirm_account&amp;userid=" . (int)$arr['id'] . '&amp;sender=' . (int)$CURUSER['id'] . "'>
                                <img src='{$site_config['pic_base_url']}confirm.png' alt='confirm' class='tooltipper' title='Confirm' />
                            </a>
                        </td>
                    </tr>";
            } else {
                $HTMLOUT .= "
                        <td>---</td>
                    </tr>";
            }
        }
    }
    $HTMLOUT .= '
                </tbody>
            </table>';
    $select = sql_query('SELECT * FROM invite_codes WHERE sender = ' . sqlesc($CURUSER['id']) . " AND status = 'Pending'") or sqlerr(__FILE__, __LINE__);
    $num_row = mysqli_num_rows($select);
    $HTMLOUT .= "
            <table class='table table-bordered table-striped top20 bottom20'>
                <thead>
                    <tr>
                        <th colspan='6'>{$lang['invites_codes']}</th>
                    </tr>
                </thead>
                <tbody>";
    if (!$num_row) {
        $HTMLOUT .= "
                    <tr>
                        <td>{$lang['invites_nocodes']}</td>
                    </tr>";
    } else {
        $HTMLOUT .= "
                    <tr>
                        <td>{$lang['invites_send_code']}</td>
                        <td>{$lang['invites_date']}</td>
                        <td>{$lang['invites_delete']}</td>
                        <td>{$lang['invites_status']}</td>
                    </tr>";
        for ($i = 0; $i < $num_row; ++$i) {
            $fetch_assoc = mysqli_fetch_assoc($select);
            $HTMLOUT .= "
                    <tr>
                        <td>" . htmlsafechars($fetch_assoc['code']) . "
                            <a href='?do=send_email&amp;id=" . (int)$fetch_assoc['id'] . "'>
                                <img src='{$site_config['pic_base_url']}email.gif' alt='Email' class='tooltipper' title='Send Email' />
                            </a>
                        </td>
                        <td>" . get_date($fetch_assoc['invite_added'], '', 0, 1) . "</td>
                        <td>
                            
                            <a href='?do=delete_invite&amp;id=" . (int)$fetch_assoc['id'] . '&amp;sender=' . (int)$CURUSER['id'] . "' class='tooltipper' title='Delete'>
                                <i class='fa fa-remove fa-2x'></i>
                            </a>
                        </td>
                        <td>" . htmlsafechars($fetch_assoc['status']) . '</td>
                    </tr>';
        }
    }
    $HTMLOUT .= "
                </tbody>
            </table>
            <form action='?do=create_invite' method='post'>
                <div class='has-text-centered bottom20'>
                    <input type='submit' class='button' value='{$lang['invites_create']}' />
                </div>
            </form>
        </div>";
    echo stdhead('Invites') . $HTMLOUT . stdfoot();
    die;
} elseif ($do == 'create_invite') {
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
    $cache->update_row('user' . $CURUSER['id'], [
        'invites' => $update['invites'],
    ], $site_config['expires']['user_cache']);
    header('Location: ?do=view_page');
} elseif ($do == 'send_email') {
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
You have been invited to {$site_config['site_name']} by $inviter.

$inviter has specified this address ($email) as your email.

If you do not know this person, please ignore this email. Please do not reply.

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
    $HTMLOUT .= "
        <div class='container is-fluid portlet'>
            <form method='post' action='?do=send_email'>
                <table class='table table-bordered top20 bottom20'>
                    <thead>
                        <tr>
                            <td>E-Mail</td>
                            <td>
                                <input type='text' class='w-100' name='email' />
                            </td>
                        </tr>
                    </thead>
                </table>
                <div class='has-text-centered bottom20'>
                    <input type='hidden' name='code' value='" . htmlsafechars($fetch['code']) . "' />
                    <input type='submit' value='Send e-mail' class='button' />
                </div>
            </form>
        </div>";
    echo stdhead('Invites') . $HTMLOUT . stdfoot();
} elseif ($do == 'delete_invite') {
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
    $cache->update_row('user' . $CURUSER['id'], [
        'invites' => $update['invites'],
    ], $site_config['expires']['user_cache']);
    header('Location: ?do=view_page');
} elseif ($do = 'confirm_account') {
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

    $cache->update_row('user' . $userid, [
        'status' => 'confirmed',
    ], $site_config['expires']['user_cache']);

    //==pm to new invitee/////
    $msg = sqlesc("Hey there :wave:
Welcome to {$site_config['site_name']}!\n
We have made many changes to the site, and we hope you enjoy them!\n 
We have been working hard to make {$site_config['site_name']} somethin' special!\n
{$site_config['site_name']} has a strong community (just check out forums), and is a feature rich site. We hope you'll join in on all the fun!\n
Be sure to read the [url={$site_config['baseurl']}/rules.php]Rules[/url] and [url={$site_config['baseurl']}/faq.php]FAQ[/url] before you start using the site.\n
We are a strong friendly community here :D {$site_config['site_name']} is so much more then just torrents.\n
Just for kicks, we've started you out with 200.0 Karma Bonus Points, and a couple of bonus GB to get ya started!\n 
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
