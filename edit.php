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
require_once INCL_DIR . 'html_functions.php';
require_once INCL_DIR . 'bbcode_functions.php';
require_once CLASS_DIR . 'page_verify.php';
global $CURUSER;
if (!mkglobal("id")) die();
$id = 0 + $id;
if (!$id) die();
/** who is modding by pdq **/
if ((isset($_GET['unedit']) && $_GET['unedit'] == 1) && $CURUSER['class'] >= UC_STAFF) {
    $mc1->delete_value('editedby_' . $id);
    $returl = "details.php?id=$id";
    if (isset($_POST["returnto"])) $returl.= "&returnto=" . urlencode($_POST["returnto"]);
    header("Refresh: 1; url=$returl");
    exit();
}
dbconn();
loggedinorreturn();
$lang = array_merge(load_language('global') , load_language('edit'));
$stdfoot = array(
    /** include js **/
    'js' => array(
        'shout',
        'FormManager'
    )
);
$stdhead = array(
    /** include css **/
    'css' => array(
        'forums',
        'style',
        'style2',
        'bbcode'
    )
);
$newpage = new page_verify();
$newpage->create('teit');
$res = sql_query("SELECT * FROM torrents WHERE id = " . sqlesc($id));
$row = mysqli_fetch_assoc($res);
if (!$row) stderr($lang['edit_user_error'], $lang['edit_no_torrent']);
if (!isset($CURUSER) || ($CURUSER["id"] != $row["owner"] && $CURUSER["class"] < UC_STAFF)) {
    stderr($lang['edit_user_error'], sprintf($lang['edit_no_permission'], urlencode($_SERVER['REQUEST_URI'])));
}
$HTMLOUT = $mod_cache_name = '';
$HTMLOUT = "<script type='text/javascript'>
    window.onload = function() {
    setupDependencies('edit'); //name of form(s). Seperate each with a comma (ie: 'weboptions', 'myotherform' )
    };
    </script>";
if ($CURUSER['class'] >= UC_STAFF) {
    if (($mod_cache_name = $mc1->get_value('editedby_' . $id)) === false) {
        $mod_cache_name = $CURUSER['username'];
        $mc1->add_value('editedby_' . $id, $mod_cache_name, $INSTALLER09['expires']['ismoddin']);
    }
    $HTMLOUT.= '<h1><font size="+1"><font color="#FF0000">' . $mod_cache_name . '</font> is currently editing this torrent!</font></h1>';
}
$ismodd = '<tr><td align=\'center\' class=\'colhead\' colspan=\'2\'><b>Edit Torrent</b> ' . (($CURUSER['class'] > UC_UPLOADER) ? '<small><a href="edit.php?id=' . $id . '&amp;unedit=1">Click here</a> to add temp edit notification while you edit this torrent</small>' : '') . '</td></tr>';
$HTMLOUT.= "<form method='post' name='edit' action='takeedit.php' enctype='multipart/form-data'>
    <input type='hidden' name='id' value='$id' />";
