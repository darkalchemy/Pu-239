<?php

require_once INCL_DIR . 'html_functions.php';
require_once INCL_DIR . 'pager_functions.php';
global $CURUSER, $site_config, $lang, $user_stuffs, $cache, $user;

/**
 * @param $torrents
 *
 * @return string
 */
function snatchtable($torrents)
{
    global $site_config, $lang;

    $heading = "
        <tr>
            <th>{$lang['userdetails_s_cat']}</th>
            <th>{$lang['userdetails_s_torr']}</th>
            <th>{$lang['userdetails_s_up']}</th>
            <th>{$lang['userdetails_rate']}</th>" . ($site_config['ratio_free'] ? '' : "
            <th>{$lang['userdetails_downl']}</th>") . ($site_config['ratio_free'] ? '' : "
            <th>{$lang['userdetails_rate']}</th>") . "
            <th>{$lang['userdetails_ratio']}</th>
            <th>{$lang['userdetails_activity']}</th>
            <th>{$lang['userdetails_s_fin']}</th>
        </tr>";
    $body = '';
    foreach ($torrents as $torrent) {
        $upspeed = ($torrent['upspeed'] > 0 ? mksize($torrent['upspeed']) : ($torrent['seedtime'] > 0 ? mksize($torrent['uploaded'] / ($torrent['seedtime'] + $torrent['leechtime'])) : mksize(0)));
        $downspeed = ($torrent['downspeed'] > 0 ? mksize($torrent['downspeed']) : ($torrent['leechtime'] > 0 ? mksize($torrent['downloaded'] / $torrent['leechtime']) : mksize(0)));
        $ratio = ($torrent['downloaded'] > 0 ? number_format($torrent['uploaded'] / $torrent['downloaded'], 3) : ($torrent['uploaded'] > 0 ? 'Inf.' : '---'));
        $XBT_or_PHP = (XBT_TRACKER ? $torrent['fid'] : $torrent['torrentid']);
        $XBT_or_PHP_TIME = (XBT_TRACKER ? $torrent['completedtime'] : $torrent['complete_date']);
        $body .= "
        <tr>
            <td>
                <img src='{$site_config['pic_baseurl']}caticons/" . get_category_icons() . '/' . htmlsafechars($torrent['image']) . "' alt='" . htmlsafechars($torrent['catname']) . "' width='42' height='42' />
            </td>
            <td>
                <a href='{$site_config['baseurl']}/details.php?id=" . (int) $XBT_or_PHP . "'><b>" . (strlen($torrent['name']) > 50 ? substr($torrent['name'], 0, 50 - 3) . '...' : htmlsafechars($torrent['name'])) . '</b></a>
            </td>
            <td>' . mksize($torrent['uploaded']) . "</td>
            <td>$upspeed/s</td>" . ($site_config['ratio_free'] ? '' : '
            <td>' . mksize($torrent['downloaded']) . '</td>') . ($site_config['ratio_free'] ? '' : "
            <td>$downspeed/s</td>") . "
            <td>$ratio</td>
            <td>" . mkprettytime($torrent['seedtime'] + $torrent['leechtime']) . '</td>
            <td>' . ($XBT_or_PHP_TIME != 0 ? "
                <span class='has-text-lime'><b>{$lang['userdetails_yes']}</b></span>" : "
                <span class='has-text-red'><b>{$lang['userdetails_no']}</b></span>") . '
            </td>
        </tr>';
    }
    $table = main_table($body, $heading);

    return $table;
}

/**
 * @param $torrents
 *
 * @return string
 */
