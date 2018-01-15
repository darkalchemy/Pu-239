<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'user_functions.php';
require_once INCL_DIR . 'bbcode_functions.php';
require_once INCL_DIR . 'html_functions.php';
require_once INCL_DIR . 'pager_functions.php';
check_user_status();
global $CURUSER;

$lang = array_merge(load_language('global'), load_language('catalogue'));
$htmlout = '';
/**
 * @param $text
 * @param $char
 * @param $link
 *
 * @return mixed|string
 */
function readMore($text, $char, $link)
{
    global $lang;
    return strlen($text) > $char ? substr(htmlsafechars($text), 0, $char - 1) . "...<br><a href='$link'>{$lang['catol_read_more']}</a>" : htmlsafechars($text);
}

/**
 * @param $array
 *
 * @return string
 */
function peer_list($array)
{
    global $CURUSER, $lang;
    $htmlout = '';
    $htmlout .= "<table width='100%' border='1' cellpadding='5' style='border-collapse:collapse'>
                <tr>
            <td class='colhead'>{$lang['catol_user']}</td>
            <td class='colhead'>{$lang['catol_port']}&amp;{$lang['catol_ip']}</td>
            <td class='colhead'>{$lang['catol_ratio']}</td>
            <td class='colhead'>{$lang['catol_downloaded']}</td>
            <td class='colhead'>{$lang['catol_uploaded']}</td>
            <td class='colhead'>{$lang['catol_started']}</td>
            <td class='colhead'>{$lang['catol_finished']}</td>
       </tr>";
    foreach ($array as $p) {
        $time = max(1, (TIME_NOW - $p['started']) - (TIME_NOW - $p['last_action']));
        $htmlout .= "<tr>
            <td><a href='userdetails.php?id=" . (int)$p['p_uid'] . "' >" . htmlsafechars($p['p_user']) . "</a></td>
            <td>" . ($CURUSER['class'] >= UC_STAFF ? htmlsafechars($p['ip']) . ' : ' . (int)$p['port'] : 'xx.xx.xx.xx:xxxx') . "</td>
            <td>" . ($p['downloaded'] > 0 ? number_format(($p['uploaded'] / $p['downloaded']), 2) : ($p['uploaded'] > 0 ? '&infin;' : '---')) . "</td>
            <td>" . ($p['downloaded'] > 0 ? mksize($p['downloaded']) . ' @' . (mksize(($p['downloaded'] - $p['downloadoffset']) / $time)) . 's' : '0kb') . "</td>
            <td>" . ($p['uploaded'] > 0 ? mksize($p['uploaded']) . ' @' . (mksize(($p['uploaded'] - $p['uploadoffset']) / $time)) . 's' : '0kb') . "</td>
            <td>" . (get_date($p['started'], 'LONG', 0, 1)) . "</td>
            <td>" . (get_date($p['finishedat'], 'LONG', 0, 1)) . '</td>
            </tr>';
    }
    $htmlout .= '</table>';

    return $htmlout;
}

$letter = (isset($_GET['letter']) ? htmlsafechars($_GET['letter']) : '');
$search = (isset($_GET['search']) ? htmlsafechars($_GET['search']) : '');
if (strlen($search) > 4) {
    $where = 'WHERE t.name LIKE' . sqlesc('%' . $search . '%');
    $p = 'search=' . $search . '&amp;';
} elseif (strlen($letter) == 1 && strpos('abcdefghijklmnopqrstuvwxyz', $letter) !== false) {
    $where = "WHERE t.name LIKE '" . $letter . "%'";
    $p = 'letter=' . $letter . '&amp;';
} else {
    $where = "WHERE t.name LIKE 'a%'";
    $p = 'letter=a&amp;';
    $letter = 'a';
}
$count = mysqli_fetch_row(sql_query("SELECT count(*) FROM torrents AS t $where"));
$perpage = 10;
$pager = pager($perpage, $count[0], $_SERVER['PHP_SELF'] . '?' . $p);
//$tid='';
//$rows='';
$top = '';
$bottom = '';
$rows = [];
$tids = [];
$t = sql_query("SELECT t.id,t.name,t.leechers,t.seeders,t.poster,t.times_completed as snatched,t.owner,t.size,t.added,t.descr, u.username as user FROM torrents as t LEFT JOIN users AS u on u.id=t.owner $where ORDER BY t.name ASC " . $pager['limit']) or sqlerr(__FILE__, __LINE__);
while ($ta = mysqli_fetch_assoc($t)) {
    $rows[] = $ta;
    $tid[] = (int)$ta['id'];
}
if (isset($tids) && count($tids)) {
    $p = sql_query('SELECT p.id,p.torrent AS tid,p.seeder, p.finishedat, p.downloadoffset, p.uploadoffset, p.ip, p.port, p.uploaded, p.downloaded, p.started AS started, p.last_action AS last_action, u.id AS p_uid , u.username AS p_user FROM peers AS p LEFT JOIN users AS u ON u.id=p.userid WHERE p.torrent IN (' . join(',', $tid) . ") AND p.seeder = 'yes' AND to_go=0 LIMIT 5") or sqlerr(__FILE__, __LINE__);
    while ($pa = mysqli_fetch_assoc($p)) {
        $peers[$pa['tid']][] = $pa;
    }
}
$htmlout .= "<div style='width:90%'>
        <fieldset style='border:2px solid #333333;'>
            <legend style='padding:5xp 0 0 5px;'>{$lang['catol_search']}</legend>
                <form  action='" . $_SERVER['PHP_SELF'] . "' method='get' style='margin:10px;'>
                    <input type='text' size='50' name='search' value='" . ($search ? $search : "{$lang['catol_search_for_tor']}") . "' onblur=\"if (this.value == '') this.value='{$lang['catol_search_for_tor']}';\" onfocus=\"if (this.value == '{$lang['catol_search_for_tor']}') this.value='';\" />&#160;<input type='submit' value='search!' />
                </form>";
