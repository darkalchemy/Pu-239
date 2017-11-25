<?php
/**
 * @param        $rows
 * @param string $variant
 *
 * @return string
 */
function commenttable($rows, $variant = 'torrent')
{
    require_once INCL_DIR . 'html_functions.php';
    require_once INCL_DIR . 'add_functions.php';
    global $CURUSER, $site_config, $mood, $cache;
    $lang = load_language('torrenttable_functions');
    $htmlout = '';
    $count = 0;
    $variant_options = [
        'torrent' => 'details',
        'request' => 'viewrequests',
    ];
    if (isset($variant_options[ $variant ])) {
        $locale_link = $variant_options[ $variant ];
    } else {
        return;
    }
    $extra_link = ($variant == 'request' ? '&type=request' : ($variant == 'offer' ? '&type=offer' : ''));
    foreach ($rows as $row) {
        $moodname = (isset($mood['name'][ $row['mood'] ]) ? htmlsafechars($mood['name'][ $row['mood'] ]) : 'is feeling neutral');
        $moodpic = (isset($mood['image'][ $row['mood'] ]) ? htmlsafechars($mood['image'][ $row['mood'] ]) : 'noexpression.gif');
        $htmlout .= "<div class='top20'><span>#{$row['id']} {$lang['commenttable_by']} ";
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
        if (isset($row['username'])) {
            if ($row['anonymous'] == 'yes') {
                $htmlout .= ($CURUSER['class'] >= UC_STAFF ? 'Anonymous - Posted by: <b>' . htmlsafechars($row['username']) . '</b> ID: ' . (int)$row['user'] . '' : 'Anonymous') . ' ';
            } else {
                $title = $row['title'];
                if ($title == '') {
                    $title = get_user_class_name($row['class']);
                } else {
                    $title = htmlsafechars($title);
                }
                $username = htmlsafechars($row['username']);
                $avatar1 = ($row['anonymous'] == 'yes' ? "<img src='{$site_config['pic_base_url']}anonymous_1.jpg' alt='Avatar' title='Avatar' class='avatar' />" : "<img src='" . htmlsafechars($row['avatar']) . "' alt='Avatar' title='Avatar' class='avatar' />");
                if (!$avatar1) {
                    $avatar1 = "{$site_config['pic_base_url']}forumicons/default_avatar.gif";
                }
                $htmlout .= format_username($row['user']);
                $htmlout .= '
                <a href="javascript:;" onclick="PopUp(\'usermood.php\',\'Mood\',530,500,1,1);">
                    <img src="' . $site_config['pic_base_url'] . 'smilies/' . $moodpic . '" alt="' . $moodname . '" class="tooltipper" title="' . ($row['anonymous'] == 'yes' ? '<i>Anonymous</i>' : htmlsafechars($row['username'])) . ' ' . $moodname . '!" />
                </a>';
            }
        } else {
            $htmlout .= "<a name='comm" . (int)$row['id'] . "'><i>(" . $lang['commenttable_orphaned'] . ")</i></a>\n";
        }
        $htmlout .= get_date($row['added'], '');
        $htmlout .= ($row['user'] == $CURUSER['id'] || $CURUSER['class'] >= UC_STAFF ? "- [<a href='comment.php?action=edit&amp;cid=" . (int)$row['id'] . $extra_link . '&amp;tid=' . $row[ $variant ] . "'>" . $lang['commenttable_edit'] . '</a>]' : '') . ($CURUSER['class'] >= UC_VIP ? " - [<a href='report.php?type=Comment&amp;id=" . (int)$row['id'] . "'>Report this Comment</a>]" : '') . ($CURUSER['class'] >= UC_STAFF ? " - [<a href='comment.php?action=delete&amp;cid=" . (int)$row['id'] . $extra_link . '&amp;tid=' . $row[ $variant ] . "'>" . $lang['commenttable_delete'] . '</a>]' : '') . ($row['editedby'] && $CURUSER['class'] >= UC_STAFF ? "- [<a href='comment.php?action=vieworiginal&amp;cid=" . (int)$row['id'] . $extra_link . '&amp;tid=' . $row[ $variant ] . "'>" . $lang['commenttable_view_original'] . '</a>]' : '') . "

          <span id='mlike' data-com='" . (int)$row['id'] . "' class='comment {$wht}'>[" . ucfirst($wht) . "]</span><span class='tot-" . (int)$row['id'] . "' data-tot='" . (!empty($likes) && count(array_unique($likes)) > 0 ? count(array_unique($likes)) : '') . "'>&#160;{$att_str}</span></span></div>\n";
        $avatar = ($row['anonymous'] == 'yes' ? "{$site_config['pic_base_url']}anonymous_1.jpg" : htmlsafechars($row['avatar']));
        if (!$avatar) {
            $avatar = "{$site_config['pic_base_url']}forumicons/default_avatar.gif";
        }
        $text = format_comment($row['text']);
        if ($row['editedby']) {
            $text .= "<p><font size='1' class='small'>" . $lang['commenttable_last_edited_by'] . " <a href='userdetails.php?id=" . (int)$row['editedby'] . "'>" . format_username($row['editby']) . '</a> ' . $lang['commenttable_last_edited_at'] . ' ' . get_date($row['editedat'], 'DATE') . "</font></p>\n";
        }
        $htmlout .= begin_table(false);

        $htmlout .= "<tr>\n";
        $htmlout .= "<td width='150' style='padding: 0px'><img src='{$avatar}' alt='Avatar' class='avatar' /><br>" . get_reputation($row, 'comments') . "</td>\n";
        $htmlout .= "<td class='text'>$text</td>\n";
        $htmlout .= "</tr>\n";
        $htmlout .= end_table();
    }

    return $htmlout;
}
