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

$viewer = check_user_status();
$edit_profile = $friend_links = $shitty_link = $sharemark_link = '';
$start = microtime(true);
$stdfoot = [
    'js' => [
        get_file_name('userdetails_js'),
    ],
];

use Delight\Auth\Auth;
use Pu239\Cache;
use Pu239\Database;
use Pu239\Mood;
use Pu239\Roles;
use Pu239\Session;
use Pu239\Snatched;
use Pu239\User;

global $container, $site_config;

$snatched = $container->get(Snatched::class);
$cache = $container->get(Cache::class);
$fluent = $container->get(Database::class);
$id = !empty($_GET['id']) ? (int) $_GET['id'] : $viewer['id'];
if (!is_valid_id($id)) {
    stderr(_('Error'), _('Bad ID.'));
}
$users_class = $container->get(User::class);
$user = $users_class->getUserFromId($id);
if (empty($user)) {
    stderr(_('Error'), _('Invalid UserID'));
} elseif ($user['verified'] === 0) {
    stderr(_('Error'), _('Pending'));
} elseif ($user['paranoia'] === 3 && !has_access($viewer['class'], UC_STAFF, 'coder') && $user['id'] != $viewer['id']) {
    stderr(_('Error'), '<span><img src="' . $site_config['paths']['images_baseurl'] . 'smilies/tinfoilhat.gif" alt="' . _('I wear a tin-foil hat!') . '" class="tooltipper" title="' . _('I wear a tin-foil hat!') . '">
       ' . _('This members paranoia settings are at tinfoil hat levels!!!') . ' <img src="' . $site_config['paths']['images_baseurl'] . 'smilies/tinfoilhat.gif" alt="' . _('I wear a tin-foil hat!') . '" class="tooltipper" title="' . _('I wear a tin-foil hat!') . '"></span>');
    die();
}
if (isset($_GET['delete_hit_and_run']) && has_access($viewer['class'], UC_STAFF, 'coder')) {
    $delete_me = isset($_GET['delete_hit_and_run']) ? (int) $_GET['delete_hit_and_run'] : 0;
    if (!is_valid_id($delete_me)) {
        stderr(_('Error'), _('Bad ID.'));
    }
    $update = [
        'hit_and_run' => 0,
        'mark_of_cain' => 'no',
    ];
    if (!$snatched->update_by_id($update, $delete_me)) {
        stderr(_('Error'), _('H&R not deleted!'));
    }
    header("Location: {$_SERVER['PHP_SELF']}?id={$user['id']}&completed=1");
    die();
}
$session = $container->get(Session::class);
if (isset($_GET['force_logout']) && ($viewer['id'] != $user['id'] && has_access($viewer['class'], UC_STAFF, 'coder') || has_access($viewer['class'], UC_MAX, ''))) {
    $cache->set('forced_logout_' . $viewer['id'], TIME_NOW);
    $session->set('is-success', 'This user will be forced to logout on next page view');
}
if (has_access($viewer['class'], UC_STAFF, 'coder') || $user['id'] === $viewer['id']) {
    $ip = getip($user['id']);
    $addr = gethostbyaddr($ip) . "($ip)";
}
if ($user['perms'] & PERMS_STEALTH) {
    $joindate = _('N/A');
} else {
    $joindate = get_date((int) $user['registered'], '');
}
$lastseen = $user['last_access'];
if ($lastseen == 0 || $user['perms'] & PERMS_STEALTH) {
    $lastseen = _('never');
} else {
    $lastseen = get_date((int) $user['last_access'], '', 0, 1);
}
if (has_access($viewer['class'], $site_config['allowed']['enable_invincible'], '') && isset($_GET['nologip'])) {
    require_once INCL_DIR . 'nologip.php';
    if ($_GET['nologip'] === 'yes') {
        nologip($user['id'], true);
    } else {
        nologip($user['id'], false);
    }
}
if (has_access($viewer['class'], $site_config['allowed']['enable_invincible'], '') && isset($_GET['invincible'])) {
    require_once INCL_DIR . 'invincible.php';
    if ($_GET['invincible'] === 'yes') {
        invincible($user['id'], true);
    } else {
        invincible($user['id'], false);
    }
}
if ((has_access($viewer['class'], UC_STAFF, 'coder') || $user['id'] === $viewer['id'] || has_access($user['class'], UC_STAFF, '')) && isset($_GET['stealth'])) {
    require_once INCL_DIR . 'stealth.php';
    if ($_GET['stealth'] === 'yes') {
        stealth($user['id']);
    } elseif ($_GET['stealth'] === 'no') {
        stealth($user['id'], false);
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
if (!(isset($_GET['hit'])) && $viewer['id'] !== $user['id']) {
    $update = [
        'hits' => $user['hits'] + 1,
    ];
    $users_class->update($update, $user['id']);
}
$HTMLOUT = $perms = $stealth = $suspended = $watched_user = $h1_thingie = '';
if ($user['anonymous_until'] > TIME_NOW && (!has_access($viewer['class'], UC_STAFF, 'coder') && $user['id'] != $viewer['id'])) {
    $HTMLOUT .= "
    <div class='table-wrapper'>
        <table class='table table-bordered table-striped'>
            <tr>
                <td colspan='3' class='has-text-centered'>" . _('This users profile is protected, because his or her status is anonymous!') . '</td>
            </tr>';
    if ($user['avatar']) {
        $HTMLOUT .= "
            <tr>
                <td colspan='3' class='has-text-centered'>
                    <img src='" . url_proxy($user['avatar'], true) . "' alt='Avatar'>
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
                    <form method='get' action='{$site_config['paths']['baseurl']}/messages.php?action=send_message' enctype='multipart/form-data' accept-charset='utf-8'>
                        <input type='hidden' name='receiver' value='" . (int) $user['id'] . "'>
                        <input type='submit' value='" . _('Send Message') . "'>
                    </form>";
    if (has_access($viewer['class'], UC_STAFF, 'coder') && $user['id'] != $viewer['id']) {
        $title = _('Anonymous User');
        $breadcrumbs = [
            "<a href='{$_SERVER['PHP_SELF']}'>$title</a>",
        ];
        echo stdhead($title, [], 'page-wrapper', $breadcrumbs) . wrapper($HTMLOUT) . stdfoot();
    }
    $HTMLOUT .= '
                </td>
            </tr>
        </table>
    </div>';
}
$h1_thingie = ((isset($_GET['sn']) || isset($_GET['wu'])) ? '<h1>' . _('Member Updated') . '</h1>' : '');
if ($viewer['id'] != $user['id'] && has_access($viewer['class'], UC_STAFF, 'coder')) {
    $suspended .= ($user['status'] === 5 ? '  <img src="' . $site_config['paths']['images_baseurl'] . 'smilies/excl.gif" alt="' . _('Suspended') . '" class="tooltipper" title="' . _('Suspended') . '"> <b>' . _('This account has been suspended') . '</b> <img src="' . $site_config['paths']['images_baseurl'] . 'smilies/excl.gif" alt="' . _('Suspended') . '" class="tooltipper" title="' . _('Suspended') . '">' : '');
    $watched_user .= ($user['watched_user'] == 0 ? '' : '  <img src="' . $site_config['paths']['images_baseurl'] . 'smilies/excl.gif" alt="' . _('Watched User') . '" class="tooltipper" title="' . _('Watched User') . '"> <b>' . _('This account is currently on the') . ' <a href="' . $site_config['paths']['baseurl'] . '/staffpanel.php?tool=watched_users">' . _('watched user list') . '</a></b> <img src="' . $site_config['paths']['images_baseurl'] . 'smilies/excl.gif" alt="' . _('Watched User') . '" class="tooltipper" title="' . _('Watched User') . '">');
}
$perms .= (has_access($viewer['class'], UC_STAFF, 'coder') ? (($user['perms'] & PERMS_BYPASS_BAN) ? '  <img src="' . $site_config['paths']['images_baseurl'] . 'smilies/super.gif" alt="' . _('Invincible!') . '"  class="tooltipper" title="' . _('Invincible!') . '">' : '') : '');
$stealth .= (has_access($viewer['class'], UC_STAFF, 'coder') ? (($user['perms'] & PERMS_STEALTH) ? '  <img src="' . $site_config['paths']['images_baseurl'] . 'smilies/ninja.gif" alt="' . _('Stealth mode!') . '"  class="tooltipper" title="' . _('Stealth mode!') . '">' : '') : '');
$enabled = $user['status'] === 0;
$parked = $user['status'] === 1 ? _('This Account is Currently Parked!') : '';

$h1 = "
                <h1 class='has-text-centered'>" . format_username((int) $user['id']) . "$country$stealth$watched_user$suspended$h1_thingie$perms$parked</h1>";
if (!$enabled) {
    $h1 .= _('This account has been disabled');
} elseif ($viewer['id'] != $user['id']) {
    $friend = $cache->get('Friends_' . $user['id']);
    if ($friend === false || is_null($friend)) {
        $friend = $fluent->from('friends')
                         ->select(null)
                         ->select('COUNT(id) AS count')
                         ->where('userid = ?', $user['id'])
                         ->where('friendid = ?', $viewer['id'])
                         ->fetch('count');
        $cache->set('Friends_' . $user['id'], $friend, $site_config['expires']['user_friends']);
    }
    $block = $cache->get('Blocks_' . $user['id']);
    if ($block === false || is_null($block)) {
        $block = $fluent->from('blocks')
                        ->select(null)
                        ->select('COUNT(id) AS count')
                        ->where('userid = ?', $user['id'])
                        ->where('blockid = ?', $viewer['id'])
                        ->fetch('count');
        $cache->set('Blocks_' . $user['id'], $block, $site_config['expires']['user_blocks']);
    }
    if ($friend > 0) {
        $friend_links .= "<li class='is-link margin10'><a href='{$site_config['paths']['baseurl']}/friends.php?action=delete&amp;type=friend&amp;targetid=${user['id']}'>" . _('Remove from Friends') . '</a></li>';
    } else {
        $friend_links .= "<li class='is-link margin10'><a href='{$site_config['paths']['baseurl']}/friends.php?action=add&amp;type=friend&amp;targetid=${user['id']}'>" . _('Add to Friends') . '</a></li>';
    }
    if ($block > 0) {
        $friend_links .= "<li class='is-link margin10'><a href='{$site_config['paths']['baseurl']}/friends.php?action=delete&amp;type=block&amp;targetid=${user['id']}'>" . _('Remove from Flocks') . '</a></li>';
    } else {
        $friend_links .= "<li class='is-link margin10'><a href='{$site_config['paths']['baseurl']}/friends.php?action=add&amp;type=block&amp;targetid=${user['id']}'>" . _('Add to Blocks') . '</a></li>';
    }
}

if (has_access($viewer['class'], UC_STAFF, 'coder')) {
    $shitty = '';
    $shit_list = $cache->get('shit_list_' . $user['id']);
    if ($shit_list === false || is_null($shit_list)) {
        $suspect = $fluent->from('shit_list')
                          ->select(null)
                          ->select('suspect')
                          ->where('userid = ?', $user['id'])
                          ->where('suspect = ?', $viewer['id'])
                          ->fetchAll();
        $cache->set('shit_list_' . $user['id'], $shit_list, $site_config['expires']['shit_list']);
    }
    if ($shit_list > 0) {
        $shitty_link = "<li class='is-link margin10'><a href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=shit_list&amp;action=shit_list'>
                Remove from your
                <img class='tooltipper right5' src='{$site_config['paths']['images_baseurl']}smilies/shit.gif' alt='Shit' class='tooltipper' title='Shit'>
            </a></li>";
    } elseif ($viewer['id'] != $user['id']) {
        $shitty_link .= "<li class='is-link margin10'><a href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=shit_list&amp;action=shit_list&amp;action2=new&amp;shit_list_id={$user['id']}&amp;return_to='{$_SERVER['PHP_SELF']}?id={$user['id']}'>
                " . _('Add member to your shit list') . '
            </a></li>';
    }
}
if ($user['donor'] && $viewer['id'] == $user['id'] || has_access($viewer['class'], UC_SYSOP, 'coder')) {
    $donoruntil = (int) $user['donoruntil'];
    if ($donoruntil === 0) {
        $HTMLOUT .= '';
    } else {
        $h1 .= "
            <div class='top20 has-text-centered'>
                " . _('Donated Status Until') . ' - ' . get_date((int) $user['donoruntil'], 'DATE', 1, 0) . ' [ ' . mkprettytime($donoruntil - TIME_NOW) . ' ] ' . _('To go') . "...</b>
                <br><span class='size_4'> " . _('To re-new your donation click') . " <a class='is-link' href='{$site_config['paths']['baseurl']}/donate.php'>" . _('here') . '</a>.</span>
            </div>';
    }
}
if ($viewer['id'] == $user['id']) {
    $edit_profile = "<li class='is-link margin10'><a href='{$site_config['paths']['baseurl']}/usercp.php?action=default'>" . _('Edit My Profile') . "</a></li>
        <li class='is-link margin10'><a href='{$site_config['paths']['baseurl']}/view_announce_history.php'>" . _('View My Announcements') . '</a></li>';
}
if ($viewer['id'] != $user['id']) {
    $sharemark_link .= "<li class='is-link margin10'><a href='{$site_config['paths']['baseurl']}/sharemarks.php?id=${user['id']}'>" . _('View sharemarks') . '</a></li>';
}
$HTMLOUT .= "
    <div class='bottom20'>
        <ul class='level-center bg-06'>
        $sharemark_link
        $shitty_link
        $friend_links
        $edit_profile";

$HTMLOUT .= $viewer['class'] >= UC_MAX ? ($user['perms'] & PERMS_NO_IP ? "
        <li class='margin10'><a class='is-link tooltipper' title='" . _('IP is not tracked anywhere except peers.<br>IP history is deleted.') . "' href='{$_SERVER['PHP_SELF']}?id={$user['id']}&amp;nologip=no'>" . _('Enable IP Logging') . '</a></li>' : "
        <li class='margin10'><a class='is-link tooltipper' title='" . _('IP is not tracked anywhere except peers.<br>IP history is deleted.') . "' href='{$_SERVER['PHP_SELF']}?id={$user['id']}&amp;nologip=yes'>" . _('Disable IP Logging') . '</a></li>') : '';
$HTMLOUT .= $viewer['class'] >= UC_MAX ? ($user['perms'] & PERMS_BYPASS_BAN ? "
        <li class='margin10'><a class='is-link tooltipper' title='" . _('Invincible means you are exempt from bans') . "' href='{$_SERVER['PHP_SELF']}?id={$user['id']}&amp;invincible=no'>" . _('Remove Invincible') . '</a></li>' : "
        <li class='margin10'><a class='is-link tooltipper' title='" . _('Invincible means you are exempt from bans') . "' href='{$_SERVER['PHP_SELF']}?id={$user['id']}&amp;invincible=yes'>" . _('Make Invincible') . '</a></li>') : '';
$HTMLOUT .= (has_access($viewer['class'], UC_STAFF, 'coder') ? (($user['perms'] & PERMS_STEALTH) ? "
            <li class='margin10'><a class='is-link tooltipper' title='" . _('Stealth mode means that you can lurk with the expectation you will be invisible to all on the site including staff =]') . '<br>' . _('Blah blah blah.') . "' href='{$_SERVER['PHP_SELF']}?id={$user['id']}&amp;stealth=no'>" . _('De-Activate Stealth') . '</a></li>' : "
            <li class='margin10'><a class='is-link tooltipper' title='" . _('Stealth mode means that you can lurk with the expectation you will be invisible to all on the site including staff =]') . '<br>' . _('Blah blah blah.') . "' href='{$_SERVER['PHP_SELF']}?id={$user['id']}&amp;stealth=yes'>" . _('Activate Stealth') . '</a></li>') : '');
$HTMLOUT .= has_access($viewer['class'], UC_SYSOP, 'coder') ? "
            <li class='margin10'><a class='has-text-danger tooltipper' title='Reset this users password' href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=reset&amp;username={$user['username']}&amp;userid={$user['id']}'>Reset Password</a></li>
            <li class='margin10'><a class='has-text-danger tooltipper' title='Force this user to Logout' href='{$_SERVER['PHP_SELF']}?id={$user['id']}&amp;force_logout=yes'>Force Logout</a></li>" : '';
$HTMLOUT .= '
        </ul>
    </div>';

$HTMLOUT .= "
        $h1
        <div>
            <ul class='tabs'>
                <li class='top20'><a href='#torrents'>" . _('Torrents') . "</a></li>
                <li class='top20'><a href='#general'>" . _('General') . "</a></li>
                <li class='top20'><a href='#activity'>" . _('Activity') . "</a></li>
                <li class='top20'><a href='#comments'>" . _('User Comments') . '</a></li>';
if ((has_access($viewer['class'], UC_STAFF, 'coder') && $user['class'] < $viewer['class']) || $viewer['class'] >= UC_MAX) {
    $HTMLOUT .= "
                <li class='top20'><a href='#edit'>" . _('Edit User') . '</a></li>';
}
$HTMLOUT .= '
            </ul>';
$HTMLOUT .= "
            <div class='tabcontent'>
                <div id='torrents' class='table-wrapper'>";

$table_data = '';
if ($viewer['blocks']['userdetails_page'] & class_blocks_userdetails::FLUSH && $BLOCKS['userdetails_flush_on']) {
    require_once BLOCK_DIR . 'userdetails/flush.php';
}
if ($user['id'] === $viewer['id'] || has_access($viewer['class'], UC_ADMINISTRATOR, 'coder')) {
    $table_data .= "
        <tr>
            <td class='rowhead'>Download Torrents</td>
            <td>
                <ul class='level-left buttons'>
                    <li class='right10'>
                        <a href='{$site_config['paths']['baseurl']}/download_multi.php?userid=${user['id']}' class='button is-small tooltipper' title='Download <i><b>all torrents</b></i> that you have previously snatched'>Snatched Torrents</a>
                    </li>
                    <li class='right10'>
                        <a href='{$site_config['paths']['baseurl']}/download_multi.php?owner=true&amp;userid=${user['id']}' class='button is-small tooltipper' title='Download <i><b>all torrents</b></i> that you have uploaded'>Uploaded Torrents</a>
                    </li>";
    if ($user['id'] === $viewer['id'] && has_access($viewer['class'], UC_ADMINISTRATOR, 'coder')) {
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
if ($viewer['blocks']['userdetails_page'] & class_blocks_userdetails::TRAFFIC && $BLOCKS['userdetails_traffic_on']) {
    require_once BLOCK_DIR . 'userdetails/traffic.php';
}
if ($viewer['blocks']['userdetails_page'] & class_blocks_userdetails::SHARE_RATIO && $BLOCKS['userdetails_share_ratio_on']) {
    require_once BLOCK_DIR . 'userdetails/shareratio.php';
}
if ($viewer['blocks']['userdetails_page'] & class_blocks_userdetails::SEEDTIME_RATIO && $BLOCKS['userdetails_seedtime_ratio_on']) {
    require_once BLOCK_DIR . 'userdetails/seedtimeratio.php';
}
if ($viewer['blocks']['userdetails_page'] & class_blocks_userdetails::CONNECTABLE_PORT && $BLOCKS['userdetails_connectable_port_on']) {
    require_once BLOCK_DIR . 'userdetails/connectable.php';
}
if ($viewer['blocks']['userdetails_page'] & class_blocks_userdetails::TORRENTS_BLOCK && $BLOCKS['userdetails_torrents_block_on']) {
    require_once BLOCK_DIR . 'userdetails/torrents_block.php';
}
if ($viewer['blocks']['userdetails_page'] & class_blocks_userdetails::COMPLETED && $BLOCKS['userdetails_completed_on']) {
    require_once BLOCK_DIR . 'userdetails/completed.php';
}
if ($viewer['blocks']['userdetails_page'] & class_blocks_userdetails::SNATCHED_STAFF && $BLOCKS['userdetails_snatched_staff_on']) {
    require_once BLOCK_DIR . 'userdetails/snatched_staff.php';
}

$HTMLOUT .= main_table($table_data);

$HTMLOUT .= "
                </div>
                <div id='general' class='table-wrapper'>
                    <table class='table table-bordered table-striped'>";

if ($viewer['id'] !== $user['id'] && has_access($viewer['class'], UC_STAFF, 'coder')) {
    $the_flip_box = "
        <a id='watched_user'></a>
        <a class='is-link tooltipper' href='#watched_user' onclick=\"flipBox('3')\" title='" . _('Add - Edit - View Watched User') . "'>" . ($user['watched_user'] > 0 ? _('Add - Edit - View') : _('Add - View')) . "<img onclick=\"flipBox('3')\" src='{$site_config['paths']['images_baseurl']}panel_on.gif' id='b_3' width='8' height='8' alt='" . _('Add - Edit - View Watched User') . "' class='tooltipper' title='" . _('Add - Edit - View Watched User') . "'></a>";
    $HTMLOUT .= "
                        <tr>
                            <td class='rowhead'>" . _('Watched User') . "</td>
                            <td class='has-text-left'>" . ($user['watched_user'] > 0 ? '
                                ' . _('Currently being watched since') . ' ' . get_date((int) $user['watched_user'], '') : _('Not currently being watched')) . "
                                $the_flip_box
                                <div class='has-text-left' id='box_3'>
                                    <form method='post' action='ajax/member_input.php' name='notes_for_staff' enctype='multipart/form-data' accept-charset='utf-8'>
                                        <input name='id' type='hidden' value='{$user['id']}'>
                                        <input type='hidden' value='watched_user' name='action'>
                                        " . _('Add to watched users?') . "
                                        <input type='radio' class='right5' value='yes' name='add_to_watched_users' " . ($user['watched_user'] > 0 ? 'checked' : '') . '>' . _('yes') . "
                                        <input type='radio' class='right5' value='no' name='add_to_watched_users' " . ($user['watched_user'] == 0 ? 'checked' : '') . "'>" . _('no') . "<br>
                                        <div id='desc_text'>
                                            * " . _('you must select yes or no if you wish to change the watched user status!') . '<br>
                                            ' . _('you may add, edit or delete the text below without changing their status.') . "
                                        </div>
                                        <textarea id='watched_reason' class='w-100' rows='6' name='watched_reason'>" . format_comment($user['watched_user_reason']) . "</textarea>
                                        <div class='has-text-centered'>
                                            <input id='watched_user_button' type='submit' value='" . _('Submit!') . "' class='button is-small' name='watched_user_button'>
                                        </div>
                                    </form>
                                </div>
                            </td>
                        </tr>";

    $the_flip_box_4 = '[ <a id="staff_notes"></a><a class="is-link tooltipper" href="#staff_notes" onclick="flipBox(\'4\')" id="b_4" title="' . _('Open / Close Staff Notes') . '">view <img onclick="flipBox(\'4\')" src="' . $site_config['paths']['images_baseurl'] . 's/panel_on.gif" id="b_4" width="8" height="8" alt="' . _('Open / Close Staff Notes') . '" class="tooltipper" title="' . _('Open / Close Staff Notes') . '"></a> ]';
    $HTMLOUT .= '<tr><td class="rowhead">' . _('Staff Notes') . '</td><td class="has-text-left">
                            <a class="is-link tooltipper" href="#staff_notes" onclick="flipBox(\'6\')" id="b_6" title="' . _('Add - Edit - View staff note') . '">' . ($user['staff_notes'] !== '' ? _('View - Add - Edit') . ' ' : _('Add') . ' ') . '<img onclick="flipBox(\'6\')" src="' . $site_config['paths']['images_baseurl'] . 'panel_on.gif" id="b_6" width="8" height="8" alt="' . _('Add - Edit - View staff note') . '" class="tooltipper" title="' . _('Add - Edit - View staff note') . '"></a>
                            <div class="has-text-left" id="box_6">
                                <form method="post" action="ajax/member_input.php" name="notes_for_staff" accept-charset="utf-8">
                                    <input name="id" type="hidden" value="' . (int) $user['id'] . '">
                                    <input type="hidden" value="staff_notes" name="action" id="action">
                                    <textarea id="new_staff_note" class="w-100" rows="6" name="new_staff_note">' . format_comment($user['staff_notes']) . '</textarea>
                                    <div class="has-text-centered">
                                        <input id="staff_notes_button" type="submit" value="' . _('Submit!') . '" class="button is-small" name="staff_notes_button">
                                    </div>
                                </form>
                            </div> </td></tr>';

    $the_flip_box_7 = '[ <a id="system_comments"></a><a class="is-link tooltipper" href="#system_comments" onclick="flipBox(\'7\')"  id="b_7" title="' . _('Open / Close System Comments') . ')"><img src="' . $site_config['paths']['images_baseurl'] . 'panel_on.gif" id="b_7" width="8" height="8" alt="' . _('Open / Close System Comments') . '" class="tooltipper" title="' . _('Open / Close System Comments') . '"></a> ]';
    if (!empty($user['modcomment'])) {
        $HTMLOUT .= "<tr><td class='rowhead'>" . _('System Comments') . "</td><td class='has-text-left'>" . ($user['modcomment'] != '' ? $the_flip_box_7 . '<div class="has-text-left" id="box_7"><hr>' . format_comment($user['modcomment']) . '</div>' : '') . '</td></tr>';
    }
}
if ($viewer['blocks']['userdetails_page'] & class_blocks_userdetails::SHOWFRIENDS && $BLOCKS['userdetails_showfriends_on']) {
    require_once BLOCK_DIR . 'userdetails/showfriends.php';
}
if ($viewer['blocks']['userdetails_page'] & class_blocks_userdetails::JOINED && $BLOCKS['userdetails_joined_on']) {
    require_once BLOCK_DIR . 'userdetails/joined.php';
}
if ($viewer['blocks']['userdetails_page'] & class_blocks_userdetails::ONLINETIME && $BLOCKS['userdetails_online_time_on']) {
    require_once BLOCK_DIR . 'userdetails/onlinetime.php';
}
if ($viewer['blocks']['userdetails_page'] & class_blocks_userdetails::BROWSER && $BLOCKS['userdetails_browser_on']) {
    require_once BLOCK_DIR . 'userdetails/browser.php';
}
if ($viewer['blocks']['userdetails_page'] & class_blocks_userdetails::BIRTHDAY && $BLOCKS['userdetails_birthday_on']) {
    require_once BLOCK_DIR . 'userdetails/birthday.php';
}
if ($viewer['blocks']['userdetails_page'] & class_blocks_userdetails::CONTACT_INFO && $BLOCKS['userdetails_contact_info_on']) {
    require_once BLOCK_DIR . 'userdetails/contactinfo.php';
}
if ($viewer['blocks']['userdetails_page'] & class_blocks_userdetails::IPHISTORY && $BLOCKS['userdetails_iphistory_on']) {
    require_once BLOCK_DIR . 'userdetails/iphistory.php';
}
if ($viewer['blocks']['userdetails_page'] & class_blocks_userdetails::AVATAR && $BLOCKS['userdetails_avatar_on']) {
    require_once BLOCK_DIR . 'userdetails/avatar.php';
}
if ($viewer['blocks']['userdetails_page'] & class_blocks_userdetails::USERCLASS && $BLOCKS['userdetails_userclass_on']) {
    require_once BLOCK_DIR . 'userdetails/userclass.php';
}
if ($viewer['blocks']['userdetails_page'] & class_blocks_userdetails::GENDER && $BLOCKS['userdetails_gender_on']) {
    require_once BLOCK_DIR . 'userdetails/gender.php';
}
if ($viewer['blocks']['userdetails_page'] & class_blocks_userdetails::USERINFO && $BLOCKS['userdetails_userinfo_on']) {
    require_once BLOCK_DIR . 'userdetails/userinfo.php';
}
if ($viewer['blocks']['userdetails_page'] & class_blocks_userdetails::REPORT_USER && $BLOCKS['userdetails_report_user_on']) {
    require_once BLOCK_DIR . 'userdetails/report.php';
}
if ($viewer['blocks']['userdetails_page'] & class_blocks_userdetails::USERSTATUS && $BLOCKS['userdetails_user_status_on']) {
    require_once BLOCK_DIR . 'userdetails/userstatus.php';
}
if ($viewer['blocks']['userdetails_page'] & class_blocks_userdetails::SHOWPM && $BLOCKS['userdetails_showpm_on']) {
    require_once BLOCK_DIR . 'userdetails/showpm.php';
}
$HTMLOUT .= '</table></div>';
$HTMLOUT .= "<div id='activity' class='table-wrapper'>";
$HTMLOUT .= "<table class='table table-bordered table-striped'>";

if (!empty($user['where_is'])) {
    $HTMLOUT .= "<tr><td class='rowhead'>" . _('Location') . "</td><td class='has-text-left'>" . format_urls($user['where_is']) . '</td></tr>';
}
$mood_class = $container->get(Mood::class);
$moods = $mood_class->get();
$moodname = (isset($moods['name'][$user['mood']]) ? format_comment($moods['name'][$user['mood']]) : _('is feeling neutral'));
$moodpic = (isset($moods['image'][$user['mood']]) ? format_comment($moods['image'][$user['mood']]) : 'noexpression.gif');
$HTMLOUT .= '<tr><td class="rowhead">' . _('Current Mood') . '</td><td class="has-text-left"><span class="tool">
       <a href="javascript:;" onclick="PopUp(\'usermood.php\',\'' . _('Mood') . '\',530,500,1,1);">
       <img src="' . $site_config['paths']['images_baseurl'] . 'smilies/' . $moodpic . '" alt="' . $moodname . '">
       <span class="tip">' . format_comment($user['username']) . ' ' . $moodname . ' !</span></a></span></td></tr>';
if ($viewer['blocks']['userdetails_page'] & class_blocks_userdetails::SEEDBONUS && $BLOCKS['userdetails_seedbonus_on']) {
    require_once BLOCK_DIR . 'userdetails/seedbonus.php';
}
if ($viewer['blocks']['userdetails_page'] & class_blocks_userdetails::IRC_STATS && $BLOCKS['userdetails_irc_stats_on']) {
    require_once BLOCK_DIR . 'userdetails/irc.php';
}
if ($viewer['blocks']['userdetails_page'] & class_blocks_userdetails::REPUTATION && $BLOCKS['userdetails_reputation_on']) {
    require_once BLOCK_DIR . 'userdetails/reputation.php';
}
if ($viewer['blocks']['userdetails_page'] & class_blocks_userdetails::PROFILE_HITS && $BLOCKS['userdetails_profile_hits_on']) {
    require_once BLOCK_DIR . 'userdetails/userhits.php';
}
if ($viewer['blocks']['userdetails_page'] & class_blocks_userdetails::FREESTUFFS && $BLOCKS['userdetails_freestuffs_on']) {
    require_once BLOCK_DIR . 'userdetails/freestuffs.php';
}
if ($viewer['blocks']['userdetails_page'] & class_blocks_userdetails::COMMENTS && $BLOCKS['userdetails_comments_on']) {
    require_once BLOCK_DIR . 'userdetails/comments.php';
}
if ($viewer['blocks']['userdetails_page'] & class_blocks_userdetails::FORUMPOSTS && $BLOCKS['userdetails_forumposts_on']) {
    require_once BLOCK_DIR . 'userdetails/forumposts.php';
}
if ($viewer['blocks']['userdetails_page'] & class_blocks_userdetails::INVITEDBY && $BLOCKS['userdetails_invitedby_on']) {
    require_once BLOCK_DIR . 'userdetails/invitedby.php';
}
$HTMLOUT .= '</table></div>';
$HTMLOUT .= "<div id='comments' class='table-wrapper'>";
if ($viewer['blocks']['userdetails_page'] & class_blocks_userdetails::USERCOMMENTS && $BLOCKS['userdetails_user_comments_on']) {
    require_once BLOCK_DIR . 'userdetails/usercomments.php';
}
$HTMLOUT .= '</div>';
$HTMLOUT .= "<div id='edit' class='table-wrapper'>";
if ((has_access($viewer['class'], UC_STAFF, 'coder') && $user['class'] < $viewer['class']) || has_access($viewer['class'], UC_MAX, 'coder')) {
    $HTMLOUT .= "
    <form method='post' action='./staffpanel.php?tool=modtask' enctype='multipart/form-data' accept-charset='utf-8'>
        <input type='hidden' name='action' value='edituser'>
        <input type='hidden' name='userid' value='{$user['id']}'>
        <input type='hidden' name='returnto' value='{$_SERVER['PHP_SELF']}?id=${user['id']}'>
        <table class='table table-bordered table-striped'>
        <tr>
            <td class='rowhead'>" . _('Title') . "</td><td colspan='3' class='has-text-left'>
                <input type='text' class='w-100' name='title' value='" . htmlsafechars((string) $user['title']) . "'>
            </td>
        </tr>";
    $avatar = htmlsafechars((string) $user['avatar']);
    $HTMLOUT .= "<tr><td class='rowhead'>" . _('Avatar URL') . "</td><td colspan='3' class='has-text-left'><input type='text' class='w-100' name='avatar' value='$avatar'></td></tr>";
    $HTMLOUT .= "<tr>
    <td class='rowhead'>" . _('Signature Rights') . "</td>
    <td colspan='3'>
        <div class='level-left'>
            <input name='signature_post' value='yes' type='radio' " . ($user['signature_post'] === 'yes' ? 'checked' : '') . " class='right5'>" . _('Yes') . " 
            <input name='signature_post' value='no' type='radio' " . ($user['signature_post'] === 'no' ? 'checked' : '') . " class='left20 right5'>" . _('No Disable this members signature rights.') . "
        </div>
    </td></tr>

               <tr>
                      <td class='rowhead'>" . _('Signature') . "</td>
                      <td colspan='3' class='has-text-left'><textarea class='w-100' rows='2' name='signature'>" . format_comment($user['signature']) . "</textarea></td>
                </tr>
                <tr>
                      <td class='rowhead'>" . _('Skype') . "</td>
                      <td colspan='3' class='has-text-left'><input type='text' class='w-100' name='skype' value='" . format_comment($user['skype']) . "'></td>
                </tr>
                <tr>
                      <td class='rowhead'>" . _('Website') . "</td>
                      <td colspan='3' class='has-text-left'><input type='text' class='w-100' name='website' value='" . format_comment($user['website']) . "'></td>
                </tr>";

    if ($viewer['class'] >= UC_MAX) {
        $donor = $user['donor'] === 'yes';
        $HTMLOUT .= "
                <tr>
                    <td class='rowhead' class='has-text-right'><b>
                        " . _('Donor') . "</b>
                    </td>
                    <td colspan='2' class='has-text-centered'>";
        if ($donor) {
            $donoruntil = (int) $user['donoruntil'];
            if ($donoruntil === 0) {
                $HTMLOUT .= _('Arbitrary duration');
            } else {
                $HTMLOUT .= _('Donated status until') . ' ' . get_date((int) $user['donoruntil'], 'DATE') . ' [ ' . mkprettytime($donoruntil - TIME_NOW) . ' ] ' . _('To go');
            }
        } else {
            $HTMLOUT .= '
                    <div>' . _('Donor for') . "</div>
                     <select name='donorlength' class='bottom10 w-100'>
                        <option value='0'>------</option>
                        <option value='4'>1 " . _('month') . "</option>
                        <option value='6'>6 " . _('weeks') . "</option>
                        <option value='8'>2 " . _('months') . "</option>
                        <option value='10'>10 " . _('weeks') . "</option>
                        <option value='12'>3 " . _('months') . "</option>
                        <option value='255'>" . _('Unlimited') . '</option>
                    </select>';
        }
        $HTMLOUT .= '
                    <div>' . _('Current donation') . "</div>
                    <input class='w-100' type='text' name='donated' value='" . (int) $user['donated'] . "'>
                    <div class='top10 size_5 has-text-centered'>" . _('Total donations &#36;') . ' ' . number_format((float) $user['total_donated'], 2) . '</div>';
        if ($donor) {
            $HTMLOUT .= '
                    <div>' . _('Add to donor time:') . "</div>
                    <select name='donorlengthadd' class='w-100'>
                        <option value='0'>------</option>
                        <option value='4'>1 " . _('month') . "</option>
                        <option value='6'>6 " . _('weeks') . "</option>
                        <option value='8'>2 " . _('months') . "</option>
                        <option value='10'>10 " . _('weeks') . "</option>
                        <option value='12'>3 " . _('months') . "</option>
                        <option value='255'>" . _('Unlimited') . '</option>
                    </select>
                    <div>' . _('Remove donor status') . "</div>
                    <input name='donor' value='no' type='checkbox'>
                    <div>" . _('If they were bad') . '</div>';
        }
        $HTMLOUT .= '
                    </td>
                </tr>';
    }
    if ($viewer['class'] === UC_STAFF && has_access($user['class'], UC_VIP, '')) {
        $HTMLOUT .= "<input type='hidden' name='class' value='{$user['class']}'>";
    } else {
        $HTMLOUT .= "<tr><td class='rowhead'>Class</td><td colspan='3' class='has-text-left'><select name='class' class='w-100'>";
        if (has_access($viewer['class'], UC_MAX, 'coder')) {
            $maxclass = UC_MAX;
        } elseif ($viewer['class'] === UC_STAFF) {
            $maxclass = UC_VIP;
        } else {
            $maxclass = $viewer['class'] - 1;
        }
        for ($i = 0; $i <= $maxclass; ++$i) {
            $HTMLOUT .= "<option value='$i' " . ($user['class'] == $i ? 'selected' : '') . '>' . get_user_class_name((int) $i) . '</option>';
        }
        $HTMLOUT .= '</select></td></tr>';
    }
    $supportfor = format_comment($user['supportfor']);
    $HTMLOUT .= "
            <tr>
                <td class='rowhead'>" . _('Roles') . "</td>
                <td colspan='3'>
                    <div class='level-left'>
                        <input type='checkbox' name='role_coder' value='1' " . ($user['roles_mask'] & Roles::CODER ? 'checked' : '') . " class='right5'>" . _('Coder') . " :
                        <span class='left5 has-text-danger has-text-weight-bold'>" . _('The CODER role has, nearly, the same effect as making the user Sysop, without the class designation. They will have full access to the site and the staff panel.') . "</span>
                    </div>
                    <div class='level-left top10'>
                        <input type='checkbox' name='role_forum_mod' value='1' " . ($user['roles_mask'] & Roles::FORUM_MOD ? 'checked' : '') . " class='right5'>" . _('Forum Moderator') . "
                    </div>
                    <div class='level-left top10'>
                        <input type='checkbox' name='role_torrent_mod' value='1' " . ($user['roles_mask'] & Roles::TORRENT_MOD ? 'checked' : '') . " class='right5'>" . _('Torrent Moderator') . "
                    </div>
                    <div class='level-left top10'>
                        <input type='checkbox' name='role_internal' value='1' " . ($user['roles_mask'] & Roles::INTERNAL ? 'checked' : '') . " class='right5'>" . _('Internal') . "
                    </div>
                    <div class='level-left top10'>
                        <input type='checkbox' name='role_uploader' value='1' " . ($user['roles_mask'] & Roles::UPLOADER ? 'checked' : '') . " class='right5'>" . _('Uploader') . "
                    </div>
                    <div class='top5 bottom5 has-text-info'>" . _('It may take up to 5 minutes for the user to see these changes.') . "</div>
                </td>
            </tr>
            <tr>
                <td class='rowhead'>" . _('Support') . "</td>
                <td colspan='3' class='has-text-left'>
                    <div class='level-left'>
                        <input type='radio' name='support' value='yes' " . ($user['support'] === 'yes' ? 'checked' : '') . " class='right5'>" . _('Yes') . "
                        <input type='radio' name='support' value='no' " . ($user['support'] === 'no' ? 'checked' : '') . " class='left20 right5'>" . _('No') . "
                    </div>
                </td>
            </tr>
            <tr>
                <td class='rowhead'>" . _('Support For') . "</td>
                <td colspan='3' class='has-text-left'>
                    <textarea class='w-100' rows='2' name='supportfor'>{$supportfor}</textarea>
                </td>
            </tr>";
    $modcomment = str_replace('<br>', "\n", $user['modcomment']);
    $HTMLOUT .= "<tr><td class='rowhead'>" . _('Comment') . "</td><td colspan='3' class='has-text-left'><textarea class='w-100' rows='6' name='modcomment' " . (!has_access($viewer['class'], UC_MAX, 'coder') ? "readonly='readonly'" : '') . ">$modcomment</textarea></td></tr>";
    $HTMLOUT .= "<tr><td class='rowhead'>" . _('Add Comment') . "</td><td colspan='3' class='has-text-left'><textarea class='w-100' rows='2' name='addcomment'></textarea></td></tr>";
    $bonuscomment = str_replace('<br>', "\n", $user['bonuscomment']);
    $HTMLOUT .= "
        <tr>
            <td class='rowhead'>" . _('Bonus Comment') . "</td>
            <td colspan='3' class='has-text-left'>
                <textarea class='w-100' rows='6' name='bonuscomment' readonly='readonly'>$bonuscomment</textarea>
            </td>
        </tr>
        <tr>
            <td class='rowhead'>" . _('Enabled') . "</td>
            <td colspan='3' class='has-text-left'>
                <div class='level-left'>
                    <input name='status' value='0' type='radio' " . ($enabled ? 'checked' : '') . " class='right5'>" . _('Yes') . " 
                    <input name='status' value='2' type='radio' " . (!$enabled ? 'checked' : '') . " class='left20 right5'>" . _('No') . "
                </div>
            </td>
        </tr>
        <tr>
            <td class='rowhead'>" . _('Park Account') . "</td>
            <td colspan='3' class='has-text-left'>
                <div class='level-left'>
                    <input name='status' value='1' type='checkbox' " . ($user['status'] === 1 ? 'checked' : '') . " class='right5'>" . _('Yes') . "
                </div>
            </td>
        </tr>
        <tr>
            <td class='rowhead'>" . _('Suspended') . "</td>
            <td colspan='3' class='has-text-left'>
                <div class='level-left'>
                    <input name='status' value='5' type='checkbox' " . ($user['status'] === 5 ? 'checked' : '') . " class='right5'>" . _('Yes') . '
                </div>
                <br>' . _('Please enter the reason, it will be PMed to them') . "<br>
                <input type='text' class='w-100 top10' name='suspended_reason'>
            </td>
        </tr>";

    $HTMLOUT .= '<tr>
                      <td class="rowhead">' . _('Hit and Runs') . '</td>
                      <td colspan="3" class="has-text-left"><input type="text" class="w-100" name="hit_and_run_total" value="' . (int) $user['hit_and_run_total'] . '"></td>
                </tr>';
    if ($viewer['class'] >= UC_STAFF) {
        $HTMLOUT .= "
                <tr>
                    <td class='rowhead'>" . _('Freeleech Slots:') . "</td>
                    <td colspan='3' class='has-text-left'>
                        <input class='w-100' type='text' name='freeslots' value='" . (int) $user['freeslots'] . "'>
                    </td>
                </tr>";
    }
    if (has_access($viewer['class'], UC_ADMINISTRATOR, 'coder')) {
        $personal_freeleech = strtotime($user['personal_freeleech']);
        $HTMLOUT .= "
                <tr>
                    <td class='rowhead' " . ($personal_freeleech < TIME_NOW ? ' rowspan="2"' : '') . '>' . _('Freeleech Status') . "</td>
                    <td class='has-text-left w-20'>" . ($personal_freeleech > TIME_NOW ? "
                        <span class='level-left'>
                            <input name='personal_freeleech' value='42' type='checkbox' class='right5'>" . _('Remove Freeleech Status') : _('No Freeleech Status Set')) . '
                        </span>
                    </td>';
        if ($personal_freeleech > TIME_NOW) {
            $HTMLOUT .= "
                    <td class='has-text-centered'>" . _('Until') . ' ' . get_date($personal_freeleech, 'DATE') . ' (' . mkprettytime($personal_freeleech - TIME_NOW) . ' ' . _('To go') . ')</td>
                </tr>';
        } else {
            $HTMLOUT .= '
                    <td>' . _('Freeleech for') . '
                        <select name="personal_freeleech" class="w-100">
                            <option value="0">------</option>
                            <option value="1">1 ' . _('week') . '</option>
                            <option value="2">2 ' . _('weeks') . '</option>
                            <option value="4">4 ' . _('weeks') . '</option>
                            <option value="8">8 ' . _('weeks') . '</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td colspan="3" class="has-text-left">' . _('PM comment') . ':
                        <input type="text" class="w-100" name="free_pm">
                    </td>
                </tr>';
        }

        $personal_doubleseed = strtotime($user['personal_doubleseed']);
        $HTMLOUT .= "
                <tr>
                    <td class='rowhead' " . ($personal_doubleseed < TIME_NOW ? ' rowspan="2"' : '') . '>' . _('DoubleSeed Status') . "</td>
                    <td class='has-text-left w-20'>" . ($personal_doubleseed > TIME_NOW ? "
                        <span class='level-left'>
                            <input name='personal_doubleseed' value='42' type='checkbox' class='right5'>" . _('Remove DoubleSeed Status') : _('No DoubleSeed Status Set')) . '
                        </span>
                    </td>';
        if ($personal_doubleseed > TIME_NOW) {
            $HTMLOUT .= "
                    <td class='has-text-centered'>" . _('Until') . ' ' . get_date($personal_doubleseed, 'DATE') . ' (' . mkprettytime($personal_doubleseed - TIME_NOW) . ' ' . _('To go') . ')</td>
                </tr>';
        } else {
            $HTMLOUT .= '
                    <td>' . _('DoubleSeed for') . '
                        <select name="personal_doubleseed" class="w-100">
                            <option value="0">------</option>
                            <option value="1">1 ' . _('week') . '</option>
                            <option value="2">2 ' . _('weeks') . '</option>
                            <option value="4">4 ' . _('weeks') . '</option>
                            <option value="8">8 ' . _('weeks') . '</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td colspan="3" class="has-text-left">' . _('PM comment') . ':
                        <input type="text" class="w-100" name="double_pm">
                    </td>
                </tr>';
        }
    }

    if (has_access($viewer['class'], UC_STAFF, 'coder')) {
        $downloadpos = $user['downloadpos'] != 1;
        $HTMLOUT .= "<tr><td class='rowhead'" . (!$downloadpos ? ' rowspan="2"' : '') . '>' . _('Download Possible') . "</td>
               <td class='level-left'>" . ($downloadpos ? "<input name='downloadpos' value='42' type='checkbox' class='right5'>" . _('Remove download disablement') . '' : _('No disablement Status Set')) . '</td>';
        if ($downloadpos) {
            if ($user['downloadpos'] == 0) {
                $HTMLOUT .= '<td class="has-text-centered">(' . _('Unlimited Duration') . ')</td></tr>';
            } else {
                $HTMLOUT .= "<td class='has-text-centered'>" . _('Until') . ' ' . get_date((int) $user['downloadpos'], 'DATE') . ' (' . mkprettytime($user['downloadpos'] - TIME_NOW) . ' ' . _('To go') . ')</td></tr>';
            }
        } else {
            $HTMLOUT .= '<td>' . _('Disable for') . ' <select name="downloadpos" class="w-100">
        <option value="0">------</option>
        <option value="1">1 ' . _('week') . '</option>
        <option value="2">2 ' . _('weeks') . '</option>
        <option value="4">4 ' . _('weeks') . '</option>
        <option value="8">8 ' . _('weeks') . '</option>
        <option value="255">' . _('Unlimited') . '</option>
        </select></td></tr>
        <tr><td colspan="3" class="has-text-left">' . _('PM comment') . ':<input type="text" class="w-100" name="disable_pm"></td></tr>';
        }
    }

    if ($viewer['class'] >= UC_STAFF) {
        $uploadpos = $user['uploadpos'] != 1;
        $HTMLOUT .= "<tr><td class='rowhead'" . (!$uploadpos ? ' rowspan="2"' : '') . '>' . _('Upload Possible') . "</td>
               <td class='level-left'>" . ($uploadpos ? "<input name='uploadpos' value='42' type='checkbox' class='right5'>" . _('Remove upload disablement') . '' : _('No disablement Status Set')) . '</td>';
        if ($uploadpos) {
            if ($user['uploadpos'] == 0) {
                $HTMLOUT .= '<td class="has-text-centered">(' . _('Unlimited Duration') . ')</td></tr>';
            } else {
                $HTMLOUT .= "<td class='has-text-centered'>" . _('Until') . ' ' . get_date((int) $user['uploadpos'], 'DATE') . ' (' . mkprettytime($user['uploadpos'] - TIME_NOW) . ' ' . _('To go') . ')</td></tr>';
            }
        } else {
            $HTMLOUT .= '<td>' . _('Disable for') . ' <select name="uploadpos" class="w-100">
        <option value="0">------</option>
        <option value="1">1 ' . _('week') . '</option>
        <option value="2">2 ' . _('weeks') . '</option>
        <option value="4">4 ' . _('weeks') . '</option>
        <option value="8">8 ' . _('weeks') . '</option>
        <option value="255">' . _('Unlimited') . '</option>
        </select></td></tr>
        <tr><td colspan="3" class="has-text-left">' . _('PM comment') . ':<input type="text" class="w-100" name="updisable_pm"></td></tr>';
        }
    }

    if ($viewer['class'] >= UC_STAFF) {
        $sendpmpos = $user['sendpmpos'] != 1;
        $HTMLOUT .= "<tr><td class='rowhead'" . (!$sendpmpos ? ' rowspan="2"' : '') . '>' . _('PM Possible') . "</td>
               <td class='level-left'>" . ($sendpmpos ? "<input name='sendpmpos' value='42' type='checkbox' class='right5'>" . _('Remove pm disablement') . '' : _('No disablement Status Set')) . '</td>';
        if ($sendpmpos) {
            if ($user['sendpmpos'] == 0) {
                $HTMLOUT .= '<td class="has-text-centered">(' . _('Unlimited Duration') . ')</td></tr>';
            } else {
                $HTMLOUT .= "<td class='has-text-centered'>" . _('Until') . ' ' . get_date((int) $user['sendpmpos'], 'DATE') . ' (' . mkprettytime($user['sendpmpos'] - TIME_NOW) . ' ' . _('To go') . ')</td></tr>';
            }
        } else {
            $HTMLOUT .= '<td>' . _('Disable for') . ' <select name="sendpmpos" class="w-100">
        <option value="0">------</option>
        <option value="1">1 ' . _('week') . '</option>
        <option value="2">2 ' . _('weeks') . '</option>
        <option value="4">4 ' . _('weeks') . '</option>
        <option value="8">8 ' . _('weeks') . '</option>
        <option value="255">' . _('Unlimited') . '</option>
        </select></td></tr>
        <tr><td colspan="3" class="has-text-left">' . _('PM comment') . ':<input type="text" class="w-100" name="pmdisable_pm"></td></tr>';
        }
    }

    if ($viewer['class'] >= UC_STAFF) {
        $chatpost = $user['chatpost'] != 1;
        $HTMLOUT .= "<tr><td class='rowhead'" . (!$chatpost ? ' rowspan="2"' : '') . '>' . _('Chat Possible') . "</td>
               <td class='level-left'>" . ($chatpost ? "<input name='chatpost' value='42' type='checkbox' class='right5'>" . _('Remove Shout disablement') . '' : _('No disablement Status Set')) . '</td>';
        if ($chatpost) {
            if ($user['chatpost'] == 0) {
                $HTMLOUT .= '<td class="has-text-centered">(' . _('Unlimited Duration') . ')</td></tr>';
            } else {
                $HTMLOUT .= "<td class='has-text-centered'>" . _('Until') . ' ' . get_date((int) $user['chatpost'], 'DATE') . ' (' . mkprettytime($user['chatpost'] - TIME_NOW) . ' ' . _('To go') . ')</td></tr>';
            }
        } else {
            $HTMLOUT .= '<td>' . _('Disable for') . ' <select name="chatpost" class="w-100">
        <option value="0">------</option>
        <option value="1">1 ' . _('week') . '</option>
        <option value="2">2 ' . _('weeks') . '</option>
        <option value="4">4 ' . _('weeks') . '</option>
        <option value="8">8 ' . _('weeks') . '</option>
        <option value="255">' . _('Unlimited') . '</option>
        </select></td></tr>
        <tr><td colspan="3" class="has-text-left">' . _('PM comment') . ':<input type="text" class="w-100" name="chatdisable_pm"></td></tr>';
        }
    }

    if ($viewer['class'] >= UC_STAFF) {
        $avatarpos = $user['avatarpos'] != 1;
        $HTMLOUT .= "<tr><td class='rowhead'" . (!$avatarpos ? ' rowspan="2"' : '') . '>' . _('Avatar Possible') . "</td>
          <td class='level-left'>" . ($avatarpos ? "<input name='avatarpos' value='42' type='checkbox' class='right5'>" . _('Remove Avatar disablement') . '' : _('No disablement Status Set')) . '</td>';
        if ($avatarpos) {
            if ($user['avatarpos'] == 0) {
                $HTMLOUT .= '<td class="has-text-centered">(' . _('Unlimited Duration') . ')</td></tr>';
            } else {
                $HTMLOUT .= "<td class='has-text-centered'>" . _('Until') . ' ' . get_date((int) $user['avatarpos'], 'DATE') . ' (' . mkprettytime($user['avatarpos'] - TIME_NOW) . ' ' . _('To go') . ')</td></tr>';
            }
        } else {
            $HTMLOUT .= '<td>' . _('Disable for') . ' <select name="avatarpos" class="w-100">
        <option value="0">------</option>
        <option value="1">1 ' . _('week') . '</option>
        <option value="2">2 ' . _('weeks') . '</option>
        <option value="4">4 ' . _('weeks') . '</option>
        <option value="8">8 ' . _('weeks') . '</option>
        <option value="255">' . _('Unlimited') . '</option>
        </select></td></tr>
        <tr><td colspan="3" class="has-text-left">' . _('PM comment') . ':<input type="text" class="w-100" name="avatardisable_pm"></td></tr>';
        }
    }

    if ($viewer['class'] >= UC_STAFF) {
        $immunity = $user['immunity'] != 0;
        $HTMLOUT .= "<tr><td class='rowhead'" . (!$immunity ? ' rowspan="2"' : '') . '>' . _('Immunity') . "</td>
               <td class='level-left'>" . ($immunity ? "<input name='immunity' value='42' type='checkbox' class='right5'>" . _('Remove immune Status') . '' : _('No immunity Status Set')) . '</td>';
        if ($immunity) {
            if ($user['immunity'] == 1) {
                $HTMLOUT .= '<td class="has-text-centered">(' . _('Unlimited Duration') . ')</td></tr>';
            } else {
                $HTMLOUT .= "<td class='has-text-centered'>" . _('Until') . ' ' . get_date((int) $user['immunity'], 'DATE') . ' (' . mkprettytime($user['immunity'] - TIME_NOW) . ' ' . _('To go') . ')</td></tr>';
            }
        } else {
            $HTMLOUT .= '<td>' . _('Immunity for') . ' <select name="immunity" class="w-100">
        <option value="0">------</option>
        <option value="1">1 ' . _('week') . '</option>
        <option value="2">2 ' . _('weeks') . '</option>
        <option value="4">4 ' . _('weeks') . '</option>
        <option value="8">8 ' . _('weeks') . '</option>
        <option value="255">' . _('Unlimited') . '</option>
        </select></td></tr>
        <tr><td colspan="3" class="has-text-left">' . _('PM comment') . ':<input type="text" class="w-100" name="immunity_pm"></td></tr>';
        }
    }

    if ($viewer['class'] >= UC_STAFF) {
        $leechwarn = $user['leechwarn'] != 0;
        $HTMLOUT .= "<tr><td class='rowhead'" . (!$leechwarn ? ' rowspan="2"' : '') . '>' . _('Leech warn') . "</td>
               <td class='level-left'>" . ($leechwarn ? "<input name='leechwarn' value='42' type='checkbox' class='right5'>" . _('Remove Leechwarn Status') . '' : _('No leech warning Status Set')) . '</td>';
        if ($leechwarn) {
            if ($user['leechwarn'] == 1) {
                $HTMLOUT .= '<td class="has-text-centered">(' . _('Unlimited Duration') . ')</td></tr>';
            } else {
                $HTMLOUT .= "<td class='has-text-centered'>" . _('Until') . ' ' . get_date((int) $user['leechwarn'], 'DATE') . ' (' . mkprettytime($user['leechwarn'] - TIME_NOW) . ' ' . _('To go') . ')</td></tr>';
            }
        } else {
            $HTMLOUT .= '<td>' . _('Leechwarn for') . ' <select name="leechwarn" class="w-100">
        <option value="0">------</option>
        <option value="1">1 ' . _('week') . '</option>
        <option value="2">2 ' . _('weeks') . '</option>
        <option value="4">4 ' . _('weeks') . '</option>
        <option value="8">8 ' . _('weeks') . '</option>
        <option value="255">' . _('Unlimited') . '</option>
        </select></td></tr>
        <tr><td colspan="3" class="has-text-left">' . _('PM comment') . ':<input type="text" class="w-100" name="leechwarn_pm"></td></tr>';
        }
    }

    if ($viewer['class'] >= UC_STAFF) {
        $warned = $user['warned'] != 0;
        $HTMLOUT .= "<tr><td class='rowhead'" . (!$warned ? ' rowspan="2"' : '') . '>' . _('Warned') . "</td>
               <td class='level-left'>" . ($warned ? "<input name='warned' value='42' type='checkbox' class='right5'>" . _('Remove warned Status') . '' : _('No warning Status Set')) . '</td>';
        if ($warned) {
            if ($user['warned'] == 1) {
                $HTMLOUT .= '<td class="has-text-centered">(' . _('Unlimited Duration') . ')</td></tr>';
            } else {
                $HTMLOUT .= "<td class='has-text-centered'>" . _('Until') . ' ' . get_date((int) $user['warned'], 'DATE') . ' (' . mkprettytime($user['warned'] - TIME_NOW) . ' ' . _('To go') . ')</td></tr>';
            }
        } else {
            $HTMLOUT .= '<td>' . _('Warn for') . '<select name="warned" class="w-100">
        <option value="0">------</option>
        <option value="1">' . _fe('{0} week', 1) . '</option>
        <option value="2">' . _fe('{0} weeks', 2) . '</option>
        <option value="4">' . _fe('{0} weeks', 4) . '</option>
        <option value="8">' . _fe('{0} weeks', 8) . '</option>
        <option value="255">' . _('Unlimited') . '</option>
        </select></td></tr>
        <tr><td colspan="3" class="has-text-left">' . _('PM Comment') . ':<input type="text" class="w-100" name="warned_pm"></td></tr>';
        }
    }

    if ($viewer['class'] >= UC_STAFF) {
        $game_access = $user['game_access'] != 1;
        $HTMLOUT .= "<tr><td class='rowhead'" . (!$game_access ? ' rowspan="2"' : '') . '>' . _('Games Possible') . "</td>
           <td class='level-left'>" . ($game_access ? "<input name='game_access' value='42' type='checkbox' class='right5'>" . _('Remove games disablement') . '' : _('No disablement Status Set')) . '</td>';
        if ($game_access) {
            if ($user['game_access'] == 0) {
                $HTMLOUT .= '<td class="has-text-centered">(' . _('Unlimited Duration') . ')</td></tr>';
            } else {
                $HTMLOUT .= "<td class='has-text-centered'>" . _('Until') . ' ' . get_date((int) $user['game_access'], 'DATE') . ' (' . mkprettytime($user['game_access'] - TIME_NOW) . ' ' . _('To go') . ')</td></tr>';
            }
        } else {
            $HTMLOUT .= '<td>' . _('Disable for') . ' <select name="game_access" class="w-100">
        <option value="0">------</option>
        <option value="1">1 ' . _('week') . '</option>
        <option value="2">2 ' . _('weeks') . '</option>
        <option value="4">4 ' . _('weeks') . '</option>
        <option value="8">8 ' . _('weeks') . '</option>
        <option value="255">' . _('Unlimited') . '</option>
        </select></td></tr>
        <tr><td colspan="3" class="has-text-left">' . _('PM comment') . ':<input type="text" class="w-100" name="game_disable_pm"></td></tr>';
        }
    }

    if ($viewer['class'] >= UC_MAX) {
        $HTMLOUT .= "
            <tr>
                <td class='rowhead'>" . _('Highspeed Uploader') . "</td>
                <td class='row' colspan='3'>
                    <div class='level-left'>
                        <input type='radio' name='highspeed' value='yes' " . ($user['highspeed'] === 'yes' ? 'checked' : '') . " class='right5'>" . _('Yes') . "
                        <input type='radio' name='highspeed' value='no' " . ($user['highspeed'] === 'no' ? 'checked' : '') . " class='left20 right5'>" . _('No') . '
                    </div>
                </td>
            </tr>';
    }

    $HTMLOUT .= "<tr><td class='rowhead'>" . _('Reset Torrent Pass') . "</td><td colspan='3'><div class='level-left'><input type='checkbox' name='reset_torrent_pass' value='1'><span class='left5'>" . _('Any active torrents must be downloaded again to continue leeching/seeding.') . '</span></div></td></tr>';
    $HTMLOUT .= "<tr><td class='rowhead'>" . _('Reset Auth Key') . "</td><td colspan='3'><div class='level-left'><input type='checkbox' name='reset_auth' value='1'><span class='left5'>" . _('This is used for Scars Upload Script and possibly others') . '</span></div></td></tr>';
    $HTMLOUT .= "<tr><td class='rowhead'>" . _('Reset API Key') . "</td><td colspan='3'><div class='level-left'><input type='checkbox' name='reset_apikey' value='1'><span class='left5'>" . _('This is used for auto download scripts, such as CouchPotota, SickRage and others') . '</span></div></td></tr>';

    if ($viewer['class'] >= UC_STAFF) {
        $HTMLOUT .= "<tr><td class='rowhead'>" . _('Karma Points') . "</td><td colspan='3' class='has-text-left'><input type='number' class='w-100' name='seedbonus' min='0' max='9999999999999' value='" . (int) $user['seedbonus'] . "'></td></tr>";
    }

    if ($viewer['class'] >= UC_STAFF) {
        $HTMLOUT .= "<tr><td class='rowhead'>" . _('Reputation Points') . "</td><td colspan='3' class='has-text-left'><input type='text' class='w-100' name='reputation' value='" . (int) $user['reputation'] . "'></td></tr>";
    }

    $HTMLOUT .= "
        <tr>
            <td class='rowhead'>" . _('Invite rights') . "</td>
            <td colspan='3'>
                <div class='level-left'>
                    <input type='radio' name='invite_on' value='yes' " . ($user['invite_on'] === 'yes' ? 'checked' : '') . " class='right5'>" . _('Yes') . "
                    <input type='radio' name='invite_on' value='no' " . ($user['invite_on'] === 'no' ? 'checked' : '') . " class='left20 right5'>" . _('No') . "
                </div>
            </td>
        </tr>
        <tr>
            <td class='rowhead'><b>" . _('Invites') . "</b></td>
            <td colspan='3'>
                <input type='text' class='w-100' name='invites' value='" . (int) $user['invites'] . "'>
            </td>
        </tr>
        <tr>
            <td class='rowhead'>" . _('Avatar Rights') . "</td>
            <td colspan='3'>
                <div class='level-left'>
                    <input name='view_offensive_avatar' value='yes' type='radio' " . ($user['view_offensive_avatar'] === 'yes' ? 'checked' : '') . " class='right5'>" . _('Yes') . "
                    <input name='view_offensive_avatar' value='no' type='radio' " . ($user['view_offensive_avatar'] === 'no' ? 'checked' : '') . " class='left20 right5'>" . _('No') . "
                </div>
            </td>
        </tr>
        <tr>
            <td class='rowhead'>" . _('Offensive Avatar') . "</td>
            <td colspan='3'>
                <div class='level-left'>
                    <input name='offensive_avatar' value='yes' type='radio' " . ($user['offensive_avatar'] === 'yes' ? 'checked' : '') . " class='right5'>" . _('Yes') . "
                    <input name='offensive_avatar' value='no' type='radio' " . ($user['offensive_avatar'] === 'no' ? 'checked' : '') . " class='left20 right5'>" . _('No') . "
                </div>
            </td>
        </tr>
        <tr>
            <td class='rowhead'>" . _('View Offensive Avatars') . "</td>
            <td colspan='3'>
                <div class='level-left'>
                    <input name='avatar_rights' value='yes' type='radio' " . ($user['avatar_rights'] === 'yes' ? 'checked' : '') . " class='right5'>" . _('Yes') . "
                    <input name='avatar_rights' value='no' type='radio' " . ($user['avatar_rights'] == 'no' ? 'checked' : '') . " class='left20 right5'>" . _('No') . '
                </div>
            </td>
        </tr>';

    $HTMLOUT .= "<tr>
                      <td class='rowhead'>" . _('Paranoia') . "</td>
                      <td colspan='3' class='has-text-left'>
                      <select name='paranoia' class='w-100'>
                      <option value='0' " . ($user['paranoia'] == 0 ? 'selected' : '') . '>' . _('Totally relaxed') . "</option>
                      <option value='1' " . ($user['paranoia'] == 1 ? 'selected' : '') . '>' . _('Sort of relaxed') . "</option>
                      <option value='2' " . ($user['paranoia'] == 2 ? 'selected' : '') . '>' . _('Paranoid') . "</option>
                      <option value='3' " . ($user['paranoia'] == 3 ? 'selected' : '') . '>' . _('Wears a tin-foil hat') . "</option>
                      </select></td>
                </tr>
                <tr>
                     <td class='rowhead'>" . _('Forum Rights') . "</td>
                     <td colspan='3'>
                        <div class='level-left'>
                            <input name='forum_post' value='yes' type='radio' " . ($user['forum_post'] === 'yes' ? 'checked' : '') . " class='right5'>" . _('Yes') . "
                            <input name='forum_post' value='no' type='radio' " . ($user['forum_post'] === 'no' ? 'checked' : '') . " class='left20 right5'>" . _('No Disable this members forum rights.') . '
                        </div>
                     </td>
                </tr>';

    if (has_access($viewer['class'], UC_ADMINISTRATOR, 'coder')) {
        $HTMLOUT .= "<tr>
         <td class='rowhead'>" . _('Change Upload') . "</td>
         <td class='has-text-centered'>
        <div class='level'>
            <img src='{$site_config['paths']['images_baseurl']}plus.gif' alt='" . _('Change Ratio') . "' class='tooltipper' title='" . _('Change Ratio') . "!' id='uppic' onclick=\"togglepic('{$site_config['paths']['baseurl']}', 'uppic','upchange')\">
            <input type='text' name='amountup' class='w-75'>
        </div>
         </td>
         <td>
         <select name='formatup' class='w-100'>
         <option value='mb'>" . _('MB') . "</option>
         <option value='gb'>" . _('GB') . "</option></select>
         <input type='hidden' id='upchange' name='upchange' value='plus'>
         </td>
         </tr>
         <tr>
         <td class='rowhead'>" . _('Change Download') . "</td>
         <td class='has-text-centered'>
        <div class='level'>
            <img src='{$site_config['paths']['images_baseurl']}plus.gif' alt='" . _('Change Ratio') . "' class='tooltipper' title='" . _('Change Ratio') . "!' id='downpic' onclick=\"togglepic('{$site_config['paths']['baseurl']}','downpic','downchange')\">
            <input type='text' name='amountdown' class='w-75'>
        </div>
         </td>
         <td>
         <select name='formatdown' class='w-100'>
         <option value='mb'>" . _('MB') . "</option>
         <option value='gb'>" . _('GB') . "</option></select>
         <input type='hidden' id='downchange' name='downchange' value='plus'>
         </td></tr>";
    }
    $HTMLOUT .= "<tr><td colspan='3' class='has-text-centered'><input type='submit' class='button is-small' value='" . _('Okay') . "'></td></tr>";
    $HTMLOUT .= '</table>';
    $HTMLOUT .= '</form>';
}
$HTMLOUT .= '</div></div></div>';

$title = _('User Details');
$breadcrumbs = [
    "<a href='{$_SERVER['PHP_SELF']}'>$title</a>",
];
echo stdhead($title, [], 'page-wrapper', $breadcrumbs) . wrapper($HTMLOUT) . stdfoot($stdfoot);
