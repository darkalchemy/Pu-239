<?php
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
require_once (dirname(__FILE__) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php');
require_once (INCL_DIR . 'user_functions.php');
require_once INCL_DIR . 'torrenttable_functions.php';
require_once INCL_DIR . 'pager_functions.php';
require_once (INCL_DIR . 'searchcloud_functions.php');
require_once (CLASS_DIR . 'class_user_options.php');
require_once (CLASS_DIR . 'class_user_options_2.php');
dbconn(false);
loggedinorreturn();
if (isset($_GET['clear_new']) && $_GET['clear_new'] == 1) {
    sql_query("UPDATE users SET last_browse=" . TIME_NOW . " WHERE id=" . sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);
    $mc1->begin_transaction('MyUser_' . $CURUSER['id']);
    $mc1->update_row(false, array(
        'last_browse' => TIME_NOW
    ));
    $mc1->commit_transaction($INSTALLER09['expires']['curuser']);
    $mc1->begin_transaction('user' . $CURUSER['id']);
    $mc1->update_row(false, array(
        'last_browse' => TIME_NOW
    ));
    $mc1->commit_transaction($INSTALLER09['expires']['user_cache']);
    header("Location: {$INSTALLER09['baseurl']}/browse.php");
}
$stdfoot = array(
    /** include js **/
    'js' => array(
        'java_klappe',
        'wz_tooltip'
    )
);
$stdhead = array(
    /** include css **/
    'css' => array(
        'browse'
    )
);
$lang = array_merge(load_language('global') , load_language('browse') , load_language('torrenttable_functions'));
if (function_exists('parked')) parked();
$HTMLOUT = $searchin = $select_searchin = $where = $addparam = $new_button = $search_help_boolean = $vip_box = $only_free = '';
$HTMLOUT .='<script type="text/javascript">
/*<![CDATA[*/
$(document).ready(function(){
	$("#help_open").click(function(){
	$("#help").slideToggle("slow", function() {
	});
	});
})
/*]]>*/
</script>';
$search_help_boolean = '<table border="0" cellspacing="0" cellpadding="0" style="max-width:600px;" align="center">
    <tr>
    <td class="two"><div id="help" style="display:none"><h1>The boolean search supports the following operators:</h1>
    <span style="font-weight: bold;">+</span> A leading plus sign indicates that this word must be present.<br /><br />
    <span style="font-weight: bold;">-</span> A leading minus sign indicates that this word must not be present.<br /><br />
        By default (when neither + nor - is specified) the word is optional, but results that contain it are rated higher. <br /><br />
    <span style="font-weight: bold;">*</span> The asterisk serves as the wildcard operator. Unlike the other operators, it should be appended to the word to be affected. Words match if they begin with the word preceding the * operator.<br /><br />
    <span style="font-weight: bold;">> <</span> These two operators are used to change a word\'s contribution to the relevance value that is assigned to a word. The > operator increases the contribution and the < operator decreases it.<br /><br />
    <span style="font-weight: bold;">~</span> A leading tilde acts as a negation operator, causing the word\'s contribution to the words\'s relevance to be negative. A row containing such a word is rated lower than others, but is not excluded altogether, as it would be with the - operator.<br /><br />
    <span style="font-weight: bold;">" "</span> A phrase that is enclosed within double quotes return only results that contain the phrase literally, as it was typed. <br /><br />
    <span style="font-weight: bold;">( )</span> Parentheses group words into subexpressions. Parenthesized groups can be nested.<br /><br /></div></td>
     </tr></table>';
$cats = genrelist();
if (isset($_GET["search"])) {
    $searchstr = unesc($_GET["search"]);
    $cleansearchstr = searchfield($searchstr);
    if (empty($cleansearchstr)) unset($cleansearchstr);
}
$valid_searchin = array(
    'title' => array(
        'name'
    ) ,
    'descr' => array(
        'descr'
    ) ,
    'genre' => array(
        'newgenre'
    ) ,
    'all' => array(
        'name',
        'newgenre',
        'descr'
    )
);
if (isset($_GET['searchin']) && isset($valid_searchin[$_GET['searchin']])) {
    $searchin = $valid_searchin[$_GET['searchin']];
    $select_searchin = $_GET['searchin'];
    $addparam.= sprintf('search=%s&amp;searchin=%s&amp;', $searchstr, $select_searchin);
}
if (isset($_GET['sort']) && isset($_GET['type'])) {
    $column = $ascdesc = '';
    $_valid_sort = array(
        'id',
        'name',
        'numfiles',
        'comments',
        'added',
        'size',
        'times_completed',
        'seeders',
        'leechers',
        'owner'
    );
    $column = isset($_GET['sort']) && isset($_valid_sort[(int)$_GET['sort']]) ? $_valid_sort[(int)$_GET['sort']] : $_valid_sort[0];
    switch (htmlsafechars($_GET['type'])) {
    case 'asc':
        $ascdesc = "ASC";
        $linkascdesc = "asc";
        break;

    case 'desc':
        $ascdesc = "DESC";
        $linkascdesc = "desc";
        break;

    default:
        $ascdesc = "DESC";
        $linkascdesc = "desc";
        break;
    }
    $orderby = "ORDER BY {$column} " . $ascdesc;
    $pagerlink = "sort=" . intval($_GET['sort']) . "&amp;type={$linkascdesc}&amp;";
} else {
    $orderby = "ORDER BY sticky ASC, id DESC";
    $pagerlink = "";
}
$wherea = $wherecatina = array();
if (isset($_GET["incldead"]) && $_GET["incldead"] == 1) {
    $addparam.= "incldead=1&amp;";
    if (!isset($CURUSER) || $CURUSER["class"] < UC_ADMINISTRATOR) $wherea[] = "banned != 'yes'";
} else {
    if (isset($_GET["incldead"]) && $_GET["incldead"] == 2) {
        $addparam.= "incldead=2&amp;";
        $wherea[] = "visible = 'no'";
    } else $wherea[] = "visible = 'yes'";
}
//=== added an only free torrents option \\o\o/o//
if (isset($_GET['only_free']) && $_GET['only_free'] == 1) {
    if (XBT_TRACKER == true ? $wherea[] = "freetorrent >= '1'" : $wherea[] = "free >= '1'");
    //$wherea[] = "free >= '1'";
    $addparam.= "only_free=1&amp;";
}
//=== added an only VIP torrents option - TheGene
if (isset($_GET['vip']) && $_GET['vip'] == 1) {
    $wherea[] = "vip = '1'";
    $addparam.= "vip=1&amp;";
}
$category = (isset($_GET["cat"])) ? (int)$_GET["cat"] : false;
$all = isset($_GET["all"]) ? $_GET["all"] : false;
if (!$all) {
    if (!$_GET && $CURUSER["notifs"]) {
        $all = true;
        foreach ($cats as $cat) {
            $all&= $cat['id'];
            if (strpos($CURUSER["notifs"], "[cat" . $cat['id'] . "]") !== false) {
                $wherecatina[] = $cat['id'];
                $addparam.= "c{$cat['id']}=1&amp;";
            }
        }
    } elseif ($category) {
        if (!is_valid_id($category)) stderr("{$lang['browse_error']}", "{$lang['browse_invalid_cat']}");
        $wherecatina[] = $category;
        $addparam.= "cat=$category&amp;";
    } else {
        $all = true;
        foreach ($cats as $cat) {
            $all&= isset($_GET["c{$cat['id']}"]);
            if (isset($_GET["c{$cat['id']}"])) {
                $wherecatina[] = $cat['id'];
                $addparam.= "c{$cat['id']}=1&amp;";
            }
        }
    }
}
if ($all) {
    $wherecatina = array();
    $addparam = "";
}
if (count($wherecatina) > 1) $wherea[] = 'category IN (' . join(', ', $wherecatina) . ') ';
elseif (count($wherecatina) == 1) $wherea[] = 'category =' . $wherecatina[0];
if (isset($cleansearchstr)) {
    //== boolean search by djgrr
    if ($searchstr != '') {
        $addparam.= 'search=' . rawurlencode($searchstr) . '&amp;searchin='.htmlsafechars($_GET['searchin']).'&amp;incldead='.intval($_GET['incldead']).'&amp;';
        $searchstring = str_replace(array(
            '_',
            '.',
            '-'
        ) , ' ', $searchstr);
        $s = array(
            '*',
            '?',
            '.',
            '-',
            ' '
        );
        $r = array(
            '%',
            '_',
            '_',
            '_',
            '_'
        );
        if (preg_match('/^\"(.+)\"$/i', $searchstring, $matches)) $wherea[] = '`name` LIKE ' . sqlesc('%' . str_replace($s, $r, $matches[1]) . '%');
        elseif (strpos($searchstr, '*') !== false || strpos($searchstr, '?') !== false) $wherea[] = '`name` LIKE ' . sqlesc(str_replace($s, $r, $searchstr));
        elseif (preg_match('/^[A-Za-z0-9][a-zA-Z0-9()._-]+-[A-Za-z0-9_]*[A-Za-z0-9]$/iD', $searchstr)) $wherea[] = '`name` = ' . sqlesc($searchstr);
        else $wherea[] = 'MATCH (`search_text`, `descr`) AGAINST (' . sqlesc($searchstr) . ' IN BOOLEAN MODE)';
        //......
        $searcha = explode(' ', $cleansearchstr);
        //==Memcache search cloud by putyn
        searchcloud_insert($cleansearchstr);
        //==
        foreach ($searcha as $foo) {
            foreach ($searchin as $boo) $searchincrt[] = sprintf('%s LIKE \'%s\'', $boo, '%' . $foo . '%');
        }
        $wherea[] = '( ' . join(' OR ', $searchincrt) . ' )';
    }
}
$where = count($wherea) ? 'WHERE ' . join(' AND ', $wherea) : '';
$where_key = 'where::' . sha1($where);
if (($count = $mc1->get_value($where_key)) === false) {
    $res = sql_query("SELECT COUNT(id) FROM torrents $where") or sqlerr(__FILE__, __LINE__);
    $row = mysqli_fetch_row($res);
    $count = (int)$row[0];
    $mc1->cache_value($where_key, $count, $INSTALLER09['expires']['browse_where']);
}
$torrentsperpage = $CURUSER["torrentsperpage"];
if (!$torrentsperpage) $torrentsperpage = 15;
if ($count) {
    if ($addparam != "") {
        if ($pagerlink != "") {
            if ($addparam{strlen($addparam) - 1} != ";") { // & = &amp;
                $addparam = $addparam . "&" . $pagerlink;
            } else {
                $addparam = $addparam . $pagerlink;
            }
        }
    } else {
        $addparam = $pagerlink;
    }
    $pager = pager($torrentsperpage, $count, "browse.php?" . $addparam);
    $query = "SELECT id, search_text, category, leechers, seeders, bump, release_group, subs, name, times_completed, size, added, poster, descr, type, free, freetorrent, silver, comments, numfiles, filename, anonymous, sticky, nuked, vip, nukereason, newgenre, description, owner, username, youtube, checked_by, IF(nfo <> '', 1, 0) as nfoav," . "IF(num_ratings < {$INSTALLER09['minvotes']}, NULL, ROUND(rating_sum / num_ratings, 1)) AS rating " . "FROM torrents {$where} {$orderby} {$pager['limit']}";
    $res = sql_query($query) or sqlerr(__FILE__, __LINE__);
} else {
    unset($query);
}

if (isset($cleansearchstr)) $title = "{$lang['browse_search']} $searchstr";
else $title = '';
$HTMLOUT.= "<div class='article' align='center'>";
if ($CURUSER['opt1'] & user_options::VIEWSCLOUD) {
    $HTMLOUT.= "<div id='wrapper' style='width:80%;border:1px solid black;background-color:pink;'>";
    //print out the tag cloud
    $HTMLOUT.= cloud() . "
    </div>";
}
$HTMLOUT.= "<br /><br />
    <form method='get' action='browse.php'>
    <table class='bottom'>
    <tr>
    <td class='bottom'>
    <table class='bottom'>
    <tr>";
$i = 0;
foreach ($cats as $cat) {
    $HTMLOUT.= ($i && $i % $INSTALLER09['catsperrow'] == 0) ? "</tr><tr>" : "";
    $HTMLOUT.= "<td class='bottom' style=\"padding-bottom: 2px;padding-left: 7px\">
      <input name='c" . (int)$cat['id'] . "' class=\"styled\" type=\"checkbox\" " . (in_array($cat['id'], $wherecatina) ? "checked='checked' " : "") . "value='1' /><a class='catlink' href='browse.php?cat=" . (int)$cat['id'] . "'> " . (($CURUSER['opt2'] & user_options_2::BROWSE_ICONS) ? "<img src='{$INSTALLER09['pic_base_url']}caticons/{$CURUSER['categorie_icon']}/" . htmlsafechars($cat['image']) . "' alt='" . htmlsafechars($cat['name']) . "' title='" . htmlsafechars($cat['name']) . "' />" : "" . htmlsafechars($cat['name']) . "") . "</a></td>\n";
    $i++;
}
$alllink = "<div align='left'>&nbsp;</div>";
$ncats = count($cats);
$nrows = ceil($ncats / $INSTALLER09['catsperrow']);
$lastrowcols = $ncats % $INSTALLER09['catsperrow'];
if ($lastrowcols != 0) {
    if ($INSTALLER09['catsperrow'] - $lastrowcols != 1) {
        $HTMLOUT.= "<td class='bottom' rowspan='" . ($INSTALLER09['catsperrow'] - $lastrowcols - 1) . "'>&nbsp;</td>";
    }
    $HTMLOUT.= "<td class='bottom' style=\"padding-left: 5px\">$alllink</td>\n";
}
$HTMLOUT.= "</tr>
    </table>
    </td>
    <td class='bottom'>
    <table class='main'>
    <tr><td>&nbsp;</td>";
if ($ncats % $INSTALLER09['catsperrow'] == 0) {
    $HTMLOUT.= "<td class='bottom' style='padding-left: 15px' rowspan='$nrows' valign='middle' align='right'>$alllink</td>\n";
}
$HTMLOUT.= "</tr>
    </table>
    </td>
    </tr>
    </table><br />";
//== clear new tag manually
if ($CURUSER['opt1'] & user_options::CLEAR_NEW_TAG_MANUALLY) {
    $new_button = "<a href='?clear_new=1'><input type='submit' value='clear new tag' class='button' /></a><br />";
} else {
    //== clear new tag automatically
    sql_query("UPDATE users SET last_browse=" . TIME_NOW . " where id=" . $CURUSER['id']);
    $mc1->begin_transaction('MyUser_' . $CURUSER['id']);
    $mc1->update_row(false, array(
        'last_browse' => TIME_NOW
    ));
    $mc1->commit_transaction($INSTALLER09['expires']['curuser']);
    $mc1->begin_transaction('user' . $CURUSER['id']);
    $mc1->update_row(false, array(
        'last_browse' => TIME_NOW
    ));
    $mc1->commit_transaction($INSTALLER09['expires']['user_cache']);
}
$HTMLOUT.= "<br />
    <table width='1000' class='main' border='0' cellspacing='0' cellpadding='0'>
    <tr>
    <td class='colhead' colspan='2' align='center'>Torrent search
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <a class='altlink' title='Open/Close Boolean Search Help' id='help_open' style='font-weight:bold;cursor:help;'>Search help</a></td>
    </tr>
    <tr>
    <td class='two' colspan='2'>".$search_help_boolean."</td>
    </tr>
    <tr><td class='embedded'><br />
    <input type='text' name='search' size='40' value='' />";
//=== only free option :o)
$only_free = ((isset($_GET['only_free'])) ? intval($_GET['only_free']) : '');
//=== only vip option
$vip = ((isset($_GET['vip'])) ? intval($_GET['vip']) : '');
//=== checkbox for only free torrents
$only_free_box = '<input type="checkbox" name="only_free" value="1"'.(isset($_GET['only_free']) ? ' checked="checked"' : '').' /> Only Free Torrents ';
//=== checkbox for only VIP torrents
$vip_box = '<input type="checkbox" name="vip" value="1"'.(isset($_GET['vip']) ? ' checked="checked"' : '').' /> VIP torrents ';
$selected = (isset($_GET["incldead"])) ? (int)$_GET["incldead"] : "";
$deadcheck = "";
$deadcheck.= " in: <select name='incldead'>
    <option value='0'>{$lang['browse_active']}</option>
    <option value='1'" . ($selected == 1 ? " selected='selected'" : "") . ">{$lang['browse_inc_dead']}</option>
    <option value='2'" . ($selected == 2 ? " selected='selected'" : "") . ">{$lang['browse_dead']}</option>
    </select>";
$searchin = ' by: <select name="searchin">';
foreach (array(
    'title' => 'Name',
    'descr' => 'Description',
    'genre' => 'Genre',
    'all' => 'All'
) as $k => $v) $searchin.= '<option value="' . $k . '" ' . ($select_searchin == $k ? 'selected=\'selected\'' : '') . '>' . $v . '</option>';
$searchin.= '</select>';
$HTMLOUT.= $searchin . '&nbsp;' . $deadcheck . '&nbsp;' . $only_free_box.''.$vip_box;
$HTMLOUT.= "<p align='center'><input type='submit' value='{$lang['search_search_btn']}' class='btn' /></p>
            </td></tr></table></form><br />";
$HTMLOUT.= "{$new_button}";
if (isset($cleansearchstr)) {
    $HTMLOUT.= "<h2>{$lang['browse_search']} " . htmlsafechars($searchstr, ENT_QUOTES) . "</h2>\n";
}
if ($count) {
    $HTMLOUT.= $pager['pagertop'];
    $HTMLOUT.= "<br />";
    $HTMLOUT.= torrenttable($res);
    $HTMLOUT.= $pager['pagerbottom'];
} else {
    if (isset($cleansearchstr)) {
        $HTMLOUT.= "<h2>{$lang['browse_not_found']}</h2>\n";
        $HTMLOUT.= "<p>{$lang['browse_tryagain']}</p>\n";
    } else {
        $HTMLOUT.= "<h2>{$lang['browse_nothing']}</h2>\n";
        $HTMLOUT.= "<p>{$lang['browse_sorry']}(</p>\n";
    }
}
$HTMLOUT.= "</div>";
$ip = getip();
//== Start ip logger - Melvinmeow, Mindless, pdq
$no_log_ip = ($CURUSER['perms'] & bt_options::PERMS_NO_IP);
if ($no_log_ip) {
    $ip = '127.0.0.1';
}
if (!$no_log_ip) {
    $userid = (int)$CURUSER['id'];
    $added = TIME_NOW;
    $res = sql_query("SELECT * FROM ips WHERE ip = " . sqlesc($ip) . " AND userid = " . sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
    if (mysqli_num_rows($res) == 0) {
        sql_query("INSERT INTO ips (userid, ip, lastbrowse, type) VALUES (" . sqlesc($userid) . ", " . sqlesc($ip) . ", $added, 'Browse')") or sqlerr(__FILE__, __LINE__);
        $mc1->delete_value('ip_history_' . $userid);
    } else {
        sql_query("UPDATE ips SET lastbrowse = $added WHERE ip=" . sqlesc($ip) . " AND userid = " . sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
        $mc1->delete_value('ip_history_' . $userid);
    }
}
//== End Ip logger
echo stdhead($title, true, $stdhead) . $HTMLOUT . stdfoot($stdfoot);
?>
