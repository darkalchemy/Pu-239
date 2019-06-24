<?php

declare(strict_types = 1);

use Pu239\Cache;
use Pu239\Database;
use Pu239\Message;
use Pu239\User;

require_once INCL_DIR . 'function_users.php';
require_once CLASS_DIR . 'class_check.php';
require_once INCL_DIR . 'function_autopost.php';
require_once INCL_DIR . 'function_html.php';
require_once INCL_DIR . 'function_staff.php';
require_once CLASS_DIR . 'class_user_options.php';
require_once CLASS_DIR . 'class_user_options_2.php';
class_check(UC_STAFF);
$lang = array_merge($lang, load_language('modtask'));

$dt = TIME_NOW;
global $CURUSER, $site_config;

if ($CURUSER['class'] < UC_STAFF) {
    stderr("{$lang['modtask_user_error']}", "{$lang['modtask_try_again']}");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'edituser') {
    $post = $_POST;
    unset($_POST);
    $userid = !empty($post['userid']) ? (int) $post['userid'] : 0;
    if (!is_valid_id($userid)) {
        stderr($lang['modtask_error'], $lang['modtask_bad_id']);
    }
    $users_class = $container->get(User::class);
    $user = $users_class->getUserFromId($userid);
    if ($CURUSER['id'] !== $userid && $CURUSER['class'] <= $user['class'] && $CURUSER['class'] < UC_MAX) {
        stderr($lang['modtask_error'], $lang['modtask_cannot_edit']);
    }
    if ($user['immunity'] >= 1 && $CURUSER['class'] < UC_MAX) {
        stderr($lang['modtask_error'], $lang['modtask_user_immune']);
    }
    $username = $CURUSER['perms'] & PERMS_STEALTH ? 'System' : htmlsafechars($CURUSER['username']);
    $modcomment = !empty($user['modcomment']) ? $user['modcomment'] : '';
    $cache = $container->get(Cache::class);
    $fluent = $container->get(Database::class);

    if ($user['id'] !== $CURUSER['id']) {
        if ($CURUSER['class'] === UC_MAX) {
            $modcomment = $post['modcomment'];
            $update['modcomment'] = $modcomment;
        }
        if (isset($post['class']) && (($class = (int) $post['class']) !== $user['class'])) {
            if ($CURUSER['class'] !== UC_MAX && ($class === UC_MAX || $class >= $CURUSER['class'] || $user['class'] >= $CURUSER['class'])) {
                stderr($lang['modtask_user_error'], $lang['modtask_try_again']);
            }
            if (!valid_class($class)) {
                stderr(($lang['modtask_error']), $lang['modtask_badclass']);
            }
            $what = $class > $user['class'] ? $lang['modtask_promoted'] : $lang['modtask_demoted'];
            $msgs[] = [
                'poster' => $CURUSER['id'],
                'receiver' => $userid,
                'added' => $dt,
                'msg' => sprintf($lang['modtask_have_been'], $what) . ' ' . get_user_class_name($class) . " {$lang['modtask_by']} " . $username,
                'subject' => $lang['modtask_cls_change'],
            ];
            $update['class'] = $class;
            $useredit[] = $what . $lang['modtask_to'] . get_user_class_name($class);
            $modcomment = get_date($dt, 'DATE', 1) . " - $what {$lang['modtask_to']} '" . get_user_class_name($class) . "'{$lang['modtask_gl_by']} {$CURUSER['username']}.\n" . $modcomment;
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
                $msg = $lang['modtask_donor_received'] . $username;
                $subject = $lang['modtask_donor_subject'];
                $modcomment = get_date($dt, 'DATE', 1) . "{$lang['modtask_donor_set']} {$CURUSER['username']}\n" . $modcomment;
                $donoruntil = $dt + (2607 * 604800);
            } else {
                $donoruntil = $dt + ($donorlength * 604800);
                $dur = $donorlength . $lang['modtask_donor_week'] . ($donorlength > 1 ? $lang['modtask_donor_weeks'] : '');
                $msg = $lang['modtask_donor_dear'] . $user['username'] . "{$lang['modtask_donor_msg']} $dur {$lang['modtask_donor_msg1']}" . $username;
                $subject = $lang['modtask_donor_subject'];
                $modcomment = get_date($dt, 'DATE', 1) . "{$lang['modtask_donor_set']}" . $CURUSER['username'] . ".\n" . $modcomment;
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
            $useredit[] = $lang['modtask_donor_yes'];
            if ($user['class'] < UC_VIP) {
                $update['class'] = UC_VIP;
                $update['vipclass_before'] = $user['class'];
            }
        }
    }
    if (isset($post['donorlengthadd'])) {
        $donoruntil = $user['donoruntil'];
        $donorlengthadd = $post['donorlengthadd'] === 255 ? 2607 : $post['donorlengthadd'];
        $dur = $donorlengthadd . $lang['modtask_donor_week'] . ($donorlengthadd > 1 ? $lang['modtask_donor_weeks'] : '');
        $msg = $lang['modtask_donor_dear'] . htmlsafechars($user['username']) . "{$lang['modtask_donor_msg2']} $dur {$lang['modtask_donor_msg3']}" . $username;
        $subject = $lang['modtask_donor_subject_again'];
        $modcomment = get_date($dt, 'DATE', 1) . "{$lang['modtask_donor_set_another']} $dur {$lang['modtask_gl_by']}" . $CURUSER['username'] . ".\n" . $modcomment;
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
        $useredit[] = $lang['modtask_donor_no'];
        if ($donor === 'no') {
            $modcomment = get_date($dt, 'DATE', 1) . "{$lang['modtask_donor_removed']} " . $CURUSER['username'] . ".\n" . $modcomment;
            $msg = sprintf($lang['modtask_donor_removed']) . $username;
            $subject = $lang['modtask_donor_subject_expire'];
            $msgs[] = [
                'poster' => $CURUSER['id'],
                'receiver' => $userid,
                'added' => $dt,
                'msg' => $msg,
                'subject' => $subject,
            ];
        }
    }
    if ((isset($post['enabled'])) && (($enabled = $post['enabled']) !== $user['enabled'])) {
        $modcomment = get_date($dt, 'DATE', 1) . ' ' . ($enabled === 'yes' ? $lang['modtask_enabled'] : $lang['modtask_disabled']) . ' ' . $CURUSER['username'] . ".\n" . $modcomment;
        $update['enabled'] = $enabled;
        $useredit[] = $lang['modtask_enabled_disabled'] . $enabled;
        $fluent->deleteFrom('ajax_chat_online')
               ->where('userID = ?', $userid)
               ->execute();
        $cache->set('forced_logout_' . $userid, $dt);
    }
    if (isset($post['downloadpos']) && ($downloadpos = (int) $post['downloadpos'])) {
        $disable_pm = '';
        if (isset($post['disable_pm'])) {
            $disable_pm = $post['disable_pm'];
        }
        $subject = $lang['modtask_gl_notification'];
        if ($downloadpos === 255) {
            $modcomment = get_date($dt, 'DATE', 1) . $lang['modtask_down_dis_by'] . $CURUSER['username'] . ".\n{$lang['modtask_gl_reason']} $disable_pm\n" . $modcomment;
            $msg = $lang['modtask_down_dis_right'] . $username . (!empty($disable_pm) ? "\n\n{$lang['modtask_gl_reason']} $disable_pm" : '');
            $update['downloadpos'] = 0;
            $useredit[] = $lang['modtask_down_pos_no'];
        } elseif ($downloadpos === 42) {
            $modcomment = get_date($dt, 'DATE', 1) . $lang['modtask_down_dis_status'] . $CURUSER['username'] . ".\n" . $modcomment;
            $msg = $lang['modtask_down_res_by'] . $username;
            $update['downloadpos'] = 1;
            $useredit[] = $lang['modtask_down_pos_yes'];
        } else {
            $downloadpos_until = $dt + ($downloadpos * 604800);
            $dur = $downloadpos . $lang['modtask_gl_week'] . ($downloadpos > 1 ? $lang['modtask_gl_weeks'] : '');
            $msg = "{$lang['modtask_gl_received']} $dur {$lang['modtask_down_dis_from']} " . $username . ($disable_pm ? "\n\n{$lang['modtask_gl_reason']} $disable_pm" : '');
            $modcomment = get_date($dt, 'DATE', 1) . "{$lang['modtask_down_dis_for']} $dur {$lang['modtask_gl_by']}" . $CURUSER['username'] . ".\n{$lang['modtask_gl_reason']} $disable_pm\n" . $modcomment;
            $update['downloadpos'] = $downloadpos_until;
            $useredit[] = $lang['modtask_down_disabled'] . $downloadpos_until;
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
        $subject = $lang['modtask_gl_notification'];
        if ($uploadpos === 255) {
            $modcomment = get_date($dt, 'DATE', 1) . $lang['modtask_up_dis_by'] . $CURUSER['username'] . ".\n{$lang['modtask_gl_reason']} $updisable_pm\n" . $modcomment;
            $msg = $lang['modtask_up_dis_right'] . $username . (!empty($updisable_pm) ? "\n\n{$lang['modtask_gl_reason']} $updisable_pm" : '');
            $update['uploadpos'] = 0;
            $useredit[] = $lang['modtask_up_pos_no'];
        } elseif ($uploadpos === 42) {
            $modcomment = get_date($dt, 'DATE', 1) . $lang['modtask_up_dis_status'] . $CURUSER['username'] . ".\n" . $modcomment;
            $msg = $lang['modtask_up_res_by'] . $username;
            $update['uploadpos'] = 1;
            $useredit[] = $lang['modtask_up_pos_yes'];
        } else {
            $uploadpos_until = $dt + ($uploadpos * 604800);
            $dur = $uploadpos . $lang['modtask_gl_week'] . ($uploadpos > 1 ? $lang['modtask_gl_weeks'] : '');
            $msg = "{$lang['modtask_gl_received']} $dur {$lang['modtask_up_dis_from']}" . $username . ($updisable_pm ? "\n\n{$lang['modtask_gl_reason']} $updisable_pm" : '');
            $modcomment = get_date($dt, 'DATE', 1) . "{$lang['modtask_up_dis_for']} $dur {$lang['modtask_gl_by']}" . $CURUSER['username'] . ".\n{$lang['modtask_gl_reason']} $updisable_pm\n" . $modcomment;
            $update['uploadpos'] = $uploadpos_until;
            $useredit[] = $lang['modtask_up_disabled'] . $uploadpos_until . '';
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
        $subject = $lang['modtask_gl_notification'];
        if ($sendpmpos === 255) {
            $modcomment = get_date($dt, 'DATE', 1) . $lang['modtask_pm_dis_by'] . $CURUSER['username'] . ".\n{$lang['modtask_gl_reason']} $pmdisable_pm\n" . $modcomment;
            $msg = $lang['modtask_pm_dis_right'] . $username . (!empty($pmdisable_pm) ? "\n\n{$lang['modtask_gl_reason']} $pmdisable_pm" : '');
            $update['sendpmpos'] = 0;
            $useredit[] = $lang['modtask_pm_pos_no'];
        } elseif ($sendpmpos === 42) {
            $modcomment = get_date($dt, 'DATE', 1) . $lang['modtask_pm_dis_status'] . $CURUSER['username'] . ".\n" . $modcomment;
            $msg = $lang['modtask_pm_res_by'] . $username;
            $update['sendpmpos'] = 1;
            $useredit[] = $lang['modtask_pm_pos_yes'];
        } else {
            $sendpmpos_until = $dt + ($sendpmpos * 604800);
            $dur = $sendpmpos . $lang['modtask_gl_week'] . ($sendpmpos > 1 ? $lang['modtask_gl_weeks'] : '');
            $msg = "{$lang['modtask_gl_received']} $dur {$lang['modtask_pm_dis_from']}" . $username . ($pmdisable_pm ? "\n\n{$lang['modtask_gl_reason']} $pmdisable_pm" : '');
            $modcomment = get_date($dt, 'DATE', 1) . "{$lang['modtask_pm_dis_for']} $dur {$lang['modtask_gl_by']}" . $CURUSER['username'] . ".\n{$lang['modtask_gl_reason']} $pmdisable_pm\n" . $modcomment;
            $update['sendpmpos'] = $sendpmpos_until;
            $useredit[] = $lang['modtask_pm_disabled'] . $sendpmpos_until . '';
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
        $subject = $lang['modtask_gl_notification'];
        if ($chatpost === 255) {
            $modcomment = get_date($dt, 'DATE', 1) . $lang['modtask_ajaxchat_dis_by'] . $CURUSER['username'] . ".\n{$lang['modtask_gl_reason']} $chatdisable_pm\n" . $modcomment;
            $msg = $lang['modtask_ajaxchat_dis_right'] . $username . (!empty($chatdisable_pm) ? "\n\n{$lang['modtask_gl_reason']} $chatdisable_pm" : '');
            $update['chatpost'] = 0;
            $useredit[] = $lang['modtask_ajaxchat_pos_no'];
        } elseif ($chatpost === 42) {
            $modcomment = get_date($dt, 'DATE', 1) . $lang['modtask_ajaxchat_dis_status'] . $CURUSER['username'] . ".\n" . $modcomment;
            $msg = $lang['modtask_ajaxchat_res_by'] . $username;
            $update['chatpost'] = 1;
            $useredit[] = $lang['modtask_ajaxchat_pos_yes'];
        } else {
            $chatpost_until = $dt + ($chatpost * 604800);
            $dur = $chatpost . $lang['modtask_gl_week'] . ($chatpost > 1 ? $lang['modtask_gl_weeks'] : '');
            $msg = "{$lang['modtask_gl_received']} $dur {$lang['modtask_ajaxchat_dis_from']}" . $username . ($chatdisable_pm ? "\n\n{$lang['modtask_gl_reason']} $chatdisable_pm" : '');
            $modcomment = get_date($dt, 'DATE', 1) . "{$lang['modtask_ajaxchat_dis_for']} $dur {$lang['modtask_gl_by']}" . $CURUSER['username'] . ".\n{$lang['modtask_gl_reason']} $chatdisable_pm\n" . $modcomment;
            $update['chatpost'] = $chatpost_until;
            $useredit[] = $lang['modtask_ajaxchat_disabled'] . $chatpost_until;
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
        $subject = $lang['modtask_gl_notification'];
        if ($immunity === 255) {
            $modcomment = get_date($dt, 'DATE', 1) . $lang['modtask_immune_status'] . $CURUSER['username'] . ".\n{$lang['modtask_gl_reason']} $immunity_pm\n" . $modcomment;
            $msg = $lang['modtask_immune_received'] . $username . (!empty($immunity_pm) ? "\n\n{$lang['modtask_gl_reason']} $immunity_pm" : '');
            $update['immunity'] = 1;
        } elseif ($immunity === 42) {
            $modcomment = get_date($dt, 'DATE', 1) . $lang['modtask_immune_remove'] . $CURUSER['username'] . ".\n" . $modcomment;
            $msg = $lang['modtask_immune_removed'] . $username;
            $update['immunity'] = 0;
        } else {
            $immunity_until = $dt + ($immunity * 604800);
            $dur = $immunity . $lang['modtask_gl_week'] . ($immunity > 1 ? $lang['modtask_gl_weeks'] : '');
            $msg = "{$lang['modtask_gl_received']} $dur {$lang['modtask_immune_status_from']}" . $username . ($immunity_pm ? "\n\n{$lang['modtask_gl_reason']} $immunity_pm" : '');
            $modcomment = get_date($dt, 'DATE', 1) . "{$lang['modtask_immune_status_for']} $dur {$lang['modtask_gl_by']}" . $CURUSER['username'] . ".\n{$lang['modtask_gl_reason']} $immunity_pm\n" . $modcomment;
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
        $subject = $lang['modtask_gl_notification'];
        if ($leechwarn === 255) {
            $modcomment = get_date($dt, 'DATE', 1) . $lang['modtask_leechwarn_status'] . $CURUSER['username'] . ".\n{$lang['modtask_gl_reason']} $leechwarn_pm\n" . $modcomment;
            $msg = $lang['modtask_leechwarn_received'] . $username . (!empty($leechwarn_pm) ? "\n\n{$lang['modtask_gl_reason']} $leechwarn_pm" : '');
            $update['leechwarn'] = 1;
        } elseif ($leechwarn === 42) {
            $modcomment = get_date($dt, 'DATE', 1) . $lang['modtask_leechwarn_remove'] . $CURUSER['username'] . ".\n" . $modcomment;
            $msg = $lang['modtask_leechwarn_removed'] . $username;
            $update['leechwarn'] = 0;
        } else {
            $leechwarn_until = $dt + ($leechwarn * 604800);
            $dur = $leechwarn . $lang['modtask_gl_week'] . ($leechwarn > 1 ? $lang['modtask_gl_weeks'] : '');
            $msg = "{$lang['modtask_gl_received']} $dur {$lang['modtask_leechwarn_status_from']}" . $username . ($leechwarn_pm ? "\n\n{$lang['modtask_gl_reason']} $leechwarn_pm" : '');
            $modcomment = get_date($dt, 'DATE', 1) . "{$lang['modtask_leechwarn_status_for']} $dur {$lang['modtask_gl_by']}" . $CURUSER['username'] . ".\n{$lang['modtask_gl_reason']} $leechwarn_pm\n" . $modcomment;
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
        $subject = $lang['modtask_gl_notification'];
        if ($warned === 255) {
            $modcomment = get_date($dt, 'DATE', 1) . $lang['modtask_warned_status'] . $CURUSER['username'] . ".\n{$lang['modtask_gl_reason']} $warned_pm\n" . $modcomment;
            $msg = $lang['modtask_warned_received'] . $username . (!empty($warned_pm) ? "\n\n{$lang['modtask_gl_reason']} $warned_pm" : '');
            $update['warned'] = 1;
        } elseif ($warned === 42) {
            $modcomment = get_date($dt, 'DATE', 1) . $lang['modtask_warned_remove'] . $CURUSER['username'] . ".\n" . $modcomment;
            $msg = $lang['modtask_warned_removed'] . $username;
            $update['warned'] = 0;
        } else {
            $warned_until = $dt + ($warned * 604800);
            $dur = $warned . $lang['modtask_gl_week'] . ($warned > 1 ? $lang['modtask_gl_weeks'] : '');
            $msg = "{$lang['modtask_gl_received']} $dur {$lang['modtask_warned_status_from']}" . $username . ($warned_pm ? "\n\n{$lang['modtask_gl_reason']} $warned_pm" : '');
            $modcomment = get_date($dt, 'DATE', 1) . "{$lang['modtask_warned_status_for']} $dur {$lang['modtask_gl_by']}" . $CURUSER['username'] . ".\n{$lang['modtask_gl_reason']} $warned_pm\n" . $modcomment;
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
    if ($CURUSER['class'] >= UC_ADMINISTRATOR) {
        $uploadtoadd = (int) $post['amountup'];
        $downloadtoadd = (int) $post['amountdown'];
        $formatup = $post['formatup'];
        $formatdown = $post['formatdown'];
        $mpup = $post['upchange'];
        $mpdown = $post['downchange'];
        if ($uploadtoadd > 0) {
            if ($mpup === 'plus') {
                $newupload = $user['uploaded'] + ($formatup === 'mb' ? ($uploadtoadd * 1048576) : ($uploadtoadd * 1073741824));
                $modcomment = get_date($dt, 'DATE', 1) . " {$lang['modtask_add_upload']} (" . $uploadtoadd . ' ' . $formatup . ") {$lang['modtask_by']} " . $CURUSER['username'] . "\n" . $modcomment;
            } else {
                $newupload = $user['uploaded'] - ($formatup === 'mb' ? ($uploadtoadd * 1048576) : ($uploadtoadd * 1073741824));
                $newupload = $newupload < 0 ? 0 : $newupload;
                if ($newupload >= 0) {
                    $modcomment = get_date($dt, 'DATE', 1) . " {$lang['modtask_subtract_upload']} (" . $uploadtoadd . ' ' . $formatup . ") {$lang['modtask_by']} " . $CURUSER['username'] . "\n" . $modcomment;
                }
            }
            $update['uploaded'] = $newupload;
            $useredit[] = $lang['modtask_uploaded_altered'] . mksize($uploadtoadd) . $lang['modtask_to'] . mksize($newupload);
        }
        if ($downloadtoadd > 0) {
            if ($mpdown === 'plus') {
                $newdownload = $user['downloaded'] + ($formatdown === 'mb' ? ($downloadtoadd * 1048576) : ($downloadtoadd * 1073741824));
                $modcomment = get_date($dt, 'DATE', 1) . " {$lang['modtask_added_download']} (" . $downloadtoadd . ' ' . $formatdown . ") {$lang['modtask_by']} " . $CURUSER['username'] . "\n" . $modcomment;
            } else {
                $newdownload = $user['downloaded'] - ($formatdown === 'mb' ? ($downloadtoadd * 1048576) : ($downloadtoadd * 1073741824));
                $newdownload = $newdownload < 0 ? 0 : $newdownload;
                if ($newdownload >= 0) {
                    $modcomment = get_date($dt, 'DATE', 1) . " {$lang['modtask_subtract_download']} (" . $downloadtoadd . ' ' . $formatdown . ") {$lang['modtask_by']} " . $CURUSER['username'] . "\n" . $modcomment;
                }
            }
            $update['downloaded'] = $newdownload;
            $useredit[] = $lang['modtask_download_altered'] . mksize($downloadtoadd) . $lang['modtask_to'] . mksize($newdownload);
        }
    }
    if ((isset($post['title'])) && (($title = $post['title']) !== ($curtitle = $user['title']))) {
        $modcomment = get_date($dt, 'DATE', 1) . "{$lang['modtask_custom_title']}'" . $title . "'{$lang['modtask_gl_from']}'" . $curtitle . "' {$lang['modtask_by']} " . $CURUSER['username'] . ".\n" . $modcomment;
        $update['title'] = $title;
        $useredit[] = $lang['modtask_custom_title_altered'];
    }
    if (!empty($post['reset_torrent_pass'])) {
        $newtorrentpass = make_password(32);
        $modcomment = get_date($dt, 'DATE', 1) . " - {$lang['modtask_torrent_pass']} {$user['torrent_pass']} {$lang['modtask_reset']} {$newtorrentpass} {$lang['modtask_by']} " . $CURUSER['username'] . ".\n" . $modcomment;
        $update['torrent_pass'] = $newtorrentpass;
        $useredit[] = "{$lang['modtask_torrent_pass']} {$user['torrent_pass']} {$lang['modtask_reset']} $newtorrentpass}";
    }
    if (!empty($post['reset_auth'])) {
        $newauthkey = make_password(32);
        $modcomment = get_date($dt, 'DATE', 1) . " - {$lang['modtask_authkey']} {$user['auth']} {$lang['modtask_reset']} {$newauthkey} {$lang['modtask_by']} " . $CURUSER['username'] . ".\n" . $modcomment;
        $update['auth'] = $newauthkey;
        $useredit[] = "{$lang['modtask_authkey']} {$user['auth']} {$lang['modtask_reset']} $newauthkey";
    }
    if (!empty($post['reset_apikey'])) {
        $newapikey = make_password(32);
        $modcomment = get_date($dt, 'DATE', 1) . " - {$lang['modtask_apikey']} {$user['apikey']} {$lang['modtask_reset']} {$newapikey} {$lang['modtask_by']} " . $CURUSER['username'] . ".\n" . $modcomment;
        $update['apikey'] = $newapikey;
        $useredit[] = "{$lang['modtask_apikey']} {$user['apikey']} {$lang['modtask_reset']} $newapikey";
    }
    if ((isset($post['seedbonus'])) && (($seedbonus = (int) $post['seedbonus']) !== $user['seedbonus'])) {
        $modcomment = get_date($dt, 'DATE', 1) . $lang['modtask_seedbonus'] . $seedbonus . $lang['modtask_gl_from'] . $user['seedbonus'] . $lang['modtask_gl_by'] . $CURUSER['username'] . ".\n" . $modcomment;
        $update['seedbonus'] = $seedbonus;
        $useredit[] = $lang['modtask_seedbonus_total'];
    }
    if ((isset($post['reputation'])) && (($reputation = (int) $post['reputation']) !== $user['reputation'])) {
        $modcomment = get_date($dt, 'DATE', 1) . $lang['modtask_reputation'] . $reputation . $lang['modtask_gl_from'] . $user['reputation'] . $lang['modtask_gl_by'] . $CURUSER['username'] . ".\n" . $modcomment;
        $update['reputation'] = $reputation;
        $useredit[] = $lang['modtask_reputation_total'];
    }
    if ((isset($_POST['addcomment'])) && ($addcomment = trim($_POST['addcomment']))) {
        $modcomment = get_date($dt, 'DATE', 1) . ' - ' . $addcomment . ' - ' . $CURUSER['username'] . ".\n" . $modcomment;
    }
    if ((isset($post['avatar'])) && (($avatar = $post['avatar']) !== $user['avatar'])) {
        $avatar = validate_url($avatar);
        if (!empty($avatar)) {
            $img_size = getimagesize($avatar);
            if ($img_size == false || !in_array($img_size['mime'], $site_config['images']['extensions'])) {
                stderr("{$lang['modtask_user_error']}", "{$lang['modtask_not_image']}");
            }
            if ($img_size[0] < 100 || $img_size[1] < 100) {
                stderr("{$lang['modtask_user_error']}", "{$lang['modtask_image_small']}");
            }
        }
        $modcomment = get_date($dt, 'DATE', 1) . "{$lang['modtask_avatar_change']}" . htmlsafechars((string) $user['avatar']) . "{$lang['modtask_to']}" . htmlsafechars((string) $avatar) . " {$lang['modtask_by']} " . $CURUSER['username'] . ".\n" . $modcomment;
        $update['avatar'] = !empty($avatar) ? $avatar : '';
        $useredit[] = $lang['modtask_avatar_changed'];
    }
    if ((isset($post['signature'])) && (($signature = $post['signature']) !== $user['signature'])) {
        $signature = validate_url($signature);
        if (!empty($signature)) {
            $img_size = getimagesize($signature);
            if ($img_size == false || !in_array($img_size['mime'], $site_config['images']['extensions'])) {
                stderr("{$lang['modtask_user_error']}", "{$lang['modtask_not_image']}");
            }
            if ($img_size[0] < 100 || $img_size[1] < 15) {
                stderr("{$lang['modtask_user_error']}", "{$lang['modtask_image_small']}");
            }
        }
        $modcomment = get_date($dt, 'DATE', 1) . "{$lang['modtask_signature_change']}" . htmlsafechars((string) $user['signature']) . "{$lang['modtask_to']}" . htmlsafechars((string) $signature) . " {$lang['modtask_by']} " . $CURUSER['username'] . ".\n" . $modcomment;
        $update['signature'] = !empty($signature) ? $signature : '';
        $useredit[] = $lang['modtask_signature_changed'];
    }
    if ((isset($post['invite_on'])) && (($invite_on = $post['invite_on']) != $user['invite_on'])) {
        $modcomment = get_date($dt, 'DATE', 1) . $lang['modtask_invites_allowed'] . htmlsafechars((string) $user['invite_on']) . " {$lang['modtask_to']} $invite_on{$lang['modtask_gl_by']}" . $CURUSER['username'] . ".\n" . $modcomment;
        $update['invite_on'] = $invite_on;
        $useredit[] = $lang['modtask_invites_enabled'] . $invite_on;
    }
    if ((isset($post['invites'])) && (($invites = (int) $post['invites']) !== $user['invites'])) {
        $modcomment = get_date($dt, 'DATE', 1) . $lang['modtask_invites_amount'] . $invites . $lang['modtask_gl_from'] . $user['invites'] . $lang['modtask_gl_by'] . $CURUSER['username'] . ".\n" . $modcomment;
        $update['invites'] = $invites;
        $useredit[] = $lang['modtask_invites_total'];
    }
    if ((isset($post['support'])) && (($support = $post['support']) !== $user['support'])) {
        if ($support === 'yes') {
            $modcomment = get_date($dt, 'DATE', 1) . $lang['modtask_fls_promoted'] . $CURUSER['username'] . ".\n" . $modcomment;
        } elseif ($support === 'no') {
            $modcomment = get_date($dt, 'DATE', 1) . $lang['modtask_fls_demoted'] . $CURUSER['username'] . ".\n" . $modcomment;
        } else {
            stderr("{$lang['modtask_user_error']}", "{$lang['modtask_try_again']}");
        }
        $supportfor = $post['supportfor'];
        $update['support'] = $support;
        $update['supportfor'] = $supportfor;
        $useredit[] = $lang['modtask_fls_support'] . $support;
        $useredit[] = $lang['modtask_fls_support'] . $supportfor;
    }
    if ((isset($post['freeslots'])) && (($freeslots = (int) $post['freeslots']) !== $user['freeslots'])) {
        $modcomment = get_date($dt, 'DATE', 1) . $lang['modtask_freeslots_amount'] . $freeslots . $lang['modtask_gl_from'] . $user['freeslots'] . $lang['modtask_gl_by'] . $CURUSER['username'] . ".\n" . $modcomment;
        $update['freeslots'] = $freeslots;
        $useredit[] = $lang['modtask_freeslots_total'];
    }
    if (isset($post['free_switch']) && ($free_switch = (int) $post['free_switch'])) {
        $free_pm = '';
        if (isset($post['free_pm'])) {
            $free_pm = $post['free_pm'];
        }
        $subject = $lang['modtask_gl_notification'];
        if ($free_switch === 255) {
            $modcomment = get_date($dt, 'DATE', 1) . $lang['modtask_freeleech_status'] . $CURUSER['username'] . ".\n{$lang['modtask_gl_reason']} $free_pm\n" . $modcomment;
            $msg = $lang['modtask_freeleech_received'] . $username . (!empty($free_pm) ? "\n\n{$lang['modtask_gl_reason']} $free_pm" : '');
            $update['free_switch'] = 1;
            $useredit[] = $lang['modtask_freeleech_yes'];
        } elseif ($free_switch === 42) {
            $modcomment = get_date($dt, 'DATE', 1) . $lang['modtask_freeleech_remove'] . $CURUSER['username'] . ".\n" . $modcomment;
            $msg = $lang['modtask_freeleech_removed'] . $username;
            $update['free_switch'] = 0;
            $useredit[] = $lang['modtask_freeleech_no'];
        } else {
            $free_until = $dt + ($free_switch * 604800);
            $dur = $free_switch . $lang['modtask_gl_week'] . ($free_switch > 1 ? $lang['modtask_gl_weeks'] : '');
            $msg = "{$lang['modtask_gl_received']} $dur {$lang['modtask_freeleech_from']}Freeleech Status from " . $username . ($free_pm ? "\n\n{$lang['modtask_gl_reason']} $free_pm" : '');
            $modcomment = get_date($dt, 'DATE', 1) . "{$lang['modtask_freeleech_for']} $dur {$lang['modtask_gl_by']}" . $CURUSER['username'] . ".\n{$lang['modtask_gl_reason']} $free_pm\n" . $modcomment;
            $update['free_switch'] = $free_until;
            $useredit[] = $lang['modtask_freeleech_enabled'] . get_date((int) $free_until, 'DATE', 0, 1);
        }
        $msgs[] = [
            'poster' => $CURUSER['id'],
            'receiver' => $userid,
            'added' => $dt,
            'msg' => $msg,
            'subject' => $lang['modtask_cls_change'],
        ];
    }
    if (isset($post['game_access']) && ($game_access = (int) $post['game_access'])) {
        $disable_pm = '';
        if (isset($post['game_disable_pm'])) {
            $disable_pm = $post['game_disable_pm'];
        }
        $subject = $lang['modtask_gl_notification'];
        if ($game_access === 255) {
            $modcomment = get_date($dt, 'DATE', 1) . $lang['modtask_games_dis_by'] . $CURUSER['username'] . ".\n{$lang['modtask_gl_reason']}\n" . $modcomment;
            $msg = $lang['modtask_games_dis_right'] . $username . "\n\n{$lang['modtask_gl_reason']}";
            $update['game_access'] = 0;
            $useredit[] = $lang['modtask_games_poss_no'];
        } elseif ($game_access === 42) {
            $modcomment = get_date($dt, 'DATE', 1) . $lang['modtask_games_dis_status'] . $CURUSER['username'] . ".\n" . $modcomment;
            $msg = $lang['modtask_games_res_by'] . $username;
            $update['game_access'] = 1;
            $useredit[] = $lang['modtask_games_poss_yes'];
        } else {
            $game_access_until = $dt + ($game_access * 604800);
            $dur = $game_access . $lang['modtask_gl_week'] . ($game_access > 1 ? $lang['modtask_gl_weeks'] : '');
            $msg = "{$lang['modtask_gl_received']} $dur {$lang['modtask_games_dis_from']}" . $username . "\n\n{$lang['modtask_gl_reason']}";
            $modcomment = get_date($dt, 'DATE', 1) . "{$lang['modtask_games_dis_for']} $dur {$lang['modtask_gl_by']}" . $CURUSER['username'] . ".\n{$lang['modtask_gl_reason']}\n" . $modcomment;
            $update['game_access'] = $game_access_until;
            $useredit[] = $lang['modtask_games_disabled'] . get_date((int) $game_access_until, 'DATE', 0, 1);
        }
        $msgs[] = [
            'poster' => $CURUSER['id'],
            'receiver' => $userid,
            'added' => $dt,
            'msg' => $msg,
            'subject' => $lang['modtask_cls_change'],
        ];
    }
    if (isset($post['avatarpos']) && ($avatarpos = (int) $post['avatarpos'])) {
        $avatardisable_pm = '';
        if (isset($post['avatardisable_pm'])) {
            $avatardisable_pm = $post['avatardisable_pm'];
        }
        $subject = $lang['modtask_gl_notification'];
        if ($avatarpos === 255) {
            $modcomment = get_date($dt, 'DATE', 1) . $lang['modtask_avatar_dis_by'] . $CURUSER['username'] . ".\n{$lang['modtask_gl_reason']} $avatardisable_pm\n" . $modcomment;
            $msg = $lang['modtask_avatar_dis_right'] . $username . (!empty($avatardisable_pm) ? "\n\n{$lang['modtask_gl_reason']} $avatardisable_pm" : '');
            $update['avatarpos'] = 0;
            $useredit[] = $lang['modtask_avatar_poss_no'];
        } elseif ($avatarpos === 42) {
            $modcomment = get_date($dt, 'DATE', 1) . $lang['modtask_avatar_dis_status'] . $CURUSER['username'] . ".\n" . $modcomment;
            $msg = $lang['modtask_avatar_res_by'] . $username;
            $update['avatarpos'] = 1;
            $useredit[] = $lang['modtask_avatar_poss_yes'];
        } else {
            $avatarpos_until = $dt + ($avatarpos * 604800);
            $dur = $avatarpos . $lang['modtask_gl_week'] . ($avatarpos > 1 ? $lang['modtask_gl_weeks'] : '');
            $msg = "{$lang['modtask_gl_received']} $dur {$lang['modtask_avatar_dis_from']}" . $username . ($avatardisable_pm ? "\n\n{$lang['modtask_gl_reason']} $avatardisable_pm" : '');
            $modcomment = get_date($dt, 'DATE', 1) . "{$lang['modtask_avatar_dis_for']} $dur {$lang['modtask_gl_by']}" . $CURUSER['username'] . ".\n{$lang['modtask_gl_reason']} $avatardisable_pm\n" . $modcomment;
            $update['avatarpos'] = $avatarpos_until;
            $useredit[] = $lang['modtask_avatar_sel_dis'] . get_date((int) $avatarpos_until, 'DATE', 0, 1);
        }
        $msgs[] = [
            'poster' => $CURUSER['id'],
            'receiver' => $userid,
            'added' => $dt,
            'msg' => $msg,
            'subject' => $lang['modtask_cls_change'],
        ];
    }
    if ((isset($post['highspeed'])) && (($highspeed = $post['highspeed']) !== $user['highspeed'])) {
        if ($highspeed === 'yes') {
            $modcomment = get_date($dt, 'DATE', 1) . $lang['modtask_highs_enable_by'] . $CURUSER['username'] . ".\n" . $modcomment;
            $subject = $lang['modtask_highs_status'];
            $msg = $lang['modtask_highs_set'] . $username . $lang['modtask_highs_msg'];
        } elseif ($highspeed === 'no') {
            $modcomment = get_date($dt, 'DATE', 1) . $lang['modtask_highs_disable_by'] . $CURUSER['username'] . ".\n" . $modcomment;
            $subject = $lang['modtask_highs_status'];
            $msg = $lang['modtask_highs_disabled'] . $username . $lang['modtask_highs_pm'] . $username . $lang['modtask_highs_reason'];
        } else {
            stderr("{$lang['modtask_user_error']}", "{$lang['modtask_try_again']}");
        }
        $update['highspeed'] = $highspeed;
        $useredit[] = $lang['modtask_highs_enabled'] . $highspeed;
        $msgs[] = [
            'poster' => $CURUSER['id'],
            'receiver' => $userid,
            'added' => $dt,
            'msg' => $msg,
            'subject' => $lang['modtask_cls_change'],
        ];
    }
    if ((isset($post['can_leech'])) && (($can_leech = (int) $post['can_leech']) !== $user['can_leech'])) {
        if ($can_leech === 1) {
            $modcomment = get_date($dt, 'DATE', 1) . $lang['modtask_canleech_on_by'] . $CURUSER['username'] . ".\n" . $modcomment;
            $subject = $lang['modtask_canleech_status'];
            $msg = $lang['modtask_canleech_rights_on'] . $username . ' . ';
        } elseif ($can_leech === 0) {
            $modcomment = get_date($dt, 'DATE', 1) . $lang['modtask_canleech_off_by'] . $CURUSER['username'] . ".\n" . $modcomment;
            $subject = $lang['modtask_canleech_status'];
            $msg = $lang['modtask_canleech_ability'] . $username . $lang['modtask_canleech_pm'] . $username . $lang['modtask_canleech_reason'];
        } else {
            stderr("{$lang['modtask_user_error']}", "{$lang['modtask_try_again']}");
        }
        $update['can_leech'] = $can_leech;
        $useredit[] = $lang['modtask_canleech_edited'] . $can_leech;
        $msgs[] = [
            'poster' => $CURUSER['id'],
            'receiver' => $userid,
            'added' => $dt,
            'msg' => $msg,
            'subject' => $lang['modtask_cls_change'],
        ];
    }
    if ((isset($post['wait_time'])) && (($wait_time = $post['wait_time']) !== $user['wait_time'])) {
        $modcomment = get_date($dt, 'DATE', 1) . "{$lang['modtask_wait_set']} $wait_time{$lang['modtask_gl_was']}" . (int) $user['wait_time'] . $lang['modtask_gl_by'] . $CURUSER['username'] . ".\n" . $modcomment;
        $update['wait_time'] = $wait_time;
        $useredit[] = $lang['modtask_wait_yes'];
    }
    if ((isset($post['peers_limit'])) && (($peers_limit = $post['peers_limit']) !== $user['peers_limit'])) {
        $modcomment = get_date($dt, 'DATE', 1) . "{$lang['modtask_peer_limit']} $peers_limit{$lang['modtask_gl_was']}" . (int) $user['peers_limit'] . $lang['modtask_gl_by'] . $CURUSER['username'] . ".\n" . $modcomment;
        $update['peers_limit'] = $peers_limit;
        $useredit[] = $lang['modtask_peer_adjusted'];
    }
    if ((isset($post['torrents_limit'])) && (($torrents_limit = $post['torrents_limit']) !== $user['torrents_limit'])) {
        $modcomment = get_date($dt, 'DATE', 1) . "{$lang['modtask_torrent_limit']} $torrents_limit{$lang['modtask_gl_was']}" . (int) $user['torrents_limit'] . $lang['modtask_gl_by'] . $CURUSER['username'] . ".\n" . $modcomment;
        $update['torrents_limit'] = $torrents_limit;
        $useredit[] = $lang['modtask_torrent_adjusted'];
    }
    if ((isset($post['parked'])) && (($parked = $post['parked']) !== $user['parked'])) {
        if ($parked === 'yes') {
            $modcomment = get_date($dt, 'DATE', 1) . $lang['modtask_parked_by'] . $CURUSER['username'] . ".\n" . $modcomment;
        } elseif ($parked === 'no') {
            $modcomment = get_date($dt, 'DATE', 1) . $lang['modtask_unparked_by'] . $CURUSER['username'] . ".\n" . $modcomment;
        } else {
            stderr("{$lang['modtask_user_error']}", "{$lang['modtask_try_again']}");
        }
        $update['parked'] = $parked;
        $useredit[] = $lang['modtask_parked_acc'] . $parked;
    }
    if ((isset($post['suspended'])) && (($suspended = $post['suspended']) !== $user['suspended'])) {
        $suspended_reason = $post['suspended_reason'];
        if (!$suspended_reason) {
            stderr($lang['modtask_error'], $lang['modtask_suspend_err']);
        }
        if ($post['suspended'] === 'yes') {
            $modcomment = get_date($dt, 'DATE', 1) . $lang['modtask_suspend_by'] . $CURUSER['username'] . $lang['modtask_suspend_reason'] . $suspended_reason . ".\n" . $modcomment;
            $update['downloadpos'] = 0;
            $update['uploadpos'] = 0;
            $update['forum_post'] = 'no';
            $update['invite_on'] = 'no';
            $update['chatpost'] = 'no';
            $useredit[] = $lang['modtask_suspended_yes'];
            $subject = $lang['modtask_suspend_title'];
            $msg = $lang['modtask_suspend_msg'] . $username . ".\n[b]{$lang['modtask_suspend_msg1']}[/b]\n{$suspended_reason}.\n\n{$lang['modtask_suspend_msg2']}\n\n{$lang['modtask_suspend_msg3']}\n\n{$lang['modtask_suspend_msg4']}\n" . $site_config['site']['name'] . $lang['modtask_suspend_msg5'];
            $body = "{$lang['modtask_suspend_acc_for']}[b][url=" . $site_config['paths']['baseurl'] . ' / userdetails . php ? id = ' . (int) $user['id'] . ']' . htmlsafechars($user['username']) . "[/url][/b]{$lang['modtask_suspend_has_by']}" . $CURUSER['username'] . "\n\n [b]{$lang['modtask_suspend_reason']}[/b]\n " . $suspended_reason;
            auto_post($lang['modtask_suspend_title'], $body);
        }
        if ($post['suspended'] === 'no') {
            $modcomment = get_date($dt, 'DATE', 1) . $lang['modtask_unsuspend_by'] . $CURUSER['username'] . $lang['modtask_suspend_reason'] . $suspended_reason . ".\n" . $modcomment;
            $update['downloadpos'] = 1;
            $update['uploadpos'] = 1;
            $update['forum_post'] = 'yes';
            $update['invite_on'] = 'yes';
            $update['chatpost'] = 'yes';
            $useredit[] = $lang['modtask_suspended_no'];
            $subject = $lang['modtask_unsuspend_title'];
            $msg = $lang['modtask_unsuspend_msg'] . $username . ".\n[b]{$lang['modtask_suspend_msg1']}[/b]\n{$suspended_reason}. \n\n{$lang['modtask_suspend_msg4']}\n" . $site_config['site']['name'] . $lang['modtask_suspend_msg5'];
        }
        $update['suspended'] = $post['suspended'];
        $msgs[] = [
            'poster' => $CURUSER['id'],
            'receiver' => $userid,
            'added' => $dt,
            'msg' => $msg,
            'subject' => $lang['modtask_cls_change'],
        ];
    }
    if ((isset($post['hit_and_run_total'])) && (($hit_and_run_total = (int) $post['hit_and_run_total']) !== $user['hit_and_run_total'])) {
        $modcomment = get_date($dt, 'DATE', 1) . "{$lang['modtask_hit_run_set']} $hit_and_run_total{$lang['modtask_gl_was']}" . (int) $user['hit_and_run_total'] . $lang['modtask_gl_by'] . $CURUSER['username'] . ".\n" . $modcomment;
        $update['hit_and_run_total'] = $hit_and_run_total;
        $useredit[] = $lang['modtask_hit_run_adjusted'];
    }
    if ((isset($post['forum_post'])) && (($forum_post = $post['forum_post']) !== $user['forum_post'])) {
        if ($forum_post === 'yes') {
            $modcomment = get_date($dt, 'DATE', 1) . $lang['modtask_post_en_by'] . $CURUSER['username'] . ".\n" . $modcomment;
            $msg = $lang['modtask_post_give_back'] . $username . $lang['modtask_post_forum_again'];
        } else {
            $modcomment = get_date($dt, 'DATE', 1) . $lang['modtask_post_dis_by'] . $CURUSER['username'] . ".\n" . $modcomment;
            $msg = $lang['modtask_post_rem_by'] . $username . $lang['modtask_post_pm'] . $username . $lang['modtask_post_reason'];
        }
        $msgs[] = [
            'poster' => $CURUSER['id'],
            'receiver' => $userid,
            'added' => $dt,
            'msg' => $msg,
            'subject' => $lang['modtask_cls_change'],
        ];
        $update['forum_post'] = $forum_post;
        $useredit[] = $lang['modtask_post_enabled'] . $forum_post;
    }
    if ((isset($post['signature_post'])) && (($signature_post = $post['signature_post']) !== $user['signature_post'])) {
        if ($signature_post === 'no') {
            $modcomment = get_date($dt, 'DATE', 1) . $lang['modtask_signature_rights_off'] . $CURUSER['username'] . ".\n" . $modcomment;
            $msg = $lang['modtask_signature_rights_off_by'] . $username . $lang['modtask_signature_rights_pm'];
        } else {
            $modcomment = get_date($dt, 'DATE', 1) . $lang['modtask_signature_rights_on'] . $CURUSER['username'] . ".\n" . $modcomment;
            $msg = $lang['modtask_signature_rights_on_by'] . $username . '.';
        }
        $msgs[] = [
            'poster' => $CURUSER['id'],
            'receiver' => $userid,
            'added' => $dt,
            'msg' => $msg,
            'subject' => $lang['modtask_cls_change'],
        ];
        $update['signature_post'] = $signature_post;
        $useredit[] = $lang['modtask_signature_rights_enabled'] . $signature_post;
    }
    if ((isset($post['avatar_rights'])) && (($avatar_rights = $post['avatar_rights']) !== $user['avatar_rights'])) {
        if ($avatar_rights === 'no') {
            $modcomment = get_date($dt, 'DATE', 1) . $lang['modtask_avatar_rights_off'] . $CURUSER['username'] . ".\n" . $modcomment;
        } else {
            $modcomment = get_date($dt, 'DATE', 1) . $lang['modtask_avatar_rights_on'] . $CURUSER['username'] . ".\n" . $modcomment;
        }
        $update['avatar_rights'] = $avatar_rights;
        $useredit[] = $lang['modtask_avatar_rights_enabled'] . $avatar_rights;
    }
    if ((isset($post['offensive_avatar'])) && (($offensive_avatar = $post['offensive_avatar']) !== $user['offensive_avatar'])) {
        if ($offensive_avatar === 'no') {
            $modcomment = get_date($dt, 'DATE', 1) . $lang['modtask_offensive_no'] . $CURUSER['username'] . ".\n" . $modcomment;
            $msg = $lang['modtask_offensive_no_by'] . $username;
        } else {
            $modcomment = get_date($dt, 'DATE', 1) . $lang['modtask_offensive_yes'] . $CURUSER['username'] . ".\n" . $modcomment;
            $msg = $lang['modtask_offensive_yes_by'] . $username . $lang['modtask_offensive_pm'];
        }
        $msgs[] = [
            'poster' => $CURUSER['id'],
            'receiver' => $userid,
            'added' => $dt,
            'msg' => $msg,
            'subject' => $lang['modtask_cls_change'],
        ];
        $update['offensive_avatar'] = $offensive_avatar;
        $useredit[] = $lang['modtask_offensive_enabled'] . $offensive_avatar;
    }
    if ((isset($post['view_offensive_avatar'])) && (($view_offensive_avatar = $post['view_offensive_avatar']) !== $user['view_offensive_avatar'])) {
        if ($view_offensive_avatar === 'no') {
            $modcomment = get_date($dt, 'DATE', 1) . $lang['modtask_viewoffensive_no'] . $CURUSER['username'] . ".\n" . $modcomment;
        } else {
            $modcomment = get_date($dt, 'DATE', 1) . $lang['modtask_viewoffensive_yes'] . $CURUSER['username'] . ".\n" . $modcomment;
        }
        $update['view_offensive_avatar'] = $view_offensive_avatar;
        $useredit[] = $lang['modtask_viewoffensive_enabled'] . $view_offensive_avatar;
    }
    if ((isset($post['paranoia'])) && (($paranoia = (int) $post['paranoia']) !== $user['paranoia'])) {
        $modcomment = get_date($dt, 'DATE', 1) . $lang['modtask_paranoia_changed_to'] . (int) $post['paranoia'] . $lang['modtask_gl_from'] . (int) $user['paranoia'] . $lang['modtask_gl_by'] . $CURUSER['username'] . ".\n" . $modcomment;
        $update['paranoia'] = $paranoia;
        $useredit[] = $lang['modtask_paranoia_changed'];
    }
    if ((isset($post['website'])) && (($website = $post['website']) !== $user['website'])) {
        $modcomment = get_date($dt, 'DATE', 1) . $lang['modtask_website_changed_to'] . strip_tags($post['website']) . $lang['modtask_gl_from'] . htmlsafechars((string) $user['website']) . $lang['modtask_gl_by'] . $CURUSER['username'] . ".\n" . $modcomment;
        $update['website'] = $website;
        $useredit[] = $lang['modtask_website_changed'];
    }
    if ((isset($post['skype'])) && (($skype = $post['skype']) !== $user['skype'])) {
        $modcomment = get_date($dt, 'DATE', 1) . $lang['modtask_skype_changed_to'] . strip_tags($post['skype']) . $lang['modtask_gl_from'] . htmlsafechars((string) $user['skype']) . $lang['modtask_gl_by'] . $CURUSER['username'] . ".\n" . $modcomment;
        $update['skype'] = $skype;
        $useredit[] = $lang['modtask_skype_changed'];
    }
    if (!empty($update)) {
        $update['modcomment'] = $modcomment;
        $users_class->update($update, $userid);
        if ($post['enabled'] !== 'yes') {
            $cache->delete('user_' . $userid);
        }
        if ((isset($post['class'])) && (($class = $post['class']) !== $user['class'])) {
            $cache->delete('is_staff_');
        }
        $cache->deleteMulti([
            'last24_users_',
            'birthdayusers_',
            'ircusers_',
            'activeusers_',
        ]);
    }
    if (!empty($msgs)) {
        $messages = $container->get(Message::class);
        $messages->insert($msgs);
    }
    if (!empty($useredit)) {
        write_info("{$lang['modtask_sysop_user_acc']} $userid (" . format_username((int) $userid) . ")\n{$lang['modtask_sysop_thing']}" . implode(', ', $useredit) . "{$lang['modtask_gl_by']}" . format_username((int) $CURUSER['id']));
    }
    $returnto = htmlsafechars($post['returnto']) . '#edit';
    header("Location: {$site_config['paths']['baseurl']}/$returnto");
}

stderr("{$lang['modtask_user_error']}", "{$lang['modtask_no_idea']}");
