<?php
global $CURUSER, $site_config;

if ($CURUSER['class'] < $site_config['offer_min_class']) {
    $HTMLOUT .= "<h1>Oops!</h1>
    <div class='some class'>You must be " . get_user_class_name($site_config['offer_min_class']) . ' or above <b>AND</b> have a ratio above <b>' . $site_config['offer_min_ratio'] . "</b> to make an offer.
    <br><br> Please see the <a href='faq.php'><b>FAQ</b></a> 
    for more information on different user classes and what they can do.<br><br>
    <b>" . $site_config['site_name'] . ' staff</b></div>';
    /////////////////////// HTML OUTPUT //////////////////////////////
    echo stdhead('Offers Page') . $HTMLOUT . stdfoot();
    die();
}
$gigsneeded = ($site_config['offer_gigs_upped'] * 1024 * 1024 * 1024);
$gigsupped = $CURUSER['uploaded'];
$ratio = (($CURUSER['downloaded'] > 0) ? ($CURUSER['uploaded'] / $CURUSER['downloaded']) : 0);
if ($CURUSER['class'] < UC_VIP) {
    $gigsdowned = $CURUSER['downloaded'];
    if ($gigsdowned >= $gigsneeded) {
        $gigs = $CURUSER['uploaded'] / (1024 * 1024 * 1024);
    }
}
$HTMLOUT .= '<h3>Offer Rules</h3>';
$HTMLOUT .= 'To make an offer you must have a ratio of at least<b> ' . $site_config['offer_min_ratio'] . '</b> AND have uploaded at least <b>' . $site_config['offer_gigs_upped'] . ' GB</b>.<br>' . ($site_config['karma'] ? " A offer will also cost you <b><a class='altlink' href='mybonus.php'>" . $site_config['offer_cost_bonus'] . ' Karma Points</a></b>....<br><br>' : '') . " 
In your particular case <a class='altlink' href='userdetails.php?id=" . $CURUSER['id'] . "'>" . $CURUSER['username'] . '</a>, ';
if ($site_config['karma'] && isset($CURUSER['seedbonus']) && $CURUSER['seedbonus'] < $site_config['offer_cost_bonus']) {
    $HTMLOUT .= "you do not have enough <a class='altlink' href='mybonus.php'>Karma Points</a> ...
        you can not make offers.<p>To view all offers, click 
        <a class='altlink' href='viewoffers.php'><b>here</b></a></p>\n<br><br>";
} elseif ($gigsupped < $gigsneeded && $CURUSER['class'] < UC_VIP) {
    $HTMLOUT .= 'you have <b>not</b> yet uploaded <b>' . $site_config['offer_gigs_upped'] . " GB</b>... you can not make offers.<p>
    To view all offers, click <a class='altlink' href='viewoffers.php'><b>here</b></a></p>\n
    <br><br>";
} elseif ($ratio < $site_config['offer_min_ratio'] && $CURUSER['class'] < UC_VIP) {
    $sss = ($gigsupped < $gigsneeded ? 's' : '');
    $HTMLOUT .= 'your ratio of <b>' . member_ratio($CURUSER['uploaded'], $CURUSER['downloaded']) . '</b>' . ($gigsupped < $gigsneeded ? ' and your total uploaded of<b> ' . round($gigs, 2) . ' GB</b>' : '') . " fail$sss to meet the minimum requirements. to Make a Offer.<br><br>
         <p>To view all offers, click <a href='viewoffers.php'><b>here</b></a></p>\n<br><br>";
} else {
    $HTMLOUT .= "you <b>can</b> make offers.<p>To view all offers, click 
    <a class='altlink' href='viewoffers.php'>here</a></p>\n";
    /* search first **/
    $HTMLOUT .= "<form method='get' action='browse.php'><table width='750px' ><tr><td class='colhead'>
Please search torrents before adding an offer!</td></tr><tr><td>
<input type='text' name='search' size='40' value='' class='button is-small' /> in <select name='cat'> <option value='0'>(all types)</option>
";
    $catdropdown = '';
    foreach ($cats as $cat) {
        $catdropdown .= "<option value='" . $cat['id'] . "'";
        if ($cat['id'] == (isset($_GET['cat']) ? $_GET['cat'] : '')) {
            $catdropdown .= " selected";
        }
        $catdropdown .= '>' . htmlspecialchars($cat['name']) . "</option>\n";
    }
    $deadchkbox = "<input type='checkbox' name='incldead' value='1'";
    if (isset($_GET['incldead'])) {
        $deadchkbox .= " checked";
    }
    $deadchkbox .= " /> including dead torrents\n";
    $HTMLOUT .= ' ' . $catdropdown . ' </select> ' . $deadchkbox . " 
<input type='submit' value='Search!' class='button is-small' /></td></tr></table></form>
<br>\n";
    $HTMLOUT .= "<form method='post' name='compose' action='viewoffers.php?new_offer'><a name='add' id='add'></a>
<table border='1' cellspacing='0' width='750px' cellpadding='5'><tr><td class='colhead' colspan='2'>
Offers are for Users with a good ratio who have uploaded at least " . $site_config['offer_gigs_upped'] . " gigs Only... Share and you shall recieve!</td></tr>
<tr><td><b>Title</b></td><td><input type='text' size='40' name='offertitle' />
<select name='category'><option value='0'>(Select a Category)</option>\n";
    $res2 = sql_query('SELECT id, name FROM categories ORDER BY name');
    $num = mysqli_num_rows($res2);
    $catdropdown2 = '';
    for ($i = 0; $i < $num; ++$i) {
        $cats2 = mysqli_fetch_assoc($res2);
        $catdropdown2 .= "<option value='" . $cats2['id'] . "'";
        $catdropdown2 .= '>' . htmlspecialchars($cats2['name']) . "</option>\n";
    }
    $HTMLOUT .= $catdropdown2 . " </select></td></tr>
<tr><td><b>Image</b></td>
<td>
<input type='text' name='picture' size='80' /><br>
(Direct link to image, NO TAGS NEEDED! Will be shown in description)<br>
<!--
<a href='panel.php?tool=bitbucket' rel='external'><strong>Upload Image</strong></a>
-->
</td></tr>

<tr><td><b>Description</b></td><td>\n";
    if ($site_config['textbbcode']) {
        require_once INCL_DIR . 'bbcode_functions.php';
        $HTMLOUT .= textbbcode('add_offer', 'body', '');
    } else {
        $HTMLOUT .= "<textarea name='body' rows='20' cols='80'></textarea>";
    }
    $HTMLOUT .= "</td></tr>
<tr><td colspan='2'>
<input type='submit' value='Okay' class='button is-small' /></td></tr></table>
</form>
<br><br>\n";
}
$rescount = sql_query('SELECT id FROM offers LIMIT 1') or sqlerr(__FILE__, __LINE__);
if (mysqli_num_rows($rescount) > 0) {
    $res = sql_query('SELECT users.username, offers.id, offers.offered_by_user_id, offers.category, offers.offer_name, offers.added, categories.name, categories.image, uploaded, downloaded FROM users INNER JOIN offers ON offers.offered_by_user_id = users.id LEFT JOIN categories ON offers.category = categories.id ORDER BY offers.id DESC LIMIT 10') or sqlerr(__FILE__, __LINE__);
    $num = mysqli_num_rows($res);
    $HTMLOUT .= "<table border='1' cellspacing='0' width='750px' cellpadding='5'>
    <tr><td width='50px' class='colhead'>Category</td>
    <td class='colhead'>Offer</td><td class='colhead'>Added</td>
    <td class='colhead'>Offered By</td></tr>\n";
    foreach ($cats as $key => $value) {
        $change[$value['id']] = [
            'id'    => $value['id'],
            'name'  => $value['name'],
            'image' => $value['image'],
        ];
    }
    while ($arr = mysqli_fetch_assoc($res)) {
        $addedby = "<td style='padding: 0;'><b><a href='userdetails.php?id=$arr[offered_by_user_id]'>$arr[username]</a></b></td>";
        $catname = htmlspecialchars($change[$arr['cat']]['name']);
        $catpic = htmlspecialchars($change[$arr['cat']]['image']);
        $catimage = "<img src='{$site_config['pic_baseurl']}caticons/" . $catpic . "' title='$catname' alt='$catname' />";
        $HTMLOUT .= "<tr>
    <td>" . $catimage . "</td>
    <td><a href='viewoffers.php?id=$arr[id]&amp;offer_details'>
    <b>" . htmlspecialchars($arr['offer_name']) . "</b></a></td>
    <td>" . get_date($arr['added'], '') . "</td>
    $addedby
    </tr>\n";
    }
    $HTMLOUT .= "<tr><td colspan='4'>
<form method='get' action='viewoffers.php'>
<input type='submit' value='Show All' class='button is-small' />
</form>
</td></tr>
</table>\n";
}
echo stdhead('Offers Page') . $HTMLOUT . stdfoot();
