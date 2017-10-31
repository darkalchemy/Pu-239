<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'user_functions.php';
check_user_status();

header('Content-Type: application/json');
global $site_config, $mc1;
$lang = array_merge(load_language('global'), load_language('index'));

if (!empty($CURUSER) && validateToken($_POST['csrf_token'])) {
    $upped = mksize($CURUSER['uploaded']);
    $downed = mksize($CURUSER['downloaded']);

    if (XBT_TRACKER == true) {
        if (($MyPeersXbtCache = $mc1->get_value('MyPeers_XBT_' . $CURUSER['id'])) === false) {
            $seed['yes'] = $seed['no'] = 0;
            $seed['conn'] = 3;
            $r = sql_query('SELECT COUNT(uid) AS count, left, active, connectable
                                FROM xbt_files_users
                                WHERE uid = ' . sqlesc($CURUSER['id']) . '
                                GROUP BY left') or sqlerr(__LINE__, __FILE__);
            while ($a = mysqli_fetch_assoc($r)) {
                $key = $a['left'] == 0 ? 'yes' : 'no';
                $seed[$key] = number_format((int)$a['count']);
                $seed['conn'] = $a['connectable'] == 0 ? 1 : 2;
            }
            $mc1->cache_value('MyPeers_XBT_' . $CURUSER['id'], $seed, $site_config['expires']['MyPeers_xbt_']);
            unset($r, $a);
        } else {
            $seed = $MyPeersXbtCache;
        }
    } else {
        if (($MyPeersCache = $mc1->get_value('MyPeers_' . $CURUSER['id'])) === false) {
            $seed['yes'] = $seed['no'] = 0;
            $seed['conn'] = 3;
            $r = sql_query('SELECT COUNT(id) AS count, seeder, connectable
                                FROM peers
                                WHERE userid = ' . sqlesc($CURUSER['id']) . '
                                GROUP BY seeder');
            while ($a = mysqli_fetch_assoc($r)) {
                $key = $a['seeder'] == 'yes' ? 'yes' : 'no';
                $seed[$key] = number_format((int)$a['count']);
                $seed['conn'] = $a['connectable'] == 'no' ? 1 : 2;
            }
            $mc1->cache_value('MyPeers_' . $CURUSER['id'], $seed, $site_config['expires']['MyPeers_']);
            unset($r, $a);
        } else {
            $seed = $MyPeersCache;
        }
    }
    // for display connectable  1 / 2 / 3
    if (!empty($seed['conn'])) {
        switch ($seed['conn']) {
            case 1:
                $connectable = "<img src='{$site_config['pic_base_url']}notcon.png' alt='{$lang['gl_not_connectable']}' class='tooltipper' title='{$lang['gl_not_connectable']}' />";
                break;

            case 2:
                $connectable = "<img src='{$site_config['pic_base_url']}yescon.png' alt='{$lang['gl_connectable']}' class='tooltipper' title='{$lang['gl_connectable']}' />";
                break;

            default:
                $connectable = "{$lang['gl_na_connectable']}";
        }
    } else {
        $connectable = $lang['gl_na_connectable'];
    }

    if (($Achievement_Points = $mc1->get_value('user_achievement_points_' . $CURUSER['id'])) === false) {
        $Sql = sql_query('SELECT u.id, u.username, a.achpoints, a.spentpoints
                            FROM users AS u
                            left JOIN usersachiev AS a ON u.id = a.userid
                            WHERE u.id = ' . sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);
        $Achievement_Points = mysqli_fetch_assoc($Sql);
        $Achievement_Points['id'] = (int)$Achievement_Points['id'];
        $Achievement_Points['achpoints'] = (int)$Achievement_Points['achpoints'];
        $Achievement_Points['spentpoints'] = (int)$Achievement_Points['spentpoints'];
        $mc1->cache_value('user_achievement_points_' . $CURUSER['id'], $Achievement_Points, 0);
    }

    if ($CURUSER['override_class'] != 255) {
        $usrclass = ' <b>(' . get_user_class_name($CURUSER['class']) . ')</b> ';
    } elseif ($CURUSER['class'] >= UC_STAFF) {
        $usrclass = " <a href='./setclass.php' class='tooltipper' title='Temporarily Change User Class'><b>(" . get_user_class_name($CURUSER['class']) . ')</b></a>';
    }
    $member_reputation = get_reputation($CURUSER);

    $StatusBar = "
    <div class='navbar-start'>{$lang['gl_pstats']}</div>
    <div class='is-flex'>
        <div class='navbar-start'>{$lang['gl_uclass']}</div>
        " . ($CURUSER['class'] < UC_STAFF ? "<div>" . get_user_class_name($CURUSER['class']) . "</div>" : "<div>{$usrclass}</div>") . "
    </div>
    <div class='is-flex'>
        <div class='navbar-start'>{$lang['gl_rep']}</div>
        <div>$member_reputation</div>
    </div>


    <div class='is-flex'>
        <div class='navbar-start'>{$lang['gl_invites']}</div>
        <div><a href='./invite.php'>{$CURUSER['invites']}</a></div>
    </div>
    <div class='is-flex'>
        <div class='navbar-start'>{$lang['gl_karma']}</div>
        <div><a href='./mybonus.php'>" . number_format($CURUSER['seedbonus']) . "</a></div>
    </div>
    <div class='is-flex'>
        <div class='navbar-start'>{$lang['gl_achpoints']}</div>
        <div><a href='./achievementhistory.php?id={$CURUSER['id']}'>" . (int)$Achievement_Points['achpoints'] . "</a></div>
    </div>
    <br>
    <div class='navbar-start'>{$lang['gl_tstats']}</div>
    <div class='is-flex'>
        <div class='navbar-start'>{$lang['gl_shareratio']}</div>
        <div>" . member_ratio($CURUSER['uploaded'], $site_config['ratio_free'] ? '0' : $CURUSER['downloaded']) . "</div>
    </div>";

    if ($site_config['ratio_free']) {
        $StatusBar .= "
    <div class='is-flex'>
        <div class='navbar-start'>{$lang['gl_uploaded']}</div>
        <div>$upped</div>
    </div>";
    } else {
        $StatusBar .= "
    <div class='is-flex'>
        <div class='navbar-start'>{$lang['gl_uploaded']}</div>
        <div>$upped</div>
    </div>
    <div class='is-flex'>
        <div class='navbar-start'>{$lang['gl_downloaded']}</div>
        <div>$downed</div>
    </div>";
    }

    $StatusBar .= "
    <div class='is-flex'>
        <div class='navbar-start'>{$lang['gl_seed_torrents']}</div>
        <div>{$seed['yes']}</div>
    </div>
    <div class='is-flex'>
        <div class='navbar-start'>{$lang['gl_leech_torrents']}</div>
        <div>{$seed['no']}</div>
    </div>
    <div class='is-flex'>
        <div class='navbar-start'>{$lang['gl_connectable']}</div>
        <div>{$connectable}</div>
    </div>
    " . ($CURUSER['class'] >= UC_STAFF || $CURUSER['got_blocks'] == 'yes' || $CURUSER['got_moods'] == 'yes' ? "
    <br>
    <div class='navbar-start'>{$lang['gl_userblocks']}</div>
    <div class='is-flex'>
        <div class='navbar-start'>{$lang['gl_myblocks']}</div>
        <div><a href='./user_blocks.php'>{$lang['gl_click']}</a></div>" : '') . "
    </div>
    " . ($CURUSER['class'] >= UC_STAFF || $CURUSER['got_moods'] == 'yes' ? "
    <div class='is-flex'>
        <div class='navbar-start'>{$lang['gl_myunlocks']}</div>
        <div><a href='./user_unlocks.php'>{$lang['gl_click']}</a></div>" : '') . "
    </div>";

    echo json_encode($StatusBar);
} else {
    echo json_encode('failed...');
}
