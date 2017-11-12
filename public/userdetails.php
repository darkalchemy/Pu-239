<?php
require_once realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..') . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'user_functions.php';
require_once INCL_DIR . 'bbcode_functions.php';
require_once INCL_DIR . 'html_functions.php';
require_once INCL_DIR . 'function_onlinetime.php';
require_once CLASS_DIR . 'class_user_options.php';
require_once CLASS_DIR . 'class_user_options_2.php';
check_user_status();
global $mc1, $CURUSER, $site_config;
$lang = array_merge(load_language('global'), load_language('userdetails'));
$edit_profile = $friend_links = $shitty_link = $sharemark_link = '';

$stdhead = [
    'css' => [
        get_file('userdetails_css'),
    ],
];
$stdfoot = [
    'js' => [
        get_file('userdetails_js'),
    ],
];
$id = (int)$_GET['id'];
if (!is_valid_id($id)) {
    stderr($lang['userdetails_error'], "{$lang['userdetails_bad_id']}");
}
if (($user = $mc1->get_value('user' . $id)) === false) {
    $user_fields_ar_int = [
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
    ];
    $user_fields_ar_float = [
        'time_offset',
        'total_donated',
    ];
    $user_fields_ar_str = [
        'username',
        'torrent_pass',
        'email',
        'status',
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
        'browse_icons',
    ];
    $user_fields = implode(', ', array_merge($user_fields_ar_int, $user_fields_ar_float, $user_fields_ar_str));
    $r1 = sql_query('SELECT ' . $user_fields . ' FROM users WHERE id=' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
    $user = mysqli_fetch_assoc($r1) or stderr($lang['userdetails_error'], "{$lang['userdetails_no_user']}");
    foreach ($user_fields_ar_int as $i) {
        $user[ $i ] = (int)$user[ $i ];
    }
    foreach ($user_fields_ar_float as $i) {
        $user[ $i ] = (float)$user[ $i ];
    }

    $mc1->cache_value('user' . $id, $user, $site_config['expires']['user_cache']);
}
if ($user['status'] == 'pending') {
    stderr($lang['userdetails_error'], $lang['userdetails_pending']);
}
// user stats
$What_Cache = (XBT_TRACKER == true ? 'user_stats_xbt_' : 'user_stats_');
if (($user_stats = $mc1->get_value($What_Cache . $id)) === false) {
    $What_Expire = (XBT_TRACKER == true ? $site_config['expires']['user_stats_xbt'] : $site_config['expires']['user_stats']);
    $stats_fields_ar_int = [
        'uploaded',
        'downloaded',
    ];
    $stats_fields_ar_float = [
        'seedbonus',
    ];
    $stats_fields_ar_str = [
        'modcomment',
        'bonuscomment',
    ];
    $stats_fields = implode(', ', array_merge($stats_fields_ar_int, $stats_fields_ar_float, $stats_fields_ar_str));
    $sql_1 = sql_query('SELECT ' . $stats_fields . ' FROM users WHERE id= ' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
    $user_stats = mysqli_fetch_assoc($sql_1);
    foreach ($stats_fields_ar_int as $i) {
        $user_stats[ $i ] = (int)$user_stats[ $i ];
    }
    foreach ($stats_fields_ar_float as $i) {
        $user_stats[ $i ] = (float)$user_stats[ $i ];
    }

    $mc1->cache_value($What_Cache . $id, $user_stats, $What_Expire);
}
if (($user_status = $mc1->get_value('user_status_' . $id)) === false) {
    $sql_2 = sql_query('SELECT * FROM ustatus WHERE userid = ' . sqlesc($id));
    if (mysqli_num_rows($sql_2)) {
        $user_status = mysqli_fetch_assoc($sql_2);
    } else {
        $user_status = [
            'last_status' => '',
            'last_update' => 0,
            'archive'     => '',
        ];
    }
    $mc1->add_value('user_status_' . $id, $user_status, $site_config['expires']['user_status']); // 30 days
}

if ($user['paranoia'] == 3 && $CURUSER['class'] < UC_STAFF && $CURUSER['id'] != $id) {
    stderr($lang['userdetails_error'], '<span><img src=".images/smilies/tinfoilhat.gif" alt="' . $lang['userdetails_tinfoil'] . '" class="tooltipper" title="' . $lang['userdetails_tinfoil'] . '" />
       ' . $lang['userdetails_tinfoil2'] . ' <img src="./images/smilies/tinfoilhat.gif" alt="' . $lang['userdetails_tinfoil'] . '" class="tooltipper" title="' . $lang['userdetails_tinfoil'] . '" /></span>');
    exit();
}

if (isset($_GET['delete_hit_and_run']) && $CURUSER['class'] >= UC_STAFF) {
    $delete_me = isset($_GET['delete_hit_and_run']) ? intval($_GET['delete_hit_and_run']) : 0;
    if (!is_valid_id($delete_me)) {
        stderr($lang['userdetails_error'], $lang['userdetails_bad_id']);
    }
    if (XBT_TRACKER === false) {
        sql_query('UPDATE snatched SET hit_and_run = \'0\', mark_of_cain = \'no\' WHERE id = ' . sqlesc($delete_me)) or sqlerr(__FILE__, __LINE__);
    } else {
        sql_query('UPDATE xbt_files_users SET hit_and_run = \'0\', mark_of_cain = \'no\' WHERE fid = ' . sqlesc($delete_me)) or sqlerr(__FILE__, __LINE__);
    }
    if (@mysqli_affected_rows($GLOBALS['___mysqli_ston']) === 0) {
        stderr($lang['userdetails_error'], $lang['userdetails_notdeleted']);
    }
    header('Location: ?id=' . $id . '&completed=1');
    exit();
}
$r = sql_query('SELECT t.id, t.name, t.seeders, t.leechers, c.name AS cname, c.image FROM torrents t LEFT JOIN categories c ON t.category = c.id WHERE t.owner = ' . sqlesc($id) . ' ORDER BY t.name') or sqlerr(__FILE__, __LINE__);
if (mysqli_num_rows($r) > 0) {
    $torrents = "
    <div class='table-wrapper'>
    <table class='table table-bordered table-striped bottom20 one'>
            <tr>
                <td class='colhead'>{$lang['userdetails_type']}</td>
                <td class='colhead'>{$lang['userdetails_name']}</td>
                <td class='colhead'>{$lang['userdetails_seeders']}</td>
                <td class='colhead'>{$lang['userdetails_leechers']}</td>
            </tr>";
    while ($a = mysqli_fetch_assoc($r)) {
        $cat = !empty($a['image']) ? "<img src='{$site_config['pic_base_url']}caticons/" . get_categorie_icons() . "/" . htmlsafechars($a['image']) . '" class="tooltipper" title="' . htmlsafechars($a['cname']) . '" alt="' . htmlsafechars($a['cname']) . '" />' : '';
        $torrents .= "
            <tr>
                <td>$cat</td>
                <td>
                    <a href='{$site_config['baseurl']}/details.php?id=" . (int)$a['id'] . "&amp;hit=1'><b>" . htmlsafechars($a['name']) . "</b></a>
                </td>
                <td class='has-text-right'>{$a['seeders']}</td>
                <td class='has-text-right'>{$a['leechers']}</td>
            </tr>";
    }
    $torrents .= "
        </table>
    </div>";
}
if ($user['ip'] && ($CURUSER['class'] >= UC_STAFF || $user['id'] == $CURUSER['id'])) {
    $dom = @gethostbyaddr($user['ip']);
    $addr = ($dom == $user['ip'] || @gethostbyname($dom) != $user['ip']) ? $user['ip'] : $user['ip'] . ' (' . $dom . ')';
}
if ($user['added'] == 0 or $user['perms'] & bt_options::PERMS_STEALTH) {
    $joindate = "{$lang['userdetails_na']}";
} else {
    $joindate = get_date($user['added'], '');
}
$lastseen = $user['last_access'];
if ($lastseen == 0 or $user['perms'] & bt_options::PERMS_STEALTH) {
    $lastseen = "{$lang['userdetails_never']}";
} else {
    $lastseen = get_date($user['last_access'], '', 0, 1);
}

if ((($user['class'] == UC_MAX or $user['id'] == $CURUSER['id']) || ($user['class'] < UC_MAX) && $CURUSER['class'] == UC_MAX) && isset($_GET['invincible'])) {
    require_once INCL_DIR . 'invincible.php';
    if ($_GET['invincible'] == 'yes') {
        $HTMLOUT .= invincible($id, true, true);
    } elseif ($_GET['invincible'] == 'remove_bypass') {
        $HTMLOUT .= invincible($id, false, false);
    } else {
        $HTMLOUT .= invincible($id, false, false);
    }
}

if ((($user['class'] >= UC_STAFF or $user['id'] == $CURUSER['id']) || ($user['class'] < UC_STAFF) && $CURUSER['class'] >= UC_STAFF) && isset($_GET['stealth'])) {
    require_once INCL_DIR . 'stealth.php';
    if ($_GET['stealth'] == 'yes') {
        $HTMLOUT .= stealth($id);
    } elseif ($_GET['stealth'] == 'no') {
        $HTMLOUT .= stealth($id, false);
    }
}

$country = '';
$countries = countries();
foreach ($countries as $cntry) {
    if ($cntry['id'] == $user['country']) {
        $country = "<img src='{$site_config['pic_base_url']}flag/{$cntry['flagpic']}' alt='" . htmlsafechars($cntry['name']) . "' />";
        break;
    }
}
if (XBT_TRACKER == true) {
    $res = sql_query('SELECT x.fid, x.uploaded, x.downloaded, x.active, x.left, t.added, t.name AS torrentname, t.size, t.category, t.seeders, t.leechers, c.name AS catname, c.image FROM xbt_files_users x LEFT JOIN torrents t ON x.fid = t.id LEFT JOIN categories c ON t.category = c.id WHERE x.uid=' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
    while ($arr = mysqli_fetch_assoc($res)) {
        if ($arr['left'] == '0') {
            $seeding[] = $arr;
        } else {
            $leeching[] = $arr;
        }
    }
} else {
    $res = sql_query('SELECT p.torrent, p.uploaded, p.downloaded, p.seeder, t.added, t.name AS torrentname, t.size, t.category, t.seeders, t.leechers, c.name AS catname, c.image FROM peers p LEFT JOIN torrents t ON p.torrent = t.id LEFT JOIN categories c ON t.category = c.id WHERE p.userid=' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
    while ($arr = mysqli_fetch_assoc($res)) {
        if ($arr['seeder'] == 'yes') {
            $seeding[] = $arr;
        } else {
            $leeching[] = $arr;
        }
    }
}
//==userhits update by pdq
if (!(isset($_GET['hit'])) && $CURUSER['id'] != $user['id']) {
    $res = sql_query('SELECT added FROM userhits WHERE userid =' . sqlesc($CURUSER['id']) . ' AND hitid = ' . sqlesc($id) . ' LIMIT 1') or sqlerr(__FILE__, __LINE__);
    $row = mysqli_fetch_row($res);
    if (!($row[0] > TIME_NOW - 3600)) {
        $hitnumber = $user['hits'] + 1;
        sql_query('UPDATE users SET hits = hits + 1 WHERE id = ' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
        // do update hits userdetails cache
        $update['user_hits'] = ($user['hits'] + 1);
        $mc1->begin_transaction('MyUser_' . $id);
        $mc1->update_row(false, [
            'hits' => $update['user_hits'],
        ]);
        $mc1->commit_transaction($site_config['expires']['curuser']);
        $mc1->begin_transaction('user' . $id);
        $mc1->update_row(false, [
            'hits' => $update['user_hits'],
        ]);
        $mc1->commit_transaction($site_config['expires']['user_cache']);
        sql_query('INSERT INTO userhits (userid, hitid, number, added) VALUES(' . sqlesc($CURUSER['id']) . ', ' . sqlesc($id) . ', ' . sqlesc($hitnumber) . ', ' . sqlesc(TIME_NOW) . ')') or sqlerr(__FILE__, __LINE__);
    }
}
$HTMLOUT = $perms = $stealth = $suspended = $watched_user = $h1_thingie = '';
if (($user['opt1'] & user_options::ANONYMOUS) && ($CURUSER['class'] < UC_STAFF && $user['id'] != $CURUSER['id'])) {
    $HTMLOUT .= "
    <div class='table-wrapper'>
        <table class='table table-bordered table-striped bottom20 two'>
            <tr>
                <td colspan='3' class='has-text-centered'>{$lang['userdetails_anonymous']}</td>
            </tr>";
    if ($user['avatar']) {
        $HTMLOUT .= "
            <tr>
                <td colspan='3' class='has-text-centered'>
                    <img src='" . htmlsafechars($user['avatar']) . "'>
                </td>
            </tr>";
    }
    if ($user['info']) {
        $HTMLOUT .= "
            <tr class='text-top'>
                <td class='has-text-left' colspan='3'>" . format_comment($user['info']) . "</td>
            </tr>";
    }
    $HTMLOUT .= "
            <tr>
                <td colspan='3' class='has-text-centered'>
                    <form method='get' action='{$site_config['baseurl']}/pm_system.php?action=send_message'>
                        <input type='hidden' name='receiver' value='" . (int)$user['id'] . "' />
                        <input type='submit' value='{$lang['userdetails_sendmess']}' />
                    </form>";
    if ($CURUSER['class'] < UC_STAFF && $user['id'] != $CURUSER['id']) {
        echo stdhead($lang['userdetails_anonymoususer']) . $HTMLOUT . stdfoot();
        die;
    }
    $HTMLOUT .= "
                </td>
            </tr>
        </table>
    </div>";
}
$h1_thingie = ((isset($_GET['sn']) || isset($_GET['wu'])) ? '<h1>' . $lang['userdetails_updated'] . '</h1>' : '');
if ($CURUSER['id'] != $user['id'] && $CURUSER['class'] >= UC_STAFF) {
    $suspended .= ($user['suspended'] == 'yes' ? '  <img src="' . $site_config['pic_base_url'] . 'smilies/excl.gif" alt="' . $lang['userdetails_suspended'] . '" class="tooltipper" title="' . $lang['userdetails_suspended'] . '" /> <b>' . $lang['userdetails_usersuspended'] . '</b> <img src="' . $site_config['pic_base_url'] . 'smilies/excl.gif" alt="' . $lang['userdetails_suspended'] . '" class="tooltipper" title="' . $lang['userdetails_suspended'] . '" />' : '');
}
if ($CURUSER['id'] != $user['id'] && $CURUSER['class'] >= UC_STAFF) {
    $watched_user .= ($user['watched_user'] == 0 ? '' : '  <img src="' . $site_config['pic_base_url'] . 'smilies/excl.gif" alt="' . $lang['userdetails_watched'] . '" class="tooltipper" title="' . $lang['userdetails_watched'] . '" /> <b>' . $lang['userdetails_watchlist1'] . ' <a href="staffpanel.php?tool=watched_users" >' . $lang['userdetails_watchlist2'] . '</a></b> <img src="' . $site_config['pic_base_url'] . 'smilies/excl.gif" alt="' . $lang['userdetails_watched'] . '" class="tooltipper" title="' . $lang['userdetails_watched'] . '" />');
}
$perms .= ($CURUSER['class'] >= UC_STAFF ? (($user['perms'] & bt_options::PERMS_NO_IP) ? '  <img src="' . $site_config['pic_base_url'] . 'smilies/super.gif" alt="' . $lang['userdetails_invincible'] . '"  class="tooltipper" title="' . $lang['userdetails_invincible'] . '" />' : '') : '');
$stealth .= ($CURUSER['class'] >= UC_STAFF ? (($user['perms'] & bt_options::PERMS_STEALTH) ? '  <img src="' . $site_config['pic_base_url'] . 'smilies/ninja.gif" alt="' . $lang['userdetails_stelth'] . '"  class="tooltipper" title="' . $lang['userdetails_stelth'] . '" />' : '') : '');
$enabled = $user['enabled'] == 'yes';
$parked = $user['opt1'] & user_options::PARKED ? $lang['userdetails_parked'] : '';

$HTMLOUT .= "
                <div class='has-text-centered'>
                    <h1>" . format_username($user['id']) . "$country$stealth$watched_user$suspended$h1_thingie$perms$parked</h1>
                </div>";
if (!$enabled) {
    $HTMLOUT .= $lang['userdetails_disabled'];
} elseif ($CURUSER['id'] != $user['id']) {
    if (($friends = $mc1->get_value('Friends_' . $id)) === false) {
        $r3 = sql_query('SELECT id FROM friends WHERE userid=' . sqlesc($CURUSER['id']) . ' AND friendid=' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
        $friends = mysqli_num_rows($r3);
        $mc1->cache_value('Friends_' . $id, $friends, $site_config['expires']['user_friends']);
    }
    if (($blocks = $mc1->get_value('Blocks_' . $id)) === false) {
        $r4 = sql_query('SELECT id FROM blocks WHERE userid=' . sqlesc($CURUSER['id']) . ' AND blockid=' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
        $blocks = mysqli_num_rows($r4);
        $mc1->cache_value('Blocks_' . $id, $blocks, $site_config['expires']['user_blocks']);
    }
    if ($friends > 0) {
        $friend_links .= "<a class='bordered margin10 bg-02' href='{$site_config['baseurl']}/friends.php?action=delete&amp;type=friend&amp;targetid=$id'>{$lang['userdetails_remove_friends']}</a>";
    } else {
        $friend_links .= "<a class='bordered margin10 bg-02' href='{$site_config['baseurl']}/friends.php?action=add&amp;type=friend&amp;targetid=$id'>{$lang['userdetails_add_friends']}</a>";
    }
    if ($blocks > 0) {
        $friend_links .= "<a class='bordered margin10 bg-02' href='{$site_config['baseurl']}/friends.php?action=delete&amp;type=block&amp;targetid=$id'>{$lang['userdetails_remove_blocks']}</a>";
    } else {
        $friend_links .= "<a class='bordered margin10 bg-02' href='{$site_config['baseurl']}/friends.php?action=add&amp;type=block&amp;targetid=$id'>{$lang['userdetails_add_blocks']}</a>";
    }
}

if ($CURUSER['class'] >= UC_STAFF) {
    $shitty = '';
    if (($shit_list = $mc1->get_value('shit_list_' . $id)) === false) {
        $check_if_theyre_shitty = sql_query('SELECT suspect FROM shit_list WHERE userid=' . sqlesc($CURUSER['id']) . ' AND suspect=' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
        list($shit_list) = mysqli_fetch_row($check_if_theyre_shitty);
        $mc1->cache_value('shit_list_' . $id, $shit_list, $site_config['expires']['shit_list']);
    }
    if ($shit_list > 0) {
        $shitty_link = "
            <a class='bordered margin10 bg-02' href='{$site_config['baseurl']}/staffpanel.php?tool=shit_list&amp;action=shit_list'>
                Remove from your
                <img class='tooltipper right5' src='./images/smilies/shit.gif' alt='Shit' class='tooltipper' title='Shit' />
            </a>";
    } elseif ($CURUSER['id'] != $user['id']) {
        $shitty_link .= "
            <a class='bordered margin10 bg-02' href='{$site_config['baseurl']}/staffpanel.php?tool=shit_list&amp;action=shit_list&amp;action2=new&amp;shit_list_id={$id}&amp;return_to=userdetails.php?id={$id}'>
                {$lang['userdetails_shit3']}
            </a>";
    }
}

if ($user['donor'] && $CURUSER['id'] == $user['id'] || $CURUSER['class'] == UC_SYSOP) {
    $donoruntil = htmlsafechars($user['donoruntil']);
    if ($donoruntil == '0') {
        $HTMLOUT .= '';
    } else {
        $HTMLOUT .= "<br><b>{$lang['userdetails_donatedtill']} - " . get_date($user['donoruntil'], 'DATE') . '';
        $HTMLOUT .= ' [ ' . mkprettytime($donoruntil - TIME_NOW) . " ] {$lang['userdetails_togo']}...</b><font size='-2'> {$lang['userdetails_renew']} <a class='altlink' href='{$site_config['baseurl']}/donate.php'>{$lang['userdetails_here']}</a>.</font><br><br>";
    }
}
if ($CURUSER['id'] == $user['id']) {
    $edit_profile = "
        <a class='bordered margin10 bg-02' href='{$site_config['baseurl']}/usercp.php?action=default'>{$lang['userdetails_editself']}</a>
        <a class='bordered margin10 bg-02' href='{$site_config['baseurl']}/view_announce_history.php'>{$lang['userdetails_announcements']}</a>";
}
if ($CURUSER['id'] != $user['id']) {
    $sharemark_link .= "
        <a class='bordered margin10 bg-02' href='{$site_config['baseurl']}/sharemarks.php?id=$id'>{$lang['userdetails_sharemarks']}</a>";
}

$HTMLOUT .= "
    <div class='level-center'>
        $sharemark_link
        $shitty_link
        $friend_links
        $edit_profile" . ($CURUSER['class'] === UC_MAX ? $user['perms'] & bt_options::PERMS_NO_IP ? "
        <a class='bordered margin10 bg-02 tooltipper' title='{$lang['userdetails_invincible_def1']}<br>{$lang['userdetails_invincible_def2']}' href='{$site_config['baseurl']}/userdetails.php?id={$id}&amp;invincible=no'>{$lang['userdetails_invincible_remove']}</a>" . ($user['perms'] & bt_options::PERMS_BYPASS_BAN) ? "
        <a class='bordered margin10 bg-02 tooltipper' title='{$lang['userdetails_invincible_def3']}<br>{$lang['userdetails_invincible_def4']}' href='{$site_config['baseurl']}/userdetails.php?id={$id}&amp;invincible=remove_bypass'>{$lang['userdetails_remove_bypass']}</a>" : "
        <a class='bordered margin10 bg-02 tooltipper' title='{$lang['userdetails_invincible_def5']}<br>{$lang['userdetails_invincible_def6']}<br>{$lang['userdetails_invincible_def7']}<br>{$lang['userdetails_invincible_def8']} href='{$site_config['baseurl']}/userdetails.php?id={$id}&amp;invincible=yes'>{$lang['userdetails_add_bypass']}</a>" : "
        <a class='bordered margin10 bg-02 tooltipper' title='{$lang['userdetails_invincible_def9']}<br>{$lang['userdetails_invincible_def0']}' href='{$site_config['baseurl']}/userdetails.php?id={$id}&amp;invincible=yes'>{$lang['userdetails_make_invincible']}</a>" : '');

$stealth = $mc1->get_value('display_stealth' . $CURUSER['id']);
if ($stealth) {
    setSessionVar('is-info', htmlsafechars($user['username']) . " $stealth {$lang['userdetails_in_stelth']}");
}

$HTMLOUT .= ($CURUSER['class'] >= UC_STAFF ? (($user['perms'] & bt_options::PERMS_STEALTH) ? "
        <a class='bordered margin10 bg-02 tooltipper' title='{$lang['userdetails_stelth_def1']}<br>{$lang['userdetails_stelth_def2']}' href='{$site_config['baseurl']}/userdetails.php?id={$id}&amp;stealth=no'>{$lang['userdetails_stelth_disable']}</a>" : "
        <a class='bordered margin10 bg-02 tooltipper' title='{$lang['userdetails_stelth_def1']}<br>{$lang['userdetails_stelth_def2']}' href='{$site_config['baseurl']}/userdetails.php?id={$id}&amp;stealth=yes'>{$lang['userdetails_stelth_enable']}</a>") : '') . "
    </div>";

$HTMLOUT .= "
        <div id='tabs' class='widget'>
            <ul class='level-center'>
                <li class='bordered margin10 bg-02 tablinks' onclick=\"openCity(event, 'torrents')\"><a href='#torrents'>{$lang['userdetails_torrents']}</a></li>
                <li class='bordered margin10 bg-02 tablinks' onclick=\"openCity(event, 'general')\"><a href='#general'>{$lang['userdetails_general']}</a></li>
                <li class='bordered margin10 bg-02 tablinks' onclick=\"openCity(event, 'activity')\"><a href='#activity'>{$lang['userdetails_activity']}</a></li>
                <li class='bordered margin10 bg-02 tablinks' onclick=\"openCity(event, 'comments')\"><a href='#comments'>{$lang['userdetails_usercomments']}</a></li>";
if (($CURUSER['class'] >= UC_STAFF && $user['class'] < $CURUSER['class']) || $CURUSER['class'] === UC_MAX) {
    $HTMLOUT .= "
                <li class='bordered margin10 bg-02 tablinks' onclick=\"openCity(event, 'edit')\"><a href='#edit'>{$lang['userdetails_edit_user']}</a></li>";
}
$HTMLOUT .= '
            </ul>';
$HTMLOUT .= "
            <div class='tabdiv'>
                <div id='torrents' class='table-wrapper tabcontent top20'>
                    <table class='table table-bordered table-striped bottom20 four'>";
if (curuser::$blocks['userdetails_page'] & block_userdetails::FLUSH && $BLOCKS['userdetails_flush_on']) {
    require_once BLOCK_DIR . 'userdetails/flush.php';
}
if (curuser::$blocks['userdetails_page'] & block_userdetails::TRAFFIC && $BLOCKS['userdetails_traffic_on']) {
    require_once BLOCK_DIR . 'userdetails/traffic.php';
}
if (curuser::$blocks['userdetails_page'] & block_userdetails::SHARE_RATIO && $BLOCKS['userdetails_share_ratio_on']) {
    require_once BLOCK_DIR . 'userdetails/shareratio.php';
}
if (curuser::$blocks['userdetails_page'] & block_userdetails::SEEDTIME_RATIO && $BLOCKS['userdetails_seedtime_ratio_on']) {
    require_once BLOCK_DIR . 'userdetails/seedtimeratio.php';
}
if (curuser::$blocks['userdetails_page'] & block_userdetails::TORRENTS_BLOCK && $BLOCKS['userdetails_torrents_block_on']) {
    require_once BLOCK_DIR . 'userdetails/torrents_block.php';
}
if (curuser::$blocks['userdetails_page'] & block_userdetails::COMPLETED && $BLOCKS['userdetails_completed_on']/* && XBT_TRACKER == false*/) {
    require_once BLOCK_DIR . 'userdetails/completed.php';
}
if (curuser::$blocks['userdetails_page'] & block_userdetails::SNATCHED_STAFF && $BLOCKS['userdetails_snatched_staff_on']/* && XBT_TRACKER == false*/) {
    require_once BLOCK_DIR . 'userdetails/snatched_staff.php';
}
if (curuser::$blocks['userdetails_page'] & block_userdetails::CONNECTABLE_PORT && $BLOCKS['userdetails_connectable_port_on']) {
    require_once BLOCK_DIR . 'userdetails/connectable.php';
}
$HTMLOUT .= "
                    </table>
                </div>
                <div id='general' class='table-wrapper tabcontent'>
                    <table class='table table-bordered table-striped bottom20 five'>";

if (($CURUSER['id'] !== $user['id']) && ($CURUSER['class'] >= UC_STAFF)) {
    $the_flip_box = "
        <a name='watched_user'></a>
        <a class='altlink tooltipper' href='#watched_user' onclick=\"javascript:flipBox('3')\" title='{$lang['userdetails_flip1']}'>" . ($user['watched_user'] > 0 ? $lang['userdetails_flip2'] : $lang['userdetails_flip3']) . "<img onclick=\"javascript:flipBox('3')\" src='./images/panel_on.gif' name='b_3' width='8' height='8' alt='{$lang['userdetails_flip1']}' class='tooltipper' title='{$lang['userdetails_flip1']}' /></a>";
    $HTMLOUT .= "
                        <tr>
                            <td class='rowhead w-10'>{$lang['userdetails_watched']}</td>
                            <td class='has-text-left'>" . ($user['watched_user'] > 0 ? "
                                {$lang['userdetails_watched_since']} " . get_date($user['watched_user'], '') :
            $lang['userdetails_not_watched']) . "
                                $the_flip_box
                                <div class='has-text-left' id='box_3'>
                                    <form method='post' action='ajax/member_input.php' name='notes_for_staff'>
                                        <input name='id' type='hidden' value='{$id}' />
                                        <input type='hidden' value='watched_user' name='action' />
                                        {$lang['userdetails_add_watch']}
                                        <input type='radio' class='right5' value='yes' name='add_to_watched_users'" . ($user['watched_user'] > 0 ? ' checked' : '') . " />{$lang['userdetails_yes1']}
                                        <input type='radio' class='right5' value='no' name='add_to_watched_users'" . ($user['watched_user'] == 0 ? ' checked' : '') . "' />{$lang['userdetails_no1']}<br>
                                        <div id='desc_text'>
                                            * {$lang['userdetails_watch_change1']}<br>
                                            {$lang['userdetails_watch_change2']}
                                        </div>
                                        <textarea id='watched_reason' class='w-100' rows='6' name='watched_reason'>" . htmlsafechars($user['watched_user_reason']) . "</textarea>
                                        <div class='has-text-centered'>
                                            <input id='watched_user_button' type='submit' value='{$lang['userdetails_submit']}' class='button' name='watched_user_button' />
                                        </div>
                                    </form>
                                </div>
                            </td>
                        </tr>";

    $the_flip_box_4 = '[ <a name="staff_notes"></a><a class="altlink" href="#staff_notes" onclick="javascript:flipBox(\'4\')" name="b_4" title="' . $lang['userdetails_open_staff'] . '">view <img onclick="javascript:flipBox(\'4\')" src="./images/panel_on.gif" name="b_4" width="8" height="8" alt="' . $lang['userdetails_open_staff'] . '" title="' . $lang['userdetails_open_staff'] . '" /></a> ]';
    $HTMLOUT .= '<tr><td class="rowhead w-10">' . $lang['userdetails_staffnotes'] . '</td><td class="has-text-left">
                            <a class="altlink" href="#staff_notes" onclick="javascript:flipBox(\'6\')" name="b_6" title="' . $lang['userdetails_aev_staffnote'] . '">' . ($user['staff_notes'] !== '' ? '' . $lang['userdetails_vae'] . ' ' : '' . $lang['userdetails_add'] . ' ') . '<img onclick="javascript:flipBox(\'6\')" src="./images/panel_on.gif" name="b_6" width="8" height="8" alt="' . $lang['userdetails_aev_staffnote'] . '" title="' . $lang['userdetails_aev_staffnote'] . '" /></a>
                            <div class="has-text-left" id="box_6">
                            <form method="post" action="ajax/member_input.php" name="notes_for_staff">
                            <input name="id" type="hidden" value="' . (int)$user['id'] . '" />
                            <input type="hidden" value="staff_notes" name="action" id="action" />
                            <textarea id="new_staff_note" class="w-100" rows="6" name="new_staff_note">' . htmlsafechars($user['staff_notes']) . '</textarea>
                            <br><input id="staff_notes_button" type="submit" value="' . $lang['userdetails_submit'] . '" class="button" name="staff_notes_button"/>
                            </form>
                            </div> </td></tr>';
    //=== system comments
    $the_flip_box_7 = '[ <a name="system_comments"></a><a class="altlink" href="#system_comments" onclick="javascript:flipBox(\'7\')"  name="b_7" title="' . $lang['userdetails_open_system'] . '">view <img onclick="javascript:flipBox(\'7\')" src="./images/panel_on.gif" name="b_7" width="8" height="8" alt="' . $lang['userdetails_open_system'] . '" title="' . $lang['userdetails_open_system'] . '" /></a> ]';
    if (!empty($user_stats['modcomment'])) {
        $HTMLOUT .= "<tr><td class='rowhead w-10'>{$lang['userdetails_system']}</td><td class='has-text-left'>" . ($user_stats['modcomment'] != '' ? $the_flip_box_7 . '<div class="has-text-left" id="box_7"><hr>' . format_comment($user_stats['modcomment']) . '</div>' : '') . "</td></tr>";
    }
}
if (curuser::$blocks['userdetails_page'] & block_userdetails::SHOWFRIENDS && $BLOCKS['userdetails_showfriends_on']) {
    require_once BLOCK_DIR . 'userdetails/showfriends.php';
}
if (curuser::$blocks['userdetails_page'] & block_userdetails::JOINED && $BLOCKS['userdetails_joined_on']) {
    require_once BLOCK_DIR . 'userdetails/joined.php';
}
if (curuser::$blocks['userdetails_page'] & block_userdetails::ONLINETIME && $BLOCKS['userdetails_online_time_on']) {
    require_once BLOCK_DIR . 'userdetails/onlinetime.php';
}
if (curuser::$blocks['userdetails_page'] & block_userdetails::BROWSER && $BLOCKS['userdetails_browser_on']) {
    require_once BLOCK_DIR . 'userdetails/browser.php';
}
if (curuser::$blocks['userdetails_page'] & block_userdetails::BIRTHDAY && $BLOCKS['userdetails_birthday_on']) {
    require_once BLOCK_DIR . 'userdetails/birthday.php';
}
if (curuser::$blocks['userdetails_page'] & block_userdetails::CONTACT_INFO && $BLOCKS['userdetails_contact_info_on']) {
    require_once BLOCK_DIR . 'userdetails/contactinfo.php';
}
if (curuser::$blocks['userdetails_page'] & block_userdetails::IPHISTORY && $BLOCKS['userdetails_iphistory_on']) {
    require_once BLOCK_DIR . 'userdetails/iphistory.php';
}
if (curuser::$blocks['userdetails_page'] & block_userdetails::AVATAR && $BLOCKS['userdetails_avatar_on']) {
    require_once BLOCK_DIR . 'userdetails/avatar.php';
}
if (curuser::$blocks['userdetails_page'] & block_userdetails::USERCLASS && $BLOCKS['userdetails_userclass_on']) {
    require_once BLOCK_DIR . 'userdetails/userclass.php';
}
if (curuser::$blocks['userdetails_page'] & block_userdetails::GENDER && $BLOCKS['userdetails_gender_on']) {
    require_once BLOCK_DIR . 'userdetails/gender.php';
}
if (curuser::$blocks['userdetails_page'] & block_userdetails::USERINFO && $BLOCKS['userdetails_userinfo_on']) {
    require_once BLOCK_DIR . 'userdetails/userinfo.php';
}
if (curuser::$blocks['userdetails_page'] & block_userdetails::REPORT_USER && $BLOCKS['userdetails_report_user_on']) {
    require_once BLOCK_DIR . 'userdetails/report.php';
}
if (curuser::$blocks['userdetails_page'] & block_userdetails::USERSTATUS && $BLOCKS['userdetails_user_status_on']) {
    require_once BLOCK_DIR . 'userdetails/userstatus.php';
}
if (curuser::$blocks['userdetails_page'] & block_userdetails::SHOWPM && $BLOCKS['userdetails_showpm_on']) {
    require_once BLOCK_DIR . 'userdetails/showpm.php';
}
$HTMLOUT .= '</table></div>';
$HTMLOUT .= "<div id='activity' class='table-wrapper tabcontent'>";
$HTMLOUT .= "<table class='table table-bordered table-striped bottom20 six'>";
//==where is user now
if (!empty($user['where_is'])) {
    $HTMLOUT .= "<tr><td class='rowhead w-10' width='1%'>{$lang['userdetails_location']}</td><td class='has-text-left' width='99%'>" . format_urls($user['where_is']) . "</td></tr>";
}
//==
$moodname = (isset($mood['name'][ $user['mood'] ]) ? htmlsafechars($mood['name'][ $user['mood'] ]) : $lang['userdetails_neutral']);
$moodpic = (isset($mood['image'][ $user['mood'] ]) ? htmlsafechars($mood['image'][ $user['mood'] ]) : 'noexpression.gif');
$HTMLOUT .= '<tr><td class="rowhead w-10">' . $lang['userdetails_currentmood'] . '</td><td class="has-text-left"><span class="tool">
       <a href="javascript:;" onclick="PopUp(\'usermood.php\',\'' . $lang['userdetails_mood'] . '\',530,500,1,1);">
       <img src="' . $site_config['pic_base_url'] . 'smilies/' . $moodpic . '" alt="' . $moodname . '" border="0" />
       <span class="tip">' . htmlsafechars($user['username']) . ' ' . $moodname . ' !</span></a></span></td></tr>';
if (curuser::$blocks['userdetails_page'] & block_userdetails::SEEDBONUS && $BLOCKS['userdetails_seedbonus_on']) {
    require_once BLOCK_DIR . 'userdetails/seedbonus.php';
}
if (curuser::$blocks['userdetails_page'] & block_userdetails::IRC_STATS && $BLOCKS['userdetails_irc_stats_on']) {
    require_once BLOCK_DIR . 'userdetails/irc.php';
}
if (curuser::$blocks['userdetails_page'] & block_userdetails::REPUTATION && $BLOCKS['userdetails_reputation_on']) {
    require_once BLOCK_DIR . 'userdetails/reputation.php';
}
if (curuser::$blocks['userdetails_page'] & block_userdetails::PROFILE_HITS && $BLOCKS['userdetails_profile_hits_on']) {
    require_once BLOCK_DIR . 'userdetails/userhits.php';
}
if (curuser::$blocks['userdetails_page'] & block_userdetails::FREESTUFFS && $BLOCKS['userdetails_freestuffs_on'] && XBT_TRACKER == false) {
    require_once BLOCK_DIR . 'userdetails/freestuffs.php';
}
if (curuser::$blocks['userdetails_page'] & block_userdetails::COMMENTS && $BLOCKS['userdetails_comments_on']) {
    require_once BLOCK_DIR . 'userdetails/comments.php';
}
if (curuser::$blocks['userdetails_page'] & block_userdetails::FORUMPOSTS && $BLOCKS['userdetails_forumposts_on']) {
    require_once BLOCK_DIR . 'userdetails/forumposts.php';
}
if (curuser::$blocks['userdetails_page'] & block_userdetails::INVITEDBY && $BLOCKS['userdetails_invitedby_on']) {
    require_once BLOCK_DIR . 'userdetails/invitedby.php';
}
$HTMLOUT .= '</table></div>';
$HTMLOUT .= "<div id='comments' class='table-wrapper tabcontent'>";
if (curuser::$blocks['userdetails_page'] & block_userdetails::USERCOMMENTS && $BLOCKS['userdetails_user_comments_on']) {
    require_once BLOCK_DIR . 'userdetails/usercomments.php';
}
$HTMLOUT .= '</div>';
$HTMLOUT .= "<div id='edit' class='table-wrapper tabcontent'>";
//==end blocks


if (($CURUSER['class'] >= UC_STAFF && $user['class'] < $CURUSER['class']) || $CURUSER['class'] === UC_MAX) {
    $HTMLOUT .= "<form method='post' action='staffpanel.php?tool=modtask'>";
    require_once CLASS_DIR . 'validator.php';
    $HTMLOUT .= validatorForm('ModTask_' . $user['id']);
    $postkey = PostKey([
        $user['id'],
        $CURUSER['id'],
    ]);
    $HTMLOUT .= "<input type='hidden' name='action' value='edituser' />";
    $HTMLOUT .= "<input type='hidden' name='userid' value='$id' />";
    $HTMLOUT .= "<input type='hidden' name='postkey' value='$postkey' />";
    $HTMLOUT .= "<input type='hidden' name='returnto' value='userdetails.php?id=$id' />";
    $HTMLOUT .= "
         <table class='table table-bordered table-striped bottom20 seven'>";
    $HTMLOUT .= "<tr><td class='rowhead w-10'>{$lang['userdetails_title']}</td><td colspan='3' class='has-text-left'><input type='text' class='w-100' name='title' value='" . htmlsafechars($user['title']) . "' /></td></tr>";
    $avatar = htmlsafechars($user['avatar']);
    $HTMLOUT .= "<tr><td class='rowhead w-10'>{$lang['userdetails_avatar_url']}</td><td colspan='3' class='has-text-left'><input type='text' class='w-100' name='avatar' value='$avatar' /></td></tr>";

    $HTMLOUT .= "<tr>
    <td class='rowhead w-10'>{$lang['userdetails_signature_rights']}</td>
    <td colspan='3' class='has-text-left'>
        <input name='signature_post' value='yes' type='radio'" . ($user['signature_post'] == 'yes' ? "    checked='checked'" : '') . " />{$lang['userdetails_yes']}
        <input name='signature_post' value='no' type='radio'" . ($user['signature_post'] == 'no' ? " checked='checked'" : '') . " />{$lang['userdetails_disable_signature']}
    </td></tr>
   <!--<tr><td class='rowhead w-10'>{$lang['userdetails_view_signature']}</td>
   <td colspan='3' class='has-text-left'><input name='signatures' value='yes' type='radio'" . ($user['signatures'] == 'yes' ? " checked='checked'" : '') . " />{$lang['userdetails_yes']}
   <input name='signatures' value='no' type='radio'" . ($user['signatures'] == 'no' ? " checked='checked'" : '') . " /></td>
   </tr>-->
               <tr>
                      <td class='rowhead w-10'>{$lang['userdetails_signature']}</td>
                      <td colspan='3' class='has-text-left'><textarea class='w-100' rows='2' name='signature'>" . htmlsafechars($user['signature']) . "</textarea></td>
                </tr>

                <tr>
                      <td class='rowhead w-10'>{$lang['userdetails_gtalk']}</td>
                      <td colspan='3' class='has-text-left'><input type='text' class='w-100' name='google_talk' value='" . htmlsafechars($user['google_talk']) . "' /></td>
                </tr>
                <tr>
                      <td class='rowhead w-10'>{$lang['userdetails_msn']}</td>
                      <td colspan='3' class='has-text-left'><input type='text' class='w-100' name='msn' value='" . htmlsafechars($user['msn']) . "' /></td>
                </tr>
                <tr>
                      <td class='rowhead w-10'>{$lang['userdetails_aim']}</td>
                      <td colspan='3' class='has-text-left'><input type='text' class='w-100' name='aim' value='" . htmlsafechars($user['aim']) . "' /></td>
                </tr>
                <tr>
                      <td class='rowhead w-10'>{$lang['userdetails_yahoo']}</td>
                      <td colspan='3' class='has-text-left'><input type='text' class='w-100' name='yahoo' value='" . htmlsafechars($user['yahoo']) . "' /></td>
                </tr>
                <tr>
                      <td class='rowhead w-10'>{$lang['userdetails_icq']}</td>
                      <td colspan='3' class='has-text-left'><input type='text' class='w-100' name='icq' value='" . htmlsafechars($user['icq']) . "' /></td>
                </tr>
                <tr>
                      <td class='rowhead w-10'>{$lang['userdetails_website']}</td>
                      <td colspan='3' class='has-text-left'><input type='text' class='w-100' name='website' value='" . htmlsafechars($user['website']) . "' /></td>
                </tr>";

    if ($CURUSER['class'] === UC_MAX) {
        $donor = $user['donor'] == 'yes';
        $HTMLOUT .= "
                <tr>
                    <td class='rowhead w-10' class='has-text-right'><b>
                        {$lang['userdetails_donor']}</b>
                    </td>
                    <td colspan='2' class='has-text-centered'>";
        if ($donor) {
            $donoruntil = (int)$user['donoruntil'];
            if ($donoruntil == '0') {
                $HTMLOUT .= $lang['userdetails_arbitrary'];
            } else {
                $HTMLOUT .= $lang['userdetails_donor2'] . " " . get_date($user['donoruntil'], 'DATE') . " [ " . mkprettytime($donoruntil - TIME_NOW) . " ] {$lang['userdetails_togo']}";
            }
        } else {
            $HTMLOUT .= "
                    <div>{$lang['userdetails_dfor']}</div>
                     <select name='donorlength' class='bottom10 w-100'>
                        <option value='0'>------</option>
                        <option value='4'>1 {$lang['userdetails_month']}</option>
                        <option value='6'>6 {$lang['userdetails_weeks']}</option>
                        <option value='8'>2 {$lang['userdetails_months']}</option>
                        <option value='10'>10 {$lang['userdetails_weeks']}</option>
                        <option value='12'>3 {$lang['userdetails_months']}</option>
                        <option value='255'>{$lang['userdetails_unlimited']}</option>
                    </select>";
        }
        $HTMLOUT .= "
                    <div>{$lang['userdetails_cdonation']}</div>
                    <input class='w-100' type='text' name='donated' value='" . htmlsafechars($user['donated']) . "' />
                    <div class='top10 size_5 has-text-centered'>{$lang['userdetails_tdonations']} " . number_format(htmlsafechars($user['total_donated']), 2) . '</div>';
        if ($donor) {
            $HTMLOUT .= "
                    <div>{$lang['userdetails_adonor']}</div>
                    <select name='donorlengthadd' class='w-100'>
                        <option value='0'>------</option>
                        <option value='4'>1 {$lang['userdetails_month']}</option>
                        <option value='6'>6 {$lang['userdetails_weeks']}</option>
                        <option value='8'>2 {$lang['userdetails_months']}</option>
                        <option value='10'>10 {$lang['userdetails_weeks']}</option>
                        <option value='12'>3 {$lang['userdetails_months']}</option>
                        <option value='255'>{$lang['userdetails_unlimited']}</option>
                    </select>
                    <div>{$lang['userdetails_rdonor']}</div>
                    <input name='donor' value='no' type='checkbox' />
                    <div>{$lang['userdetails_bad']}</div>";
        }
        $HTMLOUT .= "
                    </td>
                </tr>";
    }
    if ($CURUSER['class'] === UC_STAFF && $user['class'] > UC_VIP) {
        $HTMLOUT .= "<input type='hidden' name='class' value='{$user['class']}' />";
    } else {
        $HTMLOUT .= "<tr><td class='rowhead w-10'>Class</td><td colspan='3' class='has-text-left'><select name='class' class='w-100'>";
        if ($CURUSER['class'] === UC_MAX) {
            $maxclass = UC_SYSOP;
        } elseif ($CURUSER['class'] === UC_STAFF) {
            $maxclass = UC_VIP;
        } else {
            $maxclass = $CURUSER['class'] - 1;
        }
        for ($i = 0; $i <= $maxclass; ++$i) {
            $HTMLOUT .= "<option value='$i'" . ($user['class'] == $i ? " selected='selected'" : '') . '>' . get_user_class_name($i) . "</option>";
        }
        $HTMLOUT .= "</select></td></tr>";
    }
    $supportfor = htmlsafechars($user['supportfor']);
    //$HTMLOUT.= "<tr><td class='rowhead w-10'>{$lang['userdetails_support']}</td><td colspan='3' class='has-text-left'><input type='checkbox' name='support' value='yes'" . (($user['opt1'] & user_options::SUPPORT) ? " checked='checked'" : "") . " />{$lang['userdetails_yes']}</td></tr>";
    $HTMLOUT .= "<tr><td class='rowhead w-10'>{$lang['userdetails_support']}</td><td colspan='3' class='has-text-left'><input type='radio' name='support' value='yes'" . ($user['support'] == 'yes' ? " checked='checked'" : '') . " />{$lang['userdetails_yes']}<input type='radio' name='support' value='no'" . ($user['support'] == 'no' ? " checked='checked'" : '') . " />{$lang['userdetails_no']}</td></tr>";
    $HTMLOUT .= "<tr><td class='rowhead w-10'>{$lang['userdetails_supportfor']}</td><td colspan='3' class='has-text-left'><textarea class='w-100' rows='2' name='supportfor'>{$supportfor}</textarea></td></tr>";
    $modcomment = htmlsafechars($user_stats['modcomment']);
    if ($CURUSER['class'] < UC_SYSOP) {
        $HTMLOUT .= "<tr><td class='rowhead w-10'>{$lang['userdetails_comment']}</td><td colspan='3' class='has-text-left'><textarea class='w-100' rows='6' name='modcomment' readonly='readonly'>$modcomment</textarea></td></tr>";
    } else {
        $HTMLOUT .= "<tr><td class='rowhead w-10'>{$lang['userdetails_comment']}</td><td colspan='3' class='has-text-left'><textarea class='w-100' rows='6' name='modcomment'>$modcomment</textarea></td></tr>";
    }
    $HTMLOUT .= "<tr><td class='rowhead w-10'>{$lang['userdetails_add_comment']}</td><td colspan='3' class='has-text-left'><textarea class='w-100' rows='2' name='addcomment'></textarea></td></tr>";
    //=== bonus comment
    $bonuscomment = htmlsafechars($user_stats['bonuscomment']);
    $HTMLOUT .= "<tr><td class='rowhead w-10'>{$lang['userdetails_bonus_comment']}</td><td colspan='3' class='has-text-left'><textarea class='w-100' rows='6' name='bonuscomment' readonly='readonly'>$bonuscomment</textarea></td></tr>";
    //==end
    $HTMLOUT .= "<tr><td class='rowhead w-10'>{$lang['userdetails_enabled']}</td><td colspan='3' class='has-text-left'><input name='enabled' value='yes' type='radio'" . ($enabled ? " checked='checked'" : '') . " />{$lang['userdetails_yes']} <input name='enabled' value='no' type='radio'" . (!$enabled ? " checked='checked'" : '') . " />{$lang['userdetails_no']}</td></tr>";
    if ($CURUSER['class'] >= UC_STAFF && XBT_TRACKER == false) {
        $HTMLOUT .= "
                <tr>
                    <td class='rowhead w-10'>{$lang['userdetails_freeleech_slots']}</td>
                    <td colspan='3' class='has-text-left'>
                        <input class='w-100' type='text' name='freeslots' value='" . (int)$user['freeslots'] . "' />
                    </td>
                </tr>";
    }
    if ($CURUSER['class'] >= UC_ADMINISTRATOR && XBT_TRACKER == false) {
        $free_switch = $user['free_switch'] != 0;
        $HTMLOUT .= "<tr><td class='rowhead w-10'" . (!$free_switch ? ' rowspan="2"' : '') . ">{$lang['userdetails_freeleech_status']}</td>
                <td class='has-text-left' width='20%'>" . ($free_switch ? "<input name='free_switch' value='42' type='radio' />{$lang['userdetails_remove_freeleech']}" : $lang['userdetails_no_freeleech']) . "</td>";
        if ($free_switch) {
            if ($user['free_switch'] == 1) {
                $HTMLOUT .= '<td class="has-text-centered">(' . $lang['userdetails_unlimited_d'] . ')</td></tr>';
            } else {
                $HTMLOUT .= "<td class='has-text-centered'>{$lang['userdetails_until']} " . get_date($user['free_switch'], 'DATE') . ' (' . mkprettytime($user['free_switch'] - TIME_NOW) . " {$lang['userdetails_togo']})</td></tr>";
            }
        } else {
            $HTMLOUT .= '<td>' . $lang['userdetails_freeleech_for'] . ' <select name="free_switch" class="w-100">
         <option value="0">------</option>
         <option value="1">1 ' . $lang['userdetails_week'] . '</option>
         <option value="2">2 ' . $lang['userdetails_weeks'] . '</option>
         <option value="4">4 ' . $lang['userdetails_weeks'] . '</option>
         <option value="8">8 ' . $lang['userdetails_weeks'] . '</option>
         <option value="255">' . $lang['userdetails_unlimited'] . '</option>
         </select></td></tr>
         <tr><td colspan="3" class="has-text-left">' . $lang['userdetails_pm_comment'] . ':<input type="text" class="w-100" name="free_pm" /></td></tr>';
        }
    }
    //==XBT - Can Leech
    if (XBT_TRACKER == true) {
        $HTMLOUT .= "<tr><td class='rowhead w-10'>{$lang['userdetails_canleech']}</td><td class='row' colspan='3' class='has-text-left'><input type='radio' name='can_leech' value='1' " . ($user['can_leech'] == 1 ? " checked='checked'" : '') . " />{$lang['userdetails_yes']} <input type='radio' name='can_leech' value='0' " . ($user['can_leech'] == 0 ? " checked='checked'" : '') . " />{$lang['userdetails_no']}</td></tr>";
    }
    //==Download disable
    if ($CURUSER['class'] >= UC_STAFF && XBT_TRACKER == false) {
        $downloadpos = $user['downloadpos'] != 1;
        $HTMLOUT .= "<tr><td class='rowhead w-10'" . (!$downloadpos ? ' rowspan="2"' : '') . ">{$lang['userdetails_dpos']}</td>
               <td class='has-text-left' width='20%'>" . ($downloadpos ? "<input name='downloadpos' value='42' type='radio' />{$lang['userdetails_remove_download_d']}" : $lang['userdetails_no_disablement']) . "</td>";
        if ($downloadpos) {
            if ($user['downloadpos'] == 0) {
                $HTMLOUT .= '<td class="has-text-centered">(' . $lang['userdetails_unlimited_d'] . ')</td></tr>';
            } else {
                $HTMLOUT .= "<td class='has-text-centered'>{$lang['userdetails_until']} " . get_date($user['downloadpos'], 'DATE') . ' (' . mkprettytime($user['downloadpos'] - TIME_NOW) . " {$lang['userdetails_togo']})</td></tr>";
            }
        } else {
            $HTMLOUT .= '<td>' . $lang['userdetails_disable_for'] . ' <select name="downloadpos" class="w-100">
        <option value="0">------</option>
        <option value="1">1 ' . $lang['userdetails_week'] . '</option>
        <option value="2">2 ' . $lang['userdetails_weeks'] . '</option>
        <option value="4">4 ' . $lang['userdetails_weeks'] . '</option>
        <option value="8">8 ' . $lang['userdetails_weeks'] . '</option>
        <option value="255">' . $lang['userdetails_unlimited'] . '</option>
        </select></td></tr>
        <tr><td colspan="3" class="has-text-left">' . $lang['userdetails_pm_comment'] . ':<input type="text" class="w-100" name="disable_pm" /></td></tr>';
        }
    }
    //==Upload disable
    if ($CURUSER['class'] >= UC_STAFF) {
        $uploadpos = $user['uploadpos'] != 1;
        $HTMLOUT .= "<tr><td class='rowhead w-10'" . (!$uploadpos ? ' rowspan="2"' : '') . ">{$lang['userdetails_upos']}</td>
               <td class='has-text-left' width='20%'>" . ($uploadpos ? "<input name='uploadpos' value='42' type='radio' />{$lang['userdetails_remove_upload_d']}" : $lang['userdetails_no_disablement']) . "</td>";
        if ($uploadpos) {
            if ($user['uploadpos'] == 0) {
                $HTMLOUT .= '<td class="has-text-centered">(' . $lang['userdetails_unlimited_d'] . ')</td></tr>';
            } else {
                $HTMLOUT .= "<td class='has-text-centered'>{$lang['userdetails_until']} " . get_date($user['uploadpos'], 'DATE') . ' (' . mkprettytime($user['uploadpos'] - TIME_NOW) . " {$lang['userdetails_togo']})</td></tr>";
            }
        } else {
            $HTMLOUT .= '<td>' . $lang['userdetails_disable_for'] . ' <select name="uploadpos" class="w-100">
        <option value="0">------</option>
        <option value="1">1 ' . $lang['userdetails_week'] . '</option>
        <option value="2">2 ' . $lang['userdetails_weeks'] . '</option>
        <option value="4">4 ' . $lang['userdetails_weeks'] . '</option>
        <option value="8">8 ' . $lang['userdetails_weeks'] . '</option>
        <option value="255">' . $lang['userdetails_unlimited'] . '</option>
        </select></td></tr>
        <tr><td colspan="3" class="has-text-left">' . $lang['userdetails_pm_comment'] . ':<input type="text" class="w-100" name="updisable_pm" /></td></tr>';
        }
    }
    //==
    //==Pm disable
    if ($CURUSER['class'] >= UC_STAFF) {
        $sendpmpos = $user['sendpmpos'] != 1;
        $HTMLOUT .= "<tr><td class='rowhead w-10'" . (!$sendpmpos ? ' rowspan="2"' : '') . ">{$lang['userdetails_pmpos']}</td>
               <td class='has-text-left' width='20%'>" . ($sendpmpos ? "<input name='sendpmpos' value='42' type='radio' />{$lang['userdetails_remove_pm_d']}" : $lang['userdetails_no_disablement']) . "</td>";
        if ($sendpmpos) {
            if ($user['sendpmpos'] == 0) {
                $HTMLOUT .= '<td class="has-text-centered">(' . $lang['userdetails_unlimited_d'] . ')</td></tr>';
            } else {
                $HTMLOUT .= "<td class='has-text-centered'>{$lang['userdetails_until']} " . get_date($user['sendpmpos'], 'DATE') . ' (' . mkprettytime($user['sendpmpos'] - TIME_NOW) . " {$lang['userdetails_togo']})</td></tr>";
            }
        } else {
            $HTMLOUT .= '<td>' . $lang['userdetails_disable_for'] . ' <select name="sendpmpos" class="w-100">
        <option value="0">------</option>
        <option value="1">1 ' . $lang['userdetails_week'] . '</option>
        <option value="2">2 ' . $lang['userdetails_weeks'] . '</option>
        <option value="4">4 ' . $lang['userdetails_weeks'] . '</option>
        <option value="8">8 ' . $lang['userdetails_weeks'] . '</option>
        <option value="255">' . $lang['userdetails_unlimited'] . '</option>
        </select></td></tr>
        <tr><td colspan="3" class="has-text-left">' . $lang['userdetails_pm_comment'] . ':<input type="text" class="w-100" name="pmdisable_pm" /></td></tr>';
        }
    }
    //==AJAX Chat disable
    if ($CURUSER['class'] >= UC_STAFF) {
        $chatpost = $user['chatpost'] != 1;
        $HTMLOUT .= "<tr><td class='rowhead w-10'" . (!$chatpost ? ' rowspan="2"' : '') . ">{$lang['userdetails_chatpos']}</td>
               <td class='has-text-left' width='20%'>" . ($chatpost ? "<input name='chatpost' value='42' type='radio' />{$lang['userdetails_remove_shout_d']}" : $lang['userdetails_no_disablement']) . "</td>";
        if ($chatpost) {
            if ($user['chatpost'] == 0) {
                $HTMLOUT .= '<td class="has-text-centered">(' . $lang['userdetails_unlimited_d'] . ')</td></tr>';
            } else {
                $HTMLOUT .= "<td class='has-text-centered'>{$lang['userdetails_until']} " . get_date($user['chatpost'], 'DATE') . ' (' . mkprettytime($user['chatpost'] - TIME_NOW) . " {$lang['userdetails_togo']})</td></tr>";
            }
        } else {
            $HTMLOUT .= '<td>' . $lang['userdetails_disable_for'] . ' <select name="chatpost" class="w-100">
        <option value="0">------</option>
        <option value="1">1 ' . $lang['userdetails_week'] . '</option>
        <option value="2">2 ' . $lang['userdetails_weeks'] . '</option>
        <option value="4">4 ' . $lang['userdetails_weeks'] . '</option>
        <option value="8">8 ' . $lang['userdetails_weeks'] . '</option>
        <option value="255">' . $lang['userdetails_unlimited'] . '</option>
        </select></td></tr>
        <tr><td colspan="3" class="has-text-left">' . $lang['userdetails_pm_comment'] . ':<input type="text" class="w-100" name="chatdisable_pm" /></td></tr>';
        }
    }
    //==Avatar disable
    if ($CURUSER['class'] >= UC_STAFF) {
        $avatarpos = $user['avatarpos'] != 1;
        $HTMLOUT .= "<tr><td class='rowhead w-10'" . (!$avatarpos ? ' rowspan="2"' : '') . ">{$lang['userdetails_avatarpos']}</td>
          <td class='has-text-left' width='20%'>" . ($avatarpos ? "<input name='avatarpos' value='42' type='radio' />{$lang['userdetails_remove_avatar_d']}" : $lang['userdetails_no_disablement']) . "</td>";
        if ($avatarpos) {
            if ($user['avatarpos'] == 0) {
                $HTMLOUT .= '<td class="has-text-centered">(' . $lang['userdetails_unlimited_d'] . ')</td></tr>';
            } else {
                $HTMLOUT .= "<td class='has-text-centered'>{$lang['userdetails_until']} " . get_date($user['avatarpos'], 'DATE') . ' (' . mkprettytime($user['avatarpos'] - TIME_NOW) . " {$lang['userdetails_togo']})</td></tr>";
            }
        } else {
            $HTMLOUT .= '<td>' . $lang['userdetails_disable_for'] . ' <select name="avatarpos" class="w-100">
        <option value="0">------</option>
        <option value="1">1 ' . $lang['userdetails_week'] . '</option>
        <option value="2">2 ' . $lang['userdetails_weeks'] . '</option>
        <option value="4">4 ' . $lang['userdetails_weeks'] . '</option>
        <option value="8">8 ' . $lang['userdetails_weeks'] . '</option>
        <option value="255">' . $lang['userdetails_unlimited'] . '</option>
        </select></td></tr>
        <tr><td colspan="3" class="has-text-left">' . $lang['userdetails_pm_comment'] . ':<input type="text" class="w-100" name="avatardisable_pm" /></td></tr>';
        }
    }
    //==Immunity
    if ($CURUSER['class'] >= UC_STAFF) {
        $immunity = $user['immunity'] != 0;
        $HTMLOUT .= "<tr><td class='rowhead w-10'" . (!$immunity ? ' rowspan="2"' : '') . ">{$lang['userdetails_immunity']}</td>
               <td class='has-text-left' width='20%'>" . ($immunity ? "<input name='immunity' value='42' type='radio' />{$lang['userdetails_remove_immunity']}" : $lang['userdetails_no_immunity']) . "</td>";
        if ($immunity) {
            if ($user['immunity'] == 1) {
                $HTMLOUT .= '<td class="has-text-centered">(' . $lang['userdetails_unlimited_d'] . ')</td></tr>';
            } else {
                $HTMLOUT .= "<td class='has-text-centered'>{$lang['userdetails_until']} " . get_date($user['immunity'], 'DATE') . ' (' . mkprettytime($user['immunity'] - TIME_NOW) . " {$lang['userdetails_togo']})</td></tr>";
            }
        } else {
            $HTMLOUT .= '<td>' . $lang['userdetails_immunity_for'] . ' <select name="immunity" class="w-100">
        <option value="0">------</option>
        <option value="1">1 ' . $lang['userdetails_week'] . '</option>
        <option value="2">2 ' . $lang['userdetails_weeks'] . '</option>
        <option value="4">4 ' . $lang['userdetails_weeks'] . '</option>
        <option value="8">8 ' . $lang['userdetails_weeks'] . '</option>
        <option value="255">' . $lang['userdetails_unlimited'] . '</option>
        </select></td></tr>
        <tr><td colspan="3" class="has-text-left">' . $lang['userdetails_pm_comment'] . ':<input type="text" class="w-100" name="immunity_pm" /></td></tr>';
        }
    }
    //==End
    //==Leech Warnings
    if ($CURUSER['class'] >= UC_STAFF) {
        $leechwarn = $user['leechwarn'] != 0;
        $HTMLOUT .= "<tr><td class='rowhead w-10'" . (!$leechwarn ? ' rowspan="2"' : '') . ">{$lang['userdetails_leechwarn']}</td>
               <td class='has-text-left' width='20%'>" . ($leechwarn ? "<input name='leechwarn' value='42' type='radio' />{$lang['userdetails_remove_leechwarn']}" : $lang['userdetails_no_leechwarn']) . "</td>";
        if ($leechwarn) {
            if ($user['leechwarn'] == 1) {
                $HTMLOUT .= '<td class="has-text-centered">(' . $lang['userdetails_unlimited_d'] . ')</td></tr>';
            } else {
                $HTMLOUT .= "<td class='has-text-centered'>{$lang['userdetails_until']} " . get_date($user['leechwarn'], 'DATE') . ' (' . mkprettytime($user['leechwarn'] - TIME_NOW) . " {$lang['userdetails_togo']})</td></tr>";
            }
        } else {
            $HTMLOUT .= '<td>' . $lang['userdetails_leechwarn_for'] . ' <select name="leechwarn" class="w-100">
        <option value="0">------</option>
        <option value="1">1 ' . $lang['userdetails_week'] . '</option>
        <option value="2">2 ' . $lang['userdetails_weeks'] . '</option>
        <option value="4">4 ' . $lang['userdetails_weeks'] . '</option>
        <option value="8">8 ' . $lang['userdetails_weeks'] . '</option>
        <option value="255">' . $lang['userdetails_unlimited'] . '</option>
        </select></td></tr>
        <tr><td colspan="3" class="has-text-left">' . $lang['userdetails_pm_comment'] . ':<input type="text" class="w-100" name="leechwarn_pm" /></td></tr>';
        }
    }
    //==End
    //==Warnings
    if ($CURUSER['class'] >= UC_STAFF) {
        $warned = $user['warned'] != 0;
        $HTMLOUT .= "<tr><td class='rowhead w-10'" . (!$warned ? ' rowspan="2"' : '') . ">{$lang['userdetails_warned']}</td>
               <td class='has-text-left' width='20%'>" . ($warned ? "<input name='warned' value='42' type='radio' />{$lang['userdetails_remove_warned']}" : $lang['userdetails_no_warning']) . "</td>";
        if ($warned) {
            if ($user['warned'] == 1) {
                $HTMLOUT .= '<td class="has-text-centered">(' . $lang['userdetails_unlimited_d'] . ')</td></tr>';
            } else {
                $HTMLOUT .= "<td class='has-text-centered'>{$lang['userdetails_until']} " . get_date($user['warned'], 'DATE') . ' (' . mkprettytime($user['warned'] - TIME_NOW) . " {$lang['userdetails_togo']})</td></tr>";
            }
        } else {
            $HTMLOUT .= '<td>' . $lang['userdetails_warn_for'] . '<select name="warned" class="w-100">
        <option value="0">' . $lang['userdetails_warn0'] . '</option>
        <option value="1">' . $lang['userdetails_warn1'] . '</option>
        <option value="2">' . $lang['userdetails_warn2'] . '</option>
        <option value="4">' . $lang['userdetails_warn4'] . '</option>
        <option value="8">' . $lang['userdetails_warn8'] . '</option>
        <option value="255">' . $lang['userdetails_warninf'] . '</option>
        </select></td></tr>
        <tr><td colspan="3" class="has-text-left">' . $lang['userdetails_pm_comm'] . '<input type="text" class="w-100" name="warned_pm" /></td></tr>';
        }
    }
    //==End
    //==Games disable
    if ($CURUSER['class'] >= UC_STAFF) {
        $game_access = $user['game_access'] != 1;
        $HTMLOUT .= "<tr><td class='rowhead w-10'" . (!$game_access ? ' rowspan="2"' : '') . ">{$lang['userdetails_games']}</td>
           <td class='has-text-left' width='20%'>" . ($game_access ? "<input name='game_access' value='42' type='radio' />{$lang['userdetails_remove_game_d']}" : $lang['userdetails_no_disablement']) . "</td>";
        if ($game_access) {
            if ($user['game_access'] == 0) {
                $HTMLOUT .= '<td class="has-text-centered">(' . $lang['userdetails_unlimited_d'] . ')</td></tr>';
            } else {
                $HTMLOUT .= "<td class='has-text-centered'>{$lang['userdetails_until']} " . get_date($user['game_access'], 'DATE') . ' (' . mkprettytime($user['game_access'] - TIME_NOW) . " {$lang['userdetails_togo']})</td></tr>";
            }
        } else {
            $HTMLOUT .= '<td>' . $lang['userdetails_disable_for'] . ' <select name="game_access" class="w-100">
        <option value="0">------</option>
        <option value="1">1 ' . $lang['userdetails_week'] . '</option>
        <option value="2">2 ' . $lang['userdetails_weeks'] . '</option>
        <option value="4">4 ' . $lang['userdetails_weeks'] . '</option>
        <option value="8">8 ' . $lang['userdetails_weeks'] . '</option>
        <option value="255">' . $lang['userdetails_unlimited'] . '</option>
        </select></td></tr>
        <tr><td colspan="3" class="has-text-left">' . $lang['userdetails_pm_comment'] . ':<input type="text" class="w-100" name="game_disable_pm" /></td></tr>';
        }
    }
    if (XBT_TRACKER == true) {
        // == Wait time
        if ($CURUSER['class'] >= UC_STAFF) {
            $HTMLOUT .= "<tr><td class='rowhead w-10'>{$lang['userdetails_waittime']}</td><td colspan='3' class='has-text-left'><input type='text' class='w-100' name='wait_time' value='" . (int)$user['wait_time'] . "' /></td></tr>";
        }
        // ==end
        // == Peers limit
        if ($CURUSER['class'] >= UC_STAFF) {
            $HTMLOUT .= "<tr><td class='rowhead w-10'>{$lang['userdetails_peerslimit']}</td><td colspan='3' class='has-text-left'><input type='text' class='w-100' name='peers_limit' value='" . (int)$user['peers_limit'] . "' /></td></tr>";
        }
        // ==end
        // == Torrents limit
        if ($CURUSER['class'] >= UC_STAFF) {
            $HTMLOUT .= "<tr><td class='rowhead w-10'>{$lang['userdetails_torrentslimit']}</td><td colspan='3' class='has-text-left'><input type='text' class='w-100' name='torrents_limit' value='" . (int)$user['torrents_limit'] . "' /></td></tr>";
        }
        // ==end
    }
    //==High speed
    if ($CURUSER['class'] == UC_MAX && XBT_TRACKER == false) {
        //$HTMLOUT.= "<tr><td class='rowhead w-10'>{$lang['userdetails_highspeed']}</td><td class='row' colspan='3' class='has-text-left'><input type='checkbox' name='highspeed' value='yes'" . (($user['opt1'] & user_options::HIGHSPEED) ? " checked='checked'" : "") . " />Yes</td></tr>";
        $HTMLOUT .= "<tr><td class='rowhead w-10'>{$lang['userdetails_highspeed']}</td><td class='row' colspan='3' class='has-text-left'><input type='radio' name='highspeed' value='yes' " . ($user['highspeed'] == 'yes' ? " checked='checked'" : '') . " />{$lang['userdetails_yes']} <input type='radio' name='highspeed' value='no' " . ($user['highspeed'] == 'no' ? " checked='checked'" : '') . " />{$lang['userdetails_no']}</td></tr>";
    }
    //$HTMLOUT.= "<tr><td class='rowhead w-10'>{$lang['userdetails_park']}</td><td colspan='3' class='has-text-left'><input name='parked' value='yes' type='checkbox'" . (($user['opt1'] & user_options::PARKED) ? " checked='checked'" : "") . " />{$lang['userdetails_yes']}</td></tr>";
    $HTMLOUT .= "<tr><td class='rowhead w-10'>{$lang['userdetails_park']}</td><td colspan='3' class='has-text-left'><input name='parked' value='yes' type='radio'" . ($user['parked'] == 'yes' ? " checked='checked'" : '') . " />{$lang['userdetails_yes']} <input name='parked' value='no' type='radio'" . ($user['parked'] == 'no' ? " checked='checked'" : '') . " />{$lang['userdetails_no']}</td></tr>";
    $HTMLOUT .= "<tr><td class='rowhead w-10'>{$lang['userdetails_reset']}</td><td colspan='3'><input type='checkbox' name='reset_torrent_pass' value='1' /><font class='small'>{$lang['userdetails_pass_msg']}</font></td></tr>";
    // == seedbonus
    if ($CURUSER['class'] >= UC_STAFF) {
        $HTMLOUT .= "<tr><td class='rowhead w-10'>{$lang['userdetails_bonus_points']}</td><td colspan='3' class='has-text-left'><input type='text' class='w-100' name='seedbonus' value='" . (int)$user_stats['seedbonus'] . "' /></td></tr>";
    }
    // ==end
    // == rep
    if ($CURUSER['class'] >= UC_STAFF) {
        $HTMLOUT .= "<tr><td class='rowhead w-10'>{$lang['userdetails_rep_points']}</td><td colspan='3' class='has-text-left'><input type='text' class='w-100' name='reputation' value='" . (int)$user['reputation'] . "' /></td></tr>";
    }
    // ==end
    //==Invites
    $HTMLOUT .= "<tr><td class='rowhead w-10'>{$lang['userdetails_invright']}</td><td colspan='3' class='has-text-left'><input type='radio' name='invite_on' value='yes'" . ($user['invite_on'] == 'yes' ? " checked='checked'" : '') . " />{$lang['userdetails_yes']}<input type='radio' name='invite_on' value='no'" . ($user['invite_on'] == 'no' ? " checked='checked'" : '') . " />{$lang['userdetails_no']}</td></tr>";
    //$HTMLOUT.= "<tr><td class='rowhead w-10'>{$lang['userdetails_invright']}</td><td colspan='3' class='has-text-left'><input type='checkbox' name='invite_on' value='yes'" . (($user['opt1'] & user_options::INVITE_ON) ? " checked='checked'" : "") . " />{$lang['userdetails_yes']}</td></tr>";
    $HTMLOUT .= "<tr><td class='rowhead w-10'><b>{$lang['userdetails_invites']}</b></td><td colspan='3' class='has-text-left'><input type='text' class='w-100' name='invites' value='" . htmlsafechars($user['invites']) . "' /></td></tr>";
    /*$HTMLOUT.= "<tr>
                      <td class='rowhead w-10'>Avatar Rights</td>
                      <td colspan='3' class='has-text-left'><input name='view_offensive_avatar' value='yes' type='checkbox'" . (($user['opt1'] & user_options::VIEW_OFFENSIVE_AVATAR) ? " checked='checked'" : "") . " />Yes</td>
                </tr>
                <tr>
                      <td class='rowhead w-10'>Offensive Avatar</td>
                      <td colspan='3' class='has-text-left'><input name='offensive_avatar' value='yes' type='checkbox'" . (($user['opt1'] & user_options::OFFENSIVE_AVATAR) ? " checked='checked'" : "") . " />Yes</td>
                </tr>
                <tr>
                      <td class='rowhead w-10'>View Offensive Avatars</td>
                      <td colspan='3' class='has-text-left'><input name='avatar_rights' value='yes' type='checkbox'" . (($user['opt1'] & user_options::AVATAR_RIGHTS) ? " checked='checked'" : "") . " />Yes</td>
                </tr>";*/
    $HTMLOUT .= "<tr>
                  <td class='rowhead w-10'>{$lang['userdetails_avatar_rights']}</td>
                  <td colspan='3' class='has-text-left'><input name='view_offensive_avatar' value='yes' type='radio'" . ($user['view_offensive_avatar'] == 'yes' ? " checked='checked'" : '') . " />{$lang['userdetails_yes']}
                  <input name='view_offensive_avatar' value='no' type='radio'" . ($user['view_offensive_avatar'] == 'no' ? " checked='checked'" : '') . " />{$lang['userdetails_no']} </td>
                 </tr>
                 <tr>
                  <td class='rowhead w-10'>{$lang['userdetails_offensive']}</td>
                  <td colspan='3' class='has-text-left'><input name='offensive_avatar' value='yes' type='radio'" . ($user['offensive_avatar'] == 'yes' ? " checked='checked'" : '') . " />{$lang['userdetails_yes']}
                  <input name='offensive_avatar' value='no' type='radio'" . ($user['offensive_avatar'] == 'no' ? " checked='checked'" : '') . " />{$lang['userdetails_no']} </td>
                </tr>
                <tr>
                  <td class='rowhead w-10'>{$lang['userdetails_view_offensive']}</td>
                  <td colspan='3' class='has-text-left'><input name='avatar_rights' value='yes' type='radio'" . ($user['avatar_rights'] == 'yes' ? " checked='checked'" : '') . " />{$lang['userdetails_yes']}
                  <input name='avatar_rights' value='no' type='radio'" . ($user['avatar_rights'] == 'no' ? " checked='checked'" : '') . " />{$lang['userdetails_no']} </td>
               </tr>";
    $HTMLOUT .= '<tr>
                      <td class="rowhead w-10">' . $lang['userdetails_hnr'] . '</td>
                      <td colspan="3" class="has-text-left"><input type="text" class="w-100" name="hit_and_run_total" value="' . (int)$user['hit_and_run_total'] . '" /></td>
                </tr>
                 <tr>
                     <td class="rowhead w-10">' . $lang['userdetails_suspended'] . '</td>
                     <td colspan="3" class="has-text-left"><input name="suspended" value="yes" type="radio"' . ($user['suspended'] == 'yes' ? ' checked="checked"' : '') . ' />' . $lang['userdetails_yes'] . '
                     <input name="suspended" value="no" type="radio"' . ($user['suspended'] == 'no' ? ' checked="checked"' : '') . ' />' . $lang['userdetails_no'] . '
        ' . $lang['userdetails_suspended_reason'] . '<br>
                    <input type="text" class="w-100" name="suspended_reason" /></td>
                   </tr>
                <!--<tr>
                      <td class="rowhead w-10">' . $lang['userdetails_suspended'] . '</td>
                      <td colspan="3" class="has-text-left"><input name="suspended" value="yes" type="checkbox"' . (($user['opt1'] & user_options::SUSPENDED) ? ' checked="checked"' : '') . ' />' . $lang['userdetails_yes'] . '
                              ' . $lang['userdetails_suspended_reason'] . '<br>
                      <input type="text" class="w-100" name="suspended_reason" /></td>
                </tr>-->
      ';
    $HTMLOUT .= "<tr>
                      <td class='rowhead w-10'>{$lang['userdetails_paranoia']}</td>
                      <td colspan='3' class='has-text-left'>
                      <select name='paranoia' class='w-100'>
                      <option value='0'" . ($user['paranoia'] == 0 ? " selected='selected'" : '') . ">{$lang['userdetails_paranoia_0']}</option>
                      <option value='1'" . ($user['paranoia'] == 1 ? " selected='selected'" : '') . ">{$lang['userdetails_paranoia_1']}</option>
                      <option value='2'" . ($user['paranoia'] == 2 ? " selected='selected'" : '') . ">{$lang['userdetails_paranoia_2']}</option>
                      <option value='3'" . ($user['paranoia'] == 3 ? " selected='selected'" : '') . ">{$lang['userdetails_paranoia_3']}</option>
                      </select></td>
                </tr>
                 <tr>
                     <td class='rowhead w-10'>{$lang['userdetails_forum_rights']}</td>
                     <td colspan='3' class='has-text-left'><input name='forum_post' value='yes' type='radio'" . ($user['forum_post'] == 'yes' ? " checked='checked'" : '') . " />{$lang['userdetails_yes']}
                     <input name='forum_post' value='no' type='radio'" . ($user['forum_post'] == 'no' ? " checked='checked'" : '') . " />{$lang['userdetails_forums_no']}</td>
                    </tr>
                <!--<tr>
                      <td class='rowhead w-10'>{$lang['userdetails_forum_rights']}</td>
                      <td colspan='3' class='has-text-left'><input name='forum_post' value='yes' type='checkbox'" . (($user['opt1'] & user_options::FORUM_POST) ? " checked='checked'" : '') . " />{$lang['userdetails_yes']}</td>
                </tr>-->";

    if ($CURUSER['class'] >= UC_ADMINISTRATOR) {
        $HTMLOUT .= "<tr>
         <td class='rowhead w-10'>{$lang['userdetails_addupload']}</td>
         <td class='has-text-centered'>
         <img src='{$site_config['pic_base_url']}plus.gif' alt='{$lang['userdetails_change_ratio']}' title='{$lang['userdetails_change_ratio']}!' id='uppic' onclick='togglepic('{$site_config['baseurl']}', 'uppic','upchange')' />
         <input type='text' name='amountup' class='w-100' />
         </td>
         <td>
         <select name='formatup' class='w-100'>
         <option value='mb'>{$lang['userdetails_MB']}</option>
         <option value='gb'>{$lang['userdetails_GB']}</option></select>
         <input type='hidden' id='upchange' name='upchange' value='plus' />
         </td>
         </tr>
         <tr>
         <td class='rowhead w-10'>{$lang['userdetails_adddownload']}</td>
         <td class='has-text-centered'>
         <img src='{$site_config['pic_base_url']}plus.gif' alt='{$lang['userdetails_change_ratio']}' title='{$lang['userdetails_change_ratio']}!' id='downpic' onclick='togglepic('{$site_config['baseurl']}','downpic','downchange')' />
         <input type='text' name='amountdown' class='w-100' />
         </td>
         <td>
         <select name='formatdown' class='w-100'>
         <option value='mb'>{$lang['userdetails_MB']}</option>
         <option value='gb'>{$lang['userdetails_GB']}</option></select>
         <input type='hidden' id='downchange' name='downchange' value='plus' />
         </td></tr>";
    }
    $HTMLOUT .= "<tr><td colspan='3' class='has-text-centered'><input type='submit' class='button' value='{$lang['userdetails_okay']}' /></td></tr>";
    $HTMLOUT .= "</table>";
    $HTMLOUT .= "</form>";
}
$HTMLOUT .= '</div></div></div>';

echo stdhead("{$lang['userdetails_details']} " . $user['username'], true, $stdhead) . wrapper($HTMLOUT) . stdfoot($stdfoot);
