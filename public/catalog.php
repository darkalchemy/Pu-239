<?php

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'user_functions.php';
require_once INCL_DIR . 'bbcode_functions.php';
require_once INCL_DIR . 'html_functions.php';
require_once INCL_DIR . 'pager_functions.php';
check_user_status();
global $CURUSER;

$lang = array_merge(load_language('global'), load_language('catalogue'));

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

    return format_comment_no_bbcode(strlen($text) > $char ? substr(htmlsafechars($text), 0, $char - 1) . "...<br><a href='$link'><span class='has-text-primary'>{$lang['catol_read_more']}</span></a>" : htmlsafechars($text));
}

/**
 * @param $array
 *
 * @return string
 */
function peer_list($array)
{
    global $CURUSER, $lang;

    $heading = "
        <tr>
            <th>{$lang['catol_user']}</th>";
    if ($CURUSER['class'] >= UC_STAFF) {
        $heading .= "
            <th>{$lang['catol_port']}&amp;{$lang['catol_ip']}</th>";
    }
    $heading .= "
            <th>{$lang['catol_ratio']}</th>
            <th>{$lang['catol_downloaded']}</th>
            <th>{$lang['catol_uploaded']}</th>
            <th>{$lang['catol_started']}</th>
            <th>{$lang['catol_finished']}</th>
        </tr>";
    $body = '';
    foreach ($array as $p) {
        $time = max(1, (TIME_NOW - $p['started']) - (TIME_NOW - $p['last_action']));
        $body .= '
        <tr>
            <td>' . format_username($p['p_uid']) . '</td>';
        if ($CURUSER['class'] >= UC_STAFF) {
            $body .= '
            <td>' . ($CURUSER['class'] >= UC_STAFF ? htmlsafechars($p['ip']) . ' : ' . (int) $p['port'] : 'xx.xx.xx.xx:xxxx') . '</td>';
        }
        $body .= '
            <td>' . ($p['downloaded'] > 0 ? number_format(($p['uploaded'] / $p['downloaded']), 2) : ($p['uploaded'] > 0 ? '&infin;' : '---')) . '</td>
            <td>' . ($p['downloaded'] > 0 ? mksize($p['downloaded']) . ' @' . (mksize(($p['downloaded'] - $p['downloadoffset']) / $time)) . 's' : '0kb') . '</td>
            <td>' . ($p['uploaded'] > 0 ? mksize($p['uploaded']) . ' @' . (mksize(($p['uploaded'] - $p['uploadoffset']) / $time)) . 's' : '0kb') . '</td>
            <td>' . (get_date($p['started'], 'LONG', 0, 1)) . '</td>
            <td>' . (get_date($p['finishedat'], 'LONG', 0, 1)) . '</td>
        </tr>';
    }

    return main_table($body, $heading);
}

$letter = (isset($_GET['letter']) ? htmlsafechars($_GET['letter']) : '');
$search = (isset($_GET['search']) ? htmlsafechars($_GET['search']) : '');
if (strlen($search) > 4) {
    $params = [
        ':name' => "%$search%",
    ];
    $p = 'search=' . $search . '&amp;';
} elseif (1 == strlen($letter) && false !== stripos('abcdefghijklmnopqrstuvwxyz', $letter)) {
    $params = [
        ':name' => "$letter%",
    ];
    $p = 'letter=' . $letter . '&amp;';
} else {
    $params = [
        ':name' => 'a%',
    ];
    $p      = 'letter=a&amp;';
    $letter = 'a';
}

$count = $fluent->from('torrents')
    ->select(null)
    ->select('COUNT(*) AS count')
    ->where('name LIKE :name', $params)
    ->fetch('count');

$perpage = 10;
$pager   = pager($perpage, $count, $_SERVER['PHP_SELF'] . '?' . $p);
$top     = $bottom     = '';
$rows    = $tids    = [];

$query = $fluent->from('torrents')
    ->select(null)
    ->select('id')
    ->select('name')
    ->select('leechers')
    ->select('seeders')
    ->select('poster')
    ->select('times_completed AS snatched')
    ->select('owner')
    ->select('size')
    ->select('added')
    ->select('descr')
    ->select('anonymous')
    ->where('name LIKE :name', $params)
    ->limit(str_replace('LIMIT ', '', $pager['limit']));

foreach ($query as $ta) {
    $rows[] = $ta;
    $tid[]  = $ta['id'];
}

