<?php

declare(strict_types = 1);

use Pu239\Message;
use Pu239\Session;

require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_html.php';
require_once INCL_DIR . 'function_bbcode.php';
require_once CLASS_DIR . 'class_check.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
global $container, $site_config, $CURUSER;

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
    for ($i = $min; $i < $max + 1; ++$i) {
        $sent2classes[] = get_user_class_name((int) $i);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $groups = isset($_POST['groups']) ? $_POST['groups'] : '';
    $subject = isset($_POST['subject']) ? htmlsafechars($_POST['subject']) : '';
    $msg = isset($_POST['body']) ? htmlsafechars($_POST['body']) : '';
    $msg = str_replace('&amp', '&', $_POST['body']);
    $sender = isset($_POST['system']) && $_POST['system'] === 'yes' ? 2 : $CURUSER['id'];
    if (empty($subject)) {
        $err[] = _("Your message doesn't have a subject");
    }
    if (empty($msg)) {
        $err[] = _('There is not any text in your message!');
    }
    if (empty($groups)) {
        $err[] = _('You have to select a group to send your message');
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
                        $sent2classes[] = _('First line support');
                        break;

                    case 'donor':
                        $where[] = "u.donor = 'yes'";
                        $sent2classes[] = _('Donors');
                        break;

                    case 'all_friends':
                        $fq = sql_query('SELECT friendid FROM friends WHERE userid=' . sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);
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
                $sent2classes[] = get_user_class_name((int) $group);
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
            $msg .= '[class=top20][p]' . _pfe('This message was set to the following class: {1}', 'This message was set to the following classes: {1}', count($sent2classes), implode(', ', $sent2classes)) . '[/p][/class]';
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
            $messages_class = $container->get(Message::class);
            $r = $messages_class->insert($msgs_buffer);
            $err[] = $r ? _pfe('Message sent to {0} user', 'Message sent to {0} users', count($msgs_buffer)) : _('Unable to send the message try again!');
        } else {
            $err[] = _('There are not any users in the groups you selected!');
        }
    }
}

$groups = [];
$groups['staff'] = [
    'opname' => _('Site Staff'),
    'minclass' => UC_MIN,
];
for ($i = UC_STAFF; $i <= UC_MAX; ++$i) {
    $groups['staff']['ops'][$i] = get_user_class_name((int) $i);
}
$groups['staff']['ops']['fls'] = _('First line support');
$groups['staff']['ops']['all_staff'] = _('All staff');
$groups['members'] = [];
$groups['members'] = [
    'opname' => _('Members Groups'),
    'minclass' => UC_STAFF,
];
for ($i = UC_MIN; $i <= $last_user_class; ++$i) {
    $groups['members']['ops'][$i] = get_user_class_name((int) $i);
}
$groups['members']['ops']['donor'] = _('Donors');
$groups['members']['ops']['all_users'] = _('All users');
$groups['friends'] = [
    'opname' => _('Related to you'),
    'minclass' => UC_MIN,
    'ops' => ['all_friends' => _('Your friends')],
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
    $session = $container->get(Session::class);
    foreach ($err as $error) {
        $session->set($status, $error);
    }
}
$HTMLOUT .= "
    <h1 class='has-text-centered'>" . _('Group message') . "</h1>
    <form action='staffpanel.php?tool=grouppm&amp;action=grouppm' method='post' enctype='multipart/form-data' accept-charset='utf-8'>
      <table class='table table-bordered table-striped'>
        <tr>
          <td colspan='2'>" . _('Subject') . "
            <input type='text' name='subject' class='w-100'></td>
        </tr>
        <tr>
          <td>" . _('Body') . '</td>
          <td>' . _('Groups') . "</td>
          </tr>
        <tr>
          <td class='is-paddingless'>" . BBcode() . '</td>
          <td>' . dropdown() . "</td>

        </tr>
      </table>
        <div class='has-text-centered margin20'>
            <label for='sys'>" . _('Send as System') . "</label>
            <input id='sys' type='checkbox' name='system' value='yes' class=''>
            <input type='submit' value='" . _('Send!') . "' class='button is-small left20'>
        </div>
    </form>";
$title = _('Group PM');
$breadcrumbs = [
    "<a href='{$site_config['paths']['baseurl']}/staffpanel.php'>" . _('Staff Panel') . '</a>',
    "<a href='{$_SERVER['PHP_SELF']}'>$title</a>",
];
echo stdhead($title, $stdhead, 'page-wrapper', $breadcrumbs) . wrapper($HTMLOUT) . stdfoot($stdfoot);
