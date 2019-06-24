<?php

declare(strict_types = 1);

require_once __DIR__ . '/../include/bittorrent.php';
require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_bbcode.php';
require_once INCL_DIR . 'function_html.php';
require_once INCL_DIR . 'function_onlinetime.php';
require_once CLASS_DIR . 'class_user_options.php';
require_once CLASS_DIR . 'class_user_options_2.php';
require_once INCL_DIR . 'function_comments.php';

check_user_status();
$lang = array_merge(load_language('global'), load_language('userdetails'));
$edit_profile = $friend_links = $shitty_link = $sharemark_link = '';
$start = microtime(true);
$stdfoot = [
    'js' => [
        get_file_name('userdetails_js'),
    ],
];

use Delight\Auth\Auth;
use Pu239\Cache;
use Pu239\Mood;
use Pu239\Session;
use Pu239\User;

global $container, $lang, $site_config, $CURUSER, $mysqli;

$cache = $container->get(Cache::class);
$id = !empty($_GET['id']) ? (int) $_GET['id'] : $CURUSER['id'];
if (!is_valid_id($id)) {
    stderr($lang['userdetails_error'], "{$lang['userdetails_bad_id']}");
}
$users_class = $container->get(User::class);
$user = $users_class->getUserFromId($id);
if (empty($user)) {
    stderr($lang['userdetails_error'], $lang['userdetails_invalid']);
} elseif ($user['status'] === 'pending') {
    stderr($lang['userdetails_error'], $lang['userdetails_pending']);
} elseif ($user['paranoia'] == 3 && $CURUSER['class'] < UC_STAFF && $CURUSER['id'] != $id) {
    stderr($lang['userdetails_error'], '<span><img src="' . $site_config['paths']['images_baseurl'] . 'smilies/tinfoilhat.gif" alt="' . $lang['userdetails_tinfoil'] . '" class="tooltipper" title="' . $lang['userdetails_tinfoil'] . '">
       ' . $lang['userdetails_tinfoil2'] . ' <img src="' . $site_config['paths']['images_baseurl'] . 'smilies/tinfoilhat.gif" alt="' . $lang['userdetails_tinfoil'] . '" class="tooltipper" title="' . $lang['userdetails_tinfoil'] . '"></span>');
    die();
}
if (isset($_GET['delete_hit_and_run']) && $CURUSER['class'] >= UC_STAFF) {
    $delete_me = isset($_GET['delete_hit_and_run']) ? (int) $_GET['delete_hit_and_run'] : 0;
    if (!is_valid_id($delete_me)) {
        stderr($lang['userdetails_error'], $lang['userdetails_bad_id']);
    }
    sql_query("UPDATE snatched SET hit_and_run = '0', mark_of_cain = 'no' WHERE id = " . sqlesc($delete_me)) or sqlerr(__FILE__, __LINE__);
    if (@mysqli_affected_rows($mysqli) === 0) {
        stderr($lang['userdetails_error'], $lang['userdetails_notdeleted']);
    }
    header('Location: ?id=' . $id . '&completed=1');
    die();
}
$session = $container->get(Session::class);
if (isset($_GET['force_logout']) && $id != $CURUSER['id'] && $CURUSER['class'] >= UC_STAFF) {
    $cache->set('forced_logout_' . $id, TIME_NOW);
    $session->set('is-success', 'This user will be forced to logout on next page view');
}
if ($CURUSER['class'] >= UC_STAFF || $user['id'] == $CURUSER['id']) {
    $auth = $container->get(Auth::class);
    $ip = $auth->getIpAddress();
    $addr = gethostbyaddr($ip) . "($ip)";
}
if ($user['perms'] & PERMS_STEALTH) {
    $joindate = "{$lang['userdetails_na']}";
} else {
    $joindate = get_date((int) $user['registered'], '');
}
$lastseen = $user['last_access'];
if ($lastseen == 0 || $user['perms'] & PERMS_STEALTH) {
    $lastseen = "{$lang['userdetails_never']}";
} else {
    $lastseen = get_date((int) $user['last_access'], '', 0, 1);
}
if ((($user['class'] >= $site_config['allowed']['enable_invincible'] || $user['id'] == $CURUSER['id']) || ($user['class'] < $site_config['allowed']['enable_invincible']) && $CURUSER['class'] >= $site_config['allowed']['enable_invincible']) && isset($_GET['invincible'])) {
    require_once INCL_DIR . 'invincible.php';
    if ($_GET['invincible'] === 'yes') {
        invincible($id, true, true);
    } elseif ($_GET['invincible'] === 'remove_bypass') {
        invincible($id, false, false);
    } else {
        invincible($id, false, false);
    }
}
if ((($user['class'] >= UC_STAFF || $user['id'] == $CURUSER['id']) || ($user['class'] < UC_STAFF) && $CURUSER['class'] >= UC_STAFF) && isset($_GET['stealth'])) {
    require_once INCL_DIR . 'stealth.php';
    if ($_GET['stealth'] === 'yes') {
        stealth($id);
    } elseif ($_GET['stealth'] === 'no') {
        stealth($id, false);
    }
}
$country = '';
$countries = countries();
foreach ($countries as $cntry) {
    if ($cntry['id'] == $user['country']) {
        $country = "<img src='{$site_config['paths']['images_baseurl']}flag/{$cntry['flagpic']}' alt='" . htmlsafechars((string) $cntry['name']) . "'>";
        break;
    }
}
if (!(isset($_GET['hit'])) && $CURUSER['id'] !== $user['id']) {
    $update = [
        'hits' => $user['hits'] + 1,
    ];
    $users_class->update($update, $user['id']);
}
$HTMLOUT = $perms = $stealth = $suspended = $watched_user = $h1_thingie = '';
if (($user['opt1'] & user_options::ANONYMOUS) && ($CURUSER['class'] < UC_STAFF && $user['id'] != $CURUSER['id'])) {
    $HTMLOUT .= "
    <div class='table-wrapper'>
        <table class='table table-bordered table-striped two'>
            <tr>
                <td colspan='3' class='has-text-centered'>{$lang['userdetails_anonymous']}</td>
            </tr>";
    if ($user['avatar']) {
        $HTMLOUT .= "
            <tr>
                <td colspan='3' class='has-text-centered'>
                    <img src='" . url_proxy($user['avatar'], true) . "'>
                </td>
            </tr>";
    }
    if ($user['info']) {
        $HTMLOUT .= "
            <tr class='text-top'>
                <td class='has-text-left' colspan='3'>" . format_comment($user['info']) . '</td>
            </tr>';
    }
    $HTMLOUT .= "
            <tr>
                <td colspan='3' class='has-text-centered'>
                    <form method='get' action='{$site_config['paths']['baseurl']}/messages.php?action=send_message' accept-charset='utf-8'>
                        <input type='hidden' name='receiver' value='" . (int) $user['id'] . "'>
                        <input type='submit' value='{$lang['userdetails_sendmess']}'>
                    </form>";
    if ($CURUSER['class'] < UC_STAFF && $user['id'] != $CURUSER['id']) {
        echo stdhead($lang['userdetails_anonymoususer']) . $HTMLOUT . stdfoot();
    }
    $HTMLOUT .= '
                </td>
            </tr>
        </table>
    </div>';
}
$h1_thingie = ((isset($_GET['sn']) || isset($_GET['wu'])) ? '<h1>' . $lang['userdetails_updated'] . '</h1>' : '');
if ($CURUSER['id'] != $user['id'] && $CURUSER['class'] >= UC_STAFF) {
    $suspended .= ($user['suspended'] === 'yes' ? '  <img src="' . $site_config['paths']['images_baseurl'] . 'smilies/excl.gif" alt="' . $lang['userdetails_suspended'] . '" class="tooltipper" title="' . $lang['userdetails_suspended'] . '"> <b>' . $lang['userdetails_usersuspended'] . '</b> <img src="' . $site_config['paths']['images_baseurl'] . 'smilies/excl.gif" alt="' . $lang['userdetails_suspended'] . '" class="tooltipper" title="' . $lang['userdetails_suspended'] . '">' : '');
}
if ($CURUSER['id'] != $user['id'] && $CURUSER['class'] >= UC_STAFF) {
    $watched_user .= ($user['watched_user'] == 0 ? '' : '  <img src="' . $site_config['paths']['images_baseurl'] . 'smilies/excl.gif" alt="' . $lang['userdetails_watched'] . '" class="tooltipper" title="' . $lang['userdetails_watched'] . '"> <b>' . $lang['userdetails_watchlist1'] . ' <a href="' . $site_config['paths']['baseurl'] . '/staffpanel.php?tool=watched_users">' . $lang['userdetails_watchlist2'] . '</a></b> <img src="' . $site_config['paths']['images_baseurl'] . 'smilies/excl.gif" alt="' . $lang['userdetails_watched'] . '" class="tooltipper" title="' . $lang['userdetails_watched'] . '">');
}
$perms .= ($CURUSER['class'] >= UC_STAFF ? (($user['perms'] & PERMS_NO_IP) ? '  <img src="' . $site_config['paths']['images_baseurl'] . 'smilies/super.gif" alt="' . $lang['userdetails_invincible'] . '"  class="tooltipper" title="' . $lang['userdetails_invincible'] . '">' : '') : '');
$stealth .= ($CURUSER['class'] >= UC_STAFF ? (($user['perms'] & PERMS_STEALTH) ? '  <img src="' . $site_config['paths']['images_baseurl'] . 'smilies/ninja.gif" alt="' . $lang['userdetails_stealth'] . '"  class="tooltipper" title="' . $lang['userdetails_stealth'] . '">' : '') : '');
$enabled = $user['enabled'] === 'yes';
$parked = $user['opt1'] & user_options::PARKED ? $lang['userdetails_parked'] : '';

$h1 = "
                <h1 class='has-text-centered'>" . format_username((int) $user['id']) . "$country$stealth$watched_user$suspended$h1_thingie$perms$parked</h1>";
if (!$enabled) {
    $h1 .= $lang['userdetails_disabled'];
} elseif ($CURUSER['id'] != $user['id']) {
    $friends = $cache->get('Friends_' . $id);
    if ($friends === false || is_null($friends)) {
        $r3 = sql_query('SELECT id FROM friends WHERE userid=' . sqlesc($user['id']) . ' AND friendid=' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
        $friends = mysqli_num_rows($r3);
        $cache->set('Friends_' . $id, $friends, $site_config['expires']['user_friends']);
    }
    $blocks = $cache->get('Blocks_' . $id);
    if ($blocks === false || is_null($blocks)) {
        $r4 = sql_query('SELECT id FROM blocks WHERE userid=' . sqlesc($user['id']) . ' AND blockid=' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
        $blocks = mysqli_num_rows($r4);
        $cache->set('Blocks_' . $id, $blocks, $site_config['expires']['user_blocks']);
    }
    if ($friends > 0) {
        $friend_links .= "<li class='is-link margin10'><a href='{$site_config['paths']['baseurl']}/friends.php?action=delete&amp;type=friend&amp;targetid=$id'>{$lang['userdetails_remove_friends']}</a></li>";
    } else {
        $friend_links .= "<li class='is-link margin10'><a href='{$site_config['paths']['baseurl']}/friends.php?action=add&amp;type=friend&amp;targetid=$id'>{$lang['userdetails_add_friends']}</a></li>";
    }
    if ($blocks > 0) {
        $friend_links .= "<li class='is-link margin10'><a href='{$site_config['paths']['baseurl']}/friends.php?action=delete&amp;type=block&amp;targetid=$id'>{$lang['userdetails_remove_blocks']}</a></li>";
    } else {
        $friend_links .= "<li class='is-link margin10'><a href='{$site_config['paths']['baseurl']}/friends.php?action=add&amp;type=block&amp;targetid=$id'>{$lang['userdetails_add_blocks']}</a></li>";
    }
}

if ($CURUSER['class'] >= UC_STAFF) {
    $shitty = '';
    $shit_list = $cache->get('shit_list_' . $id);
    if ($shit_list === false || is_null($shit_list)) {
        $check_if_theyre_shitty = sql_query('SELECT suspect FROM shit_list WHERE userid=' . sqlesc($user['id']) . ' AND suspect = ' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
        list($shit_list) = mysqli_fetch_row($check_if_theyre_shitty);
        $cache->set('shit_list_' . $id, $shit_list, $site_config['expires']['shit_list']);
    }
    if ($shit_list > 0) {
        $shitty_link = "<li class='is-link margin10'><a href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=shit_list&amp;action=shit_list'>
                Remove from your
                <img class='tooltipper right5' src='{$site_config['paths']['images_baseurl']}smilies/shit.gif' alt='Shit' class='tooltipper' title='Shit'>
            </a></li>";
    } elseif ($CURUSER['id'] != $user['id']) {
        $shitty_link .= "<li class='is-link margin10'><a href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=shit_list&amp;action=shit_list&amp;action2=new&amp;shit_list_id={$id}&amp;return_to='{$_SERVER['PHP_SELF']}?id={$id}'>
                {$lang['userdetails_shit3']}
            </a></li>";
    }
}
if ($user['donor'] && $CURUSER['id'] == $user['id'] || $CURUSER['class'] >= UC_SYSOP) {
    $donoruntil = (int) $user['donoruntil'];
    if ($donoruntil === 0) {
        $HTMLOUT .= '';
    } else {
        $h1 .= "
            <div class='top20 has-text-centered'>
                {$lang['userdetails_donatedtill']} - " . get_date((int) $user['donoruntil'], 'DATE', 1, 0) . ' [ ' . mkprettytime($donoruntil - TIME_NOW) . " ] {$lang['userdetails_togo']}...</b>
                <br><span class='size_4'> {$lang['userdetails_renew']} <a class='is-link' href='{$site_config['paths']['baseurl']}/donate.php'>{$lang['userdetails_here']}</a>.</span>
            </div>";
    }
}
if ($CURUSER['id'] == $user['id']) {
    $edit_profile = "<li class='is-link margin10'><a href='{$site_config['paths']['baseurl']}/usercp.php?action=default'>{$lang['userdetails_editself']}</a></li>
        <li class='is-link margin10'><a href='{$site_config['paths']['baseurl']}/view_announce_history.php'>{$lang['userdetails_announcements']}</a></li>";
}
if ($CURUSER['id'] != $user['id']) {
    $sharemark_link .= "<li class='is-link margin10'><a href='{$site_config['paths']['baseurl']}/sharemarks.php?id=$id'>{$lang['userdetails_sharemarks']}</a></li>";
}

$HTMLOUT .= "
    <div class='bottom20'>
        <ul class='level-center bg-06'>
        $sharemark_link
        $shitty_link
        $friend_links
        $edit_profile" . ($CURUSER['class'] >= UC_MAX ? $user['perms'] & PERMS_NO_IP ? "
        <li class='margin10'><a class='is-link tooltipper' title='{$lang['userdetails_invincible_def1']}<br>{$lang['userdetails_invincible_def2']}' href='{$_SERVER['PHP_SELF']}?id={$id}&amp;invincible=no'>{$lang['userdetails_invincible_remove']}</a></li>" . ($user['perms'] & PERMS_BYPASS_BAN) ? "
        <li class='margin10'><a class='is-link tooltipper' title='{$lang['userdetails_invincible_def3']}<br>{$lang['userdetails_invincible_def4']}' href='{$_SERVER['PHP_SELF']}?id={$id}&amp;invincible=remove_bypass'>{$lang['userdetails_remove_bypass']}</a></li>" : "
        <li class='margin10'><a class='is-link tooltipper' title='{$lang['userdetails_invincible_def5']}<br>{$lang['userdetails_invincible_def6']}<br>{$lang['userdetails_invincible_def7']}<br>{$lang['userdetails_invincible_def8']} href='{$_SERVER['PHP_SELF']}?id={$id}&amp;invincible=yes'>{$lang['userdetails_add_bypass']}</a></li>" : "
        <li class='margin10'><a class='is-link tooltipper' title='{$lang['userdetails_invincible_def9']}<br>{$lang['userdetails_invincible_def0']}' href='{$_SERVER['PHP_SELF']}?id={$id}&amp;invincible=yes'>{$lang['userdetails_make_invincible']}</a></li>" : '');

$stealth = $cache->get('display_stealth_' . $user['id']);
if ($stealth) {
    $session->set('is-info', htmlsafechars((string) $user['username']) . " $stealth {$lang['userdetails_in_stealth']}");
}

$HTMLOUT .= ($CURUSER['class'] >= UC_STAFF ? (($user['perms'] & PERMS_STEALTH) ? "
            <li class='margin10'><a class='is-link tooltipper' title='{$lang['userdetails_stealth_def1']}<br>{$lang['userdetails_stealth_def2']}' href='{$_SERVER['PHP_SELF']}?id={$id}&amp;stealth=no'>{$lang['userdetails_stealth_disable']}</a></li>" : "
            <li class='margin10'><a class='is-link tooltipper' title='{$lang['userdetails_stealth_def1']}<br>{$lang['userdetails_stealth_def2']}' href='{$_SERVER['PHP_SELF']}?id={$id}&amp;stealth=yes'>{$lang['userdetails_stealth_enable']}</a></li>") : '');
$HTMLOUT .= $CURUSER['class'] >= UC_SYSOP ? "
            <li class='margin10'><a class='has-text-danger tooltipper' title='Reset this users password' href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=reset&amp;username={$user['username']}&amp;userid={$id}'>Reset Password</a></li>
            <li class='margin10'><a class='has-text-danger tooltipper' title='Force this user to Logout' href='{$_SERVER['PHP_SELF']}?id={$id}&amp;force_logout=yes'>Force Logout</a></li>" : '';
$HTMLOUT .= '
        </ul>
    </div>';

$HTMLOUT .= "
        $h1
        <div>
            <ul class='tabs'>
                <li class='top20'><a href='#torrents'>{$lang['userdetails_torrents']}</a></li>
                <li class='top20'><a href='#general'>{$lang['userdetails_general']}</a></li>
                <li class='top20'><a href='#activity'>{$lang['userdetails_activity']}</a></li>
                <li class='top20'><a href='#comments'>{$lang['userdetails_usercomments']}</a></li>";
if (($CURUSER['class'] >= UC_STAFF && $user['class'] < $CURUSER['class']) || $CURUSER['class'] >= UC_MAX) {
    $HTMLOUT .= "
                <li class='top20'><a href='#edit'>{$lang['userdetails_edit_user']}</a></li>";
}
$HTMLOUT .= '
            </ul>';
$HTMLOUT .= "
            <div class='tabcontent'>
                <div id='torrents' class='table-wrapper'>";

$table_data = '';
if ($CURUSER['blocks']['userdetails_page'] & block_userdetails::FLUSH && $BLOCKS['userdetails_flush_on']) {
    require_once BLOCK_DIR . 'userdetails/flush.php';
}
if ($CURUSER['id'] === $id || $CURUSER['class'] >= UC_ADMINISTRATOR) {
    $table_data .= "
        <tr>
            <td class='rowhead'>Download Torrents</td>
            <td>
                <ul class='level-left buttons'>
                    <li class='right10'>
                        <a href='{$site_config['paths']['baseurl']}/download_multi.php?userid=$id' class='button is-small tooltipper' title='Download <i><b>all torrents</b></i> that you have previously snatched'>Snatched Torrents</a>
                    </li>
                    <li class='right10'>
                        <a href='{$site_config['paths']['baseurl']}/download_multi.php?owner=true&amp;userid=$id' class='button is-small tooltipper' title='Download <i><b>all torrents</b></i> that you have uploaded'>Uploaded Torrents</a>
                    </li>";
    if ($CURUSER['id'] === $id && $CURUSER['class'] >= UC_ADMINISTRATOR) {
        $table_data .= "
                    <li class='right10'>
                        <a href='{$site_config['paths']['baseurl']}/download_multi.php?getall=yes' class='button is-small tooltipper' title='Download <i><b>all active</b></i> torrents'>Live Torrents</a>
                    </li>
                    <li class='right10'>
                        <a href='{$site_config['paths']['baseurl']}/download_multi.php?getall=no' class='button is-small tooltipper' title='Download <i><b>all dead</b></i> torrents'>Dead Torrents</a>
                    </li>";
    }
    $table_data .= '
                </ul>
            </td>
        </tr>';
}
if ($CURUSER['blocks']['userdetails_page'] & block_userdetails::TRAFFIC && $BLOCKS['userdetails_traffic_on']) {
    require_once BLOCK_DIR . 'userdetails/traffic.php';
}
if ($CURUSER['blocks']['userdetails_page'] & block_userdetails::SHARE_RATIO && $BLOCKS['userdetails_share_ratio_on']) {
    require_once BLOCK_DIR . 'userdetails/shareratio.php';
}
if ($CURUSER['blocks']['userdetails_page'] & block_userdetails::SEEDTIME_RATIO && $BLOCKS['userdetails_seedtime_ratio_on']) {
    require_once BLOCK_DIR . 'userdetails/seedtimeratio.php';
}
if ($CURUSER['blocks']['userdetails_page'] & block_userdetails::CONNECTABLE_PORT && $BLOCKS['userdetails_connectable_port_on']) {
    require_once BLOCK_DIR . 'userdetails/connectable.php';
}
if ($CURUSER['blocks']['userdetails_page'] & block_userdetails::TORRENTS_BLOCK && $BLOCKS['userdetails_torrents_block_on']) {
    require_once BLOCK_DIR . 'userdetails/torrents_block.php';
}
if ($CURUSER['blocks']['userdetails_page'] & block_userdetails::COMPLETED && $BLOCKS['userdetails_completed_on']) {
    require_once BLOCK_DIR . 'userdetails/completed.php';
}
if ($CURUSER['blocks']['userdetails_page'] & block_userdetails::SNATCHED_STAFF && $BLOCKS['userdetails_snatched_staff_on']) {
    require_once BLOCK_DIR . 'userdetails/snatched_staff.php';
}

$HTMLOUT .= main_table($table_data);

$HTMLOUT .= "
                </div>
                <div id='general' class='table-wrapper'>
                    <table class='table table-bordered table-striped five'>";

if (($CURUSER['id'] !== $user['id']) && ($CURUSER['class'] >= UC_STAFF)) {
    $the_flip_box = "
        <a id='watched_user'></a>
        <a class='is-link tooltipper' href='#watched_user' onclick=\"flipBox('3')\" title='{$lang['userdetails_flip1']}'>" . ($user['watched_user'] > 0 ? $lang['userdetails_flip2'] : $lang['userdetails_flip3']) . "<img onclick=\"flipBox('3')\" src='{$site_config['paths']['images_baseurl']}panel_on.gif' name='b_3' width='8' height='8' alt='{$lang['userdetails_flip1']}' class='tooltipper' title='{$lang['userdetails_flip1']}'></a>";
    $HTMLOUT .= "
                        <tr>
                            <td class='rowhead'>{$lang['userdetails_watched']}</td>
                            <td class='has-text-left'>" . ($user['watched_user'] > 0 ? "
                                {$lang['userdetails_watched_since']} " . get_date((int) $user['watched_user'], '') : $lang['userdetails_not_watched']) . "
                                $the_flip_box
                                <div class='has-text-left' id='box_3'>
                                    <form method='post' action='ajax/member_input.php' name='notes_for_staff' accept-charset='utf-8'>
                                        <input name='id' type='hidden' value='{$id}'>
                                        <input type='hidden' value='watched_user' name='action'>
                                        {$lang['userdetails_add_watch']}
                                        <input type='radio' class='right5' value='yes' name='add_to_watched_users'" . ($user['watched_user'] > 0 ? ' checked' : '') . ">{$lang['userdetails_yes1']}
                                        <input type='radio' class='right5' value='no' name='add_to_watched_users'" . ($user['watched_user'] == 0 ? ' checked' : '') . "'>{$lang['userdetails_no1']}<br>
                                        <div id='desc_text'>
                                            * {$lang['userdetails_watch_change1']}<br>
                                            {$lang['userdetails_watch_change2']}
                                        </div>
                                        <textarea id='watched_reason' class='w-100' rows='6' name='watched_reason'>" . htmlsafechars((string) $user['watched_user_reason']) . "</textarea>
                                        <div class='has-text-centered'>
                                            <input id='watched_user_button' type='submit' value='{$lang['userdetails_submit']}' class='button is-small' name='watched_user_button'>
                                        </div>
                                    </form>
                                </div>
                            </td>
                        </tr>";

    $the_flip_box_4 = '[ <a id="staff_notes"></a><a class="is-link tooltipper" href="#staff_notes" onclick="flipBox(\'4\')" name="b_4" title="' . $lang['userdetails_open_staff'] . '">view <img onclick="flipBox(\'4\')" src="' . $site_config['paths']['images_baseurl'] . 's/panel_on.gif" name="b_4" width="8" height="8" alt="' . $lang['userdetails_open_staff'] . '" class="tooltipper" title="' . $lang['userdetails_open_staff'] . '"></a> ]';
    $HTMLOUT .= '<tr><td class="rowhead">' . $lang['userdetails_staffnotes'] . '</td><td class="has-text-left">
                            <a class="is-link tooltipper" href="#staff_notes" onclick="flipBox(\'6\')" name="b_6" title="' . $lang['userdetails_aev_staffnote'] . '">' . ($user['staff_notes'] !== '' ? '' . $lang['userdetails_vae'] . ' ' : '' . $lang['userdetails_add'] . ' ') . '<img onclick="flipBox(\'6\')" src="' . $site_config['paths']['images_baseurl'] . 'panel_on.gif" name="b_6" width="8" height="8" alt="' . $lang['userdetails_aev_staffnote'] . '" class="tooltipper" title="' . $lang['userdetails_aev_staffnote'] . '"></a>
                            <div class="has-text-left" id="box_6">
                                <form method="post" action="ajax/member_input.php" name="notes_for_staff" accept-charset="utf-8">
                                    <input name="id" type="hidden" value="' . (int) $user['id'] . '">
                                    <input type="hidden" value="staff_notes" name="action" id="action">
                                    <textarea id="new_staff_note" class="w-100" rows="6" name="new_staff_note">' . htmlsafechars((string) $user['staff_notes']) . '</textarea>
                                    <div class="has-text-centered">
                                        <input id="staff_notes_button" type="submit" value="' . $lang['userdetails_submit'] . '" class="button is-small" name="staff_notes_button">
                                    </div>
                                </form>
                            </div> </td></tr>';

    $the_flip_box_7 = '[ <a id="system_comments"></a><a class="is-link tooltipper" href="#system_comments" onclick="flipBox(\'7\')"  name="b_7" title="' . $lang['userdetails_open_system'] . ')" src="' . $site_config['paths']['images_baseurl'] . 'panel_on.gif" name="b_7" width="8" height="8" alt="' . $lang['userdetails_open_system'] . '" class="tooltipper" title="' . $lang['userdetails_open_system'] . '"></a> ]';
    if (!empty($user['modcomment'])) {
        $HTMLOUT .= "<tr><td class='rowhead'>{$lang['userdetails_system']}</td><td class='has-text-left'>" . ($user['modcomment'] != '' ? $the_flip_box_7 . '<div class="has-text-left" id="box_7"><hr>' . format_comment($user['modcomment']) . '</div>' : '') . '</td></tr>';
    }
}
if ($CURUSER['blocks']['userdetails_page'] & block_userdetails::SHOWFRIENDS && $BLOCKS['userdetails_showfriends_on']) {
    require_once BLOCK_DIR . 'userdetails/showfriends.php';
}
if ($CURUSER['blocks']['userdetails_page'] & block_userdetails::JOINED && $BLOCKS['userdetails_joined_on']) {
    require_once BLOCK_DIR . 'userdetails/joined.php';
}
if ($CURUSER['blocks']['userdetails_page'] & block_userdetails::ONLINETIME && $BLOCKS['userdetails_online_time_on']) {
    require_once BLOCK_DIR . 'userdetails/onlinetime.php';
}
if ($CURUSER['blocks']['userdetails_page'] & block_userdetails::BROWSER && $BLOCKS['userdetails_browser_on']) {
    require_once BLOCK_DIR . 'userdetails/browser.php';
}
if ($CURUSER['blocks']['userdetails_page'] & block_userdetails::BIRTHDAY && $BLOCKS['userdetails_birthday_on']) {
    require_once BLOCK_DIR . 'userdetails/birthday.php';
}
if ($CURUSER['blocks']['userdetails_page'] & block_userdetails::CONTACT_INFO && $BLOCKS['userdetails_contact_info_on']) {
    require_once BLOCK_DIR . 'userdetails/contactinfo.php';
}
if ($CURUSER['blocks']['userdetails_page'] & block_userdetails::IPHISTORY && $BLOCKS['userdetails_iphistory_on']) {
    require_once BLOCK_DIR . 'userdetails/iphistory.php';
}
if ($CURUSER['blocks']['userdetails_page'] & block_userdetails::AVATAR && $BLOCKS['userdetails_avatar_on']) {
    require_once BLOCK_DIR . 'userdetails/avatar.php';
}
if ($CURUSER['blocks']['userdetails_page'] & block_userdetails::USERCLASS && $BLOCKS['userdetails_userclass_on']) {
    require_once BLOCK_DIR . 'userdetails/userclass.php';
}
if ($CURUSER['blocks']['userdetails_page'] & block_userdetails::GENDER && $BLOCKS['userdetails_gender_on']) {
    require_once BLOCK_DIR . 'userdetails/gender.php';
}
if ($CURUSER['blocks']['userdetails_page'] & block_userdetails::USERINFO && $BLOCKS['userdetails_userinfo_on']) {
    require_once BLOCK_DIR . 'userdetails/userinfo.php';
}
if ($CURUSER['blocks']['userdetails_page'] & block_userdetails::REPORT_USER && $BLOCKS['userdetails_report_user_on']) {
    require_once BLOCK_DIR . 'userdetails/report.php';
}
if ($CURUSER['blocks']['userdetails_page'] & block_userdetails::USERSTATUS && $BLOCKS['userdetails_user_status_on']) {
    require_once BLOCK_DIR . 'userdetails/userstatus.php';
}
if ($CURUSER['blocks']['userdetails_page'] & block_userdetails::SHOWPM && $BLOCKS['userdetails_showpm_on']) {
    require_once BLOCK_DIR . 'userdetails/showpm.php';
}
$HTMLOUT .= '</table></div>';
$HTMLOUT .= "<div id='activity' class='table-wrapper'>";
$HTMLOUT .= "<table class='table table-bordered table-striped six'>";

if (!empty($user['where_is'])) {
    $HTMLOUT .= "<tr><td class='rowhead'>{$lang['userdetails_location']}</td><td class='has-text-left'>" . format_urls($user['where_is']) . '</td></tr>';
}
$mood_class = $container->get(Mood::class);
$moods = $mood_class->get();
$moodname = (isset($moods['name'][$user['mood']]) ? htmlsafechars((string) $moods['name'][$user['mood']]) : $lang['userdetails_neutral']);
$moodpic = (isset($moods['image'][$user['mood']]) ? htmlsafechars((string) $moods['image'][$user['mood']]) : 'noexpression.gif');
$HTMLOUT .= '<tr><td class="rowhead">' . $lang['userdetails_currentmood'] . '</td><td class="has-text-left"><span class="tool">
       <a href="javascript:;" onclick="PopUp(\'usermood.php\',\'' . $lang['userdetails_mood'] . '\',530,500,1,1);">
       <img src="' . $site_config['paths']['images_baseurl'] . 'smilies/' . $moodpic . '" alt="' . $moodname . '">
       <span class="tip">' . htmlsafechars((string) $user['username']) . ' ' . $moodname . ' !</span></a></span></td></tr>';
if ($CURUSER['blocks']['userdetails_page'] & block_userdetails::SEEDBONUS && $BLOCKS['userdetails_seedbonus_on']) {
    require_once BLOCK_DIR . 'userdetails/seedbonus.php';
}
if ($CURUSER['blocks']['userdetails_page'] & block_userdetails::IRC_STATS && $BLOCKS['userdetails_irc_stats_on']) {
    require_once BLOCK_DIR . 'userdetails/irc.php';
}
if ($CURUSER['blocks']['userdetails_page'] & block_userdetails::REPUTATION && $BLOCKS['userdetails_reputation_on']) {
    require_once BLOCK_DIR . 'userdetails/reputation.php';
}
if ($CURUSER['blocks']['userdetails_page'] & block_userdetails::PROFILE_HITS && $BLOCKS['userdetails_profile_hits_on']) {
    require_once BLOCK_DIR . 'userdetails/userhits.php';
}
if ($CURUSER['blocks']['userdetails_page'] & block_userdetails::FREESTUFFS && $BLOCKS['userdetails_freestuffs_on']) {
    require_once BLOCK_DIR . 'userdetails/freestuffs.php';
}
if ($CURUSER['blocks']['userdetails_page'] & block_userdetails::COMMENTS && $BLOCKS['userdetails_comments_on']) {
    require_once BLOCK_DIR . 'userdetails/comments.php';
}
if ($CURUSER['blocks']['userdetails_page'] & block_userdetails::FORUMPOSTS && $BLOCKS['userdetails_forumposts_on']) {
    require_once BLOCK_DIR . 'userdetails/forumposts.php';
}
if ($CURUSER['blocks']['userdetails_page'] & block_userdetails::INVITEDBY && $BLOCKS['userdetails_invitedby_on']) {
    require_once BLOCK_DIR . 'userdetails/invitedby.php';
}
$HTMLOUT .= '</table></div>';
$HTMLOUT .= "<div id='comments' class='table-wrapper'>";
if ($CURUSER['blocks']['userdetails_page'] & block_userdetails::USERCOMMENTS && $BLOCKS['userdetails_user_comments_on']) {
    require_once BLOCK_DIR . 'userdetails/usercomments.php';
}
$HTMLOUT .= '</div>';
$HTMLOUT .= "<div id='edit' class='table-wrapper'>";

if (($CURUSER['class'] >= UC_STAFF && $user['class'] < $CURUSER['class']) || $CURUSER['class'] >= UC_MAX) {
    $HTMLOUT .= "
    <form method='post' action='./staffpanel.php?tool=modtask' accept-charset='utf-8'>
        <input type='hidden' name='action' value='edituser'>
        <input type='hidden' name='userid' value='$id'>
        <input type='hidden' name='returnto' value='{$_SERVER['PHP_SELF']}?id=$id'>
        <table class='table table-bordered table-striped seven'>
        <tr>
            <td class='rowhead'>{$lang['userdetails_title']}</td><td colspan='3' class='has-text-left'>
                <input type='text' class='w-100' name='title' value='" . htmlsafechars((string) $user['title']) . "'>
            </td>
        </tr>";
    $avatar = htmlsafechars((string) $user['avatar']);
    $HTMLOUT .= "<tr><td class='rowhead'>{$lang['userdetails_avatar_url']}</td><td colspan='3' class='has-text-left'><input type='text' class='w-100' name='avatar' value='$avatar'></td></tr>";

    $HTMLOUT .= "<tr>
    <td class='rowhead'>{$lang['userdetails_signature_rights']}</td>
    <td colspan='3' class='has-text-left'>
        <input name='signature_post' value='yes' type='radio'" . ($user['signature_post'] === 'yes' ? '    checked' : '') . "> {$lang['userdetails_yes']} 
        <input name='signature_post' value='no' type='radio'" . ($user['signature_post'] === 'no' ? ' checked' : '') . "> {$lang['userdetails_disable_signature']}
    </td></tr>
   <!--<tr><td class='rowhead'>{$lang['userdetails_view_signature']}</td>
   <td colspan='3' class='has-text-left'><input name='signatures' value='yes' type='radio'" . ($user['signatures'] === 'yes' ? ' checked' : '') . ">{$lang['userdetails_yes']}
   <input name='signatures' value='no' type='radio'" . ($user['signatures'] === 'no' ? ' checked' : '') . "></td>
   </tr>-->
               <tr>
                      <td class='rowhead'>{$lang['userdetails_signature']}</td>
                      <td colspan='3' class='has-text-left'><textarea class='w-100' rows='2' name='signature'>" . htmlsafechars((string) $user['signature']) . "</textarea></td>
                </tr>
                <tr>
                      <td class='rowhead'>{$lang['userdetails_skype']}</td>
                      <td colspan='3' class='has-text-left'><input type='text' class='w-100' name='skype' value='" . htmlsafechars((string) $user['skype']) . "'></td>
                </tr>
                <tr>
                      <td class='rowhead'>{$lang['userdetails_website']}</td>
                      <td colspan='3' class='has-text-left'><input type='text' class='w-100' name='website' value='" . htmlsafechars((string) $user['website']) . "'></td>
                </tr>";

    if ($CURUSER['class'] >= UC_MAX) {
        $donor = $user['donor'] === 'yes';
        $HTMLOUT .= "
                <tr>
                    <td class='rowhead' class='has-text-right'><b>
                        {$lang['userdetails_donor']}</b>
                    </td>
                    <td colspan='2' class='has-text-centered'>";
        if ($donor) {
            $donoruntil = (int) $user['donoruntil'];
            if ($donoruntil === 0) {
                $HTMLOUT .= $lang['userdetails_arbitrary'];
            } else {
                $HTMLOUT .= $lang['userdetails_donor2'] . ' ' . get_date((int) $user['donoruntil'], 'DATE') . ' [ ' . mkprettytime($donoruntil - TIME_NOW) . " ] {$lang['userdetails_togo']}";
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
                    <input class='w-100' type='text' name='donated' value='" . (int) $user['donated'] . "'>
                    <div class='top10 size_5 has-text-centered'>{$lang['userdetails_tdonations']} " . number_format((float) $user['total_donated'], 2) . '</div>';
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
                    <input name='donor' value='no' type='checkbox'>
                    <div>{$lang['userdetails_bad']}</div>";
        }
        $HTMLOUT .= '
                    </td>
                </tr>';
    }
    if ($CURUSER['class'] === UC_STAFF && $user['class'] > UC_VIP) {
        $HTMLOUT .= "<input type='hidden' name='class' value='{$user['class']}'>";
    } else {
        $HTMLOUT .= "<tr><td class='rowhead'>Class</td><td colspan='3' class='has-text-left'><select name='class' class='w-100'>";
        if ($CURUSER['class'] >= UC_MAX) {
            $maxclass = UC_MAX;
        } elseif ($CURUSER['class'] === UC_STAFF) {
            $maxclass = UC_VIP;
        } else {
            $maxclass = $CURUSER['class'] - 1;
        }
        for ($i = 0; $i <= $maxclass; ++$i) {
            $HTMLOUT .= "<option value='$i'" . ($user['class'] == $i ? ' selected' : '') . '>' . get_user_class_name((int) $i) . '</option>';
        }
        $HTMLOUT .= '</select></td></tr>';
    }
    $supportfor = htmlsafechars((string) $user['supportfor']);
    //$HTMLOUT.= "<tr><td class='rowhead'>{$lang['userdetails_support']}</td><td colspan='3' class='has-text-left'><input type='checkbox' name='support' value='yes'" . (($user['opt1'] & user_options::SUPPORT) ? " checked" : "") . ">{$lang['userdetails_yes']}</td></tr>";
    $HTMLOUT .= "<tr><td class='rowhead'>{$lang['userdetails_support']}</td><td colspan='3' class='has-text-left'><input type='radio' name='support' value='yes'" . ($user['support'] === 'yes' ? ' checked' : '') . ">{$lang['userdetails_yes']}<input type='radio' name='support' value='no'" . ($user['support'] === 'no' ? ' checked' : '') . ">{$lang['userdetails_no']}</td></tr>";
    $HTMLOUT .= "<tr><td class='rowhead'>{$lang['userdetails_supportfor']}</td><td colspan='3' class='has-text-left'><textarea class='w-100' rows='2' name='supportfor'>{$supportfor}</textarea></td></tr>";
    $modcomment = htmlsafechars((string) $user['modcomment']);
    if ($CURUSER['class'] < UC_MAX) {
        $HTMLOUT .= "<tr><td class='rowhead'>{$lang['userdetails_comment']}</td><td colspan='3' class='has-text-left'><textarea class='w-100' rows='6' name='modcomment' readonly='readonly'>$modcomment</textarea></td></tr>";
    } else {
        $HTMLOUT .= "<tr><td class='rowhead'>{$lang['userdetails_comment']}</td><td colspan='3' class='has-text-left'><textarea class='w-100' rows='6' name='modcomment'>$modcomment</textarea></td></tr>";
    }
    $HTMLOUT .= "<tr><td class='rowhead'>{$lang['userdetails_add_comment']}</td><td colspan='3' class='has-text-left'><textarea class='w-100' rows='2' name='addcomment'></textarea></td></tr>";

    $bonuscomment = htmlsafechars((string) $user['bonuscomment']);
    $HTMLOUT .= "<tr><td class='rowhead'>{$lang['userdetails_bonus_comment']}</td><td colspan='3' class='has-text-left'><textarea class='w-100' rows='6' name='bonuscomment' readonly='readonly'>$bonuscomment</textarea></td></tr>";

    $HTMLOUT .= "<tr><td class='rowhead'>{$lang['userdetails_enabled']}</td><td colspan='3' class='has-text-left'><input name='enabled' value='yes' type='radio'" . ($enabled ? ' checked' : '') . ">{$lang['userdetails_yes']} <input name='enabled' value='no' type='radio'" . (!$enabled ? ' checked' : '') . ">{$lang['userdetails_no']}</td></tr>";
    if ($CURUSER['class'] >= UC_STAFF) {
        $HTMLOUT .= "
                <tr>
                    <td class='rowhead'>{$lang['userdetails_freeleech_slots']}</td>
                    <td colspan='3' class='has-text-left'>
                        <input class='w-100' type='text' name='freeslots' value='" . (int) $user['freeslots'] . "'>
                    </td>
                </tr>";
    }
    if ($CURUSER['class'] >= UC_ADMINISTRATOR) {
        $free_switch = $user['free_switch'] != 0;
        $HTMLOUT .= "<tr><td class='rowhead'" . (!$free_switch ? ' rowspan="2"' : '') . ">{$lang['userdetails_freeleech_status']}</td>
                <td class='has-text-left w-20'>" . ($free_switch ? "<input name='free_switch' value='42' type='radio'>{$lang['userdetails_remove_freeleech']}" : $lang['userdetails_no_freeleech']) . '</td>';
        if ($free_switch) {
            if ($user['free_switch'] == 1) {
                $HTMLOUT .= '<td class="has-text-centered">(' . $lang['userdetails_unlimited_d'] . ')</td></tr>';
            } else {
                $HTMLOUT .= "<td class='has-text-centered'>{$lang['userdetails_until']} " . get_date((int) $user['free_switch'], 'DATE') . ' (' . mkprettytime($user['free_switch'] - TIME_NOW) . " {$lang['userdetails_togo']})</td></tr>";
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
         <tr><td colspan="3" class="has-text-left">' . $lang['userdetails_pm_comment'] . ':<input type="text" class="w-100" name="free_pm"></td></tr>';
        }
    }

    if ($CURUSER['class'] >= UC_STAFF) {
        $downloadpos = $user['downloadpos'] != 1;
        $HTMLOUT .= "<tr><td class='rowhead'" . (!$downloadpos ? ' rowspan="2"' : '') . ">{$lang['userdetails_dpos']}</td>
               <td class='has-text-left' width='20%'>" . ($downloadpos ? "<input name='downloadpos' value='42' type='radio'>{$lang['userdetails_remove_download_d']}" : $lang['userdetails_no_disablement']) . '</td>';
        if ($downloadpos) {
            if ($user['downloadpos'] == 0) {
                $HTMLOUT .= '<td class="has-text-centered">(' . $lang['userdetails_unlimited_d'] . ')</td></tr>';
            } else {
                $HTMLOUT .= "<td class='has-text-centered'>{$lang['userdetails_until']} " . get_date((int) $user['downloadpos'], 'DATE') . ' (' . mkprettytime($user['downloadpos'] - TIME_NOW) . " {$lang['userdetails_togo']})</td></tr>";
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
        <tr><td colspan="3" class="has-text-left">' . $lang['userdetails_pm_comment'] . ':<input type="text" class="w-100" name="disable_pm"></td></tr>';
        }
    }

    if ($CURUSER['class'] >= UC_STAFF) {
        $uploadpos = $user['uploadpos'] != 1;
        $HTMLOUT .= "<tr><td class='rowhead'" . (!$uploadpos ? ' rowspan="2"' : '') . ">{$lang['userdetails_upos']}</td>
               <td class='has-text-left' width='20%'>" . ($uploadpos ? "<input name='uploadpos' value='42' type='radio'>{$lang['userdetails_remove_upload_d']}" : $lang['userdetails_no_disablement']) . '</td>';
        if ($uploadpos) {
            if ($user['uploadpos'] == 0) {
                $HTMLOUT .= '<td class="has-text-centered">(' . $lang['userdetails_unlimited_d'] . ')</td></tr>';
            } else {
                $HTMLOUT .= "<td class='has-text-centered'>{$lang['userdetails_until']} " . get_date((int) $user['uploadpos'], 'DATE') . ' (' . mkprettytime($user['uploadpos'] - TIME_NOW) . " {$lang['userdetails_togo']})</td></tr>";
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
        <tr><td colspan="3" class="has-text-left">' . $lang['userdetails_pm_comment'] . ':<input type="text" class="w-100" name="updisable_pm"></td></tr>';
        }
    }

    if ($CURUSER['class'] >= UC_STAFF) {
        $sendpmpos = $user['sendpmpos'] != 1;
        $HTMLOUT .= "<tr><td class='rowhead'" . (!$sendpmpos ? ' rowspan="2"' : '') . ">{$lang['userdetails_pmpos']}</td>
               <td class='has-text-left' width='20%'>" . ($sendpmpos ? "<input name='sendpmpos' value='42' type='radio'>{$lang['userdetails_remove_pm_d']}" : $lang['userdetails_no_disablement']) . '</td>';
        if ($sendpmpos) {
            if ($user['sendpmpos'] == 0) {
                $HTMLOUT .= '<td class="has-text-centered">(' . $lang['userdetails_unlimited_d'] . ')</td></tr>';
            } else {
                $HTMLOUT .= "<td class='has-text-centered'>{$lang['userdetails_until']} " . get_date((int) $user['sendpmpos'], 'DATE') . ' (' . mkprettytime($user['sendpmpos'] - TIME_NOW) . " {$lang['userdetails_togo']})</td></tr>";
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
        <tr><td colspan="3" class="has-text-left">' . $lang['userdetails_pm_comment'] . ':<input type="text" class="w-100" name="pmdisable_pm"></td></tr>';
        }
    }

    if ($CURUSER['class'] >= UC_STAFF) {
        $chatpost = $user['chatpost'] != 1;
        $HTMLOUT .= "<tr><td class='rowhead'" . (!$chatpost ? ' rowspan="2"' : '') . ">{$lang['userdetails_chatpos']}</td>
               <td class='has-text-left' width='20%'>" . ($chatpost ? "<input name='chatpost' value='42' type='radio'>{$lang['userdetails_remove_shout_d']}" : $lang['userdetails_no_disablement']) . '</td>';
        if ($chatpost) {
            if ($user['chatpost'] == 0) {
                $HTMLOUT .= '<td class="has-text-centered">(' . $lang['userdetails_unlimited_d'] . ')</td></tr>';
            } else {
                $HTMLOUT .= "<td class='has-text-centered'>{$lang['userdetails_until']} " . get_date((int) $user['chatpost'], 'DATE') . ' (' . mkprettytime($user['chatpost'] - TIME_NOW) . " {$lang['userdetails_togo']})</td></tr>";
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
        <tr><td colspan="3" class="has-text-left">' . $lang['userdetails_pm_comment'] . ':<input type="text" class="w-100" name="chatdisable_pm"></td></tr>';
        }
    }

    if ($CURUSER['class'] >= UC_STAFF) {
        $avatarpos = $user['avatarpos'] != 1;
        $HTMLOUT .= "<tr><td class='rowhead'" . (!$avatarpos ? ' rowspan="2"' : '') . ">{$lang['userdetails_avatarpos']}</td>
          <td class='has-text-left' width='20%'>" . ($avatarpos ? "<input name='avatarpos' value='42' type='radio'>{$lang['userdetails_remove_avatar_d']}" : $lang['userdetails_no_disablement']) . '</td>';
        if ($avatarpos) {
            if ($user['avatarpos'] == 0) {
                $HTMLOUT .= '<td class="has-text-centered">(' . $lang['userdetails_unlimited_d'] . ')</td></tr>';
            } else {
                $HTMLOUT .= "<td class='has-text-centered'>{$lang['userdetails_until']} " . get_date((int) $user['avatarpos'], 'DATE') . ' (' . mkprettytime($user['avatarpos'] - TIME_NOW) . " {$lang['userdetails_togo']})</td></tr>";
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
        <tr><td colspan="3" class="has-text-left">' . $lang['userdetails_pm_comment'] . ':<input type="text" class="w-100" name="avatardisable_pm"></td></tr>';
        }
    }

    if ($CURUSER['class'] >= UC_STAFF) {
        $immunity = $user['immunity'] != 0;
        $HTMLOUT .= "<tr><td class='rowhead'" . (!$immunity ? ' rowspan="2"' : '') . ">{$lang['userdetails_immunity']}</td>
               <td class='has-text-left' width='20%'>" . ($immunity ? "<input name='immunity' value='42' type='radio'>{$lang['userdetails_remove_immunity']}" : $lang['userdetails_no_immunity']) . '</td>';
        if ($immunity) {
            if ($user['immunity'] == 1) {
                $HTMLOUT .= '<td class="has-text-centered">(' . $lang['userdetails_unlimited_d'] . ')</td></tr>';
            } else {
                $HTMLOUT .= "<td class='has-text-centered'>{$lang['userdetails_until']} " . get_date((int) $user['immunity'], 'DATE') . ' (' . mkprettytime($user['immunity'] - TIME_NOW) . " {$lang['userdetails_togo']})</td></tr>";
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
        <tr><td colspan="3" class="has-text-left">' . $lang['userdetails_pm_comment'] . ':<input type="text" class="w-100" name="immunity_pm"></td></tr>';
        }
    }

    if ($CURUSER['class'] >= UC_STAFF) {
        $leechwarn = $user['leechwarn'] != 0;
        $HTMLOUT .= "<tr><td class='rowhead'" . (!$leechwarn ? ' rowspan="2"' : '') . ">{$lang['userdetails_leechwarn']}</td>
               <td class='has-text-left' width='20%'>" . ($leechwarn ? "<input name='leechwarn' value='42' type='radio'>{$lang['userdetails_remove_leechwarn']}" : $lang['userdetails_no_leechwarn']) . '</td>';
        if ($leechwarn) {
            if ($user['leechwarn'] == 1) {
                $HTMLOUT .= '<td class="has-text-centered">(' . $lang['userdetails_unlimited_d'] . ')</td></tr>';
            } else {
                $HTMLOUT .= "<td class='has-text-centered'>{$lang['userdetails_until']} " . get_date((int) $user['leechwarn'], 'DATE') . ' (' . mkprettytime($user['leechwarn'] - TIME_NOW) . " {$lang['userdetails_togo']})</td></tr>";
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
        <tr><td colspan="3" class="has-text-left">' . $lang['userdetails_pm_comment'] . ':<input type="text" class="w-100" name="leechwarn_pm"></td></tr>';
        }
    }

    if ($CURUSER['class'] >= UC_STAFF) {
        $warned = $user['warned'] != 0;
        $HTMLOUT .= "<tr><td class='rowhead'" . (!$warned ? ' rowspan="2"' : '') . ">{$lang['userdetails_warned']}</td>
               <td class='has-text-left' width='20%'>" . ($warned ? "<input name='warned' value='42' type='radio'>{$lang['userdetails_remove_warned']}" : $lang['userdetails_no_warning']) . '</td>';
        if ($warned) {
            if ($user['warned'] == 1) {
                $HTMLOUT .= '<td class="has-text-centered">(' . $lang['userdetails_unlimited_d'] . ')</td></tr>';
            } else {
                $HTMLOUT .= "<td class='has-text-centered'>{$lang['userdetails_until']} " . get_date((int) $user['warned'], 'DATE') . ' (' . mkprettytime($user['warned'] - TIME_NOW) . " {$lang['userdetails_togo']})</td></tr>";
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
        <tr><td colspan="3" class="has-text-left">' . $lang['userdetails_pm_comm'] . '<input type="text" class="w-100" name="warned_pm"></td></tr>';
        }
    }

    if ($CURUSER['class'] >= UC_STAFF) {
        $game_access = $user['game_access'] != 1;
        $HTMLOUT .= "<tr><td class='rowhead'" . (!$game_access ? ' rowspan="2"' : '') . ">{$lang['userdetails_games']}</td>
           <td class='has-text-left' width='20%'>" . ($game_access ? "<input name='game_access' value='42' type='radio'>{$lang['userdetails_remove_game_d']}" : $lang['userdetails_no_disablement']) . '</td>';
        if ($game_access) {
            if ($user['game_access'] == 0) {
                $HTMLOUT .= '<td class="has-text-centered">(' . $lang['userdetails_unlimited_d'] . ')</td></tr>';
            } else {
                $HTMLOUT .= "<td class='has-text-centered'>{$lang['userdetails_until']} " . get_date((int) $user['game_access'], 'DATE') . ' (' . mkprettytime($user['game_access'] - TIME_NOW) . " {$lang['userdetails_togo']})</td></tr>";
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
        <tr><td colspan="3" class="has-text-left">' . $lang['userdetails_pm_comment'] . ':<input type="text" class="w-100" name="game_disable_pm"></td></tr>';
        }
    }

    if ($CURUSER['class'] >= UC_MAX) {
        $HTMLOUT .= "<tr><td class='rowhead'>{$lang['userdetails_highspeed']}</td><td class='row' colspan='3' class='has-text-left'><input type='radio' name='highspeed' value='yes' " . ($user['highspeed'] === 'yes' ? ' checked' : '') . ">{$lang['userdetails_yes']} <input type='radio' name='highspeed' value='no' " . ($user['highspeed'] === 'no' ? ' checked' : '') . ">{$lang['userdetails_no']}</td></tr>";
    }

    $HTMLOUT .= "<tr><td class='rowhead'>{$lang['userdetails_park']}</td><td colspan='3' class='has-text-left'><input name='parked' value='yes' type='radio'" . ($user['parked'] === 'yes' ? ' checked' : '') . ">{$lang['userdetails_yes']} <input name='parked' value='no' type='radio'" . ($user['parked'] === 'no' ? ' checked' : '') . ">{$lang['userdetails_no']}</td></tr>";
    $HTMLOUT .= "<tr><td class='rowhead'>{$lang['userdetails_reset']}</td><td colspan='3'><input type='checkbox' name='reset_torrent_pass' value='1'><span class='small left10'>{$lang['userdetails_pass_msg']}</span></td></tr>";
    $HTMLOUT .= "<tr><td class='rowhead'>{$lang['userdetails_reset_auth']}</td><td colspan='3'><input type='checkbox' name='reset_auth' value='1'><span class='small left10'>{$lang['userdetails_auth_msg']}</span></td></tr>";
    $HTMLOUT .= "<tr><td class='rowhead'>{$lang['userdetails_reset_apikey']}</td><td colspan='3'><input type='checkbox' name='reset_apikey' value='1'><span class='small left10'>{$lang['userdetails_apikey_msg']}</span></td></tr>";

    if ($CURUSER['class'] >= UC_STAFF) {
        $HTMLOUT .= "<tr><td class='rowhead'>{$lang['userdetails_bonus_points']}</td><td colspan='3' class='has-text-left'><input type='text' class='w-100' name='seedbonus' value='" . (int) $user['seedbonus'] . "'></td></tr>";
    }

    if ($CURUSER['class'] >= UC_STAFF) {
        $HTMLOUT .= "<tr><td class='rowhead'>{$lang['userdetails_rep_points']}</td><td colspan='3' class='has-text-left'><input type='text' class='w-100' name='reputation' value='" . (int) $user['reputation'] . "'></td></tr>";
    }

    $HTMLOUT .= "<tr><td class='rowhead'>{$lang['userdetails_invright']}</td><td colspan='3' class='has-text-left'><input type='radio' name='invite_on' value='yes'" . ($user['invite_on'] === 'yes' ? ' checked' : '') . "> {$lang['userdetails_yes']}<input type='radio' name='invite_on' value='no'" . ($user['invite_on'] === 'no' ? ' checked' : '') . "> {$lang['userdetails_no']}</td></tr>";

    $HTMLOUT .= "<tr><td class='rowhead'><b>{$lang['userdetails_invites']}</b></td><td colspan='3' class='has-text-left'><input type='text' class='w-100' name='invites' value='" . (int) $user['invites'] . "'></td></tr>";

    $HTMLOUT .= "<tr>
                  <td class='rowhead'>{$lang['userdetails_avatar_rights']}</td>
                  <td colspan='3' class='has-text-left'><input name='view_offensive_avatar' value='yes' type='radio'" . ($user['view_offensive_avatar'] === 'yes' ? ' checked' : '') . ">{$lang['userdetails_yes']}
                  <input name='view_offensive_avatar' value='no' type='radio'" . ($user['view_offensive_avatar'] === 'no' ? ' checked' : '') . ">{$lang['userdetails_no']} </td>
                 </tr>
                 <tr>
                  <td class='rowhead'>{$lang['userdetails_offensive']}</td>
                  <td colspan='3' class='has-text-left'><input name='offensive_avatar' value='yes' type='radio'" . ($user['offensive_avatar'] === 'yes' ? ' checked' : '') . ">{$lang['userdetails_yes']}
                  <input name='offensive_avatar' value='no' type='radio'" . ($user['offensive_avatar'] === 'no' ? ' checked' : '') . ">{$lang['userdetails_no']} </td>
                </tr>
                <tr>
                  <td class='rowhead'>{$lang['userdetails_view_offensive']}</td>
                  <td colspan='3' class='has-text-left'><input name='avatar_rights' value='yes' type='radio'" . ($user['avatar_rights'] === 'yes' ? ' checked' : '') . ">{$lang['userdetails_yes']}
                  <input name='avatar_rights' value='no' type='radio'" . ($user['avatar_rights'] == 'no' ? ' checked' : '') . ">{$lang['userdetails_no']} </td>
               </tr>";
    $HTMLOUT .= '<tr>
                      <td class="rowhead">' . $lang['userdetails_hnr'] . '</td>
                      <td colspan="3" class="has-text-left"><input type="text" class="w-100" name="hit_and_run_total" value="' . (int) $user['hit_and_run_total'] . '"></td>
                </tr>
                 <tr>
                     <td class="rowhead">' . $lang['userdetails_suspended'] . '</td>
                     <td colspan="3" class="has-text-left"><input name="suspended" value="yes" type="radio"' . ($user['suspended'] === 'yes' ? ' checked' : '') . '>' . $lang['userdetails_yes'] . '
                     <input name="suspended" value="no" type="radio"' . ($user['suspended'] === 'no' ? ' checked' : '') . '>' . $lang['userdetails_no'] . '
        ' . $lang['userdetails_suspended_reason'] . '<br>
                    <input type="text" class="w-100" name="suspended_reason"></td>
                   </tr>
                <!--<tr>
                      <td class="rowhead">' . $lang['userdetails_suspended'] . '</td>
                      <td colspan="3" class="has-text-left"><input name="suspended" value="yes" type="checkbox"' . (($user['opt1'] & user_options::SUSPENDED) ? ' checked' : '') . '>' . $lang['userdetails_yes'] . '
                              ' . $lang['userdetails_suspended_reason'] . '<br>
                      <input type="text" class="w-100" name="suspended_reason"></td>
                </tr>-->
      ';
    $HTMLOUT .= "<tr>
                      <td class='rowhead'>{$lang['userdetails_paranoia']}</td>
                      <td colspan='3' class='has-text-left'>
                      <select name='paranoia' class='w-100'>
                      <option value='0'" . ($user['paranoia'] == 0 ? ' selected' : '') . ">{$lang['userdetails_paranoia_0']}</option>
                      <option value='1'" . ($user['paranoia'] == 1 ? ' selected' : '') . ">{$lang['userdetails_paranoia_1']}</option>
                      <option value='2'" . ($user['paranoia'] == 2 ? ' selected' : '') . ">{$lang['userdetails_paranoia_2']}</option>
                      <option value='3'" . ($user['paranoia'] == 3 ? ' selected' : '') . ">{$lang['userdetails_paranoia_3']}</option>
                      </select></td>
                </tr>
                 <tr>
                     <td class='rowhead'>{$lang['userdetails_forum_rights']}</td>
                     <td colspan='3' class='has-text-left'><input name='forum_post' value='yes' type='radio'" . ($user['forum_post'] === 'yes' ? ' checked' : '') . ">{$lang['userdetails_yes']}
                     <input name='forum_post' value='no' type='radio'" . ($user['forum_post'] === 'no' ? ' checked' : '') . ">{$lang['userdetails_forums_no']}</td>
                    </tr>
                <!--<tr>
                      <td class='rowhead'>{$lang['userdetails_forum_rights']}</td>
                      <td colspan='3' class='has-text-left'><input name='forum_post' value='yes' type='checkbox'" . (($user['opt1'] & user_options::FORUM_POST) ? ' checked' : '') . ">{$lang['userdetails_yes']}</td>
                </tr>-->";

    if ($CURUSER['class'] >= UC_ADMINISTRATOR) {
        $HTMLOUT .= "<tr>
         <td class='rowhead'>{$lang['userdetails_addupload']}</td>
         <td class='has-text-centered'>
        <div class='level'>
            <img src='{$site_config['paths']['images_baseurl']}plus.gif' alt='{$lang['userdetails_change_ratio']}' class='tooltipper' title='{$lang['userdetails_change_ratio']}!' id='uppic' onclick=\"togglepic('{$site_config['paths']['baseurl']}', 'uppic','upchange')\">
            <input type='text' name='amountup' class='w-75'>
        </div>
         </td>
         <td>
         <select name='formatup' class='w-100'>
         <option value='mb'>{$lang['userdetails_MB']}</option>
         <option value='gb'>{$lang['userdetails_GB']}</option></select>
         <input type='hidden' id='upchange' name='upchange' value='plus'>
         </td>
         </tr>
         <tr>
         <td class='rowhead'>{$lang['userdetails_adddownload']}</td>
         <td class='has-text-centered'>
        <div class='level'>
            <img src='{$site_config['paths']['images_baseurl']}plus.gif' alt='{$lang['userdetails_change_ratio']}' class='tooltipper' title='{$lang['userdetails_change_ratio']}!' id='downpic' onclick=\"togglepic('{$site_config['paths']['baseurl']}','downpic','downchange')\">
            <input type='text' name='amountdown' class='w-75'>
        </div>
         </td>
         <td>
         <select name='formatdown' class='w-100'>
         <option value='mb'>{$lang['userdetails_MB']}</option>
         <option value='gb'>{$lang['userdetails_GB']}</option></select>
         <input type='hidden' id='downchange' name='downchange' value='plus'>
         </td></tr>";
    }
    $HTMLOUT .= "<tr><td colspan='3' class='has-text-centered'><input type='submit' class='button is-small' value='{$lang['userdetails_okay']}'></td></tr>";
    $HTMLOUT .= '</table>';
    $HTMLOUT .= '</form>';
}
$HTMLOUT .= '</div></div></div>';

echo stdhead("{$lang['userdetails_details']} " . $user['username']) . wrapper($HTMLOUT) . stdfoot($stdfoot);
