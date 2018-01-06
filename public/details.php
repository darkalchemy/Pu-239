<?php
require_once realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..') . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'user_functions.php';
require_once INCL_DIR . 'bbcode_functions.php';
require_once INCL_DIR . 'pager_functions.php';
require_once INCL_DIR . 'comment_functions.php';
require_once INCL_DIR . 'html_functions.php';
require_once INCL_DIR . 'function_rating.php';
require_once INCL_DIR . 'tvmaze_functions.php';
require_once IMDB_DIR . 'imdb.class.php';
check_user_status();
global $CURUSER, $site_config, $cache;

$lang = array_merge(load_language('global'), load_language('details'));
$stdhead = [
    'css' => [
        get_file('details_css'),
    ],
];
$stdfoot = [
    'js' => [
        get_file('details_js'),
    ],
];
$HTMLOUT = $torrent_cache = '';
$dt = TIME_NOW;

if (!isset($_GET['id']) || !is_valid_id($_GET['id'])) {
    stderr("{$lang['details_user_error']}", "{$lang['details_bad_id']}");
}
$id = (int)$_GET['id'];

$slot = make_freeslots($CURUSER['id'], 'fllslot_');
$torrent['addedfree'] = $torrent['addedup'] = $free_slot = $double_slot = '';
if (!empty($slot)) {
    foreach ($slot as $sl) {
        if ($sl['torrentid'] == $id && $sl['free'] == 'yes') {
            $free_slot = 1;
            $torrent['addedfree'] = $sl['addedfree'];
        }
        if ($sl['torrentid'] == $id && $sl['doubleup'] == 'yes') {
            $double_slot = 1;
            $torrent['addedup'] = $sl['addedup'];
        }
        if ($free_slot && $double_slot) {
            break;
        }
    }
}
$categorie = genrelist();
foreach ($categorie as $key => $value) {
    $change[ $value['id'] ] = [
        'id'    => $value['id'],
        'name'  => $value['name'],
        'image' => $value['image'],
    ];
}

