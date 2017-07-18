<?php
/**
 |--------------------------------------------------------------------------|
 |   https://github.com/Bigjoos/                			    |
 |--------------------------------------------------------------------------|
 |   Licence Info: GPL			                                    |
 |--------------------------------------------------------------------------|
 |   Copyright (C) 2010 U-232 V5					    |
 |--------------------------------------------------------------------------|
 |   A bittorrent tracker source based on TBDev.net/tbsource/bytemonsoon.   |
 |--------------------------------------------------------------------------|
 |   Project Leaders: Mindless, Autotron, whocares, Swizzles.					    |
 |--------------------------------------------------------------------------|
  _   _   _   _   _     _   _   _   _   _   _     _   _   _   _
 / \ / \ / \ / \ / \   / \ / \ / \ / \ / \ / \   / \ / \ / \ / \
( U | - | 2 | 3 | 2 )-( S | o | u | r | c | e )-( C | o | d | e )
 \_/ \_/ \_/ \_/ \_/   \_/ \_/ \_/ \_/ \_/ \_/   \_/ \_/ \_/ \_/
 */
//== Made by putyn @tbdev
require_once (__DIR__ . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php');
require_once (INCL_DIR . 'user_functions.php');
require_once (INCL_DIR . 'html_functions.php');
require_once (INCL_DIR . 'pager_functions.php');
dbconn();
loggedinorreturn();
$lang = array_merge(load_language('global'));
$HTMLOUT = '';
if (!function_exists('htmlsafechars')) {
    function htmlsafechars($var)
    {
        return str_replace(array(
            '&',
            '>',
            '<',
            '"',
            '\''
        ) , array(
            '&amp;',
            '&gt;',
            '&lt;',
            '&quot;',
            '&#039;'
        ) , str_replace(array(
            '&gt;',
            '&lt;',
            '&quot;',
            '&#039;',
            '&amp;'
        ) , array(
            '>',
            '<',
            '"',
            '\'',
            '&'
        ) , $var));
    }
}

$action = (isset($_GET["action"]) ? htmlsafechars($_GET["action"]) : (isset($_POST["action"]) ? htmlsafechars($_POST["action"]) : ''));
$mode = (isset($_GET["mode"]) ? htmlsafechars($_GET["mode"]) : "");
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if ($action == "upload" || $action == "edit") {
        $langs = isset($_POST["language"]) ? htmlsafechars($_POST["language"]) : "";
        if (empty($langs)) stderr("Upload failed", "No language selected");
        $releasename = isset($_POST["releasename"]) ? htmlsafechars($_POST["releasename"]) : "";
        if (empty($releasename)) stderr("Upload failed", "Use a descriptive name for you subtitle");
        $imdb = isset($_POST["imdb"]) ? htmlsafechars($_POST["imdb"]) : "";
        if (empty($imdb)) stderr("Upload failed", "You forgot to add the imdb link");
        $comment = isset($_POST["comment"]) ? htmlsafechars($_POST["comment"]) : "";
        $poster = isset($_POST["poster"]) ? htmlsafechars($_POST["poster"]) : "";
        $fps = isset($_POST["fps"]) ? htmlsafechars($_POST["fps"]) : "";
        $cd = isset($_POST["cd"]) ? htmlsafechars($_POST["cd"]) : "";
        if ($action == "upload") {
            $file = $_FILES["sub"];
            if (!isset($file)) stderr("Upload failed", "The file can't be empty!");
            if ($file["size"] > $INSTALLER09['sub_max_size']) stderr("Upload failed", "What the hell did you upload?");
            $fname = $file["name"];
            $temp_name = $file["tmp_name"];
            $ext = (substr($fname, -3));
            $allowed = array(
                "srt",
                "sub",
                "txt"
            );
            if (!in_array($ext, $allowed)) stderr("Upload failed", "File not allowed only .srt , .sub , .txt  files");
            $new_name = md5(TIME_NOW);
            $filename = "$new_name.$ext";
            $date = TIME_NOW;
            $owner = $CURUSER["id"];
            sql_query("INSERT INTO subtitles (name , filename,imdb,comment, lang, fps, poster, cds, added, owner ) VALUES (" . implode(",", array_map("sqlesc", array(
                $releasename,
                $filename,
                $imdb,
                $comment,
                $langs,
                $fps,
                $poster,
                $cd,
                $date,
                $owner
            ))) . ")") or sqlerr(__FILE__, __LINE__);
            move_uploaded_file($temp_name, "{$INSTALLER09['sub_up_dir']}/$filename");
            $id = ((is_null($___mysqli_res = mysqli_insert_id($GLOBALS["___mysqli_ston"]))) ? false : $___mysqli_res);
            header("Refresh: 0; url=subtitles.php?mode=details&id=$id");
        } //end upload
        if ($action == "edit") {
            $id = isset($_POST["id"]) ? (int) $_POST["id"] : 0;
            if ($id == 0) stderr("Err", "Not a valid id");
            else {
                $res = sql_query("SELECT * FROM subtitles WHERE id=".sqlesc($id)) or sqlerr(__FILE__, __LINE__);
                $arr = mysqli_fetch_assoc($res);
                if (mysqli_num_rows($res) == 0) stderr("Sorry", "There is no subtitle with that id");
                if ($CURUSER["id"] != $arr["owner"] && $CURUSER['class'] < UC_MODERATOR) bark("You're not the owner! How did that happen?\n");
                $updateset = array();
                if ($arr["name"] != $releasename) $updateset[] = "name = " . sqlesc($releasename);
                if ($arr["imdb"] != $imdb) $updateset[] = "imdb = " . sqlesc($imdb);
                if ($arr["lang"] != $langs) $updateset[] = "lang = " . sqlesc($langs);
                if ($arr["poster"] != $poster) $updateset[] = "poster = " . sqlesc($poster);
                if ($arr["fps"] != $fps) $updateset[] = "fps = " . sqlesc($fps);
                if ($arr["cds"] != $cd) $updateset[] = "cds = " . sqlesc($cd);
                if ($arr["comment"] != $comment) $updateset[] = "comment = " . sqlesc($comment);
                if (count($updateset) > 0) sql_query("UPDATE subtitles SET " . join(",", $updateset) . " WHERE id =".sqlesc($id)) or sqlerr(__FILE__, __LINE__);
                header("Refresh: 0; url=subtitles.php?mode=details&id=$id");
            }
        } //end edit
        
    } //end upload && edit
    
} //end POST
if ($mode == "upload" || $mode == "edit") {
    if ($mode == "edit") {
        $id = isset($_GET["id"]) ? (int) $_GET["id"] : 0;
        if ($id == 0) stderr("Err", "Not a valid id");
        else {
            $res = sql_query("SELECT id, name, imdb, poster, fps, comment, cds, lang FROM subtitles WHERE id=".sqlesc($id)) or sqlerr(__FILE__, __LINE__);
            $arr = mysqli_fetch_assoc($res);
            if (mysqli_num_rows($res) == 0) stderr("Sorry", "There is no subtitle with that id");
        }
    }
    $HTMLOUT.= begin_main_frame();
    $HTMLOUT.= begin_frame("" . ($mode == "upload" ? "New Subtitle" : "Edit subtitle " . htmlsafechars($arr["name"]) . "") . "");
    $HTMLOUT.= "<script type='text/javascript'>
function checkext(upload_field)
{
    var re_text = /\.sub|\.srt|\.txt/i;
    var filename = upload_field.value;

    /* Checking file type */
    if (filename.search(re_text) == -1)
    {
        alert('File does not have allowed (sub, srt, txt) extension');
        upload_field.form.reset();
        return false;
    }
}
</script>
<form enctype='multipart/form-data' method='post' action='subtitles.php'>
<table style='width:400px; border:solid 1px #000000;' align='center' cellpadding='5' cellspacing='0'>";
    if ($mode == "upload") {
        $HTMLOUT.= "<tr><td colspan='2' align='center' class='colhead'><font color='red'><b>Only .srt, .sub , .txt  file are accepted<br />Max file size " . mksize($INSTALLER09['sub_max_size']) . "</b></font></td></tr>";
    }
    $HTMLOUT.= "<tr><td class='rowhead' style='border:none'>Language&nbsp;<font color='red'>*</font></td><td style='border:none'><select name='language' title='Select the subtitle language'>
	<option value=''>- Select -</option>
	<option value='eng' " . ($mode == "edit" && $arr["lang"] == "eng" ? "selected=\"selected\"" : "") . ">English</option>
	<option value='swe' " . ($mode == "edit" && $arr["lang"] == "swe" ? "selected=\"selected\"" : "") . ">Swedish</option>
	<option value='dan' " . ($mode == "edit" && $arr["lang"] == "dan" ? "selected=\"selected\"" : "") . ">Danish</option>
	<option value='nor' " . ($mode == "edit" && $arr["lang"] == "nor" ? "selected=\"selected\"" : "") . ">Norwegian</option>
	<option value='fin' " . ($mode == "edit" && $arr["lang"] == "fin" ? "selected=\"selected\"" : "") . ">Finnish</option>
	<option value='spa' " . ($mode == "edit" && $arr["lang"] == "spa" ? "selected=\"selected\"" : "") . ">Spanish</option>
	<option value='fre' " . ($mode == "edit" && $arr["lang"] == "fre" ? "selected=\"selected\"" : "") . ">French</option>