function maketable($torrents)
{
    global $site_config, $lang;

    $heading = "
        <tr>
            <th>{$lang['userdetails_type']}</th>
            <th>{$lang['userdetails_name']}</th>
            <th>{$lang['userdetails_size']}</th>
            <th>{$lang['userdetails_se']}</th>
            <th>{$lang['userdetails_le']}</th>
            <th>{$lang['userdetails_upl']}</th>" . ($site_config['ratio_free'] ? '' : "
            <th>{$lang['userdetails_downl']}</th>") . "
            <th>{$lang['userdetails_ratio']}</th>
        </tr>";
    $body = '';
    foreach ($torrents as $torrent) {
        if ($torrent['downloaded'] > 0) {
            $ratio = number_format($torrent['uploaded'] / $torrent['downloaded'], 3);
            $ratio = "<span style='color: " . get_ratio_color($ratio) . ";'>$ratio</span>";
        } elseif ($torrent['uploaded'] > 0) {
            $ratio = "{$lang['userdetails_inf']}";
        } else {
            $ratio = '---';
        }
        $catimage = "{$site_config['pic_baseurl']}caticons/" . get_category_icons() . "/{$torrent['image']}";
        $catname = htmlsafechars($torrent['catname']);
        $catimage = "<img src='$catimage' title='$catname' alt='$catname' width='42' height='42' class='tooltipper' />";
        $size = str_replace(' ', '<br>', mksize($torrent['size']));
        $uploaded = str_replace(' ', '<br>', mksize($torrent['uploaded']));
        $downloaded = str_replace(' ', '<br>', mksize($torrent['downloaded']));
        $seeders = number_format($torrent['seeders']);
        $leechers = number_format($torrent['leechers']);
        $XBT_or_PHP = (XBT_TRACKER ? $torrent['fid'] : $torrent['torrentid']);
        $body .= "
        <tr>
            <td class='has-text-centered'>$catimage</td>
            <td>
                <a href='{$site_config['baseurl']}/details.php?id=" . (int) $XBT_or_PHP . "&amp;hit=1'><b>" . htmlsafechars($torrent['name']) . "</b></a>
            </td>
            <td class='has-text-centered'>$size</td>
            <td class='has-text-centered'>$seeders</td>
            <td class='has-text-centered'>$leechers</td>
            <td class='has-text-centered'>$uploaded</td>" . ($site_config['ratio_free'] ? '' : "
            <td class='has-text-centered'>$downloaded</td>") . "
            <td class='has-text-centered'>$ratio</td>
        </tr>";
    }
    $table = main_table($body, $heading);

    return $table;
}

