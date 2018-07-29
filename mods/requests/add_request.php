<?php

global $CURUSER, $site_config, $lang;

if ($CURUSER['class'] < $site_config['req_min_class']) {
    $HTMLOUT .= "<h1>Oops!</h1>
    <div class='some class'>{$lang['add_must_be']}" . get_user_class_name($site_config['req_min_class']) . "{$lang['add_ratio_above']}" . $site_config['req_min_ratio'] . "{$lang['add_make_req']}
    <br><br>{$lang['add_faq']}<br><br>
    <b>" . $site_config['site_name'] . ' staff</b></div>';
    /////////////////////// HTML OUTPUT //////////////////////////////
    echo stdhead('Requests Page') . $HTMLOUT . stdfoot();
    die();
}
$gigsneeded = ($site_config['req_gigs_upped'] * 1024 * 1024 * 1024);
$gigsupped = $CURUSER['uploaded'];
$ratio = (($CURUSER['downloaded'] > 0) ? ($CURUSER['uploaded'] / $CURUSER['downloaded']) : 0);
if ($CURUSER['class'] < UC_VIP) {
    $gigsdowned = $CURUSER['downloaded'];
    if ($gigsdowned >= $gigsneeded) {
        $gigs = $CURUSER['uploaded'] / (1024 * 1024 * 1024);
    }
}
$HTMLOUT .= "<h3>{$lang['add_rules']}</h3>";
$HTMLOUT .= "{$lang['add_rules1']}<b> " . $site_config['req_min_ratio'] . "</b>{$lang['add_rules2']}<b>" . $site_config['req_gigs_upped'] . ' GB</b>.<br>' . ($site_config['karma'] ? "{$lang['add_rules3']}<b><a class='altlink' href='mybonus.php'>" . $site_config['req_cost_bonus'] . ' Karma Points</a></b>....<br><br>' : '') . " 
{$lang['add_rules4']}" . format_username($CURUSER['id']) . ', ';
if ($site_config['karma'] && isset($CURUSER['seedbonus']) && $CURUSER['seedbonus'] < $site_config['req_cost_bonus']) {
    $HTMLOUT .= "{$lang['add_rules7']}<a class='altlink' href='mybonus.php'>Karma Points</a> ...
        {$lang['add_rules8']}<p>{$lang['add_rules9']}
        <a class='altlink' href='viewrequests.php'><b>{$lang['add_rules6']}</b></a></p>\n<br><br>";
} elseif ($gigsupped < $gigsneeded && $CURUSER['class'] < UC_VIP) {
    $HTMLOUT .= "{$lang['add_rules10']}<b>" . $site_config['req_gigs_upped'] . " GB</b>{$lang['add_rules11']}<p>
    {$lang['add_rules9']}<a class='altlink' href='viewrequests.php'><b>{$lang['add_rules6']}</b></a></p>\n
    <br><br>";
} elseif ($ratio < $site_config['req_min_ratio'] && $CURUSER['class'] < UC_VIP) {
    $sss = ($gigsupped < $gigsneeded ? 's' : '');
    $HTMLOUT .= "{$lang['add_rules15']}<b>" . member_ratio($CURUSER['uploaded'], $CURUSER['downloaded']) . '</b>' . ($gigsupped < $gigsneeded ? "{$lang['add_rules12']}<b> " . round($gigs, 2) . ' GB</b>' : '') . " {$lang['add_rules13']}$sss{$lang['add_rules14']}<br><br>
         <p>{$lang['add_rules9']}<a href='viewrequests.php'><b>{$lang['add_rules6']}</b></a></p>\n<br><br>";
} else {
    $HTMLOUT .= "{$lang['add_rules5']} 
    <a class='altlink' href='viewrequests.php'>{$lang['add_rules6']}</a></p>\n";
    /* search first **/
    $HTMLOUT .= "<form method='get' action='browse.php'><table width='750px' ><tr><td class='colhead'>
{$lang['add_search_before']}</td></tr><tr><td>
<input type='text' name='search' size='40' value='' class='button is-small' />{$lang['add_in']}<select name='cat'> <option value='0'>{$lang['add_all_types']}</option>
";
    $catdropdown = '';
    foreach ($cats as $cat) {
        $catdropdown .= "<option value='" . $cat['id'] . "'";
        if ($cat['id'] == (isset($_GET['cat']) ? $_GET['cat'] : '')) {
            $catdropdown .= ' selected';
        }
        $catdropdown .= '>' . htmlspecialchars($cat['name']) . "</option>\n";
    }
    $deadchkbox = "<input type='checkbox' name='incldead' value='1'";
    if (isset($_GET['incldead'])) {
        $deadchkbox .= ' checked';
    }
    $deadchkbox .= " />{$lang['add_incl_dead']}\n";
    $HTMLOUT .= ' ' . $catdropdown . ' </select> ' . $deadchkbox . " 
<input type='submit' value='{$lang['req_search']}' class='button is-small' /></td></tr></table></form>
<br>\n";
    $HTMLOUT .= "<form method='post' name='compose' action='viewrequests.php?new_request'><a id='add'></a>
<table width='750px'><tr><td class='colhead' colspan='2'>
{$lang['add_good_ratio']}" . $site_config['req_gigs_upped'] . "{$lang['add_share']}</td></tr>
<tr><td><b>{$lang['add_title']}</b></td><td><input type='text' size='40' name='requesttitle' />
<select name='category'><option value='0'>{$lang['add_select_cat']}</option>\n";
    $res2 = sql_query('SELECT id, name FROM categories ORDER BY name');
    $num = mysqli_num_rows($res2);
    $catdropdown2 = '';
    for ($i = 0; $i < $num; ++$i) {
        $cats2 = mysqli_fetch_assoc($res2);
        $catdropdown2 .= "<option value='" . $cats2['id'] . "'";
        $catdropdown2 .= '>' . htmlspecialchars($cats2['name']) . "</option>\n";
    }
    $HTMLOUT .= $catdropdown2 . " </select></td></tr>
<tr><td><b>{$lang['add_image']}</b></td>
<td>
<input type='text' name='picture' size='80' /><br>
{$lang['add_direct_link']}<br>
<!--
<a href='panel.php?tool=bitbucket' rel='external'><strong>{$lang['add_upload_image']}</strong></a>
-->
</td></tr>

<tr><td><b>{$lang['add_description']}</b></td><td>\n";
    if ($site_config['textbbcode']) {
        require_once INCL_DIR . 'bbcode_functions.php';
        $HTMLOUT .= textbbcode('add_request', 'body', '');
    } else {
        $HTMLOUT .= "<textarea name='body' rows='20' cols='80'></textarea>";
    }
    $HTMLOUT .= "</td></tr>
<tr><td colspan='2'>
<input type='submit' value='{$lang['add_ok']}' class='button is-small' /></td></tr></table>
</form>
<br><br>\n";
}
$rescount = sql_query('SELECT id FROM requests LIMIT 1') or sqlerr(__FILE__, __LINE__);
if (mysqli_num_rows($rescount) > 0) {
    $res = sql_query('SELECT users.username, requests.id, requests.requested_by_user_id, requests.category, requests.request, requests.added, categories.name, categories.image, uploaded, downloaded FROM users INNER JOIN requests ON requests.requested_by_user_id = users.id LEFT JOIN categories ON requests.category = categories.id ORDER BY requests.id DESC LIMIT 10') or sqlerr(__FILE__, __LINE__);
    $num = mysqli_num_rows($res);
    $HTMLOUT .= "<table width='750px'>
    <tr><td width='50px' class='colhead'>{$lang['add_cat']}</td>
    <td class='colhead'>{$lang['add_request']}</td><td class='colhead'>{$lang['req_added']}</td>
    <td class='colhead'>{$lang['req_req_by']}</td></tr>\n";
    foreach ($cats as $key => $value) {
        $change[$value['id']] = [
            'id' => $value['id'],
            'name' => $value['name'],
            'image' => $value['image'],
        ];
    }
    while ($arr = mysqli_fetch_assoc($res)) {
        $addedby = "<td style='padding: 0;'>" . get_username($arr[requested_by_user_id]) . '</td>';
        $catname = htmlspecialchars($change[$arr['category']]['name']);
        $catpic = htmlspecialchars($change[$arr['category']]['image']);
        $catimage = "<img src='{$site_config['pic_baseurl']}caticons/" . $catpic . "' title='$catname' alt='$catname' />";
        $HTMLOUT .= '<tr>
    <td>' . $catimage . "</td>
    <td><a href='viewrequests.php?id=$arr[id]&amp;req_details'>
    <b>" . htmlspecialchars($arr['request']) . '</b></a></td>
    <td>' . get_date($arr['added'], '') . "</td>
    $addedby
    </tr>\n";
    }
    $HTMLOUT .= "<tr><td colspan='4'>
<form method='get' action='viewrequests.php'>
<input type='submit' value='{$lang['req_show_all']}' class='button is-small' />
</form>
</td></tr>
</table>\n";
}
echo stdhead('Requests Page') . $HTMLOUT . stdfoot();
