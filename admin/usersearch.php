<?php

declare(strict_types = 1);

use Pu239\Cache;
use Pu239\Database;

require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_pager.php';
require_once CLASS_DIR . 'class_check.php';
require_once INCL_DIR . 'function_html.php';

$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
global $container, $site_config, $CURUSER;

$search = array_merge($_POST, $_GET);
$cache = $container->get(Cache::class);
$oldest = $cache->get('oldest_');
$fluent = $container->get(Database::class);
if ($oldest === false || is_null($oldest)) {
    $oldest = $fluent->from('users')
                     ->select(null)
                     ->select('registered')
                     ->orderBy('registered')
                     ->limit(1)
                     ->fetch('registered');
    $cache->set('oldest_', $oldest, 0);
}
$oldest = get_date((int) $oldest, 'FORM', 1, 0);
$today = get_date((int) TIME_NOW, 'FORM', 1, 0);

$HTMLOUT = $q1 = $comment_is = $comments_exc = $email_is = '';
$where_is = ' i.type = "login" ';
$HTMLOUT .= "
        <ul class='level-center bg-06'>
            <li class='is-link margin10'>
                <a href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=usersearch&amp;h=1'>" . _('Instructions') . "</a>
            </li>
            <li class='is-link margin10'>
                <a href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=usersearch'>" . _('Reset') . "</a>
            </li>
        </ul>
        <h1 class='has-text-centered'>" . _('Administrative User Search') . '</h1>';

