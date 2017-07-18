<?php
/**
 |--------------------------------------------------------------------------|
 |   https://github.com/Bigjoos/                			    |
 |--------------------------------------------------------------------------|
 |   Licence Info: GPL							    |
 |--------------------------------------------------------------------------|
 |   Copyright (C) 2010 U-232 V4					    |
 |--------------------------------------------------------------------------|
 |   A bittorrent tracker source based on TBDev.net/tbsource/bytemonsoon.   |
 |--------------------------------------------------------------------------|
 |   Project Leaders: Mindless,putyn.					    |
 |--------------------------------------------------------------------------|
   _   _   _   _   _     _   _   _   _   _   _     _   _   _   _
  / \ / \ / \ / \ / \   / \ / \ / \ / \ / \ / \   / \ / \ / \ / \
 ( U | - | 2 | 3 | 2 )-( S | o | u | r | c | e )-( C | o | d | e )
  \_/ \_/ \_/ \_/ \_/   \_/ \_/ \_/ \_/ \_/ \_/   \_/ \_/ \_/ \_/
 */
require_once (dirname(__FILE__) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php');
require_once (INCL_DIR . 'user_functions.php');
require_once (INCL_DIR . 'bbcode_functions.php');
require_once (INCL_DIR . 'pager_functions.php');
require_once (INCL_DIR . 'comment_functions.php');
require_once (INCL_DIR . 'html_functions.php');
require_once (INCL_DIR . 'function_rating.php');
require_once (INCL_DIR . 'tvmaze_functions.php');
require_once (IMDB_DIR . 'imdb.class.php');
dbconn(false);
loggedinorreturn();
$lang = array_merge(load_language('global') , load_language('details'));
parked();
$stdhead = array(
    /** include css **/
    'css' => array(
        'bbcode',
        'rating_style',
        'details'
    )
);
$stdfoot = array(
    /** include js **/
    'js' => array(
        'popup',
        'jquery.thanks',
        'wz_tooltip',
        'java_klappe',
        'balloontip',
        'shout',
        'thumbs',
        'sack'
    )
);
$HTMLOUT = $torrent_cache = '';
if (!isset($_GET['id']) || !is_valid_id($_GET['id'])) stderr("{$lang['details_user_error']}", "{$lang['details_bad_id']}");
$id = (int)$_GET["id"];
//==pdq memcache slots
$slot = make_freeslots($CURUSER['id'], 'fllslot_');
$torrent['addedfree'] = $torrent['addedup'] = $free_slot = $double_slot = '';
if (!empty($slot)) foreach ($slot as $sl) {
    if ($sl['torrentid'] == $id && $sl['free'] == 'yes') {
        $free_slot = 1;
        $torrent['addedfree'] = $sl['addedfree'];
    }
    if ($sl['torrentid'] == $id && $sl['doubleup'] == 'yes') {
        $double_slot = 1;
        $torrent['addedup'] = $sl['addedup'];
    }
    if ($free_slot && $double_slot) break;
}
$categorie = genrelist();
foreach ($categorie as $key => $value) $change[$value['id']] = array(
    'id' => $value['id'],
    'name' => $value['name'],
    'image' => $value['image']
);

if (($torrents = $mc1->get_value('torrent_details_' . $id)) === false) {
    $tor_fields_ar_int = array(
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
        'checked_when'
    );
    $tor_fields_ar_str = array(
        'banned',
        'info_hash',
        'checked_by',
        'filename',
        'search_text',
        'name',
        'save_as',
        'visible',
        'type',
        'poster',
        'url',
        'anonymous',
        'allow_comments',
        'description',
        'nuked',
        'nukereason',
        'vip',
        'subs',
        'username',
        'newgenre',
        'release_group',
        'youtube',
        'tags'
    );
    $tor_fields = implode(', ', array_merge($tor_fields_ar_int, $tor_fields_ar_str));
    $result = sql_query("SELECT " . $tor_fields . ", LENGTH(nfo) AS nfosz, IF(num_ratings < {$INSTALLER09['minvotes']}, NULL, ROUND(rating_sum / num_ratings, 1)) AS rating FROM torrents WHERE id = " . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
    $torrents = mysqli_fetch_assoc($result);
    foreach ($tor_fields_ar_int as $i) $torrents[$i] = (int)$torrents[$i];
    foreach ($tor_fields_ar_str as $i) $torrents[$i] = $torrents[$i];
    $mc1->cache_value('torrent_details_' . $id, $torrents, $INSTALLER09['expires']['torrent_details']);
}
//==
if (($torrents_xbt = $mc1->get_value('torrent_xbt_data_' . $id)) === false && XBT_TRACKER == true) {
    $torrents_xbt = mysqli_fetch_assoc(sql_query("SELECT seeders, leechers, times_completed FROM torrents WHERE id =" . sqlesc($id))) or sqlerr(__FILE__, __LINE__);
    $mc1->cache_value('torrent_xbt_data_' . $id, $torrents_xbt, $INSTALLER09['expires']['torrent_xbt_data']);
}
//==
if (($torrents_txt = $mc1->get_value('torrent_details_txt' . $id)) === false) {
    $torrents_txt = mysqli_fetch_assoc(sql_query("SELECT descr FROM torrents WHERE id =" . sqlesc($id))) or sqlerr(__FILE__, __LINE__);
    $mc1->cache_value('torrent_details_txt' . $id, $torrents_txt, $INSTALLER09['expires']['torrent_details_text']);
}
//==
if (isset($_GET["hit"])) {
    sql_query("UPDATE torrents SET views = views + 1 WHERE id =" . sqlesc($id));
    $update['views'] = ($torrents['views'] + 1);
    $mc1->begin_transaction('torrent_details_' . $id);
    $mc1->update_row(false, array(
        'views' => $update['views']
    ));
    $mc1->commit_transaction($INSTALLER09['expires']['torrent_details']);
    header("Location: {$INSTALLER09['baseurl']}/details.php?id=$id");
    exit();
}
$What_String = (XBT_TRACKER == true ? 'mtime' : 'last_action');
$What_String_Key = (XBT_TRACKER == true ? 'last_action_xbt_' : 'last_action_');
if (($l_a = $mc1->get_value($What_String_Key.$id)) === false) {
    $l_a = mysqli_fetch_assoc(sql_query('SELECT '.$What_String.' AS lastseed ' . 'FROM torrents ' . 'WHERE id = ' . sqlesc($id))) or sqlerr(__FILE__, __LINE__);
    $l_a['lastseed'] = (int)$l_a['lastseed'];
    $mc1->add_value('last_action_' . $id, $l_a, 1800);
}
/** seeders/leechers/completed caches pdq**/
$torrent_cache['seeders'] = $mc1->get_value('torrents::seeds:::' . $id);
$torrent_cache['leechers'] = $mc1->get_value('torrents::leechs:::' . $id);
$torrent_cache['times_completed'] = $mc1->get_value('torrents::comps:::' . $id);
$torrents['seeders'] = ((XBT_TRACKER === false || $torrent_cache['seeders'] === false || $torrent_cache['seeders'] === 0 || $torrent_cache['seeders'] === false) ? $torrents['seeders'] : $torrent_cache['seeders']);
$torrents['leechers'] = ((XBT_TRACKER === false || $torrent_cache['leechers'] === false || $torrent_cache['leechers'] === 0 || $torrent_cache['leechers'] === false) ? $torrents['leechers'] : $torrent_cache['leechers']);
$torrents['times_completed'] = ((XBT_TRACKER === false || $torrent_cache['times_completed'] === false || $torrent_cache['times_completed'] === 0 || $torrent_cache['times_completed'] === false) ? $torrents['times_completed'] : $torrent_cache['times_completed']);
//==slots by pdq
$torrent['addup'] = get_date($torrent['addedup'], 'DATE');
$torrent['addfree'] = get_date($torrent['addedfree'], 'DATE');
$torrent['idk'] = (TIME_NOW + 14 * 86400);
$torrent['freeimg'] = '<img src="' . $INSTALLER09['pic_base_url'] . 'freedownload.gif" alt="" />';
$torrent['doubleimg'] = '<img src="' . $INSTALLER09['pic_base_url'] . 'doubleseed.gif" alt="" />';
$torrent['free_color'] = '#FF0000';
$torrent['silver_color'] = 'silver';
//==rep user query by pdq
if (($torrent_cache['rep'] = $mc1->get_value('user_rep_' . $torrents['owner'])) === false) {
    $torrent_cache['rep'] = array();
    $us = sql_query("SELECT reputation FROM users WHERE id =" . sqlesc($torrents['owner'])) or sqlerr(__FILE__, __LINE__);
    if (mysqli_num_rows($us)) {
        $torrent_cache['rep'] = mysqli_fetch_assoc($us);
        $mc1->add_value('user_rep_' . $torrents['owner'], $torrent_cache['rep'], 14 * 86400);
    }
}
$HTMLOUT.= "<script type='text/javascript'>
    /*<![CDATA[*/
	var e = new sack();
function do_rate(rate,id,what) {
		var box = document.getElementById('rate_'+id);
		e.setVar('rate',rate);
		e.setVar('id',id);
		e.setVar('ajax','1');
		e.setVar('what',what);
		e.requestFile = 'rating.php';
		e.method = 'GET';
		e.element = 'rate_'+id;
		e.onloading = function () {
			box.innerHTML = 'Loading ...'
		}
		e.onCompletion = function() {
			if(e.responseStatus)
				box.innerHTML = e.response();
		}
		e.onerror = function () {
			alert('That was something wrong with the request!');
		}
		e.runAJAX();
}
/*]]>*/
</script>";
$owned = $moderator = 0;
if ($CURUSER["class"] >= UC_STAFF) $owned = $moderator = 1;
elseif ($CURUSER["id"] == $torrents["owner"]) $owned = 1;
if ($torrents["vip"] == "1" && $CURUSER["class"] < UC_VIP) stderr("VIP Access Required", "You must be a VIP In order to view details or download this torrent! You may become a Vip By Donating to our site. Donating ensures we stay online to provide you more Vip-Only Torrents!");
if (!$torrents || ($torrents["banned"] == "yes" && !$moderator)) stderr("{$lang['details_error']}", "{$lang['details_torrent_id']}");
if ($CURUSER["id"] == $torrents["owner"] || $CURUSER["class"] >= UC_STAFF) $owned = 1;
else $owned = 0;
$spacer = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
if (empty($torrents["tags"])) {
    $keywords = "No Keywords Specified.";
} else {
    $tags = explode(",", $torrents['tags']);
    $keywords = "";
    foreach ($tags as $tag) {
        $keywords.= "<a href='browse.php?search=$tag&amp;searchin=all&amp;incldead=1'>" . htmlsafechars($tag) . "</a>,";
    }
    $keywords = substr($keywords, 0, (strlen($keywords) - 1));
}
if (isset($_GET["uploaded"])) {
    $HTMLOUT.= "<div class='alert alert-success span11' align='center'><h2>{$lang['details_success']}</h2></div>\n";
    $HTMLOUT.= "<p>{$lang['details_start_seeding']}</p>\n";
    $HTMLOUT.= '<meta http-equiv="refresh" content="1;url=download.php?torrent=' . $id . '' . ($CURUSER['ssluse'] == 3 ? "&amp;ssl=1" : "") . '" />';
} elseif (isset($_GET["edited"])) {
    $HTMLOUT.= "<div class='alert alert-success span11' align='center'><h2>{$lang['details_success_edit']}</h2></div>\n";
    if (isset($_GET["returnto"])) $HTMLOUT.= "<p><b>{$lang['details_go_back']}<a href='" . htmlsafechars($_GET["returnto"]) . "'>{$lang['details_whence']}</a>.</b></p>\n";
} elseif (isset($_GET["reseed"])) {
    $HTMLOUT.= "<div class='alert alert-success span11' align='center'><h2>PM was sent! Now wait for a seeder !</h2></div>\n";
}
//==pdq's Torrent Moderation
if ($CURUSER['class'] >= UC_STAFF) {
    if (isset($_GET["checked"]) && $_GET["checked"] == 1) {
        sql_query("UPDATE torrents SET checked_by = " . sqlesc($CURUSER['username']) . ", checked_when = ".TIME_NOW." WHERE id =" . sqlesc($id) . " LIMIT 1") or sqlerr(__FILE__, __LINE__);
        $mc1->begin_transaction('torrent_details_' . $id);
        $mc1->update_row(false, array(
            'checked_by' => $CURUSER['username'],
			'checked_when' => TIME_NOW
        ));
        $mc1->commit_transaction($INSTALLER09['expires']['torrent_details']);
        $mc1->delete_value('checked_by_' . $id);
        write_log("Torrent <a href={$INSTALLER09['baseurl']}/details.php?id=$id>(" . htmlsafechars($torrents['name']) . ")</a> was checked by {$CURUSER['username']}");
        header("Location: {$INSTALLER09["baseurl"]}/details.php?id=$id&checked=done#Success");
    } elseif (isset($_GET["rechecked"]) && $_GET["rechecked"] == 1) {
        sql_query("UPDATE torrents SET checked_by = " . sqlesc($CURUSER['username']) . ", checked_when = ".TIME_NOW." WHERE id =" . sqlesc($id) . " LIMIT 1") or sqlerr(__FILE__, __LINE__);
        $mc1->begin_transaction('torrent_details_' . $id);
        $mc1->update_row(false, array(
            'checked_by' => $CURUSER['username'],
			'checked_when' => TIME_NOW
        ));
        $mc1->commit_transaction($INSTALLER09['expires']['torrent_details']);
        $mc1->delete_value('checked_by_' . $id);
        write_log("Torrent <a href={$INSTALLER09['baseurl']}/details.php?id=$id>(" . htmlsafechars($torrents['name']) . ")</a> was re-checked by {$CURUSER['username']}");
        header("Location: {$INSTALLER09["baseurl"]}/details.php?id=$id&rechecked=done#Success");
    } elseif (isset($_GET["clearchecked"]) && $_GET["clearchecked"] == 1) {
        sql_query("UPDATE torrents SET checked_by = '', checked_when='' WHERE id =" . sqlesc($id) . " LIMIT 1") or sqlerr(__FILE__, __LINE__);
        $mc1->begin_transaction('torrent_details_' . $id);
        $mc1->update_row(false, array(
            'checked_by' => '',
            'checked_when' => ''
        ));
        $mc1->commit_transaction($INSTALLER09['expires']['torrent_details']);
        $mc1->delete_value('checked_by_' . $id);
        write_log("Torrent <a href={$INSTALLER09["baseurl"]}/details.php?id=$id>(" . htmlsafechars($torrents['name']) . ")</a> was un-checked by {$CURUSER['username']}");
        header("Location: {$INSTALLER09["baseurl"]}/details.php?id=$id&clearchecked=done#Success");
    }
    if (isset($_GET["checked"]) && $_GET["checked"] == 'done') $HTMLOUT.= "<div class='alert alert-success span11' align='center'><h2><a name='Success'>Successfully checked {$CURUSER['username']}!</a></h2></div>";
    if (isset($_GET["rechecked"]) && $_GET["rechecked"] == 'done') $HTMLOUT.= "<div class='alert alert-success span11' align='center'><h2><a name='Success'>Successfully re-checked {$CURUSER['username']}!</a></h2></div>";
    if (isset($_GET["clearchecked"]) && $_GET["clearchecked"] == 'done') $HTMLOUT.= "<div class='alert alert-success span11' align='center'><h2><a name='Success'>Successfully un-checked {$CURUSER['username']}!</a></h2></div>";
}
// end
$s = htmlsafechars($torrents["name"], ENT_QUOTES);
$HTMLOUT.= "<div class='container' ><div class='pull-left'><h1>$s</h1>\n";
$HTMLOUT.= "<h2><a href='random.php'>" . (!isset($_GET['random']) ? '[Random Any]' : '<span style="color:#3366FF;">[Random Any]</span>') . "</a></h2>";
//Thumbs Up
if (($thumbs = $mc1->get_value('thumbs_up_' . $id)) === false) {
    $thumbs = mysqli_num_rows(sql_query("SELECT id, type, torrentid, userid FROM thumbsup WHERE torrentid = " . sqlesc($torrents['id'])));
    $thumbs = (int)$thumbs;
    $mc1->add_value('thumbs_up_' . $id, $thumbs, 0);
}
$HTMLOUT.= "</div>
			<div class='pull-right'>
		{$lang['details_thumbs']}
			<div id='thumbsup'>
			<a href=\"javascript:ThumbsUp('" . (int)$torrents['id'] . "')\">
			<img src='{$INSTALLER09['pic_base_url']}thumb_up.png' alt='Thumbs Up' title='Thumbs Up' width='12' height='12' /></a>&nbsp;&nbsp;&nbsp;(" . $thumbs . ")
			</div>
	   </div>
		</div>\n";
//==

/** free mod pdq **/
$HTMLOUT.= '
        <div id="balloon1" class="balloonstyle">
			Once chosen this torrent will be Freeleech ' . $torrent['freeimg'] . ' until ' . get_date($torrent['idk'], 'DATE') . ' and can be resumed or started over using the
			regular download link. Doing so will result in one Freeleech Slot being taken away from your total.
		</div>
        <div id="balloon2" class="balloonstyle">
			Once chosen this torrent will be Doubleseed ' . $torrent['doubleimg'] . ' until ' . get_date($torrent['idk'], 'DATE') . ' and can be resumed or started over using the
			regular download link. Doing so will result in one Freeleech Slot being taken away from your total.
		</div>
		<div id="balloon3" class="balloonstyle">
			Remember to show your gratitude and Thank the Uploader. <img src="' . $INSTALLER09['pic_base_url'] . 'smilies/smile1.gif" alt="" />
		</div>';
/** end **/
$HTMLOUT.= "<hr /><div>";
$HTMLOUT.= "<!-- <ul class='nav nav-pills'>
    <li><a href='#Download'>Download</a></li>
    <li><a href='#Poster'>Poster</a></li>
    <li><a href='#imdb'>Imdb</a></li>
    <li><a href='#info'>Info</a></li>
    <li><a href='#comments'>Comments</a></li>
    </ul> -->";
$HTMLOUT.= "<div>";
$HTMLOUT.= "<div class='container-fluid'>";
$url = "edit.php?id=" . (int)$torrents["id"];
if (isset($_GET["returnto"])) {
    $addthis = "&amp;returnto=" . urlencode($_GET["returnto"]);
    $url.= $addthis;
    $keepget = $addthis;
}
$editlink = "a href=\"$url\" class=\"sublink\"";
if (!($CURUSER["downloadpos"] == 0 && $CURUSER["id"] != $torrents["owner"] OR $CURUSER["downloadpos"] > 1)) {
    /** free mod by pdq **/
    //== Display the freeslots links etc.
    if ($free_slot && !$double_slot) {
        $HTMLOUT.= '<tr>
				<td align="right" class="heading">Slots</td>
				<td align="left">' . $torrent['freeimg'] . ' <b><font color="' . $torrent['free_color'] . '">Freeleech Slot In Use!</font></b> (only upload stats are recorded) - Expires: 12:01AM ' . $torrent['addfree'] . '
				</td>
			</tr>';
        $freeslot = ($CURUSER['freeslots'] >= 1 ? "&nbsp;&nbsp;<b>Use: </b><a class=\"index\" href=\"download.php?torrent={$id}" . ($CURUSER['ssluse'] == 3 ? "&amp;ssl=1" : "") . "&amp;slot=double\" rel='balloon2' onclick=\"return confirm('Are you sure you want to use a doubleseed slot?')\"><font color='" . $torrent['free_color'] . "'><b>Doubleseed Slot</b></font></a>&nbsp;- " . htmlsafechars($CURUSER['freeslots']) . " Slots Remaining. " : "");
        $freeslot_zip = ($CURUSER['freeslots'] >= 1 ? "&nbsp;&nbsp;<b>Use: </b><a class=\"index\" href=\"download.php?torrent={$id}" . ($CURUSER['ssluse'] == 3 ? "&amp;ssl=1" : "") . "&amp;slot=double&amp;zip=1\" rel='balloon2' onclick=\"return confirm('Are you sure you want to use a doubleseed slot?')\"><font color='" . $torrent['free_color'] . "'><b>Doubleseed Slot</b></font></a>&nbsp;- " . htmlsafechars($CURUSER['freeslots']) . " Slots Remaining. " : "");
        $freeslot_text = ($CURUSER['freeslots'] >= 1 ? "&nbsp;&nbsp;<b>Use: </b><a class=\"index\" href=\"download.php?torrent={$id}" . ($CURUSER['ssluse'] == 3 ? "&amp;ssl=1" : "") . "&amp;slot=double&amp;text=1\" rel='balloon2' onclick=\"return confirm('Are you sure you want to use a doubleseed slot?')\"><font color='" . $torrent['free_color'] . "'><b>Doubleseed Slot</b></font></a>&nbsp;- " . htmlsafechars($CURUSER['freeslots']) . " Slots Remaining. " : "");
    } elseif (!$free_slot && $double_slot) {
        $HTMLOUT.= '<tr>
				<td align="right" class="heading">Slots</td>
				<td align="left">' . $torrent['doubleimg'] . ' <b><font color="' . $torrent['free_color'] . '">Doubleseed Slot In Use!</font></b> (upload stats x2) - Expires: 12:01AM ' . $torrent['addup'] . '
				</td>
			</tr>';
        $freeslot = ($CURUSER['freeslots'] >= 1 ? "&nbsp;&nbsp;<b>Use: </b><a class=\"index\" href=\"download.php?torrent={$id}" . ($CURUSER['ssluse'] == 3 ? "&amp;ssl=1" : "") . "&amp;slot=free\" rel='balloon1' onclick=\"return confirm('Are you sure you want to use a freeleech slot?')\"><font color='" . $torrent['free_color'] . "'><b>Freeleech Slot</b></font></a>&nbsp;- " . htmlsafechars($CURUSER['freeslots']) . " Slots Remaining. " : "");
        $freeslot_zip = ($CURUSER['freeslots'] >= 1 ? "&nbsp;&nbsp;<b>Use: </b><a class=\"index\" href=\"download.php?torrent={$id}" . ($CURUSER['ssluse'] == 3 ? "&amp;ssl=1" : "") . "&amp;slot=free&amp;zip=1\" rel='balloon1' onclick=\"return confirm('Are you sure you want to use a freeleech slot?')\"><font color='" . $torrent['free_color'] . "'><b>Freeleech Slot</b></font></a>&nbsp;- " . htmlsafechars($CURUSER['freeslots']) . " Slots Remaining. " : "");
        $freeslot_text = ($CURUSER['freeslots'] >= 1 ? "&nbsp;&nbsp;<b>Use: </b><a class=\"index\" href=\"download.php?torrent={$id}" . ($CURUSER['ssluse'] == 3 ? "&amp;ssl=1" : "") . "&amp;slot=free&amp;text=1\" rel='balloon1' onclick=\"return confirm('Are you sure you want to use a freeleech slot?')\"><font color='" . $torrent['free_color'] . "'><b>Freeleech Slot</b></font></a>&nbsp;- " . htmlsafechars($CURUSER['freeslots']) . " Slots Remaining. " : "");
    } elseif ($free_slot && $double_slot) {
        $HTMLOUT.= '<tr>
				<td align="right" class="heading">Slots</td>
				<td align="left">' . $torrent['freeimg'] . ' ' . $torrent['doubleimg'] . ' <b><font color="' . $torrent['free_color'] . '">Freeleech and Doubleseed Slots In Use!</font></b> (upload stats x2 and no download stats are recorded)<p>Freeleech Expires: 12:01AM ' . $torrent['addfree'] . ' and Doubleseed Expires: 12:01AM ' . $torrent['addup'] . '</p>
				</td>
			</tr>';
        $freeslot = $freeslot_zip = $freeslot_text = '';
    } else $freeslot = ($CURUSER['freeslots'] >= 1 ? "&nbsp;&nbsp;<b>Use: </b><a class=\"index\" href=\"download.php?torrent={$id}" . ($CURUSER['ssluse'] == 3 ? "&amp;ssl=1" : "") . "&amp;slot=free\" rel='balloon1' onclick=\"return confirm('Are you sure you want to use a freeleech slot?')\"><font color='" . $torrent['free_color'] . "'><b>Freeleech Slot</b></font></a> &nbsp;&nbsp;<b>Use: </b><a class=\"index\" href=\"download.php?torrent={$id}" . ($CURUSER['ssluse'] == 3 ? "&amp;ssl=1" : "") . "&amp;slot=double\" rel='balloon2' onclick=\"return confirm('Are you sure you want to use a doubleseed slot?')\"><font color='" . $torrent['free_color'] . "'><b>Doubleseed Slot</b></font></a>&nbsp;- " . htmlsafechars($CURUSER['freeslots']) . " Slots Remaining. " : "");
    $freeslot_zip = ($CURUSER['freeslots'] >= 1 ? "&nbsp;&nbsp;<b>Use: </b><a class=\"index\" href=\"download.php?torrent={$id}" . ($CURUSER['ssluse'] == 3 ? "&amp;ssl=1" : "") . "&amp;slot=free&amp;zip=1\" rel='balloon1' onclick=\"return confirm('Are you sure you want to use a freeleech slot?')\"><font color='" . $torrent['free_color'] . "'><b>Freeleech Slot</b></font></a> &nbsp;&nbsp;<b>Use: </b><a class=\"index\" href=\"download.php?torrent={$id}" . ($CURUSER['ssluse'] == 3 ? "&amp;ssl=1" : "") . "&amp;slot=double&amp;zip=1\" rel='balloon2' onclick=\"return confirm('Are you sure you want to use a doubleseed slot?')\"><font color='" . $torrent['free_color'] . "'><b>Doubleseed Slot</b></font></a>&nbsp;- " . htmlsafechars($CURUSER['freeslots']) . " Slots Remaining. " : "");
    $freeslot_text = ($CURUSER['freeslots'] >= 1 ? "&nbsp;&nbsp;<b>Use: </b><a class=\"index\" href=\"download.php?torrent={$id}" . ($CURUSER['ssluse'] == 3 ? "&amp;ssl=1" : "") . "&amp;slot=free&amp;text=1\" rel='balloon1' onclick=\"return confirm('Are you sure you want to use a freeleech slot?')\"><font color='" . $torrent['free_color'] . "'><b>Freeleech Slot</b></font></a> &nbsp;&nbsp;<b>Use: </b><a class=\"index\" href=\"download.php?torrent={$id}" . ($CURUSER['ssluse'] == 3 ? "&amp;ssl=1" : "") . "&amp;slot=double&amp;text=1\" rel='balloon2' onclick=\"return confirm('Are you sure you want to use a doubleseed slot?')\"><font color='" . $torrent['free_color'] . "'><b>Doubleseed Slot</b></font></a>&nbsp;- " . htmlsafechars($CURUSER['freeslots']) . " Slots Remaining. " : "");
    //==
    require_once MODS_DIR . 'free_details.php';
    $HTMLOUT.= "
	<div class='row-fluid'>
	<div class='pull-left img-polaroid span5'>";
    //==09 Poster mod
    if (!empty($torrents["poster"])) $HTMLOUT.= "<img src='" . htmlsafechars($torrents["poster"]) . "' alt='Poster' title='Poster' style='width:435px; height:450px;' />";
    if (empty($torrents["poster"])) $HTMLOUT.= "No Poster Found.";
    $Free_Slot = (XBT_TRACKER == true ? '' : $freeslot);
    $Free_Slot_Zip = (XBT_TRACKER == true ? '' : $freeslot_zip);
    $Free_Slot_Text = (XBT_TRACKER == true ? '' : $freeslot_text);
    $HTMLOUT.= "</div>
	<div class=' span7'>
	<table class='table table-bordered'>
			<tr>
				<td align=\"right\" class=\"heading\" width=\"3%\">{$lang['details_download']}</td>
				<td align=\"left\">
				<a class=\"index\" href=\"download.php?torrent={$id}" . ($CURUSER['ssluse'] == 3 ? "&amp;ssl=1" : "") . "\">&nbsp;<u>" . htmlsafechars($torrents["filename"]) . "</u></a>{$Free_Slot}
				</td>
			</tr>";
    /** end **/
    //==Torrent as zip by putyn
    $HTMLOUT.= "<tr>
				<td>{$lang['details_zip']}</td>
				<td align=\"left\">
				<a class=\"index\" href=\"download.php?torrent={$id}" . ($CURUSER['ssluse'] == 3 ? "&amp;ssl=1" : "") . "&amp;zip=1\">&nbsp;<u>" . htmlsafechars($torrents["filename"]) . "</u></a>{$Free_Slot_Zip}
				</td>
			</tr>";
    //==Torrent as text by putyn
    $HTMLOUT.= "<tr>
				<td>{$lang['details_text']}</td>
				<td align=\"left\">
				<a class=\"index\" href=\"download.php?torrent={$id}" . ($CURUSER['ssluse'] == 3 ? "&amp;ssl=1" : "") . "&amp;text=1\">&nbsp;<u>" . htmlsafechars($torrents["filename"]) . "</u></a>{$Free_Slot_Text}
				</td>
			</tr>
	           </table>";
    $HTMLOUT.= "
	<table class='table  table-bordered'>\n
			<tr>
				<td>{$lang['details_tags']}</td>
				<td align=\"left\">" . $keywords . "</td>
			</tr>";
    /**  Mod by dokty, rewrote by pdq  **/
    $my_points = 0;
    if (($torrent['torrent_points_'] = $mc1->get('coin_points_' . $id)) === false) {
        $sql_points = sql_query('SELECT userid, points FROM coins WHERE torrentid=' . sqlesc($id));
        $torrent['torrent_points_'] = array();
        if (mysqli_num_rows($sql_points) !== 0) {
            while ($points_cache = mysqli_fetch_assoc($sql_points)) $torrent['torrent_points_'][$points_cache['userid']] = $points_cache['points'];
        }
        $mc1->add('coin_points_' . $id, $torrent['torrent_points_'], 0);
    }
    $my_points = (isset($torrent['torrent_points_'][$CURUSER['id']]) ? (int)$torrent['torrent_points_'][$CURUSER['id']] : 0);
    $HTMLOUT.= '<tr>
		<td class="heading" valign="top" align="right">Karma Points</td>
		<td valign="top" align="left"><b>In total ' . (int)$torrents['points'] . ' Karma Points given to this torrent of which ' . $my_points . ' from you.<br /><br />
		<a href="coins.php?id=' . $id . '&amp;points=10"><img src="' . $INSTALLER09['pic_base_url'] . '10coin.png" alt="10" title="10 Points" /></a>&nbsp;&nbsp;
		<a href="coins.php?id=' . $id . '&amp;points=20"><img src="' . $INSTALLER09['pic_base_url'] . '20coin.png" alt="20" title="20 Points" /></a>&nbsp;&nbsp;
		<a href="coins.php?id=' . $id . '&amp;points=50"><img src="' . $INSTALLER09['pic_base_url'] . '50coin.png" alt="50" title="50 Points" /></a>&nbsp;&nbsp;
		<a href="coins.php?id=' . $id . '&amp;points=100"><img src="' . $INSTALLER09['pic_base_url'] . '100coin.png" alt="100" title="100 Points" /></a>&nbsp;&nbsp;
		<a href="coins.php?id=' . $id . '&amp;points=200"><img src="' . $INSTALLER09['pic_base_url'] . '200coin.png" alt="200" title="200 Points" /></a>&nbsp;&nbsp;
		<a href="coins.php?id=' . $id . '&amp;points=500"><img src="' . $INSTALLER09['pic_base_url'] . '500coin.png" alt="500" title="500 Points" /></a>&nbsp;&nbsp;
		<a href="coins.php?id=' . $id . '&amp;points=1000"><img src="' . $INSTALLER09['pic_base_url'] . '1000coin.png" alt="1000" title="1000 Points" /></a></b>&nbsp;&nbsp;
		<br />By clicking on the coins you can give Karma Points to the uploader of this torrent.</td></tr>';
    /** END **/
    /** pdq's ratio afer d/load **/
    $downl = ($CURUSER["downloaded"] + $torrents["size"]);
    $sr = $CURUSER["uploaded"] / $downl;
    switch (true) {
    case ($sr >= 4):
        $s = "w00t";
        break;

    case ($sr >= 2):
        $s = "grin";
        break;

    case ($sr >= 1):
        $s = "smile1";
        break;

    case ($sr >= 0.5):
        $s = "noexpression";
        break;

    case ($sr >= 0.25):
        $s = "sad";
        break;

    case ($sr > 0.00):
        $s = "cry";
        break;

    default;
    $s = "w00t";
    break;
}
$sr = floor($sr * 1000) / 1000;
$sr = "<font color='" . get_ratio_color($sr) . "'>" . number_format($sr, 3) . "</font>&nbsp;&nbsp;<img src=\"pic/smilies/{$s}.gif\" alt=\"\" />";
if ($torrents['free'] >= 1 || $torrents['freetorrent'] >= 1 || $isfree['yep'] || $free_slot OR $double_slot != 0 || $CURUSER['free_switch'] != 0) {
    $HTMLOUT.= "<tr>
				<td align='right' class='heading'>Ratio After Download</td>
				<td><del>{$sr}&nbsp;&nbsp;Your new ratio if you download this torrent.</del> <b><font size='' color='#FF0000'>[FREE]</font></b>&nbsp;(Only upload stats are recorded)
				</td>
			</tr>";
} else {
    $HTMLOUT.= "<tr>
				<td align='right' class='heading'>Ratio After Download</td>
				<td>{$sr}&nbsp;&nbsp;Your new ratio if you download this torrent.</td>
			</tr>";
}
//==End
function hex_esc($matches) {
	return sprintf("%02x", ord($matches[0]));
}
$HTMLOUT .= tr("{$lang['details_info_hash']}", preg_replace_callback('/./s', "hex_esc", hash_pad($torrents["info_hash"])));
} else {
    $HTMLOUT.="<div><div class='container-fluid'><table class='table  table-bordered'><tr><td align='right' class='heading'>Download Disabled!!</td><td>Your not allowed to download presently !!</td></tr>";
}     
$HTMLOUT.= "</table>";
$HTMLOUT.= "<table class='table  table-bordered'>\n";
if (!empty($torrents["description"])) {
$HTMLOUT.= tr("{$lang['details_small_descr']}", "<i>" . htmlsafechars($torrents['description']) . "</i>", 1);
} else {
$HTMLOUT.= "<tr><td>No small description found</td></tr>";
}
$HTMLOUT.= "</table>\n";
//== Similar Torrents mod
$searchname = substr($torrents['name'], 0, 6);
$query1 = str_replace(" ", ".", sqlesc("%" . $searchname . "%"));
$query2 = str_replace(".", " ", sqlesc("%" . $searchname . "%"));
if (($sim_torrents = $mc1->get_value('similiar_tor_' . $id)) === false) {
    $r = sql_query("SELECT id, name, size, added, seeders, leechers, category FROM torrents WHERE name LIKE {$query1} AND id <> " . sqlesc($id) . " OR name LIKE {$query2} AND id <> " . sqlesc($id) . " ORDER BY name") or sqlerr(__FILE__, __LINE__);
    while ($sim_torrent = mysqli_fetch_assoc($r)) $sim_torrents[] = $sim_torrent;
    $mc1->cache_value('similiar_tor_' . $id, $sim_torrents, 86400);
}
if (count($sim_torrents) > 0) {
    $sim_torrent = "<table class='table  table-bordered'>\n" . "
		<thead>
			<tr>
				<th>Type</th>
				<th>Name</th>
				<th>Size</th>
				<th>Added</th>
				<th>Seeders</th>
				<th>Leechers</th>
			</tr>
		</thead>\n";
    if ($sim_torrents) {
        foreach ($sim_torrents as $a) {
            $sim_tor['cat_name'] = htmlsafechars($change[$a['category']]['name']);
            $sim_tor['cat_pic'] = htmlsafechars($change[$a['category']]['image']);
            $cat = "<img src=\"pic/caticons/{$CURUSER['categorie_icon']}/{$sim_tor['cat_pic']}\" alt=\"{$sim_tor['cat_name']}\" title=\"{$sim_tor['cat_name']}\" />";
            $name = htmlsafechars(CutName($a["name"]));
            $seeders = (int)$a["seeders"];
            $leechers = (int)$a["leechers"];
            $added = get_date($a["added"], 'DATE', 0, 1);
            $sim_torrent.= "<tr>
				<td class='one' style='padding: 0px; border: none' width='40px'>{$cat}</td>
				<td class='one'><a href='details.php?id=" . (int)$a["id"] . "&amp;hit=1'><b>{$name}</b></a></td>
				<td class='one' style='padding: 1px' align='center'>" . mksize($a['size']) . "</td>
				<td class='one' style='padding: 1px' align='center'>{$added}</td>
				<td class='one' style='padding: 1px' align='center'>{$seeders}</td>
				<td class='one' style='padding: 1px' align='center'>{$leechers}</td>
			</tr>\n";
        }
        $sim_torrent.= "
	</table>";
        $HTMLOUT.= "<table class='table  table-bordered'><tr><td align='right' class='heading'>{$lang['details_similiar']}<a href=\"javascript: klappe_news('a5')\"><img border=\"0\" src=\"pic/plus.png\" id=\"pica5".(int)$a['id']."\" alt=\"[Hide/Show]\" title=\"[Hide/Show]\" /></a><div id=\"ka5\" style=\"display: none;\"><br />$sim_torrent</div></td></tr></table></div></div>";
    } else {
        if (empty($sim_torrents)) $HTMLOUT.= "
		<table class='table  table-bordered'>\n
				<tr>
				<td colspan='2'>Nothing similiar to " . htmlsafechars($torrents["name"]) . " found.</td>
				</tr>	
		</table>
		</div>
		</div>";
    }
}
$HTMLOUT.= "
<div class='row-fluid'>
<table align='center' class='table table-bordered span3'>\n";
//==subs by putyn
if (in_array($torrents["category"], $INSTALLER09['movie_cats']) && !empty($torrents["subs"])) {
    $HTMLOUT.= "<tr>
				<td class='rowhead'>Subtitles</td>
				<td align='left'>";
    $subs_array = explode(",", $torrents["subs"]);
    foreach ($subs_array as $k => $sid) {
        require_once (CACHE_DIR . 'subs.php');
        foreach ($subs as $sub) {
            if ($sub["id"] == $sid) $HTMLOUT.= "<img border=\"0\" width=\"25px\" style=\"padding:3px;\"src=\"" . htmlsafechars($sub["pic"]) . "\" alt=\"" . htmlsafechars($sub["name"]) . "\" title=\"" . htmlsafechars($sub["name"]) . "\" />";
        }
    }
    $HTMLOUT.= "</td></tr>\n";
}
//
if ($CURUSER["class"] >= UC_POWER_USER && $torrents["nfosz"] > 0) $HTMLOUT.= "<tr><td class='rowhead'>{$lang['details_nfo']}</td><td align='left'><a href='viewnfo.php?id=" . (int)$torrents['id'] . "'><b>{$lang['details_view_nfo']}</b></a> (" . mksize($torrents["nfosz"]) . ")</td></tr>\n";
if ($torrents["visible"] == "no") $HTMLOUT.= tr("{$lang['details_visible']}", "<b>{$lang['details_no']}</b>{$lang['details_dead']}", 1);
if ($moderator) $HTMLOUT.= tr("{$lang['details_banned']}", $torrents["banned"]);
if ($torrents["nuked"] == "yes") $HTMLOUT.= "<tr><td class='rowhead'><b>Nuked</b></td><td align='left'><img src='{$INSTALLER09['pic_base_url']}nuked.gif' alt='Nuked' title='Nuked' /></td></tr>\n";
if (!empty($torrents["nukereason"])) $HTMLOUT.= "<tr><td class='rowhead'><b>Nuke-Reason</b></td><td align='left'>" . htmlsafechars($torrents["nukereason"]) . "</td></tr>\n";
$torrents['cat_name'] = htmlsafechars($change[$torrents['category']]['name']);
if (isset($torrents["cat_name"])) $HTMLOUT.= tr("{$lang['details_type']}", htmlsafechars($torrents["cat_name"]));
else $HTMLOUT.= tr("{$lang['details_type']}", "None");
$HTMLOUT.= tr("Rating", getRate($id, "torrent") , 1);
$HTMLOUT.= tr("{$lang['details_last_seeder']}", "{$lang['details_last_activity']}" . get_date($l_a['lastseed'], '', 0, 1));
$HTMLOUT.= tr("{$lang['details_size']}", mksize($torrents["size"]) . " (" . number_format($torrents["size"]) . " {$lang['details_bytes']})");
$HTMLOUT.= tr("{$lang['details_added']}", get_date($torrents['added'], "{$lang['details_long']}"));
$HTMLOUT.= tr("{$lang['details_views']}", (int)$torrents["views"]);
$HTMLOUT.= tr("{$lang['details_hits']}", (int)$torrents["hits"]);
$XBT_Or_Default = (XBT_TRACKER == true ? 'snatches_xbt.php?id=' : 'snatches.php?id=');
$HTMLOUT.= tr("{$lang['details_snatched']}", ($torrents["times_completed"] > 0 ? "<a href='{$INSTALLER09["baseurl"]}/{$XBT_Or_Default}{$id}'>{$torrents['times_completed']} {$lang['details_times']}</a>" : "0 {$lang['details_times']}") , 1);
$HTMLOUT.= "<tr><td class='rowhead'>Status update</td><td><input type='button' onclick='status_showbox(\"{$CURUSER['username']} is viewing details for torrent {$INSTALLER09['baseurl']}/details.php?id=" . (int)$torrents['id'] . "\")' value='do it!'/></td></tr>";
$HTMLOUT.= "</table>
<table align='center' class='table table-bordered span9'>";
//==Report Torrent Link
$HTMLOUT.= tr("Report Torrent", "<form action='report.php?type=Torrent&amp;id=$id' method='post'><input class='btn btn-primary' type='submit' name='submit' value='Report This Torrent' />&nbsp;&nbsp;<strong><em class='label label-primary'>For breaking the&nbsp;<a href='rules.php'>rules</a></em></strong></form>", 1);
//== Tor Reputation by pdq
if ($torrent_cache['rep']) {
    $torrents = array_merge($torrents, $torrent_cache['rep']);
    $member_reputation = get_reputation($torrents, 'torrents', $torrents['anonymous'], $id);
    $HTMLOUT.= '<tr>
		        <td class="heading" valign="top" align="right" width="1%">Reputation</td>
			<td align="left" width="99%">' . $member_reputation . ' (counts towards uploaders Reputation)<br /></td>
		</tr>';
}
//==Anonymous
$rowuser = (isset($torrents['username']) ? ("<a href='userdetails.php?id=" . (int)$torrents['owner'] . "'><b>" . htmlsafechars($torrents['username']) . "</b></a>") : "{$lang['details_unknown']}");
$uprow = (($torrents['anonymous'] == 'yes') ? ($CURUSER['class'] < UC_STAFF && $torrents['owner'] != $CURUSER['id'] ? '' : $rowuser . ' - ') . "<i>{$lang['details_anon']}</i>" : $rowuser);
if ($owned) $uprow.= " $spacer<$editlink><b>{$lang['details_edit']}</b></a>";
$HTMLOUT.= tr("Upped by", $uprow, 1);
//==pdq's Torrent Moderation
if ($CURUSER['class'] >= UC_STAFF) {
    if (!empty($torrents['checked_by'])) {
        if (($checked_by = $mc1->get_value('checked_by_' . $id)) === false) {
            $checked_by = mysqli_fetch_assoc(sql_query("SELECT id FROM users WHERE username=" . sqlesc($torrents['checked_by']))) or sqlerr(__FILE__, __LINE__);
            $mc1->add_value('checked_by_' . $id, $checked_by, 30 * 86400);
        }
        $HTMLOUT.= "<tr>
				<td class='rowhead'>Checked by</td>
				<td align='left'><a class='label label-primary' href='{$INSTALLER09["baseurl"]}/userdetails.php?id=" . (int)$checked_by['id'] . "'>
				<strong>" . htmlsafechars($torrents['checked_by']) . "</strong></a> 
				<a href='{$INSTALLER09["baseurl"]}/details.php?id=" . (int)$torrents['id'] . "&amp;rechecked=1'>
				<small><em class='label label-primary'><strong>[Re-Check this torrent]</strong></em></small></a> 
				<a href='{$INSTALLER09["baseurl"]}/details.php?id=" . (int)$torrents['id'] . "&amp;clearchecked=1'>
				<small><em class='label label-primary'><strong>[Un-Check this torrent]</strong></em></small></a>
				&nbsp;<em class='alert alert-info'>* STAFF Eyes Only *</em>
				".(isset($torrents["checked_when"]) && $torrents["checked_when"] > 0 ? "<strong>Checked When : ".get_date($torrents["checked_when"],'DATE',0,1)."</strong>":'' )."
				</td>
			</tr>";
    } else {
        $HTMLOUT.= "<tr><td class='rowhead'>Checked by</td><td align='left'><em class='alert alert-error'><strong>NOT CHECKED!</strong></em> 
       <a href='{$INSTALLER09["baseurl"]}/details.php?id=" . (int)$torrents['id'] . "&amp;checked=1'>
       <em class='alert alert-warning'><small><strong>[Check this torrent]</strong></small></em></a>&nbsp;<em class='alert alert-info'><strong>* STAFF Eyes Only *</strong></em></td></tr>";
    }
}
// end
//==
if ($torrents["type"] == "multi") {
    if (!isset($_GET["filelist"])) $HTMLOUT.= tr("{$lang['details_num_files']}<br /><a href=\"./filelist.php?id=$id\" class=\"sublink\">{$lang['details_list']}</a>", (int)$torrents["numfiles"] . " files", 1);
    else {
        $HTMLOUT.= tr("{$lang['details_num-files']}", (int)$torrents["numfiles"] . "{$lang['details_files']}", 1);
    }
}

if(XBT_TRACKER == true) {
$HTMLOUT.= tr("{$lang['details_peers']}<br /><a href=\"./peerlist_xbt.php?id=$id#seeders\" class=\"sublink\">{$lang['details_list']}</a>", (int)$torrents_xbt["seeders"] . " seeder(s), " . (int)$torrents_xbt["leechers"] . " leecher(s) = " . ((int)$torrents_xbt["seeders"] + (int)$torrents_xbt["leechers"]) . "{$lang['details_peer_total']}", 1);
} else {
$HTMLOUT.= tr("{$lang['details_peers']}<br /><a href=\"./peerlist.php?id=$id#seeders\" class=\"sublink\">{$lang['details_list']}</a>", (int)$torrents["seeders"] . " seeder(s), " . (int)$torrents["leechers"] . " leecher(s) = " . ((int)$torrents["seeders"] + (int)$torrents["leechers"]) . "{$lang['details_peer_total']}", 1);
}

//==putyns thanks mod
$HTMLOUT.= tr($lang['details_thanks'], '
	  <script type="text/javascript">
		/*<![CDATA[*/
		$(document).ready(function() {
			var tid = ' . $id . ';
			show_thanks(tid);
		});
		/*]]>*/
		</script>
		<noscript><iframe id="thanked" src ="thanks.php?torrentid=' . $id . '" style="width:500px;height:50px;border:none;overflow:auto;">
	  <p>Your browser does not support iframes. And it has Javascript disabled!</p>
	  </iframe></noscript>
	  <div id="thanks_holder"></div>', 1);
//==End
//==09 Reseed by putyn
$next_reseed = 0;
if ($torrents["last_reseed"] > 0) $next_reseed = ($torrents["last_reseed"] + 172800); //add 2 days
$reseed = "<form method=\"post\" action=\"./takereseed.php\">
	  <select name=\"pm_what\">
	  <option value=\"last10\">last10</option>
	  <option value=\"owner\">uploader</option>
	  </select>
	  <input type=\"submit\"  " . (($next_reseed > TIME_NOW) ? "disabled='disabled'" : "") . " value=\"SendPM\" />
	  <input type=\"hidden\" name=\"uploader\" value=\"" . (int)$torrents["owner"] . "\" />
	  <input type=\"hidden\" name=\"reseedid\" value=\"$id\" />
	  </form>";
$HTMLOUT.= tr("Request reseed", $reseed, 1);
//==End
$HTMLOUT.= "
</table>
</div>
";
/////////////////////////////////////////////////////////
if (!empty($torrents_txt["descr"])) $HTMLOUT.= "
	<table class='table  table-bordered'>
			<tr>
				<td style='vertical-align:top'><b>{$lang['details_description']}</b></td>
				<td>
					<div style='background-color:transparent;width:100%;height:150px;overflow: auto'>" . str_replace(array(
    "\n",
    "\r",
    "  "
) , array(
    "<br />",
    "<br />",
    "&nbsp; "
) , format_comment($torrents_txt["descr"])) . "
					</div>
				</td>
			</tr>
	</table>
";
$HTMLOUT.= "<table class='table  table-bordered'>\n";
if (!empty($torrents['youtube'])) {
$HTMLOUT.= tr($lang['details_youtube'], '<object type="application/x-shockwave-flash" style="width:560px; height:340px;" data="' . str_replace('watch?v=', 'v/', $torrents['youtube']) . '"><param name="movie" value="' . str_replace('watch?v=', 'v/', $torrents['youtube']) . '" /></object><br /><a 
href=\'' . htmlsafechars($torrents['youtube']) . '\' target=\'_blank\'>' . $lang['details_youtube_link'] . '</a>', 1);
} else {
$HTMLOUT.= "<tr><td>No youtube data found</td></tr>";
}
$HTMLOUT.= "</table>
<div>\n";
$HTMLOUT.= "
<table align='center' class='table table-bordered'>\n";
//== tvmaze by whocares converted from former tvrage functions by pdq/putyn
$torrents['tvcats'] = array(
    5
); // change these to match your TV categories
if (in_array($torrents['category'], $torrents['tvcats'])) {
    $tvmaze_info = tvmaze($torrents);
    if ($tvmaze_info) $HTMLOUT.= tr($lang['details_tvrage'], $tvmaze_info, 1);
}
//==auto imdb rewritten putyn 28/06/2011
$imdb_html = "";
if (preg_match('/^http\:\/\/(.*?)imdb\.com\/title\/tt([\d]{7})/i', $torrents['url'], $imdb_tmp)) {
    $imdb_id = $imdb_tmp[2];
    unset($imdb_tmp);
    if (!($imdb_html = $mc1->get_value('imdb::' . $imdb_id))) {
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
        $imdb = array(
            'country' => 'Country',
            'director' => 'Directed by',
            'writing' => 'Writing by',
            'producer' => 'Produced by',
            'cast' => 'Cast',
            'plot' => 'Description',
            'composer' => 'Music',
            'genres' => 'All genres',
            'plotoutline' => 'Plot outline',
            'trailers' => 'Trailers',
            'language' => 'Language',
            'rating' => 'Rating',
            'title' => 'Title',
            'year' => 'Year',
            'runtime' => 'Runtime',
            'votes' => 'Votes'
        );
        foreach ($imdb as $foo => $boo) {
            if (isset($imdb_data[$foo]) && !empty($imdb_data[$foo])) {
                if (!is_array($imdb_data[$foo])) $imdb_html.= "<span style='font-weight:bold;color:#800517'>" . $boo . ":</span>&nbsp;" . $imdb_data[$foo] . "<br />\n";
                elseif (is_array($imdb_data[$foo]) && in_array($foo, array(
                    'director',
                    'writing',
                    'producer',
                    'composer',
                    'cast',
                    'trailers'
                ))) {
                    foreach ($imdb_data[$foo] as $pp) {
                        if ($foo == 'cast') {
                            $imdb_tmp[] = "<a href='http://www.imdb.com/name/nm" . $pp['imdb'] . "' target='_blank' title='" . (!empty($pp['name']) ? $pp['name'] : 'unknown') . "'>" . (isset($pp['thumb']) ? "<img src='" . $pp['thumb'] . "' alt='" . $pp['name'] . "' border='0' width='20' height='30' />" : $pp['name']) . "</a> as <span style='font-weight:bold'>" . (!empty($pp['role']) ? $pp['role'] : 'unknown') . "</span>";
                        } elseif ($foo == 'trailers') $imdb_tmp[] = "<a href='" . $pp . "' target='_blank'>" . $pp . "</a>";
                        else $imdb_tmp[] = "<a href='http://www.imdb.com/name/nm" . $pp['imdb'] . "' target='_blank' title='" . (!empty($pp['role']) ? $pp['role'] : 'unknown') . "'>" . $pp['name'] . "</a>\n";
                    }
                    $imdb_html.= "<span style='font-weight:bold;color:#800517'>" . $boo . ":</span>&nbsp;" . join(', ', $imdb_tmp) . "<br />\n";
                    unset($imdb_tmp);
                } else $imdb_html.= "<span style='font-weight:bold;color:#800517'>" . $boo . ":</span>&nbsp;" . join(', ', $imdb_data[$foo]) . "<br />\n";
            }
        }
        $imdb_html = preg_replace('/&(?![A-Za-z0-9#]{1,7};)/', '&amp;', $imdb_html);
        $mc1->add_value('imdb::' . $imdb_id, $imdb_html, 0);
    }
    $HTMLOUT.= tr('Auto imdb', $imdb_html, 1);
}
if (empty($tvmaze_info) && empty($imdb_html)) $HTMLOUT.= "<tr><td colspan='2'>No Imdb or TVMaze info.</td></tr>";
$HTMLOUT.= "</table></div><div align='center'>";
$HTMLOUT.= "<h1>{$lang['details_comments']}<a href='details.php?id=$id'>" . htmlsafechars($torrents["name"], ENT_QUOTES) . "</a></h1>\n";
//==
$HTMLOUT.= "<p>
    <a name='startcomments'></a></p>
    <form name='comment' method='post' action='comment.php?action=add&amp;tid=$id'>
		<table align='center'>
				<tr>
					<td align='center'><b>{$lang['details_quick_comment']}</b></td>
				</tr>
				<tr>
					<td align='center'>
					<textarea name='body' cols='280' rows='4'></textarea>
					<input type='hidden' name='tid' value='" . htmlsafechars($id) . "' />
					<br />
					<a href=\"javascript:SmileIT(':-)','comment','body')\"><img border='0' src='{$INSTALLER09['pic_base_url']}smilies/smile1.gif' alt='Smile' title='Smile' /></a> 
					<a href=\"javascript:SmileIT(':smile:','comment','body')\"><img border='0' src='{$INSTALLER09['pic_base_url']}smilies/smile2.gif' alt='Smiling' title='Smiling' /></a> 
					<a href=\"javascript:SmileIT(':-D','comment','body')\"><img border='0' src='{$INSTALLER09['pic_base_url']}smilies/grin.gif' alt='Grin' title='Grin' /></a> 
					<a href=\"javascript:SmileIT(':lol:','comment','body')\"><img border='0' src='{$INSTALLER09['pic_base_url']}smilies/laugh.gif' alt='Laughing' title='Laughing' /></a> 
					<a href=\"javascript:SmileIT(':w00t:','comment','body')\"><img border='0' src='{$INSTALLER09['pic_base_url']}smilies/w00t.gif' alt='W00t' title='W00t' /></a> 
					<a href=\"javascript:SmileIT(':blum:','comment','body')\"><img border='0' src='{$INSTALLER09['pic_base_url']}smilies/blum.gif' alt='Rasp' title='Rasp' /></a> 
					<a href=\"javascript:SmileIT(';-)','comment','body')\"><img border='0' src='{$INSTALLER09['pic_base_url']}smilies/wink.gif' alt='Wink' title='Wink' /></a> 
					<a href=\"javascript:SmileIT(':devil:','comment','body')\"><img border='0' src='{$INSTALLER09['pic_base_url']}smilies/devil.gif' alt='Devil' title='Devil' /></a> 
					<a href=\"javascript:SmileIT(':yawn:','comment','body')\"><img border='0' src='{$INSTALLER09['pic_base_url']}smilies/yawn.gif' alt='Yawn' title='Yawn' /></a> 
					<a href=\"javascript:SmileIT(':-/','comment','body')\"><img border='0' src='{$INSTALLER09['pic_base_url']}smilies/confused.gif' alt='Confused' title='Confused' /></a> 
					<a href=\"javascript:SmileIT(':o)','comment','body')\"><img border='0' src='{$INSTALLER09['pic_base_url']}smilies/clown.gif' alt='Clown' title='Clown' /></a> 
					<a href=\"javascript:SmileIT(':innocent:','comment','body')\"><img border='0' src='{$INSTALLER09['pic_base_url']}smilies/innocent.gif' alt='Innocent' title='innocent' /></a> 
					<a href=\"javascript:SmileIT(':whistle:','comment','body')\"><img border='0' src='{$INSTALLER09['pic_base_url']}smilies/whistle.gif' alt='Whistle' title='Whistle' /></a> 
					<a href=\"javascript:SmileIT(':unsure:','comment','body')\"><img border='0' src='{$INSTALLER09['pic_base_url']}smilies/unsure.gif' alt='Unsure' title='Unsure' /></a> 
					<a href=\"javascript:SmileIT(':blush:','comment','body')\"><img border='0' src='{$INSTALLER09['pic_base_url']}smilies/blush.gif' alt='Blush' title='Blush' /></a> 
					<a href=\"javascript:SmileIT(':hmm:','comment','body')\"><img border='0' src='{$INSTALLER09['pic_base_url']}smilies/hmm.gif' alt='Hmm' title='Hmm' /></a> 
					<a href=\"javascript:SmileIT(':hmmm:','comment','body')\"><img border='0' src='{$INSTALLER09['pic_base_url']}smilies/hmmm.gif' alt='Hmmm' title='Hmmm' /></a> 
					<a href=\"javascript:SmileIT(':huh:','comment','body')\"><img border='0' src='{$INSTALLER09['pic_base_url']}smilies/huh.gif' alt='Huh' title='Huh' /></a> 
					<a href=\"javascript:SmileIT(':look:','comment','body')\"><img border='0' src='{$INSTALLER09['pic_base_url']}smilies/look.gif' alt='Look' title='Look' /></a> 
					<a href=\"javascript:SmileIT(':rolleyes:','comment','body')\"><img border='0' src='{$INSTALLER09['pic_base_url']}smilies/rolleyes.gif' alt='Roll Eyes' title='Roll Eyes' /></a> 
					<a href=\"javascript:SmileIT(':kiss:','comment','body')\"><img border='0' src='{$INSTALLER09['pic_base_url']}smilies/kiss.gif' alt='Kiss' title='Kiss' /></a> 
					<a href=\"javascript:SmileIT(':blink:','comment','body')\"><img border='0' src='{$INSTALLER09['pic_base_url']}smilies/blink.gif' alt='Blink' title='Blink' /></a> 
					<a href=\"javascript:SmileIT(':baby:','comment','body')\"><img border='0' src='{$INSTALLER09['pic_base_url']}smilies/baby.gif' alt='Baby' title='Baby' /></a>
					<br />
					<input class='btn btn-primary' type='submit' value='Submit' />
					</td>
				</tr>
		</table>
	</form>";
if ($torrents["allow_comments"] == "yes" || $CURUSER['class'] >= UC_STAFF && $CURUSER['class'] <= UC_MAX) {
    $HTMLOUT.= "<p><a name=\"startcomments\"></a></p>\n";
} else {
    $HTMLOUT.= "<table align='center' class='table table-bordered'>
			<tr>
				<td><a name='startcomments'>&nbsp;</a><b>{$lang['details_com_disabled']}</b></td>
			</tr>
    </table>\n";
    echo stdhead("{$lang['details_details']}\"" . htmlsafechars($torrents["name"], ENT_QUOTES) . "\"", true, $stdhead) . $HTMLOUT . stdfoot($stdfoot);
    die();
}
$HTMLOUT.= "<!-- accordion collapse going here -->
<script type='text/javascript'>
/*<![CDATA[*/
jQuery(document).ready(function() {
  jQuery('.content').hide();
  //toggle the componenet with class msg_body
  jQuery('.h1').click(function()
  {
    jQuery(this).next('.content').slideToggle(500);
  });
});
/*]]>*/
</script>";
$commentbar = "<p class='h1 btn btn-primary'>Comments Open/Close</p><div class='content'><p align='center' ><a class='index' href='comment.php?action=add&amp;tid=$id'>{$lang['details_add_comment']}</a>
    <br /><a class='index' href='{$INSTALLER09['baseurl']}/takethankyou.php?id=" . $id . "'>
    <img src='{$INSTALLER09['pic_base_url']}smilies/thankyou.gif' alt='Thanks' title='Thank You' border='0' /></a></p>\n";
$count = (int)$torrents['comments'];
if (!$count) {
    $HTMLOUT.= "<h2>{$lang['details_no_comment']}</h2>\n";
} else {
    $perpage = 15;
    $pager = pager($perpage, $count, "details.php?id=$id&amp;", array(
        'lastpagedefault' => 1
    ));
    $subres = sql_query("SELECT comments.id, comments.text, comments.user_likes, comments.user, comments.torrent, comments.added, comments.anonymous, comments.editedby, comments.editedat, users.avatar, users.av_w, users.av_h, users.offavatar, users.warned, users.reputation, users.opt1, users.opt2, users.mood, users.username, users.title, users.class, users.donor FROM comments LEFT JOIN users ON comments.user = users.id WHERE torrent = " . sqlesc($id) . " ORDER BY comments.id " . $pager['limit']) or sqlerr(__FILE__, __LINE__);
    $allrows = array();
    while ($subrow = mysqli_fetch_assoc($subres)) $allrows[] = $subrow;
    $HTMLOUT.= $commentbar;
    $HTMLOUT.= $pager['pagertop'];
    $HTMLOUT.= commenttable($allrows);
    $HTMLOUT.= $pager['pagerbottom'];
}
$HTMLOUT.= $commentbar;
$HTMLOUT.= "</div>
</div>
</div>
</div>
</div>";
///////////////////////// HTML OUTPUT ////////////////////////////
echo stdhead("{$lang['details_details']}\"" . htmlsafechars($torrents["name"], ENT_QUOTES) . "\"", true, $stdhead) . $HTMLOUT . stdfoot($stdfoot);
?>
