<?php

require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_html.php';
require_once INCL_DIR . 'function_bbcode.php';
require_once CLASS_DIR . 'class_check.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
global $CURUSER, $lang, $site_config, $cache, $message_stuffs;

$lang = array_merge($lang, load_language('ad_grouppm'));

$stdhead = [
    'css' => [
        get_file_name('sceditor_css'),
    ],
];
$stdfoot = [
    'js' => [
        get_file_name('sceditor_js'),
    ],
];
$HTMLOUT = '';
$err = [];
$last_user_class = UC_STAFF - 1; //== Last users class;
$sent2classes = [];
$dt = TIME_NOW;
/**
 * @param $min
 * @param $max
 */
function classes2name($min, $max)
{
    global $sent2classes;

    for ($i = $min; $i < $max + 1; ++$i) {
        $sent2classes[] = get_user_class_name($i);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $groups = isset($_POST['groups']) ? $_POST['groups'] : '';
    $subject = isset($_POST['subject']) ? htmlsafechars($_POST['subject']) : '';
    $msg = isset($_POST['body']) ? htmlsafechars($_POST['body']) : '';
    $msg = str_replace('&amp', '&', $_POST['body']);
    $sender = isset($_POST['system']) && $_POST['system'] === 'yes' ? 0 : $CURUSER['id'];
    if (empty($subject)) {
        $err[] = $lang['grouppm_nosub'];
    }
    if (empty($msg)) {
        $err[] = $lang['grouppm_nomsg'];
    }
    if (empty($groups)) {
        $err[] = $lang['grouppm_nogrp'];
    }
    if (count($err) == 0) {
        $where = $classes = $ids = [];
        foreach ($groups as $group) {
            if (is_string($group)) {
                switch ($group) {
                    case 'all_staff':
                        $where[] = 'u.class BETWEEN ' . UC_STAFF . ' AND ' . UC_MAX;
                        classes2name(UC_STAFF, UC_MAX);
                        break;

                    case 'all_users':
                        $where[] = 'u.class BETWEEN ' . UC_MIN . ' AND ' . UC_MAX;
                        classes2name(UC_MIN, UC_MAX);
                        break;

                    case 'fls':
                        $where[] = "u.support='yes'";
                        $sent2classes[] = '' . $lang['grouppm_fls'] . '';
                        break;

                    case 'donor':
                        $where[] = "u.donor = 'yes'";
                        $sent2classes[] = '' . $lang['grouppm_donor'] . '';
                        break;

                    case 'all_friends':
                        $fq = sql_query('SELECT friendid FROM friends WHERE userid = ' . sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);
                        if (mysqli_num_rows($fq)) {
                            while ($fa = mysqli_fetch_row($fq)) {
                                $ids[] = $fa[0];
                            }
                        }
                        break;
                }
            }
            if (ctype_digit($group) && (int) $group >= 0) {
                $classes[] = $group;
                $sent2classes[] = get_user_class_name($group);
            }
        }
        if (count($classes) > 0) {
            $where[] = 'u.class IN (' . implode(', ', $classes) . ')';
        }
        if (count($where) > 0) {
            $q1 = sql_query('SELECT u.id FROM users AS u WHERE ' . implode(' OR ', $where)) or sqlerr(__FILE__, __LINE__);
            if (mysqli_num_rows($q1) > 0) {
                while ($a = mysqli_fetch_row($q1)) {
                    $ids[] = $a[0];
                }
            }
        }
        $ids = array_unique($ids);
        if (count($ids) > 0) {
            $msg .= '[class=top20][p]' . $lang['grouppm_this'] . implode(', ', $sent2classes) . '[/p][/class]';
            foreach ($ids as $rid) {
                $msgs_buffer[] = [
                    'sender' => $sender,
                    'poster' => $CURUSER['id'],
                    'receiver' => $rid,
                    'added' => $dt,
                    'msg' => $msg,
                    'subject' => $subject,
                ];
            }
            $r = $message_stuffs->insert($msgs_buffer);
            $err[] = $r ? $lang['grouppm_sent'] . ' to ' . count($msgs_buffer) . ' users' : $lang['grouppm_again'];
        } else {
            $err[] = $lang['grouppm_nousers'];
        }
    }
}

$groups = [];
$groups['staff'] = [
    'opname' => $lang['grouppm_staff'],
    'minclass' => UC_MIN,
];
for ($i = UC_STAFF; $i <= UC_MAX; ++$i) {
    $groups['staff']['ops'][$i] = get_user_class_name($i);
}
$groups['staff']['ops']['fls'] = $lang['grouppm_fls'];
$groups['staff']['ops']['all_staff'] = $lang['grouppm_allstaff'];
$groups['members'] = [];
$groups['members'] = [
    'opname' => $lang['grouppm_mem'],
    'minclass' => UC_STAFF,
];
for ($i = UC_MIN; $i <= $last_user_class; ++$i) {
    $groups['members']['ops'][$i] = get_user_class_name($i);
}
$groups['members']['ops']['donor'] = $lang['grouppm_donor'];
$groups['members']['ops']['all_users'] = $lang['grouppm_allusers'];
$groups['friends'] = [
    'opname' => $lang['grouppm_related'],
    'minclass' => UC_MIN,
    'ops' => ['all_friends' => $lang['grouppm_friends']],
];

/**
 * @return string
 */
function dropdown()
{
    global $CURUSER, $groups;

    $r = '<select multiple="multiple" name="groups[]"  size="16">';
    foreach ($groups as $group) {
        if ($group['minclass'] >= $CURUSER['class']) {
            continue;
        }
        $r .= '<optgroup label="' . $group['opname'] . '">';
        $ops = $group['ops'];
        foreach ($ops as $k => $v) {
            $r .= '<option value="' . $k . '">' . $v . '</option>';
        }
        $r .= '</optgroup>';
    }
    $r .= '</select>';

    return $r;
}

if (count($err) > 0) {
    $status = stristr($err[0], 'sent!') == true ? 'is-success' : 'is-warning';
    foreach ($err as $error) {
        $session->set($status, $error);
    }
}
$HTMLOUT .= "
    <h1 class='has-text-centered'>{$lang['grouppm_head']}</h1>
    <form action='staffpanel.php?tool=grouppm&amp;action=grouppm' method='post'>
      <table class='table table-bordered table-striped'>
        <tr>
          <td colspan='2'>{$lang['grouppm_sub']}
            <input type='text' name='subject' class='w-100'></td>
        </tr>
        <tr>
          <td>{$lang['grouppm_body']}</td>
          <td>{$lang['grouppm_groups']}</td>
          </tr>
        <tr>
          <td class='is-paddingless'>" . BBcode() . '</td>
          <td>' . dropdown() . "</td>

        </tr>
      </table>
        <div class='has-text-centered margin20'>
            <label for='sys'>{$lang['grouppm_sendas']}</label>
            <input id='sys' type='checkbox' name='system' value='yes' class=''>
            <input type='submit' value='{$lang['grouppm_send']}' class='button is-small left20'>
        </div>
    </form>";
echo stdhead($lang['grouppm_stdhead'], $stdhead) . wrapper($HTMLOUT) . stdfoot($stdfoot);
