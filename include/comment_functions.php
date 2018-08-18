<?php

/**
 * @param        $rows
 * @param string $variant
 *
 * @return string
 */
function commenttable($rows, $variant = 'torrent')
{
    require_once INCL_DIR . 'user_functions.php';
    require_once INCL_DIR . 'html_functions.php';
    global $CURUSER, $site_config, $mood, $cache, $session, $user_stuffs, $fluent;

    $lang = load_language('torrenttable_functions');
    $count = 0;
    $variant_options = [
        'torrent' => 'comment',
        'request' => 'requests',
        'offer' => 'offers',
        'usercomment' => 'usercomment',
    ];
    if (isset($variant_options[$variant])) {
        $type = $variant_options[$variant];
    } else {
        return;
    }
    $extra_link = ($variant === 'request' ? '&amp;type=request' : ($variant === 'offer' ? '&amp;type=offer' : ''));
    $delete = ($variant === 'request' || $variant === 'offer') ? 'action=delete_comment' : 'action=delete';
    $htmlout = '';
    $i = 0;
    foreach ($rows as $row) {
        $cid = $row['id'];
        if ($variant === 'torrent') {
            $variantc = 'comment';
        }
        $usersdata = $user_stuffs->getUserFromId($row['user']);
        $this_text = '';
        $moodname = (isset($mood['name'][$usersdata['mood']]) ? htmlsafechars($mood['name'][$usersdata['mood']]) : 'is feeling neutral');
        $moodpic = (isset($mood['image'][$usersdata['mood']]) ? htmlsafechars($mood['image'][$usersdata['mood']]) : 'noexpression.gif');
        $this_text .= "
            <div class='bottom20'>
                <span class='level-left'>#{$row['id']} {$lang['commenttable_by']} ";
        $likes = $att_str = '';
        $likers = $user_likes = [];
        if ($row['user_likes'] > 0) {
            $user_likes = $cache->get("{$type}_user_likes_" . $cid);
            if ($user_likes === false || is_null($user_likes)) {
                $query = $fluent->from('likes')
                    ->select(null)
                    ->select('user_id')
                    ->where("{$variantc}_id = ?", $cid);

                foreach ($query as $userid) {
                    $user_likes[] = $userid['user_id'];
                }
                $cache->set("{$type}_user_likes_" . $cid, $user_likes, 86400);
            }
            if ($user_likes) {
                foreach ($user_likes as $userid) {
                    $likers[] = format_username($userid);
                }
                $likes = implode(', ', $likers);
                $count = count($user_likes);
            }
        }
        if (!empty($likes) && $count > 0) {
            if (in_array($CURUSER['id'], $user_likes)) {
                if ($count === 1) {
                    $att_str = "<span class='chg'>You like this</span>";
                } else {
                    $att_str = "<span class='chg'>You and " . (($count - 1) === 1 ? '1 other person likes this' : ($count - 1) . ' others like this') . '</span>';
                }
            } else {
                if ($count === 1) {
                    $att_str = '1 person likes this';
                } else {
                    $att_str = $count . ' others like this';
                }
            }
        }
        $wht = $count > 0 && in_array($CURUSER['id'], $user_likes) ? 'unlike' : 'like';
        if (isset($row['user'])) {
            if ($row['anonymous'] === 'yes') {
                $this_text .= ($CURUSER['class'] >= UC_STAFF ? get_anonymous_name() . ' - Posted by: ' . format_username($row['user']) : get_anonymous_name());
            } else {
                $title = $usersdata['title'];
                if (empty($title)) {
                    $title = get_user_class_name($usersdata['class']);
                } else {
                    $title = htmlsafechars($title);
                }
                $this_text .= "<span class='left5'>" . format_username($row['user']) . '</span>';
                $this_text .= '
                    <a href="javascript:;" onclick="PopUp(\'usermood.php\',\'Mood\',530,500,1,1);" class="left5">
                        <img src="' . $site_config['pic_baseurl'] . 'smilies/' . $moodpic . '" alt="' . $moodname . '" class="tooltipper" title="' . ($row['anonymous'] === 'yes' ? '<i>' . get_anonymous_name() . '</i>' : htmlsafechars($usersdata['username'])) . ' ' . $moodname . '!" />
                    </a>';
            }
        } else {
            $this_text .= "<i>(" . $lang['commenttable_orphaned'] . ')</i></a>';
        }
        $this_text .= "<span class='left5'>" . get_date($row['added'], '') . '</span>';
        $row['id'] = (int) $row['id'];
        $tid = !empty($row[$variant]) ? "&amp;tid={$row[$variant]}" : '';

        $this_text .= ($row['user'] == $CURUSER['id'] || $CURUSER['class'] >= UC_STAFF ? "
                    <a href='{$site_config['baseurl']}/{$type}.php?action=edit&amp;cid={$row['id']}{$extra_link}{$tid}' class='button is-small left10'>{$lang['commenttable_edit']}</a>" : '') . ($CURUSER['class'] >= UC_VIP ? "
                    <a href='{$site_config['baseurl']}/report.php?type=Comment&amp;id={$row['id']}' class='button is-small left10'>Report this Comment</a>" : '') . ($CURUSER['class'] >= UC_STAFF ? "
                    <a href='{$site_config['baseurl']}/{$type}.php?{$delete}&amp;cid={$row['id']}{$extra_link}{$tid}' class='button is-small left10'>{$lang['commenttable_delete']}</a>" : '') . ($row['editedby'] && $CURUSER['class'] >= UC_STAFF ? "
                    <a href='{$site_config['baseurl']}/{$type}.php?action=vieworiginal&amp;cid={$row['id']}{$extra_link}{$tid}' class='button is-small left10'>{$lang['commenttable_view_original']}</a>" : '') . "
                    <span data-id='{$cid}' data-type='{$variant}' data-csrf='" . $session->get('csrf_token') . "' class='mlike button is-small left10'>" . ucfirst($wht) . "</span>
                    <span class='tot-{$cid} left10'>{$att_str}</span>
                </span>
            </div>";
        $avatar = get_avatar($row);
        $text = format_comment($row['text']);
        if ($row['editedby']) {
            $text = "
            <div class='flex-vertical comments h-100'>
                <div>$text</div>
                <div class='size_3'>{$lang['commenttable_last_edited_by']} " . format_username($row['editedby']) . " {$lang['commenttable_last_edited_at']} " . get_date($row['editedat'], 'DATE') . '</div>
            </div>';
        }
        $top = $i++ >= 1 ? 'top20' : '';
        $htmlout .= main_div("
            <a id='comm{$row['id']}'></a>
            $this_text
            <div class='columns'>
                <span class='margin10 round10 bg-02 column is-one-fifth has-text-centered img-avatar'>
                    {$avatar}
                    <div>" . get_reputation($row['user'], 'comments') . "</div>
                </span>
                <span class='margin10 bg-02 round10 column'>
                    $text
                </span>
            </div>", $top);
    }

    return $htmlout;
}
