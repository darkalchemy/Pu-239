<?php
/**
 |--------------------------------------------------------------------------|
 |   https://github.com/Bigjoos/                			    |
 |--------------------------------------------------------------------------|
 |   Licence Info: GPL			                                    |
 |--------------------------------------------------------------------------|
 |   Copyright (C) 2010 U-232 V4					    |
 |--------------------------------------------------------------------------|
 |   A bittorrent tracker source based on TBDev.net/tbsource/bytemonsoon.   |
 |--------------------------------------------------------------------------|
 |   Project Leaders: Mindless,putyn.					    |
 |--------------------------------------------------------------------------|
  _   _   _   _   _     _   _   _   _   _   _     _   _   _   _
 / \ / \ / \ / \ / \   / \ / \ / \ / \ / \ / \   / \ / \ / \ / \
( U | - | 2 | 3 | 2 )-( S | o | u | r | c | e )-( C | o | d | e )
 \_/ \_/ \_/ \_/ \_/   \_/ \_/ \_/ \_/ \_/ \_/   \_/ \_/ \_/ \_/
 */
require_once (dirname(__FILE__) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php');
require_once CLASS_DIR . 'page_verify.php';
require_once (INCL_DIR . 'user_functions.php');
require_once INCL_DIR . 'bbcode_functions.php';
require_once INCL_DIR . 'html_functions.php';
require_once (INCL_DIR . 'function_onlinetime.php');
require_once (CLASS_DIR . 'class_user_options.php');
require_once (CLASS_DIR . 'class_user_options_2.php');
dbconn(false);
loggedinorreturn();
$lang = array_merge(load_language('global') , load_language('userdetails'));
if (function_exists('parked')) parked();
$newpage = new page_verify();
$newpage->create('mdk1@@9');
$stdhead = array(
    /** include css **/
    'css' => array(
        'jquery-ui',
        'jquery.treeview'
    )
);
$stdfoot = array(
    /** include js **/
    'js' => array(
        'popup',
        'java_klappe',
        'flip_box',
        'jquery-ui-personalized-1.5.2.packed',
        'sprinkle',
        'jquery.treeview.pack',
        'flush_torrents'
    )
);
$id = (int)$_GET["id"];
if (!is_valid_id($id)) stderr($lang['userdetails_error'], "{$lang['userdetails_bad_id']}");
if (($user = $mc1->get_value('user' . $id)) === false) {
    $user_fields_ar_int = array(
        'id',
        'added',
        'last_login',
        'last_access',
        'curr_ann_last_check',
        'curr_ann_id',
        'stylesheet',
        'class',
        'override_class',
        'language',
        'av_w',
        'av_h',
        'country',
        'warned',
        'torrentsperpage',
        'topicsperpage',
        'postsperpage',
        'reputation',
        'dst_in_use',
        'auto_correct_dst',
        'chatpost',
        'smile_until',
        'vip_until',
        'freeslots',
        'free_switch',
        'invites',
        'invitedby',
        'uploadpos',
        'forumpost',
        'downloadpos',
        'immunity',
        'leechwarn',
        'last_browse',
        'sig_w',
        'sig_h',
        'forum_access',
        'hit_and_run_total',
        'donoruntil',
        'donated',
        'vipclass_before',
        'passhint',
        'avatarpos',
        'sendpmpos',
        'invitedate',
        'anonymous_until',
        'pirate',
        'king',
        'ssluse',
        'paranoia',
        'parked_until',
        'bjwins',
        'bjlosses',
        'irctotal',
        'last_access_numb',
        'onlinetime',
        'hits',
        'comments',
        'categorie_icon',
        'perms',
        'mood',
        'pms_per_page',
        'watched_user',
        'game_access',
        'reputation',
        'opt1',
        'opt2',
        'can_leech',
        'wait_time',
        'torrents_limit',
        'peers_limit',
        'torrent_pass_version'
    );
    $user_fields_ar_float = array(
        'time_offset',
        'total_donated'
    );
    $user_fields_ar_str = array(
        'username',
        'passhash',
        'secret',
        'torrent_pass',
        'email',
        'status',
        'editsecret',
        'privacy',
        'info',
        'acceptpms',
        'ip',
        'avatar',
        'title',
        'notifs',
        'enabled',
        'donor',
        'deletepms',
        'savepms',
        'show_shout',
        'show_staffshout',
        'shoutboxbg',
        'vip_added',
        'invite_rights',
        'anonymous',
        'disable_reason',
        'clear_new_tag_manually',
        'signatures',
        'signature',
        'highspeed',
        'hnrwarn',
        'parked',
        'hintanswer',
        'support',
        'supportfor',
        'invitees',
        'invite_on',
        'subscription_pm',
        'gender',
        'viewscloud',
        'tenpercent',
        'avatars',
        'offavatar',
        'hidecur',
        'signature_post',
        'forum_post',
        'avatar_rights',
        'offensive_avatar',
        'view_offensive_avatar',
        'google_talk',
        'msn',
        'aim',
        'yahoo',
        'website',
        'icq',
        'show_email',
        'gotgift',
        'hash1',
        'suspended',
        'warn_reason',
        'onirc',
        'birthday',
        'got_blocks',
        'pm_on_delete',
        'commentpm',
        'split',
        'browser',
        'got_moods',
        'show_pm_avatar',
        'watched_user_reason',
        'staff_notes',
        'where_is',
        'browse_icons'
    );
    $user_fields = implode(', ', array_merge($user_fields_ar_int, $user_fields_ar_float, $user_fields_ar_str));
    $r1 = sql_query("SELECT " . $user_fields . " FROM users WHERE id=" . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
    $user = mysqli_fetch_assoc($r1) or stderr($lang['userdetails_error'], "{$lang['userdetails_no_user']}");
    foreach ($user_fields_ar_int as $i) $user[$i] = (int)$user[$i];
    foreach ($user_fields_ar_float as $i) $user[$i] = (float)$user[$i];
    foreach ($user_fields_ar_str as $i) $user[$i] = $user[$i];
    $mc1->cache_value('user' . $id, $user, $INSTALLER09['expires']['user_cache']);
}
if ($user["status"] == "pending") stderr($lang['userdetails_error'], $lang['userdetails_pending']);
// user stats
$What_Cache = (XBT_TRACKER == true ? 'user_stats_xbt_' : 'user_stats_');
if (($user_stats = $mc1->get_value($What_Cache.$id)) === false) {
    $What_Expire = (XBT_TRACKER == true ? $INSTALLER09['expires']['user_stats_xbt'] : $INSTALLER09['expires']['user_stats']);
    $stats_fields_ar_int = array(
            'uploaded',
            'downloaded'
        );
        $stats_fields_ar_float = array(
            'seedbonus'
        );
        $stats_fields_ar_str = array(
            'modcomment',
            'bonuscomment'
        );
        $stats_fields = implode(', ', array_merge($stats_fields_ar_int, $stats_fields_ar_float, $stats_fields_ar_str));
    $sql_1 = sql_query('SELECT ' . $stats_fields . ' FROM users WHERE id= ' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
    $user_stats = mysqli_fetch_assoc($sql_1);
    foreach ($stats_fields_ar_int as $i) $user_stats[$i] = (int)$user_stats[$i];
    foreach ($stats_fields_ar_float as $i) $user_stats[$i] = (float)$user_stats[$i];
    foreach ($stats_fields_ar_str as $i) $user_stats[$i] = $user_stats[$i];
    $mc1->cache_value($What_Cache.$id, $user_stats, $What_Expire); // 5 mins
}
if (($user_status = $mc1->get_value('user_status_' . $id)) === false) {
    $sql_2 = sql_query('SELECT * FROM ustatus WHERE userid = ' . sqlesc($id));
    if (mysqli_num_rows($sql_2)) $user_status = mysqli_fetch_assoc($sql_2);
    else $user_status = array(
        'last_status' => '',
        'last_update' => 0,
        'archive' => ''
    );
    $mc1->add_value('user_status_' . $id, $user_status, $INSTALLER09['expires']['user_status']); // 30 days
    
}
//===  paranoid settings
if ($user['paranoia'] == 3 && $CURUSER['class'] < UC_STAFF && $CURUSER['id'] <> $id) {
    stderr($lang['userdetails_error'], '<span style="font-weight: bold; text-align: center;"><img src="pic/smilies/tinfoilhat.gif" alt="'.$lang['userdetails_tinfoil'].'" title="'.$lang['userdetails_tinfoil'].'" />
       '.$lang['userdetails_tinfoil2'].' <img src="pic/smilies/tinfoilhat.gif" alt="'.$lang['userdetails_tinfoil'].'" title="'.$lang['userdetails_tinfoil'].'" /></span>');
    die();
}
//=== delete H&R
if (isset($_GET['delete_hit_and_run']) && $CURUSER['class'] >= UC_STAFF) {
    $delete_me = isset($_GET['delete_hit_and_run']) ? intval($_GET['delete_hit_and_run']) : 0;
    if (!is_valid_id($delete_me)) stderr($lang['userdetails_error'], $lang['userdetails_bad_id']);
    if(XBT_TRACKER === false) {
    sql_query('UPDATE snatched SET hit_and_run = \'0\', mark_of_cain = \'no\' WHERE id = ' . sqlesc($delete_me)) or sqlerr(__FILE__, __LINE__);
    } else {
    sql_query('UPDATE xbt_files_users SET hit_and_run = \'0\', mark_of_cain = \'no\' WHERE fid = ' . sqlesc($delete_me)) or sqlerr(__FILE__, __LINE__);
    }
    if (@mysqli_affected_rows($GLOBALS["___mysqli_ston"]) === 0) {
        stderr($lang['userdetails_error'], $lang['userdetails_notdeleted']);
    }
    header('Location: ?id=' . $id . '&completed=1');
    die();
}
$r = sql_query("SELECT t.id, t.name, t.seeders, t.leechers, c.name AS cname, c.image FROM torrents t LEFT JOIN categories c ON t.category = c.id WHERE t.owner = " . sqlesc($id) . " ORDER BY t.name") or sqlerr(__FILE__, __LINE__);
if (mysqli_num_rows($r) > 0) {
    $torrents = "<table class='main' border='1' cellspacing='0' cellpadding='5'>\n" . "<tr><td class='colhead'>{$lang['userdetails_type']}</td><td class='colhead'>{$lang['userdetails_name']}</td><td class='colhead'>{$lang['userdetails_seeders']}</td><td class='colhead'>{$lang['userdetails_leechers']}</td></tr>\n";
    while ($a = mysqli_fetch_assoc($r)) {
        $cat = "<img src=\"{$INSTALLER09['pic_base_url']}/caticons/{$CURUSER['categorie_icon']}/" . htmlsafechars($a['image']) . "\" title=\"" . htmlsafechars($a['cname']) . "\" alt=\"" . htmlsafechars($a['cname']) . "\" />";
        $torrents.= "<tr><td style='padding: 0px'>$cat</td><td><a href='details.php?id=" . (int)$a['id'] . "&amp;hit=1'><b>" . htmlsafechars($a["name"]) . "</b></a></td>" . "<td align='right'>" . (int)$a['seeders'] . "</td><td align='right'>" . (int)$a['leechers'] . "</td></tr>\n";
    }
    $torrents.= "</table>";
}
if ($user['ip'] && ($CURUSER['class'] >= UC_STAFF || $user['id'] == $CURUSER['id'])) {
    $dom = @gethostbyaddr($user['ip']);
    $addr = ($dom == $user['ip'] || @gethostbyname($dom) != $user['ip']) ? $user['ip'] : $user['ip'] . ' (' . $dom . ')';
}
if ($user['added'] == 0 OR $user['perms'] & bt_options::PERMS_STEALTH) $joindate = "{$lang['userdetails_na']}";
else $joindate = get_date($user['added'], '');
$lastseen = $user["last_access"];
if ($lastseen == 0 OR $user['perms'] & bt_options::PERMS_STEALTH) $lastseen = "{$lang['userdetails_never']}";
else {
    $lastseen = get_date($user['last_access'], '', 0, 1);
}
/** #$^$&%$&@ invincible! NO IP LOGGING..pdq **/
if ((($user['class'] == UC_MAX OR $user['id'] == $CURUSER['id']) || ($user['class'] < UC_MAX) && $CURUSER['class'] == UC_MAX) && isset($_GET['invincible'])) {
    require_once (INCL_DIR . 'invincible.php');
    if ($_GET['invincible'] == 'yes') $HTMLOUT.= invincible($id);
    elseif ($_GET['invincible'] == 'remove_bypass') $HTMLOUT.= invincible($id, true, false);
    else $HTMLOUT.= invincible($id, false);
} // End

/** #$^$&%$&@ stealth!..pdq **/
if ((($user['class'] >= UC_STAFF OR $user['id'] == $CURUSER['id']) || ($user['class'] < UC_STAFF) && $CURUSER['class'] >= UC_STAFF) && isset($_GET['stealth'])) {
    require_once (INCL_DIR . 'stealth.php');
    if ($_GET['stealth'] == 'yes') $HTMLOUT.= stealth($id);
    elseif ($_GET['stealth'] == 'no') $HTMLOUT.= stealth($id, false);
} // End
//==country by pdq
function countries()
{
    global $mc1, $INSTALLER09;
    if (($ret = $mc1->get_value('countries::arr')) === false) {
        $res = sql_query("SELECT id, name, flagpic FROM countries ORDER BY name ASC") or sqlerr(__FILE__, __LINE__);
        while ($row = mysqli_fetch_assoc($res)) $ret[] = $row;
        $mc1->cache_value('countries::arr', $ret, $INSTALLER09['expires']['user_flag']);
    }
    return $ret;
}
$country = '';
$countries = countries();
foreach ($countries as $cntry) if ($cntry['id'] == $user['country']) {
    $country = "<img src=\"{$INSTALLER09['pic_base_url']}flag/{$cntry['flagpic']}\" alt=\"" . htmlsafechars($cntry['name']) . "\" style='margin-left: 8pt' />";
    break;
}
if (XBT_TRACKER == true) {
    $res = sql_query("SELECT x.fid, x.uploaded, x.downloaded, x.active, x.left, t.added, t.name as torrentname, t.size, t.category, t.seeders, t.leechers, c.name as catname, c.image FROM xbt_files_users x LEFT JOIN torrents t ON x.fid = t.id LEFT JOIN categories c ON t.category = c.id WHERE x.uid=" . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
    while ($arr = mysqli_fetch_assoc($res)) {
        if ($arr['left'] == '0') $seeding[] = $arr;
        else $leeching[] = $arr;
    }
} else {
    $res = sql_query("SELECT p.torrent, p.uploaded, p.downloaded, p.seeder, t.added, t.name as torrentname, t.size, t.category, t.seeders, t.leechers, c.name as catname, c.image FROM peers p LEFT JOIN torrents t ON p.torrent = t.id LEFT JOIN categories c ON t.category = c.id WHERE p.userid=" . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
    while ($arr = mysqli_fetch_assoc($res)) {
        if ($arr['seeder'] == 'yes') $seeding[] = $arr;
        else $leeching[] = $arr;
    }
}
//==userhits update by pdq
if (!(isset($_GET["hit"])) && $CURUSER["id"] <> $user["id"]) {
    $res = sql_query("SELECT added FROM userhits WHERE userid =" . sqlesc($CURUSER['id']) . " AND hitid = " . sqlesc($id) . " LIMIT 1") or sqlerr(__FILE__, __LINE__);
    $row = mysqli_fetch_row($res);
    if (!($row[0] > TIME_NOW - 3600)) {
        $hitnumber = $user['hits'] + 1;
        sql_query("UPDATE users SET hits = hits + 1 WHERE id = " . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
        // do update hits userdetails cache
        $update['user_hits'] = ($user['hits'] + 1);
        $mc1->begin_transaction('MyUser_' . $id);
        $mc1->update_row(false, array(
            'hits' => $update['user_hits']
        ));
        $mc1->commit_transaction($INSTALLER09['expires']['curuser']);
        $mc1->begin_transaction('user' . $id);
        $mc1->update_row(false, array(
            'hits' => $update['user_hits']
        ));
        $mc1->commit_transaction($INSTALLER09['expires']['user_cache']);
        sql_query("INSERT INTO userhits (userid, hitid, number, added) VALUES(" . sqlesc($CURUSER['id']) . ", " . sqlesc($id) . ", " . sqlesc($hitnumber) . ", " . sqlesc(TIME_NOW) . ")") or sqlerr(__FILE__, __LINE__);
    }
}
$HTMLOUT = $perms = $stealth = $suspended = $watched_user = $h1_thingie = '';
if (($user['opt1'] & user_options::ANONYMOUS) && ($CURUSER['class'] < UC_STAFF && $user["id"] != $CURUSER["id"])) {
    $HTMLOUT.= "<table width='100%' border='1' cellspacing='0' cellpadding='5' class='main'>";
    $HTMLOUT.= "<tr><td colspan='2' align='center'>{$lang['userdetails_anonymous']}</td></tr>";
    if ($user["avatar"]) $HTMLOUT.= "<tr><td colspan='2' align='center'><img src='" . htmlsafechars($user["avatar"]) . "'></td></tr>\n";
    if ($user["info"]) $HTMLOUT.= "<tr valign='top'><td align='left' colspan='2' class=text bgcolor='#F4F4F0'>'" . format_comment($user["info"]) . "'</td></tr>\n";
    $HTMLOUT.= "<tr><td colspan='2' align='center'><form method='get' action='{$INSTALLER09['baseurl']}/pm_system.php?action=send_message'><input type='hidden' name='receiver' value='" . (int)$user["id"] . "' /><input type='submit' value='{$lang['userdetails_sendmess']}' style='height: 23px' /></form>";
    if ($CURUSER['class'] < UC_STAFF && $user["id"] != $CURUSER["id"]) {
        $HTMLOUT.= end_main_frame();
        echo stdhead($lang['userdetails_anonymoususer']) . $HTMLOUT . stdfoot();
        die;
    }
    $HTMLOUT.= "</td></tr></table><br />";
}
$h1_thingie = ((isset($_GET['sn']) || isset($_GET['wu'])) ? '<h1>'.$lang['userdetails_updated'].'</h1>' : '');
if ($CURUSER["id"] <> $user["id"] && $CURUSER['class'] >= UC_STAFF) $suspended.= ($user['suspended'] == 'yes' ? '&nbsp;&nbsp;<img src="' . $INSTALLER09['pic_base_url'] . 'smilies/excl.gif" alt="'.$lang['userdetails_suspended'].'" title="'.$lang['userdetails_suspended'].'" />&nbsp;<b>'.$lang['userdetails_usersuspended'].'</b>&nbsp;<img src="' . $INSTALLER09['pic_base_url'] . 'smilies/excl.gif" alt="'.$lang['userdetails_suspended'].'" title="'.$lang['userdetails_suspended'].'" />' : '');
if ($CURUSER["id"] <> $user["id"] && $CURUSER['class'] >= UC_STAFF) $watched_user.= ($user['watched_user'] == 0 ? '' : '&nbsp;&nbsp;<img src="' . $INSTALLER09['pic_base_url'] . 'smilies/excl.gif" align="middle" alt="'.$lang['userdetails_watched'].'" title="'.$lang['userdetails_watched'].'" /> <b>'.$lang['userdetails_watchlist1'].' <a href="staffpanel.php?tool=watched_users" >'.$lang['userdetails_watchlist2'].'</a></b> <img src="' . $INSTALLER09['pic_base_url'] . 'smilies/excl.gif" align="middle" alt="'.$lang['userdetails_watched'].'" title="'.$lang['userdetails_watched'].'" />');
$perms.= ($CURUSER['class'] >= UC_STAFF ? (($user['perms'] & bt_options::PERMS_NO_IP) ? '&nbsp;&nbsp;<img src="' . $INSTALLER09['pic_base_url'] . 'smilies/super.gif" alt="'.$lang['userdetails_invincible'].'"  title="'.$lang['userdetails_invincible'].'" />' : '') : '');
$stealth.= ($CURUSER['class'] >= UC_STAFF ? (($user['perms'] & bt_options::PERMS_STEALTH) ? '&nbsp;&nbsp;<img src="' . $INSTALLER09['pic_base_url'] . 'smilies/ninja.gif" alt="'.$lang['userdetails_stelth'].'"  title="'.$lang['userdetails_stelth'].'" />' : '') : '');
$enabled = $user["enabled"] == 'yes';
$HTMLOUT.= "<table class='main' border='0' cellspacing='0' cellpadding='0'>" . "<tr><td class='embedded'><h1 style='margin:0px'>" . format_username($user, true) . "</h1>$country$perms$stealth$watched_user$suspended$h1_thingie</td></tr></table>\n";
if ($user['opt1'] & user_options::PARKED) $HTMLOUT.= "<p><b>{$lang['userdetails_parked']}</b></p>\n";
if (!$enabled) $HTMLOUT.= "<p><b>{$lang['userdetails_disabled']}</b></p>\n";
elseif ($CURUSER["id"] <> $user["id"]) {
    if (($friends = $mc1->get_value('Friends_' . $id)) === false) {
        $r3 = sql_query("SELECT id FROM friends WHERE userid=" . sqlesc($CURUSER['id']) . " AND friendid=" . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
        $friends = mysqli_num_rows($r3);
        $mc1->cache_value('Friends_' . $id, $friends, $INSTALLER09['expires']['user_friends']);
    }
    if (($blocks = $mc1->get_value('Blocks_' . $id)) === false) {
        $r4 = sql_query("SELECT id FROM blocks WHERE userid=" . sqlesc($CURUSER['id']) . " AND blockid=" . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
        $blocks = mysqli_num_rows($r4);
        $mc1->cache_value('Blocks_' . $id, $blocks, $INSTALLER09['expires']['user_blocks']);
    }
    if ($friends > 0) $HTMLOUT.= "<p>(<a href='friends.php?action=delete&amp;type=friend&amp;targetid=$id'>{$lang['userdetails_remove_friends']}</a>)</p>\n";
    else $HTMLOUT.= "<p>(<a href='friends.php?action=add&amp;type=friend&amp;targetid=$id'>{$lang['userdetails_add_friends']}</a>)</p>";
    if ($blocks > 0) $HTMLOUT.= "<p>(<a href='friends.php?action=delete&amp;type=block&amp;targetid=$id'>{$lang['userdetails_remove_blocks']}</a>)</p>\n";
    else $HTMLOUT.= "<p>(<a href='friends.php?action=add&amp;type=block&amp;targetid=$id'>{$lang['userdetails_add_blocks']}</a>)</p>\n";
}
//== 09 Shitlist by Sir_Snuggles
if ($CURUSER['class'] >= UC_STAFF) {
    $shitty = '';
    if (($shit_list = $mc1->get_value('shit_list_' . $id)) === false) {
        $check_if_theyre_shitty = sql_query("SELECT suspect FROM shit_list WHERE userid=" . sqlesc($CURUSER['id']) . " AND suspect=" . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
        list($shit_list) = mysqli_fetch_row($check_if_theyre_shitty);
        $mc1->cache_value('shit_list_' . $id, $shit_list, $INSTALLER09['expires']['shit_list']);
    }
    if ($shit_list > 0) {
        $shitty = "<img src='pic/smilies/shit.gif' alt='Shit' title='Shit' />";
        $HTMLOUT.= "<br /><b>" . $shitty . "&nbsp;{$lang['userdetails_shit1']} <a class='altlink' href='staffpanel.php?tool=shit_list&amp;action=shit_list'>{$lang['userdetails_here']}</a> {$lang['userdetails_shit2']}&nbsp;" . $shitty . "</b>";
    } elseif ($CURUSER["id"] <> $user["id"]) {
        $HTMLOUT.= "<br /><a class='altlink' href='staffpanel.php?tool=shit_list&amp;action=shit_list&amp;action2=new&amp;shit_list_id=" . $id . "&amp;return_to=userdetails.php?id=" . $id . "'><b>{$lang['userdetails_shit3']}</b></a>";
    }
}
// ===donor count down
if ($user["donor"] && $CURUSER["id"] == $user["id"] || $CURUSER["class"] == UC_SYSOP) {
    $donoruntil = htmlsafechars($user['donoruntil']);
    if ($donoruntil == '0') $HTMLOUT.= "";
    else {
        $HTMLOUT.= "<br /><b>{$lang['userdetails_donatedtill']} - " . get_date($user['donoruntil'], 'DATE') . "";
        $HTMLOUT.= " [ " . mkprettytime($donoruntil - TIME_NOW) . " ] {$lang['userdetails_togo']}...</b><font size=\"-2\"> {$lang['userdetails_renew']} <a class='altlink' href='{$INSTALLER09['baseurl']}/donate.php'>{$lang['userdetails_here']}</a>.</font><br /><br />\n";
    }
}
if ($CURUSER['id'] == $user['id']) $HTMLOUT.= "<h1><a href='{$INSTALLER09['baseurl']}/usercp.php?action=default'>{$lang['userdetails_editself']}</a></h1>
              <h1><a href='{$INSTALLER09['baseurl']}/view_announce_history.php'>{$lang['userdetails_announcements']}</a></h1>";
if ($CURUSER['id'] != $user['id']) $HTMLOUT.= "<h1><a href='{$INSTALLER09['baseurl']}/sharemarks.php?id=$id'>{$lang['userdetails_sharemarks']}</a></h1>\n";
//==invincible no iplogging and ban bypass by pdq
$invincible = $mc1->get_value('display_' . $CURUSER['id']);
if ($invincible) $HTMLOUT.= '<h1>' . htmlsafechars($user['username']) . ' '.$lang['userdetails_is'].' ' . $invincible . ' '.$lang['userdetails_invincible'].'</h1>';
//== links to make invincible method 1(PERMS_NO_IP/ no log ip) and 2(PERMS_BYPASS_BAN/cannot be banned)
$HTMLOUT.= ($CURUSER['class'] === UC_MAX ? (($user['perms'] & bt_options::PERMS_NO_IP) ? ' [<a title=' . "\n" . '"'.$lang['userdetails_invincible_def1'].' ' . "\n" . ''.$lang['userdetails_invincible_def2'].'" href="userdetails.php?id=' . $id . '&amp;invincible=no">' . "\n" . ''.$lang['userdetails_invincible_remove'].'</a>]' . (($user['perms'] & bt_options::PERMS_BYPASS_BAN) ? ' - ' . "\n" . ' [<a title="'.$lang['userdetails_invincible_def3'].'' . "\n" . ' '.$lang['userdetails_invincible_def4'].'" href="userdetails.php?id=' . $id . '&amp;' . "\n" . 'invincible=remove_bypass">'.$lang['userdetails_remove_bypass'].'</a>]' : ' - [<a title="'.$lang['userdetails_invincible_def5'].' ' . "\n" . $lang['userdetails_invincible_def6'] . "\n" . ' '.$lang['userdetails_invincible_def7'].' ' . "\n" . ''.$lang['userdetails_invincible_def8'].'" href="userdetails.php?id=' . $id . '&amp;invincible=yes">' . "\n" . ''.$lang['userdetails_add_bypass'].'</a>]') : '[<a title="'.$lang['userdetails_invincible_def9'].'' . "
               \n" . ' '.$lang['userdetails_invincible_def0'].'" ' . "\n" . 'href="userdetails.php?id=' . $id . '&amp;invincible=yes">'.$lang['userdetails_make_invincible'].'</a>]') : '');
//==Stealth mode by pdq
$stealth = $mc1->get_value('display_stealth' . $CURUSER['id']);
if ($stealth) $HTMLOUT.= '<h1>' . htmlsafechars($user['username']) . '&nbsp;' . $stealth . ' '.$lang['userdetails_in_stelth'].'</h1>';
//== links to make stealth method (PERMS_STEALTH)
$HTMLOUT.= ($CURUSER['class'] >= UC_STAFF ? (($user['perms'] & bt_options::PERMS_STEALTH) ? '[<a title=' . "\n" . '"'.$lang['userdetails_stelth_def1'].' ' . "\n" . ' '.$lang['userdetails_stelth_def2'].'" href="userdetails.php?id=' . $id . '&amp;stealth=no">' . "\n" . ''.$lang['userdetails_stelth_disable'].'</a>]' : ' - [<a title="'.$lang['userdetails_stelth_def1'].'' . "
               \n" . ' '.$lang['userdetails_stelth_def2'].'" ' . "\n" . 'href="userdetails.php?id=' . $id . '&amp;stealth=yes">'.$lang['userdetails_stelth_enable'].'</a>]') : '');
$HTMLOUT.= begin_main_frame();
$HTMLOUT.= "<div id='tabvanilla' class='widget'>";
$HTMLOUT.= "<ul class='tabnav'>
       <li><a href='#torrents'>{$lang['userdetails_torrents']}</a></li>
       <li><a href='#general'>{$lang['userdetails_general']}</a></li>
       <li><a href='#activity'>{$lang['userdetails_activity']}</a></li>
       <li><a href='#comments'>{$lang['userdetails_usercomments']}</a></li>";
if ($CURUSER['class'] >= UC_STAFF && $user["class"] < $CURUSER['class']) {
    $HTMLOUT.= '<li><a href="#edit">'.$lang['userdetails_edit_user'].'</a></li>';
}
$HTMLOUT.= '  </ul><br />';
$HTMLOUT.= "<div class='tabdiv'><div id='torrents'>";
$HTMLOUT.= "<table align='center' width='100%' border='1' cellspacing='0' cellpadding='5' bgcolor='transparent'>\n";
if (curuser::$blocks['userdetails_page'] & block_userdetails::FLUSH && $BLOCKS['userdetails_flush_on']) {
    require_once (BLOCK_DIR . 'userdetails/flush.php');
}
if (curuser::$blocks['userdetails_page'] & block_userdetails::TRAFFIC && $BLOCKS['userdetails_traffic_on']) {
    require_once (BLOCK_DIR . 'userdetails/traffic.php');
}
if (curuser::$blocks['userdetails_page'] & block_userdetails::SHARE_RATIO && $BLOCKS['userdetails_share_ratio_on']) {
    require_once (BLOCK_DIR . 'userdetails/shareratio.php');
}
if (curuser::$blocks['userdetails_page'] & block_userdetails::SEEDTIME_RATIO && $BLOCKS['userdetails_seedtime_ratio_on']) {
    require_once (BLOCK_DIR . 'userdetails/seedtimeratio.php');
}
if (curuser::$blocks['userdetails_page'] & block_userdetails::TORRENTS_BLOCK && $BLOCKS['userdetails_torrents_block_on']) {
    require_once (BLOCK_DIR . 'userdetails/torrents_block.php');
}
if (curuser::$blocks['userdetails_page'] & block_userdetails::COMPLETED && $BLOCKS['userdetails_completed_on']/* && XBT_TRACKER == false*/) {
    require_once (BLOCK_DIR . 'userdetails/completed.php');
}
if (curuser::$blocks['userdetails_page'] & block_userdetails::SNATCHED_STAFF && $BLOCKS['userdetails_snatched_staff_on']/* && XBT_TRACKER == false*/) {
    require_once (BLOCK_DIR . 'userdetails/snatched_staff.php');
}
if (curuser::$blocks['userdetails_page'] & block_userdetails::CONNECTABLE_PORT && $BLOCKS['userdetails_connectable_port_on']) {
    require_once (BLOCK_DIR . 'userdetails/connectable.php');
}
$HTMLOUT.= "</table></div>";
$HTMLOUT.= "<div id='general'> ";
$HTMLOUT.= "<table align='center' width='100%' border='1' cellspacing='0' cellpadding='5'>\n";
// === make sure prople can't see their own naughty history by snuggles
if (($CURUSER['id'] !== $user['id']) && ($CURUSER['class'] >= UC_STAFF)) {
    //=== watched user stuff
    $the_flip_box = '[ <a name="watched_user"></a><a class="altlink" href="#watched_user" onclick="javascript:flipBox(\'3\')" title="'.$lang['userdetails_flip1'].'">' . ($user['watched_user'] > 0 ? ''.$lang['userdetails_flip2'].' ' : ''.$lang['userdetails_flip3'].' ') . '<img onclick="javascript:flipBox(\'3\')" src="pic/panel_on.gif" name="b_3" style="vertical-align:middle;"   width="8" height="8" alt="'.$lang['userdetails_flip1'].'" title="'.$lang['userdetails_flip1'].'" /></a> ]';
    $HTMLOUT.= '<tr><td class="rowhead">'.$lang['userdetails_watched'].'</td>
                            <td align="left">' . ($user['watched_user'] > 0 ? ''.$lang['userdetails_watched_since'].'  ' . get_date($user['watched_user'], '') . ' ' : ' '.$lang['userdetails_not_watched'].' ') . $the_flip_box . '
                            <div align="left" id="box_3" style="display:none">
                            <form method="post" action="member_input.php" name="notes_for_staff">
                            <input name="id" type="hidden" value="' . $id . '" />
                            <input type="hidden" value="watched_user" name="action" />
                            '.$lang['userdetails_add_watch'].'                  
                            <input type="radio" value="yes" name="add_to_watched_users"' . ($user['watched_user'] > 0 ? ' checked="checked"' : '') . ' /> '.$lang['userdetails_yes1'].'
                            <input type="radio" value="no" name="add_to_watched_users"' . ($user['watched_user'] == 0 ? ' checked="checked"' : '') . ' /> '.$lang['userdetails_no1'].' <br />
                            <span id="desc_text" style="color:red;font-size: xx-small;">* '.$lang['userdetails_watch_change1'].'<br />
                            '.$lang['userdetails_watch_change2'].'</span><br />
                            <textarea id="watched_reason" cols="50" rows="6" name="watched_reason">' . htmlsafechars($user['watched_user_reason']) . '</textarea><br />
                            <input id="watched_user_button" type="submit" value="'.$lang['userdetails_submit'].'" class="btn" name="watched_user_button" />
                            </form></div> </td></tr>';
    //=== staff Notes
    $the_flip_box_4 = '[ <a name="staff_notes"></a><a class="altlink" href="#staff_notes" onclick="javascript:flipBox(\'4\')" name="b_4" title="'.$lang['userdetails_open_staff'].'">view <img onclick="javascript:flipBox(\'4\')" src="pic/panel_on.gif" name="b_4" style="vertical-align:middle;" width="8" height="8" alt="'.$lang['userdetails_open_staff'].'" title="'.$lang['userdetails_open_staff'].'" /></a> ]';
    $HTMLOUT.= '<tr><td class="rowhead">'.$lang['userdetails_staffnotes'].'</td><td align="left">           
                            <a class="altlink" href="#staff_notes" onclick="javascript:flipBox(\'6\')" name="b_6" title="'.$lang['userdetails_aev_staffnote'].'">' . ($user['staff_notes'] !== '' ? ''.$lang['userdetails_vae'].' ' : ''.$lang['userdetails_add'].' ') . '<img onclick="javascript:flipBox(\'6\')" src="pic/panel_on.gif" name="b_6" style="vertical-align:middle;" width="8" height="8" alt="'.$lang['userdetails_aev_staffnote'].'" title="'.$lang['userdetails_aev_staffnote'].'" /></a>
                            <div align="left" id="box_6" style="display:none">
                            <form method="post" action="member_input.php" name="notes_for_staff">
                            <input name="id" type="hidden" value="' . (int)$user['id'] . '" />
                            <input type="hidden" value="staff_notes" name="action" id="action" />
                            <textarea id="new_staff_note" cols="50" rows="6" name="new_staff_note">' . htmlsafechars($user['staff_notes']) . '</textarea>
                            <br /><input id="staff_notes_button" type="submit" value="'.$lang['userdetails_submit'].'" class="btn" name="staff_notes_button"/>
                            </form>
                            </div> </td></tr>';
    //=== system comments
    $the_flip_box_7 = '[ <a name="system_comments"></a><a class="altlink" href="#system_comments" onclick="javascript:flipBox(\'7\')"  name="b_7" title="'.$lang['userdetails_open_system'].'">view <img onclick="javascript:flipBox(\'7\')" src="pic/panel_on.gif" name="b_7" style="vertical-align:middle;" width="8" height="8" alt="'.$lang['userdetails_open_system'].'" title="'.$lang['userdetails_open_system'].'" /></a> ]';
    if (!empty($user_stats['modcomment'])) $HTMLOUT.= "<tr><td class='rowhead'>{$lang['userdetails_system']}</td><td align='left'>" . ($user_stats['modcomment'] != '' ? $the_flip_box_7 . '<div align="left" id="box_7" style="display:none"><hr />' . format_comment($user_stats['modcomment']) . '</div>' : '') . "</td></tr>\n";
}
//==Begin blocks
//if (curuser::$blocks['userdetails_page'] & block_userdetails::SHOWFRIENDS && $BLOCKS['userdetails_showfriends_on']){
require_once (BLOCK_DIR . 'userdetails/showfriends.php');
//}
if (curuser::$blocks['userdetails_page'] & block_userdetails::LOGIN_LINK && $BLOCKS['userdetails_login_link_on']) {
    require_once (BLOCK_DIR . 'userdetails/loginlink.php');
}
if (curuser::$blocks['userdetails_page'] & block_userdetails::JOINED && $BLOCKS['userdetails_joined_on']) {
    require_once (BLOCK_DIR . 'userdetails/joined.php');
}
if (curuser::$blocks['userdetails_page'] & block_userdetails::ONLINETIME && $BLOCKS['userdetails_online_time_on']) {
    require_once (BLOCK_DIR . 'userdetails/onlinetime.php');
}
if (curuser::$blocks['userdetails_page'] & block_userdetails::BROWSER && $BLOCKS['userdetails_browser_on']) {
    require_once (BLOCK_DIR . 'userdetails/browser.php');
}
if (curuser::$blocks['userdetails_page'] & block_userdetails::BIRTHDAY && $BLOCKS['userdetails_birthday_on']) {
    require_once (BLOCK_DIR . 'userdetails/birthday.php');
}
if (curuser::$blocks['userdetails_page'] & block_userdetails::CONTACT_INFO && $BLOCKS['userdetails_contact_info_on']) {
    require_once (BLOCK_DIR . 'userdetails/contactinfo.php');
}
if (curuser::$blocks['userdetails_page'] & block_userdetails::IPHISTORY && $BLOCKS['userdetails_iphistory_on']) {
    require_once (BLOCK_DIR . 'userdetails/iphistory.php');
}
if (curuser::$blocks['userdetails_page'] & block_userdetails::AVATAR && $BLOCKS['userdetails_avatar_on']) {
    require_once (BLOCK_DIR . 'userdetails/avatar.php');
}
if (curuser::$blocks['userdetails_page'] & block_userdetails::USERCLASS && $BLOCKS['userdetails_userclass_on']) {
    require_once (BLOCK_DIR . 'userdetails/userclass.php');
}
if (curuser::$blocks['userdetails_page'] & block_userdetails::GENDER && $BLOCKS['userdetails_gender_on']) {
    require_once (BLOCK_DIR . 'userdetails/gender.php');
}
if (curuser::$blocks['userdetails_page'] & block_userdetails::USERINFO && $BLOCKS['userdetails_userinfo_on']) {
    require_once (BLOCK_DIR . 'userdetails/userinfo.php');
}
if (curuser::$blocks['userdetails_page'] & block_userdetails::REPORT_USER && $BLOCKS['userdetails_report_user_on']) {
    require_once (BLOCK_DIR . 'userdetails/report.php');
}
if (curuser::$blocks['userdetails_page'] & block_userdetails::USERSTATUS && $BLOCKS['userdetails_user_status_on']) {
    require_once (BLOCK_DIR . 'userdetails/userstatus.php');
}
if (curuser::$blocks['userdetails_page'] & block_userdetails::SHOWPM && $BLOCKS['userdetails_showpm_on']) {
    require_once (BLOCK_DIR . 'userdetails/showpm.php');
}
$HTMLOUT.= "</table></div>";
$HTMLOUT.= "<div id='activity'>";
$HTMLOUT.= "<table align='center' width='100%' border='1' cellspacing='0' cellpadding='5'>\n";
//==where is user now
if (!empty($user['where_is'])) $HTMLOUT.= "<tr><td class='rowhead' width='1%'>{$lang['userdetails_location']}</td><td align='left' width='99%'>" . format_urls($user['where_is']) . "</td></tr>\n";
//==
$moodname = (isset($mood['name'][$user['mood']]) ? htmlsafechars($mood['name'][$user['mood']]) : $lang['userdetails_neutral']);
$moodpic = (isset($mood['image'][$user['mood']]) ? htmlsafechars($mood['image'][$user['mood']]) : 'noexpression.gif');
$HTMLOUT.= '<tr><td class="rowhead">'.$lang['userdetails_currentmood'].'</td><td align="left"><span class="tool">
       <a href="javascript:;" onclick="PopUp(\'usermood.php\',\''.$lang['userdetails_mood'].'\',530,500,1,1);">
       <img src="' . $INSTALLER09['pic_base_url'] . 'smilies/' . $moodpic . '" alt="' . $moodname . '" border="0" />
       <span class="tip">' . htmlsafechars($user['username']) . ' ' . $moodname . ' !</span></a></span></td></tr>';
if (curuser::$blocks['userdetails_page'] & block_userdetails::SEEDBONUS && $BLOCKS['userdetails_seedbonus_on']) {
    require_once (BLOCK_DIR . 'userdetails/seedbonus.php');
}
if (curuser::$blocks['userdetails_page'] & block_userdetails::IRC_STATS && $BLOCKS['userdetails_irc_stats_on']) {
    require_once (BLOCK_DIR . 'userdetails/irc.php');
}
if (curuser::$blocks['userdetails_page'] & block_userdetails::REPUTATION && $BLOCKS['userdetails_reputation_on']) {
    require_once (BLOCK_DIR . 'userdetails/reputation.php');
}
if (curuser::$blocks['userdetails_page'] & block_userdetails::PROFILE_HITS && $BLOCKS['userdetails_profile_hits_on']) {
    require_once (BLOCK_DIR . 'userdetails/userhits.php');
}
if (curuser::$blocks['userdetails_page'] & block_userdetails::FREESTUFFS && $BLOCKS['userdetails_freestuffs_on'] && XBT_TRACKER == false) {
    require_once (BLOCK_DIR . 'userdetails/freestuffs.php');
}
if (curuser::$blocks['userdetails_page'] & block_userdetails::COMMENTS && $BLOCKS['userdetails_comments_on']) {
    require_once (BLOCK_DIR . 'userdetails/comments.php');
}
if (curuser::$blocks['userdetails_page'] & block_userdetails::FORUMPOSTS && $BLOCKS['userdetails_forumposts_on']) {
    require_once (BLOCK_DIR . 'userdetails/forumposts.php');
}
if (curuser::$blocks['userdetails_page'] & block_userdetails::INVITEDBY && $BLOCKS['userdetails_invitedby_on']) {
    require_once (BLOCK_DIR . 'userdetails/invitedby.php');
}
$HTMLOUT.= "</table></div>";
$HTMLOUT.= "<div id='comments'>";
if (curuser::$blocks['userdetails_page'] & block_userdetails::USERCOMMENTS && $BLOCKS['userdetails_user_comments_on']) {
    require_once (BLOCK_DIR . 'userdetails/usercomments.php');
}
$HTMLOUT.= "</div>";
$HTMLOUT.= "<div id='edit'>";
//==end blocks

$HTMLOUT.= "<script type='text/javascript'>
       /*<![CDATA[*/
       function togglepic(bu, picid, formid){
              var pic = document.getElementById(picid);
              var form = document.getElementById(formid);
           
              if(pic.src == bu + '/pic/plus.gif')   {
                    pic.src = bu + '/pic/minus.gif';
                    form.value = 'minus';
              }else{
                    pic.src = bu + '/pic/plus.gif';
                    form.value = 'plus';
              }
       }
       /*]]>*/
       </script>";
if ($CURUSER['class'] >= UC_STAFF && $user["class"] < $CURUSER['class']) {
    //$HTMLOUT .= begin_frame("Edit User", true);
    $HTMLOUT.= "<form method='post' action='staffpanel.php?tool=modtask'>\n";
    require_once CLASS_DIR . 'validator.php';
    $HTMLOUT.= validatorForm('ModTask_' . $user['id']);
    $postkey = PostKey(array(
        $user['id'],
        $CURUSER['id']
    ));
    $HTMLOUT.= "<input type='hidden' name='action' value='edituser' />\n";
    $HTMLOUT.= "<input type='hidden' name='userid' value='$id' />\n";
    $HTMLOUT.= "<input type='hidden' name='postkey' value='$postkey' />\n";
    $HTMLOUT.= "<input type='hidden' name='returnto' value='userdetails.php?id=$id' />\n";
    $HTMLOUT.= "
         <table class='main' border='1' cellspacing='0' cellpadding='5'>\n";
    $HTMLOUT.= "<tr><td class='rowhead'>{$lang['userdetails_title']}</td><td colspan='2' align='left'><input type='text' size='60' name='title' value='" . htmlsafechars($user['title']) . "' /></td></tr>\n";
    $avatar = htmlsafechars($user["avatar"]);
    $HTMLOUT.= "<tr><td class='rowhead'>{$lang['userdetails_avatar_url']}</td><td colspan='2' align='left'><input type='text' size='60' name='avatar' value='$avatar' /></td></tr>\n";
   
    $HTMLOUT.="<tr>
    <td class='rowhead'>{$lang['userdetails_signature_rights']}</td>
    <td colspan='2' align='left'><input name='signature_post' value='yes' type='radio'".($user['signature_post'] == "yes" ? "    checked='checked'" : "")." />{$lang['userdetails_yes']}
    <input name='signature_post' value='no' type='radio'".($user['signature_post'] == "no" ? " checked='checked'" : "")." />{$lang['userdetails_disable_signature']}</td></tr>
   <!--<tr><td class='rowhead'>{$lang['userdetails_view_signature']}</td>
   <td colspan='2' align='left'><input name='signatures' value='yes' type='radio'".($user['signatures'] == "yes" ? " checked='checked'" : "")." />{$lang['userdetails_yes']}
   <input name='signatures' value='no' type='radio'".($user['signatures'] == "no" ? " checked='checked'" : "")." /></td>
   </tr>-->
               <tr>
                      <td class='rowhead'>{$lang['userdetails_signature']}</td>
                      <td colspan='2' align='left'><textarea cols='60' rows='2' name='signature'>" . htmlsafechars($user['signature']) . "</textarea></td>
                </tr>
     
                <tr>
                      <td class='rowhead'>{$lang['userdetails_gtalk']}</td>
                      <td colspan='2' align='left'><input type='text' size='60' name='google_talk' value='" . htmlsafechars($user['google_talk']) . "' /></td>
                </tr>
                <tr>
                      <td class='rowhead'>{$lang['userdetails_msn']}</td>
                      <td colspan='2' align='left'><input type='text' size='60' name='msn' value='" . htmlsafechars($user['msn']) . "' /></td>
                </tr>
                <tr>
                      <td class='rowhead'>{$lang['userdetails_aim']}</td>
                      <td colspan='2' align='left'><input type='text' size='60' name='aim' value='" . htmlsafechars($user['aim']) . "' /></td>
                </tr>
                <tr>
                      <td class='rowhead'>{$lang['userdetails_yahoo']}</td>
                      <td colspan='2' align='left'><input type='text' size='60' name='yahoo' value='" . htmlsafechars($user['yahoo']) . "' /></td>
                </tr>
                <tr>
                      <td class='rowhead'>{$lang['userdetails_icq']}</td>
                      <td colspan='2' align='left'><input type='text' size='60' name='icq' value='" . htmlsafechars($user['icq']) . "' /></td>
                </tr>
                <tr>
                      <td class='rowhead'>{$lang['userdetails_website']}</td>
                      <td colspan='2' align='left'><input type='text' size='60' name='website' value='" . htmlsafechars($user['website']) . "' /></td>
                </tr>";
    //== we do not want mods to be able to change user classes or amount donated...
    // === Donor mod time based by snuggles
    if ($CURUSER["class"] == UC_MAX) {
        $donor = $user["donor"] == "yes";
        $HTMLOUT.= "<tr><td class='rowhead' align='right'><b>{$lang['userdetails_donor']}</b></td><td colspan='2' align='center'>";
        if ($donor) {
            $donoruntil = (int)$user['donoruntil'];
            if ($donoruntil == '0') $HTMLOUT.= $lang['userdetails_arbitrary'];
            else {
                $HTMLOUT.= "<b>" . $lang['userdetails_donor2'] . "</b> " . get_date($user['donoruntil'], 'DATE') . " ";
                $HTMLOUT.= " [ " . mkprettytime($donoruntil - TIME_NOW) . " ] {$lang['userdetails_togo']}\n";
            }
        } else {
            $HTMLOUT.= "{$lang['userdetails_dfor']}<select name='donorlength'><option value='0'>------</option><option value='4'>1 {$lang['userdetails_month']}</option>" . "<option value='6'>6 {$lang['userdetails_weeks']}</option><option value='8'>2 {$lang['userdetails_months']}</option><option value='10'>10 {$lang['userdetails_weeks']}</option>" . "<option value='12'>3 {$lang['userdetails_months']}</option><option value='255'>{$lang['userdetails_unlimited']}</option></select>\n";
        }
        $HTMLOUT.= "<br /><b>{$lang['userdetails_cdonation']}</b><input type='text' size='6' name='donated' value=\"" . htmlsafechars($user["donated"]) . "\" />" . "<b>{$lang['userdetails_tdonations']}</b>" . htmlsafechars($user["total_donated"]) . "";
        if ($donor) {
            $HTMLOUT.= "<br /><b>{$lang['userdetails_adonor']}</b> <select name='donorlengthadd'><option value='0'>------</option><option value='4'>1 {$lang['userdetails_month']}</option>" . "<option value='6'>6 {$lang['userdetails_weeks']}</option><option value='8'>2 {$lang['userdetails_months']}</option><option value='10'>10 {$lang['userdetails_weeks']}</option>" . "<option value='12'>3 {$lang['userdetails_months']}</option><option value='255'>{$lang['userdetails_unlimited']}</option></select>\n";
            $HTMLOUT.= "<br /><b>{$lang['userdetails_rdonor']}</b><input name='donor' value='no' type='checkbox' /> [ {$lang['userdetails_bad']} ]";
        }
        $HTMLOUT.= "</td></tr>\n";
    }
    // ====End
    if ($CURUSER['class'] == UC_STAFF && $user["class"] > UC_VIP) $HTMLOUT.= "<input type='hidden' name='class' value='{$user['class']}' />\n";
    else {
        $HTMLOUT.= "<tr><td class='rowhead'>Class</td><td colspan='2' align='left'><select name='class'>\n";
        if ($CURUSER['class'] == UC_STAFF) $maxclass = UC_VIP;
        else $maxclass = $CURUSER['class'] - 1;
        for ($i = 0; $i <= $maxclass; ++$i) $HTMLOUT.= "<option value='$i'" . ($user["class"] == $i ? " selected='selected'" : "") . ">" . get_user_class_name($i) . "</option>\n";
        $HTMLOUT.= "</select></td></tr>\n";
    }
    $supportfor = htmlsafechars($user["supportfor"]);
    //$HTMLOUT.= "<tr><td class='rowhead'>{$lang['userdetails_support']}</td><td colspan='2' align='left'><input type='checkbox' name='support' value='yes'" . (($user['opt1'] & user_options::SUPPORT) ? " checked='checked'" : "") . " />{$lang['userdetails_yes']}</td></tr>\n";
    $HTMLOUT.= "<tr><td class='rowhead'>{$lang['userdetails_support']}</td><td colspan='2' align='left'><input type='radio' name='support' value='yes'".($user["support"] == "yes" ? " checked='checked'" : "")." />{$lang['userdetails_yes']}<input type='radio' name='support' value='no'".($user["support"] == "no" ? " checked='checked'" : "")." />{$lang['userdetails_no']}</td></tr>\n";
    $HTMLOUT.= "<tr><td class='rowhead'>{$lang['userdetails_supportfor']}</td><td colspan='2' align='left'><textarea cols='60' rows='2' name='supportfor'>{$supportfor}</textarea></td></tr>\n";
    $modcomment = htmlsafechars($user_stats["modcomment"]);
    if ($CURUSER["class"] < UC_SYSOP) {
        $HTMLOUT.= "<tr><td class='rowhead'>{$lang['userdetails_comment']}</td><td colspan='2' align='left'><textarea cols='60' rows='6' name='modcomment' readonly='readonly'>$modcomment</textarea></td></tr>\n";
    } else {
        $HTMLOUT.= "<tr><td class='rowhead'>{$lang['userdetails_comment']}</td><td colspan='2' align='left'><textarea cols='60' rows='6' name='modcomment'>$modcomment</textarea></td></tr>\n";
    }
    $HTMLOUT.= "<tr><td class='rowhead'>{$lang['userdetails_add_comment']}</td><td colspan='2' align='left'><textarea cols='60' rows='2' name='addcomment'></textarea></td></tr>\n";
    //=== bonus comment
    $bonuscomment = htmlsafechars($user_stats["bonuscomment"]);
    $HTMLOUT.= "<tr><td class='rowhead'>{$lang['userdetails_bonus_comment']}</td><td colspan='2' align='left'><textarea cols='60' rows='6' name='bonuscomment' readonly='readonly' style='background:purple;color:yellow;'>$bonuscomment</textarea></td></tr>\n";
    //==end
    $HTMLOUT.= "<tr><td class='rowhead'>{$lang['userdetails_enabled']}</td><td colspan='2' align='left'><input name='enabled' value='yes' type='radio'" . ($enabled ? " checked='checked'" : "") . " />{$lang['userdetails_yes']} <input name='enabled' value='no' type='radio'" . (!$enabled ? " checked='checked'" : "") . " />{$lang['userdetails_no']}</td></tr>\n";
    if ($CURUSER['class'] >= UC_STAFF && XBT_TRACKER == false) $HTMLOUT.= "<tr><td class='rowhead'>{$lang['userdetails_freeleech_slots']}</td><td colspan='2' align='left'>
         <input type='text' size='6' name='freeslots' value='" . (int)$user['freeslots'] . "' /></td></tr>";
    if ($CURUSER['class'] >= UC_ADMINISTRATOR && XBT_TRACKER == false) {
        $free_switch = $user['free_switch'] != 0;
        $HTMLOUT.= "<tr><td class='rowhead'" . (!$free_switch ? ' rowspan="2"' : '') . ">{$lang['userdetails_freeleech_status']}</td>
                <td align='left' width='20%'>" . ($free_switch ? "<input name='free_switch' value='42' type='radio' />{$lang['userdetails_remove_freeleech']}" : $lang['userdetails_no_freeleech']) . "</td>\n";
        if ($free_switch) {
            if ($user['free_switch'] == 1) $HTMLOUT.= '<td align="center">('.$lang['userdetails_unlimited_d'].')</td></tr>';
            else $HTMLOUT.= "<td align='center'>{$lang['userdetails_until']} " . get_date($user['free_switch'], 'DATE') . " (" . mkprettytime($user['free_switch'] - TIME_NOW) . " {$lang['userdetails_togo']})</td></tr>";
        } else {
            $HTMLOUT.= '<td>'.$lang['userdetails_freeleech_for'].' <select name="free_switch">
         <option value="0">------</option>
         <option value="1">1 '.$lang['userdetails_week'].'</option>
         <option value="2">2 '.$lang['userdetails_weeks'].'</option>
         <option value="4">4 '.$lang['userdetails_weeks'].'</option>
         <option value="8">8 '.$lang['userdetails_weeks'].'</option>
         <option value="255">'.$lang['userdetails_unlimited'].'</option>
         </select></td></tr>
         <tr><td colspan="2" align="left">'.$lang['userdetails_pm_comment'].':<input type="text" size="60" name="free_pm" /></td></tr>';
        }
    }
    //==XBT - Can Leech
    if (XBT_TRACKER == true) {
        $HTMLOUT.= "<tr><td class='rowhead'>{$lang['userdetails_canleech']}</td><td class='row' colspan='2' align='left'><input type='radio' name='can_leech' value='1' " . ($user["can_leech"] == 1 ? " checked='checked'" : "") . " />{$lang['userdetails_yes']} <input type='radio' name='can_leech' value='0' " . ($user["can_leech"] == 0 ? " checked='checked'" : "") . " />{$lang['userdetails_no']}</td></tr>\n";
    }
    //==Download disable
    if ($CURUSER['class'] >= UC_STAFF && XBT_TRACKER == false) {
        $downloadpos = $user['downloadpos'] != 1;
        $HTMLOUT.= "<tr><td class='rowhead'" . (!$downloadpos ? ' rowspan="2"' : '') . ">{$lang['userdetails_dpos']}</td>
               <td align='left' width='20%'>" . ($downloadpos ? "<input name='downloadpos' value='42' type='radio' />{$lang['userdetails_remove_download_d']}" : $lang['userdetails_no_disablement']) . "</td>\n";
        if ($downloadpos) {
            if ($user['downloadpos'] == 0) $HTMLOUT.= '<td align="center">('.$lang['userdetails_unlimited_d'].')</td></tr>';
            else $HTMLOUT.= "<td align='center'>{$lang['userdetails_until']} " . get_date($user['downloadpos'], 'DATE') . " (" . mkprettytime($user['downloadpos'] - TIME_NOW) . " {$lang['userdetails_togo']})</td></tr>";
        } else {
            $HTMLOUT.= '<td>'.$lang['userdetails_disable_for'].' <select name="downloadpos">
        <option value="0">------</option>
        <option value="1">1 '.$lang['userdetails_week'].'</option>
        <option value="2">2 '.$lang['userdetails_weeks'].'</option>
        <option value="4">4 '.$lang['userdetails_weeks'].'</option>
        <option value="8">8 '.$lang['userdetails_weeks'].'</option>
        <option value="255">'.$lang['userdetails_unlimited'].'</option>
        </select></td></tr>
        <tr><td colspan="2" align="left">'.$lang['userdetails_pm_comment'].':<input type="text" size="60" name="disable_pm" /></td></tr>';
        }
    }
    //==Upload disable
    if ($CURUSER['class'] >= UC_STAFF) {
        $uploadpos = $user['uploadpos'] != 1;
        $HTMLOUT.= "<tr><td class='rowhead'" . (!$uploadpos ? ' rowspan="2"' : '') . ">{$lang['userdetails_upos']}</td>
               <td align='left' width='20%'>" . ($uploadpos ? "<input name='uploadpos' value='42' type='radio' />{$lang['userdetails_remove_upload_d']}" : $lang['userdetails_no_disablement']) . "</td>\n";
        if ($uploadpos) {
            if ($user['uploadpos'] == 0) $HTMLOUT.= '<td align="center">('.$lang['userdetails_unlimited_d'].')</td></tr>';
            else $HTMLOUT.= "<td align='center'>{$lang['userdetails_until']} " . get_date($user['uploadpos'], 'DATE') . " (" . mkprettytime($user['uploadpos'] - TIME_NOW) . " {$lang['userdetails_togo']})</td></tr>";
        } else {
            $HTMLOUT.= '<td>'.$lang['userdetails_disable_for'].' <select name="uploadpos">
        <option value="0">------</option>
        <option value="1">1 '.$lang['userdetails_week'].'</option>
        <option value="2">2 '.$lang['userdetails_weeks'].'</option>
        <option value="4">4 '.$lang['userdetails_weeks'].'</option>
        <option value="8">8 '.$lang['userdetails_weeks'].'</option>
        <option value="255">'.$lang['userdetails_unlimited'].'</option>
        </select></td></tr>
        <tr><td colspan="2" align="left">'.$lang['userdetails_pm_comment'].':<input type="text" size="60" name="updisable_pm" /></td></tr>';
        }
    }
    //==
    //==Pm disable
    if ($CURUSER['class'] >= UC_STAFF) {
        $sendpmpos = $user['sendpmpos'] != 1;
        $HTMLOUT.= "<tr><td class='rowhead'" . (!$sendpmpos ? ' rowspan="2"' : '') . ">{$lang['userdetails_pmpos']}</td>
               <td align='left' width='20%'>" . ($sendpmpos ? "<input name='sendpmpos' value='42' type='radio' />{$lang['userdetails_remove_pm_d']}" : $lang['userdetails_no_disablement']) . "</td>\n";
        if ($sendpmpos) {
            if ($user['sendpmpos'] == 0) $HTMLOUT.= '<td align="center">('.$lang['userdetails_unlimited_d'].')</td></tr>';
            else $HTMLOUT.= "<td align='center'>{$lang['userdetails_until']} " . get_date($user['sendpmpos'], 'DATE') . " (" . mkprettytime($user['sendpmpos'] - TIME_NOW) . " {$lang['userdetails_togo']})</td></tr>";
        } else {
            $HTMLOUT.= '<td>'.$lang['userdetails_disable_for'].' <select name="sendpmpos">
        <option value="0">------</option>
        <option value="1">1 '.$lang['userdetails_week'].'</option>
        <option value="2">2 '.$lang['userdetails_weeks'].'</option>
        <option value="4">4 '.$lang['userdetails_weeks'].'</option>
        <option value="8">8 '.$lang['userdetails_weeks'].'</option>
        <option value="255">'.$lang['userdetails_unlimited'].'</option>
        </select></td></tr>
        <tr><td colspan="2" align="left">'.$lang['userdetails_pm_comment'].':<input type="text" size="60" name="pmdisable_pm" /></td></tr>';
        }
    }
    //==Shoutbox disable
    if ($CURUSER['class'] >= UC_STAFF) {
        $chatpost = $user['chatpost'] != 1;
        $HTMLOUT.= "<tr><td class='rowhead'" . (!$chatpost ? ' rowspan="2"' : '') . ">{$lang['userdetails_chatpos']}</td>
               <td align='left' width='20%'>" . ($chatpost ? "<input name='chatpost' value='42' type='radio' />{$lang['userdetails_remove_shout_d']}" : $lang['userdetails_no_disablement']) . "</td>\n";
        if ($chatpost) {
            if ($user['chatpost'] == 0) $HTMLOUT.= '<td align="center">('.$lang['userdetails_unlimited_d'].')</td></tr>';
            else $HTMLOUT.= "<td align='center'>{$lang['userdetails_until']} " . get_date($user['chatpost'], 'DATE') . " (" . mkprettytime($user['chatpost'] - TIME_NOW) . " {$lang['userdetails_togo']})</td></tr>";
        } else {
            $HTMLOUT.= '<td>'.$lang['userdetails_disable_for'].' <select name="chatpost">
        <option value="0">------</option>
        <option value="1">1 '.$lang['userdetails_week'].'</option>
        <option value="2">2 '.$lang['userdetails_weeks'].'</option>
        <option value="4">4 '.$lang['userdetails_weeks'].'</option>
        <option value="8">8 '.$lang['userdetails_weeks'].'</option>
        <option value="255">'.$lang['userdetails_unlimited'].'</option>
        </select></td></tr>
        <tr><td colspan="2" align="left">'.$lang['userdetails_pm_comment'].':<input type="text" size="60" name="chatdisable_pm" /></td></tr>';
        }
    }
    //==Avatar disable
    if ($CURUSER['class'] >= UC_STAFF) {
        $avatarpos = $user['avatarpos'] != 1;
        $HTMLOUT.= "<tr><td class='rowhead'" . (!$avatarpos ? ' rowspan="2"' : '') . ">{$lang['userdetails_avatarpos']}</td>
          <td align='left' width='20%'>" . ($avatarpos ? "<input name='avatarpos' value='42' type='radio' />{$lang['userdetails_remove_avatar_d']}" : $lang['userdetails_no_disablement']) . "</td>\n";
        if ($avatarpos) {
            if ($user['avatarpos'] == 0) $HTMLOUT.= '<td align="center">('.$lang['userdetails_unlimited_d'].')</td></tr>';
            else $HTMLOUT.= "<td align='center'>{$lang['userdetails_until']} " . get_date($user['avatarpos'], 'DATE') . " (" . mkprettytime($user['avatarpos'] - TIME_NOW) . " {$lang['userdetails_togo']})</td></tr>";
        } else {
            $HTMLOUT.= '<td>'.$lang['userdetails_disable_for'].' <select name="avatarpos">
        <option value="0">------</option>
        <option value="1">1 '.$lang['userdetails_week'].'</option>
        <option value="2">2 '.$lang['userdetails_weeks'].'</option>
        <option value="4">4 '.$lang['userdetails_weeks'].'</option>
        <option value="8">8 '.$lang['userdetails_weeks'].'</option>
        <option value="255">'.$lang['userdetails_unlimited'].'</option>
        </select></td></tr>
        <tr><td colspan="2" align="left">'.$lang['userdetails_pm_comment'].':<input type="text" size="60" name="avatardisable_pm" /></td></tr>';
        }
    }
    //==Immunity
    if ($CURUSER['class'] >= UC_STAFF) {
        $immunity = $user['immunity'] != 0;
        $HTMLOUT.= "<tr><td class='rowhead'" . (!$immunity ? ' rowspan="2"' : '') . ">{$lang['userdetails_immunity']}</td>
               <td align='left' width='20%'>" . ($immunity ? "<input name='immunity' value='42' type='radio' />{$lang['userdetails_remove_immunity']}" : $lang['userdetails_no_immunity']) . "</td>\n";
        if ($immunity) {
            if ($user['immunity'] == 1) $HTMLOUT.= '<td align="center">('.$lang['userdetails_unlimited_d'].')</td></tr>';
            else $HTMLOUT.= "<td align='center'>{$lang['userdetails_until']} " . get_date($user['immunity'], 'DATE') . " (" . mkprettytime($user['immunity'] - TIME_NOW) . " {$lang['userdetails_togo']})</td></tr>";
        } else {
            $HTMLOUT.= '<td>'.$lang['userdetails_immunity_for'].' <select name="immunity">
        <option value="0">------</option>
        <option value="1">1 '.$lang['userdetails_week'].'</option>
        <option value="2">2 '.$lang['userdetails_weeks'].'</option>
        <option value="4">4 '.$lang['userdetails_weeks'].'</option>
        <option value="8">8 '.$lang['userdetails_weeks'].'</option>
        <option value="255">'.$lang['userdetails_unlimited'].'</option>
        </select></td></tr>
        <tr><td colspan="2" align="left">'.$lang['userdetails_pm_comment'].':<input type="text" size="60" name="immunity_pm" /></td></tr>';
        }
    }
    //==End
    //==Leech Warnings
    if ($CURUSER['class'] >= UC_STAFF) {
        $leechwarn = $user['leechwarn'] != 0;
        $HTMLOUT.= "<tr><td class='rowhead'" . (!$leechwarn ? ' rowspan="2"' : '') . ">{$lang['userdetails_leechwarn']}</td>
               <td align='left' width='20%'>" . ($leechwarn ? "<input name='leechwarn' value='42' type='radio' />{$lang['userdetails_remove_leechwarn']}" : $lang['userdetails_no_leechwarn']) . "</td>\n";
        if ($leechwarn) {
            if ($user['leechwarn'] == 1) $HTMLOUT.= '<td align="center">('.$lang['userdetails_unlimited_d'].')</td></tr>';
            else $HTMLOUT.= "<td align='center'>{$lang['userdetails_until']} " . get_date($user['leechwarn'], 'DATE') . " (" . mkprettytime($user['leechwarn'] - TIME_NOW) . " {$lang['userdetails_togo']})</td></tr>";
        } else {
            $HTMLOUT.= '<td>'.$lang['userdetails_leechwarn_for'].' <select name="leechwarn">
        <option value="0">------</option>
        <option value="1">1 '.$lang['userdetails_week'].'</option>
        <option value="2">2 '.$lang['userdetails_weeks'].'</option>
        <option value="4">4 '.$lang['userdetails_weeks'].'</option>
        <option value="8">8 '.$lang['userdetails_weeks'].'</option>
        <option value="255">'.$lang['userdetails_unlimited'].'</option>
        </select></td></tr>
        <tr><td colspan="2" align="left">'.$lang['userdetails_pm_comment'].':<input type="text" size="60" name="leechwarn_pm" /></td></tr>';
        }
    }
    //==End
    //==Warnings
    if ($CURUSER['class'] >= UC_STAFF) {
        $warned = $user['warned'] != 0;
        $HTMLOUT.= "<tr><td class='rowhead'" . (!$warned ? ' rowspan="2"' : '') . ">{$lang['userdetails_warned']}</td>
               <td align='left' width='20%'>" . ($warned ? "<input name='warned' value='42' type='radio' />{$lang['userdetails_remove_warned']}" : $lang['userdetails_no_warning']) . "</td>\n";
        if ($warned) {
            if ($user['warned'] == 1) $HTMLOUT.= '<td align="center">('.$lang['userdetails_unlimited_d'].')</td></tr>';
            else $HTMLOUT.= "<td align='center'>{$lang['userdetails_until']} " . get_date($user['warned'], 'DATE') . " (" . mkprettytime($user['warned'] - TIME_NOW) . " {$lang['userdetails_togo']})</td></tr>";
        } else {
            $HTMLOUT.= '<td>' . $lang['userdetails_warn_for'] . '<select name="warned">
        <option value="0">' . $lang['userdetails_warn0'] . '</option>
        <option value="1">' . $lang['userdetails_warn1'] . '</option>
        <option value="2">' . $lang['userdetails_warn2'] . '</option>
        <option value="4">' . $lang['userdetails_warn4'] . '</option>
        <option value="8">' . $lang['userdetails_warn8'] . '</option>
        <option value="255">' . $lang['userdetails_warninf'] . '</option>
        </select></td></tr>
        <tr><td colspan="2" align="left">' . $lang['userdetails_pm_comm'] . '<input type="text" size="60" name="warned_pm" /></td></tr>';
        }
    }
    //==End
    //==Games disable
    if ($CURUSER['class'] >= UC_STAFF) {
        $game_access = $user['game_access'] != 1;
        $HTMLOUT.= "<tr><td class='rowhead'" . (!$game_access ? ' rowspan="2"' : '') . ">{$lang['userdetails_games']}</td>
           <td align='left' width='20%'>" . ($game_access ? "<input name='game_access' value='42' type='radio' />{$lang['userdetails_remove_game_d']}" : $lang['userdetails_no_disablement']) . "</td>\n";
        if ($game_access) {
            if ($user['game_access'] == 0) $HTMLOUT.= '<td align="center">('.$lang['userdetails_unlimited_d'].')</td></tr>';
            else $HTMLOUT.= "<td align='center'>{$lang['userdetails_until']} " . get_date($user['game_access'], 'DATE') . " (" . mkprettytime($user['game_access'] - TIME_NOW) . " {$lang['userdetails_togo']})</td></tr>";
        } else {
            $HTMLOUT.= '<td>'.$lang['userdetails_disable_for'].' <select name="game_access">
        <option value="0">------</option>
        <option value="1">1 '.$lang['userdetails_week'].'</option>
        <option value="2">2 '.$lang['userdetails_weeks'].'</option>
        <option value="4">4 '.$lang['userdetails_weeks'].'</option>
        <option value="8">8 '.$lang['userdetails_weeks'].'</option>
        <option value="255">'.$lang['userdetails_unlimited'].'</option>
        </select></td></tr>
        <tr><td colspan="2" align="left">'.$lang['userdetails_pm_comment'].':<input type="text" size="60" name="game_disable_pm" /></td></tr>';
        }
    }
    if (XBT_TRACKER == true) {
        // == Wait time
        if ($CURUSER['class'] >= UC_STAFF) $HTMLOUT.= "<tr><td class='rowhead'>{$lang['userdetails_waittime']}</td><td colspan='2' align='left'><input type='text' size='6' name='wait_time' value='" . (int)$user['wait_time'] . "' /></td></tr>";
        // ==end
        // == Peers limit
        if ($CURUSER['class'] >= UC_STAFF) $HTMLOUT.= "<tr><td class='rowhead'>{$lang['userdetails_peerslimit']}</td><td colspan='2' align='left'><input type='text' size='6' name='peers_limit' value='" . (int)$user['peers_limit'] . "' /></td></tr>";
        // ==end
        // == Torrents limit
        if ($CURUSER['class'] >= UC_STAFF) $HTMLOUT.= "<tr><td class='rowhead'>{$lang['userdetails_torrentslimit']}</td><td colspan='2' align='left'><input type='text' size='6' name='torrents_limit' value='" . (int)$user['torrents_limit'] . "' /></td></tr>";
        // ==end
        
    }
    //==High speed
    if ($CURUSER["class"] == UC_MAX && XBT_TRACKER == false) {
        //$HTMLOUT.= "<tr><td class='rowhead'>{$lang['userdetails_highspeed']}</td><td class='row' colspan='2' align='left'><input type='checkbox' name='highspeed' value='yes'" . (($user['opt1'] & user_options::HIGHSPEED) ? " checked='checked'" : "") . " />Yes</td></tr>\n";
          $HTMLOUT.= "<tr><td class='rowhead'>{$lang['userdetails_highspeed']}</td><td class='row' colspan='2' align='left'><input type='radio' name='highspeed' value='yes' ".($user["highspeed"] == "yes" ? " checked='checked'" : "")." />{$lang['userdetails_yes']} <input type='radio' name='highspeed' value='no' ".($user["highspeed"] == "no" ? " checked='checked'" : "")." />{$lang['userdetails_no']}</td></tr>\n";
    }
    //$HTMLOUT.= "<tr><td class='rowhead'>{$lang['userdetails_park']}</td><td colspan='2' align='left'><input name='parked' value='yes' type='checkbox'" . (($user['opt1'] & user_options::PARKED) ? " checked='checked'" : "") . " />{$lang['userdetails_yes']}</td></tr>\n";
      $HTMLOUT.= "<tr><td class='rowhead'>{$lang['userdetails_park']}</td><td colspan='2' align='left'><input name='parked' value='yes' type='radio'".($user["parked"] == "yes" ? " checked='checked'" : "")." />{$lang['userdetails_yes']} <input name='parked' value='no' type='radio'".($user["parked"] == "no" ? " checked='checked'" : "")." />{$lang['userdetails_no']}</td></tr>\n";
     $HTMLOUT.= "<tr><td class='rowhead'>{$lang['userdetails_reset']}</td><td colspan='2'><input type='checkbox' name='reset_torrent_pass' value='1' /><font class='small'>{$lang['userdetails_pass_msg']}</font></td></tr>";
    // == seedbonus
    if ($CURUSER['class'] >= UC_STAFF) $HTMLOUT.= "<tr><td class='rowhead'>{$lang['userdetails_bonus_points']}</td><td colspan='2' align='left'><input type='text' size='6' name='seedbonus' value='" . (int)$user_stats['seedbonus'] . "' /></td></tr>";
    // ==end
    // == rep
    if ($CURUSER['class'] >= UC_STAFF) $HTMLOUT.= "<tr><td class='rowhead'>{$lang['userdetails_rep_points']}</td><td colspan='2' align='left'><input type='text' size='6' name='reputation' value='" . (int)$user['reputation'] . "' /></td></tr>";
    // ==end
    //==Invites
    $HTMLOUT.= "<tr><td class='rowhead'>{$lang['userdetails_invright']}</td><td colspan='2' align='left'><input type='radio' name='invite_on' value='yes'".($user["invite_on"] == "yes" ? " checked='checked'" : "")." />{$lang['userdetails_yes']}<input type='radio' name='invite_on' value='no'".($user["invite_on"] == "no" ? " checked='checked'" : "")." />{$lang['userdetails_no']}</td></tr>\n";
    //$HTMLOUT.= "<tr><td class='rowhead'>{$lang['userdetails_invright']}</td><td colspan='2' align='left'><input type='checkbox' name='invite_on' value='yes'" . (($user['opt1'] & user_options::INVITE_ON) ? " checked='checked'" : "") . " />{$lang['userdetails_yes']}</td></tr>\n";
    $HTMLOUT.= "<tr><td class='rowhead'><b>{$lang['userdetails_invites']}</b></td><td colspan='2' align='left'><input type='text' size='3' name='invites' value='" . htmlsafechars($user['invites']) . "' /></td></tr>\n";
    /*$HTMLOUT.= "<tr>
                      <td class='rowhead'>Avatar Rights</td>
                      <td colspan='2' align='left'><input name='view_offensive_avatar' value='yes' type='checkbox'" . (($user['opt1'] & user_options::VIEW_OFFENSIVE_AVATAR) ? " checked='checked'" : "") . " />Yes</td>
                </tr>      
                <tr>
                      <td class='rowhead'>Offensive Avatar</td>
                      <td colspan='2' align='left'><input name='offensive_avatar' value='yes' type='checkbox'" . (($user['opt1'] & user_options::OFFENSIVE_AVATAR) ? " checked='checked'" : "") . " />Yes</td>
                </tr>
                <tr>
                      <td class='rowhead'>View Offensive Avatars</td>
                      <td colspan='2' align='left'><input name='avatar_rights' value='yes' type='checkbox'" . (($user['opt1'] & user_options::AVATAR_RIGHTS) ? " checked='checked'" : "") . " />Yes</td>
                </tr>";*/
    $HTMLOUT.= "<tr>
                  <td class='rowhead'>{$lang['userdetails_avatar_rights']}</td>
                  <td colspan='2' align='left'><input name='view_offensive_avatar' value='yes' type='radio'".($user['view_offensive_avatar'] == "yes" ? " checked='checked'" : "")." />{$lang['userdetails_yes']}
                  <input name='view_offensive_avatar' value='no' type='radio'".($user['view_offensive_avatar'] == "no" ? " checked='checked'" : "")." />{$lang['userdetails_no']} </td>
                 </tr>
                 <tr>
                  <td class='rowhead'>{$lang['userdetails_offensive']}</td>
                  <td colspan='2' align='left'><input name='offensive_avatar' value='yes' type='radio'".($user['offensive_avatar'] == "yes" ? " checked='checked'" : "")." />{$lang['userdetails_yes']}
                  <input name='offensive_avatar' value='no' type='radio'".($user['offensive_avatar'] == "no" ? " checked='checked'" : "")." />{$lang['userdetails_no']} </td>
                </tr>
                <tr>
                  <td class='rowhead'>{$lang['userdetails_view_offensive']}</td>
                  <td colspan='2' align='left'><input name='avatar_rights' value='yes' type='radio'".($user['avatar_rights'] == "yes" ? " checked='checked'" : "")." />{$lang['userdetails_yes']}
                  <input name='avatar_rights' value='no' type='radio'".($user['avatar_rights'] == "no" ? " checked='checked'" : "")." />{$lang['userdetails_no']} </td>
               </tr>";
    $HTMLOUT.= '<tr>
                      <td class="rowhead">'.$lang['userdetails_hnr'].'</td>
                      <td colspan="2" align="left"><input type="text" size="3" name="hit_and_run_total" value="' . (int)$user['hit_and_run_total'] . '" /></td>
                </tr>
                 <tr>
                     <td class="rowhead">'.$lang['userdetails_suspended'].'</td>
                     <td colspan="2" align="left"><input name="suspended" value="yes" type="radio"'.($user['suspended'] == 'yes' ? ' checked="checked"' : '').' />'.$lang['userdetails_yes'].'
                     <input name="suspended" value="no" type="radio"'.($user['suspended'] == 'no' ? ' checked="checked"' : '').' />'.$lang['userdetails_no'].'
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; '.$lang['userdetails_suspended_reason'].'<br />
                    <input type="text" size="60" name="suspended_reason" /></td>
                   </tr>
                <!--<tr>
                      <td class="rowhead">'.$lang['userdetails_suspended'].'</td>
                      <td colspan="2" align="left"><input name="suspended" value="yes" type="checkbox"' . (($user['opt1'] & user_options::SUSPENDED) ? ' checked="checked"' : '') . ' />'.$lang['userdetails_yes'].'
                      &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; '.$lang['userdetails_suspended_reason'].'<br />
                      <input type="text" size="60" name="suspended_reason" /></td>
                </tr>-->
      ';
    $HTMLOUT.= "<tr>
                      <td class='rowhead'>{$lang['userdetails_paranoia']}</td>
                      <td colspan='2' align='left'>
                      <select name='paranoia'>
                      <option value='0'" . ($user['paranoia'] == 0 ? " selected='selected'" : "") . ">{$lang['userdetails_paranoia_0']}</option>
                      <option value='1'" . ($user['paranoia'] == 1 ? " selected='selected'" : "") . ">{$lang['userdetails_paranoia_1']}</option>
                      <option value='2'" . ($user['paranoia'] == 2 ? " selected='selected'" : "") . ">{$lang['userdetails_paranoia_2']}</option>
                      <option value='3'" . ($user['paranoia'] == 3 ? " selected='selected'" : "") . ">{$lang['userdetails_paranoia_3']}</option>
                      </select></td>
                </tr>
                 <tr>
                     <td class='rowhead'>{$lang['userdetails_forum_rights']}</td>
                     <td colspan='2' align='left'><input name='forum_post' value='yes' type='radio'".($user['forum_post'] == "yes" ? " checked='checked'" : "")." />{$lang['userdetails_yes']}
                     <input name='forum_post' value='no' type='radio'".($user['forum_post'] == "no" ? " checked='checked'" : "")." />{$lang['userdetails_forums_no']}</td>
                    </tr>
                <!--<tr>
                      <td class='rowhead'>{$lang['userdetails_forum_rights']}</td>
                      <td colspan='2' align='left'><input name='forum_post' value='yes' type='checkbox'" . (($user['opt1'] & user_options::FORUM_POST) ? " checked='checked'" : "") . " />{$lang['userdetails_yes']}</td>
                </tr>-->";
    //Adjust up/down
    if ($CURUSER['class'] >= UC_ADMINISTRATOR) {
        $HTMLOUT.= "<tr>
         <td class='rowhead'>{$lang['userdetails_addupload']}</td>
         <td align='center'>
         <img src='{$INSTALLER09['pic_base_url']}plus.gif' alt='{$lang['userdetails_change_ratio']}' title='{$lang['userdetails_change_ratio']}!' id='uppic' onclick=\"togglepic('{$INSTALLER09['baseurl']}', 'uppic','upchange')\" />
         <input type='text' name='amountup' size='10' />
         </td>
         <td>
         <select name='formatup'>\n
         <option value='mb'>{$lang['userdetails_MB']}</option>\n
         <option value='gb'>{$lang['userdetails_GB']}</option></select>\n
         <input type='hidden' id='upchange' name='upchange' value='plus' />
         </td>
         </tr>
         <tr>
         <td class='rowhead'>{$lang['userdetails_adddownload']}</td>
         <td align='center'>
         <img src='{$INSTALLER09['pic_base_url']}plus.gif' alt='{$lang['userdetails_change_ratio']}' title='{$lang['userdetails_change_ratio']}!' id='downpic' onclick=\"togglepic('{$INSTALLER09['baseurl']}','downpic','downchange')\" />
         <input type='text' name='amountdown' size='10' />
         </td>
         <td>
         <select name='formatdown'>\n
         <option value='mb'>{$lang['userdetails_MB']}</option>\n
         <option value='gb'>{$lang['userdetails_GB']}</option></select>\n
         <input type='hidden' id='downchange' name='downchange' value='plus' />
         </td></tr>";
    }
    $HTMLOUT.= "<tr><td colspan='3' align='center'><input type='submit' class='btn' value='{$lang['userdetails_okay']}' /></td></tr>\n";
    $HTMLOUT.= "</table>\n";
    $HTMLOUT.= "</form>\n";
}
$HTMLOUT.= "</div></div></div>";
$HTMLOUT.= end_main_frame();
echo stdhead("{$lang['userdetails_details']} " . $user["username"], true, $stdhead) . $HTMLOUT . stdfoot($stdfoot);
?>