</select>
</td></tr>
<tr><td class='rowhead' style='border:none'>Release Name&nbsp;<font color='red'>*</font></td><td style='border:none'><input type='text' name='releasename' size='50' value='" . ($mode == "edit" ? $arr["name"] : "") . "'  title='The releasename of the movie (Example:Disturbia.2007.DVDRip.XViD-aAF)'/></td></tr>
<tr><td class='rowhead' style='border:none'>IMDB link&nbsp;<font color='red'>*</font></td><td style='border:none'><input type='text' name='imdb' size='50' value='" . ($mode == "edit" ? $arr["imdb"] : "") . "' title='Copy&amp;Paste the link from IMDB for this movie'/></td></tr>";
    if ($mode == "upload") {
        $HTMLOUT.= "<tr><td class='rowhead' style='border:none'>SubFile&nbsp;<font color='red'>*</font></td><td style='border:none'><input type='file' name='sub' size='36' onchange=\"checkext(this)\" title='Only .rar and .zip file allowed'/></td></tr>";
    }
    $HTMLOUT.= "<tr><td class='rowhead' style='border:none'>Poster</td><td style='border:none'><input type='text' name='poster' size='50' value='" . ($mode == "edit" ? $arr["poster"] : "") . "' title='Direct link to a picture'/></td></tr>