$torrents = $cache->get('torrent_details_' . $id);
if ($torrents === false || is_null($torrents)) {
    $tor_fields_ar_int = [
        'id',
        'leechers',
        'seeders',
        'thanks',
        'comments',
        'owner',
        'size',
        'added',
        'views',
        'hits',
        'numfiles',
        'times_completed',
        'points',
        'last_reseed',
        'category',
        'free',
        'freetorrent',
        'silver',
        'rating_sum',
        'checked_when',
        'num_ratings',
        'mtime',
        'checked_when',
        'checked_by',
    ];
    $tor_fields_ar_str = [
        'banned',
        'info_hash',
        'filename',
        'search_text',
        'name',
        'save_as',
        'visible',
        'poster',
        'url',
        'anonymous',
        'allow_comments',
        'description',
        'nuked',
        'nukereason',
        'vip',
        'subs',
        'newgenre',
        'release_group',
        'youtube',
        'tags',
    ];
    $tor_fields = implode(', ', array_merge($tor_fields_ar_int, $tor_fields_ar_str));
    $result = sql_query('SELECT ' . $tor_fields . ", LENGTH(nfo) AS nfosz, IF(num_ratings < {$site_config['minvotes']}, NULL, ROUND(rating_sum / num_ratings, 1)) AS rating FROM torrents WHERE id = " . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
    $torrents = mysqli_fetch_assoc($result);
    foreach ($tor_fields_ar_int as $i) {
        $torrents[ $i ] = (int)$torrents[ $i ];
    }
    foreach ($tor_fields_ar_str as $i) {
        $torrents[ $i ] = $torrents[ $i ];
    }
    $cache->set('torrent_details_' . $id, $torrents, $site_config['expires']['torrent_details']);
}
//==
if (($torrents_xbt = $cache->get('torrent_xbt_data_' . $id)) === false && XBT_TRACKER == true) {
    $torrents_xbt = mysqli_fetch_assoc(sql_query('SELECT seeders, leechers, times_completed FROM torrents WHERE id =' . sqlesc($id))) or sqlerr(__FILE__, __LINE__);
    $cache->set('torrent_xbt_data_' . $id, $torrents_xbt, $site_config['expires']['torrent_xbt_data']);
}
//==
$torrents_txt = $cache->get('torrent_details_txt' . $id);
if ($torrents_txt === false || is_null($torrents_txt)) {
    $torrents_txt = mysqli_fetch_assoc(sql_query('SELECT descr FROM torrents WHERE id =' . sqlesc($id))) or sqlerr(__FILE__, __LINE__);
    $cache->set('torrent_details_txt' . $id, $torrents_txt, $site_config['expires']['torrent_details_text']);
}
//==
if (isset($_GET['hit'])) {
    sql_query('UPDATE torrents SET views = views + 1 WHERE id =' . sqlesc($id));
    $update['views'] = ($torrents['views'] + 1);
    $cache->update_row('torrent_details_' . $id, [
        'views' => $update['views'],
    ], $site_config['expires']['torrent_details']);
    header("Location: details.php?id=$id");
    die();
}
$What_String = (XBT_TRACKER == true ? 'mtime' : 'last_action');
$What_String_Key = (XBT_TRACKER == true ? 'last_action_xbt_' : 'last_action_');
$l_a = $cache->get($What_String_Key . $id);
if ($l_a === false || is_null($l_a)) {
    $l_a = mysqli_fetch_assoc(sql_query('SELECT ' . $What_String . ' AS lastseed ' . 'FROM torrents ' . 'WHERE id = ' . sqlesc($id))) or sqlerr(__FILE__, __LINE__);
    $l_a['lastseed'] = (int)$l_a['lastseed'];
    $cache->add('last_action_' . $id, $l_a, 1800);
}

$torrent_cache['seeders'] = $cache->get('torrents::seeds:::' . $id);
$torrent_cache['leechers'] = $cache->get('torrents::leechs:::' . $id);
$torrent_cache['times_completed'] = $cache->get('torrents::comps:::' . $id);
$torrents['seeders'] = ((XBT_TRACKER === false || $torrent_cache['seeders'] === false || $torrent_cache['seeders'] === 0 || $torrent_cache['seeders'] === false) ? $torrents['seeders'] : $torrent_cache['seeders']);
$torrents['leechers'] = ((XBT_TRACKER === false || $torrent_cache['leechers'] === false || $torrent_cache['leechers'] === 0 || $torrent_cache['leechers'] === false) ? $torrents['leechers'] : $torrent_cache['leechers']);
$torrents['times_completed'] = ((XBT_TRACKER === false || $torrent_cache['times_completed'] === false || $torrent_cache['times_completed'] === 0 || $torrent_cache['times_completed'] === false) ? $torrents['times_completed'] : $torrent_cache['times_completed']);

$torrent['addup'] = get_date($torrent['addedup'], 'DATE');
$torrent['addfree'] = get_date($torrent['addedfree'], 'DATE');
$torrent['idk'] = ($dt + 14 * 86400);
$torrent['freeimg'] = '<img src="' . $site_config['pic_base_url'] . 'freedownload.gif" alt="" />';
$torrent['doubleimg'] = '<img src="' . $site_config['pic_base_url'] . 'doubleseed.gif" alt="" />';
$torrent['free_color'] = '#FF0000';
$torrent['silver_color'] = 'silver';
$torrent_cache['rep'] = $cache->get('user_rep_' . $torrents['owner']);
if ($torrent_cache['rep'] === false || is_null($torrent_cache['rep'])) {
    $torrent_cache['rep'] = [];
    $us = sql_query('SELECT reputation FROM users WHERE id =' . sqlesc($torrents['owner'])) or sqlerr(__FILE__, __LINE__);
    if (mysqli_num_rows($us)) {
        $torrent_cache['rep'] = mysqli_fetch_assoc($us);
        $cache->add('user_rep_' . $torrents['owner'], $torrent_cache['rep'], 14 * 86400);
    }
}
$owned = $moderator = 0;
if ($CURUSER['class'] >= UC_STAFF) {
    $owned = $moderator = 1;
} elseif ($CURUSER['id'] == $torrents['owner']) {
    $owned = 1;
}
if ($torrents['vip'] == '1' && $CURUSER['class'] < UC_VIP) {
    stderr('VIP Access Required', 'You must be a VIP In order to view details or download this torrent! You may become a Vip By Donating to our site. Donating ensures we stay online to provide you more Vip-Only Torrents!');
}
if (!$torrents || ($torrents['banned'] == 'yes' && !$moderator)) {
    stderr("{$lang['details_error']}", "{$lang['details_torrent_id']}");
}
if ($CURUSER['id'] == $torrents['owner'] || $CURUSER['class'] >= UC_STAFF) {
    $owned = 1;
} else {
    $owned = 0;
}
if (empty($torrents['tags'])) {
    $keywords = 'No Keywords Specified.';
} else {
    $tags = explode(',', $torrents['tags']);
    $keywords = '';
    foreach ($tags as $tag) {
        $keywords .= "<a href='browse.php?search=$tag&amp;searchin=all&amp;incldead=1'>" . htmlsafechars($tag) . '</a>,';
    }
    $keywords = substr($keywords, 0, (strlen($keywords) - 1));
}

if ($CURUSER['class'] >= UC_STAFF) {
    if (isset($_POST['checked']) && $_POST['checked'] === $_GET['id']) {
        sql_query('UPDATE torrents SET checked_by = ' . sqlesc($CURUSER['id']) . ', checked_when = ' . $dt . ' WHERE id =' . sqlesc($id) . ' LIMIT 1') or sqlerr(__FILE__, __LINE__);
        $cache->update_row('torrent_details_' . $id, [
            'checked_by'   => $CURUSER['id'],
            'checked_when' => $dt,
        ], $site_config['expires']['torrent_details']);
        $torrents['checked_by'] = $CURUSER['id'];
        $torrents['checked_when'] = $dt;
        $cache->set('checked_by_' . $id, $CURUSER['id'], 0);
        write_log("Torrent <a href=details.php?id=$id>(" . htmlsafechars($torrents['name']) . ")</a> was checked by {$CURUSER['username']}");
        setSessionVar('is-success', "Torrents has been 'Checked'");
    } elseif (isset($_POST['rechecked']) && $_POST['rechecked'] === $_GET['id']) {
        sql_query('UPDATE torrents SET checked_by = ' . sqlesc($CURUSER['id']) . ', checked_when = ' . $dt . ' WHERE id =' . sqlesc($id) . ' LIMIT 1') or sqlerr(__FILE__, __LINE__);
        $cache->update_row('torrent_details_' . $id, [
            'checked_by'   => $CURUSER['id'],
            'checked_when' => $dt,
        ], $site_config['expires']['torrent_details']);
        $torrents['checked_by'] = $CURUSER['id'];
        $torrents['checked_when'] = $dt;
        $cache->set('checked_by_' . $id, $CURUSER['id'], 0);
        write_log("Torrent <a href=details.php?id=$id>(" . htmlsafechars($torrents['name']) . ")</a> was re-checked by {$CURUSER['username']}");
        setSessionVar('is-success', "Torrents has been 'Re-Checked'");
    } elseif (isset($_POST['clearchecked']) && $_POST['clearchecked'] === $_GET['id']) {
        sql_query("UPDATE torrents SET checked_by = 0, checked_when = 0 WHERE id = " . sqlesc($id) . ' LIMIT 1') or sqlerr(__FILE__, __LINE__);
        $cache->update_row('torrent_details_' . $id, [
            'checked_by'   => 0,
            'checked_when' => 0,
        ], $site_config['expires']['torrent_details']);
        $torrents['checked_by'] = 0;
        $torrents['checked_when'] = 0;
        $cache->delete('checked_by_' . $id);
        write_log("Torrent <a href=details.php?id=$id>(" . htmlsafechars($torrents['name']) . ")</a> was un-checked by {$CURUSER['username']}");
        setSessionVar('is-success', "Torrents has been 'Un-Checked'");
    }
}

$s = htmlsafechars($torrents['name'], ENT_QUOTES);
$HTMLOUT .= "
        <div class='container is-fluid portlet'>
            <div class='has-text-centered margin20'>
                <span class='size_7'>$s</span>";

$thumbs = $cache->get('thumbs_up_' . $id);
if ($thumbs === false || is_null($thumbs)) {
    $thumbs = mysqli_num_rows(sql_query('SELECT id, type, torrentid, userid FROM thumbsup WHERE torrentid = ' . sqlesc($torrents['id'])));
    $thumbs = (int)$thumbs;
    $cache->add('thumbs_up_' . $id, $thumbs, 0);
}
$HTMLOUT .= "
                <div class='top20'>{$lang['details_thumbs']}</div>
                <div id='thumbsup'>
                    <a href=\"javascript:ThumbsUp('" . (int)$torrents['id'] . "')\">
                        <img src='{$site_config['pic_base_url']}thumb_up.png' alt='Thumbs Up' class='tooltipper' title='Thumbs Up' width='12' height='12' class='right10' />
                    </a>(" . $thumbs . ")
                </div>
           </div>
        </div>
    <div class='tooltip_templates'>
        <span id='balloon1'>
            Once chosen this torrent will be Freeleech {$torrent['freeimg']} until " . get_date($torrent['idk'], 'DATE') . " and can be resumed or started over using the regular download link. Doing so will result in one Freeleech Slot being taken away from your total.
        </span>
    </div>
    <div class='tooltip_templates'>
        <span id='balloon2'>
            Once chosen this torrent will be Doubleseed {$torrent['doubleimg']} until " . get_date($torrent['idk'], 'DATE') . " and can be resumed or started over using the regular download link. Doing so will result in one Freeleech Slot being taken away from your total.
        </span>
    </div>
    <div class='tooltip_templates'>
        <span id='balloon3'>
            Remember to show your gratitude and Thank the Uploader. <img src='{$site_config['pic_base_url']}smilies/smile1.gif' alt='' />
        </span>
    </div>";
$url = 'edit.php?id=' . (int)$torrents['id'];
if (isset($_GET['returnto'])) {
    $addthis = '&amp;returnto=' . urlencode($_GET['returnto']);
    $url .= $addthis;
    $keepget = $addthis;
}
$editlink = "a href='$url' class='button is-primary is-small bottom10'";
if (!($CURUSER['downloadpos'] == 0 && $CURUSER['id'] != $torrents['owner'] or $CURUSER['downloadpos'] > 1)) {
    if ($free_slot && !$double_slot) {
        $HTMLOUT .= '
                <tr>
                    <td class="rowhead">Slots</td>
                        <td>' . $torrent['freeimg'] . ' <b><font color="' . $torrent['free_color'] . '">Freeleech Slot In Use!</font></b> (only upload stats are recorded) - Expires: 12:01AM ' . $torrent['addfree'] . '
                    </td>
                </tr>';
        $freeslot = ($CURUSER['freeslots'] >= 1 ? "<b>Use: </b><a class='index dt-tooltipper-small' href='{$site_config['baseurl']}/download.php?torrent={$id}" . ($CURUSER['ssluse'] == 3 ? '&amp;ssl=1' : '') . "&amp;slot=double' data-tooltip-content='#balloon1' rel='balloon2' onclick=\"return confirm('Are you sure you want to use a doubleseed slot?')\"><span style='color: " . $torrent['free_color'] . ";'><b>Doubleseed Slot</b></span></a>- " . htmlsafechars($CURUSER['freeslots']) . ' Slots Remaining. ' : '');
        $freeslot_zip = ($CURUSER['freeslots'] >= 1 ? "<b>Use: </b><a class='index dt-tooltipper-small' href='{$site_config['baseurl']}/download.php?torrent={$id}" . ($CURUSER['ssluse'] == 3 ? '&amp;ssl=1' : '') . "&amp;slot=double&amp;zip=1' data-tooltip-content='#balloon2' rel='balloon2' onclick=\"return confirm('Are you sure you want to use a doubleseed slot?')'><span style='color: " . $torrent['free_color'] . ";'><b>Doubleseed Slot</b></span></a>- " . htmlsafechars($CURUSER['freeslots']) . ' Slots Remaining. ' : '');
        $freeslot_text = ($CURUSER['freeslots'] >= 1 ? "<b>Use: </b><a class='index dt-tooltipper-small' href='{$site_config['baseurl']}/download.php?torrent={$id}" . ($CURUSER['ssluse'] == 3 ? '&amp;ssl=1' : '') . "&amp;slot=double&amp;text=1' data-tooltip-content='#balloon2' rel='balloon2' onclick=\"return confirm('Are you sure you want to use a doubleseed slot?')\"><span style='color: " . $torrent['free_color'] . ";'><b>Doubleseed Slot</b></span></a>- " . htmlsafechars($CURUSER['freeslots']) . ' Slots Remaining. ' : '');
    } elseif (!$free_slot && $double_slot) {
        $HTMLOUT .= '<tr>
                <td class="rowhead">Slots</td>
                <td>' . $torrent['doubleimg'] . ' <b><font color="' . $torrent['free_color'] . '">Doubleseed Slot In Use!</font></b> (upload stats x2) - Expires: 12:01AM ' . $torrent['addup'] . '
                </td>
            </tr>';
        $freeslot = ($CURUSER['freeslots'] >= 1 ? "<b>Use: </b><a class='index dt-tooltipper-small' href='{$site_config['baseurl']}/download.php?torrent={$id}" . ($CURUSER['ssluse'] == 3 ? '&amp;ssl=1' : '') . "&amp;slot=free' data-tooltip-content='#balloon1' rel='balloon1' onclick=\"return confirm('Are you sure you want to use a freeleech slot?')\"><span style='color: " . $torrent['free_color'] . ";'><b>Freeleech Slot</b></span></a>- " . htmlsafechars($CURUSER['freeslots']) . ' Slots Remaining. ' : '');
        $freeslot_zip = ($CURUSER['freeslots'] >= 1 ? "<b>Use: </b><a class='index dt-tooltipper-small' href='{$site_config['baseurl']}/download.php?torrent={$id}" . ($CURUSER['ssluse'] == 3 ? '&amp;ssl=1' : '') . "&amp;slot=free&amp;zip=1' data-tooltip-content='#balloon1' rel='balloon1' onclick=\"return confirm('Are you sure you want to use a freeleech slot?')\"><span style='color: " . $torrent['free_color'] . ";'><b>Freeleech Slot</b></span></a>- " . htmlsafechars($CURUSER['freeslots']) . ' Slots Remaining. ' : '');
        $freeslot_text = ($CURUSER['freeslots'] >= 1 ? "<b>Use: </b><a class='index dt-tooltipper-small' href='{$site_config['baseurl']}/download.php?torrent={$id}" . ($CURUSER['ssluse'] == 3 ? '&amp;ssl=1' : '') . "&amp;slot=free&amp;text=1' data-tooltip-content='#balloon1' rel='balloon1' onclick=\"return confirm('Are you sure you want to use a freeleech slot?')\"><span style='color: " . $torrent['free_color'] . ";'><b>Freeleech Slot</b></span></a>- " . htmlsafechars($CURUSER['freeslots']) . ' Slots Remaining. ' : '');
    } elseif ($free_slot && $double_slot) {
        $HTMLOUT .= '<tr>
                <td class="rowhead">Slots</td>
                <td>' . $torrent['freeimg'] . ' ' . $torrent['doubleimg'] . ' <b><font color="' . $torrent['free_color'] . '">Freeleech and Doubleseed Slots In Use!</font></b> (upload stats x2 and no download stats are recorded)<p>Freeleech Expires: 12:01AM ' . $torrent['addfree'] . ' and Doubleseed Expires: 12:01AM ' . $torrent['addup'] . '</p>
                </td>
            </tr>';
        $freeslot = $freeslot_zip = $freeslot_text = '';
    } else {
        $freeslot = ($CURUSER['freeslots'] >= 1 ? "
        <b>Use: </b>
            <a class='index dt-tooltipper-small' href='{$site_config['baseurl']}/download.php?torrent={$id}" . ($CURUSER['ssluse'] == 3 ? '&amp;ssl=1' : '') . "&amp;slot=free' data-tooltip-content='#balloon1' rel='balloon1' onclick=\"return confirm('Are you sure you want to use a freeleech slot?')\">
                <span style='color: " . $torrent['free_color'] . ";'><b>Freeleech Slot</b></span>
            </a>
        <b>Use: </b>
            <a class='index dt-tooltipper-small' href='download.php?torrent={$id}" . ($CURUSER['ssluse'] == 3 ? '&amp;ssl=1' : '') . "&amp;slot=double' data-tooltip-content='#balloon2' rel='balloon2' onclick=\"return confirm('Are you sure you want to use a doubleseed slot?')\">
                <span style='color: " . $torrent['free_color'] . ";'><b>Doubleseed Slot</b></span>
            </a>- " . htmlsafechars($CURUSER['freeslots']) . ' Slots Remaining. ' : '');
    }
    $freeslot_zip = ($CURUSER['freeslots'] >= 1 ? "<b>Use: </b><a class='index dt-tooltipper-small' href='{$site_config['baseurl']}/download.php?torrent={$id}" . ($CURUSER['ssluse'] == 3 ? '&amp;ssl=1' : '') . "&amp;slot=free&amp;zip=1' data-tooltip-content='#balloon1' rel='balloon1' onclick=\"return confirm('Are you sure you want to use a freeleech slot?')\"><span style='color: " . $torrent['free_color'] . "'><b>Freeleech Slot</b></font></a> <b>Use: </b><a class='index dt-tooltipper-small' href='download.php?torrent={$id}" . ($CURUSER['ssluse'] == 3 ? '&amp;ssl=1' : '') . "&amp;slot=double&amp;zip=1' data-tooltip-content='#balloon2' rel='balloon2' onclick=\"return confirm('Are you sure you want to use a doubleseed slot?')\"><font color='" . $torrent['free_color'] . ";'><b>Doubleseed Slot</b></span></a>- " . htmlsafechars($CURUSER['freeslots']) . ' Slots Remaining. ' : '');
    $freeslot_text = ($CURUSER['freeslots'] >= 1 ? "<b>Use: </b><a class='index dt-tooltipper-small' href='{$site_config['baseurl']}/download.php?torrent={$id}" . ($CURUSER['ssluse'] == 3 ? '&amp;ssl=1' : '') . "&amp;slot=free&amp;text=1' data-tooltip-content='#balloon1' rel='balloon1' onclick=\"return confirm('Are you sure you want to use a freeleech slot?')\"><span style='color: " . $torrent['free_color'] . "'><b>Freeleech Slot</b></font></a> <b>Use: </b><a class='index dt-tooltipper-small' href='download.php?torrent={$id}" . ($CURUSER['ssluse'] == 3 ? '&amp;ssl=1' : '') . "&amp;slot=double&amp;text=1' data-tooltip-content='#balloon2' rel='balloon2' onclick=\"return confirm('Are you sure you want to use a doubleseed slot?')\"><font color='" . $torrent['free_color'] . ";'><b>Doubleseed Slot</b></span></a>- " . htmlsafechars($CURUSER['freeslots']) . ' Slots Remaining. ' : '');

    $HTMLOUT .= "
        <div class='top10 bottom10 has-text-centered'>";

    require_once MODS_DIR . 'free_details.php';
    $HTMLOUT .= "
        </div>
        <div class='level has-text-centered bottom20'>
            <div class='img-polaroid round10'>";

    if (!empty($torrents['poster'])) {
        $HTMLOUT .= "<img src='" . htmlsafechars($torrents['poster']) . "' class='round10' alt='Poster' />";
    }
    if (empty($torrents['poster'])) {
        $HTMLOUT .= 'No Poster Found.';
    }
    $Free_Slot = (XBT_TRACKER == true ? '' : $freeslot);
    $Free_Slot_Zip = (XBT_TRACKER == true ? '' : $freeslot_zip);
    $Free_Slot_Text = (XBT_TRACKER == true ? '' : $freeslot_text);
    $HTMLOUT .= "
            </div>
            <div class='w-100 table-wrapper'>
                <table class='table table-bordered crap'>
                    <tr>
                        <td class='rowhead' width='3%'>{$lang['details_download']}</td>
                        <td>
                            <a class='index' href='{$site_config['baseurl']}/download.php?torrent={$id}" . ($CURUSER['ssluse'] == 3 ? '&amp;ssl=1' : '') . "'><u>" . htmlsafechars($torrents['filename']) . "</u></a><br>{$Free_Slot}
                        </td>
                    </tr>
                    <tr>
                        <td>{$lang['details_zip']}</td>
                        <td>
                            <a class='index' href='{$site_config['baseurl']}/download.php?torrent={$id}" . ($CURUSER['ssluse'] == 3 ? '&amp;ssl=1' : '') . "'&amp;zip=1'><u>" . htmlsafechars($torrents['filename']) . "</u></a><br>{$Free_Slot_Zip}
                        </td>
                    </tr>
                    <tr>
                        <td>{$lang['details_text']}</td>
                        <td>
                            <a class='index' href='{$site_config['baseurl']}/download.php?torrent={$id}" . ($CURUSER['ssluse'] == 3 ? '&amp;ssl=1' : '') . "'&amp;text=1'><u>" . htmlsafechars($torrents['filename']) . "</u></a><br>{$Free_Slot_Text}
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        <div class='table-wrapper bottom20'>
            <table class='table table-bordered'>
                <tr>
                    <td>{$lang['details_tags']}</td>
                    <td>{$keywords}</td>
                </tr>";

    $my_points = 0;
    $torrent['torrent_points_'] = $cache->get('coin_points_' . $id);
    if ($torrent['torrent_points_'] === false || is_null($torrent['torrent_points_'])) {
        $sql_points = sql_query('SELECT userid, points FROM coins WHERE torrentid=' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
        $torrent['torrent_points_'] = [];
        if (mysqli_num_rows($sql_points) !== 0) {
            while ($points_cache = mysqli_fetch_assoc($sql_points)) {
                $torrent['torrent_points_'][ $points_cache['userid'] ] = $points_cache['points'];
            }
        }
        $cache->add('coin_points_' . $id, $torrent['torrent_points_'], 0);
    }
    $my_points = (isset($torrent['torrent_points_'][ $CURUSER['id'] ]) ? (int)$torrent['torrent_points_'][ $CURUSER['id'] ] : 0);
    $HTMLOUT .= '
                <tr>
                    <td class="rowhead">Karma Points</td>
                    <td><b>In total ' . (int)$torrents['points'] . ' Karma Points given to this torrent of which ' . $my_points . ' from you.<br><br>
                        <a href="coins.php?id=' . $id . '&amp;points=10"><img src="' . $site_config['pic_base_url'] . '10coin.png" alt="10" class="tooltipper" title="10 Points" /></a>
                        <a href="coins.php?id=' . $id . '&amp;points=20"><img src="' . $site_config['pic_base_url'] . '20coin.png" alt="20" class="tooltipper" title="20 Points" /></a>
                        <a href="coins.php?id=' . $id . '&amp;points=50"><img src="' . $site_config['pic_base_url'] . '50coin.png" alt="50" class="tooltipper" title="50 Points" /></a>
                        <a href="coins.php?id=' . $id . '&amp;points=100"><img src="' . $site_config['pic_base_url'] . '100coin.png" alt="100" class="tooltipper" title="100 Points" /></a>
                        <a href="coins.php?id=' . $id . '&amp;points=200"><img src="' . $site_config['pic_base_url'] . '200coin.png" alt="200" class="tooltipper" title="200 Points" /></a>
                        <a href="coins.php?id=' . $id . '&amp;points=500"><img src="' . $site_config['pic_base_url'] . '500coin.png" alt="500" class="tooltipper" title="500 Points" /></a>
                        <a href="coins.php?id=' . $id . '&amp;points=1000"><img src="' . $site_config['pic_base_url'] . '1000coin.png" alt="1000" class="tooltipper" title="1000 Points" /></a></b>
                        <br>By clicking on the coins you can give Karma Points to the uploader of this torrent.
                    </td>
                </tr>';

    $downl = ($CURUSER['downloaded'] + $torrents['size']);
    $sr = $CURUSER['uploaded'] / $downl;
    switch (true) {
        case $sr >= 4:
            $s = 'w00t';
            break;

        case $sr >= 2:
            $s = 'grin';
            break;

        case $sr >= 1:
            $s = 'smile1';
            break;

        case $sr >= 0.5:
            $s = 'noexpression';
            break;

        case $sr >= 0.25:
            $s = 'sad';
            break;

        case $sr > 0.00:
            $s = 'cry';
            break;

        default:
            $s = 'w00t';
            break;
    }
    $sr = floor($sr * 1000) / 1000;
    $sr = "<img src='{$site_config['pic_base_url']}smilies/{$s}.gif' alt='' class='right10' /><span style='color: " . get_ratio_color($sr) . ";'>" . number_format($sr, 3) . "</span>";
    if ($torrents['free'] >= 1 || $torrents['freetorrent'] >= 1 || $isfree['yep'] || $free_slot or $double_slot != 0 || $CURUSER['free_switch'] != 0) {
        $HTMLOUT .= "
                <tr>
                    <td class='rowhead'>Ratio After Download</td>
                    <td>
                        <del>{$sr}Your new ratio if you download this torrent.</del> <b><span style='color: #FF0000;'>[FREE]</span></b>(Only upload stats are recorded)
                    </td>
                </tr>";
    } else {
        $HTMLOUT .= "
                <tr>
                    <td class='rowhead'>Ratio After Download</td>
                    <td>{$sr}Your new ratio if you download this torrent.</td>
                </tr>";
    }
    /**
     * @param $matches
     *
     * @return string
     */
    function hex_esc($matches)
    {
        return sprintf('%02x', ord($matches[0]));
    }

    $HTMLOUT .= tr("{$lang['details_info_hash']}", preg_replace_callback('/./s', 'hex_esc', hash_pad($torrents['info_hash'])));
} else {
    $HTMLOUT .= "
        <div class=''>
            <table class='table table-bordered bottom10'>
                <tr>
                    <td class='rowhead'>Download Disabled!!</td>
                    <td>Your not allowed to download presently !!</td>
                </tr>";
}
$HTMLOUT .= "</table>
        </div>
        <div class='table-wrapper bottom20'>
            <table class='table table-bordered bottom10'>";
if (!empty($torrents['description'])) {
    $HTMLOUT .= tr("{$lang['details_small_descr']}", '<i>' . htmlsafechars($torrents['description']) . '</i>', 1);
} else {
    $HTMLOUT .= '
                <tr>
                    <td>No small description found</td>
                </tr>';
}
$HTMLOUT .= "
            </table>
        </div>";

$searchname = substr($torrents['name'], 0, 6);
$query1 = str_replace(' ', '.', sqlesc('%' . $searchname . '%'));
$query2 = str_replace('.', ' ', sqlesc('%' . $searchname . '%'));
$sim_torrents = $cache->get('similiar_tor_' . $id);
if ($sim_torrents === false || is_null($sim_torrents)) {
    $r = sql_query("SELECT id, name, size, added, seeders, leechers, category FROM torrents WHERE name LIKE {$query1} AND id <> " . sqlesc($id) . " OR name LIKE {$query2} AND id <> " . sqlesc($id) . ' ORDER BY name') or sqlerr(__FILE__, __LINE__);
    while ($sim_torrent = mysqli_fetch_assoc($r)) {
        $sim_torrents[] = $sim_torrent;
    }
    $cache->set('similiar_tor_' . $id, $sim_torrents, 86400);
}
if (count($sim_torrents) > 0) {
    $sim_torrent = "
        <div class='table-wrapper bottom20'>
            <table class='table table-bordered table-striped'>
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Name</th>
                        <th>Size</th>
                        <th>Added</th>
                        <th>Seeders</th>
                        <th>Leechers</th>
                    </tr>
                </thead>
                <tbody>";
    if ($sim_torrents) {
        foreach ($sim_torrents as $a) {
            $sim_tor['cat_name'] = htmlsafechars($change[ $a['category'] ]['name']);
            $sim_tor['cat_pic'] = htmlsafechars($change[ $a['category'] ]['image']);
            $cat = "<img src='{$site_config['pic_base_url']}caticons/" . get_categorie_icons() . "/{$sim_tor['cat_pic']}' alt='{$sim_tor['cat_name']}' class='tooltipper' title='{$sim_tor['cat_name']}' />";
            $name = htmlsafechars(CutName($a['name']));
            $seeders = (int)$a['seeders'];
            $leechers = (int)$a['leechers'];
            $added = get_date($a['added'], 'DATE', 0, 1);
            $sim_torrent .= "
                    <tr>
                        <td>{$cat}</td>
                        <td><a href='details.php?id=" . (int)$a['id'] . "&amp;hit=1'><b>{$name}</b></a></td>
                        <td>" . mksize($a['size']) . "</td>
                        <td>{$added}</td>
                        <td>{$seeders}</td>
                        <td>{$leechers}</td>
                    </tr>";
        }
        $sim_torrent .= '
                </tbody>
            </table>
        </div>';

        $HTMLOUT .= "
        <div class='table-wrapper bottom20'>
            <table class='table table-bordered'>
                <tr class='no_hover'>
                    <td class='rowhead'>
                        <span class='flipper has-text-primary'>
                            <i class='fa fa-angle-down right10' aria-hidden='true'></i>{$lang['details_similiar']}
                        </span>
                        <div class='is_hidden'>$sim_torrent</div>
                    </td>
                </tr>
            </table>
        </div>";
    } else {
        if (empty($sim_torrents)) {
            $HTMLOUT .= "
        <div class='table-wrapper bottom20'>
            <table class='table table-bordered'>
                <tr>
                    <td colspan='2'>Nothing similiar to " . htmlsafechars($torrents['name']) . " found.</td>
                </tr>
            </table>
        </div>";
        }
    }
}
$HTMLOUT .= "
    <div class='table-wrapper bottom20'>
        <table class='table table-bordered'>";

if (in_array($torrents['category'], $site_config['movie_cats']) && !empty($torrents['subs'])) {
    $HTMLOUT .= "
            <tr>
                <td class='rowhead'>Subtitles</td>
                <td>";
    $subs_array = explode(',', $torrents['subs']);
    foreach ($subs_array as $k => $sid) {
        require_once CACHE_DIR . 'subs.php';
        foreach ($subs as $sub) {
            if ($sub['id'] == $sid) {
                $HTMLOUT .= '
                    <img width="25px" src="' . htmlsafechars($sub['pic']) . '" alt="' . htmlsafechars($sub['name']) . '" class="tooltipper" title="' . htmlsafechars($sub['name']) . '" />';
            }
        }
    }
    $HTMLOUT .= "
                </td>
            </tr>";
}

if ($CURUSER['class'] >= UC_POWER_USER && $torrents['nfosz'] > 0) {
    $HTMLOUT .= "
            <tr>
                <td class='rowhead'>{$lang['details_nfo']}</td><td><a href='viewnfo.php?id=" . (int)$torrents['id'] . "'><b>{$lang['details_view_nfo']}</b></a> (" . mksize($torrents['nfosz']) . ")</td>
            </tr>";
}
if ($torrents['visible'] == 'no') {
    $HTMLOUT .= tr("{$lang['details_visible']}", "<b>{$lang['details_no']}</b>{$lang['details_dead']}", 1);
}
if ($moderator) {
    $HTMLOUT .= tr("{$lang['details_banned']}", $torrents['banned']);
}
if ($torrents['nuked'] == 'yes') {
    $HTMLOUT .= "
            <tr>
                <td class='rowhead'><b>Nuked</b></td><td><img src='{$site_config['pic_base_url']}nuked.gif' alt='Nuked' class='tooltipper' title='Nuked' /></td>
            </tr>";
}
if (!empty($torrents['nukereason'])) {
    $HTMLOUT .= "
            <tr>
                <td class='rowhead'><b>Nuke-Reason</b></td><td>" . htmlsafechars($torrents['nukereason']) . "</td>
            </tr>";
}
$torrents['cat_name'] = htmlsafechars($change[ $torrents['category'] ]['name']);
if (isset($torrents['cat_name'])) {
    $HTMLOUT .= tr("{$lang['details_type']}", htmlsafechars($torrents['cat_name']));
} else {
    $HTMLOUT .= tr("{$lang['details_type']}", 'None');
}
$HTMLOUT .= tr('Rating', getRate($id, 'torrent'), 1);
$HTMLOUT .= tr("{$lang['details_last_seeder']}", "{$lang['details_last_activity']}" . get_date($l_a['lastseed'], '', 0, 1));
$HTMLOUT .= tr("{$lang['details_size']}", mksize($torrents['size']) . ' (' . number_format($torrents['size']) . " {$lang['details_bytes']})");
$HTMLOUT .= tr("{$lang['details_added']}", get_date($torrents['added'], "{$lang['details_long']}"));
$HTMLOUT .= tr("{$lang['details_views']}", (int)$torrents['views']);
$HTMLOUT .= tr("{$lang['details_hits']}", (int)$torrents['hits']);
$XBT_Or_Default = (XBT_TRACKER == true ? 'snatches_xbt.php?id=' : 'snatches.php?id=');
$HTMLOUT .= tr("{$lang['details_snatched']}", ($torrents['times_completed'] > 0 ? "<a href='{$XBT_Or_Default}{$id}'>{$torrents['times_completed']} {$lang['details_times']}</a>" : "0 {$lang['details_times']}"), 1);
$HTMLOUT .= "
        </table>
    </div>
    <div class='table-wrapper bottom20'>
        <table class='table table-bordered'>";

$HTMLOUT .= tr('Report Torrent', "<form action='report.php?type=Torrent&amp;id=$id' method='post'><input class='button is-primary is-small bottom10' type='submit' name='submit' value='Report This Torrent' /><strong><em class='label label-primary'>For breaking the <a href='rules.php'>rules</a></em></strong></form>", 1);

if ($torrent_cache['rep']) {
    $torrents = array_merge($torrents, $torrent_cache['rep']);
    $member_reputation = get_reputation($torrents, 'torrents', $torrents['anonymous'], $id);
    $HTMLOUT .= '
            <tr>
                <td class="rowhead">Reputation</td>
                <td>' . $member_reputation . ' (counts towards uploaders Reputation)<br></td>
            </tr>';
}

$rowuser = isset($torrents['owner']) ? format_username($torrents['owner']) : $lang['details_unknown'];
$uprow = (($torrents['anonymous'] == 'yes') ? ($CURUSER['class'] < UC_STAFF && $torrents['owner'] != $CURUSER['id'] ? '' : $rowuser . ' - ') . "<i>{$lang['details_anon']}</i>" : $rowuser);
if ($owned) {
    $uprow .= "<br><$editlink>{$lang['details_edit']}</a>";
}
$HTMLOUT .= tr('Upped by', $uprow, 1);

if ($CURUSER['class'] >= UC_STAFF) {
    if (!empty($torrents['checked_by'])) {
        $checked_by = $cache->get('checked_by_' . $id);
        if ($checked_by === false || is_null($checked_by)) {
            $checked_by = $torrents['checked_by'];
            $cache->set('checked_by_' . $id, $checked_by, 0);
        }
        $HTMLOUT .= "<tr>
                <td class='rowhead'>Checked by</td>
                <td>
                    <div class='bottom10'>" .
                        format_username($torrents['checked_by']) . (isset($torrents['checked_when']) && $torrents['checked_when'] > 0 ? ' checked: ' . get_date($torrents['checked_when'], 'DATE', 0, 1) : '') . "
                    </div>
                    <div class='bottom10'>
                        <form method='post' action='./details.php?id={$torrents['id']}'>
                            <input type='hidden' name='rechecked' value={$torrents['id']}>
                            <input type='submit' class='button is-small is-primary bottom10' value='Re-Check this torrent' />
                        </form>
                        <form method='post' action='./details.php?id={$torrents['id']}'>
                            <input type='hidden' name='clearchecked' value={$torrents['id']}>
                            <input type='submit' class='button is-small is-primary' value='Un-Check this torrent' />
                        </form>
                    </div>
                </td>
            </tr>";
    } else {
        $HTMLOUT .= "
            <tr>
                <td class='rowhead'>Checked by</td>
                <td>
                    <form method='post' action='./details.php?id={$torrents['id']}'>
                        <input type='hidden' name='checked' value={$torrents['id']}>
                        <input type='submit' class='button is-small is-primary' value='Check this torrent' />
                    </form>
                </td>
            </tr>";
    }
}
// end
//==
if ($torrents['type'] == 'multi') {
    if (!isset($_GET['filelist'])) {
        $HTMLOUT .= tr("{$lang['details_num_files']}<br><a href='{$site_config['baseurl']}/filelist.php?id=$id' class='sublink'>{$lang['details_list']}</a>", (int)$torrents['numfiles'] . ' files', 1);
    } else {
        $HTMLOUT .= tr("{$lang['details_num-files']}", (int)$torrents['numfiles'] . "{$lang['details_files']}", 1);
    }
}

if (XBT_TRACKER == true) {
    $HTMLOUT .= tr("{$lang['details_peers']}", (int)$torrents_xbt['seeders'] . ' seeder(s), ' . (int)$torrents_xbt['leechers'] . ' leecher(s) = ' . ((int)$torrents_xbt['seeders'] + (int)$torrents_xbt['leechers']) . "{$lang['details_peer_total']}<br><a href='{$site_config['baseurl']}/peerlist.php?id=$id#seeders' class='button is-small is-primary'>{$lang['details_list']}</a>", 1);
} else {
    $HTMLOUT .= tr("{$lang['details_peers']}", (int)$torrents['seeders'] . ' seeder(s), ' . (int)$torrents['leechers'] . ' leecher(s) = ' . ((int)$torrents['seeders'] + (int)$torrents['leechers']) . "{$lang['details_peer_total']}<br><a href='{$site_config['baseurl']}/peerlist.php?id=$id#seeders' class='button is-small is-primary'>{$lang['details_list']}</a>", 1);
}

$HTMLOUT .= tr($lang['details_thanks'], '
        <script>
            var tid = ' . $id . ';
        </script>
        <noscript>
            <iframe id="thanked" src ="./ajax/thanks.php?torrentid=' . $id . '">
                <p>Your browser does not support iframes. And it has Javascript disabled!</p>
            </iframe>
        </noscript>
        <div id="thanks_holder"></div>', 1);

$next_reseed = 0;
if ($torrents['last_reseed'] > 0) {
    $next_reseed = ($torrents['last_reseed'] + 172800);
}
$reseed = "
        <form method='post' action='./takereseed.php'>
            <select name='pm_what'>
                <option value='last10'>last10</option>
                <option value='owner'>uploader</option>
            </select>
            <input type='hidden' name='uploader' value='" . (int)$torrents['owner'] . "' />
            <input type='hidden' name='reseedid' value='$id' />
            <input type='submit' class='button is-small is-primary left10'" . (($next_reseed > $dt) ? ' disabled' : '') . " value='SendPM' />
        </form>";
$HTMLOUT .= tr('Request reseed', $reseed, 1);

$HTMLOUT .= '
</table>
</div>';
if (!empty($torrents_txt['descr'])) {
    $HTMLOUT .= main_div(format_comment($torrents_txt['descr']), 'has-text-left bottom20');
}
$HTMLOUT .= "
        <div class='table-wrapper bottom20'>
            <table class='table table-bordered'>";
if (!empty($torrents['youtube'])) {
    $HTMLOUT .= tr($lang['details_youtube'], '<object type="application/x-shockwave-flash" data="' . str_replace('watch?v=', 'v/', $torrents['youtube']) . '"><param name="movie" value="' . str_replace('watch?v=', 'v/', $torrents['youtube']) . '" /></object><br><a
href=\'' . htmlsafechars($torrents['youtube']) . '\' target=\'_blank\'>' . $lang['details_youtube_link'] . '</a>', 1);
} else {
    $HTMLOUT .= '
                <tr>
                    <td>No youtube data found</td>
                </tr>';
}
$HTMLOUT .= "
            </table>
        </div>";
$HTMLOUT .= "
        <div class='table-wrapper bottom20'>
            <table class='table table-bordered'>";
$torrents['tvcats'] = [
    5,
]; // change these to match your TV categories
if (in_array($torrents['category'], $torrents['tvcats'])) {
    $tvmaze_info = tvmaze($torrents);
    if ($tvmaze_info) {
        $HTMLOUT .= tr($lang['details_tvrage'], $tvmaze_info, 1);
    }
}

$imdb_html = '';
if (preg_match('/^http\:\/\/(.*?)imdb\.com\/title\/tt([\d]{7})/i', $torrents['url'], $imdb_tmp)) {
    $imdb_id = $imdb_tmp[2];
    unset($imdb_tmp);
    if (!($imdb_html = $cache->get('imdb::' . $imdb_id))) {
        $movie = new imdb($imdb_id);
        $movie->setid($imdb_id);
        $imdb_data['director'] = $movie->director();
        $imdb_data['writing'] = $movie->writing();
        $imdb_data['producer'] = $movie->producer();
        $imdb_data['composer'] = $movie->composer();
        $imdb_data['cast'] = $movie->cast();
        $imdb_data['cast'] = array_slice($imdb_data['cast'], 0, 10);
        $imdb_data['genres'] = $movie->genres();
        $imdb_data['plot'] = $movie->plot();
        $imdb_data['plotoutline'] = $movie->plotoutline();
        $imdb_data['trailers'] = $movie->trailers();
        $imdb_data['language'] = $movie->language();
        $imdb_data['rating'] = $movie->rating();
        $imdb_data['title'] = $movie->title();
        $imdb_data['year'] = $movie->year();
        $imdb_data['runtime'] = $movie->runtime();
        $imdb_data['votes'] = $movie->votes();
        $imdb_data['country'] = $movie->country();
        $imdb = [
            'country'     => 'Country',
            'director'    => 'Directed by',
            'writing'     => 'Writing by',
            'producer'    => 'Produced by',
            'cast'        => 'Cast',
            'plot'        => 'Description',
            'composer'    => 'Music',
            'genres'      => 'All genres',
            'plotoutline' => 'Plot outline',
            'trailers'    => 'Trailers',
            'language'    => 'Language',
            'rating'      => 'Rating',
            'title'       => 'Title',
            'year'        => 'Year',
            'runtime'     => 'Runtime',
            'votes'       => 'Votes',
        ];
        foreach ($imdb as $foo => $boo) {
            if (isset($imdb_data[ $foo ]) && !empty($imdb_data[ $foo ])) {
                if (!is_array($imdb_data[ $foo ])) {
                    $imdb_html .= "<span>" . $boo . ':</span>' . $imdb_data[ $foo ] . "<br>\n";
                } elseif (is_array($imdb_data[ $foo ]) && in_array($foo, [
                        'director',
                        'writing',
                        'producer',
                        'composer',
                        'cast',
                        'trailers',
                    ])) {
                    foreach ($imdb_data[ $foo ] as $pp) {
                        if ($foo == 'cast') {
                            $imdb_tmp[] = "<a href='http://www.imdb.com/name/nm" . $pp['imdb'] . "' target='_blank' class='tooltipper' title='" . (!empty($pp['name']) ? $pp['name'] : 'unknown') . "'>" . (isset($pp['thumb']) ? "<img src='" . $pp['thumb'] . "' alt='" . $pp['name'] . "' width='20' height='30' />" : $pp['name']) . "</a> as <span>" . (!empty($pp['role']) ? $pp['role'] : 'unknown') . '</span>';
                        } elseif ($foo == 'trailers') {
                            $imdb_tmp[] = "<a href='" . $pp . "' target='_blank'>" . $pp . '</a>';
                        } else {
                            $imdb_tmp[] = "<a href='http://www.imdb.com/name/nm" . $pp['imdb'] . "' target='_blank' class='tooltipper' title='" . (!empty($pp['role']) ? $pp['role'] : 'unknown') . "'>" . $pp['name'] . "</a>\n";
                        }
                    }
                    $imdb_html .= "<span>" . $boo . ':</span>' . join(', ', $imdb_tmp) . "<br>\n";
                    unset($imdb_tmp);
                } else {
                    $imdb_html .= "<span>" . $boo . ':</span>' . join(', ', $imdb_data[ $foo ]) . "<br>\n";
                }
            }
        }
        $imdb_html = preg_replace('/&(?![A-Za-z0-9#]{1,7};)/', '&amp;', $imdb_html);
        $cache->add('imdb::' . $imdb_id, $imdb_html, 0);
    }
    $HTMLOUT .= tr('Auto imdb', $imdb_html, 1);
}
if (empty($tvmaze_info) && empty($imdb_html)) {
    $HTMLOUT .= "
                <tr>
                    <td colspan='2'>No Imdb or TVMaze info.</td>
                </tr>";
}
$HTMLOUT .= "
            </table>
        </div>";

$HTMLOUT .= "
    <a name='startcomments'></a>
    <form name='comment' method='post' action='{$site_config['baseurl']}/comment.php?action=add&amp;tid=$id'>
        <div class='bordered top20 bottom20'>
            <div class='alt_bordered bg-00'>
                <div class='has-text-centered'>
                    <div class='size_6'>{$lang['details_comments']}:</div>
                    <h1><a href='{$site_config['baseurl']}/details.php?id=$id'>" . htmlsafechars($torrents['name'], ENT_QUOTES) . "</a></h1>
                </div>
                <div class='bg-02 round10'>
                    <div class='level-center'>
                        <a class='index' href='{$site_config['baseurl']}/comment.php?action=add&amp;tid=$id'><span class='has-text-primary size_6'>Use the BBcode Editor</span></a>
                        <a class='index' href='{$site_config['baseurl']}/takethankyou.php?id=" . $id . "'>
                            <img src='{$site_config['pic_base_url']}smilies/thankyou.gif' class='tooltipper' alt='Thank You' title='Give a quick \"Thank You\"' />
                        </a>
                    </div>
                    <textarea name='body' class='w-100' rows='6'></textarea>
                    <input type='hidden' name='tid' value='" . htmlsafechars($id) . "' />
                    <div class='has-text-centered'>
                        <a href=\"javascript:SmileIT(':-)','comment','body')\"><img src='{$site_config['pic_base_url']}smilies/smile1.gif' alt='Smile' class='tooltipper' title='Smile' /></a>
                        <a href=\"javascript:SmileIT(':smile:','comment','body')\"><img src='{$site_config['pic_base_url']}smilies/smile2.gif' alt='Smiling' class='tooltipper' title='Smiling' /></a>
                        <a href=\"javascript:SmileIT(':-D','comment','body')\"><img src='{$site_config['pic_base_url']}smilies/grin.gif' alt='Grin' class='tooltipper' title='Grin' /></a>
                        <a href=\"javascript:SmileIT(':lol:','comment','body')\"><img src='{$site_config['pic_base_url']}smilies/laugh.gif' alt='Laughing' class='tooltipper' title='Laughing' /></a>
                        <a href=\"javascript:SmileIT(':w00t:','comment','body')\"><img src='{$site_config['pic_base_url']}smilies/w00t.gif' alt='W00t' class='tooltipper' title='W00t' /></a>
                        <a href=\"javascript:SmileIT(':blum:','comment','body')\"><img src='{$site_config['pic_base_url']}smilies/blum.gif' alt='Rasp' class='tooltipper' title='Rasp' /></a>
                        <a href=\"javascript:SmileIT(';-)','comment','body')\"><img src='{$site_config['pic_base_url']}smilies/wink.gif' alt='Wink' class='tooltipper' title='Wink' /></a>
                        <a href=\"javascript:SmileIT(':devil:','comment','body')\"><img src='{$site_config['pic_base_url']}smilies/devil.gif' alt='Devil' class='tooltipper' title='Devil' /></a>
                        <a href=\"javascript:SmileIT(':yawn:','comment','body')\"><img src='{$site_config['pic_base_url']}smilies/yawn.gif' alt='Yawn' class='tooltipper' title='Yawn' /></a>
                        <a href=\"javascript:SmileIT(':-/','comment','body')\"><img src='{$site_config['pic_base_url']}smilies/confused.gif' alt='Confused' class='tooltipper' title='Confused' /></a>
                        <a href=\"javascript:SmileIT(':o)','comment','body')\"><img src='{$site_config['pic_base_url']}smilies/clown.gif' alt='Clown' class='tooltipper' title='Clown' /></a>
                        <a href=\"javascript:SmileIT(':innocent:','comment','body')\"><img src='{$site_config['pic_base_url']}smilies/innocent.gif' alt='Innocent' class='tooltipper' title='innocent' /></a>
                        <a href=\"javascript:SmileIT(':whistle:','comment','body')\"><img src='{$site_config['pic_base_url']}smilies/whistle.gif' alt='Whistle' class='tooltipper' title='Whistle' /></a>
                        <a href=\"javascript:SmileIT(':unsure:','comment','body')\"><img src='{$site_config['pic_base_url']}smilies/unsure.gif' alt='Unsure' class='tooltipper' title='Unsure' /></a>
                        <a href=\"javascript:SmileIT(':blush:','comment','body')\"><img src='{$site_config['pic_base_url']}smilies/blush.gif' alt='Blush' class='tooltipper' title='Blush' /></a>
                        <a href=\"javascript:SmileIT(':hmm:','comment','body')\"><img src='{$site_config['pic_base_url']}smilies/hmm.gif' alt='Hmm' class='tooltipper' title='Hmm' /></a>
                        <a href=\"javascript:SmileIT(':hmmm:','comment','body')\"><img src='{$site_config['pic_base_url']}smilies/hmmm.gif' alt='Hmmm' class='tooltipper' title='Hmmm' /></a>
                        <a href=\"javascript:SmileIT(':huh:','comment','body')\"><img src='{$site_config['pic_base_url']}smilies/huh.gif' alt='Huh' class='tooltipper' title='Huh' /></a>
                        <a href=\"javascript:SmileIT(':look:','comment','body')\"><img src='{$site_config['pic_base_url']}smilies/look.gif' alt='Look' class='tooltipper' title='Look' /></a>
                        <a href=\"javascript:SmileIT(':rolleyes:','comment','body')\"><img src='{$site_config['pic_base_url']}smilies/rolleyes.gif' alt='Roll Eyes' class='tooltipper' title='Roll Eyes' /></a>
                        <a href=\"javascript:SmileIT(':kiss:','comment','body')\"><img src='{$site_config['pic_base_url']}smilies/kiss.gif' alt='Kiss' class='tooltipper' title='Kiss' /></a>
                        <a href=\"javascript:SmileIT(':blink:','comment','body')\"><img src='{$site_config['pic_base_url']}smilies/blink.gif' alt='Blink' class='tooltipper' title='Blink' /></a>
                        <a href=\"javascript:SmileIT(':baby:','comment','body')\"><img src='{$site_config['pic_base_url']}smilies/baby.gif' alt='Baby' class='tooltipper' title='Baby' /></a>
                    </div>
                    <div class='has-text-centered'>
                        <input class='button is-small is-primary margin20' type='submit' value='Submit' />
                    </div>
                </div>
            </div>
        </div>
    </form>";

if ($torrents['allow_comments'] == 'yes' || $CURUSER['class'] >= UC_STAFF && $CURUSER['class'] <= UC_MAX) {
    $HTMLOUT .= "
            <p><a name='startcomments'></a></p>";
} else {
    $HTMLOUT .= "
        <div class='table-wrapper bottom20'>
            <table class='table table-bordered'>
                <tr>
                    <td><a name='startcomments'> </a><b>{$lang['details_com_disabled']}</b></td>
                </tr>
            </table>
        </div>";
    echo stdhead("{$lang['details_details']}'" . htmlsafechars($torrents['name'], ENT_QUOTES) . '"', true, $stdhead) . $HTMLOUT . stdfoot($stdfoot);
    die();
}
$count = (int)$torrents['comments'];
if (!$count) {
    $HTMLOUT .= "
            <h2 class='has-text-centered'>{$lang['details_no_comment']}</h2>";
} else {
    $perpage = 15;
    $pager = pager($perpage, $count, "details.php?id=$id&amp;", [
        'lastpagedefault' => 1,
    ]);
    $subres = sql_query('SELECT c.id, c.text, c.user_likes, c.user, c.torrent, c.added, c.anonymous, c.editedby, c.editedat, u.avatar, u.av_w, u.av_h, u.offavatar, u.warned, u.reputation, u.opt1, u.opt2, u.mood, u.username, u.title, u.class, u.donor
                            FROM comments AS c
                            LEFT JOIN users AS u ON c.user = u.id
                            WHERE torrent = ' . sqlesc($id) . ' ORDER BY c.id ' . $pager['limit']) or sqlerr(__FILE__, __LINE__);
    $allrows = [];
    while ($subrow = mysqli_fetch_assoc($subres)) {
        $allrows[] = $subrow;
    }
    $HTMLOUT .= "
                <div class='container is-fluid portlet'>
                    <a id='comments-hash'></a>
                    <fieldset id='comments' class='header'>
                        <legend class='flipper has-text-primary'><i class='fa fa-angle-up right10' aria-hidden='true'></i>Comments</legend>
                        <div>";

    if (count($allrows) > $perpage) {
        $HTMLOUT .= $pager['pagertop'];
    }
    $HTMLOUT .= commenttable($allrows);
    if (count($allrows) > $perpage) {
        $HTMLOUT .= $pager['pagerbottom'];
    }
    $HTMLOUT .= "
                        </div>
                    </fieldset>
                </div>";
}

echo stdhead("{$lang['details_details']}'" . htmlsafechars($torrents['name'], ENT_QUOTES) . '"', true, $stdhead) . wrapper($HTMLOUT) . stdfoot($stdfoot);
