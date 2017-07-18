<?php
if (!defined('IN_OFFERS')) exit('No direct script access allowed');
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
if ($CURUSER['class'] < $INSTALLER09['offer_min_class']) {
    $HTMLOUT.= "<h1>Oops!</h1>
    <div class='some class'>You must be " . get_user_class_name($INSTALLER09['offer_min_class']) . " or above <b>AND</b> have a ratio above <b>" . $INSTALLER09['offer_min_ratio'] . "</b> to make an offer.
    <br /><br /> Please see the <a href='faq.php'><b>FAQ</b></a> 
    for more information on different user classes and what they can do.<br /><br />
    <b>" . $INSTALLER09['site_name'] . " staff</b></div>";
    /////////////////////// HTML OUTPUT //////////////////////////////
    echo stdhead('Offers Page') . $HTMLOUT . stdfoot();
    die();
}
$gigsneeded = ($INSTALLER09['offer_gigs_upped'] * 1024 * 1024 * 1024);
$gigsupped = $CURUSER['uploaded'];
$ratio = (($CURUSER['downloaded'] > 0) ? ($CURUSER['uploaded'] / $CURUSER['downloaded']) : 0);
if ($CURUSER['class'] < UC_VIP) {
    $gigsdowned = $CURUSER['downloaded'];
    if ($gigsdowned >= $gigsneeded) $gigs = $CURUSER['uploaded'] / (1024 * 1024 * 1024);
}
$HTMLOUT.= '<h3>Offer Rules</h3>';
$HTMLOUT.= 'To make an offer you must have a ratio of at least<b> ' . $INSTALLER09['offer_min_ratio'] . '</b> AND have uploaded at least <b>' . $INSTALLER09['offer_gigs_upped'] . ' GB</b>.<br />' . ($INSTALLER09['karma'] ? " A offer will also cost you <b><a class='altlink' href='mybonus.php'>" . $INSTALLER09['offer_cost_bonus'] . " Karma Points</a></b>....<br /><br />" : '') . " 
In your particular case <a class='altlink' href='userdetails.php?id=" . $CURUSER['id'] . "'>" . $CURUSER['username'] . '</a>, ';
if ($INSTALLER09['karma'] && isset($CURUSER['seedbonus']) && $CURUSER['seedbonus'] < $INSTALLER09['offer_cost_bonus']) {
    $HTMLOUT.= "you do not have enough <a class='altlink' href='mybonus.php'>Karma Points</a> ...
        you can not make offers.<p>To view all offers, click 
        <a class='altlink' href='viewoffers.php'><b>here</b></a></p>\n<br /><br />";
} elseif ($gigsupped < $gigsneeded && $CURUSER['class'] < UC_VIP) {
    $HTMLOUT.= "you have <b>not</b> yet uploaded <b>" . $INSTALLER09['offer_gigs_upped'] . " GB</b>... you can not make offers.<p>
    To view all offers, click <a class='altlink' href='viewoffers.php'><b>here</b></a></p>\n
    <br /><br />";
} elseif ($ratio < $INSTALLER09['offer_min_ratio'] && $CURUSER['class'] < UC_VIP) {
    $sss = ($gigsupped < $gigsneeded ? 's' : '');
    $HTMLOUT.= "your ratio of <b>" . member_ratio($CURUSER['uploaded'], $CURUSER['downloaded']) . "</b>" . ($gigsupped < $gigsneeded ? ' and your total uploaded of<b> ' . round($gigs, 2) . ' GB</b>' : '') . " fail$sss to meet the minimum requirements. to Make a Offer.<br /><br />
         <p>To view all offers, click <a href='viewoffers.php'><b>here</b></a></p>\n<br /><br />";
} else {
    $HTMLOUT.= "you <b>can</b> make offers.<p>To view all offers, click 
    <a class='altlink' href='viewoffers.php'>here</a></p>\n";
    /** search first **/
    $HTMLOUT.= "<form method='get' action='browse.php'><table width='750px' border='1' cellspacing='0' cellpadding='5'><tr><td class='colhead' align='left'>
Please search torrents before adding an offer!</td></tr><tr><td align='left'>
<input type='text' name='search' size='40' value='' class='btn' /> in <select name='cat'> <option value='0'>(all types)</option>
";
    $catdropdown = '';
    foreach ($cats as $cat) {
        $catdropdown.= "<option value='" . $cat['id'] . "'";
        if ($cat['id'] == (isset($_GET['cat']) ? $_GET['cat'] : '')) $catdropdown.= " selected='selected'";
        $catdropdown.= ">" . htmlspecialchars($cat['name']) . "</option>\n";
    }
    $deadchkbox = "<input type='checkbox' name='incldead' value='1'";
    if (isset($_GET['incldead'])) $deadchkbox.= " checked='checked'";
    $deadchkbox.= " /> including dead torrents\n";
    $HTMLOUT.= " " . $catdropdown . " </select> " . $deadchkbox . " 
<input type='submit' value='Search!' class='btn' /></td></tr></table></form>
<br />\n";
    $HTMLOUT.= "<form method='post' name='compose' action='viewoffers.php?new_offer'><a name='add' id='add'></a>
<table border='1' cellspacing='0' width='750px' cellpadding='5'><tr><td class='colhead' align='left' colspan='2'>
Offers are for Users with a good ratio who have uploaded at least " . $INSTALLER09['offer_gigs_upped'] . " gigs Only... Share and you shall recieve!</td></tr>
<tr><td align='right'><b>Title</b></td><td align='left'><input type='text' size='40' name='offertitle' />
<select name='category'><option value='0'>(Select a Category)</option>\n";
    $res2 = sql_query('SELECT id, name FROM categories order by name');
    $num = mysqli_num_rows($res2);
    $catdropdown2 = '';
    for ($i = 0; $i < $num; ++$i) {
        $cats2 = mysqli_fetch_assoc($res2);
        $catdropdown2.= "<option value='" . $cats2['id'] . "'";
        $catdropdown2.= ">" . htmlspecialchars($cats2['name']) . "</option>\n";
    }
    $HTMLOUT.= $catdropdown2 . " </select></td></tr>
<tr><td align='right' valign='top'><b>Image</b></td>
<td align='left'>
<input type='text' name='picture' size='80' /><br />
(Direct link to image, NO TAGS NEEDED! Will be shown in description)<br />
<!--
<a href='panel.php?tool=bitbucket' rel='external'><strong>Upload Image</strong></a>
-->
</td></tr>

<tr><td align='right'><b>Description</b></td><td align='left'>\n";
    if ($INSTALLER09['textbbcode']) {
        require_once (INCL_DIR . 'bbcode_functions.php');
        $HTMLOUT.= textbbcode('add_offer', 'body', '');
    } else $HTMLOUT.= "<textarea name='body' rows='20' cols='80'></textarea>";
    $HTMLOUT.= "</td></tr>
<tr><td align='center' colspan='2'>
<input type='submit' value='Okay' class='btn' /></td></tr></table>
</form>
<br /><br />\n";
}
$rescount = sql_query('SELECT id FROM offers LIMIT 1') or sqlerr(__FILE__, __LINE__);
if (mysqli_num_rows($rescount) > 0) {
    $res = sql_query("SELECT users.username, offers.id, offers.userid, offers.cat, offers.offer, offers.added, categories.name, categories.image, uploaded, downloaded FROM users inner join offers ON offers.userid = users.id left join categories ON offers.cat = categories.id order by offers.id desc LIMIT 10") or sqlerr();
    $num = mysqli_num_rows($res);
    $HTMLOUT.= "<table border='1' cellspacing='0' width='750px' cellpadding='5'>
    <tr><td width='50px' class='colhead' align='left'>Category</td>
    <td class='colhead' align='left'>Offer</td><td class='colhead' align='center'>Added</td>
    <td class='colhead' align='center'>Offered By</td></tr>\n";
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
    <td align='left'><a href='viewoffers.php?id=$arr[id]&amp;offer_details'>
    <b>" . htmlspecialchars($arr['offer']) . "</b></a></td>
    <td align='center'>" . get_date($arr['added'], '') . "</td>
    $addedby
    </tr>\n";
    }
    $HTMLOUT.= "<tr><td align='center' colspan='4'>
<form method='get' action='viewoffers.php'>
<input type='submit' value='Show All' class='btn' />
</form>
</td></tr>
</table>\n";
}
/////////////////////// HTML OUTPUT //////////////////////////////
echo stdhead('Offers Page') . $HTMLOUT . stdfoot();
?>
