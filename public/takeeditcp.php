<?php

declare(strict_types = 1);

require_once __DIR__ . '/../include/bittorrent.php';
require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_password.php';
require_once CLASS_DIR . 'class_user_options.php';
require_once CLASS_DIR . 'class_user_options_2.php';
require_once INCL_DIR . 'function_html.php';
$user = check_user_status();

use Delight\Auth\Auth;
use Delight\Auth\EmailNotVerifiedException;
use Delight\Auth\InvalidEmailException;
use Delight\Auth\InvalidPasswordException;
use Delight\Auth\NotLoggedInException;
use Delight\Auth\TooManyRequestsException;
use Delight\Auth\UserAlreadyExistsException;
use Pu239\Cache;
use Pu239\Database;
use Pu239\Message;
use Pu239\Session;

$curuser_cache = $user_cache = $urladd = $changedemail = $birthday = '';
$action = isset($_POST['action']) ? htmlsafechars($_POST['action']) : '';
$updateset = $curuser_cache = $user_cache = [];
$setbits = $clrbits = $setbits2 = $clrbits2 = 0;
global $container, $site_config;

$auth = $container->get(Auth::class);
$fluent = $container->get(Database::class);
$cache = $container->get(Cache::class);
$session = $container->get(Session::class);

if ($action === 'avatar') {
    $avatars = isset($_POST['avatars']) && $_POST['avatars'] === 'yes' ? 'yes' : 'no';
    $offensive_avatar = isset($_POST['offensive_avatar']) && $_POST['offensive_avatar'] === 'yes' ? 'yes' : 'no';
    $view_offensive_avatar = isset($_POST['view_offensive_avatar']) && $_POST['view_offensive_avatar'] === 'yes' ? 'yes' : 'no';
    if (!($user['avatarpos'] == 0 || $user['avatarpos'] != 1)) {
        $avatar = validate_url($_POST['avatar']);
    }
    if (!empty($avatar)) {
        $img_size = @getimagesize($avatar);
        if ($img_size == false || !in_array($img_size['mime'], $site_config['images']['extensions'])) {
            stderr(_('Error'), _('Not an image or unsupported image!'));
        }
        if ($img_size[0] < 5 || $img_size[1] < 5) {
            stderr(_('Error'), _('Image is too small'));
        }
        sql_query('UPDATE usersachiev SET avatarset = avatarset + 1 WHERE userid = ' . sqlesc($user['id']) . ' AND avatarset = 0') or sqlerr(__FILE__, __LINE__);
    }
    $updateset[] = 'offensive_avatar = ' . sqlesc($offensive_avatar);
    $updateset[] = 'view_offensive_avatar = ' . sqlesc($view_offensive_avatar);
    if (!empty($avatar) && !($user['avatarpos'] == 0 || $user['avatarpos'] != 1)) {
        $updateset[] = 'avatar = ' . sqlesc($avatar);
    }
    $updateset[] = 'avatars = ' . sqlesc($avatars);
    $curuser_cache['offensive_avatar'] = $offensive_avatar;
    $user_cache['offensive_avatar'] = $offensive_avatar;
    $curuser_cache['view_offensive_avatar'] = $view_offensive_avatar;
    $user_cache['view_offensive_avatar'] = $view_offensive_avatar;
    $curuser_cache['avatar'] = $avatar;
    $user_cache['avatar'] = $avatar;
    $curuser_cache['avatars'] = $avatars;
    $user_cache['avatars'] = $avatars;
    $action = 'avatar';
} elseif ($action === 'signature') {
    if (isset($_POST['info']) && (($info = $_POST['info']) != $user['info'])) {
        $updateset[] = 'info = ' . sqlesc($info);
        $curuser_cache['info'] = $info;
        $user_cache['info'] = $info;
    }
    $signatures = isset($_POST['signatures']) && $_POST['signatures'] === 'yes' ? 'yes' : 'no';
    $signature = validate_url($_POST['signature']);
    if (!empty($signature)) {
        $img_size = @getimagesize($signature);
        if ($img_size == false || !in_array($img_size['mime'], $site_config['images']['extensions'])) {
            stderr(_('Error'), _('Not an image or unsupported image!'));
        }
        if ($img_size[0] < 5 || $img_size[1] < 5) {
            stderr(_('Error'), _('Image is too small'));
        }
        sql_query('UPDATE usersachiev SET sigset = sigset+1 WHERE userid=' . sqlesc($user['id']) . ' AND sigset = 0') or sqlerr(__FILE__, __LINE__);
        $updateset[] = 'signature = ' . sqlesc('[img]' . $signature . "[/img]\n");
        $curuser_cache['signature'] = ('[img]' . $signature . "[/img]\n");
        $user_cache['signature'] = ('[img]' . $signature . "[/img]\n");
    }
    $updateset[] = "signatures = '$signatures'";
    $curuser_cache['signatures'] = $signatures;
    $user_cache['signatures'] = $signatures;
    $action = 'signature';
} elseif ($action === 'security') {
    if (!empty($_POST['password'])) {
        if ($_POST['password'] !== $_POST['confirm_password']) {
            stderr(_('Error'), _("The passwords didn't match. Try again."));
        }
        if (empty($_POST['current_pass'])) {
            stderr(_('Error'), _('Current Password can not be empty!'));
        }
        if ($_POST['password'] === $_POST['current_pass']) {
            stderr(_('Error'), _('New password can not be the same as the old password!'));
        }
        try {
            $auth->changePassword($_POST['current_pass'], $_POST['password']);

            $cache->set('forced_logout_' . $user['id'], TIME_NOW);
            stderr(_('Success'), _('Password has been changed. You will now be able to login with your new password.'));
        } catch (NotLoggedInException $e) {
            stderr(_('Error'), _('Not logged in'));
        } catch (InvalidPasswordException $e) {
            stderr(_('Error'), _('Invalid password'));
        } catch (TooManyRequestsException $e) {
            stderr(_('Error'), _('Too many requests from your IP'));
        }
    }

    if (!empty($_POST['chmailpass'])) {
        if (strlen($_POST['chmailpass']) > 72) {
            stderr(_('Error'), _('Sorry, password is too long(max is 40 chars)'));
        }
    }

    if ($_POST['email'] != $user['email']) {
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            stderr(_('Error'), _("That doesn't look like a valid email address."));
        }
        $r = sql_query('SELECT id FROM users WHERE email = ' . sqlesc($email)) or sqlerr(__FILE__, __LINE__);
        if (mysqli_num_rows($r) > 0) {
            stderr(_('Error'), _('Could not change email, address already taken or password mismatch.'));
        }
        $cur_passhash = $fluent->from('users')
                               ->select(null)
                               ->select('password')
                               ->where('id = ?', $user['id'])
                               ->fetch('password');

        if (!password_verify($_POST['chmailpass'], $cur_passhash)) {
            stderr(_('Error'), _("The passwords didn't match. Try again."));
        }
        $changedemail = 1;
    }
    if ($user['status'] === 1) {
        if (isset($_POST['parked'])) {
            if ($_POST['parked'] === 'no') {
                $updateset[] = 'status = 0';
                $updateset[] = 'parked_until = 0';
                $curuser_cache['status'] = 0;
                $curuser_cache['parked_until'] = 0;
                $user_cache['status'] = 0;
                $user_cache['parked_until'] = 0;
            }
        }
    }
    if ($user['anonymous_until'] != 0) {
        $anonymous = isset($_POST['anonymous']) && $_POST['anonymous'] != '' ? TIME_NOW + (365 * 86400) : 0;
        $updateset[] = 'anonymous_until = ' . sqlesc($anonymous);
        $curuser_cache['anonymous_until'] = $anonymous;
        $user_cache['anonymous_until'] = $anonymous;
    }
    if (isset($_POST['hidecur']) && ($hidecur = $_POST['hidecur']) != $user['hidecur']) {
        $updateset[] = 'hidecur = ' . sqlesc($hidecur);
        $curuser_cache['hidecur'] = $hidecur;
        $user_cache['hidecur'] = $hidecur;
    }
    if (isset($_POST['show_email']) && ($show_email = $_POST['show_email']) != $user['show_email']) {
        $updateset[] = 'show_email = ' . sqlesc($show_email);
        $curuser_cache['show_email'] = $show_email;
        $user_cache['show_email'] = $show_email;
    }
    if (isset($_POST['paranoia']) && ($paranoia = $_POST['paranoia']) != $user['paranoia']) {
        $updateset[] = 'paranoia = ' . sqlesc($paranoia);
        $curuser_cache['paranoia'] = $paranoia;
        $user_cache['paranoia'] = $paranoia;
    }
    if (isset($_POST['changeq']) && (($changeq = (int) $_POST['changeq']) != $user['passhint']) && is_valid_id($changeq)) {
        $updateset[] = 'passhint = ' . sqlesc($changeq);
        $curuser_cache['passhint'] = $changeq;
        $user_cache['passhint'] = $changeq;
    }
    if ($changedemail) {
        try {
            if ($auth->reconfirmPassword($_POST['chmailpass'])) {
                $auth->changeEmail($_POST['email'], function ($selector, $token) {
                    global $site_config, $user, $email;

                    $url = $site_config['paths']['baseurl'] . '/verify_email.php?selector=' . urlencode($selector) . '&token=' . urlencode($token);
                    $body = str_replace([
                        ' <#USERNAME#>',
                        '<#SITENAME#>',
                        '<#USEREMAIL#>',
                        '<#IP_ADDRESS#>',
                        '<#CHANGE_LINK#>',
                    ], [
                        $user['username'],
                        $site_config['site']['name'],
                        $email,
                        getip($user['id']),
                        $url,
                    ], _fe("<!doctype html>
<html lang='en-US'>
<head>
    <style>html{visibility: hidden;opacity:0;}</style>
    <meta property='og:title' content='Crafty Email Change'>
    <title>Crafty Email Change</title>
    <meta charset='utf-8'>
    <meta http-equiv='X-UA-Compatible' content='IE=edge'>
    <meta name='viewport' content='width=device-width,initial-scale=1'>
    <meta property='og:url' content='{0}'>
    <meta property='og:type' content='website'>
    <meta property='og:description' content='{1}'>
</head>
<body>
<p>You have requested that your user profile(username <#USERNAME#>) on <#SITENAME#> should be updated with this email address (<#USEREMAIL#>) as user contact.</p>
<p>If you did not do this, please ignore this email and nothing will be changed. The person who entered your email address had the IP address <#IP_ADDRESS#>. Please do not reply to this email.</p>
<br>
<p>To complete the update of your user profile, please follow this link:</p>
<p><#CHANGE_LINK#></p>
<p>Your new email address will appear in your profile after you do this. Otherwise your profile will remain unchanged.</p>
<p> --<#SITENAME#></p>
</body>
</html>", $site_config['paths']['baseurl'], $site_config['site']['name']));
                    send_mail($email, "{$site_config['site']['name']} " . _('profile change confirmation'), $body, strip_tags($body));
                });
                $session->set('is-info', _('The change will take effect as soon as the new email address has been confirmed'));
            } else {
                stderr(_('Error'), _("We can't say if the user is who they claim to be"));
            }
        } catch (InvalidEmailException $e) {
            stderr(_('Error'), _('Invalid email address'));
        } catch (UserAlreadyExistsException $e) {
            stderr(_('Error'), _('Email address already exists'));
        } catch (EmailNotVerifiedException $e) {
            stderr(_('Error'), _('Account not verified'));
        } catch (NotLoggedInException $e) {
            stderr(_('Error'), _('Not logged in'));
        } catch (TooManyRequestsException $e) {
            stderr(_('Error'), _('Too many requests from your IP'));
        }
        $dt = TIME_NOW;
        $subject = _('Email Alert');
        $msg = _('User ') . "[url={$site_config['paths']['baseurl']}/userdetails.php?id=" . $user['id'] . '][b]' . htmlsafechars($user['username']) . '[/b][/url]' . _(' changed email address :') . _(' Old email was ') . htmlsafechars($user['email']) . _(' new email is ') . "$email" . _(', please check this was for a legitimate reason') . '';
        $pmstaff = $fluent->from('users')
                          ->select(null)
                          ->select('id')
                          ->where('class >= ?', UC_ADMINISTRATOR)
                          ->fetchAll();
        foreach ($pmstaff as $arr) {
            $msgs_buffer[] = [
                'receiver' => $arr['id'],
                'added' => $dt,
                'msg' => $msg,
                'subject' => $subject,
            ];
        }
        if (!empty($msgs_buffer)) {
            $messages_class = $container->get(Message::class);
            $messages_class->insert($msgs_buffer);
        }
        $urladd .= '&mailsent=1';
    }
    $action = 'security';
} elseif ($action === 'torrents') {
    $emailnotif = isset($_POST['emailnotif']) ? $_POST['emailnotif'] : '';
    $pmnotif = isset($_POST['pmnotif']) ? $_POST['pmnotif'] : '';
    $notifs = $pmnotif === 'yes' ? '[pmail]' : '';
    $notifs .= $emailnotif === 'yes' ? '[email]' : '';
    $r = sql_query('SELECT id FROM categories') or sqlerr(__FILE__, __LINE__);
    $rows = mysqli_num_rows($r);
    for ($i = 0; $i < $rows; ++$i) {
        $a = mysqli_fetch_assoc($r);
        if (isset($_POST["cat{$a['id']}"]) && $_POST["cat{$a['id']}"] === 'yes') {
            $notifs .= "[cat{$a['id']}]";
        }
    }
    $updateset[] = 'notifs = ' . sqlesc($notifs) . '';
    $curuser_cache['notifs'] = $notifs;
    $user_cache['notifs'] = $notifs;
    if (isset($_POST['torrentsperpage']) && (($torrentspp = min(100, (int) $_POST['torrentsperpage'])) != $user['torrentsperpage'])) {
        $updateset[] = "torrentsperpage = $torrentspp";
    }
    $curuser_cache['torrentsperpage'] = $torrentspp;
    $user_cache['torrentsperpage'] = $torrentspp;
    if (isset($_POST['viewscloud'])) {
        $setbits |= class_user_options::VIEWSCLOUD;
    } else {
        $clrbits |= class_user_options::VIEWSCLOUD;
    }
    if (isset($_POST['clear_new_tag_manually'])) {
        $setbits |= class_user_options::CLEAR_NEW_TAG_MANUALLY;
    } else {
        $clrbits |= class_user_options::CLEAR_NEW_TAG_MANUALLY;
    }
    if (isset($_POST['split'])) {
        $setbits2 |= class_user_options_2::SPLIT;
    } else {
        $clrbits2 |= class_user_options_2::SPLIT;
    }
    if (isset($_POST['browse_icons'])) {
        $setbits2 |= class_user_options_2::BROWSE_ICONS;
    } else {
        $clrbits2 |= class_user_options_2::BROWSE_ICONS;
    }
    $hidden = isset($_POST['hidden']) ? 1 : 0;
    if ($hidden != $user['hidden']) {
        $updateset[] = 'hidden = ' . sqlesc($hidden);
        $curuser_cache['hidden'] = $hidden;
        $user_cache['hidden'] = $hidden;
    }
    if (isset($_POST['categorie_icon']) && (($categorie_icon = (int) $_POST['categorie_icon']) != $user['categorie_icon']) && is_valid_id($categorie_icon)) {
        $updateset[] = 'categorie_icon = ' . sqlesc($categorie_icon);
        $curuser_cache['categorie_icon'] = $categorie_icon;
        $user_cache['categorie_icon'] = $categorie_icon;
    }
    $action = 'torrents';
} elseif ($action === 'personal') {
    if (isset($_POST['title']) && $user['class'] >= UC_VIP && ($title = $_POST['title']) != $user['title']) {
        $notallow = [
            'sysop',
            'administrator',
            'admin',
            'mod',
            'moderator',
            'vip',
            'motherfucker',
        ];
        if (in_array(strtolower($title), ($notallow))) {
            stderr(_('Error'), _('Invalid custom title!'));
        }
        $updateset[] = 'title = ' . sqlesc($title);
        $curuser_cache['title'] = $title;
        $user_cache['title'] = $title;
    }
    if (isset($_POST['stylesheet']) && (($stylesheet = (int) $_POST['stylesheet']) != $user['stylesheet']) && is_valid_id($stylesheet)) {
        $updateset[] = 'stylesheet = ' . sqlesc($stylesheet);
        $curuser_cache['stylesheet'] = $stylesheet;
        $user_cache['stylesheet'] = $stylesheet;
    }
    if (isset($_POST['topicsperpage']) && (($topicspp = min(100, (int) $_POST['topicsperpage'])) != $user['topicsperpage'])) {
        $updateset[] = "topicsperpage = $topicspp";
        $curuser_cache['topicsperpage'] = $topicspp;
        $user_cache['topicsperpage'] = $topicspp;
    }
    if (isset($_POST['postsperpage']) && (($postspp = min(100, (int) $_POST['postsperpage'])) != $user['postsperpage'])) {
        $updateset[] = "postsperpage = $postspp";
        $curuser_cache['postsperpage'] = $postspp;
        $user_cache['postsperpage'] = $postspp;
    }
    if (isset($_POST['ajaxchat_height']) && (($ajaxchat_heightpp = $_POST['ajaxchat_height']) != $user['ajaxchat_height'])) {
        $updateset[] = "ajaxchat_height = $ajaxchat_heightpp";
        $curuser_cache['ajaxchat_height'] = $ajaxchat_heightpp;
        $user_cache['ajaxchat_height'] = $ajaxchat_heightpp;
    }
    if (isset($_POST['forum_sort']) && ($forum_sort = $_POST['forum_sort']) != $user['forum_sort']) {
        $updateset[] = 'forum_sort= ' . sqlesc($forum_sort);
        $curuser_cache['forum_sort'] = $forum_sort;
        $user_cache['forum_sort'] = $forum_sort;
    }
    if (isset($_POST['use_12_hour']) && ($is_12_hour = $_POST['use_12_hour']) != $user['use_12_hour']) {
        $updateset[] = 'use_12_hour = ' . sqlesc($is_12_hour);
        $curuser_cache['use_12_hour'] = $is_12_hour;
        $user_cache['use_12_hour'] = $is_12_hour;
        $session = $container->get(Session::class);
        $session->set('use_12_hour', $is_12_hour);
    }
    if (isset($_POST['fontsize']) && ($fontsize = $_POST['fontsize']) != $user['font_size']) {
        $updateset[] = 'font_size = ' . sqlesc($fontsize);
        $curuser_cache['font_size'] = $fontsize;
        $user_cache['font_size'] = $fontsize;
    }
    if (isset($_POST['gender']) && ($gender = $_POST['gender']) != $user['gender']) {
        $updateset[] = 'gender = ' . sqlesc($gender);
        $curuser_cache['gender'] = $gender;
        $user_cache['gender'] = $gender;
    }

    if (empty($user['birthday']) || $user['birthday'] === '1970-01-01') {
        $birthday = isset($_POST['birthday']) ? $_POST['birthday'] : 0;
        if (!empty($birthday)) {
            $updateset[] = 'birthday = ' . sqlesc($birthday);
            $curuser_cache['birthday'] = $birthday;
            $user_cache['birthday'] = $birthday;
            $cache->delete('birthdayusers_');
        } else {
            $birthday = '1970-01-01';
            $updateset[] = 'birthday = ' . sqlesc($birthday);
            $curuser_cache['birthday'] = $birthday;
            $user_cache['birthday'] = $birthday;
            $cache->delete('birthdayusers_');
        }
    }
    $action = 'personal';
} elseif ($action === 'social') {
    if (isset($_POST['skype']) && ($skype = $_POST['skype']) != $user['skype']) {
        $updateset[] = 'skype= ' . sqlesc($skype);
        $curuser_cache['skype'] = $skype;
        $user_cache['skype'] = $skype;
    }
    if (isset($_POST['website']) && ($website = $_POST['website']) != $user['website']) {
        $updateset[] = 'website= ' . sqlesc($website);
        $curuser_cache['website'] = $website;
        $user_cache['website'] = $website;
    }
    $action = 'social';
} elseif ($action === 'location') {
    if (isset($_POST['country']) && (($country = (int) $_POST['country']) != $user['country']) && is_valid_id($country)) {
        $updateset[] = "country = $country";
        $curuser_cache['country'] = $country;
        $user_cache['country'] = $country;
    }
    if (isset($_POST['language']) && (($language = $_POST['language']) != $user['language'])) {
        $updateset[] = 'language = ' . sqlesc($language);
        $curuser_cache['language'] = $language;
        $user_cache['language'] = $language;
    }
    if (isset($_POST['user_timezone']) && preg_match('#^\-?\d{1,2}(?:\.\d{1,2})?$#', $_POST['user_timezone'])) {
        $updateset[] = 'time_offset = ' . sqlesc($_POST['user_timezone']);
        $curuser_cache['time_offset'] = $_POST['user_timezone'];
        $user_cache['time_offset'] = $_POST['user_timezone'];
    }
    $updateset[] = 'auto_correct_dst = ' . (isset($_POST['checkdst']) ? 1 : 0);
    $updateset[] = 'dst_in_use = ' . (isset($_POST['manualdst']) ? 1 : 0);
    $curuser_cache['auto_correct_dst'] = (isset($_POST['checkdst']) ? 1 : 0);
    $user_cache['auto_correct_dst'] = (isset($_POST['checkdst']) ? 1 : 0);
    $curuser_cache['dst_in_use'] = (isset($_POST['manualdst']) ? 1 : 0);
    $user_cache['dst_in_use'] = (isset($_POST['manualdst']) ? 1 : 0);

    $action = 'location';
} elseif ($action === 'default') {
    if (isset($_POST['pm_on_delete']) && $_POST['pm_on_delete'] === 'yes') {
        $setbits2 |= class_user_options_2::PM_ON_DELETE;
    } elseif (isset($_POST['pm_on_delete']) && $_POST['pm_on_delete'] === 'no') {
        $clrbits2 |= class_user_options_2::PM_ON_DELETE;
    }
    if (isset($_POST['commentpm']) && $_POST['commentpm'] === 'yes') {
        $setbits2 |= class_user_options_2::COMMENTPM;
    } elseif (isset($_POST['commentpm']) && $_POST['commentpm'] === 'no') {
        $clrbits2 |= class_user_options_2::COMMENTPM;
    }

    $pmnotif = isset($_POST['pmnotif']) ? $_POST['pmnotif'] : '';
    $emailnotif = 'no';
    if (!empty($user['notifs']) && strpos($user['notifs'], '[email]') !== false) {
        $emailnotif = 'yes';
    }

    $notifs = ($pmnotif === 'yes' ? '[pm]' : '');
    $notifs .= ($emailnotif === 'yes' ? '[email]' : '');

    $updateset[] = 'notifs = ' . sqlesc($notifs);
    $curuser_cache['notifs'] = $notifs;
    $user_cache['notifs'] = $notifs;

    $acceptpms_choices = [
        'yes' => 1,
        'friends' => 2,
        'no' => 3,
    ];
    $acceptpms = (isset($_POST['acceptpms']) ? $_POST['acceptpms'] : 'all');
    if (isset($acceptpms_choices[$acceptpms])) {
        $updateset[] = 'acceptpms = ' . sqlesc($acceptpms);
    }
    $curuser_cache['acceptpms'] = $acceptpms;
    $user_cache['acceptpms'] = $acceptpms;
    $deletepms = isset($_POST['deletepms']) ? 'yes' : 'no';
    $updateset[] = "deletepms = '$deletepms'";
    $curuser_cache['deletepms'] = $deletepms;
    $user_cache['deletepms'] = $deletepms;
    $savepms = (isset($_POST['savepms']) && $_POST['savepms'] != '' ? 'yes' : 'no');
    $updateset[] = "savepms = '$savepms'";
    $curuser_cache['savepms'] = $savepms;
    $user_cache['savepms'] = $savepms;
    if (isset($_POST['subscription_pm']) && ($subscription_pm = $_POST['subscription_pm']) != $user['subscription_pm']) {
        $updateset[] = 'subscription_pm = ' . sqlesc($subscription_pm);
        $curuser_cache['subscription_pm'] = $subscription_pm;
        $user_cache['subscription_pm'] = $subscription_pm;
    }
    $action = 'default';
}

if ($user_cache) {
    $cache->update_row('user_' . $user['id'], $user_cache, $site_config['expires']['user_cache']);
}

if (!empty($updateset)) {
    sql_query('UPDATE users SET ' . implode(',', $updateset) . ' WHERE id=' . sqlesc($user['id'])) or sqlerr(__FILE__, __LINE__);
}
if ($setbits || $clrbits) {
    $sql = 'UPDATE users SET opt1 = ((opt1 | ' . $setbits . ') & ~' . $clrbits . ') WHERE id=' . sqlesc($user['id']);
    sql_query($sql) or sqlerr(__FILE__, __LINE__);
}
if ($setbits2 || $clrbits2) {
    $sql = 'UPDATE users SET opt2 = ((opt2 | ' . $setbits2 . ') & ~' . $clrbits2 . ') WHERE id=' . sqlesc($user['id']);
    sql_query($sql) or sqlerr(__FILE__, __LINE__);
}

$opt = $fluent->from('users')
              ->select(null)
              ->select('opt1')
              ->select('opt2')
              ->where('id = ?', $user['id'])
              ->fetch();

$cache->update_row('user_' . $user['id'], [
    'opt1' => $opt['opt1'],
    'opt2' => $opt['opt2'],
], $site_config['expires']['user_cache']);
$edited = $urladd === '&mailsent=1' ? 'action=security&mailsent=1' : "edited=1&action={$action}{$urladd}";
header("Location: {$site_config['paths']['baseurl']}/usercp.php?{$edited}");
