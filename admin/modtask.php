<?php

declare(strict_types = 1);

use Envms\FluentPDO\Literal;
use Pu239\Cache;
use Pu239\Database;
use Pu239\Message;
use Pu239\Roles;
use Pu239\Session;
use Pu239\User;

require_once INCL_DIR . 'function_users.php';
require_once CLASS_DIR . 'class_check.php';
require_once INCL_DIR . 'function_autopost.php';
require_once INCL_DIR . 'function_html.php';
require_once INCL_DIR . 'function_staff.php';
require_once CLASS_DIR . 'class_user_options.php';
require_once CLASS_DIR . 'class_user_options_2.php';
require_once INCL_DIR . 'function_password.php';
global $container, $CURUSER, $site_config;

$session = $container->get(Session::class);
if (empty($_POST)) {
    $_POST = $session->get('post_data');
} else {
    $session->set('post_data', $_POST);
}

class_check(UC_STAFF);
$session->unset('post_data');
$dt = TIME_NOW;
if ($CURUSER['class'] < UC_STAFF) {
    stderr(_('USER ERROR'), _('Please try again'));
}
if (!empty($_POST) && $_POST['action'] === 'edituser') {
    $post = $_POST;
    unset($_POST);
    $userid = !empty($post['userid']) ? (int) $post['userid'] : 0;
    if (!is_valid_id($userid)) {
        stderr(_('Error'), _('Invalid ID.'));
    }
    $users_class = $container->get(User::class);
    $user = $users_class->getUserFromId($userid);
    if ($CURUSER['id'] !== $userid && $CURUSER['class'] <= $user['class'] && $CURUSER['class'] < UC_MAX) {
        stderr(_('Error'), _('You cannot edit someone of the same or higher. Action logged.'));
    }
    if ($user['immunity'] >= 1 && $CURUSER['class'] < UC_MAX) {
        stderr(_('Error'), _('This user is immune to your commands!'));
    }
    $username = get_anonymous($CURUSER['id']) ? 'System' : htmlsafechars($CURUSER['username']);
    $modcomment = !empty($user['modcomment']) ? $user['modcomment'] : '';
    $cache = $container->get(Cache::class);
    $fluent = $container->get(Database::class);
    $update = $useredit = $msgs = [];
    if (($user['id'] !== $CURUSER['id']) || ($CURUSER['class'] === UC_MAX && $user['id'] === $CURUSER['id'])) {
        if ($CURUSER['class'] === UC_MAX) {
            $modcomment = $post['modcomment'];
            $update['modcomment'] = $modcomment;
        }
        $setbits = $clrbits = 0;
        if (isset($post['role_coder']) && $post['role_coder'] == 1 && !($user['roles_mask'] & Roles::CODER)) {
            $setbits |= Roles::CODER;
            $msgs[] = [
                'poster' => $CURUSER['id'],
                'receiver' => $userid,
                'added' => $dt,
                'msg' => _f("'CODER' Role has been added to your profile by %s", $username),
                'subject' => _('Role Added'),
            ];
            $modcomment = _f("%1$s - CODER Role Added by %2$s\n", get_date($dt, 'DATE', 1), $CURUSER['username']) . $modcomment;
        } elseif (!isset($post['role_coder']) && $user['roles_mask'] & Roles::CODER) {
            $clrbits |= Roles::CODER;
            $msgs[] = [
                'poster' => $CURUSER['id'],
                'receiver' => $userid,
                'added' => $dt,
                'msg' => _f("'CODER' Role has been removed from your profile by %s", $username),
                'subject' => _('Role Removed'),
            ];
            $modcomment = _f("%1$s - CODER Role Removed by %2$s\n", get_date($dt, 'DATE', 1), $CURUSER['username']) . $modcomment;
        }
        if (isset($post['role_uploader']) && $post['role_uploader'] == 1 && !($user['roles_mask'] & Roles::UPLOADER)) {
            $setbits |= Roles::UPLOADER;
            $msgs[] = [
                'poster' => $CURUSER['id'],
                'receiver' => $userid,
                'added' => $dt,
                'msg' => _f("'UPLOADER' Role has been added to your profile by %s", $username),
                'subject' => _('Role Added'),
            ];
            $modcomment = _f("%1$s - UPLOADER Role Added by %2$s\n", get_date($dt, 'DATE', 1), $CURUSER['username']) . $modcomment;
        } elseif (!isset($post['role_uploader']) && $user['roles_mask'] & Roles::UPLOADER) {
            $clrbits |= Roles::UPLOADER;
            $msgs[] = [
                'poster' => $CURUSER['id'],
                'receiver' => $userid,
                'added' => $dt,
                'msg' => _f("'UPLOADER' Role has been removed from your profile by %s", $username),
                'subject' => _('Role Removed'),
            ];
            $modcomment = _f("%1$s - UPLOADER Role Removed by %2$s\n", get_date($dt, 'DATE', 1), $CURUSER['username']) . $modcomment;
        }

        if (isset($post['role_forum_mod']) && $post['role_forum_mod'] == 1 && !($user['roles_mask'] & Roles::FORUM_MOD)) {
            $setbits |= Roles::FORUM_MOD;
            $msgs[] = [
                'poster' => $CURUSER['id'],
                'receiver' => $userid,
                'added' => $dt,
                'msg' => _f("'FORUM_MOD' Role has been added to your profile by %s", $username),
                'subject' => _('Role Added'),
            ];
            $modcomment = _f("%1$s - FORUM_MOD Role Added by %2$s\n", get_date($dt, 'DATE', 1), $CURUSER['username']) . $modcomment;
        } elseif (!isset($post['role_forum_mod']) && $user['roles_mask'] & Roles::FORUM_MOD) {
            $clrbits |= Roles::FORUM_MOD;
            $msgs[] = [
                'poster' => $CURUSER['id'],
                'receiver' => $userid,
                'added' => $dt,
                'msg' => _f("'FORUM_MOD' Role has been removed from your profile by %s", $username),
                'subject' => _('Role Removed'),
            ];
            $modcomment = _f("%1$s - FORUM_MOD Role Removed by %2$s\n", get_date($dt, 'DATE', 1), $CURUSER['username']) . $modcomment;
        }
        if (isset($post['role_torrent_mod']) && $post['role_torrent_mod'] == 1 && !($user['roles_mask'] & Roles::TORRENT_MOD)) {
            $setbits |= Roles::TORRENT_MOD;
            $msgs[] = [
                'poster' => $CURUSER['id'],
                'receiver' => $userid,
                'added' => $dt,
                'msg' => _f("'TORRENT_MOD' Role has been added to your profile by %s", $username),
                'subject' => _('Role Added'),
            ];
            $modcomment = _f("%1$s - TORRENT_MOD Role Added by %2$s\n", get_date($dt, 'DATE', 1), $CURUSER['username']) . $modcomment;
        } elseif (!isset($post['role_torrent_mod']) && $user['roles_mask'] & Roles::TORRENT_MOD) {
            $clrbits |= Roles::TORRENT_MOD;
            $msgs[] = [
                'poster' => $CURUSER['id'],
                'receiver' => $userid,
                'added' => $dt,
                'msg' => _f("'TORRENT_MOD' Role has been removed from your profile by %s", $username),
                'subject' => _('Role Removed'),
            ];
            $modcomment = _f("%1$s - TORRENT_MOD Role Removed by %2$s\n", get_date($dt, 'DATE', 1), $CURUSER['username']) . $modcomment;
        }
        if (isset($post['role_internal']) && $post['role_internal'] == 1 && !($user['roles_mask'] & Roles::INTERNAL)) {
            $setbits |= Roles::INTERNAL;
            $msgs[] = [
                'poster' => $CURUSER['id'],
                'receiver' => $userid,
                'added' => $dt,
                'msg' => _f("'INTERNAL' Role has been added to your profile by %s", $username),
                'subject' => _('Role Added'),
            ];
            $modcomment = _f("%1$s - INTERNAL Role Added by %2$s\n", get_date($dt, 'DATE', 1), $CURUSER['username']) . $modcomment;
        } elseif (!isset($post['role_internal']) && $user['roles_mask'] & Roles::INTERNAL) {
            $clrbits |= Roles::INTERNAL;
            $msgs[] = [
                'poster' => $CURUSER['id'],
                'receiver' => $userid,
                'added' => $dt,
                'msg' => _f("'INTERNAL' Role has been removed from your profile by %s", $username),
                'subject' => _('Role Removed'),
            ];
            $modcomment = _f("%1$s - INTERNAL Role Removed by %2$s\n", get_date($dt, 'DATE', 1), $CURUSER['username']) . $modcomment;
        }
        if ($setbits > 0 || $clrbits > 0) {
            $update['roles_mask'] = new Literal('((roles_mask | ' . $setbits . ') & ~' . $clrbits . ')');
        }
        if (isset($post['class']) && (($class = (int) $post['class']) !== $user['class'])) {
            if ($CURUSER['class'] !== UC_MAX && ($class === UC_MAX || $class >= $CURUSER['class'] || $user['class'] >= $CURUSER['class'])) {
                stderr(_('Error'), _('Please try again'));
            }
            if (!valid_class($class)) {
                stderr(_('Error'), _('Bad class'));
            }
            $what = $class > $user['class'] ? _('Promoted') : _('Demoted');
            $msgs[] = [
                'poster' => $CURUSER['id'],
                'receiver' => $userid,
                'added' => $dt,
                'msg' => _f("You have been %1$s to %2$s by %3$s", $what, get_user_class_name($class), $username),
                'subject' => _('Member Class Change'),
            ];
            $update['class'] = $class;
            $useredit[] = $what . _(' to ') . get_user_class_name($class);
            $modcomment = get_date($dt, 'DATE', 1) . " - $what " . _(' to ') . " '" . get_user_class_name($class) . "'" . _(' by ') . " {$CURUSER['username']}.\n" . $modcomment;
        }
    }
    if ((isset($post['donated'])) && (($donated = (int) $post['donated']) !== $user['donated'])) {
        $values = [
            'cash' => $donated,
            'user' => $userid,
            'added' => $dt,
        ];
        $cache->delete('totalfunds_');
        $fluent->insertInto('funds')
               ->values($values)
               ->execute();
        $update = [
            'donated' => $donated,
            'total_donated' => $user['total_donated'] + $donated,
        ];
    }
    if (isset($post['donorlength']) && (($donorlength = (int) $post['donorlength']))) {
        if ($donorlength > 0) {
            if ($donorlength === 255) {
                $msg = _f('You have received donor status from %s', $username);
                $subject = _('Thank you for your Donation!');
                $modcomment = get_date($dt, 'DATE', 1) . '' . _(' - Donor status set by ') . " {$CURUSER['username']}\n" . $modcomment;
                $donoruntil = $dt + (2607 * 604800);
            } else {
                $donoruntil = $dt + ($donorlength * 604800);
                $dur = $donorlength . _(' week') . ($donorlength > 1 ? _('s') : '');
                $msg = _('Dear') . $user['username'] . '' . _('
       :wave:
       Thanks for your support to Crafty!
       Your donation helps us in the costs of running the site!
       As a donor, you are given some bonus gigs added to your uploaded amount, the status of VIP, and the warm fuzzy feeling you get inside for helping to support this site that we all know and love :smile: so, thanks again, and enjoy!
       cheers,
       Crafty Staff
       PS. Your donator status will last for ') . " $dur " . _('and can be found on your user details page and can only be seen by you :smile: It was set by ') . '' . $username;
                $subject = _('Thank You for Your Donation!');
                $modcomment = get_date($dt, 'DATE', 1) . _(' - Donor status set by ') . $CURUSER['username'] . ".\n" . $modcomment;
            }
            $update['donoruntil'] = $donoruntil;
            $msgs[] = [
                'poster' => $CURUSER['id'],
                'receiver' => $userid,
                'added' => $dt,
                'msg' => $msg,
                'subject' => $subject,
            ];
            $update['donor'] = 'yes';
            $useredit[] = _('Donor = Yes');
            if ($user['class'] < UC_VIP) {
                $update['class'] = UC_VIP;
                $update['vipclass_before'] = $user['class'];
            }
        }
    }
    if (isset($post['donorlengthadd'])) {
        $donoruntil = $user['donoruntil'];
        $donorlengthadd = $post['donorlengthadd'] === 255 ? 2607 : $post['donorlengthadd'];
        $dur = $donorlengthadd . _(' week') . ($donorlengthadd > 1 ? _('s') : '');
        $msg = _('Dear') . htmlsafechars($user['username']) . '' . _('
       :wave:
       Thanks for your continued support to Crafty!
       Your donation helps us in the costs of running the site. Everything above the current running costs will go towards next months costs!
       As a donor, you are given some bonus gigs added to your uploaded amount, and, you have the the status of VIP, and the warm fuzzy feeling you get inside for helping to support this site that we all know and love :smile: so, thanks again, and enjoy! cheers,
       Crafty Staff
        PS. Your donator status will last for an extra ') . " $dur " . _(' on top of your current donation status, and can be found on your user details page and can only be seen by you :smile: It was set by ') . '' . $username;
        $subject = _('Thank You for Your Donation... Again!');
        $modcomment = get_date($dt, 'DATE', 1) . '' . _(' - Donator status set for another ') . " $dur " . _(' by ') . '' . $CURUSER['username'] . ".\n" . $modcomment;
        $msgs[] = [
            'poster' => $CURUSER['id'],
            'receiver' => $userid,
            'added' => $dt,
            'msg' => $msg,
            'subject' => $subject,
        ];
        $update['donoruntil'] = $user['donoruntil'] === 0 ? $dt + (604800 * $donorlengthadd) : $user['donoruntil'] + (604800 * $donorlengthadd);
    }
    if (isset($post['donor']) && (($donor = $post['donor']) !== $user['donor'])) {
        $update['donor'] = $donor;
        $update['class'] = $user['vipclass_before'];
        $useredit[] = _('Donor = No');
        if ($donor === 'no') {
            $modcomment = get_date($dt, 'DATE', 1) . '' . _(' - Donor status removed by ') . ' ' . $CURUSER['username'] . ".\n" . $modcomment;
            $msg = sprintf(_(' - Donor status removed by ')) . $username;
            $subject = _('Donator status expired.');
            $msgs[] = [
                'poster' => $CURUSER['id'],
                'receiver' => $userid,
                'added' => $dt,
                'msg' => $msg,
                'subject' => $subject,
            ];
        }
    }
    if (isset($post['downloadpos']) && ($downloadpos = (int) $post['downloadpos'])) {
        $disable_pm = '';
        if (isset($post['disable_pm'])) {
            $disable_pm = $post['disable_pm'];
        }
        $subject = _('Notification!');
        if ($downloadpos === 255) {
            $modcomment = get_date($dt, 'DATE', 1) . _(' - Download disablement by ') . $CURUSER['username'] . ".\n" . _('Reason:') . " $disable_pm\n" . $modcomment;
            $msg = _('Your Downloading rights have been disabled by ') . $username . (!empty($disable_pm) ? "\n\n" . _('Reason:') . " $disable_pm" : '');
            $update['downloadpos'] = 0;
            $useredit[] = _('Download possible = No');
        } elseif ($downloadpos === 42) {
            $modcomment = get_date($dt, 'DATE', 1) . _(' - Download disablement status removed by ') . $CURUSER['username'] . ".\n" . $modcomment;
            $msg = _('Your Downloading rights have been restored by ') . $username;
            $update['downloadpos'] = 1;
            $useredit[] = _('Download possible = Yes');
        } else {
            $downloadpos_until = $dt + ($downloadpos * 604800);
            $dur = $downloadpos . _(' week') . ($downloadpos > 1 ? _('s') : '');
            $msg = '' . _('You have received') . " $dur " . _('Download disablement from ') . ' ' . $username . ($disable_pm ? "\n\n" . _('Reason:') . " $disable_pm" : '');
            $modcomment = get_date($dt, 'DATE', 1) . '' . _(' - Download disablement for') . " $dur " . _(' by ') . '' . $CURUSER['username'] . ".\n" . _('Reason:') . " $disable_pm\n" . $modcomment;
            $update['downloadpos'] = $downloadpos_until;
            $useredit[] = _('Downloads disabled = ') . $downloadpos_until;
        }
        $msgs[] = [
            'poster' => $CURUSER['id'],
            'receiver' => $userid,
            'added' => $dt,
            'msg' => $msg,
            'subject' => $subject,
        ];
    }
    if (isset($post['uploadpos']) && ($uploadpos = (int) $post['uploadpos'])) {
        $updisable_pm = '';
        if (isset($post['updisable_pm'])) {
            $updisable_pm = $post['updisable_pm'];
        }
        $subject = _('Notification!');
        if ($uploadpos === 255) {
            $modcomment = get_date($dt, 'DATE', 1) . _(' - Upload disablement by ') . $CURUSER['username'] . ".\n" . _('Reason:') . " $updisable_pm\n" . $modcomment;
            $msg = _('Your Uploading rights have been disabled by ') . $username . (!empty($updisable_pm) ? "\n\n" . _('Reason:') . " $updisable_pm" : '');
            $update['uploadpos'] = 0;
            $useredit[] = _('Uploads enabled = No');
        } elseif ($uploadpos === 42) {
            $modcomment = get_date($dt, 'DATE', 1) . _(' - Upload disablement status removed by ') . $CURUSER['username'] . ".\n" . $modcomment;
            $msg = _('Your Uploading rights have been restored by ') . $username;
            $update['uploadpos'] = 1;
            $useredit[] = _('Uploads enabled = Yes');
        } else {
            $uploadpos_until = $dt + ($uploadpos * 604800);
            $dur = $uploadpos . _(' week') . ($uploadpos > 1 ? _('s') : '');
            $msg = '' . _('You have received') . " $dur " . _('Upload disablement from ') . '' . $username . ($updisable_pm ? "\n\n" . _('Reason:') . " $updisable_pm" : '');
            $modcomment = get_date($dt, 'DATE', 1) . '' . _(' - Upload disablement for') . " $dur " . _(' by ') . '' . $CURUSER['username'] . ".\n" . _('Reason:') . " $updisable_pm\n" . $modcomment;
            $update['uploadpos'] = $uploadpos_until;
            $useredit[] = _('Uploads disabled = ') . $uploadpos_until . '';
        }
        $msgs[] = [
            'poster' => $CURUSER['id'],
            'receiver' => $userid,
            'added' => $dt,
            'msg' => $msg,
            'subject' => $subject,
        ];
    }
    if (isset($post['sendpmpos']) && ($sendpmpos = (int) $post['sendpmpos'])) {
        $pmdisable_pm = '';
        if (isset($post['pmdisable_pm'])) {
            $pmdisable_pm = $post['pmdisable_pm'];
        }
        $subject = _('Notification!');
        if ($sendpmpos === 255) {
            $modcomment = get_date($dt, 'DATE', 1) . _(' - Pm disablement by ') . $CURUSER['username'] . ".\n" . _('Reason:') . " $pmdisable_pm\n" . $modcomment;
            $msg = _('Your Pm rights have been disabled by ') . $username . (!empty($pmdisable_pm) ? "\n\n" . _('Reason:') . " $pmdisable_pm" : '');
            $update['sendpmpos'] = 0;
            $useredit[] = _('Private messages enabled = No');
        } elseif ($sendpmpos === 42) {
            $modcomment = get_date($dt, 'DATE', 1) . _(' - Pm disablement status removed by ') . $CURUSER['username'] . ".\n" . $modcomment;
            $msg = _('Your Pm rights have been restored by ') . $username;
            $update['sendpmpos'] = 1;
            $useredit[] = _('Private messages enabled = Yes');
        } else {
            $sendpmpos_until = $dt + ($sendpmpos * 604800);
            $dur = $sendpmpos . _(' week') . ($sendpmpos > 1 ? _('s') : '');
            $msg = '' . _('You have received') . " $dur " . _('Pm disablement from ') . '' . $username . ($pmdisable_pm ? "\n\n" . _('Reason:') . " $pmdisable_pm" : '');
            $modcomment = get_date($dt, 'DATE', 1) . '' . _(' - Pm disablement for') . " $dur " . _(' by ') . '' . $CURUSER['username'] . ".\n" . _('Reason:') . " $pmdisable_pm\n" . $modcomment;
            $update['sendpmpos'] = $sendpmpos_until;
            $useredit[] = _('Private messages disabled = ') . $sendpmpos_until . '';
        }
        $msgs[] = [
            'poster' => $CURUSER['id'],
            'receiver' => $userid,
            'added' => $dt,
            'msg' => $msg,
            'subject' => $subject,
        ];
    }
    if (isset($post['chatpost']) && ($chatpost = (int) $post['chatpost'])) {
        $chatdisable_pm = '';
        if (isset($post['chatdisable_pm'])) {
            $chatdisable_pm = $post['chatdisable_pm'];
        }
        $subject = _('Notification!');
        if ($chatpost === 255) {
            $modcomment = get_date($dt, 'DATE', 1) . _(' - AJAX Chat disablement by ') . $CURUSER['username'] . ".\n" . _('Reason:') . " $chatdisable_pm\n" . $modcomment;
            $msg = _('Your AJAX Chat rights have been disabled by ') . $username . (!empty($chatdisable_pm) ? "\n\n" . _('Reason:') . " $chatdisable_pm" : '');
            $update['chatpost'] = 0;
            $useredit[] = _('AJAX Chat enabled = No');
        } elseif ($chatpost === 42) {
            $modcomment = get_date($dt, 'DATE', 1) . _(' - AJAX Chat disablement status removed by ') . $CURUSER['username'] . ".\n" . $modcomment;
            $msg = _('Your AJAX Chat rights have been restored by ') . $username;
            $update['chatpost'] = 1;
            $useredit[] = _('AJAX Chat enabled = Yes');
        } else {
            $chatpost_until = $dt + ($chatpost * 604800);
            $dur = $chatpost . _(' week') . ($chatpost > 1 ? _('s') : '');
            $msg = '' . _('You have received') . " $dur " . _('AJAX Chat disablement from ') . '' . $username . ($chatdisable_pm ? "\n\n" . _('Reason:') . " $chatdisable_pm" : '');
            $modcomment = get_date($dt, 'DATE', 1) . '' . _(' - AJAX Chat disablement for') . " $dur " . _(' by ') . '' . $CURUSER['username'] . ".\n" . _('Reason:') . " $chatdisable_pm\n" . $modcomment;
            $update['chatpost'] = $chatpost_until;
            $useredit[] = _('AJAX Chat disabled = ') . $chatpost_until;
        }
        $msgs[] = [
            'poster' => $CURUSER['id'],
            'receiver' => $userid,
            'added' => $dt,
            'msg' => $msg,
            'subject' => $subject,
        ];
    }
    if (isset($post['immunity']) && (($immunity = (int) $post['immunity']) !== $user['immunity'])) {
        $immunity_pm = '';
        if (isset($post['immunity_pm'])) {
            $immunity_pm = $post['immunity_pm'];
        }
        $subject = _('Notification!');
        if ($immunity === 255) {
            $modcomment = get_date($dt, 'DATE', 1) . _(' - Immune Status enabled by ') . $CURUSER['username'] . ".\n" . _('Reason:') . " $immunity_pm\n" . $modcomment;
            $msg = _('You have received immunity Status from ') . $username . (!empty($immunity_pm) ? "\n\n" . _('Reason:') . " $immunity_pm" : '');
            $update['immunity'] = 1;
        } elseif ($immunity === 42) {
            $modcomment = get_date($dt, 'DATE', 1) . _(' - Immunity Status removed by ') . $CURUSER['username'] . ".\n" . $modcomment;
            $msg = _('Your Immunity Status has been removed by ') . $username;
            $update['immunity'] = 0;
        } else {
            $immunity_until = $dt + ($immunity * 604800);
            $dur = $immunity . _(' week') . ($immunity > 1 ? _('s') : '');
            $msg = '' . _('You have received') . " $dur " . _('Immunity Status from ') . '' . $username . ($immunity_pm ? "\n\n" . _('Reason:') . " $immunity_pm" : '');
            $modcomment = get_date($dt, 'DATE', 1) . '' . _(' - Immunity Status for') . " $dur " . _(' by ') . '' . $CURUSER['username'] . ".\n" . _('Reason:') . " $immunity_pm\n" . $modcomment;
            $update['immunity'] = $immunity_until;
        }
        $msgs[] = [
            'poster' => $CURUSER['id'],
            'receiver' => $userid,
            'added' => $dt,
            'msg' => $msg,
            'subject' => $subject,
        ];
    }
    if (isset($post['leechwarn']) && (($leechwarn = (int) $post['leechwarn']) !== $user['leechwarn'])) {
        $leechwarn_pm = '';
        if (isset($post['leechwarn_pm'])) {
            $leechwarn_pm = $post['leechwarn_pm'];
        }
        $subject = _('Notification!');
        if ($leechwarn === 255) {
            $modcomment = get_date($dt, 'DATE', 1) . _(' - leechwarn Status enabled by ') . $CURUSER['username'] . ".\n" . _('Reason:') . " $leechwarn_pm\n" . $modcomment;
            $msg = _('You have received leechwarn Status from ') . $username . (!empty($leechwarn_pm) ? "\n\n" . _('Reason:') . " $leechwarn_pm" : '');
            $update['leechwarn'] = 1;
        } elseif ($leechwarn === 42) {
            $modcomment = get_date($dt, 'DATE', 1) . _(' - leechwarn Status removed by ') . $CURUSER['username'] . ".\n" . $modcomment;
            $msg = _('Your leechwarn Status has been removed by ') . $username;
            $update['leechwarn'] = 0;
        } else {
            $leechwarn_until = $dt + ($leechwarn * 604800);
            $dur = $leechwarn . _(' week') . ($leechwarn > 1 ? _('s') : '');
            $msg = '' . _('You have received') . " $dur " . _('leechwarn Status from ') . '' . $username . ($leechwarn_pm ? "\n\n" . _('Reason:') . " $leechwarn_pm" : '');
            $modcomment = get_date($dt, 'DATE', 1) . '' . _(' - leechwarn Status for') . " $dur " . _(' by ') . '' . $CURUSER['username'] . ".\n" . _('Reason:') . " $leechwarn_pm\n" . $modcomment;
            $update['leechwarn'] = $leechwarn_until;
        }
        $msgs[] = [
            'poster' => $CURUSER['id'],
            'receiver' => $userid,
            'added' => $dt,
            'msg' => $msg,
            'subject' => $subject,
        ];
    }
    if (isset($post['warned']) && (($warned = (int) $post['warned']) !== $user['warned'])) {
        $warned_pm = '';
        if (isset($post['warned_pm'])) {
            $warned_pm = $post['warned_pm'];
        }
        $subject = _('Notification!');
        if ($warned === 255) {
            $modcomment = get_date($dt, 'DATE', 1) . _(' - warned Status enabled by ') . $CURUSER['username'] . ".\n" . _('Reason:') . " $warned_pm\n" . $modcomment;
            $msg = _('You have received warned Status from ') . $username . (!empty($warned_pm) ? "\n\n" . _('Reason:') . " $warned_pm" : '');
            $update['warned'] = 1;
        } elseif ($warned === 42) {
            $modcomment = get_date($dt, 'DATE', 1) . _(' - warned Status removed by ') . $CURUSER['username'] . ".\n" . $modcomment;
            $msg = _('Your warned Status has been removed by ') . $username;
            $update['warned'] = 0;
        } else {
            $warned_until = $dt + ($warned * 604800);
            $dur = $warned . _(' week') . ($warned > 1 ? _('s') : '');
            $msg = '' . _('You have received') . " $dur " . _('warned Status from ') . '' . $username . ($warned_pm ? "\n\n" . _('Reason:') . " $warned_pm" : '');
            $modcomment = get_date($dt, 'DATE', 1) . '' . _(' - warned Status for') . " $dur " . _(' by ') . '' . $CURUSER['username'] . ".\n" . _('Reason:') . " $warned_pm\n" . $modcomment;
            $update['warned'] = $warned_until;
        }
        $msgs[] = [
            'poster' => $CURUSER['id'],
            'receiver' => $userid,
            'added' => $dt,
            'msg' => $msg,
            'subject' => $subject,
        ];
    }
    if (has_access($CURUSER['class'], UC_ADMINISTRATOR, 'coder')) {
        $uploadtoadd = (int) $post['amountup'];
        $downloadtoadd = (int) $post['amountdown'];
        $formatup = $post['formatup'];
        $formatdown = $post['formatdown'];
        $mpup = $post['upchange'];
        $mpdown = $post['downchange'];
        if ($uploadtoadd > 0) {
            if ($mpup === 'plus') {
                $newupload = $user['uploaded'] + ($formatup === 'mb' ? ($uploadtoadd * 1048576) : ($uploadtoadd * 1073741824));
                $modcomment = get_date($dt, 'DATE', 1) . ' ' . _(' - Added Upload') . ' (' . $uploadtoadd . ' ' . $formatup . ') ' . _('by') . ' ' . $CURUSER['username'] . "\n" . $modcomment;
            } else {
                $newupload = $user['uploaded'] - ($formatup === 'mb' ? ($uploadtoadd * 1048576) : ($uploadtoadd * 1073741824));
                $newupload = $newupload < 0 ? 0 : $newupload;
                if ($newupload >= 0) {
                    $modcomment = get_date($dt, 'DATE', 1) . ' ' . _(' - Subtracted Upload') . ' (' . $uploadtoadd . ' ' . $formatup . ') ' . _('by') . ' ' . $CURUSER['username'] . "\n" . $modcomment;
                }
            }
            $update['uploaded'] = $newupload;
            $useredit[] = _('Uploaded total altered from ') . mksize($uploadtoadd) . _(' to ') . mksize($newupload);
        }
        if ($downloadtoadd > 0) {
            if ($mpdown === 'plus') {
                $newdownload = $user['downloaded'] + ($formatdown === 'mb' ? ($downloadtoadd * 1048576) : ($downloadtoadd * 1073741824));
                $modcomment = get_date($dt, 'DATE', 1) . ' ' . _(' - Added Download') . ' (' . $downloadtoadd . ' ' . $formatdown . ') ' . _('by') . ' ' . $CURUSER['username'] . "\n" . $modcomment;
            } else {
                $newdownload = $user['downloaded'] - ($formatdown === 'mb' ? ($downloadtoadd * 1048576) : ($downloadtoadd * 1073741824));
                $newdownload = $newdownload < 0 ? 0 : $newdownload;
                if ($newdownload >= 0) {
                    $modcomment = get_date($dt, 'DATE', 1) . ' ' . _(' - Subtracted Download') . ' (' . $downloadtoadd . ' ' . $formatdown . ') ' . _('by') . ' ' . $CURUSER['username'] . "\n" . $modcomment;
                }
            }
            $update['downloaded'] = $newdownload;
            $useredit[] = _('Downloaded total altered from ') . mksize($downloadtoadd) . _(' to ') . mksize($newdownload);
        }
    }
    if (isset($post['title'])) {
        $curtitle = !empty($user['title']) ? $user['title'] : '';
        $title = $post['title'];
        if ($title != $curtitle) {
            $modcomment = get_date($dt, 'DATE', 1) . '' . _(' - Custom Title changed to ') . "'" . $title . "'" . _(' from ') . "'" . $curtitle . "' " . _('by') . ' ' . $CURUSER['username'] . ".\n" . $modcomment;
            $update['title'] = $title;
            $useredit[] = _('Custom title altered');
        }
    }
    if (!empty($post['reset_torrent_pass'])) {
        $newtorrentpass = make_password(32);
        $modcomment = get_date($dt, 'DATE', 1) . ' - ' . _('Torrent Pass') . " {$user['torrent_pass']} " . _('reset to') . " {$newtorrentpass} " . _('by') . ' ' . $CURUSER['username'] . ".\n" . $modcomment;
        $update['torrent_pass'] = $newtorrentpass;
        $useredit[] = '' . _('Torrent Pass') . " {$user['torrent_pass']} " . _('reset to') . " $newtorrentpass}";
    }
    if (!empty($post['reset_auth'])) {
        $newauthkey = make_password(32);
        $modcomment = get_date($dt, 'DATE', 1) . ' - ' . _('Auth Key ') . " {$user['auth']} " . _('reset to') . " {$newauthkey} " . _('by') . ' ' . $CURUSER['username'] . ".\n" . $modcomment;
        $update['auth'] = $newauthkey;
        $useredit[] = '' . _('Auth Key ') . " {$user['auth']} " . _('reset to') . " $newauthkey";
    }
    if (!empty($post['reset_apikey'])) {
        $newapikey = make_password(32);
        $modcomment = get_date($dt, 'DATE', 1) . ' - ' . _('APIKey ') . " {$user['apikey']} " . _('reset to') . " {$newapikey} " . _('by') . ' ' . $CURUSER['username'] . ".\n" . $modcomment;
        $update['apikey'] = $newapikey;
        $useredit[] = '' . _('APIKey ') . " {$user['apikey']} " . _('reset to') . " $newapikey";
    }
    if ((isset($post['seedbonus'])) && (($seedbonus = (int) $post['seedbonus']) !== (int) $user['seedbonus'])) {
        $modcomment = get_date($dt, 'DATE', 1) . _(' - Seedbonus amount changed to ') . $seedbonus . _(' from ') . $user['seedbonus'] . _(' by ') . $CURUSER['username'] . ".\n" . $modcomment;
        $update['seedbonus'] = $seedbonus;
        $useredit[] = _('Seedbonus points total adjusted');
    }
    if ((isset($post['reputation'])) && (($reputation = (int) $post['reputation']) !== $user['reputation'])) {
        $modcomment = get_date($dt, 'DATE', 1) . _(' - Reputation points changed to ') . $reputation . _(' from ') . $user['reputation'] . _(' by ') . $CURUSER['username'] . ".\n" . $modcomment;
        $update['reputation'] = $reputation;
        $useredit[] = _('Reputation points total adjusted');
    }
    if ((isset($_POST['addcomment'])) && ($addcomment = trim($_POST['addcomment']))) {
        $modcomment = get_date($dt, 'DATE', 1) . ' - ' . $addcomment . ' - ' . $CURUSER['username'] . ".\n" . $modcomment;
    }
    if ((isset($post['avatar'])) && (($avatar = $post['avatar']) !== $user['avatar'])) {
        $avatar = validate_url($avatar);
        if (!empty($avatar)) {
            $img_size = getimagesize($avatar);
            if ($img_size == false || !in_array($img_size['mime'], $site_config['images']['extensions'])) {
                stderr(_('Error'), _('Not an image or unsupported image type!'));
            }
            if ($img_size[0] < 100 || $img_size[1] < 100) {
                stderr(_('Error'), _('Image is too small'));
            }
        }
        $modcomment = get_date($dt, 'DATE', 1) . '' . _(' - Avatar changed from ') . '' . htmlsafechars((string) $user['avatar']) . '' . _(' to ') . '' . htmlsafechars((string) $avatar) . ' ' . _('by') . ' ' . $CURUSER['username'] . ".\n" . $modcomment;
        $update['avatar'] = !empty($avatar) ? $avatar : '';
        $useredit[] = _('Avatar changed');
    }
    if ((isset($post['signature'])) && (($signature = $post['signature']) !== $user['signature'])) {
        $signature = validate_url($signature);
        if (!empty($signature)) {
            $img_size = getimagesize($signature);
            if ($img_size == false || !in_array($img_size['mime'], $site_config['images']['extensions'])) {
                stderr(_('Error'), _('Not an image or unsupported image type!'));
            }
            if ($img_size[0] < 100 || $img_size[1] < 15) {
                stderr(_('Error'), _('Image is too small'));
            }
        }
        $modcomment = get_date($dt, 'DATE', 1) . '' . _(' - Signature changed from ') . '' . htmlsafechars((string) $user['signature']) . '' . _(' to ') . '' . htmlsafechars((string) $signature) . ' ' . _('by') . ' ' . $CURUSER['username'] . ".\n" . $modcomment;
        $update['signature'] = !empty($signature) ? $signature : '';
        $useredit[] = _('Signature changed');
    }
    if ((isset($post['invite_on'])) && (($invite_on = $post['invite_on']) != $user['invite_on'])) {
        $modcomment = get_date($dt, 'DATE', 1) . _(' - Invites allowed changed from ') . htmlsafechars((string) $user['invite_on']) . ' ' . _(' to ') . " $invite_on" . _(' by ') . '' . $CURUSER['username'] . ".\n" . $modcomment;
        $update['invite_on'] = $invite_on;
        $useredit[] = _('Invites enabled = ') . $invite_on;
    }
    if ((isset($post['invites'])) && (($invites = (int) $post['invites']) !== $user['invites'])) {
        $modcomment = get_date($dt, 'DATE', 1) . _(' - Invite amount changed to ') . $invites . _(' from ') . $user['invites'] . _(' by ') . $CURUSER['username'] . ".\n" . $modcomment;
        $update['invites'] = $invites;
        $useredit[] = _('Invites total adjusted');
    }
    if ((isset($post['support'])) && (($support = $post['support']) !== $user['support'])) {
        if ($support === 'yes') {
            $modcomment = get_date($dt, 'DATE', 1) . _(' - Promoted to FLS by ') . $CURUSER['username'] . ".\n" . $modcomment;
        } elseif ($support === 'no') {
            $modcomment = get_date($dt, 'DATE', 1) . _(' - Demoted from FLS by ') . $CURUSER['username'] . ".\n" . $modcomment;
        } else {
            stderr(_('Error'), _('Please try again'));
        }
        $supportfor = $post['supportfor'];
        $update['support'] = $support;
        $update['supportfor'] = $supportfor;
        $useredit[] = _('Support = ') . $support;
        $useredit[] = _('Support = ') . $supportfor;
    }
    if ((isset($post['freeslots'])) && (($freeslots = (int) $post['freeslots']) !== $user['freeslots'])) {
        $modcomment = get_date($dt, 'DATE', 1) . _(' - freeslots amount changed to ') . $freeslots . _(' from ') . $user['freeslots'] . _(' by ') . $CURUSER['username'] . ".\n" . $modcomment;
        $update['freeslots'] = $freeslots;
        $useredit[] = _('Freeeslots total adjusted = Yes');
    }
    if (isset($post['free_switch']) && ($free_switch = (int) $post['free_switch'])) {
        $free_pm = '';
        if (isset($post['free_pm'])) {
            $free_pm = $post['free_pm'];
        }
        $subject = _('Notification!');
        if ($free_switch === 255) {
            $modcomment = get_date($dt, 'DATE', 1) . _(' - Freeleech Status enabled by ') . $CURUSER['username'] . ".\n" . _('Reason:') . " $free_pm\n" . $modcomment;
            $msg = _('You have received Freeleech Status from ') . $username . (!empty($free_pm) ? "\n\n" . _('Reason:') . " $free_pm" : '');
            $update['free_switch'] = 1;
            $useredit[] = _('Freeleech enabled = Yes');
        } elseif ($free_switch === 42) {
            $modcomment = get_date($dt, 'DATE', 1) . _(' - Freeleech Status removed by ') . $CURUSER['username'] . ".\n" . $modcomment;
            $msg = _('Your Freeleech Status has been removed by ') . $username;
            $update['free_switch'] = 0;
            $useredit[] = _('Freeleech enabled = No');
        } else {
            $free_until = $dt + ($free_switch * 604800);
            $dur = $free_switch . _(' week') . ($free_switch > 1 ? _('s') : '');
            $msg = '' . _('You have received') . " $dur " . _('Freeleech Status from ') . 'Freeleech Status from ' . $username . ($free_pm ? "\n\n" . _('Reason:') . " $free_pm" : '');
            $modcomment = get_date($dt, 'DATE', 1) . '' . _(' - Freeleech Status for') . " $dur " . _(' by ') . '' . $CURUSER['username'] . ".\n" . _('Reason:') . " $free_pm\n" . $modcomment;
            $update['free_switch'] = $free_until;
            $useredit[] = _('Freeleech enabled = ') . get_date((int) $free_until, 'DATE', 0, 1);
        }
        $msgs[] = [
            'poster' => $CURUSER['id'],
            'receiver' => $userid,
            'added' => $dt,
            'msg' => $msg,
            'subject' => $subject,
        ];
    }
    if (isset($post['game_access']) && ($game_access = (int) $post['game_access'])) {
        $disable_pm = '';
        if (isset($post['game_disable_pm'])) {
            $disable_pm = $post['game_disable_pm'];
        }
        $subject = _('Notification!');
        if ($game_access === 255) {
            $modcomment = get_date($dt, 'DATE', 1) . _(' - Gaming disablement by ') . $CURUSER['username'] . ".\n" . _('Reason:') . "\n" . $modcomment;
            $msg = _('Your gaming rights have been disabled by ') . $username . "\n\n" . _('Reason:') . '';
            $update['game_access'] = 0;
            $useredit[] = _('Games possible = No');
        } elseif ($game_access === 42) {
            $modcomment = get_date($dt, 'DATE', 1) . _(' - Gaming disablement status removed by ') . $CURUSER['username'] . ".\n" . $modcomment;
            $msg = _('Your gaming rights have been restored by ') . $username;
            $update['game_access'] = 1;
            $useredit[] = _('Games possible = Yes');
        } else {
            $game_access_until = $dt + ($game_access * 604800);
            $dur = $game_access . _(' week') . ($game_access > 1 ? _('s') : '');
            $msg = '' . _('You have received') . " $dur " . _('games disablement from ') . '' . $username . "\n\n" . _('Reason:') . '';
            $modcomment = get_date($dt, 'DATE', 1) . '' . _(' - Games disablement for') . " $dur " . _(' by ') . '' . $CURUSER['username'] . ".\n" . _('Reason:') . "\n" . $modcomment;
            $update['game_access'] = $game_access_until;
            $useredit[] = _('Games disabled = ') . get_date((int) $game_access_until, 'DATE', 0, 1);
        }
        $msgs[] = [
            'poster' => $CURUSER['id'],
            'receiver' => $userid,
            'added' => $dt,
            'msg' => $msg,
            'subject' => _('Member Class Change'),
        ];
    }
    if (isset($post['avatarpos']) && ($avatarpos = (int) $post['avatarpos'])) {
        $avatardisable_pm = '';
        if (isset($post['avatardisable_pm'])) {
            $avatardisable_pm = $post['avatardisable_pm'];
        }
        $subject = _('Notification!');
        if ($avatarpos === 255) {
            $modcomment = get_date($dt, 'DATE', 1) . _(' - Avatar disablement by ') . $CURUSER['username'] . ".\n" . _('Reason:') . " $avatardisable_pm\n" . $modcomment;
            $msg = _('Your Avatar rights have been disabled by ') . $username . (!empty($avatardisable_pm) ? "\n\n" . _('Reason:') . " $avatardisable_pm" : '');
            $update['avatarpos'] = 0;
            $useredit[] = _('Avatars possible = No');
        } elseif ($avatarpos === 42) {
            $modcomment = get_date($dt, 'DATE', 1) . _(' - Avatar disablement status removed by ') . $CURUSER['username'] . ".\n" . $modcomment;
            $msg = _('Your Avatar rights have been restored by ') . $username;
            $update['avatarpos'] = 1;
            $useredit[] = _('Avatars possible = Yes');
        } else {
            $avatarpos_until = $dt + ($avatarpos * 604800);
            $dur = $avatarpos . _(' week') . ($avatarpos > 1 ? _('s') : '');
            $msg = '' . _('You have received') . " $dur " . _('Avatar disablement from ') . '' . $username . ($avatardisable_pm ? "\n\n" . _('Reason:') . " $avatardisable_pm" : '');
            $modcomment = get_date($dt, 'DATE', 1) . '' . _(' - Avatar disablement for') . " $dur " . _(' by ') . '' . $CURUSER['username'] . ".\n" . _('Reason:') . " $avatardisable_pm\n" . $modcomment;
            $update['avatarpos'] = $avatarpos_until;
            $useredit[] = _('Avatar selection disabled = ') . get_date((int) $avatarpos_until, 'DATE', 0, 1);
        }
        $msgs[] = [
            'poster' => $CURUSER['id'],
            'receiver' => $userid,
            'added' => $dt,
            'msg' => $msg,
            'subject' => $subject,
        ];
    }
    if ((isset($post['highspeed'])) && (($highspeed = $post['highspeed']) !== $user['highspeed'])) {
        if ($highspeed === 'yes') {
            $modcomment = get_date($dt, 'DATE', 1) . _(' - Highspeed Upload enabled by ') . $CURUSER['username'] . ".\n" . $modcomment;
            $subject = _('Highspeed uploader status.');
            $msg = _('You have been set as a high speed uploader by ') . $username . _('. You can now upload torrents using highspeeds without being flagged as a cheater.');
        } elseif ($highspeed === 'no') {
            $modcomment = get_date($dt, 'DATE', 1) . _(' - Highspeed Upload disabled by ') . $CURUSER['username'] . ".\n" . $modcomment;
            $subject = _('Highspeed uploader status.');
            $msg = _('Your highspeed upload setting has been disabled by ') . $username . _('. Please PM ') . $username . _(' for the reason why.');
        } else {
            stderr(_('Error'), _('Please try again'));
        }
        $update['highspeed'] = $highspeed;
        $useredit[] = _('Highspeed uploader enabled = ') . $highspeed;
        $msgs[] = [
            'poster' => $CURUSER['id'],
            'receiver' => $userid,
            'added' => $dt,
            'msg' => $msg,
            'subject' => $subject,
        ];
    }
    if ((isset($post['can_leech'])) && (($can_leech = (int) $post['can_leech']) !== $user['can_leech'])) {
        if ($can_leech === 1) {
            $modcomment = get_date($dt, 'DATE', 1) . _(' - Download enabled by ') . $CURUSER['username'] . ".\n" . $modcomment;
            $subject = _('Download status.');
            $msg = _('Your Downloads have been enabled by ') . $username . ' . ';
        } elseif ($can_leech === 0) {
            $modcomment = get_date($dt, 'DATE', 1) . _(' - Downloads disabled by ') . $CURUSER['username'] . ".\n" . $modcomment;
            $subject = _('Download status.');
            $msg = _('Your downloading ability has been disabled by ') . $username . _('. Please PM ') . $username . _(' for the reason why.');
        } else {
            stderr(_('Error'), _('Please try again'));
        }
        $update['can_leech'] = $can_leech;
        $useredit[] = _('Downloads edited = ') . $can_leech;
        $msgs[] = [
            'poster' => $CURUSER['id'],
            'receiver' => $userid,
            'added' => $dt,
            'msg' => $msg,
            'subject' => $subject,
        ];
    }
    if ((isset($post['wait_time'])) && (($wait_time = $post['wait_time']) !== $user['wait_time'])) {
        $modcomment = get_date($dt, 'DATE', 1) . '' . _(' - Wait time set to') . " $wait_time" . _('. was ') . '' . (int) $user['wait_time'] . _(' by ') . $CURUSER['username'] . ".\n" . $modcomment;
        $update['wait_time'] = $wait_time;
        $useredit[] = _('Wait time adjusted = Yes');
    }
    if ((isset($post['peers_limit'])) && (($peers_limit = $post['peers_limit']) !== $user['peers_limit'])) {
        $modcomment = get_date($dt, 'DATE', 1) . '' . _(' - Peers limit set to') . " $peers_limit" . _('. was ') . '' . (int) $user['peers_limit'] . _(' by ') . $CURUSER['username'] . ".\n" . $modcomment;
        $update['peers_limit'] = $peers_limit;
        $useredit[] = _('Peers limit adjusted = Yes');
    }
    if ((isset($post['torrents_limit'])) && (($torrents_limit = $post['torrents_limit']) !== $user['torrents_limit'])) {
        $modcomment = get_date($dt, 'DATE', 1) . '' . _(' - Torrents limit set to') . " $torrents_limit" . _('. was ') . '' . (int) $user['torrents_limit'] . _(' by ') . $CURUSER['username'] . ".\n" . $modcomment;
        $update['torrents_limit'] = $torrents_limit;
        $useredit[] = _('Torrents limit adjusted = Yes');
    }
    if (isset($post['status'])) {
        $status = (int) $post['status'];
        $userstatus = $user['status'];
        if ($status === 0) {
            $update['status'] = 0;
            $update['parked_until'] = 0;
            $update['downloadpos'] = isset($update['downloadpos']) ? $update['downloadpos'] : 1;
            $update['uploadpos'] = isset($update['uploadpos']) ? $update['uploadpos'] : 1;
            $update['sendpmpos'] = isset($update['sendpmpos']) ? $update['sendpmpos'] : 1;
            $update['game_access'] = isset($update['game_access']) ? $update['game_access'] : 1;
            $update['forum_post'] = isset($update['forum_post']) ? $update['forum_post'] : 'yes';
            $update['invite_on'] = isset($update['invite_on']) ? $update['invite_on'] : 'yes';
            $update['chatpost'] = isset($update['chatpost']) ? $update['chatpost'] : 1;
        } elseif ($status === 2 || $status === 3) {
            $update['downloadpos'] = 0;
            $update['uploadpos'] = 0;
            $update['game_access'] = 0;
            $update['forum_post'] = 'no';
            $update['invite_on'] = 'no';
            $update['chatpost'] = 0;
        }
        if ($status === 1) {
            $update['status'] = 1;
            $modcomment = get_date($dt, 'DATE', 1) . _(' - Account Parked by ') . $CURUSER['username'] . ".\n" . $modcomment;
            $useredit[] = _('Account parked = ') . 'yes';
        } elseif ($status === 2) {
            $update['status'] = 2;
            $modcomment = get_date($dt, 'DATE', 1) . ' ' . _('- Disabled by ') . ' ' . $CURUSER['username'] . ".\n" . $modcomment;
            $useredit[] = _('Enabled = ') . 'no';
            $fluent->deleteFrom('ajax_chat_online')
                   ->where('userID = ?', $userid)
                   ->execute();
            $cache->set('forced_logout_' . $userid, $dt);
        } elseif ($status === 5) {
            $update['status'] = 5;
            $suspended_reason = $post['suspended_reason'];
            if (!$suspended_reason) {
                stderr(_('Error'), _('You must enter a reason to suspend this account!'));
            }
            $modcomment = get_date($dt, 'DATE', 1) . _(' - This account has been suspended by ') . $CURUSER['username'] . _(' reason: ') . $suspended_reason . ".\n" . $modcomment;
            $useredit[] = _('Account suspended = Yes');
            $subject = _('Account Suspended!');
            $msg = _('Your account has been suspended by ') . $username . ".\n[b]" . _('The Reason:') . "[/b]\n{$suspended_reason}.\n\n" . _('While your account is suspended, your posting - uploading - downloading - commenting - invites will not work, and the only people that you can PM are staff members.') . "\n\n" . _('If you feel this suspension is in error, please feel free to contact a staff member. ') . "\n\n" . _('cheers,') . "\n" . $site_config['site']['name'] . _(' Staff');
            $body = '' . _('Account for ') . '[b][url=' . $site_config['paths']['baseurl'] . '/userdetails.php?id=' . (int) $user['id'] . ']' . htmlsafechars($user['username']) . '[/url][/b]' . _(' has been suspended by ') . '' . $CURUSER['username'] . "\n\n [b]" . _(' reason: ') . "[/b]\n " . $suspended_reason;
            auto_post(_('Account Suspended!'), $body);
            $msgs[] = [
                'poster' => $CURUSER['id'],
                'receiver' => $userid,
                'added' => $dt,
                'msg' => $msg,
                'subject' => $subject,
            ];
        } else {
            if ($userstatus === 1) {
                $modcomment = get_date($dt, 'DATE', 1) . _(' - Account UnParked by ') . $CURUSER['username'] . ".\n" . $modcomment;
                $useredit[] = _('Account parked = ') . 'no';
            } elseif ($userstatus === 2) {
                $modcomment = get_date($dt, 'DATE', 1) . ' ' . _('- Enabled by ') . ' ' . $CURUSER['username'] . ".\n" . $modcomment;
                $useredit[] = _('Enabled = ') . 'yes';
            } elseif ($userstatus === 5) {
                $modcomment = get_date($dt, 'DATE', 1) . _(' - This account has been Un-suspended by ') . $CURUSER['username'] . ".\n" . $modcomment;
                $useredit[] = _('Account suspended = No');
                $subject = _('Account Un-Suspended!');
                $msg = _("Your account has had it's suspension lifted by ") . $username . ".\n\n" . _('cheers,') . "\n" . $site_config['site']['name'] . _(' Staff');
                $msgs[] = [
                    'poster' => $CURUSER['id'],
                    'receiver' => $userid,
                    'added' => $dt,
                    'msg' => $msg,
                    'subject' => $subject,
                ];
            }
        }
    }
    if ((isset($post['hit_and_run_total'])) && (($hit_and_run_total = (int) $post['hit_and_run_total']) !== $user['hit_and_run_total'])) {
        $modcomment = get_date($dt, 'DATE', 1) . '' . _(' - Hit and runs set to ') . " $hit_and_run_total" . _('. was ') . '' . (int) $user['hit_and_run_total'] . _(' by ') . $CURUSER['username'] . ".\n" . $modcomment;
        $update['hit_and_run_total'] = $hit_and_run_total;
        $useredit[] = _('Hit and run total adjusted = Yes');
    }
    if ((isset($post['forum_post'])) && (($forum_post = $post['forum_post']) !== $user['forum_post'])) {
        if ($forum_post === 'yes') {
            $modcomment = get_date($dt, 'DATE', 1) . _(' - Posting enabled by ') . $CURUSER['username'] . ".\n" . $modcomment;
            $msg = _('Your Posting rights have been given back by ') . $username . _('. You can post to forum again.');
        } else {
            $modcomment = get_date($dt, 'DATE', 1) . _(' - Posting disabled by ') . $CURUSER['username'] . ".\n" . $modcomment;
            $msg = _('Your Posting rights have been removed by ') . $username . _(', Please PM ') . $username . _(' for the reason why.');
        }
        $msgs[] = [
            'poster' => $CURUSER['id'],
            'receiver' => $userid,
            'added' => $dt,
            'msg' => $msg,
            'subject' => _('Posting rights'),
        ];
        $update['forum_post'] = $forum_post;
        $useredit[] = _('Forum post enabled = ') . $forum_post;
    }
    if ((isset($post['signature_post'])) && (($signature_post = $post['signature_post']) !== $user['signature_post'])) {
        if ($signature_post === 'no') {
            $modcomment = get_date($dt, 'DATE', 1) . _(' - Signature rights turned off by ') . $CURUSER['username'] . ".\n" . $modcomment;
            $msg = _('Your Signature rights turned off by ') . $username . _('. PM them for more information.');
        } else {
            $modcomment = get_date($dt, 'DATE', 1) . _(' - Signature rights turned on by ') . $CURUSER['username'] . ".\n" . $modcomment;
            $msg = _('Your Signature rights turned back on by ') . $username . '.';
        }
        $msgs[] = [
            'poster' => $CURUSER['id'],
            'receiver' => $userid,
            'added' => $dt,
            'msg' => $msg,
            'subject' => _('Signature rights'),
        ];
        $update['signature_post'] = $signature_post;
        $useredit[] = _('Signature post enabled = ') . $signature_post;
    }
    if ((isset($post['avatar_rights'])) && (($avatar_rights = $post['avatar_rights']) !== $user['avatar_rights'])) {
        if ($avatar_rights === 'no') {
            $modcomment = get_date($dt, 'DATE', 1) . _(' - Avatar rights turned off by ') . $CURUSER['username'] . ".\n" . $modcomment;
        } else {
            $modcomment = get_date($dt, 'DATE', 1) . _(' - Avatar rights turned on by ') . $CURUSER['username'] . ".\n" . $modcomment;
        }
        $update['avatar_rights'] = $avatar_rights;
        $useredit[] = _('Avatar rights enabled = ') . $avatar_rights;
    }
    if ((isset($post['offensive_avatar'])) && (($offensive_avatar = $post['offensive_avatar']) !== $user['offensive_avatar'])) {
        if ($offensive_avatar === 'no') {
            $modcomment = get_date($dt, 'DATE', 1) . _(' - Offensive avatar set to no by ') . $CURUSER['username'] . ".\n" . $modcomment;
            $msg = _('Your avatar has been set to not offensive by ') . $username;
        } else {
            $modcomment = get_date($dt, 'DATE', 1) . _(' - Offensive avatar set to yes by ') . $CURUSER['username'] . ".\n" . $modcomment;
            $msg = _('Your avatar has been set to offensive by ') . $username . _(' PM them to ask why.');
        }
        $msgs[] = [
            'poster' => $CURUSER['id'],
            'receiver' => $userid,
            'added' => $dt,
            'msg' => $msg,
            'subject' => _('Member Class Change'),
        ];
        $update['offensive_avatar'] = $offensive_avatar;
        $useredit[] = _('Offensive avatar enabled = ') . $offensive_avatar;
    }
    if ((isset($post['view_offensive_avatar'])) && (($view_offensive_avatar = $post['view_offensive_avatar']) !== $user['view_offensive_avatar'])) {
        if ($view_offensive_avatar === 'no') {
            $modcomment = get_date($dt, 'DATE', 1) . _f("%1$s view offensive avatar by %2$s, %3$s\n", 'Set', $CURUSER['username'], $modcomment);
        } else {
            $modcomment = get_date($dt, 'DATE', 1) . _f("%1$s view offensive avatar by %2$s, %3$s\n", 'Unset', $CURUSER['username'], $modcomment);
        }
        $update['view_offensive_avatar'] = $view_offensive_avatar;
        $useredit[] = _f('View offensive avatars = %s', $view_offensive_avatar);
    }
    if ((isset($post['paranoia'])) && (($paranoia = (int) $post['paranoia']) !== $user['paranoia'])) {
        $modcomment = get_date($dt, 'DATE', 1) . _(' - Paranoia changed to ') . (int) $post['paranoia'] . _(' from ') . (int) $user['paranoia'] . _(' by ') . $CURUSER['username'] . ".\n" . $modcomment;
        $update['paranoia'] = $paranoia;
        $useredit[] = _('Paranoia level changed');
    }
    if ((isset($post['website'])) && (($website = $post['website']) !== $user['website'])) {
        $modcomment = get_date($dt, 'DATE', 1) . _(' - website changed to ') . strip_tags($post['website']) . _(' from ') . htmlsafechars((string) $user['website']) . _(' by ') . $CURUSER['username'] . ".\n" . $modcomment;
        $update['website'] = $website;
        $useredit[] = _('Website changed');
    }
    if ((isset($post['skype'])) && (($skype = $post['skype']) !== $user['skype'])) {
        $modcomment = get_date($dt, 'DATE', 1) . _(' - skype changed to ') . strip_tags($post['skype']) . _(' from ') . htmlsafechars((string) $user['skype']) . _(' by ') . $CURUSER['username'] . ".\n" . $modcomment;
        $update['skype'] = $skype;
        $useredit[] = _('Skype address changed');
    }
    if (!empty($update)) {
        $update['modcomment'] = $modcomment;
        $users_class->update($update, $userid, false);
        if (isset($post['class']) && $post['class'] !== $user['class']) {
            $cache->delete('is_staff_');
        }
        $cache->deleteMulti([
            'last24_users_',
            'birthdayusers_',
            'ircusers_',
            'activeusers_',
            'chat_users_list_',
        ]);
    }
    if (!empty($msgs)) {
        $messages = $container->get(Message::class);
        $messages->insert($msgs);
    }
    if (!empty($useredit)) {
        write_info('' . _('User account') . " $userid (" . format_username((int) $userid) . ")\n" . _('Things edited: ') . '' . implode(', ', $useredit) . '' . _(' by ') . '' . format_username((int) $CURUSER['id']));
    }
    $returnto = htmlsafechars($post['returnto']) . '#edit';
    header("Location: {$site_config['paths']['baseurl']}/$returnto");
}

stderr(_('Error'), _('No idea what to do'));
