<?php

declare(strict_types = 1);

use Pu239\Cache;
use Pu239\Message;

require_once __DIR__ . '/../include/bittorrent.php';
require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_html.php';
$curuser = check_user_status();
global $container, $site_config;

$userid = isset($_GET['id']) ? (int) $_GET['id'] : $curuser['id'];
$action = isset($_GET['action']) ? htmlsafechars($_GET['action']) : '';
if (!is_valid_id($userid)) {
    stderr(_('Error'), _('Invalid ID.'));
}
if ($userid != $curuser['id']) {
    stderr(_('Error'), _('Access denied.'));
}
$dt = TIME_NOW;
//== action == add
$messages_class = $container->get(Message::class);
$cache = $container->get(Cache::class);
if ($action === 'add') {
    if (!isset($_GET['targetid'])) {
        stderr(_('Error'), _('Invalid ID.'));
    }
    $targetid = (int) $_GET['targetid'];
    $type = $_GET['type'];
    if (!is_valid_id($targetid)) {
        stderr(_('Error'), _('Invalid ID.'));
    }
    if ($curuser['id'] == $targetid) {
        stderr(_('Error'), _("You can't add yourself."));
    }
    if ($type === 'friend') {
        $table_is = $frag = 'friends';
        $field_is = 'friendid';
        $confirmed = 'confirmed';
    } elseif ($type === 'block') {
        $table_is = $frag = 'blocks';
        $field_is = 'blockid';
    } else {
        stderr(_('Error'), _('Unknown type.'));
    }
    if ($type === 'friend') {
        $r = sql_query("SELECT id, confirmed FROM $table_is WHERE userid = " . sqlesc($userid) . " AND $field_is = " . sqlesc($targetid)) or sqlerr(__FILE__, __LINE__);
        $q = mysqli_fetch_assoc($r);
        $subject = _('New Friend Request!');
        $msg = _fe('{0} has added you to their Friends List. See all Friend Requests {1}here{2}', "[url={$site_config['paths']['baseurl']}/userdetails.php?id=$userid][b]{$curuser['username']}[/b][/url]", "[url={$site_config['paths']['baseurl']}/friends.php#pending][b]", '[/b][/url]');
        $msgs_buffer[] = [
            'receiver' => $targetid,
            'added' => $dt,
            'msg' => $msg,
            'subject' => $subject,
        ];
        $messages_class->insert($msgs_buffer);
        if (mysqli_num_rows($r) == 1) {
            stderr(_('Error'), _f('User ID is already in your %s list', htmlsafechars($table_is)));
        }
        sql_query("INSERT INTO $table_is VALUES (0, " . sqlesc($userid) . ', ' . sqlesc($targetid) . ", 'no')") or sqlerr(__FILE__, __LINE__);
        stderr(_('Request Added!'), _fe('The user will be informed of your Friend Request, you will be informed via PM upon confirmation.<br><br>{0}Go to your Friends List{1}', "<a href='{$site_config['paths']['baseurl']}/friends.php?id=$userid#$frag'><b>", '</b></a>'));
        die();
    }
    if ($type === 'block') {
        $r = sql_query("SELECT id FROM $table_is WHERE userid=" . sqlesc($userid) . " AND $field_is = " . sqlesc($targetid)) or sqlerr(__FILE__, __LINE__);
        if (mysqli_num_rows($r) == 1) {
            stderr(_('Error'), _f('User ID is already in your %s list', htmlsafechars($table_is)));
        }
        sql_query("INSERT INTO $table_is VALUES (0, " . sqlesc($userid) . ', ' . sqlesc($targetid) . ')') or sqlerr(__FILE__, __LINE__);
        $cache->delete('Blocks_' . $userid);
        $cache->delete('Friends_' . $userid);
        $cache->delete('Blocks_' . $targetid);
        $cache->delete('Friends_' . $targetid);
        $cache->delete('user_friends_' . $targetid);
        $cache->delete('user_friends_' . $userid);
        header("Location: {$site_config['paths']['baseurl']}/friends.php?id=$userid#$frag");
        die();
    }
}
//== action == confirm
if ($action === 'confirm') {
    $targetid = (int) $_GET['targetid'];
    $sure = isset($_GET['sure']) ? (int) $_GET['sure'] : false;
    if (isset($_GET['type'])) {
        $type = $_GET['type'] === 'friend' ? 'friend' : 'block';
    } else {
        stderr(_('Error'), _('Invalid type.'));
    }
    if (!is_valid_id($targetid)) {
        stderr(_('Error'), _('Invalid ID.'));
    }
    $hash = md5('c@@me' . $curuser['id'] . $targetid . $type . 'confirm' . 'sa7t');
    if (!$sure) {
        stderr(_('Confirm Friend'), _fe('Do you really want to confirm this person? Click {0}here{1} if you are sure.', "<a href='{$site_config['paths']['baseurl']}/friends.php?id=$userid&amp;action=confirm&amp;type=$type&amp;targetid=$targetid&amp;sure=1&amp;h=$hash'><b>", '</b></a>'));
    }
    if ($_GET['h'] != $hash) {
        stderr(_('Error'), _('Invalid data.'));
    }
    if ($type === 'friend') {
        sql_query('INSERT INTO friends VALUES (0, ' . sqlesc($userid) . ', ' . sqlesc($targetid) . ", 'yes') ON DUPLICATE KEY UPDATE userid=" . sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
        sql_query("UPDATE friends SET confirmed = 'yes' WHERE userid = " . sqlesc($targetid) . ' AND friendid = ' . sqlesc($curuser['id'])) or sqlerr(__FILE__, __LINE__);
        $cache->delete('Blocks_' . $userid);
        $cache->delete('Friends_' . $userid);
        $cache->delete('Blocks_' . $targetid);
        $cache->delete('Friends_' . $targetid);
        $cache->delete('user_friends_' . $targetid);
        $cache->delete('user_friends_' . $userid);
        $subject = _('You have a new friend!');
        $msg = _fe('{0} has just confirmed your Friendship Request. See your Friends {1}here{2}', "[url={$site_config['paths']['baseurl']}/userdetails.php?id=$userid][b]{$curuser['username']}[/b][/url]", "[url={$site_config['paths']['baseurl']}/friends.php][b]", '[/b][/url]');
        $msgs_buffer[] = [
            'receiver' => $targetid,
            'added' => $dt,
            'msg' => $msg,
            'subject' => $subject,
        ];
        $messages_class->insert($msgs_buffer);
        $frag = 'friends';
        header("Refresh: 3; url=friends.php?id=$userid#$frag");
        stderr('Success', _('Friend was added successfully.'));
    }
} elseif ($action === 'delpending') {
    $targetid = (int) $_GET['targetid'];
    $sure = isset($_GET['sure']) ? (int) $_GET['sure'] : false;
    $type = htmlsafechars($_GET['type']);
    if (!is_valid_id($targetid)) {
        stderr(_('Error'), _('Invalid ID.'));
    }
    $hash = md5('c@@me' . $curuser['id'] . $targetid . $type . 'confirm' . 'sa7t');
    if (!$sure) {
        stderr(_f('Delete %s Request', $type), _fe('Do you really want to delete this friend request? Click {0}here{1} if you are sure.', "<a href='{$site_config['paths']['baseurl']}/friends.php?id=$userid&amp;action=delpending&amp;type=$type&amp;targetid=$targetid&amp;sure=1&amp;h=$hash'><b>", '</b></a>'));
    }
    if ($_GET['h'] != $hash) {
        stderr(_('Error'), _('Invalid data.'));
    }
    if ($type === 'friend') {
        sql_query('DELETE FROM friends WHERE userid = ' . sqlesc($targetid) . ' AND friendid = ' . sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
        $cache->delete('Friends_' . $userid);
        $cache->delete('Friends_' . $targetid);
        $cache->delete('user_friends_' . $userid);
        $cache->delete('user_friends_' . $targetid);
        $frag = 'friends';
        header("Refresh: 3; url=friends.php?id=$userid#$frag");
        stderr(_('Success'), _('Friend was deleted successfully.'));
    }
} elseif ($action === 'delete') {
    $targetid = (int) $_GET['targetid'];
    $sure = isset($_GET['sure']) ? (int) $_GET['sure'] : false;
    $type = htmlsafechars($_GET['type']);
    if (!is_valid_id($targetid)) {
        stderr(_('Error'), _('Invalid ID.'));
    }
    $hash = md5('c@@me' . $curuser['id'] . $targetid . $type . 'confirm' . 'sa7t');
    if (!$sure) {
        stderr("Delete $type", "Do you really want to delete a $type? Click\n<a href='{$site_config['paths']['baseurl']}/friends.php?id=$userid&amp;action=delete&amp;type=$type&amp;targetid=$targetid&amp;sure=1&amp;h=$hash'><b>here</b></a> if you are sure.", null);
    }
    if ($_GET['h'] != $hash) {
        stderr(_('Error'), _('Invalid data.'));
    }
    if ($type === 'friend') {
        sql_query('DELETE FROM friends WHERE userid =' . sqlesc($userid) . ' AND friendid=' . sqlesc($targetid)) or sqlerr(__FILE__, __LINE__);
        sql_query('DELETE FROM friends WHERE userid =' . sqlesc($targetid) . ' AND friendid=' . sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
        $cache->delete('Friends_' . $userid);
        $cache->delete('Friends_' . $targetid);
        $cache->delete('user_friends_' . $userid);
        $cache->delete('user_friends_' . $targetid);
        $frag = 'friends';
        header("Refresh: 3; url=friends.php?id=$userid#$frag");
        stderr(_('Success'), _('Friend was deleted successfully.'));
    } elseif ($type === 'block') {
        sql_query('DELETE FROM blocks WHERE userid = ' . sqlesc($userid) . ' AND blockid = ' . sqlesc($targetid)) or sqlerr(__FILE__, __LINE__);
        $cache->delete('Blocks_' . $userid);
        $cache->delete('Blocks_' . $targetid);
        $frag = 'blocks';
        header("Refresh: 3; url=friends.php?id=$userid#$frag");
        stderr(_('Success'), _('Block was deleted successfully.'));
    } else {
        stderr(_('Error'), _('Invalid type.'));
    }
    header('Location: friends.php');
    die();
}

$res = sql_query('SELECT * FROM users WHERE id = ' . sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
$user = mysqli_fetch_assoc($res) or stderr(_('Error'), _('No user with that ID.'));
$HTMLOUT = '';
$i = 0;
$res = sql_query('SELECT f.userid AS id, u.username, u.class, u.avatar, u.offensive_avatar, u.anonymous_until, u.title, u.donor, u.warned, u.status, u.leechwarn, u.chatpost, u.pirate, u.king, u.last_access, u.perms FROM friends AS f LEFT JOIN users AS u ON f.userid = u.id WHERE friendid = ' . sqlesc($curuser['id']) . " AND f.confirmed = 'no' AND NOT f.userid IN (SELECT blockid FROM blocks WHERE blockid=f.userid) ORDER BY username") or sqlerr(__FILE__, __LINE__);
$friendsp = '';
if (mysqli_num_rows($res) == 0) {
    $friendsp = '<em>' . _('Your pending list is empty') . '.</em>';
} else {
    while ($friendp = mysqli_fetch_assoc($res)) {
        $dt = $dt - 180;
        $online = ($friendp['last_access'] >= $dt && get_anonymous((int) $friendp['id']) ? ' <img src="' . $site_config['paths']['images_baseurl'] . 'online.png" alt="Online" class="tooltipper" title="Online">' : '<img src="' . $site_config['paths']['images_baseurl'] . 'offline.png" alt="Offline" class="tooltipper" title="Offline">');
        $title = !empty($friendp['title']) ? htmlsafechars($friendp['title']) : '';
        if (!$title) {
            $title = get_user_class_name((int) $friendp['class']);
        }
        $linktouser = format_username((int) $friendp['id']) . " [$title]<br>" . _('last seen on') . ' ' . (get_anonymous((int) $friendp['id']) ? get_date((int) $friendp['last_access'], '') : _('Never'));
        $confirm = "<br><span class='button is-small'><a href='{$site_config['paths']['baseurl']}/friends.php?id=$userid&amp;action=confirm&amp;type=friend&amp;targetid=" . (int) $friendp['id'] . "' class='has-text-black'>Confirm</a></span>";
        $block = " <span class='button is-small'><a href='{$site_config['paths']['baseurl']}/friends.php?action=add&amp;type=block&amp;targetid=" . (int) $friendp['id'] . "' class='has-text-black'>Block</a></span>";
        $avatar = get_avatar($friendp);
        $reject = " <span class='button is-small'><a href='{$site_config['paths']['baseurl']}/friends.php?id=$userid&amp;action=delpending&amp;type=friend&amp;targetid=" . (int) $friendp['id'] . "' class='has-text-black'>" . _('Reject') . '</a></span>';
        $friendsp .= "<div>{$avatar}<p>{$linktouser}<br><br>{$confirm}{$block}{$reject}</p></div><br>";
    }
}
$res = sql_query('SELECT f.friendid AS id, u.username, u.donor, u.class, u.warned, u.status, u.leechwarn, u.chatpost, u.pirate, u.king, u.last_access FROM friends AS f LEFT JOIN users AS u ON f.friendid = u.id WHERE userid = ' . sqlesc($userid) . " AND f.confirmed = 'no' ORDER BY username") or sqlerr(__FILE__, __LINE__);
$friendreqs = '';
if (mysqli_num_rows($res) == 0) {
    $friendreqs = '<em>' . _('Your requests list is empty.') . '</em>';
} else {
    $i = 0;
    $friendreqs = "<table class='table table-bordered table-striped'>";
    while ($friendreq = mysqli_fetch_assoc($res)) {
        if ($i % 6 == 0) {
            $friendreqs .= '<tr>';
        }
        $friendreqs .= '<td>' . format_username((int) $friendreq['id']) . '</td></tr>';
        if ($i % 6 == 5) {
            $friendreqs .= '</tr>';
        }
        ++$i;
    }
    $friendreqs .= '</table>';
}
$i = 0;
$res = sql_query('SELECT f.friendid AS id, u.username, u.class, u.avatar, u.offensive_avatar, u.anonymous_until, u.title, u.donor, u.warned, u.status, u.leechwarn, u.chatpost, u.pirate, u.king, u.last_access, u.uploaded, u.downloaded, u.country, u.perms FROM friends AS f LEFT JOIN users AS u ON f.friendid=u.id WHERE userid=' . sqlesc($userid) . " AND f.confirmed = 'yes' ORDER BY username") or sqlerr(__FILE__, __LINE__);
$friends = '';
if (mysqli_num_rows($res) == 0) {
    $friends = '<em>' . _('Your friends list is empty.') . '</em>';
} else {
    while ($friend = mysqli_fetch_assoc($res)) {
        $dt = $dt - 180;
        $online = ($friend['last_access'] >= $dt && get_anonymous((int) $friend['id']) ? ' <img src="' . $site_config['paths']['images_baseurl'] . 'online.png" alt="Online" class="tooltipper" title="Online">' : '<img src="' . $site_config['paths']['images_baseurl'] . 'offline.png" alt="Offline" class="tooltipper" title="Offline">');
        $title = !empty($friend['title']) ? htmlsafechars($friend['title']) : '';
        if (!$title) {
            $title = get_user_class_name((int) $friend['class']);
        }
        $ratio = member_ratio((float) $friend['uploaded'], (float) $friend['downloaded']);
        $linktouser = format_username((int) $friend['id']) . " [$title] [$ratio]<br>" . _('last seen on') . ' ' . (get_anonymous((int) $friend['id']) ? get_date((int) $friend['last_access'], '') : 'Never');
        $delete = "<span class='button is-small'><a href='{$site_config['paths']['baseurl']}/friends.php?id=$userid&amp;action=delete&amp;type=friend&amp;targetid=" . (int) $friend['id'] . "' class='has-text-black'>" . _('Remove') . '</a></span>';
        $pm_link = " <span class='button is-small'><a href='{$site_config['paths']['baseurl']}/messages.php?action=send_message&amp;receiver=" . (int) $friend['id'] . "' class='has-text-black'>" . _('Send PM') . '</a></span>';
        $avatar = get_avatar($friend);
        $friends .= "<div>{$avatar}<p>{$linktouser} {$online}<br><br>{$delete}{$pm_link}</p></div><br>";
    }
}

$res = sql_query('SELECT b.blockid AS id, u.username, u.donor, u.class, u.warned, u.status, u.leechwarn, u.chatpost, u.pirate, u.king, u.last_access FROM blocks AS b LEFT JOIN users AS u ON b.blockid=u.id WHERE userid=' . sqlesc($userid) . ' ORDER BY username') or sqlerr(__FILE__, __LINE__);
$blocks = '';
if (mysqli_num_rows($res) == 0) {
    $blocks = '<em>' . _('Your blocked list is empty.') . '</em>';
} else {
    while ($block = mysqli_fetch_assoc($res)) {
        $blocks .= '<div>';
        $blocks .= "<span class='button is-small'><a href='{$site_config['paths']['baseurl']}/friends.php?id=$userid&amp;action=delete&amp;type=block&amp;targetid=" . (int) $block['id'] . "' class='has-text-black'>" . _('Delete') . '</a></span><br>';
        $blocks .= '<p>' . format_username((int) $block['id']) . '</p></div><br>';
    }
}

$country = '';
$countries = countries();
foreach ($countries as $cntry) {
    if ($cntry['id'] == $user['country']) {
        $country = "<img src='{$site_config['paths']['images_baseurl']}flag/{$cntry['flagpic']}' alt='" . htmlsafechars($cntry['name']) . "'>";
        break;
    }
}
$HTMLOUT .= "
        <h1 class='has-text-centered'>" . _f('Personal Lists for %s', format_comment($user['username'])) . " $country</h1>
        <table class='table table-bordered table-striped top20 bottom20'>
            <thead>
                <tr>
                    <th class='w-50'>
                        <h2><a id='friends'>" . _('Friends list') . "</a></h2>
                    </th>
                    <th>
                        <h2><a id='blocks'>" . _('Blocked list') . "</a></h2>
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class='w-50'>$friends</td>
                    <td>$blocks</td>
                </tr>
            </tbody>
        </table>
        <table class='table table-bordered table-striped top20 bottom20'>
            <thead>
                <tr>
                    <th class='w-50'><h2><a id='friendsp'>" . _('Pending List') . "</a></h2></th>
                    <th><h2><a id='friendreqs'>" . _('Awaiting Confirmation') . "</a></h2></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class='w-50'>$friendsp</td>
                    <td>$friendreqs</td>
                </tr>
            </tbody>
        </table>
        <div class='has-text-centered bottom20'>
            <a href='{$site_config['paths']['baseurl']}/users.php' class='button is-small top20'>
                " . _('Find User/Browse User List') . '
            </a>
        </div>';
$title = _('Friends');
$breadcrumbs = [
    "<a href='{$_SERVER['PHP_SELF']}'>$title</a>",
];
echo stdhead($title, [], 'page-wrapper', $breadcrumbs) . wrapper($HTMLOUT) . stdfoot();
