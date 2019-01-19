<?php

require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'user_functions.php';
require_once CLASS_DIR . 'class_user_options_2.php';
check_user_status();
global $site_config, $cache, $session;

$lang = array_merge(load_language('global'), load_language('index'));

if (empty($_POST)) {
    $session->set('is-danger', 'Access Not Allowed');
    header("Location: {$site_config['baseurl']}/index.php");
    die();
}

header('Content-Type: application/json');
if (!empty($CURUSER)) {
    $upped = mksize($CURUSER['uploaded']);
    $downed = mksize($CURUSER['downloaded']);

    $peer = new DarkAlchemy\Pu239\Peer();
    $seed = $peer->getPeersFromUserId($CURUSER['id']);

    if (!empty($seed['conn'])) {
        switch ($seed['conn']) {
            case 1:
                $connectable = "<img src='{$site_config['pic_baseurl']}notcon.png' alt='{$lang['gl_not_connectable']}' class='tooltipper' title='{$lang['gl_not_connectable']}'>";
                break;

            case 2:
                $connectable = "<img src='{$site_config['pic_baseurl']}yescon.png' alt='{$lang['gl_connectable']}' class='tooltipper' title='{$lang['gl_connectable']}'>";
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
                            LEFT JOIN usersachiev AS a ON u.id = a.userid
                            WHERE u.id = ' . sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);
        $Achievement_Points = mysqli_fetch_assoc($Sql);
        $Achievement_Points['id'] = (int) $Achievement_Points['id'];
        $Achievement_Points['achpoints'] = (int) $Achievement_Points['achpoints'];
        $Achievement_Points['spentpoints'] = (int) $Achievement_Points['spentpoints'];
        $cache->set('user_achievement_points_' . $CURUSER['id'], $Achievement_Points, 0);
    }
    if ($CURUSER['override_class'] != 255) {
        $usrclass = " <a href='{$site_config['baseurl']}/restoreclass.php' class='tooltipper' title='Restore to Your User Class'><b>" . get_user_class_name($CURUSER['override_class']) . '</b></a>';
    } elseif ($CURUSER['class'] >= UC_STAFF) {
        $usrclass = " <a href='{$site_config['baseurl']}/setclass.php' class='tooltipper' title='Temporarily Change User Class'><b>" . get_user_class_name($CURUSER['class']) . '</b></a>';
    } else {
        $usrclass = get_user_class_name($CURUSER['class']);
    }
    $member_reputation = get_reputation($CURUSER);

    $StatusBar = "
    <div class='navbar-start'>{$lang['gl_pstats']}</div>
    <div class='level is-marginless'>
        <div class='navbar-start'>{$lang['gl_uclass']}</div>
        <div>{$usrclass}</div>
    </div>
    <div class='level is-marginless'>
        <div class='navbar-start'>{$lang['gl_rep']}</div>
        <div>$member_reputation</div>
    </div>

    <div class='level is-marginless'>
        <div class='navbar-start'>{$lang['gl_invites']}</div>
        <div><a href='{$site_config['baseurl']}/invite.php'>{$CURUSER['invites']}</a></div>
    </div>
    <div class='level is-marginless'>
        <div class='navbar-start'>{$lang['gl_karma']}</div>
        <div><a href='{$site_config['baseurl']}/mybonus.php'>" . number_format($CURUSER['seedbonus']) . "</a></div>
    </div>
    <div class='level is-marginless'>
        <div class='navbar-start'>{$lang['gl_achpoints']}</div>
        <div><a href='{$site_config['baseurl']}/achievementhistory.php?id={$CURUSER['id']}'>" . (int) $Achievement_Points['achpoints'] . "</a></div>
    </div>
    <br>
    <div class='navbar-start'>{$lang['gl_tstats']}</div>
    <div class='level is-marginless'>
        <div class='navbar-start'>{$lang['gl_shareratio']}</div>
        <div>" . member_ratio($CURUSER['uploaded'], $site_config['ratio_free'] ? '0' : $CURUSER['downloaded']) . '</div>
    </div>';

    if ($site_config['ratio_free']) {
        $StatusBar .= "
    <div class='level is-marginless'>
        <div class='navbar-start'>{$lang['gl_uploaded']}</div>
        <div>$upped</div>
    </div>";
    } else {
        $StatusBar .= "
    <div class='level is-marginless'>
        <div class='navbar-start'>{$lang['gl_uploaded']}</div>
        <div>$upped</div>
    </div>
    <div class='level is-marginless'>
        <div class='navbar-start'>{$lang['gl_downloaded']}</div>
        <div>$downed</div>
    </div>";
    }

    $got_moods = ($CURUSER['opt2'] & user_options_2::GOT_MOODS) === user_options_2::GOT_MOODS;
    $StatusBar .= "
    <div class='level is-marginless'>
        <div class='navbar-start'>{$lang['gl_seed_torrents']}</div>
        <div>{$seed['yes']}</div>
    </div>
    <div class='level is-marginless'>
        <div class='navbar-start'>{$lang['gl_leech_torrents']}</div>
        <div>{$seed['no']}</div>
    </div>
    <div class='level is-marginless'>
        <div class='navbar-start'>{$lang['gl_connectable']}</div>
        <div>{$connectable}</div>
    </div>
    " . ($CURUSER['class'] >= UC_STAFF || $CURUSER['got_blocks'] === 'yes' ? "
    <br>
    <div class='navbar-start'>{$lang['gl_userblocks']}</div>
    <div class='level is-marginless'>
        <div class='navbar-start'>{$lang['gl_myblocks']}</div>
        <div><a href='{$site_config['baseurl']}/user_blocks.php'>{$lang['gl_click']}</a></div>" : '') . '
    </div>
    ' . ($CURUSER['class'] >= UC_STAFF || $got_moods ? "
    <div class='level is-marginless'>
        <div class='navbar-start'>{$lang['gl_myunlocks']}</div>
        <div><a href='{$site_config['baseurl']}/user_unlocks.php'>{$lang['gl_click']}</a></div>" : '') . '
    </div>';

    echo json_encode($StatusBar);
} else {
    echo json_encode('failed...');
}