if (isset($_GET["returnto"])) $HTMLOUT.= "<input type='hidden' name='returnto' value='" . htmlsafechars($_GET["returnto"]) . "' />\n";
$HTMLOUT.= "<table border='1' cellspacing='0' cellpadding='10'>\n";
$HTMLOUT.= $ismodd;
$HTMLOUT.= tr("{$lang['edit_imdb_url']}", "<input type='text' name='url' size='80' value='" . htmlsafechars($row["url"]) . "' />", 1);
$HTMLOUT.= tr($lang['edit_poster'], "<input type='text' name='poster' size='80' value='" . htmlsafechars($row["poster"]) . "' /><br />{$lang['edit_poster1']}\n", 1);
$HTMLOUT.= tr($lang['edit_youtube'], "<input type='text' name='youtube' value='" . htmlsafechars($row['youtube']) . "'size='80' /><br />({$lang['edit_youtube_info']})\n", 1);
$HTMLOUT.= tr($lang['edit_torrent_name'], "<input type='text' name='name' value='" . htmlsafechars($row["name"]) . "' size='80' />", 1);
$HTMLOUT.= tr($lang['edit_torrent_tags'], "<input type='text' name='tags' value='" . htmlsafechars($row["tags"]) . "' size='80' /><br />({$lang['edit_tags_info']})\n", 1);
$HTMLOUT.= tr($lang['edit_torrent_description'], "<input type='text' name='description' value='" . htmlsafechars($row['description']) . "' size='80' />", 1);
$HTMLOUT.= tr($lang['edit_nfo'], "<input type='radio' name='nfoaction' value='keep' checked='checked' />{$lang['edit_keep_current']}<br />" . "<input type='radio' name='nfoaction' value='update' />{$lang['edit_update']}<br /><input type='file' name='nfo' size='80' />", 1);
if ((strpos($row["ori_descr"], "<") === false) || (strpos($row["ori_descr"], "&lt;") !== false)) {
    $c = "";
} else {
    $c = " checked";
}
$HTMLOUT .= tr($lang['edit_description'], "". BBcode(htmlsafechars($row['ori_descr']),false)."<br />({$lang['edit_tags']})", 1);
$s = "<select name='type'>\n";
$cats = genrelist();
foreach ($cats as $subrow) {
    $s.= "<option value='" . (int)$subrow["id"] . "'";
    if ($subrow["id"] == $row["category"]) $s.= " selected='selected'";
    $s.= ">" . htmlsafechars($subrow["name"]) . "</option>\n";
}
$s.= "</select>\n";
$HTMLOUT.= tr($lang['edit_type'], $s, 1);
require_once (CACHE_DIR . 'subs.php');
$subs_list = '';
$subs_list.= "<table border=\"1\"><tr>\n";
$i = 0;
foreach ($subs as $s) {
    $subs_list.= ($i && $i % 4 == 0) ? "</tr><tr>" : "";
    $subs_list.= "<td style='padding-right: 5px'><input name=\"subs[]\" " . (strpos($row["subs"], $s["id"]) !== false ? " checked='checked'" : "") . "  type=\"checkbox\" value=\"" . (int)$s["id"] . "\" />" . htmlsafechars($s["name"]) . "</td>\n";
    ++$i;
}
$subs_list.= "</tr></table>\n";
$HTMLOUT.= tr("Subtitiles", $subs_list, 1);
$rg = "<select name='release_group'>\n<option value='scene'" . ($row["release_group"] == "scene" ? " selected='selected'" : "") . ">Scene</option>\n<option value='p2p'" . ($row["release_group"] == "p2p" ? " selected='selected'" : "") . ">p2p</option>\n<option value='none'" . ($row["release_group"] == "none" ? " selected='selected'" : "") . ">None</option> \n</select>\n";
$HTMLOUT.= tr("Release Group", $rg, 1);
$HTMLOUT.= tr($lang['edit_visible'], "<input type='checkbox' name='visible'" . (($row["visible"] == "yes") ? " checked='checked'" : "") . " value='1' /> {$lang['edit_visible_mainpage']}<br /><table border='0' cellspacing='0' cellpadding='0' width='420'><tr><td class='embedded'>{$lang['edit_visible_info']}</td></tr></table>", 1);
if ($CURUSER['class'] >= UC_STAFF) $HTMLOUT.= tr($lang['edit_banned'], "<input type='checkbox' name='banned'" . (($row["banned"] == "yes") ? " checked='checked'" : "") . " value='1' /> {$lang['edit_banned']}", 1);
$HTMLOUT.= tr($lang['edit_recommend_torrent'], "<input type='radio' name='recommended' " . (($row["recommended"] == "yes") ? "checked='checked'" : "") . " value='yes' />Yes!<input type='radio' name='recommended' " . ($row["recommended"] == "no" ? "checked='checked'" : "") . " value='no' />No!<br/><font class='small' >{$lang['edit_recommend']}</font>", 1);
if ($CURUSER['class'] >= UC_UPLOADER) {
    $HTMLOUT.= tr("Nuked", "<input type='radio' name='nuked'" . ($row["nuked"] == "yes" ? " checked='checked'" : "") . " value='yes' />Yes <input type='radio' name='nuked'" . ($row["nuked"] == "no" ? " checked='checked'" : "") . " value='no' />No", 1);
    $HTMLOUT.= tr("Nuke Reason", "<input type='text' name='nukereason' value='" . htmlsafechars($row["nukereason"]) . "' size='80' />", 1);
}
if ($CURUSER['class'] >= UC_STAFF && XBT_TRACKER == false) {
    $HTMLOUT.= tr("Free Leech", ($row['free'] != 0 ? "<input type='checkbox' name='fl' value='1' /> Remove Freeleech" : "
    <select name='free_length'>
    <option value='0'>------</option>
    <option value='42'>Free for 1 day</option>
    <option value='1'>Free for 1 week</option>
    <option value='2'>Free for 2 weeks</option>
    <option value='4'>Free for 4 weeks</option>
    <option value='8'>Free for 8 weeks</option>
    <option value='255'>Unlimited</option>
    </select>") , 1);
    if ($row['free'] != 0) {
        $HTMLOUT.= tr("Free Leech Duration", ($row['free'] != 1 ? "Until " . get_date($row['free'], 'DATE') . " 
		 (" . mkprettytime($row['free'] - TIME_NOW) . " to go)" : 'Unlimited') , 1);
    }
    $HTMLOUT.= tr("Silver torrent ", ($row['silver'] != 0 ? "<input type='checkbox' name='slvr' value='1' /> Remove Silver torrent" : "
    <select name='half_length'>
    <option value='0'>------</option>
    <option value='42'>Silver for 1 day</option>
    <option value='1'>Silver for 1 week</option>
    <option value='2'>Silver for 2 weeks</option>
    <option value='4'>Silver for 4 weeks</option>
    <option value='8'>Silver for 8 weeks</option>
    <option value='255'>Unlimited</option>
    </select>") , 1);
    if ($row['silver'] != 0) {
        $HTMLOUT.= tr("Silver Torrent Duration", ($row['silver'] != 1 ? "Until " . get_date($row['silver'], 'DATE') . " 
		 (" . mkprettytime($row['silver'] - TIME_NOW) . " to go)" : 'Unlimited') , 1);
    }
}
// ===09 Allow Comments
if ($CURUSER['class'] >= UC_STAFF && $CURUSER['class'] == UC_MAX) {
    if ($row["allow_comments"] == "yes") $messc = "&nbsp;Comments are allowed for everyone on this torrent!";
    else $messc = "&nbsp;Only staff members are able to comment on this torrent!";
    $HTMLOUT.= "<tr>
    <td align='right'><font color='red'>&nbsp;*&nbsp;</font><b>&nbsp;{$lang['edit_comment']}</b></td>
    <td>
    <select name='allow_comments'>
    <option value='" . htmlsafechars($row["allow_comments"]) . "'>" . htmlsafechars($row["allow_comments"]) . "</option>
    <option value='yes'>Yes</option><option value='no'>No</option></select>{$messc}</td></tr>\n";
}
// ===end
if ($CURUSER['class'] >= UC_STAFF) {
    $HTMLOUT.= tr("Sticky", "<input type='checkbox' name='sticky'" . (($row["sticky"] == "yes") ? " checked='checked'" : "") . " value='yes' />Sticky this torrent !", 1);
    $HTMLOUT.= tr($lang['edit_anonymous'], "<input type='checkbox' name='anonymous'" . (($row["anonymous"] == "yes") ? " checked='checked'" : "") . " value='1' />{$lang['edit_anonymous1']}", 1);
    if (XBT_TRACKER == false) {
        $HTMLOUT.= tr("VIP Torrent?", "<input type='checkbox' name='vip'" . (($row["vip"] == 1) ? " checked='checked'" : "") . " value='1' /> If this one is checked, only VIPs can download this torrent", 1);
    }
    if (XBT_TRACKER == true) {
        $HTMLOUT.= tr("Freeleech", "<input type='checkbox' name='freetorrent'" . (($row["freetorrent"] == 1) ? " checked='checked'" : "") . " value='1' /> Check this to make this torrent freeleech", 1);
    }
}
//==09 Genre mod no sql
$HTMLOUT.= "
    <tr>
    <td class='heading' align='right'><b>Genre</b>&nbsp;&nbsp;&nbsp;<br />Optional&nbsp;&nbsp;&nbsp;
    </td>
    <td align='left'> 
    <table>
    <tr>
    <td align='left'>
    <input type='radio' name='genre' value='keep' checked='checked' />Dont touch it [ Current: " . htmlsafechars($row['newgenre']) . " ]<br /></td>
    <td style='border:none'><input type='radio' name='genre' value='movie' />Movie</td>
    <td style='border:none'><input type='radio' name='genre' value='music' />Music</td>
    <td style='border:none'><input type='radio' name='genre' value='game' />Game</td>
    <td style='border:none'><input type='radio' name='genre' value='apps' />Apps</td>
    <td style='border:none'><input type='radio' name='genre' value='none' />None</td>
    </tr>
    </table> 
    <table> 
    <tr>
    <td colspan='4' style='border:none'>
    <label style='margin-bottom: 1em; padding-bottom: 1em; border-bottom: 3px silver groove;'>
    <input type='hidden' class='Depends on genre being movie or genre being music' /></label>";
$movie = array(
    'Action',
    'Comedy',
    'Thriller',
    'Adventure',
    'Family',
    'Adult',
    'Sci-fi'
);
for ($x = 0; $x < count($movie); $x++) {
    $HTMLOUT.= "<label><input type=\"checkbox\" value=\"$movie[$x]\"  name=\"movie[]\" class=\"DEPENDS ON genre BEING movie\" />$movie[$x]</label>";
}
$music = array(
    'Hip Hop',
    'Rock',
    'Pop',
    'House',
    'Techno',
    'Commercial'
);
for ($x = 0; $x < count($music); $x++) {
    $HTMLOUT.= "<label><input type=\"checkbox\" value=\"$music[$x]\" name=\"music[]\" class=\"DEPENDS ON genre BEING music\" />$music[$x]</label>";
}
$game = array(
    'Fps',
    'Strategy',
    'Adventure',
    '3rd Person',
    'Acton'
);
for ($x = 0; $x < count($game); $x++) {
    $HTMLOUT.= "<label><input type=\"checkbox\" value=\"$game[$x]\" name=\"game[]\" class=\"DEPENDS ON genre BEING game\" />$game[$x]</label>";
}
$apps = array(
    'Burning',
    'Encoding',
    'Anti-Virus',
    'Office',
    'Os',
    'Misc',
    'Image'
);
for ($x = 0; $x < count($apps); $x++) {
    $HTMLOUT.= "<label><input type=\"checkbox\" value=\"$apps[$x]\" name=\"apps[]\" class=\"DEPENDS ON genre BEING apps\" />$apps[$x]</label>";
}
$HTMLOUT.= "</td></tr></table></td></tr>";
$HTMLOUT.= "<tr><td colspan='2' align='center'><input type='submit' value='{$lang['edit_submit']}' class='btn' /> <input type='reset' value='{$lang['edit_revert']}' class='btn' /></td></tr>
    </table>
    </form>
    <br />
    <form method='post' action='delete.php'>
    <table align='center' border='1' cellspacing='0' cellpadding='5'>
    <tr>
      <td class='colhead' style='background-color: #F5F4EA;padding-bottom: 5px' colspan='2'><b>{$lang['edit_delete_torrent']}.</b> {$lang['edit_reason']}</td>
    </tr>
    <tr>
      <td><input name='reasontype' type='radio' value='1' />&nbsp;{$lang['edit_dead']} </td><td> {$lang['edit_peers']}</td>
    </tr>
    <tr>
      <td><input name='reasontype' type='radio' value='2' />&nbsp;{$lang['edit_dupe']}</td><td><input type='text' size='40' name='reason[]' /></td>
    </tr>
    <tr>
      <td><input name='reasontype' type='radio' value='3' />&nbsp;{$lang['edit_nuked']}</td><td><input type='text' size='40' name='reason[]' /></td>
    </tr>
    <tr>
      <td><input name='reasontype' type='radio' value='4' />&nbsp;{$lang['edit_rules']}</td><td><input type='text' size='40' name='reason[]' />({$lang['edit_req']})</td>
    </tr>
    <tr>
      <td><input name='reasontype' type='radio' value='5' checked='checked' />&nbsp;{$lang['edit_other']}</td><td><input type='text' size='40' name='reason[]' />({$lang['edit_req']})<input type='hidden' name='id' value='$id' /></td>
    </tr>";
if (isset($_GET["returnto"])) {
    $HTMLOUT.= "<input type='hidden' name='returnto' value='" . htmlsafechars($_GET["returnto"]) . "' />\n";
}
$HTMLOUT.= "<tr><td colspan='2' align='center'><input type='submit' value='{$lang['edit_delete']}' class='btn' /></td>
    </tr>
    </table>
    </form>";
//////////////////////////// HTML OUTPIT ////////////////////////////////
echo stdhead("{$lang['edit_stdhead']} '{$row["name"]}'", true, $stdhead) . $HTMLOUT . stdfoot($stdfoot);
?>
