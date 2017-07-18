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
function commenttable($rows, $variant = 'torrent')
{
    require_once (INCL_DIR . 'html_functions.php');
    require_once (INCL_DIR . 'add_functions.php');
    global $CURUSER, $INSTALLER09, $mood, $mc1;
    $lang = load_language('torrenttable_functions');
    $htmlout = '';
    $count = 0;
    $variant_options = array(
        'torrent' => 'details',
        'request' => 'viewrequests'
    );
    if (isset($variant_options[$variant])) $locale_link = $variant_options[$variant];
    else return;
    $extra_link = ($variant == 'request' ? '&type=request' : ($variant == 'offer' ? '&type=offer' : ''));
    $htmlout.= begin_main_frame();
    $htmlout.= begin_frame();
    foreach ($rows as $row) {
        $moodname = (isset($mood['name'][$row['mood']]) ? htmlsafechars($mood['name'][$row['mood']]) : 'is feeling neutral');
        $moodpic = (isset($mood['image'][$row['mood']]) ? htmlsafechars($mood['image'][$row['mood']]) : 'noexpression.gif');
        $htmlout.= "<p class='sub'>#{$row["id"]} {$lang["commenttable_by"]} ";
        // --------------- likes start------
        $att_str = '';
        if (!empty($row['user_likes'])) {
            $likes = explode(',', $row['user_likes']);
        } else {
            $likes = '';
        }
        if (!empty($likes) && count(array_unique($likes)) > 0) {
            if (in_array($CURUSER['id'], $likes)) {
                if (count($likes) == 1) {
                    $att_str = jq('You like this');
                } elseif (count(array_unique($likes)) > 1) {
                    $att_str = jq('You and ') . ((count(array_unique($likes)) - 1) == '1' ? '1 other person likes this' : (count($likes) - 1) . 'others like this');
                }
            } elseif (!(in_array($CURUSER['id'], $likes))) {
                if (count(array_unique($likes)) == 1) {
                    $att_str = '1 other person likes this';
                } elseif (count(array_unique($likes)) > 1) {
                    $att_str = (count(array_unique($likes))) . ' others like this';
                }
            }
        }
        $wht = ((!empty($likes) && count(array_unique($likes)) > 0 && in_array($CURUSER['id'], $likes)) ? 'unlike' : 'like');
        // --------------- likes end------
        if (isset($row["username"])) {
            if ($row['anonymous'] == 'yes') {
                //$htmlout.= '/*<![CDATA[*/';
                $htmlout.= ($CURUSER['class'] >= UC_STAFF ? 'Anonymous - Posted by: <b>' . htmlsafechars($row['username']) . '</b> ID: ' . (int)$row['user'] . '' : 'Anonymous') . ' ';
            } else {
                $title = $row["title"];
                if ($title == "") $title = get_user_class_name($row["class"]);
                else $title = htmlsafechars($title);
                $username = htmlsafechars($row['username']);
                $avatar1 = ($row['anonymous'] == 'yes' ? "<img src=\'{$INSTALLER09['pic_base_url']}anonymous_1.jpg\' width=\'150\' height=\'150\' border=\'0\' alt=\'Avatar\' title=\'Avatar\' />" : "<img src=\'" . htmlsafechars($row['avatar']) . "\' width=\'150\' height=\'150\' border=\'0\' alt=\'Avatar\' title=\'Avatar\' />");
                if (!$avatar1) $avatar1 = "{$INSTALLER09['pic_base_url']}default_avatar.gif";
                $htmlout.= "<a name='comm" . (int)$row["id"] . "' onmouseover=\"Tip('<b>$username</b><br />$avatar1');\" onmouseout=\"UnTip();\" href='userdetails.php?id=" . (int)$row["user"] . "'><b>" . htmlsafechars($row["username"]) . "</b></a>" . ($row["donor"] == "yes" ? "<img src='{$INSTALLER09['pic_base_url']}star.gif' alt='" . $lang["commenttable_donor_alt"] . "' />" : "") . ($row["warned"] == "yes" ? "<img src='{$INSTALLER09['pic_base_url']}warned.gif' alt='" . $lang["commenttable_warned_alt"] . "' />" : "") . " ($title)\n";
                $htmlout.= '<a href="javascript:;" onclick="PopUp(\'usermood.php\',\'Mood\',530,500,1,1);">
    <span class="tool"><img src="' . $INSTALLER09['pic_base_url'] . 'smilies/' . $moodpic . '" alt="' . $moodname . '" border="0" />
    <span class="tip">' . ($row['anonymous'] == 'yes' ? '<i>Anonymous</i>' : htmlsafechars($row['username'])) . ' ' . $moodname . ' !</span></span></a>';
            }
        } else $htmlout.= "<a name='comm" . (int)$row["id"] . "'><i>(" . $lang["commenttable_orphaned"] . ")</i></a>\n";
        $htmlout.= get_date($row['added'], '');
        $htmlout.= ($row["user"] == $CURUSER["id"] || $CURUSER["class"] >= UC_STAFF ? "- [<a href='comment.php?action=edit&amp;cid=" . (int)$row['id'] . $extra_link . "&amp;tid=" . $row[$variant] . "'>" . $lang["commenttable_edit"] . "</a>]" : "") . ($CURUSER["class"] >= UC_VIP ? " - [<a href='report.php?type=Comment&amp;id=" . (int)$row['id'] . "'>Report this Comment</a>]" : "") . ($CURUSER["class"] >= UC_STAFF ? " - [<a href='comment.php?action=delete&amp;cid=" . (int)$row['id'] . $extra_link . "&amp;tid=" . $row[$variant] . "'>" . $lang["commenttable_delete"] . "</a>]" : "") . ($row["editedby"] && $CURUSER["class"] >= UC_STAFF ? "- [<a href='comment.php?action=vieworiginal&amp;cid=" . (int)$row['id'] . $extra_link . "&amp;tid=" . $row[$variant] . "'>" . $lang["commenttable_view_original"] . "</a>]" : "") . "
		
		  <span id='mlike' data-com='" . (int)$row["id"] . "' class='comment {$wht}'>[" . ucfirst($wht) . "]</span><span class='tot-" . (int)$row["id"] . "' data-tot='" . (!empty($likes) && count(array_unique($likes)) > 0 ? count(array_unique($likes)) : '') . "'>&nbsp;{$att_str}</span></p>\n";
        $avatar = ($row['anonymous'] == "yes" ? "{$INSTALLER09['pic_base_url']}anonymous_1.jpg" : htmlsafechars($row["avatar"]));
        if (!$avatar) $avatar = "{$INSTALLER09['pic_base_url']}default_avatar.gif";
        $text = format_comment($row["text"]);
        if ($row["editedby"]) $text.= "<p><font size='1' class='small'>" . $lang["commenttable_last_edited_by"] . " <a href='userdetails.php?id=" . (int)$row['editedby'] . "'><b>" . htmlsafechars($row['edit_name']) . "</b></a> " . $lang["commenttable_last_edited_at"] . " " . get_date($row['editedat'], 'DATE') . "</font></p>\n";
        $htmlout.= begin_table(true);
        
        $htmlout.= "<tr valign='top'>\n";
        $htmlout.= "<td align='center' width='150' style='padding: 0px'><img width='150' height='150' src='{$avatar}' alt='' /><br />" . get_reputation($row, 'comments') . "</td>\n";
        $htmlout.= "<td class='text'>$text</td>\n";
        $htmlout.= "</tr>\n";
        $htmlout.= end_table();
        //$htmlout.= '/*]]>*/';
    }
    $htmlout.= end_frame();
    $htmlout.= end_main_frame();
    return $htmlout;
}
?>
