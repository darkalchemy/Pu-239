<?php

declare(strict_types = 1);

use Pu239\Cache;
use Pu239\Message;

require_once __DIR__ . '/../include/bittorrent.php';
require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_html.php';
check_user_status();
$lang = array_merge(load_language('global'), load_language('friends'));
global $container, $site_config, $CURUSER, $mysqli;

$userid = isset($_GET['id']) ? (int) $_GET['id'] : $CURUSER['id'];
$action = isset($_GET['action']) ? htmlsafechars($_GET['action']) : '';
if (!is_valid_id($userid)) {
    stderr($lang['friends_error'], $lang['friends_invalid_id']);
}
if ($userid != $CURUSER['id']) {
    stderr($lang['friends_error'], $lang['friends_no_access']);
}
$dt = TIME_NOW;
//== action == add
$messages_class = $container->get(Message::class);
$cache = $container->get(Cache::class);
if ($action === 'add') {
    $targetid = (int) $_GET['targetid'];
    $type = $_GET['type'];
    if (!is_valid_id($targetid)) {
        stderr('Error', 'Invalid ID.');
    }
    if ($CURUSER['id'] == $targetid) {
        stderr('Error', 'Ye cant add yerself nugget.');
    }
    if ($type === 'friend') {
        $table_is = $frag = 'friends';
        $field_is = 'friendid';
        $confirmed = 'confirmed';
    } elseif ($type === 'block') {
        $table_is = $frag = 'blocks';
        $field_is = 'blockid';
    } else {
        stderr('Error', 'Unknown type.');
    }
    if ($type === 'friend') {
        $r = sql_query("SELECT id, confirmed FROM $table_is WHERE userid=" . sqlesc($userid) . " AND $field_is = " . sqlesc($targetid)) or sqlerr(__FILE__, __LINE__);
        $q = mysqli_fetch_assoc($r);
        $subject = 'New Friend Request!';
        $msg = "[url={$site_config['paths']['baseurl']}/userdetails.php?id=$userid][b]This person[/b][/url] has added you to their Friends List. See all Friend Requests [url={$site_config['paths']['baseurl']}/friends.php#pending][b]Here[/b][/url]\n ";
        $msgs_buffer[] = [
            'sender' => 0,
            'receiver' => $targetid,
            'added' => $dt,
            'msg' => $msg,
            'subject' => $subject,
        ];
        $messages_class->insert($msgs_buffer);
        if (mysqli_num_rows($r) == 1) {
            stderr('Error', 'User ID is already in your ' . htmlsafechars($table_is) . ' list.');
        }
        sql_query("INSERT INTO $table_is VALUES (0, " . sqlesc($userid) . ', ' . sqlesc($targetid) . ", 'no')") or sqlerr(__FILE__, __LINE__);
        stderr('Request Added!', "The user will be informed of your Friend Request, you will be informed via PM upon confirmation.<br><br><a href='{$site_config['paths']['baseurl']}/friends.php?id=$userid#$frag'><b>Go to your Friends List</b></a>", null);
        die();
    }
    if ($type === 'block') {
        $r = sql_query("SELECT id FROM $table_is WHERE userid=" . sqlesc($userid) . " AND $field_is = " . sqlesc($targetid)) or sqlerr(__FILE__, __LINE__);
        if (mysqli_num_rows($r) == 1) {
            stderr('Error', 'User ID is already in your ' . htmlsafechars($table_is) . ' list.');
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
    if (isset($_POST['type'])) {
        $type = $_GET['type'] === 'friend' ? 'friend' : 'block';
    } else {
        stderr($lang['friends_error'], 'LoL');
    }
    if (!is_valid_id($targetid)) {
        stderr('Error', 'Invalid ID.');
    }
    $hash = md5('c@@me' . $CURUSER['id'] . $targetid . $type . 'confirm' . 'sa7t');
    if (!$sure) {
        stderr('Confirm Friend', "Do you really want to confirm this person? Click\n<a href='{$site_config['paths']['baseurl']}/friends.php?id=$userid&amp;action=confirm&amp;type=$type&amp;targetid=$targetid&amp;sure=1&amp;h=$hash'><b>here</b></a> if you are sure.", null);
    }
    if ($_GET['h'] != $hash) {
        stderr('Error', 'what are you doing?');
    }
    if ($type === 'friend') {
        sql_query('INSERT INTO friends VALUES (0, ' . sqlesc($userid) . ', ' . sqlesc($targetid) . ", 'yes') ON DUPLICATE KEY UPDATE userid=" . sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
        sql_query("UPDATE friends SET confirmed = 'yes' WHERE userid=" . sqlesc($targetid) . ' AND friendid=' . sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);
        $cache->delete('Blocks_' . $userid);
        $cache->delete('Friends_' . $userid);
        $cache->delete('Blocks_' . $targetid);
        $cache->delete('Friends_' . $targetid);
        $cache->delete('user_friends_' . $targetid);
        $cache->delete('user_friends_' . $userid);
        $subject = 'You have a new friend!';
        $msg = "[url={$site_config['paths']['baseurl']}/userdetails.php?id=$userid][b]This person[/b][/url] has just confirmed your Friendship Request. See your Friends  [url={$site_config['paths']['baseurl']}/friends.php][b]Here[/b][/url]\n ";
        $msgs_buffer[] = [
            'sender' => 0,
            'receiver' => $targetid,
            'added' => $dt,
            'msg' => $msg,
            'subject' => $subject,
        ];
        $messages_class->insert($msgs_buffer);
        $frag = 'friends';
        header("Refresh: 3; url=friends.php?id=$userid#$frag");
        mysqli_affected_rows($mysqli) == 1 ? stderr('Success', 'Friend was added successfully.') : stderr('oops', 'That friend is already confirmed!');
    }
} elseif ($action === 'delpending') {
    $targetid = (int) $_GET['targetid'];
    $sure = isset($_GET['sure']) ? (int) $_GET['sure'] : false;
    $type = htmlsafechars($_GET['type']);
    if (!is_valid_id($targetid)) {
        stderr('Error', 'Invalid ID.');
    }
    $hash = md5('c@@me' . $CURUSER['id'] . $targetid . $type . 'confirm' . 'sa7t');
    if (!$sure) {
        stderr("Delete $type Request", "Do you really want to delete this friend request? Click\n<a href='{$site_config['paths']['baseurl']}/friends.php?id=$userid&amp;action=delpending&amp;type=$type&amp;targetid=$targetid&amp;sure=1&amp;h=$hash'><b>here</b></a> if you are sure.", null);
    }
    if ($_GET['h'] != $hash) {
        stderr('Error', 'what are you doing?');
    }
    if ($type === 'friend') {
        sql_query('DELETE FROM friends WHERE userid=' . sqlesc($targetid) . ' AND friendid=' . sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
        $cache->delete('Friends_' . $userid);
        $cache->delete('Friends_' . $targetid);
        $cache->delete('user_friends_' . $userid);
        $cache->delete('user_friends_' . $targetid);
        $frag = 'friends';
        header("Refresh: 3; url=friends.php?id=$userid#$frag");
        mysqli_affected_rows($mysqli) == 1 ? stderr('Success', 'Friend was deleted successfully.') : stderr('oopss', 'No friend request found with ID !! .');
    }
} elseif ($action === 'delete') {
    $targetid = (int) $_GET['targetid'];
    $sure = isset($_GET['sure']) ? (int) $_GET['sure'] : false;
    $type = htmlsafechars($_GET['type']);
    if (!is_valid_id($targetid)) {
        stderr('Error', 'Invalid ID.');
    }
    $hash = md5('c@@me' . $CURUSER['id'] . $targetid . $type . 'confirm' . 'sa7t');
    if (!$sure) {
        stderr("Delete $type", "Do you really want to delete a $type? Click\n<a href='{$site_config['paths']['baseurl']}/friends.php?id=$userid&amp;action=delete&amp;type=$type&amp;targetid=$targetid&amp;sure=1&amp;h=$hash'><b>here</b></a> if you are sure.", null);
    }
    if ($_GET['h'] != $hash) {
        stderr('Error', 'what are you doing?');
    }
    if ($type === 'friend') {
        sql_query('DELETE FROM friends WHERE userid=' . sqlesc($userid) . ' AND friendid=' . sqlesc($targetid)) or sqlerr(__FILE__, __LINE__);
        sql_query('DELETE FROM friends WHERE userid=' . sqlesc($targetid) . ' AND friendid=' . sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
        $cache->delete('Friends_' . $userid);
        $cache->delete('Friends_' . $targetid);
        $cache->delete('user_friends_' . $userid);
        $cache->delete('user_friends_' . $targetid);
        $frag = 'friends';
        header("Refresh: 3; url=friends.php?id=$userid#$frag");
        mysqli_affected_rows($mysqli) == 1 ? stderr('Success', 'Friend was deleted successfully.') : stderr('oopss', 'No friend request found with ID !! .');
    } elseif ($type === 'block') {
        sql_query('DELETE FROM blocks WHERE userid=' . sqlesc($userid) . ' AND blockid=' . sqlesc($targetid)) or sqlerr(__FILE__, __LINE__);
        $cache->delete('Blocks_' . $userid);
        $cache->delete('Blocks_' . $targetid);
        $frag = 'blocks';
        header("Refresh: 3; url=friends.php?id=$userid#$frag");
        mysqli_affected_rows($mysqli) == 1 ? stderr('Success', 'Block was deleted successfully.') : stderr('oopss', 'No Block found with ID !! .');
    } else {
        stderr('Error', 'Unknown type.');
    }
    header('Location: friends.php');
    die();
}

$res = sql_query('SELECT * FROM users WHERE id=' . sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
$user = mysqli_fetch_assoc($res) or stderr($lang['friends_error'], $lang['friends_no_user']);
$HTMLOUT = '';
$i = 0;
$res = sql_query('SELECT f.userid AS id, u.username, u.class, u.avatar, u.offensive_avatar, u.anonymous, u.title, u.donor, u.warned, u.enabled, u.leechwarn, u.chatpost, u.pirate, u.king, u.last_access, u.perms FROM friends AS f LEFT JOIN users AS u ON f.userid=u.id WHERE friendid=' . sqlesc($CURUSER['id']) . " AND f.confirmed = 'no' AND NOT f.userid IN (SELECT blockid FROM blocks WHERE blockid=f.userid) ORDER BY username") or sqlerr(__FILE__, __LINE__);
$friendsp = '';
if (mysqli_num_rows($res) == 0) {
    $friendsp = "<em>{$lang['friends_pending_empty']}.</em>";
} else {
    while ($friendp = mysqli_fetch_assoc($res)) {
        $dt = $dt - 180;
        $online = ($friendp['last_access'] >= $dt && $friendp['perms'] < bt_options::PERMS_STEALTH ? ' <img src="' . $site_config['paths']['images_baseurl'] . 'online.png" alt="Online" class="tooltipper" title="Online">' : '<img src="' . $site_config['paths']['images_baseurl'] . 'offline.png" alt="Offline" class="tooltipper" title="Offline">');
        $title = !empty($friendp['title']) ? htmlsafechars($friendp['title']) : '';
        if (!$title) {
            $title = get_user_class_name((int) $friendp['class']);
        }
        $linktouser = format_username((int) $friendp['id']) . " [$title]<br>{$lang['friends_last_seen']} " . ($friendp['perms'] < bt_options::PERMS_STEALTH ? get_date((int) $friendp['last_access'], '') : 'Never');
        $confirm = "<br><span class='button is-small'><a href='{$site_config['paths']['baseurl']}/friends.php?id=$userid&amp;action=confirm&amp;type=friend&amp;targetid=" . (int) $friendp['id'] . "' class='has-text-black'>Confirm</a></span>";
        $block = " <span class='button is-small'><a href='{$site_config['paths']['baseurl']}/friends.php?action=add&amp;type=block&amp;targetid=" . (int) $friendp['id'] . "' class='has-text-black'>Block</a></span>";
        $avatar = get_avatar($friendp);
        $reject = " <span class='button is-small'><a href='{$site_config['paths']['baseurl']}/friends.php?id=$userid&amp;action=delpending&amp;type=friend&amp;targetid=" . (int) $friendp['id'] . "' class='has-text-black'>{$lang['friends_reject']}</a></span>";
        $friendsp .= "<div>{$avatar}<p>{$linktouser}<br><br>{$confirm}{$block}{$reject}</p></div><br>";
    }
}
$res = sql_query('SELECT f.friendid AS id, u.username, u.donor, u.class, u.warned, u.enabled, u.leechwarn, u.chatpost, u.pirate, u.king, u.last_access FROM friends AS f LEFT JOIN users AS u ON f.friendid=u.id WHERE userid=' . sqlesc($userid) . " AND f.confirmed='no' ORDER BY username") or sqlerr(__FILE__, __LINE__);
$friendreqs = '';
if (mysqli_num_rows($res) == 0) {
    $friendreqs = '<em>Your requests list is empty.</em>';
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
$res = sql_query('SELECT f.friendid AS id, u.username, u.class, u.avatar, u.offensive_avatar, u.anonymous, u.title, u.donor, u.warned, u.enabled, u.leechwarn, u.chatpost, u.pirate, u.king, u.last_access, u.uploaded, u.downloaded, u.country, u.perms FROM friends AS f LEFT JOIN users AS u ON f.friendid=u.id WHERE userid=' . sqlesc($userid) . " AND f.confirmed = 'yes' ORDER BY username") or sqlerr(__FILE__, __LINE__);
$friends = '';
if (mysqli_num_rows($res) == 0) {
    $friends = '<em>Your friends list is empty.</em>';
} else {
    while ($friend = mysqli_fetch_assoc($res)) {
        $dt = $dt - 180;
        $online = ($friend['last_access'] >= $dt && $friend['perms'] < bt_options::PERMS_STEALTH ? ' <img src="' . $site_config['paths']['images_baseurl'] . 'online.png" alt="Online" class="tooltipper" title="Online">' : '<img src="' . $site_config['paths']['images_baseurl'] . 'offline.png" alt="Offline" class="tooltipper" title="Offline">');
        $title = !empty($friend['title']) ? htmlsafechars($friend['title']) : '';
        if (!$title) {
            $title = get_user_class_name((int) $friend['class']);
        }
        $ratio = member_ratio((int) $friend['uploaded'], (int) $friend['downloaded']);
        $linktouser = format_username((int) $friend['id']) . " [$title] [$ratio]<br>{$lang['friends_last_seen']} " . ($friend['perms'] < bt_options::PERMS_STEALTH ? get_date((int) $friend['last_access'], '') : 'Never');
        $delete = "<span class='button is-small'><a href='{$site_config['paths']['baseurl']}/friends.php?id=$userid&amp;action=delete&amp;type=friend&amp;targetid=" . (int) $friend['id'] . "' class='has-text-black'>{$lang['friends_remove']}</a></span>";
        $pm_link = " <span class='button is-small'><a href='{$site_config['paths']['baseurl']}/messages.php?action=send_message&amp;receiver=" . (int) $friend['id'] . "' class='has-text-black'>{$lang['friends_pm']}</a></span>";
        $avatar = get_avatar($friend);
        $friends .= "<div>{$avatar}<p>{$linktouser} {$online}<br><br>{$delete}{$pm_link}</p></div><br>";
    }
}

$res = sql_query('SELECT b.blockid AS id, u.username, u.donor, u.class, u.warned, u.enabled, u.leechwarn, u.chatpost, u.pirate, u.king, u.last_access FROM blocks AS b LEFT JOIN users AS u ON b.blockid=u.id WHERE userid=' . sqlesc($userid) . ' ORDER BY username') or sqlerr(__FILE__, __LINE__);
$blocks = '';
if (mysqli_num_rows($res) == 0) {
    $blocks = "{$lang['friends_blocks_empty']}<em>.</em>";
} else {
    while ($block = mysqli_fetch_assoc($res)) {
        $blocks .= '<div>';
        $blocks .= "<span class='button is-small'><a href='{$site_config['paths']['baseurl']}/friends.php?id=$userid&amp;action=delete&amp;type=block&amp;targetid=" . (int) $block['id'] . "' class='has-text-black'>{$lang['friends_delete']}</a></span><br>";
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
        <h1 class='has-text-centered'>{$lang['friends_personal']} " . htmlsafechars($user['username']) . " $country</h1>
        <table class='table table-bordered table-striped top20 bottom20'>
            <thead>
                <tr>
                    <th class='w-50'>
                        <h2><a id='friends'>{$lang['friends_friends_list']}</a></h2>
                    </th>
                    <th>
                        <h2><a id='blocks'>{$lang['friends_blocks_list']}</a></h2>
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
                    <th class='w-50'><h2><a id='friendsp'>{$lang['friends_pending_list']}</a></h2></th>
                    <th><h2><a id='friendreqs'>{$lang['friends_await_list']}</a></h2></th>
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
                {$lang['friends_user_list']}
            </a>
        </div>";
echo stdhead("{$lang['friends_stdhead']} " . htmlsafechars($user['username'])) . wrapper($HTMLOUT) . stdfoot();
