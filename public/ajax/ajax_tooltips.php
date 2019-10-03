<?php

declare(strict_types = 1);

use Pu239\Peer;

require_once __DIR__ . '/../../include/bittorrent.php';
require_once INCL_DIR . 'function_users.php';
require_once CLASS_DIR . 'class_user_options_2.php';
$user = check_user_status();

global $container, $site_config;

header('Content-Type: application/json');
if (!empty($user) && is_array($user)) {
    $upped = mksize($user['uploaded']);
    $downed = mksize($user['downloaded']);
    $peer = $container->get(Peer::class);
    $seed = $peer->getPeersFromUserId($user['id']);

    if (!empty($seed['conn'])) {
        switch ($seed['conn']) {
            case 1:
                $connectable = "<img src='{$site_config['paths']['images_baseurl']}notcon.png' alt='" . _('Not Connectable') . "' class='tooltipper' title='" . _('Not Connectable') . "'>";
                break;

            case 2:
                $connectable = "<img src='{$site_config['paths']['images_baseurl']}yescon.png' alt='" . _('Connectable') . "' class='tooltipper' title='" . _('Connectable') . "'>";
                break;

            default:
                $connectable = _('N/A');
        }
    } else {
        $connectable = _('N/A');
    }

    if ($user['override_class'] != 255) {
        $usrclass = " <a href='{$site_config['paths']['baseurl']}/restoreclass.php' class='tooltipper' title='" . _('Restore Your User Class') . "'><b>" . get_user_class_name($user['override_class']) . '</b></a>';
    } elseif ($user['class'] >= UC_STAFF) {
        $usrclass = " <a href='{$site_config['paths']['baseurl']}/setclass.php' class='tooltipper' title='" . _('Temporarily Change User Class') . "'><b>" . get_user_class_name($user['class']) . '</b></a>';
    } else {
        $usrclass = get_user_class_name($user['class']);
    }
    $member_reputation = get_reputation($user);

    $StatusBar = "
    <span class='navbar-start'>:: " . _('Personal Stats') . "</span>
    <span class='level is-marginless'>
        <span class='navbar-start'>" . _('User Class') . "</span>
        <span>{$usrclass}</span>
    </span>
    <span class='level is-marginless'>
        <span class='navbar-start'>" . _('Reputation') . "</span>
        <span>$member_reputation</span>
    </span>

    <span class='level is-marginless'>
        <span class='navbar-start'>" . _('Invites') . "</span>
        <span><a href='{$site_config['paths']['baseurl']}/invite.php'>{$user['invites']}</a></span>
    </span>
    <span class='level is-marginless'>
        <span class='navbar-start'>" . _('Karma Store') . "</span>
        <span><a href='{$site_config['paths']['baseurl']}/mybonus.php'>" . number_format((float) $user['seedbonus']) . "</a></span>
    </span>
    <span class='level is-marginless'>
        <span class='navbar-start'>" . _('Achievements') . "</span>
        <span><a href='{$site_config['paths']['baseurl']}/achievementhistory.php?id={$user['id']}'>" . $user['achpoints'] . "</a></span>
    </span>
    <br>
    <span class='navbar-start' id='hide_html'>:: " . _('Torrent Stats') . "</span>
    <span class='level is-marginless'>
        <span class='navbar-start'>" . _('Share Ratio') . '</span>
        <span>' . member_ratio($user['uploaded'], $user['downloaded']) . '</span>
    </span>';

    if ($site_config['site']['ratio_free']) {
        $StatusBar .= "
    <span class='level is-marginless'>
        <span class='navbar-start'>" . _('Uploaded') . "</span>
        <span>$upped</span>
    </span>";
    } else {
        $StatusBar .= "
    <span class='level is-marginless'>
        <span class='navbar-start'>" . _('Uploaded') . "</span>
        <span>$upped</span>
    </span>
    <span class='level is-marginless'>
        <span class='navbar-start'>" . _('Downloaded') . "</span>
        <span>$downed</span>
    </span>";
    }

    $got_moods = ($user['opt2'] & user_options_2::GOT_MOODS) === user_options_2::GOT_MOODS;
    $StatusBar .= "
    <span class='level is-marginless'>
        <span class='navbar-start'>" . _('Uploading Files') . "</span>
        <span>{$seed['yes']}</span>
    </span>
    <span class='level is-marginless'>
        <span class='navbar-start'>" . _('Downloading Files') . "</span>
        <span>{$seed['no']}</span>
    </span>
    <span class='level is-marginless'>
        <span class='navbar-start'>" . _('Connectable') . "</span>
        <span>{$connectable}</span>
    </span>
    " . ($user['class'] >= UC_STAFF || $user['got_blocks'] === 'yes' ? "
    <br>
    <span class='navbar-start'>:: " . _('User Blocks') . "</span>
    <span class='level is-marginless'>
        <span class='navbar-start'>" . _('My Blocks') . "</span>
        <span><a href='{$site_config['paths']['baseurl']}/user_blocks.php'>" . _('Click here') . '</a></span>' : '') . '
    </span>
    ' . ($user['class'] >= UC_STAFF || $got_moods ? "
    <span class='level is-marginless'>
        <span class='navbar-start'>" . _('My Unlocks') . "</span>
        <span><a href='{$site_config['paths']['baseurl']}/user_unlocks.php'>" . _('Click here') . '</a></span>' : '') . '
    </span>';

    echo json_encode($StatusBar);
} else {
    echo json_encode('failed...');
}
