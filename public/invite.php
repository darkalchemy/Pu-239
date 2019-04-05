<?php

require_once __DIR__ . '/../include/bittorrent.php';
require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_html.php';
require_once INCL_DIR . 'function_password.php';
check_user_status();
global $CURUSER, $site_config, $fluent, $cache, $session;

$lang = array_merge(load_language('global'), load_language('invite_code'));

use Nette\Mail\Message;
use Nette\Mail\SendmailMailer;

$HTMLOUT = $sure = '';
$do = (isset($_GET['do']) ? htmlsafechars($_GET['do']) : (isset($_POST['do']) ? htmlsafechars($_POST['do']) : ''));
$valid_actions = [
    'create_invite',
    'delete_invite',
    'confirm_account',
    'view_page',
    'send_email',
];
$do = (($do && in_array($do, $valid_actions, true)) ? $do : '') or header('Location: ?do=view_page');
if ($CURUSER['suspended'] === 'yes') {
    stderr('Sorry', 'Your account is suspended');
}

if ($do === 'view_page') {
    $sql = $fluent->from('users')
        ->select(null)
        ->select('id')
        ->select('uploaded')
        ->select('downloaded')
        ->select('status')
        ->where('invitedby = ?', $CURUSER['id']);

    foreach ($sql as $row) {
        $rows[] = $row;
    }
    $HTMLOUT = "<h1 class='has-text-centered'>{$lang['invites_users']}</h1>";
    $heading = $body = '';
    if (empty($rows)) {
        $body .= "
                    <tr>
                        <td colspan='7'><div class='padding20'>{$lang['invites_nousers']}</div></td>
                    </tr>";
    } else {
        $heading = "
                    <tr>
                        <th>{$lang['invites_username']}</th>
                        <th>{$lang['invites_uploaded']}</th>
                        " . ($site_config['site']['ratio_free'] ? '' : "
                        <th>{$lang['invites_downloaded']}</th>") . "
                        <th>{$lang['invites_ratio']}</th>
                        <th>{$lang['invites_status']}</th>
                        <th>{$lang['invites_confirm']}</th>
                    </tr>";
        foreach ($rows as $row) {
            $ratio = member_ratio($row['uploaded'], $site_config['site']['ratio_free'] ? '0' : $row['downloaded']);
            if ($row['status'] === 'confirmed') {
                $status = "<span class='has-text-success'>{$lang['invites_confirm1']}</span>";
            } else {
                $status = "<span class='has-text-danger'>{$lang['invites_pend']}</span>";
            }
            $body .= "
                    <tr>
                        <td class='level-left'>" . format_username($row['id']) . '</td>
                        <td>' . mksize($row['uploaded']) . '</td>' . ($site_config['site']['ratio_free'] ? '' : '
                        <td>' . mksize($row['downloaded']) . '</td>') . "
                        <td>{$ratio}</td>
                        <td>{$status}</td>";
            if ($row['status'] === 'pending') {
                $body .= "
                        <td>
                            <a {$site_config['paths']['baseurl']}/invite.php?do=confirm_account&amp;userid=" . (int) $row['id'] . '&amp;sender=' . (int) $CURUSER['id'] . "'>
                                <img src='{$site_config['paths']['images_baseurl']}confirm.png' alt='confirm' class='tooltipper' title='Confirm'>
                            </a>
                        </td>
                    </tr>";
            } else {
                $body .= '
                        <td>---</td>
                    </tr>';
            }
        }
    }

    $HTMLOUT .= main_table($body, $heading);
    $body = $heading = '';
    $select = sql_query('SELECT * FROM invite_codes WHERE sender = ' . sqlesc($CURUSER['id']) . " AND status = 'pending'") or sqlerr(__FILE__, __LINE__);
    $num_row = mysqli_num_rows($select);
    $HTMLOUT .= "<h1 class='has-text-centered top20'>{$lang['invites_codes']}</h1>";
    if (!$num_row) {
        $body .= "
                    <tr>
                        <td><div class='padding20'>{$lang['invites_nocodes']}</div></td>
                    </tr>";
    } else {
        $body .= "
                    <tr>
                        <td class='level-item'>{$lang['invites_send_code']}</td>
                        <td class='has-text-centered'>Sent To</td>
                        <td class='has-text-centered'>{$lang['invites_date']}</td>
                        <td class='has-text-centered'>{$lang['invites_delete']}</td>
                        <td class='has-text-centered'>{$lang['invites_status']}</td>
                    </tr>";
        for ($i = 0; $i < $num_row; ++$i) {
            $fetch_assoc = mysqli_fetch_assoc($select);
            $can_send_it = empty($fetch_assoc['email']) ? "
                            <a href='{$site_config['paths']['baseurl']}/invite.php?do=send_email&amp;id=" . (int) $fetch_assoc['id'] . "' class='tooltipper' title='Send Email'>
                                <i class='icon-mail-alt' aria-hidden='true'></i>" . htmlsafechars($fetch_assoc['code']) . '
                            </a>' : "
                            <span class='tooltipper' title='Email Sent'>
                                " . htmlsafechars($fetch_assoc['code']) . '
                            </span>';

            $body .= "
                    <tr>
                        <td>$can_send_it</td>
                        <td class='has-text-centered'>
                            <span>" . (!empty($fetch_assoc['email']) ? htmlsafechars($fetch_assoc['email']) : '---') . "</span>
                        </td>
                        <td class='has-text-centered'>" . get_date($fetch_assoc['added'], '', 0, 1) . "</td>
                        <td class='has-text-centered'>
                            <a href='{$site_config['paths']['baseurl']}/invite.php?do=delete_invite&amp;id=" . (int) $fetch_assoc['id'] . '&amp;sender=' . (int) $CURUSER['id'] . "' class='tooltipper' title='Delete'>
                                <i class='icon-cancel icon has-text-danger'></i>
                            </a>
                        </td>
                        <td class='has-text-centered'>" . htmlsafechars($fetch_assoc['status']) . '</td>
                    </tr>';
        }
    }
    $HTMLOUT .= main_table($body, $heading) . "
            <form action='?do=create_invite' method='post' accept-charset='utf-8'>
                <div class='has-text-centered margin20'>
                    <input type='submit' class='button is-small' value='{$lang['invites_create']}'>
                </div>
            </form>";
    echo stdhead('Invites') . wrapper($HTMLOUT) . stdfoot();
    die();
} elseif ($do === 'create_invite') {
    if ($CURUSER['invites'] <= 0) {
        stderr($lang['invites_error'], $lang['invites_noinvite']);
    }
    if ($CURUSER['invite_rights'] === 'no' || $CURUSER['suspended'] === 'yes') {
        stderr($lang['invites_deny'], $lang['invites_disabled']);
    }
    $count = $fluent->from('invite_codes')
        ->select(null)
        ->select('COUNT(*) AS count')
        ->where('status = "Pending')
        ->fetch('count');
    if ($count >= $site_config['site']['invites']) {
        stderr($lang['invites_error'], $lang['invites_limit']);
    }
    $token = make_password(32);

    $values = [
        'sender' => $CURUSER['id'],
        'code' => $token,
        'added' => TIME_NOW,
    ];
    $fluent->insertInto('invite_codes')
        ->values($values)
        ->execute();

    $set = [
        'invites' => new Envms\FluentPDO\Literal('invites - 1'),
    ];
    $fluent->update('users')
        ->set($set)
        ->where('id=?', $CURUSER['id'])
        ->execute();

    $update['invites'] = ($CURUSER['invites'] - 1);
    $cache->update_row('user_' . $CURUSER['id'], [
        'invites' => $update['invites'],
    ], $site_config['expires']['user_cache']);
    header('Location: ?do=view_page');
} elseif ($do === 'send_email') {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $email = (isset($_POST['email']) ? htmlsafechars($_POST['email']) : '');
        $invite = (isset($_POST['code']) ? htmlsafechars($_POST['code']) : '');
        $secret = (isset($_POST['secret']) ? htmlsafechars($_POST['secret']) : '');
        if (!$email) {
            stderr($lang['invites_error'], $lang['invites_noemail']);
        }
        $check = $fluent->from('users')
            ->select(null)
            ->select('COUNT(*) AS count')
            ->where('email = ?', $email)
            ->fetch('count');
        if ($check != 0) {
            stderr('Error', 'This email address is already in use!');
        }
        if (!validemail($email)) {
            stderr($lang['invites_error'], $lang['invites_invalidemail']);
        }
        $fluent->update('invite_codes')
            ->set(['email' => $email])
            ->where('code = ?', $_POST['code'])
            ->execute();

        $inviter = htmlsafechars($CURUSER['username']);
        $title = $site_config['site']['name'];
        $body = doc_head() . "
    <meta property='og:title' content='{$title}'>
    <title>{$title} Invitation</title>
</head>
<body>
<p>You have been invited to {$site_config['site']['name']} by $inviter.<br>
$inviter has specified this address ($email) as your email.<br>
If you do not know this person, please ignore this email. Please do not reply.<br>
This is a private site and you must agree to the rules before you can enter:</p>
<p>{$site_config['paths']['baseurl']}/useragreement.php</p>
<p>{$site_config['paths']['baseurl']}/rules.php</p>
<p>{$site_config['paths']['baseurl']}/faq.php</p>
<hr>
<p>To confirm your invitation, you have to follow this link:</p>
{$site_config['paths']['baseurl']}/invite_signup.php?id={$secret}&code=$invite
<hr>
<p>After you do this, $inviter may need to confirm your account.<br>
We urge you to read the RULES and FAQ before you start using {$site_config['site']['name']}.</p>
</body>
</html>";

        $mail = new Message();
        $mail->setFrom("{$site_config['site']['email']}", "{$site_config['chatBotName']}")
            ->addTo($email)
            ->setReturnPath($site_config['site']['email'])
            ->setSubject("You have been invited to {$site_config['site']['name']}")
            ->setHtmlBody($body);

        $mailer = new SendmailMailer();
        $mailer->commandArgs = "-f{$site_config['site']['email']}";
        $mailer->send($mail);

        $session->set('is-success', $lang['invites_confirmation']);
        header("Location: {$site_config['paths']['baseurl']}/invite.php?do=view_page");
        die();
    }
    $id = (isset($_GET['id']) ? (int) $_GET['id'] : (isset($_POST['id']) ? (int) $_POST['id'] : ''));
    if (!is_valid_id($id)) {
        stderr($lang['invites_error'], $lang['invites_invalid']);
    }
    $fetch = $fluent->from('invite_codes')
        ->where('id=?', $id)
        ->where('sender = ?', $CURUSER['id'])
        ->where('status = "pending"')
        ->fetch();

    if (!$fetch) {
        stderr($lang['invites_error'], $lang['invites_noexsist']);
    }

    $HTMLOUT .= "
        <div class='portlet'>
            <form method='post' action='?do=send_email' accept-charset='utf-8'>
                <table class='table table-bordered bottom20'>
                    <thead>
                        <tr>
                            <th>E-Mail</th>
                            <th>
                                <input type='text' class='w-100' name='email'>
                            </th>
                        </tr>
                    </thead>
                </table>
                <div class='has-text-centered margin20'>
                    <input type='hidden' name='code' value='" . htmlsafechars($fetch['code']) . "'>
                    <input type='hidden' name='secret' value='" . htmlsafechars($fetch['id']) . "'>
                    <input type='submit' value='Send e-mail' class='button is-small'>
                </div>
            </form>
        </div>";
    echo stdhead('Invites') . $HTMLOUT . stdfoot();
} elseif ($do === 'delete_invite') {
    $id = (isset($_GET['id']) ? (int) $_GET['id'] : (isset($_POST['id']) ? (int) $_POST['id'] : ''));
    $query = sql_query('SELECT * FROM invite_codes WHERE id=' . sqlesc($id) . ' AND sender = ' . sqlesc($CURUSER['id']) . ' AND status = "pending"') or sqlerr(__FILE__, __LINE__);
    $assoc = mysqli_fetch_assoc($query);
    if (!$assoc) {
        stderr($lang['invites_error'], $lang['invites_noexsist']);
    }
    isset($_GET['sure']) && $sure = htmlsafechars($_GET['sure']);
    if (!$sure) {
        stderr($lang['invites_delete1'], $lang['invites_sure'] . ' Click <a href="' . $_SERVER['PHP_SELF'] . '?do=delete_invite&amp;id=' . $id . '&amp;sender=' . $CURUSER['id'] . '&amp;sure=yes">here</a> to delete it or <a href="?do=view_page">here</a> to go back.');
    }
    $fluent->deleteFrom('invite_codes')
        ->where('id=?', $id)
        ->where('sender = ?', $CURUSER['id'])
        ->where('status = "pending"')
        ->execute();

    $set = [
        'invites' => new Envms\FluentPDO\Literal('invites + 1'),
    ];

    $fluent->update('users')
        ->set($set)
        ->where('id=?', $CURUSER['id'])
        ->execute();
    $update['invites'] = ($CURUSER['invites'] + 1);

    $cache->update_row('user_' . $CURUSER['id'], [
        'invites' => $update['invites'],
    ], $site_config['expires']['user_cache']);
    header('Location: ?do=view_page');
} elseif ($do = 'confirm_account') {
    $userid = (isset($_GET['userid']) ? (int) $_GET['userid'] : (isset($_POST['userid']) ? (int) $_POST['userid'] : ''));
    if (!is_valid_id($userid)) {
        stderr($lang['invites_error'], $lang['invites_invalid']);
    }

    $select = sql_query('SELECT id, username FROM users WHERE id=' . sqlesc($userid) . ' AND invitedby = ' . sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);
    $assoc = mysqli_fetch_assoc($select);
    if (!$assoc) {
        stderr($lang['invites_error'], $lang['invites_errorid']);
    }
    isset($_GET['sure']) && $sure = htmlsafechars($_GET['sure']);
    if (!$sure) {
        stderr($lang['invites_confirm1'], $lang['invites_sure1'] . ' ' . htmlsafechars($assoc['username']) . '\'s account? Click <a href="?do=confirm_account&amp;userid=' . $userid . '&amp;sender=' . (int) $CURUSER['id'] . '&amp;sure=yes">here</a> to confirm it or <a href="?do=view_page">here</a> to go back.');
    }
    sql_query('UPDATE users SET status = "confirmed" WHERE id=' . sqlesc($userid) . ' AND invitedby = ' . sqlesc($CURUSER['id']) . ' AND status="pending"') or sqlerr(__FILE__, __LINE__);

    $cache->update_row('user_' . $userid, [
        'status' => 'confirmed',
    ], $site_config['expires']['user_cache']);

    $msg = sqlesc("Hey there :wave:
Welcome to {$site_config['site']['name']}!\n
We have made many changes to the site, and we hope you enjoy them!\n 
We have been working hard to make {$site_config['site']['name']} somethin' special!\n
{$site_config['site']['name']} has a strong community (just check out forums), and is a feature rich site. We hope you'll join in on all the fun!\n
Be sure to read the [url={$site_config['paths']['baseurl']}/rules.php]Rules[/url] and [url={$site_config['paths']['baseurl']}/faq.php]FAQ[/url] before you start using the site.\n
We are a strong friendly community here :D {$site_config['site']['name']} is so much more then just torrents.\n
Just for kicks, we've started you out with 200.0 Karma Bonus Points, and a couple of bonus GB to get ya started!\n 
so, enjoy\n  
cheers,\n 
{$site_config['site']['name']} Staff.\n");
    $id = (int) $assoc['id'];
    $subject = sqlesc("Welcome to {$site_config['site']['name']} !");
    $added = TIME_NOW;
    sql_query("INSERT INTO messages (sender, subject, receiver, msg, added) VALUES (0, $subject, " . sqlesc($id) . ", $msg, $added)") or sqlerr(__FILE__, __LINE__);
    ///////////////////end////////////
    header('Location: ?do=view_page');
}