<tr><td class='rowhead' style='border:none'>Comments</td><td style='border:none'><textarea rows='5' cols='45' name='comment' title='Any specific details about this subtitle we need to know'>" . ($mode == "edit" ? htmlsafechars($arr["comment"]) : "") . "</textarea></td></tr>
<tr><td class='rowhead' style='border:none'>FPS</td><td style='border:none'><select name='fps'>
<option value='0'>- Select -</option>
<option value='23.976' " . ($mode == "edit" && $arr["fps"] == "23.976" ? "selected=\"selected\"" : "") . ">23.976</option>
<option value='23.980' " . ($mode == "edit" && $arr["fps"] == "23.980" ? "selected=\"selected\"" : "") . ">23.980</option>
<option value='24.000' " . ($mode == "edit" && $arr["fps"] == "24.000" ? "selected=\"selected\"" : "") . ">24.000</option>
<option value='25.000' " . ($mode == "edit" && $arr["fps"] == "25.000" ? "selected=\"selected\"" : "") . ">25.000</option>
<option value='29.970' " . ($mode == "edit" && $arr["fps"] == "29.970" ? "selected=\"selected\"" : "") . ">29.970</option>
<option value='30.000' " . ($mode == "edit" && $arr["fps"] == "30.000" ? "selected=\"selected\"" : "") . ">30.000</option>
</select>
</td></tr>
<tr><td class='rowhead' style='border:none'>CD<br/>number</td><td style='border:none'><select name='cd'>
<option value='0'>- Select -</option>
<option value='1' " . ($mode == "edit" && $arr["cds"] == "1" ? "selected=\"selected\"" : "") . ">1CD</option>
<option value='2' " . ($mode == "edit" && $arr["cds"] == "2" ? "selected=\"selected\"" : "") . ">2CD</option>
<option value='3' " . ($mode == "edit" && $arr["cds"] == "3" ? "selected=\"selected\"" : "") . ">3CD</option>
<option value='4' " . ($mode == "edit" && $arr["cds"] == "4" ? "selected=\"selected\"" : "") . ">4CD</option>
<option value='5' " . ($mode == "edit" && $arr["cds"] == "5" ? "selected=\"selected\"" : "") . ">5CD</option>
<option value='255' " . ($mode == "edit" && $arr["cds"] == "255" ? "selected=\"selected\"" : "") . ">More</option>
</select>
</td></tr>
<tr><td colspan='2' align='center' class='colhead'>";
    if ($mode == "upload") {
        $HTMLOUT.= "<input type='submit' value='Upload it' />
<input type='hidden' name='action' value='upload' />";
    } else {
        $HTMLOUT.= "<input type='submit' value='Edit it'/>
<input type='hidden' name='action' value='edit' />
<input type='hidden' name='id' value='" . (int)$arr["id"] . "' />";
    }
    $HTMLOUT.= "</td></tr>