if (!empty($tid)) {
    $query = $fluent->from('peers')
        ->select(null)
        ->select('id')
        ->select('torrent AS tid')
        ->select('seeder')
        ->select('finishedat')
        ->select('downloadoffset')
        ->select('uploadoffset')
        ->select('uploaded')
        ->select('downloaded')
        ->select('started')
        ->select('last_action')
        ->select('userid AS p_uid')
        ->select('INET6_NTOA(ip) AS ip')
        ->select('port')
        ->where('torrent', $tid)
        ->where('seeder = "yes"')
        ->where('to_go = 0')
        ->limit(5);

    foreach ($query as $pa) {
        $peers[$pa['tid']][] = $pa;
    }
}

$htmlout = "
    <h1 class='has-text-centered'>Torrent Catalog</h1>";
$div = "
    <h2 class='has-text-centered'>{$lang['catol_search']}</h2>
    <form  action='" . $_SERVER['PHP_SELF'] . "' method='get' class='has-text-centered'>
        <input type='text' name='search' class='w-50' placeholder='{$lang['catol_search_for_tor']}' value='$search' /><br>
        <input type='submit' value='search!' class='button is-small margin20' />
    </form>
    <div class='tabs is-centered is-small'>
        <ul>";
for ($i = 97; $i < 123; ++$i) {
    $active = !empty($letter) && $letter == chr($i) ? "class='active'" : '';
    $div .= "
            <li>
                <a href='{$site_config['baseurl']}/catalog.php?letter=" . chr($i) . "' $active>" . chr($i - 32) . '</a>
            </li>';
}
$div .= '
        </ul>
    </div>';

$htmlout .= main_div($div);

if (!empty($rows)) {
    foreach ($rows as $row) {
        if ('yes' == $row['anonymous'] && ($CURUSER['class'] < UC_STAFF || $row['owner'] === $CURUSER['id'])) {
            $uploader = get_anonymous_name();
        } else {
            $uploader = format_username($row['owner']);
        }

        $div = "
        <div class='columns'>
            <div class='column is-2 has-text-centered'>
                <div class='bottom10'>{$lang['catol_upper']}: $uploader</div>
                <div>" . ($row['poster'] ? "
                    <img src='" . image_proxy($row['poster']) . "' alt='Poster' class='tooltip-poster' />
                </div>" : "
                    <img src='{$site_config['pic_baseurl']}noposter.png' alt='{$lang['catol_no_poster']}' class='tooltip-poster' />
                </div>") . "
            </div   >
            <div class='column'>";
        $heading = "
                    <tr>
                        <th>Name</th>
                        <th>{$lang['catol_added']}</th>
                        <th>{$lang['catol_size']}</th>
                        <th>{$lang['catol_snatched']}</th>
                        <th>S.</th>
                        <th>L.</th>
                    </tr>";
        $body = "
                    <tr>
                        <td><a href='{$site_config['baseurl']}/details.php?id=" . (int) $row['id'] . "&amp;hit=1'><b>" . substr(htmlsafechars($row['name']), 0, 60) . '</b></a></td>
                        <td>' . get_date($row['added'], 'LONG', 0, 1) . "</td>
                        <td nowrap='nowrap'>" . (mksize($row['size'])) . "</td>
                        <td nowrap='nowrap'>" . ($row['snatched'] > 0 ? (1 == $row['snatched'] ? (int) $row['snatched'] . ' time' : (int) $row['snatched'] . ' times') : 0) . '</td>
                        <td>' . (int) $row['seeders'] . '</td>
                        <td>' . (int) $row['leechers'] . '</td>
                    </tr>';
        $div .= main_table($body, $heading, 'top20');
        $heading = "
                <tr>
                    <th>{$lang['catol_info']}.</th>
                </tr>";
        $body = '
                <tr>
                    <td>' . readMore($row['descr'], 500, $site_config['baseurl'] . '/details.php?id=' . (int) $row['id'] . '&amp;hit=1') . '</td>
                </tr>';
        $div .= main_table($body, $heading, 'top20');
        $div .= "
            </div>
        </div>
        <div class='w-100'>
            <h2 class='has-text-centered'>{$lang['catol_seeder_info']}</h2>
            " . (isset($peers[$row['id']]) ? peer_list($peers[$row['id']]) : main_div("
            <h2 class='has-text-centered'>{$lang['catol_no_info_show']}</h2>")) . '
        </div>';
        $htmlout .= main_div($div, 'top20');
    }
    $htmlout .= "
        <div>
            {$bottom}
        </div>";
} else {
    $htmlout .= main_div("
        <h2 class='has-text-centered'>{$lang['catol_nothing_found']}!</h2>", 'top20');
}

echo stdhead($lang['catol_std_head']) . wrapper($htmlout) . stdfoot();
