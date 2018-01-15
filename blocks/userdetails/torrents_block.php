<?php
global $CURUSER, $site_config, $cache, $lang, $user, $id;

/**
 * @param $res
 *
 * @return string
 */
function snatchtable($res)
{
    global $site_config, $lang;
    $htmlout = "<table class='main' >
 <tr>
<td class='colhead'>{$lang['userdetails_s_cat']}</td>
<td class='colhead'>{$lang['userdetails_s_torr']}</td>
<td class='colhead'>{$lang['userdetails_s_up']}</td>
<td class='colhead'>{$lang['userdetails_rate']}</td>
" . ($site_config['ratio_free'] ? '' : "<td class='colhead'>{$lang['userdetails_downl']}</td>") . '
' . ($site_config['ratio_free'] ? '' : "<td class='colhead'>{$lang['userdetails_rate']}</td>") . "
<td class='colhead'>{$lang['userdetails_ratio']}</td>
<td class='colhead'>{$lang['userdetails_activity']}</td>
<td class='colhead'>{$lang['userdetails_s_fin']}</td>
</tr>";
    while ($arr = mysqli_fetch_assoc($res)) {
        $upspeed = ($arr['upspeed'] > 0 ? mksize($arr['upspeed']) : ($arr['seedtime'] > 0 ? mksize($arr['uploaded'] / ($arr['seedtime'] + $arr['leechtime'])) : mksize(0)));
        $downspeed = ($arr['downspeed'] > 0 ? mksize($arr['downspeed']) : ($arr['leechtime'] > 0 ? mksize($arr['downloaded'] / $arr['leechtime']) : mksize(0)));
        $ratio = ($arr['downloaded'] > 0 ? number_format($arr['uploaded'] / $arr['downloaded'], 3) : ($arr['uploaded'] > 0 ? 'Inf.' : '---'));
        $XBT_or_PHP = (XBT_TRACKER ? $arr['fid'] : $arr['torrentid']);
        $XBT_or_PHP_TIME = (XBT_TRACKER ? $arr['completedtime'] : $arr['complete_date']);
        $htmlout .= "<tr>
 <td style='padding: 0;'><img src='{$site_config['pic_baseurl']}caticons/" . get_categorie_icons() . "/" . htmlsafechars($arr['catimg']) . "' alt='" . htmlsafechars($arr['catname']) . "' width='42' height='42' /></td>
 <td><a href='details.php?id=" . (int)$XBT_or_PHP . "'><b>" . (strlen($arr['name']) > 50 ? substr($arr['name'], 0, 50 - 3) . '...' : htmlsafechars($arr['name'])) . '</b></a></td>
 <td>' . mksize($arr['uploaded']) . "</td>
 <td>$upspeed/s</td>
 " . ($site_config['ratio_free'] ? '' : '<td>' . mksize($arr['downloaded']) . '</td>') . '
 ' . ($site_config['ratio_free'] ? '' : "<td>$downspeed/s</td>") . "
 <td>$ratio</td>
 <td>" . mkprettytime($arr['seedtime'] + $arr['leechtime']) . '</td>
 <td>' . ($XBT_or_PHP_TIME != 0 ? "<span style='color: green;'><b>{$lang['userdetails_yes']}</b></span>" : "<span class='has-text-danger'><b>{$lang['userdetails_no']}</b></span>") . "</td>
 </tr>\n";
    }
    $htmlout .= "</table>\n";

    return $htmlout;
}

/**
 * @param $res
 *
 * @return string
 */
function maketable($res)
{
    global $site_config, $lang;

    $htmlout = "<table class='main' >" . "<tr><td class='colhead'>{$lang['userdetails_type']}</td>
         <td class='colhead'>{$lang['userdetails_name']}</td>
         <td class='colhead'>{$lang['userdetails_size']}</td>
         <td class='colhead'>{$lang['userdetails_se']}</td>
         <td class='colhead'>{$lang['userdetails_le']}</td>
         <td class='colhead'>{$lang['userdetails_upl']}</td>\n" . '' . ($site_config['ratio_free'] ? '' : "<td class='colhead'>{$lang['userdetails_downl']}</td>") . "
         <td class='colhead'>{$lang['userdetails_ratio']}</td></tr>\n";
    foreach ($res as $arr) {
        if ($arr['downloaded'] > 0) {
            $ratio = number_format($arr['uploaded'] / $arr['downloaded'], 3);
            $ratio = "<span style='color: " . get_ratio_color($ratio) . ";'>$ratio</span>";
        } elseif ($arr['uploaded'] > 0) {
            $ratio = "{$lang['userdetails_inf']}";
        } else {
            $ratio = '---';
        }
        $catimage = "{$site_config['pic_baseurl']}caticons/" . get_categorie_icons() . "/{$arr['image']}";
        $catname = htmlsafechars($arr['catname']);
        $catimage = '<img src="' . htmlsafechars($catimage) . "' title='$catname' alt='$catname' width='42' height='42' />";
        $size = str_replace(' ', '<br>', mksize($arr['size']));
        $uploaded = str_replace(' ', '<br>', mksize($arr['uploaded']));
        $downloaded = str_replace(' ', '<br>', mksize($arr['downloaded']));
        $seeders = number_format($arr['seeders']);
        $leechers = number_format($arr['leechers']);
        $XBT_or_PHP = (XBT_TRACKER ? $arr['fid'] : $arr['torrent']);
        $htmlout .= "<tr><td style='padding: 0;'>$catimage</td>\n" . "<td><a href='details.php?id=" . (int)$XBT_or_PHP . "&amp;hit=1'><b>" . htmlsafechars($arr['torrentname']) . "</b></a></td><td>$size</td><td>$seeders</td><td>$leechers</td><td>$uploaded</td>\n" . '' . ($site_config['ratio_free'] ? '' : "<td>$downloaded</td>") . "<td>$ratio</td></tr>\n";
    }
    $htmlout .= "</table>\n";

    return $htmlout;
}