for ($i = 97; $i < 123; ++$i) {
    $l = chr($i);
    $L = chr($i - 32);
    if ($l == $letter) {
        $htmlout .= "<font class=\"sublink-active\">$L</font>\n";
    } else {
        $htmlout .= '<a class="sublink" href="' . $_SERVER['PHP_SELF'] . '?letter=' . $l . '">' . $L . "</a>\n";
    }
}
$htmlout .= '</fieldset></div><br>';
$htmlout .= begin_frame();
if (count($rows) > 0) {
    $htmlout .= "<table width='95%' border='1' cellpadding='5' style='border-collapse:collapse'>
        <tr><td colspan='2' class='colhead'>{$lang['catol_std_head']}</td></tr>
        <tr><td colspan='2' >{$top}</td></tr>";
    foreach ($rows as $row) {
        $htmlout .= "<tr>
         <td nowrap='nowrap'>
            <table width='160' border='1' cellpadding='2'>
                <tr><td class='colhead'>{$lang['catol_upper']} : <a href='userdetails.php?id=" . (int)$row['owner'] . "'>" . ($row['user'] ? htmlsafechars($row['user']) : "{$lang['catol_unknown']}[" . (int)$row['owner'] . ']') . "</a></td></tr>
                <tr><td>" . ($row['poster'] ? '<a href="' . htmlsafechars($row['poster']) . '"><img src="' . htmlsafechars($row['poster']) . "\" border=\"0\" width=\"150\" height=\"195\" alt=\"{$lang['catol_no_poster']}\" title=\"{$lang['catol_no_poster']}\" /></a>" : "<img src='{$site_config['pic_baseurl']}noposter.png' border='0' width='150' alt='{$lang['catol_no_poster']}' title='{$lang['catol_no_poster']}' />") . "</td></tr>
            </table>

        </td>

            <td width='100%'>
            <table width='100%' cellpadding='3' cellspacing='0' border='1' style='border-collapse:collapse;font-weight:bold;'>
            <tr>
                <td width='100%' rowspan='2' ><a href='details.php?id=" . (int)$row['id'] . "&amp;hit=1'><b>" . substr(htmlsafechars($row['name']), 0, 60) . "</b></a></td>
                <td class='colhead'>{$lang['catol_added']}</td>
                <td class='colhead'>{$lang['catol_size']}</td>
                <td class='colhead'>{$lang['catol_snatched']}</td>
                <td class='colhead'>S.</td>
                <td class='colhead'>L.</td>
            </tr>
            <tr>
                <td>" . get_date($row['added'], 'LONG', 0, 1) . "</td>
                <td nowrap='nowrap'>" . (mksize($row['size'])) . "</td>
                <td nowrap='nowrap'>" . ($row['snatched'] > 0 ? ($row['snatched'] == 1 ? (int)$row['snatched'] . ' time' : (int)$row['snatched'] . ' times') : 0) . "</td>
                <td>" . (int)$row['seeders'] . "</td>
                <td>" . (int)$row['leechers'] . "</td>
            </tr>
            <tr><td width='100%' colspan='6' class='colhead' >{$lang['catol_info']}.</td></tr>
            <tr><td width='100%' colspan='6' style='font-weight:normal;' >" . readMore($row['descr'], 500, 'details.php?id=' . (int)$row['id'] . '&amp;hit=1') . "</td></tr>
            <tr><td width='100%' colspan='6' class='colhead'>{$lang['catol_seeder_info']}</td></tr>
            <tr><td width='100%' colspan='6' style='font-weight:normal;' >" . (isset($peers[$row['id']]) ? peer_list($peers[$row['id']]) : "{$lang['catol_no_info_show']}") . '</td></tr>
            </table></td></tr>';
    }
    $htmlout .= "<tr><td colspan='2' >{$bottom}</td></tr>
        <tr><td colspan='2' class='colhead'>{$lang['catol_orig_created_by']}</td></tr>
        </table>";
} else {
    $htmlout .= "<h2>{$lang['catol_nothing_found']}!</h2>";
}
$htmlout .= end_frame();
echo stdhead($lang['catol_std_head']) . $htmlout . stdfoot();
