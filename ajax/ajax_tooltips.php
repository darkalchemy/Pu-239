<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'user_functions.php';
check_user_status();
//dbconn();

//file_put_contents('/var/log/nginx/ajax.log', json_encode($_SESSION) . PHP_EOL, FILE_APPEND);
//file_put_contents('/var/log/nginx/ajax.log', json_encode($_POST) . PHP_EOL, FILE_APPEND);
//file_put_contents('/var/log/nginx/ajax.log', json_encode($user) . PHP_EOL, FILE_APPEND);
return;

header('Content-Type: application/json');
global $site_config, $cache;
$lang = array_merge(load_language('global'), load_language('index'));

//file_put_contents('/var/log/nginx/ajax.log', json_encode($_SESSION) . PHP_EOL, FILE_APPEND);
//file_put_contents('/var/log/nginx/ajax.log', json_encode($_POST) . PHP_EOL, FILE_APPEND);
//file_put_contents('/var/log/nginx/ajax.log', json_encode($user) . PHP_EOL, FILE_APPEND);

//echo json_encode($_POST['csrf_token']);
//die();
if ($id = getSessionVar('userID') && validateToken($_POST['csrf_token'])) {
    $user = $cache->get('MyUser_' . $id);
    if ($user === false || is_null($user)) {
        echo json_encode('failed...');
    }

    $upped = mksize($user['uploaded']);
    $downed = mksize($user['downloaded']);

    if (XBT_TRACKER == true) {
        $MyPeersXbtCache = $cache->get('MyPeers_XBT_' . $user['id']);
        if ($MyPeersXbtCache === false || is_null($MyPeersXbtCache)) {
            $seed['yes'] = $seed['no'] = 0;
            $seed['conn'] = 3;
            $r = sql_query('SELECT COUNT(uid) AS count, `left`, active, connectable
                                FROM xbt_files_users
                                WHERE uid = ' . sqlesc($user['id']) . '
                                GROUP BY `left`') or sqlerr(__LINE__, __FILE__);
            while ($a = mysqli_fetch_assoc($r)) {
                $key = $a['left'] == 0 ? 'yes' : 'no';
                $seed[ $key ] = number_format((int)$a['count']);
                $seed['conn'] = $a['connectable'] == 0 ? 1 : 2;
            }
            $cache->set('MyPeers_XBT_' . $user['id'], $seed, $site_config['expires']['MyPeers_xbt_']);
            unset($r, $a);
        } else {
            $seed = $MyPeersXbtCache;
        }
    } else {
        $MyPeersCache = $cache->get('MyPeers_' . $user['id']);
        if ($MyPeersCache === false || is_null($MyPeersCache)) {
            $seed['yes'] = $seed['no'] = 0;
            $seed['conn'] = 3;
            $r = sql_query('SELECT COUNT(id) AS count, seeder, connectable
                                FROM peers
                                WHERE userid = ' . sqlesc($user['id']) . '
                                GROUP BY seeder');
            while ($a = mysqli_fetch_assoc($r)) {
                $key = $a['seeder'] == 'yes' ? 'yes' : 'no';
                $seed[ $key ] = number_format((int)$a['count']);
                $seed['conn'] = $a['connectable'] == 'no' ? 1 : 2;
            }
            $cache->set('MyPeers_' . $user['id'], $seed, $site_config['expires']['MyPeers_']);
            unset($r, $a);
        } else {
            $seed = $MyPeersCache;
        }
    }
    // for display connectable  1 / 2 / 3
    if (!empty($seed['conn'])) {
        switch ($seed['conn']) {
            case 1:
                $connectable = "<img src='{$site_config['pic_base_url']}notcon.png' alt='{$lang['gl_not_connectable']}' title='{$lang['gl_not_connectable']}' />";
                break;

            case 2:
                $connectable = "<img src='{$site_config['pic_base_url']}yescon.png' alt='{$lang['gl_connectable']}' title='{$lang['gl_connectable']}' />";
                break;

            default:
                $connectable = "{$lang['gl_na_connectable']}";
        }
    } else {
        $connectable = $lang['gl_na_connectable'];
    }

    $Achievement_Points = $cache->get('user_achievement_points_' . $user['id']);
    if ($Achievement_Points === false || is_null($Achievement_Points)) {
        $Sql = sql_query('SELECT u.id, u.username, a.achpoints, a.spentpoints
                            FROM users AS u
                            LEFT JOIN usersachiev AS a ON u.id = a.userid
                            WHERE u.id = ' . sqlesc($user['id'])) or sqlerr(__FILE__, __LINE__);
        $Achievement_Points = mysqli_fetch_assoc($Sql);
        $Achievement_Points['id'] = (int)$Achievement_Points['id'];
        $Achievement_Points['achpoints'] = (int)$Achievement_Points['achpoints'];
        $Achievement_Points['spentpoints'] = (int)$Achievement_Points['spentpoints'];
        $cache->set('user_achievement_points_' . $user['id'], $Achievement_Points, 0);
    }

    if ($user['override_class'] != 255) {
        $usrclass = ' <b>(' . get_user_class_name($user['class']) . ')</b> ';
    } elseif ($user['class'] >= UC_STAFF) {
        $usrclass = " <a href='./setclass.php'><b>(" . get_user_class_name($user['class']) . ')</b></a>';
    }
    $member_reputation = get_reputation($user);

    $StatusBar = "
    <div class='left'>{$lang['gl_pstats']}</div>
    <div class='flex-user-stats'>
        <div class='left'>{$lang['gl_uclass']}</div>
        " . ($user['class'] < UC_STAFF ? "<div>" . get_user_class_name($user['class']) . "</div>" : "<div>{$usrclass}</div>") . "
    </div>
    <div class='flex-user-stats'>
        <div class='left'>{$lang['gl_rep']}</div>
        <div>$member_reputation</div>
    </div>

    <div class='flex-user-stats'>
        <div class='left'>{$lang['gl_invites']}</div>
        <div><a href='./invite.php'>{$user['invites']}</a></div>
    </div>
    <div class='flex-user-stats'>
        <div class='left'>{$lang['gl_karma']}</div>
        <div><a href='./mybonus.php'>{$user['seedbonus']}</a></div>
    </div>
    <div class='flex-user-stats'>
        <div class='left'>{$lang['gl_achpoints']}</div>
        <div><a href='./achievementhistory.php?id={$user['id']}'>" . (int)$Achievement_Points['achpoints'] . "</a></div>
    </div>
    <br>
    <div class='left'>{$lang['gl_tstats']}</div>
    <div class='flex-user-stats'>
        <div class='left'>{$lang['gl_shareratio']}</div>
        <div>" . member_ratio($user['uploaded'], $site_config['ratio_free'] ? '0' : $user['downloaded']) . "</div>
    </div>";

    if ($site_config['ratio_free']) {
        $StatusBar .= "
    <div class='flex-user-stats'>
        <div class='left'>{$lang['gl_uploaded']}</div>
        <div>$upped</div>
    </div>";
    } else {
        $StatusBar .= "
    <div class='flex-user-stats'>
        <div class='left'>{$lang['gl_uploaded']}</div>
        <div>$upped</div>
    </div>
    <div class='flex-user-stats'>
        <div class='left'>{$lang['gl_downloaded']}</div>
        <div>$downed</div>
    </div>";
    }

    $StatusBar .= "
    <div class='flex-user-stats'>
        <div class='left'>{$lang['gl_seed_torrents']}</div>
        <div>{$seed['yes']}</div>
    </div>
    <div class='flex-user-stats'>
        <div class='left'>{$lang['gl_leech_torrents']}</div>
        <div>{$seed['no']}</div>
    </div>
    <div class='flex-user-stats'>
        <div class='left'>{$lang['gl_connectable']}</div>
        <div>{$connectable}</div>
    </div>
    " . ($user['class'] >= UC_STAFF || $user['got_blocks'] == 'yes' || $user['got_moods'] == 'yes' ? "
    <br>
    <div class='left'>{$lang['gl_userblocks']}</div>
    <div class='flex-user-stats'>
        <div class='left'>{$lang['gl_myblocks']}</div>
        <div><a href='./user_blocks.php'>{$lang['gl_click']}</a></div>" : '') . "
    </div>
    " . ($user['class'] >= UC_STAFF || $user['got_moods'] == 'yes' ? "
    <div class='flex-user-stats'>
        <div class='left'>{$lang['gl_myunlocks']}</div>
        <div><a href='./user_unlocks.php'>{$lang['gl_click']}</a></div>" : '') . "
    </div>";

    echo json_encode($StatusBar);
} else {
    echo json_encode('failed...');
}