if ($user['paranoia'] < 2 || $user['opt1'] & user_options::HIDECUR || $CURUSER['id'] == $id || $CURUSER['class'] >= UC_STAFF) {
    if (isset($torrents)) {
        $HTMLOUT .= "<tr><td class='rowhead'>{$lang['userdetails_uploaded_t']}</td><td align='left' width='90%'><a href=\"javascript: klappe_news('a')\"><img src='{$site_config['pic_baseurl']}plus.png' id='pica' alt='Show/Hide' /></a><div id='ka' style='display: none;'>$torrents</div></td></tr>\n";
    }
    /*
    if (isset($torrents)) {
       $HTMLOUT .= "   <tr>
                        <td class='rowhead'>
                         {$lang['userdetails_uploaded_t']}
                      </td>
                      <td align='left' width='90%'>
                         <a href='#' id='slick-toggle'>Show/Hide</a>
                         <div id='slickbox' style='display: none;'>{$torrents}</div>
                      </td>
                   </tr>";
    }
    */
    /*
    if (isset($seeding)) {
       $HTMLOUT .= "   <tr>
                        <td class='rowhead'>
                         {$lang['userdetails_cur_seed']}
                      </td>
                      <td align='left' width='90%'>
                         <a href='#' id='slick-toggle'>Show/Hide</a>
                         <div id='slickbox' style='display: none;'>".maketable($seeding)."</div>
                      </td>
                   </tr>";
    }
    */
    if (isset($seeding)) {
        $HTMLOUT .= "<tr><td class='rowhead'>{$lang['userdetails_cur_seed']}</td><td align='left' width='90%'><a href=\"javascript: klappe_news('a1')\"><img src='{$site_config['pic_baseurl']}plus.png' id='pica1' alt='Show/Hide' /></a><div id='ka1' style='display: none;'>" . maketable($seeding) . "</div></td></tr>\n";
    }
    /*
    if (isset($leeching)) {
       $HTMLOUT .= "   <tr>
                        <td class='rowhead'>
                         {$lang['userdetails_cur_leech']}
                      </td>
                      <td align='left' width='90%'>
                         <a href='#' id='slick-toggle'>Show/Hide</a>
                         <div id='slickbox' style='display: none;'>".maketable($leeching)."</div>
                      </td>
                   </tr>";
    }
    */
    if (isset($leeching)) {
        $HTMLOUT .= "<tr><td class='rowhead'>{$lang['userdetails_cur_leech']}</td><td align='left' width='90%'><a href=\"javascript: klappe_news('a2')\"><img src='{$site_config['pic_baseurl']}plus.png' id='pica2' alt='Show/Hide' /></a><div id='ka2' style='display: none;'>" . maketable($leeching) . "</div></td></tr>\n";
    }
    //==Snatched

    $user_snatches_data = $cache->get('user_snatches_data_' . $id);
    if ($user_snatches_data === false || is_null($user_snatches_data)) {
        if (!XBT_TRACKER) {
            $ressnatch = sql_query('SELECT s.*, t.name AS name, c.name AS catname, c.image AS catimg FROM snatched AS s INNER JOIN torrents AS t ON s.torrentid = t.id LEFT JOIN categories AS c ON t.category = c.id WHERE s.userid =' . sqlesc($user['id'])) or sqlerr(__FILE__, __LINE__);
        } else {
            $ressnatch = sql_query('SELECT x.*, t.name AS name, c.name AS catname, c.image AS catimg FROM xbt_files_users AS x INNER JOIN torrents AS t ON x.fid = t.id LEFT JOIN categories AS c ON t.category = c.id WHERE x.uid =' . sqlesc($user['id'])) or sqlerr(__FILE__, __LINE__);
        }
        $user_snatches_data = snatchtable($ressnatch);
        $cache->set('user_snatches_data_' . $id, $user_snatches_data, $site_config['expires']['user_snatches_data']);
    }
    /*
    if (isset($user_snatches_data))
       $HTMLOUT .= "   <tr>
                        <td class='rowhead'>
                         {$lang['userdetails_cur_snatched']}
                      </td>
                      <td align='left' width='90%'>
                         <a href='#' id='slick-toggle'>Show/Hide</a>
                         <div id='slickbox' style='display: none;'>$user_snatches_data</div>
                      </td>
                   </tr>";
    //}
    */
    if (isset($user_snatches_data)) {
        $HTMLOUT .= "<tr><td class='rowhead'>{$lang['userdetails_cur_snatched']}</td><td align='left' width='90%'><a href=\"javascript: klappe_news('a3')\"><img src='{$site_config['pic_baseurl']}plus.png' id='pica3' alt='Show/Hide' /></a><div id='ka3' style='display: none;'>$user_snatches_data</div></td></tr>\n";
    }
}
//==End
// End Class
// End File