</table>
</form>";
    $HTMLOUT.= end_frame();
    $HTMLOUT.= end_main_frame();
    echo stdhead("" . ($mode == "upload" ? "Upload new Subtitle" : "Edit subtitle " . htmlsafechars($arr["name"]) . "") . "") . $HTMLOUT . stdfoot();
}
//==Delete subtitle
elseif ($mode == "delete") {
    $id = isset($_GET["id"]) ? (int)$_GET["id"] : 0;
    if ($id == 0) stderr("Err", "Not a valid id");
    else {
        $res = sql_query("SELECT id, name, filename FROM subtitles WHERE id=".sqlesc($id)) or sqlerr(__FILE__, __LINE__);
        $arr = mysqli_fetch_assoc($res);
        if (mysqli_num_rows($res) == 0) stderr("Sorry", "There is no subtitle with that id");
        $sure = (isset($_GET["sure"]) && $_GET["sure"] == "yes") ? "yes" : "no";
        if ($sure == "no") stderr("Sanity check...", "Your are about to delete subtitile <b>" . htmlsafechars($arr["name"]) . "</b> . Click <a href='subtitles.php?mode=delete&amp;id=$id&amp;sure=yes'>here</a> if you are sure.", false);
        else {
            sql_query("DELETE FROM subtitles WHERE id=".sqlesc($id)) or sqlerr(__FILE__, __LINE__);
            $file = $INSTALLER09['sub_up_dir'] . '/' . $arr["filename"];
            @unlink($file);
            header("Refresh: 0; url=subtitles.php");
        }
    }
}
//==End delete subtitle
elseif ($mode == "details") {
    $id = isset($_GET["id"]) ? (int) $_GET["id"] : 0;
    if ($id == 0) stderr("Err", "Not a valid id");
    else {
        $res = sql_query("SELECT s.id, s.name,s.lang, s.imdb,s.fps,s.poster,s.cds,s.hits,s.added,s.owner,s.comment, u.username FROM subtitles AS s LEFT JOIN users AS u ON s.owner=u.id  WHERE s.id=".sqlesc($id)) or sqlerr(__FILE__, __LINE__);
        $arr = mysqli_fetch_assoc($res);
        if (mysqli_num_rows($res) == 0) stderr("Sorry", "There is no subtitle with that id");
        if ($arr["lang"] == "eng") $langs = "<img src=\"pic/flag/england.gif\" border=\"0\" alt=\"English\" title=\"English\" />";
        elseif ($arr["lang"] == "swe") $langs = "<img src=\"pic/flag/sweden.gif\" border=\"0\" alt=\"Swedish\" title=\"Swedish\" />";
        elseif ($arr["lang"] == "dan") $langs = "<img src=\"pic/flag/denmark.gif\" border=\"0\" alt=\"Danish\" title=\"Danish\" />";
        elseif ($arr["lang"] == "nor") $langs = "<img src=\"pic/flag/norway.gih\" border=\"0\" alt=\"Norwegian\" title=\"Norwegian\" />";
        elseif ($arr["lang"] == "fin") $langs = "<img src=\"pic/flag/finland.gif\" border=\"0\" alt=\"Finnish\" title=\"Finnish\" />";
        elseif ($arr["lang"] == "spa") $langs = "<img src=\"pic/flag/spain.gif\" border=\"0\" alt=\"Spanish\" title=\"Spanish\" />";
        elseif ($arr["lang"] == "fre") $langs = "<img src=\"pic/flag/france.gif\" border=\"0\" alt=\"French\" title=\"French\" />";
        else $langs = "<b>Unknown</b>";
        $HTMLOUT.= begin_main_frame();
        $HTMLOUT.= "<table width='600' cellpadding='5' cellspacing='0' border='1' align='center' style='border-collapse:collapse;'>
<tr><td width='150' rowspan='10' valign='top' align='center'>
<img src='" . htmlsafechars($arr["poster"]) . "' width='150' height='195' alt='" . htmlsafechars($arr["name"]) . "' />
<br /><br />
<form action='downloadsub.php' method='post'>
<input type='hidden' name='sid' value='" . (int)$arr["id"] . "' />
<input type='submit' value='' style='background:url(pic/down.png) no-repeat; width:124px;height:25px;border:none;' />
<input type='hidden' name='action' value='download' />
</form><br />
<a href='#' onclick=\"window.open('subtitles.php?mode=preview&amp;id=".(int)$arr["id"]."','','height=500,width=400,resizable=yes,scrollbars=yes')\" ><img src='pic/preview.png' width='124' height='25' border='0' alt='Preview' title='Preview'  /></a>
</td></tr>
<tr><td align='left'>Name :&nbsp;<b>" . htmlsafechars($arr['name']) . "</b></td></tr>
<tr><td align='left'>IMDb :&nbsp;<a href='" . htmlsafechars($arr['imdb']) . "' target='_blank'>" . htmlsafechars($arr['imdb']) . "</a></td></tr>
<tr><td align='left'>Language :&nbsp;{$langs}</td></tr>";
        if (!empty($arr["comment"])) {
            $HTMLOUT.= "<tr><td align='left'><fieldset><legend><b>Comment</b></legend>&nbsp;" . htmlsafechars($arr["comment"]) . "</fieldset></td></tr>";
        }
        $HTMLOUT.= "<tr><td align='left'>FPS :&nbsp;<b>" . ($arr["fps"] == 0 ? "Unknown" : htmlsafechars($arr["fps"])) . "</b></td></tr>
<tr><td align='left'>Cd# :&nbsp;<b>" . ($arr["cds"] == 0 ? "Unknown" : ($arr["cds"] == 255 ? "More than 5 " : htmlsafechars($arr["cds"]))) . "</b></td></tr>
<tr><td align='left'>Hits :&nbsp;<b>".(int)$arr["hits"]."</b></td></tr>
<tr><td align='left'>Uploader :&nbsp;<b><a href='userdetails.php?id=" . (int)$arr["owner"] . "' target='_blank'>" . htmlsafechars($arr["username"]) . "</a></b>&nbsp;&nbsp;";
        if ($arr["owner"] == $CURUSER["id"] || $CURUSER['class'] > UC_MODERATOR) {
            $HTMLOUT.= "<a href='subtitles.php?mode=edit&amp;id=" . (int)$arr["id"] . "'><img src='pic/edit.png' alt='Edit Sub' title='Edit Sub' style='border:none;padding:2px;' /></a>
<a href='subtitles.php?mode=delete&amp;id=" . (int)$arr["id"] . "'><img src='pic/drop.png' alt='Delete Sub' title='Delete Sub' style='border:none;padding:2px;' /></a>";
        }
        $HTMLOUT.= "</td></tr>
<tr><td align='left'>Added :&nbsp;<b>" . get_date($arr["added"], 'LONG', 0, 1) . "</b></td></tr>
</table>";
        $HTMLOUT.= end_main_frame();
        echo stdhead("Details for " . htmlsafechars($arr["name"]) . "") . $HTMLOUT . stdfoot();
    }
} elseif ($mode == "preview") {
    $id = isset($_GET["id"]) ? (int) $_GET["id"] : 0;
    if ($id == 0) stderr("Err", "Not a valid id");
    else {
        $res = sql_query("SELECT id, name,filename FROM subtitles  WHERE id=".sqlesc($id)) or sqlerr(__FILE__, __LINE__);
        $arr = mysqli_fetch_assoc($res);
        if (mysqli_num_rows($res) == 0) stderr("Sorry", "There is no subtitle with that id");
        $file = $INSTALLER09['sub_up_dir'] . "/" . $arr["filename"];
        $fileContent = file_get_contents($file);
        $HTMLOUT.= "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\"
		\"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
		<html xmlns='http://www.w3.org/1999/xhtml'>
		<head>
		<title>Preview for - " . htmlsafechars($arr["name"]) . "</title>
		</head>
		<body>
	<div style='font-size:12px;color:black;background-color:#CCCCCC;'>Subtitle preview<br />" . htmlsafechars($fileContent) . "</div>
	</body></html>";
        echo $HTMLOUT;
    }
} else {
    $HTMLOUT.= begin_frame();
    $s = (isset($_GET["s"]) ? htmlsafechars($_GET["s"]) : "");
    $w = (isset($_GET["w"]) ? htmlsafechars($_GET["w"]) : "");
    if ($s && $w == "name") $where = "WHERE s.name LIKE " . sqlesc("%" . $s . "%");
    elseif ($s && $w == "imdb") $where = "WHERE s.imdb LIKE " . sqlesc("%" . $s . "%");
    elseif ($s && $w == "comment") $where = "WHERE s.comment LIKE " . sqlesc("%" . $s . "%");
    else $where = "";
    $link = ($s && $w ? "s=$s&amp;w=$w&amp;" : "");
    $count = get_row_count("subtitles AS s", "$where");
    if ($count == 0 && !$s && !$w) stdmsg("", "There is no subtitle, go <a href=\"subtitles.php?mode=upload\">here</a> and start uploading.", false);
    $perpage = 5;
    $pager = pager($perpage, $count, "subtitles.php?" . $link);
    $res = sql_query("SELECT s.id, s.name,s.lang, s.imdb,s.fps,s.poster,s.cds,s.hits,s.added,s.owner,s.comment, u.username FROM subtitles AS s LEFT JOIN users AS u ON s.owner=u.id $where ORDER BY s.added DESC {$pager['limit']}") or sqlerr(__FILE__, __LINE__);
    $HTMLOUT.= "<table width='700' cellpadding='5' cellspacing='0' border='0' align='center' style='font-weight:bold'>
<tr><td style='border:none' valign='middle'>
<fieldset style='text-align:center; border:#0066CC solid 1px; background-color:#999999'>
<legend style='text-align:center; border:#0066CC solid 1px ; background-color:#999999;font-size:13px;'><b>Search</b></legend>
<form action='subtitles.php' method='get'>
<input size='50' value='" . $s . "' name='s' type='text' />
<select name='w'>
<option value='name' " . ($w == "name" ? "selected='selected'" : "") . ">Name</option>
<option value='imdb' " . ($w == "imdb" ? "selected='selected'" : "") . ">IMDb</option>
<option value='comment' " . ($w == "comment" ? "selected='selected'" : "") . ">Comments</option>
</select>
<input type='submit' value='Search' />&nbsp;<input type='button' onclick=\"window.location.href='subtitles.php?mode=upload'\" value='Upload' />
</form></fieldset></td></tr>";
    if ($s) {
        $HTMLOUT.= "<tr><td style='border:none;'>Search result for <i>'{$s}'</i><br />" . (mysqli_num_rows($res) == 0 ? "Nothing found! Try again with a refined search string." : "") . "</td></tr>";
    }
    $HTMLOUT.= "
</table>
<br />";
    if (mysqli_num_rows($res) > 0) {
        if ($count > $perpage) $HTMLOUT.= "<div align=\"left\" style=\"padding:5px\">{$pager['pagertop']}</div>";
        $HTMLOUT.= "<table width='700' cellpadding='5' cellspacing='0' border='1' align='center' style='font-weight:bold'>
<tr><td class='colhead' align='center'>Lang</td>
<td class='colhead' align='left' style='width:80%'>Name</td>
<td class='colhead' align='center'>IMDb</td>
<td class='colhead' align='center'>Added</td>
<td class='colhead' align='center'>Hits</td>
<td class='colhead' align='center'>FPS</td>
<td class='colhead' align='center'>CD#</td>";
        while ($arr = mysqli_fetch_assoc($res)) {
            if ($arr["owner"] == $CURUSER["id"] || $CURUSER['class'] > UC_MODERATOR) {
                $HTMLOUT.= "<td class='colhead' align='center'>Tools</td>";
            }
            $HTMLOUT.= "<td class='colhead' align='center'>Upper</td></tr>";
            if ($arr["lang"] == "eng") $langs = "<img src=\"pic/flag/england.gif\" border=\"0\" alt=\"English\" title=\"English\" />";
            elseif ($arr["lang"] == "swe") $langs = "<img src=\"pic/flag/sweden.gif\" border=\"0\" alt=\"Swedish\" title=\"Swedish\" />";
            elseif ($arr["lang"] == "dan") $langs = "<img src=\"pic/flag/denmark.gif\" border=\"0\" alt=\"Danish\" title=\"Danish\" />";
            elseif ($arr["lang"] == "nor") $langs = "<img src=\"pic/flag/norway.gih\" border=\"0\" alt=\"Norwegian\" title=\"Norwegian\" />";
            elseif ($arr["lang"] == "fin") $langs = "<img src=\"pic/flag/finland.gif\" border=\"0\" alt=\"Finnish\" title=\"Finnish\" />";
            elseif ($arr["lang"] == "spa") $langs = "<img src=\"pic/flag/spain.gif\" border=\"0\" alt=\"Spanish\" title=\"Spanish\" />";
            elseif ($arr["lang"] == "fre") $langs = "<img src=\"pic/flag/france.gif\" border=\"0\" alt=\"French\" title=\"French\" />";
            else $langs = "<b>Unknown</b>";
            $HTMLOUT.= "<tr valign='middle'>
<td align='center'>{$langs}</td>
<td><a href='subtitles.php?mode=details&amp;id=" . (int)$arr["id"] . "' onmouseover=\"tip('<img src=\'" . htmlsafechars($arr["poster"]) . "\' width=\'100\'>')\" onmouseout=\"untip()\">" . htmlsafechars($arr["name"]) . "</a></td>
<td align='center'><a href='" . htmlsafechars($arr["imdb"]) . "'  target='_blank'><img src='pic/imdb.gif' border='0' alt='Imdb' title='Imdb' /></a></td>
<td align='center'>" . get_date($arr["added"], 'LONG', 0, 1) . "</td>
<td align='center'>" . htmlsafechars($arr["hits"]) . "</td>
<td align='center'>" . ($arr["fps"] == 0 ? "Unknow" : htmlsafechars($arr["fps"])) . "</td>
<td align='center'>" . ($arr["cds"] == 0 ? "Unknow" : ($arr["cds"] == 255 ? "More than 5 " : htmlsafechars($arr["cds"]))) . "</td>";
            if ($arr["owner"] == $CURUSER["id"] || $CURUSER['class'] > UC_STAFF) {
                $HTMLOUT.= "<td align='center' nowrap='nowrap'>
<a href='subtitles.php?mode=edit&amp;id=" . (int)$arr["id"] . "'><img src='pic/edit.png' alt='Edit Sub' title='Edit Sub' style='border:none;padding:2px;' /></a>
<a href='subtitles.php?mode=delete&amp;id=" . (int)$arr["id"] . "'><img src='pic/drop.png' alt='Delete Sub' title='Delete Sub' style='border:none;padding:2px;' /></a>
</td>";
            }
            $HTMLOUT.= "<td align='center'><a href='userdetails.php?id=" . (int)$arr["owner"] . "'>" . htmlsafechars($arr["username"]) . "</a></td></tr>";
        }
        $HTMLOUT.= "</table>";
    }
    $HTMLOUT.= end_frame();
    echo stdhead("Subtitles") . $HTMLOUT . stdfoot();
}
?>