$HTMLOUT .= stdmsg('', '<div>' . _("
Fields left blank will be ignored; Wildcards * and ? may be used in Name, Email and Comments, as well as multiple values separated by spaces (e.g. 'wyz Max*' in Name will list both users named 'wyz' and those whose names start by 'Max'. Similarly  '~' can be used for negation, e.g. '~alfiest' in comments will restrict the search to users that do not have 'alfiest' in their comments).<br><br>

The Ratio field accepts 'Inf' and '---' besides the usual numeric values.<br><br>
The subnet mask may be entered either in dotted decimal or CIDR notation (e.g. 255.255.255.0 is the same as /24).<br><br>
Uploaded and Downloaded should be entered in GB.<br><br>
For search parameters with multiple text fields the second will be ignored unless relevant for the type of search chosen. <br><br>
'Active only' restricts the search to users currently leeching or seeding, 'Disabled IPs' to those whose IPs also show up in disabled accounts.<br><br>
The 'p' columns in the results show partial stats, that is, those of the torrents in progress. <br><br>
The History column lists the number of forum posts and torrent comments, respectively, as well as linking to the history page.") . '</div>', 'bottom20');
$HTMLOUT .= "
    <form method='post' action='{$_SERVER['PHP_SELF']}?tool=usersearch' enctype='multipart/form-data' accept-charset='utf-8'>";
$body = "
        <tr>
            <td class='w-1'>" . _('Name') . "</td>
            <td class='w-10'><input name='n' type='text' value='" . (isset($search['n']) ? $search['n'] : '') . "' class='w-100'></td>
            <td class='w-1'>" . _('Ratio') . "</td>
            <td class='w-10'>
                <select name='rt' class='w-100'>";
$options = [
    _('equal'),
    _('above'),
    _('below'),
    _('between'),
];
for ($i = 0; $i < count($options); ++$i) {
    $body .= "
                    <option value='$i' " . (isset($search['rt']) && $search['rt'] == $i ? 'selected' : '') . ">{$options[$i]}</option>";
}
$body .= "
                </select>
                <input name='r' type='test' value='" . (isset($search['r']) ? $search['r'] : '') . "' class='top10 w-100'>
                <input name='r2' type='text' value='" . (isset($search['r2']) ? $search['r2'] : '') . "' class='top10 w-100'>
            </td>
            <td class='w-1'>" . _('Member status') . "</td>
            <td class='w-10'>
                <select name='st' class='w-100'>";
$options = [
    _('(any)'),
    _('confirmed'),
    _('pending'),
];
for ($i = 0; $i < count($options); ++$i) {
    $body .= "
                    <option value='$i' " . (isset($search['st']) && $search['st'] == $i ? 'selected' : '') . ">{$options[$i]}</option>";
}
$body .= '
                </select>
            </td>
        </tr>
        <tr>
            <td>' . _('Email') . "</td>
            <td><input name='em' type='text' value='" . (isset($search['em']) ? $search['em'] : '') . "' class='w-100'></td>
            <td>" . _('IP') . "</td>
            <td><input name='ip' type='text' value='" . (isset($search['ip']) ? $search['ip'] : '') . "' maxlength='17' class='w-100'></td>
            <td>" . _('Account status') . "</td>
            <td>
                <select name='as' class='w-100'>";
$options = [
    _('(any)'),
    _('Enabled'),
    _('disabled'),
];
for ($i = 0; $i < count($options); ++$i) {
    $body .= "
                    <option value='$i' " . (isset($search['as']) && $search['as'] == $i ? 'selected' : '') . ">{$options[$i]}</option>";
}
$body .= '
                </select>
            </td>
        </tr>
        <tr>
            <td>' . _('Comments') . "</td>
            <td><input name='co' type='text' value='" . (isset($search['co']) ? $search['co'] : '') . "' class='w-100'></td>
            <td>" . _('Mask') . "</td>
            <td><input name='ma' type='text' value='" . (isset($search['ma']) ? $search['ma'] : '') . "' maxlength='17' class='w-100'></td>
            <td>" . _('Class') . "</td>
            <td>
                <select name='c' class='w-100'>
                    <option value=''>" . _('(any)') . '</option>';

$class = isset($search['c']) ? (int) $search['c'] : '';
for ($i = 2;; ++$i) {
    if ($c = get_user_class_name((int) $i - 2)) {
        $body .= "
                    <option value='$i' " . (isset($class) && $class == $i ? 'selected' : '') . ">$c</option>";
    } else {
        break;
    }
}
$body .= '
                </select>
            </td>
        </tr>
        <tr>
            <td>' . _('Joined') . "</td>
            <td>
                <select name='dt' class='w-100'>";
$options = [
    _('on'),
    _('before'),
    _('after'),
    _('between'),
];
for ($i = 0; $i < count($options); ++$i) {
    $body .= "
                    <option value='$i' " . (isset($search['dt']) && $search['dt'] == $i ? 'selected' : '') . ">{$options[$i]}</option>";
}
$body .= "
                </select>
                <input name='d' type='date' value='" . (isset($search['d']) ? $search['d'] : '') . "' min='$oldest' max='$today' class='top10 w-100'>
                <input name='d2' type='date' value='" . (isset($search['d2']) ? $search['d2'] : '') . "' min='$oldest' max='$today' class='top10 w-100'>
            </td>
            <td>" . _('Uploaded') . "</td>
            <td>
                <select name='ult' id='ult' class='w-100'>";

$options = [
    _('equal'),
    _('above'),
    _('below'),
    _('between'),
];
for ($i = 0; $i < count($options); ++$i) {
    $body .= "
                    <option value='$i' " . (isset($search['ult']) && $search['ult'] == $i ? 'selected' : '') . ">{$options[$i]}</option>";
}
$body .= "
                </select>
                <input name='ul' type='number' id='ul' maxlength='7' value='" . (isset($search['ul']) ? $search['ul'] : '') . "' class='top10 w-100'>
                <input name='ul2' type='number' id='ul2' maxlength='7' value='" . (isset($search['ul2']) ? $search['ul2'] : '') . "' class='top10 w-100'>
            </td>
            <td>" . _('Donor') . "</td>
            <td>
                <select name='do' class='w-100'>";
$options = [
    _('(any)'),
    _('Yes'),
    _('No'),
];
for ($i = 0; $i < count($options); ++$i) {
    $body .= "
                    <option value='$i' " . (isset($search['do']) && $search['do'] == $i ? 'selected' : '') . ">{$options[$i]}</option>";
}
$body .= '
                </select>
            </td>
        </tr>
        <tr>
            <td>' . _('Last seen') . "</td>
            <td>
                <select name='lst' class='w-100'>";
$options = [
    _('on'),
    _('before'),
    _('after'),
    _('between'),
];
for ($i = 0; $i < count($options); ++$i) {
    $body .= "
                    <option value='$i' " . (isset($search['lst']) && $search['lst'] == $i ? 'selected' : '') . ">{$options[$i]}</option>";
}
$body .= "
                </select>
                <input name='ls' type='date' value='" . (isset($search['ls']) ? $search['ls'] : '') . "' min='$oldest' max='$today' class='top10 w-100'>
                <input name='ls2' type='date' value='" . (isset($search['ls2']) ? $search['ls2'] : '') . "' min='$oldest' max='$today' class='top10 w-100'>
            </td>
            <td>" . _('Downloaded') . "</td>
            <td>
                <select name='dlt' id='dlt' class='w-100'>";
$options = [
    _('equal'),
    _('above'),
    _('below'),
    _('between'),
];
for ($i = 0; $i < count($options); ++$i) {
    $body .= "
                    <option value='$i' " . (isset($search['dlt']) && $search['dlt'] == $i ? 'selected' : '') . ">{$options[$i]}</option>";
}
$body .= "
                </select>
                <input name='dl' type='number' id='dl' maxlength='7' value='" . (isset($search['dl']) ? $search['dl'] : '') . "' class='top10 w-100'>
                <input name='dl2' type='number' id='dl2' maxlength='7' value='" . (isset($search['dl2']) ? $search['dl2'] : '') . "' class='top10 w-100'>
            </td>
            <td>" . _('Warned') . "</td>
            <td>
                <select name='w' class='w-100'>";
$options = [
    _('(any)'),
    _('Yes'),
    _('No'),
];
for ($i = 0; $i < count($options); ++$i) {
    $body .= "
                    <option value='$i' " . (isset($search['w']) && $search['w'] == $i ? 'selected' : '') . ">{$options[$i]}</option>";
}
$body .= '
                </select>
            </td>
        </tr>
        <tr>
            <td></td>
            <td></td>
            <td>' . _('Active only') . "</td>
            <td>
                <input name='ac' type='checkbox' value='1' " . (isset($search['ac']) ? 'checked' : '') . '>
            </td>
            <td>' . _('Disabled IP') . "</td>
            <td><input name='dip' type='checkbox' value='1' " . (isset($search['dip']) ? 'checked' : '') . "></td>
        </tr>
        <tr>
            <td colspan='6' class='has-text-centered'><input name='submit' type='submit' class='button is-small margin20'></td>
        </tr>";
$HTMLOUT .= main_table($body) . '
    </form>';

/**
 * @param string $param
 *
 * @return bool
 */
function is_set_not_empty(string $param)
{
    global $search;

    if (isset($search[$param]) && !empty($search[$param])) {
        return true;
    } else {
        return false;
    }
}

/**
 * @param int  $up
 * @param int  $down
 * @param bool $color
 *
 * @return float|int|string
 */
function ratios(int $up, int $down, bool $color = true)
{
    if ($down > 0) {
        $r = $up / $down;
        if ($color) {
            $r = "<span style='color: " . get_ratio_color($r) . ";'>" . number_format($r, 3) . '</span>';
        }
    } elseif ($up > 0) {
        $r = 'Inf.';
    } else {
        $r = '---';
    }

    return $r;
}

/**
 * @param string $text
 *
 * @return bool
 */
function haswildcard(string $text)
{
    if (strpos($text, '*') === false && strpos($text, '?') === false && strpos($text, '%') === false && strpos($text, '_') === false) {
        return false;
    } else {
        return true;
    }
}

if (!empty($search)) {
    $name_is = '';
    $names_exc = 0;
    $names = isset($search['n']) ? explode(' ', trim($search['n'])) : [
        0 => '',
    ];
    $join_is = ' LEFT JOIN ips AS i ON u.id = i.userid';
    if ($names[0] !== '') {
        $names_inc = [];
        foreach ($names as $name) {
            if (substr($name, 0, 1) == '~') {
                if ($name == '~') {
                    continue;
                }
                $names_exc[] = substr($name, 1);
            } else {
                $names_inc[] = $name;
            }
        }
        if (is_array($names_inc)) {
            $where_is .= !empty($where_is) ? ' AND (' : '(';
            foreach ($names_inc as $name) {
                if (!haswildcard($name)) {
                    $name_is .= (!empty($name_is) ? ' OR ' : '') . 'u.username = ' . sqlesc($name);
                } else {
                    $name = str_replace([
                        '?',
                        '*',
                    ], [
                        '_',
                        '%',
                    ], $name);
                    $name_is .= (!empty($name_is) ? ' OR ' : '') . 'u.username LIKE ' . sqlesc($name);
                }
            }
            $where_is .= $name_is . ')';
            unset($name_is);
        }

        if (is_array($names_exc)) {
            $where_is .= !empty($where_is) ? ' AND NOT (' : ' NOT (';
            foreach ($names_exc as $name) {
                if (!haswildcard($name)) {
                    $name_is .= (isset($name_is) ? ' OR ' : '') . 'u.username = ' . sqlesc($name);
                } else {
                    $name = str_replace([
                        '?',
                        '*',
                    ], [
                        '_',
                        '%',
                    ], $name);
                    $name_is .= (isset($name_is) ? ' OR ' : '') . 'u.username LIKE ' . sqlesc($name);
                }
            }
            $where_is .= $name_is . ')';
        }
        $q1 .= ($q1 ? '&amp;' : '') . 'n=' . urlencode(trim($search['n']));
    }
    // email
    if (is_set_not_empty('em')) {
        $emaila = explode(' ', trim($search['em']));
        if ($emaila[0] !== '') {
            $where_is .= !empty($where_is) ? ' AND (' : '(';
            foreach ($emaila as $email) {
                if (strpos($email, '*') === false && strpos($email, '?') === false && strpos($email, '%') === false) {
                    if (validemail($email) !== 1) {
                        stdmsg(_('Error'), _('Bad email'));
                        stdfoot();
                        die();
                    }
                    $email_is .= (!empty($email_is) ? ' OR ' : '') . 'u.email =' . sqlesc($email);
                } else {
                    $sql_email = str_replace([
                        '?',
                        '*',
                    ], [
                        '_',
                        '%',
                    ], $email);
                    $email_is .= (!empty($email_is) ? ' OR ' : '') . 'u.email LIKE ' . sqlesc($sql_email);
                }
            }
            $where_is .= $email_is . ')';
            $q1 .= ($q1 ? '&amp;' : '') . 'em=' . urlencode(trim($search['em']));
        }
    }
    //class
    // NB: the c parameter is passed as two units above the real one
    $class = is_set_not_empty('c') ? $search['c'] - 2 : -2;
    if (is_valid_id($class + 1)) {
        $where_is .= (!empty($where_is) ? ' AND ' : '') . "u.class=$class";
        $q1 .= ($q1 ? '&amp;' : '') . 'c=' . ($class + 2);
    }
    // IP
    if (is_set_not_empty('ip')) {
        $ip = trim($search['ip']);
        $regex = "/^(((1?\d{1,2})|(2[0-4]\d)|(25[0-5]))(\.\b|$)){4}$/";
        if (!preg_match($regex, $ip)) {
            stdmsg(_('Error'), _('Bad ip'));
            stdfoot();
            die();
        }
        $mask = trim($search['ma']);
        if (empty($mask) || $mask === '255.255.255.255') {
            $where_is .= (!empty($where_is) ? ' AND ' : '') . "i.ip = '$ip'";
        } else {
            if (substr($mask, 0, 1) == '/') {
                $n = substr($mask, 1, strlen($mask) - 1);
                if (!is_numeric($n) or $n < 0 or $n > 32) {
                    stdmsg(_('Error'), _('Bad subnet mask'));
                    stdfoot();
                    die();
                } else {
                    $mask = long2ip(pow(2, 32) - pow(2, 32 - $n));
                }
            } elseif (!preg_match($regex, $mask)) {
                stdmsg(_('Error'), _('Bad subnet mask'));
                stdfoot();
                die();
            }
            $where_is .= (!empty($where_is) ? ' AND ' : '') . "INET_ATON(i.ip) & INET_ATON('$mask') = INET_ATON('$ip') & INET_ATON('$mask')";
            $q1 .= ($q1 ? '&amp;' : '') . "ma=$mask";
        }
        $q1 .= ($q1 ? '&amp;' : '') . "ip=$ip";
    }
    // ratio
    if (is_set_not_empty('r')) {
        $ratio = trim($search['r']);
        if ($ratio == '---') {
            $ratio2 = '';
            $where_is .= !empty($where_is) ? ' AND ' : '';
            $where_is .= ' u.uploaded = 0 and u.downloaded = 0';
        } elseif (strtolower(substr($ratio, 0, 3)) === 'inf') {
            $ratio2 = '';
            $where_is .= !empty($where_is) ? ' AND ' : '';
            $where_is .= ' u.uploaded > 0 and u.downloaded = 0';
        } else {
            if (!is_numeric($ratio) || $ratio < 0) {
                stdmsg(_('Error'), _('Bad ratio'));
                stdfoot();
                die();
            }
            $where_is .= !empty($where_is) ? ' AND ' : '';
            $where_is .= ' (u.uploaded/u.downloaded)';
            $ratiotype = (int) $search['rt'];
            $q1 .= ($q1 ? '&amp;' : '') . "rt=$ratiotype";
            if ($ratiotype === 3) {
                $ratio2 = trim($search['r2']);
                if (!$ratio2) {
                    stdmsg(_('Error'), _('Two ratios needed for this type of search.'));
                    stdfoot();
                    die();
                }
                if (!is_numeric($ratio2) || $ratio2 < $ratio) {
                    stdmsg(_('Error'), $lang['usersearch_badratio3']);
                    stdfoot();
                    die();
                }
                $where_is .= " BETWEEN $ratio and $ratio2";
                $q1 .= ($q1 ? '&amp;' : '') . "r2=$ratio2";
            } elseif ($ratiotype === 2) {
                $where_is .= " < $ratio";
            } elseif ($ratiotype === 1) {
                $where_is .= ">$ratio";
            } else {
                $where_is .= " BETWEEN ($ratio - 0.004) and ($ratio + 0.004)";
            }
        }
        $q1 .= ($q1 ? '&amp;' : '') . "r=$ratio";
    }
    // comment
    if (is_set_not_empty('co')) {
        $comments = explode(' ', trim($search['co']));
        if ($comments[0] !== '') {
            $comments_inc = [];
            foreach ($comments as $comment) {
                if (substr($comment, 0, 1) === '~') {
                    if ($comment === '~') {
                        continue;
                    }
                    $comments_exc[] = substr($comment, 1);
                } else {
                    $comments_inc[] = $comment;
                }
            }
            if (is_array($comments_inc)) {
                unset($comment);
                $where_is .= !empty($where_is) ? ' AND (' : '(';
                foreach ($comments_inc as $comment) {
                    if (!haswildcard($comment)) {
                        $comment_is .= (!empty($comment_is) ? ' OR ' : '') . 'u.modcomment LIKE ' . sqlesc('%' . $comment . '%');
                    } else {
                        $comment = str_replace([
                            '?',
                            '*',
                        ], [
                            '_',
                            '%',
                        ], $comment);
                        $comment_is .= (!empty($comment_is) ? ' OR ' : '') . 'u.modcomment LIKE ' . sqlesc($comment);
                    }
                }
                $where_is .= $comment_is . ')';
                unset($comment_is);
            }
            if (is_array($comments_exc)) {
                $where_is .= !empty($where_is) ? ' AND NOT (' : ' NOT (';
                foreach ($comments_exc as $comment) {
                    if (!haswildcard($comment)) {
                        $comment_is .= (isset($comment_is) ? ' OR ' : '') . 'u.modcomment LIKE ' . sqlesc('%' . $comment . '%');
                    } else {
                        $comment = str_replace([
                            '?',
                            '*',
                        ], [
                            '_',
                            '%',
                        ], $comment);
                        $comment_is .= (isset($comment_is) ? ' OR ' : '') . 'u.modcomment LIKE ' . sqlesc($comment);
                    }
                }
                $where_is .= $comment_is . ')';
            }
            $q1 .= ($q1 ? '&amp;' : '') . 'co=' . urlencode(trim($search['co']));
            $where_is .= (isset($where_is) ? ' AND ' : '') . 'u.class<' . $CURUSER['class'];
        }
    }
    $unit = 1073741824; // 1GB
    // uploaded
    if (is_set_not_empty('ul')) {
        $ul = trim($search['ul']);
        if (!is_numeric($ul) || $ul < 0) {
            stdmsg(_('Error'), _('Bad upload ammount.'));
            stdfoot();
            die();
        }
        $where_is .= !empty($where_is) ? ' AND ' : '';
        $where_is .= ' u.uploaded ';
        $ultype = (int) $search['ult'];
        $q1 .= ($q1 ? '&amp;' : '') . "ult=$ultype";
        if ($ultype === 3) {
            $ul2 = trim($search['ul2']);
            if (!$ul2) {
                stdmsg(_('Error'), _('Two uploaded amounts needed for this type of search.'));
                stdfoot();
                die();
            }
            if (!is_numeric($ul2) || $ul2 < $ul) {
                stdmsg(_('Error'), _('Bad second uploaded amount.'));
                stdfoot();
                die();
            }
            $where_is .= ' BETWEEN ' . $ul * $unit . ' and ' . $ul2 * $unit;
            $q1 .= ($q1 ? '&amp;' : '') . "ul2=$ul2";
        } elseif ($ultype === 2) {
            $where_is .= ' < ' . $ul * $unit;
        } elseif ($ultype === 1) {
            $where_is .= '>' . $ul * $unit;
        } else {
            $where_is .= ' BETWEEN ' . ($ul - 0.004) * $unit . ' and ' . ($ul + 0.004) * $unit;
        }
        $q1 .= ($q1 ? '&amp;' : '') . "ul=$ul";
    }
    // downloaded
    if (is_set_not_empty('dl')) {
        $dl = trim($search['dl']);
        if (!is_numeric($dl) || $dl < 0) {
            stdmsg(_('Error'), _('Bad download ammount.'));
            stdfoot();
            die();
        }
        $where_is .= !empty($where_is) ? ' AND ' : '';
        $where_is .= ' u.downloaded ';
        $dltype = (int) $search['dlt'];
        $q1 .= ($q1 ? '&amp;' : '') . "dlt=$dltype";
        if ($dltype === 3) {
            $dl2 = trim($search['dl2']);
            if (!$dl2) {
                stdmsg(_('Error'), _('Two downloaded amounts needed for this type of search.'));
                stdfoot();
                die();
            }
            if (!is_numeric($dl2) || $dl2 < $dl) {
                stdmsg(_('Error'), _('Bad second downloaded amount.'));
                stdfoot();
                die();
            }
            $where_is .= ' BETWEEN ' . $dl * $unit . ' and ' . $dl2 * $unit;
            $q1 .= ($q1 ? '&amp;' : '') . "dl2=$dl2";
        } elseif ($dltype === 2) {
            $where_is .= ' < ' . $dl * $unit;
        } elseif ($dltype === 1) {
            $where_is .= '>' . $dl * $unit;
        } else {
            $where_is .= ' BETWEEN ' . ($dl - 0.004) * $unit . ' and ' . ($dl + 0.004) * $unit;
        }
        $q1 .= ($q1 ? '&amp;' : '') . "dl=$dl";
    }
    // date joined
    if (is_set_not_empty('d')) {
        $date = strtotime($search['d']);
        $q1 .= ($q1 ? '&amp;' : '') . "d={$search['d']}";
        $datetype = (int) $search['dt'];
        $q1 .= ($q1 ? '&amp;' : '') . "dt=$datetype";
        if ($datetype === 0) {
            $date2 = $date + 86400;
            $where_is .= (!empty($where_is) ? ' AND ' : '') . "u.registered BETWEEN $date AND $date2";
        } else {
            $where_is .= (!empty($where_is) ? ' AND ' : '') . 'u.registered ';
            if ($datetype === 3) {
                $date2 = strtotime($search['d2']) + 86400;
                $q1 .= ($q1 ? '&amp;' : '') . "d2={$search['d2']}";
                $where_is .= "BETWEEN $date AND $date2";
            } elseif ($datetype === 1) {
                $where_is .= "< $date";
            } elseif ($datetype === 2) {
                $where_is .= "> $date";
            }
        }
    }
    // date last seen
    if (is_set_not_empty('ls')) {
        $date = strtotime($search['ls']);
        $q1 .= ($q1 ? '&amp;' : '') . "d={$search['ls']}";
        $datetype = (int) $search['lst'];
        $q1 .= ($q1 ? '&amp;' : '') . "lst=$datetype";
        if ($datetype === 0) {
            $date2 = $date + 86400;
            $where_is .= (!empty($where_is) ? ' AND ' : '') . "u.last_access BETWEEN $date AND $date2";
        } else {
            $where_is .= (!empty($where_is) ? ' AND ' : '') . 'u.last_access ';
            if ($datetype === 3) {
                $date2 = strtotime($search['ls2']) + 86400;
                $q1 .= ($q1 ? '&amp;' : '') . "ls2={$search['ls2']}";
                $where_is .= "BETWEEN $date AND $date2";
            } elseif ($datetype === 1) {
                $where_is .= "< $date";
            } elseif ($datetype === 2) {
                $where_is .= "> $date";
            }
        }
    }
    // status
    if (is_set_not_empty('st')) {
        $status = (int) $search['st'];
        $where_is .= ((!empty($where_is)) ? ' AND ' : '');
        if ($status === 1) {
            $where_is .= 'u.status = 0';
        } else {
            $where_is .= 'u.status = 4';
        }
        $q1 .= ($q1 ? '&amp;' : '') . "st=$status";
    }
    // account status
    if (is_set_not_empty('as')) {
        $accountstatus = (int) $search['as'];
        $where_is .= (!empty($where_is)) ? ' AND ' : '';
        if ($accountstatus === 1) {
            $where_is .= ' u.status = 0';
        } else {
            $where_is .= ' u.status = 2';
        }
        $q1 .= ($q1 ? '&amp;' : '') . "as=$accountstatus";
    }
    //donor
    if (is_set_not_empty('do')) {
        $donor = (int) $search['do'];
        $where_is .= (!empty($where_is)) ? ' AND ' : '';
        if ($donor === 1) {
            $where_is .= " u.donor = 'yes'";
        } else {
            $where_is .= " u.donor = 'no'";
        }
        $q1 .= ($q1 ? '&amp;' : '') . "do=$donor";
    }
    //warned
    if (is_set_not_empty('w')) {
        $warned = (int) $search['w'];
        $where_is .= (!empty($where_is)) ? ' AND ' : '';
        if ($warned === 1) {
            $where_is .= ' u.warned >= 1';
        } else {
            $where_is .= ' u.warned = 0';
        }
        $q1 .= ($q1 ? '&amp;' : '') . "w=$warned";
    }
    // disabled IP
    /*
    $disabled = isset($search['dip']) ? (int) $search['dip'] : '';
    if (!empty($disabled)) {
        $distinct = 'DISTINCT ';
        $join_is .= ' LEFT JOIN users AS u2 ON u.ip = u2.ip';
        $where_is .= ((!empty($where_is)) ? ' AND ' : '') . "u2.status = 2";
        $q1 .= ($q1 ? '&amp;' : '') . "dip=$disabled";
    }
    */
    // active
    $active = isset($search['ac']) ? $search['ac'] : '';
    if ($active === 1) {
        $distinct = 'DISTINCT ';
        $join_is .= ' LEFT JOIN peers AS p ON u.id = p.userid';
        $q1 .= ($q1 ? '&amp;' : '') . "ac=$active";
    }
    $from_is = isset($join_is) ? 'users AS u' . $join_is : 'users AS u';
    $distinct = isset($distinct) ? $distinct : '';
    $where_is = !empty($where_is) ? $where_is : '';
    $queryc = 'SELECT COUNT(' . $distinct . 'u.id) FROM ' . $from_is . (empty($where_is) ? '' : " WHERE $where_is ");
    $querypm = 'FROM ' . $from_is . (empty($where_is) ? ' ' : " WHERE $where_is ");
    $announcement_query = "SELECT u.id FROM $from_is " . (empty($where_is) ? ' WHERE 1 = 1' : " WHERE $where_is");
    $select_is = 'u.id, u.username, u.email, u.status, u.registered, u.last_access, u.class, u.uploaded, u.downloaded, u.donor, u.modcomment, u.status, u.warned, INET6_NTOA(i.ip) AS ip, i.type';
    $query1 = 'SELECT ' . $distinct . ' ' . $select_is . ' ' . $querypm;
    $res = sql_query($queryc) or sqlerr(__FILE__, __LINE__);
    $arr = mysqli_fetch_row($res);
    $count = (int) $arr[0];
    $q1 = isset($q1) ? ($q1 . '&amp;') : '';
    $perpage = 30;
    $pager = pager($perpage, $count, "{$site_config['paths']['baseurl']}/staffpanel.php?tool=usersearch&amp;" . $q1);
    $query1 .= $pager['limit'];
    $res = sql_query($query1) or sqlerr(__FILE__, __LINE__);
    if (mysqli_num_rows($res) == 0) {
        stdmsg(_('Warning'), _('No user was found.'));
    } else {
        if ($count > $perpage) {
            $HTMLOUT .= $pager['pagertop'];
        }
        $heading = '
        <tr>
            <th>' . _('Name') . '</th>
            <th>' . _('Ratio') . '</th>
            <th>' . _('IP') . '</th>
            <th>' . _('Email') . '</th>
            <th>' . _('Joined') . '</th>
            <th>' . _('Last seen') . '</th>
            <th>' . _('Status') . '</th>
            <th>' . _('Enabled') . '</th>
            <th>' . _('pR') . '</th>
            <th>' . _('pUL (MB)') . '</th>
            <th>' . _('pDL(MB)') . '</th>
            <th>' . _('History') . '</th>
        </tr>';
        $body = $ids = '';
        while ($user = mysqli_fetch_array($res)) {
            if (!empty($user['ip'])) {
                $count = $fluent->from('bans')
                                ->select(null)
                                ->select('COUNT(id) AS count')
                                ->where('INET6_NTOA(first) <= ?', $user['ip'])
                                ->where('INET6_NTOA(last) >= ?', $user['ip'])
                                ->fetch('count');
                if ($count === 0) {
                    $ipstr = $user['ip'] . ' ' . $user['type'];
                } else {
                    $ipstr = "<a href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=testip&amp;action=testip&amp;ip=" . htmlsafechars($user['ip']) . "'><span style='color: #FF0000;'><b>" . htmlsafechars($user['ip']) . '</b></span></a>';
                }
            } else {
                $ipstr = '---';
            }
            $auxres = sql_query('SELECT SUM(uploaded) AS pul, SUM(downloaded) AS pdl FROM peers WHERE userid=' . sqlesc($user['id'])) or sqlerr(__FILE__, __LINE__);
            $array = mysqli_fetch_array($auxres);
            $pul = (int) $array['pul'];
            $pdl = (int) $array['pdl'];
            if ($pdl > 0) {
                $partial = ratios($pul, $pdl) . ' (' . mksize($pul) . '/' . mksize($pdl) . ')';
            } elseif ($pul > 0) {
                $partial = 'Inf. ' . mksize($pul) . '/' . mksize($pdl) . ')';
            } else {
                $partial = '---';
            }
            $auxres = sql_query('SELECT COUNT(DISTINCT p.id)
      FROM posts AS p LEFT JOIN topics AS t ON p.topic_id=t.id
      LEFT JOIN forums AS f ON t.forum_id=f.id
      WHERE p.user_id=' . sqlesc($user['id']) . ' AND f.min_class_read <= ' . sqlesc($CURUSER['class'])) or sqlerr(__FILE__, __LINE__);
            $n = mysqli_fetch_row($auxres);
            $n_posts = $n[0];
            $auxres = sql_query('SELECT COUNT(id) FROM comments WHERE user = ' . sqlesc($user['id'])) or sqlerr(__FILE__, __LINE__);
            $n = mysqli_fetch_row($auxres);
            $n_comments = $n[0];
            $ids .= (int) $user['id'] . ':';
            $body .= '
        <tr>
            <td>' . format_username((int) $user['id']) . '</td>
            <td>' . ratios((int) $user['uploaded'], (int) $user['downloaded']) . '</td>
            <td>' . $ipstr . '</td>
            <td>' . htmlsafechars($user['email']) . '</td>
            <td>' . get_date((int) $user['registered'], '') . '</td>
            <td>' . get_date((int) $user['last_access'], '', 0, 1) . '</td>
            <td>' . $user['status'] . '</td>
            <td>' . $user['status'] . '</td>
            <td>' . ratios($pul, $pdl) . '</td>
            <td>' . number_format($pul / 1048576) . '</td>
            <td>' . number_format($pdl / 1048576) . '</td>
            <td>' . ($n_posts ? "<a href='{$site_config['paths']['baseurl']}/userhistory.php?action=viewposts&amp;id=" . (int) $user['id'] . "'>$n_posts</a>" : $n_posts) . '|' . ($n_comments ? "<a href='{$site_config['paths']['baseurl']}/userhistory.php?action=viewcomments&amp;id=" . (int) $user['id'] . "'>$n_comments</a>" : $n_comments) . '</td>
        </tr>';
        }
        $HTMLOUT .= main_table($body, $heading, 'top20');
        if ($count > $perpage) {
            $HTMLOUT .= $pager['pagerbottom'];
        }
        $HTMLOUT .= "
<br>
<form method='post' action='{$site_config['paths']['baseurl']}/new_announcement.php' enctype='multipart/form-data' accept-charset='utf-8'>
    <div class='has-text-centered margin20'>
        <input name='n_pms' type='hidden' value='" . $count . "'>
        <input name='ann_query' type='hidden' value='" . rawurlencode($announcement_query) . "'>
        <button type='submit' class='button is-small' disabled>" . _('Create New Announcement') . '</button>
    </div>
</form>';
    }
}
if (isset($pagemenu)) {
    $HTMLOUT .= ("<p>$pagemenu<br>$browsemenu</p>");
}

$title = _('User Search');
$breadcrumbs = [
    "<a href='{$site_config['paths']['baseurl']}/staffpanel.php'>" . _('Staff Panel') . '</a>',
    "<a href='{$_SERVER['PHP_SELF']}'>$title</a>",
];
echo stdhead($title, [], 'page-wrapper', $breadcrumbs) . wrapper($HTMLOUT) . stdfoot();
