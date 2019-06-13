<?php

declare(strict_types = 1);

use Pu239\Peer;

require_once __DIR__ . '/../../include/bittorrent.php';
require_once INCL_DIR . 'function_users.php';
require_once CLASS_DIR . 'class_user_options_2.php';
check_user_status();
$lang = array_merge(load_language('global'), load_language('index'));

global $container, $CURUSER, $site_config;

header('Content-Type: application/json');
if (!empty($CURUSER)) {
    $upped = mksize($CURUSER['uploaded']);
    $downed = mksize($CURUSER['downloaded']);
    $peer = $container->get(Peer::class);
    $seed = $peer->getPeersFromUserId($CURUSER['id']);

    if (!empty($seed['conn'])) {
        switch ($seed['conn']) {
            case 1:
                $connectable = "<img src='{$site_config['paths']['images_baseurl']}notcon.png' alt='{$lang['gl_not_connectable']}' class='tooltipper' title='{$lang['gl_not_connectable']}'>";
                break;

            case 2:
                $connectable = "<img src='{$site_config['paths']['images_baseurl']}yescon.png' alt='{$lang['gl_connectable']}' class='tooltipper' title='{$lang['gl_connectable']}'>";
                break;

            default:
                $connectable = "{$lang['gl_na_connectable']}";
        }
    } else {
        $connectable = $lang['gl_na_connectable'];
    }

    $Achievement_Points = $cache->get('user_achievement_points_' . $CURUSER['id']);
    if ($Achievement_Points === false || is_null($Achievement_Points)) {
        $Sql = sql_query('SELECT u.id, u.username, a.achpoints, a.spentpoints
                            FROM users AS u
                            LEFT JOIN usersachiev AS a ON u.id=a.userid
                            WHERE u.id=' . sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);
        $Achievement_Points = mysqli_fetch_assoc($Sql);
        $Achievement_Points['id'] = (int) $Achievement_Points['id'];
        $Achievement_Points['achpoints'] = (int) $Achievement_Points['achpoints'];
        $Achievement_Points['spentpoints'] = (int) $Achievement_Points['spentpoints'];
        $cache->set('user_achievement_points_' . $CURUSER['id'], $Achievement_Points, 0);
    }
    if ($CURUSER['override_class'] != 255) {
        $usrclass = " <a href='{$site_config['paths']['baseurl']}/restoreclass.php' class='tooltipper' title='Restore to Your User Class'><b>" . get_user_class_name((int) $CURUSER['override_class']) . '</b></a>';
    } elseif ($CURUSER['class'] >= UC_STAFF) {
        $usrclass = " <a href='{$site_config['paths']['baseurl']}/setclass.php' class='tooltipper' title='Temporarily Change User Class'><b>" . get_user_class_name((int) $CURUSER['class']) . '</b></a>';
    } else {
        $usrclass = get_user_class_name((int) $CURUSER['class']);
    }
    $member_reputation = get_reputation($CURUSER);

    $StatusBar = "
    <span class='navbar-start'>{$lang['gl_pstats']}</span>
    <span class='level is-marginless'>
        <span class='navbar-start'>{$lang['gl_uclass']}</span>
        <span>{$usrclass}</span>
    </span>
    <span class='level is-marginless'>
        <span class='navbar-start'>{$lang['gl_rep']}</span>
        <span>$member_reputation</span>
    </span>

    <span class='level is-marginless'>
        <span class='navbar-start'>{$lang['gl_invites']}</span>
        <span><a href='{$site_config['paths']['baseurl']}/invite.php'>{$CURUSER['invites']}</a></span>
    </span>
    <span class='level is-marginless'>
        <span class='navbar-start'>{$lang['gl_karma']}</span>
        <span><a href='{$site_config['paths']['baseurl']}/mybonus.php'>" . number_format($CURUSER['seedbonus']) . "</a></span>
    </span>
    <span class='level is-marginless'>
        <span class='navbar-start'>{$lang['gl_achpoints']}</span>
        <span><a href='{$site_config['paths']['baseurl']}/achievementhistory.php?id={$CURUSER['id']}'>" . (int) $Achievement_Points['achpoints'] . "</a></span>
    </span>
    <br>
    <span class='navbar-start' id='hide_html'>{$lang['gl_tstats']}</span>
    <span class='level is-marginless'>
        <span class='navbar-start'>{$lang['gl_shareratio']}</span>
        <span>" . member_ratio($CURUSER['uploaded'], $CURUSER['downloaded']) . '</span>
    </span>';

    if ($site_config['site']['ratio_free']) {
        $StatusBar .= "
    <span class='level is-marginless'>
        <span class='navbar-start'>{$lang['gl_uploaded']}</span>
        <span>$upped</span>
    </span>";
    } else {
        $StatusBar .= "
    <span class='level is-marginless'>
        <span class='navbar-start'>{$lang['gl_uploaded']}</span>
        <span>$upped</span>
    </span>
    <span class='level is-marginless'>
        <span class='navbar-start'>{$lang['gl_downloaded']}</span>
        <span>$downed</span>
    </span>";
    }

    $got_moods = ($CURUSER['opt2'] & user_options_2::GOT_MOODS) === user_options_2::GOT_MOODS;
    $StatusBar .= "
    <span class='level is-marginless'>
        <span class='navbar-start'>{$lang['gl_seed_torrents']}</span>
        <span>{$seed['yes']}</span>
    </span>
    <span class='level is-marginless'>
        <span class='navbar-start'>{$lang['gl_leech_torrents']}</span>
        <span>{$seed['no']}</span>
    </span>
    <span class='level is-marginless'>
        <span class='navbar-start'>{$lang['gl_connectable']}</span>
        <span>{$connectable}</span>
    </span>
    " . ($CURUSER['class'] >= UC_STAFF || $CURUSER['got_blocks'] === 'yes' ? "
    <br>
    <span class='navbar-start'>{$lang['gl_userblocks']}</span>
    <span class='level is-marginless'>
        <span class='navbar-start'>{$lang['gl_myblocks']}</span>
        <span><a href='{$site_config['paths']['baseurl']}/user_blocks.php'>{$lang['gl_click']}</a></span>" : '') . '
    </span>
    ' . ($CURUSER['class'] >= UC_STAFF || $got_moods ? "
    <span class='level is-marginless'>
        <span class='navbar-start'>{$lang['gl_myunlocks']}</span>
        <span><a href='{$site_config['paths']['baseurl']}/user_unlocks.php'>{$lang['gl_click']}</a></span>" : '') . '
    </span>';

    echo json_encode($StatusBar);
} else {
    echo json_encode('failed...');
}