if ($user['paranoia'] < 2 || $user['opt1'] & user_options::HIDECUR || $CURUSER['id'] == $user['id'] || $CURUSER['class'] >= UC_STAFF) {
    $seeding = $leeching = $torrents = $user_snatches_data = [];

    $query = $fluent->from('torrents')
        ->select(null)
        ->select('torrents.id AS torrentid')
        ->select('torrents.name')
        ->select('torrents.seeders')
        ->select('torrents.leechers')
        ->select('torrents.size')
        ->select('categories.name AS catname')
        ->select('categories.image')
        ->select('users.uploaded')
        ->select('users.downloaded')
        ->leftJoin('categories ON torrents.category = categories.id')
        ->leftJoin('users ON torrents.owner = users.id')
        ->where('torrents.owner = ?', $user['id'])
        ->orderBy('torrents.name')
        ->limit('0, 15');
    foreach ($query as $results) {
        $torrents[] = $results;
    }

    if (XBT_TRACKER) {
        $res = sql_query('SELECT x.fid, x.uploaded, x.downloaded, x.active, x.left, t.added, t.name AS torrentname, t.size, t.category, t.seeders, t.leechers, c.name AS catname, c.image FROM xbt_files_users x LEFT JOIN torrents t ON x.fid = t.id LEFT JOIN categories c ON t.category = c.id WHERE x.uid=' . sqlesc($user['id'])) or sqlerr(__FILE__, __LINE__);
        while ($arr = mysqli_fetch_assoc($res)) {
            if ($arr['left'] == '0') {
                $seeding[] = $arr;
            } else {
                $leeching[] = $arr;
            }
        }
    } else {
        $query = $fluent->from('peers')
            ->select(null)
            ->select('peers.torrent AS torrentid')
            ->select('peers.uploaded')
            ->select('peers.downloaded')
            ->select('peers.seeder')
            ->select('peers.last_action')
            ->select('torrents.added')
            ->select('torrents.name')
            ->select('torrents.size')
            ->select('torrents.seeders')
            ->select('torrents.leechers')
            ->select('torrents.owner')
            ->select('categories.name AS catname')
            ->select('categories.image')
            ->leftJoin('torrents ON peers.torrent = torrents.id')
            ->leftJoin('categories ON torrents.category = categories.id')
            ->where('peers.userid = ?', $user['id'])
            ->orderBy('last_action DESC')
            ->limit('0, 15');

        foreach ($query as $arr) {
            if ($arr['seeder'] === 'yes') {
                $seeding[] = $arr;
            } else {
                $leeching[] = $arr;
            }
        }
    }

    $user_snatches_data = $cache->get('user_snatches_data_' . $user['id']);
    if ($user_snatches_data === false || is_null($user_snatches_data)) {
        if (!XBT_TRACKER) {
            $query = $fluent->from('snatched')
                ->select(null)
                ->select('snatched.*')
                ->select('torrents.name')
                ->select('categories.name AS catname')
                ->select('categories.image')
                ->leftJoin('torrents ON snatched.torrentid = torrents.id')
                ->leftJoin('categories ON torrents.category = categories.id')
                ->where('snatched.userid = ?', $user['id'])
                ->orderBy('last_action DESC')
                ->limit('0, 15');
        } else {
            $ressnatch = sql_query('SELECT x.*, t.name AS name, c.name AS catname, c.image AS catimg FROM xbt_files_users AS x INNER JOIN torrents AS t ON x.fid = t.id LEFT JOIN categories AS c ON t.category = c.id WHERE x.uid =' . sqlesc($user['id'])) or sqlerr(__FILE__, __LINE__);
        }
        foreach ($query as $item) {
            $user_snatches_data[] = $item;
        }
        $cache->set('user_snatches_data_' . $user['id'], $user_snatches_data, $site_config['expires']['user_snatches_data']);
    }

    if (!empty($torrents)) {
        $table_data .= "
        <tr>
            <td class='rowhead'>{$lang['userdetails_uploaded_t']}</td>
            <td>
                <a id='torrents-hash'></a>
                <fieldset id='torrents' class='header'>
                    <legend class='flipper size_4'><i class='icon-down-open' aria-hidden='true'></i>View Torrents you uploaded</legend>
                    " . maketable($torrents) . '
                </fieldset>
            </td>
        </tr>';
    }

    if (!empty($seeding)) {
        $table_data .= "
        <tr>
            <td class='rowhead'>{$lang['userdetails_cur_seed']}</td>
            <td>
                <a id='seeding-hash'></a>
                <fieldset id='seeding' class='header'>
                    <legend class='flipper size_4'><i class='icon-down-open' aria-hidden='true'></i>View Torrents you are currently seeding</legend>
                    " . maketable($seeding) . '
                </fieldset>
            </td>
        </tr>';
    }

    if (!empty($leeching)) {
        $table_data .= "
        <tr>
            <td class='rowhead'>{$lang['userdetails_cur_leech']}</td>
            <td>
                <a id='leeching-hash'></a>
                <fieldset id='leeching' class='header'>
                    <legend class='flipper size_4'><i class='icon-down-open' aria-hidden='true'></i>View Torrents you are currently leeching</legend>
                " . maketable($leeching) . '
                </fieldset>
            </td>
        </tr>';
    }

    if (!empty($user_snatches_data)) {
        $table_data .= "
        <tr>
            <td class='rowhead'>{$lang['userdetails_cur_snatched']}</td>
            <td>
                <a id='snatched-hash'></a>
                <fieldset id='snatched' class='header'>
                    <legend class='flipper size_4'><i class='icon-down-open' aria-hidden='true'></i>View Torrents you have snatched</legend>
                " . snatchtable($user_snatches_data) . '
                </fieldset>
            </td>
        </tr>';
    }
}
