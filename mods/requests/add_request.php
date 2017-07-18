<?php
if (!defined('IN_REQUESTS')) exit('No direct script access allowed');
/**
 |--------------------------------------------------------------------------|
 |   https://github.com/Bigjoos/                			    |
 |--------------------------------------------------------------------------|
 |   Licence Info: GPL			                                    |
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
if ($CURUSER['class'] < $INSTALLER09['req_min_class']) {
    $HTMLOUT.= "<h1>Oops!</h1>
    <div class='some class'>{$lang['add_must_be']}" . get_user_class_name($INSTALLER09['req_min_class']) . "{$lang['add_ratio_above']}" . $INSTALLER09['req_min_ratio'] . "{$lang['add_make_req']}
    <br /><br />{$lang['add_faq']}<br /><br />
    <b>" . $INSTALLER09['site_name'] . " staff</b></div>";
    /////////////////////// HTML OUTPUT //////////////////////////////
    echo stdhead('Requests Page') . $HTMLOUT . stdfoot();
    die();
}
$gigsneeded = ($INSTALLER09['req_gigs_upped'] * 1024 * 1024 * 1024);
$gigsupped = $CURUSER['uploaded'];
$ratio = (($CURUSER['downloaded'] > 0) ? ($CURUSER['uploaded'] / $CURUSER['downloaded']) : 0);
if ($CURUSER['class'] < UC_VIP) {
    $gigsdowned = $CURUSER['downloaded'];
    if ($gigsdowned >= $gigsneeded) $gigs = $CURUSER['uploaded'] / (1024 * 1024 * 1024);
}
$HTMLOUT.= "<h3>{$lang['add_rules']}</h3>";
$HTMLOUT.= "{$lang['add_rules1']}<b> " . $INSTALLER09['req_min_ratio'] . "</b>{$lang['add_rules2']}<b>" . $INSTALLER09['req_gigs_upped'] . " GB</b>.<br />" . ($INSTALLER09['karma'] ? "{$lang['add_rules3']}<b><a class='altlink' href='mybonus.php'>" . $INSTALLER09['req_cost_bonus'] . " Karma Points</a></b>....<br /><br />" : '') . " 
{$lang['add_rules4']}<a class='altlink' href='userdetails.php?id=" . $CURUSER['id'] . "'>" . $CURUSER['username'] . "</a>, ";
if ($INSTALLER09['karma'] && isset($CURUSER['seedbonus']) && $CURUSER['seedbonus'] < $INSTALLER09['req_cost_bonus']) {
    $HTMLOUT.= "{$lang['add_rules7']}<a class='altlink' href='mybonus.php'>Karma Points</a> ...
        {$lang['add_rules8']}<p>{$lang['add_rules9']}
        <a class='altlink' href='viewrequests.php'><b>{$lang['add_rules6']}</b></a></p>\n<br /><br />";
} elseif ($gigsupped < $gigsneeded && $CURUSER['class'] < UC_VIP) {
    $HTMLOUT.= "{$lang['add_rules10']}<b>" . $INSTALLER09['req_gigs_upped'] . " GB</b>{$lang['add_rules11']}<p>
    {$lang['add_rules9']}<a class='altlink' href='viewrequests.php'><b>{$lang['add_rules6']}</b></a></p>\n
    <br /><br />";
} elseif ($ratio < $INSTALLER09['req_min_ratio'] && $CURUSER['class'] < UC_VIP) {
    $sss = ($gigsupped < $gigsneeded ? 's' : '');
    $HTMLOUT.= "{$lang['add_rules15']}<b>" . member_ratio($CURUSER['uploaded'], $CURUSER['downloaded']) . "</b>" . ($gigsupped < $gigsneeded ? "{$lang['add_rules12']}<b> " . round($gigs, 2) . ' GB</b>' : '') . " {$lang['add_rules13']}$sss{$lang['add_rules14']}<br /><br />
         <p>{$lang['add_rules9']}<a href='viewrequests.php'><b>{$lang['add_rules6']}</b></a></p>\n<br /><br />";
} else {
    $HTMLOUT.= "{$lang['add_rules5']} 
    <a class='altlink' href='viewrequests.php'>{$lang['add_rules6']}</a></p>\n";
    /** search first **/
    $HTMLOUT.= "<form method='get' action='browse.php'><table width='750px' border='1' cellspacing='0' cellpadding='5'><tr><td class='colhead' align='left'>
{$lang['add_search_before']}</td></tr><tr><td align='left'>
<input type='text' name='search' size='40' value='' class='btn' />{$lang['add_in']}<select name='cat'> <option value='0'>{$lang['add_all_types']}</option>
";
    $catdropdown = '';
    foreach ($cats as $cat) {
        $catdropdown.= "<option value='" . $cat['id'] . "'";
        if ($cat['id'] == (isset($_GET['cat']) ? $_GET['cat'] : '')) $catdropdown.= " selected='selected'";
        $catdropdown.= ">" . htmlspecialchars($cat['name']) . "</option>\n";
    }
    $deadchkbox = "<input type='checkbox' name='incldead' value='1'";
    if (isset($_GET['incldead'])) $deadchkbox.= " checked='checked'";
    $deadchkbox.= " />{$lang['add_incl_dead']}\n";
    $HTMLOUT.= " " . $catdropdown . " </select> " . $deadchkbox . " 
