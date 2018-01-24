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
    $count = 0;
    $variant_options = [
        'torrent' => 'details',
        'request' => 'viewrequests',
    ];
    if (isset($variant_options[$variant])) {
        $locale_link = $variant_options[$variant];
    } else {
        return;
    }
    $extra_link = ($variant == 'request' ? '&type=request' : ($variant == 'offer' ? '&type=offer' : ''));
    $htmlout = '';
    $i = 0;
    foreach ($rows as $row) {
        $this_text = '';
        $moodname = (isset($mood['name'][$row['mood']]) ? htmlsafechars($mood['name'][$row['mood']]) : 'is feeling neutral');
        $moodpic = (isset($mood['image'][$row['mood']]) ? htmlsafechars($mood['image'][$row['mood']]) : 'noexpression.gif');
        $this_text .= "
            <div class='bottom20'>
                <span class='level-left'>#{$row['id']} {$lang['commenttable_by']} ";
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

        if (isset($row['username'])) {
            if ($row['anonymous'] == 'yes') {
                $this_text .= ($CURUSER['class'] >= UC_STAFF ? 'Anonymous - Posted by: <b>' . htmlsafechars($row['username']) . '</b> ID: ' . (int)$row['user'] . '' : 'Anonymous') . ' ';
            } else {
                $title = $row['title'];
                if ($title == '') {
                    $title = get_user_class_name($row['class']);
                } else {
                    $title = htmlsafechars($title);
                }
                $avatar1 = ($row['anonymous'] == 'yes' ? "<img src='{$site_config['pic_baseurl']}anonymous_1.jpg' alt='Avatar' title='Avatar' class='avatar' />" : "<img src='" . htmlsafechars(image_proxy($row['avatar'])) . "' alt='Avatar' title='Avatar' class='avatar' />");
                if (!$avatar1) {
                    $avatar1 = "{$site_config['pic_baseurl']}forumicons/default_avatar.gif";
                }
                $this_text .= format_username($row['user']);
                $this_text .= '
                    <a href="javascript:;" onclick="PopUp(\'usermood.php\',\'Mood\',530,500,1,1);">
                        <img src="' . $site_config['pic_baseurl'] . 'smilies/' . $moodpic . '" alt="' . $moodname . '" class="tooltipper" title="' . ($row['anonymous'] == 'yes' ? '<i>Anonymous</i>' : htmlsafechars($row['username'])) . ' ' . $moodname . '!" />
                    </a>';
            }
        } else {
            $this_text .= "<a name='comm" . (int)$row['id'] . "'><i>(" . $lang['commenttable_orphaned'] . ")</i></a>\n";
        }
        $this_text .= get_date($row['added'], '');
        $row['id'] = (int)$row['id'];
        $this_text .= ($row['user'] == $CURUSER['id'] || $CURUSER['class'] >= UC_STAFF ? "
                    <a href='{$site_config['baseurl']}/comment.php?action=edit&amp;cid={$row['id']}{$extra_link}&amp;tid={$row[ $variant ]}' class='button is-small is-primary left10'>{$lang['commenttable_edit']}</a>" : '') . ($CURUSER['class'] >= UC_VIP ? "
                    <a href='{$site_config['baseurl']}/report.php?type=Comment&amp;id={$row['id']}' class='button is-small is-primary left10'>Report this Comment</a>" : '') . ($CURUSER['class'] >= UC_STAFF ? "
                    <a href='{$site_config['baseurl']}/comment.php?action=delete&amp;cid={$row['id']}{$extra_link}&amp;tid={$row[ $variant ]}' class='button is-small is-primary left10'>{$lang['commenttable_delete']}</a>" : '') . ($row['editedby'] && $CURUSER['class'] >= UC_STAFF ? "
                    <a href='{$site_config['baseurl']}/comment.php?action=vieworiginal&amp;cid={$row['id']}{$extra_link}&amp;tid={$row[ $variant ]}' class='button is-small is-primary left10'>{$lang['commenttable_view_original']}</a>" : '') . "
                    <span id='mlike' data-com='{$row['id']}' class='comment {$wht} button is-small is-primary left10'>" . ucfirst($wht) . "</span>
                    <span class='tot-{$row['id']}' data-tot='" . (!empty($likes) && count(array_unique($likes)) > 0 ? count(array_unique($likes)) : '') . "'>&#160;{$att_str}</span>
                </span>
            </div>";
        $avatar = ($row['anonymous'] == 'yes' ? "{$site_config['pic_baseurl']}anonymous_1.jpg" : htmlsafechars($row['avatar']));
        if (!$avatar) {
            $avatar = "{$site_config['pic_baseurl']}forumicons/default_avatar.gif";
        }
        $text = format_comment($row['text']);
        if ($row['editedby']) {
            $text .= "<p><font size='1' class='small'>" . $lang['commenttable_last_edited_by'] . " <a href='userdetails.php?id=" . (int)$row['editedby'] . "'>" . format_username($row['editby']) . '</a> ' . $lang['commenttable_last_edited_at'] . ' ' . get_date($row['editedat'], 'DATE') . "</font></p>\n";
        }
        $top = $i++ >= 1 ? 'top20' : '';
        $htmlout .= main_div("
            $this_text
            <a id='comment_{$row['id']}'></a>
            <div class='is-flex'>
                <div class='w-20 padding20 round10 bg-02'>
                    <img src='" . image_proxy($avatar) . "' alt='Avatar' class='avatar' /><br>" . get_reputation($row, 'comments') . "
                </div>
                <div class='left20 padding20 w-100 bg-02 round10'>
                    $text
                </div>
            </div>", $top);
    }

    return $htmlout;
}