<input type='submit' value='{$lang['req_search']}' class='btn' /></td></tr></table></form>
<br />\n";
    $HTMLOUT.= "<form method='post' name='compose' action='viewrequests.php?new_request'><a name='add' id='add'></a>
<table border='1' cellspacing='0' width='750px' cellpadding='5'><tr><td class='colhead' align='left' colspan='2'>
{$lang['add_good_ratio']}" . $INSTALLER09['req_gigs_upped'] . "{$lang['add_share']}</td></tr>
<tr><td align='right'><b>{$lang['add_title']}</b></td><td align='left'><input type='text' size='40' name='requesttitle' />
<select name='category'><option value='0'>{$lang['add_select_cat']}</option>\n";
    $res2 = sql_query('SELECT id, name FROM categories order by name');
    $num = mysqli_num_rows($res2);
    $catdropdown2 = '';
    for ($i = 0; $i < $num; ++$i) {
        $cats2 = mysqli_fetch_assoc($res2);
        $catdropdown2.= "<option value='" . $cats2['id'] . "'";
        $catdropdown2.= ">" . htmlspecialchars($cats2['name']) . "</option>\n";
    }
    $HTMLOUT.= $catdropdown2 . " </select></td></tr>
<tr><td align='right' valign='top'><b>{$lang['add_image']}</b></td>
<td align='left'>
<input type='text' name='picture' size='80' /><br />
{$lang['add_direct_link']}<br />
<!--
<a href='panel.php?tool=bitbucket' rel='external'><strong>{$lang['add_upload_image']}</strong></a>
-->
</td></tr>

<tr><td align='right'><b>{$lang['add_description']}</b></td><td align='left'>\n";
    if ($INSTALLER09['textbbcode']) {
        require_once (INCL_DIR . 'bbcode_functions.php');
        $HTMLOUT.= textbbcode('add_request', 'body', '');
    } else $HTMLOUT.= "<textarea name='body' rows='20' cols='80'></textarea>";
    $HTMLOUT.= "</td></tr>
<tr><td align='center' colspan='2'>
<input type='submit' value='{$lang['add_ok']}' class='btn' /></td></tr></table>
</form>
<br /><br />\n";
}
$rescount = sql_query('SELECT id FROM requests LIMIT 1') or sqlerr(__FILE__, __LINE__);
if (mysqli_num_rows($rescount) > 0) {
    $res = sql_query("SELECT users.username, requests.id, requests.userid, requests.cat, requests.request, requests.added, categories.name, categories.image, uploaded, downloaded FROM users inner join requests ON requests.userid = users.id left join categories ON requests.cat = categories.id order by requests.id desc LIMIT 10") or sqlerr();
    $num = mysqli_num_rows($res);
    $HTMLOUT.= "<table border='1' cellspacing='0' width='750px' cellpadding='5'>
    <tr><td width='50px' class='colhead' align='left'>{$lang['add_cat']}</td>
    <td class='colhead' align='left'>{$lang['add_request']}</td><td class='colhead' align='center'>{$lang['req_added']}</td>
    <td class='colhead' align='center'>{$lang['req_req_by']}</td></tr>\n";
    foreach ($cats as $key => $value) $change[$value['id']] = array(
        'id' => $value['id'],
        'name' => $value['name'],
        'image' => $value['image']
    );
    while ($arr = mysqli_fetch_assoc($res)) {
        $addedby = "<td style='padding: 0px' align='center'><b><a href='userdetails.php?id=$arr[userid]'>$arr[username]</a></b></td>";
        $catname = htmlspecialchars($change[$arr['cat']]['name']);
        $catpic = htmlspecialchars($change[$arr['cat']]['image']);
        $catimage = "<img src='pic/caticons/" . $catpic . "' title='$catname' alt='$catname' />";
        $HTMLOUT.= "<tr>
    <td align='center'>" . $catimage . "</td>
    <td align='left'><a href='viewrequests.php?id=$arr[id]&amp;req_details'>
    <b>" . htmlspecialchars($arr['request']) . "</b></a></td>
    <td align='center'>" . get_date($arr['added'], '') . "</td>
    $addedby
    </tr>\n";
    }
    $HTMLOUT.= "<tr><td align='center' colspan='4'>
<form method='get' action='viewrequests.php'>
<input type='submit' value='{$lang['req_show_all']}' class='btn' />
</form>
</td></tr>
</table>\n";
}
/////////////////////// HTML OUTPUT //////////////////////////////
echo stdhead('Requests Page') . $HTMLOUT . stdfoot();
?>
